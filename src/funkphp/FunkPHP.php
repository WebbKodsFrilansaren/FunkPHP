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
// $iniSets = $c['INI_SETS'] ?? [];
// foreach ($iniSets as $key => $value) {
//     // Hard error on invalid configured $c['INI_SETS'] data
//     if (!is_string($key) || empty($key) || !is_scalar($value)) {
//         $err = 'Tell The Developer: Invalid Data Provided in $c[\'INI_SETS\'] Global Configuration Array. The Data must be an Associative Array with Non-Empty String Keys and Non-Empty Values that are either Strings, Numbers or Booleans. Thus, it is likely that the Developer have used a non-string for $key or a non-scalar/empty value for $value!';
//         funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
//     }
//     ini_set($key, $value);
// }

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', 1);
$FUNKPHP = require_once __DIR__ . '/config/app.php';
if (is_object($FUNKPHP) && $FUNKPHP instanceof FunkPHP) {
    $reflectFunk = new ReflectionObject($FUNKPHP);
    $cProperty   = $reflectFunk->getProperty('c');
    $cProperty->setAccessible(true);
    $cInstance   = $cProperty->getValue($FUNKPHP);
    $reflectC    = new ReflectionObject($cInstance);
    $getProp = function (string $propName) use ($reflectC, $cInstance) {
        if (!$reflectC->hasProperty($propName)) {
            return null;
        }
        $prop = $reflectC->getProperty($propName);
        $prop->setAccessible(true);
        return $prop->getValue($cInstance);
    };
    $debug    = $getProp('debug') ?? [];
    $errors   = $getProp('errors') ?? [];
    $warnings = $getProp('WARNINGS') ?? [];
    $fluent   = $getProp('FunkPHPFluentAPI') ?? [];
    $errCount  = count($errors);
    $warnCount = count($warnings);
    $isDebugOn          = $debug['ON_OR_OFF'] ?? false;
    $alwaysShow         = $debug['ALWAYS_SHOW'] ?? true;
    $showValid          = $debug['SHOW_VALID_BATCHES'] ?? false;
    $showInvalid        = $debug['SHOW_INVALID_BATCHES'] ?? false;
    $showCached         = $debug['SHOW_CACHED'] ?? false;
    $showCompiled       = $debug['SHOW_COMPILED'] ?? false;
    $showAll            = $debug['SHOW_ALL'] ?? false;
    $hasErrorsToReport = ($errCount > 0);
    $shouldTriggerDump = $hasErrorsToReport || ($isDebugOn && $alwaysShow);
    if ($shouldTriggerDump) {
        $toDump = [];
        $toDump['API'] = $fluent;
        if ($errCount > 0) {
            $toDump['ERRORS'] = $errors;
        }
        if ($warnCount > 0) {
            $toDump['WARNINGS'] = $warnings;
        }
        if ($showAll || $showValid) {
            $toDump['VALID_BATCHES'] = $getProp('validBatches') ?? [];
        }
        if ($showAll || $showInvalid) {
            $toDump['INVALID_BATCHES'] = $getProp('invalidBatches') ?? [];
        }
        if ($showAll || $showCached) {
            $toDump['CACHED'] = $getProp('cached') ?? [];
        }
        if ($showAll || $showCompiled) {
            $toDump['COMPILED'] = $getProp('compiled') ?? [];
        }
        if ($showAll) {
            $toDump['COMPILE_FLAGS'] = $getProp('compileFlags') ?? [];
        }
        if ($errCount > 0) {
            $title = "FunkPHP Configuration Debug ($errCount Error" . ($errCount === 1 ? '' : 's') . ")";
        } else {
            $title = "FunkPHP Configuration Debug";
        }
        dd($toDump, $title, false);
    }
} else {
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
