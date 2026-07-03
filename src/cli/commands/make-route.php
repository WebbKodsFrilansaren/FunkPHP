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
$folder = "funkphp/pipeline/routes/";
$singleFolder = 'funkphp/pipeline/routes/';
$file = null;
$fn = null;
$optionalCodeSnippets = ''; // Optional code snippets to add inside created File=>Fn Handler
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
    $createdFFF = "File:`funkphp/pipeline/routes/$file.php` with Function:`function $fn(){};`";
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
    // CONDITION 1: Check for an EXACT string match first (Handles static and exact matching dynamic parameters)
    if (isset($ROUTES[$method][$route])) {
        cli_info_without_exit("`$method$route` already exists exactly in `funkphp/core/pipeline_routes.php`. Any optionally provided `File=>Function` will be added as a Route Pipeline Key to it!");
    }
    // CONDITION 2: Not an exact match, but structurally collides (The Parameter Name Clash Edge Case!)
    else if (!cli_new_route_is_unique_in_its_method_group_VF($ROUTES[$method], $route)) {
        // Find which specific route in the array is causing the collision to give great developer feedback
        $clashingRoute = '[Unknown Path]';
        foreach (array_keys($ROUTES[$method]) as $existingKey) {
            if ($existingKey === '<CONFIG_METHOD>') continue;
            // Pass a single-item array to isolate the clash check
            if (!cli_new_route_is_unique_in_its_method_group_VF([$existingKey => []], $route)) {
                $clashingRoute = $existingKey;
                break;
            }
        }
        cli_err_syntax_without_exit("Route Collision Between Input:`$method$route` and Already Existing:`$method$clashingRoute`!");
        cli_info("If You meant to Target:`$method$clashingRoute`, please try again with that or Provide a Unique METHOD/ROUTE instead!");
    }
    // CONDITION 3: Completely unique new route structure
    else {
        // Here a new Route is added to the Method because it does not already exist
        $ROUTES[$method][$route] = FUNKPHP_DEFAULT_ROUTE_KEYS;
        cli_info_without_exit("Added New Route `$route` to Method `$method` in `funkphp/core/pipeline_routes.php`... Attempting to rebuild the Trie & Route File Now... If it fails, the Method/Route will NOT have been added and you will have to retry!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/core/pipeline_routes.php`!");
    }
}

// We exit if no optional Folder/File/Function argument was provided
if (!$arg_folderFileAndFn) {
    cli_success("No File=>Function argument was provided so only `$method$route` was created. Command Done!");
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
$statusArray = cli_folder_and_php_file_status($folder, $file);

// File does not exist so we create it and then rebuild routes
if (!$statusArray['file_exists']) {
    $mwString = "<?php\n\nnamespace funkphp\\pipeline\\routes\\$file;\n// FunkCLI Created File on " . date('Y-m-d H:i:s') . "!\n\nfunction $fn(&\$c)\n{\n\t// Placeholder Comment so Regex works - Remove & Add Your Own Code!\n$optionalCodeSnippets\n};\n";
    $newFilePath = $statusArray['folder_path'] . '/' . $statusArray['file_name'];
    if (cli_crud_folder_php_file_atomic_write($mwString, $newFilePath)) {
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
    cli_info_without_exit("The Route Function File `$file.php` can be used in your Routes other than just `$method$route` where it has BEEN ADDED AGAIN AT THE END of the `pipeline` Array Key!");
    cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
    cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/core/pipeline_routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its LAST PIPELINE KEY for the Route!");
}
// else means the function does not exist in the existing file so we add it to the end and rebuild routes
else {
    cli_info_without_exit("Function `$fn` does NOT exist in File `$file.php` in `$singleFolder`! Attempting to create it now... If it fails, the Route Pipeline Key `$file=>$fn` will be removed from `$method$route` and any created function will be removed from the file!");
    $mwString = $statusArray['file_raw'] . "\nfunction $fn(&\$c)\n{\n\t// Placeholder Comment so Regex works - Remove & Add Your Own Code!\n$optionalCodeSnippets\n};\n";
    $newFilePath = $statusArray['folder_path'] . '/' . $statusArray['file_name'];
    if (cli_crud_folder_php_file_atomic_write($mwString, $newFilePath)) {
        cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in `$singleFolder`!");
        cli_info_without_exit("The Route Function File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to by now!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/core/pipeline_routes.php` with the File=>Function in `$singleFolder=>$file=>$fn` added as its first or last Route Pipeline Key!");
    } else {
        array_pop($ROUTES[$method][$route]);
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_err("FAILED to Create Function `$fn` in File `$file.php` in `$singleFolder`. Creating $createdFFF did NOT complete for `$method$route`!");
    }
}

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:route` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
