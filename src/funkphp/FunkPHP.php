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
    $compileAndRun->invoke($cInstance, true);
}
// When it is NOT FunkPHP Object as defined in the `/src/funkphp/core/functions.php`
// dd() automatically exits here
else {
    dd([
        'Internal FunkPHP Error' => 'Expected to find `FunkPHP Class` in `/src/funkphp/config/app.php` but instead found Data Type: `' . (is_object($FUNKPHP) ? get_class($FUNKPHP) : gettype($FUNKPHP)) . '`',
        'Step-by-Step Fix' => 'The return Value in `/src/funkphp/config/app.php` must be the Object Instance of `FunkPHP` (defined in `/src/funkphp/core/functions.php`) that is returned at the end of the File. `DO NOT` modify the `return $FUNK; statement` (unless: you just `return the entire Object in one long method-chaining`) at the end of the `app.php` File.'
    ], 'See Internal FunkPHP Error');
}
