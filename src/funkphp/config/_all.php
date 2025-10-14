<?php // FunkPHP Global Configuration File (The `$c` Variable)
// IMPORTANT: This file is used to set the global configuration for FunkPHP
// CHANGE AS NEEDED BELOW SO IT WORKS OFFLINE & ONLINE FOR YOU!
define('FUNKPHP_IS_LOCAL', (isset($_SERVER['SERVER_NAME'])
    && ($_SERVER['SERVER_NAME'] === 'localhost'
        || $_SERVER['SERVER_NAME'] === "127.0.0.1")));
define('FUNKPHP_LOCAL', "http://localhost/funkphp/src/public_html/");
define('FUNKPHP_ONLINE', "https://www.funkphp.com/");
define("ROOT_FOLDER", dirname(__DIR__, 1)); // The root folder of FunkPHP
define("FUNKPHP_NO_VALUE", new stdClass()); // A Singleton Object that indicates "no value"!
// YUP! Unfortunately ONE SINGLE CLASS needed for the sake of SECURITY.
// "FunkDBConfig" is a Class used to handle the database connections
// only that are stored by reference in $c['DATABASES'] array below!
// Inside of `db.php` you can change where your version of "db_config.php"
// resides that is GITIGNORED and contains your sensitive credentials.
class FunkDBConfig
{
    private static $credentials = [];
    private static $initialized = false;
    private static $configFilePath =  __DIR__ . '/db.php';
    public static function setConfigPath(string $path)
    {
        self::$configFilePath = $path;
    }
    public static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        // Lazy load the sensitive credentials file
        if (is_readable(self::$configFilePath)) {
            self::$credentials = include self::$configFilePath;
        }
        // Load the general profiles (assuming they are now loaded elsewhere or hardcoded)
        // For simplicity, we assume the credentials array contains everything needed.
        self::$initialized = true;
    }
    // Get a specific connection
    public static function getCredentials(string $key): ?array
    {
        self::initialize(); // Ensure the file has been loaded
        return self::$credentials[$key] ?? null;
    }
    public static function clearCredentials()
    {
        self::$credentials = [];
    }
}
// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
// IMPORTANT: Do NOT store sensitive data here (e.g passwords/API-keys)
return [
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

    // '<ENTRY>' - This is where `pipeline`, `exit` & and `no_match` keys are stored
    // in the `funkphp/config/pipeline.php` file and used to run the pipeline!
    '<ENTRY>' => [],

    // ROUTES - The `funkphp/config/routes.php` file (this is first populated
    // when `m_match_route` is ran during `pipeline` in `funkphp_start.php`)
    'ROUTES' => [],

    // 'TABLES' is the array of Processed SQL Tables ("schemas" folder) that
    // are used in tandem with Validation & SQL Handlers during DB CRUD!
    'TABLES' => include_once __DIR__ . '/tables.php',


    // 'CLASSES' is the array of Composer Class Configurations (and your own)
    // that are used by the `funk_use_class` function inside the
    // `funkphp/_internals/functions/h_helpers_funs.php` to instantiate
    // and return the object instance by reference in `$c['composer']` array!
    // SYNTAX: ['folder_name_in_vendor' =>'Full\Namespace\ClassName']
    'CLASSES' => include_once __DIR__ . '/classes.php',

    // 'DATABASES' is the array of multiple database connections that you can
    // use and can be SQL, MongoDB, PostgreSQL, etc. - Change as needed!
    // For example: `$c['DATABASES']['mysql_main'] = new mysqli/PDO(...);
    'DATABASES' => [],

    // 'req' is the array of request data which will also include changed data based
    // on matched route, middlewares (if any), data (if any) and page (if any), etc.
    'req' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'uri' => null,
        'matched_in' => null,
        'route' => null,
        'params' => null,
        'segments' => null,
        'route_keys' => [],
        'skip_post-response' => false,
        'matched_middlewares' => null,
        'current_passed_value' => [],
        'current_passed_values' => [],
        'current_pipeline' => null,
        'next_pipeline' => null,
        'deleted_pipeline' => [],
        'deleted_pipeline#' => 0,
        'completed_pipeline#' => 0,
        'current_middleware' => null,
        'next_middleware' => null,
        'deleted_middlewares' => [],
        'deleted_middlewares#' => 0,
        'completed_middlewares#' => 0,
        'last_returned_pipeline_value' => FUNKPHP_NO_VALUE,
        'last_returned_middleware_value' => FUNKPHP_NO_VALUE,
        'last_returned_route_key_value' => FUNKPHP_NO_VALUE,
        'current_exit' => null,
        'next_exit' => null,
        'deleted_exit' => null,
        'completed_exit' => 0,
        'deleted_exit#' => 0,
        'keep_running_pipeline' => null,
        'keep_running_middlewares' => null,
        'keep_running_exit' => null,
        'code' => 418,
        'log' => [],
        'time' => $_SERVER['REQUEST_TIME'] ?? time() ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
        'accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
        'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? null,
        'query' => $_SERVER['QUERY_STRING'] ?? null,
    ],

    // 'dispatchers' is the array of function closures that are the loaded
    // anoynmous function files that then contain namespaces with all the
    // other functions that are called based on folder name, file name
    // and function name in the file! This is used during Route Key Running!
    'dispatchers' => [],

    // 'r' will store route-related data
    'r' => null,

    // 'd' will ALWAYS store hydrated database data!
    // data (it does NOT store validation errors)
    // 'd_temp' is where data is temporarily stored after SQL SELECT Query
    // you have not chosen a specific place inside of $c['d']['subkey']!
    'd' => null,
    'd_temp' => null,

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
        'FUNCTIONS' => [],
        'CLASSES' => [],
        'DATABASES' => [],
        'PIPELINE' => [],
        'CACHED' => [],
        'MIDDLEWARES' => [],
        'PAGE' => [],
        'VALIDATION' => [],
        'SQL' => [],
    ],
];
