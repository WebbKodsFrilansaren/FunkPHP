<?php // ROUTE-related FUNCTIONS FOR FunPHP

// TEST FUNCTION - REMOVE LATER OR JUST IGNORE IN PROD!
// Mainly used to test 'callback' keys at various places!
function TEST_FUNCTION_REMOVE_LATER(&$c)
{
    $accept = $c['req']['accept'] ?? null;
    if ($accept === null) {
        echo "No Accept Header Found!";
    } else {
        echo "Accept Header Found: " . htmlspecialchars($accept);
    }
}

// TEST FUNCTION - either returns a string or a integer
function TEST_2()
{
    // use random to return either string or integer
    return rand(0, 1) === 0 ? "A String" : 12345;
}

// FunkPHP Complex Custom Error Function - use the more single purpose ones if possible!
function funk_use_error(&$c, int $errCode, string $errMsg, string $handleType, $optionalJSONData = null, $optionalCallbackData = null, $optionalPageName = null) {} {
    // $handleTypeAndDataOptionalCBData[0]  = handleType (string)
    // $handleTypeAndDataOptionalCBData[1]  = handleData (mixed, depends on handleType)
    // $handleTypeAndDataOptionalCBData[1]['json']  = JSON handleType for handleType = 'json_or_page'
    // $handleTypeAndDataOptionalCBData[1]['page']  = Page handleType for handleType = 'json_or_page'
    // $handleTypeAndDataOptionalCBData[2]  = (optional) callbackData (mixed, depends on handleType)
    // Available error types it can handle as of now! - more can be added as needed!
    $availableHandleTypes = ['json', 'page', 'json_or_page', 'callback', 'html', 'text', 'xml', 'throw'];

    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }

    // $$handleTypeAndDataOptionalCBData must be an array of at least two items!
    if (
        !isset($handleTypeAndDataOptionalCBData)
        || !is_array($handleTypeAndDataOptionalCBData)
        || count($handleTypeAndDataOptionalCBData) < 2
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Type and Data Provided to funk_handle_custom_error() Function. This should be an array with at least two items: `[HandleType, HandleData, (Optional) CallbackData]`!');
    }
    // When handleType is not a string and not in the available types
    if (
        !isset($handleTypeAndDataOptionalCBData[0])
        || !is_string($handleTypeAndDataOptionalCBData[0])
        || empty($handleTypeAndDataOptionalCBData[0])
        || !in_array($handleTypeAndDataOptionalCBData[0], $availableHandleTypes)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Type Provided to funk_handle_custom_error() Function. This should be a string and one of the following: `' . implode('`, `', $availableHandleTypes) . '`!');
    }
    // $$handleTypeAndDataOptionalCBData[1] must be set and not null|empty
    if (!isset($handleTypeAndDataOptionalCBData[1]) || empty($handleTypeAndDataOptionalCBData[1])) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function. This should be an array with at least two items: `[HandleType, HandleData, (Optional) CallbackData]`!');
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errorCode)
        || !is_int($errorCode)
        || $errorCode < 100
        || $errorCode > 599
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to funk_handle_custom_error() Function. This should be an integer between 100 and 599!');
    }

    // Clear any DB connections before handling the error
    // unless the handle type is 'throw' since that could
    // be caught and maybe DB is used after that!
    if (
        isset($handleTypeAndDataOptionalCBData[0])
        && is_string($handleTypeAndDataOptionalCBData[0])
        && $handleTypeAndDataOptionalCBData[0] !== 'throw'
    ) {
        FunkDBConfig::clearCredentials();
    }

    // HERE WE HAVE VALIDATED: Valid existing Handle Type,
    // Valid existing Handle Data, Valid existing Error Code
    // Handle JSON Handle Type
    if ($handleTypeAndDataOptionalCBData[0] === 'json') {
        http_response_code($errorCode);
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || (!is_array($handleTypeAndDataOptionalCBData[1])
                && !is_object($handleTypeAndDataOptionalCBData[1]))
            || empty($handleTypeAndDataOptionalCBData[1])
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `json` Type. This should be a non-empty array!');
        }
        try {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($handleTypeAndDataOptionalCBData[1], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred While Encoding the Provided Data to JSON (`' . $e->getMessage() . '`) inside funk_handle_custom_error() Function for `json` Type!');
        }
    }  // Handle Page Type
    else if ($handleTypeAndDataOptionalCBData[0] === 'page') {
        http_response_code($errorCode);
        // TODO: MAYBE Change later when 'part' part of FunkPHP has been 100 % fully realized. For example, maybe compile on call, etc?
        // handleData must be a non empty string (the path to the page to load)
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || !is_string($handleTypeAndDataOptionalCBData[1])
            || empty($handleTypeAndDataOptionalCBData[1])
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `page` Type. This should be a non-empty string!');
        }
        $pageToInclude = ROOT_FOLDER . '/page/complete/[errors]/' . $handleTypeAndDataOptionalCBData[1] . '.php';
        if (!is_readable($pageToInclude)) {
            critical_err_json_or_html(500, 'Tell the Developer: The Provided Page to Load inside funk_handle_custom_error() Function for `page` Type does NOT EXIST or is NOT READABLE! Please check the path: `' . $pageToInclude . '`');
        } else {
            // Use the same "$custom_error_message" inside the included file to show custom error message!
            $custom_error_message = $handleTypeAndDataOptionalCBData[2] ?? "";
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            include_once $pageToInclude;
        }
    }  // Handle JSON Or Page Type (based on 'accept' header)
    else if ($handleTypeAndDataOptionalCBData[0] === 'json_or_page') {
        http_response_code($errorCode);
        // Check if HandleData [1] is at least an array with two elements
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || !is_array($handleTypeAndDataOptionalCBData[1])
            || count($handleTypeAndDataOptionalCBData[1]) < 2
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `json_or_page` Type. This should be an array with at least two items: `["json" => [JSON_Data], "page" => "Page_File_Name"]`!');
        }
        // We want JSON
        if (
            isset($c['req']['accept'])
            && is_string($c['req']['accept'])
            && !empty($c['req']['accept'])
            && in_array($c['req']['accept'], ['application/json', 'text/json'])
        ) {
            if (
                !isset($handleTypeAndDataOptionalCBData[1]['json'])
                || (!is_array($handleTypeAndDataOptionalCBData[1]['json'])
                    && !is_object($handleTypeAndDataOptionalCBData[1]['json']))
                || empty($handleTypeAndDataOptionalCBData[1]['json'])
            ) {
                critical_err_json_or_html(500, 'Tell the Developer: Invalid Handle Data Provided to funk_handle_custom_error() Function for `json` Type inside `json_or_page`. This should be a non-empty array. For Handle Type `json_or_page` the `HandleData` is an array: `["json" => [JSON_Data], "page" => "Page_File_Name"]`!');
            }
            try {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($handleTypeAndDataOptionalCBData[1]['json'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\JsonException $e) {
                critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred While Encoding the Provided Data to JSON (`' . $e->getMessage() . '`) inside funk_handle_custom_error() Function for `json` Type. Expected to find JSON data at: `$handleTypeAndDataOptionalCBData[1][\'json\']`!');
            }
        } // We want Page
        else {
            // TODO: MAYBE Change later when 'part' part of FunkPHP has been 100 % fully realized. For example, maybe compile on call, etc?
            if (
                !isset($handleTypeAndDataOptionalCBData[1]['page'])
                || !is_string($handleTypeAndDataOptionalCBData[1]['page'])
                || empty($handleTypeAndDataOptionalCBData[1]['page'])
            ) {
                critical_err_json_or_html(500, 'Tell the Developer: Invalid Handle Data Provided to funk_handle_custom_error() Function for `page` Type inside `json_or_page`. This should be a non-empty array. For Handle Type `json_or_page` the `HandleData` is an array: `["json" => [JSON_Data], "page" => "Page_File_Name"]`!');
            }
            $pageToInclude = ROOT_FOLDER . '/page/complete/[errors]/' . $handleTypeAndDataOptionalCBData[1]['page'] . '.php';
            if (!is_readable($pageToInclude)) {
                critical_err_json_or_html(500, 'Tell the Developer: The Provided Page to Load inside funk_handle_custom_error() Function for `page` Type does NOT EXIST or is NOT READABLE! Please check the path: `' . $pageToInclude . '`');
            } else {
                // Use the same "$custom_error_message" inside the included file to show custom error message!
                $custom_error_message = $handleTypeAndDataOptionalCBData[2] ?? "";
                header('Content-Type: text/html; charset=utf-8');
                header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
                include_once $pageToInclude;
            }
        }
    }  // Handle Throw Type
    else if ($handleTypeAndDataOptionalCBData[0] === 'throw') {
        // Validation: handleData must be a non-empty string (the exception message)
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || !is_string($handleTypeAndDataOptionalCBData[1])
            || empty($handleTypeAndDataOptionalCBData[1])
        ) {
            // Throwing is the error-handling mechanism itself, so if the data
            // is invalid, we must fall back to the critical error handler.
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `throw` Type. This should be a non-empty string for the exception message!');
        }
        // Just throw and trust the Developer to catch it somewhere else!
        throw new Exception($handleTypeAndDataOptionalCBData[1]);
    }
    // Handle Callback Type
    else if ($handleTypeAndDataOptionalCBData[0] === 'callback') {
        // callBack data at [2] should not be null if this is the case
        if (!isset($handleTypeAndDataOptionalCBData[2]) || empty($handleTypeAndDataOptionalCBData[2])) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Data Provided to funk_handle_custom_error() Function for `callback` Type. This should be an array with at least a Callable Function Name and (Optional) Callback Data to pass to the function!');
        }
        if (!isset($handleTypeAndDataOptionalCBData[1]) || !is_callable($handleTypeAndDataOptionalCBData[1])) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Function Provided to funk_handle_custom_error() Function for `callback` Type. This should be a Callable Function!');
        }
        try {
            $handleTypeAndDataOptionalCBData[1]($c, $handleTypeAndDataOptionalCBData[2]);
        } catch (\Throwable $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the Custom Error Callback (`' . $e->getMessage() . '`) the Developer had Configured!');
        }
    }  // Handle HTML Type
    else if ($handleTypeAndDataOptionalCBData[0] === 'html') {
        http_response_code($errorCode);
        // Validate that handleData is a string and NOT empty
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || !is_string($handleTypeAndDataOptionalCBData[1])
            || empty($handleTypeAndDataOptionalCBData[1])
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `html` Type. This should be a non-empty string!');
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $handleTypeAndDataOptionalCBData[1];
    }  // Handle Text Type
    else if ($handleTypeAndDataOptionalCBData[0] === 'text') {
        http_response_code($errorCode);
        // Validate that handleData is a string and NOT empty
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || !is_string($handleTypeAndDataOptionalCBData[1])
            || empty($handleTypeAndDataOptionalCBData[1])
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `text` Type. This should be a non-empty string!');
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo $handleTypeAndDataOptionalCBData[1];
    }  // Handle XML Type
    else if ($handleTypeAndDataOptionalCBData[0] === 'xml') {
        http_response_code($errorCode);
        // Validate that handleData is a string and NOT empty
        if (
            !isset($handleTypeAndDataOptionalCBData[1])
            || !is_string($handleTypeAndDataOptionalCBData[1])
            || empty($handleTypeAndDataOptionalCBData[1])
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `xml` Type. This should be a non-empty string!');
        }
        header('Content-Type: application/xml; charset=utf-8');
        echo $handleTypeAndDataOptionalCBData[1];
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Outputs a raw HTML string directly to the client.
 *
 * This is used for simple, non-templated HTML error responses.
 *
 * SECURITY NOTE: Clears database credentials (FunkDBConfig::clearCredentials())
 * to prevent accidental database usage in subsequent request pipeline functions.
 * Existing database connections stored in $c['DATABASES'] remain active.
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw HTML string to be echoed as the response body.
 * @return void              Sends the HTML response and terminates execution via `exit()`.
 */
function funk_use_error_raw_html(&$c, int $errCode, string $errMsg)
{
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the error page
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_html_string()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_html_string()` Function. This should be a non-empty string!');
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
 * SECURITY NOTE: Clears database credentials (FunkDBConfig::clearCredentials())
 * to prevent accidental database usage in subsequent request pipeline functions.
 * Existing database connections stored in $c['DATABASES'] remain active.
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw plain text string to be echoed as the response body.
 * @return void              Sends the plain text response and terminates execution via `exit()`.
 */
function funk_use_error_raw_plain(&$c, int $errCode, string $errMsg)
{
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the error page
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_plain_text()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_plain_text()` Function. This should be a non-empty string!');
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
 * SECURITY NOTE: Clears database credentials (FunkDBConfig::clearCredentials())
 * to prevent accidental database usage in subsequent request pipeline functions.
 * Existing database connections stored in $c['DATABASES'] remain active.
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw XML string to be echoed as the response body.
 * @return void              Sends the XML response and terminates execution via `exit()`.
 */
function funk_use_error_xml(&$c, int $errCode, string $errMsg)
{
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the error
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_xml()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_xml()` Function. This should be a non-empty string!');
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
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the error page
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_page()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_page()` Function. This should be a non-empty string!');
    }
    // When $pageName is not a string or empty or file not readable
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_FOLDER . '/page/complete/[errors]/' . $pageName . '.php')
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_handle_error_page()` Function. This should be a non-empty string that is also a readable file inside `/page/complete/[errors]/` folder!');
    }
    // Headers that also support <styles> tag inline
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
    // Use the same "$custom_error_message" inside the included file to show custom error message!
    $custom_error_message = $errMsg;
    include_once ROOT_FOLDER . '/page/complete/[errors]/' . $pageName . '.php';
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
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the callback
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_callback()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_callback()` Function. This should be a non-empty string!');
    }
    // When $callbackName is not a string or empty or not callable
    if (
        !isset($callbackName)
        || !is_string($callbackName)
        || empty($callbackName)
        || !is_callable($callbackName)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_handle_error_callback()` Function. This should be a non-empty string that is also callable!');
    }
    // Set response code, call function and exit
    http_response_code($errCode);
    try {
        $callbackName($c, $errMsg, $optionalCallbackData);
    } catch (\Throwable $e) {
        critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_callback()` Function with the following Error Message:`' . $e->getMessage() . '`.');
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_throw()` Function. This should be a non-empty string!');
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
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the JSON generation
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_json()` Function. This should be an integer between 100 and 599!');
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_handle_error_json()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // Set the response code for both JSON
    http_response_code($errCode);
    // Retrieve JSON Payload either directly or by verified callable
    $jsonData = $jsonObjectOrStringThatReturnsJSON;
    if (is_string($jsonData) && is_callable($jsonData)) {
        try {
            $jsonData = $jsonData($c);
        } catch (\Throwable $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
        }
    }
    // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (\JsonException $e) {
        critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_json()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
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
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the JSON generation or the error page
    FunkDBConfig::clearCredentials();
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page()` Function. This should be an Integer between 100 and 599!');
    }
    // When $pageErrMsg is not a string or empty
    if (
        !isset($pageErrMsg)
        || !is_string($pageErrMsg)
        || empty($pageErrMsg)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String!');
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // When $pageName is not a string or empty or the file does not exist in the expected folder
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_FOLDER . '/page/complete/[errors]/' . $pageName . '.php')
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String!');
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
                critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
        }
    }
    // Otherwise we return a Page even if that was not explicitly requested
    else {
        // Headers that also support <styles> tag inline
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        // Use the same "$custom_error_message" inside the included file to show custom error message!
        $custom_error_message = $pageErrMsg;
        include_once ROOT_FOLDER . '/page/complete/[errors]/' . $pageName . '.php';
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
    // Clears any DB connections before handling the error so they cannot accidentally be used inside the JSON generation or the error page
    FunkDBConfig::clearCredentials();

    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post-response pipeline since all data there is only for server
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_custom_error_json_or_page_or_callback()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsgForPageAndCallback is not a string or empty
    if (
        !isset($errMsgForPageAndCallback)
        || !is_string($errMsgForPageAndCallback)
        || empty($errMsgForPageAndCallback)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_custom_error_json_or_page_or_callback()` Function. This should be a Non-Empty String!');
    }
    // When $pageName is not a string or empty or the file does not exist in the expected folder
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_FOLDER . '/page/complete/[errors]/' . $pageName . '.php')
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_custom_error_json_or_page_or_callback()` Function. This should be a Non-Empty String!');
    }
    // $callableName is not a string or empty or not callable
    if (
        !isset($callableName)
        || !is_string($callableName)
        || empty($callableName)
        || !is_callable($callableName)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_use_custom_error_json_or_page_or_callback()` Function. This should be a Non-Empty String that is also Callable!');
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
        critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_custom_error_json_or_page_or_callback()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
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
        // Use the same "$custom_error_message" inside the included file to show custom error message!
        $custom_error_message = $errMsgForPageAndCallback;
        include_once ROOT_FOLDER . '/page/complete/[errors]/' . $pageName . '.php';
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
                critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
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

