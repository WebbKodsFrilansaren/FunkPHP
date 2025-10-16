<?php // FunkCLI COMMAND "php funk make|create|new" - creates a new file and optionally adds a function to a specific Method/Route
// MAKE: Create something new and OPTIONALLY adding it to a specific Method/Route
// SYNTAX: funk make|create|new:<first_param> <file_name>[=>function_name] [method/route]
$middlewaresAliases = ['mw', 'mws', 'middlewares', 'middleware'];
$singlePipelineAliases = ['pl', 'pls', 'pipeline', 'pipelines'];
$validationAliases = ['v', 'validation'];
$sqlAliases = ['sql', 's'];
$routeAliases = ['route', 'r', 'rutt']; // "rutt" Swedish Easter Egg for "Route" in Swedish
$folderListThatNeedsTables = ['sql', 'validation', 'v', 's'];
$folderListThatWillCauseWarning = [
    'routes',
    'cached',
    'classes',
    'backups',
    '_final_backups',
    'valid',
    'complete',
    'functions',
    'compiled',
    'components',
    'partials',
    'blocked',
    'config',
    '_internals',
    'batteries',
    'post-response',
    'request',
    'gui',
    'public_html',
    'cli',
    'test',
    'tests',
    'schema',
    'schemas',
];
$ROUTES = $singleRoutesRoute['ROUTES'];

// Issue a warning but still continue if the first parameter is in the list of folders that will cause a warning
// because these folders are already being used for other purposes but they could be used inside of "funkphp/routes/"
// which is where they will be put!
if (in_array($firstParam, $folderListThatWillCauseWarning)) {
    cli_warning_without_exit("The First Parameter `$firstParam` is in the list of Folders that are already being used by FunkPHP. Despite this, `$firstParam` will be used as a subfolder in `funkphp/routes/`!");
    cli_info_without_exit("This is just a heads-up so you are not confused by seeing a folder with the same name in several places inside of FunkPHP!");
}



// Structure the correct folder name based on the first parameter,
// $folderType based on first parameter, and also initial $routeKey
// $routeKey is only applicable to "routes" and "middlewares"!
$folder = null;
$routeKey = null;
$anonymousFile = false;
$folderType = null;
$tablesProvided = null;
$file = null;
$fn = null;
$routeOnly = false;

// DEBUG
echo "Command: $command\nArg1: $arg1\nArg2: $arg2\nArg3: $arg3\nArg4: $arg4\nArg5: $arg5\nArg6: $arg6\n";

