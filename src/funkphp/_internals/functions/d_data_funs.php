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
        $c['err']['FAILED_TO_RUN_DATA_HANDLER'] = "Data Handler must be a string or an array!";
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
            $c['err']['FAILED_TO_RUN_DATA_HANDLER'] = "Data Handler function is not callable!";
            return;
        }
    }
    // Handle error: file not found or not readable  (or just use default below)
    else {
        $c['err']['FAILED_TO_RUN_DATA_HANDLER'] = "Data Handler File '$handler.php' not found or not readable!";
        return;
    }
}

// Function that either gets a valid validation array from a given validation
// file and then a given validation function name or returns null with error.
function funk_use_validation_get_validation_array_or_err_out(&$c, $string)
{
    $handlerFile = null;
    $fnName = null;
    if (!is_string($string) && !is_array($string)) {
        $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = "Validation Handler File must be a string or an array!";
        return null;
    }
    if (is_string($string)) {
        if (strpos($string, '=>') !== false) {
            [$handlerFile, $fnName] = explode('=>', $string);
            $handlerFile = trim($handlerFile);
            $fnName = trim($fnName);
        } else {
            $handlerFile = $string;
            $fnName = $handlerFile;
        }
    } elseif (is_array($string)) {
        $handlerFile = key($string);
        $fnName = $string[$handlerFile];
    }
    if (!str_starts_with($handlerFile, "v_")) {
        $handlerFile = "v_" . $handlerFile;
    }
    if (!str_starts_with($fnName, "v_")) {
        $fnName = "v_" . $fnName;
    }
    if (str_ends_with($handlerFile, ".php")) {
        $handlerFile = substr($handlerFile, 0, -4);
    }
    if (!preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = "Validation Handler File \"{$handlerFile}\" must be a lowercase string containing only letters, numbers and underscores!";
        return null;
    }
    if (!preg_match('/^[a-z0-9_]+$/', $fnName)) {
        $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] =  "Validation Function Name \"$fnName\" must be a lowercase string containing only letters, numbers and underscores!";
        return null;
    }
    $validationFile = dirname(dirname(__DIR__)) . '/validations/' . $handlerFile . '.php';
    if (file_exists_is_readable_writable($validationFile)) {
        $validationDataFromFile = include_once $validationFile;
        if (is_callable($validationDataFromFile)) {
            $resultFromHandler = $validationDataFromFile($c, $fnName);
            if ($resultFromHandler === null || $resultFromHandler === false) {
                $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = "Validation Handler File '{$handlerFile}.php' returned null instead of Validation Function Name \"$fnName\"!";
                return null;
            }
            return $resultFromHandler;
        } else {
            $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = "Validation Handler File '{$handlerFile}.php' did not return a callable function.";
            return null;
        }
    } else {
        $c['err']['FAILED_TO_LOAD_VALIDATION_FILE'] = "Validation Handler File \"{$handlerFile}.php\" not found or not readable!";
        return null;
    }
}

// Function that returns a reference to the current array
function &funk_navigate_v_err_array(&$c, &$currentArrRef, $key, $setValue = null)
{
    if (!isset($currentArrRef[$key]) || !is_array($currentArrRef[$key])) {
        $currentArrRef[$key] = [];
    }
    // If a value is set, we set it in the current array reference
    if (is_string($setValue) && !empty(trim($setValue))) {
        $currentArrRef[$key] =  $setValue;
    }
    // Return a reference to the newly navigated segment
    return $currentArrRef[$key];
}

// Fuction that sets the value of the current array reference
// it assumes $value is string to be optimized for performance
function funk_set_v_err_value(&$c, &$currentArrRef, $value)
{
    $currentArrRef = $value;
}

