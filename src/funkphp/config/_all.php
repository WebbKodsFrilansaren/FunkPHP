<?php
// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
define('FUNKPHP_IS_LOCAL', (isset($_SERVER['SERVER_NAME'])
    && ($_SERVER['SERVER_NAME'] === 'localhost'
        || $_SERVER['SERVER_NAME'] === "127.0.0.1")));
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
        'LOCAL' => 'http://localhost/funkphp/src/public_html/',
        'ONLINE' => 'https://www.funkphp.com/',
        'BASEURL' =>  FUNKPHP_IS_LOCAL ? 'localhost' : 'https://www.funkphp.com',
        // This changes to "/" in localhost to match online experience
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
        'ADD' => [
            "Content-Type: text/html; charset=utf-8", // IMPORTANT: Change to "application/json" for API requests!
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
    // (meaning those that are NOT in the public_html folder) - Change as needed!
    'PATHS' => [
        "css" => ["css", "styles"],
        "js" => ["js", "javascript"],
        "files" => ["files", "fls"],
        "fonts" => ["fonts", "fnt"],
        "images" => ["images", "img"],
        "temp" => ["temp", "tmp"],
        "videos" => ["videos", "vids"],
    ],
    // Route matching Loads first:"STEP 3: Match Single Route and its associated Middlewares"
    // in "funkphp_start.php" file! Change their Loading Logic there if needed!
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
    'db' => include __DIR__ . '/db.php',

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

    // 'r' will store route-related data
    // 'r_config' is the array of route configurations ('<CONFIG'>)
    // that will be used to handle the route configurations!
    'r' => null,
    'r_config' => null,

    // 'd' will ALWAYS store fetched database
    // data (it does NOT store validation errors)
    'd' => null,

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
    'err' => [
        'MAYBE' => [],
        'FAILED_TO_LOAD_DB' => false,
        'FAILED_TO_START_SESSION' => false,
        'FAILED_TO_LOAD_TROUTE_FILES' => false,
        'FAILED_TO_LOAD_ROUTE_FILES' => false,
        'FAILED_TO_LOAD_ROUTE_CONFIG' => false,
        'FAILED_TO_LOAD_ROUTE_CONFIG_MIDDLEWARES' => false,
        'FAILED_TO_RUN_SINGLE_ROUTE_CONFIG_MIDDLEWARES' => false,
        'FAILED_TO_RUN_ROUTE_CONFIG_MIDDLEWARES' => false,
        'FAILED_TO_MATCH_ROUTE' => false,
        'FAILED_TO_LOAD_ROUTE_HANDLER_FILE' => false,
        'FAILED_TO_RUN_ROUTE_FUNCTION' => false,
        'FAILED_TO_LOAD_ROUTE_MIDDLEWARE' => false,
        'FAILED_TO_RUN_SINGLE_ROUTE_MIDDLEWARES' => false,
        'FAILED_TO_RUN_ROUTE_MIDDLEWARE' => false,
        'FAILED_TO_LOAD_DATA_HANDLER_FILE' => false,
        'FAILED_TO_RUN_DATA_FUNCTION' => false,
        'FAILED_TO_LOAD_VALIDATION_FILE' => false,
        'FAILED_TO_LOAD_VALIDATION_FILES' => false,
        'FAILED_TO_RUN_VALIDATION_FUNCTION' => false,
        'FAILED_TO_LOAD_SQL_FILE' => false,
        'FAILED_TO_LOAD_SQL_FILES' => false,
        'FAILED_TO_RUN_SQL_FUNCTION' => false,
        'FAILED_TO_RUN_PAGE_HANDLER' => false,
        'FAILED_TO_RENDER_PAGE_FILE' => false,
        'FAILED_TO_LOAD_PAGE_COMPONENTS' => false,
        'FAILED_TO_LOAD_PAGE_PARTS' => false,
        'FAILED_TO_RUN_JSON' => false,
        'FAILED_TO_RUN_API' => false,
        'FAILED_TO_RUN_DB' => false,
        'FAILED_TO_RUN_CACHE' => false,
        'FAILED_TO_RUN_SESSION' => false,
        'FAILED_TO_RUN_COOKIE' => false,
        'FAILED_TO_RUN_HEADER' => false,
    ],

];
