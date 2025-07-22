<?php
return function (&$c) {
    // Attempt connecting to the database creating a new mysqli object
    try {
        // Create a new mysqli object with the provided parameters
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 3306);
        $conn->set_charset('utf8mb4');

        // No error reporting for production environment
        if ($_SERVER['SERVER_NAME'] !== "localhost" && $_SERVER['SERVER_NAME' !== '127.0.0.1']) {
            mysqli_report(MYSQLI_REPORT_OFF); // No MySQL errors
            error_reporting(0);   // Also no PHP errors
        }
        $c['db'] = $conn;
    } catch (Exception $e) {
        if ($c['db'] === null) {
            $c['err']['PIPELINE']['REQUEST']['pl_db_connect'][] = 'Database Connection Failed. Please check your Database Connection Configuration in `funkphp/config/db_config.php`!';
        }
    }
};
