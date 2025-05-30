<?php // ENTRY POINT OF EACH HTTP(S) REQUEST thanks to ".htaccess" file

// DEFAULT CHECK THAT ALL NEEDED FILES EXIST OR WE THROW DEFAULT JSON ERROR
// OR DEFAULT HTML ERROR PAGE - YOU CAN CONFIGURE THIS RIGHT BELOW HERE!
// - Default JSON Error Response
$DEFAULT_JSON_ERROR = [
    'error' => 'FunkPHP Framework - Internal Error: Important files could not be loaded, so Please Tell the Developer to fix the website or the Web Hosting Service to allow for reading the necessary folders & files! If you are the Developer, please check your Configuration and File permissions where you Develop and/or Host this Website!Thanks in advance! You are Awesome, anyway! ^_^',
    'status' => 500,
];
// - Default HTML Error Response
$DEFAULT_HTML_ERROR = <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FunkPHP Framework - Internal Error</title>
    <style>
        body {
            font-family: Arial, sans-serif; background-color: #f4f4f4;
            color: #333; display: flex; justify-content: center; align-items: center;
            min-height: 100vh; margin: 0;
        }
        .container {
            max-width: 350px;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #e74c3c; }
        p { font-size: 16px;  line-height: 1.5; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .center-text {
            text-align: center;
        }
    </style>
    </head>
    <body>
        <div class="container">
            <h1>FunkPHP Framework - Internal Error</h1>
            <p>Important files could not be loaded, so Please Tell the Developer to fix the website or the Web Hosting Service to allow for reading the necessary folders & files!</p>
            <p>If you are the Developer, please check your Configuration and File permissions where you Develop and/or Host this Website!</p>
            <p class="center-text">Thanks in advance!<br>You are Awesome, anyway! ^_^</p>
        </div>
    </body>
</html>
HTML;


echo $DEFAULT_HTML_ERROR;
exit;

// TODO: Add checks first for all the DIRS and then the FILES
// OR error out based on request 'accept' headers such as:
// ('accept': 'application/json' or we just assume text/html!)



if (!file_exists(__DIR__ . '/config/_all.php')) {
    http_response_code(500);
    die(json_encode(['error' => 'Configuration file not found!']));
}

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
    // Run `funkcli add r route_name METHOD/route_path/:optional_param`
    // to start adding routes to your FunkPHP Web Application!
    $c['ROUTES'] = [];

    // When Routes or Trie Compiled Route File(s) not found/non-readable or empty/missing keys!
    if (!file_exists_is_readable_writable(__DIR__ . '/routes/route_single_routes.php')) {
        $c['err']['FAILED_TO_LOAD_ROUTE_FILES'] = "Routes in File `funkphp/routes/route_single_routes.php` not found or non-readable!";
    } elseif (!file_exists_is_readable_writable(__DIR__ . '/_internals/compiled/troute_route.php')) {
        $c['err']['FAILED_TO_LOAD_TROUTE_FILES'] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` not found or non-readable!";
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
        $c['err']['FAILED_TO_LOAD_TROUTE_FILES'] = "Compiled Routes in File `funkphp/_internals/compiled/troute_route.php` seems empty, please check!";
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
        $c['err']['FAILED_TO_LOAD_ROUTE_FILES'] = "Routes in File `funkphp/routes/route_single_routes.php` seems empty, please check!";
    }

    // Load the Route Configurations or set error if not found!
    if (
        !isset($c['ROUTES']['SINGLES']['<CONFIG>'])
        || !is_array($c['ROUTES']['SINGLES']['<CONFIG>']) || empty($c['ROUTES']['SINGLES']['<CONFIG>'])
    ) {
        $c['err']['FAILED_TO_LOAD_ROUTE_CONFIG'] = "Route Configurations Key (`'<CONFIG>'`) Not Found! Check your `funk/routes/route_single_routes.php` File!";
    } else {
        $c['r_config'] = $c['ROUTES']['SINGLES']['<CONFIG>'];
    }

    // BEFORE STEP 3: Do anything you want here before matching the route and middlewares!
    // Here configured & existing middlewares are loaded and runs before route matching!
    if (
        isset($c['r_config']['middlewares_before_route_match']) &&
        is_array($c['r_config']['middlewares_before_route_match']) &&
        !empty($c['r_config']['middlewares_before_route_match'])
    ) {
        funk_run_middleware_before_matched_routing($c);
    } else {
        $c['err']['MAYBE']['FAILED_TO_LOAD_ROUTE_CONFIG_MIDDLEWARES_MAYBE'] = "No Configured Route Middlewares (`'<CONFIG>' => 'middlewares_before_route_match'`) to load and run before Route Matching. If you expected Middlewares to run before Route Matching, check the `<CONFIG>` key in the Route `funk/routes/route_single_routes.php` File!";
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

    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // One or more middlewares failed to run? What then or just move on?
    // IMPORTANT: This occurs after trying to run the middlewares, so if one of them fails, this will be true.
    // This means one or more middlewares might have run _before_ this error is handled!
    if (
        $c['err']['FAILED_TO_LOAD_ROUTE_MIDDLEWARE'] ||
        $c['err']['FAILED_TO_RUN_SINGLE_ROUTE_MIDDLEWARES'] ||
        $c['err']['FAILED_TO_RUN_ROUTE_MIDDLEWARE']
    ) {
    }

    // Run the matched route handler if it exists and is not empty.
    // Even if not null, file may not exist; the function checks that.
    if ($c['req']['matched_handler'] !== null) {
        // Do not run Data Handler if the Route Handler failed to load or run!
        if (
            $c['err']['FAILED_TO_LOAD_ROUTE_HANDLER_FILE']
            || $c['err']['FAILED_TO_RUN_ROUTE_FUNCTION']
        ) {
            $c['err']['FAILED_TO_RUN_ROUTE_FUNCTION'] = "Route Handler Failed to Load or Run.";
        } else {
            funk_run_matched_route_handler($c);
        }
    }
    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // matched_handler doesn't exist? What then or just move on?
    else {
        $c['err']['FAILED_TO_MATCH_ROUTE_MAYBE'] = "No Route Handler Matched. If you expected a Route to match, check your Routes file and ensure the Route exists and that a Handler File with a Handler Function has been added to it under the key `handler`. For example: `['handler' => 'handler_file' => 'handler_function']`.";
    }
    // Matched_handler failed to run? What then or just move on?
    if ($c['err']['FAILED_TO_RUN_ROUTE_FUNCTION']) {
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
        // Do not run Data Handler if the Route Handler failed to load or run!
        if (
            $c['err']['FAILED_TO_LOAD_ROUTE_HANDLER_FILE']
            || $c['err']['FAILED_TO_RUN_ROUTE_FUNCTION']
        ) {
            $c['err']['FAILED_TO_RUN_DATA_FUNCTION'] = "Route Handler Failed to Load or Run, so Data Handler will not be run.";
        } else {
            funk_run_matched_data_handler($c);
        }
    }
    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // matched_data doesn't exist? What then or just move on?
    else {
    }

    // matched_data failed to run? What then or just move on?
    if ($c['err']['FAILED_TO_RUN_DATA_FUNCTION']) {
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
//http_response_code(500);

var_dump($c['err']);
