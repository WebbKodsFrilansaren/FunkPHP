<?php // FunkCLI COMMAND "php funk make:handler" - creates a new file and optionally adds a function to a specific Method/Route
$ROUTES = $singleRoutesRoute['ROUTES'];

// Structure the correct folder name based on the first parameter,
// $folderType based on first parameter, and also initial $routeKey
// $routeKey is only applicable to "routes" and "middlewares"!
$arg_methodRoute = null;
$arg_folderFileAndFn = null;
$method = null;
$route = null;
$matchedRoute = null;
$routeKey = null;
$createStatus = null;
$folderType = "routes";
$folderBase = "funkphp/routes/";
$folder = null;
$singleFolder = null;
$file = null;
$fn = null;
$createdFFF = "<N/A>";

//1. Find & extract Folder/File/Function argument (e.g., "fff:usersFolder=>userFile=>FunctionInsideFile")
$arg_folderFileAndFn = cli_get_cli_input_from_interactive_or_regular($args, 'make:handler', 'folder/file/fn');
[$folder, $file, $fn] =  cli_extract_folder_file_fn($arg_folderFileAndFn);
$routeKey = [$folder => [$file => [$fn => null]]];
$singleFolder = $folder;
$folder = $folderBase . $folder . '/';
$createdFFF = "Folder/File:`routes/$singleFolder/$file.php` with Function:`function $fn(){};`";

// 2. Find/create optional the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:handler', 'method/route');
if ($arg_methodRoute) {
    [$method, $route] = cli_extract_method_route($arg_methodRoute);
}

///////////////////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Creating a Handler (Folder=>File=>Function) unless it already exists!
///////////////////////////////////////////////////////////////////////////////////////////
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_folder_and_php_file_status($folder, $file);

// If folder path does not exist, we attempt creating it which also means
// that file and its fn does NOT exist so we will pass crudType "create_new_file_and_fn"
if (!$statusArray['folder_path']) {
    if (!mkdir(PROJECT_DIR . '/' . $folder, 0755, true)) {
        cli_err("FAILED to Create Folder `$folder`! Verify File Permissions and try again. Creating $createdFFF did NOT complete for `$method$route`!");
    }
    $statusArray = cli_folder_and_php_file_status($folder, $file);
    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
    if ($createStatus) {
        cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
        cli_info_without_exit("The File `$file.php` is now ready to be used in your Routes!");
    } else {
        cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`. Creating $createdFFF did NOT complete for `$method$route`!");
    }
}  // Here crudType is "create_only_new_fn_in_file" unless the file does not exist
else {
    // File does not exist meaning crudType "create_new_file_and_fn"
    if (!$statusArray['file_exists']) {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
            cli_info_without_exit("The File `$file.php` is now ready to be used in your Routes!");
        } else {
            cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`.  Creating $createdFFF did NOT complete for `$method$route`!");
        }
    }
    // crudType is now "create_only_new_fn_in_file" since file exists
    // unless function already exists in the file!
    else {
        // Function already exists in the file
        if (isset($statusArray['functions'][$fn])) {
            cli_info_without_exit("Function `$fn` already exists in File `$file.php` in Folder `$singleFolder`!");
            cli_info_without_exit("The File `$file.php` is now ready to be used in your Routes!");
        }
        // Function does not exist in the file so
        // crudType "create_only_new_fn_in_file"
        else {
            $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "routes", $method . $route);
            if ($createStatus) {
                cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in Folder `$singleFolder`!");
                cli_info_without_exit("The File `$file.php` is now ready to be used in your Routes!");
            } else {
                cli_err("FAILED to Create Function `$fn` in File `$file.php` in Folder `$singleFolder`.  Creating $createdFFF did NOT complete for `$method$route`!");
            }
        }
    }
}
// We exit if no optional Method/Route argument was provided
if (!$arg_methodRoute) {
    cli_info("No `Method/Route` Argument was provided so only the `$folder=>$file=>$fn` Handler was created. Command Done!");
}

/////////////////////////////////////////////////////////
// OPTIONAL: Creating the Method/Route if it does not
// exist yet AND add the found/created Route Key to it!
/////////////////////////////////////////////////////////
if (!array_key_exists($method, $ROUTES)) {
    $ROUTES[$method] = [];
    $ROUTES[$method][$route] = $routeKey;
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
