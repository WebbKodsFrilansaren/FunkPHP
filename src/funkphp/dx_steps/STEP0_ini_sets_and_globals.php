<?php // IMPORTANT: All steps in 0 are meant to be "set and forget" in the sense that they are not meant to be changed after the initial setup of the application.
// Step 0.0 is setting all the ini_set() that are needed for the application to run properly.
// Add your own ini_set() here if needed and/or change the default ones.

//INI_SET_START_DELIMTIER//
ini_set('session.cache_limiter', 'public'); // Prevents caching of the session pages
session_cache_limiter(false); // Prevents caching of the session pages
ini_set('session.use_only_cookies', 1); // Only use cookies for session management
ini_set('session.use_strict_mode', 1); // Prevents session fixation attacks
ini_set('session.cache_expire', 30); // Session cache expiration time in minutes
ini_set('session.cookie_lifetime', 0); // 0 = until browser is closed
ini_set('session.name', 'fphp_id'); // We overwrite other cookie named "id" with this one
ini_set('session.sid_length', 192); // Length of the session ID
ini_set('session.sid_bits_per_character', 6); // Bits per character to increase entropy
//INI_SET_END_DELIMTIER//

// ALWAYS REMOVE THE FOLLOWING LINES IN PRODUCTION!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ALWAYS REMOVE THE LINES ABOVE IN PRODUCTION!

//BASEURL_START_DELIMTIER//
$fphp_BASEURL_LOCAL = "http://localhost:8080/funkphp/src/public_html/";
$fphp_BASEURL_ONLINE = "https://"; // Change to your hardcoded online URL!
$fphp_BASEURL = $_SERVER['SERVER_NAME'] == "localhost"
    || $_SERVER['SERVER_NAME'] == "127.0.0.1" ? $fphp_BASEURL_LOCAL : $fphp_BASEURL_ONLINE;
$fphp_BASEURL_URI = "/funkphp/src/public_html/"; // This changes to "/" in localhost so the experience is the same as online
//BASEURL_END_DELIMTIER//

//BASE_STATIC_FILE_PATHS_START_DELIMTIER//
$fphp_BASE_STATIC_FILE_PATHS = [
    "css" => ["css", "styles"],
    "js" => ["js", "javascript"],
    "files" => ["files", "fls"],
    "fonts" => ["fonts", "fnt"],
    "images" => ["images", "img"],
    "temp" => ["temp", "tmp"],
    "videos" => ["videos", "vid"],
];
//BASE_STATIC_FILE_PATHS_END_DELIMTIER//

//DATA_KEY_NAMES_START_DELIMTIER//
$fphp_data_keys = [
    "params" => "params", // This is the parameters that will be used to handle the request
    "req" => "req", // Probably not needed as $_SERVER has everything
    "post" => "post", // This is the POST data that will be used to handle the request
    "get" => "get", // This is the GET data that will be used to handle the request
    "json" => "json", // This is the JSON data that will be used to handle the request
    "files" => "files", // This is the files data that will be used to handle the request
    "session" => "session", // This is the session data that will be used to handle the request (not needed in this app)
];
//DATA_KEY_NAMES_END_DELIMTIER//

//ALL_GLOBAL_VARIABLES_START_DELIMTIER//
$fphp_all_global_variables_as_strings =
    [
        "fphp_BASEURL_LOCAL",
        "fphp_BASEURL_ONLINE",
        "fphp_BASEURL",
        "fphp_BASEURL_URI",
        "fphp_BASE_STATIC_FILE_PATHS",
        "fphp_data_keys",
        "fphp_DEFAULT_SESSION_COOKIE_NAME",
        "fphp_DEFAULT_SESSION_COOKIE_LIFETIME",
        "fphp_DEFAULT_SESSION_COOKIE_PATH",
        "fphp_DEFAULT_SESSION_COOKIE_DOMAIN",
        "fphp_DEFAULT_SESSION_COOKIE_SECURE",
        "fphp_DEFAULT_SESSION_COOKIE_HTTPONLY",
        "fphp_DEFAULT_SESSION_COOKIE_SAMESITE",
        "fphp_ips_filtered_globals",
        "fphp_ips_filtered_grouped",
        "fphp_uas_filtered_globals",
        "fphp_uas_filtered_grouped",
        "fphp_denied_uas_ais",
        "fphp_denied_uas_others",
        "fphp_o_fail_priorities",
        "fphp_o_ok_priorities",
    ];
