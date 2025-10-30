<?php // FunkCLI COMMAND "php funk make:route" - creates a new file and optionally adds a function to a specific Method/Route
// MAKE: Create something new and OPTIONALLY adding it to a specific Method/Route
// SYNTAX: funk make|create|new:<first_param> <file_name>[=>function_name] [method/route]
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

// 1. Find the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:route', 'method/route');
[$method, $route] = cli_extract_method_route($arg_methodRoute);

// 2. Find optional Folder/File/Function argument (e.g., "fff:usersFolder=>userFile=>FunctionInsideFile")
$arg_folderFileAndFn = cli_get_cli_input_from_interactive_or_regular($args, 'make:route', 'folder/file/fn');
if ($arg_folderFileAndFn) {
    [$folder, $file, $fn] =  cli_extract_folder_file_fn($arg_folderFileAndFn);
    $routeKey = [$folder => [$file => [$fn => null]]];
    $singleFolder = $folder;
    $folder = $folderBase . $folder . '/';
}

////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Creating a Route unless it already exists!
// If Method is not set, we create it even though it should actually exist
// and then we can also just add the Route to it and be done with it!
////////////////////////////////////////////////////////////////////////////
if (!array_key_exists($method, $ROUTES)) {
    $ROUTES[$method] = [];
    $ROUTES[$method][$route] = [];
    cli_info_without_exit("Added New Method and New Route to it... Attempting to rebuild the Trie & Route File Now... If it fails, the Route will NOT have been added and you will have to try again!");
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
    if (!cli_duplicate_folder_file_fn_route_key($ROUTES[$method][$route], $singleFolder, $file, $fn)) {
        cli_info_without_exit("The Provided Route Key `$singleFolder=>$file=>$fn` does NOT exists in `$method$route` in `funkphp/routes/routes.php`. Folder : `$singleFolder` | File: `$file` | Function: `$fn` will be created in that order unless already existing as folder, file and/or function, and then it will be added as the next Route Key to the Matched Route if everything went OK!");
    }
} else {
    cli_info_without_exit("`$method$route` has NO Route Keys yet.  Folder : `$folder` | File: `$file` | Function: `$fn` will be created in that order unless already existing as folder, file and/or function, and then it will be added as the first Route Key to the Matched Route if everything went OK!");
}
// We add the Route Key to the Method/Route now and we can use array_pop() later if anything failed
// meaning it should not exist as a Route Key any longer for the matched/created Method/Route!
$ROUTES[$method][$route][] = $routeKey;

// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_folder_and_php_file_status($folder, $file);
var_dump("File: " . $file, "Fn: " . $fn, "Method: " . $method, "Route: " . $route, "Folder: " . $folder, "RouteKey: ", $routeKey, "StatusArray:", $statusArray);

// If folder path does not exist, we attempt creating it which also means
// that file and its fn does NOT exist so we will pass crudType "create_new_file_and_fn"
if (!$statusArray['folder_path']) {
    if (!mkdir(PROJECT_DIR . '/' . $folder, 0755, true)) {
        array_pop($ROUTES[$method][$route]);
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_err("FAILED to Create Folder `$folder`! Verify File Permissions and try again. Creating Folder : `$singleFolder` | File: `$file` | Function: `$fn` did NOT complete for `$method$route`!");
    }
    $statusArray = cli_folder_and_php_file_status($folder, $file);
    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
    if ($createStatus) {
        cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
        cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes. You did not provide a Method/Route so it will not be automatically added to any Route. You can add it manually later on!");
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolderr=>$file=>$fn` added as its first or last Route Key!");
    } else {
        array_pop($ROUTES[$method][$route]);
        cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
        cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`. Creating Folder : `$singleFolder` | File: `$file` | Function: `$fn` did NOT complete for `$method$route`!");
    }
}  // Here crudType is "create_only_new_fn_in_file" unless the file does not exist
else {
    // File does not exist meaning crudType "create_new_file_and_fn"
    if (!$statusArray['file_exists']) {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "routes", $method . $route);
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created File `$file.php` with Function `$fn` in Folder `$singleFolder`!");
            cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
        } else {
            array_pop($ROUTES[$method][$route]);
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_err("FAILED to Create File `$file.php` with Function `$fn` in Folder `$singleFolder`.  Creating Folder : `$singleFolder` | File: `$file` | Function: `$fn` did NOT complete for `$method$route`!");
        }
    }
    // crudType is now "create_only_new_fn_in_file" since file exists
    // unless function already exists in the file!
    else {
        // Function already exists in the file
        if (isset($statusArray['functions'][$fn])) {
            cli_info_without_exit("Function `$fn` already exists in File `$file.php` in Folder `$singleFolder`!");
            cli_info_without_exit("The Route File `$file.php` can be used in your Routes other than just `$method$route` where it has already been added to!");
            cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
            cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
        }
        // Function does not exist in the file so
        // crudType "create_only_new_fn_in_file"
        else {
            $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "routes", $method . $route);
            if ($createStatus) {
                cli_success_without_exit("SUCCESSFULLY Created Function `$fn` in File `$file.php` in Folder `$singleFolder`!");
                cli_info_without_exit("The Route File `$file.php` is now ready to be used in your Routes other than just `$method$route` where it has already been added to!");
                cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
                cli_success("`$method$route` was SUCCESSFULLY created/used in `funkphp/routes/routes.php` with the Folder=>File=>Function `$singleFolder=>$file=>$fn` added as its first or last Route Key!");
            } else {
                array_pop($ROUTES[$method][$route]);
                cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
                cli_err("FAILED to Create Function `$fn` in File `$file.php` in Folder `$singleFolder`.  Creating Folder : `$singleFolder` | File: `$file` | Function: `$fn` did NOT complete for `$method$route`!");
            }
        }
    }
}
















exit;
// NO METHOD & ROUTE WAS PROVIDED - This is automatically
// forced when making/creating "pipeline"/"sql"/"validation"
// but it can also be manually set to null (not provided)


exit;
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
                    cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:$subCommand\", \"arg1\": \"$file=>$fn\", \"arg2\": \"<method/route>\" }`");
                } else {
                    cli_info("Add it manually in the CLI by typing `funk add:$subCommand $file=>$fn <method/route>`!");
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
                        cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:$subCommand\", \"arg1\": \"$file=>$fn\", \"arg2\": \"<method/route>\" }`");
                    } else {
                        cli_info("Add it manually in the CLI by typing `funk add:$subCommand $file=>$fn <method/route>`!");
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
                            cli_info("Add it manually with the following JSON Syntax: `{ \"command\": \"add:$subCommand\", \"arg1\": \"$file=>$fn\", \"arg2\": \"<method/route>\" }`");
                        } else {
                            cli_info("Add it manually in the CLI by typing `funk add:$subCommand $file=>$fn <method/route>`!");
                        }
                    } else {
                        cli_err("FAILED to Create Function `$fn` in File `$file.php` in Folder `$folder`!");
                    }
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
            cli_err_without_exit("No Valid Method/Route provided when wanting to only Create a New Route! `($cmd:$subCommand)`");
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
