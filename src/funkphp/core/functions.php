<?php

/**
 * -----------------
 * FUNKPHP FUNCTIONS
 * -----------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
/***  HELPER-RELATED FUNCTIONS FOR FunkPHP ***/
// Data Dump ONLY $c['err'] array and die (stop execution)
function dderr()
{
    header('Content-Type: application/json', true, 200);
    echo json_encode($GLOBALS['c']['err'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Data Dump ONLY $c array and die (stop execution)
function ddc()
{
    header('Content-Type: application/json', true, 200);
    echo json_encode($GLOBALS['c'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Var_dump shorthand, doe NOT exit
function vd($data)
{
    // Apply CSS to force word wrap and limit width
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// Data Dump function to dump data and optionally return it as JSON
function dd($data, $json = false)
{
    // Dump the data and die (stop execution)
    if ($json) {
        header('Content-Type: application/json', true, 200);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } else {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
    exit;
}

// Data Dump function to dump data as JSON
function ddj($data, $json = false)
{
    header('Content-Type: application/json', true, 200);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Compares an IP address against a compiled wildcard Trie array.
 * Max 4 iterations. Blazing fast. Provide a white OR black list of IPs!
 */
function funk_use_ip_trie_matching(string $ip, array $trie): bool
{
    $octets = explode('.', $ip);
    $currentNode = $trie;

    foreach ($octets as $octet) {
        // 1. Check for the Short-Circuit Wildcard '!' (Matches everything from here down)
        if (isset($currentNode['!'])) {
            return true;
        }

        // 2. Check for an exact octet match
        if (isset($currentNode[$octet])) {
            $currentNode = $currentNode[$octet];
        }
        // 3. Check for a single-segment wildcard '*'
        elseif (isset($currentNode['*'])) {
            $currentNode = $currentNode['*'];
        }
        // No match found at this level
        else {
            return false;
        }
    }

    // If we successfully traversed 4 steps, check if this is an explicit match leaf
    return $currentNode === true || isset($currentNode['!']);
}

function funk_return_download($filePath, $fileName = null, $statusCode = 200)
{
    // Set the content type to application/octet-stream and the status code, then return the file response
    header('Content-Type: application/octet-stream', true, $statusCode);
    header('Content-Disposition: attachment; filename="' . ($fileName ?? basename($filePath)) . '"');
    readfile($filePath);
    exit;
}

// Function either sets and/or gets (if it sets, then it also returns that instance)
// It uses $c['INSTANCES'] array from config/_all.php
function funk_use_class(&$c, $objClassFolder, $newObjectOrExistingObject, $instanceKey = null)
{
    // $objClassFolder is either "vendor" (composer) or "classes" (custom classes)
    if (!in_array($objClassFolder, ['vendor', 'classes'])) {
        $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` received invalid $objClassFolder Value. Must be STRING (either "vendor" or "classes").';
        return null;
    }
    // $newObjectOrExistingObject is either a new object instance to SET, or an empty array to GET
    if (!is_object($newObjectOrExistingObject) && !is_string($newObjectOrExistingObject)) {
        $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` received invalid $newObjectOrExistingObject Value. Must be STRING (object to GET) or OBJECT (to SET).';
        return null;
    }
    // If it is a string, we check if it exists within the INSTANCES array and return it
    if (is_string($newObjectOrExistingObject)) {
        if (isset($c['INSTANCES'][$objClassFolder][$newObjectOrExistingObject])) {
            return $c['INSTANCES'][$objClassFolder][$newObjectOrExistingObject];
        } else {
            $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` did not find the requested instance `' . $newObjectOrExistingObject . '` in the `' . $objClassFolder . '` Folder. Typo and/or not set first?';
            return null;
        }
    }
    // If object, we first check that the instanceKey is a valid string
    else if (is_object($newObjectOrExistingObject)) {
        if (!is_string($instanceKey) || empty($instanceKey)) {
            $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` received invalid $instanceKey Value. Must be NON-EMPTY STRING when setting a new object instance.';
            return null;
        }
        // then check if it already exists for the given key in the given folder
        // which is NOT allowed as it is like overwriting an existing instance!
        if (isset($c['INSTANCES'][$objClassFolder][$instanceKey])) {
            // Hard-error if overwrite is not allowed
            if (!FUNKPHP_ALLOW_INSTANCE_OVERWRITE) {
                $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` cannot set the instance for key `' . $instanceKey . '` in the `' . $objClassFolder . '` Folder as it already exists! Overwriting existing instances is not allowed.';
                $err = 'The `funk_use_class()` cannot set the instance for key `' . $instanceKey . '` in the `' . $objClassFolder . '` Folder as it already exists! Overwriting existing instances is not allowed. Change to: `define("FUNKPHP_ALLOW_INSTANCE_OVERWRITE",true)` in `config/_all.php` (below $c["INSTANCES"] to `true` if you want to allow overwriting existing instances!';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            } else {
                $c['INSTANCES'][$objClassFolder][$instanceKey] = $newObjectOrExistingObject;
                return $c['INSTANCES'][$objClassFolder][$instanceKey];
            }
        } else {
            // Finally, we set the new object instance and return it
            $c['INSTANCES'][$objClassFolder][$instanceKey] = $newObjectOrExistingObject;
            return $c['INSTANCES'][$objClassFolder][$instanceKey];
        }
    }
    return null;
}

// FUNKPHP SESSION-BASED FUNCTIONS
function funk_session_started_or_start_it(&$c)
{ // If already active in this request lifecycle, exit instantly (Zero overhead)
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // Lazy infrastructure allocation: Connect to Redis/DB only when a session is actually requested!
    if (($c['SESSION']['driver'] ?? 'files') === 'redis') {
        // funk_connect_redis_infrastructure($c); TODO: FIX LATER Or remove?
    }
    // Configure native cookie settings right before booting
    // Pass the raw, pre-verified array straight to PHP. No runtime IF statements required!
    session_set_cookie_params([
        'lifetime' => $c['SESSION']['COOKIES']['SESSION_LIFETIME'] ?? 0,
        'path' => $c['SESSION']['COOKIES']['SESSION_PATH'] ?? '/',
        'domain' => $c['SESSION']['COOKIES']['SESSION_DOMAIN'] ?? '',
        'secure' => $c['SESSION']['COOKIES']['SESSION_SECURE'] ?? true,
        'httponly' => true,
        'samesite' => $c['SESSION']['COOKIES']['SESSION_SAMESITE'] ?? 'Lax',
    ]);
    // If it fails to start a session, throw an error and exit with a 500 Internal Server Error
    if (!session_start()) {
        $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
}
// The unified way to read session values across FunkPHP
function funk_session_get(&$c, string $key, $default = null)
{
    \funk_session_started_or_start_it($c);
    return $_SESSION[$key] ?? $default;
}
// The unified way to write session values across FunkPHP
function funk_session_set(&$c, string $key, $value): void
{
    \funk_session_started_or_start_it($c);
    $_SESSION[$key] = $value;
}

// Function to destroy the session and optionally set other cookies using funk_session_cookie_set as an array
function funk_session_destroy(&$c, $set_other_cookies_with_h_setcookie_as_array = [], $redirect = null)
{
    // If session is active, destroy it
    if (session_id() || session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
        \funk_session_cookie_set($c, session_name(), '', time() - 3600);
        \funk_session_cookie_set($c, "csrf", '', time() - 3600);

        // Optional funk_session_cookie_set to set other cookies
        if (!empty($set_other_cookies_with_h_setcookie_as_array)) {
            foreach ($set_other_cookies_with_h_setcookie_as_array as $cookie) {
                \funk_session_cookie_set(...$cookie);
            }
        }
    }
    // Redirect to the specified URI if provided
    if ($redirect) {
        header("Location: $redirect");
        exit;
    }
}

// Function to set a cookie with the specified parameters
function funk_session_cookie_set(&$c, $name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true, $samesite = 'strict')
{
    // Set the cookie with the specified parameters
    setcookie($name, $value, [
        'expires' => $expire,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}
function funk_generate_csrf(&$c, string $currentUri, ?int $lifetimeSeconds = null): string
{
    if (\funk_session_get($c, '_funk_csrf') === null) {
        $_SESSION['_funk_csrf'] = [];
    }
    // 1. Generate a completely unique, unpredictable token string
    $token = hash('sha256', random_bytes(32));
    // 2. Store the URI and expiration metadata INSIDE the array payload
    $_SESSION['_funk_csrf'][$token] = [
        'uri' => $currentUri,
        'expires' => ($lifetimeSeconds === null) ? null : (time() + $lifetimeSeconds)
    ];
    // Optional: Keep the session array from growing forever if a user opens 100 tabs
    if (count($_SESSION['_funk_csrf']) > 99) {
        array_shift($_SESSION['_funk_csrf']); // Drop the oldest token
    }
    return $token;
}

// FUNKPHP GENERIC RANDOMIZER FUNCTIONS
// This function uses the "The Random\Randomizer class" to generate a unique password
function funk_generate_random_password(&$c, $length = 20, $returnHashed = false)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();
    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];
    $special = [
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        '[',
        '\\',
        ']',
        '^',
        '_',
        '`',
        '{',
        '|',
        '}',
        '~',
    ];
    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers, $special);
    $total = count($all) - 1;

    // Prepare empty password string
    $password = '';

    // Add random characters to the password until it reaches the desired length
    while (strlen($password) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total); // Get a random index using the randomizer
        $password .= $all[$randomCharIndex];
    }

    // Split the password, shuffle it and join it back together using shuffleArray from randomizer class!
    $password = $randomizer->shuffleArray(str_split($password));
    $password = implode('', $password);

    // Return a hashed password if needed
    if ($returnHashed) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // Otherwise, return the generated password
    return $password;
}
// This function uses the "The Random\Randomizer class" to generate a unique number
function funk_generate_random_number(&$c, $length = 10)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();
    // Prepare numbers that can be used
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];
    // Prepare empty number string and total count of numbers array minus 1
    // and add random numbers to the number until it reaches the desired length
    $total = count($numbers) - 1;
    $number = '';
    // First number cannot be 0
    $randomCharIndex = $randomizer->getInt(1, $total);
    $number .= $numbers[$randomCharIndex];
    while (strlen($number) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total);
        $number .= $numbers[$randomCharIndex];
    }
    // Return the generated number as an integer
    return (int)$number;
}
// This function uses the "The Random\Randomizer class" to generate a unique user_id
function funk_generate_random_user_id(&$c, $length = 96)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();

    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers);
    $total = count($all) - 1;

    // Prepare empty user_id string and add random characters to the user_id until it reaches the desired length
    $user_id = '';
    while (strlen($user_id) < $length) {
        // Insert a "-" after every 24 characters except for the last one
        if (strlen($user_id) % 24 == 0 && strlen($user_id) != 0) {
            $user_id .= '-';
            continue;
        }
        $randomCharIndex = $randomizer->getInt(0, $total);
        $user_id .= $all[$randomCharIndex];
    }

    // Return the generated user_id
    return $user_id;
}
// This function uses the "The Random\Randomizer class" to generate a unique CSRF
function funk_generate_random_csrf(&$c, $length = 384)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();

    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers);
    $total = count($all) - 1;

    // Prepare empty CSRF string and add random characters to the CSRF until it reaches the desired length
    $csrf = '';
    while (strlen($csrf) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total);
        $csrf .= $all[$randomCharIndex];
    }

    // Return the generated CSRF
    return $csrf;
}
/***  ROUTE-RELATED PHP FUNCTIONS FOR FUNKPHP ***/
// Default FunkPHP Exception Handler that catches any uncaught exceptions and returns
// a JSON or HTML error response depending on the Accept Header of the request. It is
// used unless a user-defined Exception Handler is set by the Developer creating one
// own using the "funk_handle_uncaught_exception()" in "/src/funkphp/config/functions.php" file.
function funk_default_exception_handler(&$c, $e)
{
    $c['err']['UNCAUGHT_EXCEPTION'] = $e;
    \funk_use_log($c, "UNCAUGHT EXCEPTION BY DEVELOPER: " . $e->getMessage(), 'CRIT');
    $err = 'Tell the Developer: An Uncaught Exception Occurred: `' . $e->getMessage() . '` Please check the Logs for more details.';
    \funk_use_error_json_or_page($c, 500, ["internal_error" => $err], '500', $err);
}

// Default FunkPHP Registered Shutdown Function which runs after a request has been
// handled. It is used unless a user-defined register_shutdown_function is set by
// the Developer creating one own using the "funk_set_register_shutdown_function()"
// in the "/src/funkphp/config/functions.php" file.
function funk_default_register_shutdown_function(&$c)
{
    if (
        isset($c['<ENTRY>']['pipeline']['post_response'])
        && is_array($c['<ENTRY>']['pipeline']['post_response'])
        && array_is_list($c['<ENTRY>']['pipeline']['post_response'])
        && !empty($c['<ENTRY>']['pipeline']['post_response'])
    ) {
        \funk_run_pipeline_post_response($c);
    } else {
        $c['err']['MAYBE']['PIPELINE']['funk_run_post_request'][] = 'No Configured Post-Response Pipeline Functions (`"<ENTRY>" => "pipeline" => "post_response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post_response\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File!';
    }
}

/**
 * CUSTOM ERROR HANDLER: Outputs a raw HTML string directly to the client.
 *
 * This is used for simple, non-templated HTML error responses.
 *
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw HTML string to be echoed as the response body.
 * @return void              Sends the HTML response and terminates execution via `exit()`.
 */
function funk_use_error_raw_html(&$c, int $errCode, string $errMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($erCode)
        || !is_int($erCode)
        || $erCode < 100
        || $erCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_html_string()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_html_string()` Function. This should be a non-empty string!');
    }
    // Set the response code & header for HTML and output the message
    http_response_code($errCode);
    header('Content-Type: text/html; charset=utf-8');
    echo $errMsg;
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Outputs a raw plain text string directly to the client.
 *
 * This is typically used for simple API errors or basic, non-formatted text responses.
 *
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw plain text string to be echoed as the response body.
 * @return void              Sends the plain text response and terminates execution via `exit()`.
 */
function funk_use_error_raw_plain(&$c, int $errCode, string $errMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_plain_text()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_plain_text()` Function. This should be a non-empty string!');
    }
    // Set response code & header for plain text and output the message
    http_response_code($errCode);
    header('Content-Type: text/plain; charset=utf-8');
    echo $errMsg;
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Outputs a raw XML string directly to the client.
 *
 * This is used for providing error responses compatible with older SOAP/XML-based APIs.
 *
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw XML string to be echoed as the response body.
 * @return void              Sends the XML response and terminates execution via `exit()`.
 */
function funk_use_error_xml(&$c, int $errCode, string $errMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_xml()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_xml()` Function. This should be a non-empty string!');
    }
    // Set response code & header for XML and output the message
    http_response_code($errCode);
    header('Content-Type: application/xml; charset=utf-8');
    echo $errMsg;
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Displays a user-friendly error by including a specified HTML error page.
 *
 * This function clears output buffering, performs validation, sets appropriate security headers,
 * and includes the target error page file. It then terminates execution.
 *
 * NOTE ON HTML PAGE: The provided error message ($errMsg) is injected into the local scope
 * of the included error page file using the variable **$custom_error_message**.
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg      The human-readable error message. This message is accessible inside
 * the included page file via the variable **$custom_error_message**.
 * @param string $pageName    The filename (without '.php' extension) of the custom error page
 * located in the 'ROOT_FOLDER/page/complete/[errors]/' directory. Must be a readable file.
 * @return void              Sends the HTML response and terminates execution via `exit()`.
 */
function funk_use_error_page(&$c, int $errCode, string $errMsg, string $pageName)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_page()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_page()` Function. This should be a non-empty string!');
    }
    // When $pageName is not a string or empty or file not readable
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_handle_error_page()` Function. This should be a non-empty string that is also a readable file inside `/pages/compiled/[errors]/` directory!');
    }
    // Headers that also support <styles> tag inline
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
    try {
        $custom_error_message = $errMsg;
        include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
    } catch (\Throwable $e) {
        \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_page()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Executes a user-defined callback function to handle an error.
 *
 * This function clears the output buffer, performs validation on the error code
 * and callback, and then executes the callback, passing the global context ($c)
 * and optional custom data to it. The function exits execution after the callback
 * runs successfully or fails critically.
 *
 * IMPORTANT: Database Credentials are cleared before calling the callback function so they need to be set again if needed!
 *
 * @param array $c                     The global context array (passed by reference). 1st Argument passed to the Callback.
 * @param int $errCode                 The HTTP status code associated with the error (100-599).
 * @param string $errMsg               The Primary Error Message passed as the 2nd Argument after $c.
 * @param string $callbackName         The String name of the Callable Function or method to execute.
 * @param mixed $optionalCallbackData  Optional Data passed as the 3rd Argument to the Callback Function.
 * @return void                        Sends response and exits execution via `exit()`.
 */
function funk_use_error_callback(&$c, int $errCode, string $errMsg, string $callbackName, $optionalCallbackData = null)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_callback()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_callback()` Function. This should be a non-empty string!');
    }
    // When $callbackName is not a string or empty or not callable
    if (
        !isset($callbackName)
        || !is_string($callbackName)
        || empty($callbackName)
        || !is_callable($callbackName)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_handle_error_callback()` Function. This should be a non-empty string that is also callable!');
    }
    // Set response code, call function and exit
    http_response_code($errCode);
    try {
        $callbackName($c, $errMsg, $optionalCallbackData);
    } catch (\Throwable $e) {
        \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_callback()` Function with the following Error Message:`' . $e->getMessage() . '`.');
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Throws a standard PHP Exception to halt execution and be caught by a global handler.
 *
 * This function is intended for internal flow control where the error handling logic
 * is implemented higher up in the call stack (e.g., a global exception handler).
 * It does not set an HTTP status code or clear output buffering.
 *
 * @param array $c                 The global context array (passed by reference).
 * @param string $exceptionErrMsg  The message to be included in the new \Exception object.
 * @return void
 * @throws \Exception              Always throws a new \Exception with the provided message.
 */
function funk_use_error_throw(&$c, string $exceptionErrMsg)
{
    // The `funk_use_error_throw()` does not set any HTTP status code
    // OR "eating" output buffering since it just throws an exception
    // When $exceptionErrMsg is not a string or empty
    if (
        !isset($exceptionErrMsg)
        || !is_string($exceptionErrMsg)
        || empty($exceptionErrMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_throw()` Function. This should be a non-empty string!');
    }
    throw new Exception($exceptionErrMsg);
}

/**
 * CUSTOM ERROR HANDLER: Returns a JSON response. Accepts either a Direct Data Structure
 * or a Callable (string/closure) that must return the Data Structure when invoked.
 *
 * IMPORTANT: Database Credentials are cleared before calling the callable function
 * (the one optionally used for JSON Generation) so they need to be set again if needed!
 *
 * @param array $c      Global context array (by reference).
 * @param int $errCode  The HTTP status code.
 * @param mixed $jsonObjectOrCallableThatReturnsJSON The JSON data (array/object) OR a string/callable that returns JSON Data.
 * @return void         Sends response and exits.
 */
function funk_use_error_json(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_json()` Function. This should be an integer between 100 and 599!');
    }
    // When $jsonObjectOrStringThatReturnsJSON is not an Object/Array, nor a String that is also Callable
    if (
        !isset($jsonObjectOrStringThatReturnsJSON)
        || (
            !is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON)
            && (
                !is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)
            )
        )
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_handle_error_json()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // Set the response code for both JSON
    http_response_code($errCode);
    // Retrieve JSON Payload either directly or by verified callable
    $jsonData = $jsonObjectOrStringThatReturnsJSON;
    if (is_string($jsonData) && is_callable($jsonData)) {
        try {
            $jsonData = $jsonData($c);
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
        }
    }
    // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (\JsonException $e) {
        \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_json()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Determines error response based on the client's Accept header,
 * choosing between JSON (for APIs) or a dedicated HTML error page (as the universal fallback).
 *
 * Execution Logic:
 * 1. If the client accepts 'application/json' or 'text/json', a JSON response is generated.
 * 2. If JSON is NOT accepted (i.e., any other Accept header, or none at all), the specified
 * HTML error page is served as the guaranteed fallback.
 *
 * NOTE ON HTML PAGES: For the HTML error page, the custom message (passed in **$pageErrMsg**) is made
 * available to the included file via the variable **$custom_error_message**.
 *
 * IMPORTANT: Database Credentials are cleared before calling the callable function
 * (the one optionally used for JSON Generation) so they need to be set again if needed!
 *
 * @param array $c                                The global context array (passed by reference).
 * @param int $errCode                            The HTTP status code associated with the error (100-599).
 * @param mixed $jsonObjectOrStringThatReturnsJSON The source of the JSON payload. This must be an array, object,
 * or a string/callable that returns an array/object.
 * @param string $pageName                        The filename (without '.php') of the custom error page in the
 * 'ROOT_FOLDER/page/complete/[errors]/' directory. Must be a readable file.
 * @param string $pageErrMsg                      The human-readable message used exclusively for:
 * - The custom message on the HTML error page (**$custom_error_message**).
 * @return void                                   Sends the appropriate response headers/content and terminates execution via `exit()`.
 */
function funk_use_error_json_or_page(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON, string $pageName, string $pageErrMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    // if (ob_get_level() > 0) {
    //     ob_clean();
    // }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page()` Function. This should be an Integer between 100 and 599!');
    }
    // When $pageErrMsg is not a string or empty
    if (
        !isset($pageErrMsg)
        || !is_string($pageErrMsg)
        || empty($pageErrMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String!');
    }
    // When $jsonObjectOrStringThatReturnsJSON is not an Object/Array, nor a String that is also Callable
    if (
        !isset($jsonObjectOrStringThatReturnsJSON)
        || (
            !is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON)
            && (
                !is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)
            )
        )
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // When $pageName is not a string or empty or the file does not exist in the expected folder
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String and it must exist as a file in the `src/funkphp/pages/compiled/[errors]` directory!');
    }
    // Set the response code for both JSON and Page
    http_response_code($errCode);
    // JSON Response
    if (
        isset($c['req']['accept'])
        && is_string($c['req']['accept'])
        && !empty($c['req']['accept'])
        && (str_contains($c['req']['accept'], 'application/json') || str_contains($c['req']['accept'], 'text/json'))
    ) {
        // Retrieve JSON Payload either directly or by verified callable
        $jsonData = $jsonObjectOrStringThatReturnsJSON;
        if (is_string($jsonData) && is_callable($jsonData)) {
            try {
                $jsonData = $jsonData($c);
            } catch (\Throwable $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
        }
    }
    // Otherwise we return a Page even if that was not explicitly requested
    else {
        // Headers that also support <styles> tag inline
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        try {
            $custom_error_message = $pageErrMsg;
            include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
        }
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Provides flexible error handling based on the client's Accept header.
 *
 * This function attempts to handle the error in the following order:
 * 1. **HTML:** If the client accepts 'text/html', it includes the specified error page.
 * 2. **JSON:** If the client accepts 'application/json' or 'text/json', it encodes and returns the provided JSON data/callable result.
 * 3. **CALLBACK:** If neither HTML nor JSON is accepted, it executes the specified user-defined callback function.
 *
 * IMPORTANT ABOUT CALLBACK: Database Credentials are cleared before calling the callback function so they need to be set again if needed!
 *
 * NOTE ON HTML PAGES: For HTML error pages, the custom message is made available to the included file
 * via the variable **$custom_error_message**.
 *
 * @param array $c                               The global context array (passed by reference).
 * @param int $errCode                           The HTTP status code associated with the error (100-599).
 * @param string $errMsgForPageAndCallback       The human-readable message used for:
 * - The custom message on the HTML error page ($custom_error_message).
 * - The second argument passed to the callable function.
 * @param mixed $jsonObjectOrStringThatReturnsJSON The source of the JSON payload. This must be an array, object,
 * or a string/callable that returns an array/object.
 * @param string $pageName                       The filename (without '.php') of the custom error page in the
 * 'ROOT_FOLDER/page/complete/[errors]/' directory.
 * @param string $callableName                   The string name of the callable function to execute if neither
 * HTML nor JSON is accepted.
 * @param mixed $optionalCallbackData            Optional data passed as the third argument to the callback function.
 * @return void                                  Sends response headers/content and terminates execution via `exit()`.
 */
function funk_use_error_json_or_page_or_callback(&$c, int $errCode, string $errMsgForPageAndCallback, $jsonObjectOrStringThatReturnsJSON, string $pageName, string $callableName, $optionalCallbackData = null)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsgForPageAndCallback is not a string or empty
    if (
        !isset($errMsgForPageAndCallback)
        || !is_string($errMsgForPageAndCallback)
        || empty($errMsgForPageAndCallback)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String!');
    }
    // When $pageName is not a string or empty or the file does not exist in the expected folder
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String and it must exist as a file in the `src/funkphp/pages/compiled/[errors]` directory!');
    }
    // $callableName is not a string or empty or not callable
    if (
        !isset($callableName)
        || !is_string($callableName)
        || empty($callableName)
        || !is_callable($callableName)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String that is also Callable!');
    }
    // When $jsonObjectOrStringThatReturnsJSON is not an Object/Array, nor a String that is also Callable
    if (
        !isset($jsonObjectOrStringThatReturnsJSON)
        || (
            !is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON)
            && (
                !is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)
            )
        )
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // Set response code and check if Accept header contains text/html, application/json or text/json
    // If none of those headers then we call the callback function. We always exit nonetheless!
    http_response_code($errCode);
    // HTML Response
    if (
        isset($c['req']['accept'])
        && is_string($c['req']['accept'])
        && !empty($c['req']['accept'])
        && str_contains($c['req']['accept'], 'text/html')
    ) {
        // Headers that also support <styles> tag inline
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        try {
            $custom_error_message = $errMsgForPageAndCallback;
            include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
        }
    }
    // JSON Response
    else if (
        isset($c['req']['accept'])
        && is_string($c['req']['accept'])
        && !empty($c['req']['accept'])
        && (str_contains($c['req']['accept'], 'application/json') || str_contains($c['req']['accept'], 'text/json'))
    ) {
        // Retrieve JSON Payload either directly or by verified callable
        $jsonData = $jsonObjectOrStringThatReturnsJSON;
        if (is_string($jsonData) && is_callable($jsonData)) {
            try {
                $jsonData = $jsonData($c);
            } catch (\Throwable $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
        }
    }
    // CALLBACK Response
    else {
        try {
            $callableName($c, $errMsgForPageAndCallback, $optionalCallbackData);
        } catch (\Throwable $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` Function with the following Error Message:`' . $e->getMessage() . '`');
        }
    }
    exit();
}

// Function stores a user-focused message that is meant to be used in the final output (HTML page or JSON output)
function funk_collect_output_message(&$c, $level, $key, $message)
{
    // All three variables must be non-empty strings!
    if (
        !isset($level)
        || !is_string($level)
        || empty($level)
        || !in_array(strtolower($level), [ // Add more below in array as needed
            'info',
            'warning',
            'error',
            'debug',
            'critical',
            'notice',
            'alert',
            'emergency',
            'success',
            'failure',
        ])
        || !isset($key)
        || !is_string($key)
        || empty($key)
        || !isset($message)
        || !is_string($message)
        || empty($message)
    ) {
        error_log('FunkPHP: Invalid Parameters Passed to funk_collect_output_message() Function. Expected 3 Non-Empty String:s: [Level, Key, Message]!');
        return;
    }
    $c['req']['user_messages'][] = [
        'level'   => strtolower($level),
        'key'     => mb_strtoupper($key),
        'message' => $message,
    ];
}

/**
 * Pushes a log message into the global configuration object.
 * This log array is typically persisted (e.g., written to disk)
 * in the framework's shutdown function.
 *
 * @param array $c The global configuration array, passed by reference.
 * @param string $logMessage The message to log.
 * @param string $logType Optional type identifier (e.g., 'CRITICAL','FATAL', 'WARN','INFO' - these are just examples, and You decide what to use!).
 * @return void
 */
function funk_use_log(&$c, string $logMessage, string $logType = 'WARN'): void
{
    // Ensure the log structure exists, otherwise create it
    // and log that it was created due to not existing
    if (!isset($c['req']['log']) || !is_array($c['req']['log'])) {
        $c['req']['log'] = [];
        funk_use_log($c, 'The Log Array Did Not Exist, so it was Created Automatically!', 'INFO');
        return;
    }
    // Add the log entry with timestamp and type
    $c['req']['log'][] = [
        'timestamp' => time(),
        'type' => strtoupper($logType),
        'message' => $logMessage
    ];
    return;
}

/**
 * Placeholder for the final function that saves the log array to a file.
 * This function should be called within the application's shutdown handler.
 *
 * @param array $c The global configuration array, passed by reference.
 * @return void
 */
function funk_save_log(&$c): void
{
    // TODO: Add support later for different ways of saving (file, db, etc.)
    // Implementation needed here to serialize and write $c['req']['log']
    // to a persistent location (e.g., a file or database).
    // For now, we will simply log to the PHP error log for visibility.
    if (!empty($c['req']['log'])) {
        error_log("--- FUNKPHP POST-RESPONSE LOGS ---");
        error_log(print_r($c['req']['log'], true));
        error_log("--- END LOGS ---");
    }
    return;
}
// Function that clears the log array
function funk_clear_log(&$c, $saveFirst = false)
{
    if ($saveFirst === true) {
        \funk_save_log($c);
    }
    if (!isset($c['req']['log']) || !is_array($c['req']['log'])) {
        $c['err']['FUNCTIONS']['funk_clear_log'][] = 'The Log Array Did Not Exist, so it was Created Automatically!';
        \funk_use_log($c, 'The Log Array Did Not Exist, so it was Created Automatically!', 'INFO');
    } else {
        $c['req']['log'] = [];
        \funk_use_log($c, 'The Log Array was Cleared Successfully!', 'INFO');
    }
    return;
}

// Function to skip the post_response pipeline
function funk_skip_post_response(&$c)
{
    $c['req']['skip_post_response'] = true;
    ob_end_clean();
    return;
}

// `pipeline` is the list of functions to always run for each request (unless any
// of the functions terminates it early!) This is the main entry point for each request!
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_request(&$c)
{
    // 'defensive' = we check almost everything and output error to user if something gets wrong
    // Must be a non-empty numbered array
    if (
        !isset($c['<ENTRY>']['pipeline']['request'])
        || !is_array($c['<ENTRY>']['pipeline']['request'])
        || !array_is_list($c['<ENTRY>']['pipeline']['request'])
        || count($c['<ENTRY>']['pipeline']['request']) === 0
    ) {
        $c['err']['PIPELINE']['funk_run_pipeline_request'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "request"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'request\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File!';
        $err = 'Tell the Developer: No Pipeline Functions to run? Please check the `[\'pipeline\'][\'request\']` Key in the `funkphp/core/pipeline_request.php` File!';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }

    // Prepare for main loop to run each pipeline function
    $count = count($c['<ENTRY>']['pipeline']['request']);
    $pipeDir = ROOT_FOLDER . '/pipeline/request/';
    for ($i = 0; $i < $count; $i++) {
        // $current pipeline function should be a single associative array with a single value (which can be null)
        $current_pipe = $c['<ENTRY>']['pipeline']['request'][$i] ?? null;
        if (
            !isset($current_pipe)
            || !is_string($current_pipe)
        ) {
            $c['err']['PIPELINE']['funk_run_pipeline_request'][] = 'Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. Must be a String!';
            $err = 'Tell the Developer: Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. Must be a String!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $fnToRun = $current_pipe;
        $pipeToRun = $pipeDir . $fnToRun . '.php';

        if (!is_readable($pipeToRun)) {
            $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
            $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $runPipe = include_once $pipeToRun;
        $pipeFnToRun = NAMESPACE_PIPELINE_REQUEST . $fnToRun . '\\' . $fnToRun;
        if (is_callable($pipeFnToRun)) {
            $rawRun = $pipeFnToRun($c);
        }
        // HARD ERROR to not allow to pass security checks
        else {
            $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
            $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }

        // Clean up before running the next pipeline function
        $c['req']['current_pipeline'] = $current_pipe;
        unset($c['<ENTRY>']['pipeline']['request'][$i]);
        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['request'][$i + 1])
            && is_array($c['<ENTRY>']['pipeline']['request'][$i + 1])
            ? array_key_first($c['<ENTRY>']['pipeline']['request'][$i + 1])
            : null;
    }

    // Default values after either 'defensive' or 'happy' mode has run
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['request'] = null;
}

// Try run middlewares AFTER handled request (and this can
// also be due to being exited prematurely by the application)
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_post_response(&$c)
{
    // Use ob_start() to "swallow" any possibly unwanted output to the client
    // but before starting, check if it already exists and clear its previous
    // contents if it does!
    ob_start();

    // We only run post_response pipelines if not skipped by the application!
    // and they are also optional, so it can be skipped if not configured!
    if ($c['req']['skip_post_response']) {
        $c['err']['MAYBE']['PIPELINE']['POST-RESPONSE']['funk_run_pipeline_post_response'][] = 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!';
        funk_use_log($c, 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!', 'INFO');
        ob_end_clean();
        return;
    }

    // 'defensive' = we check almost everything and output error to user if something gets wrong
    // Must be a non-empty numbered array if it is set
    if (
        isset($c['<ENTRY>']['pipeline']['post_response'])
    ) {
        if (
            !is_array($c['<ENTRY>']['pipeline']['post_response'])
            || !array_is_list($c['<ENTRY>']['pipeline']['post_response'])
            || count($c['<ENTRY>']['pipeline']['post_response']) === 0
        ) {
            $c['err']['PIPELINE']['funk_run_pipeline_post_response'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post_response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post_response\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File!';
            funk_use_log($c, 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post_response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post_response\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File! - Function stops here!', 'CRITICAL');
            ob_end_clean();
            return;
        }
        // Prepare for main loop to run each pipeline function
        $count = count($c['<ENTRY>']['pipeline']['post_response']);
        $pipeDir = ROOT_FOLDER . '/pipeline/post_response/';
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }
            // $current pipeline function should be a single associative array with a single value (which can be null)
            $current_pipe = $c['<ENTRY>']['pipeline']['post_response'][$i] ?? null;
            if (
                !isset($current_pipe)
                || !is_string($current_pipe)
            ) {
                $c['err']['PIPELINE']['funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be a String!';
                funk_use_log($c, 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must a String - Function stops here!', 'CRITICAL');
                ob_end_clean();
                return;
            }
            $fnToRun = $current_pipe;
            $pipeToRun = $pipeDir . $fnToRun . '.php';
            // if = pipeline already exists in dispatchers, so reuse it but with newly passed value!
            if (isset($c['dispatchers']['pipeline']['post_response'][$fnToRun])) {
                if (is_callable($c['dispatchers']['pipeline']['post_response'][$fnToRun])) {
                    $runPipeKey = $c['dispatchers']['pipeline']['post_response'][$fnToRun];
                    // Clean up before running the next pipeline function
                    $c['req']['current_pipeline'] = $current_pipe;
                    unset($c['<ENTRY>']['pipeline']['post_response'][$i]);
                    $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        && is_array($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        ? array_key_first($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        : null;
                    $rawRun = $runPipeKey($c);
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    \funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
            }
            // else = pipeline does not exist yet, so include, store and run it with passed value!
            else {
                if (!is_readable($pipeToRun)) {
                    $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                    \funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory! - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
                $runPipe = include_once $pipeToRun;
                if (is_callable($runPipe)) {
                    $c['dispatchers']['pipeline']['post_response'][$fnToRun] = $runPipe;
                    // Clean up before running the next pipeline function
                    $c['req']['current_pipeline'] = $current_pipe;
                    unset($c['<ENTRY>']['pipeline']['post_response'][$i]);
                    $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        && is_array($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        ? array_key_first($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        : null;
                    $rawRun = $runPipe($c);
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    \funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
            }
        }
    }
    // Default values after either 'defensive' or 'happy' mode has run
    ob_end_clean(); // Clear any possibly unwanted output to the client
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['post_response'] = null;
}

// Same as above but used for the post response functions
// IMPORTANT: As you can see, it will remove all remaining
// pipeline functions, so use with care!
function funk_abort_pipeline_post_response(&$c)
{
    $c['req']['keep_running_pipeline'] = false;
    return;
}

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function funk_match_compiled_route(&$c, string $requestUri, array $methodRootNode): ?array
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
function funk_match_developer_route(&$c, string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes)
{
    // Prepare return values
    $matchedRoute = null;
    $matchedPathSegments = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ''; // Use as debug value
    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = \funk_match_compiled_route($c, $uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = 'NO MATCH FOR COMPILED_ROUTE_KEY (' . mb_strtoupper($method) . ') & ';
        return false;
    }
    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedPathSegments = $routeDefinition["segments"] ?? [];
        $matchedRouteParams = $routeDefinition["params"] ?? null;
        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $noMatchIn = 'ROUTE_MATCHED_BOTH';
            $c['req']['route'] = $matchedRoute;
            $c['req']['segments'] = $matchedPathSegments;
            $c['req']['params'] = $matchedRouteParams;
            $c['req']['matched_in'] = $noMatchIn;
            $c['req']['matched_config'] = $routeInfo['config'] ?? [];
            $c['req']['matched_pipeline'] = $routeInfo['pipeline'] ?? [];
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
            $c['req']['matched_middlewares'] = $matchedMiddlewareHandlers;
            return true;
        } else {
            $noMatchIn .= 'NO MATCH IN DEVELOPER_ROUTES(funkphp/core/pipeline_routes.php)';
        }
    } else {
        $noMatchIn .= 'NO MATCH IN COMPILED_ROUTES(funkphp/core/compiled_routes.php)';
    }
    // Return all Keys in matched Route and then overwrite some keys that are "hardcoded"
    return false;
}

// Function that validates dynamic parameters in a given route
// It uses the $c['req']['matched_params'] array so you only
// provide an array of ['param_key' => 'validationLogic'] pairs.
// It returns null when incorrectly used and true/false whether
// all provided parameters are valid or not.
function funk_params_are(&$c, $args)
{
    if (!isset($args) || !is_array($args)) {
        $c['err']['ROUTES']['funk_param_are'][] = 'No Parameters provided (by the Developer) to Validate for Current Route!';
        return null;
    }
    if (!isset($c['req']['params']) || !is_array($c['req']['params'])) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters (from the Visitor) to Validate for Current Route!';
        return null;
    }
    $params = $c['req']['params'];

    // When all parameters are valid, return true
    return true;
}
// Same as above but only takes a single Dynamic Parameter Key
// and returns true/false whether it is valid or not. Returns null
// when incorrectly used or no matched parameters.
function funk_param_is(&$c, $param_key, $validation)
{
    if (!isset($c['req']['params']) || !is_array($c['req']['params'])) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters to Validate for Current Route!';
        return null;
    }
    $param = $c['req']['params'][$param_key] ?? null;
    if ($param === null) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameter with Key `' . $param_key . '` to Validate for Current Route!';
        return null;
    }

    // When provided parameter is valid, return true
    return true;
}

// Quick Validate a $c['params'][$param_key] is one of many types:
function funk_param_is_string(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a string, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_string($param) && !empty($param);
}
function funk_param_is_number(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a string, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_numeric($param);
}
function funk_param_is_integer(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_integer'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is an integer, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_int($param) || intval($param) == $param;
}
function funk_param_is_float(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_float'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a float, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_float($param) || (is_numeric($param) && strpos($param, '.') !== false && floatval($param) == $param);
}
function funk_param_is_regex(&$c, $param_key, $regexStr)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_regex'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    if (!isset($regexStr) || !is_string($regexStr) || empty($regexStr)) {
        $c['err']['ROUTES']['funk_param_is_regex'][] = 'No Regex String provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter matches the regex, return true
    $param = $c['req']['params'][$param_key] ?? "";
    return preg_match($regexStr, $param) === 1;
}


/***  DATA-RELATED PHP FUNCTIONS FOR FUNKPHP ***/
// Function that either creates and returns a new database connection or returns
// an already existing one in $c['DATABASES'][<$dbKey>] if it exists
function funk_db_conn(&$c, $dbKey)
{
    // Set error and return null if no dbKey provided
    if (!isset($dbKey) || !is_string($dbKey)) {
        $c['err']['DATABASES']['funk_db_conn'][] = 'Invalid or missing $dbKey passed to funk_db_conn().';
        return null;
    }
    // First check if the connection already exists and thus just return it by reference
    if (isset($c['DATABASES'][$dbKey])) {
        return $c['DATABASES'][$dbKey];
    }
    // Can be used for all if/else statements below
    // TODO: Change $credentials source to $c['CONNECTIONS'] later!
    $credentials = null; // "null" for now!
    if ($credentials === null) {
        $c['err']['DATABASES']['funk_db_conn'][] = "No database configuration found for key '$dbKey'.";
        return null;
    }
    // 'driver' = mysqli
    if ($credentials['driver'] === 'mysqli') {
        $host = $credentials['host'] ?? 'localhost';
        $user = $credentials['user'] ?? 'root';
        $password = $credentials['password'] ?? '';
        $database = $credentials['database'] ?? '';
        $port = $credentials['port'] ?? 3306;
        $charset = $credentials['charset'] ?? 'utf8mb4';

        // Attempt creating a new mysqli connection
        try {
            $mysqli = new mysqli($host, $user, $password, $database, $port);
            // Check for connection errors
            if ($mysqli->connect_error) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . $mysqli->connect_error;
                return null;
            }

            // Set the charset
            if (!$mysqli->set_charset($charset)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Error loading character set ' . $charset . ' for ' . $dbKey . ': ' . $mysqli->error;
                // Not returning null here since connection is still valid
            }

            // Store the connection in the global array by reference
            $c['DATABASES'][$dbKey] = $mysqli;

            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = pgsql
    else if ($credentials['driver'] === 'pgsql') {
        $host = $credentials['host'] ?? 'localhost';
        $user = $credentials['user'] ?? 'postgres';
        $password = $credentials['password'] ?? '';
        $database = $credentials['database'] ?? '';
        $port = $credentials['port'] ?? 5432;
        $charset = $credentials['charset'] ?? 'utf8';
        $connString = "host=$host port=$port dbname=$database user=$user password=$password options='--client_encoding=$charset'";

        // Attempt creating a new pgsql connection
        try {
            $pgsql = pg_connect($connString);
            // Check for connection errors
            if ($pgsql === false) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . pg_last_error(null);
                return null;
            }
            // Store the connection in the global array by reference
            $c['DATABASES'][$dbKey] = $pgsql;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = mongodb
    elseif ($credentials['driver'] === 'mongodb') {
        $host = $credentials['host'] ?? 'localhost';
        $user = $credentials['user'] ?? '';
        $password = $credentials['password'] ?? '';
        $database = $credentials['database'] ?? '';
        $port = $credentials['port'] ?? 27017;
        $charset = $credentials['charset'] ?? 'utf8';
        // Build the MongoDB connection URI
        $authPart = ($user && $password) ? $user . ':' . $password . '@' : '';
        $uri = 'mongodb://' . $authPart . $host . ':' . $port;
        // Attempt creating a new MongoDB connection
        try {
            // Ensure the MongoDB extension is loaded
            if (!class_exists('MongoDB\Client')) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'MongoDB extension is not installed or enabled.';
                return null;
            }
            // Create a new MongoDB client
            $mongoClient = new \MongoDB\Client($uri);
            // Select the database
            $mongoDB = $mongoClient->selectDatabase($database);
            // Store the connection in the global array by reference
            $c['DATABASES'][$dbKey] = $mongoDB;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = redis
    elseif ($credentials['driver'] === 'redis') {
        $host = $credentials['host'] ?? '127.0.0.1';
        $port = $credentials['port'] ?? 6379;
        $password = $credentials['password'] ?? null;
        $database = $credentials['database'] ?? 0;
        // Attempt creating a new Redis connection
        try {
            // Ensure the Redis extension is loaded
            if (!class_exists('\Redis')) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis extension is not installed or enabled.';
                return null;
            }
            $redis = new \Redis();
            // 1. Connect
            if (!$redis->connect($host, $port)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis connection failed for ' . $dbKey;
                return null;
            }
            // 2. Authenticate (if password provided)
            if ($password !== null && !$redis->auth($password)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis authentication failed for ' . $dbKey;
                $redis->close(); // Important to close a failed connection
                return null;
            }
            // 3. Select Database
            if (!$redis->select($database)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis database selection failed for ' . $dbKey . ' (DB: ' . $database . ')';
                $redis->close();
                return null;
            }
            // Store the connection object
            $c['DATABASES'][$dbKey] = $redis;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (\Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = memcached
    elseif ($credentials['driver'] === 'memcached') {
        $host = $credentials['host'] ?? '127.0.0.1';
        $port = $credentials['port'] ?? 11211;
        // Attempt creating a new Memcached connection
        try {
            // Ensure the Memcached extension is loaded
            // Note: Use '\Memcached' (modern) over '\Memcache' (legacy)
            if (!class_exists('\Memcached')) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached extension is not installed or enabled.';
                return null;
            }
            $memcached = new \Memcached();
            // Memcached uses addServer to connect. It returns true/false on success.
            if (!$memcached->addServer($host, $port)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached failed to add server for ' . $dbKey . '.';
                return null;
            }
            // Optional: Check if the server is actually available (e.g., ping)
            // Note: Memcached connections are often "lazy," but we can check the status.
            $stats = $memcached->getStats();
            if (empty($stats) || !isset($stats["$host:$port"]) || $stats["$host:$port"]['pid'] === -1) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached server ' . $host . ':' . $port . ' is unavailable.';
                return null;
            }
            // Store the connection object
            $c['DATABASES'][$dbKey] = $memcached;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (\Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }

    // TODO: Add more here later
    // DRIVER NOT SUPPORTED YET - this is always the last one
    else {
        $c['err']['DATABASES']['funk_db_conn'][] = 'Database driver "' . $credentials['driver'] . '" for key `' . $dbKey . '` is not supported in current version of FunkPHP.';
        return null;
    }
}

// Function that validates a set of rules for a given single input field/data
function funk_validation_validate_rules(&$c, $inputValue, $fullFieldName, array $rules, array &$currentErrPath): void
{
    // Extract some important flag-like rules from the rules array
    $stop = array_key_exists('stop', $rules);
    $nullable = array_key_exists('nullable', $rules);
    $required = array_key_exists('required', $rules);
    $field = array_key_exists('field', $rules);
    // Check if "field" rule exist since that contains the custom
    // field name used by the Developer that would then apply to
    // ALL rules for this given input field/data!
    if ($field) {
        $fullFieldName = $rules['field']['value'] ?? $fullFieldName;
        unset($rules['field']);
    }
    // If required rule exist, we grab its value & error and unset it
    // from the array of rules so we do not loop through it later
    if ($required) {
        $required = $rules['required'];
        unset($rules['required']);
    }
    // If stop rule exist, we just unset it because the boolean value
    // is enough to know if we should stop further validation later
    if ($stop) {
        unset($rules['stop']);
    }
    // if nullable exists and the input value is null,
    // then we can just skip validation for this field
    if ($nullable && $inputValue === null) {
        return;
    }
    // Now use the required rule to validate
    // the input value if it exists and we
    // stored its value + error message
    if ($required) {
        $ruleValue = $required['value'] ?? null;
        $customErr = $required['err_msg'] ?? null;
        // echo "Running `funk_validate_required` for field `$fullFieldName` with value `" . (is_string($inputValue) ? $inputValue :  json_encode($inputValue)) . "`\n";
        //$error = \funk_validate_required($fullFieldName, $inputValue, $ruleValue, $customErr);
        $error = '';
        // We set the error we got from the
        // required validation meaning it failed
        if ($error !== null) {
            $currentErrPath['required'] = $error;
            $c['v_ok'] = false;
            // MAYBE EXPERIMENTAL: Might not work as intended in all cases
            // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
            if (
                isset($c['v_config']['stop_all_on_first_error'])
            ) {
                $c['v_config']['stop_all_on_first_error'] = true;
                return;
            }
            // Stop further validation for this field as
            // 'required' failed and if 'stop' is true!
            if ($stop) {
                return;
            }
        }
    }
    // Categorize found data type rule so "min" and "max" and similar
    // ambiguous rules can be applied to the correct data type!
    // We will swiftly loop through to find it. Thanks to the priority
    // order of the rules, it should actually be the first rule right
    // after "nullable", "required", & "stop" rules if they exist!
    $categorizedDataTypeRules = [
        // Rules that generally apply to string-like inputs
        // Dates are often validated as strings
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
        // Rules that generally apply to numeric inputs
        // "numbers" = More general numeric type
        'number_types' => [
            'digit' => true,
            'integer' => true,
            'float' => true,
            'number' => true,
        ],
        // Rules that generally apply to array-like inputs
        // Lists are often treated as arrays
        // Sets can be treated as arrays with unique values
        'array_types' => [
            'array' => true,
            'list' => true,
            'set' => true,
            'enum' => true,
        ],
        'file_types' => [
            'file' => true,
            'image' => true,
            'video' => true,
            'audio' => true,
        ],
        // Rules for arrays, objects, and other complex structures
        // JSON is typically validated as a string or an object/array
        // Enums can be strings or numbers, but often involve specific sets
        // Similar to enum, for validating against a predefined set
        // Booleans are distinct, but often processed separately from numbers/strings
        'complex_types' => [
            'null' => true,
            'object' => true,
            'unchecked' => true,
            'checked' => true,
            'boolean' => true,
        ],
    ];
    $foundTypeRule = null;
    $foundTypeCat = null;
    foreach ($rules as $ruleName => $ruleConfig) {
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
            break;
        } elseif (isset($categorizedDataTypeRules['file_types'][$ruleName])) {
            $foundTypeRule = $ruleName;
            $foundTypeCat = 'file_types';
            break;
        }
    }
    if ($foundTypeRule) {
        $validatorFn = 'funk_validate_' . $foundTypeRule;
        $ruleConfig = $rules[$foundTypeRule];
        $ruleValue = $ruleConfig['value'] ?? null;
        $customErr = $ruleConfig['err_msg'] ?? null;

        // SPECIAL EDGE-CASE for 'password/password_custom' and 'password_confirm' where
        // first one is used to store the password second one to match against!
        if ($foundTypeRule === 'password' || 'password_custom' === $foundTypeRule) {
            $c['v_config']['passwords_to_match'][$fullFieldName] = is_string($inputValue) ? (string)$inputValue : null;
        } elseif ($foundTypeRule === 'password_confirm') {
            $ruleValue = $c['v_config']['passwords_to_match'][$ruleValue] ?? null;
        }
        // Validate matching Data Type Rule
        $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);
        // Mark validation as failed if error is not null
        // and also stop if optionally set
        if ($error !== null) {
            $currentErrPath[$foundTypeRule] = $error;
            $c['v_ok'] = false;
            // MAYBE EXPERIMENTAL: Might not work as intended in all cases
            // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
            if (
                isset($c['v_config']['stop_all_on_first_error'])
            ) {
                $c['v_config']['stop_all_on_first_error'] = true;
                return;
            }
            if (isset($rules['stop'])) {
                return;
            }
        }
        // Remove the found Data Type to not repeat
        unset($rules[$foundTypeRule]);
    }
    // In case no valid data type rule was found
    // (should only happen if it hasn't been added yet)
    else {
        // Because we find no valid data type rule, nothing else
        // would work as expected so we just set the error and quit
        // validation for this input field! Internal error is logged!
        $currentErrPath[$foundTypeRule] = "This is unknown data type: '{$foundTypeRule}' in field '{$fullFieldName}'. Please tell the Developer about it since validation cannot continue without a valid data type provided!";
        $c['err']['VALIDATIONS']['funk_validation_validate_rules'][] = "Unknown Data Type Validation Rule: '{$foundTypeRule}' for field '{$fullFieldName}'.";
        $c['v_ok'] = false;
        // MAYBE EXPERIMENTAL: Might not work as intended in all cases
        // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
        if (
            isset($c['v_config']['stop_all_on_first_error'])
        ) {
            $c['v_config']['stop_all_on_first_error'] = true;
            return;
        }
        return;
    }
    // Rule mappings based on data type categories
    $mappedRulesBasedTypeCategory = [
        'string_types' => [
            'min' => 'minlen',
            'max' => 'maxlen',
            'exact' => 'exactlen',
            'between' => 'betweenlen',
            'size' => 'sizelen',
        ],
        'number_types' => [
            'min' => 'minval',
            'max' => 'maxval',
            'exact' => 'exactval',
            'between' => 'betweenval',
            'size' => 'sizeval',
        ],
        'array_types' => [
            'count' => 'arraycount',
            'min' => 'mincount',
            'max' => 'maxcount',
            'exact' => 'exactcount',
            'between' => 'betweencount',
            'size' => 'sizecount',
        ],
        'complex_types' => [],
        'file_types' => [
            'min' => 'min_filesize',
            'max' => 'max_filesize',
            'exact' => 'exact_filesize',
            'between' => 'between_filesize',
            'size' => 'size_filesize',
        ],
    ];
    // We check if $c_['v_config']['source'] is set to "GET" meaning we should
    // try to convert $inputValue to numeric value if  $foundTypeRule is either
    // digit, integer, float, or number. This is because GET variables are
    // always strings and we need to convert them to the appropriate type!
    if (
        isset($c['v_config']['source']) &&
        $c['v_config']['source'] === 'GET' &&
        $foundTypeCat === 'number_types'
    ) {
        // Check if numeric then convert it to the appropriate type
        // (digit|integer=intval,float=floatval,number=floatval)
        if (is_numeric($inputValue)) {
            if ($foundTypeRule === 'digit') {
                $inputValue = (int)$inputValue ?? null;
            } elseif ($foundTypeRule === 'integer') {
                $inputValue = intval($inputValue) ?? null;
            } elseif ($foundTypeRule === 'float') {
                $inputValue = floatval($inputValue) ?? null;
            } elseif ($foundTypeRule === 'number') {
                $inputValue = floatval($inputValue) ?? null;
            }
        }
    }
    // ITERATING THROUGH REMAINING RULES THIS INPUT FIELD
    foreach ($rules as $rule => $ruleConfig) {
        $ruleValue = $ruleConfig['value'];
        $customErr = $ruleConfig['err_msg'];
        $errorKey = $rule;
        // Check if $rule is the mapped rule ($foundTypeCat['$foundTypeRule'])
        // and set $Rule to that value then before concatenating.
        // If the rule is not in the mapped rules, we just use it as is
        if (isset($mappedRulesBasedTypeCategory[$foundTypeCat][$rule])) {
            $rule = $mappedRulesBasedTypeCategory[$foundTypeCat][$rule];
        }
        // Dynamically call the validation function for this rule
        // Assuming your rule functions are named funk_validate_rule
        $validatorFn = 'funk_validate_' . $rule;

        if (function_exists($validatorFn)) {
            $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);
            // Set the error message for this specific rule
            // if it is not null, meaning validation failed
            // Also stop remaining validation for
            // this input data if 'stop' is true!
            if ($error !== null) {
                $currentErrPath[$errorKey] = $error;
                $c['v_ok'] = false;
                // MAYBE EXPERIMENTAL: Might not work as intended in all cases
                // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
                // Stop ALL Validation if "stop_all_on_first_error" key exists
                if (
                    isset($c['v_config']['stop_all_on_first_error'])
                ) {
                    $c['v_config']['stop_all_on_first_error'] = true;
                    return;
                }
                if ($stop) {
                    return;
                }
            }
        } else {
            // Handle unknown validator functions (e.g., log, add to $c['err'])
            $currentErrPath[$foundTypeRule] = "This is unknown data type: '{$foundTypeRule}' in field '{$fullFieldName}'. Please tell the Developer about it. Validation will continue though!";
            $c['err']["VALIDATIONS"]['funk_validation_validate_rules'][] = "Unknown Data Validation Rule: '{$foundTypeRule}' for field '{$fullFieldName}'.";
            $c['v_ok'] = false;
            // MAYBE EXPERIMENTAL: Might not work as intended in all cases
            // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
            // Stop ALL Validation if "stop_all_on_first_error" key exists
            if (
                isset($c['v_config']['stop_all_on_first_error'])
            ) {
                $c['v_config']['stop_all_on_first_error'] = true;
                return;
            }
        }
        // Next rule will be processed
    }
};

// This is the improved version of funk_validation_recursively (RIP)
function funk_validation_recursively_improved(
    &$c,
    $inputData,
    array $validationRules,
    array &$currentErrPath,
    &$currentValidData
) {
    // Iterate through the main `return array()` from optimized validation array
    foreach ($validationRules as $DXKey => $rulesOrNestedFields) {
        // TODO: EXPERIMENTAL: Might not work as intended in all cases
        // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
        // If stop_all_on_first_error is true, we stop further validation
        // and return immediately with the current error path
        if (
            isset($c['v_config']['stop_all_on_first_error'])
            && $c['v_config']['stop_all_on_first_error'] === true
        ) {
            if (!empty($currentErrPath)) {
                $c['v'] = $currentErrPath;
            }
            return;
        }
        // Here we initialize all the global configs (this is ALWAYS the first key)
        // TODO: EXPERIMENTAL: Might not work as intended in all cases
        if ($DXKey === '<CONFIG>') {
            // Set "STOP_ALL_ON_FIRST_ERROR" key to true in $c['v_config']['stop_all_on_first_error']
            // as a global configuration for the validation process (including if it is a nested field)
            // Any error will check if this exists and set it to true and when true it all returns
            // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
            if (
                isset($rulesOrNestedFields['stop_all_on_first_error'])
                || isset($rulesOrNestedFields['stop_all_first'])
            ) {
                $c['v_config']['stop_all_on_first_error'] = false;
            }
            // This is used at the end of all validation and enables to ONLY
            // show all v_data and only if all validation rules passed!
            if (isset($rulesOrNestedFields['show_v_data_only_if_all_valid'])) {
                $c['v_config']['show_v_data_only_if_all_valid'] = true;
            }
            // Finally delete the "<CONFIG>" key since it has been fully processed
            unset($validationRules[$DXKey]);
            continue;
        }
        // When root key is NOT "*" (but "key.*", "key" or "key.subkey" and so on!)
        if ($DXKey !== '*') {
            // Get current rules, input data|null and initialize current error path
            $currentRules = $rulesOrNestedFields['<RULES>'] ?? null;
            $currentInputData = $inputData[$DXKey] ?? null;
            $currentErrPath[$DXKey] = [];
            $currentValidData[$DXKey] = null;
            $wildCardExist = ($DXKey === '*' || key($rulesOrNestedFields) === '*');
            // If "<RULES>" node exists, we process it by passing it to the
            // funk_validation_validate_rules function which also receives
            // the current error path!
            if ($currentRules) {
                \funk_validation_validate_rules(
                    $c,
                    $currentInputData,
                    $DXKey,
                    $rulesOrNestedFields['<RULES>'],
                    $currentErrPath[$DXKey],
                );
                // Remove the <RULES> key after processing
                // If no errors were found for this key, we can just remove it
                // We also set the data to the current valid data array
                unset($rulesOrNestedFields['<RULES>']);
                if (empty($currentErrPath[$DXKey])) {
                    unset($currentErrPath[$DXKey]);
                    $currentValidData[$DXKey] = $currentInputData;
                } else {
                    unset($currentValidData[$DXKey]);
                }
            }
            // If there still exists elements in the $rulesOrNestedFields we
            // can assume that they are nested fields or the * wildcard
            // but we first ONLY process the nested fields first
            if (
                is_array($rulesOrNestedFields)
                && !empty($rulesOrNestedFields)
                && !$wildCardExist
            ) {
                foreach ($rulesOrNestedFields as $name => $nestedField) {
                    // if ($name === '<RULES>' || $name === '*') {
                    //     continue; // Skip the <RULES> key if it exists
                    // } <- This might not be needed since we always checked, processed
                    // and unset it above in the currentRules check
                    if (is_array($nestedField) && $name !== '*') {
                        // If the nested field is an array, we can recurse into it
                        // and pass the current error path for this nested field
                        $currentErrPath[$DXKey][$name] = [];
                        $currentValidData[$DXKey][$name] = null;
                        \funk_validation_recursively_improved(
                            $c,
                            $inputData[$DXKey] ?? null,
                            $rulesOrNestedFields ?? [],
                            $currentErrPath[$DXKey],
                            $currentValidData[$DXKey]
                        );
                    }
                }
                // After loop check if the error path is empty
                if (empty($currentErrPath[$DXKey])) {
                    unset($currentErrPath[$DXKey]);
                    // If no errors were found, we can set the valid data
                    $currentValidData[$DXKey] = $currentInputData;
                } else {
                    unset($currentValidData[$DXKey]);
                }
            }
            // Handle "*" wildcard for Numbered Arrays (works when they are in
            // the $wildCardExist = $rulesOrNestedFields, not $DXKey === '*' yet!)
            if ($wildCardExist) {
                $wildCardRules = $rulesOrNestedFields['*']["<RULES>"] ?? null;
                // If Rules found for Numbered Array * we pass on the rules to the
                // validation function and then set the current error path.
                // Only if it all passes do we actually start iterating through the numbered array
                $actualCount = (is_array($currentInputData)
                    && array_is_list($currentInputData)) ? count($currentInputData) : 0;
                // If Rules for Numbered Array * exist, we can validate it
                if ($wildCardRules) {
                    $currentErrPath[$DXKey] = [];
                    \funk_validation_validate_rules(
                        $c,
                        $currentInputData,
                        $DXKey,
                        $wildCardRules,
                        $currentErrPath[$DXKey]
                    );
                    // Only if it is empty do we actually iterate
                    if (empty($currentErrPath[$DXKey])) {
                        unset($currentErrPath[$DXKey]);
                        unset($rulesOrNestedFields['*']["<RULES>"]);
                        // We now extract the number of iterations
                        // from the Wildcard Rules array, which should exist
                        // otherwise we set to 0
                        $iterations = 0;
                        if (
                            isset($wildCardRules['count']['value'])
                        ) {
                            $iterations = (int)$wildCardRules['count']['value'] ?? 0;
                        } else if (isset($wildCardRules['count']['value'])) {
                            $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                        } else if (isset($wildCardRules['exact']['value'])) {
                            $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                        } else if (isset($wildCardRules['size']['value'])) {
                            $iterations = (int)$wildCardRules['size']['value'] ?? 0;
                        } else if (isset($wildCardRules['between']['value'])) {
                            $iterations = (int)$wildCardRules['between']['value'][1] ?? 0;
                        }
                        // If iterations is larger than the actual count,
                        // we can set it to the actual count so we do not
                        // iterate more than the actual number of elements
                        $iterations = ($iterations > 0) ? min($iterations, $actualCount) : $actualCount;
                        // Now we can recurse into the validation function for this
                        // numbered array element when iterations is greater than 0!
                        if ($iterations > 0) {
                            for ($index = 0; $index < $iterations; $index++) {
                                $currentErrPath[$DXKey][$index] = [];
                                $currentValidData[$DXKey][$index] = null;
                                \funk_validation_recursively_improved(
                                    $c,
                                    $currentInputData[$index] ?? null,
                                    $rulesOrNestedFields['*'],
                                    $currentErrPath[$DXKey][$index],
                                    $currentValidData[$DXKey][$index]
                                );
                                // Unset if no errors were found
                                if (empty($currentErrPath[$DXKey][$index])) {
                                    unset($currentErrPath[$DXKey][$index]);
                                    $currentValidData[$DXKey][$index] = $currentInputData[$index];
                                }
                                // Unset non-existing/invalid data
                                else {
                                    unset($currentValidData[$DXKey][$index]);
                                }
                            }
                            // Also unset for the main DXKey if no errors were found
                            if (empty($currentErrPath[$DXKey])) {
                                unset($currentErrPath[$DXKey]);
                                //HM? $currentValidData[$DXKey] = $currentInputData;
                            }
                            // Unset non-existing/invalid data
                            else {
                                unset($currentValidData[$DXKey]);
                            }
                        }
                    }
                }
                // We found Wildcard * Indicator but not the Rules
                // so throw possible misconfiguration error!
                else {
                    $c['err']['VALIDATIONS']['funk_validation_recursively_improved'][] = "Validation Function for `$DXKey` with Wildcard * found but no Rules were defined for it!";
                    $currentErrPath[$DXKey] = "Failed to Validate Numbered Array in `$DXKey`. Tell the Developer about it since this is possibly a misconfiguration in the so called `returned validation array()`!";
                }
            }
        }
        // MAYBE EXPERIMENTAL: Might not work as intended in all cases, but otherwise nicely done!!! ^_^
        // When root key IS "*" meaning everything is shifted to the left where the first key
        // is the wildcard "*" and the rest are the nested fields meaning all must be different.
        if ($DXKey === '*') {
            $currentInputData = $inputData ?? null;
            $currentErrPath[$DXKey] = [];
            $currentValidData[$DXKey] = null;
            $wildCardRules = $rulesOrNestedFields["<RULES>"] ?? null;
            // If Rules found for Numbered Array * we pass on the rules to the
            // validation function and then set the current error path.
            // Only if it all passes do we actually start iterating through the numbered array
            $actualCount = (is_array($currentInputData)
                && array_is_list($currentInputData)) ? count($currentInputData) : 0;
            // If Rules for Numbered Array * exist, we can validate it
            if ($wildCardRules) {
                $currentErrPath[$DXKey] = [];
                \funk_validation_validate_rules(
                    $c,
                    $currentInputData,
                    $DXKey,
                    $wildCardRules,
                    $currentErrPath[$DXKey]
                );
                // Only if it is empty do we actually iterate
                if (empty($currentErrPath[$DXKey])) {
                    unset($currentErrPath[$DXKey]);
                    unset($rulesOrNestedFields["<RULES>"]);
                    unset($currentValidData[$DXKey]); // Delete "$c['v_data']['*'] = null"
                    // We now extract the number of iterations
                    // from the Wildcard Rules array, which should exist
                    // otherwise we set to 0
                    $iterations = 0;
                    if (
                        isset($wildCardRules['count']['value'])
                    ) {
                        $iterations = (int)$wildCardRules['count']['value'] ?? 0;
                    } else if (isset($wildCardRules['count']['value'])) {
                        $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                    } else if (isset($wildCardRules['exact']['value'])) {
                        $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                    } else if (isset($wildCardRules['size']['value'])) {
                        $iterations = (int)$wildCardRules['size']['value'] ?? 0;
                    } else if (isset($wildCardRules['between']['value'])) {
                        $iterations = (int)$wildCardRules['between']['value'][1] ?? 0;
                    }
                    // If iterations is larger than the actual count,
                    // we can set it to the actual count so we do not
                    // iterate more than the actual number of elements
                    $iterations = ($iterations > 0) ? min($iterations, $actualCount) : $actualCount;
                    // Now we can recurse into the validation function for this
                    // numbered array element when iterations is greater than 0!
                    if ($iterations > 0) {
                        for ($index = 0; $index < $iterations; $index++) {
                            $currentErrPath[$index] = [];
                            $currentValidData[$index] = null;
                            \funk_validation_recursively_improved(
                                $c,
                                $currentInputData[$index] ?? null,
                                $rulesOrNestedFields,
                                $currentErrPath[$index],
                                $currentValidData[$index]
                            );
                            // Unset if no errors were found
                            if (empty($currentErrPath[$index])) {
                                unset($currentErrPath[$index]);
                            }
                            // Unset non-existing/invalid data
                            else {
                                unset($currentValidData[$index]);
                            }
                        }
                        // TODO: Maybe is needed after all in special case
                        // when root is numbered array?
                        // Also unset for the main DXKey if no errors were found
                        // if (empty($currentErrPath[$DXKey])) {
                        //     unset($currentErrPath[$DXKey]);
                        // }
                    }
                }
            }
            // We found Wildcard * Indicator but not the Rules
            // so throw possible misconfiguration error!
            else {
                $c['err']['VALIDATIONS']['funk_validation_recursively_improved'][] = "Validation Function for `$DXKey` with Wildcard * found but no Rules were defined for it!";
                $currentErrPath[$DXKey] = "Failed to Validate Numbered Array in `$DXKey`. Tell the Developer about it since this is possibly a misconfiguration in the so called `returned validation array()`!";
            }
        }
    }
}

// Load a specific SQL Handler and its SQL Function (s_fileName.php => s_functionName)
// to load its SQL String, Hydration Array, Matched Validation Fields, etc. This is
// used to run SQL queries and hydrate the data from the database. This is NOT the function
// that actually runs the SQL query, but rather prepares the necessary data for it!
function funk_load_sql(&$c, $sqlHandler, $sqlFunction)
{
    // Check that both "$validationHandler, $validationFunction" are strings
    if (!is_string($sqlHandler) || !is_string($sqlFunction)) {
        $c['err']['SQL']['funk_use_sql'][] = 'funk_use_sql() needs Valid Strings for `\$sqlHandler` and `\$sqlFunction`. First is the SQL Handler File Name `s_FileName` without extension and second is the SQL Function Name `s_FunctionName`!';
        return false;
    }
    $sqlFunk = null;
    // Return SQL Handler=>Function if it exists or try to load
    // it from the file or return false and set an error!
    if (isset($c['dispatchers']['sql'][$sqlHandler])) {
        if (!is_callable($c['dispatchers']['sql'][$sqlHandler])) {
            $c['err']['SQL']['funk_use_sql'][] = 'Already Loaded SQL Handler `' . $sqlHandler . '` is not callable. Has it been mutated after first loading/use?';
            return false;
        }
        $sqlFunk = $c['dispatchers']['sql'][$sqlHandler]($c, $sqlFunction) ?? null;
        if ($sqlFunk === null) {
            $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` did not return the SQL Handler Function `' . $sqlFunction . '`. Does it exist in the File as a callable function with the correct name?';
            return false;
        } else {
            return $sqlFunk;
        }
    }
    // When SQL Handler not found in $c['dispatchers']['sql'] array
    else {
        if (!is_readable(ROOT_FOLDER . '/data/sql/' . $sqlHandler . '.php')) {
            $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` not found or not readable. Does the file exist in the `sql` directory and/or is it forbidden to read/access?';
            return false;
        }
        $sqlFile = include_once ROOT_FOLDER . '/data/sql/' . $sqlHandler . '.php';
        if (!is_callable($sqlFile)) {
            $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` was loaded but did not return a callable function. It should return a function that accepts `$c` and `$sqlFunction` as parameters which it then checks if it exists in current scope and then calls and returns its return value!';
            return false;
        }
        $c['dispatchers']['sql'][$sqlHandler] = $sqlFile;
        $sqlFunk = $c['dispatchers']['sql'][$sqlHandler]($c, $sqlFunction) ?? null;
        if ($sqlFunk === null) {
            $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` was loaded and is callable but did not return the SQL Handler Function `' . $sqlFunction . '`. Does it exist in the File as a callable function with the correct name?';
            return false;
        }
        return $sqlFunk;
    }
}

// Function that actually EXECUTES SQL Queries using provided `$sqlArrayKey`
// which should contain the following keys: 'qtype', 'sql', 'hydrate', 'bparam' & 'fields'.
// 'qtype' is the SQL Query Type (e.g., SELECT, INSERT, UPDATE, DELETE),
// 'sql' is the SQL Query String, 'hydrate' is the Hydration Array Key
// 'bparam' is the Bind Parameters Array Key and 'fields' is the Matching Validated
// Data Input Fields Array Key. It returns true if the SQL Query executed successfully, else false.
// $inputData is optional and can be used to pass additional data to the SQL Query whose keys should
// match those defined in the `fields` array. $hydrateDataAfter is a boolean that indicates whether
// to hydrate the data after executing the SQL Query. That means it calls `funk_use_hydrate` function
// after a successful SQL Query execution assuming it was `SELECT` Query Type. Otherwise it ignores it.
// TODO: Fix soon? IMPORTANT: NOT DONE - STILL WORK IN PROGRESS !!!!!!!!!!!!!!!!!!!!!!!
function funk_use_sql(&$c, $sqlArrayKey, $inputData = null, $hydrateDataAfter = false)
{
    // Validate `$sqlArrayKey` which should contain the following keys:
    // 'qtype', 'sql', 'hydrate','bparam' & 'fields'. Only 'qtype' and 'sql'
    // are required keys, the rest are optional in whether they have values but
    // they must exist as keys though.
    $longDefaultErr = 'The `\$sqlArrayKey` must be a Valid Array containing the following keys: `qtype`, `sql`, `hydrate`, `bparam` and `fields`. `qtype` is the SQL Query Type (e.g., SELECT, INSERT, UPDATE, DELETE), `sql` is the SQL Query String, `hydrate` is the Hydration Array Key, `bparam` is the Bind Parameters Array Key and `fields` is the Matching Validated Data Input Fields Array Key. Only `qtype` and `sql` must contain actual values that would be used whereas the rest are optional meaning they must exist as array keys but can be empty or null!';
    if (!is_array($sqlArrayKey)) {
        $c['err']['SQL']['funk_use_sql'][] = $longDefaultErr;
        return false;
    }
    if (
        !array_key_exists('qtype', $sqlArrayKey)
        || !array_key_exists('sql', $sqlArrayKey)
        || !array_key_exists('hydrate', $sqlArrayKey)
        || !array_key_exists('bparam', $sqlArrayKey)
        || !array_key_exists('fields', $sqlArrayKey)
    ) {
        $c['err']['SQL']['funk_use_sql'][] = $longDefaultErr;
        return false;
    }
    // Validate $c['db'] is a valid MySQLi Connection Object
    if (!isset($c['db']) || $c['db'] === null || !($c['db'] instanceof mysqli)) {
        $c['err']['SQL']['funk_use_sql'][] = 'Database Connection `$c[\'db\']` is NOT Set, IS NULL or NOT a Valid MySQLi Object Instance. Connect to the Database before calling this Function!';
        return false;
    }
    // Valid Query Types Hashed Key Array:
    $validQueryTypes = [
        'SELECT' => [],
        'INSERT' => [],
        'UPDATE' => [],
        'DELETE' => [],
    ];
    if (!isset($validQueryTypes[$sqlArrayKey['qtype']])) {
        $c['err']['SQL']['funk_use_sql'][] = 'Invalid SQL Query Type Provided. Valid Query Types are: `SELECT`,`UPDATE`,`INSERT` & `DELETE` in current version of FunkPHP!';
        return false;
    }
    // Return True when everything succeeded!
    return true;
}

// Function that hydrates data using the `hydrate` Key, is recommended
// to be used after a successful SQL Query execution using `funk_use_sql`
// but it is NOT a requirement! It is ALWAYS your Choice whether to use it!
function funk_use_hydrate_sql(&$c, $hydrateKey, $fetchedData) {}

// The main validation function for validating data in FunkPHP
// mapping to the "$_GET"/"$_POST" or "php://input" (JSON) variable ONLY!
function funk_use_validation(&$c, $validationHandler, $validationFunction, $source)
{
    // Check that both "$validationHandler, $validationFunction" are strings
    if (!is_string($validationHandler) || !is_string($validationFunction)) {
        $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid string for `\$validationHandler` and `\$validationFunction`. First is the Validation Handler File Name `v_FileName` without extension and second is the Validation Function Name `v_FunctionName`!';
        return false;
    }
    // In "$optimizedValidationArray" we will store the retrieved VaLidation Array
    // from a Validation Handler and one of its Validation Functions!
    $optimizedValidationArray = null;
    // If the Validation Handler exists in the $c['v_handlers'] we try call the Validation Function
    // and store the result in $optimizedValidationArray which is then used for validation!
    if (isset($c['dispatchers']['validation'][$validationHandler])) {
        $optimizedValidationArray = $c['dispatchers']['validation'][$validationHandler]($c, $validationFunction) ?? null;
    }
    // If not set, we check if the file
    else {
        $validationFile = ROOT_FOLDER . '/data/validations/' . $validationHandler . '.php';
        if (is_readable($validationFile)) {
            $validationDataFromFile = include_once $validationFile;
            if (is_callable($validationDataFromFile)) {
                $c['dispatchers']['validation'][$validationHandler] = $validationDataFromFile;
                $optimizedValidationArray = $c['dispatchers']['validation'][$validationHandler]($c, $validationFunction) ?? null;
            } else {
                $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Handler File ``' . $validationHandler . '.php` did not return a callable function.';
                return false;
            }
        } else {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Handler File `' . $validationHandler . '.php` not found or not readable! (Reminder: a single string is parsed as `v_file=>v_function`!)';
            return false;
        }
    }
    // Inform about the fact that this function is not
    // used for validating $_FILES variables and that
    // a different function should be used for that!
    if ($source === "FILES") {
        $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Use Validation Function `funk_use_validation_files(&\$c, \$optimizedValidationArray)` instead to validate `\$_FILES`!';
        return false;
    }
    // Check that $optimizedValidationArray is a valid array
    if (!is_array($optimizedValidationArray) || empty($optimizedValidationArray)) {
        $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a non-empty array for `\$optimizedValidationArray`!';
        return false;
    }
    // Check that $source is a valid string and is either "GET", "POST" or "JSON" (must be exact)
    $allowedSources = ['GET' => [], 'POST' => [], 'JSON' => []];
    if (!is_string($source) || !isset($allowedSources[$source])) {
        $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid string for `\$source` (\"GET\", \"POST\" or \"JSON\" - uppercase only)!';
        return false;
    }
    // Load input based on the source and make
    // sure it is a valid non-empty array!
    $inputData = null;
    if ($source === 'GET') {
        $inputData = $_GET ?? null;
        $c['v_config']['source'] = "GET";
    } elseif ($source === 'POST') {
        $inputData = $_POST ?? null;
        $c['v_config']['source'] = "POST";
    } elseif ($source === 'JSON') {
        $inputData = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid decoded JSON string for `\$source`!';
            return false;
        }
        $c['v_config']['source'] = "JSON";
    }
    if (!is_array($inputData) || empty($inputData)) {
        $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid non-empty array for `\$inputData`!';
        return false;
    }
    // TODO: REMOVE THIS LINE WHEN DONE TESTING
    // This is just for testing purposes to see the input data
    var_dump('TEST DATA(GET/POST/JSON):', $inputData);
    // Now we can run the validation recursively and
    $c['v_ok'] = true;
    $c['v'] = [];
    $c['v_data'] = [];
    \funk_validation_recursively_improved(
        $c,
        $inputData,
        $optimizedValidationArray,
        $c['v'],
        $c['v_data'],
    );
    // When this is set to true, it means that the validation
    // function has passed and no errors were found/added to $c['v']
    // Its default value is null meaning either no validation was run
    // or it failed and no errors were found/added to $c['v'] before this!
    // If validation passed, we can set the $c['v'] to null again
    if ($c['v_ok']) {
        $c['v'] = null;
        return true;
    }
    // Clear Valid Data Array if Validation failed but only if
    // "v_config" key "show_v_data_only_if_all_valid" is true
    if (
        isset($c['v_config']['show_v_data_only_if_all_valid'])
        && $c['v_config']['show_v_data_only_if_all_valid'] === true
    ) {
        $c['v_data'] = null;
    }
    return false;
}

// The main validation function for validating data
// in FunkPHP mapping to the $_FILES variables ONLY!
// TODO: Fix function or remove????
function funk_use_validation_files(&$c, $optimizedValidationArray)
{
    // Check that $optimizedValidationArray is a valid array
    if (!is_array($optimizedValidationArray) || empty($optimizedValidationArray)) {
        $c['err']['VALIDATIONS']['funk_use_validation_files'][] = "Files Validation Function must receive a non-empty array for `\$optimizedValidationArray`!";
        return false;
    }

    // Check that $_FILES is a valid array and is not empty
    if (!is_array($_FILES) || empty($_FILES)) {
        $c['err']['VALIDATIONS']['funk_use_validation_files'][] = "Files Validation Function must receive a non-empty array for `\$_FILES`!";
        return false;
    }
    // When this is set to true, it means that the validation
    // function has passed and no errors were found/added to $c['v']
    if ($c['v_ok']) {
        return true;
    }
    return false;
}

/*** PAGE-RELATED Functions For FunkPHP  ***/
