<?php // ENTRY POINT OF EACH HTTP(S) REQUEST USING FUNKPHP!
// Load all functions needed for the FunkPHP Framework Web Application
// $c is the global configuration array that is used throughout the application
require_once __DIR__ . '/core/functions.php';
$c = require_once __DIR__ . '/config/_all.php';
$c['<ENTRY>'] = require_once __DIR__ . '/pipeline/pipeline.php';
// Use either Custom Exception Handler by Developer OR Default one!
// Developer is advised to use `funk_use_error_throw` to intentionally
// throw exceptions that are caught the Developer then catches later!
set_exception_handler(function (\Throwable $e) use (&$c) {
    if (function_exists('funk_handle_uncaught_exception')) {
        funk_handle_uncaught_exception($c, $e);
    } else {
        funk_default_exception_handler($c, $e);
    }
});
// Load Composer Autoloader so that any Composer installed packages can be used
if (FUNKPHP_USE_VENDOR) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}
// Prepare what to run after each request is handled
// and/or exit() is used prematurely by the application
register_shutdown_function(function () use (&$c) {
    if (function_exists('funk_set_register_shutdown_function')) {
        funk_set_register_shutdown_function($c);
    } else {
        funk_default_register_shutdown_function($c);
    }
});
// The MAIN "KERNEL" STEP: Run the Pipeline of Anonymous
// Functions that control the flow of the request. It will
// be caught by the Global Exception Handler if any uncaught
// exceptions occur during the Pipeline execution!
// Choose between 'happy' or 'defensive' mode in the config file!
funk_run_pipeline_request($c, FUNKPHP_PIPLINE_REQUEST_ENTRY);
// The registered shutdown callback function will be executed after pipeline
// has run (unless the script is exited prematurely by the application)!
