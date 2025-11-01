<?php // SECOND CLI FUNCTIONS FILE SINCE SECOND ONE STARTED TO BECOME TOO LARGE!

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
 * @throws Exception/cli_err Stops command execution if $middlewareNameString is invalid,
 * $mwStatusArray is missing required keys, file already exists,
 * or directory permissions are insufficient.
 */
function cli_create_middleware_file($middlewareNameString, $mwStatusArray): bool
{
    // $middlewareNameString must be non-empty string or hard error
    if (!is_string($middlewareNameString) || empty(trim($middlewareNameString))) {
        cli_err('[cli_create_middleware_file()]: The Provided Middleware Name String (middlewareNameString) must be a Non-Empty String in order to continue creating the new Middleware File. If a Command File called this function, this error now stopped the command execution!');
    }
    // $mwStatusArray must be an array with the following keys existing: exists, has_valid_prefix, is_anonymous, middleware_is_valid
    $requiredKeys = [
        'exists',
        'has_valid_prefix',
        'is_anonymous',
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
    $mwString = "<?php\n\nnamespace FunkPHP\\Middlewares\\$middlewareNameString;\n// FunkCLI Created on " . date('Y-m-d H:i:s') . "!\n\nreturn function (&\$c, \$passedValue = null) {\n\t// Placeholder Comment so Regex works - Remove & Add Your Own Code!\n};\n";
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
 * Checks the existence and validity status of a Pipeline file across request and post-response directories.
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
 * - 'exists_in_post_response_dir': (bool) True if the file exists in the post-response pipeline directory.
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
function cli_duplicate_folder_file_fn_route_key($matchedRoute, $folder, $file, $fn, $methodroute): bool
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
        !is_string($folder)
        || empty(trim($folder))
        || !is_string($file)
        || empty(trim($file))
        || !is_string($fn)
        || empty(trim($fn))
        || !is_string($methodroute)
        || empty(trim($methodroute))
    ) {
        cli_err('[cli_duplicate_folder_file_fn_route_key]: The Provided $folder, $file, $fn, $methodroute must be Non-Empty Strings. Function expects a Matched Route with a Numbered Array of Route Keys with `[index] => Folder => File => Function => {optionalValue}` Structure to check against!');
    }
    // We now iterate over $matchedRoute keys using the array_subkeys_single() helper function
    // passing the matched Route array and the three strings provided by "$folder", "$file","$fn"
    foreach ($matchedRoute as $idx => $routeKey) {
        // If all three subkeys exist and are valid single-key structures, we have a duplicate
        $checkResult = array_subkeys_single($routeKey, $folder, $file, $fn);
        if (
            count($checkResult) === 3
            && $checkResult[0]['exists'] === true
            && $checkResult[1]['exists'] === true
            && $checkResult[2]['exists'] === true
        ) {
            cli_warning_without_exit("Duplicate Route Key `$folder=>$file=>$fn` at Index:[$idx] in:`$methodroute`!");
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
 * Extracts and normalizes the HTTP method and route path from a validated string.
 * SUPER IMPORTANT: This function ASSUMES the input has already been successfully validated.
 *
 * @param string $validatedMethodRouteString The input string (e.g., 'get/users/:id', possibly with 'r:').
 * @return array{0: string, 1: string}|null Returns array [Method, Route] or null if processing fails (highly unlikely on validated data).
 */
function cli_extract_method_route($validatedMethodRouteString)
{
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
 *
 * NOTE: ASSUMES the input string has been previously validated and is non-empty.
 *
 * @param string $validatedMiddlewareString The validated input string (e.g., "n:auth_check" or "auth_check").
 * @return string The normalized file name, guaranteed to start with 'mw_' (e.g., "mw_auth_check").
 */
function cli_extract_middleware($validatedMiddlewareString)
{
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
 *
 * NOTE: ASSUMES the input string has been previously validated and is non-empty.
 *
 * @param string $validatedPipelineString The validated input string (e.g., "name:logging" or "pl_logging").
 * @return string The normalized file name, guaranteed to start with 'pl_' (e.g., "pl_logging").
 */
function cli_extract_pipeline($validatedPipelineString)
{
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
        if (!preg_match('/^((sd|si|s|i|u|d)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $sv_tables)) {
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
            || !preg_match('/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i', $tables)
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
    $folder = ucwords($folder, '\\');
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
        $namespaceString = "<?php\n\nnamespace FunkPHP\\Routes\\$folder\\$file;\n";
    } else { // "Routes" folder is always used for those $type values!
        $namespaceString = "<?php\n\nnamespace FunkPHP\\$folder\\$file;\n";
    }
    $newFilesString .= $namespaceString;
    $createdOnCommentString = "// FunkCLI Created on " . date('Y-m-d H:i:s') . "!\n\n";
    $newFilesString .= $createdOnCommentString;

    // Based on $type, we create the necessary File (or just updated File!)
    // When just anonmyous function is needed (usually for middlewares & pipeline functions)
    if ($type === 'anonymous') {
        $typePartString .= "return function (&\$c, \$passedValue = null) {\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // When a named function is needed but file ALREADY EXISTS - Funk\Routes\<FOLDER>\<FILE>.php
    elseif ($type === 'named_not_new_file') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$methodAndRoute>\n";
        $typePartString .= "{\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // When a named function is needed and file DOES NOT EXIST - Funk\Routes\<FOLDER>\<FILE>.php
    elseif ($type === 'named_and_new_file') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$methodAndRoute>\n";
        $typePartString .= "{\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n\n";
        $typePartString .= "return function (&\$c, \$handler = \"$fn\", \$passedValue = null) {\n";
        $typePartString .= "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
        $typePartString .= "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
        $typePartString .= "\n\tif (function_exists(\$full)) {";
        $typePartString .= "\n\t\treturn \$full(\$c, \$passedValue);";
        $typePartString .= "\n\t} else {";
        $typePartString .= "\n\t\t\$c['err']['ROUTES']['$folderUPPER'][] = '$folderUPPER Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
        $typePartString .= "\n\t\treturn null;";
        $typePartString .= "\n\t}\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // Special-case #1: "funkphp/sql" folder
    // New SQL FILE
    elseif ($type === 'sql_new_file_and_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("sql", $tables);
        $typePartString .= "\n};\n\n";
        $typePartString .= "return function (&\$c, \$handler = \"$fn\", \$passedValue = null) {\n";
        $typePartString .= "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
        $typePartString .= "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
        $typePartString .= "\n\tif (function_exists(\$full)) {";
        $typePartString .= "\n\t\treturn \$full(\$c, \$passedValue);";
        $typePartString .= "\n\t} else {";
        $typePartString .= "\n\t\t\$c['err']['ROUTES']['$folderUPPER'][] = '$folderUPPER Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
        $typePartString .= "\n\t\treturn null;";
        $typePartString .= "\n\t}\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    } // Only NEW SQL FUNCTION in existing file
    elseif ($type === 'sql_only_new_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("sql", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // Special-case #2: "funkphp/validation" folder
    // New Validation FILE
    elseif ($type === 'validation_new_file_and_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("validation", $tables);
        $typePartString .= "\n};\n\n";
        $typePartString .= "return function (&\$c, \$handler = \"$fn\", \$passedValue = null) {\n";
        $typePartString .= "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
        $typePartString .= "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
        $typePartString .= "\n\tif (function_exists(\$full)) {";
        $typePartString .= "\n\t\treturn \$full(\$c, \$passedValue);";
        $typePartString .= "\n\t} else {";
        $typePartString .= "\n\t\t\$c['err']['ROUTES']['$folderUPPER'][] = '$folderUPPER Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
        $typePartString .= "\n\t\treturn null;";
        $typePartString .= "\n\t}\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // Only NEW Validation FUNCTION in existing file
    elseif ($type === 'validation_only_new_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
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

// Returns the status of a method/route in the routes.php file
function cli_route_status(&$ROUTES, $method, $route)
{
    // Validate that &$ROUTES is an associative array
    if (!isset($ROUTES) || !is_array($ROUTES) || array_is_list($ROUTES)) {
        cli_err_without_exit('[cli_route_status()]: &$ROUTES must be An Associative Array! (passed by reference)');
        cli_info('[cli_route_status()]: Use the `$ROUTES` variable from `funkphp/routes.php` file which is an Associative Array passed by reference as the first argument!');
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
    $returnRegex = '/return\s*array\(.*?\);$\n/ims';
    $returnFnRegex = '/^(?:(<\?php\s*))?(return function)\s*\(&\$c\s*.+$.*?^};/ims';
    $fns = null;
    $fileRaw = null;
    $fileReturnRaw = null;
    if (is_file($file) && is_readable($file)) {
        $fileCnt = file_get_contents($file);
        if (!$fileCnt) {
            cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT Read the File `' . $file . '` when it SHOULD have been Readable. This means that Named Functions, their $DX and/or Return arrays(), OR Anonymous Function Files CANNOT be retrieved for use!');
        } else {
            $fileRaw = $fileCnt;
            if (preg_match($returnFnRegex, $fileRaw, $fileReturnMatch)) {
                $fileReturnRaw = $fileReturnMatch[0] ?? null;
            } else {
                cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT find the Expected Anoynmous `return function` in the File `' . $file . '` when it SHOULD have been Found. This means it will NOT be possible to add any new Functions to this File (unless it is a Single Anonymous Function File) since it needs that matched part to add new functions from. This is due to the Regex: `/^(?:(<\?php\s*))?(return function)\s*\(&\$c\s*.+$.*?^};/ims` that cannot match `return function(&$c){};`!');
            }
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
        }
    }
    return [
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
            cli_info("It needs the following Keys: 'folder_name', 'folder_path', 'folder_exists', 'folder_readable', 'folder_writable', 'file_name', 'file_path', 'file_exists', 'file_readable', 'file_writable', 'functions' and 'file_raw'!");
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
            || !preg_match('/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i', $table)
            || (!str_contains($folder_provided_path, "funkphp/sql")
                && !str_contains($folder_provided_path, "funkphp/validation")))
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
            } elseif (file_exists($folder_path . '/post-request' . '/' . $file)) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline/post-request` Folder!');
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
                cli_err_without_exit('Middleware Function File `' . $file_name . '` already exists in the `funkphp/middlewares` Folder!');
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
            // Concatenate the new function to the existing file by adding it to the
            // "return function" key of the file_raw_entire
            $newFile .= $file_raw_return_fn;

            // We now replace the entire raw part with the $newFile since that now
            // contains the new function as well as the return function at the end
            $fileRaw = str_replace($file_raw_return_fn, $newFile, $file_raw_entire);
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
            $newFile .= $file_raw_return_fn;
            $fileRaw = str_replace($file_raw_return_fn, $newFile, $file_raw_entire);
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
            $newFile .= $file_raw_return_fn;
            $fileRaw = str_replace($file_raw_return_fn, $newFile, $file_raw_entire);
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
        cli_err('Provided $default value for cli_get_valid_cli_input() is NOT a String which it must be if you wanna use a default value that is used when skipping CLI input. This error means that the Command File has stopped running before receiving any remaining CLI inputs!');
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
            // A. If required, error out.
            // B. If not required and a default exists, use the default.
            // C. If not required and NO default, return null (skippable).
            if ($required) {
                cli_err_without_exit('Required input and must Match Regex Pattern:`' . $regex . '`. (You can omit the prefix if any)');
                if (isset($helpText) && is_string($helpText) && !empty($helpText)) {
                    cli_info_without_exit($helpText);
                }
                continue;
            }
            if ($default !== null) {
                return $default;
            }
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
