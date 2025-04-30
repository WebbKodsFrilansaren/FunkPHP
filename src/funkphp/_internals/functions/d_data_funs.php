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

// The main validation function for validating data in FunkPHP
function d_validate(&$c, array $data_keys_and_associated_validation_rules_values)
{
    $errors = [];

    // It has a start function so it can also be used recursively!
    $start_validate = function (&$c, $data_keys_and_associated_validation_rules_values) use ($errors) {};

    // The Available Validation Rules
    $utf8 = function (&$c, $data, $value, $customErr = null) {
        if (!mb_check_encoding($value, 'UTF-8')) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid UTF-8 string.";
        }
    };
    $mb_minlen = function (&$c, $data, $value, $customErr = null) {
        if (mb_strlen($value) < $data['min']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    };
    $mb_maxlen = function (&$c, $data, $value, $customErr = null) {
        if (mb_strlen($value) > $data['max']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    };
    $minlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) < $data['min']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    };
    $maxlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) > $data['max']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    };
    $minmax = function (&$c, $data, $value, $customErr = null) {
        if ($value < $data['min'] || $value > $data['max']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be between {$data['min']} and {$data['max']}.";
        }
    };
    $minval  = function (&$c, $data, $value, $customErr = null) {
        if ($value < $data['min']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']}.";
        }
    };
    $required = function (&$c, $data, $value, $customErr = null) {
        if (!isset($value) || empty($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} is required.";
        }
    };
    $string  = function (&$c, $data, $value, $customErr = null) {
        if (!is_string($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a string.";
        }
    };
    $int  = function (&$c, $data, $value, $customErr = null) {
        if (!is_int($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be an integer.";
        }
    };
    $float  = function (&$c, $data, $value, $customErr = null) {
        if (!is_float($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a float.";
        }
    };
    $bool  = function (&$c, $data, $value, $customErr = null) {
        if (!is_bool($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a boolean.";
        }
    };
    $hex  = function (&$c, $data, $value, $customErr = null) {
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid hex color code.";
        }
    };
    // TODO: Improve this!
    $email  = function (&$c, $data, $value, $customErr = null) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid email address.";
        }
    };
    $url =  function (&$c, $data, $value, $customErr = null) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid URL.";
        }
    };
    // TODO: Improve this!
    $phone = function (&$c, $data, $value, $customErr = null) {
        if (!preg_match('/^\+?[0-9]{1,4}?[-. ]?\(?[0-9]{1,4}?\)?[-. ]?[0-9]{1,4}[-. ]?[0-9]{1,9}$/', $value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid phone number.";
        }
    };
}
