<?php // ENTRY POINT OF EACH HTTP(S) REQUEST thanks to ".htaccess" file
// CHECK DEFAULT FOLDERS & FILES OR CRITICAL ERROR BASED 'ACCEPT' TPYE!
if (
    !is_readable(__DIR__ . '/config/_all.php')
    || !is_readable(__DIR__ . '/_internals/functions/_includeAllExceptCLI.php')
) {
    critical_err_json_or_html(500);
}
// Load all functions needed for the
// FunkPHP Framework Web Application
include_once __DIR__ . '/_internals/functions/_includeAllExceptCLI.php';
// $c is the global configuration array that
// will be used throughout the application
$c = include_once __DIR__ . '/config/_all.php';
// Prepare what to run after each request is handled
// and/or exit() is used prematurely by the application
register_shutdown_function(function () use (&$c) {
    if (
        isset($c['r_config']['exit'])
        && is_array($c['r_config']['exit'])
        && !empty($c['r_config']['exit'])
    ) {
        funk_run_middleware_after_handled_request($c);
    }
});
// When Routes or Trie Compiled Route File(s) not found/non-readable or empty/missing keys!
$c['ROUTES'] = [];
if (!file_exists_is_readable_writable(__DIR__ . '/routes/route_single_routes.php')) {
    $c['err']['ROUTES'][] = "Routes in File `funkphp/routes/route_single_routes.php` not found or non-readable!";
    critical_err_json_or_html(500);
} elseif (!file_exists_is_readable_writable(__DIR__ . '/_internals/compiled/troute_route.php')) {
    $c['err']['ROUTES'][] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` not found or non-readable!";
    critical_err_json_or_html(500);
} else {
    $c['ROUTES'] = [
        'COMPILED' => include __DIR__ . '/_internals/compiled/troute_route.php',
        'SINGLES' => include __DIR__ . '/routes/route_single_routes.php',
    ];
}
if (
    empty($c['ROUTES'])
    || !isset($c['ROUTES']['COMPILED'])
    || empty($c['ROUTES']['COMPILED'])
) {
    $c['err']['ROUTES'][] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` seems empty, please check!";
}
if (
    empty($c['ROUTES'])
    || !isset($c['ROUTES']['SINGLES'])
    || !is_array($c['ROUTES']['SINGLES'])
    || empty($c['ROUTES']['SINGLES'])
    || !isset($c['ROUTES']['SINGLES']['ROUTES'])
    || !is_array($c['ROUTES']['SINGLES']['ROUTES'])
    || empty($c['ROUTES']['SINGLES']['ROUTES'])
) {
    $c['err']['ROUTES'][] = "Routes in File `funkphp/routes/route_single_routes.php` seems empty, please check!";
}
// Load the Route Configurations or set error if not found!
if (
    !isset($c['ROUTES']['SINGLES']['<CONFIG>'])
    || !is_array($c['ROUTES']['SINGLES']['<CONFIG>']) || empty($c['ROUTES']['SINGLES']['<CONFIG>'])
) {
    $c['err']['CONFIG'][] = "Route Configurations Key (`'<CONFIG>'`) Not Found! Check your `funk/routes/route_single_routes.php` File!";
} else {
    $c['r_config'] = $c['ROUTES']['SINGLES']['<CONFIG>'];
}
// MAIN STEP: Run the Pipeline of Anonymous Functions that control the flow of the request!
if (
    isset($c['r_config']['pipeline']) &&
    is_array($c['r_config']['pipeline']) &&
    !empty($c['r_config']['pipeline'])
) {
    funk_run_pipeline($c);
} else {
    $c['err']['MAYBE']['CONFIG'][] = "No Configured Route Middlewares (`'<CONFIG>' => 'middlewares_before_route_match'`) to load and run before Route Matching. If you expected Middlewares to run before Route Matching, check the `<CONFIG>` key in the Route `funk/routes/route_single_routes.php` File!";
}
// The registered shutdown callback function will be executed after pipeline
// has run (unless the script is exited prematurely by the application)!
exit;
// TODO: Add matching page step. Add running middleware step. Add the Template Engine function too!