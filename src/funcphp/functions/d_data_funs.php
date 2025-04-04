<?php // DATABASE FUNCTIONS FOR FuncPHP
// This file contains functions related to database operations and/or configurations.

// Return the database connection object or an error message
function d_connect_db($dbHost, $dbUser, $dbPass, $dbName, $dbPort = 3306, $dbCharset = 'utf8mb4')
{
    // Attempt connecting to the database creating a new mysqli object
    try {
        // Create a new mysqli object with the provided parameters
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
        $conn->set_charset($dbCharset);

        // No error reporting for production environment
        if ($_SERVER['SERVER_NAME'] !== "localhost" && $_SERVER['SERVER_NAME' !== '127.0.0.1']) {
            mysqli_report(MYSQLI_REPORT_OFF); // No MySQL errors
            error_reporting(0);   // Also no PHP errors
        }
        return success($conn);
    } catch (Exception $e) {
        // Return error message if connection fails
        return fail("[d_connect_db]: DB Connection failed: " . $e->getMessage());
    }
}
