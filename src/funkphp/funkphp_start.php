<?php // ENTRY POINT OF EACH HTTP(S) REQUEST thanks to ".htaccess" file
// CHECK DEFAULT FOLDERS & FILES OR CRITICAL ERROR BASED 'ACCEPT' TPYE!
if (
    !is_readable(__DIR__ . '/config/_all.php')
    || !is_readable(__DIR__ . '/_internals/functions/_all.php')
    || !is_readable(__DIR__ . '/config/pipeline.php')
) {
    critical_err_json_or_html(500, "The Global Configuration Variable `\$c` could not be loaded and/or all the necessary Function Files!");
}
// Load all functions needed for the FunkPHP Framework Web Application
// $c is the global configuration array that is used throughout the application
include_once __DIR__ . '/_internals/functions/_all.php';
$c = include_once __DIR__ . '/config/_all.php';
$c['<ENTRY>'] = include_once __DIR__ . '/config/pipeline.php';

// Prepare what to run after each request is handled
// and/or exit() is used prematurely by the application
register_shutdown_function(function () use (&$c) {
    if (
        isset($c['<ENTRY>']['exit'])
        && is_array($c['<ENTRY>']['exit'])
        && !empty($c['<ENTRY>']['exit'])
    ) {
        funk_run_exit($c);
    }
});
// MAIN STEP: Run the Pipeline of Anonymous Functions that control the flow of the request!
if (
    isset($c['<ENTRY>']['pipeline']) &&
    is_array($c['<ENTRY>']['pipeline']) &&
    !empty($c['<ENTRY>']['pipeline'])
) {
    funk_run_pipeline($c);
} else {
    $c['err']['MAYBE']['CONFIG'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!';
}
// The registered shutdown callback function will be executed after pipeline
// has run (unless the script is exited prematurely by the application)!
