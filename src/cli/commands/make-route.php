<?php // FunkCLI COMMAND "php funk make:route" - creates a new route & optionally adds a folder=>file=>function to it
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

// 1. Find/create the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:route', 'method/route');
[$method, $route] = cli_extract_method_route($arg_methodRoute);

// 2. Find/create optional Folder/File/Function argument (e.g., "fff:usersFolder=>userFile=>FunctionInsideFile")
$arg_folderFileAndFn = cli_get_cli_input_from_interactive_or_regular($args, 'make:route', 'folder/file/fn');
if ($arg_folderFileAndFn) {
    [$folder, $file, $fn] =  cli_extract_folder_file_fn($arg_folderFileAndFn);
    $routeKey = [$folder => [$file => [$fn => null]]];
    $singleFolder = $folder;
    $folder = $folderBase . $folder . '/';
    $createdFFF = "Folder/File:`routes/$singleFolder/$file.php` with Function:`function $fn(){};`";
}

////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Creating a Route unless it already exists!
// If Method is not set, we create it even though it should actually exist
// and then we can also just add the Route to it and be done with it!
////////////////////////////////////////////////////////////////////////////
if (!array_key_exists($method, $ROUTES)) {
    $ROUTES[$method] = [];
    $ROUTES[$method][$route] = [];
    cli_info_without_exit("Added New Method and New Route to it... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to retry!");
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success_without_exit("Created New Valid Method `$method` in `funkphp/routes/routes.php`!");
    cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
}
// "else" means Method exist, but we do not know if Route exists in that Method
else {
    if (array_key_exists($route, $ROUTES[$method])) {
        cli_info_without_exit("`$method$route` already exists in `funkphp/routes/routes.php`. Any optionally provided `Folder=>File=>Function` will be added as a Route Key to it!");
    } else {
        // Check for dynamic conflicting routes in Trie Routes if the new route ends with a dynamic part like "/:something"
        if (preg_match($cliRegex['routeDynamicEndRegex'], $route)) {
            $troute = $singleTroute;
            $findDynamicRoute = cli_match_developer_route($method, $route, $troute, $ROUTES, $ROUTES);
            if ($findDynamicRoute['route'] !== null) {
                cli_err_without_exit("Found Dynamic Route \"{$findDynamicRoute['method']}{$findDynamicRoute['route']}\" in Trie Routes would conflict with \"$method$route\".");
                cli_info("Run `php funk recompile|rc` to rebuild Trie Routes if You Manually Removed that Route from `funkphp/routes/routes.php` you want to add again. Command stopped due to this and any optionally provided `Folder=>File=>Function` was NOT created as a result!");
            }
        }
        // Here a new Route is added to the Method because it does not already exist
        $ROUTES[$method][$route] = [];
        cli_info_without_exit("Added New Route `$route` to Method `$method` in `funkphp/routes/routes.php`... Attempting to rebuild the Trie & Route File Now... If it fails, the Method/Route will NOT have been added and you will have to retry!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
    }
}

// We exit if no optional Folder/File/Function argument was provided
if (!$arg_folderFileAndFn) {
    cli_info("No Folder=>File=>Function argument was provided so only `$method$route` was created. Command Done!");
}
////////////////////////////////////////////////////////////////////
// Here we have a valid Folder, File and Function to create but we
// do not know if it already exists or not so we check that now!
////////////////////////////////////////////////////////////////////
// Route should exist and must be a numbered array even if empty!
if (!isset($ROUTES[$method][$route])) {
    cli_err("`$method$route` does suddenly NOT EXIST in `funkphp/routes/routes.php` even though it should since we just created it earlier if it did not exist before. Command stopped without creating/adding the Route Key `$singleFolder=>$file=>$fn`!");
}
if (!is_array($ROUTES[$method][$route]) || !array_is_list($ROUTES[$method][$route])) {
    cli_err("`$method$route` in `funkphp/routes/routes.php` is NOT a Numbered Array even though it should be. Command stopped without creating/adding the Route Key `$singleFolder=>$file=>$fn`!");
}

// If there are already Route Keys in the Method/Route, we check if
// the provided Route Key already exists or not to warn about
// duplicates but we still allow adding duplicates if wanted.
if (count($ROUTES[$method][$route]) > 0) {
    if (!cli_duplicate_folder_file_fn_route_key($ROUTES[$method][$route], $singleFolder, $file, $fn, $method . $route)) {
        cli_info_without_exit("The Provided Route Key `$singleFolder=>$file=>$fn` does NOT exists in `$method$route` in `funkphp/routes/routes.php`. $createdFFF will be created in that order unless already existing as folder, file and/or function, and then it will be added as the next Route Key to `$method$route` if everything went OK!");
    } else {
        cli_info_without_exit("$createdFFF will be created in that order unless already existing as folder, file and/or function, and then it will be added as the last Route Key to `$method$route` if everything went OK!");
    }
} else {
    cli_info_without_exit("`$method$route` has NO Route Keys yet. $createdFFF will be created in that order unless already existing as folder, file and/or function, and then it will be added as the first Route Key to `$method$route` if everything went OK!");
}
// We add the Route Key to the Method/Route now and we can use array_pop() later if anything failed
// meaning it should not exist as a Route Key any longer for the matched/created Method/Route!
$ROUTES[$method][$route][] = $routeKey;

// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_folder_and_php_file_status($folder, $file);

// If folder path does not exist, we attempt creating it which also means
// that file and its fn does NOT exist so we will pass crudType "create_new_file_and_fn"
if (!$statusArray['folder_path']) {
    if (!mkdir(PROJECT_DIR . '/' . $folder, 0755, true)) {
        array_pop($ROUTES[$method][$route]);
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_err("FAILED to Create Folder `$folder`! Verify File Permissions and try again. Creating $createdFFF did NOT complete for `$method$route`!");
    }
    $statusArray = cli_folder_and_php_file_status($folder, $file);
    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
    if ($createStatus) {
        cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
        cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to by now!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
    } else {
        array_pop($ROUTES[$method][$route]);
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`. Creating $createdFFF did NOT complete for `$method$route`!");
    }
}  // Here crudType is "create_only_new_fn_in_file" unless the file does not exist
else {
    // File does not exist meaning crudType "create_new_file_and_fn"
    if (!$statusArray['file_exists']) {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
            cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to by now!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
        } else {
            array_pop($ROUTES[$method][$route]);
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`.  Creating $createdFFF did NOT complete for `$method$route`!");
        }
    }
    // crudType is now "create_only_new_fn_in_file" since file exists
    // unless function already exists in the file!
    else {
        // Function already exists in the file
        if (isset($statusArray['functions'][$fn])) {
            cli_info_without_exit("Function `$fn` already exists in File `$file.php` in Folder `$singleFolder`!");
            cli_info_without_exit("The Route File `$file.php` can be used in your Routes other than just `$method$route` where it has already been added to by now!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
        }
        // Function does not exist in the file so
        // crudType "create_only_new_fn_in_file"
        else {
            $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "routes", $method . $route);
            if ($createStatus) {
                cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in Folder `$singleFolder`!");
                cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to by now!");
                cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
                cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
            } else {
                array_pop($ROUTES[$method][$route]);
                cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
                cli_err("FAILED to Create Function `$fn` in File `$file.php` in Folder `$singleFolder`.  Creating $createdFFF did NOT complete for `$method$route`!");
            }
        }
    }
}
// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:route` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