// Function that validates a set of rules for a given single input field/data
function funk_validation_validate_rules(&$c, $inputValue, $fullFieldName, array $rules, array &$currentErrPath, &$allPassed): void
{
    // Extract some important flag-like rules from the rules array
    $stop = array_key_exists('stop', $rules);
    $nullable = array_key_exists('nullable', $rules);
    $required = array_key_exists('required', $rules);

    // If required rule exist, we grab its value & error and unset it
    // from the array of rules so we do not loop through it later
    if ($required) {
        $required = $rules['required'];
        unset($rules['required']);
    }

    // If stop rule exist, we just unset it because the boolean value
    // is enough to know if we should stop further validation later
    if ($stop) {
        unset($rules['stop']);
    }

    // if nullable exists and the input value is null,
    // then we can just skip validation for this field
    if ($nullable && $inputValue === null) {
        return;
    }

    // Now use the required rule to validate
    // the input value if it exists and we
    // stored its value + error message
    if ($required) {
        $ruleValue = $required['value'] ?? null;
        $customErr = $required['err_msg'] ?? null;
        $error = funk_validate_required($fullFieldName, $inputValue, $ruleValue, $customErr);

        // We set the error we got from the
        // required validation meaning it failed
        if ($error !== null) {
            $currentErrPath['required'] = $error;
            $allPassed = false;

            // Stop further validation for this field as
            // 'required' failed and if 'stop' is true!
            if ($stop) {
                return;
            }
        }
    }

    // ITERATING THROUGH REMAINING RULES THIS INPUT FIELD
    foreach ($rules as $rule => $ruleConfig) {
        $ruleValue = $ruleConfig['value'];
        $customErr = $ruleConfig['err_msg'];

        // Dynamically call the validation function for this rule
        // Assuming your rule functions are named funk_validate_rule
        $validatorFn = 'funk_validate_' . $rule;
        echo "Running `$validatorFn` for field `$fullFieldName` with value `" . json_encode($inputValue) . "`\n";

        if (function_exists($validatorFn)) {
            // Pass current input value, rule value, and custom error
            $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);

            if ($error !== null) {
                $currentErrPath[$rule] = $error; // Set the error message for this specific rule
                $allPassed = false;

                // If 'stop' is true, we stop further validation for this field
                // and do not continue with other rules for this field
                if ($stop) {
                    break;
                }
            }
        } else {
            // Handle unknown validator functions (e.g., log, add to $c['err'])
            $c['err']['UNKNOWN_VALIDATOR_RULE'] = "Please inform the Developer that Validation Rule '{$rule}' has not been implemented yet!";
            $allPassed = false;
        }
    }
};


// Supposed to be an improved version of funk_validation_recursively
function funk_validation_recursively_improved(&$c, array $inputData, array $validationRules, array &$currentErrPath, &$allPassed): bool
{

    // Iterate through the main `return array()` from optimized validation array
    foreach ($validationRules as $DXey => $rulesOrNestedFields) {
        $rulesNodeExist = isset($rulesOrNestedFields['<RULES>']);
        $wildCardExist = $DXey === '*' || $rulesOrNestedFields === '*';
    }
    return $allPassed;
}

