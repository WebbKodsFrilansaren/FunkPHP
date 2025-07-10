<?php
// FunkPHP Global Configuration File
// IMPORTANT: This file is used to set the global configuration for FunkPHP

// CHANGE AS NEEDED BELOW SO IT WORKS OFFLINE & ONLINE FOR YOU!
define('FUNKPHP_IS_LOCAL', (isset($_SERVER['SERVER_NAME'])
    && ($_SERVER['SERVER_NAME'] === 'localhost'
        || $_SERVER['SERVER_NAME'] === "127.0.0.1")));
define('FUNKPHP_LOCAL', "http://localhost/funkphp/src/public_html/");
define('FUNKPHP_ONLINE', "https://www.funkphp.com/");

// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
return [
    'INI_SETS' => [
        // IMPORTANT: Change and/or add these as needed! For example, if you wanna use
        // Redis or Memcached, you can add those configurations here as this INI_SET
        // array is used in "funkphp_start.php" to set the starting PHP INI settings!
        'session.cache_limiter' => 'public',
        'session.use_strict_mode' => 1,
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
    'COOKIES' => [
        'SESSION_NAME' => 'fphp_id',
        'SESSION_LIFETIME' => 28800, // 28800 = 8 hours
        'SESSION_PATH' => '/',
        // Maybe hardcode these key values for best security & performance?
        'SESSION_DOMAIN' => FUNKPHP_IS_LOCAL ? "localhost" : $_SERVER['SERVER_NAME'],
        'SESSION_SECURE' => FUNKPHP_IS_LOCAL ? false : true,
        'SESSION_HTTPONLY' => true,
        'SESSION_SAMESITE' => FUNKPHP_IS_LOCAL ? 'Lax' : 'Strict',
    ],

    // DEFAULT HEADERS That are Added & Removed For Each Request! - Change as needed!
    'HEADERS' => [
        // You might change these as needed per Matched Route in your Web App!
        // The "ADD" & "REMOVE" headers below are for Maximum User Security!
        // IMOPRTANT: Change the CSP Header below to match your needs!
        'ADD' => [
            "Content-Type: text/html; charset=utf-8", // IMPORTANT: Change to "application/json" if you are primarily handling API!
            "Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';",
            "x-frame-options: DENY",
            "x-content-type-options: nosniff",
            "x-xss-protection: 1; mode=block",
            "x-permitted-cross-domain-policies: none",
            "referrer-policy: strict-origin-when-cross-origin",
            "Access-Control-Allow-Origin: 'self'",
            "cross-origin-resource-policy: same-origin",
            "Cross-Origin-Embedder-Policy: require-corp",
            "Cross-Origin-Opener-Policy: same-origin",
            "Expect-CT: enforce, max-age=86400",
            "Strict-Transport-Security: max-age=31536000; includeSubDomains; preload"
        ],
        'REMOVE' => [
            "X-Powered-By",
            "Server",
            "X-AspNet-Version",
            "X-AspNetMvc-Version"
        ]
    ],

    // DEFAULT INTERNAL PATHS for CSS, JS, Files, Fonts, Images, Temp, Videos, etc.
    // (meaning those that are NOT in the `public_html` folder) - Change as needed!
    'PATHS' => [
        "css" => ["css", "styles"],
        "js" => ["js", "javascript"],
        "files" => ["files", "fls"],
        "fonts" => ["fonts", "fnt"],
        "images" => ["images", "img"],
        "temp" => ["temp", "tmp"],
        "videos" => ["videos", "vids"],
    ],

    // ROUTES - The `funkphp/routes/route_single_routes.php` file (this is
    // first populuated during STEP 3: Match Routing in `funkphp_start.php`)
    'ROUTES' => [],

    // 'TABLES' is the array of Processed SQL Tables ("schemas" folder) that
    // are used in tandem with Validation & SQL Handlers during DB CRUD!
    'TABLES' => include __DIR__ . '/tables.php',

    // 'req' is the array of request data which will also include changed data based
    // on matched route, middlewares (if any), data (if any) and page (if any), etc.
    'req' => [
        'current_step' => 1,
        'next_step' => 1,
        'no_match_in' => null,
        'matched_method' => null,
        'matched_handler' => null,
        'matched_route' => null,
        'matched_params' => null,
        'matched_middlewares' => null,
        'matched_data' => null,
        'matched_page' => null,
        'deleted_middlewares' => null,
        'keep_running_middlewares' => null,
        'current_middleware_running' => null,
        'next_middleware_to_run' => null,
        'matched_auth' => null,
        'matched_csrf' => null,
        'number_of_ran_middlewares' => 0,
        'number_of_deleted_middlewares' => 0,
        'cache_page_response' => null,
        'cache_json_response' => null,
        'code' => 418,
        'time' => $_SERVER['REQUEST_TIME'] ?? time() ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
        'accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
        'uri' => null,
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? null,
        'query' => $_SERVER['QUERY_STRING'] ?? null,
    ],

    // 'db' is the database object that will be used to handle the database connection & queries!
    // 'db_lid' will always contain the last inserted ID from the last database query!
    'db' => include __DIR__ . '/db.php',
    'db_lid' => null,

    // 'v' should be NULL but stores ANY founds errors during the validation process while
    // 'v_ok' will is true if not a single v['key']['optionalSubkey'] is set with error(s)!
    // 'v_handlers' contains a unique array of validation handlers that are closures to functios
    // where the Validation Arrays are stored (the funkphp/validations/ folder with its files!)
    // The 'v_ok_files' is boolean for validating files and works the same way as 'v_ok'!
    // 'v_config' is a global array of validation configurations that can be accessed
    // when validating no matter how nested or not the validation is! It stores "password"
    // to for "password_confirm" to check against the "password" field, etc.
    // 'v_data' contains the validate data for a given validation process and default
    // for the "funk_use_validation" function is to ONLY populate it if EVERYTHING
    // is valid. Set this to "false" if you want to for example repopulate incomplete
    // form data instead of Users having to re-enter everything because of a single error!
    'v' => null,
    'v_handlers' => null,
    'v_ok' => null,
    'v_ok_files' => null,
    'v_config' => [],
    'v_data' => null,

    // 'r' will store route-related data
    // 'r_config' is the array of route configurations ('<CONFIG'>)
    // that will be used to handle the route configurations!
    'r' => null,
    'r_config' => null,

    // 'd' will ALWAYS store fetched database
    // data (it does NOT store validation errors)
    'd' => null,

    // 's_handlers' is the array of SQL Handlers that are closures to functions
    's_handlers' => null, // SQL Handlers (the funkphp/sql/ folder with its files)

    // 'p' is the page object that will be used to handle the
    // page rendering and output (not needed for API requests)!
    // 'p_config' is the array of configurations to be applied
    // to your matched page to render (or cached to return)!
    'p' => null,
    'p_config' => null,

    // 'files' is the array of uploaded files (if any)
    // that will be used to handle the file uploads!
    'files' => null,

    // 'current' is collection of running File Handlers and/or their Handler Functions!
    // This is used to keep track of them and also to include correct one during errors!
    // Each key is the  "FileHandlerName" => "HandlerFunction" Pair!
    'current' => ['MIDDLEWARES' => [], 'HANDLERS' => [], 'DATA' => [], 'VALIDATIONS' => [], 'SQL' => [], 'PAGES' => []],

    // 'err(ors)' is an array of errors that will be filled when errors occur in the
    // application, so they can optionally be handled later in the application flow!
    // "MAYBE" errors are always populated when some arrays are just empty in order
    // to indicate that you might have missed populating them in your code! They are
    // NEVER considered as errors, but rather like hints on what you might have missed!
    'err' => [
        'MAYBE' => [],
        'CONFIG' => [],
        'CACHED' => [],
        'MIDDLEWARES' => [],
        'ROUTES' => [],
        'HANDLERS' => [],
        'DATA' => [],
        'VALIDATIONS' => [],
        'SQL' => [],
        'PAGES' => [],
    ],

];
