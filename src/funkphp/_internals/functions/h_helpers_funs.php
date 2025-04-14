<?php // HELPER FUNCTIONS FOR FuncPHP

// The functions "ok" and "err" are used to check the result of a function call
// whereas "fail" and "success" are used to return the result of a function call
function ok($result)
{
    return !isset($result['err']) ? true : false;
}
function err($result)
{
    return isset($result['err']) ? true : false;
}
function fail($errMsg)
{
    return ['err' => $errMsg];
}
function success($data)
{
    return ['data' => $data];
}

// The functions "return_html", "return_json" and the like sets a specific
// content type and then returns response code and usually some data
function return_html($html, $statusCode = 200)
{
    // Set/change the content type to HTML and the status code, then return the HTML response
    header('Content-Type: text/html', true, $statusCode);
    echo $html;
    exit;
}
function return_json($json, $statusCode = 200)
{
    // Set/change the content type to JSON and the status code, then return the JSON response
    header('Content-Type: application/json', true, $statusCode);
    echo json_encode($json);
    exit;
}
function return_code($statusCode = 418)
{
    // Default to 418 if invalid status code
    if (!is_numeric($statusCode) || $statusCode < 100 || $statusCode > 599) {
        $statusCode = 418;
    }
    // Set the/change content type to text and the status code, then return the code response
    header('Content-Type: text/plain', true, $statusCode);
    exit;
}
function return_download($filePath, $fileName = null, $statusCode = 200)
{
    // Set the content type to application/octet-stream and the status code, then return the file response
    header('Content-Type: application/octet-stream', true, $statusCode);
    header('Content-Disposition: attachment; filename="' . ($fileName ?? basename($filePath)) . '"');
    readfile($filePath);
    exit;
}

// Function that loops through an array and checks if any element matches based on user-defined function
function array_any_element($array, $callback, $stringToCheck, $options = [])
{
    if (isset($options) && is_array($options) && in_array("lowercase", $options)) {
        $stringToCheck = mb_strtolower($stringToCheck);
    }
    foreach ($array as $element) {
        if (isset($options) && is_array($options) && in_array("lowercase", $options)) {
            $element = mb_strtolower($element);
        }
        if (isset($options) && is_array($options) && in_array("swap_args", $options)) {
            if ($callback($stringToCheck, $element)) {
                if (in_array("return_element", $options)) {
                    return $element;
                }
                return true;
            }
        } else {
            if ($callback($element, $stringToCheck)) {
                if (isset($options) && is_array($options) && in_array("return_element", $options)) {
                    return $element;
                }
                return true;
            }
        }
    }
    return false;
}

// Function to check if both strings are equal
function str_equals($a, $b)
{
    return strcmp($a, $b) === 0;
}

// The function "h_destroy_session" is used to destroy the session and optionally redirect to a specified URI
function h_destroy_session($set_other_cookies_with_h_setcookie_as_array = [], $redirect = null)
{
    // If session is active, destroy it
    if (session_id() || session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
        h_headers_setcookie(session_name(), '', time() - 3600);
        h_headers_setcookie("csrf", '', time() - 3600);

        // Optional h_setcookie() to set other cookies
        if (!empty($set_other_cookies_with_h_setcookie_as_array)) {
            foreach ($set_other_cookies_with_h_setcookie_as_array as $cookie) {
                h_headers_setcookie(...$cookie);
            }
        }
    }
    // Redirect to the specified URI if provided
    if ($redirect) {
        header("Location: $redirect");
        exit;
    }
}

// The function "h_headers_set" is used to set the headers for the HTTPS response
// while the "h_headers_remove" is used to remove the headers for the HTTPS response
function h_headers_remove($headersToRemove)
{
    // Remove header(s) for the HTTPS reponse
    foreach ($headersToRemove as $header) {
        header_remove($header);
    }
}
function h_headers_set($headersToSet)
{
    // Set the header(s) for the HTTPS response
    foreach ($headersToSet as $header) {
        header($header);
    }
}

// Function to set a cookie with the specified parameters
function h_headers_setcookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true, $samesite = 'strict')
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

// Function that checks for a specific key in $c['DEFAULT_ACTION'] for a specific step (STEP_1 -> STEP_5),
// contextKey, conditionKey, actionKeyName and actionKeyValue. actionKeyName and actionKeyValue are used
// if a specific condition is met. Or an optional callback can be used instead of actionKeyName and actionKeyValue!
function h_try_default_action(&$c, $step1_5_Key, $contextKey, $conditionKey, $actionKeyName, $actionKeyValue, $optionalCallback = null)
{
    // First check if it is example that is trying to be ran:
    if ($step1_5_Key === "STEP_0" && $contextKey === "req" && $conditionKey === "_EXAMPLE_") {
        err("[h_try_default_action]: Hardcoded example without any valid function and/or value is trying to be ran. Please check the code!");
    }
    // First check each key level just in case
    if (!isset($c['DEFAULT_ACTION'][$step1_5_Key])) {
        return false;
    }
    if (!isset($c['DEFAULT_ACTION'][$step1_5_Key][$contextKey])) {
        return false;
    }
    if (!isset($c['DEFAULT_ACTION'][$step1_5_Key][$contextKey][$conditionKey])) {
        return false;
    }
    if (!isset($c['DEFAULT_ACTION'][$step1_5_Key][$contextKey][$conditionKey][$actionKeyName])) {
        return false;
    }

    // Here we know that the step, context and condition keys are set and that there is
    // a specific action to be taken. We now check whether to use the actionKeyName and
    // actionKeyValue or the optionalCallback which is default null meaning not to use it.
    if ($actionKeyName && $actionKeyValue) {
        if (is_callable($actionKeyName)) {
            return call_user_func($actionKeyName, $actionKeyValue);
        } else if (is_callable($optionalCallback)) {
            return call_user_func($optionalCallback, $actionKeyValue);
        } else {
            return false;
        }
    }
    return false;
}

// Function to run an array of simple ini_sets(key,value)
function h_run_ini_sets(array $iniSets)
{
    foreach ($iniSets as $key => $value) {
        ini_set($key, $value);
    }
}

// Function to start session if not already started
function h_start_session()
{
    if (!session_id() || session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Function to check if a string contains any of the specified substrings
function h_splitOnAndCheckInArray($splitOn, $stringToCheck, $InArray, $lowerCaseEachPart = false)
{
    try {
        // Split the string on the specified delimiter and check if any of the parts are in the array
        $parts = explode($splitOn, $stringToCheck);
        foreach ($parts as $part) {
            if ($lowerCaseEachPart) {
                $part = mb_strtolower($part);
            }
            if (in_array($part, $InArray)) {
                return true;
            }
        }
    } catch (Exception $e) {
        return false;
    }
}

// This function uses the "The Random\Randomizer class" to generate a unique password
function h_generate_password($length = 20, $returnHashed = false)
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
function h_generate_number($length = 10)
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
function h_generate_user_id($length = 96)
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
function h_generate_csrf($length = 384)
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