//ALL_GLOBAL_VARIABLES_END_DELIMTIER//

// Step 0.2 is setting the default session cookie and database connection for the application.
// Change as needed! They are following Zero Trust- & Least Privilege-principles and are meant to be secure by default.

//DEFAULT_SESSION_COOKIE_START_DELIMTIER//
$fphp_DEFAULT_SESSION_COOKIE_NAME = "fphp_id"; // Change as needed
$fphp_DEFAULT_SESSION_COOKIE_LIFETIME_SECONDS = 28800; // 28800 = 8 hours, change as needed
$fphp_DEFAULT_SESSION_COOKIE_PATH = "/"; // Change as needed
$fphp_DEFAULT_SESSION_COOKIE_DOMAIN = $_SERVER['SERVER_NAME'] == "localhost"
    || $_SERVER['SERVER_NAME'] == "127.0.0.1" ? "localhost" : $fphp_BASEURL_ONLINE;
$fphp_DEFAULT_SESSION_COOKIE_SECURE = $_SERVER['SERVER_NAME'] == "localhost"
    || $_SERVER['SERVER_NAME'] == "127.0.0.1" ? false : true;
$fphp_DEFAULT_SESSION_COOKIE_HTTPONLY = true; // Change as needed
$fphp_DEFAULT_SESSION_COOKIE_SAMESITE = 'Strict'; // Change as needed
//DEFAULT_SESSION_COOKIE_END_DELIMTIER//

//DEFAULTDATABASE_CONNECTION_START_DELIMTIER//
if ($_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "127.0.0.1") {
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASSWORD", "");
    define("DB_NAME", "fphp");
    define("DB_PORT", "3306");
} else {
    // Include your own ONLINE database connection constants here!
    // Or include it or use enviroment variables in your own way.
    // Change below and/or in your inclusded file as needed! This is just an example.
    // IMPORTANT: Always keep your database connection credentials secret and secure!
    $dbFile = include __DIR__ . '/_internals/db_config.php';

    define("DB_HOST", $db_config['DB_HOST']);
    define("DB_USER", $db_config['DB_USER']);
    define("DB_PASSWORD", $db_config['DB_PASSWORD']);
    define("DB_NAME", $db_config['DB_NAME']);
    define("DB_PORT", $db_config['DB_PORT']);
}
//DEFAULT_DATABASE_CONNECTION_END_DELIMTIER//

// Attempt set cookie parameters for the session cookie
session_set_cookie_params([
    'lifetime' => $fphp_DEFAULT_SESSION_COOKIE_LIFETIME_SECONDS,
    'path' => $fphp_DEFAULT_SESSION_COOKIE_PATH,
    'domain' => $fphp_DEFAULT_SESSION_COOKIE_DOMAIN,
    'secure' => $fphp_DEFAULT_SESSION_COOKIE_SECURE,
    'httponly' => $fphp_DEFAULT_SESSION_COOKIE_HTTPONLY,
    'samesite' => $fphp_DEFAULT_SESSION_COOKIE_SAMESITE
]);

// Attempt to connect to the database (returns err key if failed connecting)
$d['db'] = d_connect_db(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (err($d['db'])) {
    $d['db'] = null; // No database connection available
}

//DEFAULT_HEADERS_ADD_START_DELIMTIER//
h_headers_set(
    "Content-Type: text/plain; charset=utf-8", // Change this per matched route in your application
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
);
//DEFAULT_HEADERS_ADD_END_DELIMTIER//

//DEFAULT_HEADERS_REMOVE_START_DELIMTIER//
h_headers_remove(
    "X-Powered-By",
    "Server",
    "X-AspNet-Version",
    "X-AspNetMvc-Version"
);
//DEFAULT_HEADERS_REMOVE_END_DELIMTIER//

//START_SESSION_START_DELIMTIER//
h_start_session();
//START_SESSION_END_DELIMTIER//