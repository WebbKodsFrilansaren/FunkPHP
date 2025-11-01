<?php // FunkCLI COMMAND "php funk make:middleware" - creates a new Middleware File with a skeleton Middleware Anonymous Function inside of it
// it can also attach to an optionally - but must exist - Method/Route!
$ROUTES = $singleRoutesRoute['ROUTES'];

// Structure the correct folder name based on the first parameter,
// $folderType based on first parameter, and also initial $routeKey
// $routeKey is only applicable to "routes" and "middlewares"!
$arg_middleware  = null;
$middleware = null;
$newMWFile = null;
$arg_methodRoute = null;
$method = null;
$route = null;
$matchedRoute = null;
$createStatus = null;
$folderTypeMW = "middlewares";
$folderBaseMW = FUNKPHP_MIDDLEWARES_DIR . "/";
$folderTypeRoutes = "routes";
$folder = null;

// 1. Find/create the Middleware Name argument (e.g., "n:auth" -> mw_auth)
$arg_middleware = cli_get_cli_input_from_interactive_or_regular($args, 'make:middleware', 'middleware_name');
$middleware =  cli_extract_middleware($arg_middleware);

// 2. Find/create optional the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:middleware', 'method/route');
if ($arg_methodRoute) {
    [$method, $route] = cli_extract_method_route($arg_methodRoute);
}

///////////////////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Create or Find the Middleware!
///////////////////////////////////////////////////////////////////////////////////////////
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_middleware_file_status($middleware);

// MW File exists but is invalid (not returning a function) so we error out and help how to fix
if ($statusArray['exists'] && !$statusArray['middleware_is_valid']) {
    cli_err_without_exit("The Middleware File for Middleware `$middleware` already exists but is INVALID (it does NOT return a valid Middleware Anonymous Function). Command Stopped to prevent from accidentally adding it to optionally provided Method/Route!");
    cli_info("Please fix the Middleware File:`{$statusArray['full_file_path']}` by starting it with:`namespace FunkPHP\Middlewares\\$middleware; return function(&\$c,\$passedValue = null){// Your MW Code Here!};` so it returns an Anonymous Function. Then retry this Command!");
}

// MW File exists and is valid Middleware Anonymous Function
if ($statusArray['exists'] && $statusArray['middleware_is_valid']) {
    cli_info_without_exit("OK! Middleware File `$middleware` already exists and is a VALID Middleware Anonymous Function. Proceeding to optionally add it to the provided Method/Route if any...");
}

// MW File does NOT exist and its Middleware Directory is missing or not Readable/Writable
// meaning we cannot continue and hard error out and informing developer to fix the issue first!
if (
    !$statusArray['exists']
    && (!$statusArray['middleware_dir_exists']
        || !$statusArray['middleware_dir_readable']
        || !$statusArray['middleware_dir_writable'])
) {
    cli_err_without_exit("Middleware File `$middleware` does NOT exist yet but its Middleware Directory:`$folderBaseMW` is either missing or not Readable/Writable so cannot create the Middleware File. Command Stopped!");
    cli_info("Please make sure the Middleware Directory:`$folderBaseMW` exists and is Readable/Writable by the current User running the CLI Command. Then retry this Command!");
}

// MW File dose NOT exist so we procede creating it now using the $statusArray info
// that is also used as "proof" that we have checked everything needed before creating!
if (!$statusArray['exists']) {
    $newMWFile = cli_create_middleware_file($middleware, $statusArray);
    if ($newMWFile) {
        cli_success_without_exit("Created New Middleware File for Middleware `$middleware` at Path:`$newMWFile`! Adding it to any optionally provided Method/Route now... IMPORTANT: If it already exists in the Method/Route it will still be added AGAIN as the last Route Key but a warning will also be shown!");
    } else {
        cli_err("Failed to Create New Middleware File for Middleware `$middleware`, probably due to Folder and/or File Permissions in your FunkPHP Project Middlewares Folder:`src/funkphp/middlewares/`. Command Stopped!");
    }
}

