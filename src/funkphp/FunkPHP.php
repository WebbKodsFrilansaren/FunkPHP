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
require_once __DIR__ . '/core/classes.php'; // Core classes
require_once __DIR__ . '/core/functions.php'; // Core functions
$c = null; // Initialize c that is then populated after compile() and/or run()
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', 1);
$FUNKPHP = require_once __DIR__ . '/core/app.php';
if (is_object($FUNKPHP) && $FUNKPHP instanceof FunkPHP) {
    $reflectFunk = new ReflectionObject($FUNKPHP);
    $cProperty   = $reflectFunk->getProperty('c');
    $cProperty->setAccessible(true);
    $cInstance   = $cProperty->getValue($FUNKPHP);
    // Attempt running Class C->compile() which meaning it will attempt
    // compiling first and then run right after it if no errors occurs.
    $compileAndRun = new ReflectionMethod($cInstance, 'compile');
    $compileAndRun->setAccessible(true);
    $compileAndRun->invoke($cInstance);
    // Only here we consider loading validated user-defined functions&classes
    require_once __DIR__ . '/config/functions.php';
    require_once __DIR__ . '/config/classes.php';
}
// When it is NOT FunkPHP Object as defined in the `/src/funkphp/core/functions.php`
else {
    dd([
        'Internal FunkPHP Error' => 'Expected to find `FunkPHP Class` in `/src/funkphp/config/app.php` but instead found Data Type: `' . (is_object($FUNKPHP) ? get_class($FUNKPHP) : gettype($FUNKPHP)) . '`',
        'Step-by-Step Fix' => 'The return Value in `/src/funkphp/config/app.php` must be the Object Instance of `FunkPHP` (defined in `/src/funkphp/core/functions.php`) that is returned at the end of the File. `DO NOT` modify the `return $FUNK; statement` (unless: you just `return the entire Object in one long method-chaining`) at the end of the `app.php` File.'
    ], 'See Internal FunkPHP Error');
}
exit;


//$c['<ENTRY>'] = require_once __DIR__ . '/core/pipeline_request.php';
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
