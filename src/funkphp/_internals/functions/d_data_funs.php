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

    // Categorize found data type rule so "min" and "max" and similar
    // ambiguous rules can be applied to the correct data type!
    // We will swiftly loop through to find it. Thanks to the priority
    // order of the rules, it should actually be the first rule right
    // after "nullable", "required", & "stop" rules if they exist!
    $categorizedDataTypeRules = [
        // Rules that generally apply to string-like inputs
        // Dates are often validated as strings
        'string_types' => [
            'string' => true,
            'email' => true,
            'json' => true,
            'url' => true,
            'ip' => true,
            'ip4' => true,
            'ip6' => true,
            'uuid' => true,
            'phone' => true,
            'date' => true,
        ],
        // Rules that generally apply to numeric inputs
        // "numbers" = More general numeric type
        'number_types' => [
            'integer' => true,
            'float' => true,
            'number' => true,
        ],
        // Rules that generally apply to array-like inputs
        // Lists are often treated as arrays
        // Sets can be treated as arrays with unique values
        'array_types' => [
            'array' => true,
            'list' => true,
            'set' => true,
        ],
        // Rules for arrays, objects, and other complex structures
        // JSON is typically validated as a string or an object/array
        // Enums can be strings or numbers, but often involve specific sets
        // Similar to enum, for validating against a predefined set
        // Booleans are distinct, but often processed separately from numbers/strings
        'complex_types' => [
            'object' => true,
            'checked',
            'enum' => true,
            'boolean' => true,
            'file' => true,
            'image' => true,
            'audio' => true,
            'video' => true,
        ],
    ];
    $foundTypeRule = null;
    $foundTypeCat = null;
    foreach ($rules as $ruleName => $ruleConfig) {
        if (
            isset($categorizedDataTypeRules['string_types'][$ruleName])
        ) {
            $foundTypeRule = $ruleName;
            $foundTypeCat = 'string_types';
            break;
        } elseif (isset($categorizedDataTypeRules['number_types'][$ruleName])) {
            $foundTypeRule = $ruleName;
            $foundTypeCat = 'number_types';
            break;
        } elseif (isset($categorizedDataTypeRules['array_types'][$ruleName])) {
            $foundTypeRule = $ruleName;
            $foundTypeCat = 'array_types';
            break;
        } elseif (isset($categorizedDataTypeRules['complex_types'][$ruleName])) {
            $foundTypeRule = $ruleName;
            $foundTypeCat = 'complex_types';
            break;
        }
    }
    if ($foundTypeRule) {
        $validatorFn = 'funk_validate_' . $foundTypeRule;
        $ruleConfig = $rules[$foundTypeRule];
        $ruleValue = $ruleConfig['value'] ?? null;
        $customErr = $ruleConfig['err_msg'] ?? null;

        $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);

        if ($error !== null) {
            $currentErrPath[$foundTypeRule] = $error;
            $allPassed = false;
            if (isset($rules['stop'])) {
                return;
            }
        }
    }
    // In case no valid data type rule was found (should only happen if it hasn't been added yet)
    else {
        $c['err']['UNKNOWN_VALIDATOR_DATA_TYPE_RULE'] = "Please inform the Developer that An Unknown Data Type Validation Rule was spotted!";
        if (isset($rules['stop'])) {
            return;
        }
        $allPassed = false;
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

            // Set the error message for this specific rule
            // if it is not null, meaning validation failed
            // Also stop remaining validation for
            // this input data if 'stop' is true!
            if ($error !== null) {
                $currentErrPath[$rule] = $error;
                $allPassed = false;
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

// Validate that Input Data is a valid list (a numbered array)
function funk_validate_list($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || (is_array($inputData) && !array_is_list($inputData))) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a list";
    }
    return null;
}

// Validate that Input Data is a valid set (an array with unique values)
function funk_validate_set($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || (is_array($inputData) && count($inputData) !== count(array_unique($inputData)))) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a set (an array with unique values).";
    }
    return null;
}

// Validate that Input Data is a valid boolean (true/false, 1/0, "1"/"0")
function funk_validate_boolean($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        $inputData === true ||
        $inputData === false ||
        $inputData === 1 ||
        $inputData === 0 ||
        $inputData === "1" ||
        $inputData === "0"
    ) {
        return null;
    } else {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be of a boolean value type.";
    }
}

