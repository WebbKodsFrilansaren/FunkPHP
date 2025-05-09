<?php // DATABASE FUNCTIONS FOR FuncPHP
// This file contains functions related to database operations and/or configurations.

// Return the database connection object or an error message
function funk_connect_db($dbHost, $dbUser, $dbPass, $dbName, $dbPort = 3306, $dbCharset = 'utf8mb4')
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
function funk_validate(&$c, array $data_keys_and_associated_validation_rules_values)
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
        if (mb_strlen($value) < $data['minlen']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    };
    $mb_maxlen = function (&$c, $data, $value, $customErr = null) {
        if (mb_strlen($value) > $data['maxlen']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    };
    $minlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) < $data['minlen']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    };
    $maxlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) > $data['maxlen']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    };
    $minmaxlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) < $data['minlen'] || strlen($value) > $data['maxlen']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be between {$data['min']} and {$data['max']} characters long.";
        }
    };
    $minmaxval = function (&$c, $data, $value, $customErr = null) {
        if ($value < $data['minval'] || $value > $data['maxval']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be between {$data['min']} and {$data['max']}.";
        }
    };
    $maxval = function (&$c, $data, $value, $customErr = null) {
        if ($value > $data['maxval']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']}.";
        }
    };
    $minval  = function (&$c, $data, $value, $customErr = null) {
        if ($value < $data['minval']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']}.";
        }
    };
    $nonnegative = function (&$c, $data, $value, $customErr = null) {
        if ($value < 0) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a non-negative value.";
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
    $number = function (&$c, $data, $value, $customErr = null) {
        if (!is_numeric($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a number.";
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

    // TODO: Verify this works as intended!
    // We now loop through the data keys and their associated validation rules
    foreach ($data_keys_and_associated_validation_rules_values as $key => $data) {
        // Check if the key exists in the request data
        if (isset($c['req'][$key])) {
            // Loop through the validation rules and apply them
            foreach ($data['validation'] as $rule => $value) {
                // Check if the rule is callable (a function)
                if (is_callable($$rule)) {
                    // Call the rule with the data and value
                    $$rule($c, $data, $c['req'][$key], $value);
                } else {
                    // Handle error: rule not callable (or just use default below)
                    $errors[] = "The validation rule {$rule} is not callable.";
                }
            }
        } else {
            // Handle error: key not found in request data (or just use default below)
            $errors[] = "The field {$key} is not found in the request data.";
        }
    }

    return $errors;
}

// Loads a validation file from the funkphp/validations/ and then
// sends it to the funk_validate function to validate the data
function funk_use_validation(&$c, $validation)
{
    // Check if the validation file exists and is readable
    if (!is_array($validation)) {
        $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = true;
        return false;
    }

    // We will now loop through the root keys in $validation
    // since each root key is the file name inside of funkphp/validations/
    // so we need to include all files and then merge them into one array
    // and then pass it to the funk_validate function! For each root key
    // we check that the file exists and is readable, and then include it
    // and run the validation function with the $c variable as argument.
    $tablesAndColsToValidate = [];

    foreach ($validation as $key => $value) {
        // Check if the file exists and is readable
        $validationFile = dirname(dirname(__DIR__)) . '/validations/' . $key . '.php';
        if (file_exists_is_readable_writable($validationFile)) {
            $validationData = include_once $validationFile;
            if (is_array($validationData)) {
                // Merge the validation data into the main validation array
                $tablesAndColsToValidate = array_merge($tablesAndColsToValidate, $validationData);
            } else {
                $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = true;
                echo "Validation file is not an array!";
                return false;
            }
        } else {
            // Handle error: file not found or not readable (or just use default below)
            echo "Validation file does not exist or is not readable!";
            $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = true;
            return false;
        }
    }
}

// Run the matched data handler (Step 4 after matched routing in Routes,
// then running Middlewares and then the Route Handler)
function funk_run_matched_data_handler(&$c)
{
    // Grab Data Handler Path and prepare whether it is a string
    // or array to match "handler" or ["handler" => "fn"]
    $handlerPath = dirname(dirname(__DIR__)) . '/data/';
    $handler = "";
    $handleString = null;
    if (is_string($c['req']['matched_data'])) {
        $handler = $c['req']['matched_data'];
    } elseif (is_array($c['req']['matched_data'])) {
        $handler = key($c['req']['matched_data']);
        $handleString = $c['req']['matched_data'][$handler] ?? null;
    }

    // Finally check if the file exists and is readable, and then include it
    // and run the handler function with the $c variable as argument
    if (file_exists("$handlerPath/$handler.php") && is_readable("$handlerPath/$handler.php")) {
        $runHandler = include_once "$handlerPath/$handler.php";
        if (is_callable($runHandler)) {
            if (!is_null($handleString)) {
                $runHandler($c, $handleString);
            } else {
                $runHandler($c);
            }
        }
        // Handle error: not callable (or just use default below)
        else {
            $c['err']['FAILED_TO_RUN_DATA_HANDLER'] = true;
            return;
        }
    }
    // Handle error: file not found or not readable  (or just use default below)
    else {
        $c['err']['FAILED_TO_RUN_DATA_HANDLER'] = true;
        return;
    }
}
