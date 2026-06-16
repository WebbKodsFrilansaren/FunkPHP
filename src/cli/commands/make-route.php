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
$folderBase = "funkphp/pipeline/routes/";
$folder = "funkphp/pipeline/routes/";
$singleFolder = 'funkphp/pipeline/routes/';
$file = null;
$fn = null;
$createdFFF = "<N/A>";

// 1. Find/create the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:route', 'method/route');
[$method, $route] = cli_extract_method_route($arg_methodRoute);

// 2. Find/create optional Folder/File/Function argument (e.g., "fff:usersFolder=>userFile=>FunctionInsideFile")
$arg_folderFileAndFn = cli_get_cli_input_from_interactive_or_regular($args, 'make:route', 'file/fn');
if ($arg_folderFileAndFn) {
    [$file, $fn] =  cli_extract_folder_file($arg_folderFileAndFn);
    $routeKey = [$file => $fn];
    $singleFolder = $folder;
    $folder = $folderBase;
    $createdFFF = "File:`src/funkphp/pipeline/routes/$file.php` with Function:`function $fn(){};`";
}

////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Creating a Route unless it already exists!
// If Method is not set, we create it even though it should actually exist
// and then we can also just add the Route to it and be done with it!
////////////////////////////////////////////////////////////////////////////
if (!array_key_exists($method, $ROUTES)) {
    $ROUTES[$method] = [];
    $ROUTES[$method][$route] = FUNKPHP_DEFAULT_ROUTE_KEYS;
    cli_info_without_exit("Added New Method and New Route to it... Attempting to rebuild the Routes Files Now... If it fails, the Route will NOT have been added and you will have to retry!");
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/core/pipeline_routes.php`!");
}
// "else" means Method exist, but we do not know if Route exists in that Method
else {
    if (!cli_new_route_is_unique_in_its_method_group_VF($ROUTES[$method], $route)) {
        cli_info_without_exit("`$method$route` already exists in `funkphp/core/pipeline_routes.php`. Any optionally provided `Folder=>File=>Function` will be added as a Route Pipeline Key to it!");
    } else {
        // Here a new Route is added to the Method because it does not already exist
        $ROUTES[$method][$route] = FUNKPHP_DEFAULT_ROUTE_KEYS;
        cli_info_without_exit("Added New Route `$route` to Method `$method` in `funkphp/core/pipeline_routes.php`... Attempting to rebuild the Trie & Route File Now... If it fails, the Method/Route will NOT have been added and you will have to retry!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/core/pipeline_routes.php`!");
    }
}

// We exit if no optional Folder/File/Function argument was provided
if (!$arg_folderFileAndFn) {
    cli_info("No File=>Function argument was provided so only `$method$route` was created. Command Done!");
}
////////////////////////////////////////////////////////////////////
// Here we have a valid Folder, File and Function to create but we
// do not know if it already exists or not so we check that now!
////////////////////////////////////////////////////////////////////
// Route should exist and must be a numbered array even if empty!
if (!isset($ROUTES[$method][$route])) {
    cli_err("`$method$route` does suddenly NOT EXIST in `funkphp/core/pipeline_routes.php` even though it should since we just created it earlier if it did not exist before. Command stopped without creating/adding the Route Pipeline Key `$file=>$fn`!");
}
if (!is_array($ROUTES[$method][$route]) || !cli_existing_route_has_valid_key_structure_VF($ROUTES[$method][$route])) {
    cli_err("`$method$route` in `funkphp/core/pipeline_routes.php` has INVALID data structure where its 3 first Associative Keys should be: `config` (associative), `middlewares` (numbered) and `pipeline` (numbered). Command stopped without creating/adding the Route Pipeline Key `$file=>$fn`!");
}

// If there are already Route Pipeline Keys in the Method/Route, we check if
// the provided Route Pipeline Key already exists or not to warn about
// duplicates but we still allow adding duplicates if wanted.
if (count($ROUTES[$method][$route]) > 0) {
    if (!cli_existing_route_has_duplicate_pipeline_fns_VF($ROUTES[$method][$route], $file, $fn)) {
        cli_info_without_exit("The Provided Route Pipeline Key `$file=>$fn` does NOT exists in `$method$route` in `funkphp/core/pipeline_routes.php`. $createdFFF will be created in that order unless already existing as File and/or Function, and then it will be added as the next Route Pipeline Key to `$method$route` if everything went OK!");
    } else {
        cli_info_without_exit("$createdFFF will be created in that order unless already existing as a File and/or Function, and then it will be added as the last Route Pipeline Key to `$method$route` if everything went OK!");
    }
} else {
    cli_info_without_exit("`$method$route` has NO Route Pipeline Keys yet. $createdFFF will be created in that order unless already existing as a File and/or Function, and then it will be added as the first Route Pipeline Key to `$method$route` if everything went OK!");
}
// We add the Route Pipeline Key to the Method/Route now and we can use array_pop() later if anything failed
// meaning it should not exist as a Route Pipeline Key any longer for the matched/created Method/Route!
$ROUTES[$method][$route]['pipeline'][] = $routeKey;

// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
var_dump($folder, $file, $fn);
$statusArray = cli_folder_and_php_file_status($folder, $file);

// File does not exist meaning crudType "create_new_file_and_fn"
if (!$statusArray['file_exists']) {
    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
    if ($createStatus) {
        cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
        cli_info_without_exit("The Route Function File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to by now!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/core/pipeline_routes.php` with the File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Pipeline Key!");
    } else {
        array_pop($ROUTES[$method][$route]);
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`.  Creating $createdFFF did NOT complete for `$method$route`!");
    }
}
// crudType is now "create_only_new_fn_in_file" since file exists
// unless function already exists in the file!

// Function already exists in the file
if (isset($statusArray['functions'][$fn])) {
    cli_info_without_exit("Function `$fn` ALREADY EXISTS in File `$file.php` in`$singleFolder`!");
    cli_info_without_exit("The Route Function File `$file.php` can be used in your Routes other than just `$method$route` where it has already been added to by now!");
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/core/pipeline_routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Pipeline Key!");
}
// Function does not exist in the file so
// crudType "create_only_new_fn_in_file"
cli_info_without_exit("Function `$fn` does NOT exist in File `$file.php` in `$singleFolder`! Attempting to create it now... If it fails, the Route Pipeline Key `$file=>$fn` will be removed from `$method$route` and any created function will be removed from the file!");
$createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "routes", $method . $route);
if ($createStatus) {
    cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in `$singleFolder`!");
    cli_info_without_exit("The Route Function File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to by now!");
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/core/pipeline_routes.php` with the File=>Function in `$singleFolder=>$file=>$fn` added as its first or last Route Pipeline Key!");
} else {
    array_pop($ROUTES[$method][$route]);
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_err("FAILED to Create Function `$fn` in File `$file.php` in `$singleFolder`. Creating $createdFFF did NOT complete for `$method$route`!");
}



// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:route` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
