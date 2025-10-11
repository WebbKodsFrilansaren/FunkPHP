<?php // ROUTE-related FUNCTIONS FOR FunPHP

// Function calls `funkphp/config/errors_custom.php` to handle errors in a custom way or it
// defaults to `critical_err_json_or_html()` if no matching custom error handler is found!
function funk_handle_custom_error(&$c, $errorKey, $errorType, $errorCode = 500, $handleType, $handleData, $callbackData = null, $skipPostRequest = true)
{
    // Check if $skipPostRequest is true, if so, skip post-request pipeline
    if ($skipPostRequest === true) {
        funk_skip_post_request($c);
    }
    // Available error types it can handle as of now! - more can be added as needed!
    $availableHandleTypes = ['json', 'page', 'json_or_page', 'callback', 'html', 'text', 'xml'];
    // When no valid handleData provided (it cannot be null or empty, that's really all)
    if (
        !isset($handleData)
        || empty($handleData)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function. This should be either a string (for json/html/page types) or an array/object (for callback type)!');
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
    // When handleType is not a string and not in the available types
    if (
        !isset($handleType)
        || !is_string($handleType)
        || empty($handleType)
        || !in_array($handleType, $availableHandleTypes)
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Type Provided to funk_handle_custom_error() Function. This should be one of the following:`' . implode(', ', $availableHandleTypes) . '`!');
    }
    // Check if we have a valid error key to handle
    // Default to critical when error key not found in $c!
    if (!isset($c['errors_custom']) || !is_array($c['errors_custom'])) {
        critical_err_json_or_html(500, 'Tell the Developer: No Custom Error Handlers Configured in `$c[\'errors_custom\']` Key! Please check the `funkphp/config/error_custom.php` File!');
    }
    // When no valid function to target handling an error for
    if (
        !isset($errorKey)
        || !is_string($errorKey)
        || empty($errorKey)
        || !isset($c['errors_custom'][$errorKey])
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Key Provided to funk_handle_custom_error() Function. This is usually the name of the function where an error occurred, e.g. `pl_db_connect` or `m_middleware` etc. Please check the `funkphp/config/error_custom.php` File!');
    }
    // When no valid error type for existing valid function to error handle
    if (
        !isset($errorType)
        || !is_string($errorType)
        || empty($errorType)
        || !isset($c['errors_custom'][$errorKey][$errorType])
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Type Provided to funk_handle_custom_error() Function. This is usually the type of error that occurred, e.g. `NO_DATABASE_CONNECTION`, `NO_MIDDLEWARE_FOUND` etc. Please check the `funkphp/config/error_custom.php` File!');
    }
    // When handlerType (which is valid now) is NOT in the array of the $errorType for the $errorKey
    if (
        !isset($c['errors_custom'][$errorKey][$errorType])
        || !is_array($c['errors_custom'][$errorKey][$errorType])
        || !isset($c['errors_custom'][$errorKey][$errorType][$handleType])
    ) {
        critical_err_json_or_html(500, 'Tell the Developer: No Valid Handler Type Found for the Existing ErrorKey and its Existing ErrorType! Please check the `funkphp/config/error_custom.php` File!');
    }
    // HERE WE HAVE VALIDATED: [FunctionName][ErrorType][HandleType] exists in $c['errors_custom'] and that it is valid
    // Now, we attempt using the different handleTypes and this is where we could get critical error if maybe JSON is not
    // provided when asked to use JSON etc.
    http_response_code($errorCode);

    // Handle JSON Handle Type
    if ($handleType === 'json') {
        if (!isset($handleData) || (!is_array($handleData) && !is_object($handleData)) || empty($handleData)) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `json` Type. This should be a non-empty array!');
        }
        try {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($handleData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred While Encoding the Provided Data to JSON (`' . $e->getMessage() . '`) inside funk_handle_custom_error() Function for `json` Type!');
        }
    }  // Handle Page Type
    else if ($handleType === 'page') {
        // TODO: Later when 'page' has been implemented in the entire FunkPHP framework! - not far from now!
    }  // Handle JSON Or Page Type (based on 'accept' header)
    else if ($handleType === 'json_or_page') {
        // We want JSON
        if (
            isset($c['req']['accept'])
            && is_string($c['req']['accept'])
            && !empty($c['req']['accept'])
            && in_array($c['req']['accept'], ['application/json', 'text/json'])
        ) {
            if (!isset($handleData) || (!is_array($handleData) && !is_object($handleData)) || empty($handleData)) {
                critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `json_or_page` Type when `Accept` Header indicates JSON is wanted. This should be a non-empty array!');
            }
            try {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($handleData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\JsonException $e) {
                critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred While Encoding the Provided Data to JSON (`' . $e->getMessage() . '`) inside funk_handle_custom_error() Function for `json` Type!');
            }
        } // We want Page
        else {
            // TODO: Later when 'page' has been implemented in the entire FunkPHP framework! - not far from now!
        }
    }  // Handle Callback Type
    else if ($handleType === 'callback') {
        // $callBack data should not be null if this is the case
        if (!isset($callbackData) || empty($callbackData)) {
            critical_err_json_or_html(500, 'Tell the Developer: No Callback Data Provided to funk_handle_custom_error() Function. This should be some value else besides `null` or `empty` that is passed to the Callable Function Name!');
        }
        if (!isset($handleData) || !is_callable($handleData)) {
            critical_err_json_or_html(500, 'Tell the Developer: Invalid Callback Handle Type Provided to funk_handle_custom_error() Function. This should be a Valid Callable Function Name within the Available Scope of this Custom Error Handling Function!!');
        }
        try {
            $handleData($c, $callbackData);
        } catch (\Throwable $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the Custom Error Callback (`' . $e->getMessage() . '`) the Developer had Configured!');
        }
    }  // Handle HTML Type
    else if ($handleType === 'html') {
        // Validate that handleData is a string and NOT empty
        if (
            !isset($handleData)
            || !is_string($handleData)
            || empty($handleData)
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `html` Type. This should be a non-empty string!');
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $handleData;
    }  // Handle Text Type
    else if ($handleType === 'text') {
        // Validate that handleData is a string and NOT empty
        if (
            !isset($handleData)
            || !is_string($handleData)
            || empty($handleData)
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `text` Type. This should be a non-empty string!');
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo $handleData;
    }  // Handle XML Type
    else if ($handleType === 'xml') {
        // Validate that handleData is a string and NOT empty
        if (
            !isset($handleData)
            || !is_string($handleData)
            || empty($handleData)
        ) {
            critical_err_json_or_html(500, 'Tell the Developer: No Valid Handle Data Provided to funk_handle_custom_error() Function for `xml` Type. This should be a non-empty string!');
        }
        header('Content-Type: application/xml; charset=utf-8');
        echo $handleData;
    }
    exit();
}

// Function to skip the post-request pipeline
function funk_skip_post_request(&$c)
{
    $c['req']['skip_post-request'] = true;
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
function funk_run_pipeline_post_request(&$c, $passedValue = null)
{
    // We only run post-request pipelines if not skipped by the application!
    // and they are also optional, so it can be skipped if not configured!
    if ($c['req']['skip_post-request']) {
        $c['err']['MAYBE']['PIPELINE']['POST-REQUEST']['funk_run_pipeline_post_request'][] = 'Post-Request Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Request Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_request(&$c)` could have been ran for your HTTP(S) Request!';
        return;
    }
    if (
        $passedValue === null
        || !is_string($passedValue)
        || !in_array($passedValue, ['defensive', 'happy'])
    ) {
        $c['err']['PIPELINE']['function funk_run_pipeline_post_request'][] = 'Passed Value for funk_run_pipeline_post_request() must be either `defensive` or `happy`!';
        critical_err_json_or_html(500, 'Tell the Developer: Invalid Pipeline Mode Passed Value (to run all Post-Request Pipeline Functions) - should be either `defensive` or `happy`!');
    }
    // 'defensive' = we check almost everything and output error to user if something gets wrong
    if ($passedValue === 'defensive') {
        // Must be a non-empty numbered array if it is set
        if (
            isset($c['<ENTRY>']['pipeline']['post-request'])
        ) {
            if (
                !is_array($c['<ENTRY>']['pipeline']['post-request'])
                || !array_is_list($c['<ENTRY>']['pipeline']['post-request'])
                || count($c['<ENTRY>']['pipeline']['post-request']) === 0
            ) {
                $c['err']['PIPELINE']['funk_run_pipeline_post_request'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post-request"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post-request\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!';
                critical_err_json_or_html(500, 'Tell the Developer: No Pipeline Functions to run? Please check the `[\'pipeline\'][\'post-request\']` Key in the `funkphp/config/pipeline.php` File!');
            }
            // Prepare for main loop to run each pipeline function
            $count = count($c['<ENTRY>']['pipeline']['post-request']);
            $pipeDir = ROOT_FOLDER . '/pipeline/post-request/';
            $c['req']['keep_running_pipeline'] = true;
            for ($i = 0; $i < $count; $i++) {
                if ($c['req']['keep_running_pipeline'] === false) {
                    break;
                }
                // $current pipeline function should be a single associative array with a single value (which can be null)
                $current_pipe = $c['<ENTRY>']['pipeline']['post-request'][$i] ?? null;
                if (
                    !isset($current_pipe)
                    || !is_array($current_pipe)
                    || array_is_list($current_pipe)
                    || count($current_pipe) !== 1
                ) {
                    $c['err']['PIPELINE']['funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null, to omit passing any values)';
                    critical_err_json_or_html(500, 'Tell the Developer: Pipeline Post-Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be an Associative Array Key (single element) with a Value! (Value can be null to omit passing any values)');
                }
                $fnToRun = key($current_pipe);
                $pipeToRun = $pipeDir . $fnToRun . '.php';
                $pipeValue = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_value']['pipeline']['post-request'][$fnToRun] = $pipeValue;
                $c['req']['current_passed_values']['pipeline']['post-request'][] = [$fnToRun => $pipeValue];
                // if = pipeline already exists in dispatchers, so reuse it but with newly passed value!
                if (isset($c['dispatchers']['pipeline']['post-request'][$fnToRun])) {
                    if (is_callable($c['dispatchers']['pipeline']['post-request'][$fnToRun])) {
                        $runPipeKey = $c['dispatchers']['pipeline']['post-request'][$fnToRun];
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-request'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
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
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        critical_err_json_or_html(500, 'Tell the Developer: Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
                    }
                }
                // else = pipeline does not exist yet, so include, store and run it with passed value!
                else {
                    if (!is_readable($pipeToRun)) {
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                        critical_err_json_or_html(500, 'Tell the Developer: Pipeline Post-Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!');
                    }
                    $runPipe = include_once $pipeToRun;
                    if (is_callable($runPipe)) {
                        $c['dispatchers']['pipeline']['post-request'][$fnToRun] = $runPipe;
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-request'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
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
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        critical_err_json_or_html(500, 'Tell the Developer: Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
                    }
                }
            }
        }
    }
    // 'happy' = we assume almost everything is correct and just run the pipeline functions
    else if ($passedValue === 'happy') {
        if (
            isset($c['<ENTRY>']['pipeline']['post-request'])
        ) {
            $count = count($c['<ENTRY>']['pipeline']['post-request']);
            $pipeDir = ROOT_FOLDER . '/pipeline/post-request/';
            $c['req']['keep_running_pipeline'] = true;
            for ($i = 0; $i < $count; $i++) {
                if ($c['req']['keep_running_pipeline'] === false) {
                    break;
                }
                // Initialize current pipeline function to run without any checks in 'happy' mode
                $current_pipe = $c['<ENTRY>']['pipeline']['post-request'][$i] ?? null;
                $fnToRun = key($current_pipe);
                $pipeValue = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_value']['pipeline']['post-request'][$fnToRun] = $pipeValue;
                $c['req']['current_passed_values']['pipeline']['post-request'][] = [$fnToRun => $pipeValue];
                // if = run already loaded middleware from dispatchers
                if (isset($c['dispatchers']['pipeline']['post-request'][$fnToRun])) {
                    if (is_callable($c['dispatchers']['pipeline']['post-request'][$fnToRun])) {
                        $runPipeKey = $c['dispatchers']['pipeline']['post-request'][$fnToRun];
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-request'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
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
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        critical_err_json_or_html(500, 'Tell the Developer: Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
                    }
                }
                // else = include, store and run pipeline function
                else {
                    $pipeToRun = $pipeDir . $fnToRun . '.php';
                    $runPipe = include_once $pipeToRun;
                    if (is_callable($runPipe)) {
                        $c['dispatchers']['pipeline']['post-request'][$fnToRun] = $runPipe;
                        // Clean up before running the next pipeline function
                        $c['req']['current_pipeline'] = $current_pipe;
                        unset($c['<ENTRY>']['pipeline']['post-request'][$i]);
                        $c['req']['deleted_pipeline#']++;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['deleted_pipeline'][] = $fnToRun;
                        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            && is_array($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
                            ? array_key_first($c['<ENTRY>']['pipeline']['post-request'][$i + 1])
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
                        $c['err']['PIPELINE']['function funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        critical_err_json_or_html(500, 'Tell the Developer: Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`');
                    }
                }
            }
        }
    }
    // Default values after either 'defensive' or 'happy' mode has run
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['post-request'] = null;
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
function funk_abort_pipeline_post_requset(&$c)
{
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['post-request'] = null;
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

function funk_param_is_email(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_email'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a valid email, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return filter_var($param, FILTER_VALIDATE_EMAIL) !== false;
}
