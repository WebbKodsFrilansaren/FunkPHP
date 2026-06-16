<?php // ALL CLI FUNCTIONS

// Compile "pipeline_routes.php" (via trie routes files "compiled_routes.php") into an
// optimized flat Route matcher using GOTO labels: everywhere! IMPORTANT: Might not work,
// still working progress function and might NOT be final thing to use!
function cli_compile_trie_node(array $node, array $currentPath = [], int $depth = 0): string
{
    $code = "";

    foreach ($node as $key => $subNode) {
        // Skip middleware or metadata flags
        if ($key === '|' || $key === '<CONFIG>') continue;

        $newPath = array_merge($currentPath, [$key]);
        $labelName = "branch_GET_" . implode('_', str_replace(':', 'param_', $newPath));

        if ($depth === 0) {
            // First level creates the initial entry routing
            $code .= "    if (\$uriSegments[0] === '{$key}') goto {$labelName};\n";
        }

        // Open the label block
        $labelCode = "\n    {$labelName}:\n";

        // If this node represents a valid complete route destination
        if (isset($subNode[0]) || is_null($subNode)) {
            $routePath = '/' . implode('/', $newPath);
            $labelCode .= "    if (\$uriSegmentCount === " . ($depth + 1) . ") {\n";
            $labelCode .= "        \$c['req']['route'] = '{$routePath}';\n";
            // Hardcode fully resolved handlers & middlewares extracted from your routes.php!
            $labelCode .= "        return true;\n";
            $labelCode .= "    }\n";
        }

        // Recursively handle children if they exist
        if (is_array($subNode) && !empty($subNode)) {
            // Write look-ahead for the next segment index
            $nextIndex = $depth + 1;
            foreach ($subNode as $subKey => $childNode) {
                if ($subKey === 0 || $subKey === '|' || $subKey === '<CONFIG>') continue;

                $nextLabel = "branch_GET_" . implode('_', str_replace(':', 'param_', array_merge($newPath, [$subKey])));

                if ($subKey === ':') {
                    // It's a wildcard dynamic parameter node! It consumes whatever string is there.
                    $labelCode .= "    if (isset(\$uriSegments[{$nextIndex}])) goto {$nextLabel};\n";
                } else {
                    // It's a static string literal node
                    $labelCode .= "    if ((\$uriSegments[{$nextIndex}] ?? null) === '{$subKey}') goto {$nextLabel};\n";
                }
            }
        }

        // If no look-aheads match and the segment count wasn't an exact match, drop to 404
        $labelCode .= "    goto route_404;\n";

        // Append this whole block to our main generation system
        // (You might want to sort static literals before wildcards here!)
        $code .= $labelCode;
    }

    return $code;
}

// Get inherited middlewares (IMPORTANT: might not work yet, still work in progress) from route
function cli_resolve_inherited_middlewares(string $routePath, array $developerRoutes, string $method): array
{
    $segments = array_filter(explode('/', trim($routePath, '/')));
    $compiledMiddlewares = [];
    $currentPath = '';

    // Gradually build the path up step-by-step: /users -> /users/:id -> /users/:id/posts
    foreach ($segments as $segment) {
        $currentPath .= '/' . $segment;

        // Check if the developer defined middleware at this specific parent tier
        if (isset($developerRoutes[$method][$currentPath][0]['middlewares'])) {
            $compiledMiddlewares = array_merge(
                $compiledMiddlewares,
                $developerRoutes[$method][$currentPath][0]['middlewares']
            );
        }
    }

    return $compiledMiddlewares;
}

// Sort the developer-defined single routes with parameters in a way that ensures more specific routes
// are matched before more general ones (e.g., /users/:id before /users/:id/posts). This is crucial
// for correct route matching when using a flat array of routes. The sorting logic ensures that
// routes with more segments and fewer wildcards come first, while routes with wildcards
// (parameters) are pushed down the order. IMPORTANT: might not work yet; WiP!
function cli_uksort_on_routes_with_params($developerSingleRoutes)
{
    uksort($developerSingleRoutes, function ($routeA, $routeB) {
        $segA = explode('/', trim($routeA, '/'));
        $segB = explode('/', trim($routeB, '/'));

        $countA = count($segA);
        $countB = count($segB);
        $max = max($countA, $countB);

        for ($i = 0; $i < $max; $i++) {
            $a = $segA[$i] ?? null;
            $b = $segB[$i] ?? null;

            // If one route runs out of segments first, the shorter route comes first
            if ($a === null) return -1;
            if ($b === null) return 1;

            // If they are identical at this level, keep checking deeper
            if ($a === $b) continue;

            // 🔥 THE MAGIC RULE: Wildcards (starting with :) always lose!
            if (str_starts_with($a, ':') && !str_starts_with($b, ':')) return 1;  // A moves down
            if (!str_starts_with($a, ':') && str_starts_with($b, ':')) return -1; // A moves up

            // Otherwise, do a standard alphabetical check for literals
            return strcmp($a, $b);
        }
        return 0;
    });
}

// Meant to create super fast hydration code on compiled SQL Query results with nested relations
// hydration via GOTO labels and direct array access without function calls or loops!
// IMPORTANT: might not work yet, still work in progress!
function cli_compile_hydration_node(array $node, string $parentPath = '$results[$pKey]')
{
    $code = "";
    foreach ($node['with'] as $relationName => $childNode) {
        $childPk = $childNode['pk'];

        // Generate the nested conditional check string
        $code .= "if (isset(\$row['{$childPk}']) && \$row['{$childPk}'] !== null) {\n";
        $code .= "    \$cKey = \$row['{$childPk}'];\n";
        $code .= "    if (!isset({$parentPath}['{$relationName}'][\$cKey])) {\n";
        $code .= "        {$parentPath}['{$relationName}'][\$cKey] = [\n";

        // Loop columns for this entity
        foreach ($childNode['cols'] as $col) {
            // Map table names clean or preserve names clean
            $shortName = str_replace($relationName . '_', '', $col);
            $code .= "            '{$shortName}' => \$row['{$col}'],\n";
        }

        $code .= "        ];\n";
        $code .= "    }\n";

        // Recursively compile deeper nesting if relations go further down!
        if (!empty($childNode['with'])) {
            $code .= compile_hydration_node($childNode, "{$parentPath}['{$relationName}'][\$cKey]");
        }

        $code .= "}\n";
    }
    return $code;
}

/**
 * Recursively checks for the existence and strict single-subkey structure of a path
 * within a starting array.
 *
 * NOTE: The lookup chain will only break if a key is missing or the value is not
 * an array, allowing traversal to continue past arrays that violate the
 * 'valid_single_key' constraint (like the HTTP Method level in a routing array).
 * The 'valid_single_key' flag will still report the structural violation.
 *
 * @param array $startingArray The array to traverse.
 * @param string ...$subkeys A list of keys to look up sequentially (e.g., 'GET', '/users').
 * @return array A numerically indexed array of results, where each element contains:
 * 'key' (the subkey checked), 'exists' (bool), and 'valid_single_key' (bool).
 */
function array_subkeys_single(array &$startingArray, string ...$subkeys): array
{
    // Validate startingArray is indeed an array
    if (!is_array($startingArray)) {
        cli_err('[array_subkeys_single]: The `$startingArray` Parameter must be an Array!');
    }
    // Validate $subkeys that must be either non-empty trimmed strings OR just integers for index access
    foreach ($subkeys as $key) {
        if (is_string($key)) {
            if (empty(trim($key))) {
                cli_err('[array_subkeys_single]: Strings in $subkeys must be Non-Empty!');
            }
        } elseif (!is_int($key)) { // Only allow string (non-empty) or integer
            cli_err('[array_subkeys_single]: Each Subkey must be a Non-Empty String or an Integer for Index Access!');
        }
    }
    $currentLevel = $startingArray;
    $results = [];
    $isChainBroken = false;
    foreach ($subkeys as $key) {
        $exists = isset($currentLevel[$key]) || array_key_exists($key, $currentLevel);
        // --- 1. Handle Broken Chain or Non-Existence ---
        if ($isChainBroken || !$exists) {
            $isChainBroken = true;
            $results[] = [
                'key' => $key,
                'exists' => false,
                'valid_single_key' => false
            ];
            continue;
        }
        // --- 2. Check for strict single-subkey structure (Reporting Only) ---
        // This check reports if the structure is strictly single-keyed, but does NOT break traversal.
        $isSingleKeyValid = (
            is_array($currentLevel) &&              // Current level must be an array
            !empty($currentLevel) &&                // Must not be empty
            !array_is_list($currentLevel) &&        // Must not be a numerically indexed list
            count($currentLevel) === 1 &&           // Must have exactly one key
            key($currentLevel) === $key             // That single key must be the key we are currently checking
        );
        // Record the result for this key
        $results[] = [
            'key' => $key,
            'exists' => true,
            'valid_single_key' => $isSingleKeyValid
        ];
        // Prepare for the next loop iteration
        $value = $currentLevel[$key];
        // --- 3. Update Traversal: ONLY break the chain if the next value is NOT an array ---
        if (!is_array($value)) {
            $isChainBroken = true;
            continue;
        }
        // Move to the next level (the array held in $value)
        $currentLevel = $value;
    }
    return $results;
}

/**
 * Creates a new Pipeline file (in either 'request' or 'post_response' sub-directory)
 * with a skeleton anonymous function.
 *
 * This function enforces a strict contract: the status array MUST confirm the file
 * does not already exist in EITHER pipeline directory and that the target directory
 * is writable. It constructs the standard Pipeline file content, including the
 * required 'pl_' namespace, and uses atomic write for safety.
 *
 * @param string $pipelineNameString The validated name of the pipeline (e.g., "pl_json_api").
 * @param string $pipelineType The subdirectory target: 'request' or 'post'.
 * @param array $plStatusArray The status array returned by cli_pipeline_file_status()
 * which guarantees the file does not exist in a conflicting location and the directory is valid.
 * Requires keys: 'exists', 'has_valid_prefix', 'is_anonymous', 'full_file_path',
 * 'exists_in_request_dir', 'exists_in_post_response_dir', and directory status keys (if applicable).
 * @return bool True on successful atomic creation/write of the file, false on failure.
 * @throws cli_err Stops command execution if input is invalid or file/directory
 * checks fail (e.g., file already exists in a conflicting location, or permissions are insufficient).
 */
function cli_create_pipeline_file($pipelineNameString, $pipelineType, $plStatusArray, $optionalCodeSnippets = ''): bool
{
    // 1. Mandatory Input Validation (Defensive Checks)
    if (!is_string($pipelineNameString) || empty(trim($pipelineNameString))) {
        cli_err('[cli_create_pipeline_file()]: Provided Pipeline Name must be a Non-Empty String.');
    }
    $targetDirKey = ($pipelineType === 'request') ? 'FUNKPHP_PIPELINE_REQUEST_DIR' : 'FUNKPHP_PIPELINE_POST_RESPONSE_DIR';
    if (!defined($targetDirKey)) {
        cli_err('[cli_create_pipeline_file()]: Target Pipeline Directory Constant is not defined.');
    }
    // Validate $plStatusArray has required keys
    $requiredKeys = [
        'exists',
        'has_valid_prefix',
        'exists_in_request_dir',
        'exists_in_post_response_dir',
        'exists_in_both_dirs',
        'pipeline_is_valid',
        'full_file_path_request',
        'full_file_path_post_response',
        'pipeline_request_dir_exists',
        'pipeline_request_dir_readable',
        'pipeline_request_dir_writable',
        'pipeline_post_response_dir_exists',
        'pipeline_post_response_dir_readable',
        'pipeline_post_response_dir_writable'
    ];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $plStatusArray)) {
            cli_err('[cli_create_pipeline_file()]: The Provided Middleware Status Array ($plStatusArray) is missing the required key `' . $key . '` neeeded to safely create a new Pipeline File without accidentally overwriting existing ones in either Pipeline Subdirectories. If a Command File called this function, this error now stopped the command execution!');
        }
    }
    // 2. Critical Existence and Conflict Checks
    if ($plStatusArray['exists']) {
        cli_err('[cli_create_pipeline_file()]: Pipeline File already exists. Cannot create again.');
    }
    // Since the directory paths are needed, we reconstruct the target path
    $targetBasePath = constant($targetDirKey);
    $outputNewFile = $targetBasePath . '/' . $pipelineNameString . '.php';
    // 3. Permission Checks (Simplified, assuming $targetBasePath is the direct path)
    if (!is_dir($targetBasePath) || !is_writable($targetBasePath)) {
        cli_err_without_exit("[cli_create_pipeline_file()]: Pipeline Directory `{$targetBasePath}` is either missing or not writable. Command Stopped!");
    }
    // 4. Prepare Default Pipeline File String Content
    $namespace = "funkphp\\pipeline\\" . ($pipelineType === 'request' ? 'request' : 'post_response') . "\\$pipelineNameString";
    $plString = "<?php\n\nnamespace $namespace;\n// FunkCLI Created on " . date('Y-m-d H:i:s') . "!\n\nfunction $pipelineNameString(&\$c)\n{\n\t// Placeholder Comment so Regex works - Remove & Add Your Own Code!\n$optionalCodeSnippets\n};\n";

    // 5. Atomic Creation/Write
    return cli_crud_folder_php_file_atomic_write($plString, $outputNewFile);
}

/**
 * Creates a new Middleware file with a skeleton anonymous function, ensuring all
 * necessary status checks have passed before writing to disk.
 *
 * This function enforces a strict contract: the status array MUST confirm the file
 * does not already exist and the target directory is writable. It constructs
 * the standard Middleware file content, including the required 'mw_' namespace,
 * and uses atomic write for safety.
 *
 * @param string $middlewareNameString The validated name of the middleware (e.g., "mw_auth_check").
 * @param array $mwStatusArray The status array returned by cli_middleware_file_status()
 * which guarantees the file does not exist and the directory is valid.
 * Requires keys: 'exists', 'has_valid_prefix', 'is_anonymous',
 * 'middleware_is_valid', 'middleware_dir_exists',
 * 'middleware_dir_readable', 'middleware_dir_writable',
 * and 'full_file_path'.
 * @return bool True on successful atomic creation/write of the file, false on failure.
 * @throws cli_err Stops command execution if $middlewareNameString is invalid,
 * $mwStatusArray is missing required keys, file already exists,
 * or directory permissions are insufficient.
 */
function cli_create_middleware_file($middlewareNameString, $mwStatusArray, $optionalCodeSnippets = ''): bool
{
    // $middlewareNameString must be non-empty string or hard error
    if (!is_string($middlewareNameString) || empty(trim($middlewareNameString))) {
        cli_err('[cli_create_middleware_file()]: The Provided Middleware Name String (middlewareNameString) must be a Non-Empty String in order to continue creating the new Middleware File. If a Command File called this function, this error now stopped the command execution!');
    }
    // $mwStatusArray must be an array with the following keys existing: exists, has_valid_prefix, is_anonymous, middleware_is_valid
    $requiredKeys = [
        'exists',
        'has_valid_prefix',
        'middleware_is_valid',
        'middleware_dir_exists',
        'middleware_dir_readable',
        'middleware_dir_writable'
    ];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $mwStatusArray)) {
            cli_err('[cli_create_middleware_file()]: The Provided Middleware Status Array ($mwStatusArray) is missing the required key `' . $key . '` neeeded to safely create a new Middleware File without accidentally overwriting existing ones. If a Command File called this function, this error now stopped the command execution!');
        }
    }
    // Just an extra check that files does not already exists and that the folder can be written to
    if ($mwStatusArray['exists']) {
        cli_err('[cli_create_middleware_file()]: The Provided Middleware Status Array ($mwStatusArray) indicates that the Middleware File already exists so cannot create it again. If a Command File called this function, this error now stopped the command execution!');
    }

    // Now check that folder exists, is readable and writable
    if (
        !$mwStatusArray['middleware_dir_exists']
        || !$mwStatusArray['middleware_dir_readable']
        || !$mwStatusArray['middleware_dir_writable']
    ) {
        cli_err('[cli_create_middleware_file()]: The Provided Middleware Status Array ($mwStatusArray) indicates that the Middleware Directory is either missing or not Readable/Writable so cannot create the Middleware File. If a Command File called this function, this error now stopped the command execution!');
    }

    // Prepare Default Middleware File String Content and return the boolean value of the creation/write operation
    $mwString = "<?php\n\nnamespace funkphp\\pipeline\\middlewares\\$middlewareNameString;\n// FunkCLI Created on " . date('Y-m-d H:i:s') . "!\n\nfunction $middlewareNameString(&\$c)\n{\n\t// Placeholder Comment so Regex works - Remove & Add Your Own Code!\n$optionalCodeSnippets\n};\n";
    return cli_crud_folder_php_file_atomic_write($mwString, $mwStatusArray['full_file_path']);
}

/**
 * Checks the existence and validity status of a Middleware file based on strict FunkPHP criteria.
 *
 * It checks if the file exists, if the file name has the mandatory 'mw_' prefix, and if the file
 * correctly returns a PHP Closure (anonymous function) upon inclusion.
 *
 * @param string $validatedMiddlewareString The Middleware file name (e.g., "mw_auth_check"). MUST be a non-empty string.
 * @return array A status array containing detailed checks:
 * - 'exists': (bool) True if the file exists at FUNKPHP_MIDDLEWARES_DIR.
 * - 'has_valid_prefix': (bool) True if the string starts with 'mw_'.
 * - 'is_anonymous': (bool) True if the included file returns an instanceof Closure.
 * - 'middleware_is_valid': (bool) True only if (exists AND has_valid_prefix AND is_anonymous).
 */
function cli_middleware_file_status($validatedMiddlewareString): array
{
    // Constant must exist and provided argument must be a non-empty string
    if (!defined('FUNKPHP_MIDDLEWARES_DIR')) {
        cli_err('[cli_middleware_file_exists()]: FUNKPHP_MIDDLEWARES_DIR Constant is not defined. Cannot check for Middleware existence. If a Command File called this function, this error now stopped the command execution!');
    }
    if (!is_string($validatedMiddlewareString) || empty(trim($validatedMiddlewareString))) {
        cli_err('[cli_middleware_file_exists()]: The Provided Middleware String must be a Non-Empty String.  If a Command File called this function, this error now stopped the command execution!');
    }
    // Variables that are returned with default values that change after checks
    $mwExists = false;
    $mwPrefix = false;
    $mwAnonymous = false;
    // Check if the exact string exists in the middlewares directory (constant)
    // then try include it to check if it is an anonymous function.
    // Finally check if it has the correct "mw_" prefix. If file exists and the prefix is "mw_"
    $middlewareDir = FUNKPHP_MIDDLEWARES_DIR;
    $middlewareFilePath = FUNKPHP_MIDDLEWARES_DIR . '/' . $validatedMiddlewareString . '.php';
    if (file_exists($middlewareFilePath) && is_file($middlewareFilePath)) {
        $mwExists = true;
        $included = include_once $middlewareFilePath;
        if (is_callable($included) && ($included instanceof Closure)) {
            $mwAnonymous = true;
        }
    }
    if (str_starts_with($validatedMiddlewareString, 'mw_')) {
        $mwPrefix = true;
    }
    return [
        'exists' => $mwExists,
        'has_valid_prefix' => $mwPrefix,
        'is_anonymous' => $mwAnonymous,
        'middleware_is_valid' => ($mwExists && $mwPrefix && $mwAnonymous),
        'full_file_path' => $middlewareFilePath,
        'middleware_dir_exists' => is_dir($middlewareDir),
        'middleware_dir_readable' => is_readable($middlewareDir),
        'middleware_dir_writable' => is_writable($middlewareDir),
    ];
}

/**
 * Checks the existence and validity status of a Pipeline file across request and post_response directories.
 *
 * It checks for file existence in either pipeline directory, prefix, Closure return, and ensures
 * the file name is NOT used in both pipeline directories to prevent ambiguity.
 *
 * @param string $validatedPipelineString The Pipeline file name (e.g., "pl_logging"). MUST be a non-empty string.
 * @return array A status array containing detailed checks:
 * - 'exists': (bool) True if the file exists in EITHER directory.
 * - 'has_valid_prefix': (bool) True if the string starts with 'pl_'.
 * - 'is_anonymous': (bool) True if the included file returns an instanceof Closure.
 * - 'exists_in_request_dir': (bool) True if the file exists in the request pipeline directory.
 * - 'exists_in_post_response_dir': (bool) True if the file exists in the post_response pipeline directory.
 * - 'exists_in_both_dirs': (bool) True if the file exists in BOTH directories.
 * - 'pipeline_is_valid': (bool) True only if (exists AND has_valid_prefix AND is_anonymous AND NOT exists_in_both_dirs).
 */
function cli_pipeline_file_status($validatedPipelineString): array
{
    // Constant must exist and provided argument must be a non-empty string
    if (!defined('FUNKPHP_PIPELINE_REQUEST_DIR') || !defined('FUNKPHP_PIPELINE_POST_RESPONSE_DIR')) {
        cli_err('[cli_pipeline_file_status()]: FUNKPHP_PIPELINE_REQUEST_DIR or FUNKPHP_PIPELINE_POST_RESPONSE_DIR Constant(s) is/are not defined. Cannot check for Pipeline existence. If a Command File called this function, this error now stopped the command execution!');
    }
    if (!is_string($validatedPipelineString) || empty(trim($validatedPipelineString))) {
        cli_err('[cli_pipeline_file_status()]: The Provided Pipeline String must be a Non-Empty String.  If a Command File called this function, this error now stopped the command execution!');
    }
    // Variables that are returned with default values that change after checks
    $plExists = false;
    $plPrefix = false;
    $plAnonymous = false;
    $plExistsInRequestDir = false;
    $plExistsInPostResponseDir = false;
    $plExistsInBothDirs = false;
    // We start by trying to find in both directories based on constants and see if files exist in either
    // and change the variables accordingly
    $pipelineRequestFilePath = FUNKPHP_PIPELINE_REQUEST_DIR . '/' . $validatedPipelineString . '.php';
    $pipelinePostResponseFilePath = FUNKPHP_PIPELINE_POST_RESPONSE_DIR . '/' . $validatedPipelineString . '.php';
    if (file_exists($pipelineRequestFilePath) && is_file($pipelineRequestFilePath)) {
        $plExistsInRequestDir = true;
        $included = include_once $pipelineRequestFilePath;
        if (is_callable($included) && ($included instanceof Closure)) {
            $plAnonymous = true;
        }
    }
    if (file_exists($pipelinePostResponseFilePath) && is_file($pipelinePostResponseFilePath)) {
        $plExistsInPostResponseDir = true;
        $included = include_once $pipelinePostResponseFilePath;
        if (is_callable($included) && ($included instanceof Closure)) {
            $plAnonymous = true;
        }
    }
    // Finally check "pl_" prefix and set other variables based on checks
    if (str_starts_with($validatedPipelineString, 'pl_')) {
        $plPrefix = true;
    }
    $plExists = ($plExistsInRequestDir || $plExistsInPostResponseDir);
    $plExistsInBothDirs = ($plExistsInRequestDir && $plExistsInPostResponseDir);
    return [
        'exists' => $plExists,
        'has_valid_prefix' => $plPrefix,
        'is_anonymous' => $plAnonymous,
        'exists_in_request_dir' => $plExistsInRequestDir,
        'exists_in_post_response_dir' => $plExistsInPostResponseDir,
        'exists_in_both_dirs' => ($plExistsInRequestDir && $plExistsInPostResponseDir),
        'pipeline_is_valid' => ($plExists && $plPrefix && $plAnonymous && !$plExistsInBothDirs),
        'full_file_path_request' => $pipelineRequestFilePath,
        'full_file_path_post_response' => $pipelinePostResponseFilePath,
        'pipeline_request_dir_exists' => is_dir(FUNKPHP_PIPELINE_REQUEST_DIR),
        'pipeline_request_dir_readable' => is_readable(FUNKPHP_PIPELINE_REQUEST_DIR),
        'pipeline_request_dir_writable' => is_writable(FUNKPHP_PIPELINE_REQUEST_DIR),
        'pipeline_post_response_dir_exists' => is_dir(FUNKPHP_PIPELINE_POST_RESPONSE_DIR),
        'pipeline_post_response_dir_readable' => is_readable(FUNKPHP_PIPELINE_POST_RESPONSE_DIR),
        'pipeline_post_response_dir_writable' => is_writable(FUNKPHP_PIPELINE_POST_RESPONSE_DIR),
    ];
}

/**
 * Checks for duplicate Folder=>File=>Function route keys in a matched route array.
 * @param array $matchedRoute The matched route array to check.
 * @param string $folder The folder name to check for.
 * @param string $file The file name to check for.
 * @param string $fn The function name to check for.
 * @return bool Returns true if a duplicate is found, false otherwise.
 *
 * The function expects $matchedRoute to be a numerically indexed array of route keys,
 * each structured as Folder => File => Function => {optionalValue}. It checks each route key
 * to see if the specified folder, file, and function combination already exists.
 * If a duplicate is found, it logs a warning and returns true. If no duplicates are found,
 * it returns false. It is used by `make-route`, `make-handler` Command FIles and their aliases
 * to either warn or hard-error out when trying to create a route key that already exists.
 */
function cli_duplicate_folder_file_fn_route_key($matchedRoute, $file, $fn, $methodroute): bool
{
    // $matchedRoute must be a numbered array that is NOT empty!
    if (
        !is_array($matchedRoute)
        || empty($matchedRoute)
        || !array_is_list($matchedRoute)
    ) {
        cli_err('[cli_duplicate_folder_file_fn_route_key]: The Provided $matchedRoute must be a Non-Empty Numerically Indexed Array. Function expects a Matched Route with a Numbered Array of Route Keys with `[index] => Folder => File => Function => {optionalValue}` Structure to check against!');
    }
    // $folder, $file, $fn & $methodroute must be non-empty strings
    if (
        !is_string($file)
        || empty(trim($file))
        || !is_string($fn)
        || empty(trim($fn))
        || !is_string($methodroute)
        || empty(trim($methodroute))
    ) {
        cli_err('[cli_duplicate_folder_file_fn_route_key]: The Provided $file, $fn, $methodroute must be Non-Empty Strings. Function expects a Matched Route with a Numbered Array of Route Keys with `[index] => File => Function Structure to check against!');
    }
    // We now iterate over $matchedRoute keys using the array_subkeys_single() helper function
    // passing the matched Route array and the three strings provided by "$folder", "$file","$fn"
    foreach ($matchedRoute as $idx => $routeKey) {
        // If all three subkeys exist and are valid single-key structures, we have a duplicate
        $checkResult = array_subkeys_single($routeKey, $file, $fn);
        if (
            count($checkResult) === 2
            && $checkResult[0]['exists'] === true
            && $checkResult[1]['exists'] === true
        ) {
            cli_warning_without_exit("Duplicate Route Key `$file=>$fn` at Index:[$idx] in:`$methodroute`!");
            return true;
        }
    }
    return false;
}

/**
 * Extracts the folder, file, and function parts from a validated 'folder=>file=>function' string.
 * SUPER IMPORTANT: This function ASSUMES the input has already been successfully validated.
 *
 * @param string $validatedFolderFileFnString The input string (e.g., 'fff:users=>users_file=>get_user').
 * @return array{0: string, 1: string, 2: string} Returns an array [$folder, $file, $fn].
 */
function cli_extract_folder_file_fn(string $validatedFolderFileFnString): array
{
    // 1. Remove any argument prefix (e.g., fff:). We assume it exists if the input is valid.
    $prefixRegex = '/^([a-z]+:)/i';
    $sanitizedString = preg_replace($prefixRegex, '', $validatedFolderFileFnString, 1);

    // 2. Split the parts using the '=>' delimiter
    $parts = explode('=>', $sanitizedString);

    // Because validation already passed on the regex, we know $parts has 3 elements.
    $folder = $parts[0];
    $file = $parts[1];
    $fn = $parts[2];

    cli_info_without_exit("OK! Parsed Folder: `funkphp/routes/$folder`, File: `funkphp/routes/$folder/$file.php`, Function: `function $fn(&\$c, \$passedValue = null){};`");
    return [$folder, $file, $fn];
}

/**
 * Extracts File and Function parts from a validated 'file=>function' string,
 * with optional prefix handling for 'v_' (validation) or 's_' (SQL).
 * SUPER IMPORTANT: This function ASSUMES the input has already been successfully validated.
 *
 * @param string $validatedFileFnString (e.g., 'ff:users=>validate_user' or 'ff:users=>all_users').
 * @param string $prefix (currently supports 'v_' for validation or 's_' for SQL)
 * @return array {0: string, 1: string} Returns an array [$file, $fn] with optional prefix applied.
 */
function cli_extract_folder_file($validatedFileFnString, $prefix = null): array
{
    $file = null;
    $fn = null;
    $pre = '';
    // 1. Remove any argument prefix (e.g., fff:). We assume it exists if the input is valid.
    $prefixRegex = '/^([a-z]+:)/i';
    $sanitizedString = preg_replace($prefixRegex, '', $validatedFileFnString, 1);
    // 2. Split the parts using the '=>' delimiter
    $parts = explode('=>', $sanitizedString);
    $file = $parts[0];
    $fn = $parts[1];
    // 3. Add optional prefix if provided and then return results
    if (isset($prefix) && is_string($prefix) && !empty(trim($prefix))) {
        $pre = mb_strtolower($prefix);
    }
    if (isset($pre) && is_string($pre) && $pre === 'v_') {
        cli_info_without_exit("OK! Parsed Validation File:`src/FunkPHP/sql/$file.php` with Function:`function $pre$fn(&\$c, \$passedValue = null){};`");
    } else if (isset($pre) && is_string($pre) && $pre === 's_') {
        cli_info_without_exit("OK! Parsed SQL File:`src/FunkPHP/sql/$file.php` with Function:`function $pre$fn(&\$c, \$passedValue = null){};`");
    }
    return [$pre . $file, $pre . $fn];
}

/**
 * Extracts and normalizes the HTTP method and route path from a validated string.
 * SUPER IMPORTANT: This function ASSUMES the input has already been successfully validated.
 *
 * @param string $validatedMethodRouteString The input string (e.g., 'get/users/:id', possibly with 'r:').
 * @return array{0: string, 1: string}|null Returns array [Method, Route] or null if processing fails (highly unlikely on validated data).
 */
function cli_extract_method_route($validatedMethodRouteString)
{
    $validatedMethodRouteString = mb_strtolower($validatedMethodRouteString);
    // 1. Remove any prefix (e.g., r:), if this is still attached to the string
    $prefixRegex = '/^([a-z]+:)/i';
    $sanitizedString = preg_replace($prefixRegex, '', $validatedMethodRouteString, 1);
    $firstSlashPos = strpos($sanitizedString, '/');
    $methodPart = substr($sanitizedString, 0, $firstSlashPos);
    $routePart = substr($sanitizedString, $firstSlashPos);
    if ($routePart === '/') {
        $routePart = '/';
    }
    $methodMap = [
        'g' => 'GET',
        'ge' => 'GET',
        'get' => 'GET',
        'po' => 'POST',
        'pos' => 'POST',
        'post' => 'POST',
        'd' => 'DELETE',
        'del' => 'DELETE',
        'delete' => 'DELETE',
        'pu' => 'PUT',
        'put' => 'PUT',
        'pa' => 'PATCH',
        'patch' => 'PATCH'
    ];
    $method = $methodMap[$methodPart];
    $route = $routePart;
    cli_info_without_exit("OK! Parsed Method & Route:`$method$route`");
    return [$method, $route];
}

/**
 * Extracts and normalizes the Middleware file name, ensuring the 'mw_' prefix is present.
 * SUPER IMPORTANT: ASSUMES the input string has been previously validated and is non-empty.
 *
 * @param string $validatedMiddlewareString The validated input string (e.g., "n:auth_check" or "auth_check").
 * @return string The normalized file name, guaranteed to start with 'mw_' (e.g., "mw_auth_check").
 */
function cli_extract_middleware($validatedMiddlewareString)
{
    $validatedMiddlewareString = mb_strtolower($validatedMiddlewareString);
    // 1. Remove any prefix (e.g., n:), if this is still attached to the string
    $prefixRegex = '/^([a-z]+:)/i';
    $sanitizedString = preg_replace($prefixRegex, '', $validatedMiddlewareString, 1);

    // 2. Add hte "mw_" prefix if not already present and return string
    if (str_starts_with($sanitizedString, 'mw_')) {
        cli_info_without_exit("OK! Parsed Middleware Name:`$sanitizedString`");
        return $sanitizedString;
    }
    cli_info_without_exit("OK! Parsed Middleware Name:`mw_$sanitizedString`");
    return 'mw_' . $sanitizedString;
}

/**
 * Extracts and normalizes the Pipeline file name, ensuring the 'pl_' prefix is present.
 * SUPER IMPORTANT: ASSUMES the input string has been previously validated and is non-empty.
 *
 * @param string $validatedPipelineString The validated input string (e.g., "name:logging" or "pl_logging").
 * @return string The normalized file name, guaranteed to start with 'pl_' (e.g., "pl_logging").
 */
function cli_extract_pipeline($validatedPipelineString)
{
    $validatedPipelineString = mb_strtolower($validatedPipelineString);
    // 1. Remove any prefix (e.g., n:), if this is still attached to the string
    $prefixRegex = '/^([a-z]+:)/i';
    $sanitizedString = preg_replace($prefixRegex, '', $validatedPipelineString, 1);

    // 2. Add hte "pl_" prefix if not already present and return string
    if (str_starts_with($sanitizedString, 'pl_')) {
        cli_info_without_exit("OK! Parsed Pipeline Name:`$sanitizedString`");
        return $sanitizedString;
    }
    cli_info_without_exit("OK! Parsed Pipeline Name:`pl_$sanitizedString`");
    return 'pl_' . $sanitizedString;
}
/**
 * Extracts and normalizes the Pipeline type from a validated string.
 * SUPER IMPORTANT: ASSUMES the input string has been previously validated and is non-empty.
 *
 * @param string $validatedPipelineString
 * @return string The normalized Pipeline type (e.g., "request" or "post_response").
 */
function cli_extract_pipeline_type($validatedPipelineString)
{
    $validatedPipelineString = mb_strtolower($validatedPipelineString);
    // 1. Remove any prefix (e.g., n:), if this is still attached to the string
    $prefixRegex = '/^([a-z]+:)/i';
    $sanitizedString = preg_replace($prefixRegex, '', $validatedPipelineString, 1);
    // 2. If it's "post" we rename it to "post_response" for clarity
    if ($sanitizedString === 'post') {
        cli_info_without_exit("OK! Parsed Pipeline Type:`post_response`");
        return 'post_response';
    }
    cli_info_without_exit("OK! Parsed Pipeline Type:`request`");
    return "request";
}

// Helper function that checks if a given $routeKey has the structure
// "Folder" => "FileName" => "FunctionName" => <Anyvalue> and returns
// that array structure or null if not found or not valid structure
function cli_folder_file_fn_value_exist_or_null($routeKey)
{
    $validStructure = null;
    if (
        $routeKey === null
        || !is_array($routeKey)
        || empty($routeKey)
        || array_is_list($routeKey)
        || count($routeKey) !== 1
    ) {
        return ['valid' => false, 'dir' => null, 'file' => null, 'fn' => null, 'value' => null, 'original' => $routeKey];
    }
    // level 1 = Folder
    $level1 = key($routeKey) ?? null;
    if ($level1 === null || !is_string($level1) || empty($level1)) {
        return ['valid' => false, 'dir' => null, 'file' => null, 'fn' => null, 'value' => null, 'original' => $routeKey];
    }
    $validStructure[$level1] = null;

    // level 2 = Folder => File
    $level2 = (is_array($routeKey[$level1])
        && !empty($routeKey[$level1])
        && !array_is_list($routeKey[$level1])
        && count($routeKey[$level1]) === 1)
        ? key($routeKey[$level1]) : null;
    if ($level2 === null || !is_string($level2) || empty($level2)) {
        return ['valid' => false, 'dir' => $level1, 'file' => null, 'fn' => null, 'value' => null, 'original' => $routeKey];
    }
    $validStructure[$level1][$level2] = null;

    // level 3 = Folder => File => Function
    $level3 = (is_array($routeKey[$level1][$level2])
        && !empty($routeKey[$level1][$level2])
        && !array_is_list($routeKey[$level1][$level2])
        && count($routeKey[$level1][$level2]) === 1)
        ? key($routeKey[$level1][$level2]) : null;
    if ($level3 === null || !is_string($level3) || empty($level3)) {
        return ['valid' => false, 'dir' => $level1, 'file' => $level2, 'fn' => null, 'value' => null, 'original' => $routeKey];
    }
    $validStructure[$level1][$level2][$level3] = null;

    // level 4 = Folder => File => Function => <AnyValue_Even_Empty_Or_Null>
    $level4 = $routeKey[$level1][$level2][$level3] ?? null;
    $validStructure[$level1][$level2][$level3] = $level4;
    return ['valid' => true, 'dir' => $level1, 'file' => $level2, 'fn' => $level3, 'value' => $level4, 'original' => $routeKey];
}

// Helper that checks if a key exists in a list of associative arrays
// Can also return the index of the first occurrence if $returnIndex is true
function array_key_exists_in_list($key, $listArray, $returnIndex = false)
{
    foreach ($listArray as $idx => $item) {
        if (array_key_exists($key, $item)) {
            if ($returnIndex) {
                return $idx;
            }
            return true;
        }
    }
    return false;
}

// Two functions that are used to output messages in the CLI
// and to send JSON responses when in JSON_MODE (web browser access)
function cli_output(string $type, string $message, bool $do_exit = false, int $exit_code = 0): void
{
    // Access the global message array and
    // determine prefix and color for CLI output
    global $funk_response_messages;
    $prefix = '';
    $color = '';
    switch ($type) {
        case MSG_TYPE_ERROR:
            $prefix = '[FunkCLI - ERROR]: ';
            $color = ANSI_RED;
            $exit_code = ($exit_code === 0) ? 1 : $exit_code;
            break;
        case MSG_TYPE_SYNTAX_ERROR:
            $prefix = '[FunkCLI - SYNTAX ERROR]: ';
            $color = ANSI_RED;
            $exit_code = ($exit_code === 0) ? 1 : $exit_code;
            break;
        case MSG_TYPE_SUCCESS:
            $prefix = '[FunkCLI - SUCCESS]: ';
            $color = ANSI_GREEN;
            break;
        case MSG_TYPE_INFO:
            $prefix = '[FunkCLI - INFO]: ';
            $color = ANSI_BLUE;
            break;
        case MSG_TYPE_WARNING:
            $prefix = '[FunkCLI - WARNING]: ';
            $color = ANSI_YELLOW;
            break;
        case MSG_TYPE_IMPORTANT:
            $prefix = '[FunkCLI - IMPORTANT]: ';
            $color = ANSI_YELLOW;
            break;
        default:
            $type = 'UNKOWN';
            $prefix = '[FunkCLI - UNKOWN MESSAGE TYPE]: '; // Fallback for unknown types
            $color = ANSI_RESET;
            break;
    }

    // Check if we are in JSON_MODE (web browser access)
    // If not JSON_MODE, we assume & output to CLI!
    // Any message that includes $do_exit true will
    // return built JSON response & exit or just exit CLI
    if (defined('JSON_MODE') && JSON_MODE) {
        $funk_response_messages[] = [
            'type' => $type,
            'message' => $message
        ];
        if ($do_exit) {
            cli_send_json_response();
        }
    } else {
        echo $color . $prefix . $message . ANSI_RESET . "\n";
        if ($do_exit) {
            exit($exit_code);
        }
    }
}
function cli_send_json_response(): void
{
    global $funk_response_messages;
    $overall_json_status = 'Success';
    foreach ($funk_response_messages as $msg) {
        if ($msg['type'] === MSG_TYPE_ERROR || $msg['type'] === MSG_TYPE_SYNTAX_ERROR) {
            $overall_json_status = 'Error';
            break;
        }
    }
    if ($overall_json_status === 'Success') {
        http_response_code(200);
        foreach ($funk_response_messages as $msg) {
            if ($msg['type'] === MSG_TYPE_WARNING) {
                $overall_json_status .= ' with Warning(s)';
                break;
            }
        }
    } else {
        http_response_code(400);
        foreach ($funk_response_messages as $msg) {
            if ($msg['type'] === MSG_TYPE_INFO) {
                $overall_json_status .= ' with (Important) Info';
                break;
            }
        }
    }
    $response = [
        'status' => $overall_json_status,
        'messages' => $funk_response_messages,
        'data' => []
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit();
}

// Returns "fileName"=>"functionName" based on the provided string
// If no function name is provided, it returns "fileName"=>"fileName"
function cli_return_valid_file_n_fn_or_err_out($string, $prefix = null)
{
    global $reserved_functions;
    // $string must be a non-empty string, then we lowercase it
    if (!isset($string) || !is_string($string) || empty($string)) {
        cli_err_without_exit('[cli_return_valid_file_n_fn_or_err_out]: This function expects a Non-Empty String (probably missing in $arg1) | $arg2 is the optional Method/Route part!');
        cli_info_without_exit('[cli_return_valid_file_n_fn_or_err_out]: Use either "fileName" (Regex: [a-z_][a-z_0-9]*) OR "fileName=>functionName" (Regex: [a-z_][a-z_0-9]*=>[a-z_][a-z_0-9.]*)!');
        cli_info('[cli_return_valid_file_n_fn_or_err_out]: IMPORTANT: Your provided String will ALWAYS be lowercased automatically before any further processing!');
    }
    $string = strtolower(trim($string));
    // Matches a string like "fileName" or "fileName=>functionName"
    // If only "fileName" is provided, it will use the same name for the function
    $regex = '/^([a-z_][a-z_0-9]*)(?:=>([a-z_][a-z_0-9.]*))?$/i';
    $file = '';
    $fn = '';
    // Preg_match to find the file and function names
    if (preg_match($regex, $string, $matches)) {
        $file = $matches[1];
        $fn = isset($matches[2]) ? $matches[2] : $file;
    } else {
        cli_err_without_exit('[cli_return_valid_file_n_fn_or_err_out]: Invalid Syntax for File and/or Function Name! (probably in $arg1) | $arg2 is the optional Method/Route part!');
        cli_info_without_exit('[cli_return_valid_file_n_fn_or_err_out]: Use either "fileName" (Regex: [a-z_][a-z_0-9]*) OR "fileName=>functionName" (Regex: [a-z_][a-z_0-9]*=>[a-z_][a-z_0-9.]*)!');
        cli_info('[cli_return_valid_file_n_fn_or_err_out]: IMPORTANT: Your provided String will ALWAYS be lowercased automatically before any further processing!');
    }
    // Add prefix to both variables if provided
    // and then check against reserved functions
    if (isset($prefix) && is_string($prefix) && !empty($prefix)) {
        // Check if file and/or function already starts with the prefix
        if (!str_starts_with($file, $prefix)) {
            $file = $prefix . $file;
        }
        if (!str_starts_with($fn, $prefix)) {
            $fn = $prefix . $fn;
        }
    }
    if (in_array($file, $reserved_functions)) {
        cli_err_without_exit('[cli_return_valid_file_n_fn_or_err_out]: File Name `' . $file . '` is a Reserved Function Name. Please choose a different name! (probably see $arg1)');
        cli_info('[cli_return_valid_file_n_fn_or_err_out]: The majority of Reserved Function Names usually start with "funk_" or "cli_" prefix, so please avoid using those prefixes for Your Custom Functions!');
    }
    if (in_array($fn, $reserved_functions)) {
        cli_err_without_exit('[cli_return_valid_file_n_fn_or_err_out]: Function Name `' . $fn . '` is a Reserved Function Name. Please choose a different name! (probably see $arg1)');
        cli_info('[cli_return_valid_file_n_fn_or_err_out]: The majority of Reserved Function Names usually start with "funk_" or "cli_" prefix, so please avoid using those prefixes for Your Custom Functions!');
    }
    cli_info_without_exit('OK! Parsed File => Function: `' . $file . '=>' . $fn . '` (Function is ignored for Folders such as `middlewares` & `pipeline`!)');
    return [$file, $fn];
}

// Returns valid extracted method and route or errors out
function cli_return_valid_method_n_route_or_err_out($string)
{
    if (!isset($string) || !is_string($string) || empty($string)) {
        cli_err_without_exit('Method/Route String must be a Non-Empty String using the following Syntax: `method/route/segments/with/optional/:params`!');
        cli_info_without_exit('For the Method, use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info_without_exit('OR Use any of its shorthand versions: "g", "po", "pu", "d" OR "del", "pa"');
        cli_info_without_exit('For the Route, write either "/route/segments" or "/route/segments/with/:params" (where :params is a Dynamic URI Segment of the Route)');
    }
    $string = trim(strtolower($string));
    $method = '';
    $extractedMethod = '';
    $methodRegex = '/^(delete|patch|post|put|del|get|pa|po|pu|ge|g|d)\/.*/i';
    $methodConvert = [
        'delete' => 'DELETE',
        'patch' => 'PATCH',
        'post' => 'POST',
        'put' => 'PUT',
        'get' => 'GET',
        'del' => 'DELETE',
        'pa' => 'PATCH',
        'po' => 'POST',
        'pu' => 'PUT',
        'ge' => 'GET',
        'g' => 'GET',
        'd' => 'DELETE'
    ];
    $routeRegex = '/\/(:[a-zA-Z0-9_-]+)|\/([a-zA-Z0-9_-]+)/i';
    $route = '';
    $routeParams = [];
    // First we check that $methodRegex matches the start of $string
    if (!preg_match($methodRegex, $string, $methodMatches)) {
        cli_err_without_exit('Invalid Method Syntax! Use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info_without_exit('OR Use any of its shorthand versions: "g" or "ge", "po", "pu", "d" OR "del", "pa"');
        cli_info('A Single `/` is needed if you mean the Root Route `/` of that Method!');
    }
    $extractedMethod = $methodMatches[1];
    $method = $methodConvert[$methodMatches[1]] ?? '';
    if ($method === '') {
        cli_err_without_exit('Invalid Method Syntax! Use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info('OR Use any of its shorthand versions: "g" or "ge", "po", "pu", "d" OR "del", "pa"');
    }
    // We separate the method from the route to validate route
    $routeString = substr($string, strlen($extractedMethod));
    // SPECIAL CASE: If the route is just `/`, we return it as is
    if ($routeString === '/') {
        return [$method, $routeString];
    }
    // Filter out leading slashes and ensure no double (or more) slashes
    $routeString = preg_replace('/\/+/', '/', $routeString);
    if (!preg_match_all($routeRegex,  $routeString, $routeMatches)) {
        cli_err_without_exit('Invalid Route Syntax! Use either "/route/segments" or "/route/segments/with/:params" (where :params is a Dynamic URI Segment of the Route)');
        cli_info('A Single `/` is needed if you mean the Root Route `/` of that Method as in `get/` OR `g/` and so on!');
    }
    // We iterate through $routeMatches[0] to error
    // out on duplicate route parameters. Otherwise
    // we return finalized $method and $route!
    foreach ($routeMatches[0] as $match) {
        if (str_starts_with($match, '/:')) {
            if (in_array($match, $routeParams)) {
                cli_err_without_exit('Duplicate Route Parameter `' .  $match . '` found in the Route `' .  $method . $routeString . '`!');
                cli_info('Fix so each Route Parameter (`/:param`) is unique and does not repeat in the Route Definition!');
            } else {
                $routeParams[] = $match;
            }
        }
        $route .= $match;
    }
    cli_info_without_exit("OK! Parsed Method & Route: `$method$route` (it is ONLY used where applicable!)");
    return [$method, $route];
}

// Returns a generated $DX Part based on provided $arg5
// which should contain "table1,table2,etc3" or just "table1"
function cli_created_sql_or_validation_fn($sqlOrValidation, $sv_tables)
{
    // Prepare general variables for either SQL or Validation
    global $tablesAndRelationshipsFile, $mysqlDataTypesFile;
    $tables = $tablesAndRelationshipsFile ?? null;
    $types = $mysqlDataTypesFile ?? null;
    $date = date("Y-m-d H:i:s");
    $finalString = '';
    if ($tables === null || !is_array($tables)) {
        cli_err_syntax_without_exit("`Tables.php` File not found! Please check your `funkphp/config/tables.php` File!");
        cli_info("Make sure you have a valid `tables.php` File in `funkphp/config/` directory!");
    }
    if (!isset($tables['tables']) || !is_array($tables['tables']) || empty($tables['tables'])) {
        cli_err_syntax_without_exit("`Tables.php` File does not contain valid `tables` key! Please check your `funkphp/config/tables.php` File!");
        cli_info("Your `tables` array key in your `tables.php` File in `funkphp/config/` directory CANNOT be empty and should Have At Least One Table!");
    }
    if (!isset($tables['relationships']) || !is_array($tables['relationships'])) {
        cli_err_syntax_without_exit("`tables.php` File does not contain valid `relationships` key! Please check your `funkphp/config/tables.php` File!");
        cli_info("Your `relationships` array key in your `Tables.php` File in `funkphp/config/` directory must exist and CAN be empty!");
    }
    if (!isset($tables['mappings']) || !is_array($tables['mappings'])) {
        cli_err_syntax_without_exit("`tables.php` File does not contain valid `mappings` key! Please check your `funkphp/config/tables.php` File!");
        cli_info("Your `mappings` array key in your `Tables.php` File in `funkphp/config/` directory must exist and CAN be empty!");
    }

    // Prepare SQL String based on Tables
    if ($sqlOrValidation === 'sql') {
        $tbs = [];
        $queryType = null;
        $availableQueryTypes = [
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'SELECT_DISTINCT',
            'SELECT_INTO',
        ];
        $processTables = null;
        // SQL Tables must start with "sd=", "si=", "s=", "i=", "u=", or "d="
        if (!preg_match('/^((select|delete|insert|update|sel|del|ins|upd|s|d|i|u)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $sv_tables)) {
            cli_err_without_exit('[cli_created_sql_or_validation_fn()]: Invalid SQL Tables Syntax! Use either "sd=table1,table2,etc3" or just "s=table1" with optional numbers with * at the end of each table name like "s=table1*2"!');
            cli_info('[cli_created_sql_or_validation_fn()]: The Regex Syntax for SQL Tables: `/^((sd|si|s|i|u|d)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/`!');
        }
        // Extract the "sd=", "si=", "s=", "i=", "u=", or "d=" part from the "table1,table2,etc3" part of the string
        // Uppercase the query type and validate it against the available query types after transoforming shorthands
        // to full query types (s=SELECT, i=INSERT, u=UPDATE, d=DELETE, sd=SELECT_DISTINCT, si=SELECT_INTO, st=SELECT_TOP)
        $queryType = strtoupper(substr($sv_tables, 0, strpos($sv_tables, '=')));
        $processTables = str_contains(substr($sv_tables, strpos($sv_tables, '=') + 1), ",")
            ? explode(',', substr($sv_tables, strpos($sv_tables, '=') + 1))
            : [substr($sv_tables, strpos($sv_tables, '=') + 1)];
        if (str_starts_with($queryType, 'S')) {
            $queryType = 'SELECT';
        } elseif (str_starts_with($queryType, 'I')) {
            $queryType = 'INSERT';
        } elseif (str_starts_with($queryType, 'U')) {
            $queryType = 'UPDATE';
        } elseif (str_starts_with($queryType, 'D')) {
            $queryType = 'DELETE';
        } elseif (str_starts_with($queryType, 'SD')) {
            $queryType = 'SELECT_DISTINCT';
        } elseif (str_starts_with($queryType, 'SI')) {
            $queryType = 'SELECT_INTO';
        }
        if (!in_array($queryType, $availableQueryTypes)) {
            cli_err_syntax_without_exit("Invalid Query Type: \"$queryType\". Available Query Types: " . implode(', ', quotify_elements($availableQueryTypes)) . ".");
            cli_err_without_exit('[cli_created_sql_or_validation_fn()]: Invalid SQL Tables Syntax! Use either "sd=table1,table2,etc3" or just "s=table1" with optional numbers with * at the end of each table name like "s=table1*2"!');
            cli_info('[cli_created_sql_or_validation_fn()]: The Regex Syntax for SQL Tables: `/^((sd|si|s|i|u|d)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/`!');
        }

        // Validate that all the provided tables exist in the
        // Tables.php file are valid named and not duplicates!
        foreach ($processTables as $table) {
            if (!preg_match('/^[a-z_][a-z_0-9]*$/i', $table)) {
                cli_err_syntax_without_exit("Invalid Table Name: \"$table\". Use only alphanumeric characters and underscores!");
                cli_info("Example: \"table1\" or \"table_1\" or \"table_1_2\" - do not use spaces or special characters!");
            }
            if (!array_key_exists($table, $tables['tables'])) {
                cli_err_syntax_without_exit("Table \"$table\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
                cli_info("Make sure you have a valid `tables.php` File in `funkphp/config/` directory with at least one table in the ['tables'] Array!");
            }
            if (array_key_exists($table, $tbs)) {
                cli_err_syntax_without_exit("Table \"$table\" already added. Only use one Table once!");
                cli_info("Make sure you have a valid `tables.php` File in `funkphp/config/` directory with at least one table in the ['tables'] Array!");
            }
            $tbs[$table]['cols'] = $tables['tables'][$table];
        }
        // For queryType "INSERT", "UPDATE", "DELETE" we ONLY allow one table to be provided!
        if (count($tbs) > 1) {
            if (in_array($queryType, ['INSERT', 'UPDATE', 'DELETE'])) {
                cli_err_syntax_without_exit("For Query Type \"$queryType\" you can ONLY provide ONE Table! Provided Tables: " . implode(', ', quotify_elements(array_keys($tbs))));
                cli_info_without_exit("Please provide ONLY ONE Table for Query Types `INSERT`, `UPDATE` or `DELETE` in the FunkCLI command!");
                cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1` (at least one Table must be provided)!");
            }
        }
        // Default values added to the $DXPART variable
        $hydrationModeStr = "\n\t\t\t'<HYDRATION_MODE>' => 'simple|advanced', // Pick one or `simple` is used by default! (leave empty or remove line if not used!)";
        $hydrationTypeStr = "\n\t\t\t'<HYDRATION_TYPE>' => 'array|object', // Pick one or `array` is used by default! (leave empty or remove line if not used!)";
        $chosenQueryType = "'<CONFIG>' =>[\n\t\t\t'<QUERY_TYPE>' => '$queryType',\n\t\t\t'<TABLES>' => ['" . implode('\',\'', array_keys($tbs)) . "']," . ($queryType === 'SELECT' ? $hydrationModeStr : "") . ($queryType === 'SELECT' ? $hydrationTypeStr : "");
        $subQueriesEmpty = ($queryType === 'INSERT' || $queryType === 'UPDATE' || $queryType === 'DELETE') ? "" : "\t\t\t\t'[subquery_example_1]' => 'SELECT COUNT(*)',\n\t\t\t\t'[subquery_example_2]' => '(WHERE SELECT *)'";
        $subQueries = "\n\t\t\t'[SUBQUERIES]' => [\n$subQueriesEmpty\t\t\t]\n\t\t],";
        $DXPART = $chosenQueryType . $subQueries;
        $queryTypePart = "";

        // TODO: Fix all below statements!
        // When 'INSERT'
        if ($queryType === 'INSERT') {
            // Remove the 'id' column from the array since that is auto-incremented
            $tbsColsExceptId = array_keys($tbs[array_key_first($tbs)]['cols']);
            $tbName = key($tbs);
            array_shift($tbsColsExceptId);
            $valCols = $tbsColsExceptId;
            $tbsColsExceptId = implode(',', $tbsColsExceptId);
            $tbsColsExceptId = key($tbs) . ':' . $tbsColsExceptId;
            $queryTypePart .= "\n\t\t'INSERT_INTO' => '$tbsColsExceptId',";
            $bindedValidatedData = "\n\t\t'<MATCHED_FIELDS>' => [// What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey) \n\t\t\t\t'" . implode('\' => \'\',\'', $valCols) . "'=> '',],\n";
            $queryTypePart .= $bindedValidatedData;
            $DXPART .= $queryTypePart;
        }
        // When 'UPDATE'
        elseif ($queryType === 'UPDATE') {
            // Remove the 'id' column from the array since that is auto-incremented
            $tbsColsExceptId = array_keys($tbs[array_key_first($tbs)]['cols']);
            $tbName = key($tbs);
            array_shift($tbsColsExceptId);
            $valCols = $tbsColsExceptId;
            $tbsColsExceptId = implode(',', $tbsColsExceptId);
            $tbsColsExceptId = key($tbs) . ':' . $tbsColsExceptId;
            $queryTypePart .= "'UPDATE_SET' => '$tbsColsExceptId',\n\t\t'WHERE' => '$tbName:id = ?',";
            $bindedValidatedData = "\n\t\t'<MATCHED_FIELDS>' => [// What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey) \n\t\t\t\t'" . implode('\' => \'\',\'', $valCols) . "'=> '','id' => ''],\n";
            $queryTypePart .= $bindedValidatedData;
            $DXPART .= $queryTypePart;
        }
        // When 'DELETE'
        elseif ($queryType === 'DELETE') {
            // Remove the 'id' column from the array since that is auto-incremented
            $tbsColsExceptId = array_keys($tbs[array_key_first($tbs)]['cols']);
            $tbName = key($tbs);
            array_shift($tbsColsExceptId);
            $valCols = $tbsColsExceptId;
            $tbsColsExceptId = implode('|', $tbsColsExceptId);
            $tbsColsExceptId = key($tbs) . ':' . $tbsColsExceptId;
            $queryTypePart .= "'DELETE_FROM' => '$tbName',\n\t\t'WHERE' => '$tbName:id = ?',";
            $bindedValidatedData = "\n\t\t'<MATCHED_FIELDS>' => [// What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey) \n\t\t\t\t'" . implode('\' => \'\',\'', $valCols) . "'=> '','id' => ''],\n";
            $queryTypePart .= $bindedValidatedData;
            $DXPART .= $queryTypePart;
        }
        // When regular 'SELECT'
        elseif ($queryType === 'SELECT') {
            // We want 'id' this time around!
            $tbsColsWithId = [];
            $valCols = [];
            foreach ($tbs as $tbName => $tbData) {
                $tbsColsWithId[$tbName] = $tbData['cols'];
                $valCols[$tbName] = array_keys($tbData['cols']);
            }

            // We now add the 'FROM' which is always a must for every SELECT query!
            $queryTypePart .= "'FROM' => '";
            $queryTypePart .= array_key_first($tbs);
            $queryTypePart .= "',\n\t\t";

            // We now add the 'JOINS' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "// 'JOINS_ON' Syntax: `join_type=table2,table1(table1Col),table2(table2Col)`\n\t\t// Available Join Types: `inner|i|join|j|ij`,`left|l`,`right|r`\n\t\t// Example: `inner=books,authors(id),books(author_id)`\n\t\t'JOINS_ON' => [// Optional, make empty if not joining any tables!";
            $queryTypePart .= "\n\t\t\t\t";

            // We automatically generate all the possible JOINs (inner default) based
            // on what tables were provided in the $tbs array (the '<TABLES>' Key)!
            // Iterate through all defined tables in your full schema (from tables.php)
            $suggestedJoins = cli_parse_joins_on_DFS(array_key_first($tbs), array_keys($tbs), $tables['relationships']);
            if (!empty($suggestedJoins)) {
                $queryTypePart .= implode(",\n\t\t\t\t", $suggestedJoins);
            }
            $queryTypePart .= "],\n\t\t// Optional Keys, leave empty (or remove) if not used!\n\t\t"; // END OF 'JOINS_ON' Key!

            // We add the 'SELECT' part to the $queryTypePart which is the first part of the DXPART
            // and always a must for every SELECT query!
            // We now add the tables with the columns to the $queryTypePart for each table
            // which is inside of " $valCols" in the style: table => col1,col2,col3, we just
            // create a string that is added like `table1:col1,col2,col3|table2:col1,col2,col3`
            $queryTypePart .= "'SELECT' => ";
            $queryTypePart .= "[";
            foreach ($valCols as $tbName => $cols) {
                $queryTypePart .= "'$tbName:" . implode(',', $cols) . "',\n";
            }
            $queryTypePart .= "],\n\t\t";

            // We now add the 'WHERE' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "'WHERE' => '', \n\t\t";

            // We now add the 'GROUP BY' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "'GROUP BY' => '',\n\t\t";

            // We now add the 'HAVING' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "'HAVING' => '',\n\t\t";

            // We now add the 'ORDER BY' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "'ORDER BY' => '',\n\t\t";

            // We now add the 'LIMIT' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "'LIMIT' => '',\n\t\t";

            // We now add the 'OFFSET' which is OPTIONAL for every SELECT query!
            $queryTypePart .= "'OFFSET' => '',\n\t\t// Optional, leave empty if not used!\n\t\t";

            $queryTypePart .= "'<HYDRATION>' => [],\n\t\t";

            // $allValCols will include all the columns from all the tables by concatenating
            // the table name with the column name in the style: table1_col1, table1_col2, table2_col1, etc.
            // When there is only 1 Table though, we just grab all colum nnamens without the table name!
            $allValCols = [];
            foreach ($valCols as $tbName => $cols) {
                if (count($tbs) > 1) {
                    foreach ($cols as $col) {
                        $allValCols[] = $tbName . '_' . $col;
                    }
                } else {
                    $allValCols = array_merge($allValCols, $cols);
                }
            }

            $bindedValidatedData = "// What each Binded Param must match from a Validated Data\n\t\t\t\t// Field Array (empty means same as TableName_ColumnKey)\n\t\t\t\t'<MATCHED_FIELDS>' => [\n\t\t\t\t'" . implode('\' => \'\',\'', $allValCols) . "'=> ''],\n";
            $queryTypePart .= $bindedValidatedData;
            $DXPART .= $queryTypePart;
        }
        // When 'SELECT_DISTINCT'
        elseif ($queryType === 'SELECT_DISTINCT') {
        }
        // When 'SELECT_INTO'
        elseif ($queryType === 'SELECT_INTO') {
        }
        // When invalid Query Type which should not happen at this point
        else {
            cli_err_syntax_without_exit("Invalid Query Type: \"$queryType\". Available Query Types: " . implode(', ', quotify_elements($availableQueryTypes)) . ".");
            cli_info("Pick one of those as the fith argument in the FunkCLI command to create a SQL Handler File and/or Function!");
        }
        $finalString = "\t// FunkCLI created $date! Keep Closing Curly Bracket on its\n\t// own new line without indentation and no comment right after it!\n\t// Run the command `php funk compile:s_eval s_file=>s_fn`\n\t// to get SQL, Hydration & Binded Params in return statement below it!\n\t\$DX = [$DXPART\t];\n\n\treturn array([]);";
    } // END OF SQL TABLES PROCESSING

    // Prepare Validation String based on Tables
    elseif ($sqlOrValidation === 'validation') {
        // Validation Tables can only include "table1,table2,etc3" or just "table1"
        // with optional numbers with * at the end of each table name like "table1*2"
        if (!preg_match('/^([a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $sv_tables)) {
            cli_err_without_exit('[cli_created_sql_or_validation_fn()]: Invalid Validation Tables Syntax! Use either "table1,table2,etc3" or just "table1" with optional numbers with * at the end of each table name like "table1*2"!');
            cli_info('[cli_created_sql_or_validation_fn()]: The Regex Syntax for Validation Tables: `/^([a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/`!');
        }

        // Prepare the tables string for the
        // DXPART. Split on "," if it exists
        $times = [];
        $processTables = str_contains($sv_tables, ",")
            ? explode(',', $sv_tables)
            : [$sv_tables];

        // Extract the number from "*" if it exists and add foreach table to the $times array
        foreach ($processTables as $table) {
            if (str_contains($table, '*')) {
                $parts = explode('*', $table);
                if (count($parts) !== 2 || !is_numeric($parts[1]) || (int)$parts[1] <= 0) {
                    cli_err_syntax_without_exit("Invalid Table Format: \"$table\". Use \"table_name*integer\" or just \"table_name\" for each Table!");
                    cli_info("Even if you do not know the Array Number, just specify a very high integer to prevent infinite loops during Validation!");
                }
                // do not allow duplicates
                if (array_key_exists($parts[0], $times)) {
                    cli_err_syntax("Table \"$parts[0]\" already added. Only use one Table once!");
                }
                $times[$parts[0]] = (int)$parts[1];
            }
            // Default to 1 if no number is specified (as in:`table_name`)
            else {
                if (array_key_exists($table, $times)) {
                    cli_err_syntax("Table \"$table\" already added. Only use one Table once!");
                }
                $times[$table] = 1;
            }
        }

        // We now load the Tables.php file and grab the keys from $times
        // to validate that all the tables exist in the Tables.php file!
        if ($tables === null || $types === null) {
            cli_err_syntax("`Tables.php` or `MySQLDataTypes.php` File not found! Please check your `funkphp/config/tables.php` & `funkphp/config/VALID_MYSQL_DATATYPES.php` Files!");
        }
        foreach ($times as $table => $count) {
            if (!array_key_exists($table, $tables['tables'])) {
                cli_err_syntax("Table \"$table\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
            }
        }

        // their subkeys are actual columns who contain info about what apprioriate rules
        // they should have! Prepare the DXPART string for the validation limiter
        $currDXPart = "";
        $currTable = null;
        $currTablePrefix = "";
        $entireDXPART = "";

        // We now iterate through each table and its count and we use $tbName to find
        // the correct Table in the Tables.php file and the $tbCount to know whether it is
        // an array (e.g. `table_name*2`) or a single object (e.g. `table_name`) of the table.
        foreach ($times as $tbName => $tbCount) {
            $passwordColNameTemp = "";
            $currTable = $tables['tables'][$tbName] ?? null;
            if ($currTable === null) {
                cli_err_syntax("Table \"$tbName\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
            }
            // Set correct prefix for the table based on its count. When array/list we also add the first
            // part of the $DXPART which indicates it is a list of items with the table prefix with a
            // specific count of elements that all other fields (keys) from the table must include!
            if ($tbCount > 1) {
                $currTablePrefix = $tbName . ".*.";
                $entireDXPART .= wrappify_arrowed_string("$tbName.*",  "list|count:$tbCount|required");
            } else {
                $currTablePrefix = $tbName . ".";
            }
            // Now we loop through the selected Table and its keys where each key is the column name
            // which is then the "fieldName" in the DXPART string.
            foreach ($currTable as $key => $subKey) {
                $currDXPart = "";
                // We skip the primary key 'id' column so this must be added manually by Developer!
                // If it s a Foreign Key or Primary Key, we skip it as well for now!
                if ($key === 'id' && isset($subKey['primary_key'])) {
                    continue;
                }

                // We set some possible default rules to insert into the current DXPART
                $dataType = $subKey['type'] ?? null;
                $nullable = isset($subKey['nullable']) && $subKey['nullable'] === true ?
                    "required|nullable|" : "required|";
                $between = isset($subKey['value']) && !is_array($subKey['value']) ? "between:1," . $subKey['value'] . "|" : "between:<MIN>,<MAX>|";
                if (($dataType === 'SET' || $dataType === 'ENUM')) {
                    $between = "";
                }
                $unique = isset($subKey['unique']) && $subKey['unique'] === true ?
                    "unique:$tbName,$key|" : "";
                $exists =  isset($subKey['references']) && isset($subKey['references_column']) ?
                    "exists:" . $subKey['references'] . "," . $subKey['references_column'] . "|" : "";
                $anyValues = "";

                // First check is guessing the data type for the current column based on its 'type'
                // and its key name. For example if it contains 'email' we assume it is an email if
                // the 'type' is a string within the $types variable which contains all possible valid
                // MySQL data types.
                // It is considered valid `email` type if it s a string MySQL data type and
                // the key name contains 'email' in it.
                if (
                    str_contains($key, "email")
                    && isset($types['STRINGS'][$dataType])
                ) {
                    $currDXPart .= "email|";
                }
                // It is considered valid `password` if it is a string MySQL data type and
                // the key name contains 'password' in it while NOT containing "confirm" since
                // that should be handled separately ("password" "confirm" field that is).
                elseif (
                    str_contains($key, "password")
                    && !str_contains($key, "confirm")
                    && isset($types['STRINGS'][$dataType])
                ) {
                    $currDXPart .= "password|";
                    // Store the password column name temporarily to be binded to the
                    // possibly "confirm" field later on if they exist in same table.
                    $passwordColNameTemp = $key;
                }
                // It is considered valid `password_confirm` if it is a string MySQL data
                // typoe and the key name contains both 'password' and 'confirm' in it!
                elseif (
                    str_contains($key, "password")
                    && str_contains($key, "confirm")
                    && isset($types['STRINGS'][$dataType])
                ) {
                    if (!empty($passwordColNameTemp)) {
                        $currDXPart .= "password_confirm:$passwordColNameTemp|";
                    } else {
                        $currDXPart .= "password_confirm:<UNKNOWN_PLEASE_USE_PASSWORD_COLUMN_NAME_HERE>|";
                    }
                }
                // It is considered valid `string` if it is a string MySQL data type
                elseif (isset($types['STRINGS'][$dataType])) {
                    $currDXPart .= "string|";
                }
                // It is considered valid `integer`
                elseif (isset($types['INTS'][$dataType])) {
                    $currDXPart .= "integer|";
                }
                // It is considered valid `float`
                elseif (isset($types['FLOATS'][$dataType])) {
                    $currDXPart .= "float|";
                }
                // It is considered valid `datetimes` datatype
                elseif (isset($types['DATETIMES'][$dataType])) {
                    $currDXPart .= "date|";
                }
                // It is considered valid `blobs` datatype
                elseif (isset($types['BLOBS'][$dataType])) {
                    $currDXPart .= "string|";
                }
                // When it is boolean
                elseif ($dataType === 'BOOLEAN') {
                    $currDXPart .= "boolean|";
                }
                // When it is ENUM or SET
                elseif ($dataType === 'SET' || $dataType === 'ENUM') {
                    $currDXPart .= strtolower($dataType) . "|";
                    $anyValues = is_array($subKey['value']) ?
                        "any_of_these_values:" . implode(',', $subKey['value']) . "|" : "";
                }
                // UNKNOWN DATA TYPE
                else {
                    $currDXPart .= "<!UNKNOWN_DATA_TYPE_CHOOSE_ONE_FOR_THIS_TABLE_COLUMN!>|";
                }

                // We now add $nullable, $between and $unique to the current DXPart if they are not empty strings
                $currDXPart .= $nullable;
                if (!empty($between)) {
                    $currDXPart .= $between;
                }
                if (!empty($unique)) {
                    $currDXPart .= $unique;
                }
                if (!empty($exists)) {
                    $currDXPart .= $exists;
                }
                if (!empty($anyValues)) {
                    $currDXPart .= $anyValues;
                }

                // FINALLY FOR EACH ITERATION ADD THe $currDXPart to the $entireDXPART
                // and then reset the $currDXPart to an empty string for the next iteration.
                // But we remove the trailing "|" if it exists.
                if (str_ends_with($currDXPart, "|")) {
                    $currDXPart = substr($currDXPart, 0, -1);
                }
                $entireDXPART .= wrappify_arrowed_string($currTablePrefix . $key, $currDXPart);
            }
        }
        $DXPART = "\n\t\t'<CONFIG>' => '',\n\t\t" . $entireDXPART;
        $finalString = "\t// FunkCLI created $date! Keep Closing Curly Bracket on its\n\t// own new line without indentation and no comment right after it!\n\t// Run the command `php funkcli compile v file=>fn`\n\t// to get optimized version in return statement below it!\n\t\$DX = [$DXPART\n\t];\n\n\treturn array([]);";
    } // END OF VALIDATION TABLES PROCESSING

    // UNEXPECTED CASE: If $sqlOrValidation is neither "sql" nor "validation" - not allowed!
    else {
        cli_err_without_exit('[cli_created_sql_or_validation_fn()]: $sqlOrValidation must be either "sql" or "validation"!');
        cli_info('[cli_created_sql_or_validation_fn()]: Use either "sql" for SQL Functions or "validation" for Validation Functions!');
    }

    // Return the final string which is the DXPART with the return statement at the end
    return $finalString;
}

// Returns default created function files (a single anoynomous function file
// OR a named function file with a return function at the end). Also handles
// special cases using $arg5 which are for "funkphp/sql" and "funkphp/validation"
function cli_default_created_fn_files($type, $methodAndRoute, $folder, $file, $fn = null, $tables = null)
{
    // Validate $type is a non-empty string and either "named" or "anonymous"
    if (!isset($type) || !is_string($type) || empty($type) || !in_array($type, [
        'named_not_new_file',
        'named_and_new_file',
        'anonymous',
        'sql_new_file_and_fn',
        'sql_only_new_fn',
        'validation_new_file_and_fn',
        'validation_only_new_fn'
    ])) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $type must be a Non-Empty String!');
        cli_info('[cli_default_created_fn_files()]: Use either "named_not_new_file", "named_and_new_file", "anonymous", "sql_new_file_and_fn", "sql_only_new_fn", "validation_new_file_and_fn" or "validation_only_new_fn" as the `$type`! (first arg)');
        return null;
    }
    // Validate $methodAndRoute is a non-empty string which can be any characters except whitespaces or new lines
    if ($methodAndRoute === null) {
        $methodAndRoute = "N/A";
    }
    if (!isset($methodAndRoute) || !is_string($methodAndRoute) || empty($methodAndRoute) || !preg_match('/^(([a-zA-Z]+\/)|([a-zA-Z]+(\/[:]?[a-zA-Z0-9]+)+))$/i', $methodAndRoute)) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $methodAndRoute must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info("[cli_default_created_fn_files()]: The Regex Syntax for Method/Route:`/^(([a-zA-Z]+\/)|([a-zA-Z]+(\/[:]?[a-zA-Z0-9]+)+))$/i`!");
        return null;
    }
    // Validate that $folder is a non-empty string and matches the regex
    if (!isset($folder) || !is_string($folder) || empty($folder) || !preg_match('/^[a-z_][a-z_0-9\/-]*$/i', $folder)) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $folder must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_default_created_fn_files()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9\/-]*)`! (you do NOT need to add a leading slash `/` to the string)');
        return null;
    }
    // Validate that $file is a non-empty string and matches the regex
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $file must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_default_created_fn_files()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
        return null;
    }
    // Validate that if set, $fn is a non-empty string matching a regex
    if (isset($fn) && (!is_string($fn) || empty($fn) || !preg_match('/^[a-z_][a-z_0-9]*$/i', $fn))) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $fn must be A Valid Non-Empty String! (any whitespace is NOT allowed)');
        cli_info('[cli_default_created_fn_files()]: Use the following Function Syntax (Regex):`[a-z_][a-z_0-9]*)`!');
        return null;
    }
    // Validate that if set, $tables is a non-empty string matching a regex
    if (
        isset($tables) &&
        (!is_string($tables)
            || empty($tables)
            || !preg_match('/^(((select|delete|insert|update|sel|del|ins|upd|s|d|i|u)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $tables)
            || (!str_contains($folder, "sql")
                && !str_contains($folder, "validation")))
    ) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $arg5 (tables) must be A Valid Non-Empty String! (any whitespace is NOT allowed)');
        cli_info_without_exit("Regex Used For SQL+Validation Tables: `/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i`!");
        cli_info('[cli_default_created_fn_files()]: It is ONLY for `funkphp/sql` AND `funkphp/validation`, so `$folder` must be `sql` OR `validation`!');
        return null;
    }
    // Replace the "/" to "\" in the $folder and then uppercase each first letter
    // after each slash. This is to ensure that the namespace is correct
    $folder = str_replace('/', '\\', $folder);
    $folderUPPER = strtoupper($folder);

    // Remove ".php" from the $file if it exists and then
    if (str_ends_with($file, '.php')) {
        $file = substr($file, 0, -4);
    }
    // String parts of default created Files:
    $entireCreatedString = '';
    $newFilesString = '';
    $typePartString = '';

    // Parts belonging to new files created like namespace & some comments
    if ($type === "named_not_new_file" || $type === "named_and_new_file") {
        $namespaceString = "<?php\n\nnamespace funkphp\\pipeline\\routes\\$file;\n";
    } else { // "Routes" folder is always used for those $type values!
        $namespaceString = "<?php\n\nnamespace funkphp\\data\\$folder\\$file;\n";
    }
    $newFilesString .= $namespaceString;
    $createdOnCommentString = "// FunkCLI Created on " . date('Y-m-d H:i:s') . "!\n\n";
    $newFilesString .= $createdOnCommentString;

    // Based on $type, we create the necessary File (or just updated File!)
    // When a named function is needed but file ALREADY EXISTS - Funk\Routes\<FOLDER>\<FILE>.php
    if ($type === 'named_not_new_file') {
        $typePartString .= "function $fn(&\$c)\n";
        $typePartString .= "{\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // When a named function is needed and file DOES NOT EXIST - Funk\Routes\<FOLDER>\<FILE>.php
    elseif ($type === 'named_and_new_file') {
        $typePartString .= "function $fn(&\$c)\n";
        $typePartString .= "{\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // Special-case #1: "funkphp/sql" folder
    // New SQL FILE
    elseif ($type === 'sql_new_file_and_fn') {
        $typePartString .= "function $fn(&\$c)\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("sql", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    } // Only NEW SQL FUNCTION in existing file
    elseif ($type === 'sql_only_new_fn') {
        $typePartString .= "function $fn(&\$c)\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("sql", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // Special-case #2: "funkphp/validation" folder
    // New Validation FILE
    elseif ($type === 'validation_new_file_and_fn') {
        $typePartString .= "function $fn(&\$c)\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("validation", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // Only NEW Validation FUNCTION in existing file
    elseif ($type === 'validation_only_new_fn') {
        $typePartString .= "function $fn(&\$c)\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("validation", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // Catch the IMPOSSIBLE edge-case!
    else {
        cli_err_without_exit('[cli_default_created_fn_files()]: Invalid $type provided! Use either "named_not_new_file", "named_and_new_file", "anonymous", "sql" or "validation"!');
        cli_info('[cli_default_created_fn_files()]: The fact You are seeing this strongly suggests you have called the function directly instead of letting other functions calling it indirectly and you have probably removed the first safety-check at the top of the function!');
        return null;
    }
    // Return finalized created string
    return $entireCreatedString;
}

// CLI Test function, by GPT Free-use, might change later.
// Provide Stringed Name of test, then function name, then the tests in an array like
// [['testvalue1' => 'value', 'expected' =>  'expectedValue', 'args' => [arg1, arg2, ...]], ...]
// and optionally the inputKey which is the key in the test array that should be used as input
// for the function (if not using multi-args mode with 'args' key)
function cli_run_tests(string $title, callable $fn, array $tests, string|array|null $inputKey = null)
{
    echo "\n=== {$title} ===\n";
    foreach ($tests as $i => $test) {
        $expected = $test['expected'] ?? null;
        $actual = null;
        // -------------------------
        // CASE 1: multi-args mode
        // -------------------------
        if (isset($test['args']) && is_array($test['args'])) {
            $actual = $fn(...$test['args']);
        }
        // -------------------------
        // CASE 2: named keys mode (route/method/etc)
        // -------------------------
        elseif (is_string($inputKey)) {
            if (!isset($test[$inputKey])) {
                echo "❌ INVALID TEST STRUCTURE at #{$i}\n";
                continue;
            }
            $actual = $fn($test[$inputKey]);
        }
        // -------------------------
        // CASE 3: multiple named inputs (route1, route2)
        // -------------------------
        elseif (is_array($inputKey)) {
            $args = [];
            foreach ($inputKey as $key) {
                if (!isset($test[$key])) {
                    echo "❌ INVALID TEST STRUCTURE at #{$i} (missing {$key})\n";
                    continue 2;
                }
                $args[] = $test[$key];
            }
            $actual = $fn(...$args);
        }
        // -------------------------
        // fallback failure
        // -------------------------
        else {
            echo "❌ INVALID CONFIG at #{$i}\n";
            continue;
        }
        $passed = ($actual === $expected);
        echo sprintf(
            "TEST %d (%s)\n→ Expected: %s | Got: %s\n→ %s\n\n",
            $i + 1,
            json_encode($test),
            json_encode($expected),
            json_encode($actual),
            $passed ? "✅ PASS" : "❌ FAIL"
        );
    }
}

// Checks if a provided String is of typical "/route/:valid/format"
// VF = Validate First meaning you make sure you provide a String
// before calling this function!!
function cli_route_is_valid_string_VF($routeString)
{
    global $cliRegex;
    $routeRegex = $cliRegex['routeRegex'];
    if (
        !isset($routeString)
        || !is_string($routeString)
        || empty($routeString)
        || !preg_match($routeRegex, $routeString)
    ) {
        return false;
    }
    // Cannot start/end with - or _
    $segments = explode('/', trim($routeString, '/'));
    foreach ($segments as $segment) {
        if ($segment === '') {
            continue;
        }
        if (
            str_starts_with($segment, '-') ||
            str_starts_with($segment, '_') ||
            str_ends_with($segment, '-') ||
            str_ends_with($segment, '_')
        ) {
            return false;
        }
        // No consecutive separators
        if (preg_match('/[-_]{2}|-_|_-/', $segment)) {
            return false;
        }
    }
    // segments with ":" cannot have same param names like (":param/:param")
    // so we filter out those with ":" and check if there are duplicates in the remaining array
    $paramSegments = array_filter($segments, fn($seg) => str_starts_with($seg, ':'));
    $paramNames = array_map(fn($seg) => ltrim($seg, ':'), $paramSegments);
    if (count($paramNames) !== count(array_unique($paramNames))) {
        return false;
    }
    return true;
}

// Checks if a provided String is a valid method type, either its full version or
// any of its shorthands: like "GET/", "post/", "g/" and so on. Notice it MUST
// have the '/' at the end or it does not count as a valid method string!
// where method always is uppercase and then route is essentially like
// function "cli_route_is_valid_string_VF".
// VF = Validate First meaning you make sure you provide a String
// before calling this function!!
function cli_route_method_is_valid_string_VF($methodString)
{
    global $cliRegex;
    $routeRegex = $cliRegex['methodSegment'];
    if (
        !isset($methodString)
        || !is_string($methodString)
        || empty($methodString)
        || !preg_match($routeRegex, $methodString)
    ) {
        return false;
    }
    return true;
}

// Checks for valid "METHOD/route/:subroute" string where method is either
// "GET", "POST", "PUT", "DELETE" or "PATCH" (or their shorthands) and
// route is essentially like function "cli_route_is_valid_string_VF".
// VF = Validate First meaning you make sure you provide a String
// before calling this function!!
function cli_route_and_method_is_valid_string_VF($methodAndRouteString)
{
    if (
        !isset($methodAndRouteString)
        || !is_string($methodAndRouteString)
        || empty($methodAndRouteString)
    ) {
        return false;
    }
    // explode on first "/" to separate method from route and the count
    // should be now 2 and none empty. Then add a "/" to the first exploded
    // to check valid method and a "/" to the start of the second exploded
    // element and send it to check for valid route string!
    $exploded = explode('/', $methodAndRouteString, 2);
    if (count($exploded) !== 2 || empty($exploded[0]) || empty($exploded[1])) {
        return false;
    }
    $methodPart = $exploded[0] . '/';
    $routePart = '/' . $exploded[1];
    return cli_route_method_is_valid_string_VF($methodPart) && cli_route_is_valid_string_VF($routePart);
}

// Checks if route (without method part) "/route1" is same as "/route2"
// which also checks against duplicated "/:paramsegments" on same levels
// like "/:route1" compared to "/:route2" which collide due to being
// an exact match if you would replace all ":paramsegment" with a placeholder.
// VF = Validate First meaning you make sure you provide a String + a String
// before calling this function!!
function cli_route_is_same_as_another_route_VF($route, $anotherRoute)
{
    // IMPORTANT: Function really assumes both are pure strings larger than 1 so
    // use the OTHER functions to validate both are valid strings before using this one!
    // First we find out if they have ":" or not because that will matter for first
    // length test which is only valid if BOTH do NOT have the ":"!
    $routeHasParamSegment = str_contains($route, ':');
    $anotherRouteHasParamSegment = str_contains($anotherRoute, ':');
    // Now we check if BOTH do NOT have the ":" because then we can safely
    // check length (=static routes comparisons)
    if (!$routeHasParamSegment && !$anotherRouteHasParamSegment) {
        return $route === $anotherRoute;
    }
    // If one of them does NOT have the ":" but other one does then we now
    // they cannot be equal
    if ($routeHasParamSegment !== $anotherRouteHasParamSegment) {
        return false;
    }
    // Here both seem to have ":" so now we replace all ":paramsegment" with a
    // placeholder and then check if they are equal
    $placeholder = 'PLACEHOLDER';
    $normalizedRoute = preg_replace('/:[a-zA-Z0-9_-]+/', $placeholder, $route);
    $normalizedAnotherRoute = preg_replace('/:[a-zA-Z0-9_-]+/', $placeholder, $anotherRoute);
    return $normalizedRoute === $normalizedAnotherRoute;
}
// Checks if a new provided route (that should be validated before being used here)
// is not colliding in a given method group with any other existing route in that method group.
// For example "/:users" is considered the same as "/:users2" due to being the :params segments
// on the same URI levels! So "/:users/:id" is also colliding with "/:id/:users"!
// VF = Validate First meaning you make sure you provide an Array + a String
// before calling this function!!
function cli_new_route_is_unique_in_its_method_group_VF($ROUTESSource, $newRoute)
{
    // EDGE CASE: if there are no existing routes in the method group,
    // then the new route is automatically unique so we return true without doing any checks!
    if (empty($ROUTESSource)) {
        return true;
    }
    // First transform $newRoute if it has any ":" so they are just ":PLACEHOLDERS"
    if (str_contains($newRoute, ":")) {
        $newRoute = preg_replace('/:[a-zA-Z0-9_-]+/', ':PLACEHOLDER', $newRoute);
    }
    // Iterate through the &$ROUTESSource and then transform its each key first if
    // it has any ":" so they could match and then check if they are the same as
    // the transformed $newRoute using the function cli_route_is_same_as_another_route_VF which also checks for the same ":paramsegment" collisions on same levels!
    foreach ($ROUTESSource as $existingRoute => $routeConfig) {
        // Ignore global config variable for the method group if it exists, since it does
        // not represent an actual route and should not be compared to the new route!
        if ($existingRoute === '<CONFIG>') {
            continue;
        }
        $transformedExistingRoute = str_contains($existingRoute, ":") ?
            preg_replace('/:[a-zA-Z0-9_-]+/', ':PLACEHOLDER', $existingRoute) : $existingRoute;
        if (cli_route_is_same_as_another_route_VF($newRoute, $transformedExistingRoute)) {
            return false;
        }
    }
    return true;
}

// Checks so $existingRoute key has the ['config' => [], ['middlewares' => [], 'pipeline' => []]]
// where either are empty OR the 'middlewares' AND 'pipeline' both are numbered arrays, but where
// the 'config' array is just an associatiev array, empty or not!
// VF = Validate First meaning you make sure you provide a Route Key String
// before calling this function!!
function cli_existing_route_has_valid_key_structure_VF($existingRoute)
{
    if (!is_array($existingRoute)) {
        return false;
    }
    $config = $existingRoute['config'] ?? null;
    $middlewares = $existingRoute['middlewares'] ?? null;
    $pipeline = $existingRoute['pipeline'] ?? null;

    if ($config === null || !is_array($config) || array_is_list($config)) {
        return false;
    }
    if ($middlewares !== null && (!is_array($middlewares) || !array_is_list($middlewares))) {
        return false;
    }
    if ($pipeline !== null && (!is_array($pipeline) || !array_is_list($pipeline))) {
        return false;
    }
    return true;
}

// Checks $existingRoute and its _assumed_ 'pipeline' array has no duplicate keys
// meaning like 0 => 'key1' => 'test", 1 => 'key1' => 'test2' which is allowed but
// will issue a warning by the make-route.php CLI Command though. This function only
// returns true though on first instance, meaning it at least 1 to be true.
// IMPORTANT: only checks for structure not whether 0 => 'FileName' => 'Fn' actually exists!
// VF = Validate First meaning you make sure you provide a Route Key String + a String + a String
// before calling this function!!
function cli_existing_route_has_duplicate_pipeline_fns_VF($existingRoute, $file, $fn)
{
    foreach ($existingRoute['pipeline'] as $fileKey => $fnP) {
        if (strtolower($fileKey) === strtolower($file) && strtolower($fn) === strtolower($fnP)) {
            return true;
        }
    }
    return false;
}

// Checks if $existingRoute and its _assumed_ 'middlewares' array has no duplicate keys
// 'middlewares' is only a numbered array with string value compared to 'pipeline' which
// has 0 => 'FileName' => 'Fn' structure, but this function only checks for duplicate
// string values in the 'middlewares' array, meaning like 0 => 'middleware1', 1 => 'middleware1'
// which is allowed but will issue a warning by the make-route.php CLI Command though.
// This function only returns true though on first instance, meaning it at least 1 to be true.
// IMPORTANT: only checks for structure not whether 0 => 'Fn' actually exists!
// VF = Validate First meaning you make sure you provide Route Key String + a String
// before calling this function!!
function cli_existing_route_has_duplicate_middleware_fns_VF($existingRoute, $fn)
{
    foreach ($existingRoute['middlewares'] as $middlewareFn) {
        if (strtolower($middlewareFn) === strtolower($fn)) {
            return true;
        }
    }
    return false;
}

// Returns the status of a method/route in the routes.php file
function cli_route_status(&$ROUTES, $method, $route)
{
    // Validate that &$ROUTES is an associative array
    if (!isset($ROUTES) || !is_array($ROUTES) || array_is_list($ROUTES)) {
        cli_err_without_exit('[cli_route_status()]: &$ROUTES must be An Associative Array! (passed by reference)');
        cli_info('[cli_route_status()]: Use the `$ROUTES` variable from `funkphp/core/pipeline_routes.php` file which is an Associative Array passed by reference as the first argument!');
    }

    // Validate that $method is a non-empty string that starts with
    // either "GET", "POST", "PUT", "DELETE", or "PATCH",
    if (!isset($method) || !is_string($method) || empty($method) || !in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
        cli_err_without_exit('[cli_route_status()]: $method must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_route_status()]: Use either "GET", "POST", "PUT", "DELETE", or "PATCH" as the `$method`! (first arg)');
    }
    // Validate that $route is a non-empty string which can be any characters except whitespaces or new lines
    if (!isset($route) || !is_string($route) || empty($route) || !preg_match('/^((\/)|((\/[:]?[a-zA-Z0-9_-]+)+))$/i', strtolower($route))) {
        cli_err_without_exit('[cli_route_status()]: $route must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info("[cli_route_status()]: The Regex Syntax for Route:`/^((\/)|((\/[:]?[a-zA-Z0-9_-]+)+))$/i`!");
    }
    // Default values until proven otherwise
    $routeExists = false;
    $methodExists = false;

    // Middlewares related
    $middlewaresExist = false;
    $middlewares = [];
    $inheritedMiddlewares = [];

    // Route keys related
    $routeKeysExist = false;
    $routeKeys = [];
    $invalidRouteKeys = [];

    // This will include warnings about route when it exists but has potential issues
    // However, it will NOT provide any warnings, instead check for the count!
    $WARNINGS = [
        'ROUTE_NOT_LIST_ARRAY' => 'Route is NOT a Numbererd Array! No Iteration Done on Its Keys Thus! (This means one or more of its keys are NOT numeric)',
        'NO_ROUTE_KEYS' => 'Route has NO Route Keys defined! (Consider adding one or more Route Keys OR delete the Method/Route!)',
        'DUPLICATE_ROUTE_KEYS' => 'Route has DUPLICATE Route Keys: ',
        'MIDDLEWARES_ONLY_EXIST' => 'Route has ONLY Middlewares and no other Route Keys!',
        'MIDDLEWARES_NOT_LIST_ARRAY' => 'Route Middlewares is NOT a Numbered Array! (This means one or more of its keys are NOT numeric)',
        'MIDDLEWARES_NOT_FIRST_POSITION' => 'Route Middlewares is NOT at the First Position [0] of the Route Array!',
        'ROUTE_KEYS_NOT_LIST_ARRAY' => 'Route Keys is NOT a Numbered Array! (This means one or more of its keys are NOT numeric)',
        'ROUTE_KEY_NOT_ARRAY' => 'Route Key is NOT an Associative Array! (For some reason it is a different datatype?)',
        'ROUTE_KEYS_DIR_NOT_EXIST' => 'Route Key\'s Folder does NOT exist! (Check the Dir in the Routes)',
        'ROUTE_KEYS_FILE_NOT_EXIST' => 'Route Key\'s Folder\'s File does NOT exist! (Check the File in the Routes/Dir)',
        'ROUTE_KEYS_FUNCTION_NOT_EXIST' => 'Route Key\'s Folder\'s File\'s Function does NOT exist! (Check the Function Name in the Routes/Dir/File)',
    ];
    $routeWarnings = [];

    // Method & Route exists?
    if (isset($ROUTES[$method])) {
        // Route in that Method exists?
        if (isset($ROUTES[$method][$route])) {
            // Existing Route in existing Method is numbered array?
            if (is_array($ROUTES[$method][$route]) && array_is_list($ROUTES[$method][$route])) {
                $routeExists = true;
                $methodExists = true;
            }
            // Invalid Route in existing Method - not a numbered array
            else {
                $routeWarnings[] = $WARNINGS['ROUTE_NOT_LIST_ARRAY'];
                $methodExists = true;
            }
        }
        // Method exist but not Route
        else {
            $methodExists = true;
        }
    }
    // here "else" just means method does not exist
    else {
    }

    // Now we check inside of $route that DOES exist
    if ($routeExists) {
        $foundRoute = $ROUTES[$method][$route];

        // No Route Keys?
        if (empty($foundRoute)) {
            $routeWarnings[] = $WARNINGS['NO_ROUTE_KEYS'];
        }
        // OK, Route Keys exist, let's examine more
        else {
            $routeKeysExist = true;
            // Check for middlewares at the first position [0]
            if (isset($foundRoute[0]) && is_array($foundRoute[0]) && array_key_exists('middlewares', $foundRoute[0])) {
                $middlewaresExist = true;
                $middlewares = $foundRoute[0]['middlewares'];

                // Check if middlewares is a numbered array (important for order)
                if (!is_array($middlewares) || !array_is_list($middlewares)) {
                    $routeWarnings[] = $WARNINGS['MIDDLEWARES_NOT_LIST_ARRAY'];
                }
            }
            // Special case:  $middlewaresExist is true and no other route keys exist
            if ($middlewaresExist && count($foundRoute) === 1) {
                $routeWarnings[] = $WARNINGS['MIDDLEWARES_ONLY_EXIST'];
            }

            // Iterate through all other route keys (excluding the first if it's middlewares)
            foreach ($foundRoute as $key => $routeKeyArray) {

                // Skip the middlewares key if it was the first element
                if ($key === 0 && array_key_exists('middlewares', $routeKeyArray)) {
                    continue;
                }
                // Special warning case: if middlewares found but not at first position
                if (!$middlewaresExist && $key > 0 && array_key_exists('middlewares', $routeKeyArray)) {
                    $routeWarnings[] = $WARNINGS['MIDDLEWARES_NOT_FIRST_POSITION'];
                    $middlewaresExist = true;
                    $middlewares = $routeKeyArray['middlewares'];
                    // Check if middlewares is a numbered array (important for order)
                    if (!is_array($middlewares) || !array_is_list($middlewares)) {
                        $routeWarnings[] = $WARNINGS['MIDDLEWARES_NOT_LIST_ARRAY'];
                    }
                    continue;
                }
                // Special warning case: if middlewares found but not at first position and we already found
                // it somewhere else also wrong so now we also found a duplicate (or we found it at first position)
                // but now a duplicate somewhere else
                if ($middlewaresExist && $key > 0 && array_key_exists('middlewares', $routeKeyArray)) {
                    $routeWarnings[] = $WARNINGS['DUPLICATE_ROUTE_KEYS'] . "'middlewares'";
                    continue;
                }

                // Extract and store all other route keys
                if (is_array($routeKeyArray) && !empty($routeKeyArray) && !array_is_list($routeKeyArray)) {
                    $validStructure = cli_folder_file_fn_value_exist_or_null($routeKeyArray);
                    if ($validStructure['valid']) {
                        $routeKeys[] = $routeKeyArray;
                    } else {
                        $invalidRouteKeys[] = $validStructure;
                        if (!$validStructure['dir']) {
                            $routeWarnings[] = $WARNINGS['ROUTE_KEYS_DIR_NOT_EXIST'];
                        } elseif (!$validStructure['file']) {
                            $routeWarnings[] = $WARNINGS['ROUTE_KEYS_FILE_NOT_EXIST'] . " (Dir: " . ($validStructure['dir'] ?? '<UNKNOWN>') . ")";
                        } elseif (!$validStructure['fn']) {
                            $routeWarnings[] = $WARNINGS['ROUTE_KEYS_FUNCTION_NOT_EXIST'] . " (Dir: " . ($validStructure['dir'] ?? '<UNKNOWN>') . ", File: " . ($validStructure['file'] ?? '<UNKNOWN>') . ")";
                        }
                    }
                } else {
                    $routeWarnings[] = $WARNINGS['ROUTE_KEY_NOT_ARRAY'];
                }
            }
        }
    }

    return [
        "method_provided" => strtoupper($method),
        "route_provided" => strtolower($route),
        "method_exists" => $methodExists,
        "route_exists" => $routeExists,
        "method_route_exists" => ($methodExists && $routeExists),
        "middlewares_exist" => $middlewaresExist,
        "middlewares_in_route" => $middlewares,
        "middlewares_inherited" => $inheritedMiddlewares,
        "route_keys_exist" => $routeKeysExist,
        "valid_route_keys" => $routeKeys,
        "invalid_route_keys" => $invalidRouteKeys,
        "possible_issues" => $routeWarnings,
    ];
}

// Returns an array of status of $folder & $file and whether they:
// - exist, - are readable, - are writable, - the number of functions
// and each function its $DX and/or return array(). Also the entire file
// is read into a raw string and each function is as well so CRUD can
// be done for that file assuming its a PHP file with functions. If
// `return function` exists in it (like middlewares), it's included.
function cli_folder_and_php_file_status($folder, $file)
{
    // QoL fix for $folder if it is a string and starts with a slash
    if (is_string($folder) && str_starts_with(trim($folder), "/")) {
        $folder = substr(trim($folder), 1);
    }
    // Validate both are non-empty strings and match the regex
    if (!isset($folder) || !is_string($folder) || empty($folder) || !preg_match('/^[a-z_][a-z_0-9\/-]*$/i', $folder)) {
        cli_err_without_exit('[cli_folder_and_php_file_status()]: $folder must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_folder_and_php_file_status()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9\/-]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_folder_and_php_file_status()]: $file must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_folder_and_php_file_status()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
    }
    // Consistently get '$folder' . '/' . $file . '.php' always!
    $folder = trim($folder);
    $providedFolder  = $folder; // Original folder for reference
    $file = trim($file);
    $filename = '<UNKNOWN>';
    $singleFolder = '<UNKNOWN>';
    if (str_ends_with($folder, '/')) {
        $folder = rtrim($folder, '/');
    }
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    if (str_starts_with($file, '/')) {
        $file = ltrim($file, '/');
    }
    $folder = PROJECT_DIR . '/' . $folder;
    // $singleFolder is the last part of the folder path
    $singleFolder = basename($folder);
    $filename = $file;
    $file = $folder . '/' . $file;

    // If file exists and is readable, check if function exists
    // by first reading the file and then checking if
    // the function name is in the file content using regex!
    $fnRegex = '/^function\s+([a-zA-Z_][a-zA-Z0-9_]*)\(&\$[^)]*\)(.*?^};)?$/ims';
    $dxRegex = '/\$DX\s*=\s*\[\s*\'.*?];$/ims';
    $namespaceRegex = '/^namespace\s*(.*?)[;\n]$/ims';
    $classRegex = '/^class\s+[a-z_A-Z][a-zA-Z0-9_]*\s*{(.*?)}$/ims';
    $returnRegex = '/return\s*array\(.*?\);$\n/ims';
    $returnFnRegex = '/^(?:(<\?php\s*))?(return function)\s*\(&\$c\s*.+$.*?^};/ims';
    $fns = null;
    $classExists = false;
    $classes = [];
    $namespaceExists = false;
    $namespaceParts = null;
    $fileRaw = null;
    $fileReturnRaw = null;
    if (is_file($file) && is_readable($file)) {
        $fileCnt = file_get_contents($file);
        if (!$fileCnt) {
            cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT Read the File `' . $file . '` when it SHOULD have been Readable. This means that Named Functions, their $DX and/or Return arrays(), OR Anonymous Function Files CANNOT be retrieved for use!');
        } else {
            $fileRaw = $fileCnt;
            // Check if namespace exists which should start on a new line and end with ;
            if (preg_match($namespaceRegex, $fileCnt, $namespaceMatch)) {
                $namespaceExists = true;
                // we split on namespace parts and also remove last ;
                $namespaceParts = explode('\\', rtrim($namespaceMatch[1] ?? '', ';'));
            }
            // if (preg_match($returnFnRegex, $fileRaw, $fileReturnMatch)) {
            //     $fileReturnRaw = $fileReturnMatch[0] ?? null;
            // } else {
            //     cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT find the Expected Anoynmous `return function` in the File `' . $file . '` when it SHOULD have been Found. This means it will NOT be possible to add any new Functions to this File (unless it is a Single Anonymous Function File) since it needs that matched part to add new functions from. This is due to the Regex: `/^(?:(<\?php\s*))?(return function)\s*\(&\$c\s*.+$.*?^};/ims` that cannot match `return function(&$c){};`!');
            // }
            if (preg_match_all($fnRegex, $fileCnt, $fnsMatches)) {
                foreach ($fnsMatches[1] as $idx => $fn) {
                    $fns[$fn] = [
                        'fn_raw' => $fnsMatches[0][$idx] ?? null,
                        'dx_raw' => null,
                        'return_raw' => null
                    ];
                    // We now use the index to match for $DX and return arrays
                    if (preg_match($dxRegex, $fnsMatches[0][$idx], $dxMatch)) {
                        $fns[$fn]['dx_raw'] = $dxMatch[0] ?? null;
                    }
                    if (preg_match($returnRegex, $fnsMatches[0][$idx], $returnMatch)) {
                        $fns[$fn]['return_raw'] = $returnMatch[0] ?? null;
                    }
                }
            }
            // Check first if any class exist and then push all those
            // that exists to the "classes" subkey array in return []
            if (preg_match_all($classRegex, $fileCnt, $classMatches)) {
                $classExists = true;
                foreach ($classMatches[0] as $idx => $class) {
                    $classes[] = [
                        'class_raw' => $class,
                        'class_name' => null
                    ];
                    // We now use the index to match for class name
                    if (preg_match('/^class\s+([a-z_A-Z][a-zA-Z0-9_]*)\s*{/', $classMatches[0][$idx], $classNameMatch)) {
                        $classes[count($classes) - 1]['class_name'] = $classNameMatch[1] ?? null;
                    }
                }
            }
        }
    }
    return [
        'class_exists' => $classExists,
        'classes' => $classes,
        'namespace_exists' => $namespaceExists,
        'namespace_name' => ($namespaceExists ? $namespaceMatch[1] ?? null : null),
        'namespace_parts' => $namespaceParts,
        'folder_provided_path' => $providedFolder ?? null,
        'folder_name' => $singleFolder ?? null,
        'folder_path' => ((is_string($folder) && is_dir($folder) && is_readable($folder) && is_writable($folder)) ? $folder : null),
        'folder_exists' => is_dir($folder),
        'folder_readable' => is_readable($folder),
        'folder_writable' => is_writable($folder),
        'file_name' => $filename,
        'file_path' => ((is_file($file) && is_readable($file) && is_writable($file)) ? $file : null),
        'file_exists' => is_file($file),
        'file_readable' => is_readable($file),
        'file_writable' => is_writable($file),
        'functions' => (isset($fns) ? $fns : []),
        'file_raw' => ['entire' => $fileRaw ?? null, 'return function' => $fileReturnRaw ?? null],
    ];
}

// Function that expects and uses the array from the function above
// "cli_folder_and_php_file_status()" to either Create a new Folder
// with a PHP File that is just Anoymous Function (middlewares &
// pipeline functions) OR to Create a new Folder with a PHP File
// that has a Named Function (like route handlers, etc.) meaning it
// has a return function at the end of the file and named functions
// inside it. $crudType is = "create" or "delete" where "create" will
// also act as "update" if the file already exists and it just adds
// a new function to the file. If $crudType is "delete" it deletes
// a named function from the file and if that is the last named
// function, it deletes the file as well so it counts the named functions
// first because count(1) means it can just delete the file instead.
// $arg5 is for "tables" or other special cases that are ONLY for
// "funkphp/sql" AND "funkphp/validation" folders!
function cli_crud_folder_and_php_file($statusArray, $crudType, $file, $fn = null, $folderType = null, $methodAndRoute = null, $table = null)
{
    global $reserved_functions; // List of reserved functions meaning $fn CANNOT be one of them!
    // Assume success is true, if any error occurs we just set it to false
    $success = true;
    // $statusArray must be an array with the structure
    // returned by "cli_folder_and_php_file_status()"
    if (!isset($statusArray) || !is_array($statusArray) || empty($statusArray)) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $statusArray must be a Non-Empty Array returned by "cli_folder_and_php_file_status()"!');
        cli_info("It needs the following Keys: 'folder_name', 'folder_path', 'folder_exists', 'folder_readable', 'folder_writable', 'file_name', 'file_path', 'file_exists', 'file_readable', 'file_writable', 'functions' and 'file_raw'!");
        return null;
    }
    $requiredKeys = [
        'classes',
        'class_exists',
        'namespace_exists',
        'folder_provided_path',
        'folder_name',
        'folder_path',
        'folder_exists',
        'folder_readable',
        'folder_writable',
        'file_name',
        'file_path',
        'file_exists',
        'file_readable',
        'file_writable',
        'functions',
        'file_raw'
    ];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $statusArray)) {
            cli_err_without_exit('[cli_crud_folder_and_php_file()]: $statusArray must contain the Key: `' . $key . '`!');
            cli_info("It needs the following Keys: `" . implode(', ', $requiredKeys) . "`!");
            return null;
        }
    }
    // $crudType must be a string and either "create" or "delete"
    if (
        !isset($crudType)
        || !is_string($crudType)
        || empty($crudType)
        || !in_array($crudType, [
            'create_new_anonymous_file',
            'create_new_file_and_fn',
            'create_only_new_fn_in_file',
            'delete'
        ])
    ) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $crudType must be a Non-Empty String using one of the following Valid String Values! (see INFO next below)');
        cli_info('[cli_crud_folder_and_php_file()]: Valid String Values: "create_new_anonymous_file", "create_new_file_and_fn", "create_only_new_fn" OR "delete" as the $crudType!');
        return null;
    }
    // $file must be a string and match the regex, we also perform some QoL fixes
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $file must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_crud_folder_and_php_file()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
        return null;
    }
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    if (str_starts_with($file, '/')) {
        $file = ltrim($file, '/');
    }
    // If $fn is set it must be a string and match
    // the regex and not in reserved functions
    if (isset($fn) && (!is_string($fn)
        || empty($fn)
        || !preg_match('/^[a-z_][a-z_0-9]*$/i', $fn)
        || in_array($fn, $reserved_functions))) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $fn must be A Valid Non-Empty String! (any whitespace is NOT allowed)');
        cli_info_without_exit('[cli_crud_folder_and_php_file()]: The Function Name cannot be a Reserved Function Name which is usually prefixed with "funk_" or "cli_"!');
        cli_info('[cli_crud_folder_and_php_file()]: Use the following Function Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string)');
        return null;
    }

    // Extract data from the $statusArray for
    // easier flow of the function from here on
    $folder_provided_path = $statusArray['folder_provided_path'] ?? null;
    $folder_name = $statusArray['folder_name'];
    $folder_path = $statusArray['folder_path'];
    $folder_exists = $statusArray['folder_exists'];
    $folder_readable = $statusArray['folder_readable'];
    $folder_writable = $statusArray['folder_writable'];
    $file_name = $statusArray['file_name'];
    $file_path = $statusArray['file_path'];
    $file_exists = $statusArray['file_exists'];
    $file_readable = $statusArray['file_readable'];
    $file_writable = $statusArray['file_writable'];
    $functions = $statusArray['functions'];
    $file_raw_entire = $statusArray['file_raw']['entire'];
    $file_raw_return_fn = $statusArray['file_raw']['return function'];

    // This is the assumed full path if a file
    // does not exist and must be created
    $outputNewFile = $folder_path . '/' . $file_name;

    // $table is optional and if set, it must be a string and match the regex
    // for special cases for the folders "funkphp/sql" or "funkphp/validation"
    if (
        isset($table) &&
        (!is_string($table)
            || empty($table)
            || !preg_match('/^(((select|delete|insert|update|sel|del|ins|upd|s|d|i|u)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $table)
            || (!str_contains($folder_provided_path, "funkphp/data/sql")
                && !str_contains($folder_provided_path, "funkphp/data/validation")))
    ) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $table (or "arg3") must be A Valid Non-Empty String with at least 1 table or several separated by commas! (NO Whitespace is allowed)');
        cli_info('[cli_crud_folder_and_php_file()]: It is meant ONLY for `funkphp/sql` AND `funkphp/validation`!');
        return null;
    }

    // "create_XYZ" CRUD Type which either creates a new folder+new file if not
    // existing OR updates the existing file by adding a new function to it
    // ONLY Single Anonymous Function File is created
    if ($crudType === 'create_new_anonymous_file') {
        // SPECIAL-CASE: 'pipeline' Folder Type can have their files
        // either in "pipeline/post-request" OR "pipeline/request"
        // so we check if the file already exists in either of those and
        // error out if it does! Since these should not exist if being created!
        if ($folderType === 'pipeline') {
            if ($file_exists) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline` Folder!');
                return false;
            } elseif (file_exists($folder_path . '/post_request' . '/' . $file)) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline/post_request` Folder!');
                return false;
            } elseif (file_exists($folder_path . '/request' . '/' . $file)) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline/request` Folder!');
                return false;
            }
        }
        // Not Special-case but middlewares are also anonymous functions
        // meaning they should not exist in the folder if they should be created!
        elseif ($folderType === 'middlewares') {
            if ($file_exists) {
                cli_err_without_exit('Middleware Function File `' . $file_name . '` already exists in the `funkphp/pipeline/middlewares` Folder!');
                return false;
            }
        }
        $newFile = cli_default_created_fn_files('anonymous', "N/A", $folder_name, $file_name, null, null);

        // If $newFile is not a string, we error out
        if (!is_string($newFile) || empty($newFile)) {
            cli_err_without_exit('FAILED to create a New Anonymous Function File for Folder `' . $folder_name . '` and File `' . $file_name . '`!');
            cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
            return false;
        }
        // It worked, so we now output it in the folder path with the file name
        if (!$folder_exists || !$folder_readable || !$folder_writable) {
            cli_err_without_exit('Folder `' . $folder_name . '` does NOT exist or is NOT Readable/Writable!');
            cli_info_without_exit('Verify Folder Path `' . $folder_path . '` exists AND is Readable/Writable.');
            return false;
        }
        $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
        if (!$tryOuput) {
            cli_err_without_exit('FAILED to Create a New Anonymous Function File `' . $file_name . '` in Folder `' . $folder_name . '`!');
            cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
            return false;
        } else {
            return true; // Success, file created successfully
        }
    }
    // A NEW FILE WITH A NAMED FUNCTION is created!
    elseif ($crudType === 'create_new_file_and_fn') {
        // NEW ROUTE SubFolder With New File & Fn
        if ($folderType === 'routes') {
            $newFile = cli_default_created_fn_files('named_and_new_file', $methodAndRoute, $folder_name, $file_name, $fn);
            // If $newFile is not a string, we error out
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a New Named Function File for Folder `' . $folder_name . '` and File `' . $file_name . '` with Function Name `' . $fn .  '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            // Folder has already been created so we just try output file
            $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New Anonymous Function File `' . $file_name . '` in Folder `' . $folder_name . '` with Function Name `' . $fn .  '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
        // NEW FILE WITH A NAMED FUNCTION in "funkphp/sql"
        elseif ($folderType === 'sql') {
            $newFile = cli_default_created_fn_files('sql_new_file_and_fn', null, $folder_name, $file_name, $fn, $table);
            // If $newFile is not a string, we error out
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a SQL Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to create a SQL Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
        // NEW FILE WITH A NAMED FUNCTION in "funkphp/validation"
        elseif ($folderType === 'validation') {
            $newFile = cli_default_created_fn_files('validation_new_file_and_fn', null, $folder_name, $file_name, $fn, $table);
            // If $newFile is not a string, we error out
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a Validation Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to create a Validation Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
    }
    // A NEW FUNCTION is created in an EXISTING FILE
    elseif ($crudType === 'create_only_new_fn_in_file') {
        // NEW FUNCTION in EXISTING ROUTE SubFolder With Existing File & Fn
        if ($folderType === 'routes') {
            $newFile = cli_default_created_fn_files('named_not_new_file', $methodAndRoute, $folder_name, $file_name, $fn);
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a New Named Function (`' . $fn . '`) for Folder `' . $folder_name . '` and File `' . $file_name . '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            // We now replace the entire raw part with the $newFile since that now
            // contains the new function as well as the return function at the end
            $fileRaw = $file_raw_entire . "\n" . $newFile;
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New Named Function (`' . $fn . '`) in the File `' . $file_name . '` in Folder `' . $folder_name . '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file updated successfully
            }
        }
        // NEW FUNCTION in EXISTING "funkphp/sql" File
        elseif ($folderType === 'sql') {
            $newFile = cli_default_created_fn_files('sql_only_new_fn', null, $folder_name, $file_name, $fn, $table);
            $fileRaw = $file_raw_entire . "\n" . $newFile;
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New SQL Function (`' . $fn . '`) in the File `' . $file_name . '` in `funkphp/sql`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
        // NEW FUNCTION in EXISTING "funkphp/validation" File
        elseif ($folderType === 'validation') {
            $newFile = cli_default_created_fn_files('validation_only_new_fn', null, $folder_name, $file_name, $fn, $table);
            $fileRaw = $file_raw_entire . "\n" . $newFile;
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New Validation Function (`' . $fn . '`) in the File `' . $file_name . '` in `funkphp/validation`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
            } else {
                return true; // Success, file created successfully
            }
        }
    }
    // "delete" CRUD Type which deletes a named function from the file
    // and if that was the last named function, it deletes the file as well
    // meaning for just an anonymous function file, it deletes the file
    elseif (($crudType === 'delete')) {
        // First we check that the folder AND file exist, are readable & writable
        if (!$folder_exists || !$folder_readable || !$folder_writable) {
            cli_err_without_exit('Folder `' . $folder_name . '` does NOT exist or is NOT readable/writable!');
            cli_info_without_exit('Please check the Folder Path `' . $folder_path . '` and ensure it exists AND is readable/writable.');
            cli_info_without_exit('Because of this, it has not been determined whether the intended File actually exists in the possibly correct folder!');
            return false;
        }
        if (!$file_exists || !$file_readable || !$file_writable) {
            cli_err_without_exit('File `' . $file_name . '` does NOT exist or is NOT readable/writable!');
            return false;
        }
        // If $fn is not set, we assume we want to delete the entire file
        if (!isset($fn) || empty($fn)) {
            // Safety-check, $functions should be an empty array now if we assumed
            // this was a file with just a single anonymous function!
            if (is_array($functions) && !empty($functions)) {
                cli_err_without_exit('Function(s) FOUND in the File `' . $file_name . '` when trying to Delete it as a File with a Single Anonymous Function!');
                cli_important_without_exit('Manually VALIDATE that the File `' . $file_name . '` is indeed a File with ONLY a Single Anonymous Function!');
                return false;
            }
            if (unlink($file_path)) {
                cli_success_without_exit('File `' . $file_name . '` Deleted SUCCESSFULLY!');
            } else {
                cli_err_without_exit('FAILED to Delete the File `' . $file_name . '`!');
                return false;
            }
        }
        // If $fn IS set, we must check if it exists in the file first and then
        // we check if it is the last named function in the file because then
        // we can just delete file instead of just the named function
        elseif (isset($fn) && is_string($fn) && !empty($fn)) {
            // $fn does NOT exist in the file, so we error out
            if (!array_key_exists($fn, $functions)) {
                cli_err_without_exit('Function `' . $fn . '` does NOT exist in the File `' . $file_name . '`!');
                cli_info_without_exit('Please check the File `' . $file_name . '` and ensure the Function `' . $fn . '` exists in it!');
                return false;
            }
            // $fn DOES exist in the file, so we check if it is the last named function
            else {
                if (count($functions) === 1) {
                    // If it is the last named function, we delete the file
                    if (unlink($file_path)) {
                        cli_success_without_exit('Function `' . $fn . '` Deleted SUCCESSFULLY from the File `' . $file_name . '`!');
                        cli_success_without_exit('File `' . $file_name . '` Deleted SUCCESSFULLY due to no more Named Functions in it!');
                    } else {
                        cli_err_without_exit('FAILED to Delete the File `' . $file_name . '`!');
                        return false;
                    }
                } else {
                    // If it is NOT the last named function, we just remove it from the file
                    $fnRaw = $functions[$fn]['fn_raw'] ?? null;
                    if ($fnRaw) {
                        $fileRaw = str_replace($fnRaw, '', $file_raw_entire);
                        if (file_put_contents($file_path, $fileRaw) !== false) {
                            cli_success_without_exit('Function `' . $fn . '` Deleted SUCCESSFULLY from the File `' . $file_name . '`!');
                            cli_info_without_exit('`' . count($functions) - 1 . '` Function(s) left in the File `' . $file_name . '`!');
                        } else {
                            cli_err_without_exit('FAILED to Delete the Function `' . $fn . '` from the File `' . $file_name . '`!');
                            return false;
                        }
                    } else {
                        cli_err_without_exit('Function `' . $fn . '` does NOT exist in the File `' . $file_name . '`!');
                        return false;
                    }
                }
            }
        }
    }
    // Impossible edge-case, we should never reach here
    else {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: Invalid $crudType provided! Use either "create_new_anonymous_file", "create_new_file_and_fn", "create_only_new_fn_in_file" or "delete" as the $crudType!');
        cli_info('[cli_crud_folder_and_php_file()]: The fact You are seeing this strongly suggests you have called the function directly instead of letting other functions calling it indirectly and you have probably removed the first safety-check at the top of the function!');
        return false;
    }

    // Return the boolean value of $success (false means failed)
    return $success;
}

// Function that writes $fileContent to a temp file and then renames it
// to the $file_path so it is an atomic write operation. Good stuff!
function cli_crud_folder_php_file_atomic_write($fileContent, $file_path)
{
    // Write the content to the temporary file
    $tempFilePath = $file_path . '.tmp';
    if (file_put_contents($tempFilePath, $fileContent) === false) {
        cli_err_without_exit('FAILED to Write Content to Temporary File `' . $tempFilePath . '`!');
        return false;
    }
    // Rename the temporary file to the actual file path
    // Also clean up the temp file if rename fails (@unlink)
    // @ means ignore errors if the temp file does not exist
    if (!rename($tempFilePath, $file_path)) {
        @unlink($tempFilePath);
        cli_err_without_exit('FAILED to Rename Temporary File `' . $tempFilePath . '` back to Correct File Path `' . $file_path . '`!');
        return false;
    }
    // THE CROSS-PLATFORM BRIDGE:
    // Linux/Mac: Makes it globally modifiable so VS Code and Web users don't conflict.
    // Windows: Unchecks the "Read-Only" file attribute box cleanly.
    // @ ensures no system warnings leak out if the OS environment is strictly locked down.
    // This is done so FunKGUI will be able to modify the file if needed without permission issues and also so
    // that code users can modify the file without permission issues since they usually have a different user
    // than the one running the CLI commands. It also ensures that there are no permission issues for users in
    // general when trying to modify the file after it is created by the CLI.
    @chmod($file_path, 0666); // 0 = no special permissions, 6 = read/write for owner, group, and others
    return true;
}

// Function to check if a middleware exists in a given method/route which
// can either be just a 'middlewares' => 'm_name' or 'middlewares' => ['m_name1', 'm_name2']
// or 'middlewares' => ['m_name1' => 'passedValue', 'm_name2'] meaning we
// must check for three different cases of existence of the middleware
function cli_mw_exists_in_route(&$ROUTES, $method, $route, $mw_name)
{
    // Check $method, $route and $mw_name are non-empty strings
    if (
        !isset($method)
        || !is_string($method)
        || empty($method)
        || !isset($route)
        || !is_string($route)
        || empty($route)
        || !isset($mw_name)
        || !is_string($mw_name)
        || empty($mw_name)
    ) {
        cli_err_without_exit('[cli_mw_exists_in_route()]: $method, $route and $mw_name must be Non-Empty Strings!');
        return false;
    }
    // Check that $ROUTES an array and has the method and route
    if (!is_array($ROUTES) || !array_key_exists($method, $ROUTES) || !array_key_exists($route, $ROUTES[$method])) {
        cli_err_without_exit('[cli_mw_exists_in_route()]: $ROUTES must be an Array and must contain the provided $method and $route!');
        return false;
    }
}

/**
 * Validates that all non-empty prefixes across a command's arguments are unique.
 * Exits with a fatal error if duplicate non-empty prefixes are found.
 *
 * @param string $command The command name (e.g., 'make:route') to look up configuration.
 * @return void
 */
function cli_validate_command_prefixes(string $command): void
{
    global $cliCommands;
    // We assume the caller (cli_get_cli_input_from_interactive_or_regular)
    // has already validated $command and the map structure.
    $configMap = $cliCommands['commands'][$command];
    // Safety check for args container, although cli_get_cli_input_from_interactive_or_regular handles the fatal error.
    if (!isset($configMap['args']) || !is_array($configMap['args'])) {
        return;
    }
    $prefixMap = [];
    $duplicates = [];
    foreach ($configMap['args'] as $argName => $argConfig) {
        // Assume 'prefix' key exists due to validation in cli_get_cli_input_from_interactive_or_regular,
        // but we'll use null-coalescing just in case we call this function independently.
        $prefix = $argConfig['prefix'] ?? '';
        // Only check non-empty prefixes
        if (!empty($prefix)) {
            // Check if this prefix has already been mapped to a different argument
            if (isset($prefixMap[$prefix])) {
                // If the mapped argument name is NOT the current argument name, it's a true duplicate clash.
                if ($prefixMap[$prefix] !== $argName) {
                    $duplicates[] = [
                        'prefix' => $prefix,
                        'arg1'   => $prefixMap[$prefix],
                        'arg2'   => $argName,
                    ];
                }
            } else {
                // Register the prefix with the argument name
                $prefixMap[$prefix] = $argName;
            }
        }
    }
    if (!empty($duplicates)) {
        $errorDetails = [];
        foreach ($duplicates as $d) {
            $errorDetails[] = "Prefix '{$d['prefix']}' used by both argument '{$d['arg1']}' and '{$d['arg2']}'.";
        }
        $errorMsg = "Configuration Error: Command '{$command}' has conflicting argument prefixes. Duplicate prefixes prevent the Standard CLI Mode from reliably identifying which argument a user is providing.";
        $errorMsg .= "\nDetails:\n" . implode("\n", $errorDetails);
        cli_err($errorMsg);
    }
}
/**
 * Prompts the user for input, validates it against a regex, and handles optional/default values.
 * Loops until a valid non-empty value (if required) or a skippable value is provided.
 *
 * @param string $prompt       The question/prompt to display to the user.
 * @param string $regex        The PCRE regex pattern to validate the input against.
 * @param bool $required       If true, input must not be empty (and cannot use default value).
 * @param string|null $default The default value to use if input is empty and not required.
 * @param string|null $helpText Optional help text to display on validation errors.
 * @param callable|null $externalCallableValidator Optional external callable for additional validation (not implemented yet).
 * @param string $prefix Optional prefix to add to the input value (before validation).
 * @return string|null         The validated and trimmed user input (or the default value).
 */
function cli_get_valid_cli_input($prompt, $regex, $required = true, $default = null, $helpText = null, $externalCallableValidator = null, $prefix = '')
{
    // $prefix must be a string if set
    if (isset($prefix) && !is_string($prefix)) {
        cli_err('Provided $prefix value for cli_get_valid_cli_input() is NOT a String which it must be if you wanna use a prefix that is added to the input value before validation. This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // $prompt must be a non-empty string
    if (!isset($prompt) || !is_string($prompt) || empty($prompt)) {
        cli_err('Provided $prompt value for cli_get_valid_cli_input() is NOT a Non-Empty String which it must be! This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // $regex must be a non-empty string
    if (!isset($regex) || !is_string($regex) || empty($regex)) {
        cli_err('Provided $regex value for cli_get_valid_cli_input() is NOT a Non-Empty String which it must be! This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // $required must be a boolean (so null is not allowed as "pseudo-false")
    if (!is_bool($required)) {
        cli_err('Provided $required value for cli_get_valid_cli_input() is NOT a Boolean(false|true) which it must be! Using "nulL" as "pseudo-false" is not possible in current version of FunkPHP. This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // $default must be a string if set
    if (isset(($default)) && !is_string($default)) {
        cli_err('Provided $default value for cli_get_valid_cli_input() is NOT a String which it must be if you wanna use a default value that is used when skipping CLI input. Set it to `null` to omit it. This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // $helpText must be a non-empty string if set
    if (isset($helpText) && (!is_string($helpText) || empty($helpText))) {
        cli_err('Provided $helpText value for cli_get_valid_cli_input() is NOT a Non-Empty String which it must be if you wanna use help text that is displayed on validation errors. This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // $externalCallableValidator must be an existing callable if set
    if (isset($externalCallableValidator) && !is_callable('cli_external_validator_' . $externalCallableValidator)) {
        cli_err('Provided $externalCallableValidator value for cli_get_valid_cli_input() is NOT a Callable which it must be if you wanna use an external callable function for additional validation. You should NOT add "cli_external_validator_" manually, as this is done automatically. This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
    }
    // Display default value if used
    $defaultDisplay = '';
    if ($default !== null) {
        $defaultDisplay = " \033[36m[$default]\033[0m";
    }
    while (true) {
        // 1. Display prompt with required/optional context & read input using STDIN
        $requiredText = $required ? "\033[36m[FunkCLI - REQUIRED]\033[0m" : "\033[36m[FunkCLI - OPTIONAL]\033[0m";
        echo $requiredText . "\033[36m " . $prompt . $defaultDisplay . "\033[0m\n";
        $input = trim(fgets(STDIN));
        // 2. Handle Empty Input (The core logic addition)
        if (empty($input)) {
            // Check and use default value if provided
            if ($default !== null) {
                return $default;
            }
            // If required, display error and continue loop
            if ($required) {
                cli_err_without_exit('Required input and must Match Regex Pattern:`' . $regex . '`. (You can omit the prefix if any)');
                if (isset($helpText) && is_string($helpText) && !empty($helpText)) {
                    cli_info_without_exit($helpText);
                }
                continue;
            }
            // No default and not required, so we return null
            return null;
        }
        // Add prefix to input value if set so it will work
        // with the regex but without user having to type it
        if (isset($prefix) && is_string($prefix) && !empty($prefix)) {
            $input = $prefix . $input;
        }
        // 3. Validation against Regex and either return valid input or continue loop
        if (!preg_match($regex, $input)) {
            // Note: Fixed your string concatenation error here:
            cli_err_without_exit('Invalid input format - must Match Regex Pattern:`' . $regex . '`. (You can omit the prefix if any)');
            if (isset($helpText) && is_string($helpText) && !empty($helpText)) {
                cli_info_without_exit($helpText);
            }
            continue;
        }
        // OPTIONAL: External Callable Validation if provided which then expects true
        // or false return value to determine validity
        if (isset($externalCallableValidator) && is_callable('cli_external_validator_' . $externalCallableValidator)) {
            $isValid = call_user_func('cli_external_validator_' . $externalCallableValidator, $input);
            // if $isValid is NOT a boolean, we error out and mention that the external validator function is not correctly configured
            // as it should only return "true" or "false"
            if (!is_bool($isValid)) {
                cli_err('External Callable Validator Function:`' . 'cli_external_validator_' . $externalCallableValidator . '` for cli_get_valid_cli_input() did NOT return a Boolean (false|true) which it must do. Go to your `src/cli/config/external_callable_validators.php` and make sure it only returns either true or false after doing its more complex validation on provided data! This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
            }
            if ($isValid !== true) {
                cli_err_without_exit('Input Failed External Validation. Read its possibly provided Error Message Above this one!');
                continue;
            }
        }
        // Remove prefix from the now validated input value before returning it
        if (isset($prefix) && is_string($prefix) && !empty($prefix)) {
            $input = explode(":", $input, 2)[1] ?? $input;
        }
        return $input;
    }
}

/**
 * Retrieves a CLI argument value, handling both Interactive Mode (prompting the user)
 * and Standard/Power User Mode (matching against provided CLI arguments).
 *
 * This function uses the argument configuration (prompt, regex, required, default, help, prefix)
 * retrieved via $command and $argument keys from the global $commandConfigMappings.
 *
 * In Standard CLI Mode (NO_ARGS_CLI is false):
 * - It iterates through $args, returning the first value that successfully matches the configured regex.
 * - If $keepPrefix is false (default), the argument prefix (e.g., 'r:') is removed from the returned value.
 * - If the argument is 'required' and no matching argument is found in $args, the script exits with an error.
 *
 * In Interactive CLI Mode (NO_ARGS_CLI is true):
 * - It delegates to cli_get_valid_cli_input() to prompt the user, automatically prepending the
 * configured 'prefix' to the user's input before validation.
 *
 * @param array $args The array of raw CLI arguments provided by the user.
 * @param string $command The main command name (e.g., 'make:route') to look up configuration.
 * @param string $argument The specific argument key (e.g., 'method/route') to look up configuration.
 * @param callable|null $externalCallableValidator An optional external callable function for additional validation.
                        If provided, it will be called with the validated input value and should return true if valid, false otherwise.
                        If validation fails, the user will be re-prompted in Interactive Mode or an error will be thrown in Standard Mode.
                        The callable should have the signature: function(string $input): bool
                        Example:
                        function my_custom_validator($input) {
                        // Custom validation logic here
                        return true; // or false if invalid}
 * @param bool $keepPrefix Controls whether the prefix (e.g., 'r:') should be preserved if the argument is found via $args. Defaults to false (prefix removed).
 * @return string|null The validated argument value, or null if optional and no value was provided/matched.
 */
function cli_get_cli_input_from_interactive_or_regular($args, $command, $argument, $externalCallableValidator = null, $keepPrefix = false)
{
    // Constant: NO_ARGS_CLI must exist and be a boolean (false|true) before we even procede!
    if (!defined('NO_ARGS_CLI') || !is_bool(NO_ARGS_CLI)) {
        cli_err("Constant:`NO_ARGS_CLI` is NOT defined or is NOT a Boolean (false|true)! This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs!");
    }
    // $args must be an array but can be empty
    if (!isset($args) || !is_array($args)) {
        cli_err("Provided value to \$args was NOT an Array. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs!");
    }
    // Error out on $command ont being a non-empty string
    if (!isset($command) || !is_string($command) || empty(trim($command))) {
        cli_err("Provided value to \$command was NOT a Non-Empty String. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs!");
    }
    // error out on $argument ont being a non-empty string
    if (!isset($argument) || !is_string($argument) || empty(trim($argument))) {
        cli_err("Provided value to \$argument was NOT a Non-Empty String. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs!");
    }
    // $keepPrefix must be a boolean (false|true) and using null as pseud-false is not allowed
    if (!is_bool($keepPrefix)) {
        cli_err("Provided value to \$keepPrefix was NOT a Boolean (false|true). Using 'null' as 'pseudo-false' is NOT allowed in current version of FunkPHP. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs!");
    }
    // Global and check that $command is set in the
    // $cliCommands
    // oterhwise error out
    global $cliCommands;
    if (
        !isset($cliCommands)
        || !is_array($cliCommands)
        || !isset($cliCommands['commands'])
        || !is_array($cliCommands['commands'])
        || !array_key_exists($command, $cliCommands['commands'])
    ) {
        cli_err("Interactive configuration error: Command '{$command}' not found in Command Configuration Map. Check your `cli/config/commands.php`. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
    }
    $configMap = $cliCommands['commands'][$command];
    // Check that 'args' key first exist and is an array and then that any of its subkeys
    // is the $argument we are looking for
    if (
        !isset($configMap['args'])
        || !is_array($configMap['args'])
        || !array_key_exists($argument, $configMap['args'])
        || !is_array($configMap['args'][$argument])
    ) {
        cli_err("Interactive configuration error: Argument '{$argument}' not found in Command Configuration Map for Command '{$command}'. Check your `cli/config/commands.php`. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
    }
    // Throw error if any duplicate prefixes found
    cli_validate_command_prefixes($command);
    $configMap = $configMap['args'][$argument];
    // Define the required key names in the order they must appear
    $keys = ['prompt', 'regex', 'required', 'default', 'help', 'external_callable_validator', 'prefix'];
    $orderedParams = [];
    foreach ($keys as $key) {
        // Hard check for required keys (prompt, regex, required, default, help)
        if (!array_key_exists($key, $configMap)) {
            cli_err("Interactive configuration error: Missing required key '{$key}' in Command Configuration Map. Check your `cli/config/commands.php`. This might mean that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // Hard check for non-empty strings for 'prompt' and 'regex' keys that they are
        // in fact non-empty strings otherwise we hard error out here since misconfiguration!
        if (in_array($key, ['prompt', 'regex']) && (!is_string($configMap[$key]) || empty(trim($configMap[$key])))) {
            cli_err("Interactive configuration error: Key '{$key}' must be a Non-Empty String in Command Configuration Map. Check your  Check your `cli/config/commands.php` and maybe `cli/config/regexes.php` if it was the `regex` key - check for typos. This means that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // 'required' key must always be a boolean (true|false)
        // and using null is not allowed as a "pseudo-false"
        if ($key === 'required' && !is_bool($configMap[$key])) {
            cli_err("Interactive configuration error: Key '{$key}' must be a Boolean (true|false) in Command Configuration Map. Using 'null' as 'pseudo-false' is NOT allowed in current version of FunkPHP. Check your `cli/commands.php`. This means that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // 'default' key must be a string (can be empty) or just null to omit it
        if ($key === 'default' && isset($configMap[$key]) && !is_string($configMap[$key]) && $configMap[$key] !== null) {
            cli_err("Interactive configuration error: Key '{$key}' must be a String (can be empty!) - or null to omit it - in Command Configuration Map. Check your `cli/config/commands.php`. This means that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // 'help' key must be a non-empty string or just null to omit it
        if (
            $key === 'help' && $configMap[$key] !== null
            && (!is_string($configMap[$key]) || empty(trim($configMap[$key])))
        ) {
            cli_err("Interactive configuration error: Key '{$key}' must be a Non-Empty String - or null to omit it - in Command Configuration Map. Check your `cli/config/commands.php`. This means that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // 'prefix' key must be a string (can be empty) but it cannot be null or any other value. Just a string, empty or not.
        if (
            $key === 'prefix' && (!is_string($configMap[$key]) && !preg_match('/^([a-z]+:)?$/', $configMap[$key]))
        ) {
            cli_err("Interactive configuration error: Key '{$key}' must be a String (can be empty!) in Command Configuration Map. Check your `cli/config/commands.php`. This means that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // 'external_callable_validator' key must be a string function that is callable when this code part runs or null to omit it
        if (
            $key === 'external_callable_validator'
            && $configMap[$key] !== null
            && (!is_string($configMap[$key]) || !is_callable("cli_external_validator_" . $configMap[$key]))
        ) {
            cli_err("Interactive configuration error: Key '{$key}' must be a String Function Name that is Callable - or null to omit it - in Command Configuration Map. Check your `cli/config/commands.php` AND also check your `cli/config/external_callable_validators.php` for any typos. It automatically adds `cli_external_validator_` to the name so do NOT use that in  `cli/config/commands.php` for the `external_callable_validator` key! This means that Interactive CLI Mode for a given Command:SubCommand in FunkCLI was not started and thus the command stopped early! This error probably means that the Command File has stopped running before receiving any remaining CLI inputs! This error occured calling `cli_get_cli_input_from_interactive_or_regular(\$args,'$command','$argument');`");
        }
        // Add the value to the ordered array
        $orderedParams[] = $configMap[$key];
    }
    $regexToMatch = $orderedParams[1];
    $required = $orderedParams[2];
    $default = $orderedParams[3];
    $help = $orderedParams[4];
    $callableValidator = $orderedParams[5];
    $prefix = $orderedParams[6];

    // If true, it means no arguments were provided and we are in Interactive CLI Mode
    if (NO_ARGS_CLI) {
        return cli_get_valid_cli_input(...$orderedParams);
    }
    // Otherwise we are in Standard CLI Mode with arguments provided
    // where we try to match a regex ($regexToMatch) to the provided $args
    // where at least one must match if $required is true. We show optional
    // help text if provided in $help variable (configured in commands.php).
    else {
        $finalValue = null;
        foreach ($args as $arg) {
            // Only apply to non-empty strings in $args array
            if (is_string($arg) && !empty(trim($arg))) {
                // Only apply if a Regex is provided
                if (
                    isset($regexToMatch)
                    && is_string($regexToMatch)
                    && !empty($regexToMatch)
                ) {
                    if (preg_match($regexToMatch, $arg)) {
                        // Before setting finalValue we also pass it to the optional external callable validator
                        // which should return true if it all passed otherwise false if validation failed
                        if (isset($callableValidator) && is_string($callableValidator) && is_callable($callableValidator)) {
                            $isValid = call_user_func($callableValidator, $arg);
                            if (!$isValid) {
                                // If validation failed, we continue to next arg without setting finalValue
                                continue;
                            }
                        }
                        $finalValue = $arg;
                        // remove prefix as default unless $keepPrefix is true
                        if (!$keepPrefix) {
                            $finalValue = explode(":", $finalValue, 2)[1] ?? $finalValue;
                        }
                        break;
                    } else {
                        continue;
                    }
                }
                // Otherwise we take the non-empty string value as is
                else {
                    $finalValue = $arg;
                    break;
                }
            }
        }
        // Return default if no match found but default is set and required is true
        if ($finalValue === null && !$required && $default !== null) {
            return $default;
        }
        // If there is no default and no match found but required is true, we error out
        if ($finalValue === null && $required) {
            if ($help) {
                cli_err_without_exit("Required Argument '$prefix{$argument}' for Command '{$command}' did NOT match with any of the provided CLI Arguments for Regex:`$regexToMatch`. The Command File has stopped running before processing any remaining CLI Arguments!");
                cli_info($help);
            } else {
                cli_err("Required Argument '{$argument}' for Command '{$command}' did NOT match with any of the provided CLI Arguments for Regex:`$regexToMatch`. The Command File has stopped running before processing any remaining CLI Arguments!");
            }
        }
        return $finalValue;
    }
}

// Function to connect to local MySQL database!
// Configure its connection: /funkphp/config/db_config.php
function cli_db_connect()
{
    $dbConfig = include_once FUNKPHP_FILE_PATH_DB_LOCAL ?? [];
    $dbConfig = $dbConfig['funkphp_dev'] ?? null;
    // Err out if no config found
    if (!$dbConfig) {
        cli_err("No Local Database Configuration found for 'funkphp_dev' in \"src/funkphp/config/db.php\". Please add your local DB connection settings there under:`\$credentials = ['YourNewDBConnectionArray' => 'SETTINGS_HERE', ...];`. If a Command called this Function, this error has now stopped that Command from completing successfully!");
    }
    try {
        $conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database'], $dbConfig['port']);
        $conn->set_charset($dbConfig['charset'] ?? 'utf8mb4');
    } catch (Exception $e) {
        if ($conn === null) {
            cli_err("Database Connection Failed. Check Database Connection Configuration in \"funkphp/config/db_config.php\". Error Message: `" . $e->getMessage() . "`");
        }
    }
    return $conn;
}

// Function takes a SQL file and parses the CREATE TABLE(); statement
// and then stores it in funkphp/config/tables.php file as a PHP array
function cli_parse_a_sql_table_file($tableFileName)
{
    // Load globals and verify $argv is not empty string and ends with .sql
    cli_info_without_exit("IMPORTANT #1: \"php funkcli add table\" command is NOT meant for actual Table Migration.");
    cli_info_without_exit("It is ONLY meant for structuring efficient Data Validation, SQL Query Building & Data Hydration!");
    cli_info_without_exit("IMPORTANT #2: The function cli_convert_array_to_simple_syntax() in \"funkphp/core/cli_functions.php\" which converts ");
    cli_info_without_exit("array() to array[] ignores quotes inside of other qoutes. For example, \"Yours' truly\" will become \"Yours truly\".");
    cli_info_without_exit("KEEP THAT IN MIND: If you wanna use `DEFAULT \"Qouted Value with '\"Quotes\"' Inside\"` it must be manually added inside \"config/Tables.php\"");

    global $tablesAndRelationshipsFile, $mysqlDataTypesFile;
    $sqlFile = null;
    if (!is_string_and_not_empty(trim($tableFileName ?? null))) {
        cli_err_syntax("Provide a SQL File from \"funkphp/schemas/\" folder as a string!");
    }

    // Trim, add .sql extension if not already, and check that file exsts in /sql/ folder
    $tableFileName = strtolower(trim($tableFileName));
    if (!str_ends_with($tableFileName, ".sql")) {
        $tableFileName .= ".sql";
    }
    if (is_readable(SCHEMA_DIR . '/' . $tableFileName)) {
        $sqlFile = file_get_contents(SCHEMA_DIR . '/' . $tableFileName);
    } else {
        cli_err_syntax("\"{$tableFileName}\" must must exist in\"funkphp/schema/\"!");
    }

    // Check that the tables.php file exists and is writable, then load it
    if (!is_readable(FUNKPHP_FILE_PATH_TABLES) || !is_writable(FUNKPHP_FILE_PATH_TABLES)) {
        cli_err_syntax("The `" . FUNKPHP_FILE_PATH_TABLES . "` File must exist and be writable!");
    }

    // Prepare variables to store the tables.php file and parsed table
    $tablesFile = $tablesAndRelationshipsFile;
    $tableName = null;
    $parsedTable = [];

    // Check that keys "tables", "relationships" & "mappings" exist in the tables.php file
    if (
        !isset($tablesFile['tables']) || !is_array($tablesFile['tables'])
        || !isset($tablesFile['relationships']) || !is_array($tablesFile['relationships'])
        || !isset($tablesFile['mappings']) || !is_array($tablesFile['mappings'])
    ) {
        cli_err_syntax("The `" . FUNKPHP_FILE_PATH_TABLES . "` File must contain the three keys: \"tables\", \"relationships\" & \"mappings\" at root level!");
    }

    // Inform but continue that "CREATE TABLE AS" (using other tables) is not supported
    if (preg_match("/^CREATE TABLE\s+([a-zA-Z0-9_]+)\s*AS/", $sqlFile, $matches)) {
        cli_info_without_exit("You cannot use \"CREATE TABLE AS\" in the SQL file. Please use \"CREATE TABLE\" instead!");
    }

    // Check that file starsts with "CREATE TABLE a-zA-Z0-9_\s+()" or error out
    if (!preg_match("/^CREATE TABLE\s+(IF NOT EXISTS\s*)*([a-zA-Z0-9_]+)\s*\(/i", $sqlFile, $matches)) {
        cli_err_syntax("\"{$tableFileName}\" must start with \"CREATE TABLE /[a-zA-Z0-9_]+/ (\"");
    }
    // Parse out the table name and check if it is valid
    $tableName = $matches[2] ?? null;
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $tableName)) {
        cli_err_syntax("Invalid table name \"$tableName\". Should just be: \"[a-zA-Z0-9_]+\"");
    }
    // Check if the table name ends with "s" and if not add it, and inform the Developer that it happened
    if (!str_ends_with($tableName, "s")) {
        cli_info_without_exit("Table name \"$tableName\" was not pluralized.");
        $tableName .= "s";
        cli_info_without_exit("Table name is now \"$tableName\" for consistency reasons - just a heads up!");
    }
    // Check if the table name already exists in the tables.php file (as a key under "tables")
    if (isset($tablesFile['tables'][$tableName])) {
        cli_err("Table \"$tableName\" already exists in \"funkphp/config/tables.php\"!");
    }

    // Prepare the parsed table array with the table name
    $parsedTable[$tableName] = [];

    /* PREPARING TO PARSE LINE BY LINE IN THE SQL TABLE WHOSE NAME IS VALID! */
    // We now split on "\n" and iterate through each line of the SQL file
    // (this will skip the first line which is just the table name already parsed!)
    // We also remove first element, trim trailing spaces, remove empty lines,
    // the ending ");" and also all trailing "," at the end of each line. A final check
    // is that the first element must start with "id" or we error out. Just to keep things
    // consistent for all added tables in the tables.php file and all functions interacting
    // with them. But before that we will loop through each line and check if there are any
    // duplicates because that is just trolling or mistakes from the End-Developer's side!
    $sqlLines = explode("\n", $sqlFile);
    array_shift($sqlLines);
    $sqlLines = array_map('trim', $sqlLines);
    $sqlLines = array_filter($sqlLines, function ($line) {
        return !empty($line)
            && !str_starts_with($line, ")")
            && !str_starts_with($line, "PRIMARY KEY")
            && !str_starts_with($line, "CHECK")
            && !str_starts_with($line, "CONSTRAINT");
    });
    cli_warning_without_exit("DELETED ALL SQL LINES Starting with: \"PRIMARY KEY\", \"CHECK\", \"CONSTRAINT\" & \")\"!");
    cli_info_without_exit("If you prefer `PRIMARY KEY`, `CHECK`, `CONSTRAINT` for PK & FK, please don't when adding `Tables` to the `funkphp/config/tables.php` File though!");
    $sqlLines = array_map(function ($line) {
        return rtrim($line, ",\r\n\t ");
    }, $sqlLines);
    if (!str_starts_with(strtolower($sqlLines[0]), "id ")) {
        cli_err_syntax_without_exit("First Table Column in the Table SQL File must be: `id`!");
        cli_info_without_exit("Its syntax must be exactly: `id BIGINT AUTO_INCREMENT PRIMARY KEY,`!");
        cli_info("Other tables referencing to this Table must reference to the `id` column!");
    }
    // Check for duplicates in the SQL lines and error out if found. In each line we
    // split on " " and check if the first element is already in a duplicate array.
    // If it is we error out, otherwise we add it to the duplicate array.
    $duplicates = [];
    foreach ($sqlLines as $line) {
        if (
            str_starts_with($line, "//")
            || str_starts_with($line, "--")
            || empty(trim($line))
            || str_starts_with($line, "FOREIGN KEY")
        ) {
            continue;
        }
        $lineParts = explode(" ", $line);
        if (isset($duplicates[$lineParts[0]])) {
            cli_err_syntax("Duplicate Column Name \"{$lineParts[0]}\". Please fix \"sql/{$tableFileName}\" and retry!");
        } else {
            $duplicates[$lineParts[0]] = true;
        }
    }

    // Load typical MySQL Data Types with min & max lengths and number of digits (if at all applicable)
    // to compare against during parsing. This is also used when creating the validation file!
    $mysqlDataTypes = $mysqlDataTypesFile;

    // Finally we start iterating through each line by splitting on
    foreach ($sqlLines as $index => $line) {
        $lineParts = explode(" ", $line);
        $currentDataType = "";

        // Special Case #1: First Column (thus first index at 0) "ID" must be "BIGINT AUTO_INCREMENT PRIMARY KEY" for the first element
        // for consistency reasons. We check if it is the first element and if it is not we error out.
        if ($index === 0) {
            if ($line !== "id BIGINT AUTO_INCREMENT PRIMARY KEY") {
                cli_err_syntax("First Column \"{$lineParts[0]}\" must be \"id BIGINT AUTO_INCREMENT PRIMARY KEY\". Please fix \"sql/{$tableFileName}\" and try again!");
            } else {
                $parsedTable[$tableName][$lineParts[0]] = [
                    "joined_name" => $tableName . "_" . $lineParts[0],
                    "auto_increment" => true,
                    "type" => "BIGINT",
                    'binding' => 'i',
                    "value" => null,
                    "primary_key" => true,
                    "nullable" => false,
                    "default" => null,
                ];
                continue;
            }
        }

        // Special Case #2: The $line starts with "//" or "--" meaning a comment to just ignore it
        // and continue to the next line. We also check if the line is empty after trimming it.
        if (str_starts_with($line, "//") || str_starts_with($line, "--") || empty(trim($line))) {
            cli_info_without_exit("Skipping commented \"$line\"");
            continue;
        }

        // Special Case #3: The $line starts with "FOREIGN KEY" meaning we need to parse it by getting the
        // regex that I wrote myself for once instead of help from LLMs. Kinda incredible, right?! ^_^
        if (str_starts_with($line, "FOREIGN KEY")) {
            $foreignKeyRegex = "/FOREIGN KEY \(([a-zA-Z0-9_]+)\) REFERENCES ([a-zA-Z0-9_]+)\(([a-zA-Z0-9]+)\)/";

            // At match, grab variables and check all NOT being null first
            if (preg_match($foreignKeyRegex, $line, $matches)) {
                $thisTableFK = $matches[1] ?? null;
                $otherTable = $matches[2] ?? null;
                $otherTablePK = $matches[3] ?? null;
                if (!isset($thisTableFK) || !isset($otherTable) || !isset($otherTablePK)) {
                    cli_err_syntax("Foreign Key \"{$line}\" is missing one or more of the following: \"this_table_column\", \"other_table_name\" or \"other_table_primary_key\". Please fix \"sql/{$tableFileName}\" and try again!");
                } else {
                    // Check if the other table exists in the tables.php file
                    if (!isset($tablesFile['tables'][$otherTable])) {
                        cli_err_syntax("Foreign Key \"{$thisTableFK}\" references Table \"$otherTable\" not found in \"funkphp/config/tables.php\". First add Table \"$otherTable\", or fix \"sql/{$tableFileName}\" and try again!");
                    } else {
                        // Add the foreign key to the parsed table array and merge it with the existing one...
                        if (isset($parsedTable[$tableName][$thisTableFK])) {
                            $parsedTable[$tableName][$thisTableFK] = array_merge($parsedTable[$tableName][$thisTableFK], [
                                "joined_name" => $tableName . "_" . $thisTableFK,
                                "foreign_key" => true,
                                "references" => $otherTable,
                                "references_column" => $otherTablePK,
                                "referenced_joined" => $otherTable . "_" . $otherTablePK,
                            ]);
                        }
                        // ...unless it doesn't exist
                        else {
                            $parsedTable[$tableName][$thisTableFK] = [
                                "joined_name" => $tableName . "_" . $thisTableFK,
                                "foreign_key" => true,
                                "references" => $otherTable,
                                "references_column" => $otherTablePK,
                                "referenced_joined" => $otherTable . "_" . $otherTablePK,
                            ];
                        }
                        cli_info_without_exit("Foreign Key \"{$thisTableFK}\" added to Table \"$tableName\" which references Table \"$otherTable\".");
                        continue;
                    }
                }
            }
            // Line started with "FOREIGN KEY" but no match found so we error out
            else {
                cli_err_syntax_without_exit("\"$line\" started with \"FOREIGN KEY\" but failed to match. Please fix \"sql/{$tableFileName}\" and try again!");
                cli_info_without_exit("Expected Syntax:\"FOREIGN KEY (existing_column_name_in_this_table) REFERENCES other_existing_referenced_table(id)\"");
                cli_info("Anything after is not matched so you can include things such as \"ON DELETE CASCADE\", \"ON UPDATE CASCADE\", etc.");
            }
        } // This step succeeds it will continue to the next line and skip the rest of the code below!

        // Special Case #4: The $line starts with "CONSTRAINT" or "CHECK" so we just inform the Developer
        // that these are currently not supported and we will skip them for now, meaning we will not parse them,
        // but we will continue to the next line and skip the rest of the code below!
        if (str_starts_with(strtoupper($line), "CONSTRAINT")) {
            cli_info_without_exit("Skipping \"{$line}\" as parsing \"CONSTRAINT\" is not implemented.");
            cli_warning_without_exit("If you use CONSTRAINT to add a Foreign Key, please start with \"FOREIGN KEY\" instead!");
            continue;
        }
        if (str_starts_with(strtoupper($line), "CHECK")) {
            cli_info_without_exit("Skipping \"{$line}\" as parsing \"CHECK\" is not implemented.");
            continue;
        }
        if (str_starts_with(strtoupper($line), "PRIMARY KEY")) {
            cli_info_without_exit("Skipping \"{$line}\" as a PK has already been added if you reached this far!");
            continue;
        }

        // FOR FIRST two ELEMENTS[0-1] we assume a valid column name and
        // data type with optional value inside of two () brackets.
        // the regex ^[a-zA-Z_][a-zA-Z0-9_]*$ and check if it is valid
        if (!preg_match("/^([a-zA-Z_][a-zA-Z0-9_]+)\s*(([a-zA-Z0-9_]+)(\((.+)\))*)*/", $line, $matches)) {
            cli_err_without_exit("Column \"{$lineParts[0]}\" should start with valid Column Name and then valid Data Type syntax!");
            cli_info_without_exit("For example: `columnName` `dataType(optionalValueInsideOfParentheses)` (ignoring the backticks!)");
            cli_info("Examples: `comment VARCHAR(255)` OR `like_counter INT` (ignoring the backticks!)");
        }
        // Otherwise we add it to the parsed table array
        else {
            // Insert column name or error out if it is not valid
            if (isset($matches[1])) {
                $parsedTable[$tableName][$matches[1]] = [
                    "joined_name" => $tableName . "_" . $matches[1],
                ];
            } else {
                cli_err_syntax("Column \"{$lineParts[0]}\" should start with valid Column Name and then valid Data Type syntax!");
                cli_info_without_exit("For example: `columnName` `dataType(optionalValueInsideOfParentheses)` (ignoring the backticks!)");
                cli_info("Examples: `comment VARCHAR(255)` OR `like_counter INT` (ignoring the backticks!)");
            }
            // Insert data type or error out if it is not valid
            if (isset($matches[3])) {
                $matches[3] = strtoupper($matches[3]);
                if (!isset($mysqlDataTypes[$matches[3]])) {
                    cli_err_syntax("[cli_parse_a_sql_table_file] Data Type \"{$matches[3]}\" not found in \"funkphp/config/valid/supported_mysql_data_types.php\" of valid MySQL Data Types.");
                    cli_info("Please add its key if you believe it should be included, and then retry in FunkCLI!");
                } else {
                    $parsedTable[$tableName][$matches[1]]["type"] = $matches[3];
                    $parsedTable[$tableName][$matches[1]]["binding"] = $mysqlDataTypes[$matches[3]]["BINDING"] ?? "<UNKNOWN_BINDING_PARAM_VALUE>";
                }
            }
            // Insert optional value or just null
            if (isset($matches[5])) {
                // Special case when data type is ENUM or SET, we store it as string converted to array
                if ($matches[3] === "ENUM" || $matches[3] === "SET") {
                    $parsedArray = cli_try_parse_listed_string_as_array($matches[5]);
                    // Error out if the array is empty or not valid (too long)
                    if (is_array($parsedArray) && count($parsedArray) > 0) {
                        if ($matches[3] === "ENUM" && count($parsedArray) > 65535) {
                            cli_err_syntax("ENUM value \"{$matches[5]}\" is too long. Please fix \"sql/{$tableFileName}\" and try again!");
                        } elseif ($matches[3] === "SET" && count($parsedArray) > 64) {
                            cli_err_syntax("SET value \"{$matches[5]}\" is too long. Please fix \"sql/{$tableFileName}\" and try again!");
                        }
                    } else {
                        cli_warning_without_exit("ENUM/SET value \"{$matches[5]}\" is not valid. Please fix \"sql/{$tableFileName}\" after this!");
                    }
                    $parsedTable[$tableName][$matches[1]]["value"] = $parsedArray;
                }
                // Certain types will NOT get a value assigned to them!
                elseif (in_array($matches[3], $mysqlDataTypes["INVALID_VALUES_FOR_NUMBER_TYPES"])) {
                    $parsedTable[$tableName][$matches[1]]["value"] = null;
                }
                // Try to parse and store as a number or string
                else {
                    $parsedTable[$tableName][$matches[1]]["value"] = cli_try_parse_number($matches[5]);
                }
            }
            // Either it doesn't exist or is null so we set it to null
            else {
                $parsedTable[$tableName][$matches[1]]["value"] = null;
            }
        }

        // FOR REMAINING ELEMENTS[2-n] we first concatenate element[0-1] and remove them from the $line string
        // so we don't accidentally parse them again. Then we will start to check for things like "NOT NULL", "UNIQUE"
        // and so on! We begin now with removing the first two elements from the $line string.
        $line = trim(preg_replace("/^([a-zA-Z_][a-zA-Z0-9_]+)\s*(([a-zA-Z0-9_]+)(\((.+)\))*)*/", "", $line));

        // If there are no more elements in the $line string we just continue to the next line
        // This also means that the column is nullable and not unique
        if (empty($line)) {
            $parsedTable[$tableName][$lineParts[0]]["nullable"] = true;
            $parsedTable[$tableName][$lineParts[0]]["unique"] = false;
            continue;
        }

        // Check for uneven numbers of quotes in the $line string and warn the Developer
        if (substr_count($line, '"') % 2 !== 0 || substr_count($line, "'") % 2 !== 0) {
            cli_warning_without_exit("Uneven numbers of quotes left in `$line`. Values might get clipped when saving to \"tables.php\"!");
            cli_warning_without_exit("Quotes inside of quotes might be ignored during parsing!");
        }

        // Check for "NOT NULL" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("NOT NULL", $line) === "NOT NULL") {
            $parsedTable[$tableName][$lineParts[0]]["nullable"] = false;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["nullable"] = true;
        }

        // Check for "UNIQUE" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("UNIQUE", $line) === "UNIQUE") {
            $parsedTable[$tableName][$lineParts[0]]["unique"] = true;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["unique"] = false;
        }

        // Check for "UNSIGNED" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("UNSIGNED", $line) === "UNSIGNED") {
            $parsedTable[$tableName][$lineParts[0]]["unsigned"] = true;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["unsigned"] = false;
        }

        // Check for "SIGNED" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("SIGNED", $line) === "SIGNED") {
            $parsedTable[$tableName][$lineParts[0]]["signed"] = true;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["signed"] = false;
        }

        // Try match DEFAULT and its value or just set it to null
        // (ambiguity due to DEFAULT NULL is possible as well!)
        $defaultPattern = "/DEFAULT\s*(NOW\(\)|(NULL)|(\d+\.*\d+)|(\d+)|(CURRENT_DATE)|(CURRENT_TIMESTAMP)|(CURRENT_TIME)|(\"{1}(.*)\"{1})|(\'{1}(.*)\'{1})|\(.+\))/i";
        if (preg_match($defaultPattern, $line, $matches)) {
            $defaultValue = $matches[1] ?? null;
            if (isset($defaultValue)) {
                // Convert $defaultValue to a number if it is parsed as a number
                if (is_numeric($defaultValue)) {
                    // Check what data type is $currentDataType and convert it to that type
                    if (isset($currentDataType)) {
                        if ($currentDataType === "BIGINT" || $currentDataType === "INT" || $currentDataType === "SMALLINT" || $currentDataType === "MEDIUMINT") {
                            $defaultValue = (int)$defaultValue;
                        } elseif ($currentDataType === "FLOAT" || $currentDataType === "DOUBLE" || $currentDataType === "DOUBLE PRECISION") {
                            $defaultValue = (float)$defaultValue;
                        } elseif ($currentDataType === "DECIMAL" || $currentDataType === "NUMERIC") {
                            $defaultValue = (string)$defaultValue;
                        } else {
                            $defaultValue = (int)$defaultValue;
                        }
                    } else {
                        $defaultValue = (int)$defaultValue;
                    }
                }
                // Else means it is a string, we remove the quotes at the beginning and end
                else {
                    if (str_starts_with($defaultValue, '"') && str_ends_with($defaultValue, '"')) {
                        $defaultValue = substr($defaultValue, 1, -1);
                    } elseif (str_starts_with($defaultValue, "'") && str_ends_with($defaultValue, "'")) {
                        $defaultValue = substr($defaultValue, 1, -1);
                    }
                }
                $parsedTable[$tableName][$lineParts[0]]["default"] = $defaultValue;
            }
            // No matched default value found so we set it to null
            else {
                $parsedTable[$tableName][$lineParts[0]]["default"] = null;
            }
        }
        // No match after "DEFAULT " (or no DEFALT key at all) so we set it to null
        else {
            $parsedTable[$tableName][$lineParts[0]]["default"] = null;
        }
    }

    // Finally add the entire parsed table to the Tables.php file's array!
    $tablesFile['tables'][$tableName] = $parsedTable[$tableName];
    cli_success_without_exit("Parsed Table \"$tableName\" from SQL File \"schemas/{$tableFileName}\"!");
    cli_success_without_exit("You find it in `config/tables.php` => ['tables']['$tableName']!");

    // Now we add the table to the tables.php file and also pass it to the validation function which might fail
    // but the recompiling will still run first and if that fails then the validation won't run!
    cli_info_without_exit("Attempting recompiling tables with newly added Table \"$tableName\"...");
    cli_output_tables_file($tablesFile);
}

// Function tries to parse a number by first checking if it
// is a numeric, and then whether it is float, int or string
function cli_try_parse_number($number)
{
    if (!is_string_and_not_empty($number)) {
        cli_err_syntax("[cli_try_parse_number]: Expects a string as input for \$number!");
    }
    $number = trim($number);
    if (is_numeric($number)) {
        if (strpos($number, '.') !== false) {
            return (float)$number;
        } else {
            return (int)$number;
        }
    } else {
        return $number;
    }
}

// Function that takes a string like "1,2,3,4,5" or "('a', 'b', 'c')" and tries
// to parse it as an array and return it as an aray instead of a string.
function cli_try_parse_listed_string_as_array($stringedList)
{
    if (!is_string_and_not_empty($stringedList)) {
        cli_err_syntax("[cli_try_parse_listed_string_as_array]: Expects a string as input!");
    }
    $array = [];
    $stringedList = trim($stringedList);
    if (str_starts_with($stringedList, "(") && str_ends_with($stringedList, ")")) {
        $stringedList = substr($stringedList, 1, -1);
    }

    // We split on "," and remove any quotes around the
    // string and trim any whitespace around the string
    $stringedList = explode(",", $stringedList);
    foreach ($stringedList as $key => $value) {
        $value = trim($value);
        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            $value = substr($value, 1, -1);
        } elseif (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }
        $array[] = $value;
    }
    return $array ? array_values($array) : null;
}

// Function that finds a string that is NOT inside of quotes
// by iterating one character at a time and checking if it is inside quotes
function cli_find_string_outside_quotes($needle, $haystack)
{
    // Check that both are strings and not empty
    if (!is_string_and_not_empty($needle) || !is_string_and_not_empty($haystack)) {
        cli_err_syntax("[cli_find_string_outside_quotes]: Expects two non-empty strings as input!");
    }
    // Prepare variables
    $currentBuiltString = [];
    $insideQuotes = false;
    $splittedString = str_split($needle);

    // We iterate through each character in the haystack string
    // and we check first if we are inside of quotes or not
    // and if we are inside of quotes we skip the current character
    // and also reset the $currentBuiltString array to empty.
    // As we iterate through each character that is outside of quotes
    // we then check if the current character is equal to the first character of the $needle string
    // or the next one and so on. If it is we add it to the $currentBuiltString array otherwise
    // we reset the $currentBuiltString array to empty and continue iterating.
    // If we reach the end of the haystack string and the $currentBuiltString array is equal to the $needle string
    // we return the $currentBuiltString array as a string. Otherwise we return "".
    foreach (str_split($haystack) as $index => $char) {
        if ($char === '"' || $char === "'") {
            // Check first that previous character is not a backslash
            // and then toggle the $insideQuotes variable
            if ($index > 0 && $haystack[$index - 1] !== "\\") {
                $insideQuotes = !$insideQuotes;
            }
            continue;
        }
        if ($insideQuotes) {
            $currentBuiltString = [];
            continue;
        }
        // If first character is the first in the $needle string and the $currentBuiltString is empty
        if (
            $char === $splittedString[0] && empty($currentBuiltString)
            && $index > 0 && $haystack[$index - 1] === " "
        ) {
            $currentBuiltString[] = $char;
            continue;
        }
        // Here we check if $char is the next character in the $needle string
        // by taking the current count + 1 in the $currentBuiltString array and checking if it is equal to the $needle string
        // If it is we add it to the $currentBuiltString array otherwise we reset the $currentBuiltString array to empty.
        // We also check if the $currentBuiltString array is equal to the $needle string and return it as a string.
        // Otherwise we return "".
        if (
            count($currentBuiltString) < count($splittedString)
            && $char === $splittedString[count($currentBuiltString)]
        ) {
            $currentBuiltString[] = $char;
        } elseif (count($currentBuiltString) == count($splittedString)) {
            return implode("", $currentBuiltString);
        } elseif ($char !== $splittedString[count($currentBuiltString)]) {
            $currentBuiltString = [];
        }
    }
    return implode("", $currentBuiltString);
}

// Improved version of the cli_find_string_outside_quotes() function
// using some help from LLMs to handle cases like NOT matching word SIGNED
// because it is inside of the word UNSIGNED and similar cases like that.
function cli_find_string_outside_quotes_improved($needle, $haystack)
{
    // Check that both are strings and not empty
    if (!is_string_and_not_empty($needle) || !is_string_and_not_empty($haystack)) {
        cli_err_syntax("[cli_find_string_outside_quotes_improved]: Expects two non-empty strings as input!");
    }
    // Prepare variables
    $currentBuiltString = [];
    $insideQuotes = false;
    $splittedString = str_split($needle);

    // We iterate through each character in the haystack string
    // and we check first if we are inside of quotes or not
    // and if we are inside of quotes we skip the current character
    // and also reset the $currentBuiltString array to empty.
    // As we iterate through each character that is outside of quotes
    // we then check if the current character is equal to the first character of the $needle string
    // or the next one and so on. If it is we add it to the $currentBuiltString array otherwise
    // we reset the $currentBuiltString array to empty and continue iterating.
    // If we reach the end of the haystack string and the $currentBuiltString array is equal to the $needle string
    // we return the $currentBuiltString array as a string. Otherwise we return "".
    foreach (str_split($haystack) as $index => $char) {
        if ($char === '"' || $char === "'") {
            // Check first that previous character is not a backslash
            // and then toggle the $insideQuotes variable
            if ($index > 0 && $haystack[$index - 1] !== "\\") {
                $insideQuotes = !$insideQuotes;
            }
            continue;
        }
        if ($insideQuotes) {
            $currentBuiltString = [];
            continue;
        }
        // If first character is the first in the $needle string and the $currentBuiltString is empty
        if (
            $char === $splittedString[0] && empty($currentBuiltString)
            && $index > 0 && $haystack[$index - 1] === " "
        ) {
            $currentBuiltString[] = $char;
            continue;
        }
        // Here we check if $char is the next character in the $needle string
        // by taking the current count + 1 in the $currentBuiltString array and checking if it is equal to the $needle string
        // If it is we add it to the $currentBuiltString array otherwise we reset the $currentBuiltString array to empty.
        // We also check if the $currentBuiltString array is equal to the $needle string and return it as a string.
        // Otherwise we return "".
        // Check if we are currently building a potential match string
        // If we are already building, check if the current character is the next expected character
        if (count($currentBuiltString) > 0) {
            if (count($currentBuiltString) < count($splittedString) && $char === $splittedString[count($currentBuiltString)]) {
                // Character matches the next expected one, continue building
                $currentBuiltString[] = $char;
            }
            // Else if we already have a full match from the previous iteration, return it
            elseif (count($currentBuiltString) == count($splittedString)) {
                return implode("", $currentBuiltString);
            }
            // The current character does NOT match the next expected character in the sequence.
            // Reset the built string because the sequence is broken.
            // Now, check if the CURRENT character could be the START of a *new* potential match (after a reset)
            // This handles cases like "abab" searching for "aba", when it sees the second 'a', it resets and could start a new match.
            else {
                $currentBuiltString = [];
                // Check the character *before* the current one for a word boundary
                // A word boundary is the start of the string, or a non-word character (\W or [^a-zA-Z0-9_])
                if ($char === $splittedString[0]) {
                    if ($index === 0 || ($index > 0 && !ctype_alnum($haystack[$index - 1]) && $haystack[$index - 1] !== '_')) {
                        $currentBuiltString[] = $char;
                    }
                }
            }
        } else {
            // We are NOT currently building a potential match string.
            // Check if the current character could be the START of a match.
            // Check the character *before* the current one for a word boundary
            if ($char === $splittedString[0]) {
                if ($index === 0 || ($index > 0 && !ctype_alnum($haystack[$index - 1]) && $haystack[$index - 1] !== '_')) {
                    $currentBuiltString[] = $char;
                }
            }
        }
    }
    // After loop return fully matched or not
    if (count($currentBuiltString) == count($splittedString)) {
        return implode("", $currentBuiltString);
    } else {
        return "";
    }
}

// Outputs the tables.php file based on the array passed to it
function cli_output_tables_file($array)
{
    // Load globals and verify non-empty array and that file exists to written to
    if (!is_array_and_not_empty($array)) {
        cli_err_syntax("The provided Array must be a Non-Empty Array!");
    }
    if (!file_exists_is_readable_writable(FUNKPHP_FILE_PATH_TABLES)) {
        cli_err_syntax("The `" . FUNKPHP_FILE_PATH_TABLES . "` File must exist and be writable!");
    }
    // Check for the keys "tables" and "relationships" in the array at the root level
    if (
        !isset($array['tables']) || !is_array($array['tables'])
        || !isset($array['relationships']) || !is_array($array['relationships'])
        || !isset($array['mappings']) || !is_array($array['mappings'])
    ) {
        cli_err_syntax("The `" . FUNKPHP_FILE_PATH_TABLES . "` File must contain the three keys: \"tables\", \"relationships\" & \"mappings\" at root level!");
    }

    // Loop through and add any missing keys to the relationships
    // and mappings arrays based on the newly added Table!
    foreach ($array['tables'] as $tableName => $tableData) {
        // Add relationships for the table if they do not exist and also update existing ones
        if (!isset($array['relationships'][$tableName]) || !is_array($array['relationships'][$tableName])) {
            $array['relationships'][$tableName] = [];
            foreach ($tableData as $columnName => $columnData) {
                if (isset($columnData['foreign_key']) && $columnData['foreign_key'] === true) {
                    // If a FK, we create the relationship between FK Table and PK Table
                    $array['relationships'][$tableName][$columnData['references']] = [
                        'local_column' => $columnName,
                        'foreign_column' => 'id',
                        'local_table' => $tableName,
                        'foreign_table' => $columnData['references'],
                        'direction' => 'fk_to_pk',
                    ];
                    $array['relationships'][$columnData['references']][$tableName] = [
                        'local_column' => 'id',
                        'foreign_column' => $columnName,
                        'local_table' => $columnData['references'],
                        'foreign_table' => $tableName,
                        'direction' => 'pk_to_fk',
                    ];
                } else {
                    // If it is not a foreign key, we just skip it
                    continue;
                }
            }
            cli_info_without_exit("Added Relationships for Table \"$tableName\" in \"funkphp/config/tables.php\"!");
        }
        // Add mappings for the table if they do not exist (for JSON, POST & GET for each Column!)
        if (!isset($array['mappings'][$tableName]) || !is_array($array['mappings'][$tableName])) {
            $array['mappings'][$tableName] = [];
            foreach ($tableData as $columnName => $columnData) {
                $array['mappings'][$tableName][$columnName]['json'] = $columnData['joined_name'] ?? $tableName . "_" . $columnName;
                $array['mappings'][$tableName][$columnName]['post'] = $columnData['joined_name'] ?? $tableName . "_" . $columnName;
                $array['mappings'][$tableName][$columnName]['get'] = $columnData['joined_name'] ?? $tableName . "_" . $columnName;
            }
            cli_info_without_exit("Added Mappings for Table \"$tableName\" in \"funkphp/config/tables.php\"!");
        }
    }

    // --- (FROM LLM!!!) NEW LOGIC FOR AUTOMATIC MANY-TO-MANY RELATIONSHIP DETECTION ---
    // This loop runs after all direct 1:M/M:1 relationships have been set up.
    cli_info_without_exit("Analyzing for `Many-to-Many (m_to_m) Relationships` by splitting Table Names on `_` and Checking for Pivot Tables...");
    foreach ($array['tables'] as $tableName => $tableData) {
        // Heuristic: Check if the table name looks like a pivot table (e.g., 'table1_table2')
        if (strpos($tableName, '_') !== false) {
            $parts = explode('_', $tableName);
            // We expect pivot tables to typically join two main tables
            if (count($parts) === 2) {
                $table1 = $parts[0]; // e.g., 'authors'
                $table2 = $parts[1]; // e.g., 'tags'
                // Ensure both parsed table names actually exist as main tables
                if (isset($array['tables'][$table1]) && isset($array['tables'][$table2])) {
                    $pivotLocalKey = null; // Column in pivot table linking to $table1 (e.g., 'author_id')
                    $pivotForeignKey = null; // Column in pivot table linking to $table2 (e.g., 'tag_id')
                    // Iterate through the pivot table's columns to find its foreign keys
                    // and identify which main table each FK references.
                    foreach ($tableData as $columnName => $columnDef) {
                        if (isset($columnDef['foreign_key']) && $columnDef['foreign_key'] === true) {
                            if ($columnDef['references'] === $table1) {
                                $pivotLocalKey = $columnName;
                            } elseif ($columnDef['references'] === $table2) {
                                $pivotForeignKey = $columnName;
                            }
                        }
                    }
                    // If both required foreign keys were found in the pivot table's definition,
                    // then we can confidently define a many-to-many relationship.
                    if ($pivotLocalKey !== null && $pivotForeignKey !== null) {
                        // Define Many-to-Many from $table1 to $table2
                        // Only add if it doesn't already exist or if it's not already a many_to_many
                        if (
                            !isset($array['relationships'][$table1][$table2])
                            || !is_array($array['relationships'][$table1][$table2])
                            || (isset($array['relationships'][$table1][$table2]['type'])
                                && $array['relationships'][$table1][$table2]['type'] !== 'many_to_many')
                        ) {
                            $array['relationships'][$table1][$table2] = [
                                'direction' => 'm_to_m',
                                'local_table' => $table1,
                                'foreign_table' => $table2,
                                'pivot_table' => $tableName,         // The current pivot table name (e.g., 'authors_tags')
                                'pivot_local_key' => $pivotLocalKey, // FK in pivot pointing to 'local_table' (e.g., 'author_id')
                                'pivot_foreign_key' => $pivotForeignKey, // FK in pivot pointing to 'foreign_table' (e.g., 'tag_id')
                            ];
                            cli_info_without_exit("Automatically added Many-to-Many Relationship: `{$table1}=>{$table2}(via:{$tableName})`.");
                        }

                        // Define Many-to-Many from $table2 to $table1 (inverse direction)
                        // This allows hydration like tags=>authors(via:authors_tags)
                        if (
                            !isset($array['relationships'][$table2][$table1])
                            || !is_array($array['relationships'][$table2][$table1])
                            || (isset($array['relationships'][$table2][$table1]['type'])
                                && $array['relationships'][$table2][$table1]['type'] !== 'many_to_many')
                        ) {
                            $array['relationships'][$table2][$table1] = [
                                'direction' => 'm_to_m',
                                'local_table' => $table2,
                                'foreign_table' => $table1,
                                'pivot_table' => $tableName,
                                'pivot_local_key' => $pivotForeignKey, // Swapped for inverse direction
                                'pivot_foreign_key' => $pivotLocalKey,  // Swapped for inverse direction
                            ];
                            cli_info_without_exit("Automatically added Many-to-Many Relationship (Inverse): `{$table2}=>{$table1}(via:{$tableName})`.");
                        }
                    }
                }
            }
        }
    }
    // --- (FROM LLM!!!) END NEW LOGIC ---

    // Attempt to write to the Tables.php file and check if it was successful
    if (!cli_crud_folder_php_file_atomic_write("<?php\nreturn " . var_export($array, true) . ";\n", FUNKPHP_FILE_PATH_TABLES)) {
        cli_err_syntax("FAILED recompiling Tables in `" . FUNKPHP_FILE_PATH_TABLES . "`!");
    } else {
        cli_success_without_exit("Recompiled Tables in `" . FUNKPHP_FILE_PATH_TABLES . "`!");
    }
}

// Function takes a a valid array with simplified Validation Rules Syntax and converts
// it to highly optimized validation rules that are then returned as an array
function cli_convert_simple_validation_rules_to_optimized_validation($validationArray, $handlerFile, $fnName)
{
    // Validate it is an associative array - not a list
    if (!is_array_and_not_empty($validationArray)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty Associative Array as input for `\$validationArray`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (array_is_list($validationArray)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty Associative Array as input for `\$validationArray`!");
        cli_info("Here it probably means that the \"\$DX\" variable is a List Array, empty or not?");
    }

    // Both $handlerFile and $fnName must be non-empty strings
    if (!is_string_and_not_empty($handlerFile)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty String as input for `\$handlerFile`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (!is_string_and_not_empty($fnName)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty String as input for `\$fnName`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // Prepare variables to store the converted validation rules
    $convertedValidationArray = [];
    $existsTableColsToCheck = [];
    $currentDXKey = null;
    $currentRules = null;
    $currentRuleForCurrentDXKey = null;
    $currentRuleValueForCurrentDXKeyValue = null;
    $currentRuleErrMsgForCurrentDXKeyValue = null;

    // Regex patterns to match the validation rules
    $regexType = [
        'ONLY_RULE_NAME' => '/^([a-z_][a-z_0-9]+)$/',
        'ONLY_RULE_NAME_AND_VALUE' => '/^([a-z_][a-z_0-9]+):(.+)$/',
        'ONLY_RULE_NAME_AND_ERROR' => '/^([a-z_][a-z_0-9]+)\("([^"]+)"\)$/',
        'ONLY_RULE_NAME_AND_VALUE_AND_ERROR' => '/^([a-z_][a-z_0-9]+):(.+)\("([^"]+)"\)$/'
    ];

    // List of all supported data types where we check so not
    // two are used for the same $$currentDXKey since each
    // given single input must be of only one data type!
    $dataTypeRules = [
        'string',
        'char',
        'digit',
        'integer',
        'float',
        'boolean',
        'number',
        'date',
        'array',
        'list',
        'email',
        'email_custom',
        'password',
        'password_custom',
        'password_confirm',
        'url',
        'ip',
        'ip4',
        'ip6',
        'uuid',
        'phone',
        'object',
        'json',
        'enum',
        'set',
        'checked',
        'unchecked',
        'file',
        'image',
        'video',
        'audio',
    ];

    // When this value turns true, then we will add it as a special root key
    // before when finally returning the converted validation array.
    $stop_on_first_error = false;

    // Priority order of validation rules (required and the data
    // type must always be first for each $currentDXKey!). 'nullable'
    // must come very early to allow for data that are actually null!
    $priorityOrder = [
        // Special properites
        'stop_all_on_first_error',
        'field',
        'nullable',
        'stop',
        'required',
        // Data types
        'string',
        'char',
        'email',
        'email_custom',
        'password',
        'password_custom',
        'password_confirm',
        'url',
        'ip',
        'ip4',
        'ip6',
        'uuid',
        'phone',
        'date',
        'json',
        'integer',
        'digit',
        'float',
        'boolean',
        'number',
        'array',
        'list',
        'set',
        'enum',
        'object',
        'unchecked',
        'checked',
        'file',
        'image',
        'video',
        'audio',
        // Data "measurements"
        'between',
        'betweenlen',
        'betweenval',
        'betweencount',
        'exact',
        'exactlen',
        'exactval',
        'exactcount',
        'count',
        'mincount',
        'maxcount',
        'min',
        'minlen',
        'minval',
        'max',
        'maxlen',
        'maxval',
        'digits',
        'digits_between',
        'min_digits',
        'max_digits',
        'decimals',
        // Other types of data validation
        'color',
        'lowercase',
        'lowercases',
        'uppercase',
        'uppercases',
        'numbers',
        'specials',
        // Regex
        'regex',
        'not_regex',
        // Arrays
        'array_keys',
        'array_keys_exact',
        'array_values',
        'array_values_exact',
        // Elements in arrays
        'elements_all_arrays',
        'elements_all_lists',
        'elements_all_numbers',
        'elements_all_chars',
        'elements_all_strings',
        'elements_all_integers',
        'elements_all_floats',
        'elements_all_booleans',
        'elements_all_checked',
        'elements_all_unchecked',
        'elements_all_nulls',
        'elements_this_type_order',
        'any_of_these_values',
        // Always last
        'exists',
        'unique',
        'password_hash',
    ];
    include_once(FUNKPHP_CORE_DIR . "/functions.php");

    // We first verify for any duplicates in the array keys.
    // This should be impossible since same key name would just
    // overwrite the previous one, but we still check for it.
    $duplicates = [];
    foreach ($validationArray as $key => $value) {
        if (isset($duplicates[$key])) {
            cli_err_syntax_without_exit("Duplicate Validation Key `$key` found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax("Please make sure all keys (the matching data to validate) are unique!");
        }
        $duplicates[$key] = [];
    }

    // List of available Global Config Rules
    $globalConfigRules = [
        'show_v_data_only_if_all_valid' => [],
        'stop_all_first' => [], // Alias for 'stop_all_on_first_error'
        'stop_all_on_first_error' => [],
        'passwords_to_match' => [],
    ];

    // We now check for the "'<CONFIG>'" key in $validationArray which is a configuration
    // key that will always be processed first so we grab it and remove it from the array.
    if (isset($validationArray['<CONFIG>']) && $validationArray['<CONFIG>'] !== null) {
        if (!is_array($validationArray['<CONFIG>']) || empty($validationArray['<CONFIG>'])) {
            cli_warning_without_exit("The Global Validation `<CONFIG>` Key is empty - no Global Config Rules have been added!");
            cli_info_without_exit("You can add Global Config Rules to the Validation Array by adding a key `<CONFIG>` with an Array of Rules.");
            $convertedValidationArray['<CONFIG>'] = null;
        } else {
            // We initialize the `<CONFIG>` key in the converted validation array
            // and here we will add all the valid configuration rules that are found
            $convertedValidationArray['<CONFIG>'] = [];

            // Now we check if the `<CONFIG>` key has any rules and if it does, we process them
            foreach ($validationArray['<CONFIG>'] as $configKey => $configVal) {
                // Check that config rule is valid ($globalConfigRules) or err out
                if (!isset($globalConfigRules[$configKey])) {
                    cli_err_syntax_without_exit("Invalid Global Config Rule `$configKey` found in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Use any - once - of the following available Global Config Rules:\n" . implode(",\n", quotify_elements($globalConfigRules)) . "!");
                }

                // NOW WE ADD THE CONFIG RULES THAT EXIST!
                // If "stop_all_on_first_error", add it to the CONFIG key
                if (($configKey === 'stop_all_on_first_error'
                        || $configKey === 'stop_all_first')
                    && $configVal === true
                ) {
                    $convertedValidationArray['<CONFIG>']['stop_all_on_first_error'] = true;
                    cli_success_without_exit("GLOBAL CONFIG RULE ADDED: `stop_all_on_first_error` in Validation `$handlerFile.php=>$fnName`!");
                }

                // If "show_v_data_only_if_all_valid", add it to the CONFIG key
                if ($configKey === 'show_v_data_only_if_all_valid' && $configVal === true) {
                    $convertedValidationArray['<CONFIG>']['show_v_data_only_if_all_valid'] = true;
                    cli_success_without_exit("GLOBAL CONFIG RULE ADDED: `show_v_data_only_if_all_valid` in Validation `$handlerFile.php=>$fnName`!");
                }
            }
        }

        // Finally, remove the `<CONFIG>` key from the validation array since we processed it
        unset($validationArray['<CONFIG>']);
    } else {
        $convertedValidationArray['<CONFIG>'] = null;
        unset($validationArray['<CONFIG>']);
    }

    // We verify each array key is a non-empty string and we verify each value
    // is either a non-empty string or a single array of non-empty strings!
    foreach ($validationArray as $key => $value) {
        if (!is_string_and_not_empty($key)) {
            cli_err_syntax_without_exit("An empty or non-string key found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax("Please make sure all keys (the matching data to validate) are non-empty strings!");
        }
        if (!is_string($value) && !is_array($value)) {
            cli_err_syntax_without_exit("Invalid Validation Rules for \$DX key `$key` found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax_without_exit("Please make sure all Validation Rules are either non-empty strings as elements");
            cli_err_syntax("in a single array OR just a single non-empty string with Validation Rules separated with `|`!");
        }
        if ((is_string($value) && empty($value)) || (is_array($value) && empty($value))) {
            cli_err_syntax_without_exit("No Validation Rules found for \$DX key `$key` found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax_without_exit("Please make sure all Validation Rules are either non-empty strings as elements");
            cli_err_syntax("in a single array OR just a single non-empty string with Validation Rules separated with `|`!");
        }
        // If array, each element must be a non-empty string!
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                if (!is_string_and_not_empty($subValue)) {
                    cli_err_syntax_without_exit("An empty or non-string Validation Rule found for \$DX key `$key` in Validation `$handlerFile.php=>$fnName`!");
                    cli_err_syntax_without_exit("Please make sure all Validation Rules are either non-empty strings as elements");
                    cli_err_syntax("in a single array OR just a single non-empty string with Validation Rules separated with `|`!");
                }
            }
        }
    }

    // Now we finally start converting the validation rules to optimized validation rules
    foreach ($validationArray as $DXkey => $Rules) {
        // Prepare the current rule for the current key and add
        // it to the converted validation array as its own key
        // since each rule will be a subkey there and each rule
        // will also have subkeys such as "value" and "err_msg".
        // The "value" is after ":" and the "err_msg" is inside ("")
        // and it is optional and is otherwise set to null.
        $currentDXKey = $DXkey;
        $convertedValidationArray[$currentDXKey] = [];

        if (str_contains($currentDXKey, ".")) {
            if (!preg_match("/^(\*|(([a-z_]*)([a-z_0-9]+))\.(\*|[a-z_][a-z_0-9]+))(\.(\*|[a-z_][a-z_0-9]+))*$/", $currentDXKey)) {
                cli_err_syntax_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation] Invalid Nested Validation Key in `$currentDXKey`!");
                cli_info("Valid Syntax is: `user.email`, `user.email.primary`, `user.*.email`, `user.*.name` and so on.\nThe `*` character means the key before it indicates this is a numbered array and any keys after it are its subkeys for each element in that numbered array!");
            }
        }

        // Special case: using regex rule inside of string when "|" exists
        // which could cause issues if "|" is used as part of regex rule value!
        // This warning only happens if there is a "|" right after splittig on
        // "regex:" which means You, the Developer, should convert it to an array!
        if (is_string($Rules) && str_contains($Rules, "|") && str_contains($Rules, "regex:")) {
            // First split on "regex:" and then check if that split still contains "|"
            $regexParts = explode("regex:", $Rules);
            if (count($regexParts) > 1 && str_contains($regexParts[1], "|")) {
                // If it does, we warn the user to rewrite the string as an array
                // to avoid issues with regex rule value containing "|"
                cli_warning_without_exit("Please convert string `$Rules` to an array to be able ");
                cli_warning("to use the `regex:` rule due to possible conflicts of `|` inside of the `regex:` rule. Truly sorry for the sudden spontaneous nuisance!");
            }
        }

        // We now check if the $Rules is a string and if it is a string then we check
        // if it has "|" meaning we should split it into an array of rules. If it is just a
        // single rule string we still convert it to an array with a single element.
        // If it is an array we just use it as is.
        if (is_string($Rules) && str_contains($Rules, "|")) {
            // Array filter empty values to avoid empty rules
            $currentRules = explode("|", $Rules);
            $currentRules = array_filter($currentRules, function ($rule) {
                return is_string_and_not_empty($rule);
            });
        } elseif (is_string($Rules) && !str_contains($Rules, "|")) {
            $currentRules = [$Rules];
        } else {
            $currentRules = $Rules;
        }

        // We now iterate through each rule by first checking it against the $regexType
        // array to see if it matches any of them. If not, then it is invalid rule syntax
        // and we error out. If it is valid we then check if it is a rule with a value.
        foreach ($currentRules as $singleRule) {
            // $singleRule is a rule name with a value and an error message?
            if (preg_match($regexType['ONLY_RULE_NAME_AND_VALUE_AND_ERROR'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = $matches[2];
                $currentRuleErrMsgForCurrentDXKeyValue = $matches[3];
            }
            // $singleRule is a rule name with a value but no error message?
            elseif (preg_match($regexType['ONLY_RULE_NAME_AND_VALUE'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = $matches[2];
                $currentRuleErrMsgForCurrentDXKeyValue = null;
            }
            // $singleRule is a rule name with an error message but no value?
            elseif (preg_match($regexType['ONLY_RULE_NAME_AND_ERROR'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = null;
                $currentRuleErrMsgForCurrentDXKeyValue = $matches[2];
            }
            // $singleRule is just a rule name with no value nor error message?
            elseif (preg_match($regexType['ONLY_RULE_NAME'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = null;
                $currentRuleErrMsgForCurrentDXKeyValue = null;
            } else {
                cli_err_syntax_without_exit("Invalid Validation Rule Syntax for `$singleRule` in Validation `$handlerFile.php=>$fnName`!");
                cli_err_syntax_without_exit("Please make sure all Validation Rules are using one of the valid Validation Rule Syntaxes supported in FunkPHP!");
                cli_info("Examples of valid Validation Rule Syntaxes:\n`required` OR `required(\"This is required!\")`\n`min:3` OR `min:3(\"This is too short!\")`\n`between:3,50` OR `between:3,50(\"This is too short or too long!\")");
            }

            // We check if "$currentRuleForCurrentDXKey" actually exists
            // as a funk_validate_function and if it does not exist we error out.
            if (!function_exists('funk_validate_' . $currentRuleForCurrentDXKey)) {
                cli_err_syntax_without_exit("Validation Rule \"$currentRuleForCurrentDXKey\" not found in \"core/functions.php\".");
                cli_info("It must start as a function: `function funk_validate_$currentRuleForCurrentDXKey()` or it will not be found!");
            }

            // We check if the "$currentRuleValueForCurrentDXKeyValue" contains a
            // "," meaning we should split it into an array of values that is the value then!
            // We also trim all the values in the array to remove any whitespace around them.
            if (str_contains($currentRuleValueForCurrentDXKeyValue, ",")) {
                $currentRuleValueForCurrentDXKeyValue = explode(",", $currentRuleValueForCurrentDXKeyValue);
                foreach ($currentRuleValueForCurrentDXKeyValue as $subKey => $subValue) {
                    $currentRuleValueForCurrentDXKeyValue[$subKey] = trim($subValue);
                }

                // We then check if each element is actually a number but that
                // is stringified so we turn it back to a number again!
                foreach ($currentRuleValueForCurrentDXKeyValue as $subKey => $subValue) {
                    if (is_numeric($subValue)) {
                        $currentRuleValueForCurrentDXKeyValue[$subKey] = cli_try_parse_number($subValue);
                    }
                }
            }

            // Check if duplicate rule name is found for the current $currentDXKey which is not allowed
            if (isset($convertedValidationArray[$currentDXKey]["<RULES>"][$currentRuleForCurrentDXKey])) {
                cli_err_syntax_without_exit("Duplicate Validation Rule `$currentRuleForCurrentDXKey` found for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_err_syntax("Please make sure all Validation Rules are unique for each key!");
            }

            // Add the current rule to the converted validation array
            $convertedValidationArray[$currentDXKey]["<RULES>"][$currentRuleForCurrentDXKey] = [
                "value" => is_string($currentRuleValueForCurrentDXKeyValue)
                    ? cli_try_parse_number($currentRuleValueForCurrentDXKeyValue) : $currentRuleValueForCurrentDXKeyValue,
                "err_msg" => $currentRuleErrMsgForCurrentDXKeyValue,
            ];
        }

        // We will now sort some keys for the given $DXKey were are at. For example: we need 'required'
        // and the data type ('string', 'int', 'float', etc.) to be at the top of the array
        // so we can check for them first and then check for the rest of the rules. This will help the
        // actual Validation function which will need to check for some rules before others in order to
        // call the correct validation function (e.g. knowing to call 'funk_validate_minlen' or
        // 'funk_validate_minval' based on whether data type is a string or a number).
        $sortedRulesForField = [];
        $createdRules = $convertedValidationArray[$currentDXKey]["<RULES>"];

        // First Add priority rules first, in their defined order
        // and then add remaining rules in the order they were found.
        // We also make sure only one data type rule is used for each key!
        $addedDataTypeRule = false;
        $firstDataTypeRule = "";
        foreach ($priorityOrder as $ruleName) {
            if (isset($createdRules[$ruleName])) {
                if (in_array($ruleName, $dataTypeRules)) {
                    if ($addedDataTypeRule) {
                        cli_err_syntax_without_exit("Multiple Data Type Rule `$ruleName` (while Data Type Rule `$firstDataTypeRule` already exists) found for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_err_syntax_without_exit("Please make sure only one Data Type Rule is used for each key!");
                        cli_info("Use ONLY ONE of these Data Type Rules for each single Input Data: `string`, `integer`, `float`, `boolean`, `number`, `date`, `array`,\n\t\t  `email`, `email_custom`, `password`,`password_custom`, `password_confirm`, `url`, `ip`, `uuid`, `phone`, `object`, `json`, `enum`,\n\t\t  `set`, `ip4`, `ip6`, `checked`, `file`, `audio`, `video`, `image`, or `list`, `char`, or `digit`!");
                    }
                    $addedDataTypeRule = true;
                }
                $firstDataTypeRule = $ruleName;
                $sortedRulesForField[$ruleName] = $createdRules[$ruleName];
                unset($createdRules[$ruleName]);
            }
        }
        foreach ($createdRules as $ruleName => $details) {
            $sortedRulesForField[$ruleName] = $details;
        }

        // A final check that there is actually one data type rule added or
        // we error out since each $currentDXKey must have a data type rule!
        $noDataTypeRule = true;
        foreach ($dataTypeRules as $dataTypeRule) {
            if (isset($sortedRulesForField[$dataTypeRule])) {
                $noDataTypeRule = false;
                break;
            }
        }
        if ($noDataTypeRule) {
            cli_err_syntax_without_exit("No Data Type Rule found for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
            cli_info("Use ONLY ONE of these Data Type Rules for each single Input Data: `string`, `integer`, `float`, `boolean`, `number`, `date`, `array`,\n\t\t  `email`, `email_custom`, `password`,`password_custom`, `password_confirm`, `url`, `ip`, `uuid`, `phone`, `object`, `json`, `enum`,\n\t\t  `set`, `ip4`, `ip6`, `checked`, `file`, `audio`, `video`, `image`, or `list`, `char`, or `digit`!");
        }


        /*
            MANY SPECIAL CASES ARE CHECKED HERE - START:
            This includes special cases both
            for Rules and the $DXKey itself.
        */
        // First we get the current data type rule for the $currentDXKey
        // so we can make special case checks for it like not a negative
        // number for "min" rule when string type is used, etc.
        $categorizedDataTypeRules = [
            'string_types' => [
                'string' => true,
                'char' => true,
                'email' => true,
                'email_custom' => true,
                'password' => true,
                'password_custom' => true,
                'password_confirm' => true,
                'json' => true,
                'url' => true,
                'ip' => true,
                'ip4' => true,
                'ip6' => true,
                'uuid' => true,
                'phone' => true,
                'date' => true,
            ],
            'number_types' => [
                'digit' => true,
                'integer' => true,
                'float' => true,
                'number' => true,
            ],
            'array_types' => [
                'array' => true,
                'list' => true,
                'set' => true,
            ],
            'file_types' => [
                'file' => true,
                'image' => true,
                'audio' => true,
                'video' => true,
            ],
            'complex_types' => [
                'null' => true,
                'object' => true,
                'unchecked' => true,
                'checked' => true,
                'enum' => true,
                'boolean' => true,
            ],
        ];
        $foundTypeRule = null;
        $foundTypeCat = null;
        foreach ($sortedRulesForField as $ruleName => $ruleConfig) {
            if (
                isset($categorizedDataTypeRules['string_types'][$ruleName])
            ) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'string_types';
                break;
            } elseif (isset($categorizedDataTypeRules['number_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'number_types';
                break;
            } elseif (isset($categorizedDataTypeRules['array_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'array_types';
                break;
            } elseif (isset($categorizedDataTypeRules['complex_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'complex_types';
                if (isset($categorizedDataTypeRules['file_types'][$ruleName])) {
                    $foundTypeCat = 'file_types';
                }
                break;
            }
        }

        // LIST OF COMMON DATE FORMATS IN STRINGS THAT CAN
        // BE CONVERTED TO THEIR FORMATTED DATE STRINGS
        $commonDateFormats = [
            "ATOM" => "Y-m-d\\TH:i:sP",
            "COOKIE" => "l, d-M-Y H:i:s T",
            "ISO8601" => "Y-m-d\\TH:i:sO",
            "ISO8601_EXPANDED" => "X-m-d\\TH:i:sP",
            "RFC822" => "D, d M y H:i:s O",
            "RFC850" => "l, d-M-y H:i:s T",
            "RFC1036" => "D, d M y H:i:s O",
            "RFC1123" => "D, d M Y H:i:s O",
            "RFC7231" => "D, d M Y H:i:s \\G\\M\\T",
            "RFC2822" => "D, d M Y H:i:s O",
            "RFC3339" => "Y-m-d\\TH:i:sP",
            "RFC3339_EXTENDED" => "Y-m-d\\TH:i:s.vP",
            "RSS" => "D, d M Y H:i:s O",
            "W3C" => "Y-m-d\\TH:i:sP",
        ];
        $validDateFormatCharacters = [
            "d",
            'D',
            'j',
            'l',
            'N',
            'S',
            'w',
            'z',
            'W',
            'F',
            'm',
            'M',
            'n',
            't',
            'L',
            'o',
            'X',
            'x',
            'Y',
            'y',
            'a',
            'A',
            'B',
            'g',
            'G',
            'h',
            'H',
            'i',
            's',
            'u',
            'v',
            'e',
            'I',
            'O',
            'P',
            'T',
            'Z',
            'c',
            'r',
            'U',
            '.',
            ':',
            '-',
            '+',
            '/',
            '\\',
            ' ',
        ];
        $timezoneAwareFormats =  [
            "ATOM",
            "COOKIE",
            "ISO8601",
            "ISO8601_EXPANDED",
            "RFC822",
            "RFC850",
            "RFC1036",
            "RFC1123",
            "RFC7231",
            "RFC2822",
            "RFC3339",
            "RFC3339_EXTENDED",
            "RSS",
            "W3C"
        ];

        // LIST OF RULES THAT SHOULD NOT HAVE A VALUE
        // AND THUS A WARNING WILL BE SHOWN IF THEY DO!
        $theseRulesShouldHaveNoValue = [
            'required',
            'nullable',
            'lowercase',
            'uppercase',
            'string',
            'integer',
            'float',
            'boolean',
            'number',
            'array',
            'list',
            'unchecked',
            'checked',
            'file',
            'image',
            'video',
            'audio',
            'object',
            'enum',
            'set',
            'json',
            'url',
            'ip',
            'ip4',
            'ip6',
            'uuid',
            'phone',
            'elements_all_chars', // single characters per element
            'elements_all_arrays',
            'elements_all_lists', // numbered arrays
            'elements_all_strings',
            'elements_all_nulls',
            'elements_all_numbers',
            'elements_all_integers',
            'elements_all_floats',
            'elements_all_booleans',
            'elements_all_checked',
            'elements_all_unchecked',
        ];

        // LIST OF RULES that must have a value, but this array
        // does not specify what kind of values but does provide
        // a quick first check using a loop below!
        $theseRulesMustHaveValues = [
            'elements_this_type_order',
            'uppercases',
            'lowercases',
            'numbers',
            'specials',
            'min_digits',
            'max_digits',
            'min',
            'max',
            'field',
            'count',
            'exact',
            'count',
            'digits',
            'between',
            'array_keys',
            'array_values',
            'array_keys_exact',
            'array_values_exact',
            'any_of_these_values'
        ];

        // List of specific values for specific data types
        // that are allowed to be used as value(s) for the rule.
        $allowedSpecificRuleValuesForDataTypes = [
            'email' => ['dns', 'tld'],
        ];

        // List of other Rules that some Data Types Rules
        // ONLY can have. For example `digit` can only have
        // "required","nullable" & "field" but nothing else.
        // So this array is compared so no other rules are
        // used when the specific data type is used.
        $allowedOtherRulesForSpecificDataTypeRule = [
            'digit' => [
                'required',
                'nullable',
                'field'
            ],
            'email' => [
                'required',
                'nullable',
                'field',
                'min',
                'max',
                'between',
                'regex',
                'not_regex',
                'unique',
                'exists',
            ],
            'set' => [
                'required',
                'nullable',
                'field',
                'any_of_these_values',
                'regex',
                'not_regex',
                'unique',
                'exists',
            ],
            'enum' => [
                'required',
                'nullable',
                'field',
                'any_of_these_values',
                'regex',
                'not_regex',
                'unique',
                'exists',
            ],
        ];

        // Special cases for any rule found in $theseRulesShouldHaveNoValue that has a value
        // when it should not (but we do not error out but just warn that the value will be ignored).
        foreach ($theseRulesShouldHaveNoValue as $ruleName) {
            if (isset($sortedRulesForField[$ruleName]) && isset($sortedRulesForField[$ruleName]['value'])) {
                cli_warning_without_exit("The `$ruleName` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a value set!");
                cli_info_without_exit("This has been set to `null` since the `$ruleName` Rule does not use a value!");
                $sortedRulesForField[$ruleName]['value'] = null;
            }
        }

        // Special case for any rule found in $theseRulesMustHaveValues
        // that does not have a value when it should (but we do error out).
        foreach ($theseRulesMustHaveValues as $ruleName) {
            if (isset($sortedRulesForField[$ruleName])) {
                // If the rule is in $theseRulesMustHaveValues, we check if it has a value
                // and if it does not, we error out.
                if (!isset($sortedRulesForField[$ruleName]['value']) || empty($sortedRulesForField[$ruleName]['value'])) {
                    cli_err_syntax_without_exit("The `$ruleName` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must have a non-empty value!");
                    cli_info("Specify a non-empty value for the `$ruleName` Rule. This could be several values separated by commas. You will be informed!");
                }
            }
        }

        // Iterate through all rules and see if they are a key in
        // $allowedSpecificRuleValuesForDataTypes. If they are, loop
        // through all its values and check if the value
        foreach ($sortedRulesForField as $ruleKey => $ruleName) {
            // If the rule is a data type rule, we check if it has a value
            // and if it does, we check if it is in the allowed specific rule values
            // for that data type. If not, we error out.
            if (isset($allowedSpecificRuleValuesForDataTypes[$ruleKey]) && isset($sortedRulesForField[$ruleKey]['value'])) {
                $ruleValue = $sortedRulesForField[$ruleKey]['value'];
                if (is_string($ruleValue) && !in_array($ruleValue, $allowedSpecificRuleValuesForDataTypes[$ruleKey] ?? [])) {
                    cli_err_syntax_without_exit("Invalid Value for `$ruleKey` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    $transformed = "";
                    foreach ($allowedSpecificRuleValuesForDataTypes[$ruleKey] as $allowedValue) {
                        $transformed .= "`$allowedValue`, ";
                    }
                    $transformed = rtrim($transformed, ", ");
                    cli_info("Allowed Values for `$ruleKey` Rule are: $transformed!");
                }
                // If the rule value is an array, we check if all its
                // values are in the allowed specific rule values!
                else if (is_array($ruleValue)) {
                    foreach ($ruleValue as $value) {
                        if (!in_array($value, $allowedSpecificRuleValuesForDataTypes[$ruleKey] ?? [])) {
                            cli_err_syntax_without_exit("Invalid Value for `$ruleKey` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            $transformed = "";
                            foreach ($allowedSpecificRuleValuesForDataTypes[$ruleKey] as $allowedValue) {
                                $transformed .= "`$allowedValue`, ";
                            }
                            $transformed = rtrim($transformed, ", ");
                            cli_info("Allowed Values for `$ruleKey` Rule are: $transformed!");
                        }
                    }
                }
            }
        }

        // Special case where we check for a "$currentDXKey" that ends with a "*"
        // that its type is "list", otherweise we error out. We also check for
        // the "min", "max", "count", "exact" and "between" rules to see if they
        // are set, otherwise we warn the user that it could lead to infinite loop!
        if (str_ends_with($currentDXKey, "*")) {
            if (!isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `$currentDXKey` key in Validation `$handlerFile.php=>$fnName` must the Array Numbering Data Type `list`!");
                cli_info("Specify `list` Data Type for `$currentDXKey` to use the Array Numbering `*` Character at the end of the Key!");
            }
            // We check if the rule "min" exists while "max" does not exist so we warn
            // about that it could lead to infinite loop in the validation function
            if (
                !isset($sortedRulesForField['max'])
                && !isset($sortedRulesForField['count'])
                && !isset($sortedRulesForField['size'])
                && !isset($sortedRulesForField['exact'])
                && !isset($sortedRulesForField['between'])
            ) {
                cli_err_without_exit("There are no Array Elements Limiting Rule(s) set for the Numbered Array `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("Add `between`,`count`,`exact`, `size` or `max` Rule to prevent CPU/DoS exploits!");
                cli_info_without_exit("Just set a very high number to prevent infinite loops while still processing as many as you think you will need!");
                cli_info("The Value in your `between` (the higher value),`count`,`exact`, `size` or `max` Rule will set the number of iterations for the Numbered Array `$currentDXKey`!");
            }
        }

        // Special case ofr "stop_all_on_first_error" Rule
        // meaning we set the $stop_on_first_error to true
        // and if we see this rule again we will just remove it
        // as it is just a special root key that is used
        // to stop all validation on the first error found.
        if (isset($sortedRulesForField['stop_all_on_first_error'])) {
            if ($stop_on_first_error) {
                cli_info_without_exit("The `stop_all_on_first_error` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is already set!");
            } else {
                cli_success_without_exit("Special Root Key Rule `stop_all_on_first_error` in `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is set!");
                cli_info_without_exit("This will remove further occurrences of this Rule in the Validation Rules for this Key!");
                $stop_on_first_error = true;
                unset($sortedRulesForField['stop_all_on_first_error']);
            }
            unset($sortedRulesForField['stop_all_on_first_error']);
        }

        // Special case for 'field' Rule it can ONLY have
        // a single string as a value, so we check that!
        if (isset($sortedRulesForField['field'])) {
            if (!is_string($sortedRulesForField['field']['value']) || empty($sortedRulesForField['field']['value'])) {
                cli_err_syntax_without_exit("The `field` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must have a non-empty string value!");
                cli_info("Specify a non-empty string as the value for the `field` Rule!");
            }
            // if it has an error message we remove it and inform
            if (isset($sortedRulesForField['field']['err_msg']) && !empty($sortedRulesForField['field']['err_msg'])) {
                cli_warning_without_exit("The `field` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has an error message set!");
                cli_info_without_exit("The `field` Rule does not use an error message, so it has been set to null!");
                $sortedRulesForField['field']['err_msg'] = null;
            }
        }

        // Special case for 'email" Rule
        if (isset($sortedRulesForField['email'])) {
            foreach ($sortedRulesForField as $ruleName => $ruleConfig) {
                if (
                    $ruleName !== 'email'
                    && isset($allowedOtherRulesForSpecificDataTypeRule['email'])
                    && !in_array($ruleName, $allowedOtherRulesForSpecificDataTypeRule['email'])
                ) {
                    cli_err_syntax_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` cannot have the `$ruleName` Rule!");
                    cli_info("The `email` Rule can ONLY have the following additional Rules:\n" . implode(",\n", quotify_elements($allowedOtherRulesForSpecificDataTypeRule['email'])) . "!");
                }
            }
            // Check if the 'max' value is lower than 6 then we warn that the email would be
            // too short as its max length.
            if (isset($sortedRulesForField['min'])) {
                $minValue = $sortedRulesForField['min']['value'];
                if (is_numeric($minValue) && $minValue < 6) {
                    cli_warning_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `min` Rule with a value less than 6!");
                    cli_info_without_exit("This means the email would be too short to be valid. Consider increasing the min value to at least 6!");
                }
            }
            if (isset($sortedRulesForField['max'])) {
                $maxValue = $sortedRulesForField['max']['value'];
                if (is_numeric($maxValue) && $maxValue < 6) {
                    cli_warning_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `max` Rule with a value less than 6!");
                    cli_info_without_exit("This means the email would be too short to be valid. Consider increasing the max value to at least 6!");
                }
            }
            // The first value of `between` Rule should also be at least 6 or warn
            if (isset($sortedRulesForField['between'])) {
                $betweenValue = $sortedRulesForField['between']['value'];
                if (is_array($betweenValue) && isset($betweenValue[0]) && is_numeric($betweenValue[0]) && $betweenValue[0] < 6) {
                    cli_warning_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `between` Rule with a first value less than 6!");
                    cli_info_without_exit("This means the email would be too short to be valid. Consider increasing the first value to at least 6!");
                }
            }
        }

        // Special case for 'date' Rule
        if (isset($sortedRulesForField['date'])) {
            if (!isset($sortedRulesForField['date']['value'])) {
                cli_warning_without_exit("The `date` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` does not have a date format value set!");
                cli_info_without_exit("The `date` Rule will use the default date format of 'Y-m-d H:i:s' instead when being called!");
            }
            // If values exist for Date Rule then we iterate through each value
            // to check if it exists in $commonDateFormats and thus we replace
            // it with actual formatted date string. This is OPTIONAL though!
            elseif (isset($sortedRulesForField['date']['value'])) {
                $dateValue = $sortedRulesForField['date']['value'];
                if (is_string($dateValue)) {
                    if (array_key_exists($dateValue, $commonDateFormats)) {
                        $sortedRulesForField['date']['value'] = $commonDateFormats[$dateValue];
                    }
                }
                // If the date value is an array, we check each value against the common date formats
                elseif (is_array($dateValue)) {
                    foreach ($dateValue as $index => $subValue) {
                        if (array_key_exists($subValue, $commonDateFormats)) {
                            $dateValue[$index] = $commonDateFormats[$subValue];
                        }
                    }
                    // Reassign the possibly modified array back to the date value
                    $sortedRulesForField['date']['value'] = $dateValue;
                }

                // Get updated date value after checking against common formats
                $dateValue = $sortedRulesForField['date']['value'];

                // We now iterate through each character in the date value
                // to check that it only uses any of the valid date format characters
                if (is_string($dateValue)) {
                    $dateChars = str_split($dateValue);
                    foreach ($dateChars as $char) {
                        if (!in_array($char, $validDateFormatCharacters)) {
                            cli_err_syntax_without_exit("Invalid Date Format Character `$char` in `date` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            cli_info("The `date` Rule Value must only use valid date format characters:\n" . implode(",\n", quotify_elements($validDateFormatCharacters)) . ". Character list is based on: https://www.php.net/manual/en/datetime.format.php");
                        }
                    }
                } elseif (is_array($dateValue)) {
                    foreach ($dateValue as $subValue) {
                        $subChars = str_split($subValue);
                        foreach ($subChars as $char) {
                            if (!in_array($char, $validDateFormatCharacters)) {
                                cli_err_syntax_without_exit("Invalid Date Format Character `$char` in `date` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                                cli_info("The `date` Rule Value must only use valid date format characters:\n" . implode(",\n", quotify_elements($validDateFormatCharacters)) . ". Character list is based on: https://www.php.net/manual/en/datetime.format.php");
                            }
                        }
                    }
                }
            }
        }

        // Special case for "digit" Rule. We check that its value is (whether a single string
        // or an array of values) are all single digits (0-9) and if not, we error out. We also
        // check for duplicates in the digits and if there are duplicates, we error out.
        if (isset($sortedRulesForField['digit'])) {
            if (isset($sortedRulesForField['digit']['value'])) {
                $digitValue = $sortedRulesForField['digit']['value'];
                // Check if the string is a single digit
                if (is_string($digitValue)) {

                    if (!preg_match('/^[0-9]$/', $digitValue)) {
                        cli_err_syntax_without_exit("Invalid `digit` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("The `digit` Rule Value must be a Single Digit (0-9)!");
                    }
                } elseif (is_array($digitValue)) {
                    // Check that each value in the array is a single digit
                    foreach ($digitValue as $subValue) {
                        if (!preg_match('/^[0-9]$/', $subValue)) {
                            cli_err_syntax_without_exit("Invalid `digit` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            cli_info("If several Digits are used, the `digit` Rule Value must be an Array of only Single Digits (0-9)!");
                        }
                    }
                    // Check for duplicates in the array
                    if (count($digitValue) !== count(array_unique($digitValue))) {
                        cli_err_syntax_without_exit("Duplicate Digits found in `digit` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("All Digits must be unique in the `digit` Rule Value Array!");
                    }
                } else {
                    cli_err_syntax_without_exit("Invalid `digit` Rule Value Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("The `digit` Rule Value must be a Single Digit or an Array of Single Digits (0-9)!");
                }
            }
            // We loop through "$sortedRulesForField" to see if the "digit" Rule
            // has any other rules that are NOT inside of "$allowedOtherRulesForSpecificDataTypeRule"
            // meaning they cannot be used with the "digit" Rule and we error out.
            foreach ($sortedRulesForField as $ruleName => $ruleConfig) {
                if (
                    $ruleName !== 'digit'
                    && isset($allowedOtherRulesForSpecificDataTypeRule['digit'])
                    && !in_array($ruleName, $allowedOtherRulesForSpecificDataTypeRule['digit'])
                ) {
                    cli_err_syntax_without_exit("The `digit` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` cannot have the `$ruleName` Rule!");
                    cli_info("The `digit` Rule can ONLY have the following additional Rules:\n" . implode(",\n", quotify_elements($allowedOtherRulesForSpecificDataTypeRule['digit'])) . "!");
                }
            }
        }

        // Special cases for "password" Rule
        if (isset($sortedRulesForField['password'])) {
            // We loop through "$validationArray" to see if there is a "password_custom" rule
            // in any of the other $DXKeys and if there is, we warn the user that
            // they should also use "password_confirm" to increase security.
            $foundPasswordConfirm = false;
            foreach ($validationArray as $key => $value) {
                if (is_string($value) && str_contains($value, 'password_confirm')) {
                    $foundPasswordConfirm = true;
                    break;
                }
            }
            if (!$foundPasswordConfirm) {
                cli_warning_without_exit("The `password` Data Type Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is recommended (and also optional) to also have an accompanying `password_confirm` Data Type in another `\$DX Key`!");
            }
            // Check that current $DXKey also has a "between" Rule
            if (!isset($sortedRulesForField['between'])) {
                cli_err_syntax_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` need a `between` Rule!");
                cli_info("Add the `between` Rule to `$currentDXKey` to use the `password` Rule!");
            }
            // If the "between" Rule's first value is shorter than 12 characters, we warn but allow it
            if (isset($sortedRulesForField['between'])) {
                $betweenValue = $sortedRulesForField['between']['value'];
                if ($betweenValue[0] < 12) {
                    cli_warning_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `between` Rule with a first value less than 12!");
                    cli_info_without_exit("Allow passwords shorter than 12 characters? Otherwise change the first value to at least 12!");
                }
            }
            // Check that current $DXKey also has a "required" Rule or just warn
            // because you might wanna use the `password` Rule without `required` Rule
            if (!isset($sortedRulesForField['required'])) {
                cli_err_syntax_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` need a `required` Rule!");
                cli_info_without_exit("If `$currentDXKey` should be optional as a `password`, use the `string` Data Type with the `password_hash` Rule instead!");
                cli_info("This will password_hash the value stored in `$currentDXKey` after ALL validation has passed for all Input Data!");
            }
            if (isset($sortedRulesForField['nullable'])) {
                cli_err_syntax_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` cannot use `nullable` Rule!");
                cli_info_without_exit("If `$currentDXKey` should be optional as a `password`, use the `string` Data Type with the `password_hash` Rule instead!");
                cli_info("This will \"password_hash\" the value stored in `$currentDXKey` after ALL validation has passed for the value stored in `$currentDXKey`!");
            }
            // Check if the `password` has any values stored, and otherwise just warn and inform what each
            // value means in what order if they wanna use it.
            if (!isset($sortedRulesForField['password']['value']) || empty($sortedRulesForField['password']['value'])) {
                cli_warning_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has no value set!");
                cli_warning_without_exit("This means you have NO CHECKS on the password length or complexity - just a heads up!");
                cli_info_without_exit("Values for the `password` Rule are parsed in this order:number_of_uppercases,number_of_lowercases,number_of_digits,number_of_specials");
                cli_info_without_exit("Or specify them using `uppercases:INT`, `lowercases:INT`, `specials:INT`, `numbers:INT` as additional Rules for the `password` Data Type Rule!");
                cli_info_without_exit("EXAMPLE:`password:2,2,2,2` means 2 uppercases, 2 lowercases, 2 digits and 2 specials!");
                cli_info_without_exit("If you wanna change what is considered a special character, either use `password_custom` Data Type Rule for your very own");
                cli_info_without_exit("Custom Password Validation Logic OR edit `core/functions.php` in the `funk_validate_password` function!");
            }
            // If `password` rule has values, check that each value are all integers!
            if (isset($sortedRulesForField['password']['value'])) {
                $passwordValues = $sortedRulesForField['password']['value'];
                if (is_string($passwordValues)) {
                    // Check that string is a single integer by trying to parse it
                    if (!is_int($passwordValues)) {
                        cli_err_syntax_without_exit("Invalid `password` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info_without_exit("Specify an Array of Integers as the value for the `password` Rule. Up to 4 Integers are allowed!");
                        cli_info("First=number of uppercases, Second=number of lowercases, Third=number of digits, Fourth=number of specials!");
                    }
                } elseif (is_array($passwordValues)) {
                    if (count($passwordValues) < 1 || count($passwordValues) > 4) {
                        cli_err_syntax_without_exit("Invalid `password` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info_without_exit("Specify an Array of Integers as the value for the `password` Rule. Up to 4 Integers are allowed!");
                        cli_info("First=number of uppercases, Second=number of lowercases, Third=number of digits, Fourth=number of specials!");
                    }
                    foreach ($passwordValues as $value) {
                        if (!is_int($value)) {
                            cli_err_syntax_without_exit("Invalid `password` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            cli_info_without_exit("Specify an Array of Integers as the value for the `password` Rule. Up to 4 Integers are allowed!");
                            cli_info("First=number of uppercases, Second=number of lowercases, Third=number of digits, Fourth=number of specials!");
                        }
                    }
                }
            }
            // If `password` rule has an error message, we warn about how it only returns a single error default message
            // even if you check four uppercases, lowercases, digits and specials at the same time. Recommended to add a
            // custom error message to the `password` Rule so it is more informative.
            if (!isset($sortedRulesForField['password']['err_msg'])) {
                cli_warning_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has no Custom Error Message set!");
                cli_info_without_exit("The Default Error Message only returns about one thing at a time (uppercase missing, digit missing, etc.)");
                cli_info_without_exit("Recommended to add a custom error message to the `password` Rule so it is more informative!");
            }
            // Here, the "password" rule passed all checks
            // so we now add it '<CONFIG>' => 'passwords_to_match' array!
            // When we come acropss 'password_confirm' Rule we will add it as the value to this!
            $convertedValidationArray['<CONFIG>']['passwords_to_match'][$currentDXKey] = "";
        }

        // Special cases for "password_confirm" Rule
        if (isset($sortedRulesForField['password_confirm'])) {
            $foundPasswordCustom = false;
            foreach ($validationArray as $key => $value) {
                if (is_string($value) && str_contains($value, 'password') && !str_contains($value, 'password_confirm')) {
                    $foundPasswordCustom = true;
                    break;
                }
            }
            if (!$foundPasswordCustom) {
                cli_warning_without_exit("The `password_confirm` Data Type Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is recommended to also have an accompanying `password` Data Type in another `\$DX Key`!");
            }
            // Check that password_confirm has a value
            if (!isset($sortedRulesForField['password_confirm']['value']) || empty($sortedRulesForField['password_confirm']['value'])) {
                cli_err_syntax_without_exit("The `password_confirm` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must have a value!");
                cli_info("Specify the Password Field (the field with a `password` Data Type Rule) as the value for the `password_confirm` Rule!");
            }
            // Check that any $validationArray that has the value from password_confirm
            if (isset($sortedRulesForField['password_confirm']['value'])) {
                $passwordConfirmValue = $sortedRulesForField['password_confirm']['value'];
                // If the value is not a string, we error out
                if (!is_string_and_not_empty($passwordConfirmValue)) {
                    cli_err_syntax_without_exit("Invalid `password_confirm` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify a Non-Empty String as the value for the `password_confirm` Rule!");
                }
                // If the value is not a valid key in $validationArray, we error out
                if (!isset($validationArray[$passwordConfirmValue])) {
                    cli_err_syntax_without_exit("Invalid `password_confirm` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info_without_exit("Specify a Valid Key (using the Data Type `password` or `password_custom`) from the \$DX Array as the value for the `password_confirm` Rule which it should confirm against.");
                    cli_info("This Data Type (`password` or `password_custom`) must be on the same Key Level as the `password_confirm` Rule that should confirm against it!");
                }
            }
            // Check that current $DXKey ONLY has "required" and "password_confirm" Rules meaning the count
            // should be 2, otherwise we error out since it is not allowed to have other rules
            if (count($sortedRulesForField) !== 2 || !isset($sortedRulesForField['required']) || !isset($sortedRulesForField['password_confirm'])) {
                cli_err_syntax_without_exit("The `password_confirm` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must only have `required` and `password_confirm` Rules!");
                cli_info("Remove any other Rules from `$currentDXKey` to use the `password_confirm` Rule!");
            }
            // All checks passed, so we add the `password_confirm` Rule to the corresponding "passwords_to_match" array
            // here its value is the key of the `password` Rule that it should confirm against. If it does not exist
            // we will error out later when we try to match the passwords so we error out here!
            if (!isset($convertedValidationArray['<CONFIG>']['passwords_to_match'][$sortedRulesForField['password_confirm']['value']])) {
                cli_err_syntax_without_exit("The `password_confirm` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has no corresponding `password` Rule to confirm against!");
                cli_info("Specify a Valid Key (using the Data Type `password` or `password_custom`) from the \$DX Array as the value for the `password_confirm` Rule which it should confirm against.");
                cli_info("This Data Type (`password` or `password_custom`) must be on the same Key Level as the `password_confirm` Rule that should confirm against it!");
            } else {
                $convertedValidationArray['<CONFIG>']['passwords_to_match'][$sortedRulesForField['password_confirm']['value']] = $currentDXKey;
            }
        }

        // Special cases for the "between" Rule
        if (isset($sortedRulesForField['between'])) {
            $betweenValue = $sortedRulesForField['between']['value'];
            // If between is not an array, we error out
            if (!is_array($betweenValue)) {
                cli_err_syntax_without_exit("Invalid `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify Two Numbers (separated with a comma) as the value for the `between` Rule!");
            }
            // If between is not two numbers, we error out
            if (count($betweenValue) !== 2 || !is_numeric($betweenValue[0]) || !is_numeric($betweenValue[1])) {
                cli_err_syntax_without_exit("Invalid `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify Two Numbers (separated with a comma) as the values for the `between` Rule!");
            }
            if ((is_float($betweenValue[0]) || is_float($betweenValue[1])) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `between` rule with decimal value(s)!");
            }
            if ($betweenValue[0] > $betweenValue[1]) {
                cli_err_syntax_without_exit("The `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` First Number is Larger than the Second Number!");
                cli_info("Specify the First Number as less than or equal to the Second Number for the `between` Rule!");
            }
            if ($betweenValue[0] === $betweenValue[1]) {
                cli_warning_without_exit("The `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal between Both Numbers!");
                cli_info_without_exit("Recommended is to use `exact` but this will work without issues, might be confusing though!");
            }
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($betweenValue[0])
                && ($betweenValue[0] < 0 || $betweenValue[1] < 0)
            ) {
                cli_err_syntax_without_exit("Invalid `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the First Value in the Array for the `between` Rule when Data Type is a String or an array!");
            }
            // Cannot use "min", "max", "size", "exact", rules with "between" rule since it is confusing
            if (
                isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['exact'])
                || isset($sortedRulesForField['any_of_these_values'])
            ) {
                cli_err_syntax_without_exit("The `between` Rule does not work with `min`, `max`, `size`, `exact` or `any_of_these_values`, Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `between` Rule is meant to be a range which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `min`, `max`, `size`, `exact`, `any_of_these_values`, Rules to use the `between` Rule - or vice versa!");
            }
        }

        // Special cases for the "decimals" Rule
        if (isset($sortedRulesForField['decimals']) && !isset($sortedRulesForField['float'])) {
            cli_err_syntax_without_exit("The `decimals` Rule needs `float` data type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
            cli_info("Specify the `float` data type rule for `$currentDXKey` if you want to use the `decimals` rule!");
        }
        if (isset($sortedRulesForField['decimals'])) {
            $decimalsValue = $sortedRulesForField['decimals']['value'];
            if (is_array($decimalsValue)) {
                if (count($decimalsValue) > 2 || count($decimalsValue) < 1) {
                    cli_err_syntax_without_exit("Invalid `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify either one(1) or two(2) integers as the value for the `decimals` rule!");
                }
                if ($decimalsValue[0] > 20 || (count($decimalsValue) > 1 && $decimalsValue[1] > 20)) {
                    cli_warning_without_exit("Dangerous `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info_without_exit("Specify integers between 0 and 20 as the value for the `decimals` rule!");
                }
                if ($decimalsValue[0] > $decimalsValue[1]) {
                    cli_warning_without_exit("Dangerous `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info_without_exit("Specify the First Integer as less than or equal to the Second Integer for the `decimals` rule!");
                }
                if ($decimalsValue[0] === $decimalsValue[1]) {
                    cli_warning_without_exit("The `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal between Both Numbers!");
                    cli_info_without_exit("Recommended is to use `exact` but this will work without issues, might be confusing though!");
                }
                foreach ($decimalsValue as $subValue) {
                    if (!is_int($subValue)) {
                        cli_err_syntax_without_exit("Invalid `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify only Integers (whole numbers) as the value for the `decimals` rule!");
                    }
                }
            } elseif (!is_int($decimalsValue)) {
                cli_err_syntax_without_exit("Invalid `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an integer or an array of integers (whole numbers) as the value for the `decimals` rule!");
            }
        }

        // Special cases for the "min" Rule
        if (isset($sortedRulesForField['min'])) {
            $minValue = $sortedRulesForField['min']['value'];
            // If min is not a number, we error out
            if (!is_numeric($minValue)) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Number as the value for the `min` rule!");
            }
            // When min is float but data type is NOT float, we error out
            if (is_float($minValue) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `min` rule with a decimal value!");
            }
            // If min is negative when data type is a string type, we error out
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($minValue)
                && $minValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `min` rule when Data Type is a String or an Array!");
            }
            // min is set but not max so warn about getting larger data than desired
            if (isset($sortedRulesForField['max']) && $sortedRulesForField['max']['value'] < $minValue) {
                cli_warning_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the `max` Rule Value!");
                cli_info_without_exit("This could lead to processing more than desired, consider adding a `max` Rule or changing to the `between` Rule instead!");
            }
            // min is set but not max so warn about getting larger data than desired
            if (!isset($sortedRulesForField['max'])) {
                cli_warning_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is set but the `max` Rule is not!");
                cli_info_without_exit("This could lead to processing more than desired, consider adding a `max` Rule or changing to the `between` Rule instead!");
            }
        }

        // Special cases for the "max" Rule
        if (isset($sortedRulesForField['max'])) {
            $maxValue = $sortedRulesForField['max']['value'];
            // If max is not a number, OR it is a float but not data type float or number, we error out
            if (!is_numeric($maxValue)) {
                cli_err_syntax_without_exit("Invalid `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Number as the value for the `max` rule!");
            }
            if (is_float($maxValue) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `max` rule with a decimal value!");
            }
            // If max is negative when data type is a string type, we error out
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($maxValue)
                && $maxValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `max` rule when Data Type is a String or an Array!");
            }
        }

        // Special case for "min" + "max" Rule
        if (isset($sortedRulesForField['min']) && isset($sortedRulesForField['max'])) {
            $minValue = $sortedRulesForField['min']['value'];
            $maxValue = $sortedRulesForField['max']['value'];
            // If min is larger than max, we error out
            if (is_numeric($minValue) && is_numeric($maxValue) && $minValue > $maxValue) {
                cli_err_syntax_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the `max` Rule Value!");
                cli_info("Specify the `min` Rule Value as less than the `max` Rule Value for `$currentDXKey`!");
            }
            if (is_numeric($minValue) && is_numeric($maxValue) && $minValue === $maxValue) {
                cli_warning_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal to the `max` Rule Value!");
                cli_info_without_exit("Recommended is to use `exact` but this will work without issues, might be confusing though!");
            }
        }

        // Special cases for the "count" Rule (affects arrays & lists)
        if (isset($sortedRulesForField['count'])) {
            $countValue = $sortedRulesForField['count']['value'];
            // If count is NOT used with "array" or "list" data type, we error out
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list']) && !isset($sortedRulesForField['set'])) {
                cli_err_syntax_without_exit("The `count` Rule must be used with Data Type `array`, `list` or `set` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array`, `list` OR `set` Data Type Rule for `$currentDXKey` to use the `count` rule!");
            }
            // If count is not a number, we error out
            if (!is_int($countValue)) {
                cli_err_syntax_without_exit("Invalid `count` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `count` Rule!");
            }
            // If count is negative when data type is a array type, we error out
            if (
                ($foundTypeCat === 'array_types')
                && is_int($countValue)
                && $countValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `count` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `count` Rule when Data Type is a String or an Array!");
            }
            // If count value is 0 but there is required rule, we error out
            if ($countValue === 0 && isset($sortedRulesForField['required'])) {
                cli_err_syntax_without_exit("The `count` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is 0 but there is a `required` Rule!");
                cli_info("Remove the `required` Rule or set the `count` Rule Value to 1 or more for `$currentDXKey`!");
            }
        }

        // Special cases for the "exact" Rule
        if (isset($sortedRulesForField['exact'])) {
            $exactValue = $sortedRulesForField['exact']['value'];

            // If data type is string typed then we strval force the exact value
            if (is_numeric($exactValue) && $foundTypeCat === "string_types") {
                $sortedRulesForField['exact']['value'] = strval($sortedRulesForField['exact']['value']);
                cli_info_without_exit("The `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Numberic while Data Type is String-typed!");
                cli_info("Its Rule Value converted to String Value for `$currentDXKey`. Change this manually back to numeric value if you intended to use an exact numeric value!");
            }

            // 'exact' Rule shouldn't not be combined with 'count', 'between', 'min', or 'max' Rules
            // as 'exact' is a strict rule that expects a strict single value.
            if (
                isset($sortedRulesForField['count']) || isset($sortedRulesForField['between'])
                || isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['digits'])
                || isset($sortedRulesForField['min_digits']) || isset($sortedRulesForField['max_digits'])
                || isset($sortedRulesForField['decmials'])
            ) {
                cli_err_syntax_without_exit("The `exact` Rule does not work with `count`, `decimals`, `size`, `between`, `digits`, `min_digits`, `max_digits`, `min`, or `max` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `exact` Rule is meant to be exact which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `count`, `decimals`, `size`, `between`, `digits`, `min_digits`, `max_digits`, `min`, `max` Rules to use the `exact` Rule - or vice versa!");
            }
            // $exactValue is NOT numeric but "number_types" data type is used, we error out
            if (
                !is_numeric($exactValue)
                && ($foundTypeCat === 'number_types')
            ) {
                cli_err_syntax_without_exit("Invalid `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Valid Single Number value for the `exact` Rule if you intend to use a Numeric Data Type or change Data Type to a String!");
            }
            // $exactValue is a string but data type is NOT string, we error out
            if (is_string($exactValue) && !isset($sortedRulesForField['string'])) {
                cli_err_syntax_without_exit("Invalid `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `string` Data Type Rule for `$currentDXKey` to use the `exact` rule with a string value!");
            }
            // $exactValue is a float value but data type is NOT float nor number, we error out
            if (is_float($exactValue) && !isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number'])) {
                cli_err_syntax_without_exit("Invalid `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `exact` rule with a decimal value!");
            }
        }

        // Special cases for the "regex" & "no_regex" Rules
        if (isset($sortedRulesForField['regex'])) {
            $regexValue = $sortedRulesForField['regex']['value'];
            if ($foundTypeCat !== 'number_types' && $foundTypeCat !== 'string_types') {
                cli_err_syntax_without_exit("The `regex` Rule cannot be used with Array-typed Data Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a \"stringifiable\" Data Type Rule (string, numeric, etc.) for `$currentDXKey` to use the `regex` rule!");
            }
        }
        if (isset($sortedRulesForField['no_regex'])) {
            $noRegexValue = $sortedRulesForField['no_regex']['value'];
            if ($foundTypeCat !== 'number_types' && $foundTypeCat !== 'string_types') {
                cli_err_syntax_without_exit("The `no_regex` Rule cannot be used with Array-typed Data Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a \"stringifiable\" Data Type Rule (string, numeric, etc.) for `$currentDXKey` to use the `no_regex` rule!");
            }
        }

        // Special cases for the "size" Rule
        if (isset($sortedRulesForField['size'])) {
            $sizeValue = $sortedRulesForField['size']['value'];
            if (!is_numeric($sizeValue)) {
                cli_err_syntax_without_exit("Invalid `size` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Number as the value for the `size` rule!");
            }
            // When size is float but data type is NOT float, we error out
            if (is_float($sizeValue) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `size` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `size` rule with a decimal value!");
            }
            // If size is negative when data type is a string type, we error out
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($sizeValue)
                && $sizeValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `size` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `size` rule when Data Type is a String or an Array!");
            }
        }

        // Special cases for the "min_digits" & "max_digits" Rules
        if (isset($sortedRulesForField['min_digits'])) {
            $minDigitsValue = $sortedRulesForField['min_digits']['value'];
            if (!is_int($minDigitsValue)) {
                cli_err_syntax_without_exit("Invalid `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `min_digits` rule!");
            }
            if ($minDigitsValue < 1) {
                cli_err_syntax_without_exit("Invalid `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Integer as the value for the `min_digits` rule!");
            }
        }
        if (isset($sortedRulesForField['max_digits'])) {
            $maxDigitsValue = $sortedRulesForField['max_digits']['value'];
            if (!is_int($maxDigitsValue)) {
                cli_err_syntax_without_exit("Invalid `max_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `max_digits` rule!");
            }
            if ($maxDigitsValue < 1) {
                cli_err_syntax_without_exit("Invalid `max_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Integer as the value for the `max_digits` rule!");
            }
        }
        if (isset($sortedRulesForField['min_digits']) && isset($sortedRulesForField['max_digits'])) {
            $minDigitsValue = $sortedRulesForField['min_digits']['value'];
            $maxDigitsValue = $sortedRulesForField['max_digits']['value'];
            // If min_digits is larger than max_digits, we error out
            if (is_numeric($minDigitsValue) && is_numeric($maxDigitsValue) && $minDigitsValue > $maxDigitsValue) {
                cli_err_syntax_without_exit("The `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the `max_digits` Rule Value!");
                cli_info("Specify the `min_digits` Rule Value as less than the `max_digits` Rule Value for `$currentDXKey`!");
            }
            if (is_numeric($minDigitsValue) && is_numeric($maxDigitsValue) && $minDigitsValue === $maxDigitsValue) {
                cli_warning_without_exit("The `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal to the `max_digits` Rule Value!");
                cli_info_without_exit("Recommended is to use `digits` but this will work without issues, might be confusing though!");
            }
            // When both are used we cannot use "digits" Rule since it is a special case
            if (isset($sortedRulesForField['digits'])) {
                cli_err_syntax_without_exit("The `min_digits` and `max_digits` Rules cannot be used with the `digits` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Remove the `digits` Rule to use the `min_digits` and `max_digits` Rules - or vice versa!");
            }
            // When between are used and one of its number values have fewer
            // or more digits than the `min_digits` and `max_digits` Rules, we error out
            if (isset($sortedRulesForField['between'])) {
                $betweenValue = $sortedRulesForField['between']['value'];
                if (
                    (is_int($betweenValue[0]) && strlen((string)$betweenValue[0]) !== $minDigitsValue)
                    || (is_int($betweenValue[1]) && strlen((string)$betweenValue[1]) !== $maxDigitsValue)
                ) {
                    cli_err_syntax_without_exit("The `min_digits` and `max_digits` Rule Values for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` do not match the `between` Rule Value!");
                    cli_info("Specify the `min_digits` and `max_digits` Rule Values as equal to the number of digits in the `between` Rule Value for `$currentDXKey`!");
                }
            }
        }

        // Special cases for the "digits" Rule
        if (isset($sortedRulesForField['digits'])) {
            $digitsValue = $sortedRulesForField['digits']['value'];
            if (!is_int($digitsValue)) {
                cli_err_syntax_without_exit("Invalid `digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `digits` rule!");
            }
            if ($digitsValue < 1) {
                cli_err_syntax_without_exit("Invalid `digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Integer as the value for the `digits` rule!");
            }
            if (isset($sortedRulesForField['min_digits']) || isset($sortedRulesForField['max_digits'])) {
                cli_err_syntax_without_exit("The `digits` Rule cannot be used with `min_digits` or `max_digits` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Remove `min_digits` or `max_digits` Rules to use the `digits` Rule - or vice versa!");
            }
            // if "min" or "max" is set, then its number of digits must be equal to the "digits" rule
            if (isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])) {
                $minValue = $sortedRulesForField['min']['value'] ?? null;
                $maxValue = $sortedRulesForField['max']['value'] ?? null;
                if (
                    (is_numeric($minValue) && strlen((string)$minValue) !== $digitsValue)
                    || (is_numeric($maxValue) && strlen((string)$maxValue) !== $digitsValue)
                ) {
                    cli_err_syntax_without_exit("The `digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` does not match the `min` and/or `max` Rule Value!");
                    cli_info("Specify the `digits` Rule Value as equal to the number of digits in the `min` and/or `max` Rule Value for `$currentDXKey`!");
                }
            }
        }

        // Special case for "color" Rule
        if (isset($sortedRulesForField['color'])) {
            $colorValue = $sortedRulesForField['color']['value'];
            if (!isset($sortedRulesForField['string'])) {
                cli_err_syntax_without_exit("The `color` Rule need the Data Type `string` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `string` Data Type Rule for `$currentDXKey` to use the `color` rule!");
            }
            if (!is_string($colorValue)) {
                cli_err_syntax_without_exit("Invalid `color` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single String as the value for the `color` rule!");
            }
            if (!preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $colorValue)) {
                cli_err_syntax_without_exit("Invalid `color` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Valid Six Character-based Hex Color String as the value for the `color` rule!");
            }
        }

        // Special case for "lowercase" & "uppercase" Rules
        if (isset($sortedRulesForField['lowercase']) || isset($sortedRulesForField['uppercase'])) {
            if ($foundTypeCat !== 'string_types') {
                cli_err_syntax_without_exit("The `lowercase` and/or `uppercase` Rule need a String-like  Data Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a String-like Data Type Rule for `$currentDXKey` to use the `lowercase` and/or `uppercase` rule!");
            }
            if (isset($sortedRulesForField['lowercase']) && isset($sortedRulesForField['uppercase'])) {
                cli_err_syntax_without_exit("Cannot combine the `lowercase` and `uppercase` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Remove one of the Rules to use the other for `$currentDXKey`!");
            }
        }

        // Special case for "array_keys" Rule (which
        // checks if the array has specific keys)
        if (isset($sortedRulesForField['array_keys'])) {
            $arrayKeysValue = $sortedRulesForField['array_keys']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_keys` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_keys` rule!");
            }
            if (!is_array($arrayKeysValue)) {
                cli_err_syntax_without_exit("Invalid `array_keys` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Strings as the value for the `array_keys` rule!");
            }
            foreach ($arrayKeysValue as $key) {
                if (!is_string($key) && !is_int($key)) {
                    cli_err_syntax_without_exit("Invalid `array_keys` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings and/or Integers as the value for the `array_keys` rule!");
                }
            }
        }

        // Special case for "array_values" Rule (which checks if the array
        // has specific values inside of it without caring about the keys)
        if (isset($sortedRulesForField['array_values'])) {
            $arrayValuesValue = $sortedRulesForField['array_values']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_values` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_values` rule!");
            }
            if (!is_array($arrayValuesValue)) {
                cli_err_syntax_without_exit("Invalid `array_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Primitive Values (strings, numbers, booleans, nulls, etc.) as the value for the `array_values` rule!");
            }
            foreach ($arrayValuesValue as $value) {
                if (!is_string($value) && !is_numeric($value) && !is_bool($value) && !is_null($value)) {
                    cli_err_syntax_without_exit("Invalid `array_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings, Numbers, Booleans and/or Nulls as the value for the `array_values` rule!");
                }
            }
            // "min" rule demanding more the set array values than actually specified
            if (isset($sortedRulesForField['min']) && is_int($sortedRulesForField['min']['value'])) {
                $minValue = $sortedRulesForField['min']['value'];
                if ($minValue > count($arrayValuesValue)) {
                    cli_err_syntax_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the Number of Values in the `array_values` Rule!");
                    cli_info("Specify the `min` Rule Value as less than or equal to the Number of Values in the `array_values` Rule for `$currentDXKey`!");
                }
            }
            // "max" rule is less than the set array values than actually specified
            if (isset($sortedRulesForField['max']) && is_int($sortedRulesForField['max']['value'])) {
                $maxValue = $sortedRulesForField['max']['value'];
                if ($maxValue < count($arrayValuesValue)) {
                    cli_err_syntax_without_exit("The `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Less than the Number of Values in the `array_values` Rule!");
                    cli_info("Specify the `max` Rule Value as greater than or equal to the Number of Values in the `array_values` Rule for `$currentDXKey`!");
                }
            }
        }

        // Special case for "array_keys_exact" Rule (which
        // checks if the array has specific keys and no more)
        if (isset($sortedRulesForField['array_keys_exact'])) {
            $arrayKeysExactValue = $sortedRulesForField['array_keys_exact']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_keys_exact` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_keys_exact` rule!");
            }
            if (!is_array($arrayKeysExactValue)) {
                cli_err_syntax_without_exit("Invalid `array_keys_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Strings as the value for the `array_keys_exact` rule!");
            }
            foreach ($arrayKeysExactValue as $key) {
                if (!is_string($key) && !is_int($key)) {
                    cli_err_syntax_without_exit("Invalid `array_keys_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings and/or Integers as the value for the `array_keys_exact` rule!");
                }
            }
            // "min", "max", "between", "exact", "size", rules are not allowed with "array_keys_exact"
            // since its exact number of elements with values implies its count automatically
            if (
                isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['between']) || isset($sortedRulesForField['exact'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['count'])
            ) {
                cli_err_syntax_without_exit("The `array_keys_exact` Rule cannot be used with `min`, `max`, `between`, `exact`, `size`, or `count` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `array_keys_exact` Rule is meant to be exact which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `min`, `max`, `between`, `exact`, `size`, or `count` Rules to use the `array_keys_exact` Rule!");
            }
        }

        // Special case for "array_values_exact" Rule (which checks if the array
        // has specific values inside of it without caring about the keys)
        if (isset($sortedRulesForField['array_values_exact'])) {
            $arrayValuesExactValue = $sortedRulesForField['array_values_exact']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_values_exact` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_values_exact` rule!");
            }
            if (!is_array($arrayValuesExactValue)) {
                cli_err_syntax_without_exit("Invalid `array_values_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Primitive Values (strings, numbers, booleans, nulls, etc.) as the value for the `array_values_exact` rule!");
            }
            foreach ($arrayValuesExactValue as $value) {
                if (!is_string($value) && !is_numeric($value) && !is_bool($value) && !is_null($value)) {
                    cli_err_syntax_without_exit("Invalid `array_values_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings, Numbers, Booleans and/or Nulls as the value for the `array_values_exact` rule!");
                }
            }
            // "min", "max", "between", "exact", "size", rules are not allowed with "array_values_exact"
            // since its exact number of elements with values implies its count automatically
            if (
                isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['between']) || isset($sortedRulesForField['exact'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['count'])
            ) {
                cli_err_syntax_without_exit("The `array_values_exact` Rule cannot be used with `min`, `max`, `between`, `exact`, `size`, or `count` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `array_values_exact` Rule is meant to be exact which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `min`, `max`, `between`, `exact`, `size`, or `count` Rules to use the `array_values_exact` Rule!");
            }
        }

        // Special case for "elements_this_type_order" Rule which must have a
        // "array" or "list" data type set first, and then it can be used
        if (isset($sortedRulesForField['elements_this_type_order'])) {
            // If data type is not "array" or "list", we error out
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `elements_this_type_order` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `elements_this_type_order` rule!");
            }
            // Convert to array if needed
            $values = is_string($sortedRulesForField['elements_this_type_order']['value']) ?
                [$sortedRulesForField['elements_this_type_order']['value']] :
                $sortedRulesForField['elements_this_type_order']['value'];

            // Iterate and make sure each element is of one of the allowed types
            $allowedTypes = [
                'array',
                'list',
                'checked',
                'unchecked',
                'char',
                'string',
                'number',
                'boolean',
                'null',
                'float',
                'integer'
            ];
            foreach ($values as $value) {
                if (!in_array($value, $allowedTypes, true)) {
                    cli_err_syntax_without_exit("Invalid `elements_this_type_order` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array of Allowed Types (array, list, checked, unchecked, char, string, number, boolean, null, float, integer) as the value for the `elements_this_type_order` rule!");
                }
            }
        }

        // Special case for "elements_any_of_these_values" Rule which checks
        // if a specific value is one of the valid values for the field
        if (isset($sortedRulesForField['any_of_these_values'])) {
            // First check that it has values in its value key and that it must be an array
            $values = $sortedRulesForField['any_of_these_values']['value'] ??  null;
            if (!is_array($values) || empty($values)) {
                cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Primitive Values (strings, numbers, booleans, or nulls) as the value for the `any_of_these_values` rule!");
            }
            // Then we check that matching values are used based on what main data type it is!
            // For example, if it is a "boolean", it should only have "true", "false", 0 or 1 as values
            if (isset($sortedRulesForField['boolean'])) {
                $validValues = [true, false, 0, 1];
                foreach ($values as $value) {
                    if (!in_array($value, $validValues, true)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array of Valid Boolean Values (true, false, 0, 1) as the value for the `any_of_these_values` rule!");
                    }
                }
            } elseif (isset($sortedRulesForField['null'])) {
                // If it is a "null" data type, it should only have "null" as value
                foreach ($values as $value) {
                    if (!is_null($value)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array with only `null` as the value for the `any_of_these_values` rule!");
                    }
                }
            } elseif (isset($sortedRulesForField['string'])) {
                // If it is a "string" data type, it should only have strings as values
                foreach ($values as $value) {
                    if (!is_string($value)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array of Strings as the value for the `any_of_these_values` rule!");
                    }
                }
            } elseif (isset($sortedRulesForField['number']) || isset($sortedRulesForField['float']) || isset($sortedRulesForField['integer'])) {
                // If it is a "number", "float", or "integer" data type, it should only have numbers as values
                foreach ($values as $value) {
                    if (!is_numeric($value)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array of Numbers (integers or floats) as the value for the `any_of_these_values` rule!");
                    }
                }
            }
        }

        // Special case for "elements_all_X" Rule which checks if all elements
        // in an array are of data type X, and if not, it errors out. This needs
        //  the "array" OR "list" data type to be set first though!
        $elementsAllRules = [
            'elements_all_arrays',
            'elements_all_checked',
            'elements_all_unchecked',
            'elements_all_lists',
            'elements_all_chars',
            'elements_all_strings',
            'elements_all_numbers',
            'elements_all_booleans',
            'elements_all_nulls',
            'elements_all_floats',
            'elements_all_integers',

        ];
        foreach ($elementsAllRules as $ruleName) {
            if (isset($sortedRulesForField[$ruleName])) {
                if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                    cli_err_syntax_without_exit(
                        "The `{$ruleName}` Rule needs the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!"
                    );
                    cli_info(
                        "Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `{$ruleName}` rule!"
                    );
                }
            }
        }

        /*
            MANY SPECIAL CASES ARE CHECKED HERE - END:
        */

        // Finally add the priority sorted rules to the converted validation array
        $convertedValidationArray[$currentDXKey]["<RULES>"] = $sortedRulesForField;

        // We check if the key contains a "." and if it does we need to split it
        // and then we need to rebuild the nested keys in the converted validation array
        // and then we need to set the value to the $sortedRulesForField array.
        if (
            str_contains($currentDXKey, ".")
            && preg_match("/^((\*|([a-z_]*)([a-z_0-9]+))\.(\*|[a-z_][a-z_0-9]+))(\.(\*|[a-z_][a-z_0-9]+))*$/", $currentDXKey)
        ) {
            $nestedKeys = explode(".", $currentDXKey);
            $nestedKeyCount = count($nestedKeys);
            $currentNestedKey = array_shift($nestedKeys);
            $currentNestedArray = &$convertedValidationArray[$currentNestedKey];

            // We need to check if the current nested key already exists
            // and if it does not exist we need to create it as an empty array
            if (!isset($currentNestedArray) || !is_array($currentNestedArray)) {
                $currentNestedArray = [];
            }

            // We now iterate through the remaining nested keys and create them
            // as empty arrays until we reach the last key which will be the value
            foreach ($nestedKeys as $key) {
                if (!isset($currentNestedArray[$key]) || !is_array($currentNestedArray[$key])) {
                    $currentNestedArray[$key] = [];
                }
                $currentNestedArray = &$currentNestedArray[$key];
            }
            // Finally set the value to the sorted rules for the field
            $currentNestedArray["<RULES>"] = $sortedRulesForField;
            unset($convertedValidationArray[$currentDXKey]);
        }
    }

    // We now grab all the array keys from the converted validation array
    // to validate for each key that contains a "*" also has a key that begins
    // with "key.*" before "key.*.subkey" and so on. The "key.*" is what would
    // hod the rules for that numbered array like count, min, max, etc. (what is
    // applicable to arrays). We error out when for example "key.*.subkey" is used
    // but the "key.*" is not used as a key in the validation array.
    $arrayKeys = array_keys($validationArray);

    // But first we loop through to find if ANY key contains "*.*" implying
    // a multi-dimensional array which is NOT supported yet so this will error out
    // and say it has not been implemented yet.
    foreach ($arrayKeys as $currentKey) {
        // If the key does not contain "*.*" we skip it
        if (!str_contains($currentKey, "*.*")) {
            continue;
        }
        // If the key contains "*.*" we error out and say it is not supported yet
        cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` contains a Multi-Dimensional Array which is not supported yet in FunkPHP!");
        cli_info_without_exit("Specify Single-Dimensional Arrays with keys containing only one `*` per level.");
        cli_info("For example: `key.*` or `key.*.subkey` but not `key.*.*.subkey`!");
    }
    foreach ($arrayKeys as $currentKey) {
        // Key doesn't contain "*", so we skip it
        if (!str_contains($currentKey, "*")) {
            continue;
        }
        // When we find a key that contains "*", but does not end with "*",
        // we split on the last ".", and check if the first part exists
        // anywhere in the validation array keys. Error out if it does not exist.
        if (str_contains($currentKey, "*") && !str_ends_with($currentKey, "*")) {
            $lastSplit = strrpos($currentKey, ".");
            $firstPart = substr($currentKey, 0, $lastSplit);
            if (!in_array($firstPart, $arrayKeys)) {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` requires the Key `$firstPart` to exist in the Validation Array!");
                cli_info("Add the Key `$firstPart` to the Validation Array to use the Key `$currentKey`!");
            }
        }
        // When a key ends with "*", we check that it actually has any subkeys
        // by looping through all keys to see if they start with the current key
        if (str_contains($currentKey, "*") && str_ends_with($currentKey, "*")) {
            $hasSubkeys = false;
            foreach ($arrayKeys as $key) {
                if (str_starts_with($key, $currentKey) && $key !== $currentKey) {
                    $hasSubkeys = true;
                    break;
                }
            }
            if (!$hasSubkeys) {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` requires at least one Subkey to exist!");
                cli_info("Add a Subkey (as in `$currentKey.SubKey`) to the \$DX Array that starts with `$currentKey` to use it!");
            }
        }
    }

    // Special case: if we have "*" as a key, we need to check that
    // all other keys also start with "*." because now we are saying that
    // the entire thing begins as a numbered array!
    if (array_key_exists("*", $convertedValidationArray)) {
        // If we have "*" as a key, we need to check that all other keys
        // start with "*." because now we are saying that the entire thing
        // begins as a numbered array!
        foreach ($arrayKeys as $currentKey) {
            if (!str_starts_with($currentKey, "*.") && $currentKey !== "*") {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` must start with `*.` when `*` is used as a root key!");
                cli_info("Change the Key `$currentKey` to start with `*.$currentKey` to use it!");
            }
        }
    }

    // We loop through the array keys again to check if any key
    // ends with ".*" and then we check if any other key ends
    // without ".*" but starts with the same key. This means
    // an associative key is competing with a numbered array key
    foreach ($arrayKeys as $currentKey) {
        // If the key does not end with ".*", we skip it
        if (!str_ends_with($currentKey, ".*")) {
            continue;
        }
        // If the key ends with ".*", we check if any other key
        // starts with the same key but does not end with ".*"
        $baseKey = substr($currentKey, 0, -2); // Remove ".*" from the end
        foreach ($arrayKeys as $otherKey) {
            if ($otherKey === $baseKey) {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` (indicating a numbered array) in Validation `$handlerFile.php=>$fnName` conflicts with the Key `$otherKey` which is on the same key level!");
                cli_err_syntax_without_exit("It is invalid JSON to have both a Numbered Array[0-9] and an ['Associative_Key'] at the same Key Level!");
                cli_info_without_exit("Associative Key `['$otherKey'] = {values}` will conflict with Numbered Key(s) `['$baseKey'][0-9] = {values}`!");
                cli_info("Change the Key `$currentKey` to not end with `.*` (thus removing its numbered array function) or change the Key `$otherKey`!");
            }
        }
    }

    // Return the finally finalized converted validation array!
    return $convertedValidationArray;
}

// Simple Helper function that validates that a $binding (i, d, s or b)
// is correct based on provided $value. Returns true or false. Value
// "?" is always true, meaning it is a placeholder for any value!
function cli_validate_correct_binding_with_provided_value($binding, $value)
{
    // Both variables must be non empty strings! However, we do cast $value to string
    // to ensure that it is a string, even if it is a number or boolean. Since it is
    // also meant to validate whole numbers such as (i) and decimals/floats such as (d)!
    if (!is_string_and_not_empty($binding) || !is_string_and_not_empty((string)$value)) {
        cli_err("[cli_validate_correct_binding_with_provided_value]: Expects Non-Empty Strings as input for `\$binding` and `\$value`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not a String at all?");
    }
    // $binding must be one of the following: i, d, s, b
    // i = integer, d = double/float, s = string, b = blob
    // '?' is placeholder which means it is always true!
    if (!in_array($binding, ['i', 'd', 's', 'b',], true)) {
        cli_err("[cli_validate_correct_binding_with_provided_value]: Expects a valid binding type as input for `\$binding`! (i, d, s or b)");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not a String at all?");
    }
    // If value is "?" then it is always true
    if ($value === '?') return true;

    // Validate binding types: String
    if ($binding === 's') {
        if (is_string($value)) {
            return true;
        }
        return false;
    }
    // Validate binding types: Integers
    elseif ($binding === "i") {
        if (preg_match('/^-?\d+$/', $value)) {
            return true;
        }
        return false;
    }
    // Validate binding types: Doubles/Floats
    elseif ($binding === "d") {
        if (preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return true;
        }
        return false;
    }
    // Validate binding types: Blobs
    elseif ($binding === 'b') {
        if (is_string($value) && !empty($value)) {
            return true;
        }
        return false;
    }

    cli_err_without_exit("[cli_validate_correct_binding_with_provided_value]: Despite impossible, reached the end of Function Call without return value?!");
    cli_info("Check All References to this Function Call and ensure that the Binding Type is Correct!");
    return false;
}

// Simple Helper Function that can check if a `table:col` exists in the `tables.php` file
// and if it does it returns the binding type for that specific column. If it gets a string
// without ":" then it assumes it is a unique col based that can be found within $allTbs
// as long as that Table also exists in $tbs which is the currently tables being used.
function cli_find_valid_tb_col_and_binding_or_return_null($tbs, $tbColToCheck)
{
    if (!is_string_and_not_empty($tbColToCheck)) {
        cli_err_without_exit("[cli_find_valid_tb_col_and_binding_or_return_null]: Expects a Non-Empty String as input for `\$tbColToCheck`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not a String at all?");
    }
    if (!is_array_and_not_empty($tbs) || !array_is_list($tbs)) {
        cli_err_without_exit("[cli_find_valid_tb_col_and_binding_or_return_null]: Expects a Non-Empty List Array as input for `\$tbs`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    global $tablesAndRelationshipsFile;
    $allTbs = $tablesAndRelationshipsFile['tables'] ?? [];
    $binding = null;
    $found = false;
    $foundTable = null;
    $foundCol = null;

    // If it contains ":", we assume it is a table:col format
    if (str_contains($tbColToCheck, ":")) {
        [$tableName, $colName] = explode(":", $tbColToCheck, 2);
        if (!in_array($tableName, $tbs) || !array_key_exists($tableName, $allTbs)) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => null, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        if (!array_key_exists($colName, $allTbs[$tableName])) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => null, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        $foundCol = $colName;
        return [
            "found" => true,
            "binding" => $allTbs[$tableName][$colName]['binding'] ?? null,
            "found_table" => $tableName,
            "found_col" => $foundCol
        ];
    }
    // If it does not contain ":", we assume it is a unique column name
    // because that should be checked before this function is called
    else if (!str_contains($tbColToCheck, ":")) {
        // Iterate through all tables to find the column
        foreach ($allTbs as $tb => $colKey) {
            if (!in_array($tb, $tbs, true)) {
                continue;
            }
            foreach ($colKey as $k => $col) {
                if ($tbColToCheck === $k) {
                    $foundTable = $tb;
                    break 2; // Break out of both foreach loops
                }
            }
        }
        if (!$foundTable) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => null, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        // We found the table and column, now return the binding
        $binding = $allTbs[$foundTable][$tbColToCheck]['binding'] ?? null;
        if (!$binding) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => $foundTable, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        $found = true;
        $foundCol = $tbColToCheck;
        return ["checked_col" => strtolower($tbColToCheck), "found" => $found, "binding" => $binding, "found_table" => $foundTable, "found_col" => $foundCol, "tbs_checked" => array_map('strtolower', $tbs)];
    }
    cli_err_without_exit("[cli_find_valid_tb_col_and_binding_or_return_null]: Reached the end of Function Call without return value?!");
    cli_info("Check All References to this Function Call and ensure that the `Col`|`Table:Col` Argument is Correct!");
}

// Function that parses the condition clauses such as WHERE
// a Simple SQL Query to an Optimized SQL Array! The
// &$builtBindedParamsString is to add the necessary
// "?" placeholders based on how many are used within
// the parsed Where clause that would be returned!
// IMPORTANT: Changed "$whereHaving" argument from "=null" in argument list below
// to none at all since PHP parser seems to complain.
function cli_parse_condition_clause_sql($tbs, $where, $queryType, $sqlArray, $validCols, &$builtBindedParamsString, &$builtFieldsArray, &$allAliases, $whereOrHaving, &$aggAliases,  &$aliasesTbCol = null)
{
    // Prepare variables and also validate the input
    // $where = The actual CONDITION String to parse.
    // Since this function was first only for WHERE Conditions
    // but it is now more generalized to handle any condition clause!
    global $tablesAndRelationshipsFile, $mysqlOperatorSyntax;
    $parsedCondition = "";
    $allTbs = $tablesAndRelationshipsFile['tables'] ?? [];
    $singleTable = count($tbs) === 1;
    $uniqueCols = null;
    $tbsWithCols  = null;
    $relations = $tablesAndRelationshipsFile['relationships'] ?? [];

    // Keep track of even amount of left and right parentheses
    // to ensure they are balanced in the WHERE clause!
    $leftParenthesisCount = 0;
    $rightParenthesisCount = 0;

    // Special Starting Parts For Each $wPart when NOT ColName OR Table:ColName
    $specialSyntaxStart = [
        'AND=',
        'AND(=',
        'OR=',
        'OR(=',
        'NOT=',
        'NOT(=',
        'EXISTS=',
        'EXISTS(=',
        'IN=',
        'IN(=',
        'BETWEEN=',
        'BETWEEN(=',
    ];

    // Starting Parts for Aggregate Functions - but Agg Funcs are NOT allowed in WHERE clause!
    // so we use this to check and then error out if they are used in the WHERE clause!
    $aggregateFunctionsStart = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()/i";

    // $tbs must be an array and not empty
    if (!is_array_and_not_empty($tbs)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty Associative Array as input for `\$tables`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $relations must be an array but can be empty
    if (!is_array($relations)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects an Associative Array as input for `\$relations`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $where must be a string and not empty
    if (!is_string_and_not_empty($where)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty String as input for `\$where`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $queryType must be a string and not empty and be one of the allowed query types
    $allowedQueryTypes = [
        'SELECT_DISTINCT',
        'SELECT_INTO',
        'SELECT',
        'INSERT',
        'UPDATE',
        'DELETE'
    ];
    if (!is_string_and_not_empty($queryType) || !in_array($queryType, $allowedQueryTypes, true)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty String as input for `\$queryType` that is one of: " . implode(", ", $allowedQueryTypes) . "!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $sqlArray must be an array and not empty and not a list!
    if (!is_array_and_not_empty($sqlArray) || array_is_list($sqlArray)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty Associative Array as input for `\$sqlArray`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $validCols must be an array and not empty and not a list!
    // It must also contain the array keys "uniqueCols" and "table:col"
    if (!is_array_and_not_empty($validCols) || array_is_list($validCols) || !isset($validCols['uniqueCols']) || !isset($validCols['table:col'])) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty Associative Array as input for `\$validCols` that contains the keys: \"uniqueCols\" and \"table:col\"!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    $uniqueCols = $validCols['uniqueCols'];
    $tbsWithCols  = $validCols['table:col'];

    // $builtBindedParamsString must be a string but can be empty
    if (!is_string($builtBindedParamsString)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a String as input for `\$builtBindedParamsString`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $builtFieldsArray must be an array that is a list but can be empty
    if (!is_array($builtFieldsArray) || !array_is_list($builtFieldsArray)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a List Array as input for `\$builtFieldsArray`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // $whereOrHaving must be a string and not empty (either "WHERE" or "HAVING")
    if ($whereOrHaving === null) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects `WHERE` or `HAVING` for `\$whereOrHaving`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (!is_string_and_not_empty($whereOrHaving)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty String as input for `\$whereOrHaving` that is either `WHERE` or `HAVING`!");
    }
    if ($whereOrHaving !== 'WHERE' && $whereOrHaving !== 'HAVING') {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects `WHERE` or `HAVING` for `\$whereOrHaving` but got: \"$whereOrHaving\"!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // We split the $where on "|" string by spaces to get each part or turn the single string into an array
    $where = str_contains(trim($where), "|") ? explode("|", $where) : [$where];
    $wPartRegex = '/^(\[[A-Za-z_\-0-9]+]|COUNT\(DISTINCT [a-zA-Z0-9\-_:=]+\)|[()=A-Za-z_\-0-9:\*]+)\s+(\[[A-Za-z_\-0-9]+]|[+\-*\/%=&|^!<>]+|ALL|AND|BETWEEN|EXISTS|IN|LIKE|NOT|SOME)\s+(.*)$/i';

    // Regexes for Aggregate Functions. First to see if it starts with any of the aggregate functions
    // and the second to see if it is a complete aggregate function with a table and/or column name
    // and the special case of COUNT(*) which is a special case of the COUNT function!
    $aggregateFunctionsStart = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()/i";
    $aggFuncRegex = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()([a-zA-Z0-9_:\*]+)*\)$/i";
    $aggFuncValidStarts = [
        'count(distinct=' => 'count_distinct_',
        'count(distinct ' => 'count_distinct_',
        'count(' => 'count_',
        'count(*)' => 'count_all_',
        'sum(' => 'sum_',
        'avg(' => 'avg_',
        'min(' => 'min_',
        'max(' => 'max_',
    ];
    $aggFuncAliasValidStartRegex = "/^(avg_|sum_|min_|max_|count_|count_all|count_distinct_)([a-zA-Z0-9_]+)$/i";
    // Commands that You might wanna SELECT but are not allowed to do
    // through FunkPHP so you get an error message about it!
    $disallowedCommands = [
        "BIN(",
        "BINARY",
        "CASE",
        "CAST",
        "COALESCE(",
        "CONVERT(",
        "CONV(",
        "CONNECTION_ID()",
        "CURRENT_USER",
        "DATABASE()",
        "IF(",
        "IFNULL(",
        "ISNULL(",
        "LAST_INSERT_ID()",
        "SESSION_USER()",
        "SYSTEM_USER()",
        "USER()",
        "VERSION()",
    ];

    // SPECIAL REGEXES that always are ran before the main $wPartRegex!
    // TODO: Might get implemented or not at a later stage!
    $tableCol_BETWEEN_VAL1_AND_VAL2 = "/^([a-zA-Z0-9_:])+\s*(BETWEEN)\s*([a-zA-Z0-9]+)\s*(AND)\s*([a-zA-Z0-9]+)$/i";
    $tableCol_NOT_BETWEEN_VAL1_AND_VAL2 = "/^([a-zA-Z0-9_:])+\s*(NOT BETWEEN)\s*([a-zA-Z0-9]+)\s*(AND)\s*([a-zA-Z0-9]+)$/i";
    $tableCol_IN_VAL_ARRAY = "/^([a-zA-Z0-9_:]+)\s*(NOT IN)+\s*\((.*)\)$/i";
    $tableCol_NOT_IN_VAL_ARRAY = "/^([a-zA-Z0-9_:]+)\s*(NOT IN)+\s*\((.*)\)$/i";
    $tableCol_NOT_LIKE_VAL = "/^([a-zA-Z0-9_:]+)\s*(NOT LIKE)+\s*(.*)$/i";
    $NOT_tableCol_OPERATOR_VAL = "/^(NOT)\s*([a-zA-Z0-9_:]+)\s*([+\-*\/%=&|^!<>])+\s*(.*)$/i";


    // We now iterate through each part and we use regex to parse the Condition clause where it should
    // begin with a column name/tableName:columnName, followed by an operator, and then a value.
    foreach ($where as $index => $wPart) {
        // SPECIAL CASE: Escaping any parsing using {} meaning it will just be added as is
        // This is useful when current Parsing Logic is not supported yet but the Developer
        // knows what they are doing and just wanna add their Condition Clause Logic without
        // having to wait for a new FunkPHP Version to be released! ;-P
        if (str_starts_or_ends_not_with($wPart, "{", "}")) {
            cli_err_syntax_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Starting/Ending { or } but not the other way around!");
            cli_info_without_exit("You need BOTH to Start and End with { respectively } to Escape the Condition Clause Logic!");
            cli_info("When you use { and } you are telling FunkPHP to just add the Condition Clause Logic \"As Is\" WITHOUT any parsing! However, this does NOT allow You to DROP/ALTER/TRUNCATE Tables and/or entire Databases! (for obvious reasons)");
        }
        if (str_starts_ends_with($wPart, "{", "}")) {
            if (str_contains(strtoupper($wPart), "DROP TABLE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `DROP TABLE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            if (str_contains(strtoupper($wPart), "DROP DATABASE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `DROP DATABASE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            if (str_contains(strtoupper($wPart), "ALTER TABLE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `ALTER TABLE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            if (str_contains(strtoupper($wPart), "TRUNCATE TABLE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `TRUNCATE TABLE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            // Remove the {}, add it "as is" to the parsed
            // condition inform the Developer and continue!
            $wPart = substr(trim($wPart), 1, -1);
            $parsedCondition .= " " . trim($wPart);
            cli_warning_without_exit("[cli_parse_condition_clause_sql]: Added \"As Is\" Escaped Condition Clause Logic: `$wPart` in Query Type: \"$queryType\". Hopefully it still works when sent as Prepared Statement!");
            cli_info_without_exit("IMPORTANT: If you are using placeholders `?` in the `$wPart` you must add them to the `bparam` Key in the SQL Array and also add the `<MATCHED_FIELDS>` keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
            continue;
        }
        if (preg_match($aggregateFunctionsStart, $wPart) && $whereOrHaving === 'WHERE') {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to using an Aggregate Function in the WHERE clause!");
            cli_info_without_exit("Aggregate Functions like COUNT(), SUM(), AVG(), MIN(), MAX() are NOT allowed in the Condition Clause for `WHERE` Key, only for `HAVING` Key!");
            cli_info("If you want to use Aggregate Functions, use them in the SELECT and/or GROUP BY Clauses instead!");
        }
        // Condition Clause can't start with a special syntax start
        if ($index === 0) {
            if (str_starts_with($wPart, "(") && str_ends_with($wPart, ")")) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to starting with a parenthesis '(' or ')'!");
                cli_info_without_exit("Starting a Condition Clause using () to indicate a Tuple or Row Constructor is not supported as of yet in FunkPHP!");
                cli_info_without_exit("If you wanna use a [SubQuery] means you should start with `[` and end with `]`. This allows you to use Tuples, Row Constructors and such.");
                cli_info("IMPORTANT: Using [SubQuery] means you lose the Validation Parsing Logic and you must add the `?` Placeholders  in the `bparam` Key and the <MATCHED_FIELDS> keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
            }
            foreach ($specialSyntaxStart as $specialStart) {
                if (str_starts_with($wPart, $specialStart)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to starting with a special syntax start: \"$specialStart\"!");
                    cli_info("The first part of the Condition clause cannot start with a special syntax start like:\n" . implode(",\n", quotify_elements($specialSyntaxStart)) . "! If you wanna use a [SubQuery] you should start with `[` and end with `]`!");
                }
            }
        }

        // If it is ONLY  ")", we just add it and
        // increase the count of left or right parentheses
        if ($wPart === ')') {
            $rightParenthesisCount++;
            $parsedCondition .= $wPart;
            continue;
        }

        // $wPart might be an entire [SubQuery] so we check if it starts with "[" and ends with "]".
        // It does NOT apply when Query Type is INSERT since Subqueries are not allowed there!
        if (preg_match('/^\[[A-Za-z_\-0-9]+\]$/', $wPart)) {
            if ($queryType === 'INSERT') {
                cli_err_syntax_without_exit("[cli_parse_condition_clause_sql]: Subqueries are NOT allowed for Query Type `$queryType`!");
                cli_err_syntax("Subquery Syntax is ONLY used for UPDATE, DELETE, SELECT or SELECT_DISTINCT Queries!");
            }
            if (!isset($validCols['subqueries'][$wPart])) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$wPart` not being found in the SubQueries Array!");
            }
            $parsedCondition .= $wPart . " ";
            cli_info_without_exit("[cli_parse_condition_clause_sql]: Found SINGLE AND ONLY A Subquery Syntax: \"$wPart\" in Query Type: \"$queryType\". This is handled outside of this Parsing Process. Continuing...");
            continue;
        }

        // Now we finally match the $wPart against the regex to parse it
        $wMatch = preg_match($wPartRegex, trim($wPart), $wMatches);
        if (!$wMatch) {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to no match at all!");
            cli_info("This might be due to a missing/invalid operator. Valid Operators:\n" . implode(",\n", quotify_elements($mysqlOperatorSyntax['all'])) . "!");
        }
        if ($wMatches[1] === null || $wMatches[2] === null || $wMatches[3] === null) {
            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to one or more parts being null (Table with Column Name or Table Column Name, Operator, and/or Value)!");
        }
        $mCol = $wMatches[1] ?? null;
        $mOperator = $wMatches[2] ?? null;
        $mValue = $wMatches[3] ?? null;

        // None of the matched parts in the Condition Clause can be Aggregate Function
        // for "WHERE" Key since Aggregate Functions are not allowed in the WHERE clause!
        if ($whereOrHaving === 'WHERE') {
            if (
                (preg_match($aggregateFunctionsStart, $mCol)
                    || preg_match($aggregateFunctionsStart, $mOperator)
                    || preg_match($aggregateFunctionsStart, $mValue))
            ) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to using an Aggregate Function in the WHERE clause!");
                cli_info_without_exit("Aggregate Functions like COUNT(), SUM(), AVG(), MIN(), MAX() are NOT allowed in the Condition clause!");
                cli_info("If you want to use Aggregate Functions, use them in the SELECT and/or GROUP BY Clauses instead!");
            }
        }

        // Check $mCol begins with any of the special syntax starts. If so we
        // extract that and the $mCol separated by the special syntax start.
        $specialSyntax = "";
        foreach ($specialSyntaxStart as $specialStart) {
            if (str_starts_with($mCol, $specialStart)) {
                [$specialSyntax, $mCol] = explode("=", $mCol, 2);
                if (str_contains($specialSyntax, "(")) {
                    $leftParenthesisCount++;
                }
                break;
            }
        }

        // SPECIAL CASE: 'HAVING' Key called this Condition Clause Parser, so we
        // will check if it is like when being used in `SELECT Key with Agg Funcs
        // and all that!
        if ($whereOrHaving === 'HAVING') {
            if (preg_match($aggregateFunctionsStart, $mCol)) {
                if (preg_match($aggFuncRegex, $mCol, $aggMatches)) {
                    // There are 4 different cases now to handle in following order to add the parsed Condition Clause String:
                    // 1. Aggregate Function with Table and/or Column Name (table:col)
                    // 2. Aggregate Function with just table_col Alias Name
                    // 3. Aggregate Function with just agg_func Alias Name (agg_func_table_col unless count(*), then count_table)
                    // 4. Aggregate Function with just Column Name (col)
                    $aggName = strtolower($aggMatches[1]) ?? null;
                    $aggTbAndOrCol = strtolower($aggMatches[2]) ?? null;
                    $aggAliasOrTbDotCol = true; // True = it is a Agg Func Alias Name, False = it is Table:Col after Col => Table:Col or already Table:Col

                    // If the Aggregate Function is not in the valid starts
                    // array then it is not a valid Aggregate Function!
                    if (!isset($aggFuncValidStarts[$aggName])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Aggregate Function: \"$mCol\" in Query Type: \"$queryType\" due to not being a valid Aggregate Function!");
                        cli_info("Valid Aggregate Functions start with:\n" . strtoupper(implode(",\n", quotify_elements(array_keys($aggFuncValidStarts)))) . "!");
                    }
                    // If it contains ":" we assume "table:col" so we just replace ":" with "_"
                    // and then check if it is a valid table:col or alias name!
                    if (str_contains($aggTbAndOrCol, ":")) {
                        if (!in_array($aggTbAndOrCol, $validCols['table:col'], true)) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table:Col Name: `$aggTbAndOrCol` in the Table and Column Array!");
                            cli_info("Valid Table and Column Array is:\n" . implode(",\n", quotify_elements($validCols['table:col'])) . "!");
                        }
                        $aggTbAndOrCol = str_replace(":", "_", $aggTbAndOrCol);
                        if (!isset($aliasesTbCol)) {
                            $aliasesTbCol[$aggTbAndOrCol] = [
                                'tb' => explode("_", $aggTbAndOrCol)[0] ?? "<UNKNOWN_TABLE>",
                                'col' => explode("_", $aggTbAndOrCol)[1] ?? "<UNKNOWN_COLUMN>",
                            ];
                        }
                        $aggAliasOrTbDotCol = false;
                    }
                    // If it starts with any of the valid starts "avg_,"sum_", "min_", "max_", etc.
                    elseif (preg_match($aggFuncAliasValidStartRegex, $aggTbAndOrCol)) {
                        // No need to do anything here, we check valid Aggregate Function Alias Names later down!
                    }
                    // Else we assume it is a column
                    else {
                        if (isset($aggFuncAliasValidStarts)) {
                        } elseif (!in_array($aggTbAndOrCol, $validCols['uniqueCols'], true)) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Column Name: `$aggTbAndOrCol` in the Unique Columns Array!");
                            cli_info("Valid Unique Columns Array is:\n" . implode(",\n", quotify_elements($validCols['uniqueCols'])) . "! (`$aggTbAndOrCol` not being here means it could be ambigious to match Column to Table)");
                        }
                        $findTbToCol = cli_find_valid_tb_col_and_binding_or_return_null($tbs, $aggTbAndOrCol);
                        if (!$findTbToCol['found']) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table|Table_Col|Alias Name: `$aggTbAndOrCol` in the Aliases Table & Column Array!");
                            cli_info("Valid Aliases Array is:\n" . implode(",\n", quotify_elements(array_keys($aliasesTbCol))) . "!");
                        } else {
                            $aggTbAndOrCol = $findTbToCol['found_table'] . "_" . $findTbToCol['found_col'];
                            if (!isset($aliasesTbCol)) {
                                $aliasesTbCol[$aggTbAndOrCol] = [
                                    'tb' => $findTbToCol['found_table'],
                                    'col' => $findTbToCol['found_col'],
                                ];
                            }
                            $aggAliasOrTbDotCol = false;
                        }
                    }
                    // Validate its Table and Column exist in the global Aliases Table and Column Array
                    // (it includes both regular aliases and aggregate function aliases!)
                    if (!isset($aliasesTbCol[$aggTbAndOrCol])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table|Table_Col|Alias Name: `$aggTbAndOrCol` in the Aliases Table & Column Array!");
                        cli_info("Valid Aliases Array ais:\n" . implode(",\n", quotify_elements(array_keys($aliasesTbCol))) . "!");
                    }
                    // Aggregate Function is SUM or AVG meaning we need to check that $mValue is numerical OR ?
                    if (str_starts_with($aggName, "avg(") || str_starts_with($aggName, "sum(")) {
                        if (!is_numeric($mValue) && $mValue !== '?') {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" expecting a Numerical Value or '?' but got: \"$mValue\" for `$aggTbAndOrCol`!");
                            cli_info("Either Change the Value to a Numerical Value or '?' or change the Aggregate Function to something else that does not require a Numerical Value!");
                        }
                    }
                    // Validate that $mOperator is a valid operator
                    if (!in_array($mOperator, $mysqlOperatorSyntax['all_except_worded'])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" expecting a valid Operator but got: \"$mOperator\"!");
                        cli_info("Valid Operators are:\n" . implode(",\n", quotify_elements($mysqlOperatorSyntax['all'])) . "! (excluding the Worded Operators in the HAVING Key case!)");
                    }
                    $binding = cli_find_valid_tb_col_and_binding_or_return_null($tbs, ($aliasesTbCol[$aggTbAndOrCol]['tb'] ?? "<UNKNOWN_TABLE>") . ":" . ($aliasesTbCol[$aggTbAndOrCol]['col'] ?? "<UNKNOWN_COLUMN>"));

                    if (!$binding['found']) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table|Table_Col|Alias Name: `$aggTbAndOrCol` in the Aliases Table & Column Array!");
                        cli_info("Valid Aliases Array is:\n" . implode(",\n", quotify_elements(array_keys($aliasesTbCol))) . "!");
                    }
                    // No binding found when it should though
                    if (!isset($binding['binding'])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a Valid Binding Type for Value: `$mValue` (binding type is missing/null)!");
                        cli_info("Valid Binding Types are:\n" . implode(",\n", quotify_elements(['i', 'd', 's', 'b'])) . "!");
                    }

                    // If TableCol's binding is numeric and its value is then we know it is valid binding type with value!
                    if (($binding['binding'] === 'i' || $binding['binding'] === 'd') && is_numeric($mValue)) {
                        // Empty so "else" does not execeute!
                    }
                    // Otherwise we check! (always returns true when $mValue is '?')
                    else {
                        $validValueBinding = cli_validate_correct_binding_with_provided_value($binding['binding'], $mValue);
                        if (!$validValueBinding) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a Valid Binding Type for Value: `$mValue`!");
                            cli_info("`$mValue` might be Blob or String when it should be Numeric or vice versa. Valid Binding Types are:\n" . implode(",\n", quotify_elements(['i', 'd', 's', 'b'])) . "!");
                        }
                    }
                    // Finally we can add to the parsed condition for HAVING Key!
                    // and additional ? placeholder to `fields` if applying!
                    if (!empty($specialSyntax)) {
                        if ($parsedCondition !== ' ' && str_contains($specialSyntax, "(")) {
                            $parsedCondition .= " ";
                        }
                        $parsedCondition .= " " . $specialSyntax;
                    }
                    $aggName = strtoupper($aggName);

                    // True = it is a Agg Func Alias Name, False = it is Table:Col after Col => Table:Col or already Table:Col
                    if (!$aggAliasOrTbDotCol) {
                        // We preg_replace ONLY the first "_" with "." to make it table.col
                        $aggTbAndOrCol = preg_replace('/_/', '.', $aggTbAndOrCol, 1);
                    }

                    // Add parsed condition for HAVING Key
                    $parsedCondition .= " $aggName$aggTbAndOrCol) $mOperator $mValue ";

                    $mCol = $binding['found_col'] ?? null;
                    $singleTb = $binding['found_table'] ?? null;
                    if (!isset($singleTb) || !isset($mCol)) {
                        cli_err("[cli_parse_condition_clause_sql - on HAVING Key]: Huh? Added Parsed HAVING Condition but could then not add to `fields` Key in return() Array?! Due to `cli_find_valid_tb_col_and_binding_or_return_null` returning null for Table or Column Name!?");
                    }

                    // Add to `builtFieldsArray` if it is not already there when ? Placeholder since that means
                    // we need to grab that value from some validated input field (as defined in `fields` Key!)
                    $singleTb = $singleTb . "_" . $aggFuncValidStarts[strtolower($aggName)];
                    // Remove trailing "_" if it exists in $singleTb now!
                    if (str_ends_with($singleTb, "_")) {
                        $singleTb = substr($singleTb, 0, -1);
                    }
                    if ($mValue === '?') {
                        if (isset($builtFieldsArray)) {
                            if (isset($validCols["matchedFields"][$mCol])) {
                                if (is_string($validCols["matchedFields"][$mCol]) && !empty($validCols["matchedFields"][$mCol])) {
                                    $mCol = $validCols["matchedFields"][$mCol];
                                }
                            }

                            if (!in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                                $builtFieldsArray[] = $singleTb . "_" . $mCol;
                            } elseif (in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                                // We iterate adding +1 to the name until it is unique
                                $i = 1;
                                while (in_array($singleTb . "_" . $mCol . "_$i", $builtFieldsArray)) {
                                    $i++;
                                }
                                $builtFieldsArray[] = $singleTb . "_" . $mCol . "_$i";
                            }
                        }
                    }

                    // Continue to next array element of the Condition Clause
                    continue;
                } else {
                    cli_err("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Aggregate Function: \"$mCol\" in Query Type: \"$queryType\" due to not being a valid Aggregate Function!");
                }
            }
        }

        // Check $mCol is either in $uniqueCols or in $tbsWithCols
        // after first checking if it is just a [Subquery]
        if (str_starts_with($mCol, '[') && str_ends_with($mCol, ']')) {
            if ($queryType === 'INSERT') {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
            }
            if (!isset($validCols['subqueries'][$mCol])) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mCol` not being found in the SubQueries Array!");
            }
            $parsedCondition .= " $mCol ";
            cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mCol) in \"$wPart\" in Query Type: \"$queryType\" where Table:Column otherwise would be. This is replaced later. Continuing...");
        } elseif (str_starts_with($mCol, "(") && str_ends_with($mCol, ")")) {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$mCol\" in Query Type: \"$queryType\" due to using parenthesis '(' or ')' outside it!");
            cli_info_without_exit("Using () to indicate a Tuple or Row Constructor is not supported as of yet in FunkPHP!");
            cli_info_without_exit("If you wanna use a [SubQuery] means you should start with `[` and end with `]`. This allows you to use Tuples, Row Constructors and such.");
            cli_info("IMPORTANT: Using [SubQuery] means you lose the Validation Parsing Logic and you must add the `?` Placeholders  in the `bparam` Key and the <MATCHED_FIELDS> keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
        }
        // For 'WHERE' Key
        if ($whereOrHaving === 'WHERE') {
            if (
                !str_contains($mCol, ":") && !in_array($mCol, $uniqueCols, true)
            ) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to `$mCol` not being found in the Unique Columns (col1,col2,etc) Array!");
                cli_info_without_exit("When not found in Unique Columns Array, it becomes ambigious which Table it belongs to!");
                cli_info_without_exit("Valid Unique Columns Array is:\n" . implode(",\n", quotify_elements($uniqueCols)) . "!");
                cli_info("If you are only using on Table suddenly for your SQL Query, change your `<TABLES>` Key also to only have that one Table OR Write `correctTable:$mCol` exactly in the Condition Clause!");
            } elseif (
                str_contains($mCol, ":") &&
                !in_array($mCol, $tbsWithCols, true)
            ) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to `$mCol` not being found in the Table with Columns (table1:col1,table1:col2,etc2:etc1) Array!");
                cli_info_without_exit("Valid Table with Columns Array is:\n" . implode(",\n", quotify_elements($tbsWithCols)) . "!");
                cli_info("If you are only using on Table suddenly for your SQL Query, change your `<TABLES>` Key also to only have that one Table OR Write `correctTable:$mCol` exactly in the Condition Clause!");
            }
        }

        // Check that $mOperator is a valid operator after first checking if it is a [Subquery]
        if (str_starts_with($mOperator, '[') && str_ends_with($mOperator, ']')) {
            if ($queryType === 'INSERT') {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
            }
            if (!isset($validCols['subqueries'][$mOperator])) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mOperator` not being found in the SubQueries Array!");
            }
            $parsedCondition .= " $mOperator ";
            cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mOperator) in \"$wPart\" in Query Type: \"$queryType\" where Operator otherwise would be. This is replaced later. Continuing...");
        } elseif (str_starts_with($mOperator, "(") && str_ends_with($mOperator, ")")) {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$mOperator\" in Query Type: \"$queryType\" due to using parenthesis '(' or ')' outside it!");
            cli_info_without_exit("Using () to indicate a Tuple or Row Constructor is not supported as of yet in FunkPHP!");
            cli_info_without_exit("If you wanna use a [SubQuery] means you should start with `[` and end with `]`. This allows you to use Tuples, Row Constructors and such.");
            cli_info("IMPORTANT: Using [SubQuery] means you lose the Validation Parsing Logic and you must add the `?` Placeholders  in the `bparam` Key and the <MATCHED_FIELDS> keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
        } elseif (!in_array($mOperator, $mysqlOperatorSyntax['all'], true)) {
            cli_err("[cli_parse_where_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to operator `$mOperator` not being a valid MySQL Operator!");
        }

        // Special Case where $mOperator is NOT "=" meaning it could affect more than
        // one row table so we warn the Developer about it but still allow it. It applies
        // to the Query Types DELETE and UPDATE where it could cause issues if not careful!
        if (
            $mOperator !== '=' && ($queryType === 'DELETE' || $queryType === 'UPDATE')
            && (!str_starts_with($mOperator, '[') && !str_ends_with($mOperator, ']'))
        ) {
            cli_warning_without_exit("[cli_parse_condition_clause_sql]: Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" has an Operator that is NOT `=`!");
            cli_info_without_exit("This could lead to affecting more Table Rows than desired unless you really want that!");
        }

        // Now we start adding to the $parsedCondition string. First we check if "$specialSyntax" is not empty
        // meaning we should add that before the column name/tableName:columnName Operator Value parts!
        // not work correctly now due to just being added despite ")" should be at the end of the WHERE clause!
        if (!empty($specialSyntax)) {
            if ($parsedCondition !== ' ' && str_contains($specialSyntax, "(")) {
                $parsedCondition .= " ";
            }
            $parsedCondition .= " " . $specialSyntax;
        }

        // There are two cases now: Either we have a Single Table to
        // check against and add based on or we have several ones.
        // SINGLE TABLE CASE:
        if ($singleTable) {
            // $mValue contains [SubQuery] meaning it starts with "[" and ends with "]"
            if (str_ends_with($mValue, "]") && str_starts_with($mValue, "[")) {
                // We check if the $mValue is a valid SubQuery and if it is, we add it to the $parsedCondition
                // string as is. It is handled outside of this Parsing Process.
                if ($queryType === 'INSERT') {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
                }
                if (!isset($validCols['subqueries'][$mValue])) {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mValue` not being found in the SubQueries Array!");
                }
                $parsedCondition .= " $mValue ";
                cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mValue) in \"$wPart\" in Query Type: \"$queryType\" where a Value otherwise would be. This is replaced later. Continuing...");
                continue;
            }

            // When $mValue is NOT a [SubQuery]
            $singleTb = $tbs[0] ?? null;
            if (!is_string_and_not_empty($singleTb)) {
                cli_err("[cli_parse_where_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Single Table not being a valid string!");
            }

            // If $mCol contains a ":", we know it is a tableName:columnName
            // so we can extract the table name and column name from it. But
            // we also make sure it exists in both unique columns and table with columns arrays!
            // When $singleTable case, it should ALWAYS exist in both arrays!
            if (str_contains($mCol, ":")) {
                if (!in_array($mCol, $tbsWithCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Array of `Table:Column`!");
                    cli_info("A Table might be missing from `<TABLES>` Key OR it has too many Tables if you SUDDENLY changed to Only Query One! Available Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
                [$singleTb, $mCol] = explode(":", $mCol, 2);
                if (!in_array($mCol, $uniqueCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Unique Columns Array!");
                    cli_info("A Table might be missing from `<TABLES>` Key OR it has too many Tables if you SUDDENLY changed to Only Query One! Available Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
            }

            // Then we try find correct binding type for the column in the table
            $correctBinding = $allTbs[$singleTb][$mCol]['binding'] ?? null;
            $expectedBinding = "";
            if (!$correctBinding) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` in table `$singleTb` NOT having a Valid Binding Type!");
            }
            if ($correctBinding === 's') {
                $expectedBinding = "String";
            } elseif ($correctBinding === 'i') {
                $expectedBinding = "Integer";
            } elseif ($correctBinding === 'd') {
                $expectedBinding = "Double";
            } elseif ($correctBinding === 'b') {
                $expectedBinding = "Blob";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Column `$mCol` in Table `$singleTb` having an Invalid Binding Type!");
            }

            // Extract column from $mCol IF it contains a ":" and then add it
            // with the already validated operator to the $parsedCondition string.
            $mCol = str_contains($mCol, ":") ? explode(":", $mCol, 2)[1] : $mCol;
            $parsedCondition .= " $singleTb.$mCol $mOperator ";

            // Now we need to check if the $mValue is a valid type whether it is a
            // a placeholder ? or an actual value. If it is a placeholder, we
            // add it to the $builtBindedParamsString and if it is a value, we
            // add it to the $parsedCondition string with proper escaping.
            if ($mValue === '?') {
                // If it is a placeholder, we add it to the $builtBindedParamsString
                $builtBindedParamsString .= $correctBinding;
                $parsedCondition .= "? ";

                // We also add the field to the builtFieldsArray based on either any provided
                // <MATCHED_FIELDS> unique field name or just using default tableName_ColumnName
                if (isset($builtFieldsArray)) {
                    if (isset($validCols["matchedFields"][$mCol])) {
                        if (is_string($validCols["matchedFields"][$mCol]) && !empty($validCols["matchedFields"][$mCol])) {
                            $mCol = $validCols["matchedFields"][$mCol];
                        }
                    }
                    if (!in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                        $builtFieldsArray[] = $singleTb . "_" . $mCol;
                    } elseif (in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                        // We iterate adding +1 to the name until it is unique
                        $i = 1;
                        while (in_array($singleTb . "_" . $mCol . "_$i", $builtFieldsArray)) {
                            $i++;
                        }
                        $builtFieldsArray[] = $singleTb . "_" . $mCol . "_$i";
                    }
                }
            }
            // It is a hardcoded provided Value
            elseif (is_string($mValue) || is_numeric($mValue)) {
                if (is_numeric($mValue)) {
                    // It expects an Integer so we regex to validate it is ONLY integers without any decimals
                    if ($correctBinding === 'i') {
                        if (!preg_match('/^-?\d+$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$singleTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects a Double so we regex to validate it is a valid double with optional decimals
                    elseif (($correctBinding === 'd')) {
                        if (!preg_match('/^-?\d+(\.\d+)?$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$singleTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects either a String or Blob so we know the provided numeric value is invalid
                    elseif (($correctBinding === 'b') || ($correctBinding === 's')) {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$singleTb` expects `$expectedBinding`!");
                    }
                } elseif (is_string($mValue)) {
                    if ($correctBinding !== 's' && $correctBinding !== 'b') {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value `$mValue`. Column `$mCol` in Table `$singleTb` does NOT expect a String but `$expectedBinding`!");
                    }
                    $mValue = "'" . $mValue . "'";
                }
                //$builtBindedParamsString .= $correctBinding; ?? Needed when it is hardcoded value?
                $parsedCondition .= "$mValue ";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to the provided value NOT being a Valid String/Blob or Numeric Value!");
            }
        }

        // ? IMPORTANT: This might not work as intended with multiple tables yet, despite being pretty much
        // the same as the single table case. It differs mainly from having to find the correct Table first
        // based on the $mCol which might or might not already include the table name. It might include just a
        // unique column meaning we must find it manually in the $allTbs array!
        // MULTIPLE TABLES CASE:
        else {
            // $mValue contains [SubQuery] meaning it starts with "[" and ends with "]"
            if (str_ends_with($mValue, "]") && str_starts_with($mValue, "[")) {
                // We check if the $mValue is a valid SubQuery and if it is, we add it to the $parsedCondition
                // string as is. It is handled outside of this Parsing Process.
                if ($queryType === 'INSERT') {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
                }
                if (!isset($validCols['subqueries'][$mValue])) {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mValue` not being found in the SubQueries Array!");
                }
                $parsedCondition .= " $mOperator $mValue ";
                cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mValue) in \"$wPart\" in Query Type: \"$queryType\" where a Value otherwise would be. This is replaced later. Continuing...");
                continue;
            }
            // We need to find the correct Table for the $mCol when ":" is missing
            // because we now have multiple tables and the $mCol might just be
            // a unique column name without the table name giving us no table!
            $correctTb = null;

            // ":" missing, so find correct Table manually
            if (!str_contains($mCol, ":")) {
                // Before we start looping through the tables, we check if the $mCol is in the unique columns
                // because if it is not, then we can never know if we match a correct table since it could
                // multiple tables have the same column name!
                if (!in_array($mCol, $uniqueCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Unique Columns Array!");
                    cli_info("Column `$mCol` might be in multiple tables at once making it impossible to find the correct one. Please use `table:column$mCol` syntax to specify the Table explicitly for this Column!");
                }
                // We only loop through tables that are actually in the $tbs array
                foreach ($allTbs as $tb => $colKey) {
                    if (!in_array($tb, $tbs, true)) {
                        continue;
                    }
                    foreach ($colKey as $k => $col) {
                        if ($mCol === $k) {
                            // We found the correct Table for the $mCol
                            $correctTb = $tb;
                            break 2; // Break out of both foreach loops
                        }
                    }
                }
                // If we did not find the correct Table, we error out
                if (!$correctTb) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Column `$mCol` not being found in any of the Tables!");
                    cli_info("A Table might be missing from `tables.php` File. Checked Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
            }
            // We can just extract the correct Table from the $mCol when it contains a ":"
            else {
                if (!in_array($mCol, $tbsWithCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Array of `Table:Column`!");
                    cli_info("A Table might be missing from `<TABLES>` Key OR it has too many Tables if you SUDDENLY changed to Only Query One! Available Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
                [$correctTb, $mCol] = explode(":", $mCol, 2) ?? null;
            }

            // Now we finally process the $correctTb and $mCol as usual (just like with the single table case)
            if (!is_string_and_not_empty($correctTb)) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to correct Table NOT being a Valid String!");
            }
            $correctBinding = $allTbs[$correctTb][$mCol]['binding'] ?? null;
            $expectedBinding = "";
            if (!$correctBinding) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` in table `$correctTb` NOT having a Valid Binding Type. It was not found in the `tables.php` File!");
            }
            if ($correctBinding === 's') {
                $expectedBinding = "String";
            } elseif ($correctBinding === 'i') {
                $expectedBinding = "Integer";
            } elseif ($correctBinding === 'd') {
                $expectedBinding = "Double";
            } elseif ($correctBinding === 'b') {
                $expectedBinding = "Blob";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Column `$mCol` in Table `$correctTb` having an Invalid Binding Type!");
            }

            // Extract column from $mCol IF it contains a ":" and then add it
            // with the already validated operator to the $parsedCondition string.
            $mCol = str_contains($mCol, ":") ? explode(":", $mCol, 2)[1] : $mCol;
            $parsedCondition .= " $correctTb.$mCol $mOperator ";

            // Now we need to check if the $mValue is a valid type whether it is a
            // a placeholder ? or an actual value. If it is a placeholder, we
            // add it to the $builtBindedParamsString and if it is a value, we
            // add it to the $parsedCondition string with proper escaping.
            if ($mValue === '?') {
                // If it is a placeholder, we add it to the $builtBindedParamsString
                $builtBindedParamsString .= $correctBinding;
                $parsedCondition .= "? ";

                // We also add the field to the builtFieldsArray based on either any provided
                // <MATCHED_FIELDS> unique field name or just using default tableName_ColumnName
                if (isset($builtFieldsArray)) {
                    if (isset($validCols["matchedFields"][$mCol])) {
                        if (is_string($validCols["matchedFields"][$mCol]) && !empty($validCols["matchedFields"][$mCol])) {
                            $mCol = $validCols["matchedFields"][$mCol];
                        }
                    }
                    if (!in_array($correctTb . "_" . $mCol, $builtFieldsArray)) {
                        $builtFieldsArray[] = $correctTb . "_" . $mCol;
                    } elseif (in_array($correctTb . "_" . $mCol, $builtFieldsArray)) {
                        // We iterate adding +1 to the name until it is unique
                        $i = 1;
                        while (in_array($correctTb . "_" . $mCol . "_$i", $builtFieldsArray)) {
                            $i++;
                        }
                        $builtFieldsArray[] = $correctTb . "_" . $mCol . "_$i";
                    }
                }
            }
            // It is a hardcoded provided Value
            elseif (is_string($mValue) || is_numeric($mValue)) {
                if (is_numeric($mValue)) {
                    // It expects an Integer so we regex to validate it is ONLY integers without any decimals
                    if ($correctBinding === 'i') {
                        if (!preg_match('/^-?\d+$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$correctTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects a Double so we regex to validate it is a valid double with optional decimals
                    elseif (($correctBinding === 'd')) {
                        if (!preg_match('/^-?\d+(\.\d+)?$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$correctTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects either a String or Blob so we know the provided numeric value is invalid
                    elseif (($correctBinding === 'b') || ($correctBinding === 's')) {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$correctTb` expects `$expectedBinding`!");
                    }
                } elseif (is_string($mValue)) {
                    if ($correctBinding !== 's' && $correctBinding !== 'b') {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value `$mValue`. Column `$mCol` in Table `$correctTb` does NOT expect a String but `$expectedBinding`!");
                    }
                    $mValue = "'" . $mValue . "'";
                }
                $parsedCondition .= "$mValue ";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to provided value NOT being a Valid String/Blob or Numeric Value!");
            }
        }
    } // END OF LOOP THROUGH EACH CONDITION CLAUSE PART

    // If not even amount of opening and closing () then
    // we err out and tell the Developer to fix it! ^_^
    if ($leftParenthesisCount !== $rightParenthesisCount) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to unbalanced parentheses!");
        cli_info_without_exit("Match the number of left parentheses `(` with the number of right parentheses `)`!");
        cli_info("You do this by adding `|)` at the end of the Condition Clause or where necessary to get the Priority Order as needed!");
    }

    // Add last closing parenthesis if we had any opening parentheses?
    if ($leftParenthesisCount === $rightParenthesisCount && $leftParenthesisCount > 0) {
        $parsedCondition = rtrim($parsedCondition);
    }

    // FINALLY RETURN THE PARSED Condition Clause!
    $parsedCondition = rtrim($parsedCondition);
    return $parsedCondition;
}

// Function that uses a DFS Algo to parse all possible JOINS_ON using
// the $fromTb as the starting point. It returns an array of possible JOINS!
function cli_parse_joins_on_DFS($fromTb, $availableTableNames, $relationships)
{
    $visitedTables = [];
    $suggestedJoins = [];
    $processedJoinPairs = [];
    $stack = [$fromTb];

    // Starting Table does not exist in the relationships or has no relationships
    if (!isset($relationships[$fromTb]) || empty($relationships[$fromTb])) {
        return [];
    }

    // Iterate while there are still Tables to process in the stack
    // (tables with possible relationships to process)
    while (!empty($stack)) {
        // Grab the last Table from the stack
        $currentTable = array_pop($stack);

        // Skip already visited Tables to avoid cycles
        if (in_array($currentTable, $visitedTables)) {
            continue;
        }
        // Now it means we are visiting this one so mark it as visited
        $visitedTables[] = $currentTable;

        // When current Table has no relationships, continue
        if (
            !isset($relationships[$currentTable])
            || !is_array($relationships[$currentTable])
            || empty($relationships[$currentTable])
        ) {
            continue;
        }

        // Iterate through each related Table and its relationship details
        foreach ($relationships[$currentTable] as $relatedTable => $relationshipDetails) {
            // Skip those already visited to avoid cycles
            if (!in_array($relatedTable, $availableTableNames)) {
                continue;
            }

            // Prepare and grab details for the (INNER) JOIN
            // $relatedTable is Table related with $currentTable
            $joinString = "'inner=$relatedTable,";
            $table1 = $relationshipDetails['local_table'];
            $col1 = $relationshipDetails['local_column'];
            $table2 = $relationshipDetails['foreign_table'];
            $col2 = $relationshipDetails['foreign_column'];

            // We Pair, Sort & Create a Unique Identifier for the JOIN
            // so we avoid duplicates in the JOINs Array!
            $sortedPair = [$table1, $table2];
            sort($sortedPair);
            $joinIdentifier = implode('-', $sortedPair);

            // Here we check if the JOIN Identifier is already processed
            // to avoid duplicates in the suggested JOINs Array!
            // So, continue to next if that is the case!
            if (in_array($joinIdentifier, $processedJoinPairs)) {
                continue;
            }

            // Finalize the JOIN String and add it to the suggested JOINs Array
            // and the processed JOIN Pairs Array to avoid duplicates!
            $joinString .= "$table1($col1),$table2($col2)'";
            $suggestedJoins[] = $joinString;
            $processedJoinPairs[] = $joinIdentifier;

            // Add the related Table to the stack for further processing if it has not
            // been fully visited yet! Each Table can have multiple relationships!
            if (!in_array($relatedTable, $visitedTables)) {
                $stack[] = $relatedTable;
            }
        }
    }
    // Return any possible JOINs based on the relationships starting from the $fromTb
    return $suggestedJoins;
}

// Function takes a string of joined tables (like "table1=>table2=>table3" or
// maybe just "table1=>table2") and parses its relationship from left to right
// and adds it to the current provided $currentFinalHydrateKey which is the
// "return array('hydrate' => 'key' => array())" for Hydration purposes.
// $keepGoing is a reference to a boolean that indicates whether to keep going
// or not during the Hydration Compilation Process that starts with the following:
// `$hydratedKey = null;\n if (isset($hydrationKey))){...}`
function cli_parse_joined_tables_order($tablesString, &$currentFinalHydrateKey, &$keepGoing, $selectedCols)
{
    global $tablesAndRelationshipsFile;
    $tables = $tablesAndRelationshipsFile['tables'] ?? [];
    $relationships = $tablesAndRelationshipsFile['relationships'] ?? [];

    // Validate the input parameters before anything else!
    if (!isset($relationships) || !is_array($relationships) || empty($relationships)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: No Relationships found in the `tablesAndRelationships.php` File!");
        cli_info_without_exit("Please ensure that the file is properly Configured with Relationships between Tables.");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_string_and_not_empty($tablesString) || !preg_match('/^([a-zA-Z0-9_]+)((=>){1}([a-zA-Z0-9_]+)(\(via:[a-zA-Z0-9_]+\))*)*$/i', $tablesString)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Non-Empty String as input for `\$tablesString`!");
        cli_info_without_exit("This probably means that the `tablesString` is NOT a String or that it IS Empty.");
        cli_info_without_exit("The Hydration Key must be a Non-Empty String representing the Hydration Key in the Format:\n`table` to Hydrate a Single Table OR\n`table=>table2` to Hydrate Two(2) Tables based on valid JOINING between them OR\n`table=>table2=>table3` to Hydrate Three(3) Tables based on valid JOINING between them\n(and so on to hydrate multiple joined tables)");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_array($currentFinalHydrateKey)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Non-Empty Array as input for `\$currentFinalHydrateKey`!");
        cli_info_without_exit("This probably means that the `currentFinalHydrateKey` is NOT an Array. (It CAN be Empty!)");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_array_and_not_empty($selectedCols)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Non-Empty Array as input for `\$selectedCols`!");
        cli_info_without_exit("This probably means that the `selectedCols` is NOT an Array or that it IS Empty.");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_bool($keepGoing)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Boolean as input for `\$keepGoing`!");
        cli_info_without_exit("This probably means that the `keepGoing` is NOT a boolean or that it is not set.\nIMPORTANT: It has now been set to false which probably means the remaining code for Hydration Compilation will NOT be executed.");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    // Turn into an array by either splitting by "=>" or just using it as a single array element
    $tablesString = str_contains($tablesString, "=>") ? explode("=>", $tablesString) : [$tablesString];

    // Single Table Case: It is NOT needed to have a relationship but the tables must exists of course!
    if (count($tablesString) === 1) {
        if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $tablesString[0], $matches)) {
            $tb1 = $matches[1];
            $viaTb = $matches[3];
        }
        if (!isset($tables[$tablesString[0]]) || !is_array($tables[$tablesString[0]]) || empty($tables[$tablesString[0]])) {
            $keepGoing = false;
            cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tablesString[0]}` in the `tables.php` File!");
            cli_info_without_exit("Verify that the `{$tablesString[0]}` Table exists in the `tables` Key in the `tables.php` File!.");
            cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
            return;
        }
        if (!isset($currentFinalHydrateKey[$tablesString[0]])) {
            // Remove `id` since we already have it as the Primary Key ('pk')
            unset($selectedCols[$tablesString[0]][$tablesString[0] . "_id"]);
            $currentFinalHydrateKey[$tablesString[0]] = [
                'pk' => $tablesString[0] . "_id",
                'cols' => array_keys($selectedCols[$tablesString[0]]),
                'with' => [],
            ];
        }
    }
    // Multiple Tables Case: For each table in the string we will add a table and then check the relationship
    // from previous table since the idea is that you should chain correctly ("table1=>table2=>table3") so we
    // only need to check relationships from the previous table to the next one. And if a table already exists
    // we adjust the position for the $currentFinalHydrateKey[$table] so we do not accidentally overwrite it!
    elseif (count($tablesString) > 1) {
        $maxIdx = count($tablesString) - 1; // Maximum Index of the Tables String
        $prevTB = ""; // Previous Table to use for the next table
        $prevPK = ""; // Previous Primary Key to use for the next table
        $nextFK  = ""; // Next Foreign Key to use for the next table
        $nextLevel = &$currentFinalHydrateKey; // Reference to the current level of the $currentFinalHydrateKey

        foreach ($tablesString as $idx => $tbStr) {
            // SPECIAL CASE: If the Table String contains a "via:" then we need to
            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $tbStr, $matches)) {
                $tb1 = $matches[1];
                $viaTb = $matches[3];

                // Validate all 3 Tables exist
                if (!isset($tables[$prevTB]) || !is_array($tables[$prevTB]) || empty($tables[$prevTB])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$prevTB}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                if (!isset($tables[$tb1]) || !is_array($tables[$tb1]) || empty($tables[$tb1])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tb1}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$tb1}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                if (!isset($tables[$viaTb]) || !is_array($tables[$viaTb]) || empty($tables[$viaTb])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$viaTb}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$viaTb}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                // Check that intermediate Table (viaTb) has a valid relationship with the previous Table (prevTB)
                // since it is the intermediate Table that connects the previous Table (prevTB) with the current Table (tbStr)
                if (!isset($relationships[$prevTB][$viaTb]) || !is_array($relationships[$prevTB][$viaTb]) || empty($relationships[$prevTB][$viaTb])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$viaTb}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$viaTb}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                // Check that intermediate Table (viaTb) has a valid relationship with the current Table (tb1) that is
                // supposed to connect directly to the previous Table (prevTB) via the intermediate Table (viaTb)
                if (
                    !isset($relationships[$viaTb][$tb1])
                    || !is_array($relationships[$viaTb][$tb1])
                    || empty($relationships[$viaTb][$tb1])
                    || !isset($relationships[$tb1][$viaTb])
                    || !is_array($relationships[$tb1][$viaTb])
                    || empty($relationships[$tb1][$viaTb])
                ) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$viaTb}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$viaTb}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }

                // Check that the previous Table (prevTB) has a valid relationship with the current Table (tb1)
                // which is a Many-to-Many relationship via the intermediate Table (viaTb)
                if (!isset($relationships[$prevTB][$tb1]) || !is_array($relationships[$prevTB][$tb1]) || empty($relationships[$prevTB][$tb1])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$tb1}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$tb1}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }

                // Table already exists, so we just adjust the position
                if (isset($nextLevel[$tb1])) {
                    $prevTB = $tb1;
                    $nextLevel = &$nextLevel[$tb1]['with'];
                }
                // Table does not exist, so we create it and since this is larger than 0 index,
                elseif (!isset($nextLevel[$tb1])) {
                    // Validate all necessary relationships keys exist first!
                    if (
                        !isset($relationships[$viaTb][$prevTB]['local_table'])
                        || !isset($relationships[$viaTb][$prevTB]['local_column'])
                        || !isset($relationships[$viaTb][$tb1]['local_table'])
                        || !isset($relationships[$viaTb][$tb1]['local_column'])
                    ) {
                        $keepGoing = false;
                        cli_warning_without_exit("[cli_parse_joined_tables_order]: Necessary Relationships Keys for the Tables `{$viaTb}` and/or `{$prevTB}` are missing in the `relationships` Key in the `tables.php` File!");
                        cli_info_without_exit("Verify that necessary Many-to-Many Relationships for the Tables `{$viaTb}` and/or `{$prevTB}` exist in the `relationships` Key in the `tables.php` File!.");
                        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                        return;
                    }

                    // Grab the Many-to-Many relationship between the previous Table (prevTB) and the current Table (tb1)
                    // via the intermediate Table (viaTb) to use it for the pivot table and foreign keys.
                    $mmRel = $relationships[$prevTB][$tb1];

                    // We delete PK from the 'cols' array since it is not needed
                    // and we will use the PK as the primary key for the Table.
                    unset($selectedCols[$tb1][$tb1 . "_id"]);
                    $nextLevel[$tb1] = [
                        'pk' => $tb1 . "_id",
                        'fk' => null,
                        'pivot' => [
                            'table' => $mmRel['pivot_table'],
                            'fk_to_parent_pivot_col' => $mmRel['local_table'] . "_" . $mmRel['pivot_local_key'],
                            'fk_to_child_pivot_col' => $mmRel['foreign_table'] . "_" . $mmRel['pivot_foreign_key'],
                        ],
                        'cols' => array_keys($selectedCols[$tb1]),
                        'with' => [],
                    ];
                    // KEEPING THIS FOR FUTURE USE IF NEEDED
                    // IF THE ABOVE VERSION DOES NOT WORK AS INTENDED!
                    // $nextLevel[$tb1] = [
                    //     'pk' => $tb1 . "_id",
                    //     'fk' => null,
                    //     'pivot' => [
                    //         'table' => $viaTb,
                    //         'fk_to_parent_pivot_col' => $relationships[$viaTb][$prevTB]['local_table'] . "_" . $relationships[$viaTb][$prevTB]['local_column'],
                    //         'fk_to_child_pivot_col' => $relationships[$viaTb][$tb1]['local_table'] . "_" . $relationships[$viaTb][$tb1]['local_column'],
                    //     ],
                    //     'cols' => array_keys($selectedCols[$tb1]),
                    //     'with' => [],
                    // ];
                }
                // Continue to next Table level, cause we either fail before we reach this or we succeed
                continue;
            }
            // OTHERWISE CHECK AS NORMAL
            if (!isset($tables[$tbStr]) || !is_array($tables[$tbStr]) || empty($tables[$tbStr])) {
                $keepGoing = false;
                cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tbStr}` in the `tables.php` File!");
                cli_info_without_exit("Verify that the `{$tbStr}` Table exists in the `tables` Key in the `tables.php` File!.");
                cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                return;
            }

            // First Table level (index 0)
            if ($idx === 0) {
                // First Table already exists, so we just adjust the position
                // and store previous Table & Primary Key & next Foreign Key
                if (isset($nextLevel[$tbStr])) {
                    $prevTB = $tbStr;
                    $nextLevel = &$nextLevel[$tbStr]['with'];
                }
                // First Table does not exist, so we create it
                elseif (!isset($nextLevel[$tbStr])) {
                    unset($selectedCols[$tbStr][$tbStr . "_id"]);
                    $nextLevel[$tbStr] = [
                        'pk' => $tbStr . "_id",
                        'cols' => array_keys($selectedCols[$tbStr]),
                        'with' => [],
                    ];
                    // Then we store the previous Table and Primary Key
                    // and readjust the position of the $nextLevel
                    $prevTB = $tbStr;
                    $nextLevel = &$nextLevel[$tbStr]['with'];
                }
            }
            // All other levels of Tables (index > 1)
            elseif ($idx > 0) {
                if (!isset($tables[$tbStr]) || !is_array($tables[$tbStr]) || empty($tables[$tbStr])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tbStr}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$tbStr}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                if (!isset($relationships[$prevTB][$tbStr]) || !is_array($relationships[$prevTB][$tbStr]) || empty($relationships[$prevTB][$tbStr])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$tbStr}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$tbStr}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                // There are two possible relationships between previous Table and current Table:
                $correctRel = $relationships[$prevTB][$tbStr];
                $correctFK = null;
                // 1. Previous Table is the Primary Key (PK) and current Table is the Foreign Key (FK)
                if ($correctRel['direction'] === 'pk_to_fk') {
                    $correctFK = $correctRel['foreign_table'] . "_" . $correctRel['foreign_column'] ?? null;
                }
                // 2. Previous Table is the Foreign Key (FK) and current Table is the Primary Key (PK)
                elseif ($correctRel['direction'] === 'fk_to_pk') {
                    $correctFK = $correctRel['local_table'] . "_" . $correctRel['local_column'] ?? null;
                }
                if ($correctFK === null) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship Direction between Tables: `{$prevTB}` and `{$tbStr}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$tbStr}` Tables have a valid Relationship Direction in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }

                // Next Table already exists, so we just adjust the position
                if (isset($nextLevel[$tbStr])) {
                    $prevTB = $tbStr;
                    $nextLevel = &$currentFinalHydrateKey[$tbStr]['with'];
                }
                // Next Table does not exist, so we create it and since this is larger than 0 index,
                // we also add the 'fk' which we know from $nextFK which is the previous table's
                elseif (!isset($nextLevel[$tbStr])) {
                    unset($selectedCols[$tbStr][$tbStr . "_id"]);
                    $nextLevel[$tbStr] = [
                        'fk' => $correctFK,
                        'pk' => $tbStr . "_id",
                        'cols' => array_keys($selectedCols[$tbStr]),
                        'with' => [],
                    ];
                    // Then we store the previous Table and Primary Key
                    // and readjust the position of the $nextLevel
                    $prevTB = $tbStr;
                    $nextLevel = &$nextLevel[$tbStr]['with'];
                }
            }
        }
    }
}

// Compiles a $DX SQL [] to an optmized SQL array that is returned within the same
// function that is used to validate the data. This is used to optimize the SQL process!
// VERY IMPORTANT WARNING: This function calls a function which uses eval() to parse the SQL file!!!
function cli_convert_simple_sql_query_to_optimized_sql($sqlArray, $handlerFile, $fnName)
{
    global $tablesAndRelationshipsFile;

    // Validate it is an associative array - not a list
    if (!is_array_and_not_empty($sqlArray)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty Associative Array as input for `\$sqlArray`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (array_is_list($sqlArray)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty Associative Array as input for `\$sqlArray`!");
        cli_info("Here it probably means that the \"\$DX\" variable is a List Array, empty or not?");
    }

    // Both $handlerFile and $fnName must be non-empty strings
    if (!is_string_and_not_empty($handlerFile)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty String as input for `\$handlerFile`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (!is_string_and_not_empty($fnName)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty String as input for `\$fnName`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // Anonymous Functions that are used repeatedly
    $extractColsWithoutIdFromTable = function ($table, $tableString) {
        $cols = (str_contains($tableString, ",")) ? explode(",", substr($tableString, strlen($table) + 1)) : [substr($tableString, strlen($table) + 1)];
        $cols = array_map('trim', $cols);
        $cols = array_filter($cols, function ($col) {
            return $col !== 'id';
        });
        return array_values($cols);
    };
    $extractColsWithIdFromTable = function ($table, $tableString) {
        $cols = (str_contains($tableString, ",")) ? explode(",", substr($tableString, strlen($table) + 1)) : [substr($tableString, strlen($table) + 1)];
        $cols = array_map('trim', $cols);
        $cols = array_filter($cols, function ($col) {
            return $col !== '';
        });
        return array_values($cols);
    };

    // Prepare variables to store the
    // converted SQL Query Array
    $convertedSQLArray = ["qtype" => "", "sql" => "", "hydrate" => [], "bparam" => "", "fields" => [],];
    $builtSQLString = "";
    $builtHydrateArray = [];
    $builtBindedParamsString = "";
    $builtFieldsArray = [];
    $tables = $tablesAndRelationshipsFile['tables'] ?? [];
    $cols = ['uniqueCols' => [], 'table:col' => [], 'subqueries' => null];
    $relationships = $tablesAndRelationshipsFile['relationships'] ?? [];

    // List of available Global Config Rules - these will be checked against
    $globalConfigRules = [
        '<QUERY_TYPE>' => [
            'SELECT_DISTINCT',
            'SELECT_INTO',
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE'
        ],
        '<TABLES>' => [], // The table name as a string
        '[SUBQUERIES]' => [],
        '<HYDRATION_MODE>' => [],
        '<HYDRATION_TYPE>' => [],
    ];

    // List of keys that are ignored based on query type (this is just to inform the Developer)
    $ignoredKeysByQueryType = [
        'SELECT_DISTINCT' => [
            "INSERT",
            "INTO",
            "DELETE",
            "UPDATE",
            "SET",
            "VALUES",
            "GROUP BY",
            "HAVING",
        ],
        'SELECT_INTO' => [
            "INSERT",
            "DELETE",
            "UPDATE",
            "SET",
            "VALUES",
        ],
        'SELECT' => [
            "INSERT",
            "INTO",
            "DELETE",
            "UPDATE",
            "SET",
            "VALUES",
        ],
        'INSERT' => [
            "INTO",
            "JOIN",
            "JOINS",
            "ON",
            "SELECT",
            "DELETE",
            "UPDATE",
            "SET",
            "FROM",
            "VALUES",
            "WHERE",
            "ORDER BY",
            "GROUP BY",
            "HAVING",
            "LIMIT",
            "OFFSET",
            "<HYDRATION>"
        ],
        'UPDATE' => [
            "INTO",
            "JOIN",
            "JOINS",
            "ON",
            "SELECT",
            "DELETE",
            "INSERT",
            "FROM",
            "VALUES",
            "ORDER BY",
            "GROUP BY",
            "HAVING",
            "LIMIT",
            "OFFSET",
            "<HYDRATION>"
        ],
        'DELETE' => [
            "INTO",
            "JOIN",
            "JOINS",
            "ON",
            "SELECT",
            "INSERT",
            "UPDATE",
            "SET",
            "VALUES",
            "ORDER BY",
            "GROUP BY",
            "HAVING",
            "LIMIT",
            "OFFSET",
            "<HYDRATION>"
        ]
    ];

    // List of minimum required keys for each query type
    $minimumRequiredKeysByQueryType = [
        'SELECT_DISTINCT' => ['SELECT_DISTINCT', 'FROM'],
        'SELECT_INTO' => ['SELECT', 'INTO'],
        'SELECT' => ['SELECT', 'FROM'],
        'INSERT' => ['INSERT_INTO',],
        'UPDATE' => ['UPDATE_SET',],
        'DELETE' => ['DELETE_FROM']
    ];

    // List of valid binding types (s,i,d,b)
    // s = string, i = integer, d = double, b = blob
    $validBindingTypes = [
        's' => [],
        'i' => [],
        'd' => [],
        'b' => []
    ];

    // List of shorthand names of valid JOIN Types
    $validJoinTypes = [
        'i' => 'INNER JOIN',
        'inner' => 'INNER JOIN',
        'ij' => 'INNER JOIN',
        'join' => 'INNER JOIN',
        'j' => 'INNER JOIN',
        'l' => 'LEFT JOIN',
        'left' => 'LEFT JOIN',
        'r' => 'RIGHT JOIN',
        'right' => 'RIGHT JOIN',
    ];

    // "'<CONFIG>' key(s)
    $configKey = $sqlArray['<CONFIG>'] ?? null;
    $configQTKey = $configKey['<QUERY_TYPE>'] ?? null;
    $configTBKey = $configKey['<TABLES>'] ?? null;
    $hydrationModeKey = $configKey['<HYDRATION_MODE>'] ?? null;
    $hydrationTypeKey = $configKey['<HYDRATION_TYPE>'] ?? null;
    $configSubQsKey = $configKey['[SUBQUERIES]'] ?? null;
    $cols['subqueries'] = $configSubQsKey ?? null;
    $validFieldsKey = $sqlArray['<MATCHED_FIELDS>'] ?? null;
    $cols['matchedFields'] = $validFieldsKey ?? null;

    // If "$configKey" not null, we check it is an array and not empty
    // and then we iterate through the keys to check for valid keys
    if (is_array_and_not_empty($configKey)) {
        // If "$configKey" is an array, we check for valid keys
        foreach ($configKey as $key => $value) {
            // If the key is not in the global config rules, we error out
            if (!array_key_exists($key, $globalConfigRules)) {
                cli_err_syntax_without_exit("Invalid Config Key `$key` in SQL Query `$handlerFile.php=>$fnName`!");
                cli_info("Valid Config Keys are: " . implode(", ", array_keys($globalConfigRules)) . ".");
            }
        }
    } else {
        cli_err_syntax("No Config Key `<CONFIG>` found in SQL Array `$handlerFile.php=>$fnName`. It and its `[QUERY_TYPE]` key must be set!");
    }

    // Validate that $configQTKey is set and is a valid query type
    // then store it in the convertedSQLArray['qtype']!
    if (!isset($configQTKey) || !is_string_and_not_empty($configQTKey)) {
        cli_err_syntax_without_exit("No Config Key `<QUERY_TYPE>` found in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("Valid Query Types are:\n" . implode(",\n", quotify_elements($globalConfigRules['[QUERY_TYPE]'])) . ".");
    } elseif (!in_array(strtoupper($configQTKey), $globalConfigRules['<QUERY_TYPE>'], true)) {
        cli_err_syntax_without_exit("Invalid Config Key `<QUERY_TYPE>` value `$configQTKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("Valid Query Types are:\n" . implode(",\n", quotify_elements($globalConfigRules['[QUERY_TYPE]'])) . ".");
    }
    $convertedSQLArray['qtype'] = strtoupper($configQTKey);

    // Validate that $configTBKey is set and is a valid table name
    if (!isset($configTBKey) || empty($configTBKey)) {
        cli_err_syntax_without_exit("Invalid Config Key `<TABLES>` value in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<TABLES>` key must be a Non-Empty String or Array representing the Table name(s)!");
    }
    // If $configTBKey is a string, we convert it to an array meaning splitting on "," if it exists
    // or just wrapping it in an array if it is a single table name
    if (is_string($configTBKey) && is_string_and_not_empty($configTBKey)) {
        // If it is a string, we split it by comma and trim each element
        if (str_contains($configTBKey, ',')) {
            $configTBKey = array_map('trim', explode(',', $configTBKey));
        } elseif (str_contains($configTBKey, '|')) {
            $configTBKey = array_map('trim', explode('|', $configTBKey));
        } else {
            $configTBKey = [trim($configTBKey)];
        }
    } elseif (!is_array($configTBKey)) {
        cli_err_syntax_without_exit("Invalid Config Key `<TABLES>` value in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<TABLES>` key must be a Non-Empty String or Array representing the Table name(s)!");
    }

    // Loop through all Tables and check if they are valid (and exist in $tables[] array!)
    if (isset($configTBKey) && is_array_and_not_empty($configTBKey)) {
        foreach ($configTBKey as $tableName) {
            // If the table name is not a string or empty, we error out
            if (!is_string_and_not_empty($tableName)) {
                cli_err_syntax_without_exit("Invalid Table Name `$tableName` in SQL Array `$handlerFile.php=>$fnName`!");
                cli_info("Table Names must be Non-Empty Strings!");
            }
            // If the table name is not in the $tables array, we error out
            if (!array_key_exists($tableName, $tables)) {
                cli_err_syntax_without_exit("Table Name `$tableName` from SQL Array `$handlerFile.php=>$fnName` not found in `config/tables.php` File!");
                cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
            }
        }
    }

    // <HYDRATION_MODE> & <HYDRATION_TYPE> Keys are only available for `SELECT` Queries!
    if (isset($hydrationModeKey) && is_string_and_not_empty($hydrationModeKey) && $configQTKey !== 'SELECT') {
        cli_err_syntax_without_exit("Invalid Config Key `<HYDRATION_MODE>` value `$hydrationModeKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<HYDRATION_MODE>` Key is only available for `SELECT` Queries! Please remove it or change the `<QUERY_TYPE>` to `SELECT`.");
    }
    if (isset($hydrationTypeKey) && is_string_and_not_empty($hydrationTypeKey) && $configQTKey !== 'SELECT') {
        cli_err_syntax_without_exit("Invalid Config Key `<HYDRATION_TYPE>` value `$hydrationTypeKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<HYDRATION_TYPE>` Key is only available for `SELECT` Queries! Please remove it or change the `<QUERY_TYPE>` to `SELECT`.");
    }

    // $cols contains all unique columns and table:col pairs which are used to
    // build the SQL Query by only checking against valid/existing table and columns
    // from `tables.php` file and also by the tables being used ($configTBKey).
    $singleTable = count($configTBKey) === 1;
    $removeDuplicateCols = [];
    foreach ($tables as $tb => $colKey) {
        // Only do this for the tables actually being processed!
        if (!in_array($tb, $configTBKey, true)) {
            continue;
        }
        foreach ($colKey as $k => $col) {
            if (isset($col['primary_key']) || isset($col['foreign_key'])) {
                $cols['table:col'][] = "$tb:$k";
                // Special case for Single Table Queries (only 1 Table provided)
                // Then we know PK and FK are Unique Columns in that table!
                if ($singleTable) {
                    $cols['uniqueCols'][] = $k;
                }
            }
            // If it is in the array, we add it to the $removeDuplicateCols array
            elseif (in_array($k, $cols['uniqueCols'], true)) {
                if (!in_array($k, $removeDuplicateCols, true)) {
                    $removeDuplicateCols[] = $k;
                }
                $cols['table:col'][] = "$tb:$k";
            }
            // If it is not in the array we add it to the $uniqueCols array
            elseif (!in_array($k, $cols['uniqueCols'], true)) {
                $cols['uniqueCols'][] = $k;
                $cols['table:col'][] = "$tb:$k";
            }
            continue;
        }
    }
    $cols['uniqueCols'] = array_diff($cols['uniqueCols'], $removeDuplicateCols);

    // If "$configSubQsKey" is not null (or empty), we make sure each array key
    // starts and ends with "[" and "]" and that its array element value is a non-empty string
    if (isset($configSubQsKey) && is_array_and_not_empty($configSubQsKey)) {
        // If the configSubQsKey is not an array, we error out
        if (!is_array($configSubQsKey)) {
            cli_err_syntax_without_exit("Invalid Config Key `[SUBQUERIES]` value in SQL Array `$handlerFile.php=>$fnName`!");
            cli_info("The `[SUBQUERIES]` key must be an Array representing the Subqueries!");
        }
        // If the configSubQsKey is an array, we check each key
        foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
            // If the subquery key does not start with "[" or end with "]", we error out
            if (!str_starts_with($subQueryKey, "[") || !str_ends_with($subQueryKey, "]")) {
                cli_err_syntax_without_exit("Invalid Subquery Key `$subQueryKey` in SQL Array `$handlerFile.php=>$fnName`!");
                cli_info("Subquery Keys must start with `[` and end with `]`!");
            }
            // If the subquery value is not a non-empty string, we error out
            if (!is_string_and_not_empty($subQueryValue)) {
                cli_err_syntax_without_exit("Invalid Subquery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName`!");
                cli_info("Subquery Values must be Non-Empty Strings (or just remove the key if not used)!");
            }
        }
    }

    // We iterate through $ignoredKeysByQueryType to add what keys would be ignored based
    // on query type ($configQTKey) and build and array and then we inform the Developer
    // that these keys are ignored based on the query type.
    $ignoredKeys = [];
    foreach ($sqlArray as $key => $keys) {
        // If the key is not in the ignored keys by query type, we skip it
        if (!array_key_exists($configQTKey, $ignoredKeysByQueryType)) {
            continue;
        }
        // If the key is in the ignored keys by query type, we add it to the ignored keys array
        if (in_array($key, $ignoredKeysByQueryType[$configQTKey], true)) {
            $ignoredKeys[] = $key;
        }
    }

    // We iterate through $minimumRequiredKeysByQueryType and make sure the current
    // query type ($configQTKey) has all the minimum required keys set in the SQL Array
    if (
        isset($minimumRequiredKeysByQueryType[$configQTKey])
        && is_array_and_not_empty($minimumRequiredKeysByQueryType[$configQTKey])
    ) {
        foreach ($minimumRequiredKeysByQueryType[$configQTKey] as $requiredKey) {
            // If the required key is not in the SQL Array, we error out
            if (!array_key_exists($requiredKey, $sqlArray)) {
                cli_err_syntax_without_exit("Missing Required Key `$requiredKey` in SQL Array `$handlerFile.php=>$fnName` for Query Type `$configQTKey`!");
                cli_info("The `$requiredKey` key must be set in the SQL Array for this Query Type!");
            }
        }
    }

    // If we have more than 1 tables when the query type is INSERT|UPDATE|DELETE, we error out
    if (
        in_array($configQTKey, ['INSERT', 'UPDATE', 'DELETE'], true)
        && is_array_and_not_empty($configTBKey)
        && count($configTBKey) > 1
    ) {
        cli_err_syntax_without_exit("Multiple Tables found in SQL Array `$handlerFile.php=>$fnName` for Query Type `$configQTKey`!");
        cli_info("For `$configQTKey` queries, only one table can be specified in `<TABLES>` key!");
    }

    // WE NOW PROCESS BASED ON $configQTKey VALUE (the Query Type)
    // BEFORE RETURNING THE FINALLY CONVERT_SQL_ARRAY VARIABLE!!!
    // THIS IS THE MAIN AND MOST IMPORTANT PART OF THE ENTIRE FUNCTION!!!

    // INSERT
    if ($configQTKey === 'INSERT') {
        $insertTb = $configTBKey[0] ?? null;
        $insertCols = "";
        $insertValues = "";
        if (!isset($insertTb) || !is_string_and_not_empty($insertTb)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for INSERT Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }

        // We will now check that "INSERT_INTO" key starts with "table_name:" since it
        // must be the same table name as the one chosen in "<CONFIG>['<TABLES>']"!
        $insertIntoKey = $sqlArray['INSERT_INTO'] ?? null;
        if (!isset($insertIntoKey) || !is_string_and_not_empty($insertIntoKey)) {
            cli_err_syntax_without_exit("No `INSERT_INTO` Key found in SQL Array `$handlerFile.php=>$fnName` for Query Type `$configQTKey`!");
            cli_info("The `INSERT_INTO` key must be a Non-Empty String representing the Table name(s)!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($insertIntoKey, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `$insertIntoKey` Key!");
            cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }
        if (!str_starts_with($insertIntoKey, $insertTb . ":")) {
            cli_err_syntax_without_exit("The `INSERT_INTO` Key in SQL Array `$handlerFile.php=>$fnName` does not start with the Table Name `$insertTb:`!");
            cli_info("The `INSERT_INTO` key must start with the Table Name followed by a colon `:`!");
        }

        // We extract columns based on ":" and then on optional "," (if there are more than one column)
        $insertCols = $extractColsWithoutIdFromTable($insertTb, $insertIntoKey);

        // We iterate through the True Table Columns to check if any of those
        // who are nullable === false are not in the $insertCols array and if so
        // we error out since those columns must be set in the INSERT Query!
        foreach ($tables[$insertTb] as $tKey => $tCol) {
            // Skip the ID column since it is auto-incremented
            if ($tKey === 'id') {
                continue;
            }
            // If the column is not nullable, we check if it is in the $insertCols
            // array and if it does not have a default value, we error out
            if (
                isset($tCol['nullable'])
                && !$tCol['nullable']
                && !in_array($tKey, $insertCols, true)
                && !isset($tCol['default'])
            ) {
                cli_err_syntax_without_exit("Column `$tKey` in Table `$insertTb` is NOT nullable (and without a Default Value) and must be included in the INSERT Query!");
                cli_info("Include the Column `$tKey` in the `INSERT_INTO` key of the SQL Array `$handlerFile.php=>$fnName`!");
            }
        }

        // We check that the columns are valid (exists in $tables[$insertTb])
        // and create the Binded Params String while we're at it!
        foreach ($insertCols as $col) {
            // If the column is not a string or empty, we error out
            if (!is_string_and_not_empty($col)) {
                cli_err_syntax_without_exit("Invalid Column Name in SQL Array `$handlerFile.php=>$fnName` for INSERT Query!");
                cli_info("Column Names must be Non-Empty Strings!");
            }
            // If the column is not in the table, we error out
            if (!array_key_exists($col, $tables[$insertTb])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$insertTb`!");
                cli_info("Valid Column Names are:\n" . implode(",\n", quotify_elements(array_keys($tables[$insertTb]))) . ".");
            }
            if (!isset($tables[$insertTb][$col]['binding'])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a Binding defined in `config/tables.php` for Table `$insertTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$insertTb`!");
            }
            if (!isset($validBindingTypes[$tables[$insertTb][$col]['binding']])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a VALID Binding defined in `config/tables.php` for Table `$insertTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$insertTb`!");
            }
            // The "fields" key in the $convertedSQLArray is used to store matching fields
            // so the Binded Params can use the correct values from a given array!
            if (isset($validFieldsKey) && is_array_and_not_empty($validFieldsKey)) {
                if (!isset($validFieldsKey[$col])) {
                    cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` is NOT in the <MATCHED_FIELDS> Array!");
                    cli_info("Valid Fields are:\n" . implode(",\n", quotify_elements($validFieldsKey)) . ".");
                } else {
                    $builtFieldsArray[] = !empty($validFieldsKey[$col]) ? $validFieldsKey[$col] : $insertTb . '_' . $col;
                }
            }
            $builtBindedParamsString .= $tables[$insertTb][$col]['binding'];
            if (!empty($builtFieldsArray)) {
                $convertedSQLArray['fields'] = $builtFieldsArray;
            }
        }

        // We count the $insertCols and create equally many ? as $insertValues
        // Then we implode the $insertCols and create the final SQL string(s)
        $insertValues = str_repeat("?,", count($insertCols) - 1) . "?";
        $insertCols = implode(",", $insertCols);
        $builtSQLString .= "INSERT INTO $insertTb ($insertCols) VALUES ($insertValues);";
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for INSERT Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the INSERT Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // UPDATE
    elseif ($configQTKey === 'UPDATE') {
        $updateTb = $configTBKey[0] ?? null;
        $whereTb = $sqlArray['WHERE'] ?? null;
        $updateCols = "";
        $updateColsWithPlaceholders = [];
        $whereWithPlaceholders = [];

        if (!isset($updateTb) || !is_string_and_not_empty($updateTb)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for UPDATE Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }
        $updateIntoKey = $sqlArray['UPDATE_SET'] ?? null;
        if (!isset($updateIntoKey) || !is_string_and_not_empty($updateIntoKey)) {
            cli_err_syntax_without_exit("No `UPDATE_SET` Key found in SQL Array `$handlerFile.php=>$fnName` for update Query!");
            cli_info("The `UPDATE_SET` key must be a Non-Empty String representing the Table name(s)!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($updateIntoKey, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `$updateIntoKey` Key!");
            cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }
        if (!str_starts_with($updateIntoKey, $updateTb . ":")) {
            cli_err_syntax_without_exit("The `UPDATE_SET` Key in SQL Array `$handlerFile.php=>$fnName` does not start with the Table Name `$updateTb:`!");
            cli_info("The `UPDATE_SET` key must start with the Table Name followed by a colon `:`!");
        }
        $updateCols = $extractColsWithIdFromTable($updateTb, $updateIntoKey);

        // We check that the columns are valid (exists in $tables[$updateTb])
        // and create the Binded Params String while we're at it!
        foreach ($updateCols as $key => $col) {
            // If the column is not a string or empty, we error out
            if (!is_string_and_not_empty($col)) {
                cli_err_syntax_without_exit("Invalid Column Name in SQL Array `$handlerFile.php=>$fnName` for update Query!");
                cli_info("Column Names must be Non-Empty Strings!");
            }
            // If the column is not in the table, we error out
            if (!array_key_exists($col, $tables[$updateTb])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$updateTb`!");
                cli_info("Valid Column Names are:\n" . implode(",\n", quotify_elements(array_keys($tables[$updateTb]))) . ".");
            }
            if (!isset($tables[$updateTb][$col]['binding'])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a Binding defined in `config/tables.php` for Table `$updateTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$updateTb`!");
            }
            if (!isset($validBindingTypes[$tables[$updateTb][$col]['binding']])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a VALID Binding defined in `config/tables.php` for Table `$updateTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$updateTb`!");
            }
            if ($col === 'id') {
                cli_err_syntax_without_exit("Column Name `id` (Primary Key) from SQL Array `$handlerFile.php=>$fnName` is not allowed in the UPDATE_SET Key of UPDATE Query!");
                cli_info("The `id` column is auto-incremented and should not be updated. It can however be used in the WHERE Key to indicate which rows should be affected by the UPDATE Query!");
            }
            // The "fields" key in the $convertedSQLArray is used to store matching fields
            // so the Binded Params can use the correct values from a given array!
            if (isset($validFieldsKey) && is_array_and_not_empty($validFieldsKey)) {
                if (!isset($validFieldsKey[$col])) {
                    cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` is NOT in the <MATCHED_FIELDS> Array!");
                    cli_info("Valid Fields are:\n" . implode(",\n", quotify_elements($validFieldsKey)) . ".");
                } else {
                    $builtFieldsArray[] = !empty($validFieldsKey[$col]) ? $validFieldsKey[$col] : $updateTb . '_' . $col;
                }
            }
            $updateColsWithPlaceholders[] = "$col = ?";
            $builtBindedParamsString .= $tables[$updateTb][$col]['binding'];
            if (!empty($builtFieldsArray)) {
                $convertedSQLArray['fields'] = $builtFieldsArray;
            }
        }

        // If the WHERE clause is set, we parse its condition and add it to the SQL Array
        // We also pass the "$builtBindedParamsString" as reference to add the necessary
        // "?" placeholders based on how many are used within the Parsed Where Clause!
        if (isset($whereTb) && is_string_and_not_empty($whereTb)) {
            $whereTb = cli_parse_condition_clause_sql($configTBKey, $whereTb, "UPDATE", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "WHERE", $aggAliases ?? []);
            // If $whereTb is no longer a string after parsing, we error out
            if (!is_string_and_not_empty($whereTb)) {
                cli_err_syntax_without_exit("Invalid `WHERE` Key String found in SQL Array `$handlerFile.php=>$fnName` for UPDATE Query after being processed by `cli_parse_where_clause_sql` Function!");
                cli_info("The `WHERE` Key must be a Non-Empty String representing the WHERE clause after being parsed by the `cli_parse_where_clause_sql` Function!");
            }
        }

        // We count the $insertCols and create equally many ? as $insertValues
        // Then we implode the $insertCols and create the final SQL string(s)
        $updateCols = implode(", ", $updateColsWithPlaceholders);
        $builtSQLString .= "UPDATE $updateTb SET $updateCols";
        $builtSQLString .= (isset($whereTb) && is_string($whereTb) && !empty($whereTb)) ? " WHERE $whereTb" : "";
        $builtSQLString .= ";";
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // We will now replace every [SubQuery] in the $builtSQLString by iterating
        // through the $configSubQsKey array and replacing the [SubQuery] with the
        // actual SubQuery string from the $configSubQsKey array.
        if (isset($configSubQsKey) && is_array($configSubQsKey) && count($configSubQsKey) > 0) {
            foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
                // If the subquery value is not a string or empty, we error out
                if (!is_string_and_not_empty($subQueryValue)) {
                    cli_err_syntax_without_exit("Invalid SubQuery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName` for SubQuery Key `$subQueryKey`!");
                    cli_info("The SubQuery Value must be a Non-Empty String representing the SubQuery!");
                }
                // Replace the [SubQuery] with the actual SubQuery string
                $builtSQLString = str_replace($subQueryKey, $subQueryValue, $builtSQLString);
            }
        }
        // We finally remove all extra spaces and newlines from the built SQL string
        // and then add it to the converted SQL Array
        $builtSQLString = preg_replace('/\( /', '(', $builtSQLString);
        $builtSQLString = preg_replace('/ \)/', ')', $builtSQLString);
        $builtSQLString = preg_replace('/\s+/', ' ', $builtSQLString);
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['fields'] = $builtFieldsArray;

        // When the WHERE clause is missing we strongly warn about it to the Developer but still allow it.
        // The warning is about that you would change ALL rows in the table if you do not specify a WHERE clause!
        if (!isset($whereTb) || !is_string_and_not_empty($whereTb)) {
            cli_warning_without_exit("No `WHERE` Key found in SQL Array `$handlerFile.php=>$fnName` for UPDATE Query:\n`$builtSQLString`!");
            cli_warning_without_exit("This means that ALL Rows in the Table `$updateTb` will be Updated with the same provided values!");
            cli_info_without_exit("If this is truly your intention, just ignore this warning above and continue as usual!");
        }

        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for UPDATE Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the UPDATE Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // DELETE
    elseif ($configQTKey === 'DELETE') {
        $deleteTb = $configTBKey[0] ?? null;
        $whereTb = $sqlArray['WHERE'] ?? null;

        if (!isset($deleteTb) || !is_string_and_not_empty($deleteTb)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for DELETE Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }
        $deleteIntoKey = $sqlArray['DELETE_FROM'] ?? null;
        if (!isset($deleteIntoKey) || !is_string_and_not_empty($deleteIntoKey)) {
            cli_err_syntax_without_exit("No `DELETE_FROM` Key found in SQL Array `$handlerFile.php=>$fnName` for update Query!");
            cli_info("The `DELETE_FROM` key must be a Non-Empty String representing the Table name(s)!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($deleteIntoKey, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `$deleteIntoKey` Key!");
            cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }
        // We check that $deleteTB is the exact same as  $configTBKey since
        // it should be ONLY one table name in the DELETE Query that you
        // be able to delete from per SQL String!
        if (
            !is_string_and_not_empty($deleteTb)
            || !is_string_and_not_empty($deleteIntoKey)
            || ($deleteTb !== $deleteIntoKey)
        ) {
            cli_err_syntax_without_exit("The `DELETE_FROM` Key in SQL Array `$handlerFile.php=>$fnName` does not match the Table Name `$deleteTb`!");
            cli_info("The `DELETE_FROM` key must match the Table Name in `<TABLES>` key since you should only delete from one Table per SQL String!");
        }

        // We check that $deleteTB is the exact same as  $configTBKey since
        // it should be ONLY one table name in the DELETE Query that you
        // be able to delete from per SQL String!
        if (
            !is_string_and_not_empty($deleteTb)
            || !is_string_and_not_empty($deleteIntoKey)
            || ($deleteTb !== $deleteIntoKey)
        ) {
            cli_err_syntax_without_exit("The `DELETE_FROM` Key in SQL Array `$handlerFile.php=>$fnName` does not match the Table Name `$deleteTb`!");
            cli_info("The `DELETE_FROM` key must match the Table Name in `<TABLES>` key since you should only delete from one Table per SQL String!");
        }

        // If the WHERE clause is set, we parse its condition and add it to the SQL Array
        // We also pass the "$builtBindedParamsString" as reference to add the necessary
        // "?" placeholders based on how many are used within the Parsed Where Clause!
        if (isset($whereTb) && is_string_and_not_empty($whereTb)) {
            $whereTb = cli_parse_condition_clause_sql($configTBKey, $whereTb, "DELETE", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "WHERE", $aggAliases ?? []);
            // If $whereTb is no longer a string after parsing, we error out
            if (!is_string_and_not_empty($whereTb)) {
                cli_err_syntax_without_exit("Invalid `WHERE` Key String found in SQL Array `$handlerFile.php=>$fnName` for UPDATE Query after being processed by `cli_parse_where_clause_sql` Function!");
                cli_info("The `WHERE` Key must be a Non-Empty String representing the WHERE clause after being parsed by the `cli_parse_where_clause_sql` Function!");
            }
        }

        // We count the $insertCols and create equally many ? as $insertValues
        // Then we implode the $insertCols and create the final SQL string(s)
        $builtSQLString .= "DELETE FROM $deleteTb";
        $builtSQLString .= (isset($whereTb) && is_string($whereTb) && !empty($whereTb)) ? " WHERE $whereTb" : "";
        $builtSQLString .= ";";
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // We will now replace every [SubQuery] in the $builtSQLString by iterating
        // through the $configSubQsKey array and replacing the [SubQuery] with the
        // actual SubQuery string from the $configSubQsKey array.
        if (isset($configSubQsKey) && is_array($configSubQsKey) && count($configSubQsKey) > 0) {
            foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
                // If the subquery value is not a string or empty, we error out
                if (!is_string_and_not_empty($subQueryValue)) {
                    cli_err_syntax_without_exit("Invalid SubQuery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName` for SubQuery Key `$subQueryKey`!");
                    cli_info("The SubQuery Value must be a Non-Empty String representing the SubQuery!");
                }
                // Replace the [SubQuery] with the actual SubQuery string
                $builtSQLString = str_replace($subQueryKey, $subQueryValue, $builtSQLString);
            }
        }
        // We finally remove all extra spaces and newlines from the built SQL string
        // and then add it to the converted SQL Array
        $builtSQLString = preg_replace('/\( /', '(', $builtSQLString);
        $builtSQLString = preg_replace('/ \)/', ')', $builtSQLString);
        $builtSQLString = preg_replace('/\s+/', ' ', $builtSQLString);
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['fields'] = $builtFieldsArray;

        // When the WHERE clause is missing we strongly warn about it to the Developer but still allow it.
        // The warning is about that you would change ALL rows in the table if you do not specify a WHERE clause!
        if (!isset($whereTb) || !is_string_and_not_empty($whereTb)) {
            cli_warning_without_exit("No `WHERE` Key found in SQL Array `$handlerFile.php=>$fnName` for DELETE Query:\n`$builtSQLString`!");
            cli_warning_without_exit("This means that ALL Rows in the Table `$deleteTb` will be DELETED that match the provided Condition(s)!");
            cli_info_without_exit("If this is truly your intention, just ignore this warning above and continue as usual!");
        }

        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for DELETE Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the DELETE Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // SELECT DISTINCT
    elseif ($configQTKey === 'SELECT_DISTINCT') {
        cli_err("<NOT SUPPORTED/IMPLEMENTED YET IN FUNKPHP! SCRIPT STOPPED!>");
        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for SELECT_DISTINCT Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the SELECT_DISTINCT Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // SELECT INTO
    elseif ($configQTKey === 'SELECT_INTO') {
        cli_err("<NOT SUPPORTED/IMPLEMENTED YET IN FUNKPHP! SCRIPT STOPPED!>");
        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for SELECT_INTO Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the SELECT_INTO Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // SELECT
    elseif ($configQTKey === 'SELECT') {
        $selectTbs = $configTBKey ?? null;
        // SELECTed Tables!
        $currentlySelectedTbs = [];
        // JOINS_ON-joined Tables which might be needed by
        // GROUP BY Key to use. You can have more JOINed
        // Tables than the ones SELECTed in the SELECT Key!
        $joinedTables = [];
        $joinedTablesWithRef = [];
        $allAliases = [];
        $aliasesTbCol = [];
        $selectedCols = []; // This is used by "<HYDRATION>" to know which columns are selected (using AS/aliases)
        // To check for duplicate aliases later since agg functions
        // could be used multiple times on the same table+column(s)!
        $aggAliases = [];
        $selectedTbsColsStr = "";
        $joinsStr = "";
        $whereStr = "";
        $groupByStr = "";
        $havingStr = "";
        $orderByStr = "";
        $limitStr = "";
        $offsetStr = "";
        $selectTb = $sqlArray['SELECT'] ?? null;
        $fromTb = $sqlArray['FROM'] ?? null;
        $joinsTb = $sqlArray['JOINS_ON'] ?? null;
        $whereTb = $sqlArray['WHERE'] ?? null;
        $groupByTb = $sqlArray['GROUP BY'] ?? null;
        $havingTb = $sqlArray['HAVING'] ?? null;
        $orderByTb = $sqlArray['ORDER BY'] ?? null;
        $limitTb = $sqlArray['LIMIT'] ?? null;
        $offsetTb = $sqlArray['OFFSET'] ?? null;
        $hydrationKey = $sqlArray['<HYDRATION>'] ?? null;
        $hydrationMode = "";
        $hydrationType = "";

        // If <HYDRATION_MODE> is set, it should be a string but can be empty or must be
        // "simple", "advanded" or "simple|advanced" (simple is assumed as default then)
        if (isset($hydrationModeKey) && !is_string($hydrationModeKey)) {
            cli_err_syntax_without_exit("Invalid <HYDRATION_MODE> Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The <HYDRATION_MODE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `simple`, `advanced` or `simple|advanced`! (here `simple` is assumed as default)");
        }
        if (isset($hydrationTypeKey) && !is_string($hydrationTypeKey)) {
            cli_err_syntax_without_exit("Invalid <HYDRATION_TYPE> Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The <HYDRATION_TYPE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `array`, `object` or `array|object`! (here `array` is assumed as default)");
        }
        if (is_string_and_not_empty($hydrationModeKey)) {
            $hydrationModeKey = strtolower($hydrationModeKey);
            if (!in_array($hydrationModeKey, ['simple', 'advanced', 'simple|advanced'], true)) {
                cli_err_syntax_without_exit("Invalid `<HYDRATION_MODE>` Key Value found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The <HYDRATION_MODE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `simple`, `advanced` or `simple|advanced`! (here `simple` is assumed as default)");
            }
            if ($hydrationModeKey === 'simple' || $hydrationModeKey === 'simple|advanced') {
                $hydrationMode = "simple";
            } else {
                $hydrationMode = "advanced";
            }
        }
        if (is_string_and_not_empty($hydrationTypeKey)) {
            $hydrationTypeKey = strtolower($hydrationTypeKey);
            if (!in_array($hydrationTypeKey, ['array', 'object', 'array|object'], true)) {
                cli_err_syntax_without_exit("Invalid `<HYDRATION_MODE>` Key Value found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The <HYDRATION_MODE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `array`, `object` or `array|object`! (here `array` is assumed as default)");
            }
            if ($hydrationTypeKey === 'array' || $hydrationTypeKey === 'array|object') {
                $hydrationType = "array";
            } else {
                $hydrationType = "object";
            }
        }

        // $selectTbs cannot be null or empty array/string
        if (!isset($selectTbs) || empty($selectTbs)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }

        // $selectTb cannot be null, an associative array or an empty list array
        if (!isset($selectTb) || !array_is_list($selectTb) || empty($selectTb)) {
            cli_err_syntax_without_exit("No `SELECT` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `SELECT` key must be a Non-Empty List Array representing the Table name(s)! For example:\n`table_name:col1,col2,col3` OR\n`table_name!:col1`\nSecond examples selects all columns except `col1`!");
        }

        // $selectTb listed array must all be complete string (not empty strings)
        foreach ($selectTb as $selectTbName) {
            if (!is_string_and_not_empty($selectTbName)) {
                cli_err_syntax_without_exit("Invalid Data Types found in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("Each Array Element in the `SELECT` Key must be a Non-Empty String representing the Table Name and optionally Columns to select from that Table!\nSyntax Example: `table_name:col1,col2,col3` OR `table_name!:col1`.\nThe second example selects all columns except `col1`!");
            }
        }

        // $fromTb cannot be null or empty string
        if (!isset($fromTb) || !is_string_and_not_empty($fromTb)) {
            cli_err_syntax_without_exit("No `FROM` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `FROM` key must be a Non-Empty String representing the Primary Table (and ONLY a single one) to SELECT and/or JOIN from!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($fromTb, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `FROM` Key!");
            cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }

        // We now loop through $selectTb and check whether the $fromTb table exists in at least one of the
        // $selectTbs tables and if not, we error out since the FROM table must be one of the SELECT tables!
        $tbFound = false;
        foreach ($selectTbs as $selectTbName) {
            if (str_starts_with($selectTbName, $fromTb)) {
                $tbFound = true;
                break;
            }
        }
        if (!$tbFound) {
            cli_err_syntax_without_exit("The `FROM` Key Table `$fromTb` was not found in the `SELECT` Key Tables in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `FROM` Key must contain one of the Tables in the `SELECT` Key since you need a starting Table for your SELECT Query!");
        }
        // Regexes for Aggregate Functions. First to see if it starts with any of the aggregate functions
        // and the second to see if it is a complete aggregate function with a table and/or column name
        // and the special case of COUNT(*) which is a special case of the COUNT function!
        $aggregateFunctionsStart = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\(|COUNT_OPB\(|RANK\()/i";
        $aggFuncRegex = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\(|COUNT_OPB\(|RANK\()([a-zA-Z0-9_,:\*]+)*\)$/i";
        $aggTableColRegex = "/^([a-zA-Z0-9_]+):([a-zA-Z0-9_]+)$/i";
        $aggColRegex = "/^([a-zA-Z0-9_]+)$/i";
        $aggFuncValidStarts = [
            'count(distinct=' => 'count_distinct_',
            'count(distinct ' => 'count_distinct_',
            'count(' => 'count_',
            'count(*)' => 'count_all_',
            'count_opb(' => 'count_over_partition_by_',
            'rank(' => 'rank_over_',
            'sum(' => 'sum_',
            'avg(' => 'avg_',
            'min(' => 'min_',
            'max(' => 'max_',
        ];

        // Commands that You might wanna SELECT but are not allowed to do
        // through FunkPHP so you get an error message about it!
        $disallowedCommands = [
            "BIN(",
            "BINARY",
            "CASE",
            "CAST",
            "COALESCE(",
            "CONVERT(",
            "CONV(",
            "CONNECTION_ID()",
            "CURRENT_USER",
            "DATABASE()",
            "IF(",
            "IFNULL(",
            "ISNULL(",
            "LAST_INSERT_ID()",
            "SESSION_USER()",
            "SYSTEM_USER()",
            "USER()",
            "VERSION()",
        ];

        // PARSING THE "SELECT" Key (all selected tables and columns)
        // We loop through $selectTb which we know now are all valid non-empty strings.
        // We will check, validate & build the SELECT part of the SQL String based on
        // different cases:
        foreach ($selectTb as $selectTbName) {
            if (!is_string_and_not_empty($selectTbName)) {
                cli_err_syntax_without_exit("Invalid Data Type found in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("Each Array Element in the `SELECT` Key must be a Non-Empty String representing the Table Name and optionally Columns to select from that Table!\nSyntax Example: `table_name:col1,col2,col3` OR `table_name!:col1`.\nThe second example selects all columns except `col1`!");
            }
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (str_starts_ends_with($selectTbName, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `SELECT` Key!");
                cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            if (array_str_starts_with($disallowedCommands, strtoupper($selectTbName))) {
                cli_err_syntax_without_exit("The `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` contains a Disallowed Command: `$selectTbName`!");
                cli_info_without_exit("The Following Commands are NOT allowed in the `SELECT` Key:\n" . implode(",\n", quotify_elements($disallowedCommands)) . ".");
                cli_info("You will have to MANUALLY write a SQL String that runs/SELECTs that specific Command or use some of the FunkPHP's in-built SQL Functions that run some of those specific Commands!");
            }
            // Lowercase entire string to make it case-insensitive
            $selectTbName = strtolower($selectTbName);
            // CASE 1: Starts with any of the Aggregate Functions
            if (preg_match($aggregateFunctionsStart, $selectTbName)) {
                if (preg_match($aggFuncRegex, $selectTbName, $aggFuncMatches)) {
                    $aggFunc = $aggFuncMatches[1];
                    $aggTbWithCol = $aggFuncMatches[2];

                    // Incorrect Aggregate Function Format despite matching
                    if (!isset($aggFuncValidStarts[$aggFunc])) {
                        cli_err_syntax_without_exit("Invalid Aggregate Function Format (`$selectTbName`) in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        cli_info("The Aggregate Function must start with one of the following:\n" . implode(",\n", quotify_elements(array_keys($aggFuncValidStarts))) . "!");
                    }

                    // SPECIAL CASE 1: COUNT(*) which is a special case of the COUNT function and thus we must get
                    // the table name from the $fromTb variable since it does not have a table name!
                    if ($aggFuncMatches[0] === 'count(*)') {
                        if (!in_array($fromTb, $selectTbs, true)) {
                            cli_err_syntax_without_exit("Table Name `$fromTb` from `FROM` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                            cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                        }
                        // We add the COUNT(*) as a special case without table and column
                        // Only add if not already in the currently selected tables and also
                        // so you can use multiple agg functions on the same table without
                        // JOINS_ON complaining about "mulitple tables selected"!
                        if (!in_array($fromTb, $currentlySelectedTbs, true)) {
                            $currentlySelectedTbs[] = $fromTb;
                        }

                        // Rename and add to the selectedTbsColsStr and the aggAliases
                        // so you can reuse same aggregate function on the same table
                        // without conflicting with the aliases!
                        $as_name = $aggFuncValidStarts[$aggFunc] . $fromTb;
                        $i = 0;
                        while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                            $i++;
                            $as_name = $as_name . "_$i";
                        }
                        $aggAliases[] = $as_name;
                        $allAliases[] = $as_name;
                        $selectedCols[$fromTb][] = $as_name;
                        $aliasesTbCol[$as_name] = [
                            'tb' => $fromTb,
                            'col' => '*',
                        ];
                        $selectedTbsColsStr .= strtoupper($aggFunc) . "*) AS " . $as_name . ",\n";
                        continue;
                    }
                    // SPECIAL CASE 2: COUNT_OPB() which is a special case of the COUNT function that
                    // also uses two table:col in order to build its SQL string part:
                    // "COUNT(tb1.col1) OVER (PARTITION BY tb2.col2)"
                    else if ($aggFuncMatches[0] === 'count_opb(') {
                        // This one needs two table:col parts separated by a comma so check for it
                        if (!preg_match('/^[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+,[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+$/i', $aggFuncMatches[1])) {
                            cli_err_syntax_without_exit("Invalid Table/Column Format (`$aggTbWithCol`) in Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("The Aggregate Function must follow the Format: `COUNT_OPB(tableName1:colName1,tableName2:colName2)`!");
                        }
                        // Now we separate first by comma and then by ":" to get both separated tables with their respective columns
                        [$firstTbCol, $secondTbCol] = explode(",", $aggFuncMatches[1], 2);
                        [$firstTb, $firstCol] = explode(":", $firstTbCol, 2);
                        [$secondTb, $secondCol] = explode(":", $secondTbCol, 2);

                        // Validate both tables exist
                        if (!isset($tables[$firstTb]) || !is_array_and_not_empty($tables[$firstTb])) {
                            cli_err_syntax_without_exit("Table Name `$firstTb` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `tables.php` File!");
                            cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
                        }
                        if (!isset($tables[$secondTb]) || !is_array_and_not_empty($tables[$secondTb])) {
                            cli_err_syntax_without_exit("Table Name `$secondTb` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `tables.php` File!");
                            cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
                        }

                        // Validate both tables picked columns exist in their respective tables
                        if (!isset($tables[$firstTb][$firstCol]) || !is_array_and_not_empty($tables[$firstTb][$firstCol])) {
                            cli_err_syntax_without_exit("Column Name `$firstCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$firstTb`!");
                            cli_info("Valid Column Names for Table `$firstTb` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$firstTb]))) . ".");
                        }
                        if (!isset($tables[$secondTb][$secondCol]) || !is_array_and_not_empty($tables[$secondTb][$secondCol])) {
                            cli_err_syntax_without_exit("Column Name `$secondCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$secondTb`!");
                            cli_info("Valid Column Names for Table `$secondTb` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$secondTb]))) . ".");
                        }

                        // Add both tables to the currently selected tables. Only add if not already in the currently selected tables and also
                        // so you can use multiple agg functions on the same table without JOINS_ON complaining about "mulitple tables selected"!
                        if (!in_array($firstTb, $currentlySelectedTbs, true)) {
                            $currentlySelectedTbs[] = $firstTb;
                        }
                        if (!in_array($secondTb, $currentlySelectedTbs, true)) {
                            $currentlySelectedTbs[] = $secondTb;
                        }

                        // Prepare alias name
                        $as_name = $aggFuncValidStarts[$aggFunc] . $tables[$firstTb][$firstCol]['joined_name'] . "_" . $tables[$secondTb][$secondCol]['joined_name'];
                        $i = 0;
                        while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                            $i++;
                            $as_name = $as_name . "_$i";
                        }
                        $aggAliases[] = $as_name;
                        $allAliases[] = $as_name;
                        $selectedCols[$firstTb][$as_name] = $firstCol;
                        $selectedCols[$firstTb][$tables[$firstTb][$firstCol]['joined_name']] = $firstCol;
                        $selectedCols[$secondTb][$tables[$secondTb][$secondCol]['joined_name']] = $secondCol;
                        $aliasesTbCol[$as_name] = [
                            'tb' => $firstTb,    // Table used in the COUNT(tb.col) part
                            'col' => $firstCol,  // Column used in the COUNT(tb.col) part
                            'tb2' => $secondTb,  // Table used in the PARTITION BY tb2.col2 part
                            'col2' => $secondCol, // Column used in the PARTITION BY tb2.col2 part
                        ];
                        $aliasesTbCol[$tables[$firstTb][$firstCol]['joined_name']] = [
                            'tb' => $firstTb,
                            'col' => $firstCol,
                        ];

                        // Finally add the bult string part
                        $selectedTbsColsStr .= "COUNT(" . $firstTb . "." . $firstCol . ") OVER (PARTITION BY " . $secondTb . "." . $secondCol . ") AS " . $as_name . ",\n";

                        // Now continue to the next SELECTed part unless error occured before this point!
                        continue;
                    }
                    // SPECIAL CASE 3: RANK() which is when RANK() is used which needs at least
                    // one table:col but can also take two meaning you apply PARTIION BY, otherwise
                    // only the ORDER BY
                    else if ($aggFuncMatches[0] === 'rank(') {
                        // This one can either take one tb1:col1 OR two tb1:col1,tb2:col2
                        if (!preg_match('/^[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+(,[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+)?$/i', $aggFuncMatches[1])) {
                            cli_err_syntax_without_exit("Invalid Table/Column Format (`$aggTbWithCol`) in Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("The Aggregate Function must follow the Format: `RANK(tableName1:colName1)` or `RANK(tableName1:colName1,tableName2:colName2)`. Using Two Tables and Columns means you apply PARTITION BY to the First Table:Column. Sorting is DESC by default and if you want to change this, you will have to do so manually to the finally generated SQL String:`\$convertedSQLArray['sql']`!");
                        }
                        // Extract either one or two tables based on "," exists in the string
                        [$firstTbCol, $secondTbCol] = str_contains($aggFuncMatches[1], ',') ? explode(",", $aggFuncMatches[1], 2) : [$aggFuncMatches[1], null];
                        [$firstTb, $firstCol] = explode(":", $firstTbCol, 2);
                        [$secondTb, $secondCol] = $secondTbCol !== null ? explode(":", $secondTbCol, 2) : [null, null];

                        // Validate both tables exist
                        if (!isset($tables[$firstTb]) || !is_array_and_not_empty($tables[$firstTb])) {
                            cli_err_syntax_without_exit("Table Name `$firstTb` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `tables.php` File!");
                            cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
                        }
                        if (!isset($tables[$secondTb]) || !is_array_and_not_empty($tables[$secondTb])) {
                            cli_err_syntax_without_exit("Table Name `$secondTb` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `tables.php` File!");
                            cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
                        }

                        // Validate both tables picked columns exist in their respective tables
                        if (!isset($tables[$firstTb][$firstCol]) || !is_array_and_not_empty($tables[$firstTb][$firstCol])) {
                            cli_err_syntax_without_exit("Column Name `$firstCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$firstTb`!");
                            cli_info("Valid Column Names for Table `$firstTb` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$firstTb]))) . ".");
                        }
                        if (!isset($tables[$secondTb][$secondCol]) || !is_array_and_not_empty($tables[$secondTb][$secondCol])) {
                            cli_err_syntax_without_exit("Column Name `$secondCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$secondTb`!");
                            cli_info("Valid Column Names for Table `$secondTb` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$secondTb]))) . ".");
                        }

                        // Add both tables to the currently selected tables. Only add if not already in the currently selected tables and also
                        // so you can use multiple agg functions on the same table without JOINS_ON complaining about "mulitple tables selected"!
                        if (!in_array($firstTb, $currentlySelectedTbs, true)) {
                            $currentlySelectedTbs[] = $firstTb;
                        }
                        if ($secondTb !== null && !in_array($secondTb, $currentlySelectedTbs, true)) {
                            $currentlySelectedTbs[] = $secondTb;
                        }

                        // Prepare alias name and optionally add second table and column name where needed
                        $as_name = $aggFuncValidStarts[$aggFunc] . $tables[$firstTb][$firstCol]['joined_name'];
                        if ($secondTb !== null) {
                            $as_name .= "_" . $tables[$secondTb][$secondCol]['joined_name'];
                        }
                        $i = 0;
                        while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                            $i++;
                            $as_name = $as_name . "_$i";
                        }
                        $aggAliases[] = $as_name;
                        $allAliases[] = $as_name;
                        $selectedCols[$firstTb][$as_name] = $firstCol;
                        $selectedCols[$firstTb][$tables[$firstTb][$firstCol]['joined_name']] = $firstCol;
                        if ($secondTb !== null) {
                            $selectedCols[$secondTb][$tables[$secondTb][$secondCol]['joined_name']] = $secondCol;
                        }
                        $aliasesTbCol[$as_name] = [
                            'tb' => $firstTb,    // Table used in the RANK() part
                            'col' => $firstCol,  // Column used in the RANK() part
                        ];
                        if ($secondTb !== null) {
                            $aliasesTbCol[$as_name]['tb2'] = $secondTb;  // Table used in the PARTITION BY tb2.col2 part
                            $aliasesTbCol[$as_name]['col2'] = $secondCol; // Column used in the PARTITION BY tb2.col2 part
                        }
                        // Finally add the bult string part based on whether second table:col was given or not
                        if ($secondTb !== null) {
                            $selectedTbsColsStr .= "RANK() OVER (PARTITION BY " . $firstTb . "." . $firstCol . " ORDER BY " . $secondTb . "." . $secondCol . " DESC) AS " . $as_name . ",\n";
                        } else {
                            $selectedTbsColsStr .= "RANK() OVER (ORDER BY " . $firstTb . "." . $firstCol . " DESC) AS " . $as_name . ",\n";
                        }
                        // Continue to next SELECTed part unless error occured before this point!
                        continue;
                    }

                    // Incorrect Table/Column Format despite matching
                    if (!preg_match($aggTableColRegex, $aggTbWithCol)) {
                        cli_err_syntax_without_exit("Invalid Table/Column Format (`$aggTbWithCol`) in Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        cli_info("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                    }

                    // Extract Table and Column from the Aggregate Function
                    // to check against Valid Table & Column from `tables.php`!
                    [$aggTb, $aggCol] = explode(":", $aggTbWithCol, 2);
                    if (isset($tables[$aggTb]) && is_array_and_not_empty($tables[$aggTb])) {
                        // Validate correct Table:Column Binding (d or i when SUM() or AVG() is used!)
                        if (isset($tables[$aggTb][$aggCol]) && is_array_and_not_empty($tables[$aggTb][$aggCol])) {
                            if (($aggFunc === 'sum(' || $aggFunc === 'avg(') &&
                                ($tables[$aggTb][$aggCol]['binding'] !== 'd' &&
                                    $tables[$aggTb][$aggCol]['binding'] !== 'i')
                            ) {
                                cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` must have a Binding of 'd' or 'i' for Table `$aggTb`! (it has `{$tables[$aggTb][$aggCol]['binding']}`)");
                                cli_info_without_exit("Valid Bindings for Column `$aggCol` are: 'd' (double) or 'i' (integer) for SUM and AVG Aggregate Functions!");
                                cli_info("SUM() and AVG() Aggregate Functions can only be used on Numeric Columns ('d' or 'i' as binding)!");
                            }
                            // Only add if not already in the currently selected tables and also
                            // so you can use multiple agg functions on the same table without
                            // JOINS_ON complaining about "mulitple tables selected"!
                            if (!in_array($aggTb, $currentlySelectedTbs, true)) {
                                $currentlySelectedTbs[] = $aggTb;
                            }
                            $as_name = $aggFuncValidStarts[$aggFunc] . $tables[$aggTb][$aggCol]['joined_name'];
                            $i = 0;
                            while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                                $i++;
                                $as_name = $as_name . "_$i";
                            }
                            $aggAliases[] = $as_name;
                            $allAliases[] = $as_name;
                            $selectedCols[$aggTb][$as_name] = $aggCol;
                            $selectedCols[$aggTb][$tables[$aggTb][$aggCol]['joined_name']] = $aggCol;
                            $aliasesTbCol[$as_name] = [
                                'tb' => $aggTb,
                                'col' => $aggCol,
                            ];
                            $aliasesTbCol[$tables[$aggTb][$aggCol]['joined_name']] = [
                                'tb' => $aggTb,
                                'col' => $aggCol,
                            ];
                            // We remove "=" if it is inside of $aggFunc
                            // which is special case for COUNT(DISTINCT=)
                            if (str_contains($aggFunc, '=')) {
                                $aggFunc = str_replace('=', ' ', $aggFunc);
                            }
                            $selectedTbsColsStr .= strtoupper($aggFunc) . "$aggTb.$aggCol) AS " . $as_name . ",\n";
                            continue;
                        } else {
                            cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$aggTb`!");
                            cli_info("Valid Column Names for Table `$aggTb` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$aggTb]))) . ".");
                        }
                    } else {
                        cli_err_syntax_without_exit("Table Name `$aggTb` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `tables.php` File!");
                        cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
                    }
                }
                // When it failed to match despite matching the start regex, we error out
                else {
                    cli_err_syntax_without_exit("Invalid Aggregate Function Format (`$selectTbName`) in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info_without_exit("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                    cli_info("Check for Correct Punctuation such as:`table:column` and NOT:`table.column`!");
                }
            }
            // CASE 2: Only Table Name (no columns) is given so
            // add all columns from that table if it is valid table!
            elseif (
                !str_contains($selectTbName, ":")
                && !str_contains($selectTbName, "!")
                && !str_contains($selectTbName, ",")
            ) {
                if (!in_array($selectTbName, $selectTbs, true)) {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                }
                // Table exists, so we add all columns from that table.
                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                    $currentlySelectedTbs[] = $selectTbName;
                    foreach ($tables[$selectTbName] as $colKey => $singleTbCols) {
                        $selectedTbsColsStr .= $selectTbName . ".$colKey AS " . $singleTbCols['joined_name'] . ",\n";
                        $allAliases[] = $singleTbCols['joined_name'];
                        $selectedCols[$selectTbName][$singleTbCols['joined_name']] = $colKey;
                        // $aliasesTbCol[$as_name] = [ // DO I REALLY NEED THIS? Since $as_name is for agg Funcs?
                        //     'tb' => $selectTbName,
                        //     'col' => $colKey,
                        // ];
                        $aliasesTbCol[$singleTbCols['joined_name']] = [
                            'tb' => $selectTbName,
                            'col' => $colKey,
                        ];
                    }
                } else {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` has no columns defined in `config/tables.php`!");
                    cli_info("Make sure the Table `$selectTbName` has columns defined in `config/tables.php` file!");
                }
                continue;
            }
            // CASE 3: Table Name with "!" (excludes column) so kinda like above but
            // but we exclude the spelled out Columns and include the rest from Table.
            // This means Aggregate Functions are not available to use here!
            elseif (
                str_contains($selectTbName, "!:")
            ) {
                // Split the Table Name and Column(s) by "!:"
                [$selectTbName, $excludedCols] = explode("!:", $selectTbName, 2);

                // We check if Table exists otherwise we error out
                if (!in_array($selectTbName, $selectTbs, true)) {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                }

                // $excludedCols becomes an array and is also split on "," if multiple columns are excluded
                $excludedCols = str_contains($excludedCols, ",") ? explode(",", $excludedCols) : [$excludedCols];

                // Table exists, so we add all columns from that table.
                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                    $currentlySelectedTbs[] = $selectTbName;
                    // First we check that the excluded columns are valid meaning that they should exist
                    // in table, we just do not wanna select/include them in the SQL String.
                    foreach ($excludedCols as $excludedCol) {
                        // If the excluded column is not a string or empty, we error out
                        if (!is_string_and_not_empty($excludedCol)) {
                            cli_err_syntax_without_exit("Invalid Excluded Column Name in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("Excluded Column Names must be Non-Empty Strings!");
                        }
                        // You are NOT supposed to use Aggregate Functions here since
                        // you are defining what columns to exclude from the table!
                        if (preg_match($aggregateFunctionsStart, $excludedCol)) {
                            cli_err_syntax_without_exit("Aggregate Functions (`$excludedCol`) are NOT allowed in Syntax `$selectTbName:" . implode(",", $excludedCols) .  "` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info_without_exit("You can ONLY Exclude Columns from the Table when using the Syntax `table!:col1,col2,etc`, NOT add Aggregate Functions to them! (like `SUM(col1)`, `AVG(col2)` etc.)");
                            cli_info("To use Aggregate Functions, you must use the Syntax `table:AGG_FUNC(col1),col2,etc` OR `AGG_FUNC(table:col)` instead!");
                        }
                        // If the excluded column is not in the table, we error out
                        if (!array_key_exists($excludedCol, $tables[$selectTbName])) {
                            cli_err_syntax_without_exit("Excluded Column Name `$excludedCol` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$selectTbName`!");
                            cli_info("Valid Column Names for Table `$selectTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$selectTbName]))) . ".");
                        }
                    }

                    foreach ($tables[$selectTbName] as $colKey => $singleTbCols) {
                        // Only add the column if it is NOT in the excluded columns array
                        if (!in_array($colKey, $excludedCols, true)) {
                            $selectedTbsColsStr .= $selectTbName . ".$colKey AS " . $singleTbCols['joined_name'] . ",\n";
                            $allAliases[] = $singleTbCols['joined_name'];
                            $selectedCols[$selectTbName][$singleTbCols['joined_name']] =  $colKey;
                            // $aliasesTbCol[$as_name] = [ // DO I REALLY NEED THIS? Since $as_name is for agg Funcs?
                            //     'tb' => $selectTbName,
                            //     'col' => $colKey,
                            // ];
                            $aliasesTbCol[$singleTbCols['joined_name']] = [
                                'tb' => $selectTbName,
                                'col' => $colKey,
                            ];
                        }
                    }
                } else {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` has no columns defined in `config/tables.php`!");
                    cli_info("Make sure the Table `$selectTbName` has columns defined in `config/tables.php` file!");
                }
                continue;
            }
            // CASE 4: Table Name with ":" (selects specific columns) so
            elseif (
                str_contains($selectTbName, ":")
                && !str_contains($selectTbName, "!")
                && !str_contains($selectTbName, "!:")
            ) {
                // If the string ends with ":" after removing all whitespace, we error out
                if (str_ends_with(trim($selectTbName), ":")) {
                    cli_err_syntax_without_exit("Invalid Table Name Format in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Table Name Formats are:\n`table_name` (this selects all columns!) OR\n`table_name:col1,col2,col3` (this selects only these 3 columns!) OR\n`table_name!:col1` (this selects all columns except `col1`!)");
                }
                // Split the Table Name and Column(s) by "!:"
                [$selectTbName, $includedCols] = explode(":", $selectTbName, 2);

                // We check if Table exists otherwise we error out
                if (!in_array($selectTbName, $selectTbs, true)) {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                }

                // $includedCols becomes an array and is also split on "," if multiple columns are included
                $includedCols = str_contains($includedCols, ",") ? explode(",", $includedCols) : [$includedCols];
                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                    $currentlySelectedTbs[] = $selectTbName;
                    foreach ($includedCols as $includedCol) {
                        if (!is_string_and_not_empty($includedCol)) {
                            cli_err_syntax_without_exit("Invalid Included Column Name in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("Included Column Names must be Non-Empty Strings!");
                        }

                        // SPECIAL CASE FOR EACH `col` in `table` that could have Agg Function as part of its name!
                        // Like: `authors:id,AVG(age)`, so we need to handle that too!
                        if (preg_match($aggregateFunctionsStart, $includedCol)) {
                            if (preg_match($aggFuncRegex, $includedCol, $aggFuncMatches)) {
                                $aggFunc = $aggFuncMatches[1];
                                $aggTbWithCol = $aggFuncMatches[2];

                                // Incorrect Aggregate Function Format despite matching
                                if (!isset($aggFuncValidStarts[$aggFunc])) {
                                    cli_err_syntax_without_exit("Invalid Aggregate Function Format (`$includedCol`) in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                    cli_info("The Aggregate Function must start with one of the following:\n" . implode(",\n", quotify_elements(array_keys($aggFuncValidStarts))) . "!");
                                }

                                // SPECIAL CASE: COUNT(*) which is a special case of the COUNT function and thus we must get
                                // the table name from the $fromTb variable since it does not have a table name!
                                if ($aggFuncMatches[0] === 'count(*)') {
                                    if (!in_array($selectTbName, $selectTbs, true)) {
                                        cli_err_syntax_without_exit("Table Name `$selectTbName` from `FROM` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                                        cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . " OR add it to the `<TABLES>` Key in SQL Array `$handlerFile.php=>$fnName` and recompile!");
                                    }

                                    // Rename and add to the selectedTbsColsStr and the aggAliases
                                    // so you can reuse same aggregate function on the same table
                                    // without conflicting with the aliases!
                                    $as_name = $aggFuncValidStarts[$aggFunc] . $selectTbName;
                                    $i = 0;
                                    while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                                        $i++;
                                        $as_name = $as_name . "_$i";
                                    }
                                    $aggAliases[] = $as_name;
                                    $allAliases[] = $as_name;
                                    $selectedCols[$selectTbName][$as_name] = $selectTbName;
                                    $aliasesTbCol[$as_name] = [
                                        'tb' => $selectTbName,
                                        'col' => '*',
                                    ];
                                    $selectedTbsColsStr .= strtoupper($aggFunc) . "*) AS " . $as_name . ",\n";
                                    continue;
                                }

                                // Incorrect Column Format despite matching
                                if (!preg_match($aggColRegex, $aggTbWithCol)) {
                                    cli_err_syntax_without_exit("Invalid Table/Column Format (`$aggTbWithCol`) in Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                    cli_info("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                                }

                                // We are reusing something that otherwise wouldn't know the table name
                                // so we try rewrite as little as possible so just reassign the variables
                                $aggCol = $aggTbWithCol;
                                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                                    // Validate correct Table:Column Binding (d or i when SUM() or AVG() is used!)
                                    if (isset($tables[$selectTbName][$aggCol]) && is_array_and_not_empty($tables[$selectTbName][$aggCol])) {
                                        if (($aggFunc === 'sum(' || $aggFunc === 'avg(') &&
                                            ($tables[$selectTbName][$aggCol]['binding'] !== 'd' &&
                                                $tables[$selectTbName][$aggCol]['binding'] !== 'i')
                                        ) {
                                            cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` must have a Binding of 'd' or 'i' for Table `$selectTbName`! (it has `{$tables[$selectTbName][$aggCol]['binding']}`)");
                                            cli_info_without_exit("Valid Bindings for Column `$aggCol` are: 'd' (double) or 'i' (integer) for SUM and AVG Aggregate Functions!");
                                            cli_info("SUM() and AVG() Aggregate Functions can only be used on Numeric Columns ('d' or 'i' as binding)!");
                                        }

                                        // Prepare correct alias name for the aggregate function
                                        $as_name = $aggFuncValidStarts[$aggFunc] . $tables[$selectTbName][$aggCol]['joined_name'];
                                        $i = 0;
                                        while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                                            $i++;
                                            $as_name = $as_name . "_$i";
                                        }
                                        $aggAliases[] = $as_name;
                                        $allAliases[] = $as_name;
                                        $selectedCols[$selectTbName][$as_name] = $aggCol;
                                        $aliasesTbCol[$as_name] = [
                                            'tb' => $selectTbName,
                                            'col' => $aggCol,
                                        ];
                                        $aliasesTbCol[$tables[$selectTbName][$aggCol]['joined_name']] = [
                                            'tb' => $selectTbName,
                                            'col' => $aggCol,
                                        ];
                                        // We remove "=" if it is inside of $aggFunc
                                        // which is special case for COUNT(DISTINCT=)
                                        if (str_contains($aggFunc, '=')) {
                                            $aggFunc = str_replace('=', ' ', $aggFunc);
                                        }
                                        $selectedTbsColsStr .= strtoupper($aggFunc) . "$selectTbName.$aggCol) AS " . $as_name . ",\n";
                                        continue;
                                    } else {
                                        cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$selectTbName`!");
                                        cli_info("Valid Column Names for Table `$selectTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$selectTbName]))) . ".");
                                    }
                                }
                            }
                            // When it failed to match despite matching the start regex, we error out
                            else {
                                cli_err_syntax_without_exit("Invalid Aggregate Function Format in Column `$includedCol` for Table `$selectTbName` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                cli_info_without_exit("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                                cli_info("Check for Correct Punctuation such as:`table:column` and NOT:`table.column`!");
                            }
                        }

                        // Otherwise, just check as regular Table Column!
                        if (!array_key_exists($includedCol, $tables[$selectTbName])) {
                            cli_err_syntax_without_exit("Included Column Name `$includedCol` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$selectTbName`!");
                            cli_info("Valid Column Names for Table `$selectTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$selectTbName]))) . ".");
                        }

                        // Here we just add the column to the selectedTbsColsStr since we know it exists!
                        $selectedTbsColsStr .= $selectTbName . ".$includedCol AS " . $tables[$selectTbName][$includedCol]['joined_name'] . ",\n";
                        $allAliases[] = $tables[$selectTbName][$includedCol]['joined_name'];
                        $selectedCols[$selectTbName][$tables[$selectTbName][$includedCol]['joined_name']] = $includedCol;
                        $aliasesTbCol[$tables[$selectTbName][$includedCol]['joined_name']] = [
                            'tb' => $selectTbName,
                            'col' => $includedCol,
                        ];
                        continue;
                    }
                } else {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` has no Columns defined in the `tables.php` File!");
                    cli_info("Make sure the Table `$selectTbName` has columns defined in `config/tables.php` File!");
                }
                continue;
            }
            // No valid Table Name Format found in `SELECT` Key so we error out
            else {
                cli_err_syntax_without_exit("Invalid Table Name Format in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("Valid Table Name Formats are:\n`table_name` (this selects all columns!) OR\n`table_name:col1,col2,col3` (this selects only these 3 columns!) OR\n`table_name!:col1` (this selects all columns except `col1`!)");
            }
        }
        // Remove last "," from the $selectedTbsColsStr if it exists
        if (str_ends_with($selectedTbsColsStr, ",\n")) {
            $selectedTbsColsStr = substr($selectedTbsColsStr, 0, -2);
        }
        // Iterate through Table ($selTb) and if we do NOT find `id` Column in it, we just
        // add it to the selectedCols $selectedCols[$selTb][] = 'id'; and to the start of
        // the $selectedTbsColsStr so it is always selected by default! We also write WARNING
        // that it has been added because it was not since it is really NOT useful to NOT use it!
        foreach ($selectedCols as $selTb => $selCol) {
            if (!array_key_exists('id', $selCol)) {
                $selectedCols[$selTb]['id'] = 'id'; // Add 'id' Column to the selectedCols
                $selectedTbsColsStr = "$selTb.id AS $selTb" . "_id,\n" . $selectedTbsColsStr; // Add it to the start of the selectedTbsColsStr
                cli_warning_without_exit("Table `$selTb` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` did not have `id` Column so it was added by default! (`$selTb.id AS $selTb`)");
            }
        }

        // PARSING THE "FROM" Key (the primary table to select from/join/work with)
        // We already know FROM Key is a valid string and not empty so now we check
        // if the FROM table is in the SELECT tables and if it is not, we error out
        if (!in_array($fromTb, $currentlySelectedTbs, true)) {
            cli_err_syntax_without_exit("Table Name `$fromTb` from `FROM` Key in SQL Array `$handlerFile.php=>$fnName` not found in `SELECT` Key!");
            cli_info("Currently SELECTed Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . " and it needs the Table `$fromTb` included somewhere!");
        }

        // PARSING THE "JOINS_ON" Key (the JOINs to perform)
        // We check if it is empty and if it is then $currentlySelectedTbs
        // must only be 1 since you must use JOIN when selecting more
        // than 1 Table in current version of FunkPHP!
        if (
            count($currentlySelectedTbs) > 1
            && (!isset($joinsTb)
                || !is_array($joinsTb)
                || !array_is_list($joinsTb)
                || empty($joinsTb))
        ) {
            cli_err_syntax_without_exit("No `JOINS_ON` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info_without_exit("The `JOINS_ON` Key must be a Non-Empty Array and must be used when you are SELECTing more than 1 Table in the `SELECT` Key!");
            cli_info("IMPORTANT: You can use `JOINS_ON` Key to Join several Tables while still just SELECTing from 1 Table in the `SELECT` Key!");
        }

        // When we are SELECTing more than 1 Table, we must use JOINs to
        // join them together so here we start parsing the JOINS_ON Key!
        $tables_were_joined = false;
        if (isset($joinsTb) && !empty($joinsTb)) {
            if (is_string($joinsTb) && str_starts_ends_with($joinsTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `JOINS_ON` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            if (!is_array($joinsTb) || !array_is_list($joinsTb)) {
                cli_err_syntax_without_exit("No `JOINS_ON` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `JOINS_ON` Key must be a Non-Empty List Array representing the JOINs to perform between the Tables in the `SELECT` Key when more than one Table is being SELECTed!");
            }
            $joinsREGEX = '/^([a-zA-Z]+)=([a-zA-Z_0-9]+),([a-zA-Z_0-9]+)\(([a-zA-Z_0-9]+)\),([a-zA-Z_0-9]+)\(([a-zA-Z_0-9]+)\)$/i';
            $joinedTables[] = $fromTb; // We always join the FROM table first

            // Iterate through each joinTb in the JOINS_ON Key
            foreach ($joinsTb as $joinTb) {
                // If the joinTb is not a string or empty, we error out
                if (!is_string_and_not_empty($joinTb)) {
                    cli_err_syntax_without_exit("Invalid Join Table String found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Each Join Table String must be a Non-Empty String representing the JOIN condition between two Tables!");
                }
                // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
                // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
                if (str_starts_ends_with($joinTb, "{", "}")) {
                    cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `JOINS_ON` Key!");
                    cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
                }

                // If the joinTb does not match the regex, we error out
                if (!preg_match($joinsREGEX, $joinTb, $matches)) {
                    cli_err_syntax_without_exit("Invalid Join Table String found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Each Join Table Array Element String must match the following format:\n`join_type=join_table,table2(table2Col),table1(table1Col)`\nExample: `inner=articles,authors(id),articles(author_id)`");
                }

                // We extract Join Type, TB names
                // and Columns from the matches
                [
                    $joinType,
                    $joinTable,
                    $table1,
                    $table1Col,
                    $table2,
                    $table2Col
                ] = array_slice($matches, 1);

                // We lowercase all variables to ensure consistency
                $joinType = strtolower($joinType);
                $joinTable = strtolower($joinTable);
                $table1 = strtolower($table1);
                $table2 = strtolower($table2);
                $table1Col = strtolower($table1Col);
                $table2Col = strtolower($table2Col);

                // We check if the joinType is valid based on list of valid join types
                if (!isset($validJoinTypes[$joinType])) {
                    cli_err_syntax_without_exit("Invalid Join Type `$joinType` found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Join Types are:\n" . implode(",\n", quotify_elements(array_keys($validJoinTypes))) . "! (based on \$validJoinTypes)");
                }

                // We check that $joinTable is a valid table name
                if (!array_key_exists($joinTable, $tables)) {
                    cli_err_syntax_without_exit("Invalid Join Table Name `$joinTable` found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . "! (based on `tables.php` File)");
                }

                // We check that $joinTable is not already joined
                // to avoid joining the same table multiple times
                if (in_array($joinTable, $joinedTables, true)) {
                    cli_err_syntax_without_exit("Table `$joinTable` is already marked as joined in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info_without_exit("You can only join a Table once in the `JOINS_ON` Key! (based on `tables.php` File)");
                    cli_info_without_exit("Current Joined Tables are: " . implode("->", quotify_elements($joinedTables)) . "!");
                    cli_info("IMPORTANT: During SQL Handler Function Generation, you ALWAYS get all possible Relationships (all Tables that can be joined), but You have to choose unique ones after each `Join Type=`!");
                }
                // Otherwise add it to the joined tables
                $joinedTables[] = $joinTable;

                // We add tables and their references that can be used by <Hydration> Key later!
                if ($table1Col === substr($table1, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table1] = substr($table1, 0, -1) . "_id";
                } elseif ($table1Col === substr($table2, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table1] = substr($table2, 0, -1) . "_id";
                } elseif ($table2Col === substr($table2, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table2] = substr($table2, 0, -1) . "_id";
                } elseif ($table2Col === substr($table1, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table2] = substr($table1, 0, -1) . "_id";
                }

                // We check that $table2 and $table1 are valid tables
                if (!array_key_exists($table2, $tables) || !array_key_exists($table1, $tables)) {
                    cli_err_syntax_without_exit("Invalid Table Name(s) (`$table2` or `$table1`) found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . "! (based on `tables.php` File)");
                }

                // We check that $table2 actually has any relationships defined
                if (!isset($relationships[$table2]) || !is_array($relationships[$table2]) || empty($relationships[$table2])) {
                    cli_err_syntax_without_exit("Table `$table2` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query has no relationships defined in `config/tables.php` File! ('relationships' Key)");
                    cli_info("Make sure the Table `$table2` has relationships defined in `config/relationships.php` file!");
                }

                // We check $table2 has a relationship with $table1
                if (!array_key_exists($table1, $relationships[$table2])) {
                    cli_err_syntax_without_exit("Table `$table2` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query has no relationship with Table `$table1` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }

                // We get the related table from the relationships array
                // and check that it is valid by it having keys 'local_column',
                // 'foreign_column', 'local_table', 'foreign_table' & 'direction'
                // and that all those are non-emtpy string! Also, the key
                // 'direction' must be either 'pk_to_fk' or 'fk_to_pk'!
                $relatedTable = $relationships[$table2][$table1];
                if (
                    !is_array_and_not_empty($relatedTable)
                    || !array_key_exists('local_column', $relatedTable)
                    || !array_key_exists('foreign_column', $relatedTable)
                    || !array_key_exists('local_table', $relatedTable)
                    || !array_key_exists('foreign_table', $relatedTable)
                    || !array_key_exists('direction', $relatedTable)
                    || !is_string_and_not_empty($relatedTable['local_column'])
                    || !is_string_and_not_empty($relatedTable['foreign_column'])
                    || !is_string_and_not_empty($relatedTable['local_table'])
                    || !is_string_and_not_empty($relatedTable['foreign_table'])
                    || !is_string_and_not_empty($relatedTable['direction'])
                    || ($relatedTable['direction'] !== 'pk_to_fk'
                        && $relatedTable['direction'] !== 'fk_to_pk')
                ) {
                    cli_err_syntax_without_exit("Invalid Relationship Definition found in `config/relationships.php` File for Table `$table2` with Table `$table1`!");
                    cli_info("Each Relationship Definition must have the Keys: 'local_column', 'foreign_column', 'local_table', 'foreign_table' & 'direction' as Non-Empty Strings!");
                }

                // When $table1 does not match any of the local or foreign tables in the relationship
                // we error out since it must be one of the two tables in the relationship!
                if ($table1 !== $relatedTable['local_table'] && $table1 !== $relatedTable['foreign_table']) {
                    cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Table (`{$relatedTable['local_table']}`) or Foreign Table (`{$relatedTable['foreign_table']}`) in the relationship with Table `$table2` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }
                // We check same for $table2
                if ($table2 !== $relatedTable['local_table'] && $table2 !== $relatedTable['foreign_table']) {
                    cli_err_syntax_without_exit("Table `$table2` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Table (`{$relatedTable['local_table']}`) or Foreign Table (`{$relatedTable['foreign_table']}`) in the relationship with Table `$table1` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table1` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }
                // We now have two possible cases and can check the rest based on whatever $table1 is the local or foreign table in the relationship
                if ($table1 === $relatedTable['local_table']) {
                    // This means $table1Col should be the local column
                    // and $table2Col should be the foreign column
                    // and $table2 should be the foreign table
                    if (
                        $table2 !== $relatedTable['foreign_table']
                        || $table2Col !== $relatedTable['foreign_column']
                        || $table1Col !== $relatedTable['local_column']
                    ) {
                        cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Column (`{$relatedTable['local_column']}`) or Foreign Column (`{$relatedTable['foreign_column']}`) in the Relationship with Table `$table2` defined in `config/relationships.php` File!");
                        cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                    }
                } elseif ($table1 === $relatedTable['foreign_table']) {
                    // This means $table1Col should be the foreign column
                    // and $table2Col should be the local column
                    // and $table2 should be the local table
                    if (
                        $table2 !== $relatedTable['local_table']
                        || $table2Col !== $relatedTable['local_column']
                        || $table1Col !== $relatedTable['foreign_column']
                    ) {
                        cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Column (`{$relatedTable['local_column']}`) or Foreign Column (`{$relatedTable['foreign_column']}`) in the Relationship with Table `$table2` defined in `config/relationships.php` File!");
                        cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                    }
                }
                // This means error out since $table1 is not a valid table in the relationship
                else {
                    cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Table (`{$relatedTable['local_table']}`) or Foreign Table (`{$relatedTable['foreign_table']}`) in the relationship with Table `$table2` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }

                // Finally we build the JOIN String based on the joinType
                // and the tables and columns we have validated above.
                $joinType = $validJoinTypes[$joinType];
                $joinsStr .= "$joinType $joinTable ON $table1.$table1Col = $table2.$table2Col ";
            }

            // You cannot SELECT more Tables than you have
            // joined (but the reverse is possible!)
            if (count($currentlySelectedTbs) > count($joinedTables)) {
                cli_err_syntax_without_exit("You cannot SELECT more Tables than you have joined in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info_without_exit("Currently Selected Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . ".");
                cli_info_without_exit("Currently Joined Tables are: `" . implode("->", $joinedTables) . "`.");
                cli_info("Following Tables must be joined in `JOINS_ON` Key:\n" . implode(",\n", quotify_elements(array_diff($currentlySelectedTbs, $joinedTables))) . " OR remove them from the `SELECT` Key!");
            }

            // Remove last ",\n" from the $joinsStr if it exists
            if (str_ends_with($joinsStr, " ")) {
                $joinsStr = substr($joinsStr, 0, -1);
            }
            // If we reached here we can set tables_were_joined to true
            // so hydration parsing can know this at the end!
            $tables_were_joined = true;
        }

        // PARSING THE OPTIONAL "WHERE" Key (the WHERE clause to filter results) using Condition Clause Function if the "WHERE" Key is a Non-Empty String!
        $whereStr = isset($whereTb) && is_string($whereTb) && !empty($whereTb) ? cli_parse_condition_clause_sql($configTBKey, $whereTb, "SELECT", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "WHERE", $aggAliases) : "";

        // PARSING THE OPTIONAL Keys "GROUP_BY" & "HAVING" (the GROUP BY clause
        // to group results and HAVING clause to filter grouped results)
        if (isset($groupByTb) && is_string($groupByTb) && !empty($groupByTb)) {
            if (!is_string($groupByTb)) {
                cli_err_syntax_without_exit("Invalid `GROUP BY` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `GROUP BY` Key must be a String representing the Columns to Group by! (leave empty or remove if not used)\nSyntax Examples:\n`table:col1,col2`|`col1,col2` (to Group by Columns from a Single Table) OR\n`table1:col1|table2:col2` (to Group by Columns from Multiple Tables).");
            }
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (str_starts_ends_with($groupByTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `GROUP BY` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // We split $groupByTb on "|" if it exists
            // or just turn single string into an array!
            $groupByTb = trim($groupByTb);
            if (str_contains($groupByTb, "|")) {
                $groupByTb = explode("|", $groupByTb);
            } else {
                $groupByTb = [$groupByTb];
            }

            // Iterate through to validate that we can actually group by specific
            // `table:col` OR `table:col` OR `col` (when single Table)!
            foreach ($groupByTb as $groupTB) {
                $groupTB = strtolower($groupTB); // Lowercase for consistency
                // CASE 1: Does NOT include ":"
                if (!str_contains($groupTB, ":")) {
                    // We split on "," if it exists
                    // or just turn single string into an array!
                    if (str_contains($groupTB, ",")) {
                        $groupColNames = explode(",", $groupTB);
                    } else {
                        $groupColNames = [$groupTB];
                    }
                    // Validate that the column names exist in the currently selected tables
                    foreach ($groupColNames as $groupColName) {
                        // If the column name is not a string or empty, we error out
                        if (!is_string_and_not_empty($groupColName)) {
                            cli_err_syntax_without_exit("Invalid Column Name in `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("Column Names must be Non-Empty Strings!");
                        }
                        // If the Column Name is not in the `uniqueCols` array that means they need to manually write `table:col`
                        if (!in_array($groupColName, $cols['uniqueCols'], true)) {
                            cli_err_syntax_without_exit("Column Name `$groupColName` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in Unique Column Names Array! (making it ambiguous)");
                            cli_info_without_exit("Valid Unique Column Names are:\n" . implode(",\n", quotify_elements($cols['uniqueCols'])) . ".");
                            cli_info("Alternatively, you can rewrite it as `table:col1,col2,etc` to Group one ore more Column(s) from a Specific Table!");
                        }
                    }
                    $groupByStr .= implode(",", $groupColNames) . ",\n";
                    continue;
                }
                // CASE 2: Includes ":" so we split on ":"
                elseif (str_contains($groupTB, ":")) {
                    [$groupTbName, $groupColNames] = explode(":", $groupTB, 2);
                    // Validate $groupTbName exists EITHER in the `SELECT` Key or in
                    // the `JOINS_ON` Key. That is; it has been SELECTed or JOINed!
                    if (
                        !in_array($groupTbName, $currentlySelectedTbs, true)
                        && !in_array($groupTbName, $joinedTables, true)
                    ) {
                        cli_err_syntax_without_exit("Table Name `$groupTbName` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in `SELECT` Key!");
                        cli_info("Currently SELECTed Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . ".");
                    }
                    // Validate $groupColName exists in the table when it is a single column
                    if (!str_contains($groupColNames, ",") && !array_key_exists($groupColNames, $tables[$groupTbName])) {
                        cli_err_syntax_without_exit("Column Name `$groupColNames` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$groupTbName`!");
                        cli_info("Valid Column Names for Table `$groupTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$groupTbName]))) . ".");
                    }
                    // Validate $groupColNames exists in the table when it is multiple columns (so we split on ",")
                    // and check every column name on the same table as with the single column case!
                    if (str_contains($groupColNames, ",")) {
                        $groupColNames = explode(",", $groupColNames);
                        foreach ($groupColNames as $groupColName) {
                            if (!is_string_and_not_empty($groupColName)) {
                                cli_err_syntax_without_exit("Invalid Column Name in `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                cli_info("Column Names must be Non-Empty Strings!");
                            }
                            if (!array_key_exists($groupColName, $tables[$groupTbName])) {
                                cli_err_syntax_without_exit("Column Name `$groupColName` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$groupTbName`!");
                                cli_info("Valid Column Names for Table `$groupTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$groupTbName]))) . ".");
                            }
                        }
                        // All Columns are VALID for this table, so let's build the GROUP BY String
                        // by iterating through each again and adding `table.Col`
                        foreach ($groupColNames as $groupColName) {
                            $groupByStr .= "$groupTbName.$groupColName, ";
                        }
                        $groupByStr .= "\n";
                        continue;
                    }

                    // We end up here when only a single column is specified
                    // like `table:col` so we just add it directly!
                    $groupByStr .= "$groupTbName.$groupColNames,\n";
                    continue;
                }
            }
            // Remove ", " from the end of $groupByStr if it exists
            if (str_ends_with($groupByStr, ",\n")) {
                $groupByStr = substr($groupByStr, 0, -2);
            }
        }
        if (isset($havingTb)) {
            if (!is_string($havingTb)) {
                cli_err_syntax_without_exit("Invalid `HAVING` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `HAVING` Key must be a String representing the Condition(s) to Filter Grouped Results! (leave empty or remove if not used)");
            }
            // If GROUP BY was never correctly parsed (or added at all) we error out since
            // HAVING is dependent on GROUP BY being present!
            if (is_string($havingTb) && !empty($havingTb) && empty($groupByStr)) {
                cli_err_syntax_without_exit("No `GROUP BY` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `HAVING` Key must be used with a Non-Empty `GROUP BY` Key to Filter Grouped Results!");
            }
            $havingStr = isset($havingTb) && is_string($havingTb) && !empty($havingTb) ? cli_parse_condition_clause_sql($configTBKey, $havingTb, "SELECT", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "HAVING", $aggAliases, $aliasesTbCol) : "";
        }

        // PARSING THE OPTIONAL "ORDER BY" Key (the ORDER BY clause to sort results)
        if (isset($orderByTb)) {
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (str_starts_ends_with($orderByTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `ORDER BY` Key!");
                cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // Must be a string it if is set, otherwise we error out
            if (!is_string($orderByTb)) {
                cli_err_syntax_without_exit("Invalid `ORDER BY` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `ORDER BY` Key must be a String representing the Columns to Order by! (leave empty or remove if not used)");
            }
            // Only start parsing when it is a Non-Empty String!
            elseif (is_string($orderByTb) && !empty($orderByTb)) {
                // Trim string, then split on "|" if it exists
                // or just turn single string into an array!
                $orderByTb = trim($orderByTb);
                if (str_contains($orderByTb, "|")) {
                    $orderByTb = explode("|", $orderByTb);
                } else {
                    $orderByTb = [$orderByTb];
                }
                // Iterate through to validate that we can actually order by specific
                // `table:col,ASC|DESC` OR `table:col ASC|DESC` OR `col,ASC|DESC`!
                // "NULLS FIRST" & "NULLS LAST" are not supported yet in FunkPHP!
                $notSupportedYet = ["NULLS FIRST", "NULLS LAST"];
                $orderByRegex = "/^(([a-zA-Z0-9_]+):)*(\?|[a-zA-Z0-9_]+)[ |,]{1}(ASC|DESC)$/i";
                foreach ($orderByTb as $obTB) {
                    if (str_contains($obTB, "?")) {
                        cli_err_syntax_without_exit("`ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query contains the `?` Placeholder!");
                        cli_info_without_exit("The `ORDER BY` Key does NOT support `?` Placeholder Syntax!");
                        cli_info("Following SQL Key(s) Support the Placeholder `?`:\n" . implode(",\n", quotify_elements(["WHERE", "HAVING", "SET", "VALUES"])) . ".");
                    }
                    // If $notSupportedYet is found in current string, we error out
                    if (str_contains($obTB, $notSupportedYet[0]) || str_contains($obTB, $notSupportedYet[1])) {
                        cli_err_syntax_without_exit("`ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query contains Unsupported Syntax: `$obTB`!");
                        cli_info("The `ORDER BY` Key does NOT yet support `NULLS FIRST` or `NULLS LAST` Syntax in FunkPHP!");
                    }
                    // Check preg_match for current $obTB against the regex
                    if (!preg_match($orderByRegex, $obTB, $matches)) {
                        cli_err_syntax_without_exit("Invalid `ORDER BY` Key value `$obTB` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        cli_info("The `ORDER BY` Key must be a String representing the Columns to Order by in the Format: `table:col ASC|DESC`, `table:col,ASC|DESC`, `col ASC|DESC` or `col,ASC|DESC`!");
                    }
                    $obTable = isset($matches[2]) ? $matches[2] : ""; // Table Name if exists
                    $obCol = $matches[3] ?? null; // Column Name
                    $obOrder = $matches[4] ?? null; // Order (ASC or DESC)

                    // If $obTable is empty we assume a unique column OR
                    // its possible alias from $allAliases Array!
                    if (empty($obTable)) {
                        // If the column is not in the unique columns or aliases, we error out
                        if (!in_array($obCol, $cols['uniqueCols'], true) && !in_array($obCol, $allAliases, true)) {
                            cli_err_syntax_without_exit("Column `$obCol` from `ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query not found in Unique Columns or Aliases! (not being unique means it is ambiguous to what Table it belongs to)");
                            cli_info_without_exit("Valid Unique Columns are:\n" . implode(",\n", quotify_elements($cols['uniqueCols'])) . ".");
                            cli_info("Valid Unique Aliases are:\n" . implode(",\n", quotify_elements($allAliases)) . ".");
                        }
                        // Otherwise we add it to the order by string
                        $orderByStr .= "$obCol $obOrder, ";
                    } else {
                        // If the table is not in the currently selected tables, we error out
                        if (!in_array($obTable, $currentlySelectedTbs, true)) {
                            cli_err_syntax_without_exit("Table `$obTable` from `ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query not found in `SELECT` Key!");
                            cli_info("Currently SELECTed Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . ".");
                        }
                        // If the column is not in the table columns, we error out
                        if (!array_key_exists($obCol, $tables[$obTable])) {
                            cli_err_syntax_without_exit("Column `$obCol` from `ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query not found in Table `$obTable`!");
                            cli_info("Valid Columns for Table `$obTable` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$obTable]))) . ".");
                        }
                        // Otherwise we add it to the order by string
                        $orderByStr .= "$obTable.$obCol $obOrder, ";
                    }
                }

                // Remove ", " from the end of the $orderByStr if it exists
                if (str_ends_with($orderByStr, ", ")) {
                    $orderByStr = substr($orderByStr, 0, -2);
                }
            }
        }

        // PARSING THE OPTIONAL Keys: "LIMIT" & "OFFSET" Key (LIMIT is number of rows, OFFSET is where you start from results)
        if (isset($limitTb)) {
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (is_string($limitTb) && str_starts_ends_with($limitTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `LIMIT` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // 'LIMIT' key does allow a '?' as a placeholder for the limit value
            if ($limitTb === '?') {
                $limitStr = '?';
                $builtBindedParamsString .= "i";
                if (!in_array("limit", $builtFieldsArray, true)) {
                    $builtFieldsArray[] = "limit";
                } else {
                    $i = 1;
                    while (in_array("limit_$i", $builtFieldsArray, true)) {
                        $i++;
                    }
                    $builtFieldsArray[] = "limit_$i";
                }
            } elseif (is_string($limitTb) && strlen($limitTb) === 0) { // This is not error out on an Allowed Empty String!
            } elseif (is_numeric($limitTb) && (int)$limitTb >= 0) { // Must Be Positive Integer!
                $limitStr = (string)$limitTb;
            } else {
                cli_err_syntax_without_exit("Invalid `LIMIT` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `LIMIT` Key must be a Non-Negative Integer, or an Empty String (`''`) to omit it. (or just remove the entire Key)");
            }
        }
        if (isset($offsetTb)) {
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (is_string($offsetTb) && str_starts_ends_with($offsetTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `OFFSET` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // 'LIMIT' key does allow a '?' as a placeholder for the limit value
            if ($offsetTb === '?') {
                $offsetStr = '?';
                $builtBindedParamsString .= "i";
                if (!in_array("offset", $builtFieldsArray, true)) {
                    $builtFieldsArray[] = "offset";
                } else {
                    $i = 1;
                    while (in_array("offset_$i", $builtFieldsArray, true)) {
                        $i++;
                    }
                    $builtFieldsArray[] = "offset_$i";
                }
            } elseif (is_string($offsetTb) && strlen($offsetTb) === 0) { // This is not error out on an Allowed Empty String!
            } elseif (is_numeric($offsetTb) && (int)$offsetTb >= 0) { // Must Be Positive Integer!
                $offsetStr = (string)$offsetTb;
            } else {
                cli_err_syntax_without_exit("Invalid `OFFSET` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `OFFSET` Key must be a Non-Negative Integer, or an Empty String (`''`) to omit it. (or just remove the entire Key)");
            }
        }

        // This is where all parts of the SQL String are stitched together
        $builtSQLString .= isset($selectedTbsColsStr) && is_string_and_not_empty($selectedTbsColsStr) ? "SELECT $selectedTbsColsStr" : "";
        $builtSQLString .= isset($fromTb) && is_string_and_not_empty($fromTb) ? " FROM $fromTb" : "";
        $builtSQLString .= isset($joinsStr) && is_string_and_not_empty($joinsStr) ? " $joinsStr" : "";
        $builtSQLString .= isset($whereStr) && is_string_and_not_empty($whereStr) ? " WHERE $whereStr" : "";
        $builtSQLString .= isset($groupByStr) && is_string_and_not_empty($groupByStr) ? " GROUP BY $groupByStr" : "";
        $builtSQLString .= isset($havingStr) && is_string_and_not_empty($havingStr) ? " HAVING $havingStr" : "";
        $builtSQLString .= isset($orderByStr) && is_string_and_not_empty($orderByStr) ? " ORDER BY $orderByStr" : "";
        $builtSQLString .= isset($limitStr) && is_string_and_not_empty($limitStr) ? " LIMIT $limitStr" : "";
        $builtSQLString .= isset($offsetStr) && is_string_and_not_empty($offsetStr) ? " OFFSET $offsetStr" : "";
        $builtSQLString .= ";";
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // We will now replace every [SubQuery] in the $builtSQLString by iterating
        // through the $configSubQsKey array and replacing the [SubQuery] with the
        // actual SubQuery string from the $configSubQsKey array.
        if (isset($configSubQsKey) && is_array($configSubQsKey) && count($configSubQsKey) > 0) {
            foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
                // If the subquery value is not a string or empty, we error out
                if (!is_string_and_not_empty($subQueryValue)) {
                    cli_err_syntax_without_exit("Invalid SubQuery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName` for SubQuery Key `$subQueryKey`!");
                    cli_info("The SubQuery Value must be a Non-Empty String representing the SubQuery!");
                }
                // Replace the [SubQuery] with the actual SubQuery string
                $builtSQLString = str_replace($subQueryKey, $subQueryValue, $builtSQLString);
            }
        }
        // We finally remove all extra spaces and newlines from the built SQL string
        // and then add it to the converted SQL Array
        $builtSQLString = preg_replace('/\( /', '(', $builtSQLString);
        $builtSQLString = preg_replace('/ \)/', ')', $builtSQLString);
        $builtSQLString = preg_replace('/\s+/', ' ', $builtSQLString);
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['fields'] = $builtFieldsArray;

        // TODO: PARSING The <HYDRATION> Key and then setting final values!
        // IMPORTANT: This step CAN fail and still generate the SQL String!
        // So, it just gives a strong warning that the hydration failed meaning
        // its value in 'key' will be an empty array instead of actual hydration!
        $hydratedKey = [];
        if (isset($hydrationKey)) {
            // HERE PARSING ACTUALLY BEGINS WHEN IT IS A NON-EMPTY ARRAY!
            if (is_array($hydrationKey) && !empty($hydrationKey)) {
                // PARSE "SIMPLE" Hydration Mode
                if ($hydrationMode === 'simple' || $hydrationMode === 'simple|advanced') {
                    // IN "SIMPLE" Mode it is a single string so we try get it or throw
                    // warning+info but do NOT exit since it is not critical!
                    if (isset($hydrationKey)) {
                        // This is checked for each step so we can exit early
                        // and without having mass-use if / else everywhere!
                        $hydrationKeys = $hydrationKey ?? [];
                        $keepGoing = true;
                        $uniqueTbs = [];
                        foreach ($hydrationKeys as $hydrationKey) {
                            if ($keepGoing) {
                                if (!is_string_and_not_empty($hydrationKey) || !preg_match('/^([a-zA-Z0-9_]+)((=>){1}([a-zA-Z0-9_]+)(\(via:[a-zA-Z0-9_]+\))*)*$/i', $hydrationKey)) {
                                    $keepGoing = false;
                                    cli_warning_without_exit("Invalid Hydration Key `$hydrationKey` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                    cli_info_without_exit("The Hydration Key must be a Non-Empty String representing the Hydration Key in the Format:\n`table` to Hydrate a Single Table OR\n`table=>table2` to Hydrate Two(2) Tables based on valid JOINING between them OR\n`table=>table2=>table3` to Hydrate Three(3) Tables based on valid JOINING between them\n(and so on to hydrate multiple joined tables)");
                                    cli_info_without_exit("Hydration will not be applied to the results of this query!");
                                }
                            }
                            if ($keepGoing) {
                                $hydrationKey = str_contains($hydrationKey, "=>") ? explode("=>", $hydrationKey) : [$hydrationKey];
                                foreach ($hydrationKey as $hydrationTb) {
                                    // Check for special case: `table(via:table2)` which is
                                    // used for pivoting tables in the hydration process!
                                    if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydrationTb, $matches)) {
                                        $tb1 = $matches[1];
                                        $viaTb = $matches[3];
                                        if (!in_array($tb1, $uniqueTbs, true)) {
                                            $uniqueTbs[] = $tb1;
                                        }
                                        if (!in_array($viaTb, $uniqueTbs, true)) {
                                            $uniqueTbs[] = $viaTb;
                                        }
                                    } elseif (!in_array($hydrationTb, $uniqueTbs, true)) {
                                        $uniqueTbs[] = $hydrationTb; // Add to unique tables
                                    }
                                }
                            }
                        }
                        // Must be same number of uniquely hydrated tables as
                        // selected tables so we can hydrate them properly!
                        if (count($uniqueTbs) !== 0 && count($selectedCols)) {
                            if (count($uniqueTbs) !== count($selectedCols)) {
                                $keepGoing = false;
                                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Fewer/More Tables:\n" . implode(",", quotify_elements($uniqueTbs)) . "\nthan were Selected using the `SELECT` Key:\n" . implode(",", quotify_elements(array_keys($selectedCols))) . "!");
                                cli_info_without_exit("Make sure You have same Number of Tables to Hydrate as in the `SELECT` Key UNLESS you are just Hydrating A Single Table!");
                                cli_info_without_exit("The Hydration Compilation Will be Skipped for this Query!");
                            }
                        }
                        // Here we do the main parsing/compiling of each Hydration Key Array Element!
                        // and finally adding it to the 'hydrate' => 'key' Key!
                        if ($keepGoing) {
                            foreach ($hydrationKeys as $hydrationKey) {
                                // We send the hydrationKey to the cli_parse_joined_tables_order function
                                // to parse the hydration key and get the hydrated key array. It will split
                                // on "=>" and also on special case of "table(via:table2)" to add to 'hydrate' => 'key'!
                                cli_parse_joined_tables_order(strtolower(trim($hydrationKey)), $hydratedKey, $keepGoing, $selectedCols);

                                // Then we perform tons of other checks to make sure no needed
                                // primary+forieng columns are missing so it can actually be used!
                                if ($keepGoing) {
                                    $hydrationKey = str_contains($hydrationKey, "=>") ? explode("=>", $hydrationKey) : [$hydrationKey];
                                }
                                // Check if fewer tables were selected than should be hydrated
                                // since "$tables_were_joined" = true means we have joined tables
                                // meaning we might have a valid case for "=>" aving been used!
                                if ($keepGoing) {
                                    // if ($tables_were_joined) {
                                    //     if (count($hydrationKey) !== count($joinedTables)) {
                                    //         $keepGoing = false;
                                    //         cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Fewer/More Tables:\n" . implode(",", quotify_elements($hydrationKey)) . " than were Joined using the `JOINS_ON` Key: " . implode(",", quotify_elements((!empty($joinedTables) ? $joinedTables : ["<No Joined Tables>"]))) . "!");
                                    //         cli_info_without_exit("You have Joined Tables in the `JOINS_ON` Key but the Hydration Key More/Fewer Table(s) Hydrate!");
                                    //         cli_info_without_exit("Make sure You have same Number of Tables to Hydrate as in the `JOINS_ON` Key UNLESS you are just Hydrating A Single Table!");
                                    //     }
                                    // } elseif (count($hydrationKey) > 1 && count($hydrationKey) !== count($joinedTables)) {
                                    //     $keepGoing = false;
                                    //     cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Fewer/More Tables:\n" . implode(",", quotify_elements($hydrationKey)) . " than were Joined using the `JOINS_ON` Key: " . implode(",", quotify_elements((!empty($joinedTables) ? $joinedTables : ["<No Joined Tables>"]))) . "!");
                                    //     cli_info_without_exit("You have Joined Tables in the `JOINS_ON` Key but the Hydration Key More/Fewer Table(s) Hydrate!");
                                    //     cli_info_without_exit("Make sure You have same Number of Tables to Hydrate as in the `JOINS_ON` Key UNLESS you are just Hydrating A Single Table!");
                                    // }
                                }
                                // Check that the tables to hydrate are actually valid ones
                                // by comparing against the keys in the array $selectedCols!
                                if ($keepGoing) {
                                    // Check for the Tables Joined if they were!
                                    if ($tables_were_joined) {
                                        foreach ($joinedTables as $joined) {
                                            if (!array_key_exists($joined, $selectedCols)) {
                                                $keepGoing = false;
                                                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$joined` which was NOT Selected in the `SELECT` Key!");
                                                cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                            }
                                            foreach ($hydrationKey as $hydTb) {
                                                // Check for special case: `table(via:table2)` which means checking two tables!
                                                if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                    $tb1 = $matches[1];
                                                    $viaTb = $matches[3];
                                                    if (!array_key_exists($tb1, $selectedCols)) {
                                                        $keepGoing = false;
                                                        cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$tb1` which was NOT Selected in the `SELECT` Key!");
                                                        cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                        cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                    } elseif (!array_key_exists($viaTb, $selectedCols)) {
                                                        $keepGoing = false;
                                                        cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$viaTb` which was NOT Selected in the `SELECT` Key!");
                                                        cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                        cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                    }
                                                } elseif (!array_key_exists($hydTb, $selectedCols)) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$hydTb` which was NOT Selected in the `SELECT` Key!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                    cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                }
                                            }
                                        }
                                    }
                                    // Otherwise just check the "=>"-splitted Tables in <HYDRATION> Key!
                                    else {
                                        foreach ($hydrationKey as $hydTb) {
                                            // Check for special case: `table(via:table2)` which means checking two tables!
                                            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                $tb1 = $matches[1];
                                                $viaTb = $matches[3];
                                                if (!array_key_exists($tb1, $selectedCols)) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$tb1` which was NOT Selected in the `SELECT` Key!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                    cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                } elseif (!array_key_exists($viaTb, $selectedCols)) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$viaTb` which was NOT Selected in the `SELECT` Key!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                    cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                }
                                            } elseif (!array_key_exists($hydTb, $selectedCols)) {
                                                $keepGoing = false;
                                                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$hydTb` which was NOT Selected in the `SELECT` Key!");
                                                cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                            }
                                        }
                                    }
                                }
                                // Check that all tables to be hydrated have the "id" column
                                // by checking the $selectedCols array for the "id" key and in
                                // the case of joined tables, the "referencesTable_id" key!
                                // Since we have already validated all tables exist,
                                // we can directly iterate through $selectedCols starting
                                // with the first table and check if it has the "id" column!
                                // in any of its string values (the keys)
                                if ($keepGoing) {
                                    if ($tables_were_joined) {
                                        foreach ($joinedTables as $joined) {
                                            if (!isset($joinedTablesWithRef[$joined])) {
                                                continue; // Skipping tables we know cannot have the ref key we are looking for!
                                            } else {
                                                $refTbCol = $joinedTablesWithRef[$joined];
                                                $foundRef = false; // Reset for each table so all must pass!
                                                foreach ($selectedCols[$joined] as $refCol => $refVal) {
                                                    if ($refVal === $refTbCol) {
                                                        $foundRef = true; // Found the referencesTable_id Column!
                                                        break;
                                                    }
                                                }
                                                // One or more `referencesTable_id` NOT found so we "warn out"
                                                if (!$foundRef) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Joined Tables without the necessary `OtherTableNameInPlural_id` Column!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key and that they have the `referencesTable_id` Column!");
                                                    cli_info_without_exit("Valid & JOINED Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                }
                                            }
                                        }

                                        // We also check all Tables to Hydrate all have the `id` Column
                                        foreach ($hydrationKey as $hydTb) {
                                            $foundId = false; // Reset for each table so all must pass!
                                            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                $tb1 = $matches[1];
                                                $viaTb = $matches[3];
                                                foreach ($selectedCols[$tb1] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                                $foundId = false;
                                                foreach ($selectedCols[$viaTb] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                foreach ($selectedCols[$hydTb] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        // One or more `id` NOT found so we "warn out"
                                        if (!$foundId) {
                                            $keepGoing = false;
                                            cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table(s) without the `id` Column!");
                                            cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key and that they have the `id` Column!");
                                            cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                        }
                                    }
                                    // Otherwise, we only need to check the `id` column for all the single Table!
                                    else {
                                        $foundId = false;
                                        foreach ($hydrationKey as $hydTb) {
                                            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                $tb1 = $matches[1];
                                                $viaTb = $matches[3];
                                            } else {
                                                foreach ($selectedCols[$hydTb] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        // No `id` found so we warn and set $keepGoing to false
                                        if (!$foundId) {
                                            $keepGoing = false;
                                            cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table(s) without the `id` Column!");
                                            cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key and that they have the `id` Column!");
                                            cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                        }
                                    }
                                }
                            }
                        }
                        // Here it means we are all 100 % done with 0 errors!
                        if ($keepGoing) {
                            $convertedSQLArray['hydrate']['key'] = $hydratedKey;
                            cli_success_without_exit("[HYDRATION COMPILATION SUCCEEDED] Hydration Key(s) Parsed Successfully in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        }
                    }
                }
                // PARSE "ADVANCED" Hydration Mode - Would probably support M:M:M:and_so_on?
                elseif ($hydrationMode === 'advanced') {
                    cli_warning_without_exit("<CURRENTLY NOT IMPLEMENTED YET IN FUNKPHP!>");
                    cli_info_without_exit("The `advanced` Hydration Mode is currently not implemented in FunkPHP!");
                    cli_info_without_exit("Hydration Will NOT be Compiled During This SELECT Query Compilation!");
                }
                // This should not happen, but if it does, we error out without exit
                else {
                    cli_err_syntax_without_exit("Invalid Hydration Mode `$hydrationMode` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info_without_exit("Valid Hydration Modes are: `simple`, `advanced` or `simple` is used by default when `simple|advanced` is set!");
                    cli_info_without_exit("Hydration Will NOT be Compiled During This SELECT Query Compilation!");
                }
            }
            // If not array, empty or just invalid data type,
            // we warn the user that no hydration parsing was made!
            else {
                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set BUT Empty or NOT An Array!");
                cli_info_without_exit("Hydration Will NOT be Compiled During This SELECT Query Compilation!");
            }
        }
        $convertedSQLArray['hydrate']['mode'] = $hydrationMode;
        $convertedSQLArray['hydrate']['type'] = $hydrationType;


        // Report success and inform about ignored keys
        cli_success_without_exit("[SQL STRING BUILT] Built SQL String: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the SELECT Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // You should never reach this point, but if you do, we error out
    else {
        cli_err_syntax_without_exit("Invalid Config Key `<QUERY_TYPE>` value `$configQTKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("Valid Query Types are:\n" . implode(",\n", quotify_elements($globalConfigRules['<QUERY_TYPE>'])) . ".");
    }

    // FINALLY AFTER ALL THAT: Return the converted SQL Array
    return $convertedSQLArray;
}

// Match Compiled Route with URI Segments, used by "cli_match_developer_route"
function cli_match_compiled_route(string $requestUri, array $methodRootNode): ?array
{
    // Prepare & and extract URI Segments and remove empty segments
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);

    // Prepare variables to store the current node,
    // matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = ['uri' => $uriSegments, 'route' => []]; // Start with empty string to make implode work correctly
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;

    // EDGE-CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        // When no match for root node
        if (!isset($currentNode['/'])) {
            return null;
        }
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
        }
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }

    // Iterate URI segments when more than 0
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        /// First try match "|" middleware node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
        }

        // Then try match literal route
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments['route'][] = $currentUriSegment;
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
                $matchedPathSegments['route'][] = ":" . $placeholderKey;
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
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
    }

    // Return matched route, params & middlewares
    // if all consumed segments matched
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments['route'])) {
            return ["route" => '/' . implode('/', $matchedPathSegments['route']), "segments" => $matchedPathSegments, "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
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
function cli_match_developer_route(string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes)
{
    // Prepare return values
    $matchedRoute = null;
    $matchedPathSegments = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
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
        $matchedPathSegments = $routeDefinition["segments"] ?? [];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $noMatchIn = "ROUTE_MATCHED_BOTH";
            // We remove 'middlewares' from the matched route since it will
            // be array merged with all middleware-matched URI segments!
            if (isset($routeInfo['middlewares'])) {
                $routeInfo = array_splice($routeInfo, 1, null, true);
            }
            // Add Any Matched Middlewares
            if (
                isset($routeDefinition["middlewares"])
                && is_array($routeDefinition["middlewares"])
                && !empty($routeDefinition["middlewares"])
            ) {
                // Each 'middlewares' key is an numbered array so
                // we can use array_merge so always keep the order
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (
                        isset($developerSingleRoutes[$method][$middleware])
                        && isset($developerSingleRoutes[$method][$middleware]['middlewares'])
                    ) {
                        $matchedMiddlewareHandlers = array_merge($matchedMiddlewareHandlers, $developerSingleRoutes[$method][$middleware]['middlewares']);
                    }
                }
            }
        } else {
            $noMatchIn .= "DEVELOPER_ROUTES(funkphp/core/pipeline_routes.php)";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES(funkphp/core/compiled_routes.php)";
    }
    return [
        "method" => $method,
        "route" => $matchedRoute,
        "segments" => $matchedPathSegments,
        "params" => $matchedRouteParams,
        "matched_middlewares" =>  $matchedMiddlewareHandlers ?? [],
        "route_keys" => [...$routeInfo ?? []],
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// Rebuilds the Single Routes Route file (funkphp/routes/route_single_routes.php) based on valid array
function cli_rebuild_single_routes_route_file($singleRouteRoutesFileArray): bool
{
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/core/pipeline_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['ROUTES'])) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/core/pipeline_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir(FUNKPHP_ROUTES_DIR) || !is_writable(FUNKPHP_ROUTES_DIR)) {
        cli_err("[cli_rebuild_single_routes_file] Directory for `routes.php` (" . FUNKPHP_ROUTES_DIR . ") must be a Writable Directory. Check it exists and/or its File Permission!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists(FUNKPHP_FILE_PATH_ROUTES) && !is_writable(FUNKPHP_FILE_PATH_ROUTES)) {
        cli_err("[cli_rebuild_single_routes_file] Routes file (funkphp/core/pipeline_routes.php) must be writable. It is not!");
    }
    // Use Atomic File Write to prevent corruption while outputting the newly compiled Routes file
    return (cli_crud_folder_php_file_atomic_write((cli_get_prefix_code("route_singles_routes_start")
        . var_export($singleRouteRoutesFileArray, true) . ";\n"), FUNKPHP_FILE_PATH_ROUTES));
}

// Build Compiled Route from Developer's Defined Routes
function cli_build_compiled_routes(array $developerSingleRoutes, array $developerMiddlewareRoutes)
{
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
    $GETConfig = $developerSingleRoutes["GET"]['<CONFIG_METHOD>'] ?? FUNKPHP_DEFAULT_METHOD_CONFIG_KEY_AND_ITS_KEYS;
    $POSTSingles = $developerSingleRoutes["POST"] ?? [];
    $POSTConfig = $developerSingleRoutes["POST"]['<CONFIG_METHOD>'] ?? FUNKPHP_DEFAULT_METHOD_CONFIG_KEY_AND_ITS_KEYS;
    $PUTSingles = $developerSingleRoutes["PUT"] ?? [];
    $PUTConfig = $developerSingleRoutes["POST"]['<CONFIG_METHOD>'] ?? FUNKPHP_DEFAULT_METHOD_CONFIG_KEY_AND_ITS_KEYS;
    $DELETESingles = $developerSingleRoutes["DELETE"] ?? [];
    $DELETEConfig = $developerSingleRoutes["POST"]['<CONFIG_METHOD>'] ?? FUNKPHP_DEFAULT_METHOD_CONFIG_KEY_AND_ITS_KEYS;
    $PATCHSingles = $developerSingleRoutes["PATCH"] ?? [];
    $PATCHConfig = $developerSingleRoutes["PATCH"]['<CONFIG_METHOD>'] ?? FUNKPHP_DEFAULT_METHOD_CONFIG_KEY_AND_ITS_KEYS;

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
            if (
                $key === "" || $key === null
                || $key === false || $key === '<CONFIG_METHOD>'
            ) {
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
                    // And update param as next nested key and/or move to next node
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
            if ($key === "" || $key === null || $key === false || $key === '<CONFIG_METHOD>') {
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
function cli_output_compiled_routes(array $compiledTrie)
{
    // Check if the compiled route is empty
    if (!is_array($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }
    if (empty($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }
    // Try output ocmpiled route to file and report success or failure
    if (!cli_crud_folder_php_file_atomic_write("<?php\nreturn " . var_export($compiledTrie, true) . ";\n", FUNKPHP_FILE_PATH_TROUTES)) {
        cli_err("Failed to write Compiled Routes to file: `" .  FUNKPHP_FILE_PATH_TROUTES . "`! Check File Permissions?");
    } else {
        cli_success_without_exit("SUCCESSFULLY Wrote Compiled Routes to file: `" . FUNKPHP_FILE_PATH_TROUTES . "`!");
    }
}

// Convert PHP array() syntax to simplified [] syntax | ONLY USED FOR "reserved_list" output, nothing else!
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
    $str = function_exists('mb_str_split') ? mb_str_split(var_export($array, true)) : str_split(var_export($array, true));
    $arrStack = [];
    $arrayLetters = ["a", "r", "r", "a", "y", " "];
    $quotes = ["'", '"'];
    $inStr = false;
    $converted = "";

    // Check if first character is "a"
    if ($str[0] !== "a") {
        echo "[cli_convert_array_to_simple_syntax - ERROR]: The array should start with the letter 'a' as in array()!\n";
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

// Restore essentially the "funkphp" folder and all its subfolders if they do not exist!
function cli_restore_default_folders_and_files()
{
    // Prepare what folders to loop through and create if they don't exist!
    $folderBase = PROJECT_DIR;
    $folders = [
        "$folderBase",
        "$folderBase/backups/",
        "$folderBase/batteries/",
        "$folderBase/batteries/pipeline/",
        "$folderBase/batteries/pipeline/middlewares",
        "$folderBase/batteries/pipeline/post_response/",
        "$folderBase/batteries/pipeline/request/",
        "$folderBase/cli/",
        "$folderBase/cli/commands/",
        "$folderBase/cli/config/",
        "$folderBase/cli/core/",
        "$folderBase/funkphp/",
        "$folderBase/funkphp/classes/",
        "$folderBase/funkphp/core/",
        "$folderBase/funkphp/config/",
        "$folderBase/funkphp/pipeline/",
        "$folderBase/funkphp/pipeline/request/",
        "$folderBase/funkphp/pipeline/post_response/",
        "$folderBase/funkphp/pipeline/middlewares",
        "$folderBase/funkphp/pipeline/routes",
        "$folderBase/funkphp/pages/",
        "$folderBase/funkphp/pages/compiled/",
        "$folderBase/funkphp/pages/compiled/[errors]/",
        "$folderBase/funkphp/pages/components/",
        "$folderBase/funkphp/pages/layouts/",
        "$folderBase/funkphp/pages/partials/",
        "$folderBase/funkphp/data/",
        "$folderBase/funkphp/data/sql/",
        "$folderBase/funkphp/data/validation/",
        "$folderBase/funkphp/vendor/",
        "$folderBase/gui/",
        "$folderBase/public_html/",
        "$folderBase/public_html/css/",
        "$folderBase/public_html/fonts/",
        "$folderBase/public_html/images/",
        "$folderBase/public_html/js/",
        "$folderBase/schemas/",
        "$folderBase/snippets/",
        "$folderBase/tests/",
    ];

    // Prepare default files that doesn't exist if certain folders don't exist
    $defaultFiles = [
        "$folderBase/funkphp/core/compiled_routes.php",
        "$folderBase/funkphp/core/pipeline_request.php",
        "$folderBase/funkphp/core/pipeline_routes.php",
        //"$folderBase/public_html/.htaccess",
        "$folderBase/cli/.htaccess",
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
    $date = date("Y-m-d H:i:s");
    foreach ($defaultFiles as $file) {
        if (!file_exists($file)) {
            // Recreate default files based on type ("troute", "middleware routes" or "single routes")
            if (str_contains($file, "compiled_routes")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [];\n?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } elseif (str_contains($file, "pipeline_routes")) {
                file_put_contents($file, "<?php\n// pipeline_routes.php - FunkPHP Framework | FunkCLI recreated it $date\nreturn [
            'ROUTES' => ['GET' =>['<CONFIG_METHOD>' => [
        'method_headers' => [],
        'method_rate_limiting' => null,
        'method_param_rules' => [],
    ]],'POST' =>['<CONFIG_METHOD>' => [
        'method_headers' => [],
        'method_rate_limiting' => null,
        'method_param_rules' => [],
    ]],'PUT' =>['<CONFIG_METHOD>' => [
        'method_headers' => [],
        'method_rate_limiting' => null,
        'method_param_rules' => [],
    ]],'DELETE' =>['<CONFIG_METHOD>' => [
        'method_headers' => [],
        'method_rate_limiting' => null,
        'method_param_rules' => [],
    ]],'PATCH' =>['<CONFIG_METHOD>' => [
        'method_headers' => [],
        'method_rate_limiting' => null,
        'method_param_rules' => [],
    ]],],];?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } else if (str_contains($file, "pipeline_request")) {
                file_put_contents($file, "<?php\n// pipeline_request.php - FunkPHP Framework | FunkCLI recreated it $date\nreturn  [
            'pipeline' =>
            [
            '<CONFIG_GLOBAL>' => [
            'global_headers' => [],
            'global_rate_limiting' => null,
            'global_param_rules' => [],
            ],
            'request' =>
            [
            0 => 'pl_https_redirect',
            1 => 'pl_prepare_uri',
            2 => 'pl_run_ini_sets',
            3 => 'pl_match_denied_exact_ips',
            4 => 'pl_match_denied_methods',
            5 => 'pl_match_denied_uas',
            6 => 'pl_match_route_then_run_matched_middlewares_and_pipeline',
            ],'post_response' => [0 => [],],];");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } else if (str_contains($file, "public_html/.htaccess")) {
                file_put_contents($file, "# This file was recreated by FunkCLI!\nRewriteEngine On\nRewriteRule ^([^\.]+)$ $1.php [NC]\nRewriteRule ^.*$ index.php [L,QSA]");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } else if (str_contains($file, "cli/.htaccess")) {
                file_put_contents($file, "# This file was recreated by FunkCLI!\n<Files \"funk\">\nSetHandler application/x-httpd-php\n</Files>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            }
        }
    }
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
                cli_err("Output file FAILED to write: $outputFilePath!");
            } else {
                if ($customSuccessMessage !== "") {
                    cli_success_without_exit($customSuccessMessage);
                    $success = true;
                } else {
                    cli_success_without_exit("Output file written SUCCESSFULLY: $outputFilePath!");
                    $success = true;
                }
            }
        }
    }
}

// Backup batch of files based on the array of files (string values) to backup
// Function uses "cli_backup_file_until_success"!
// TODO: Fix actual backup functionality
function cli_backup_batch($arrayOfFilesToBackup)
{
    // Check if the array is a non-empty array
    if (!is_array($arrayOfFilesToBackup) || empty($arrayOfFilesToBackup)) {
        cli_err_syntax("Array of files to backup must be a non-empty array!");
    }
    global $settings;
    cli_info_without_exit("`cli_backup_batch` will be implemented another time! For now, it just simulates the backup process by waiting 1 second for each file and then reporting success!");
}

// Delete a Single Route from the Route file (funkphp/routes/route_single_routes.php)
// and delete its associated Handler Function (and Handler File if last function)
// It does NOT delete validation files, or page files unless specifically specified!
function cli_delete_a_route()
{
    // Load globals and validate input
    global
        $argv,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax_without_exit("Provide a valid Route to delete from the Route file!\nExample: \"php funkcli delete [route|r] [method/route_name]\"");
        cli_info("IMPORTANT: Its associated Handler Function (and Handler File if last function) will be deleted as well!\n");
    }
    // argv[4] is optional and can be used to delete the validation handler
    $deleteValidationHandler = false;
    if (isset($argv[4]) && is_string($argv[4]) && strtolower($argv[4]) === "with_validation") {
        $deleteValidationHandler = true;
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $deleteRoute = trim(strtolower($argv[3]));
    $oldRoute = $deleteRoute;
    [$method, $validRoute] = "";
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that provided route exists
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute])) {
        cli_err("Route: \"$method$validRoute\" does not exist. Another HTTP Method or was it deleted already?");
    }

    // TODO: Fix later when cli_backup_batch is also fixed!
    // HERE we found the route so we can delete it
    // First backup all associated route files if settings allow it
    // cli_backup_batch(
    //     [
    //         "troutes",
    //         "routes",
    //     ]
    // );
    // Grab handlers for 'handler' and 'data' from the route array
    $middlewares = $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'] ?? null;
    $handler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['handler'] ?? null;
    $datahandler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['data'] ?? null;
    $validationHandler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['validation'] ?? null;

    // Then we unset() each matched route
    unset($singleRoutesRoute['ROUTES'][$method][$validRoute]);
    cli_success_without_exit("Deleted Route \"$method$validRoute\" from Routes File!");

    // Then we rebuild and recompile Routes
    cli_rebuild_single_routes_route_file($singleRoutesRoute);
    $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
    cli_output_compiled_routes($compiledRouteRoutes);

    // Send the handler variable to delete it (this will
    // also delete file if it's the last function in it!)
    // But we only call them if they are not null or empty strings
    if ($handler !== null && !empty($handler)) {
        //delete_handler_file_with_fn_or_just_fn_or_err_out("r", $handler);
    }
    if ($datahandler !== null && !empty($datahandler)) {
        // We check if the data handler exists before deleting it
        //delete_handler_file_with_fn_or_just_fn_or_err_out("d", $datahandler);
    }
    // Only delete the validation handler if it is not null or empty string
    // and if the user provided the "with_validation" argument
    if ($validationHandler !== null && !empty($validationHandler)) {
        if ($deleteValidationHandler) {
            // We check if the validation handler exists before deleting it
            //delete_handler_file_with_fn_or_just_fn_or_err_out("v", $validationHandler);
        } else {
            if (is_string($validationHandler)) {
                cli_info_without_exit("Validation Handler \"$validationHandler\" for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            } elseif (is_array($validationHandler) && array_is_list($validationHandler)) {
                cli_info_without_exit("Validation Handler \"$validationHandler[0]\" for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            } elseif (is_array($validationHandler)) {
                cli_info_without_exit("Validation Handler \"{$validationHandler[key($validationHandler)]}\" for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            } else {
                cli_info_without_exit("Validation Handler for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            }
        }
    }

    // If "middlewares" is not null and not empty string/array
    // then we list the middlewares and inform that the middlewwares
    // where deleted from route but not as files!
    if ($middlewares !== null && !empty($middlewares)) {
        if (is_string($middlewares)) {
            cli_info_without_exit("\"$method$validRoute\" used the following middleware: \"$middlewares\" from \"funkphp/middlewares/\"!");
        } elseif (is_array($middlewares) && array_is_list($middlewares)) {
            // Join all as a string with ", " separator
            $middlewares = implode(", ", $middlewares);
            cli_info_without_exit("\"$method$validRoute\" used the following middleware(s): \"$middlewares\" from \"funkphp/middlewares/\"!");
        }
    }
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
        // Skip the <CONFIG_GLOBAL> key since it is not a
        // route and does not have route keys to sort
        if ($key === '<CONFIG_GLOBAL>') {
            continue;
        }
        if (is_array($value)) {
            ksort($singleRoutesRootArray['ROUTES'][$key]);
        }
    }

    // Then we rebuild and recompile Routes
    $rebuild = cli_rebuild_single_routes_route_file($singleRoutesRootArray);
    if ($rebuild) {
        cli_success_without_exit("Rebuilt Route file \"" . FUNKPHP_FILE_PATH_ROUTES . "\"!");
    } else {
        cli_err("FAILED to rebuild Route file \"" . FUNKPHP_FILE_PATH_ROUTES . "\". File permissions issues?");
    }
    $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRootArray['ROUTES'], $singleRoutesRootArray['ROUTES']);
    cli_output_compiled_routes($compiledRouteRoutes);
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
            cli_output_compiled_routes($compiledRouteRoutes);
            continue;
        }
    }
}

// Retrieve starting code for files created by the CLI
function cli_get_prefix_code($keyString)
{
    $currDate = date("Y-m-d H:i:s");
    $prefixCode = [
        "route_singles_routes_start" => "<?php // pipeline_routes.php - FunkPHP | FunkCLI Modified it $currDate\nreturn ",
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

// CLI Functions to show errors and success messages with colors
function cli_err_syntax($string)
{
    cli_output(MSG_TYPE_SYNTAX_ERROR, $string, true, 1);
}
function cli_err($string)
{
    cli_output(MSG_TYPE_ERROR, $string, true, 1);
}
function cli_err_without_exit($string)
{
    cli_output(MSG_TYPE_ERROR, $string, false);
}
function cli_err_syntax_without_exit($string)
{
    cli_output(MSG_TYPE_SYNTAX_ERROR, $string, false);
}
function cli_err_command($string)
{
    cli_output(MSG_TYPE_ERROR, $string, true, 1);
}
function cli_success($string)
{
    cli_output(MSG_TYPE_SUCCESS, $string, true, 0);
}
function cli_info($string)
{
    cli_output(MSG_TYPE_INFO, $string, true, 0);
}
function cli_success_without_exit($string)
{
    cli_output(MSG_TYPE_SUCCESS, $string, false);
}
function cli_info_without_exit($string)
{
    cli_output(MSG_TYPE_INFO, $string, false);
}
function cli_info_multiline($string)
{
    cli_output(MSG_TYPE_INFO, $string, false);
}
function cli_warning($string)
{
    cli_output(MSG_TYPE_WARNING, $string, true, 0);
}
function cli_warning_without_exit($string)
{
    cli_output(MSG_TYPE_WARNING, $string, false);
}
function cli_important($string)
{
    cli_output(MSG_TYPE_IMPORTANT, $string, true, 0);
}
function cli_important_without_exit($string)
{
    cli_output(MSG_TYPE_IMPORTANT, $string, false);
}
function cli_success_with_warning_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, true);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS + WARNING]: " . $string1 . ANSI_RESET;
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
        exit(0);
    }
}
function cli_err_with_info_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, true, 1); // Exit after info in this case
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + INFO]: " . $string1 . ANSI_RESET;
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
        exit(1);
    }
}
function cli_err_with_info_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, false);
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + INFO]: " . $string1 . ANSI_RESET;
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
    }
}
function cli_err_with_warning_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, true, 1);
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + WARNING]: " . $string1 . ANSI_RESET;
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
        exit(1);
    }
}
function cli_err_with_warning_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, false);
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + WARNING]: " . $string1 . ANSI_RESET;
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
    }
}
function cli_success_with_info_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, true, 0);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS + INFO]: " . $string1 . ANSI_RESET;
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
        exit(0);
    }
}
function cli_success_with_info_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, false);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS]: " . $string1 . ANSI_RESET; // Note: original prefix might be different here
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
    }
}
function cli_success_with_warning_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, false);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS]: " . $string1 . ANSI_RESET; // Note: original prefix might be different here
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
    }
}

// Function loops through all function files in funkphp/core/
// and preg matchdes "function ([a-zA-Z0-9_]+)" and then adds the function name to an
// array which is then converted to a [] array string using cli_convert_array_to_simple_syntax
// and then the FunkCLI file is open and the line "$reserved_functions = [...];" is replaced with the new array string
function cli_update_reserved_functions_list()
{
    $dir = FUNKPHP_CORE_DIR . '/';
    $dir2 = CLI_CORE_DIR . '/';
    if (!dir_exists_is_readable_writable($dir)) {
        cli_err("Directory $dir does not exist or is not readable/writable!");
    }
    if (!dir_exists_is_readable_writable($dir2)) {
        cli_err("Directory $dir2 does not exist or is not readable/writable!");
    }

    // Get all files in the directory
    $files = scandir($dir);
    $files2 = scandir($dir2);
    $reserved_functions = [];

    // Loop through all files and check if they are PHP files
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === "php") {
            // Check that file name ends with "functions.php" or exit
            if (!str_ends_with($file, "functions.php")) {
                cli_info_without_exit("File `$file` not valid function file! Skipping it...");
                continue;
            }
            // Get the contents of the file
            $contents = file_get_contents($dir . $file);
            // Use preg_match to find all function names in the file
            // The line MUST begin with "function " and then a space and then the function name
            preg_match_all("/^function\s+([a-zA-Z0-9_]+)\(/m", $contents, $matches);
            // Add the function names to the reserved_functions array
            foreach ($matches[1] as $function_name) {
                $reserved_functions[] = $function_name;
            }
        }
    }
    foreach ($files2 as $file2) {
        if (pathinfo($file2, PATHINFO_EXTENSION) === "php") {
            if (!str_starts_with($file2, "cli_functions")) {
                cli_info_without_exit("File `$file2` not valid function file! Skipping it...");
                continue;
            }
            // Get the contents of the file
            $contents = file_get_contents($dir2 . $file2);
            // Use preg_match to find all function names in the file
            // The line MUST begin with "function " and then a space and then the function name
            preg_match_all("/^function\s+([a-zA-Z0-9_]+)\(/m", $contents, $matches);
            // Add the function names to the reserved_functions array
            foreach ($matches[1] as $function_name) {
                $reserved_functions[] = $function_name;
            }
        }
    }

    // Convert the array to a string using cli_convert_array_to_simple_syntax
    $reserved_functions_string = cli_convert_array_to_simple_syntax($reserved_functions);
    $count = count($reserved_functions);

    // Replace all /\d+ => / with "" to remove the array keys
    $reserved_functions_string = preg_replace("/\d+\s*=>\s*/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\n/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\',/", "',\n", $reserved_functions_string, 1);
    // We now save an array of those functions in cli_reserved.php which is in the cli folder!
    $output = file_put_contents(
        $dir2 . "cli_reserved.php",
        "<?php\n// FunkPHP Framework - FunkCLI Created it " . date("Y-m-d H:i:s") . "\n" .
            "// This file contains all reserved functions in the FunkPHP Framework and FunkCLI.\n" .
            "// It is used to check if a function is reserved (used by FunkPHP/FunkCLI) or not.\n" .
            "return \n" . $reserved_functions_string . " // Functions Count: $count"
    );
    if ($output === false) {
        cli_err("FAILED to Write to File `$dir2" . "cli_reserved.php`! Check File Permissions?");
    } else {
        cli_success("Reserved Functions List Updated! Total Functions: $count");
    }
}
// Same as above but also returns the newly generated file as an array
function cli_update_reserved_functions_list_and_return_as_array()
{
    $dir = FUNKPHP_CORE_DIR . '/';
    $dir2 = CLI_CORE_DIR . '/';
    if (!dir_exists_is_readable_writable($dir)) {
        cli_err("Directory $dir does not exist or is not readable/writable!");
    }
    if (!dir_exists_is_readable_writable($dir2)) {
        cli_err("Directory $dir2 does not exist or is not readable/writable!");
    }
    $files = scandir($dir); // funkphp/core/
    $files2 = scandir($dir2); // src/cli/core/
    $reserved_functions = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === "php") {
            // Check that file name ends with "functions.php" inside of funkphp/core/
            if (!str_ends_with($file, "functions.php")) {
                cli_info_without_exit("File `$file` not valid function file! Skipping it...");
                continue;
            }
            // Get the contents of the file
            $contents = file_get_contents($dir . $file);
            // Use preg_match to find all function names in the file
            // The line MUST begin with "function " and then a space and then the function name
            preg_match_all("/^function\s+([a-zA-Z0-9_]+)\(/m", $contents, $matches);
            // Add the function names to the reserved_functions array
            foreach ($matches[1] as $function_name) {
                $reserved_functions[] = $function_name;
            }
        }
    }
    foreach ($files2 as $file2) {
        if (pathinfo($file2, PATHINFO_EXTENSION) === "php") {
            // Check that file name starts with "cli_functions" inside of src/cli/core/
            if (!str_starts_with($file2, "cli_functions")) {
                cli_info_without_exit("File `$file2` not valid function file! Skipping it...");
                continue;
            }
            $contents = file_get_contents($dir2 . $file2);
            preg_match_all("/^function\s+([a-zA-Z0-9_]+)\(/m", $contents, $matches);
            foreach ($matches[1] as $function_name) {
                $reserved_functions[] = $function_name;
            }
        }
    }
    $reserved_functions_string = cli_convert_array_to_simple_syntax($reserved_functions);
    $count = count($reserved_functions);
    $reserved_functions_string = preg_replace("/\d+\s*=>\s*/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\n/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\',/", "',\n", $reserved_functions_string, 1);
    $output =
        "<?php\n// FunkPHP Framework - FunkCLI Created it " . date("Y-m-d H:i:s") . "\n" .
        "// This file contains all reserved functions in the FunkPHP Framework and FunkCLI.\n" .
        "// It is used to check if a function is reserved (used by FunkPHP/FunkCLI) or not.\n" .
        "return \n" . $reserved_functions_string . " // Functions Count: $count";
    if (!cli_crud_folder_php_file_atomic_write($output, FUNKPHP_FILE_PATH_CLI_RESERVED)) {
        cli_warning_without_exit("FAILED to Write to File `"  . FUNKPHP_FILE_PATH_CLI_RESERVED .   "`! Check File Permissions? ZERO Functions Included as a result!");
        cli_info_without_exit("This means a Function that is already being used by a globally included FunkPHP Function could be added and causing function redeclaration(s) as a result!");
        return [];
    } else {
        return include_once FUNKPHP_FILE_PATH_CLI_RESERVED;
    }
}

// Function that takes a variable ($existsInWhat) and then checks if a given value
// already exists in it as a string or in an array. It returns true if it does, false otherwise.
function cli_value_exists_as_string_or_in_array($valueThatExists, $existsInWhat)
{
    if ($existsInWhat === null) {
        return false;
    }
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

// Shorthand Boolean functions to check combined
// things for files, dirs and/or different data types
function dir_exists_is_readable_writable($dirPath)
{
    return is_dir($dirPath) && is_readable($dirPath) && is_writable($dirPath);
}
function file_exists_is_readable_writable($filePath)
{
    return is_file($filePath) && is_readable($filePath) && is_writable($filePath);
}
function is_array_and_not_empty($array)
{
    return isset($array) && is_array($array) && !empty($array);
}
function is_string_and_not_empty($string)
{
    return isset($string) && is_string($string) && mb_strlen(trim($string)) > 0;
}

// Function that takes a string and returns it with quotes such as (", ' or `) around it.
// Default is backtick (`) but can be changed to single quote (') or double quote (").
function quotify_string($string, $type = "`")
{
    if (!is_string($string) || empty($string)) {
        cli_err_syntax("[quotify_string]: String must be a non-empty string!");
    }
    if (!in_array($type, ["'", '"', '`'])) {
        cli_err_syntax("[quotify_string]: Type must be one of the following: ' (single quote), \" (double quote) or ` (backtick)!");
    }
    return $type . $string . $type;
}

// Function takes two strings and returns them in the format:
// "'string1' => 'string2'" or ""string1" => "string2"" or "`string1` => `string2`"
function wrappify_arrowed_string($string1, $string2, $type = "'")
{
    if (!is_string($string1) || empty($string1)) {
        cli_err_syntax("[wrappify_arrowed_string]: First string must be a Non-Empty String!");
    }
    if (!is_string($string2) || empty($string2)) {
        cli_err_syntax("[wrappify_arrowed_string]: Second string must be a Non-Empty String!");
    }
    if (!in_array($type, ["'", '"', '`'])) {
        cli_err_syntax("[wrappify_arrowed_string]: Type must be one of the following: ' (single quote), \" (double quote) or ` (backtick)!");
    }
    return $type . $string1 . "$type => $type" . $string2 . $type . ",\n\t\t";
}

// Function same above but for arrays, it takes an array and returns all its elements
// quotified with the given type (default is backtick `).
function quotify_elements($array, $type = "`")
{
    if (!is_array($array) || empty($array)) {
        cli_err_syntax("[quotify_elements]: Array must be a Non-Empty Array!");
    }
    if (!in_array($type, ["'", '"', '`'])) {
        cli_err_syntax("[quotify_elements]: Type must be one of the following: ' (single quote), \" (double quote) or ` (backtick)!");
    }
    $quotedArray = [];
    foreach ($array as $element) {
        // Element must be string, number or boolean we type cast it to string
        if (!is_string($element) && !is_numeric($element) && !is_bool($element)) {
            cli_err_syntax("[quotify_elements]: All elements in the Array must be Strings, Numbers or Booleans!");
        }
        // Type cast the element to string and then quotify it
        $element = (string)$element;
        $quotedArray[] = quotify_string($element, $type);
    }
    return $quotedArray;
}

// Function returns true/false if String starts+ends with given strings
function str_starts_ends_with($str, $start, $end)
{
    return str_starts_with($str, $start) && str_ends_with($str, $end);
}

// Function returns true/false if String does not start OR ends with given string
function str_starts_or_ends_not_with($str, $start, $end)
{
    if (str_starts_with($str, $start) && !str_ends_with($str, $end)) {
        return true;
    } elseif (!str_starts_with($str, $start) && str_ends_with($str, $end)) {
        return true;
    }
    return false;
}

// Function that returns true if any of the elements
// get true for str_contains, otherwise returns false
function array_str_contains($arr, $containValue)
{
    if (!is_array($arr) || empty($arr)) {
        cli_err_syntax("[array_contains]: First argument must be a Non-Empty Array!");
    }
    if (!is_string($containValue) && !is_numeric($containValue) && !is_bool($containValue)) {
        cli_err_syntax("[array_contains]: Second argument must be a String, Number or Boolean!");
    }
    foreach ($arr as $value) {
        if (is_string($value) && str_contains((string)$containValue, $value)) {
            return true;
        }
    }
    return false;
}

// Function that returns true if any of the elements
// get true for str_starts_with, otherwise returns false
function array_str_starts_with($arr, $startsWith)
{
    if (!is_array($arr) || empty($arr)) {
        cli_err_syntax("[array_str_starts_with]: First argument must be a Non-Empty Array!");
    }
    if (!is_string($startsWith) && !is_numeric($startsWith) && !is_bool($startsWith)) {
        cli_err_syntax("[array_str_starts_with]: Second argument must be a String, Number or Boolean!");
    }
    foreach ($arr as $value) {
        if (is_string($value) && str_starts_with((string)$startsWith, $value)) {
            return true;
        }
    }
    return false;
}
