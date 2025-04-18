<?php
// DEFAULT SESSION COOKIES SETTINGS - Change as needed!
return [
    'SESSION_NAME' => 'fphp_id',
    'SESSION_LIFETIME' => 28800, // 28800 = 8 hours
    'SESSION_PATH' => '/',
    // Hardcode this key value for best security & performance!
    'SESSION_DOMAIN' => ((isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1")))
        ? "localhost" : $_SERVER['SERVER_NAME'],
    // Hardcode this key value for best security & performance!
    'SESSION_SECURE' => (isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1"))
        ? false : true,
    'SESSION_HTTPONLY' => true,
    'SESSION_SAMESITE' => (isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1"))
        ? 'Lax' : 'Strict',
];
