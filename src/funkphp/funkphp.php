<?php // ENTRY POINT OF EACH HTTP(S) REQUEST thanks to ".htaccess" file
// CHECK DEFAULT FOLDERS & FILES OR CRITICAL ERROR BASED 'ACCEPT' TPYE!
if (
    !is_readable(__DIR__ . '/config/_all.php')
    || !is_readable(__DIR__ . '/_internals/functions/_all.php')
    || !is_readable(__DIR__ . '/pipeline/pipeline.php')
) {
    critical_err_json_or_html(500, 'Tell the Developer: The Global Configuration Variable `\$c` could not be loaded and/or all the necessary Function Files!');
}
// Load all functions needed for the FunkPHP Framework Web Application
// $c is the global configuration array that is used throughout the application
include_once __DIR__ . '/_internals/functions/_all.php';
$c = include_once __DIR__ . '/config/_all.php';
$c['<ENTRY>'] = include_once __DIR__ . '/pipeline/pipeline.php';

// Prepare a global exception handler to catch any uncaught exceptions
// even though the Developer is advised to use `funk_use_error_throw` to
// intentionally throw exceptions that the Developer then catches later!
set_exception_handler(function (\Throwable $e) use (&$c) {
    // $c and all functions should be available by now
    $c['err']['UNCAUGHT_EXCEPTION'] = $e;
    funk_use_log($c, "UNCAUGHT EXCEPTION BY DEVELOPER: " . $e->getMessage(), 'CRIT');
    $err = 'Tell the Developer: An Uncaught Exception Occurred: `' . $e->getMessage() . '` Please check the Logs for more details.';
    funk_use_error_json_or_page($c, 500, ["internal_error" => $err], '500', $err);
});

// Load Composer Autoloader so that any Composer installed packages can be used
require_once __DIR__ . '/vendor/autoload.php';

// Prepare what to run after each request is handled
// and/or exit() is used prematurely by the application
register_shutdown_function(function () use (&$c) {
    if (
        isset($c['<ENTRY>']['pipeline']['post-response'])
        && is_array($c['<ENTRY>']['pipeline']['post-response'])
        && array_is_list($c['<ENTRY>']['pipeline']['post-response'])
        && !empty($c['<ENTRY>']['pipeline']['post-response'])
    ) {
        funk_run_pipeline_post_response($c, 'happy'); // Choose between 'happy' or 'defensive' mode
    } else {
        $c['err']['MAYBE']['PIPELINE']['funk_run_post_request'][] = 'No Configured Post-Response Pipeline Functions (`"<ENTRY>" => "pipeline" => "post-response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post-response\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!';
        funk_use_log($c, 'No Configured Post-Request Pipeline Functions (`"<ENTRY>" => "pipeline" => "post-response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post-responset\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!', 'WARN');
    }
});
// The MAIN "KERNEL" STEP: Run the Pipeline of Anonymous
// Functions that control the flow of the request. It will
// be caught by the Global Exception Handler if any uncaught
// exceptions occur during the Pipeline execution!
if (
    isset($c['<ENTRY>']['pipeline']['request']) &&
    is_array($c['<ENTRY>']['pipeline']['request']) &&
    array_is_list($c['<ENTRY>']['pipeline']['request']) &&
    !empty($c['<ENTRY>']['pipeline']['request'])
) {
    funk_run_pipeline_request($c, 'defensive'); // Choose between 'happy' or 'defensive' mode
} else {
    $c['err']['MAYBE']['PIPELINE']['funk_run_pipeline'][] = 'No (Valid) Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "request"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File. A Valid Configured Pipeline Function Is: ``[\'pipeline\'][\'request\'] => [0 => [\'pl_https_redirect\' => null], 1 => [\'pl_run_ini_sets\' => null], ...]` where it starts as a numbered array of associative arrays where each associative array has exactly one key-value pair where the Key is the Function Filename and the Value is either `null` OR an Array of Passed Parameters to the Pipeline Function!';
    $err = 'Tell the Developer: No Pipeline Functions to run or misconfigured? Please check the `[\'pipeline\'][\'request\']` Key in the `funkphp/config/pipeline.php` File. A Valid Configured Pipeline Function Is: ``[\'pipeline\'][\'request\'] => [0 => [\'pl_https_redirect\' => null], 1 => [\'pl_run_ini_sets\' => null], ...]` where it starts as a numbered array of associative arrays where each associative array has exactly one key-value pair where the Key is the Function Filename and the Value is either `null` OR an Array of Passed Parameters to the Pipeline Function!';
    funk_use_error_json_or_page($c, 500, ["internal_error" => $err], '500', $err);
}
// The registered shutdown callback function will be executed after pipeline
// has run (unless the script is exited prematurely by the application)!
