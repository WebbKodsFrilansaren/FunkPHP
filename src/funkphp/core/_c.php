<?php // FunkPHP Global Configuration File (The `$c` Variable)

/**
 * ---------------------
 * FUNKPHP (C)onfig File
 * ---------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU UNDERSTAND IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
require_once __DIR__ . '/CONSTANTS.php';

// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
// IMPORTANT: Do NOT store sensitive data here (e.g passwords/API-keys)
return [
    !defined('FUNKPHP_IS_LOCAL') ? define('FUNKPHP_IS_LOCAL', true) : null,
    !defined('FUNKPHP_LOCAL') ? define('FUNKPHP_LOCAL', 'http://localhost/funkphp/src/public_html/') : null,
    !defined('FUNKPHP_ONLINE') ? define('FUNKPHP_ONLINE', 'https://www.funkphp.com/') : null,
    'INI_SETS' => [
        // IMPORTANT: Change and/or add these as needed! For example, if you wanna use
        // Redis or Memcached, you can add those configurations here as this INI_SET
        // array is used in "funkphp_start.php" to set the starting PHP INI settings!
        'session.cache_limiter' => 'public',
        'session.use_strict_mode' => 8,
        'session.use_only_cookies' => 1,
        'session.cache_expire' => 30,
        'session.cookie_lifetime' => 0, // 0 = until browser is closed
        'session.name' => 'fphp_id',
        'session.sid_length' => 192,
        'session.sid_bits_per_character' => 6,
        // IMPORTANT: Remove these configs for PRODUCTION to improve performance?
        'display_errors' => FUNKPHP_IS_LOCAL ? 1 : 0,
        'display_startup_errors' =>  FUNKPHP_IS_LOCAL ? 1 : 0,
        'error_reporting' =>  FUNKPHP_IS_LOCAL ? E_ALL : 0,
    ],

    // IMPORTANT: Change to your hardcoded online URL!
    'BASEURLS' => [
        'LOCAL' => FUNKPHP_LOCAL,
        'ONLINE' => FUNKPHP_ONLINE,
        'BASEURL' =>  FUNKPHP_IS_LOCAL ? 'localhost' :  FUNKPHP_ONLINE,
        // This changes base to "/" during localhost
        // development to match online experience!
        'BASEURL_URI' => '/funkphp/src/public_html/',
    ],

    // DEFAULT SESSION COOKIES SETTINGS - Change as needed!
    // IMPORTANT: They are used by `pl_set_session_cookie_params`!
    'SESSION' => [
        'driver' => 'files',
        'COOKIES' => [
            'SESSION_NAME' => 'fphp_id',
            'SESSION_LIFETIME' => 28800, // 28800 = 8 hours
            'SESSION_PATH' => '/',
            // Maybe hardcode these key values for best security & performance?
            'SESSION_DOMAIN' => FUNKPHP_IS_LOCAL ? "localhost" : $_SERVER['SERVER_NAME'],
            'SESSION_SECURE' => FUNKPHP_IS_LOCAL ? false : true,
            'SESSION_HTTPONLY' => true,
            'SESSION_SAMESITE' => FUNKPHP_IS_LOCAL ? 'Lax' : 'Strict',
        ]
    ],

    // '<ENTRY>' - This is where `pipeline`, `exit` & and `no_match` keys are stored
    // in the `funkphp/config/pipeline.php` file and used to run the pipeline!
    '<ENTRY>' => [],

    // ROUTES - The `funkphp/config/routes.php` file (this is first populated
    // when `m_match_route` is ran during `pipeline` in `funkphp_start.php`)
    'ROUTES' => [],

    // 'shared' is data that you wanna be able to access across pipeline functions
    // since you CANNOT pass any other values between them except the global $c!
    'shared' => [],

    // 'classes' is the array of instantiated objects of any classes
    // from either "vendor" (composer) or "user" (your own classes)
    // folder that you want to be globally available via `$c`!
    // IMPORTANT: It ALWAYS run the Constructor even if an instance with
    // the same key already exists! use the "define()" below whether you
    // want funk_use_custom_error() to run then or if you are OK with overwrite
    'classes' => ['vendor' => [], 'user' => []],

    // 'connections' is the array of multiple (database|memory|file) connections that you can
    // use and can be SQL, MongoDB, PostgreSQL, etc. - Change as needed!
    // For example: `$c['connection']['mysql_main'] = new mysqli/PDO(...);
    'connections' => [],

    // 'req' is the array of request data which will also include changed data based
    // on matched route, middlewares (if any), data (if any) and page (if any), etc.
    'req' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
        'time'   => $_SERVER['REQUEST_TIME'] ?? time(),
        'uri' => null,
        'query' => $_SERVER['QUERY_STRING'] ?? null,
        'matched_in' => null,
        'route' => null,
        'params' => null,
        'segments' => null,
        'auth' => null,
        'matched_config' => null,
        'matched_pipeline' => [],
        'matched_middlewares' => null,
        'skip_post_response' => false,
        'current_pipeline' => null,
        'next_pipeline' => null,
        'current_middleware' => null,
        'next_middleware' => null,
        'keep_running_pipeline' => null,
        'keep_running_middlewares' => null,
        'keep_running_exit' => null,
        'code' => 418,
        'log' => [],
        'ua' => null,
        'content_type' => null,
        'accept' => null,
        'protocol' => null,
    ],
    // 'd' will ALWAYS store hydrated database data!
    // data (it does NOT store validation errors)
    'd' => null,

    // 'v' should be NULL but stores ANY founds errors during the validation process while
    // 'v_ok' will is true if not a single v['key']['optionalSubkey'] is set with error(s)!
    // The 'v_ok_files' is boolean for validating files and works the same way as 'v_ok'!
    // 'v_config' is a global array of validation configurations that can be accessed
    // when validating no matter how nested or not the validation is! It stores "password"
    // to for "password_confirm" to check against the "password" field, etc.
    // 'v_data' contains the validate data for a given validation process and default
    // for the "funk_use_validation" function is to ONLY populate it if EVERYTHING
    // is valid. Set this to "false" if you want to for example repopulate incomplete
    // form data instead of Users having to re-enter everything because of a single error!
    'v' => null,
    'v_ok' => null,
    'v_ok_files' => null,
    'v_config' => [],
    'v_data' => null,

    // 's_data' contains fetched SQL Data for a given SQL Query (and is BEFORE
    // any hydration is done) so you can use it to hydrate the data later!
    's_data' => null,

    // 'p' is the page object that will be used to handle the
    // page rendering and output (not needed for API requests)!
    // 'p_config' is the array of configurations to be applied
    // to your matched page to render (or cached to return)!
    'p' => null,
    'p_config' => null,

    // 'files' is the array of uploaded files (if any)
    // that will be used to handle the file uploads!
    'files' => null,

    // 'err(ors)' is an array of errors that will be filled when errors occur in the
    // application, so they can optionally be handled later in the application flow!
    // "MAYBE" errors are always populated when some arrays are just empty in order
    // to indicate that you might have missed populating them in your code! They are
    // NEVER considered as errors, but rather like hints on what you might have missed!
    'err' => [
        'MAYBE' => [],
        'FUNCTIONS' => [],
        'CLASSES' => [],
        'DATABASES' => [],
        'PIPELINE' => [],
        'MIDDLEWARES' => [],
        'PAGE' => [],
        'VALIDATION' => [],
        'QUERY' => [],
        'SQL' => [],
    ],
];
