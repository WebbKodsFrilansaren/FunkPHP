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
    function start_validate(&$c, $data_keys_and_associated_validation_rules_values) {}

    // The Available Validation Rules
    function mb_minlen(&$c, $data, $value)
    {
        if (mb_strlen($value) < $data['min']) {
            $errors[] = "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    }
    function mb_maxlen(&$c, $data, $value)
    {
        if (mb_strlen($value) > $data['max']) {
            $errors[] = "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    }
    function minlen(&$c, $data, $value)
    {
        if (strlen($value) < $data['min']) {
            $errors[] = "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    }
    function maxlen(&$c, $data, $value)
    {
        if (strlen($value) > $data['max']) {
            $errors[] = "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    }
    function minmax(&$c, $data, $value)
    {
        if ($value < $data['min'] || $value > $data['max']) {
            $errors[] = "The field {$data['name']} must be between {$data['min']} and {$data['max']}.";
        }
    }
    function minval(&$c, $data, $value)
    {
        if ($value < $data['min']) {
            $errors[] = "The field {$data['name']} must be at least {$data['min']}.";
        }
    }
    function required(&$c, $data, $value)
    {
        if (!isset($value) || empty($value)) {
            $errors[] = "The field {$data['name']} is required.";
        }
    }
    function string(&$c, $data, $value)
    {
        if (!is_string($value)) {
            $errors[] = "The field {$data['name']} must be a string.";
        }
    }
    function int(&$c, $data, $value)
    {
        if (!is_int($value)) {
            $errors[] = "The field {$data['name']} must be an integer.";
        }
    }
    function float(&$c, $data, $value)
    {
        if (!is_float($value)) {
            $errors[] = "The field {$data['name']} must be a float.";
        }
    }
    function bool(&$c, $data, $value)
    {
        if (!is_bool($value)) {
            $errors[] = "The field {$data['name']} must be a boolean.";
        }
    }
    function hex(&$c, $data, $value)
    {
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $value)) {
            $errors[] = "The field {$data['name']} must be a valid hex color code.";
        }
    }
    // TODO: Improve this!
    function email(&$c, $data, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "The field {$data['name']} must be a valid email address.";
        }
    }
    function url(&$c, $data, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = "The field {$data['name']} must be a valid URL.";
        }
    }
    // TODO: Improve this!
    function phone(&$c, $data, $value)
    {
        if (!preg_match('/^\+?[0-9]{1,4}?[-. ]?\(?[0-9]{1,4}?\)?[-. ]?[0-9]{1,4}[-. ]?[0-9]{1,9}$/', $value)) {
            $errors[] = "The field {$data['name']} must be a valid phone number.";
        }
    }
}
