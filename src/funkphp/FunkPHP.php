<?php // ENTRY POINT OF EACH HTTP(S) REQUEST USING FUNKPHP!

/**
 * -------------------
 * FUNKPHP ENTRY POINT
 * -------------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/

// This is replaced by all Functions, Configuration and
// highly optimized Route Matching & Pipeline Execution
// in the large compiled FunkPHPDeployment.php File!
// Load all functions needed for the FunkPHP Framework Web Application
// $c is the global configuration array that is used throughout the application
require_once __DIR__ . '/core/functions.php'; // In-built functions
require_once __DIR__ . '/config/functions.php'; // User-defined functions
$c = require_once __DIR__ . '/core/c.php';
$c['<ENTRY>'] = require_once __DIR__ . '/core/pipeline_request.php';
// Use either Custom Exception Handler by Developer OR Default one!
// Developer is advised to use `funk_use_error_throw` to intentionally
// throw exceptions that are caught the Developer then catches later!
set_exception_handler(function (\Throwable $e) use (&$c) {
    if (
        isset($c['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
        && is_string($c['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
        && !empty($c['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
        && function_exists(($c['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']))
    ) {
        $c['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']($c, $e);
    } else {
        \funk_default_exception_handler($c, $e);
    }
});
// Load Composer Autoloader so that any Composer installed packages can be used
if (isset($c['FUNKPHP_USE_VENDOR']) && $c['FUNKPHP_USE_VENDOR'] === true) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}
// Prepare what to run after each request is handled
// and/or exit() is used prematurely by the application
register_shutdown_function(function () use (&$c) {
    if (
        isset($c['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
        && is_string($c['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
        && !empty($c['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
        && function_exists(($c['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']))
    ) {
        $c['req']['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']($c);
    } else {
        \funk_default_register_shutdown_function($c);
    }
});
ob_start();
// The MAIN "KERNEL" STEP: Run the Pipeline of Anonymous
\funk_run_pipeline_request($c);