// We exit if no optional Method/Route argument was provided
if (!$arg_methodRoute) {
    cli_info_without_exit("No `Method/Route` Argument was provided so only the Middleware File `$middleware` was created. Command Done!");
    cli_success("Found/Created Middleware File without adding it to a Method/Route. Command Completed Successfully!");
}

/////////////////////////////////////////////////////////
// OPTIONAL: Creating the Method/Route if it does not
// exist yet AND add the found/created Route Key to it!
/////////////////////////////////////////////////////////
if (!array_key_exists($method, $ROUTES)) {
    $ROUTES[$method] = [];
    $ROUTES[$method][$route][] = $routeKey;
    cli_info_without_exit("Added New Method and New Route to it... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to retry. The created/found `$singleFolder=>$file=>$fn` Handler will still exist though!");
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success_without_exit("Created New Valid Method `$method` in `funkphp/routes/routes.php`!");
    cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
    cli_success("Found/Created `$singleFolder=>$file=>$fn` Handler and then added it to Created `$method$route` in `funkphp/routes/routes.php`!");
}

// Here the Method exists already so we check if the Route exists and is a numbered array (valid structure)
if (array_key_exists($route, $ROUTES[$method])) {
    cli_info_without_exit("`$method$route` already exists in `funkphp/routes/routes.php`. Attempting Adding `$folder=>$file=>$fn` to it and then rebuilding Routes!");
} else {
    // Check for dynamic conflicting routes in Trie Routes if the new route ends with a dynamic part like "/:something"
    if (preg_match($cliRegex['routeDynamicEndRegex'], $route)) {
        $troute = $singleTroute;
        $findDynamicRoute = cli_match_developer_route($method, $route, $troute, $ROUTES, $ROUTES);
        if ($findDynamicRoute['route'] !== null) {
            cli_err_without_exit("Found Dynamic Route \"{$findDynamicRoute['method']}{$findDynamicRoute['route']}\" in Trie Routes would conflict with \"$method$route\".");
            cli_info("Run `php funk recompile|rc` to rebuild Trie Routes if You Manually Removed that Route from `funkphp/routes/routes.php` you want to add again. Command stopped due to this and adding found/created `$folder=>$file=>$fn` to `$method$route` did NOT happen as a result!");
        }
    }
    // Here a new Route is added to the Method because it does not already exist
    $ROUTES[$method][$route] = [];
    cli_success_without_exit("`$method$route` CREATED in `funkphp/routes/routes.php`. Attempting Adding `$folder=>$file=>$fn` to it and then rebuilding Routes!");
}

// Created/Found the Method/Route must be a numbered array (even if empty)
if (!array_is_list($ROUTES[$method][$route])) {
    cli_err("`$method$route` in `funkphp/routes/routes.php` is NOT a Numbered Array even though it should be. Command stopped without adding the created/found Route Key `$singleFolder=>$file=>$fn` to `$method$route`!");
}
// If there are already Route Keys in the Method/Route, we check and warn for duplicates
if (count($ROUTES[$method][$route]) > 0) {
    if (!cli_duplicate_folder_file_fn_route_key($ROUTES[$method][$route], $singleFolder, $file, $fn, $method . $route)) {
        cli_info_without_exit("Created/Found `$singleFolder=>$file=>$fn` does NOT exists in found/created `$method$route` in `funkphp/routes/routes.php`. Adding $createdFFF to it and then rebuilding Routes!");
    } else {
        cli_info_without_exit("Adding $createdFFF as the last Route Key to `$method$route` and then rebuilding Routes!");
    }
} else {
    cli_info_without_exit("Created/Found `$method$route` has NO Route Keys yet. Adding $createdFFF to it and then rebuilding Routes!");
}

// We now add it and then rebuild the Routes
$ROUTES[$method][$route][] = $routeKey;
cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
cli_success("Found/Created `$singleFolder=>$file=>$fn` Handler and then added it to Created `$method$route` in `funkphp/routes/routes.php`!");

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:handler` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