/**
 **
 **
 **/
function funk_use_safe_mutate(&$c, $mainKeyAndOptionalSubKeys, $callable, $callableArgs = [], $expectedTypes, $expectedValueRanges = null)
{
    // The main reference to $c that we will traverse and possibly mutate
    $cRef = &$c;
    $currentPath = '$c';
    $validGetTypes = [
        'string',
        'integer',
        'double',
        'boolean',
        'NULL',
        'null',
        'array',
        'object',
        'resource',
        'resource (closed)',
        'unknown type'
    ];
    $validValueRangeKeys = [
        // Length Checks (Mostly for string/array count)
        'exact_length',
        'min_length',
        'max_length',
        'array_count_exact',
        'array_count_min',
        'array_count_max',
        // Literal Value Checks
        'exact_value',
        'allowed_values',
        'disallowed_values',
        // Numeric Range Checks (For integer/double)
        'min_value',
        'max_value',
        // String Pattern Checks
        'matches_regex',
        'is_json_string',
        'numeric_string',
        // Array Structure Checks
        'array_keys_only',
        // Object/Resource Checks
        'object_instanceof',
        'is_resource_type',
        // Truthiness Checks
        'is_falsey',
        'is_truthy'
    ];

    // Validate that $callable is a valid callable function
    if (!is_string($callable) || !is_callable($callable)) {
        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid Second Parameter Passed to funk_use_safe_mutate() Function. This should be the string to an existing and Callable Function within the scope the function is used. It should also return a value to be validated!';
        $err = 'Tell the Developer: Invalid Second Parameter Passed to `funk_use_safe_mutate()` Function that is meant to mutate a value in the $c Global Configuration Variable. This should be the string to an existing and Callable Function within the scope the function is used. It should also return a value to be validated!';
        funk_use_custom_error($c, ['json_or_page', [
            'json' => ['error' => $err],
            'page' => '500'
        ], $err], 500);
    }
    // Validate "$mainKeyAndOptionalSubKeys" is either a string (just the first key level in $c)
    // or an array of array meaning each level is the next level in $c. For example:
    // "mainKey" or "mainKey" => ["subKey1", => ["subKey2"], =>[=>...]]
    if (!is_string($mainKeyAndOptionalSubKeys) && !is_array($mainKeyAndOptionalSubKeys)) {
        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid First Parameter Passed to funk_use_safe_mutate() Function. Parameter should be either a Single String or An array of Strings where Each String Element is the next array key level in the $c. For example: `"mainKey"` or `["mainKey", "subKey1", "subKey2"]`. The first one accesses `$c["mainKey"]`, the second one accesses `$c["mainKey"]["subKey1"]["subKey2"]`.';
        $err = 'Tell the Developer: Invalid First Parameter Passed to `funk_use_safe_mutate()` Function that is meant to mutate a value in the $c Global Configuration Variable. Parameter should be either a Single String or An array of Strings where Each String Element is the next array key level in the $c. For example: `"mainKey"` or `["mainKey", "subKey1", "subKey2"]`. The first one accesses `$c["mainKey"]`, the second one accesses `$c["mainKey"]["subKey1"]["subKey2"]`.';
        funk_use_custom_error($c, ['json_or_page', [
            'json' => ['error' => $err],
            'page' => '500'
        ], $err], 500);
        return;
    }
    // Validate $expectedTypes is an array even if it's single element value is null.
    if (isset($expectedTypes) && !is_array($expectedTypes)) {
        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid Fifth Parameter Passed to funk_use_safe_mutate() Function. This should be an Array of Expected Types the Callable is allowed to return. This includes if you only want null returned meaning you set it to `[null]`. Set this Argument to just `null` if you want to Allow Any Value to be Returned from the Callable.';
        $err = 'Tell the Developer: Invalid Fifth Parameter Passed to `funk_use_safe_mutate()` Function that is meant to mutate a value in the $c Global Configuration Variable. This should be an Array of Expected Types the Callable is allowed to return. This includes if you only want null returned meaning you set it to `[null]`. Set this Argument to just `null` if you want to Allow Any Value to be Returned from the Callable.';
        funk_use_custom_error($c, ['json_or_page', [
            'json' => ['error' => $err],
            'page' => '500'
        ], $err], 500);
        return;
    }
    // Validate $expectedTypes are all string values that exist in the $validGetTypes array
    if (is_array($expectedTypes)) {
        foreach ($expectedTypes as $expectedType) {
            if (!is_string($expectedType) || !in_array($expectedType, $validGetTypes)) {
                $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid Value in the Fifth Parameter Passed to funk_use_safe_mutate() Function. This should be an Array of Expected Types the Callable is allowed to return. Each Value in the Array should be a String and one of the following: `' . implode('`, `', $validGetTypes) . '`';
                funk_use_custom_error($c, ['json_or_page', [
                    'json' => ['error' => 'Tell the Developer: Invalid Value in the Fifth Parameter Passed to `funk_use_safe_mutate()` Function that is meant to mutate a value in the $c Global Configuration Variable. This should be an Array of Expected Types the Callable is allowed to return. Each Value in the Array should be a String and one of the following: `' . implode('`, `', $validGetTypes) . '`'],
                    'page' => '500'
                ]], 500);
                return;
            }
        }
    }

    // Validate $expectedValueRanges if set (not null and must be an array),
    if (isset($expectedValueRanges)) {
        if (!is_array($expectedValueRanges) || empty($expectedValueRanges)) {
            $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid Sixth Parameter Passed to funk_use_safe_mutate() Function. This should be an Array of Expected Value Ranges the Callable is allowed to return. Each Key in the Array should be one of the following: `' . implode('`, `', $validValueRangeKeys) . '`. Set this Argument to just `null` if you do NOT want to validate value ranges.';
            funk_use_custom_error($c, ['json_or_page', [
                'json' => ['error' => 'Tell the Developer: Invalid Sixth Parameter Passed to `funk_use_safe_mutate()` Function that is meant to mutate a value in the $c Global Configuration Variable. This should be an Array of Expected Value Ranges the Callable is allowed to return. Each Key in the Array should be one of the following: `' . implode('`, `', $validValueRangeKeys) . '`. Set this Argument to just `null` if you do NOT want to validate value ranges.'],
                'page' => '500'
            ]], 500);
            return;
        }
        // Each element in $expectedValueRanges should be a key that matches the valid keys
        foreach ($expectedValueRanges as $key => $value) {
            if (!is_string($key) || !in_array($key, $validValueRangeKeys)) {
                $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid Key in the Sixth Parameter Passed to funk_use_safe_mutate() Function. This should be an Array of Expected Value Ranges the Callable is allowed to return. Each Key in the Array should be one of the following: `' . implode('`, `', $validValueRangeKeys) . '`. Set this Argument to just `null` if you do NOT want to validate value ranges.';
                funk_use_custom_error($c, ['json_or_page', [
                    'json' => ['error' => 'Tell the Developer: Invalid Key in the Sixth Parameter Passed to `funk_use_safe_mutate()` Function that is meant to mutate a value in the $c Global Configuration Variable. This should be an Array of Expected Value Ranges the Callable is allowed to return. Each Key in the Array should be one of the following: `' . implode('`, `', $validValueRangeKeys) . '`. Set this Argument to just `null` if you do NOT want to validate value ranges.'],
                    'page' => '500'
                ]], 500);
                return;
            }
        }
    }

    // If string, we turn it to an array with one element so we can iterate through it below
    if (is_string($mainKeyAndOptionalSubKeys)) {
        $mainKeyAndOptionalSubKeys = [$mainKeyAndOptionalSubKeys];
    }
    // If array, we iterate through it and build the reference to $c that we
    // want to mutate checking that its subkey actually exists first.
    if (is_array($mainKeyAndOptionalSubKeys)) {
        foreach ($mainKeyAndOptionalSubKeys as $subKey) {
            // 1. Validate the Subkey is a non-empty string
            if (!is_string($subKey) || empty($subKey)) {
                $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Invalid or Empty Subkey provided in Path at Current Depth: ' .  $currentPath;
                funk_use_custom_error($c, ['json_or_page', [
                    'json' => ['error' => 'Tell the Developer: Invalid or Empty Subkey provided in Path at Current Depth: ' . $currentPath . '.'],
                    'page' => '500'
                ]], 500);
                return;
            }
            // 2. Validate the Current Depth is an array so we can traverse it
            if (!is_array($cRef)) {
                $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Cannot Traverse Path. Expected Array at ' . $currentPath . ', but found a Non-Array Value.';
                funk_use_custom_error($c, ['json_or_page', [
                    'json' => ['error' => 'Tell the Developer: Cannot Traverse Path. Expected Array at: ' . $currentPath . ', but found a Non-Array Value.'],
                    'page' => '500'
                ]], 500);
                return;
            }
            // 3. Check for Key Existence at current depth
            if (!array_key_exists($subKey, $cRef)) {
                $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = 'Key `' . $subKey . '` does NOT exist at Path: `' . $currentPath . '`. Cannot mutate Non-existing Path!';
                funk_use_custom_error($c, ['json_or_page', [
                    'json' => ['error' => 'Tell the Developer: Key `' . $subKey . '` does NOT exist at Path: `' . $currentPath . '`. Cannot mutate Non-existing Path!'],
                    'page' => '500'
                ]], 500);
                return;
            }
            // 4. Move&Update the Reference Pointer to next possible subKey or Final Value
            $cRef = &$cRef[$subKey];
            $currentPath .= '[\'' . $subKey . '\']'; // Update path for next error message
        }
    }

    // We now have a valid reference to the target value in $c that we want to mutate
    $returnedValuesFromCallable = FUNKPHP_NO_VALUE;
    try {
        $returnedValuesFromCallable = call_user_func_array($callable, $callableArgs);
    } catch (\Throwable $e) {
        $err = 'Tell the Developer: An Exception or Error occurred while executing the Callable (`' . $callable . '`) for Path `' . $currentPath . '`. Error: `' . $e->getMessage() . '`';
        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
        funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
        return;
    }

    $returnedType = gettype($returnedValuesFromCallable);
    // OPTIONAL: Validate Expected Types (Specific Types: string, integer, boolean, null, array, object, resource)
    if (is_array($expectedTypes)) {
        if (!in_array($returnedType, $expectedTypes, true)) {
            $err = 'Callable (`' . $callable . '`) returned Unexpected Value Type for Path `' . $currentPath . '`. Returned Value Type was NOT the Expected Type. Expected one of: `' . implode('`, `', $expectedTypes) . '`. Returned Value was: `' . var_export($returnedValuesFromCallable, true) . '`. Set the $expectedTypes Argument to null to allow Any Return Type. Use the $expectedValueRanges Argument to Validate Value Ranges which only takes place after the Type Validation has passed.';
            $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
            funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
            return;
        }
    }

    // TODO: Validate value ranges based on returned type and the provided expectedValueRanges
    if (isset($expectedValueRanges)) {
        // Mapping of types to applicable range checks
        $typeToRangeMap = [
            'string' => [
                'exact_length',
                'min_length',
                'max_length',
                'exact_value',
                'allowed_values',
                'disallowed_values',
                'matches_regex',
                'is_json_string',
                'numeric_string',
                'is_falsey',
                'is_truthy'
            ],
            'integer' => [
                'exact_value',
                'min_value',
                'max_value',
                'allowed_values',
                'disallowed_values',
                'is_falsey',
                'is_truthy'
            ],
            'double' => [ // Doubles/floats use numeric range checks
                'exact_value',
                'min_value',
                'max_value',
                'allowed_values',
                'disallowed_values',
                'is_falsey',
                'is_truthy'
            ],
            'boolean' => [ // Only simple value checks apply
                'exact_value',
                'is_falsey',
                'is_truthy'
            ],
            'array' => [
                'exact_length',
                'min_length',
                'max_length',
                'array_count_min',
                'array_count_max',
                'array_keys_only',
                'is_falsey',
                'is_truthy'
            ],
            'object' => [
                'object_instanceof',
                'is_falsey',
                'is_truthy'
            ],
            'resource' => [ // Includes 'resource (closed)'
                'is_resource_type'
            ],
            'NULL' => [
                'exact_value' // Only checks for exact 'NULL' value
            ]
            // 'unknown type' and 'null' should generally not have range checks applied
        ];
        // Get the keys compatible with the ACTUAL returned type
        $compatibleKeys = $typeToRangeMap[$returnedType] ?? [];
        $returnedValue = $returnedValuesFromCallable; // Shorthand

        foreach ($expectedValueRanges as $key => $value) {
            // 1. INCOMPATIBILITY CHECK (Must be first)
            if (!in_array($key, $compatibleKeys)) {
                $err = 'Value Range Check Failed for Path `' . $currentPath . '`. The provided constraint `' . $key . '` is **incompatible** with the actual returned type: `' . $returnedType . '`. The return value was: `' . var_export($returnedValue, true) . '`.';
                $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
                funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
                return;
            }

            // 2. EXECUTE VALIDATION LOGIC
            switch ($key) {
                // --- Numeric Checks (for 'integer', 'double') ---
                case 'min_value':
                    if ($returnedValue < $value) {
                        $err = 'Value Range Check Failed for Path `' . $currentPath . '`. Value `' . $returnedValue . '` is below required minimum of `' . $value . '`.';
                        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
                        funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
                        return;
                    }
                    break;
                case 'max_value':
                    if ($returnedValue > $value) {
                        $err = 'Value Range Check Failed for Path `' . $currentPath . '`. Value `' . $returnedValue . '` is above required maximum of `' . $value . '`.';
                        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
                        funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
                        return;
                    }
                    break;
                // --- Length/Count Checks (for 'string', 'array') ---
                case 'exact_length':
                case 'min_length':
                case 'max_length':
                case 'array_count_min':
                case 'array_count_max':
                    // Normalize length/count logic
                    $length = ($returnedType === 'string') ? strlen($returnedValue) : count($returnedValue);
                    $checkName = ($returnedType === 'string') ? 'length' : 'count';

                    if (($key === 'exact_length' || $key === 'array_count_max') && $length !== $value) {
                        // Note: exact_length used for both string/array exact count
                        $err = 'Value Range Check Failed for Path `' . $currentPath . '`. ' . ucfirst($checkName) . ' (' . $length . ') is not exactly `' . $value . '`.';
                        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
                        funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
                        return;
                    }
                    // Handle min/max length/count... (similar error logic)
                    break;

                // --- Literal Value Checks (for all types) ---
                case 'exact_value':
                    if ($returnedValue !== $value) { // Use strict comparison
                        $err = 'Value Range Check Failed for Path `' . $currentPath . '`. Value is not exactly the required literal value.';
                        $c['err']['FUNCTIONS']['funk_use_safe_mutate'][] = $err;
                        funk_use_custom_error($c, ['json_or_page', ['json' => ['error' => $err], 'page' => '500']], 500);
                        return;
                    }
                    break;
                    // ... (Continue for all other $validValueRangeKeys) ...
            }
        }
    }

    // All ok, so mutate and return the mutated value back
    $cRef = $returnedValuesFromCallable;
    return $cRef;
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
 * NOTE: The implementation details of file saving (FunkDBConfig::clearCredentials() etc.)
 * are omitted here, but this is where it would happen.
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
        funk_save_log($c);
    }
    if (!isset($c['req']['log']) || !is_array($c['req']['log'])) {
        $c['err']['FUNCTIONS']['funk_clear_log'][] = 'The Log Array Did Not Exist, so it was Created Automatically!';
        funk_use_log($c, 'The Log Array Did Not Exist, so it was Created Automatically!', 'INFO');
    } else {
        $c['req']['log'] = [];
        funk_use_log($c, 'The Log Array was Cleared Successfully!', 'INFO');
    }
    return;
}

