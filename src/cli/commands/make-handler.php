<?php // FunkCLI COMMAND "php funk make:handler" - creates a new file and optionally adds a function to a specific Method/Route

/**
 * -----------------------
 * FUNKCLI DEFAULT COMMAND
 * -----------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU KNOW IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/

$ROUTES = $singleRoutesRoute['ROUTES'];

// Structure the correct folder name based on the first parameter,
// $folderType based on first parameter, and also initial $routeKey
// $routeKey is only applicable to "routes" and "middlewares"!
$arg_methodRoute = null;
$arg_folderFileAndFn = null;
$arg_snippets = null;
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

//1. Find & extract Folder/File/Function argument (e.g., "ff:userFile,FnInsideFile")
$arg_folderFileAndFn = cli_get_cli_input_from_interactive_or_regular($args, 'make:handler', 'file/fn');
[$file, $fn] =  cli_extract_folder_file($arg_folderFileAndFn);
$routeKey = [$file => $fn];
$singleFolder = $folder;
$createdFFF = "Folder/File:`funkphp/pipeline/routes/$file.php` with Function:`function $fn(){};`";

// 2. Optional snippets to add to the created file=>fn
$arg_snippets = cli_get_cli_input_from_interactive_or_regular($args, 'make:handler', 'snippets');
if ($arg_snippets) {
    $optionalCodeSnippets = cli_snippets_load($arg_snippets);
}

// 3. Find/create optional the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:handler', 'method/route');
if ($arg_methodRoute) {
    [$method, $route] = cli_extract_method_route($arg_methodRoute);
}

//////////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Creating a Handler (File,Fn) unless it already exists!
//////////////////////////////////////////////////////////////////////////////////
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_file_status($folder, $file);

// If folder path does not exist, we attempt creating it which also means
// that file and its fn does NOT exist so we will pass crudType "create_new_file_and_fn"
// File does not exist so we create it and then rebuild routes
if (!$statusArray['file_exists']) {
    $mwString = "<?php\n\nnamespace funkphp\\pipeline\\routes\\$file;\n// FunkCLI Created File on " . date('Y-m-d H:i:s') . "!\n\nfunction $fn(&\$c)\n{\n\t// Placeholder Comment so Regex works (do NOT place comment after closing '}') - Remove & Add Your Own Code!\n$optionalCodeSnippets\n}\n";
    $newFilePath = $statusArray['folder_path'] . '/' . $statusArray['file_name'];
    if (cli_crud_folder_php_file_atomic_write($mwString, $newFilePath)) {
        cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
    } else {
        cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`. Command Stopped!");
    }
}
// crudType is now "create_only_new_fn_in_file" since file exists
// unless function already exists in the file!
// Function already exists in the file
else if (isset($statusArray['functions'][$fn])) {
    cli_info_without_exit("Function `$fn` ALREADY EXISTS in File `$file.php` in`$singleFolder`! Nothing will be done to the file as a result!");
}
// else means the function does not exist in the existing file so we add it to the end and rebuild routes
else {
    cli_info_without_exit("Function `$fn` does NOT exist in File `$file.php` in `$singleFolder`! Adding it now... If it fails, its adding to the optionally provided `$method$route` will not take place!");
    $mwString = $statusArray['file_raw'] . "\nfunction $fn(&\$c)\n{\n\t// Placeholder Comment so Regex works (do NOT place comment after closing '}') - Remove & Add Your Own Code!\n$optionalCodeSnippets\n}\n";
    $newFilePath = $statusArray['folder_path'] . '/' . $statusArray['file_name'];
    if (cli_crud_folder_php_file_atomic_write($mwString, $newFilePath)) {
        cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in `$singleFolder`!");
        cli_info_without_exit("The Route Function File `$file.php` is now ready to be used in your other Routes besides the optionally provided one!");
    } else {
        cli_err("FAILED to Create Function `$fn` in File `$file.php` in `$singleFolder`. Adding it to the optionally provided Method/Route will not take place!");
    }
}

// When no OPTIONAL Method/Route argument was provided, we exit
// here since the Handler was created/found successfully
if (!$arg_methodRoute) {
    cli_info_without_exit("No `Method/Route` Argument was provided so only the Handler `$createdFFF` was created/found. Command Done!");
    cli_success("Found/Created Handler `$createdFFF` without adding it to a `Method/Route`. Command Completed Successfully!");
}

///////////////////////////////////////////////////////
// OPTIONAL: Creating the Method/Route if it does not
// exist yet AND add the found/created Route Key to it!
///////////////////////////////////////////////////////
if (!array_key_exists($method, $ROUTES)) {
    $ROUTES[$method] = [];
    $ROUTES[$method][$route] = FUNKPHP_DEFAULT_ROUTE_KEYS;
    cli_info_without_exit("Created New Method `$method` and New Route `$route` for `funkphp/core/pipeline_routes.php`! The `$file=>$fn` will be added as THE LAST ROUTE PIPELINE KEY to it!");
}
// "else" means Method exist, but we do not know if Route exists in that Method
else {
    // CONDITION 1: Check for an EXACT string match first (Handles static and exact matching dynamic parameters)
    if (isset($ROUTES[$method][$route])) {
        cli_info_without_exit("`$method$route` already exists exactly in `funkphp/core/pipeline_routes.php`. The `$file=>$fn` will be added as THE LAST ROUTE PIPELINE KEY to it!");
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
        cli_info("If You meant to Target:`$method$clashingRoute`, please try again with that or Provide a Unique METHOD/ROUTE instead. Command completed WITHOUT adding `$file=>$fn` to `$method$route`!");
    }
    // CONDITION 3: Completely unique new route structure
    else {
        // Here a new Route is added to the Method because it does not already exist
        $ROUTES[$method][$route] = FUNKPHP_DEFAULT_ROUTE_KEYS;
        cli_info_without_exit("Created New Route `$route` to Method `$method` in `funkphp/core/pipeline_routes.php`! The `$file=>$fn` will be added as THE LAST ROUTE PIPELINE KEY to it!");
    }
}


/////// FIX: SHOW CLEARER ERROR WHEN TRYING TO ADD SAME/ALREADY EXISTING HANDLER!!!
/////// DO SAME IN "make:route" COMMAND FILE AS WELL! ^_^

// We now add it and then rebuild the Routes
if (!isset($ROUTES[$method][$route]['pipeline'][$file])) {
    $ROUTES[$method][$route]['pipeline'][$file] = $fn;
    cli_info_without_exit("ADDED NEW `$file=>$fn` Handler to `$method$route` in `funkphp/core/pipeline_routes.php`! Rebuilding Routes Now...");
} else {
    if (isset($ROUTES[$method][$route]['pipeline'][$file]) && $ROUTES[$method][$route]['pipeline'][$file] === $fn) {
        cli_info_without_exit("`FOUND ALREADY ADDED $file=>$fn` Handler to `$method$route` in `funkphp/core/pipeline_routes.php`! Rebuilding Routes Now...");
    } else {
        $ROUTES[$method][$route]['pipeline'][$file] = $fn;
        cli_info_without_exit("`ADDED NEW $file=>$fn` Handler to `$method$route` in `funkphp/core/pipeline_routes.php`! Rebuilding Routes Now...");
    }
}
cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
cli_success("Found/Created `$file=>$fn` Handler and then added it to Created/Found `$method$route` in `funkphp/pipeline/pipeline_routes.php`!");

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:handler` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
