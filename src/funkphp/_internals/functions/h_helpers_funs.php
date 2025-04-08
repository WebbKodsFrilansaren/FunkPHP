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
function return_code($statusCode = 418)
{
    // Default to 418 if invalid status code
    if (!is_numeric($statusCode) || $statusCode < 100 || $statusCode > 599) {
        $statusCode = 418;
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

// Return "o_ok" options if available
function h_has_ok_options($array)
{
    foreach ($array as $key => $value) {
        if ($key === 'o_ok' && !empty($value)) {
            return $value;
        }
        if (is_array($value)) {
            $result = h_has_ok_options($value); // Recursive call
            if ($result !== fail("[h_has_options]: No options provided in the array.")) {
                return $result; // Return the options if found in a nested array
            }
        }
    }
    return fail("[h_has_options]: No options provided in the array.");
}

// Return "o_fail" options if available
function h_has_fail_options($array)
{
    foreach ($array as $key => $value) {
        if ($key === 'o_fail' && !empty($value)) {
            return $value;
        }
        if (is_array($value)) {
            $result = h_has_fail_options($value); // Recursive call
            if ($result !== fail("[h_has_options]: No options provided in the array.")) {
                return $result; // Return the options if found in a nested array
            }
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

// Function that loads configuration from a string array where each string
// is a variable from $GLOBALS array!
function h_load_config($configArrayString)
{
    // Check if the config array is not empty and is an array
    if (!empty($configArrayString) && is_array($configArrayString)) {
        $mergedConfig = []; // Initialize an empty array to store the merged config

        // Loop through the provided array of global variable names
        foreach ($configArrayString as $varName) {
            // Check if a global variable with this name exists
            if (isset($GLOBALS[$varName])) {
                // Retrieve the value of the global variable and store it in $mergedConfig
                $mergedConfig[$varName] = $GLOBALS[$varName];
            }
        }
        return $mergedConfig;
    } else {
        return fail("[h_load_config]: No (or invalid) config array provided.");
    }
}

// This function works as a pipeline and processes the request through a series of functions
// The "outer" part means this is the one that takes the main step functions whereas
// the "inner" part means this is the one that takes the inner step functions such as
// the "o" (option(s)) key in the array each specific step function might come across!
function outerFunktionTrain(&$req, &$d, &$p, $globalConfig, $listOuterFunctionsNamesAsKeysWithTheirArgsAsAssociatedValues)
{
    // Loop through "$listOuterFunctionsNames" and turn the function names into the key of corresponding
    $fns = [];

    // Populate the $fns array with function names, arguments, and initial return value
    foreach ($listOuterFunctionsNamesAsKeysWithTheirArgsAsAssociatedValues as $functionName => $args) {
        $fns[$functionName] = [
            "fn_name" => $functionName ?? null,
            "args" => $args ?? [],
            "return_value" => "UNDEFINED",
            "o_ok" => h_has_ok_options($args[0]) ?? [],
            "o_fail" => h_has_fail_options($args[0]) ?? [],
        ];
    }

    // Now, you would typically loop through the $fns array (or based on a priority list)
    // to execute the functions and update their return values.
    // Pass reference to modify the original array!!!
    foreach ($fns as $functionName => &$functionData) {
        if ($functionData["fn_name"] == null) {
            echo "<br>Function name is null for function $functionName!<br>"; // REMOVE LATER!!!
            return fail("[outerFunktionTrain]: Function name is null for function $functionName!");
        } else if (!function_exists($functionName)) {
            echo "<br>Function $functionName does not exist!<br>"; // REMOVE LATER!!!
            return fail("[outerFunktionTrain]: Function $functionName does not exist!");
        }
        $argsToPass = $functionData["args"];
        $returnValue = call_user_func_array($functionName, $argsToPass);
        $functionData["return_value"] = $returnValue;

        // Check if user closed the connection (e.g., browser closed) and exit script so no further processing is done
        if (connection_aborted()) {
            break;
            exit;
        }

        // Check current return_value that it is not "UNDEFINED" and also NOT "err" key but true or 1:
        if ($functionData["return_value"] !== "UNDEFINED" && ($functionData["return_value"] === true || $functionData["return_value"] === 1)) {
            // Call the optional "o_ok" functions if they exist
            if (!empty($functionData["o_ok"])) {
                h_run_ok_functions($functionData['o_ok'], $functionName, $req, $d, $p, $globalConfig);
            }
        }

        // Check current return_value that it is not "UNDEFINED" and also NOT "err" key but false or 0:
        else if ($functionData["return_value"] !== "UNDEFINED" && ($functionData["return_value"] === false || $functionData["return_value"] === 0)) {
            // Call the optional "o_fail" functions if they exist
            if (!empty($functionData["o_fail"])) {
                h_run_fail_functions($functionData['o_fail'], $functionName,  $req, $d, $p, $globalConfig);
            }
        }

        // If value IS "UNDEFINED" here
        else if ($functionData["return_value"] === "UNDEFINED") {
        }

        // Return value is "err" key here
        else {
            fail("[outerFunktionTrain]: Return value is an error key when running function $functionName!");
        }

        // REMOVE LATER!!!
        echo "<br>Return value of $functionName: " . strval($functionData["return_value"]) . "<br>";
    }
}


// Two functions to run the fail and ok functions that are inside
// of the "o_ok" and "o_fail" options of the outer functions!
function h_run_fail_functions($fnNameWithArg, $callerName, &$req, &$d, &$p, $globalConfig)
{
    $runPriority = $globalConfig['fphp_o_fail_priorities'][$callerName] ?? null;
    $failFns = $fnNameWithArg ?? null;
    if ($runPriority == null || $failFns == null) {
        return fail("[h_run_fail_functions]: Optional Fail Function or its priorities not found for function $callerName.");
    }

    // failFns is an array where each element is "fnName=value" format so we need to iterate through it
    // and split each element by "=" to get the function name and its argument(s)
    $parsedFailFunctions = [];
    $finalFns = [];
    foreach ($failFns as $failFn) {
        $parts = explode("=", $failFn, 2); // Split by "=" and limit to 2 parts
        if (count($parts) == 2) {
            $parsedFailFunctions[$parts[0]] = $parts[1] ?? null;
            $finalFns[$runPriority[$parts[0]]][$parts[0]] = $parsedFailFunctions[$parts[0]] ?? null;
        }
    }
    ksort($finalFns);
    echo "<br>Parsed Fail Functions: <br>";
    //var_dump($parsedFailFunctions);
    var_dump($finalFns); // REMOVE LATER!!!

    // Now we have an associative array where the key is the function name and the value is the argument(s)
    // We need to order the functions based on their priorities. The final key sort (ksort) will be done later
    // in the code so that we can run them in the order of their priorities.
    // $orderedArgs = [];
    // foreach ($runPriority as $fnName => $priority) {
    //     if (isset($parsedFailFunctions[$fnName])) {
    //         $orderedArgs[$priority] = [$fnName => $parsedFailFunctions[$fnName]];
    //     }
    // }
}
function h_run_ok_functions($fnNameWithArg, $callerName, &$req, &$d, &$p, $globalConfig)
{
    $runPriority = $globalConfig['fphp_o_ok_priorities'][$callerName] ?? null;
    $okFns = $fnNameWithArg ?? null;
    if ($runPriority == null || $okFns == null) {
        return fail("[h_run_fail_functions]: Optional Ok Function or its priorities not found for function $callerName.");
    }
    // okFns is an array where each element is "fnName=value" format so we need to iterate through it
    // and split each element by "=" to get the function name and its argument(s)
    $parsedokFunctions = [];
    $finalFns = [];
    foreach ($okFns as $okFn) {
        $parts = explode("=", $okFn, 2); // Split by "=" and limit to 2 parts
        if (count($parts) == 2) {
            $parsedokFunctions[$parts[0]] = $parts[1];
            $finalFns[$runPriority[$parts[0]]][$parts[0]] = $parsedokFunctions[$parts[0]] ?? null;
        }
    }
    ksort($finalFns);
    echo "<br>Parsed Ok Functions: <br>";
    //var_dump($parsedFailFunctions);
    var_dump($finalFns); // REMOVE LATER!!!

    // Now we have an associative array where the key is the function name and the value is the argument(s)
    // We need to order the functions based on their priorities. The final key sort (ksort) will be done later
    // in the code so that we can run them in the order of their priorities.
    // $orderedArgs = [];
    // foreach ($runPriority as $fnName => $priority) {
    //     if (isset($parsedokFunctions[$fnName])) {
    //         $orderedArgs[$priority] = [$fnName => $parsedokFunctions[$fnName]];
    //     }
    // }

}


function innerFunktionTrain(&$req, &$d, &$p, $globalConfig, $listOuterFunctionsNamesWithCorrespondingOptionsNames, $listOuterFunctionsNamesWithCorrespondingOptionsArgs) {}

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
