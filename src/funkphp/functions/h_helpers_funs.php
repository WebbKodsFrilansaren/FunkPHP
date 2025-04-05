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
    // Set the content type to HTML and the status code, then return the HTML response
    header('Content-Type: text/html', true, $statusCode);
    echo $html;
    exit;
}
function return_json($json, $statusCode = 200)
{
    // Set the content type to JSON and the status code, then return the JSON response
    header('Content-Type: application/json', true, $statusCode);
    echo json_encode($json);
    exit;
}
function return_code($statusCode = 423)
{
    // Default to 423 if invalid status code
    if (!is_numeric($statusCode) || $statusCode < 100 || $statusCode > 599) {
        $statusCode = 423;
    }
    // Set the content type to text and the status code, then return the code response
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

// Function to check if any element in the array either starts with,
// ends with, contains or is exact match with the specified string
function array_any_element($what, $array, $stringToCheck, $lowerCaseEachPart = false)
{
    foreach ($array as $element) {
        if ($lowerCaseEachPart) {
            $element = mb_strtolower($element);
            $stringToCheck = mb_strtolower($stringToCheck);
        }
        if ($what == "starts_with" && str_starts_with($stringToCheck, $element)) {
            return true;
        } elseif ($what == "ends_with" && str_ends_with($stringToCheck, $element)) {
            return true;
        } elseif ($what == "contains" && str_contains($stringToCheck, $element)) {
            return true;
        } elseif ($what == "exact" && $stringToCheck == $element) {
            return true;
        }
    }
    return false;
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

// Extract all options from the 'o' key into an associative array
function h_extract_all_options($array)
{
    // Lazy check for 'o' key in the array if it wasn't directly passed
    if (array_key_exists("o", $array)) {
        $array = $array['o'];
    }
    $options = [];
    if (isset($array) && is_array($array)) {
        foreach ($array as $optionString) {
            if (strpos($optionString, '=') !== false) {
                [$key, $value] = explode('=', $optionString, 2); // Split into max 2 parts
                $options[$key] = $value;
            }
        }
    }
    return $options;
}

// Extract the matched option from the array based on the provided option key
function h_extract_matched_option($array, $option)
{
    // Lazy check for 'o' key in the array if it wasn't directly passed
    if (array_key_exists("o", $array)) {
        $array = $array['o'];
    }
    foreach ($array as $key) {
        if (str_starts_with($key, $option)) {
            // return whatever is after the '=' sign
            return explode('=', $key)[1] ?? fail("[h_extract_matched_option]: No options provided in the array.");
        }
    }
    return fail("[h_extract_matched_option]: No options provided in the array.");
}
// Return the value of 'o' key from the provided $array if it exists
// otherwise return err (fail())
function h_has_options($array)
{
    foreach ($array as $key => $value) {
        if ($key === 'o' && !empty($value)) {
            return $value;
        }
    }
    return fail("[h_has_options]: No options provided in the array.");
}
// The function "h_headers_set" is used to set the headers for the HTTPS response
// while the "h_headers_remove" is used to remove the headers for the HTTPS response
function h_headers_remove(...$headersToRemove)
{
    // Remove header(s) for the HTTPS reponse
    foreach ($headersToRemove as $header) {
        header_remove($header);
    }
}
function h_headers_set(...$headersToSet)
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
