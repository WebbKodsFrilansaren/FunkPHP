<?php // FunkCLI COMMAND "php funk make:middleware" - creates a new Middleware File with a skeleton Middleware Anonymous Function inside of it
// it can also attach to an optionally provided - only existing - Method/Route. Does NOT create Method/Route!
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
// OPTIONAL: Adding Created/Found Middleware to the
// Method/Route if it exists, otherwise it will say it
// does not and say that only the Middleware was created!
/////////////////////////////////////////////////////////
if (!isset($ROUTES[$method][$route])) {
    cli_warning_without_exit("The optionally provided Method/Route:`$method$route` does NOT exist in `funkphp/routes/routes.php` so could NOT add the Middleware to it.");
    cli_info("Middleware File `$middleware` was Created/Found but the optionally provided Method/Route:`$method$route` does NOT exist in `funkphp/routes/routes.php` so could NOT add the Middleware to it. Command Done!");
} elseif (!is_array($ROUTES[$method][$route]) || !array_is_list($ROUTES[$method][$route])) {
    cli_warning_without_exit("The optionally provided Method/Route:`$method$route` does exist in `funkphp/routes/routes.php` but was NOT a valid Numbered Array so could NOT add the Middleware to it. Verify it is a Numbered Array Starting at Index 0!");
    cli_info("Middleware File `$middleware` was Created/Found WITHOUT adding it to the optionally provided Method/Route:`$method$route` in `funkphp/routes/routes.php` due to its invalid Array Structure (not a numbered list). Command Done!");
}
// Middlewares key not added yet, so we add it to the top of the numbered array meaning we need to reshift
// all existing numbered array items down by one to make room for the new first item! Because it has not
// been created yet we do not need to verify its valid structure as this is the first item being added!
if (!isset($ROUTES[$method][$route][0]['middlewares'])) {
    array_unshift($ROUTES[$method][$route], []);
    $ROUTES[$method][$route][0]['middlewares'][] = [$middleware => null];
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success("Found/Created Middleware File AND Added it as the First Key to `$method$route` with the Single Middleware `$middleware`. Command Completed Successfully!");
}
// Here we must validate the middlewares structure before proceeding to add the new Middleware. We do not
// add a new middleware if it is invalid structure due to risk of breaking the Route and we also inform
// the developer to fix the issue first before retrying this Command!
elseif (isset($ROUTES[$method][$route][0]['middlewares'])) {
    // First we iterate through $ROUTES[$method][$route] to see if
    // the "middlewares" main mkey is at any other index than 0 which
    // is NOT allowed  so we error out and warn the developer to remove
    // or move that key to the top only index 0!
    $foundDuplicateMiddlewareMainkey = false;
    foreach ($ROUTES[$method][$route] as $indexKey => $routeKeyItem) {
        if ($indexKey === 0) {
            continue;
        }
        if (isset($routeKeyItem['middlewares'])) {
            $foundDuplicateMiddlewareMainkey = true;
            break;
        }
    }
    if ($foundDuplicateMiddlewareMainkey) {
        cli_err_without_exit("The optionally provided Method/Route:`$method$route` does exist in `funkphp/routes/routes.php` but has more than one `middlewares` Main Key at different Indexes so could NOT add the Middleware to it. Command Stopped to prevent breaking the Route!");
        cli_warning_without_exit("The `middlewares` Main Key MUST ONLY exist at Index 0 of the Method/Route's Numbered Array. At any other indexes it will be considered as a `folder=>file=function` and you run the risk of thinking you are doing important Middleware additions but in reality you are just adding more Route Keys that do NOT function as Middlewares at all!");
        cli_info("Please fix the `middlewares` Main Key in `$method$route` in `funkphp/routes/routes.php` so it ONLY exists at Index 0 like this:`[0] => ['middlewares' => [0 => ['mw_yourmiddleware' => null], 1 => ['mw_anothermiddleware' => null]]]`. Then retry this Command!");
    }
    // 'middlewares' key found but is NOT a valid numbered array so we error out
    if (!is_array($ROUTES[$method][$route][0]['middlewares']) || !array_is_list($ROUTES[$method][$route][0]['middlewares'])) {
        cli_err_without_exit("The optionally provided Method/Route:`$method$route` does exist in `funkphp/routes/routes.php` but its `middlewares` Key was NOT a valid Numbered Array so could NOT add the Middleware to it. Command Stopped to prevent breaking the Route!");
        cli_info("Please fix the `middlewares` Key in `$method$route` in `funkphp/routes/routes.php` so it is a valid Numbered Array like this:`'middlewares' => [0 => ['mw_yourmiddleware' => null], 1 => ['mw_anothermiddleware' => null]]; Then retry this Command!");
    }
    // Iterate through middlewares array and check that all keys are associative arrays (meanign they are like mw_name => null/whatever)
    foreach ($ROUTES[$method][$route][0]['middlewares'] as $mwKeyItem) {
        if (!is_array($mwKeyItem) || array_is_list($mwKeyItem)) {
            cli_err_without_exit("The optionally provided Method/Route:`$method$route` does exist in `funkphp/routes/routes.php` but one or more of its `middlewares` Key Items was NOT a valid Associative Array so could NOT add the Middleware to it. Command Stopped to prevent breaking the Route!");
            cli_info("Please fix the `middlewares` Key Items in `$method$route` in `funkphp/routes/routes.php` so ALL its Items are valid Associative Arrays like this:`['middlewares'] => [0] => ['mw_yourmiddleware' => null], [1] => ['and_so_on' => null]`. Then retry this Command!");
        }
        // If middleware is assopciative and has same name then we warn about duplicate but still add it again as last item
        if (array_key_exists($middleware, $mwKeyItem)) {
            cli_warning_without_exit("Duplicate Middleware Key - `$middleware` - in `$method$route`. However, will still add it AGAIN as the Last Middleware Key if Middleware Structure is ALL Valid!");
        }
    }
    // Finally just add it to the end
    $ROUTES[$method][$route][0]['middlewares'][] = [$middleware => null];
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success("Found/Created Middleware File AND Added it as the Last Added Middleware to `$method$route`. Command Completed Successfully!");
}
// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:middleware` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