if (in_array($firstParam, $middlewaresAliases)) {
    $folder = "funkphp/middlewares";
    $folderType = "middlewares";
    $routeKey = ["middlewares" => null];
    $anonymousFile = true; // Middlewares are always single anonymous function files
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1, "m_");
    cli_info_without_exit("OK! Middleware Files. Middleware Functions are recommended to be reused in different projects, consider versioning their name endings. Run `funk recompile|rc` if you have manually added the Middleware Function File to a `middlewares` Route Key to a given Route!");
    $routeKey[key($routeKey)] = $file;
} elseif (in_array($firstParam, $singlePipelineAliases)) {
    $folderType = "pipeline";
    $folder = "funkphp/pipeline";
    $anonymousFile = true; // Pipeline functions are always single anonymous function files
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1, "pl_");
    cli_info_without_exit("OK! Pipeline File. Pipeline Functions are recommended to be reused in different projects, consider versioning their name endings. Drag this Function File into either `funkphp/pipeline/request/` OR `funkphp/pipeline/post-response/` and then add it manually to the corresponding Pipeline Sub-Array! (`pipeline['request']` or `pipeline['post-response']`)");
} elseif (in_array($firstParam, $validationAliases)) {
    $folderType = "validation";
    $folder = "funkphp/validation";
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1, "v_");
    cli_info_without_exit("OK! Validation File and/or Validation Function. These are recommended to be used by calling `funk_use_valdation(&\$c,\$file_name,\$file_fn)` inside an Anonymous Function OR a File=>Function File!");
} elseif (in_array($firstParam, $sqlAliases)) {
    $folderType = "sql";
    $folder = "funkphp/sql";
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1, "s_");
    cli_info_without_exit("OK! SQL File and/or SQL Function. These are recommended to be used by first calling `funk_load_sql(&\$c,\$file_name,\$file_fn)` and then `funk_use_sql(&\$c,\$loadedSQLArray,\$optionalInputData,\$hydrateAfterQuery)` inside an Anonymous Function OR a File=>Function File!");
}
// This is a special case where we only create
// a Route for `funkphp/routes/routes.php`!
elseif (in_array($firstParam, $routeAliases)) {
    $routeOnly = true;
    cli_info_without_exit("OK! ONLY a Route to `funkphp/routes/routes.php` unless it already exists. After this, you can add `Route Keys` to that created Route!");
} else {
    $folderType = "routes";
    $folder = "funkphp/routes" . '/' . $firstParam;
    $routeKey = $firstParam;
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1);
    cli_info_without_exit("OK! A Route File and/or Route Function that will be placed inside:`$folder`. If you provided a Method/Route it will be automatically added IF it is sucessfully created! Otherwise, you will have to manually add it to a Route Key of a Route manually or by running `funk add:$firstParam $file=>$fn <method/route>`!");
}
// Passing $arg2 for Method/Route is optional so we check if it is set
$method = null;
$route = null;
// SPECIAL_CASE: Only adding a Route to `funkphp/routes/routes.php`, so this
// part will run and either Fail or Succeed and then exit the script!
if ($routeOnly) {
    if (!isset($arg1) || !is_string($arg1) || empty($arg1) || !preg_match($methodRouteRegex, $arg1)) {
        cli_err_without_exit("No Valid Method/Route provided when wanting to only Create a New Route! `($cmd:$firstParam)`");
        cli_info_without_exit("Syntax is: `funk make:r method/route` like `funk make:r g/users` OR `funk make:r post/users`!");
        cli_info("Notice the support for shorthand versions \"g\", \"po\", \"pu\", \"d\" OR \"del\", \"pa\" for GET, POST, PUT, DELETE and PATCH respectively!");
    }
    [$method, $route] =  cli_return_valid_method_n_route_or_err_out($arg1);

    // We tell that other arguments will be ignored since we
    // are only adding a Route to `funkphp/routes/routes.php`
    if (isset($arg2) || isset($arg3) || isset($arg4) || isset($arg5) || isset($arg6)) {
        cli_warning_without_exit("Ignoring other arguments (\$'arg2' to \$'arg6') since this Command ONLY adds a New Route to `funkphp/routes/routes.php`!");
    }

    // If Method is not set, we create it even though it should actually exist
    // and then we can also just add the Route to it and be done with it!
    if (!array_key_exists($method, $ROUTES)) {
        $ROUTES[$method] = [];
        $ROUTES[$method][$route] = [];
        cli_info_without_exit("Added New Method and New Route to it... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to try again!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success_without_exit("Created New Valid Method `$method` in `funkphp/routes/routes.php`!");
        cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
        cli_info("You can now add Route Keys to this Route by running `funk add:subfolder_in_funkphp File=>Function $method$route`!");
    }
    // "else" means Method exist, but we do not know if Route exists in that Method
    else {
        if (array_key_exists($route, $ROUTES[$method])) {
            cli_err_without_exit("Route `$method$route` already exists in `funkphp/routes/routes.php`! Please provide a different Route OR Method/Route. Here is Status of that Method/Route:");
            $routeStatus = cli_route_status($ROUTES, $method, $route);
            var_dump($routeStatus);
            cli_info("You can add (additional) Route Keys to this Route by running `php funk make:subfolder_in_funkphp_routes_folder File=>Function $method$route`!");
        } else {
            // Check for dynamic conflicting routes in Trie Routes if the new route ends with a dynamic part like "/:something"
            if (preg_match($routeDynamicEndRegex, $route)) {
                $troute = $singleTroute;
                $findDynamicRoute = cli_match_developer_route($method, $route, $troute, $ROUTES, $ROUTES);
                if ($findDynamicRoute['route'] !== null) {
                    cli_err_without_exit("Found Dynamic Route \"{$findDynamicRoute['method']}{$findDynamicRoute['route']}\" in Trie Routes would conflict with \"$method$route\".");
                    cli_info("Run `php funk recompile|rc` to rebuild Trie Routes if You Manually Removed that Route from `funkphp/routes/routes.php` you want to add again!");
                }
            }

            // Here a new Route is added to the Method because it does not already exist
            $ROUTES[$method][$route] = [];
            cli_info_without_exit("Added New Route `$route` to Method `$method` in `funkphp/routes/routes.php`... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to try again!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
            cli_info("You can now add  Route Keys to this Route by running `php funk make:subfolder_in_funkphp_routes_folder File=>Function $method$route`!");
        }
    }
} // END OF SPECIAL_CASE: Only adding a Route to `funkphp/routes/routes.php`

// continue with the rest of the command when NOT only adding a Route to `funkphp/routes/routes.php`
if (isset($arg2) && is_string($arg2) && !empty($arg2) && preg_match($methodRouteRegex, $arg2)) {
    if ($folderType !== "middlewares" && $folderType !== "routes") {
        cli_warning_without_exit("$folder does NOT use Method/Route so `$arg2` Is Ignored!");
    } else {
        [$method, $route] =  cli_return_valid_method_n_route_or_err_out($arg2);
    }
} else {
    if ($folderType === "middlewares" || $folderType === "routes") {
        cli_info_without_exit("No Method/Route provided! Created File and/or Function inside of `/funkphp/routes` OR `funkphp/middlewares` will NOT be attached to a Method/Route!");
    }
}

// $folderType "validation" & "sql" demands $arg2 OR $arg3 to be a string of "tables"
if ($folderType === 'validation' || $folderType === 'sql') {
    if (
        isset($arg2) && is_string($arg2) && !empty($arg2) && preg_match($tableRegex, $arg2)
    ) {
        $tablesProvided = $arg2;
    } elseif (
        isset($arg3) && is_string($arg3) && !empty($arg3) && preg_match($tableRegex, $arg3)
    ) {
        $tablesProvided = $arg3;
    } else {
        cli_err_syntax_without_exit("Provide Valid Table Name(s) as second (\"arg2\") or third (\"arg3\") argument for `$folderType` Folder Type!");
        cli_info_without_exit("(For SQL) Use either Format:`sd|si|s|i|u|d=table1` for Single Table and its SQL Query Type OR:`s=table1,table2,etc3` for Multiple Tables for same SQL Query Type!");
        cli_info_without_exit("(For VALIDATION) Use either Format:`table1` for Single Table OR:`table1,table2,etc3` for Multiple Tables to Prepare Default Validation for!");
        cli_info_without_exit("(For SQL) sd|si|s|i|u|d: `sd`=SELECT_DISTINCT, `si`=SELECT_INTO, `s`=SELECT, `i`=INSERT, `u`=UPDATE, and `d`=DELETE");
        cli_info_without_exit("(For SQL) For example: `s=authors` for SELECT on Table `authors` assuming it exists. At least one Existing Table is ALWAYS required in current version of FunkPHP!");
        cli_info("Regex Used For SQL+Validation Tables: `/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i`!");
    }
}
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_folder_and_php_file_status($folder, $file);
var_dump("File: " . $file, "Fn: " . $fn, "Method: " . $method, "Route: " . $route, "Folder: " . $folder, "RouteKey: ", $routeKey, "StatusArray:", $statusArray);

// NO METHOD & ROUTE WAS PROVIDED - This is automatically
// forced when making/creating "pipeline"/"sql"/"validation"
// but it can also be manually set to null (not provided)
$createStatus = null;
if (!$method && !$route) {
    if ($folderType === 'pipeline') {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_anonymous_file", $file, null, "pipeline");
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created Single Anonymous Function File `$file.php` in Folder `$folder`!");
            cli_info("The Pipeline File `$file.php` is now ready to be used in your Pipeline - place it first either in `funkphp/pipeline/request/` OR `funkphp/pipeline/post-response/` and then add it to the corresponding Pipeline Sub-Array! (`pipeline['request']` or `pipeline['post-response']`. For example `pipeline['request'][{NEXT_ARRAY_INDEX}]['$file' => null]` where null could be any other data that is allowed to be passed to that Pipeline Function!");
        } else {
            cli_err("FAILED to Create Single Anonymous Function File `$file.php` in Folder `$folder`!");
        }
    } elseif ($folderType === 'middlewares') {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_anonymous_file", $file, null, "middlewares");
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created Single Anonymous Function File `$file.php` in Folder `$folder`!");
            cli_info_without_exit("The Middlewares File `$file.php` is now ready to be used in your Routes. You did not provide a Method/Route so it will not be automatically added to any Route. You can add it manually later on. Remember that it MUST be the first Route Key in the Route's Array of Numbered Route Keys. For example: `'GET' => [ '/example' => [ 0 => ['middlewares' => [0 => ['mw_filename_without_php_extension' => null]]], 1 => ['callback' => 'file=>function'], ], ],` where null is the optional passed data that can be passed to the Middleware Function!");
            if (JSON_MODE) {
                cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:middlewares\", \"arg1\": \"$file\", \"arg2\": \"<method/route>\" }`");
            } else {
                cli_info("Add it manually in the CLI by typing `funk add:middlewares $file <method/route>`!");
            }
        } else {
            cli_err("FAILED to Create Single Anonymous Function File `$file.php` in Folder `$folder`!");
        }
    } elseif ($folderType === 'routes') {
        // If folder path does not exist, we attempt creating it which also means
        // that file and its fn does NOT exist so we will pass crudType "create_new_file_and_fn"
        if (!$statusArray['folder_path']) {
            if (!mkdir(PROJECT_DIR . '/' . $folder, 0755, true)) {
                cli_err("FAILED to Create Folder `$folder`! Verify File Permissions and try again.");
            }
            $statusArray = cli_folder_and_php_file_status($folder, $file);
            $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes");
            if ($createStatus) {
                cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$folder`!");
                cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes. You did not provide a Method/Route so it will not be automatically added to any Route. You can add it manually later on!");
                if (JSON_MODE) {
                    cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:$firstParam\", \"arg1\": \"$file=>$fn\", \"arg2\": \"<method/route>\" }`");
                } else {
                    cli_info("Add it manually in the CLI by typing `funk add:$firstParam $file=>$fn <method/route>`!");
                }
            } else {
                cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$folder`!");
            }
        }  // Here crudType is "create_only_new_fn_in_file" unless the file does not exist
        else {
            // File does not exist meaning crudType "create_new_file_and_fn"
            if (!$statusArray['file_exists']) {
                $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes");
                if ($createStatus) {
                    cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$folder`!");
                    cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes. You did not provide a Method/Route so it will not be automatically added to any Route. You can add it manually later on!");
                    if (JSON_MODE) {
                        cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:$firstParam\", \"arg1\": \"$file=>$fn\", \"arg2\": \"<method/route>\" }`");
                    } else {
                        cli_info("Add it manually in the CLI by typing `funk add:$firstParam $file=>$fn <method/route>`!");
                    }
                } else {
                    cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$folder`!");
                }
            }
            // crudType is now "create_only_new_fn_in_file" since file exists
            // unless function already exists in the file!
            else {
                // Function already exists in the file
                if (isset($statusArray['functions'][$fn])) {
                    cli_err_without_exit("Function `$fn` already exists in File `$file.php` in Folder `$folder`!");
                    cli_info("Change File and/or Function Name and try again for `$folder`!");
                }
                // Function does not exist in the file so
                // crudType "create_only_new_fn_in_file"
                else {
                    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "routes");
                    if ($createStatus) {
                        cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in Folder `$folder`!");
                        cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes. You did not provide a Method/Route so it will not be automatically added to any Route. You can add it manually later on!");
                        if (JSON_MODE) {
                            cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:$firstParam\", \"arg1\": \"$file=>$fn\", \"arg2\": \"<method/route>\" }`");
                        } else {
                            cli_info("Add it manually in the CLI by typing `funk add:$firstParam $file=>$fn <method/route>`!");
                        }
                    } else {
                        cli_err("FAILED to Create Function `$fn` in File `$file.php` in Folder `$folder`!");
                    }
                }
            }
        }
    } elseif ($folderType === 'validation' || $folderType === 'sql') {
        if (!$statusArray['folder_path']) {
            cli_err_without_exit("This Folder SHOULD ALWAYS EXIST due to the nature of FunkPHP and its FunkCLI which auto-generates Default Folder on each Command!!");
            cli_info("Verify File Permissions for Subfolders in `funkphp/` and try again since this Folder should be recreated each time your run a Command to the FunkCLI!");
        }
        // Create new file when it does not exist
        if (!$statusArray['file_exists']) {
            $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, $folderType, null, $tablesProvided);
            if ($createStatus) {
                cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in `funkphp/$folderType`!");
                cli_info_without_exit("The $folderType File `$file.php` is now ready to be used in `funkphp/$folderType`.");
                if ($folderType === 'sql') {
                    cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_load_sql(&\$c, '$file', '$fn')` and then `funk_use_sql(&\$c, \$loadedSQLArray, \$optionalInputData, \$hydrateAfterQuery)`!");
                } elseif ($folderType === 'validation') {
                    cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_use_validation(&\$c, '$file', '$fn')`!");
                }
                // IMPOSSIBLE EDGE-CASE
                else {
                    cli_warning("Somehow you created a File in a Folder Type that is NOT supported! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
                }
            } else {
                cli_err("FAILED to Create File `$file.php` with Function `$fn` in `funkphp/$folderType`!");
            }
        }
        // Or try to create a new function in existing file
        else {
            // Function already exists in the file
            if (isset($statusArray['functions'][$fn])) {
                cli_err_without_exit("Function `$fn` already exists in File `$file.php` in `funkphp/$folderType`!");
                cli_info("Change File and/or Function Name and try again for `funkphp/$folderType`!");
            }
            // Function does not exist in the file so
            // crudType "create_only_new_fn_in_file"
            else {
                $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, $folderType, null, $tablesProvided);
                if ($createStatus) {
                    cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in `funkphp/$folderType`!");
                    cli_info_without_exit("The $folderType File `$file.php` is now ready to be used in `funkphp/$folderType`.");
                    if ($folderType === 'sql') {
                        cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_load_sql(&\$c, '$file', '$fn')` and then `funk_use_sql(&\$c, \$loadedSQLArray, \$optionalInputData, \$hydrateAfterQuery)`!");
                    } elseif ($folderType === 'validation') {
                        cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_use_validation(&\$c, '$file', '$fn')`!");
                    }
                    // IMPOSSIBLE EDGE-CASE
                    else {
                        cli_warning("Somehow you created a File in a Folder Type that is NOT supported! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
                    }
                } else {
                    cli_err("FAILED to Create Function `$fn` in File `$file.php` in `funkphp/$folderType`!");
                }
            }
        }
    }
    // Impossible edge-case
    else {
        cli_err("You are trying to create a File Name with a Function Name in a Folder Type that is NOT supported!");
    }
}
// A VALID METHOD & ROUTE WAS PROVIDED - This means we might need to add it to
// a matched Method/Route in the Routes File unless it already exists there!
// Anonymous Function "pipeline" Files does not apply, but "middlewares" does
elseif ($method && $route) {
    // Create a new Method and/or Route depending on if it exists or not
    $methodExistsInROUTES = false;
    $routeExistsInROUTES = false;
    if ($folderType === 'routes' || $folderType === 'middlewares') {
        if (!isset($arg2) || !is_string($arg2) || empty($arg2) || !preg_match($methodRouteRegex, $arg2)) {
            cli_err_without_exit("No Valid Method/Route provided when wanting to only Create a New Route! `($cmd:$firstParam)`");
            cli_info_without_exit("Syntax is: `funk make:r method/route` like `funk make:r g/users` OR `funk make:r post/users`!");
            cli_info("Notice the support for shorthand versions \"g\", \"po\", \"pu\", \"d\" OR \"del\", \"pa\" for GET, POST, PUT, DELETE and PATCH respectively!");
        }
        [$method, $route] =  cli_return_valid_method_n_route_or_err_out($arg2);
        // We first check if $method exists even in $ROUTES because if it does not then we also know route neither does!
        if (!array_key_exists($method, $ROUTES)) {
            $methodExistsInROUTES = false;
            $routeExistsInROUTES = false;
        } // Method exist, so check that Route exists in that Method, even dynamic ones
        else if (array_key_exists($method, $ROUTES)) {
            $methodExistsInROUTES = true;
            if (array_key_exists($route, $ROUTES[$method])) {
                cli_info_without_exit("Method/Route `$method$route` already exists in `funkphp/routes/routes.php`! Will NOT create it again, but will attempt adding Route Key to it if applicable!");
                $routeExistsInROUTES = true;
            } else {
                // Check against conflicting Dynamic Routes when they are on the same URI Segment level at the end
                // For example: `GET/users/:id` and `GET/users/:id2` because they are on the same level at the end
                // We error out because we do not want two conflicting dynamic routes on the same level at the end
                if (preg_match($routeDynamicEndRegex, $route)) {
                    $troute = $singleTroute;
                    $findDynamicRoute = cli_match_developer_route($method, $route, $troute, $ROUTES, $ROUTES);
                    if ($findDynamicRoute['route'] !== null) {
                        cli_err_without_exit("Found Dynamic Route \"{$findDynamicRoute['method']}{$findDynamicRoute['route']}\" in Trie Routes would conflict with \"$method$route\".");
                        cli_info("Run `php funk recompile|rc` to rebuild Trie Routes if You Manually Removed that Route from `funkphp/routes/routes.php` you want to add again!");
                    }
                }
                $routeExistsInROUTES = false;
            }
        }
        // Here we now know if Method and/or Route exists or not
        // CASE 1: Method does not exist, thus Route neither does
        if (!$methodExistsInROUTES) {
            $ROUTES[$method] = [];
            $ROUTES[$method][$route] = [];
            cli_info_without_exit("Added New Method and New Route to it... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to try again!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success_without_exit("Created New Valid Method `$method` in `funkphp/routes/routes.php`!");
            cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
        }
        // CASE 2: Method exists but not Route
        else if (!$routeExistsInROUTES) {
            $ROUTES[$method][$route] = [];
            cli_info_without_exit("Added New Route to it... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to try again!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success_without_exit("Created Method/Route `$method$route` in `funkphp/routes/routes.php`!");
        }
    }
    // Method + Route has now been created if they did not exist before (unless error before this)
    $routeFileAndKeyCreated = false;

    // NEW route/{subfolder}/file.php=>function for method/route (unless it already exists which is OK)
    if ($folderType === 'routes') {
        // If folder path does not exist, we attempt creating it which also means
        // that file and its fn does NOT exist so we will pass crudType "create_new_file_and_fn"
        if (!$statusArray['folder_path']) {
            if (!mkdir(PROJECT_DIR . '/' . $folder, 0755, true)) {
                cli_err("FAILED to Create Folder `$folder`! Verify File Permissions and try again.");
            }
            $statusArray = cli_folder_and_php_file_status($folder, $file);
            $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes");
            if ($createStatus) {
                cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$folder`. Adding `$fn=>$fn` as Route Key to Method/Route `$method$route` now...");
                $routeFileAndKeyCreated = true;
            } else {
                cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$folder`.  Method/Route `$method$route` was created but no Route Key was added to it!");
            }
        }  // Here crudType is "create_only_new_fn_in_file" unless the file does not exist
        else {
            // File does not exist meaning crudType "create_new_file_and_fn"
            if (!$statusArray['file_exists']) {
                $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes");
                if ($createStatus) {
                    cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$folder`. Adding `$fn=>$fn` as Route Key to Method/Route `$method$route` now...");
                    $routeFileAndKeyCreated = true;
                } else {
                    cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$folder`. Method/Route `$method$route` was created but no Route Key was added to it!");
                }
            }
            // crudType is now "create_only_new_fn_in_file" since file exists
            // unless function already exists in the file!
            else {
                // Function already exists in the file
                if (isset($statusArray['functions'][$fn])) {
                    cli_info_without_exit("Function `$fn` already exists in File `$file.php` in Folder `$folder`. Adding `$fn=>$fn` as Route Key to Method/Route `$method$route` now...");
                    $routeFileAndKeyCreated = true;
                }
                // Function does not exist in the file so
                // crudType "create_only_new_fn_in_file"
                else {
                    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "routes");
                    if ($createStatus) {
                        cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in Folder `$folder`. Adding `$fn=>$fn` as Route Key to Method/Route `$method$route` now...");
                        $routeFileAndKeyCreated = true;
                    } else {
                        cli_err("FAILED to Create Function `$fn` in File `$file.php` in Folder `$folder`.  Method/Route `$method$route` was created but no Route Key was added to it!");
                    }
                }
            }
        }

        // If File=>Fn failed (even if it existed already) then we error out, else we continue!
        if (!$routeFileAndKeyCreated) {
            cli_err("Could NOT add Route Key `$file=>$fn` to Method/Route `$method$route` since File and/or Function failed to be created. `$method$route` remains Created and Added to `Routes` though!");
        }

        // If the route key does not exist, add it
        // Else the route key already exists, inform the user
        if (!array_key_exists_in_list($routeKey, $ROUTES[$method][$route])) {
            $ROUTES[$method][$route][] = [$routeKey => [$file => [$fn => null]]];
            cli_info_without_exit("Added Route Key `$file=>$fn` to Method/Route `$method$route`... Attempting to rebuild the Trie & Route File Now...");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success("Successfully Added Route Key `$file=>$fn` to Method/Route `$method$route` in `funkphp/routes/routes.php`!");
        } else {
            cli_success("***NO ROUTE KEY ADDED!*** Routes Folder=>File=>Function `$routeKey=>$file=>$fn` already exists in Method/Route `$method$route` in `funkphp/routes/routes.php`! Nothing more to do!");
        }
    }

    // NEW middlewares/function_file.php for method/route (unless it already exists which is OK)
    elseif ($folderType === 'middlewares') {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_anonymous_file", $file, null, "middlewares");
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created Single Anonymous Function File `$file.php` in Folder `$folder`!");
            cli_info_without_exit("The Middlewares File `$file.php` is now ready to be used in your Routes. You did not provide a Method/Route so it will not be automatically added to any Route. You can add it manually later on!");
        } else {
            cli_err("FAILED to Create Single Anonymous Function File `$file.php` in Folder `$folder`. `$method$route` remains created and added to `Routes` though!");
        }
    }
    // UNEXECPTED AND IMPOSSIBLE EDGE-CASE
    else {
        cli_err("You are trying to create a File Name with a Function Name in a Folder Type that is NOT supported! (Only `Routes` and `Middlewares` support the additional Method/Route argument!)");
    }
}
// Impossible edge-case where either $method or $route
// is not set when both should be either set or not set
else {
    cli_err("Impossible Edge-Case where either \$method OR \$route is NOT SET when both either should be set or both should be null!");
}

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("Impossible Edge-Case where You are outside of the `make` Command when it should have been caught before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
