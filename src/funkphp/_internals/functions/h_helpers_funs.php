<?php // HELPER FUNCTIONS FOR FuncPHP

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
                funk_use_custom_error($c, ['json_or_page', ['json' => ["error" => $err], 'page' => '500'], $err], 500);
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

// The function "h_destroy_session" is used to destroy the session and optionally redirect to a specified URI
function funk_destroy_session(&$c, $set_other_cookies_with_h_setcookie_as_array = [], $redirect = null)
{
    // If session is active, destroy it
    if (session_id() || session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
        funk_headers_setcookie(session_name(), '', time() - 3600);
        funk_headers_setcookie("csrf", '', time() - 3600);

        // Optional h_setcookie() to set other cookies
        if (!empty($set_other_cookies_with_h_setcookie_as_array)) {
            foreach ($set_other_cookies_with_h_setcookie_as_array as $cookie) {
                funk_headers_setcookie(...$cookie);
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
function funk_headers_setcookie(&$c, $name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true, $samesite = 'strict')
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

// Boolean function that returns that a directory exists and is readable & writable
function dir_exists_is_readable_writable($dirPath)
{
    return is_dir($dirPath) && is_readable($dirPath) && is_writable($dirPath);
}

// Boolean function that returns that a file exists and is readable & writable
function file_exists_is_readable_writable($filePath)
{
    return is_file($filePath) && is_readable($filePath) && is_writable($filePath);
}

// Boolean function that checks if a variable is a non-empty array
function is_array_and_not_empty($array)
{
    return isset($array) && is_array($array) && !empty($array);
}
// Boolean function that checks if a variable is a non-empty string
function is_string_and_not_empty($string)
{
    return isset($string) && is_string($string) && !empty($string);
}
