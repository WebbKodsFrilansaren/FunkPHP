<?php // IMPORTANT: All steps in 0 are meant to be "set and forget" in the sense that they are not meant to be changed after the initial setup of the application.
// Step 0.2 is setting the default session cookie and database connection for the application.
// Change as needed! They are following Zero Trust- & Least Privilege-principles and are meant to be secure by default.

//DEFAULT_SESSION_COOKIE_START_DELIMTIER//
$fphp_DEFAULT_SESSION_COOKIE_NAME = "fphp_id"; // Change as needed
$fphp_DEFAULT_SESSION_COOKIE_LIFETIME = 28800; // 28800 = 8 hours, change as needed
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
    $dbFile = "";
    include "$dbFile";
    define("DB_HOST", $db_config['DB_HOST']);
    define("DB_USER", $db_config['DB_USER']);
    define("DB_PASSWORD", $db_config['DB_PASSWORD']);
    define("DB_NAME", $db_config['DB_NAME']);
    define("DB_PORT", $db_config['DB_PORT']);
}
//DEFAULT_DATABASE_CONNECTION_END_DELIMTIER//

// Attempt set cookie parameters for the session cookie
session_set_cookie_params([
    'lifetime' => $fphp_DEFAULT_SESSION_COOKIE_LIFETIME,
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
