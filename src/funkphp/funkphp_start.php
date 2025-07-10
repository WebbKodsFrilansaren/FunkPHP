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

// STEP 1: Run Middlewares BEFORE Matching Single Route, then Match Single Route
// and then Run its associated Middlewares before its Handler(s) run!
// This is the first step of the request, so we can run this step now!
if ($c['req']['current_step'] === 1) {
    // Load URI Routes since we are at this step and need them!
    // Run `funkcli add r route_name METHOD/route_path/:optional_param`
    // to start adding routes to your FunkPHP Web Application!
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

    // BEFORE STEP 1: Do anything you want here before matching the route and middlewares!
    // Here configured & existing middlewares are loaded and runs before route matching!
    if (
        isset($c['r_config']['middlewares_before_route_match']) &&
        is_array($c['r_config']['middlewares_before_route_match']) &&
        !empty($c['r_config']['middlewares_before_route_match'])
    ) {
        funk_run_middleware_before_matched_routing($c);
    } else {
        $c['err']['MAYBE']['CONFIG'][] = "No Configured Route Middlewares (`'<CONFIG>' => 'middlewares_before_route_match'`) to load and run before Route Matching. If you expected Middlewares to run before Route Matching, check the `<CONFIG>` key in the Route `funk/routes/route_single_routes.php` File!";
    }

    // STEP 3: Match Route & Middlewares and then
    // store them in global $c(onfig) variable,
    // then free up memory by unsetting variable
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'] ?? [],
        $c['ROUTES']['SINGLES']['ROUTES'] ?? [],
        $c['ROUTES']['SINGLES']['ROUTES'] ?? [],
    );
    $c['req']['matched_method'] = $c['req']['method'];
    $c['req']['matched_route'] = $FPHP_MATCHED_ROUTE['route'];
    $c['req']['matched_handler'] = $FPHP_MATCHED_ROUTE['handler'];
    $c['req']['matched_data'] = $FPHP_MATCHED_ROUTE['data'];
    $c['req']['matched_page'] = $FPHP_MATCHED_ROUTE['page'];
    $c['req']['matched_params'] = $FPHP_MATCHED_ROUTE['params'];
    $c['req']['matched_middlewares'] = $FPHP_MATCHED_ROUTE['middlewares'];
    $c['req']['no_matched_in'] = $FPHP_MATCHED_ROUTE['no_match_in'];
    unset($FPHP_MATCHED_ROUTE);

    // NOW WE RUN THE MIDDLEWARES BEFORE THE MATCHED ROUTE HANDLER!
    // Run `php funkcli add mw middleware_name METHOD/route_path/:optional_param`
    // Check that middlewares array exists and is not empty in $c global variable
    // Then run each middleware in the order they are defined as long as keep_running_mws is true.
    // After each run, remove it from the array to avoid running it again.
    if ($c['req']['matched_middlewares'] !== null) {
        funk_run_middleware_after_matched_routing($c);
    }

    // Run the matched route handler if it exists and is not empty.
    // Even if not null, file may not exist; the function checks that.
    if ($c['req']['matched_handler'] !== null) {
        funk_run_matched_route_handler($c);
    } else {
        $c['err']['ROUTES'][] = "No Route Handler Matched or it Failed to Run!";
        $c['err']['MAYBE']['ROUTES'][] = "No Route Handler Matched. If you expected a Route to match, check your Routes file and ensure the Route exists and that a Handler File with a Handler Function has been added to it under the key `handler`. For example: `['handler' => 'r_handler_file' => 'r_handler_function']`.";
    }

    // This is the end of Step 1, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 2;
}
$c['req']['current_step'] = $c['req']['next_step'];

// STEP 2: Match, fetch, validate data from different sources
// Only run this step if the current step is 2
if ($c['req']['current_step'] === 2) {
    // This is the second step of the request, below you can do
    // anything you want before running the matched data handler.

    // Run the matched data handler if it exists
    if ($c['req']['matched_data'] === null) {
        $c['err']['DATA'][] = "Route Handler Failed to Load or Run, so Data Handler will not be run.";
        $c['err']['MAYBE']['DATA'][] = "No Data Handler Matched. If you expected a Data Handler to match, check your Routes file and ensure the Route exists and that a Data Handler File with a Data Handler Function has been added to it under the key `data`. For example: `['data' => 'd_data_file' => 'd_data_function']`.";
    } else {
        funk_run_matched_data_handler($c);
    }

    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 3; // Set next step to 3 (Step 3)

}
$c['req']['current_step'] = $c['req']['next_step'];

// STEP 3: Return a matched page after route and data matching!
// Only run this step if the current step is 3
if ($c['req']['current_step'] === 3) {
    // This is the last step of the request, so we can run this step now!

    // TODO: Add matching page step. Add running middleware step. Add the Template Engine function too!

    // This is the end of Step 5, you can freely add any final code here!
    // You have all global (meta) data in $c variable, so you can use it as you please!

    // AFTER STEP 3: Do anything you want here after returning a page unless JSON was
    // returned insterad. Here the final middlewares are run after everything else is done!
    if (
        isset($c['r_config']['middlewares_after_handled_request']) &&
        is_array($c['r_config']['middlewares_after_handled_request']) &&
        !empty($c['r_config']['middlewares_after_handled_request'])
    ) {
        funk_run_middleware_after_handled_request($c);
    } else {
        $c['err']['MAYBE']['PAGES'][] = "No Configured After Request Middlewares (`'<CONFIG>' => 'middlewares_after_handled_request'`) to load and run after handled request. If you expected Middlewares to run After Handled Request, check the `<CONFIG>` key in the Route `funk/routes/route_single_routes.php` File!";
    }
}
// This is the end of the entire request process!

ddj($c['err']); // Debugging: Dump and Die the $c variable to see the entire request data!

// This part is only executed if the request was not properly handled by the pipeline!
// Feel free to add your own error handling here and/or easter egg!
