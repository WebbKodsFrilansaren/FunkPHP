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

// Function allows for custom error such as returning `json`,`page`,`xml`, ``text``, `html`, a `throw` exception
// or running a custom `callback` function. Defaults to `critical_err_json_or_html()` when used the wrong way!
function funk_use_custom_error(&$c, $handleTypeAndDataOptionalCBData, $errorCode = 500)
{
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
}

// Function to skip the post-response pipeline
function funk_skip_post_response(&$c)
{
    $c['req']['skip_post-response'] = true;
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
        critical_err_json_or_html(500, 'Tell the Developer: Invalid Pipeline Mode Passed Value (to run all Request Pipeline Functions) - should be either `defensive` or `happy`!');
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
            critical_err_json_or_html(500, 'Tell the Developer: No Pipeline Functions to run? Please check the `[\'pipeline\'][\'request\']` Key in the `funkphp/config/pipeline.php` File!');
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
                critical_err_json_or_html(500, 'Tell the Developer: Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null to omit passing any values)');
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
                    critical_err_json_or_html(500, 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
                }
            }
            // else = pipeline does not exist yet, so include, store and run it with passed value!
            else {
                if (!is_readable($pipeToRun)) {
                    $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                    critical_err_json_or_html(500, 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!');
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
                    critical_err_json_or_html(500, 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
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
                    critical_err_json_or_html(500, 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
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
                    critical_err_json_or_html(500, 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
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
    if (ob_get_level() === 0) {
        ob_start();
    } else {
        ob_clean();
    }

    // We only run post-response pipelines if not skipped by the application!
    // and they are also optional, so it can be skipped if not configured!
    if ($c['req']['skip_post-response']) {
        $c['err']['MAYBE']['PIPELINE']['POST-RESPONSE']['funk_run_pipeline_post_response'][] = 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!';
        funk_use_log($c, 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!', 'INFO');
        return;
    }
    if (
        $passedValue === null
        || !is_string($passedValue)
        || !in_array($passedValue, ['defensive', 'happy'])
    ) {
        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Passed Value for funk_run_pipeline_post_response() must be either `defensive` or `happy`!';
        funk_use_log($c, 'Invalid Pipeline Mode Passed Value (to run all Post-Response Pipeline Functions) - should be either `defensive` or `happy`! - No Post-Response Pipeline Functions were ran as a result.', 'CRITICAL');
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
                funk_use_log($c, 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post-response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post-response\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!', 'CRITICAL');
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
                    funk_use_log($c, 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null, to omit passing any values)', 'CRITICAL');
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
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`', 'CRITICAL');
                    }
                }
                // else = pipeline does not exist yet, so include, store and run it with passed value!
                else {
                    if (!is_readable($pipeToRun)) {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!', 'CRITICAL');
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
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`', 'CRITICAL');
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
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`', 'CRITICAL');
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
                        funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`', 'CRITICAL');
                    }
                }
            }
        }
    }
    // Default values after either 'defensive' or 'happy' mode has run
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
// Same as above but used for the exit functions instead of the pipeline
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