// Function is called by funk_use_validation recursively to validate
// all the rules & nested fields in the input data (GET, POST or JSON).
function funk_validation_recursively(&$c, array $inputData, array $validationRules, array $currentPath = [], &$allPassed): bool
{
    // Iterate through the main `return array()` from optimized validation array
    foreach ($validationRules as $key => $rulesOrNestedFields) {
        $isRulesNode = isset($rulesOrNestedFields['<RULES>']);
        $isWildcardNode = $key === '*';
        var_dump("Widlcard Node", $isWildcardNode);

        // Construct the full path for the current field
        $currentFieldPath = array_merge($currentPath, [$key]);
        $fullFieldName = implode('.', $currentFieldPath);

        // 1. Process regular fields with direct rules
        // THIS IS WHERE ACTUAL RULE VALIDATION HAPPENS - AND HERE WE WANNA
        // USE THE DYNAMIC NATURE OF FIRST FINDING OUT ABOUT "NULLABLE", "REQUIRED",
        // AND THE DATA TYPE FOUND SO WE KNOW WHAT "MIN", "MAX" is ACTUALLY FOR!
        // (minval, maxval, minlen, maxlen, etc.)
        if ($isRulesNode) {
            // Get the rules & input value for the current field
            $fieldRules = $rulesOrNestedFields['<RULES>'];
            $inputValue = $inputData[$key] ?? null;
            $inputValue = is_string($inputValue) ? trim($inputValue) : $inputValue;
            var_dump("CURRENT INPUT VALUE: ", $inputValue);
            $stop = array_key_exists('stop', $fieldRules);
            $nullable = array_key_exists('nullable', $fieldRules);
            $required = array_key_exists('required', $fieldRules);

            // if nullable exists and the input value is null,
            // then we can just skip validation for this field
            if ($nullable && $inputValue === null) {
                continue;
            }

            // ITERATING THROUGH EACH SINGLE RULE FOR THIS FIELD
            foreach ($fieldRules as $ruleName => $ruleConfig) {
                $ruleValue = $ruleConfig['value'];
                $customErr = $ruleConfig['err_msg'];

                // Dynamically call the validation function for this rule
                // Assuming your rule functions are named funk_validate_ruleName
                $validatorFn = 'funk_validate_' . $ruleName;
                echo "Running `$validatorFn` for field `$fullFieldName` with value `" . json_encode($inputValue) . "`\n";

                if (function_exists($validatorFn)) {
                    // Pass current input value, rule value, and custom error
                    $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);

                    if ($error !== null) {
                        // A validation error occurred for this field and rule
                        // Use funk_navigate_v_err_array to set the error message deeply
                        $errorPathRef = &$c['v'];
                        foreach ($currentFieldPath as $pathSegment) {
                            $errorPathRef = &funk_navigate_v_err_array($c, $errorPathRef, $pathSegment);
                        }
                        // Set the error message for this specific rule
                        // and mark overall validation as failed
                        funk_set_v_err_value($c, $errorPathRef[$ruleName], $error);
                        $allPassed = false;

                        // If 'stop' is true, we stop further validation for this field
                        // and do not continue with other rules for this field
                        if ($stop) {
                            break;
                        }
                    }
                } else {
                    // Handle unknown validator functions (e.g., log, add to $c['err'])
                    $c['err']['UNKNOWN_VALIDATOR_RULE'] = "Validation rule '{$ruleName}' not implemented.";
                    $allPassed = false;
                }
            }
        }
        // 2. Process nested fields (recursive call)
        if (is_array($rulesOrNestedFields) && !$isWildcardNode) {
            // Recurse into nested structure
            $nestedInputData = $inputData[$key] ?? [];
            if (!is_array($nestedInputData)) {
                // If input is not an array where nested rules are expected, it's an error
                $errorPathRef = &$c['v'];
                foreach ($currentFieldPath as $pathSegment) {
                    $errorPathRef = &funk_navigate_v_err_array($c, $errorPathRef, $pathSegment);
                }
                funk_set_v_err_value($c, $errorPathRef['type'], "Expected an array for '{$fullFieldName}'.");
                $allPassed = false;
                // Don't recurse further if it's not an array
            } else {
                if (!funk_validation_recursively($c, $nestedInputData, $rulesOrNestedFields, $currentFieldPath, $allPassed)) {
                    $allPassed = false; // If any nested validation fails, overall fails
                }
            }
        }
        // 3. Handle wildcard '*' array elements
        if ($isWildcardNode) {
            var_dump("InputData", $inputData);
            $inputArray = $inputData[$key] ?? []; // The array that contains multiple elements
            if (!is_array($inputArray)) {
                $errorPathRef = &$c['v'];
                foreach ($currentPath as $pathSegment) {
                    $errorPathRef = &funk_navigate_v_err_array($c, $errorPathRef, $pathSegment);
                }
                funk_set_v_err_value($c, $errorPathRef[$key]['type'], "Expected an array for wildcard validation at '{$fullFieldName}'.");
                $allPassed = false;
            } else {
                // Iterate through each element in the input array for wildcard validation
                foreach ($inputArray as $index => $arrayElement) {
                    // Construct path for the current array element (e.g., "user.0", "user.1")
                    $elementPath = array_merge($currentPath, [$index]); // Use original $key for *
                    $elementInput = is_array($arrayElement) ? $arrayElement : [$key => $arrayElement]; // Ensure array for sub-traversal

                    // Recurse using the *rules* defined under the wildcard, applied to each element
                    // The rules under '*' are for direct children of the array elements (e.g. 'email' in 'user.*.email')
                    if (!funk_validation_recursively($c, $arrayElement, $rulesOrNestedFields, $elementPath, $allPassed)) {
                        $allPassed = false;
                    }
                }
            }
        }
    }

    return $allPassed;
}

