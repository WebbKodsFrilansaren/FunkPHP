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
$c['ROUTES'] = [];
// When Routes or Trie Compiled Route File(s) not found/non-readable or empty/missing keys!
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
// TODO: Add matching page step. Add running middleware step. Add the Template Engine function too!
// This is the end of Step 5, you can freely add any final code here!
// You have all global (meta) data in $c variable, so you can use it as you please!
// AFTER STEP 3: Do anything you want here after returning a page unless JSON was
// returned insterad. Here the final middlewares are run after everything else is done!
// if (
//     isset($c['r_config']['middlewares_after_handled_request']) &&
//     is_array($c['r_config']['middlewares_after_handled_request']) &&
//     !empty($c['r_config']['middlewares_after_handled_request'])
// ) {
//     funk_run_middleware_after_handled_request($c);
// } else {
//     $c['err']['MAYBE']['PAGES'][] = "No Configured After Request Middlewares (`'<CONFIG>' => 'middlewares_after_handled_request'`) to load and run after handled request. If you expected Middlewares to run After Handled Request, check the `<CONFIG>` key in the Route `funk/routes/route_single_routes.php` File!";
// }
