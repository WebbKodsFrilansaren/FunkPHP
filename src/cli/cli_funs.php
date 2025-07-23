<?php // SECOND CLI FUNCTIONS FILE SINCE SECOND ONE STARTED TO BECOME TOO LARGE!

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
                $overall_json_status .= ' with warning(s)';
                break;
            }
        }
    } else {
        http_response_code(400);
        foreach ($funk_response_messages as $msg) {
            if ($msg['type'] === MSG_TYPE_INFO) {
                $overall_json_status .= ' with info';
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
        cli_err_without_exit('[cli_match_file_and_fn()]: \$string must be a Non-Empty String!');
        cli_info('[cli_match_file_and_fn()]: Use either "fileName" (Regex: [a-z_][a-z_0-9]*) OR "fileName=>functionName" (Regex: [a-z_][a-z_0-9]*=>[a-z_][a-z_0-9]*)!');
    }
    $string = strtolower(trim($string));
    // Matches a string like "fileName" or "fileName=>functionName"
    // If only "fileName" is provided, it will use the same name for the function
    $regex = '/^([a-z_][a-z_0-9]*)(?:=>([a-z_][a-z_0-9]*))?$/i';
    $file = '';
    $fn = '';
    // Preg_match to find the file and function names
    if (preg_match($regex, $string, $matches)) {
        $file = $matches[1];
        $fn = isset($matches[2]) ? $matches[2] : $file;
    } else {
        cli_err_without_exit('[cli_match_file_and_fn()]: Invalid Syntax for File and/or Function Name!');
        cli_info('[cli_match_file_and_fn()]: Use either "fileName" (Regex: [a-z_][a-z_0-9]*) OR "fileName=>functionName" (Regex: [a-z_][a-z_0-9]*=>[a-z_][a-z_0-9]*)!');
    }
    // Add prefix to both variables if provided
    // and then check against reserved functions
    if (isset($prefix) && is_string($prefix) && !empty($prefix)) {
        $file = $prefix . $file;
        $fn = $prefix . $fn;
    }
    if (in_array($file, $reserved_functions)) {
        cli_err_without_exit('[cli_match_file_and_fn()]: File Name `' . $file . '` is a Reserved Function Name. Please choose a different name!');
        cli_info('[cli_match_file_and_fn()]: The majority of Reserved Function Names usually start with "funk_" or "cli_" prefix, so please avoid using those prefixes for Your Custom Functions!');
    }
    if (in_array($fn, $reserved_functions)) {
        cli_err_without_exit('[cli_match_file_and_fn()]: Function Name `' . $fn . '` is a Reserved Function Name. Please choose a different name!');
        cli_info('[cli_match_file_and_fn()]: The majority of Reserved Function Names usually start with "funk_" or "cli_" prefix, so please avoid using those prefixes for Your Custom Functions!');
    }
    if (!isset($matches[2])) {
        cli_info_without_exit('No Function Name provided, thus parsing as: `' . $file . '=>' . $fn . '`!');
    }
    return [$file, $fn];
}

// Returns valid extracted method and route or errors out
function cli_return_valid_method_n_route_or_err_out($string)
{
    if (!isset($string) || !is_string($string) || empty($string)) {
        cli_err_without_exit('[cli_return_valid_method_and_route_or_err_out()]: $string must be a Non-Empty String using the following Syntax: `method/route/segments/with/optional/:params`!');
        cli_info_without_exit('[cli_return_valid_method_and_route_or_err_out()]: For the Method, use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info_without_exit('[cli_return_valid_method_and_route_or_err_out()]: OR Use any of its shorthand versions: "g", "po", "pu", "d" OR "del", "pa"');
        cli_info_without_exit('[cli_return_valid_method_and_route_or_err_out()]: For the Route, write either "/route/segments" or "/route/segments/with/:params" (where :params is a Dynamic URI Segment of the Route)');
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
        'get' => 'DELETE',
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
        cli_err_without_exit('[cli_return_valid_method_and_route_or_err_out()]: Invalid Method Syntax! Use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info_without_exit('[cli_return_valid_method_and_route_or_err_out()]: OR Use any of its shorthand versions: "g" or "ge", "po", "pu", "d" OR "del", "pa"');
        cli_info('[cli_return_valid_method_and_route_or_err_out()]: A Single `/` is needed if you mean the Root Route `/` of that Method!');
    }
    $extractedMethod = $methodMatches[1];
    $method = $methodConvert[$methodMatches[1]] ?? '';
    if ($method === '') {
        cli_err_without_exit('[cli_return_valid_method_and_route_or_err_out()]: Invalid Method Syntax! Use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info('[cli_return_valid_method_and_route_or_err_out()]: OR Use any of its shorthand versions: "g" or "ge", "po", "pu", "d" OR "del", "pa"');
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
        cli_err_without_exit('[cli_return_valid_method_and_route_or_err_out()]: Invalid Route Syntax! Use either "/route/segments" or "/route/segments/with/:params" (where :params is a Dynamic URI Segment of the Route)');
        cli_info('[cli_return_valid_method_and_route_or_err_out()]: A Single `/` is needed if you mean the Root Route `/` of that Method as in `get/` OR `g/` and so on!');
    }
    // We iterate through $routeMatches[0] to error
    // out on duplicate route parameters. Otherwise
    // we return finalized $method and $route!
    foreach ($routeMatches[0] as $match) {
        if (str_starts_with($match, '/:')) {
            if (in_array($match, $routeParams)) {
                cli_err_without_exit('[cli_return_valid_method_and_route_or_err_out()]: Duplicate Route Parameter `' .  $match . '` found in the Route `' .  $method . $routeString . '`!');
                cli_info('[cli_return_valid_method_and_route_or_err_out()]: Fix so each Route Parameter (`/:param`) is unique and does not repeat in the Route Definition!');
            } else {
                $routeParams[] = $match;
            }
        }
        $route .= $match;
    }
    cli_info_without_exit("Parsed Method/Route:`$method$route`");
    return [$method, $route];
}

// Boolean whether a folder inside of "funkphp/routes" exists or not
function cli_routes_subfolder_exists($string)
{
    $exists = true;
    if (!isset($string) || !is_string($string) || empty($string) || !preg_match('/^[a-z_][a-z_0-9]*$/i', $string)) {
        cli_err_without_exit('[cli_route_key_folder_exists()]: $string must be A Valid Non-Empty String!');
        cli_info('[cli_route_key_folder_exists()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    // Lowercase the string
    $string = strtolower(trim($string));
    // Add "/" to string's start if not present
    if (!str_starts_with($string, '/')) {
        $string = '/' . $string;
    }
    if (is_dir(FUNKPHP_ROUTES_DIR . $string)) {
        $exists = true;
    } else {
        $exists = false;
    }
    return $exists;
}
// Boolean whether a folder inside of "funkphp/routes" is readable & writable
function cli_routes_subfolder_readable_n_writable($string)
{
    $both = false;
    if (!isset($string) || !is_string($string) || empty($string) || !preg_match('/^[a-z_][a-z_0-9]*$/i', $string)) {
        cli_err_without_exit('[cli_routes_subfolder_readable_n_writable()]: $string must be A Valid Non-Empty String!');
        cli_info('[cli_routes_subfolder_readable_n_writable()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    // Lowercase the string
    $string = strtolower(trim($string));
    // Add "/" to string's start if not present
    if (!str_starts_with($string, '/')) {
        $string = '/' . $string;
    }
    if (is_readable(FUNKPHP_ROUTES_DIR . $string) && is_writable(FUNKPHP_ROUTES_DIR . $string)) {
        $both = true;
    }
    return $both;
}
// Boolean whether $file inside of "funkphp/routes/$subfolder" exists or not
function cli_routes_subfolder_file_exists($subfolder, $file)
{
    $exists = true;
    if (!isset($subfolder) || !is_string($subfolder) || empty($subfolder) || !preg_match('/^[a-z_][a-z_0-9]*$/i', $subfolder)) {
        cli_err_without_exit('[cli_routes_subfolder_file_exists()]: $subfolder must be A Valid Non-Empty String!');
        cli_info('[cli_routes_subfolder_file_exists()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_routes_subfolder_file_exists()]: $file must be A Valid Non-Empty String!');
        cli_info('[cli_routes_subfolder_file_exists()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
    }
    // Lowercase both variables
    $subfolder = strtolower(trim($subfolder));
    $file = strtolower(trim($file));
    // Add "/" to string's start if not present
    if (!str_starts_with($subfolder, '/')) {
        $subfolder = '/' . $subfolder;
    }
    // Add ".php" to file's end if not present
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    if (is_readable(FUNKPHP_ROUTES_DIR . $subfolder . '/' . $file)) {
        $exists = true;
    } else {
        $exists = false;
    }
    return $exists;
}
// Boolean whether "funkphp/routes/$subfolder/$file.php" is readable & writable
function cli_routes_subfolder_file_readable_n_writable($subfolder, $file)
{
    $both = false;
    if (!isset($subfolder) || !is_string($subfolder) || empty($subfolder) || !preg_match('/^[a-z_][a-z_0-9]*$/i', $subfolder)) {
        cli_err_without_exit('[cli_routes_subfolder_file_readable_n_writable()]: $subfolder must be A Valid Non-Empty String!');
        cli_info('[cli_routes_subfolder_file_readable_n_writable()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_routes_subfolder_file_readable_n_writable()]: $file must be A Valid Non-Empty String!');
        cli_info('[cli_routes_subfolder_file_readable_n_writable()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
    }
    // Lowercase both variables
    $subfolder = strtolower(trim($subfolder));
    $file = strtolower(trim($file));
    // Add "/" to string's start if not present
    if (!str_starts_with($subfolder, '/')) {
        $subfolder = '/' . $subfolder;
    }
    // Add ".php" to file's end if not present
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    if (is_readable(FUNKPHP_ROUTES_DIR . $subfolder . '/' . $file) && is_writable(FUNKPHP_ROUTES_DIR . $subfolder . '/' . $file)) {
        $both = true;
    }
    return $both;
}

// Returns an array of status of $folder & $file and whether they:
// - exist, - are readable, - are writable. A single array with
// keys: `folder_exists`, `folder_readable`, `folder_writable`,
// `file_exists`, `file_readable`, `file_writable` are returned.
// Optionally return also whether a $fn (function name) exists in the file
function cli_folder_and_php_file_status($folder, $file)
{
    // Validate both are non-empty strings and match the regex
    if (!isset($folder) || !is_string($folder) || empty($folder) || !preg_match('/^[a-z_][a-z_0-9\/]*$/i', $folder)) {
        cli_err_without_exit('[cli_folder_and_php_file_status()]: $folder must be A Valid Non-Empty String!');
        cli_info('[cli_folder_and_php_file_status()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9\/]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_folder_and_php_file_status()]: $file must be A Valid Non-Empty String!');
        cli_info('[cli_folder_and_php_file_status()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
    }
    // Consistently get '$folder' . '/' . $file . '.php' always!
    $folder = trim($folder);
    $file = trim($file);
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
    $file = $folder . '/' . $file;

    // If file exists and is readable, check if function exists
    // by first reading the file and then checking if
    // the function name is in the file content using regex!
    $fns = null;
    if (is_file($file) && is_readable($file)) {
        $fileCnt = file_get_contents($file);
        if (!$fileCnt) { // We error out because if we asked for $fn then we should actually get its content or err out!
            cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT Read the File `' . $file . '` when it SHOUD have been Readable. This means it CANNOT retrieve the Named Functions in the File!');
        } else {
            $fnRegex = '/^function\s+([a-zA-Z0-9][a-zA-Z0-9_]*)\(&\$[^)]*\)(.*?^};)?$/ims';
            if (preg_match_all($fnRegex, $fileCnt, $fns)) {
                var_dump($fns);
            }
        }
    }
    return [
        'folder_exists' => is_dir($folder),
        'folder_readable' => is_readable($folder),
        'folder_writable' => is_writable($folder),
        'file_exists' => is_file($file),
        'file_readable' => is_readable($file),
        'file_writable' => is_writable($file),
        'functions' => (isset($fns) ? (array_flip([...$fns[1]])) : null),
    ];
}
