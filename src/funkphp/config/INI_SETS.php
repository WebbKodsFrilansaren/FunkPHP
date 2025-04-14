<?php
// DEFAULT INI_SET Configuration - Change as needed!
// Configared as an array of running: ini_set(key, value);
// Runs AFTER: Loading all $c variables in (funkpphp/funkphp_start.php)
// Runs BEFORE: Establishing Database ($c['db'] ?? null) connection
// Runs BEFORE: Setting/removing default sent Headers ($c['HEADERS']['ADD']) & removed ($c['HEADERS']['REMOVE'])
// RUNS BEFORE: Session starts (h_start_session())!
return [
    'session.cache_limiter' => 'public',
    'session.use_strict_mode' => 1, // Prevent session fixation attacks
    'session.use_only_cookies' => 1, // Prevent session fixation attacks
    'session.cache_expire' => 30, // Session cache expiration time in minutes
    'session.cookie_lifetime' => 0, // 0 = until browser is closed
    'session.name' => 'fphp_id', // We overwrite other cookie named "id" with this one
    'session.sid_length' => 192, // Length of the session ID
    'session.sid_bits_per_character' => 6, // Bits per character to increase entropy
    // IMPORTANT: Remove these configs for PRODUCTION to improve performance!
    'display_errors' => (isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1"))
        ? 1 : 0,
    'display_startup_errors' => (isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1"))
        ? 1 : 0,
    'error_reporting' => (isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1"))
        ? E_ALL : 0,
];
