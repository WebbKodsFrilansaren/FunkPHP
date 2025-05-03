<?php

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function cli_match_compiled_route(string $requestUri, array $methodRootNode): ?array
{
    // Prepare & and extract URI Segments and remove empty segments
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);

    // Prepare variables to store the current node,
    // matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = [];
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;

    // EDGE-CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }

    // Iterate URI segments when more than 0
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        /// First try match "|" middleware node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }

        // Then try match literal route
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments[] = $currentUriSegment;
            $currentNode = $currentNode[$currentUriSegment];
            $segmentsConsumed++;
            continue;
        }

        // Or try match dynamic route ":" indicator node and
        // only store param and matched URI segment if not null
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']);

            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedParams[$placeholderKey] = $currentUriSegment;
                $matchedPathSegments[] = ":" . $placeholderKey;
                $currentNode = $currentNode[':'][$placeholderKey];
                $segmentsConsumed++;
                continue;
            }
        }

        // No matched "|", ":" or literal route in Compiled Routes!
        return null;
    }

    // EDGE-CASE: Add middleware at last node if it exists
    if (isset($currentNode['|'])) {
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
    }

    // Return matched route, params & middlewares
    // if all consumed segments matched
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments)) {
            return ["route" => '/' . implode('/', $matchedPathSegments), "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
        }
        // EDGE-CASE: 0 consumed segments,
        // return null instead of matched
        else {
            return null;
        }
    }
    // EDGE-CASES: Return null when impossible(?)/unexpected behavior
    else {
        return null;
    }
    return null;
}

