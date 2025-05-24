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
    } else {
        $c['err']['FAILED_TO_RUN_DATA_HANDLER'] = true;
        return;
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
// in FunkPHP mapping to the "$_GET" variables ONLY!
function funk_validate_get(&$c, $optimizedValidationArray, $tablesAndColsToValidate = null) {}

// The main validation function for validating data
// in FunkPHP mapping to the "$_POST" variables ONLY!
function funk_validate_post(&$c, $optimizedValidationArray, $tablesAndColsToValidate = null)
{
    $errors = [];
    echo "<pre>";
    print_r($tablesAndColsToValidate);
    echo "</pre>";
}


// The main validation function for validating data
// in FunkPHP mapping to the php://input data ONLY!
function funk_validate_json(&$c, $optimizedValidationArray, $tablesAndColsToValidate = null) {}

// The main validation function for validating data
// in FunkPHP mapping to the $_FILES variables ONLY!
function funk_validate_files(&$c, $optimizedValidationArray, $tablesAndColsToValidate = null) {}

///////////////////////////////////////////////////////////////////////////////////
// BELOW ARE ALL THE VALIDATION FUNCTIONS THAT WILL BE USED TO VALIDATE THE DATA //
// Feel free to add your own as needed. Name them funk_validate_<name> and they  //
// will be automatically added to the list of available validation functions     //
// $inputName is the $_POST/GET/JSON Key with its $inputData value               //
// $validationValues is the array of validation values for this input field      //
// $customErr is the custom error message to be used if validation fails         //
// Each Validation function returns either error message or null if validation   //
// passes which is used to set $c['v']["correctVariableDepth"] to null or error! //
///////////////////////////////////////////////////////////////////////////////////

/*
YOUR CUSTOM VALIDATION FUNCTIONS STARTS_HERE
- It must start with "funk_validate_" and then the name of the function or
  else it won't be called when you use it in any of the validation files!
- It must accept the following parameters:
    - $inputName: The name of the input field being validated
    - $inputData: The data being validated
    - $validationValues: The validation values for this input field
    - $customErr: A custom error message to be used if validation fails
*/



/*
YOUR CUSTOM VALIDATION FUNCTIONS ENDS_HERE
*/

/* ALL IN-BUILT VALIDATION FUNCTIONS IN FunkPHP */
// This function exists so "nullable" can be used as a validation rule
// When it exists and value for the $inputName is null, some rules
// should be skipped associated with value, length,
// etc. since it is already no value and no length!
function funk_validate_nullable($inputName, $inputData, $validationValues, $customErr = null)
{
    return;
}
// Validate that Value is a valid integer - this function won't
// run if "nullable" is set to true in the table definition!!!
function funk_validate_required($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        $inputData === null || (is_string($inputData) && trim($inputData) === '')
        || (is_array($inputData) && empty($inputData))
    ) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName is required.";
    }
    return null;
}

/* Validating valid data type: string, integer, float, array, boolean, email, date */
// Validate that Input Data is a valid UTF-8 string
function funk_validate_string($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a string.";
    }
    return null;
}

// Validate that Input Data is a valid integer
function funk_validate_integer($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_int($inputData) || (intval($inputData) != $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an integer.";
    }
    return null;
}

// Validate that Input Data is a valid float
function funk_validate_float($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_float($inputData) || (floatval($inputData) != $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a float number.";
    }
    return null;
}

// Validate that Input Data is a valid number (is numeric)
function funk_validate_number($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_numeric($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a number.";
    }
    return null;
}

// Validate that Input Data is a valid array
function funk_validate_array($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    return null;
}

// Validate that Input Data is a valid boolean
function funk_validate_boolean($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_bool($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a boolean.";
    }
    return null;
}

// Validate that Input Data is a valid email address
// TODO: Improve this function to check for valid email address format
function funk_validate_email($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!filter_var($inputData, FILTER_VALIDATE_EMAIL)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
    }
    return null;
}

// Validate that Input Data is a valid file (this means we need to check the $_FILES array)
// where the $inputName is the name of the file input field
// TODO: Maybe add more checks for file type, size?
function funk_validate_file($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!isset($_FILES[$inputName])) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid file.";
    }
    return null;
}

/* Validating min & max sizes as values in numbers, as lengths in strings and as number of element sin arrays */
/* These first ones are just placeholders for "cli_convert_simple_validation_rules_to_optimized_validation()"
   to not freak out when it tries to validate a funk_validate_FUNCTION actually exists during compilation! */
function funk_validate_count($inputName, $inputData, $validationValues, $customErr = null) {};
function funk_validate_between($inputName, $inputData, $validationValues, $customErr = null) {};
function funk_validate_min($inputName, $inputData, $validationValues, $customErr = null) {};
function funk_validate_max($inputName, $inputData, $validationValues, $customErr = null) {};
function funk_validate_exact($inputName, $inputData, $validationValues, $customErr = null) {};

