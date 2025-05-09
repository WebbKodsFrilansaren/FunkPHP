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

// Loads a validation file from the funkphp/validations/ and then
// sends it to the funk_validate function to validate the data
function funk_use_validation(&$c, $validation, $contentType)
{
    // Check if the input $validation is a valid array structure
    if (!is_array($validation) || empty($validation)) {
        $c['err']['INVALID_VALIDATION_INPUT'] = "Validation input must be a non-empty array!";
        return false;
    }

    // Validate that $contentType is a valid string
    if (!is_string($contentType) || empty($contentType)) {
        $c['err']['INVALID_VALIDATION_CONTENT_TYPE'] = "Content Type to validate against must be a non-empty string!";
        return false;
    }

    // Validate  $contentType is only one of the
    // following: 'post', 'get', 'json', 'files'
    $validContentTypes = [
        'post' => [],
        'get' => [],
        'json' => [],
        'files' => [],
    ];
    if (!array_key_exists(strtolower($contentType), $validContentTypes)) {
        $c['err']['INVALID_VALIDATION_CONTENT_TYPE'] = "Content Type '{$contentType}' is not valid. Choose one of: " . implode(', ', array_keys($validContentTypes));
        return false;
    }

    // This will hold all loaded data before filtering
    // $tableKey is 'authors', $columnListForTable is ['email', 'name']
    $allLoadedValidationData = [];
    foreach ($validation as $tableKey => $columnListForTable) {
        if (!is_string($tableKey) || empty($tableKey)) {
            $c['err']['INVALID_VALIDATION_INPUT'] = "Validation table key must be a non-empty string!";
            return false;
        }
        if (!is_array($columnListForTable)) {
            $c['err']['INVALID_VALIDATION_INPUT'] = "Validation column list for table '{$tableKey}' must be an array!";
            return false;
        }
        foreach ($columnListForTable as $colName) {
            if (!is_string($colName) || empty($colName)) {
                $c['err']['INVALID_VALIDATION_INPUT'] = "Validation column names in the list for table '{$tableKey}' must be non-empty strings!";
                return false;
            }
        }

        // Construct the path to the validation file for this table
        // Check if the validation file exists and is readable
        $validationFile = dirname(dirname(__DIR__)) . '/validations/' . $tableKey . '.php';
        if (file_exists_is_readable_writable($validationFile)) {
            $validationDataFromFile = include_once $validationFile;

            // Check if the file returned a valid array containing data for the specific table key
            // And then store the loaded validation data for this table into our accumulator
            // Only take the data specifically under the table key
            if (is_array($validationDataFromFile) && isset($validationDataFromFile[$tableKey]) && is_array($validationDataFromFile[$tableKey])) {
                $allLoadedValidationData[$tableKey] = $validationDataFromFile[$tableKey];
            }
            // Handle error: file content is not in the expected format
            else {
                $c['err']['INVALID_VALIDATION_FILE_CONTENT'] = "Validation file '{$tableKey}.php' content is invalid or missing table key!";
                return false;
            }
        }
        // Handle error: validation file not found or not readable
        else {
            $c['err']['VALIDATION_FILE_NOT_FOUND'] = "Validation file '{$tableKey}.php' not found or not readable!";
            return false;
        }
    }

    // Now we call the validation function for the specific
    // content type and pass the loaded validation data to it
    $validationFunctionName = 'funk_validate_' . strtolower($contentType);
    if (function_exists($validationFunctionName)) {
        $validationErrors = $validationFunctionName($c, $allLoadedValidationData, $validation);
        if (!empty($validationErrors)) {
            // Handle validation errors
            $c['err']['VALIDATION_FAILED'] = true;
            $c['err']['VALIDATION_ERRORS'] = $validationErrors;
            return false;
        }
    } else {
        // Handle error: validation function not found
        $c['err']['VALIDATION_FUNCTION_NOT_FOUND'] = "Validation function '{$validationFunctionName}' not found!";
        return false;
    }

    // If we reach here, it means ALL VALIDATION passed successfully
    return true;
}

// The main validation function for validating data
// in FunkPHP mapping to the "$_POST" variables ONLY!
function funk_validate_post(&$c, $allLoadedValidationData, $tablesAndColsToValidate)
{
    $errors = [];
    echo "<pre>";
    print_r($tablesAndColsToValidate);
    echo "</pre>";

    return $errors;
}

// The main validation function for validating data
// in FunkPHP mapping to the "$_GET" variables ONLY!
function funk_validate_get(&$c, $allLoadedValidationData, $tablesAndColsToValidate)
{
    $errors = [];

    // It has a start function so it can also be used recursively!
    $start_validate = function (&$c, $data_keys_and_associated_validation_rules_values) use ($errors) {};

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


    return $errors;
}

// The main validation function for validating data
// in FunkPHP mapping to the php://input data ONLY!
function funk_validate_json(&$c, $allLoadedValidationData, $tablesAndColsToValidate)
{
    $errors = [];

    // It has a start function so it can also be used recursively!
    $start_validate = function (&$c, $data_keys_and_associated_validation_rules_values) use ($errors) {};

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


    return $errors;
}

// The main validation function for validating data
// in FunkPHP mapping to the $_FILES variables ONLY!
function funk_validate_files(&$c, $allLoadedValidationData, $tablesAndColsToValidate)
{
    $errors = [];

    // It has a start function so it can also be used recursively!
    $start_validate = function (&$c, $data_keys_and_associated_validation_rules_values) use ($errors) {};

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

    return $errors;
}

///////////////////////////////////////////////////////////////////////////////////
// BELOW ARE ALL THE VALIDATION FUNCTIONS THAT WILL BE USED TO VALIDATE THE DATA //
// Feel free to add your own as needed. Name them funk_validate_<name> and they  //
// will be automatically added to the list of available validation functions     //
///////////////////////////////////////////////////////////////////////////////////

// Validate that Value is a valid UTF-8 string
function funk_validate_string(&$c, $data, $value, $customErr = null)
{
    if (!is_string($value)) {
        $c['d']['VALIDATION_FAILED'] = $customErr ? $customErr : "The field {$data['name']} must be a string.";
        return false;
    }
    return true;
}

// Validate that Value is a valid integer - this function won't
// run if "nullable" is set to true in the table definition!!!
function funk_validate_required(&$c, $data, $value, $customErr = null)
{
    if (!isset($value) || empty($value)) {
        $c['err']['VALIDATION_FAILED'] = $customErr ? $customErr : "The field {$data['name']} is required.";
        return false;
    }
    return true;
}