// Validate that Input Data checked in a boolean way
function funk_validate_checked($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        $inputData === true ||
        $inputData === 1 ||
        $inputData === "1" ||
        $inputData === "on" ||
        $inputData === "yes" ||
        $inputData === "ja" || // Swedish easter egg
        $inputData === "true" ||
        $inputData === "checked" ||
        $inputData === "enabled" ||
        $inputData === "selected"
    ) {
        return null;
    } else {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be checked in one way or another.";
    }
}

// Validate that Input Data is a valid date in any provided format
// This function uses PHP's strtotime to validate the date format
function funk_validate_date($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr))
            ? $customErr
            : "$inputName must be a date string.";
    }
    if (strtotime($inputData) === false) {
        return (isset($customErr) && is_string($customErr))
            ? $customErr
            : "$inputName must be a valid date in a recognizable format.";
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
function funk_validate_size($inputName, $inputData, $validationValues, $customErr = null) {};
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

// Validate that Input Data is a valid hex color code
// This function checks if the input is a valid hex color code in the format #RRGGBB or #RGB
function funk_validate_color($inputName, $inputData, $validationValues, $customErr = null)
{
    // Check if the input is a string and matches the hex color code pattern
    if (!preg_match('/^#([a-fA-F0-9]{6})$/', $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid hex color code.";
    }
    return null;
}

// Validate that Input Data is in uppercase, must be combiend with string validation
function funk_validate_lowercase($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strtolower($inputData) !== $inputData) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be in lowercase.";
    }
    return null;
}

// Validate that Input Data is in uppercase, must be combined with string validation
function funk_validate_uppercase($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strtoupper($inputData) !== $inputData) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be in uppercase.";
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

// Validate that Input Data does NOT match a specific regex pattern provided in $validationValues
// This can be used for validating strings, numbers, etc., if it can be regex-expressed!
function funk_validate_not_regex($inputName, $inputData, $validationValues, $customErr = null)
{
    if (preg_match($validationValues, $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName matches a forbidden pattern.";
    }
    return null;
}

// Validate that Input Data has a number of decimal places as specified in $validationValues (which can
// be a single number or an array with min and max values for decimal places). This function should
// only be used for floats to be on the safe side since it does NOT check for the decimal point!
function funk_validate_decimals($inputName, $inputData, $validationValues, $customErr = null)
{
    $decimalPart = explode('.', (string)$inputData)[1] ?? '';
    $decimalCount = strlen($decimalPart);

    if (is_array($validationValues)) {
        if ($decimalCount < $validationValues[0] || $decimalCount > $validationValues[1]) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have between {$validationValues[0]} and {$validationValues[1]} decimal places.";
        }
    } else {
        if ($decimalCount !== $validationValues) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have exactly $validationValues decimal places.";
        }
    }
    return null;
}

// Validate that Input Data has all the keys specified in $validationValues (which is an array of keys).
// This function checks if the input data is an array and if it contains all the specified keys.
function funk_validate_array_keys($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }

    foreach ($validationValues as $key) {
        if (!array_key_exists($key, $inputData)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must contain the key '$key'.";
        }
    }
    return null;
}

// Validate that Input Data's array values are within the specified $validationValues.
// This function checks if the input data is an array and if all its values are in the
// specified validation values and the count must be equal to the count of $validationValues.
function funk_validate_array_keys_exact($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    if (count($inputData) !== count($validationValues)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have exactly " . count($validationValues) . " keys.";
    }
    foreach ($validationValues as $key) {
        if (!array_key_exists($key, $inputData)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must contain the key '$key'.";
        }
    }
    return null;
}

// Validate that Input Data's array values are within the specified $validationValues.
// This function checks if the input data is an array and if all its values are in the specified validation values.
function funk_validate_array_values($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!in_array($value, $validationValues, true)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName contains an invalid value '$value' for key '$key'.";
        }
    }
    return null;
}

// Validate that Input Data's array values are exactly as specified in $validationValues.
// This function checks if the input data is an array and if all its values match exactly the specified
// validation values and the count must be equal to the count of $validationValues.
function funk_validate_array_values_exact($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    if (count($inputData) !== count($validationValues)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have exactly " . count($validationValues) . " values.";
    }
    foreach ($inputData as $key => $value) {
        if (!in_array($value, $validationValues, true)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName contains an invalid value '$value' for key '$key'.";
        }
    }
    return null;
}