// Validate that Input Data is of valid minimal length provided in $validationValues
// This is used ONLY for string inputs. This is "min" when it knows it is a string.
function funk_validate_minlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (mb_strlen($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at least $validationValues characters long.";
    }
    return null;
}

// Validate that Input Data is of valid maximum length provided in $validationValues
// This is used ONLY for string inputs. This is "max" when it knows it is a string.
function funk_validate_maxlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (mb_strlen($inputData) > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at most $validationValues characters long.";
    }
    return null;
}

// Validate that Input Data is of valid length provided in $validationValues
// This is used ONLY for string inputs. This is "between" when it knows it is a string.
function funk_validate_betweenlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (mb_strlen($inputData) <= $validationValues[0] || mb_strlen($inputData) >= $validationValues[1]) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be inclusively between {$validationValues[0]} and {$validationValues[1]} characters long.";
    }
    return null;
}

// Validate that Input Data is of valid minimum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "min" when it knows it is a number.
function funk_validate_minval($inputName, $inputData, $validationValues, $customErr = null)
{
    if ($inputData < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at least $validationValues in value.";
    }
    return null;
}

// Validate that Input Data is of valid maximum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "max" when it knows it is a number.
function funk_validate_maxval($inputName, $inputData, $validationValues, $customErr = null)
{
    if ($inputData > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at most $validationValues in value.";
    }
    return null;
}

// Validate that Input Data is of valid minimum and maximum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "between" when it knows it is a number.
function funk_validate_betweenval($inputName, $inputData, $validationValues, $customErr = null)
{
    if ($inputData <= $validationValues[0] || $inputData >= $validationValues[1]) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be inclusively between {$validationValues[0]} and {$validationValues[1]} in value.";
    }
    return null;
}

// Validate that Input Data's array has minimum number of elements as in $validationValues
// This is used ONLY for array inputs. This is "min" when it knows it is a array.
function funk_validate_mincount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (count($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have at least $validationValues elements.";
    }
    return null;
}

// Validate that Input Data's array has maximum number of elements as in $validationValues
// This is used ONLY for array inputs. This is "max" when it knows it is a array.
function funk_validate_maxcount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (count($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have at most $validationValues elements.";
    }
    return null;
}

// Validate that Input Data's array has minimum and maximum number of elements as in $validationValues
// This is used ONLY for array inputs. This is "between" when it knows it is a array.
function funk_validate_betweencount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (count($inputData) <= $validationValues[0] || count($inputData) >= $validationValues[1]) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have inclusively between {$validationValues[0]} and {$validationValues[1]} elements.";
    }
    return null;
}

// Validate that Input Data is of valid maximum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "max" when it knows it is a number.
function funk_validate_exactval($inputName, $inputData, $validationValues, $customErr = null)
{
    if ($inputData !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be exactly $validationValues in value.";
    }
    return null;
}

// Validate that Input Data is of valid exact length provided in $validationValues meaning
// it must be that length and not less or more. This is used ONLY for string inputs.
function funk_validate_exactlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (mb_strlen($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be exactly $validationValues characters long.";
    }
    return null;
}

// Validate that Input Data's array has an exact number of elements as in $validationValues
// This is used ONLY for array inputs. This is "max" when it knows it is a array.
function funk_validate_exactcount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (count($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have exactly $validationValues elements.";
    }
    return null;
}

// Validate that Input Data is of valid maximum number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "min_digits" when it knows it is a number.
function funk_validate_min_digits($inputName, $inputData, $validationValues, $customErr = null)
{
    if (strlen($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $validationValues digits.";
    }
    return null;
}

// Validate that Input Data is of valid maximum number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "max_digits" when it knows it is a number.
function funk_validate_max_digits($inputName, $inputData, $validationValues, $customErr = null)
{
    if (strlen($inputData) > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at most $validationValues digits.";
    }
    return null;
}

// Validate that Input Data is of valid minimum and maximum number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "between_digits" when it knows it is a number.
function funk_validate_digits_between($inputName, $inputData, $validationValues, $customErr = null)
{
    if (strlen($inputData) <= $validationValues[0] || strlen($inputData) >= $validationValues[1]) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have inclusively between {$validationValues[0]} and {$validationValues[1]} digits.";
    }
    return null;
}

// Validate that Input Data is of valid exact number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "digits" when it knows it is a number.
function funk_validate_digits($inputName, $inputData, $validationValues, $customErr = null)
{
    if (strlen($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have exactly $validationValues digits.";
    }
    return null;
}
