<?php // ENTRY POINT OF EACH HTTP(S) REQUEST thanks to ".htaccess" file
// CHECK DEFAULT FOLDERS & FILES OR CRITICAL ERROR BASED 'ACCEPT' TPYE!
if (
    !is_readable(__DIR__ . '/config/_all.php')
    || !is_readable(__DIR__ . '/_internals/functions/_includeAllExceptCLI.php')
    || !is_readable(__DIR__ . '/config/pipeline.php')
) {
    critical_err_json_or_html(500, "The Global Configuration Variable `\$c` could not be loaded and/or all the necessary Function Files!");
}
// Load all functions needed for the FunkPHP Framework Web Application
// $c is the global configuration array that is used throughout the application
include_once __DIR__ . '/_internals/functions/_includeAllExceptCLI.php';
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
        funk_run_middleware_after_handled_request($c);
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
    $c['err']['MAYBE']['CONFIG'][] = "No Configured Route Middlewares (`'<CONFIG>' => 'middlewares_before_route_match'`) to load and run before Route Matching. If you expected Middlewares to run before Route Matching, check the `<CONFIG>` key in the Route `funk/config/routes.php` File!";
}
// The registered shutdown callback function will be executed after pipeline
// has run (unless the script is exited prematurely by the application)!
echo "Hey, the request has been handled successfully!";
exit;
// TODO: Add matching page step. Add running middleware step. Add the Template Engine function too!