// Function to skip the post-response pipeline
function funk_skip_post_response(&$c)
{
    $c['req']['skip_post-response'] = true;
    ob_end_clean();
    return;
}

// `pipeline` is the list of functions to always run for each request (unless any
// of the functions terminates it early!) This is the main entry point for each request!
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_request(&$c, $passedValue = null)
{
    if (
        $passedValue === null
        || !is_string($passedValue)
        || !in_array($passedValue, ['defensive', 'happy'])
    ) {
        $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Passed Value for funk_run_pipeline_request() must be either `defensive` or `happy`!';
        $err = 'Tell the Developer: Passed Value for funk_run_pipeline_request() must be either `defensive` or `happy`!';
        funk_use_custom_error($c, ['json_or_page', [
            'json' => ['error' => $err],
            'page' => '500'
        ]], 500);
    }

    // 'defensive' = we check almost everything and output error to user if something gets wrong
    if ($passedValue === 'defensive') {
        // Must be a non-empty numbered array
        if (
            !isset($c['<ENTRY>']['pipeline']['request'])
            || !is_array($c['<ENTRY>']['pipeline']['request'])
            || !array_is_list($c['<ENTRY>']['pipeline']['request'])
            || count($c['<ENTRY>']['pipeline']['request']) === 0
        ) {
            $c['err']['PIPELINE']['funk_run_pipeline_request'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "request"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'request\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!';
            $err = 'Tell the Developer: No Pipeline Functions to run? Please check the `[\'pipeline\'][\'request\']` Key in the `funkphp/config/pipeline.php` File!';
            funk_use_custom_error($c, ['json_or_page', [
                'json' => ['error' => $err],
                'page' => '500'
            ]], 500);
        }

        // Prepare for main loop to run each pipeline function
        $count = count($c['<ENTRY>']['pipeline']['request']);
        $pipeDir = ROOT_FOLDER . '/pipeline/request/';
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }

            // $current pipeline function should be a single associative array with a single value (which can be null)
            $current_pipe = $c['<ENTRY>']['pipeline']['request'][$i] ?? null;
            if (
                !isset($current_pipe)
                || !is_array($current_pipe)
                || array_is_list($current_pipe)
                || count($current_pipe) !== 1
            ) {
                $c['err']['PIPELINE']['funk_run_pipeline_request'][] = 'Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null, to omit passing any values)';
                $err = 'Tell the Developer: Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null to omit passing any values)';
                funk_use_custom_error($c, ['json_or_page', [
                    'json' => ['error' => $err],
                    'page' => '500'
                ]], 500);
            }
            $fnToRun = key($current_pipe);
            $pipeToRun = $pipeDir . $fnToRun . '.php';
            $pipeValue = $current_pipe[$fnToRun] ?? null;
            $c['req']['current_passed_value']['pipeline']['request'][$fnToRun] = $pipeValue;
            $c['req']['current_passed_values']['pipeline']['request'][] = [$fnToRun => $pipeValue];

            // if = pipeline already exists in dispatchers, so reuse it but with newly passed value!
            if (isset($c['dispatchers']['pipeline']['request'][$fnToRun])) {
                if (is_callable($c['dispatchers']['pipeline']['request'][$fnToRun])) {
                    $runPipeKey = $c['dispatchers']['pipeline']['request'][$fnToRun];
                    $rawRun = $runPipeKey($c, $pipeValue);
                    if (is_array($rawRun) && count($rawRun) === 1) {
                        $c['req']['last_returned_pipeline_value'] = $rawRun;
                    } else {
                        $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                    }
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    funk_use_custom_error($c, ['json_or_page', [
                        'json' => ['error' => $err],
                        'page' => '500'
                    ]], 500);
                }
            }
            // else = pipeline does not exist yet, so include, store and run it with passed value!
            else {
                if (!is_readable($pipeToRun)) {
                    $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                    $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                    funk_use_custom_error($c, ['json_or_page', [
                        'json' => ['error' => $err],
                        'page' => '500'
                    ]], 500);
                }
                $runPipe = include_once $pipeToRun;
                if (is_callable($runPipe)) {
                    $c['dispatchers']['pipeline']['request'][$fnToRun] = $runPipe;
                    $rawRun = $runPipe($c, $pipeValue);
                    if (is_array($rawRun) && count($rawRun) === 1) {
                        $c['req']['last_returned_pipeline_value'] = $rawRun;
                    } else {
                        $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                    }
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    funk_use_custom_error($c, ['json_or_page', [
                        'json' => ['error' => $err],
                        'page' => '500'
                    ]], 500);
                }
            }

            // Clean up before running the next pipeline function
            $c['req']['current_pipeline'] = $current_pipe;
            unset($c['<ENTRY>']['pipeline']['request'][$i]);
            $c['req']['deleted_pipeline#']++;
            $c['req']['completed_pipeline#']++;
            $c['req']['deleted_pipeline'][] = $fnToRun;
            $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['request'][$i + 1])
                && is_array($c['<ENTRY>']['pipeline']['request'][$i + 1])
                ? array_key_first($c['<ENTRY>']['pipeline']['request'][$i + 1])
                : null;
        }
    }
    // 'happy' = we assume almost everything is correct and just run the pipeline functions
    else if ($passedValue === 'happy') {
        $count = count($c['<ENTRY>']['pipeline']['request']);
        $pipeDir = ROOT_FOLDER . '/pipeline/request/';
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }

            // Initialize current pipeline function to run without any checks in 'happy' mode
            $current_pipe = $c['<ENTRY>']['pipeline']['request'][$i] ?? null;
            $fnToRun = key($current_pipe);
            $pipeValue = $current_pipe[$fnToRun] ?? null;
            $c['req']['current_passed_value']['pipeline']['request'][$fnToRun] = $pipeValue;
            $c['req']['current_passed_values']['pipeline']['request'][] = [$fnToRun => $pipeValue];

            // if = run already loaded middleware from dispatchers
            if (isset($c['dispatchers']['pipeline']['request'][$fnToRun])) {
                if (is_callable($c['dispatchers']['pipeline']['request'][$fnToRun])) {
                    $runPipeKey = $c['dispatchers']['pipeline']['request'][$fnToRun];
                    $rawRun = $runPipeKey($c, $pipeValue);
                    if (is_array($rawRun) && count($rawRun) === 1) {
                        $c['req']['last_returned_pipeline_value'] = $rawRun;
                    } else {
                        $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                    }
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    funk_use_custom_error($c, ['json_or_page', [
                        'json' => ['error' => $err],
                        'page' => '500'
                    ]], 500);
                }
            }
            // else = include, store and run pipeline function
            else {
                $pipeToRun = $pipeDir . $fnToRun . '.php';
                $runPipe = include_once $pipeToRun;
                if (is_callable($runPipe)) {
                    $c['dispatchers']['pipeline']['request'][$fnToRun] = $runPipe;
                    $rawRun = $runPipe($c, $pipeValue);
                    if (is_array($rawRun) && count($rawRun) === 1) {
                        $c['req']['last_returned_pipeline_value'] = $rawRun;
                    } else {
                        $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                    }
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    funk_use_custom_error($c, ['json_or_page', [
                        'json' => ['error' => $err],
                        'page' => '500'
                    ]], 500);
                }
            }
            // Clean up before running the next pipeline function
            $c['req']['current_pipeline'] = $current_pipe;
            unset($c['<ENTRY>']['pipeline']['request'][$i]);
            $c['req']['deleted_pipeline#']++;
            $c['req']['completed_pipeline#']++;
            $c['req']['deleted_pipeline'][] = $fnToRun;
            $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['request'][$i + 1])
                && is_array($c['<ENTRY>']['pipeline']['request'][$i + 1])
                ? array_key_first($c['<ENTRY>']['pipeline']['request'][$i + 1])
                : null;
        }
    }

    // Default values after either 'defensive' or 'happy' mode has run
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['request'] = null;
}