// The main validation function for validating data in FunkPHP
// mapping to the "$_GET"/"$_POST" or "php://input" (JSON) variable ONLY!
function funk_use_validation(&$c, $optimizedValidationArray, $source)
{
    // Validation Error Array and its OK varaible must exist to run this function
    if (!array_key_exists('v', $c)) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Validation Function needs the Validation Error Array `\$c['v']`!";
        return false;
    }
    if (!array_key_exists('v_ok', $c)) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Validation Function needs the Validation Error Array `\$c['v_ok']`!";
        return false;
    }

    // Inform about the fact that this function is not
    // used for validating $_FILES variables and that
    // a different function should be used for that!
    if ($source === "FILES") {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Use Validation Function `funk_use_validation_files(&\$c, \$optimizedValidationArray)` instead to validate `\$_FILES`!";
        return false;
    }

    // Check that $optimizedValidationArray is a valid array
    if (!is_array($optimizedValidationArray) || empty($optimizedValidationArray)) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Validation Function needs a non-empty array for `\$optimizedValidationArray`!";
        return false;
    }

    // Check that $source is a valid string and is either "GET", "POST" or "JSON" (must be exact)
    $allowedSources = ['GET' => [], 'POST' => [], 'JSON' => []];
    if (!is_string($source) || !isset($allowedSources[$source])) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Validation Function needs a valid string for `\$source` (\"GET\", \"POST\" or \"JSON\" - uppercase only)!";
        return false;
    }

    // Load input based on the source and make
    // sure it is a valid non-empty array!
    $inputData = null;
    if ($source === 'GET') {
        $inputData = $_GET ?? null;
    } elseif ($source === 'POST') {
        $inputData = $_POST ?? null;
    } elseif ($source === 'JSON') {
        $inputData = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Validation Function needs a valid decoded JSON string for `\$source`!";
            return false;
        }
    }
    if (!is_array($inputData) || empty($inputData)) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION'] = "Validation Function needs a valid non-empty array for `\$inputData`!";
        return false;
    }

    // REMOVE THIS LINE WHEN DONE TESTING
    // This is just for testing purposes to see the input data
    var_dump("TEST DATA(GET/POST/JSON):", $inputData);

    // Now we can run the validation recursively and
    $c['v_ok'] = true;
    $c['v'] = [];
    $allPassed = true;
    if (!funk_validation_recursively(
        $c,
        $inputData,
        $optimizedValidationArray,
        [],
        $allPassed
    )) {
        $c['v_ok'] = false;
    }

    // When this is set to true, it means that the validation
    // function has passed and no errors were found/added to $c['v']
    // Its default value is null meaning either no validation was run
    // or it failed and no errors were found/added to $c['v'] before this!
    if ($c['v_ok']) {
        return true;
    }
    return false;
}

// The main validation function for validating data
// in FunkPHP mapping to the $_FILES variables ONLY!
function funk_use_validation_files(&$c, $optimizedValidationArray)
{
    // Check that $optimizedValidationArray is a valid array
    if (!is_array($optimizedValidationArray) || empty($optimizedValidationArray)) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION_FILES'] = "Files Validation Function must receive a non-empty array for `\$optimizedValidationArray`!";
        return false;
    }

    // Check that $_FILES is a valid array and is not empty
    if (!is_array($_FILES) || empty($_FILES)) {
        $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION_FILES'] = "Files Validation Function must receive a non-empty array for `\$_FILES`!";
        return false;
    }

    // When this is set to true, it means that the validation
    // function has passed and no errors were found/added to $c['v']
    if ($c['v_ok']) {
        return true;
    }
    return false;
}

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
function funk_validate_stop($inputName, $inputData, $validationValues, $customErr = null) {};

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


// Validate that Input Data matches a specific regex pattern provided in $validationValues
// This can be used for validating strings, numbers, etc., if it can be regex-expressed!
function funk_validate_regex($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!preg_match($validationValues, $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName does not match the required pattern.";
    }
    return null;
}
