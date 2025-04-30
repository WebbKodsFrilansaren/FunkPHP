<?php

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
        "$folderBase/_BACKUPS/data/MW/",
        "$folderBase/_BACKUPS/handlers/",
        "$folderBase/_BACKUPS/handlers/R/",
        "$folderBase/_BACKUPS/handlers/D/",
        "$folderBase/_BACKUPS/handlers/P/",
        "$folderBase/_BACKUPS/pages/",
        "$folderBase/_BACKUPS/pages/MW/",
        "$folderBase/_BACKUPS/routes/",
        "$folderBase/_BACKUPS/routes/MW/",
        "$folderBase/_internals/",
        "$folderBase/_internals/compiled/",
        "$folderBase/_internals/functions/",
        "$folderBase/_internals/templates/",
        "$folderBase/cached/",
        "$folderBase/cached/R/",
        "$folderBase/cached/D/",
        "$folderBase/cached/P/",
        "$folderBase/config/",
        "$folderBase/data/",
        "$folderBase/dx_steps/",
        "$folderBase/handlers/",
        "$folderBase/handlers/R/",
        "$folderBase/handlers/D/",
        "$folderBase/handlers/P/",
        "$folderBase/middlewares/",
        "$folderBase/middlewares/R/",
        "$folderBase/middlewares/D/",
        "$folderBase/middlewares/P/",
        "$folderBase/pages/",
        "$folderBase/pages/complete/",
        "$folderBase/pages/parts/",
        "$folderBase/routes/",
        "$folderBase/tests/",
    ];

    // Prepare default files that doesn't exist if certain folders don't exist
    $defaultFiles = [
        "$folderBase/_internals/compiled/troute_route.php",
        "$folderBase/_internals/compiled/troute_data.php",
        "$folderBase/_internals/compiled/troute_page.php",
        "$folderBase/data/data_middleware_routes.php",
        "$folderBase/data/data_single_routes.php",
        "$folderBase/routes/route_middleware_routes.php",
        "$folderBase/routes/route_single_routes.php",
        "$folderBase/pages/page_middleware_routes.php",
        "$folderBase/pages/page_single_routes.php",
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
            } elseif (str_contains($file, "middleware")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [\n'MIDDLEWARES' => \n['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [],]];\n?>");
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

// Check if a specific Route file exists in either "/routes/", "/data/" or "/pages/"
function cli_route_file_exist($fileName): bool
{
    // Load valid file names and check if argument is valid
    global $dirs, $exactFiles, $settings;
    $validFilenames = [
        "route_single_routes" => $exactFiles['single_routes'],
        "route_middleware_routes" => $exactFiles['single_middlewares'],
        "data_single_routes" => $exactFiles['single_data'],
        "data_middleware_routes" => $exactFiles['single_middlewares_data'],
        "page_single_routes" => $exactFiles['single_page'],
        "page_middleware_routes" => $exactFiles['single_middlewares_page'],
    ];

    if (!is_string($fileName) || empty($fileName) || !array_key_exists($fileName, $validFilenames)) {
        cli_err_syntax("[cli_route_file_exist] Route file name must be a non-empty string and one of the following: " . implode(", ", array_keys($validFilenames)) . "!");
    }
    // Here we know the argument is valid so check if the file exists by using the matched key from $validFilenames
    if (file_exists($validFilenames[$fileName])) {
        return true;
    }
    return false;
}

// Check if Routes Route Handler in handlers/R/ exists
function cli_r_handler_exists($fileName): bool
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
    if (file_exists($dirs['handlers_routes'] . $fileName)) {
        return true;
    }
    return false;
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
        cli_err_syntax("[cli_rebuild_single_routes_file] Routes directory (funkphp/routes/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_routes']) && !is_writable($exactFiles['single_routes'])) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Routes file (funkphp/routes/route_single_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_routes'],
        cli_get_prefix_code("route_singles_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Rebuilds the Single Middleware Routes Route file (funkphp/routes/route_middleware_routes.php) based on valid array
function cli_rebuild_single_routes_mw_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_routes_mw_route_file] Single Route Middleware Routes File Array (funkphp/routes/route_middleware_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['MIDDLEWARES'])) {
        cli_err_syntax("[cli_rebuild_single_routes_mw_route_file] Single Route Middleware Routes File Array (funkphp/routes/route_middleware_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['routes']) || !is_writable($dirs['routes'])) {
        cli_err_syntax("[cli_rebuild_single_routes_mw_route_file] Routes directory (funkphp/routes/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_middlewares']) && !is_writable($exactFiles['single_middlewares'])) {
        cli_err_syntax("[cli_rebuild_single_routes_mw_route_file] Routes file (funkphp/routes/route_middleware_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_middlewares'],
        cli_get_prefix_code("route_middleware_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Rebuilds the Single Data Routes Route file (funkphp/data/data_single_routes.php) based on valid array
function cli_rebuild_single_data_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_data_route_file] Single Data Routes File Array (funkphp/data/data_single_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['ROUTES'])) {
        cli_err_syntax("[cli_rebuild_single_data_route_file] Single Data Routes File Array (funkphp/data/data_single_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['data']) || !is_writable($dirs['data'])) {
        cli_err_syntax("[cli_rebuild_single_data_route_file] Routes directory (funkphp/data/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_data']) && !is_writable($exactFiles['single_data'])) {
        cli_err_syntax("[cli_rebuild_single_data_route_file] Routes file (funkphp/data/data_single_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_data'],
        cli_get_prefix_code("data_singles_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Rebuilds the Single Middleware Routes Data file (funkphp/data/data_middleware_routes.php) based on valid array
function cli_rebuild_single_data_mw_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_data_mw_route_file] Single Route Middleware Routes File Array (funkphp/data/data_middleware_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['MIDDLEWARES'])) {
        cli_err_syntax("[cli_rebuild_single_data_mw_route_file] Single Route Middleware Routes File Array (funkphp/data/data_middleware_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['data']) || !is_writable($dirs['data'])) {
        cli_err_syntax("[cli_rebuild_single_data_mw_route_file] Routes directory (funkphp/data/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_middlewares_data']) && !is_writable($exactFiles['single_middlewares_data'])) {
        cli_err_syntax("[cli_rebuild_single_data_mw_route_file] Routes file (funkphp/data/data_middleware_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_middlewares_data'],
        cli_get_prefix_code("data_middleware_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Rebuilds the Single Middleware Routes Page file (funkphp/pages/page_single_routes.php) based on valid array
function cli_rebuild_single_page_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_page_route_file] Single Page Routes File Array (funkphp/pages/page_single_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['ROUTES'])) {
        cli_err_syntax("[cli_rebuild_single_page_route_file] Single Page Routes File Array (funkphp/pages/page_single_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['pages']) || !is_writable($dirs['pages'])) {
        cli_err_syntax("[cli_rebuild_single_page_route_file] Routes directory (funkphp/pages/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_page']) && !is_writable($exactFiles['single_page'])) {
        cli_err_syntax("[cli_rebuild_single_page_route_file] Routes file (funkphp/pages/page_single_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_page'],
        cli_get_prefix_code("page_singles_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Rebuilds the Single Middleware Routes Page file (funkphp/pages/page_middleware_routes.php) based on valid array
function cli_rebuild_single_page_mw_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_page_mw_route_file] Single Route Middleware Routes File Array (funkphp/pages/page_middleware_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['MIDDLEWARES'])) {
        cli_err_syntax("[cli_rebuild_single_page_mw_route_file] Single Route Middleware Routes File Array (funkphp/pages/page_middleware_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['pages']) || !is_writable($dirs['pages'])) {
        cli_err_syntax("[cli_rebuild_single_routes_mw_route_file] Routes directory (funkphp/pages/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_middlewares_page']) && !is_writable($exactFiles['single_middlewares_page'])) {
        cli_err_syntax("[cli_rebuild_single_routes_mw_route_file] Routes file (funkphp/pages/page_middleware_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_middlewares_page'],
        cli_get_prefix_code("page_middleware_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Check if Routes Data Handler in handlers/D/ exists
function cli_d_handler_exists($fileName): bool
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
    if (file_exists($dirs['handlers_data'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Routes Page Handler in handlers/P/ exists
function cli_p_handler_exists($fileName): bool
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
    if (file_exists($dirs['handlers_pages'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Route Middleware Handler in middlewares/R/ exists
function cli_mw_r_handler_exists($fileName): bool
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
    if (file_exists($dirs['middlewares_routes'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Data Middleware Handler in middlewares/D/ exists
function cli_mw_d_handler_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_mw_d_handler_exists] Middleware Data Handler File name must be a non-empty string!");
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in middlewares/D/ folder, false otherwise
    if (file_exists($dirs['middlewares_data'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if Page Middleware Handler in middlewares/P/ exists
function cli_mw_p_handler_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_mw_p_handler_exists] Middleware Page Handler File name must be a non-empty string!");
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in middlewares/P/ folder, false otherwise
    if (file_exists($dirs['middlewares_pages'] . $fileName)) {
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
        $keys = array_keys($middlewareRoutes) ?? [];

        // The way we insert "|" to signify a middleware is to just go through all segments for each key
        // and when we are at the last segment that is the node we insert "|" and then we move on to key.
        foreach ($keys as $key) {
            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/") {
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
            if (!isset($currentNode['|'])) {
                $currentNode['|'] = [];
            }
        }
    };

    // First add the single routes to the compiled trie
    $compiledTrie['GET'] = $addMethods($GETSingles);
    $compiledTrie['POST'] = $addMethods($POSTSingles);
    $compiledTrie['PUT'] = $addMethods($PUTSingles);
    $compiledTrie['DELETE'] = $addMethods($DELETESingles);

    // Then add the middlewares to the compiled trie and return it
    $addMiddlewareRoutes($developerMiddlewareRoutes["GET"] ?? [], $compiledTrie['GET']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["POST"] ?? [], $compiledTrie['POST']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["PUT"] ?? [], $compiledTrie['PUT']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["DELETE"] ?? [], $compiledTrie['DELETE']);

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
        echo "\033[31m[FunkCLI - ERROR]: Compiling Trie Route FAILED: \"$outputDestination\"!\n\033[0m";
    } else {
        echo "\033[32m[FunkCLI - SUCCESS]: Compiled Trie Route: \"$outputDestination\"!\n\033[0m";
    }
}

// Audit Developer's Defined Routes
function cli_audit_developer_routes(array $developerSingleRoutes, array $developerMiddlewareRoutes): array
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    // Both arrays must be non-empty arrays
    if (!is_array($developerSingleRoutes)) {
        echo "[ERROR]: '\$developerSingleRoutes' Must be a non-empty array!\n";
        exit;
    } elseif (!is_array($developerMiddlewareRoutes)) {
        echo "[ERROR]: '\$developerMiddlewareRoutes' Must be a non-empty array!\n";
        exit;
    }
    if (empty($developerSingleRoutes)) {
        echo "[ERROR]: '\$developerSingleRoutes' be a non-empty array!\n";
        exit;
    } else if (empty($developerMiddlewareRoutes)) {
        echo "[ERROR]: '\$developerMiddlewareRoutes' be a non-empty array!\n";
        exit;
    }

    // Prepare result variable
    $auditResult = [];

    return $auditResult;
}

// Convert PHP array() syntax to simplified [] syntax
function cli_convert_array_to_simple_syntax(array $array): string | null | array
{
    // Must be non-empty array
    if (!is_array($array)) {
        echo "[ERROR]: Must be a non-empty array!\n";
        exit;
    }

    // Check if the array is empty
    if (empty($array)) {
        echo "[ERROR]: Must be a non-empty array!\n";
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
                cli_err_syntax("Output file failed to write: $outputFilePath!");
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
    $backupRoutesMWPath = $dirs['backups_routes_mw'];
    $backupDataMWPath = $dirs['backups_data_mw'];
    $backupPageMWPath = $dirs['backups_pages_mw'];

    // Middleware folders
    $middlewareRoutesPath = $dirs['middlewares_routes'];
    $middlewareDataPath = $dirs['middlewares_data'];
    $middlewarePagePath = $dirs['middlewares_pages'];

    // Single Route Routes (including Middlewares)
    $oldTrouteRouteFile = $exactFiles['troute_route'];
    $oldSingleRouteRouteFile = $exactFiles['single_routes'];
    $oldSingleMiddlwaresRouteFile = $exactFiles['single_middlewares'];

    // Single Data Routes (including Middlewares)
    $oldTrouteDataFile = $exactFiles['troute_data'];
    $oldSingleDataFile = $exactFiles['single_data'];
    $oldSingleMiddlwaresDataFile = $exactFiles['single_middlewares_data'];

    // Single Pages Routes (including Middlewares)
    $oldTroutePageFile = $exactFiles['troute_page'];
    $oldSinglePageFile = $exactFiles['single_page'];
    $oldSingleMiddlwaresPageFile = $exactFiles['single_middlewares_page'];

    // Now backup the old route files based on provided $filesString
    // Loop through each file in the array and backup it
    foreach ($arrayOfFilesToBackup as $fileString) {
        if ($fileString === "troute_route") {
            // Routes
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_BACKUPS']) {
                cli_backup_file_until_success($backupCompiledPath . "troute_route", ".php", $oldTrouteRouteFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "troute_route", ".php", $oldTrouteRouteFile);
            }
            continue;
        }
        if ($fileString === "route_single_routes") {
            // Single Route Routes & Middlewares
            if ($settings['ALWAYS_BACKUP_IN']['ROUTES_IN_BACKUPS']) {
                cli_backup_file_until_success($backupRouteRoutePath . "route_single_routes", ".php", $oldSingleRouteRouteFile);
                cli_backup_file_until_success($backupRouteRoutePath . "route_middleware_routes", ".php", $oldSingleMiddlwaresRouteFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['ROUTES_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "route_single_routes", ".php", $oldSingleRouteRouteFile);
                cli_backup_file_until_success($backupFinalsPath . "route_middleware_routes", ".php", $oldSingleMiddlwaresRouteFile);
            }
            continue;
        }

        if ($fileString === "troute_data") {
            // Data
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_BACKUPS']) {
                cli_backup_file_until_success($backupCompiledPath . "troute_data", ".php", $oldTrouteDataFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "troute_data", ".php", $oldTrouteDataFile);
            }
            continue;
        }
        if ($fileString === "data_single_routes") {
            // Single Data Routes & Middlewares
            if ($settings['ALWAYS_BACKUP_IN']['DATA_IN_BACKUPS']) {
                cli_backup_file_until_success($backupDataRoutePath . "data_single_routes", ".php", $oldSingleDataFile);
                cli_backup_file_until_success($backupDataRoutePath . "data_middleware_routes", ".php", $oldSingleMiddlwaresDataFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['DATA_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "data_single_routes", ".php", $oldSingleDataFile);
                cli_backup_file_until_success($backupFinalsPath . "data_middleware_routes", ".php", $oldSingleMiddlwaresDataFile);
            }
            continue;
        }
        if ($fileString === "troute_page") {
            // Pages
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_BACKUPS']) {
                cli_backup_file_until_success($backupCompiledPath . "troute_page", ".php", $oldTroutePageFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "troute_page", ".php", $oldTroutePageFile);
            }
            continue;
        }
        if ($fileString === "page_single_routes") {
            // Single Page Routes & Middlewares
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_BACKUPS']) {
                cli_backup_file_until_success($backupPageRoutePath . "page_single_routes", ".php", $oldSinglePageFile);
                cli_backup_file_until_success($backupPageRoutePath . "page_middleware_routes", ".php", $oldSingleMiddlwaresPageFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_FINAL_BACKUPS']) {

                cli_backup_file_until_success($backupFinalsPath . "page_single_routes", ".php", $oldSinglePageFile);
                cli_backup_file_until_success($backupFinalsPath . "page_middleware_routes", ".php", $oldSingleMiddlwaresPageFile);
            }
            continue;
        }
        if ($fileString === "") {

            continue;
        }
        if ($fileString === "") {

            continue;
        }
        if ($fileString === "") {

            continue;
        }
        if ($fileString === "") {

            continue;
        }
    }
}

// Delete a Single Route from "routes" folder
function cli_delete_a_single_routes_route()
{
    global
        $argv, $dirs, $exactFiles,
        $settings,
        $singleRoutesRoute,
        $middlewareRoutesRoute;
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
                "troute_route",
                "route_single_routes",
            ]
        );
        // Then we unset() each matched route
        unset($singleRoutesRoute['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Route \"$method$validRoute\" from Single Routes Route \"funkphp/routes/route_single_routes.php\"!");

        // Then we rebuild and recompile Routes
        cli_rebuild_single_routes_route_file($singleRoutesRoute);
        $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
        cli_output_compiled_routes($compiledRouteRoutes, "troute_route");

        cli_success("Deleted Single Route \"$method$validRoute\" from Single Route file!");
    }
    // When one ore more is missing, we do not go ahead with deletion
    // since this function is meant to delete all three at once!
    else {
        cli_err_syntax("Single Route: \"$method$validRoute\" does not exist!");
    }
}

function cli_delete_a_single_data_route()
{
    global
        $argv, $dirs, $exactFiles,
        $settings,
        $singleRoutesData,
        $middlewareRoutesData;
    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $deleteRoute = trim(strtolower($argv[3]));
    $oldRoute = $deleteRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($deleteRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that provided route exists
    if (
        isset($singleRoutesData['ROUTES'][$method][$validRoute])
    ) {
        // First backup all associated route files if settings allow it
        cli_backup_batch(
            [
                "troute_data",
                "data_single_routes",
            ]
        );
        // Then we unset() each matched route
        unset($singleRoutesData['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Route \"$method$validRoute\" from Single Data Routes \"funkphp/data/data_single_routes.php\"!");

        // Then we rebuild and recompile - Data
        cli_rebuild_single_data_route_file($singleRoutesData);
        $compiledRouteData = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
        cli_output_compiled_routes($compiledRouteData, "troute_data");

        cli_success("Deleted Single Data Route \"$method$validRoute\" from Single Data Route file!");
    }
    // When one ore more is missing, we do not go ahead with deletion
    // since this function is meant to delete all three at once!
    else {
        cli_err_syntax("Single Data Route: \"$method$validRoute\" does not exist!");
    }
}

// Delete a Single Page Route from "pages" folder
function cli_delete_a_single_page_route()
{
    global
        $argv, $dirs, $exactFiles,
        $settings,
        $singleRoutesPage,
        $middlewareRoutesPage;
    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $deleteRoute = trim(strtolower($argv[3]));
    $oldRoute = $deleteRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($deleteRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that provided route exists
    if (
        isset($singleRoutesPage['ROUTES'][$method][$validRoute])
    ) {
        // First backup all associated route files if settings allow it
        cli_backup_batch(
            [
                "troute_page",
                "page_single_routes",
            ]
        );
        // Then we unset() each matched route
        unset($singleRoutesPage['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Route \"$method$validRoute\" from Single Page Routes \"funkphp/pages/page_single_routes.php\"!");

        // Then we rebuild and recompile Pages
        cli_rebuild_single_page_route_file($singleRoutesPage);
        $compiledRoutePage = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
        cli_output_compiled_routes($compiledRoutePage, "troute_page");

        cli_success("Deleted Single Page Route \"$method$validRoute\" from Single Page Route file!");
    }
    // When one ore more is missing, we do not go ahead with deletion
    // since this function is meant to delete all three at once!
    else {
        cli_err_syntax("Single Page Route: \"$method$validRoute\" does not exist!");
    }
}

// Delete a single route for /routes/, /data/ AND /pages/ folder
function cli_delete_a_single_all_routes()
{
    global
        $argv, $dirs, $exactFiles,
        $settings,
        $singleRoutesRoute,
        $singleRoutesData,
        $singleRoutesPage,
        $middlewareRoutesRoute,
        $middlewareRoutesData,
        $middlewareRoutesPage;
    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $deleteRoute = trim(strtolower($argv[3]));
    $oldRoute = $deleteRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($deleteRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that provided route exists in all 3 main route files
    if (
        isset($singleRoutesRoute['ROUTES'][$method][$validRoute])
        && isset($singleRoutesData['ROUTES'][$method][$validRoute])
        && isset($singleRoutesPage['ROUTES'][$method][$validRoute])
    ) {
        // First backup all associated route files if settings allow it
        cli_backup_batch(
            [
                "troute_route",
                "route_single_routes",
                "troute_data",
                "data_single_routes",
                "troute_page",
                "page_single_routes",
            ]
        );
        // Then we unset() each matched route in all 3 main route files
        unset($singleRoutesRoute['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Route \"$method$validRoute\" from Single Routes Route \"funkphp/routes/route_single_routes.php\"!");
        unset($singleRoutesData['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Route \"$method$validRoute\" from Single Data Routes \"funkphp/data/data_single_routes.php\"!");
        unset($singleRoutesPage['ROUTES'][$method][$validRoute]);
        cli_success_without_exit("Deleted Route \"$method$validRoute\" from Single Page Routes \"funkphp/pages/page_single_routes.php\"!");

        // Then we rebuild and recompile all 3 main route files!
        // Routes
        cli_rebuild_single_routes_route_file($singleRoutesRoute);
        $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
        cli_output_compiled_routes($compiledRouteRoutes, "troute_route");

        // Data
        cli_rebuild_single_data_route_file($singleRoutesData);
        $compiledRouteData = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
        cli_output_compiled_routes($compiledRouteData, "troute_data");

        // Pages
        cli_rebuild_single_page_route_file($singleRoutesPage);
        $compiledRoutePage = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
        cli_output_compiled_routes($compiledRoutePage, "troute_page");

        cli_success("Deleted Single Route \"$method$validRoute\" from all 3 Main Route files!");
    }
    // When one ore more is missing, we do not go ahead with deletion
    // since this function is meant to delete all three at once!
    else {
        cli_err_syntax("\"$method$validRoute\" does not exist in all 3 main route files. Either make sure all three exists or use the other delete functions to delete each separately!");
    }
}

// Add one or more routes from array of string values!
function cli_add_route_batch($arrayOfRoutesToAdd)
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute,
        $singleRoutesPage,
        $singleRoutesData,
        $middlewareRoutesRoute,
        $middlewareRoutesData,
        $middlewareRoutesPage;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nadd [all_routes|only_route|only_data_route|only_page_route] [Method/route] [handler]\nExample: 'add all_routes GET/users/:id getSingleUser'");
    }

    // Prepare handlers folders
    $handlersR = $dirs['handlers_routes'];
    $handlersD = $dirs['handlers_data'];
    $handlersP = $dirs['handlers_pages'];

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[4])) {
        cli_err_syntax("\"{$argv[4]}\" - Handler name must be a lowercased string containing only letters, numbers and underscores!");
    }

    foreach ($arrayOfRoutesToAdd as $routeToAdd) {
        if ($routeToAdd === "all_routes") {
            // Check Route is not used currently in ALL 3 Main Single Route Files!
            if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]) ?? null) {
                cli_err_syntax("\"$validRoute\" already exists in $method/Single Route Routes!");
            } elseif (isset($singleRoutesData['ROUTES'][$method][$validRoute]) ?? null) {
                cli_err_syntax("\"$validRoute\" already exists in $method/Single Data Routes!");
            } elseif (isset($singleRoutesPage['ROUTES'][$method][$validRoute]) ?? null) {
                cli_err_syntax("\"$validRoute\" already exists in $method/Single Page Routes!");
            }

            // ADDING ROUTES ROUTE
            $uniqueR = cli_get_unique_filename_for_dir($handlersR, $argv[4]);
            $handlerR = explode(".", $uniqueR)[0];
            $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
                'handler' => $handlerR,
            ];
            ksort($singleRoutesRoute['ROUTES'][$method]);
            $outputHandlerRoute = file_put_contents(
                $handlersR . $uniqueR,
                "<?php\n// Route Handler for Route Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
            );
            if ($outputHandlerRoute) {
                cli_success_without_exit("Added Handler \"$handlerR\" in \"funkphp/handlers/R/$uniqueR\"!");
            }
            $outputRouteSingleFile = file_put_contents(
                $exactFiles['single_routes'],
                cli_get_prefix_code("route_singles_routes_start")
                    . cli_convert_array_to_simple_syntax($singleRoutesRoute)
            );
            if ($outputRouteSingleFile) {
                cli_success_without_exit("Added Route \"$method$validRoute\" to Single Routes Route \"funkphp/routes/route_single_routes.php\" with handler \"$handlerR\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");

            // ADD DATA ROUTE
            $uniqueD = cli_get_unique_filename_for_dir($handlersD, $argv[4]);
            $handlerD = explode(".", $uniqueD)[0];
            $singleRoutesData['ROUTES'][$method][$validRoute] = [
                'handler' => $handlerD,
            ];
            ksort($singleRoutesData['ROUTES'][$method]);
            $outputHandlerData = file_put_contents(
                $handlersD . $uniqueD,
                "<?php\n// Route Handler for Data Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
            );
            if ($outputHandlerData) {
                cli_success_without_exit("Added Handler \"$handlerD\" in \"funkphp/handlers/D/$uniqueD\"!");
            }
            $outputDataSingleFile = file_put_contents(
                $exactFiles['single_data'],
                cli_get_prefix_code("data_singles_routes_start")
                    . cli_convert_array_to_simple_syntax($singleRoutesData)
            );
            if ($outputDataSingleFile) {
                cli_success_without_exit("Added Route \"$method$validRoute\" to Single Routes Data \"funkphp/data/data_single_routes.php\" with handler \"$handlerD\"!");
            }
            $compiledDataRoutes = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
            cli_output_compiled_routes($compiledDataRoutes, "troute_data");

            // ADD PAGE ROUTE
            $uniqueP = cli_get_unique_filename_for_dir($handlersP, $argv[4]);
            $handlerP = explode(".", $uniqueP)[0];
            $singleRoutesPage['ROUTES'][$method][$validRoute] = [
                'handler' => $handlerP,
            ];
            ksort($singleRoutesPage['ROUTES'][$method]);
            $outputHandlerPage = file_put_contents(
                $handlersP . $uniqueP,
                "<?php\n// Page Handler for Page Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
            );
            if ($outputHandlerPage) {
                cli_success_without_exit("Added Page Handler \"$handlerP\" in \"funkphp/handlers/P/$uniqueP\"!");
            }
            $outputPageSingleFile = file_put_contents(
                $exactFiles['single_page'],
                cli_get_prefix_code("page_singles_routes_start")
                    . cli_convert_array_to_simple_syntax($singleRoutesPage)
            );
            if ($outputPageSingleFile) {
                cli_success_without_exit("Added Route \"$method$validRoute\" to Single Routes Page \"funkphp/pages/page_single_routes.php\" with Page Handler \"$handlerP\"!");
            }
            $compiledPageRoutes = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
            cli_output_compiled_routes($compiledPageRoutes, "troute_page");
        }
        // Adding only a Route Route to the Route Route File
        if ($routeToAdd === "only_route") {
            // Check Route is not used currently in ALL 3 Main Single Route Files!
            if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]) ?? null) {
                cli_err_syntax("\"$validRoute\" already exists in $method/Single Route Routes!");
            }
            $uniqueR = cli_get_unique_filename_for_dir($handlersR, $argv[4]);
            $handlerR = explode(".", $uniqueR)[0];
            $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
                'handler' => $handlerR,
            ];
            ksort($singleRoutesRoute['ROUTES'][$method]);
            $outputHandlerRoute = file_put_contents(
                $handlersR . $uniqueR,
                "<?php\n// Route Handler for Route Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
            );
            if ($outputHandlerRoute) {
                cli_success_without_exit("Added Handler \"$handlerR\" in \"funkphp/handlers/R/$uniqueR\"!");
            }
            $outputRouteSingleFile = file_put_contents(
                $exactFiles['single_routes'],
                cli_get_prefix_code("route_singles_routes_start")
                    . cli_convert_array_to_simple_syntax($singleRoutesRoute)
            );
            if ($outputRouteSingleFile) {
                cli_success_without_exit("Added Route \"$method$validRoute\" to Single Routes Route \"funkphp/routes/route_single_routes.php\" with handler \"$handlerR\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
        }
        // Adding only a Data Route to the Data Route File
        if ($routeToAdd === "only_data") {
            // Check Route is not used currently in ALL 3 Main Single Route Files!
            if (isset($singleRoutesData['ROUTES'][$method][$validRoute]) ?? null) {
                cli_err_syntax("\"$validRoute\" already exists in $method/Single Route Routes!");
            }
            $uniqueD = cli_get_unique_filename_for_dir($handlersD, $argv[4]);
            $handlerD = explode(".", $uniqueD)[0];
            $singleRoutesData['ROUTES'][$method][$validRoute] = [
                'handler' => $handlerD,
            ];
            ksort($singleRoutesData['ROUTES'][$method]);
            $outputHandlerData = file_put_contents(
                $handlersD . $uniqueD,
                "<?php\n// Route Handler for Data Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
            );
            if ($outputHandlerData) {
                cli_success_without_exit("Added Handler \"$handlerD\" in \"funkphp/handlers/D/$uniqueD\"!");
            }
            $outputDataSingleFile = file_put_contents(
                $exactFiles['single_data'],
                cli_get_prefix_code("data_singles_routes_start")
                    . cli_convert_array_to_simple_syntax($singleRoutesData)
            );
            if ($outputDataSingleFile) {
                cli_success_without_exit("Added Route \"$method$validRoute\" to Single Routes Data \"funkphp/data/data_single_routes.php\" with handler \"$handlerD\"!");
            }
            $compiledDataRoutes = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
            cli_output_compiled_routes($compiledDataRoutes, "troute_data");
        }
        // Adding only a Page Route to the Page Route File
        if ($routeToAdd === "only_page") {
            // Check Route is not used currently in ALL 3 Main Single Route Files!
            if (isset($singleRoutesPage['ROUTES'][$method][$validRoute]) ?? null) {
                cli_err_syntax("\"$validRoute\" already exists in $method/Single Route Routes!");
            }
            $uniqueP = cli_get_unique_filename_for_dir($handlersP, $argv[4]);
            $handlerP = explode(".", $uniqueP)[0];
            $singleRoutesPage['ROUTES'][$method][$validRoute] = [
                'handler' => $handlerP,
            ];
            ksort($singleRoutesPage['ROUTES'][$method]);
            $outputHandlerPage = file_put_contents(
                $handlersP . $uniqueP,
                "<?php\n// Page Handler for Page Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
            );
            if ($outputHandlerPage) {
                cli_success_without_exit("Added Page Handler \"$handlerP\" in \"funkphp/handlers/P/$uniqueP\"!");
            }
            $outputPageSingleFile = file_put_contents(
                $exactFiles['single_page'],
                cli_get_prefix_code("page_singles_routes_start")
                    . cli_convert_array_to_simple_syntax($singleRoutesPage)
            );
            if ($outputPageSingleFile) {
                cli_success_without_exit("Added Route \"$method$validRoute\" to Single Routes Page \"funkphp/pages/page_single_routes.php\" with Page Handler \"$handlerP\"!");
            }
            $compiledPageRoutes = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
            cli_output_compiled_routes($compiledPageRoutes, "troute_page");
        }
    }
}

// Batched function of adding and outputting middlewares to the middleware route files
function cli_add_middlewares_batch($arrayOfMiddlewaresToAdd)
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute,
        $singleRoutesPage,
        $singleRoutesData,
        $middlewareRoutesRoute,
        $middlewareRoutesData,
        $middlewareRoutesPage;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nadd [mw_all|mw_route|mw_data|mw_page] [Method/route] [Middleware_handler]\nExample: 'add mw_all GET/users/:id getSingleUser'");
    }

    // Prepare handlers folders
    $handlersR = $dirs['middlewares_routes'];
    $handlersD = $dirs['middlewares_data'];
    $handlersP = $dirs['middlewares_pages'];

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[4])) {
        cli_err_syntax("\"{$argv[4]}\" - Middleware Handler name must be a lowercased string containing only letters, numbers and underscores!");
    }

    foreach ($arrayOfMiddlewaresToAdd as $middlewareString) {
        if ($middlewareString === "mw_all") {
            // We loop routes to see if any of the routes at least starts with the middleware route
            // otherwise we cannot add it because it wouldn't match any valid/navigable route!
            $singleExist = false;
            foreach ($singleRoutesRoute['ROUTES'][$method] as $routeSingle => $val) {
                if (str_starts_with($routeSingle, $validRoute)) {
                    $singleExist = true;
                    break;
                }
            }
            if (!$singleExist) {
                cli_err_syntax("Route \"$validRoute\" does not exist in $method/Single Route Routes! (funkphp/routes/route_single_routes.php) Add that first!");
            }
            $singleExist = false;
            foreach ($singleRoutesData['ROUTES'][$method] as $routeSingle => $val) {
                if (str_starts_with($routeSingle, $validRoute)) {
                    $singleExist = true;
                    break;
                }
            }
            if (!$singleExist) {
                cli_err_syntax("Route \"$validRoute\" does not exist in $method/Single Data Routes! (funkphp/data/data_single_routes.php) Add that first!");
            }
            $singleExist = false;
            foreach ($singleRoutesPage['ROUTES'][$method] as $routeSingle => $val) {
                if (str_starts_with($routeSingle, $validRoute)) {
                    $singleExist = true;
                    break;
                }
            }
            if (!$singleExist) {
                cli_err_syntax("Route \"$validRoute\" does not exist in $method/Single Page Routes! (funkphp/pages/page_single_routes.php) Add that first!");
            }

            // For each Route, Data, Page Middleware Route, we first check that the route exists.
            // And then we chekc if the handler is a string or an array. If a string, we check if exact
            // handler name already exists in the route. If an array, we check if the handler exists in array.
            // Routes Route
            if (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])) {
                if (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    if (is_string($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if ($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"] === $argv[4]) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Route Routes!");
                        }
                    } elseif (is_array($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if (in_array($argv[4], $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Route Routes!");
                        }
                    }
                }
            }
            // Routes Data
            if (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])) {
                if (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    if (is_string($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if ($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"] === $argv[4]) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Data Routes!");
                        }
                    } elseif (is_array($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if (in_array($argv[4], $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Data Routes!");
                        }
                    }
                }
            }
            // Routes Page
            if (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])) {
                if (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    if (is_string($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if ($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"] === $argv[4]) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Page Routes!");
                        }
                    } elseif (is_array($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if (in_array($argv[4], $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Page Routes!");
                        }
                    }
                }
            }

            // CREATE MIDDLEWARE HANDLER FOR ROUTE ROUTE
            // Create Middleware Route Handler if it does not exist
            if (!cli_mw_r_handler_exists($argv[4])) {
                $outputHandlerRoute = file_put_contents(
                    $handlersR . $argv[4] . ".php",
                    "<?php\n// Middleware Handler for Route Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
                );
                if ($outputHandlerRoute) {
                    cli_success_without_exit("Added Middleware Route Handler \"{$argv[4]}\" in \"funkphp/middlewares/R/{$argv[4]}.php\"!");
                }
            } else {
                cli_info_without_exit("Middleware Route Handler \"{$argv[4]}\" already exists in \"funkphp/middlewares/R/{$argv[4]}.php\"!");
            }

            // ADDING MIDDLEWARE HANDLER TO THE CORRECT MIDDLEWARE METHOD/ROUTE!
            // Three scenarios: 1) Handler doesn't exist, so we just add it as the first string
            // 2) Handler exists as a string, so we convert it to an array and add the new handler to the array
            // 3) Handler exists as an array, so we just add the new handler to the array
            // Middleware Route and its handler don't exist, so just add it:
            if (
                !isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])
                || (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])
                    && !isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"]))
            ) {
                $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute] = [
                    'handler' => $argv[4],
                ];
            }  //
            elseif (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])) {
                if (is_string($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"] = [
                        $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"],
                        $argv[4],
                    ];
                } elseif (is_array($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"][] = $argv[4];
                }
            }

            // Finally sort the array by keys, recompile and output the updated Single Route & Middleware files!
            ksort($middlewareRoutesRoute['MIDDLEWARES'][$method]);
            $outputRouteSingleMiddlewareFile = file_put_contents(
                $exactFiles['single_middlewares'],
                cli_get_prefix_code("route_middleware_routes_start")
                    . cli_convert_array_to_simple_syntax($middlewareRoutesRoute)
            );
            if ($outputRouteSingleMiddlewareFile) {
                cli_success_without_exit("Added Middleware Route \"$method$validRoute\" to Single Middleware Routes Route \"funkphp/routes/route_middleware_routes.php\" with handler \"{$argv[4]}\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");


            // CREATE MIDDLEWARE HANDLER FOR DATA ROUTE
            // Create Middleware Route Handler if it does not exist
            if (!cli_mw_d_handler_exists($argv[4])) {
                $outputHandlerRoute = file_put_contents(
                    $handlersD . $argv[4] . ".php",
                    "<?php\n// Middleware Handler for Data Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
                );
                if ($outputHandlerRoute) {
                    cli_success_without_exit("Added Middleware Route Handler \"{$argv[4]}\" in \"funkphp/middlewares/D/{$argv[4]}.php\"!");
                }
            } else {
                cli_info_without_exit("Middleware Route Handler \"{$argv[4]}\" already exists in \"funkphp/middlewares/D/{$argv[4]}.php\"!");
            }
            if (
                !isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])
                || (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])
                    && !isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"]))
            ) {
                $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute] = [
                    'handler' => $argv[4],
                ];
            }  //
            elseif (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])) {
                if (is_string($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"] = [
                        $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"],
                        $argv[4],
                    ];
                } elseif (is_array($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"][] = $argv[4];
                }
            }

            // Finally sort the array by keys, recompile and output the updated Single Route & Middleware files!
            ksort($middlewareRoutesData['MIDDLEWARES'][$method]);
            $outputRouteSingleMiddlewareFile = file_put_contents(
                $exactFiles['single_middlewares_data'],
                cli_get_prefix_code("data_middleware_routes_start")
                    . cli_convert_array_to_simple_syntax($middlewareRoutesData)
            );
            if ($outputRouteSingleMiddlewareFile) {
                cli_success_without_exit("Added Middleware Route \"$method$validRoute\" to Single Middleware Routes Data \"funkphp/data/data_middleware_routes.php\" with handler \"{$argv[4]}\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_data");


            // CREATE MIDDLEWARE HANDLER FOR PAGE ROUTE
            // Create Middleware Route Handler if it does not exist
            if (!cli_mw_p_handler_exists($argv[4])) {
                $outputHandlerRoute = file_put_contents(
                    $handlersP . $argv[4] . ".php",
                    "<?php\n// Middleware Handler for Page Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
                );
                if ($outputHandlerRoute) {
                    cli_success_without_exit("Added Middleware Route Handler \"{$argv[4]}\" in \"funkphp/middlewares/P/{$argv[4]}.php\"!");
                }
            } else {
                cli_info_without_exit("Middleware Route Handler \"{$argv[4]}\" already exists in \"funkphp/middlewares/P/{$argv[4]}.php\"!");
            }
            if (
                !isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])
                || (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])
                    && !isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"]))
            ) {
                $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute] = [
                    'handler' => $argv[4],
                ];
            }  //
            elseif (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])) {
                if (is_string($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"] = [
                        $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"],
                        $argv[4],
                    ];
                } elseif (is_array($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"][] = $argv[4];
                }
            }

            // Finally sort the array by keys, recompile and output the updated Single Route & Middleware files!
            ksort($middlewareRoutesPage['MIDDLEWARES'][$method]);
            $outputRouteSingleMiddlewareFile = file_put_contents(
                $exactFiles['single_middlewares_page'],
                cli_get_prefix_code("page_middleware_routes_start")
                    . cli_convert_array_to_simple_syntax($middlewareRoutesPage)
            );
            if ($outputRouteSingleMiddlewareFile) {
                cli_success_without_exit("Added Middleware Route \"$method$validRoute\" to Single Middleware Routes Page \"funkphp/pages/page_middleware_routes.php\" with handler \"{$argv[4]}\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_page");
            continue;
        }
        // When ONLY adding a middleware in the Middleware Route Routes file
        if ($middlewareString === "mw_route") {
            // At least one route in Single Route Routes file must start with the middleware route
            // otherwise we cannot add it because it wouldn't match any valid/navigable route!
            $singleExist = false;
            foreach ($singleRoutesRoute['ROUTES'][$method] as $routeSingle => $val) {
                if (str_starts_with($routeSingle, $validRoute)) {
                    $singleExist = true;
                    break;
                }
            }
            if (!$singleExist) {
                cli_err_syntax("Route \"$validRoute\" does not exist in $method/Single Route Routes! (funkphp/routes/route_single_routes.php) Add that first!");
            }
            // Check that handler is not already used in the Middlewares Route Routes file
            if (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])) {
                if (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    if (is_string($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if ($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"] === $argv[4]) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Route Routes!");
                        }
                    } elseif (is_array($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if (in_array($argv[4], $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Route Routes!");
                        }
                    }
                }
            }
            // Create Middleware Route Handler if it does not exist
            if (!cli_mw_r_handler_exists($argv[4])) {
                $outputHandlerRoute = file_put_contents(
                    $handlersR . $argv[4] . ".php",
                    "<?php\n// Middleware Handler for Route Route: $method/$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
                );
                if ($outputHandlerRoute) {
                    cli_success_without_exit("Added Middleware Route Handler \"{$argv[4]}\" in \"funkphp/middlewares/R/{$argv[4]}.php\"!");
                }
            } else {
                cli_info_without_exit("Middleware Route Handler \"{$argv[4]}\" already exists in \"funkphp/middlewares/R/{$argv[4]}.php\"!");
            }
            // ADDING MIDDLEWARE HANDLER TO THE CORRECT MIDDLEWARE METHOD/ROUTE!
            // Three scenarios: 1) Handler doesn't exist, so we just add it as the first string
            // 2) Handler exists as a string, so we convert it to an array and add the new handler to the array
            // 3) Handler exists as an array, so we just add the new handler to the array
            // Middleware Route and its handler don't exist, so just add it:
            if (
                !isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])
                || (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])
                    && !isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"]))
            ) {
                $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute] = [
                    'handler' => $argv[4],
                ];
            }  //
            elseif (isset($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute])) {
                if (is_string($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"] = [
                        $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"],
                        $argv[4],
                    ];
                } elseif (is_array($middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesRoute['MIDDLEWARES'][$method][$validRoute]["handler"][] = $argv[4];
                }
            }
            // Finally sort the array by keys, recompile and output the updated Single Route & Middleware files!
            ksort($middlewareRoutesRoute['MIDDLEWARES'][$method]);
            $outputRouteSingleMiddlewareFile = file_put_contents(
                $exactFiles['single_middlewares'],
                cli_get_prefix_code("route_middleware_routes_start")
                    . cli_convert_array_to_simple_syntax($middlewareRoutesRoute)
            );
            if ($outputRouteSingleMiddlewareFile) {
                cli_success_without_exit("Added Middleware Route \"$method$validRoute\" to Single Middleware Routes Route \"funkphp/routes/route_middleware_routes.php\" with handler \"{$argv[4]}\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
            continue;
        }
        // When ONLY adding a middleware in the Middleware Data Routes file
        if ($middlewareString === "mw_data") {
            // At least one route in Single Data Routes file must start with the middleware route
            // otherwise we cannot add it because it wouldn't match any valid/navigable route!
            $singleExist = false;
            foreach ($singleRoutesData['ROUTES'][$method] as $routeSingle => $val) {
                if (str_starts_with($routeSingle, $validRoute)) {
                    $singleExist = true;
                    break;
                }
            }
            if (!$singleExist) {
                cli_err_syntax("Route \"$validRoute\" does not exist in $method/Single Data Routes! (funkphp/data/data_single_routes.php) Add that first!");
            }
            // Check that handler is not already used in the Middlewares Data Routes file
            if (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])) {
                if (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    if (is_string($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if ($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"] === $argv[4]) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Data Routes!");
                        }
                    } elseif (is_array($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if (in_array($argv[4], $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Data Routes!");
                        }
                    }
                }
            }
            // Create Middleware Route Handler if it does not exist
            if (!cli_mw_d_handler_exists($argv[4])) {
                $outputHandlerRoute = file_put_contents(
                    $handlersD . $argv[4] . ".php",
                    "<?php\n// Middleware Handler for Data Route: $method/$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
                );
                if ($outputHandlerRoute) {
                    cli_success_without_exit("Added Middleware Route Handler \"{$argv[4]}\" in \"funkphp/middlewares/D/{$argv[4]}.php\"!");
                }
            } else {
                cli_info_without_exit("Middleware Route Handler \"{$argv[4]}\" already exists in \"funkphp/middlewares/D/{$argv[4]}.php\"!");
            }
            // ADDING MIDDLEWARE HANDLER TO THE CORRECT MIDDLEWARE METHOD/ROUTE!
            // Three scenarios: 1) Handler doesn't exist, so we just add it as the first string
            // 2) Handler exists as a string, so we convert it to an array and add the new handler to the array
            // 3) Handler exists as an array, so we just add the new handler to the array
            // Middleware Route and its handler don't exist, so just add it:
            if (
                !isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])
                || (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])
                    && !isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"]))
            ) {
                $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute] = [
                    'handler' => $argv[4],
                ];
            }  //
            elseif (isset($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute])) {
                if (is_string($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"] = [
                        $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"],
                        $argv[4],
                    ];
                } elseif (is_array($middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesData['MIDDLEWARES'][$method][$validRoute]["handler"][] = $argv[4];
                }
            }
            // Finally sort the array by keys, recompile and output the updated Single Route & Middleware files!
            ksort($middlewareRoutesData['MIDDLEWARES'][$method]);
            $outputRouteSingleMiddlewareFile = file_put_contents(
                $exactFiles['single_middlewares_data'],
                cli_get_prefix_code("data_middleware_routes_start")
                    . cli_convert_array_to_simple_syntax($middlewareRoutesData)
            );
            if ($outputRouteSingleMiddlewareFile) {
                cli_success_without_exit("Added Middleware Route \"$method$validRoute\" to Single Middleware Data Route \"funkphp/data/data_middleware_routes.php\" with handler \"{$argv[4]}\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_data");
            continue;
        }
        // When ONLY adding a middleware in the Middleware Page Routes file
        if ($middlewareString === "mw_page") {
            // At least one route in Single Page Routes file must start with the middleware route
            // otherwise we cannot add it because it wouldn't match any valid/navigable route!
            $singleExist = false;
            foreach ($singleRoutesPage['ROUTES'][$method] as $routeSingle => $val) {
                if (str_starts_with($routeSingle, $validRoute)) {
                    $singleExist = true;
                    break;
                }
            }
            if (!$singleExist) {
                cli_err_syntax("Route \"$validRoute\" does not exist in $method/Single Page Routes! (funkphp/pages/page_single_routes.php) Add that first!");
            }
            // Check that handler is not already used in the Middlewares Page Routes file
            if (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])) {
                if (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    if (is_string($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if ($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"] === $argv[4]) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Page Routes!");
                        }
                    } elseif (is_array($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                        if (in_array($argv[4], $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                            cli_err_syntax("Handler \"{$argv[4]}\" for \"$validRoute\" already exists in $method/Single Middleware Page Routes!");
                        }
                    }
                }
            }

            // CREATE MIDDLEWARE HANDLER FOR PAGE ROUTE
            // Create Middleware Route Handler if it does not exist
            if (!cli_mw_p_handler_exists($argv[4])) {
                $outputHandlerRoute = file_put_contents(
                    $handlersP . $argv[4] . ".php",
                    "<?php\n// Middleware Handler for Page Route: $method$validRoute\n// File created in FunkCLI!\n\nreturn function (&\$c) { };\n?>"
                );
                if ($outputHandlerRoute) {
                    cli_success_without_exit("Added Middleware Route Handler \"{$argv[4]}\" in \"funkphp/middlewares/P/{$argv[4]}.php\"!");
                }
            } else {
                cli_info_without_exit("Middleware Route Handler \"{$argv[4]}\" already exists in \"funkphp/middlewares/P/{$argv[4]}.php\"!");
            }
            if (
                !isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])
                || (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])
                    && !isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"]))
            ) {
                $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute] = [
                    'handler' => $argv[4],
                ];
            }  //
            elseif (isset($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute])) {
                if (is_string($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"] = [
                        $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"],
                        $argv[4],
                    ];
                } elseif (is_array($middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"])) {
                    $middlewareRoutesPage['MIDDLEWARES'][$method][$validRoute]["handler"][] = $argv[4];
                }
            }

            // Finally sort the array by keys, recompile and output the updated Single Route & Middleware files!
            ksort($middlewareRoutesPage['MIDDLEWARES'][$method]);
            $outputRouteSingleMiddlewareFile = file_put_contents(
                $exactFiles['single_middlewares_page'],
                cli_get_prefix_code("page_middleware_routes_start")
                    . cli_convert_array_to_simple_syntax($middlewareRoutesPage)
            );
            if ($outputRouteSingleMiddlewareFile) {
                cli_success_without_exit("Added Middleware Route \"$method$validRoute\" to Single Middleware Routes Page \"funkphp/pages/page_middleware_routes.php\" with handler \"{$argv[4]}\"!");
            }
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_page");
            continue;
        }
    }
}

// Batched function of compiling and outputting routing files
function cli_compile_batch($arrayOfRoutesToCompileAndOutput)
{
    // Check if the array is a non-empty array
    if (!is_array($arrayOfRoutesToCompileAndOutput) || empty($arrayOfRoutesToCompileAndOutput)) {
        cli_err_syntax("Array of Routing Files to Compile & Output must be a non-empty array!");
    }

    // Load global routing files
    global $singleRoutesRoute, $singleRoutesData,
        $singleRoutesPage, $middlewareRoutesRoute,
        $middlewareRoutesData, $middlewareRoutesPage;

    foreach ($arrayOfRoutesToCompileAndOutput as $routeString) {
        if ($routeString === "troute_route") {
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $middlewareRoutesRoute['MIDDLEWARES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
            continue;
        }
        if ($routeString === "troute_data") {
            $compiledDataRoutes = cli_build_compiled_routes($singleRoutesData['ROUTES'], $middlewareRoutesData['MIDDLEWARES']);
            cli_output_compiled_routes($compiledDataRoutes, "troute_data");
            continue;
        }
        if ($routeString === "troute_page") {
            $compiledPageRoutes = cli_build_compiled_routes($singleRoutesPage['ROUTES'], $middlewareRoutesPage['MIDDLEWARES']);
            cli_output_compiled_routes($compiledPageRoutes, "troute_page");
            continue;
        }
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
                cli_err_syntax("Backup file must be readable! Path: $backupDirPath/$file is not!");
            }

            // Copy the file to the restore file path and delete the backup file after restoring it
            copy($backupDirPath . "/" . $file, $restoreFilePath);
            unlink($backupDirPath . "/" . $file);
            cli_success_without_exit("Backup File Restored: $restoreFilePath!");
            return;
        }
    }
    // If we reach here, it means we didn't find any files that start with the file starting name
    cli_err_syntax("No Backup File in $backupDirPath starting with \"$fileStartingName\"!");
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
        while (file_exists($dirPath . "/" . $startingFileName . "-" . $i . ".php")) {
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
// Valid ones are: "GET/", "POST/", "PUT/", "DELETE/", "g/", "po/", "pu/", "d/")
function cli_valid_route_start_syntax($routeString)
{
    // First check that string is non-empty string
    if (!is_string($routeString) || empty($routeString)) {
        cli_err_syntax("Route string must be a non-empty string!");
    }
    // Then we check if it starts with one of the valid ones
    if (str_starts_with($routeString, "get/") || str_starts_with($routeString, "post/") || str_starts_with($routeString, "put/") || str_starts_with($routeString, "delete/")) {
        return true;
    } elseif (str_starts_with($routeString, "g/") || str_starts_with($routeString, "po/") || str_starts_with($routeString, "pu/") || str_starts_with($routeString, "d/")) {
        return true;
    } else {
        return false;
    }
}

// Prepares a valid route string to by validating starting syntax and extracting the method from it
function cli_prepare_valid_route_string($addRoute, $test = false)
{
    // Grab the route to add and validate correct starting syntax
    // first: get/put/post/delete/ or its short form g/pu/po/d/
    if (!cli_valid_route_start_syntax($addRoute)) {
        cli_err_syntax("Route string must start with one of the valid ones:\n'GET/' (or g/)'\n'POST/' (or po/)\n'PUT/'(or pu/)\n'DELETE/' (or d/)");
    }
    // Try extract the method from the route string
    $method = cli_extracted_parsed_method_from_valid_start_syntax($addRoute);
    if ($method === null) {
        cli_err_syntax("Failed to parse the Method the Route string must start with (all of these below are valid):\n'GET/' (or g/)'\n'POST/' (or po/)\n'PUT/'(or pu/)\n'DELETE/' (or d/)");
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