// Try run middlewares AFTER handled request (and this can
// also be due to being exited prematurely by the application)
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_post_response(&$c, $passedValue = null)
{
    // Use ob_start() to "swallow" any possibly unwanted output to the client
    // but before starting, check if it already exists and clear its previous
    // contents if it does!
    ob_start();

    // We only run post-response pipelines if not skipped by the application!
    // and they are also optional, so it can be skipped if not configured!
    if ($c['req']['skip_post-response']) {
        $c['err']['MAYBE']['PIPELINE']['POST-RESPONSE']['funk_run_pipeline_post_response'][] = 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!';
        funk_use_log($c, 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!', 'INFO');
        ob_end_clean();
        return;
    }
    if (
        $passedValue === null
        || !is_string($passedValue)
        || !in_array($passedValue, ['defensive', 'happy'])
    ) {
        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Passed Value for funk_run_pipeline_post_response() must be either `defensive` or `happy`!';
        funk_use_log($c, 'Invalid Pipeline Mode Passed Value (to run all Post-Response Pipeline Functions) - should be either `defensive` or `happy`! - No Post-Response Pipeline Functions were ran as a result.', 'CRITICAL');
        ob_end_clean();
        return;
    }
    // 'defensive' = we check almost everything and output error to user if something gets wrong
    if ($passedValue === 'defensive') {
        // Must be a non-empty numbered array if it is set
        if (
            isset($c['<ENTRY>']['pipeline']['post-response'])
        ) {
            if (
                !is_array($c['<ENTRY>']['pipeline']['post-response'])
                || !array_is_list($c['<ENTRY>']['pipeline']['post-response'])
                || count($c['<ENTRY>']['pipeline']['post-response']) === 0
            ) {
                $c['err']['PIPELINE']['funk_run_pipeline_post_response'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post-response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post-response\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!';
                funk_use_log($c, 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post-response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post-response\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File! - Function stops here!', 'CRITICAL');
                ob_end_clean();
                return;
            }
            // Prepare for main loop to run each pipeline function
            $count = count($c['<ENTRY>']['pipeline']['post-response']);
            $pipeDir = ROOT_FOLDER . '/pipeline/post-response/';
            $c['req']['keep_running_pipeline'] = true;
            for ($i = 0; $i < $count; $i++) {
                if ($c['req']['keep_running_pipeline'] === false) {
                    break;
                }
                // $current pipeline function should be a single associative array with a single value (which can be null)
                $current_pipe = $c['<ENTRY>']['pipeline']['post-response'][$i] ?? null;
                if (
                    !isset($current_pipe)
                    || !is_array($current_pipe)
                    || array_is_list($current_pipe)
                    || count($current_pipe) !== 1
                ) {
                    $c['err']['PIPELINE']['funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null, to omit passing any values)';
                    funk_use_log($c, 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null, to omit passing any values) - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
                $fnToRun = key($current_pipe);
                $pipeToRun = $pipeDir . $fnToRun . '.php';
                $pipeValue = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_value']['pipeline']['post-response'][$fnToRun] = $pipeValue;
                $c['req']['current_passed_values']['pipeline']['post-response'][] = [$fnToRun => $pipeValue];
                // if = pipeline already exists in dispatchers, so reuse it but with newly passed value!
                if (isset($c['dispatchers']['pipeline']['post-response'][$fnToRun])) {
                    if (is_callable($c['dispatchers']['pipeline']['post-response'][$fnToRun])) {
                        $runPipeKey = $c['dispatchers']['pipeline']['post-response'][$fnToRun];
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-response'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            : null;
                        $rawRun = $runPipeKey($c, $pipeValue);
                        if (is_array($rawRun) && count($rawRun) === 1) {
                            $c['req']['last_returned_pipeline_value'] = $rawRun;
                        } else {
                            $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                        }
                    }
                    // HARD ERROR to not allow to pass security checks
                    else {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                        ob_end_clean();
                        return;
                    }
                }
                // else = pipeline does not exist yet, so include, store and run it with passed value!
                else {
                    if (!is_readable($pipeToRun)) {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory! - Function stops here!', 'CRITICAL');
                        ob_end_clean();
                        return;
                    }
                    $runPipe = include_once $pipeToRun;
                    if (is_callable($runPipe)) {
                        $c['dispatchers']['pipeline']['post-response'][$fnToRun] = $runPipe;
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-response'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            : null;
                        $rawRun = $runPipe($c, $pipeValue);
                        if (is_array($rawRun) && count($rawRun) === 1) {
                            $c['req']['last_returned_pipeline_value'] = $rawRun;
                        } else {
                            $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                        }
                    }
                    // HARD ERROR to not allow to pass security checks
                    else {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                        ob_end_clean();
                        return;
                    }
                }
            }
        }
    }
    // 'happy' = we assume almost everything is correct and just run the pipeline functions
    else if ($passedValue === 'happy') {
        if (
            isset($c['<ENTRY>']['pipeline']['post-response'])
        ) {
            $count = count($c['<ENTRY>']['pipeline']['post-response']);
            $pipeDir = ROOT_FOLDER . '/pipeline/post-response/';
            $c['req']['keep_running_pipeline'] = true;
            for ($i = 0; $i < $count; $i++) {
                if ($c['req']['keep_running_pipeline'] === false) {
                    break;
                }
                // Initialize current pipeline function to run without any checks in 'happy' mode
                $current_pipe = $c['<ENTRY>']['pipeline']['post-response'][$i] ?? null;
                $fnToRun = key($current_pipe);
                $pipeValue = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_value']['pipeline']['post-response'][$fnToRun] = $pipeValue;
                $c['req']['current_passed_values']['pipeline']['post-response'][] = [$fnToRun => $pipeValue];
                // if = run already loaded middleware from dispatchers
                if (isset($c['dispatchers']['pipeline']['post-response'][$fnToRun])) {
                    if (is_callable($c['dispatchers']['pipeline']['post-response'][$fnToRun])) {
                        $runPipeKey = $c['dispatchers']['pipeline']['post-response'][$fnToRun];
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-response'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            : null;
                        $rawRun = $runPipeKey($c, $pipeValue);
                        if (is_array($rawRun) && count($rawRun) === 1) {
                            $c['req']['last_returned_pipeline_value'] = $rawRun;
                        } else {
                            $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                        }
                    }
                    // HARD ERROR to not allow to pass security checks
                    else {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                        ob_end_clean();
                        return;
                    }
                }
                // else = include, store and run pipeline function
                else {
                    $pipeToRun = $pipeDir . $fnToRun . '.php';
                    $runPipe = include_once $pipeToRun;
                    if (is_callable($runPipe)) {
                        $c['dispatchers']['pipeline']['post-response'][$fnToRun] = $runPipe;
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-response'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-response'][$i + 1])
                            : null;
                        $rawRun = $runPipe($c, $pipeValue);
                        if (is_array($rawRun) && count($rawRun) === 1) {
                            $c['req']['last_returned_pipeline_value'] = $rawRun;
                        } else {
                            $c['req']['last_returned_pipeline_value'] = FUNKPHP_NO_VALUE;
                        }
                    }
                    // HARD ERROR to not allow to pass security checks
                    else {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                        ob_end_clean();
                        return;
                    }
                }
            }
        }
    }
    // Default values after either 'defensive' or 'happy' mode has run
    ob_end_clean(); // Clear any possibly unwanted output to the client
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['post-response'] = null;
}

// Three functions that just returned the last stored value
// for middleware, pipeline and route key handlers. It returns
// `FUNK_NO_VALUE` when there was no last stored value.
// IMPORTANT: Not passing a value also stores thus
// the FUNK_NO_VALUE and overwrites any previous value!
function funk_last_return_middleware_value(&$c)
{
    return $c['req']['last_returned_middleware_value'];
}
function funk_last_return_pipeline_value(&$c)
{
    return $c['req']['last_returned_pipeline_value'];
}
function funk_last_return_route_key_value(&$c)
{
    return $c['req']['last_returned_route_key_value'];
}

// Exit the Pipeline and stop running any further pipeline functions
// This is useful when you want to stop the pipeline early
// IMPORTANT: As you can see, it will remove all remaining
// pipeline functions, so use with care!
function funk_abort_pipeline_request(&$c)
{
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['request'] = null;
    return;
}
// Same as above but used for the post response functions
// IMPORTANT: As you can see, it will remove all remaining
// pipeline functions, so use with care!
function funk_abort_pipeline_post_response(&$c)
{
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['post-response'] = null;
    return;
}
// Abort the middlewares and stop running any further middlewares
// IMPORTANT: As you can see, it will remove all
// remaining middleware functions, so use with care!
// WARNING: Careful with aborting middlewares, particularly if
// any kind of authentication/authorization is used for the route!
function funk_abort_middlewares(&$c)
{
    $c['req']['current_middleware'] = null;
    $c['req']['keep_running_middlewares'] = false;
    $c['req']['matched_middlewares'] = null;
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
        $routeDefinition = funk_match_compiled_route($c, $uri, $compiledRouteTrie[$method]);
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
            // We remove 'middlewares' from the matched route since it will
            // be array merged with all middleware-matched URI segments!
            if (isset($routeInfo[0]['middlewares'])) {
                $routeInfo = array_splice($routeInfo, 1, null, true);
            }
            $c['req']['route_keys'] = [...$routeInfo ?? []];
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
                        && isset($developerSingleRoutes[$method][$middleware][0]['middlewares'])
                    ) {
                        $matchedMiddlewareHandlers = array_merge($matchedMiddlewareHandlers, $developerSingleRoutes[$method][$middleware][0]['middlewares']);
                    }
                }
            }
            $c['req']['matched_middlewares'] = $matchedMiddlewareHandlers;
            return true;
        } else {
            $noMatchIn .= 'NO MATCH IN DEVELOPER_ROUTES(funkphp/config/routes.php)';
        }
    } else {
        $noMatchIn .= 'NO MATCH IN COMPILED_ROUTES(funkphp/_internals/compiled/troute_route.php)';
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
