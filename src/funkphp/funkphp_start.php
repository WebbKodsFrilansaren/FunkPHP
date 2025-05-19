<?php // ENTRY POINT OF EACH HTTP(S) REQUEST thanks to ".htaccess" file

// Load all functions needed for the
// FunkPHP Framework Web Application
include_once __DIR__ . '/_internals/functions/_includeAllExceptCLI.php';

// $c is the global configuration array that
// will be used throughout the application
$c = include_once __DIR__ . '/config/_all.php';


// STEP 1: LOAD & INITIALIZE GLOBAL CONFIGURATION FILES
// This includes: Setting initial INI_SETS, Setting Sesssion Cookies Params,
// Connecting to the Database, Setting Headers and Starting the Session.
// Only Run Step 1 if the current step is 1 (the first step of the request)
if ($c['req']['current_step'] === 1) {
    // Redirect to HTTPS if not already on HTTPS (if needed)
    funk_https_redirect();

    // See src/funkphp/config/ini_sets.php for the default ini_sets() settings!
    funk_run_ini_sets($c['INI_SETS'] ?? []);

    // See src/funkphp/config/cookies.php for the default (session) cookie settings!
    session_set_cookie_params([
        'lifetime' => $c['COOKIES']['SESSION_LIFETIME'],
        'path' => $c['COOKIES']['SESSION_PATH'],
        'domain' => $c['BASEURLS']['BASEURL'],
        'secure' => $c['COOKIES']['SESSION_SECURE'],
        'httponly' => $c['COOKIES']['SESSION_HTTPONLY'],
        'samesite' => $c['COOKIES']['SESSION_SAMESITE'],
    ]);

    // See src/funkphp/config/db.php for the default database settings!
    $c['db'] = funk_connect_db(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)['data'] ?? null;

    // Headers to Set and Remove for each HTTP(S) Request
    funk_headers_set($c['HEADERS']['ADD']);
    funk_headers_remove($c['HEADERS']['REMOVE']);

    // Start the session (if not already started)
    funk_start_session();

    // Prepare the request URI for the FunkPHP Framework - Change
    // the function if you need to filter REQUEST_URI in more ways!
    $c['req']['uri'] = funk_prepare_uri($_SERVER['REQUEST_URI'], $c['BASEURLS']['BASEURL_URI']);

    // This is the end of Step 1, do anything more if you want to
    $c['req']['next_step'] = 2; // Set next step to 2 (Step 2)
}
// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];


// STEP 2: Globally Filter Allowed Methods, IPs and User Agents (UAs)
// Only Run Step 1 if the current step is 1 (the second step of the request)
if ($c['req']['current_step'] === 2) {
    // Match against denied and invalid methods | true = denied, false = allowed
    $FPHP_INVALID_METHOD = funk_match_denied_methods();
    if ($FPHP_INVALID_METHOD) {
        // If desired, response behavior edit as you please!
        echo "I'm a funky teapot!";
        http_response_code(418);
        exit;
    }
    unset($FPHP_INVALID_METHOD);

    // Match against denied and invalid exact IPs | true = denied, false = allowed
    $FPHP_INVALID_IP = funk_match_denied_exact_ips();
    if ($FPHP_INVALID_IP) {
        // If desired, response behavior edit as you please!
        echo "I'm a funky teapot!";
        http_response_code(418);
        exit;
    }
    unset($FPHP_INVALID_IP);

    // Match against denied UAs | true = denied, false = allowed
    $FPHP_INVALID_UA = funk_match_denied_uas_fast();
    if ($FPHP_INVALID_UA) {
        // If desired, edit response behavior as you please!
        echo "I'm a funky teapot!";
        http_response_code(418);
        exit;
    }
    unset($FPHP_INVALID_UA);

    // This is the end of Step 2, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    // Maybe you want to globally block specific Content-Type headers or something else?


    // Set next step to 3 (Step 3)
    $c['req']['next_step'] = 3;
}
// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];


// STEP 3: Match Single Route and its associated Middlewares
// This is the third step of the request, so we can run this step now!
if ($c['req']['current_step'] === 3) {
    // Load URI Routes since we are at this step and need them!
    // GOTO "funkphp/routes/route_single_routes.php" to Add Your Single Routes!
    // GOTO "funkphp/routes/route_middleware_routes.php" to Add Your middlewares!
    $c['ROUTES'] = [
        'COMPILED' => include __DIR__ . '/_internals/compiled/troute_route.php',
        'SINGLES' => include __DIR__ . '/routes/route_single_routes.php',
    ];
    // BEFORE STEP 3: Do anything you want here before matching the route and middlewares!

    // STEP 3: Match Route & Middlewares and then
    // store them in global $c(onfig) variable,
    // then free up memory by unsetting variable
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'],
        $c['ROUTES']['SINGLES']['ROUTES'],
        $c['ROUTES']['SINGLES']['ROUTES'],
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

    // GOTO: "funkphp/middlewares/" and copy&paste the "_TEMPLATE.php" file to create your own middlewares!
    // OR use the FunkCLI "php funkcli add mw middleware_name METHOD/route_path"
    // Check that middlewares array exists and is not empty in $c global variable
    // Then run each middleware in the order they are defined as long as keep_running_mws is true.
    // After each run, remove it from the array to avoid running it again.
    if ($c['req']['matched_middlewares'] !== null) {
        funk_run_middleware_after_matched_routing($c);
    }

    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // One or more middlewares failed to run? What then or just move on?
    // IMPORTANT: This occurs after trying to run the middlewares, so if one of them fails, this will be true.
    // This means one or more middlewares might have run _before_ this error is handled!
    if ($c['err']['FAILED_TO_RUN_MIDDLEWARE']) {
    }

    // Run the matched route handler if it exists and is not empty.
    // Even if not null, file may not exist; the function checks that.
    if ($c['req']['matched_handler'] !== null) {
        funk_run_matched_route_handler($c);
    }
    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // matched_handler doesn't exist? What then or just move on?
    else {
    }
    // matched_handler failed to run? What then or just move on?
    if ($c['err']['FAILED_TO_RUN_ROUTE_HANDLER']) {
    }


    // This is the end of Step 3, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 4; // Set next step to 4 (Step 4)
}
// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];


// STEP 4: Match, fetch, validate data from different sources
// Only run this step if the current step is 4
if ($c['req']['current_step'] === 4) {
    // This is the fourth(4) step of the request, below you can do
    // anything you want before running the matched data handler.

    // Run the matched data handler if it exists
    if ($c['req']['matched_data'] !== null) {
        funk_run_matched_data_handler($c);
    }
    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // matched_data doesn't exist? What then or just move on?
    else {
    }

    // matched_data failed to run? What then or just move on?
    if ($c['err']['FAILED_TO_RUN_DATA_HANDLER']) {
    }

    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 5; // Set next step to 5 (Step 5)

}
$c['req']['current_step'] = $c['req']['next_step'];


// STEP 5: Return a matched page after route and data matching!
// Only run this step if the current step is 5
if ($c['req']['current_step'] === 5) {
    // This is the last step of the request, so we can run this step now!

    // TODO: Add matching page step. Add running middleware step. Add the Template Engine function too!

    // This is the end of Step 5, you can freely add any final code here!
    // You have all global (meta) data in $c variable, so you can use it as you please!

}
// This is the end of the entire request process!

// This part is only executed if the request was not properly handled by the pipeline!
// Feel free to add your own error handling here and/or easter egg!