// TRIE ROUTER STARTING POINT: Match Returned Matched Compiled Route With Developer's Defined Route
function cli_match_developer_route(string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $handlerKey = "handler", string $mHandlerKey = "middlewares")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = cli_match_compiled_route($uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = "COMPILED_ROUTE_KEY (" . mb_strtoupper($method) . ") & ";
    }

    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $matchedRouteHandler = $routeInfo[$handlerKey] ?? null;
            $noMatchIn = "ROUTE_MATCHED_BOTH";
        } else {
            $noMatchIn .= "DEVELOPER_ROUTES(route_single_routes.php)";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES(troute_route.php)";
    }
    return [
        "method" => $method,
        "route" => $matchedRoute,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// Restore essentially the "funkphp" folder and all its subfolders if they do not exist!
function cli_restore_default_folders_and_files()
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }

    // Prepare what folders to loop through and create if they don't exist!
    $folderBase = dirname(dirname(__DIR__));
    $folders = [
        "$folderBase",
        "$folderBase/_BACKUPS/",
        "$folderBase/_BACKUPS/_FINAL_BACKUPS/",
        "$folderBase/_BACKUPS/compiled/",
        "$folderBase/_BACKUPS/data/",
        "$folderBase/_BACKUPS/handlers/",
        "$folderBase/_BACKUPS/middlewares/",
        "$folderBase/_BACKUPS/pages/",
        "$folderBase/_BACKUPS/routes/",
        "$folderBase/_BACKUPS/sql/",
        "$folderBase/_BACKUPS/templates/",
        "$folderBase/_internals/",
        "$folderBase/_internals/compiled/",
        "$folderBase/_internals/functions/",
        "$folderBase/_internals/templates/",
        "$folderBase/cached/",
        "$folderBase/cached/pages/",
        "$folderBase/cached/json/",
        "$folderBase/cached/files/",
        "$folderBase/config/",
        "$folderBase/data/",
        "$folderBase/_dx_steps/",
        "$folderBase/middlewares/",
        "$folderBase/pages/",
        "$folderBase/pages/complete/",
        "$folderBase/pages/parts/",
        "$folderBase/routes/",
        "$folderBase/tests/",
        "$folderBase/templates/",
        "$folderBase/sql/",
    ];

    // Prepare default files that doesn't exist if certain folders don't exist
    $defaultFiles = [
        "$folderBase/_internals/compiled/troute_route.php",
        "$folderBase/routes/route_single_routes.php",
    ];

    // Create folderBase if it does not exist
    if (!is_dir($folderBase)) {
        mkdir($folderBase, 0777, true);
    }
    // Loop through each folder and create it if it does not exist
    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
            echo "\033[32m[FunkCLI - SUCCESS]: Recreated folder: $folder\n\033[0m";
        }
    }
    // Loop through files, and create them if they don't exist
    foreach ($defaultFiles as $file) {
        if (!file_exists($file)) {
            // Recreate default files based on type ("troute", "middleware routes" or "single routes")
            if (str_contains($file, "troute")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [];\n?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } elseif (str_contains($file, "single")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [\n'ROUTES' => \n['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [],]];\n?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            }
        }
    }
}

// Rebuilds the Single Routes Route file (funkphp/routes/route_single_routes.php) based on valid array
function cli_rebuild_single_routes_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/routes/route_single_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['ROUTES'])) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/routes/route_single_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['routes']) || !is_writable($dirs['routes'])) {
        cli_err("[cli_rebuild_single_routes_file] Routes directory (funkphp/routes/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_routes']) && !is_writable($exactFiles['single_routes'])) {
        cli_err("[cli_rebuild_single_routes_file] Routes file (funkphp/routes/route_single_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_routes'],
        cli_get_prefix_code("route_singles_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Check if Routes Data Handler in handlers/D/ exists
function cli_data_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_d_handler_exists] Data Handler File name must be a non-empty string!");
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in handlers/D/ folder, false otherwise
    if (file_exists($dirs['data'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Routes Page Handler in handlers/P/ exists
function cli_page_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_p_handler_exists] Page Handler File name must be a non-empty string!");
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in handlers/P/ folder, false otherwise
    if (file_exists($dirs['pages'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Routes Route Handler in handlers/R/ exists
function cli_handler_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_r_handler_exists] Route Handler File name must be a non-empty string!");
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in handlers/R/ folder, false otherwise
    if (file_exists($dirs['handlers'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Route Middleware Handler in middlewares/R/ exists
function cli_middleware_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_mw_r_handler_exists] Middleware Route Handler File name must be a non-empty string!");
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in middlewares/R/ folder, false otherwise
    if (file_exists($dirs['middlewares'] . $fileName)) {
        return true;
    }
    return false;
}

// Build Compiled Route from Developer's Defined Routes
function cli_build_compiled_routes(array $developerSingleRoutes, array $developerMiddlewareRoutes)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    // Only localhost can run this function (meaning you cannot run this in production!)
    // Both arrays must be non-empty arrays
    if (!is_array($developerSingleRoutes)) {
        echo "[ERROR]: '\$developerSingleRoutes' Must be a non-empty array!\n";
        exit;
    } elseif (!is_array($developerMiddlewareRoutes)) {
        echo "[ERROR]: '\$developerMiddlewareRoutes' Must be a non-empty array!\n";
        exit;
    }
    if (empty($developerSingleRoutes)) {
        echo "[ERROR]: Must '\$developerSingleRoutes' be a non-empty array!\n";
        exit;
    } else if (empty($developerMiddlewareRoutes)) {
        echo "[ERROR]: Must '\$developerMiddlewareRoutes' be a non-empty array!\n";
        exit;
    }

    // Prepare compiled route array to return and other variables
    $compiledTrie = [];
    $GETSingles = $developerSingleRoutes["GET"] ?? [];
    $POSTSingles = $developerSingleRoutes["POST"] ?? [];
    $PUTSingles = $developerSingleRoutes["PUT"] ?? [];
    $DELETESingles = $developerSingleRoutes["DELETE"] ?? [];
    $PATCHSingles = $developerSingleRoutes["PATCH"] ?? [];

    // Using method below, iterate through each HttpMethod and then add it to the $compiledTrie array
    $addMethods = function ($singleRoutes) {
        // Begin with just getting the key names and no other nested values inside of them:
        // For example:  '/users' => ['handler' => 'USERS_PAGE', /*...*/], only gets the '/users' key name
        // and not the value inside of it. This is done by using array_keys() to get the keys of the array.
        $keys = array_keys($singleRoutes) ?? [];
        $compiledTrie = [];

        // Iterate through each key in the array and add it to the $compiledTrie array
        foreach ($keys as $key) {

            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/") {
                $compiledTrie["/"] = [];
                continue;
            }

            // Split the route into segments
            $splitRouteSegments = explode("/", trim($key, "/"));

            // Initialize the current node in the trie
            $currentNode = &$compiledTrie;

            // Iterate through each segment of the route
            foreach ($splitRouteSegments as $segment) {
                // WHEN DYNAMIC PARAMETER ROUTE SEGMENT
                if (str_starts_with($segment, ":")) {
                    // Create when not exist
                    if (!isset($currentNode[':'])) {
                        $currentNode[':'] = [];
                    }
                    // And insert param as next nested key and/or move to next node
                    $paramName = substr($segment, 1);
                    if (!isset($currentNode[':'][$paramName])) {
                        $currentNode[':'][$paramName] = [];
                    }
                    $currentNode = &$currentNode[':'][$paramName];
                }
                // WHEN LITERAL ROUTE SEGMENT
                else {
                    // Insert if not exist and/or move to next node
                    if (!isset($currentNode[$segment])) {
                        $currentNode[$segment] = [];
                    }
                    $currentNode = &$currentNode[$segment];
                }
            }
        }
        // Return the compiled trie for the method
        return $compiledTrie;
    };

    // Add the middleware routes to the compiled trie
    $addMiddlewareRoutes = function ($middlewareRoutes, &$compiledTrie) {
        // Only extract the keys from the middleware routes
        //$keys = array_keys($middlewareRoutes) ?? [];
        $keys = $middlewareRoutes ?? [];


        // The way we insert "|" to signify a middleware is to just go through all segments for each key
        // and when we are at the last segment that is the node we insert "|" and then we move on to key.
        foreach ($keys as $key => $value) {
            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/" && isset($value['middlewares']) && !empty($value['middlewares'])) {
                $compiledTrie["|"] = [];
                continue;
            }

            // Now split key into segments and iterate through each segment
            $splitRouteSegments = explode("/", trim($key, "/"));

            // Now we just navigate to the last segment and add the middleware node "|".
            // We just check what it is and then just navigate,
            $currentNode = &$compiledTrie;

            // So we just check one of three things: is there a literal route to navigate to?
            // is there a dynamic route to navigate to? or is it a middleware node? WE JUST NAVIGATE TO IT
            // until we run out of segments, that means we have reached the node where we insert the middleware node "|".
            foreach ($splitRouteSegments as $segment) {
                // SPECIAL CASE: Navigate past any middleware node "|" but not at root node!
                if (isset($currentNode['|']) && !empty($currentNode['|'])) {
                    $currentNode = &$currentNode['|'];
                }

                // LITERAL ROUTE SEGMENT
                if (isset($currentNode[$segment])) {
                    $currentNode = &$currentNode[$segment];
                    continue;
                }

                // DYNAMIC ROUTE SEGMENT
                elseif (str_starts_with($segment, ":")) {
                    $paramName = substr($segment, 1);
                    $currentNode = &$currentNode[':'][$paramName];
                    continue;
                }
            }

            // Now we are at the last segment, we just add the middleware node "|"
            // and then we add the middleware route to it.
            if (!isset($currentNode['|']) && isset($value['middlewares']) && !empty($value['middlewares'])) {
                $currentNode['|'] = [];
            }
        }
    };

    // First add the single routes to the compiled trie
    $compiledTrie['GET'] = $addMethods($GETSingles);
    $compiledTrie['POST'] = $addMethods($POSTSingles);
    $compiledTrie['PUT'] = $addMethods($PUTSingles);
    $compiledTrie['DELETE'] = $addMethods($DELETESingles);
    $compiledTrie['PATCH'] = $addMethods($PATCHSingles);

    // Then add the middlewares to the compiled trie and return it
    $addMiddlewareRoutes($developerMiddlewareRoutes["GET"] ?? [], $compiledTrie['GET']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["POST"] ?? [], $compiledTrie['POST']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["PUT"] ?? [], $compiledTrie['PUT']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["DELETE"] ?? [], $compiledTrie['DELETE']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["PATCH"] ?? [], $compiledTrie['PATCH']);

    return $compiledTrie;
}

// Output Compiled Route to File or Return as String
function cli_output_compiled_routes(array $compiledTrie, string $outputFileNameFolderIsAlways_compiled_routes = "null")
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    // Check if the compiled route is empty
    if (!is_array($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }
    if (empty($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }

    // Output either to file destiation or in current folder as datetime in file name
    $datetime = date("Y-m-d_H-i-s");
    $outputDestination = $outputFileNameFolderIsAlways_compiled_routes === "null" ? dirname(__DIR__) . "/compiled/troute_" . $datetime . ".php" : dirname(__DIR__) . "/compiled/" . $outputFileNameFolderIsAlways_compiled_routes . ".php";

    $result = null;
    if ($outputFileNameFolderIsAlways_compiled_routes !== "null") {
        $result = file_put_contents(dirname(__DIR__) . "/compiled/" . $outputFileNameFolderIsAlways_compiled_routes . ".php", "<?php\nreturn " . cli_convert_array_to_simple_syntax($compiledTrie));
    } else {
        $result = file_put_contents($outputDestination, "<?php\nreturn " . cli_convert_array_to_simple_syntax($compiledTrie));
    }
    if ($result === false) {
        echo "\033[31m[FunkCLI - ERROR]: FAILED to Recompile Trie Route: \"funkphp/_internals/compiled/troute_route.php\"!\n\033[0m";
    } else {
        echo "\033[32m[FunkCLI - SUCCESS]: Recompiled Trie Route: \"funkphp/_internals/compiled/troute_route.php\"!\n\033[0m";
    }
}

// Convert PHP array() syntax to simplified [] syntax
function cli_convert_array_to_simple_syntax(array $array): string | null | array
{
    // Must be non-empty array
    if (!is_array($array)) {
        cli_err_syntax("Array must be a non-empty array!");
        exit;
    }

    // Check if the array is empty
    if (empty($array)) {
        cli_err_syntax("Array must be a non-empty array!");
        exit;
    }

    // Prepare array and parse state variables
    $str = mb_str_split(var_export($array, true));
    $arrStack = [];
    $arrayLetters = ["a", "r", "r", "a", "y", " "];
    $quotes = ["'", '"'];
    $inStr = false;
    $converted = "";

    // Check if first character is "a"
    if ($str[0] !== "a") {
        echo "[ERROR]: Must be a non-empty array!\n";
        exit;
    }

    // Parse on each character of the prepared string
    for ($i = 0; $i < count($str); $i++) {
        $c = $str[$i];

        // If inside string and is not a quote
        if ($inStr && (!in_array($c, $quotes) && $c !== "\\")) {
            $converted .= $c;
            continue;
        }
        // If inside string with escaped character, just skip it
        elseif ($inStr && ($c === "\\")) {
            $i++;
            continue;
        }
        // If inside string and is a quote
        elseif ($inStr && (in_array($c, $quotes))) {
            $converted .= $c;
            $inStr = false;
            continue;
        }

        // If not inside string and is a quote
        if (!$inStr && empty($arrStack) && (in_array($c, $quotes))) {
            $inStr = true;
            $converted .= $c;
            continue;
        }

        // If not inside string and next character is "a" from "array (" & not from false boolean
        if (!$inStr && empty($arrStack)  && $c === "a" && $str[$i + 1] !== "l") {
            $arrStack[] = $c;
            continue;
        }

        // If not inside string and next character is one from:"rray ("
        if (!$inStr && !empty($arrStack)) {
            if (count($arrStack) < 5 && in_array($c, $arrayLetters)) {
                $arrStack[] = $c;
                continue;

                // If not inside string and next character is "(" from "array ("
            } elseif (count($arrStack) === 5 && $c === "(") {
                $converted .= "[";
                unset($arrStack);
                continue;
            }
        }

        // If outside string and ")"
        if (!$inStr && $c === ")") {
            $converted .= "]";
            continue;
        }
        $converted .= $c;
    }

    // Return the finalized string varaible
    $converted .= ";";
    return $converted;
}

// Output file until success (by waiting one second and retrying with new file name that is the file name + new datetime and extension    )
function cli_output_file_until_success($outputPathWithoutExtension, $extension, $outputData, $customSuccessMessage = "")
{
    // First check not empty strings
    if (
        !is_string($outputPathWithoutExtension) ||  !is_string($extension) || !is_string($outputData)
        || $outputPathWithoutExtension === "" || $extension === "" || $outputData === ""
    ) {
        cli_err_syntax("Output path, extension and data must be non-empty strings!");
    }

    // Check extension is valid (starting with ".") and ending with only characters
    if (!str_starts_with($extension, ".")) {
        cli_err_syntax("Output extension must start with '.' and only contain characters!");
    }

    // Check preg_match for extension which is (.[a-zA-Z0-9-_]+$)
    if (!preg_match("/\.[a-zA-Z0-9-_]+$/", $extension)) {
        cli_err_syntax("Output extension must start with '.' and only contain characters (a-zA-Z0-9-_)!");
    }

    // Check that output path exists (each folder in the path must exist)
    $outputPath = dirname($outputPathWithoutExtension);
    if (!is_dir($outputPath)) {
        cli_err_syntax("Output path must be a valid directory. Path: $outputPath is not!");
    }
    if (!is_writable($outputPath)) {
        cli_err_syntax("Output path must be writable! Path: $outputPath is not!");
    }

    // Now create first datetime string and file name and try to write it (by checking if that exact output file path exists)
    // If it exists, we wait one second and try again with new datetime string and file name
    $datetime = date("Y-m-d_H-i-s");
    $success = false;
    $outputFilePath = $outputPathWithoutExtension . "_" . $datetime . $extension;
    while (!$success) {
        if (file_exists($outputFilePath)) {
            cli_info_without_exit("Output file already exists: $outputFilePath! Trying again in 1 second...");
            sleep(1);
            $datetime = date("Y-m-d_H-i-s");
            $outputFilePath = $outputPathWithoutExtension . "_" . $datetime . $extension;
        } else {
            // Try to write the file
            $result = file_put_contents($outputFilePath, $outputData);
            if ($result === false) {
                cli_err("Output file failed to write: $outputFilePath!");
            } else {
                if ($customSuccessMessage !== "") {
                    cli_success_without_exit($customSuccessMessage);
                    $success = true;
                } else {
                    cli_success_without_exit("Output file written successfully: $outputFilePath!");
                    $success = true;
                }
            }
        }
    }
}

// Backup batch of files based on the array of files (string values) to backup
// Function uses "cli_backup_file_until_success"!
function cli_backup_batch($arrayOfFilesToBackup)
{
    // Check if the array is a non-empty array
    if (!is_array($arrayOfFilesToBackup) || empty($arrayOfFilesToBackup)) {
        cli_err_syntax("Array of files to backup must be a non-empty array!");
    }

    // Load $dirs, $exactFiles as globals
    global $dirs, $exactFiles, $settings;

    // Prepare paths for all possible that could be backed up
    // Backup paths
    $backupFinalsPath = $dirs['backups_finals'];
    $backupCompiledPath = $dirs['backups_compiled'];
    $backupRouteRoutePath = $dirs['backups_routes'];
    $backupDataRoutePath = $dirs['backups_data'];
    $backupPageRoutePath = $dirs['backups_pages'];

    // Single Route Routes (including Middlewares)
    $oldTrouteRouteFile = $exactFiles['troute_route'];
    $oldSingleRouteRouteFile = $exactFiles['single_routes'];

    // Now backup the old route files based on provided $filesString
    // Loop through each file in the array and backup it
    foreach ($arrayOfFilesToBackup as $fileString) {
        if ($fileString === "troutes") {
            // Routes
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_BACKUPS']) {
                cli_backup_file_until_success($backupCompiledPath . "troute_route", ".php", $oldTrouteRouteFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "troute_route", ".php", $oldTrouteRouteFile);
            }
            continue;
        }
        if ($fileString === "routes") {
            // Single Route Routes & Middlewares
            if ($settings['ALWAYS_BACKUP_IN']['ROUTES_IN_BACKUPS']) {
                cli_backup_file_until_success($backupRouteRoutePath . "route_single_routes", ".php", $oldSingleRouteRouteFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['ROUTES_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "route_single_routes", ".php", $oldSingleRouteRouteFile);
            }
            continue;
        }
        if ($fileString === "data") {
            if ($settings['ALWAYS_BACKUP_IN']['DATA_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['DATA_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "pages") {
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "handlers") {
            if ($settings['ALWAYS_BACKUP_IN']['HANDLERS_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['HANDLERS_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "middlewares") {
            if ($settings['ALWAYS_BACKUP_IN']['MIDDLEWARES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['MIDDLEWARES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "templates") {
            if ($settings['ALWAYS_BACKUP_IN']['TEMPLATES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['TEMPLATES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "sql") {
            if ($settings['ALWAYS_BACKUP_IN']['SQL_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['SQL_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "tests") {
            if ($settings['ALWAYS_BACKUP_IN']['TESTS_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['TESTS_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "config") {
            if ($settings['ALWAYS_BACKUP_IN']['CONFIG_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['CONFIG_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "cached") {
            if ($settings['ALWAYS_BACKUP_IN']['CACHED_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['CACHED_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
    }
}

// Delete a Single Route from the Route file (funkphp/routes/route_single_routes.php)
// and delete its associated Handler Function (and Handler File if last function)
function cli_delete_a_route()
{
    // Load globals and validate input
    global
        $argv, $dirs, $exactFiles,
        $settings,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax_without_exit("Provide a valid Route to delete from the Route file!\nExample: \"php funkcli delete [route|r] [method/route_name]\"");
        cli_info("IMPORTANT: Its associated Handler Function (and Handler File if last function) will be deleted as well!\n");
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $deleteRoute = trim(strtolower($argv[3]));
    $oldRoute = $deleteRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($deleteRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that provided route exists
    if (
        isset($singleRoutesRoute['ROUTES'][$method][$validRoute])
    ) {
        // First backup all associated route files if settings allow it
        cli_backup_batch(
            [
                "troutes",
                "routes",
            ]
        );
        // Store handler variable
        $handler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['handler'] ?? "<Handler missing?>";
        // Then we unset() each matched route
        unset($singleRoutesRoute['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Single Route \"$method$validRoute\" from the Route file!");

        // Then we rebuild and recompile Routes
        cli_rebuild_single_routes_route_file($singleRoutesRoute);
        $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
        cli_output_compiled_routes($compiledRouteRoutes, "troute_route");

        // Send the handler variable to delete it (this will
        // also delete file if it's the last function in it!)
        cli_delete_a_handler_function_or_entire_file($handler);
    }
    // When one ore more is missing, we do not go ahead with deletion
    // since this function is meant to delete all three at once!
    else {
        cli_err("The Route: \"$method$validRoute\" does not exist. Another HTTP Method or was it deleted already?");
    }
}

// Delete a Handler Function or entire Handler File if it is the last function in it
function cli_delete_a_handler_function_or_entire_file($handlerVar)
{
    // Load globals and validate input
    global
        $argv, $dirs, $exactFiles,
        $settings;

    // $handlerVar must either be a string or an array with a single string value!
    if (!is_string($handlerVar) && !is_array($handlerVar)) {
        cli_err_syntax_without_exit("The Handler argument must be 1) One string or 2) One array with one string!");
        cli_err_syntax("Example: \"[HandlerFile|HandlerFile=>Function] (the variable structure, not as a string!)\"");
    }

    // If it is a string, check that it is valid and not empty
    if (is_string($handlerVar) && empty($handlerVar)) {
        cli_err_syntax_without_exit("\"$handlerVar\" must be a non-empty string!");
    }

    // Prepare what is the handler file, function name, and handlers folder
    $handlerFile = null;
    $fnName = null;
    $handlersFolder = $dirs['handlers'];

    // If it is a string, check for "=>" because this function is either called by deleting a route
    // or just by deleting a handler function directly meaning the handlerFile=>Function would be
    // passed as a string and not as an array with one string value in the case of deleting a route.
    if (is_string($handlerVar)) {
        if (strpos($handlerVar, '=>') !== false) {
            [$handlerFile, $fnName] = explode('=>', $handlerVar);
            $handlerFile = trim($handlerFile);
            $fnName = trim($fnName);
        } else {
            $handlerFile = $handlerVar;
            $fnName = $handlerFile;
        }
    } elseif (is_array($handlerVar)) {
        $handlerFile = key($handlerVar);
        $fnName = $handlerVar[$handlerFile];
    }

    // Check that the handler file and function name are not empty strings with invalid characters
    if (!preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" Handler File must be a lowercase string containing only letters, numbers and underscores!");
    }
    if (!preg_match('/^[a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" Function Name must be a lowercase string containing only letters, numbers and underscores!");
    }

    // We now check if the handler file exists in the handlers folder, add .php if not
    if (!file_exists($handlersFolder . $handlerFile . ".php")) {
        cli_err("Handler File \"$handlerFile\" not found in \"funkphp/handlers/\"!");
    }

    // We now read the file content and check for the delimiter function name
    // as such: "//NEVER_TOUCH_ANY_COMMENTS_START|END=$handlerFile". Both
    // must exist otherwise we cannot be certain it is a valid handler file.
    $fileContent = file_get_contents($handlersFolder . $handlerFile . ".php");
    if (
        strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName") === false
        || strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_END=$fnName") === false
    ) {
        cli_err("Function \"$fnName\" in Handler \"$handlerFile\" not found or invalid structure!");
    }

    // We now match the number of "//DELIMITER_HANDLER_FUNCTION_START" and "//DELIMITER_HANDLER_FUNCTION_END"
    // in order to know how many functions are in the file. If it is 1, we then check if it is the last function
    // and thus delete entire file. If it is more than 1, we just delete the function and leave the file intact.
    // We do this by using preg_match_all() to count the number of matches in the file content.
    $startMatches = preg_match_all("/\/\/DELIMITER_HANDLER_FUNCTION_START=/", $fileContent, $matchesStart);
    $endMatches = preg_match_all("/\/\/DELIMITER_HANDLER_FUNCTION_END=/", $fileContent, $matchesEnd);
    if ($startMatches === false || $endMatches === false) {
        cli_err("Failed to find the Functions in the Handler File \"$handlerFile\"!");
    }

    // If matches are uneven, the file structure is invalid and we cannot delete it
    if ($startMatches !== $endMatches) {
        cli_err("The Handler File \"$handlerFile\" has an invalid structure! Every \"//DELIMITER_HANDLER_FUNCTION_START=\" should have a matching \"//DELIMITER_HANDLER_FUNCTION_END=\"!");
    }

    // We now check if the number of matches is 1, meaning it is the last
    // function in the file and thus we delete the entire file. If it is
    // more than 1, we just delete the function and leave the file intact.
    if ($startMatches === 1 && $endMatches === 1) {
        // TODO: Add Backup Fn that backups the file before deleting!
        // Delete the entire file
        if (unlink($handlersFolder . $handlerFile . ".php")) {
            cli_success("Deleted Handler File \"handlers/$handlerFile.php\" and Function \"$fnName\"!");
        } else {
            cli_err("FAILED to delete Handler File \"handlers/$handlerFile.php\" and Function \"$fnName\"!");
        }
    }
    // Here we know we have more than 1 match and that we have same number of matches
    // We now wanna find: //DELIMITER_HANDLER_FUNCTION_START=$fnName and //DELIMITER_HANDLER_FUNCTION_END=$fnName
    // in order to find the starting position and ending position of the function in the file content so we can
    // just replace/delete that part of the file content and then write it back to the file.
    $startPos = strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName");
    $endPos = strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_END=$fnName") + mb_strlen("//DELIMITER_HANDLER_FUNCTION_END=$fnName") + 1;
    if ($startPos === false || $endPos === false) {
        cli_err("Failed to find the Function \"$fnName\" in the Handler File \"handlers/$handlerFile.php\"!");
    }
    // Start position should NOT be larger than end position!
    if ($startPos > $endPos) {
        cli_err("The Handler File \"handlers/$handlerFile.php\" has an invalid structure! The start position is larger than the end position for \"$fnName\"!");
    }
    // We now replace the function in the file content with an empty string and write it back to the file
    $fileContent = substr_replace($fileContent, "", $startPos, $endPos - $startPos);

    // We write back the file content to the file and check if it was successful
    if (file_put_contents($handlersFolder . $handlerFile . ".php", $fileContent) !== false) {
        cli_success("Deleted Function \"$fnName\" from Handler File \"handlers/$handlerFile.php\"!");
    } else {
        cli_err("FAILED to delete Function \"$fnName\" from Handler File \"handlers/$handlerFile.php\"!");
    }
}

// Add a handler to (funkphp/handlers/) WITHOUT adding to the Route file
function cli_add_handler()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute, $reserved_functions;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax("Should be at least three(3) non-empty string arguments!\nSyntax: php funkcli add handler [handlerFile[=>handleFunction]]\nExample: 'php funkcli add handler users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!\n");
    }

    $handlerFile = null;
    $fnName = null;
    $arrow = null;
    if (strpos($argv[3], '=>') !== false) {
        [$handlerFile, $fnName] = explode('=>', $argv[3]);
        $handlerFile = trim($handlerFile);
        $fnName = trim($fnName);
        $arrow = true;
    } else {
        $handlerFile = $argv[3];
        $fnName = null;
    }

    // Preg_match validate both (unless null) handler file and function name
    if ($handlerFile !== null && !preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" - Handler name must be a lowercased string containing only letters, numbers and underscores!");
    }
    if ($fnName !== null && !preg_match('/^[a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" - Function name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Check that both fnName and handlerFile are not reserved functions
    if ($fnName !== null && in_array($fnName, $reserved_functions)) {
        cli_err_syntax("\"{$fnName}\" - Function is a reserved function name!");
    }
    if ($handlerFile !== null && in_array($handlerFile, $reserved_functions)) {
        cli_err_syntax("\"{$handlerFile}\" - Handler is a reserved function name!");
    }

    // Function name is optional, so if not provided, we set it to the handler file name since
    // that is the default name for the function in the handler file when the file is created
    if ($fnName === null) {
        $fnName = $handlerFile;
    }
    cli_info_without_exit("Parsed Handler: \"funkphp/handlers/$handlerFile.php\" and Function: \"$fnName\"");

    // Prepare handlers folders
    $handlersDir = $dirs['handlers'];

    // Check first if the handler file exists in the handlers folder, add .php if not
    if (file_exists($handlersDir . $handlerFile . ".php")) {
        // Read the file content and check if the function name exists in the file
        $fileContent = file_get_contents($handlersDir . $handlerFile . ".php");
        if ($fnName !== null && strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName") !== false) {
            cli_err("Function \"$fnName\" in Handler \"funkphp/handlers/$handlerFile.php\" is already used!");
        }
        // This means handler file exists but function name is not used, so we can add it
        else {
            cli_info_without_exit("Handler \"funkphp/handlers/$handlerFile.php\" exists and Function \"$fnName\" is valid!");
            // We now check if we can find "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile" in the file
            // and if not that means either error or Developer is trying to break the file, so we exit
            if (strpos($fileContent, "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile") === false) {
                cli_err("Handler \"funkphp/handlers/$handlerFile.php\" is invalid. Could not find \"//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile\". Please do not be a jerk trying to break the file!");
            }
            // We found the comment, so we can add the function name to the file by replacing the comment with the function name and then the comment again!
            $fileContent = str_replace(
                "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile",
                "//DELIMITER_HANDLER_FUNCTION_START=$fnName\nfunction $fnName(&\$c) // <UNKNOWN_ROUTE>\n{\n\n};\n//DELIMITER_HANDLER_FUNCTION_END=$fnName\n\n//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile",
                $fileContent
            );
            if (file_put_contents($handlersDir . $handlerFile . ".php", $fileContent) !== false) {
                cli_success_without_exit("Added Function \"$fnName\" to Handler \"funkphp/handlers/$handlerFile.php\"!");
            } else {
                cli_err("FAILED to add Function \"$fnName\" to Handler \"funkphp/handlers/$handlerFile.php\". File permissions issue?");
            }
        }
    } // File does not exist, so we create it
    else {
        // Create the handler file with the function name and return a success message
        $outputHandlerRoute = file_put_contents(
            $handlersDir . $handlerFile . ".php",
            "<?php\n//Handler File - This runs after Middlewares have ran after matched Route!\n\n//DELIMITER_HANDLER_FUNCTION_START=$fnName\nfunction $fnName(&\$c) // <UNKOWN_ROUTE>\n{\n\n};\n//DELIMITER_HANDLER_FUNCTION_END=$fnName\n\n//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile\nreturn function (&\$c, \$handler = \"$fnName\") {\n\$handler(\$c);\n};\n//NEVER_TOUCH_ANY_COMMENTS_END=$handlerFile"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Added Handler \"funkphp/handlers/$handlerFile.php\" with Function \"$fnName\" in \"funkphp/handlers/$handlerFile.php\"!");
        } else {
            cli_err("FAILED to create Handler \"funkphp/handlers/$handlerFile.php\". File permissions issue?");
        }
    }
    // Warn that the handler file was created/updated but the route is unknown (so it is not added to the route file)
    cli_warning_without_exit("You have ONLY created/updated Handler File: \"$handlerFile\" with Function \"$fnName\"");
    cli_warning_without_exit("Its associated route for Function \"$fnName\" is \"<UNKNOWN_ROUTE>\". You must add it MANUALLY to the Route file!");
    cli_warning("IMPORTANT: Using \"php funkcli add route [method/route] [$handlerFile=>$fnName]\" to combine the Route with its this created/updated Handler File and/or Function will NOT work!");
}

// All-in-one function to Sort all keys in ROUTES, build Route file, recompile and output them!
function cli_sort_build_routes_compile_and_output($singleRoutesRootArray)
{
    // Validate input
    if (!is_array($singleRoutesRootArray) || empty($singleRoutesRootArray) || !isset($singleRoutesRootArray['ROUTES'])) {
        cli_err_syntax("The Routes Array must be a non-empty array starting with the ROUTES key!");
    }

    // Loop through each key below ROUTES and sort the keys
    // and values in the array by the key name (route name)
    foreach ($singleRoutesRootArray['ROUTES'] as $key => $value) {
        if (is_array($value)) {
            ksort($singleRoutesRootArray['ROUTES'][$key]);
        }
    }

    // First backup all associated route files if settings allow it
    cli_backup_batch(
        [
            "troutes",
            "routes",
        ]
    );

    // Then we rebuild and recompile Routes
    $rebuild = cli_rebuild_single_routes_route_file($singleRoutesRootArray);
    if ($rebuild) {
        cli_success_without_exit("Rebuilt Route file \"funkphp/routes/route_single_routes.php\"!");
    } else {
        cli_err("FAILED to rebuild Route file \"funkphp/routes/route_single_routes.php\". File permissions issue?");
    }
    $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRootArray['ROUTES'], $singleRoutesRootArray['ROUTES']);
    cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
}

// Add a Route to the Route file (funkphp/routes/) INCLUDING a [HandlerFile[=>Function]]
function cli_add_a_route()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute, $reserved_functions;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add route get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
    }

    // Check if "$argv[4]" contains "=>" and split it into
    // handler & function name or just use $handlerFile name.
    $handlerFile = null;
    $fnName = null;
    $arrow = null;
    if (strpos($argv[4], '=>') !== false) {
        [$handlerFile, $fnName] = explode('=>', $argv[4]);
        $handlerFile = trim($handlerFile);
        $fnName = trim($fnName);
        $arrow = true;
    } else {
        $handlerFile = $argv[4];
        $fnName = null;
    }

    // Preg_match validate both (unless null) handler file and function name
    if ($handlerFile !== null && !preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" - Handler name must be a lowercased string containing only letters, numbers and underscores!");
    }
    if ($fnName !== null && !preg_match('/^[a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" - Function name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Check that both fnName and handlerFile are not reserved functions
    if ($fnName !== null && in_array($fnName, $reserved_functions)) {
        cli_err_syntax("\"{$fnName}\" - Function is a reserved function name!");
    }
    if ($handlerFile !== null && in_array($handlerFile, $reserved_functions)) {
        cli_err_syntax("\"{$handlerFile}\" - Handler is a reserved function name!");
    }

    // Function name is optional, so if not provided, we set it to the handler file name since
    // that is the default name for the function in the handler file when the file is created
    if ($fnName === null) {
        $fnName = $handlerFile;
    }
    cli_info_without_exit("Parsed Handler: \"funkphp/handlers/$handlerFile.php\" and Function: \"$fnName\"");

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$method$validRoute\"");

    // Check if the exact route already exists in the route file
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]) ?? null) {
        cli_err("\"$method$validRoute\" already exists in Routes!");
    }

    // Now we check against conflicting routes (dynamic routes) and if it exists, we error
    $findDynamicRoute = cli_match_developer_route($method, $validRoute, include_once $exactFiles['troute_route'], $singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
    if ($findDynamicRoute['route'] !== null) {
        cli_err("Dynamic Route \"{$findDynamicRoute['method']}{$findDynamicRoute['route']}\" would conflict with \"$method$validRoute\" in Routes!");
    }

    // Prepare handlers folders
    $handlersDir = $dirs['handlers'];

    // Check first if the handler file exists in the handlers folder, add .php if not
    if (file_exists($handlersDir . $handlerFile . ".php")) {
        // Read the file content and check if the function name exists in the file
        $fileContent = file_get_contents($handlersDir . $handlerFile . ".php");
        if ($fnName !== null && strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName") !== false) {
            cli_err_without_exit("Function \"$fnName\" in Handler \"funkphp/handlers/$handlerFile.php\" is already used!");
            cli_err_without_exit("If the Handler File with the Function was created using \"php funkcli add handler [HandlerFile[=>Function]]\", you must MANUALLY add it to the Route file!");
            cli_info("Use \"php funkcli add route [Method/Route] [HandlerFile[=>Function]]\" instead to avoid these possible issues in the first place!");
        }
        // This means handler file exists but function name is not used, so we can add it
        else {
            cli_info_without_exit("Handler \"funkphp/handlers/$handlerFile.php\" exists and Function \"$fnName\" is valid!");
            // We now check if we can find "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile" in the file
            // and if not that means either error or Developer is trying to break the file, so we exit
            if (strpos($fileContent, "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile") === false) {
                cli_err("Handler \"funkphp/handlers/$handlerFile.php\" is invalid. Could not find \"//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile\". Please do not be a jerk trying to break the file!");
            }
            // We found the comment, so we can add the function name to the file by replacing the comment with the function name and then the comment again!
            $fileContent = str_replace(
                "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile",
                "//DELIMITER_HANDLER_FUNCTION_START=$fnName\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n\n};\n//DELIMITER_HANDLER_FUNCTION_END=$fnName\n\n//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile",
                $fileContent
            );
            if (file_put_contents($handlersDir . $handlerFile . ".php", $fileContent) !== false) {
                cli_success_without_exit("Added Function \"$fnName\" to Handler \"funkphp/handlers/$handlerFile.php\"!");
            } else {
                cli_err("FAILED to add Function \"$fnName\" to Handler \"funkphp/handlers/$handlerFile.php\". File permissions issue?");
            }
        }
    } // File does not exist, so we create it
    else {
        // Create the handler file with the function name and return a success message
        $outputHandlerRoute = file_put_contents(
            $handlersDir . $handlerFile . ".php",
            "<?php\n//Handler File - This runs after Middlewares have ran after matched Route!\n\n//DELIMITER_HANDLER_FUNCTION_START=$fnName\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n\n};\n//DELIMITER_HANDLER_FUNCTION_END=$fnName\n\n//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile\nreturn function (&\$c, \$handler = \"$fnName\") {\n\$handler(\$c);\n};\n//NEVER_TOUCH_ANY_COMMENTS_END=$handlerFile"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Added Handler \"funkphp/handlers/$handlerFile.php\" with Function \"$fnName\" in \"funkphp/handlers/$handlerFile.php\"!");
        } else {
            cli_err("FAILED to create Handler \"funkphp/handlers/$handlerFile.php\". File permissions issue?");
        }
    }
    // If we are here, that means we managed to add a handler with a function
    // name to a file so now we add route to the route file and then compile it!
    if ($arrow) {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
            'handler' => [$handlerFile => $fnName],
        ];
    } else {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
            'handler' => $handlerFile,
        ];
    }
    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Added Route \"$method$validRoute\" to \"funkphp/routes/route_single_routes.php\" with Handler \"$handlerFile\" and Function \"$fnName\"!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Add a 'data' handler to a specific route (route must exist or the function will error)
function cli_add_a_data_handler()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute, $reserved_functions;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add data [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add data get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
    }

    // Check if "$argv[4]" contains "=>" and split it into
    // handler & function name or just use $handlerFile name.
    $handlerFile = null;
    $fnName = null;
    $arrow = null;
    if (strpos($argv[4], '=>') !== false) {
        [$handlerFile, $fnName] = explode('=>', $argv[4]);
        $handlerFile = trim($handlerFile);
        $fnName = trim($fnName);
        $arrow = true;
    } else {
        $handlerFile = $argv[4];
        $fnName = null;
    }

    // Preg_match validate both (unless null) handler file and function name
    if ($handlerFile !== null && !preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" - Data Handler name must be a lowercased string containing only letters, numbers and underscores!");
    }
    if ($fnName !== null && !preg_match('/^[a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" - Function name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Check that both fnName and handlerFile are not reserved functions
    if ($fnName !== null && in_array($fnName, $reserved_functions)) {
        cli_err_syntax("\"{$fnName}\" - Function is a reserved function name!");
    }
    if ($handlerFile !== null && in_array($handlerFile, $reserved_functions)) {
        cli_err_syntax("\"{$handlerFile}\" - Handler is a reserved function name!");
    }

    // Function name is optional, so if not provided, we set it to the handler file name since
    // that is the default name for the function in the handler file when the file is created
    if ($fnName === null) {
        $fnName = $handlerFile;
    }
    cli_info_without_exit("Parsed Data Handler: \"funkphp/data/$handlerFile.php\" and Function: \"$fnName\"");

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$method$validRoute\"");

    // Check if the exact route does not exist the route file
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute]) ?? null) {
        cli_err("Route \"$method$validRoute\" not found in Routes. Add it first before adding a Data Handler!");
    }

    // Check that a data handler does not already exist for the route
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['data']) && !empty($singleRoutesRoute['ROUTES'][$method][$validRoute]['data'])) {
        cli_err_without_exit("A Data Handler for Route \"$method$validRoute\" already exists!");
        cli_info("Use command \"php funkcli delete data [method/route] [handlerFile[=>Function]]\" to delete it first!");
    }

    // When data handler is empty which it should not be so we error out
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['data']) && empty($singleRoutesRoute['ROUTES'][$method][$validRoute]['data'])) {
        cli_err("Data Handler for Route \"$method$validRoute\" is empty. Consider deleting it OR manually adding a Data Handler to it!");
    }

    // Here, the routing is so far all OK so prepare data folder
    $handlersDir = $dirs['data'];

    // Check first if the handler file exists in the handlers folder, add .php if not
    if (file_exists($handlersDir . $handlerFile . ".php")) {
        // Read the file content and check if the function name exists in the file
        $fileContent = file_get_contents($handlersDir . $handlerFile . ".php");
        if ($fnName !== null && strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName") !== false) {
            // Find what <method/route> that is already using it or just show default error!
            $pattern = "/\/\/DELIMITER_HANDLER_FUNCTION_START={$fnName}.*\n.*?<(.*?)>.*/si";
            if (preg_match($pattern, $fileContent, $matches) && isset($matches[1])) {
                cli_err_without_exit("Function \"$fnName\" in Data Handler \"funkphp/data/$handlerFile.php\" is already used by Route \"{$matches[1]}\"! (unless false comment)");
            } else {
                cli_err_without_exit("Function \"$fnName\" in Data Handler \"funkphp/data/$handlerFile.php\" has already been created!");
            }
            cli_info("If you know what Route that should be using that Data Handler instead, just manually change it in the Route file!");
        }
        // This means data handler file exists but function name is not used, so we can add it
        else {
            cli_info_without_exit("Handler \"funkphp/data/$handlerFile.php\" exists and Function \"$fnName\" is valid!");
            // We now check if we can find "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile" in the file
            // and if not that means either error or Developer is trying to break the file, so we exit
            if (strpos($fileContent, "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile") === false) {
                cli_err("Handler \"funkphp/data/$handlerFile.php\" is invalid. Could not find \"//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile\". Please do not be a jerk trying to break the file!");
            }
            // We found the comment, so we can add the function name to the file by replacing the comment with the function name and then the comment again!
            $fileContent = str_replace(
                "//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile",
                "//DELIMITER_HANDLER_FUNCTION_START=$fnName\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n\n};\n//DELIMITER_HANDLER_FUNCTION_END=$fnName\n\n//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile",
                $fileContent
            );
            if (file_put_contents($handlersDir . $handlerFile . ".php", $fileContent) !== false) {
                cli_success_without_exit("Added Function \"$fnName\" to Data Handler \"funkphp/data/$handlerFile.php\"!");
            } else {
                cli_err("FAILED to add Function \"$fnName\" to Data Handler \"funkphp/data/$handlerFile.php\". File permissions issue?");
            }
        }
    } // File does not exist, so we create it
    else {
        // Create the handler file with the function name and return a success message
        $outputHandlerRoute = file_put_contents(
            $handlersDir . $handlerFile . ".php",
            "<?php\n//Data Handler File - This runs after Route Handler have ran for matched Route!\n\n//DELIMITER_HANDLER_FUNCTION_START=$fnName\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n\n};\n//DELIMITER_HANDLER_FUNCTION_END=$fnName\n\n//NEVER_TOUCH_ANY_COMMENTS_START=$handlerFile\nreturn function (&\$c, \$handler = \"$fnName\") {\n\$handler(\$c);\n};\n//NEVER_TOUCH_ANY_COMMENTS_END=$handlerFile"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Added Data Handler \"funkphp/data/$handlerFile.php\" with Function \"$fnName\" in \"funkphp/data/$handlerFile.php\"!");
        } else {
            cli_err("FAILED to create Data Handler \"funkphp/data/$handlerFile.php\". File permissions issue?");
        }
    }
    // If we are here, that means we managed to add a data handler with a function
    // name to a file so now we add route to the route file and then compile it!
    if ($arrow) {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
            'data' => [$handlerFile => $fnName],
        ];
    } else {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
            'data' => $handlerFile,
        ];
    }
    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Added Data Data Handler \"$handlerFile\" and Function \"$fnName\" to Route \"$method$validRoute\" in \"funkphp/routes/route_single_routes.php\"!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Add a single Middleware file to middleware folder (funkphp/middlewares/)
function cli_add_a_middleware()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nphp funkcli add mw [Method/route] [Middleware_handler]\nExample: 'php funkcli add mw GET/users/:id validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[4])) {
        cli_err_syntax("\"{$argv[4]}\" - Middleware Name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that the exact route already exists in the route file
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute])) {
        cli_err("The Route \"$method$validRoute\" does not exist. Add it first!");
    }

    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']) && cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
        cli_err("Middleware \"$argv[4]\" already exists in \"$method$validRoute\"!");
    }

    // We will now check if the middleware already exists one URI level down the
    // current route. This means that if the route is GET/users/:id, we will check if
    // the middleware exists in GET/users/, and then GET/. We first split the route
    // on "/" and then loop through that array and
    $splittedURI = explode("/", trim($validRoute, "/"));
    $currentParentUri = '';

    // First check default root "/" route for the given method
    $checkUri = '/';
    if (
        isset($singleRoutesRoute['ROUTES'][$method][$checkUri])
        && cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$checkUri]['middlewares'] ?? null)
    ) {
        cli_err_without_exit("Middleware \"$argv[4]\" already exists in \"$method$checkUri\"!");
        cli_err("Adding it would to \"$method$validRoute\" would cause it to run twice!");
    }

    // Now we loop through the $splittedURI array and check if
    // the middleware exists when adding each segment of the URI
    foreach ($splittedURI as $uriSegment) {
        $currentParentUri .= '/' . $uriSegment;
        if (
            isset($singleRoutesRoute['ROUTES'][$method][$currentParentUri])
            && cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$currentParentUri]['middlewares'] ?? null)
        ) {
            cli_err_without_exit("Middleware \"$argv[4]\" already exists in \"$method$currentParentUri\"!");
            cli_err("Adding it would to \"$method$validRoute\" would cause it to run twice!");
        }
    }

    // Here we know the middleware can be added to the
    // current Route so prepare middleware folder & file name
    $mwDir = $dirs['middlewares'];
    $mwName = str_ends_with($argv[4], ".php") ? $argv[4] : $argv[4] . ".php";

    // We check if file exists already because then we do not need to create it.
    if (file_exists($mwDir . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[4]\" already exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        // Create the middleware file with the function name and return a success message
        $date = date("Y-m-d H:i:s");
        $outputHandlerRoute = file_put_contents(
            $mwDir . $mwName,
            "<?php\n// Middleware \"$mwName\" \n// File created in FunkCLI on $date!\n\nreturn function (&\$c) {\n};\n?>"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Created new Middleware \"$argv[4].php\" in \"funkphp/middlewares/$mwName\"!");
        } else {
            cli_err("FAILED to create Middleware \"$argv[4]\". File permissions issue?");
        }
    }
    // File now created if not existed, so now we add it to the 'middlewares' handler (or create it if not existed)
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
        $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'][] = $argv[4];
    } else {
        $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'] = [$argv[4]];
    }
    cli_success_without_exit("Added Middleware \"$argv[4]\" to \"$method$validRoute\"!");
    // Finally we show success message and then sort, build, compile and output the routes
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Delete a single Middleware from a given method/route (does NOT delete the MW file!)
function cli_delete_a_middleware()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nfunkcli delete [mw|middleware] [method/route] [Middleware_name]\nExample: 'php funkcli delete mw GET/users/:id validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[4])) {
        cli_err_syntax("\"{$argv[4]}\" - Middleware name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Grab middlewares folder and file name with .php extension
    // and then check if the file exists in the middlewares folder
    $mwFolder = $dirs['middlewares'];
    $mwName = str_ends_with($argv[4], ".php") ? $argv[4] : $argv[4] . ".php";
    if (file_exists($mwFolder . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[4].php\" exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        cli_err_without_exit("Middleware \"$argv[4].php\" not found in \"funkphp/middlewares\"!");
        cli_info("Maybe misspelled file name if you are already using \"$argv[4]\" in other routes?");
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that route actually exists for the given method in the route file
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute])) {
        cli_err("Route \"$method$validRoute\" does not exist. It cannot have the middleware \"$argv[4]\"!");
    }
    // Now check if "middleware" key exists in the route and if it does, check if the middleware exists in it
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
        if (!cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
            cli_err("Middleware \"$argv[4]\" not found in Route \"$method$validRoute\"!");
        } else {
            // Remove the middleware from the route, first check if it is an array or string
            if (is_array($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
                $key = array_search($argv[4], $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']);
                unset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'][$key]);
                // Also remove middleware key if it is empty after removing the middleware
                if (empty($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
                    unset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']);
                }
            } else {
                unset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']);
            }
            // If successful, we show success message and then sort, build, compile and output the routes
            cli_sort_build_routes_compile_and_output($singleRoutesRoute);
            cli_success_without_exit("Removed Middleware \"$argv[4]\" from Route \"$method$validRoute\"!");
            cli_info_without_exit("The Middleware \"$argv[4].php\" still exists in \"funkphp/middlewares/\"!");
        }
    } else {
        cli_err("Route \"$method$validRoute\" has no middlewares!");
    }
}

// Delete a single Middleware from all methods with routes (does NOT delete the MW file!)
function cli_delete_a_middleware_from_all_routes()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax("Should be at least three(3) non-empty string arguments!\nphp funkcli delete [mw_from_all_routes|middleware_from_all_routes] [method/route] [Middleware_name]\nExample: 'php funkcli delete mw_from_all_routes validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[3])) {
        cli_err_syntax("\"{$argv[3]}\" - Middleware name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Grab middlewares folder and file name with .php extension
    // and then check if the file exists in the middlewares folder
    $mwFolder = $dirs['middlewares'];
    $mwName = str_ends_with($argv[3], ".php") ? $argv[3] : $argv[3] . ".php";
    if (file_exists($mwFolder . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[3].php\" exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        cli_err_without_exit("Middleware \"$argv[3].php\" not found in \"funkphp/middlewares\"!");
        cli_info("Maybe misspelled file name if you are already using \"$argv[3]\" in other routes?");
    }

    // We will now loop through all routes and check if the middleware exists in them
    $removeCount = 0;
    foreach ($singleRoutesRoute['ROUTES'] as $method => $routes) {
        foreach ($routes as $route => $routeData) {
            // Check if the route has the middleware in it, and if it does, remove it; be it a string or inside an array
            if (isset($routeData['middlewares']) && cli_value_exists_as_string_or_in_array($argv[3], $routeData['middlewares'])) {
                if (is_array($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                    $key = array_search($argv[3], $singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'][$key]);
                    // Also remove middleware key if it is empty after removing the middleware
                    if (empty($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                        unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    }
                } else {
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                }
                cli_info_without_exit("Removed Middleware \"$argv[3]\" from Route \"$method$route\"!");
                $removeCount++;
            }
        }
    }

    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Removed Middleware \"$argv[3]\" from $removeCount Routes!");
    cli_info_without_exit("The Middleware \"$argv[3].php\" still exists in \"funkphp/middlewares/\"!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Delete an actual Middleware file from the middlewares folder (funkphp/middlewares/)
// This also removes it from every route it is used in, so be careful with this one!
function cli_delete_a_middleware_file()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax("Should be at least three(3) non-empty string arguments!\nphp funkcli delete [mw_from_all_routes|middleware_from_all_routes] [method/route] [Middleware_name]\nExample: 'php funkcli delete mw_from_all_routes validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[3])) {
        cli_err_syntax("\"{$argv[3]}\" - Middleware name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Grab middlewares folder and file name with .php extension
    // and then check if the file exists in the middlewares folder
    $mwFolder = $dirs['middlewares'];
    $mwName = str_ends_with($argv[3], ".php") ? $argv[3] : $argv[3] . ".php";
    if (file_exists($mwFolder . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[3].php\" exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        cli_err_without_exit("Middleware \"$argv[3].php\" not found in \"funkphp/middlewares\"!");
        cli_info("Maybe misspelled file name if you are already using \"$argv[3]\" in other routes?");
    }

    // We now try to unlink the file and check if it was successful
    if (unlink($mwFolder . $mwName)) {
        cli_success_without_exit("Deleted Middleware \"$argv[3].php\" from \"funkphp/middlewares/$mwName\"!");
        cli_info_without_exit("Moving on to removing it from all Routes that use it...");
    } else {
        cli_err_without_exit("FAILED to delete Middleware \"$argv[3].php\". File permissions issue?");
        cli_info("No Middleware handlers have been removed from the Routes since the file was not deleted!");
    }

    // We will now loop through all routes and check if the middleware exists in them
    $removeCount = 0;
    foreach ($singleRoutesRoute['ROUTES'] as $method => $routes) {
        foreach ($routes as $route => $routeData) {
            // Check if the route has the middleware in it, and if it does, remove it; be it a string or inside an array
            if (isset($routeData['middlewares']) && cli_value_exists_as_string_or_in_array($argv[3], $routeData['middlewares'])) {
                if (is_array($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                    $key = array_search($argv[3], $singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'][$key]);
                    // Also remove middleware key if it is empty after removing the middleware
                    if (empty($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                        unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    }
                } else {
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                }
                cli_info_without_exit("Removed Middleware \"$argv[3]\" from Route \"$method$route\"!");
                $removeCount++;
            }
        }
    }

    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Removed Middleware \"$argv[3]\" from $removeCount Routes after deleting the file!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Batched function of compiling and outputting routing files
function cli_compile_batch($arrayOfRoutesToCompileAndOutput)
{
    // Check if the array is a non-empty array
    if (!is_array($arrayOfRoutesToCompileAndOutput) || empty($arrayOfRoutesToCompileAndOutput)) {
        cli_err_syntax("Array of Routing Files to Compile & Output must be a non-empty array!");
    }

    // Load global routing files
    global $singleRoutesRoute;

    foreach ($arrayOfRoutesToCompileAndOutput as $routeString) {
        if ($routeString === "troutes") {
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
            continue;
        }
    }
}

// Backup all files in a folder to another folder
function cli_backup_all_files_in_folder_to_another_folder($backupFolderDestinationWithoutExtension, $ext, $backupFolder)
{
    // Check that all three arguments are non-empty strings!
    if (
        !is_string($backupFolderDestinationWithoutExtension) ||  !is_string($ext) || !is_string($backupFolder)
        || $backupFolderDestinationWithoutExtension === "" || $ext === "" || $backupFolder === ""
    ) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder destination, file extension and backup folder must be non-empty strings!");
    }

    // Check that both dirs exist, are readable and writable
    if (!is_dir($backupFolderDestinationWithoutExtension)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder destination must be a valid directory. Path: $backupFolderDestinationWithoutExtension is not!");
    }
    if (!is_writable($backupFolderDestinationWithoutExtension)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder destination must be writable! Path: $backupFolderDestinationWithoutExtension is not!");
    }
    if (!is_dir($backupFolder)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder must be a valid directory. Path: $backupFolder is not!");
    }
    if (!is_readable($backupFolder)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder must be readable! Path: $backupFolder is not!");
    }
    // We will now loop through the $backupFolder and call the cli_backup_file_until_success() function for each file in the folder
    // and that is not a folder itself. Those are just ignored (continue;)
    $files = scandir($backupFolder);
    $countOfCopiedFiles = 0;
    foreach ($files as $file) {
        if (is_dir($backupFolder . "/" . $file)) {
            continue;
        }
        // Check if the file ends with the extension
        if (str_ends_with($file, $ext)) {
            // Call the cli_backup_file_until_success() function for each file in the folder
            cli_backup_file_until_success($backupFolderDestinationWithoutExtension, $ext, $backupFolder . "/" . $file);
            $countOfCopiedFiles++;
        }
    }
    // Check if we copied any files
    if ($countOfCopiedFiles === 0) {
        cli_info("No files copied from $backupFolder to $backupFolderDestinationWithoutExtension!");
    } else {
        cli_success_without_exit("Copied $countOfCopiedFiles files from $backupFolder to $backupFolderDestinationWithoutExtension!");
    }
}

// Output backup file until success (by waiting one second and retrying with new file name that is the file name + new datetime and extension    )
function cli_backup_file_until_success($backupDestinationWithoutExtension, $extension, $backupData)
{
    // Check non-empty strings in all three variables
    if (
        !is_string($backupDestinationWithoutExtension) ||  !is_string($extension) || !is_string($backupData)
        || $backupDestinationWithoutExtension === "" || $extension === "" || $backupData === ""
    ) {
        cli_err_syntax("Backup destination, extension and exact backup data must be non-empty strings!");
    }

    // Check extension is valid (starting with ".") and ending with only characters
    if (!str_starts_with($extension, ".")) {
        cli_err_syntax("Backup extension must start with '.' and only contain characters!");
    }

    // Check preg_match for extension which is (.[a-zA-Z0-9-_]+$)
    if (!preg_match("/\.[a-zA-Z0-9-_]+$/", $extension)) {
        cli_err_syntax("Backup extension must start with '.' and only contain characters (a-zA-Z0-9-_)!");
    }

    // Check that backup destination exists (each folder in the path must exist)
    $backupDestination = dirname($backupDestinationWithoutExtension);
    if (!is_dir($backupDestination)) {
        cli_err_syntax("Backup destination must be a valid directory. Path: $backupDestination is not!");
    }
    if (!is_writable($backupDestination)) {
        cli_err_syntax("Backup destination must be writable! Path: $backupDestination is not!");
    }

    // Check that backup data file exists (each folder in the path must exist)
    if (!is_file($backupData)) {
        cli_err_syntax("Backup data file must be a valid file. Path: $backupData is not!");
    }
    if (!is_readable($backupData)) {
        cli_err_syntax("Backup data file must be readable! Path: $backupData is not!");
    }

    // Get the contents from the $backupData file before we write it to the backup file
    $backupData = file_get_contents($backupData);

    // Now we use the cli_backup_file_until_success function to create the backup file
    cli_output_file_until_success($backupDestinationWithoutExtension, $extension, $backupData, "Backup file written successfully: $backupDestinationWithoutExtension!");
}

// Restore a backup file from the backup directory to the restore file path (it also deletes the backup file after restoring it!)
function cli_restore_file($backupDirPath, $restoreFilePath, $fileStartingName)
{
    // Check non-empty strings in all variables
    if (
        !is_string($backupDirPath) ||  !is_string($restoreFilePath) || !is_string($fileStartingName)
        || $backupDirPath === "" || $restoreFilePath === "" || $fileStartingName === ""

    ) {
        cli_err_syntax("Backup Dir Path, Restore File Path and File Starting Name must be non-empty strings!");
    }

    // We check if backup dir path is a valid directory
    if (!is_dir($backupDirPath)) {
        cli_err_syntax("Backup Dir Path must be a valid directory. Path: $backupDirPath is not!");
    }

    // We check if backup dir path is readable
    if (!is_readable($backupDirPath)) {
        cli_err_syntax("Backup Dir Path must be readable! Path: $backupDirPath is not!");
    }

    // Lowercase the file starting name
    $fileStartingName = strtolower($fileStartingName);

    // We check if backup dir has any files in it. We sort descnding so we
    // get the latest file first due to the date time stamp in the file name
    $files = scandir($backupDirPath, SCANDIR_SORT_DESCENDING);
    if (count($files) <= 2) {
        cli_err_syntax("Backup Dir Path must have at least one file in it! Path: $backupDirPath has no files!");
    }

    // We loop through all the files in the backup dir path and check if they start with the file starting name
    // and if they do, we check if the file is readable and then we copy it to the restore file path
    foreach ($files as $file) {
        // Check if the file starts with the file starting name
        if (str_starts_with(strtolower($file), $fileStartingName)) {

            // Check if the file is readable
            if (!is_readable($backupDirPath . "/" . $file)) {
                cli_err("Backup file must be readable! Path: $backupDirPath/$file is not!");
            }

            // Copy the file to the restore file path and delete the backup file after restoring it
            copy($backupDirPath . "/" . $file, $restoreFilePath);
            unlink($backupDirPath . "/" . $file);
            cli_success_without_exit("Backup File Restored: $restoreFilePath!");
            return;
        }
    }
    // If we reach here, it means we didn't find any files that start with the file starting name
    cli_err("No Backup File in $backupDirPath starting with \"$fileStartingName\"!");
}

// Retrieve starting code for files created by the CLI
function cli_get_prefix_code($keyString)
{
    $currDate = date("Y-m-d H:i:s");
    $prefixCode = [
        "route_singles_routes_start" => "<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\nreturn ",
        "route_middleware_routes_start" => "<?php // ROUTE_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "data_middleware_routes_start" => "<?php // DATA_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "page_middleware_routes_start" => "<?php // PAGES_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "data_singles_routes_start" => "<?php // DATA_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "page_singles_routes_start" => "<?php // PAGE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return",
    ];

    return $prefixCode[$keyString] ?? null;
}

// Get a unique file name for a given directory and starting file name (so it checks with the starting file name and then adds a number to it)
function cli_get_unique_filename_for_dir($dirPath, $startingFileName, $middlewareException = false)
{
    // Check both are non-empty strings
    if (
        !is_string($dirPath) ||  !is_string($startingFileName)
        || $dirPath === "" || $startingFileName === ""
    ) {
        cli_err_syntax("Directory Path and Starting File Name must be non-empty strings!");
    }

    // Check if the starting file name is valid (it must not contain any special characters)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $startingFileName)) {
        cli_err_syntax("Starting File Name must only contain letters, numbers and underscores!");
    }

    // Check if the directory path is a valid directory
    if (!is_dir($dirPath)) {
        cli_err_syntax("Directory Path must be a valid directory. Path: $dirPath is not!");
    }

    // Check if the directory path is writable
    if (!is_writable($dirPath)) {
        cli_err_syntax("Directory Path must be writable! Path: $dirPath is not!");
    }

    // First do a quick check if the combined dir path and starting file name exists
    // add ".php" to the end of the file name
    $filePath = $dirPath . "/" . $startingFileName . ".php";
    if (file_exists($filePath)) {
        // If it exists, we need to add a number to the end of the file name
        $i = 1;
        while (file_exists($dirPath . "/" . $startingFileName . "_" . $i . ".php")) {
            $i++;
        }
        return $startingFileName . "-" . $i . ".php";
    }
    // If it doesn't exist, we just return the starting file name with ".php" added to the end of it
    return $startingFileName . ".php";
}

// Delete all files in a given directory, but not folders inside of it though!
function cli_delete_all_files_in_directory_except_other_directories($directoryPath)
{
    // Check if the directory path is a valid directory
    if (!is_dir($directoryPath)) {
        cli_err_syntax("Directory Path must be a valid directory. Path: $directoryPath is not!");
    }

    // Check if the directory path is writable
    if (!is_writable($directoryPath)) {
        cli_err_syntax("Directory Path must be writable! Path: $directoryPath is not!");
    }

    // Get all files in the directory
    $files = scandir($directoryPath);
    $filecount = count($files);

    // Loop through all files and delete them
    foreach ($files as $file) {
        // Check if file is not a directory and not "." or ".." and only then delete it
        if (is_dir($directoryPath . "/" . $file) || $file === "." || $file === "..") {
            continue;
        }
        unlink($directoryPath . "/" . $file);
    }
    cli_success_without_exit("$filecount Files Deleted in: $directoryPath!");
}

// Validate start syntax for route string before processing the rest of the string
// Valid ones are: "GET/g", "POST/po", "PUT/pu", "DELETE/d", "PATCH/pa"
function cli_valid_route_start_syntax($routeString)
{
    // First check that string is non-empty string
    if (!is_string($routeString) || empty($routeString)) {
        cli_err_syntax("Route string must be a non-empty string starting with a valid HTTP Method and then the Route!");
    }
    // Then we check if it starts with one of the valid ones
    if (str_starts_with($routeString, "get/") || str_starts_with($routeString, "post/") || str_starts_with($routeString, "put/") || str_starts_with($routeString, "delete/") || str_starts_with($routeString, "patch/")) {
        return true;
    } elseif (str_starts_with($routeString, "g/") || str_starts_with($routeString, "po/") || str_starts_with($routeString, "pu/") || str_starts_with($routeString, "d/") || str_starts_with($routeString, "pa/")) {
        return true;
    } else {
        return false;
    }
}

// Prepares a valid route string to by validating starting syntax and extracting the method from it
function cli_prepare_valid_route_string($addRoute)
{
    // Grab the route to add and validate correct starting syntax
    // first: get/put/post/delete/ or its short form g/pu/po/d/
    if (!cli_valid_route_start_syntax($addRoute)) {
        cli_err_syntax("Route string must start with one of the valid ones:\n'GET/' (or g/)'\n'POST/' (or po/)\n'PUT/'(or pu/)\n'DELETE/' (or d/)\n'PATCH/' (or pa/)");
    }
    // Try extract the method from the route string
    $method = cli_extracted_parsed_method_from_valid_start_syntax($addRoute);
    if ($method === null) {
        cli_err("Failed to parse the Method the Route string must start with (all of these below are valid):\n'GET/' (or g/)'\n'POST/' (or po/)\n'PUT/'(or pu/)\n'DELETE/' (or d/)\n'PATCH/' (or pa/)");
    }
    // Split route oon first "/" and add a a "/" to beginning of the route string
    // and then parse the rest of the string to build the route and its parameters
    $addRoute = explode("/", $addRoute, 2)[1] ?? null;
    $addRoute = "/" . $addRoute;
    $validRoute = cli_parse_rest_of_valid_route_syntax_better($addRoute);

    return [
        $method,
        $validRoute,
    ];
}

// Extract the method from the route string and parse the rest of the string
function cli_extracted_parsed_method_from_valid_start_syntax($routeString)
{
    // We now extract the method from the string and then begin
    // parsing the rest of the string character by character
    // to build the route and its parameters.
    $extractedMethod = explode("/", $routeString)[0];
    if ($extractedMethod == "get") {
        $parsedMethod = "GET";
    } elseif ($extractedMethod == "post") {
        $parsedMethod = "POST";
    } elseif ($extractedMethod == "put") {
        $parsedMethod = "PUT";
    } elseif ($extractedMethod == "delete") {
        $parsedMethod = "DELETE";
    } elseif ($extractedMethod == "g") {
        $parsedMethod = "GET";
    } elseif ($extractedMethod == "po") {
        $parsedMethod = "POST";
    } elseif ($extractedMethod == "pu") {
        $parsedMethod = "PUT";
    } elseif ($extractedMethod == "d") {
        $parsedMethod = "DELETE";
    } elseif ($extractedMethod == "pa") {
        $parsedMethod = "PATCH";
    } elseif ($extractedMethod == "pa") {
        $parsedMethod = "PATCH";
    } else {
        $parsedMethod = null;
    }
    return $parsedMethod ?? null;
}

// Parse the rest of the route string after the method has been extracted
// and return the valid built route string with
function cli_parse_rest_of_valid_route_syntax_better($routeString)
{
    // Variables for states and possible characters
    $BUILTRoute = "";
    $lastAddedC = "";
    $BUILTParam = "";
    $PARAMS = [];
    $IN_DYNAMIC = false;
    $IN_STATIC = false;
    $NEW_SEGMENT = false;
    $NUMS_N_CHARS = array_flip(
        array_merge(
            range('a', 'z'),
            range('0', '9'),
        )
    );
    $SEPARATORS = [
        "-" => [],
        "_" => [],
    ];
    $PARAM_CHAR = [":" => []];
    // Prepare segments by splitting the route string
    //  by "/" and also deleting empty segments
    $path = trim($routeString, '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    // Edge case: if the route string is empty, we just return "/"
    if (count($uriSegments) === 0) {
        return "/";
    }
    // Implode again and add a "/" to the beginning of the string
    $path = "/" . implode("/", $uriSegments);
    $len = strlen($path);
    // We now loop through.
    for ($i = 0; $i < $len; $i++) {
        $c = $path[$i];
        // Special case: only one character in the string which means we just
        // return "/"
        if ($len === 1) {
            return "/";
        }
        // First char is ALWAYS a "/"!
        if ($i === 0) {
            $BUILTRoute .= "/";
            $lastAddedC = "/";
            $NEW_SEGMENT = true;
            continue;
        }
        // Check if we are at the end of the string
        if ($i === $len - 1) {
            // Only allowed chars are: NUMS_N_CHARS
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                // Check if we in param building and if so, we
                // add the param to the params array unless it already exists
                if ($IN_DYNAMIC) {
                    $BUILTParam .= $c;
                    if (in_array($BUILTParam, $PARAMS)) {
                        cli_err_syntax("Duplicate parameter found in Route: \"$BUILTParam\"!");
                    }
                    $PARAMS[] = $BUILTParam;
                    $BUILTParam = "";
                }
                continue;
            }
            // Since we are at the end of the string, we check
            // if in dynamic building and if so, we add the
            //  param to the params array unless it already exists
            if ($IN_DYNAMIC) {
                if (in_array($BUILTParam, $PARAMS)) {
                    cli_err_syntax("Duplicate parameter found in Route: \"$BUILTParam\"!");
                }
                if ($BUILTParam !== "") {
                    // Check if built param ends with "_" or "-" and remove it
                    if (isset($SEPARATORS[$BUILTParam[strlen($BUILTParam) - 1]])) {
                        $BUILTParam = substr($BUILTParam, 0, -1);
                    }
                    $PARAMS[] = $BUILTParam;
                }
                $BUILTParam = "";
            }
            continue;
        }
        // First check if we are inside of a new segment building
        if ($NEW_SEGMENT) {
            // If new segment, then only allowed chars are: NUMS_N_CHARS or PARAM_CHAR
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $NEW_SEGMENT = false;
                $IN_STATIC = true;
                continue;
            }
            // Here a new ":" param starts!
            if (isset($PARAM_CHAR[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $NEW_SEGMENT = false;
                $IN_DYNAMIC = true;
                continue;
            }

            // Continue cause no allowed char is found!
            continue;
        }
        // Check if we are inside of a parameter building (meaning the previous char was ":")
        if ($IN_DYNAMIC) {
            // Check if next is a "/" meaning we reached the end of the static segment
            if ($c === "/") {
                // Here we check if the last added char was a separator too so we remove it
                // from the built route string before adding the "/", also from param string
                if (isset($SEPARATORS[$lastAddedC]) || isset($SEPARATORS[$c])) {
                    $BUILTRoute = substr($BUILTRoute, 0, -1);
                    $BUILTParam = substr($BUILTParam, 0, -1);
                }
                // Edge case when ":" appears right before "/"
                if ($lastAddedC === ":") {
                    $BUILTRoute = substr($BUILTRoute, 0, -1);
                    $IN_DYNAMIC = false;
                    $NEW_SEGMENT = true;
                    continue;
                }
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $IN_DYNAMIC = false;
                $NEW_SEGMENT = true;
                // We add the param to the params array unless it already exists
                if (in_array($BUILTParam, $PARAMS)) {
                    cli_err_syntax("Duplicate parameter found: $BUILTParam!");
                }
                // Add and reset the param string if not empty
                if ($BUILTParam !== "") {
                    $PARAMS[] = $BUILTParam;
                }
                $BUILTParam = "";
                continue;
            }
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $BUILTParam .= $c;
                $lastAddedC = $c;
                continue;
            }
            // In static, we only allow a separator char if the last added char was a separator too
            // like "_" or "-" and if so, we check if the current char is a separator too
            // meaning we will just ignore the current char and continue
            if ((isset($SEPARATORS[$lastAddedC]) || isset($PARAM_CHAR[$lastAddedC])) && isset($SEPARATORS[$c])) {
                continue;
            }
            // We allow a separator char if the last added char was a num or char
            // and if so, we check if the current char is a separator too
            if (!isset($SEPARATORS[$lastAddedC]) && isset($SEPARATORS[$c])) {
                $BUILTRoute .= $c;
                $BUILTParam .= $c;
                $lastAddedC = $c;
                continue;
            }
        }
        // Check if we are inside of a static building
        if ($IN_STATIC) {
            // Check if next is a "/" meaning we reached the end of the static segment
            if ($c === "/") {
                // Here we check if the last added char was a separator too so we remove it
                // from the built route string before adding the "/"
                if (isset($SEPARATORS[$lastAddedC]) || isset($SEPARATORS[$c])) {
                    $BUILTRoute = substr($BUILTRoute, 0, -1);
                }
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $IN_STATIC = false;
                $NEW_SEGMENT = true;
                continue;
            }
            // In static, we first check if current char is just a num or char
            // and if so, we just add it to the built route string
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                continue;
            }
            // In static, we only allow a separator char if the last added char was a separator too
            // like "_" or "-" and if so, we check if the current char is a separator too
            // meaning we will just ignore the current char and continue
            if (isset($SEPARATORS[$lastAddedC]) && isset($SEPARATORS[$c])) {
                continue;
            }
            // We allow a separator char if the last added char was a num or char
            // and if so, we check if the current char is a separator too
            if (!isset($SEPARATORS[$lastAddedC]) && isset($SEPARATORS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                continue;
            }
        }
    }
    // If more than 1 params, first extract last param from the params array
    // then check if it ends with "-" or "_" and if so, we remove it from that
    // param and check if both params are the same and thus throw error
    // otherwise add it again!
    if (count($PARAMS) > 1) {
        $lastParam = array_pop($PARAMS);
        if (isset($SEPARATORS[$lastParam[strlen($lastParam) - 1]])) {
            $lastParam = substr($lastParam, 0, -1);
            if (in_array($lastParam, $PARAMS)) {
                cli_err_syntax("Duplicate parameter found: $lastParam!");
            }
            $PARAMS[] = $lastParam;
        } else {
            if (in_array($lastParam, $PARAMS)) {
                cli_err_syntax("Duplicate parameter found: $lastParam!");
            }
            $PARAMS[] = $lastParam;
        }
    }
    if ($BUILTRoute === "" || $BUILTRoute === "/:") {
        $BUILTRoute = "/";
    }
    // We now remove "/:", "/", "-", "_" trailing at the end of the string
    if (strlen($BUILTRoute) > 2) {
        if (str_ends_with($BUILTRoute, "/:")) {
            $BUILTRoute = substr($BUILTRoute, 0, -2);
        } elseif (
            str_ends_with($BUILTRoute, "/")
            || str_ends_with($BUILTRoute, ":")
            || str_ends_with($BUILTRoute, "-")
            || str_ends_with($BUILTRoute, "_")
        ) {
            $BUILTRoute = substr($BUILTRoute, 0, -1);
        }
    }
    return $BUILTRoute;
}

// CLI Functions to show errors and success messages with colors
function cli_err_syntax($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[31m[FunkCLI - SYNTAX ERROR]: $string\n\033[0m";
    exit;
}
function cli_err($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[31m[FunkCLI - ERROR]: $string\n\033[0m";
    exit;
}
function cli_err_without_exit($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[31m[FunkCLI - ERROR]: $string\n\033[0m";
}
function cli_err_syntax_without_exit($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[31m[FunkCLI - SYNTAX ERROR]: $string\n\033[0m";
}
function cli_err_command($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[31m[FunkCLI - COMMAND ERROR]: $string\n\033[0m";
    exit;
}
function cli_success($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[32m[FunkCLI - SUCCESS]: $string\n\033[0m";
    exit;
}
function cli_info($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[34m[FunkCLI - INFO]: $string\n\033[0m";
    exit;
}
function cli_success_without_exit($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[32m[FunkCLI - SUCCESS]: $string\n\033[0m";
}
function cli_info_without_exit($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[34m[FunkCLI - INFO]: $string\n\033[0m";
}
function cli_info_multiline($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[34m[FunkCLI - INFO]: $string\n\033[0m";
}
function cli_warning($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[33m[FunkCLI - WARNING]: $string\n\033[0m";
    exit;
}
function cli_warning_without_exit($string)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    echo "\033[33m[FunkCLI - WARNING]: $string\n\033[0m";
}

// Function that takes a variable ($existsInWhat) and then checks if a given value
// already exists in it as a string or in an array. It returns true if it does, false otherwise.
function cli_value_exists_as_string_or_in_array($valueThatExists, $existsInWhat)
{
    // Also check first if it is null, because that means it doesn't exist
    if ($existsInWhat === null) {
        return false;
    }
    // Check based on array or string, and lowercase everything first
    if (is_array($existsInWhat)) {
        $existsInWhat = array_map('strtolower', $existsInWhat);
        return in_array($valueThatExists, $existsInWhat);
    } elseif (is_string($existsInWhat)) {
        $existsInWhat = strtolower($existsInWhat);
        return $valueThatExists === $existsInWhat;
    } else {
        return false;
    }
}

// Function takes a key and a value to add to it and then checks if referenced
// &$addToWhat exists or not, and if does exist, then it checks if it is an array
// otherwise it adds the key with value or it adds/pushes to the current array.
function cli_add_value_as_string_or_to_array($keyToAdd, $valueToAdd, &$addToWhat)
{
    // Check if the key exists in the array and if it is an array
    if (array_key_exists($keyToAdd, $addToWhat)) {
        if (is_array($addToWhat[$keyToAdd])) {
            // If it is an array, we just add the value to it
            $addToWhat[$keyToAdd][] = $valueToAdd;
        } elseif (is_string($addToWhat[$keyToAdd])) {
            // If it is a string, we convert it to an array and add the value to it
            $addToWhat[$keyToAdd] = [$addToWhat[$keyToAdd], $valueToAdd];
        }
    } else {
        // If it doesn't exist, we just add the key with the value to it
        $addToWhat[$keyToAdd] = $valueToAdd;
    }
}
