<?php // STEP 0: LOAD & INITIALIZE GLOBAL CONFIGURATION FILES
// This includes: Setting initial INI_SETS, Setting Sesssion Cookies Params,
// Connecting to the Database, Setting Headers and Starting the Session

// Only Run Step 0 if the current step is 0 (the first step of the request)
if ($c['req']['current_step'] === 0) {
    // Redirect to HTTPS if not already on HTTPS (if needed)
    r_https_redirect();

    // See src/funkphp/config/ini_sets.php for the default ini_sets() settings!
    h_run_ini_sets($c['INI_SETS'] ?? []);

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
    $c['db'] = d_connect_db(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)['data'] ?? null;

    // Headers to Set and Remove for each HTTP(S) Request
    h_headers_set($c['HEADERS']['ADD']);
    h_headers_remove($c['HEADERS']['REMOVE']);

    // Start the session (if not already started)
    h_start_session();

    // Prepare the request URI for the FunkPHP Framework - Change
    // the function if you need to filter REQUEST_URI in more ways!
    $c['req']['uri'] = r_prepare_uri($_SERVER['REQUEST_URI'], $c['BASEURLS']['BASEURL_URI']);

    // This is the end of Step 0, do anything more if you want to
    $c['req']['next_step'] = 1; // Set next step to 1 (Step 1)
}

// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];
