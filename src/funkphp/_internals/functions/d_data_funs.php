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
function funk_validation_validate_rules(&$c, $inputValue, $fullFieldName, array $rules, array &$currentErrPath): void
{
    // Extract some important flag-like rules from the rules array
    $stop = array_key_exists('stop', $rules);
    $nullable = array_key_exists('nullable', $rules);
    $required = array_key_exists('required', $rules);
    $field = array_key_exists('field', $rules);

    // Check if "field" rule exist since that contains the custom
    // field name used by the Developer that would then apply to
    // ALL rules for this given input field/data!
    if ($field) {
        $fullFieldName = $rules['field']['value'] ?? $fullFieldName;
        unset($rules['field']);
    }

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
        // echo "Running `funk_validate_required` for field `$fullFieldName` with value `" . (is_string($inputValue) ? $inputValue :  json_encode($inputValue)) . "`\n";
        $error = funk_validate_required($fullFieldName, $inputValue, $ruleValue, $customErr);

        // We set the error we got from the
        // required validation meaning it failed
        if ($error !== null) {
            $currentErrPath['required'] = $error;
            $c['v_ok'] = false;

            // TODO: EXPERIMENTAL: Might not work as intended in all cases
            // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
            if (
                isset($c['v_config']['stop_all_on_first_error'])
            ) {
                $c['v_config']['stop_all_on_first_error'] = true;
                return;
            }

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
            'char' => true,
            'email' => true,
            'email_custom' => true,
            'password' => true,
            'password_custom' => true,
            'password_confirm' => true,
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
            'digit' => true,
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
            'enum' => true,
        ],
        'file_types' => [
            'file' => true,
            'image' => true,
            'video' => true,
            'audio' => true,
        ],
        // Rules for arrays, objects, and other complex structures
        // JSON is typically validated as a string or an object/array
        // Enums can be strings or numbers, but often involve specific sets
        // Similar to enum, for validating against a predefined set
        // Booleans are distinct, but often processed separately from numbers/strings
        'complex_types' => [
            'null' => true,
            'object' => true,
            'unchecked' => true,
            'checked' => true,
            'boolean' => true,
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
        } elseif (isset($categorizedDataTypeRules['file_types'][$ruleName])) {
            $foundTypeRule = $ruleName;
            $foundTypeCat = 'file_types';
            break;
        }
    }
    if ($foundTypeRule) {
        $validatorFn = 'funk_validate_' . $foundTypeRule;
        $ruleConfig = $rules[$foundTypeRule];
        $ruleValue = $ruleConfig['value'] ?? null;
        $customErr = $ruleConfig['err_msg'] ?? null;

        $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);

        // Mark validation as failed if error is not null
        // and also stop if optionally set
        if ($error !== null) {
            $currentErrPath[$foundTypeRule] = $error;
            $c['v_ok'] = false;

            // TODO: EXPERIMENTAL: Might not work as intended in all cases
            // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
            if (
                isset($c['v_config']['stop_all_on_first_error'])
            ) {
                $c['v_config']['stop_all_on_first_error'] = true;
                return;
            }

            if (isset($rules['stop'])) {
                return;
            }
        }
    }
    // In case no valid data type rule was found
    // (should only happen if it hasn't been added yet)
    else {
        // Because we find no valid data type rule, nothing else
        // would work as expected so we just set the error and quit
        // validation for this input field! Internal error is logged!
        $currentErrPath[$foundTypeRule] = "This is unknown data type: '{$foundTypeRule}' in field '{$fullFieldName}'. Please tell the Developer about it since validation cannot continue without a valid data type provided!";
        $c['err']['UNKNOWN_VALIDATOR_DATA_TYPE_RULE'] = "Unknown Data Type Validation Rule: '{$foundTypeRule}' for field '{$fullFieldName}'.";
        $c['v_ok'] = false;
        return;
    }

    // Rule mappings based on data type categories
    $mappedRulesBasedTypeCategory = [
        'string_types' => [
            'min' => 'minlen',
            'max' => 'maxlen',
            'exact' => 'exactlen',
            'between' => 'betweenlen',
            'size' => 'sizelen',
        ],
        'number_types' => [
            'min' => 'minval',
            'max' => 'maxval',
            'exact' => 'exactval',
            'between' => 'betweenval',
            'size' => 'sizeval',
        ],
        'array_types' => [
            'count' => 'arraycount',
            'min' => 'mincount',
            'max' => 'maxcount',
            'exact' => 'exactcount',
            'between' => 'betweencount',
            'size' => 'sizecount',
        ],
        'complex_types' => [],
        'file_types' => [
            'min' => 'min_filesize',
            'max' => 'max_filesize',
            'exact' => 'exact_filesize',
            'between' => 'between_filesize',
            'size' => 'size_filesize',
        ],
    ];

    // ITERATING THROUGH REMAINING RULES THIS INPUT FIELD
    foreach ($rules as $rule => $ruleConfig) {
        $ruleValue = $ruleConfig['value'];
        $customErr = $ruleConfig['err_msg'];
        $errorKey = $rule;

        // Check if $rule is the mapped rule ($foundTypeCat['$foundTypeRule'])
        // and set $Rule to that value then before concatenating.
        // If the rule is not in the mapped rules, we just use it as is
        if (isset($mappedRulesBasedTypeCategory[$foundTypeCat][$rule])) {
            $rule = $mappedRulesBasedTypeCategory[$foundTypeCat][$rule];
        }

        // Dynamically call the validation function for this rule
        // Assuming your rule functions are named funk_validate_rule
        $validatorFn = 'funk_validate_' . $rule;
        //echo "Running `$validatorFn` for field `$fullFieldName` with value `" . (is_string($inputValue) ? $inputValue : json_encode($inputValue)) . "`\n";

        if (function_exists($validatorFn)) {
            // Pass current input value, rule value, and custom error
            $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);

            // Set the error message for this specific rule
            // if it is not null, meaning validation failed
            // Also stop remaining validation for
            // this input data if 'stop' is true!
            if ($error !== null) {
                $currentErrPath[$errorKey] = $error;
                $c['v_ok'] = false;

                // TODO: EXPERIMENTAL: Might not work as intended in all cases
                // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
                // Stop ALL Validation if "stop_all_on_first_error" key exists
                if (
                    isset($c['v_config']['stop_all_on_first_error'])
                ) {
                    $c['v_config']['stop_all_on_first_error'] = true;
                    return;
                }

                if ($stop) {
                    return;
                }
            }
        } else {
            // Handle unknown validator functions (e.g., log, add to $c['err'])
            $currentErrPath[$foundTypeRule] = "This is unknown data type: '{$foundTypeRule}' in field '{$fullFieldName}'. Please tell the Developer about it. Validation will continue though!";
            $c['err']["UNKNOWN_VALIDATOR_DATA_RULE-$foundTypeRule"] = "Unknown Data Validation Rule: '{$foundTypeRule}' for field '{$fullFieldName}'.";
            $c['v_ok'] = false;
        }
    }
};

// This is the improved version of funk_validation_recursively (RIP)
function funk_validation_recursively_improved(
    &$c,
    $inputData,
    array $validationRules,
    array &$currentErrPath,
    &$currentValidData
) {
    // Iterate through the main `return array()` from optimized validation array
    foreach ($validationRules as $DXKey => $rulesOrNestedFields) {
        // TODO: EXPERIMENTAL: Might not work as intended in all cases
        // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
        // If stop_all_on_first_error is true, we stop further validation
        // and return immediately with the current error path
        if (
            isset($c['v_config']['stop_all_on_first_error'])
            && $c['v_config']['stop_all_on_first_error'] === true
        ) {
            if (!empty($currentErrPath)) {
                $c['v'] = $currentErrPath;
            }
            return;
        }
        // Set "STOP_ALL_ON_FIRST_ERROR" key to true in $c['v_config']['stop_all_on_first_error']
        // as a global configuration for the validation process (including if it is a nested field)
        // Any error will check if this exists and set it to true and when true it all returns
        // TODO: EXPERIMENTAL: Might not work as intended in all cases
        // This is the "'<STOP_ALL_ON_FIRST_ERROR>' => true," root key!
        if ($DXKey === '<STOP_ALL_ON_FIRST_ERROR>') {
            $c['v_config']['stop_all_on_first_error'] = false;
            unset($validationRules[$DXKey]);
            continue;
        }

        // When root key is NOT "*" (but "key.*", "key" or "key.subkey" and so on!)
        if ($DXKey !== '*') {
            // Get current rules, input data|null and initialize current error path
            $currentRules = $rulesOrNestedFields['<RULES>'] ?? null;
            $currentInputData = $inputData[$DXKey] ?? null;
            $currentErrPath[$DXKey] = [];
            $currentValidData[$DXKey] = null;
            $wildCardExist = ($DXKey === '*' || key($rulesOrNestedFields) === '*');

            // If "<RULES>" node exists, we process it by passing it to the
            // funk_validation_validate_rules function which also receives
            // the current error path!
            if ($currentRules) {
                funk_validation_validate_rules(
                    $c,
                    $currentInputData,
                    $DXKey,
                    $rulesOrNestedFields['<RULES>'],
                    $currentErrPath[$DXKey],
                );
                // Remove the <RULES> key after processing
                // If no errors were found for this key, we can just remove it
                // We also set the data to the current valid data array
                unset($rulesOrNestedFields['<RULES>']);
                if (empty($currentErrPath[$DXKey])) {
                    unset($currentErrPath[$DXKey]);
                    $currentValidData[$DXKey] = $currentInputData;
                } else {
                    unset($currentValidData[$DXKey]);
                }
            }

            // If there still exists elements in the $rulesOrNestedFields we
            // can assume that they are nested fields or the * wildcard
            // but we first ONLY process the nested fields first
            if (
                is_array($rulesOrNestedFields)
                && !empty($rulesOrNestedFields)
                && !$wildCardExist
            ) {
                foreach ($rulesOrNestedFields as $name => $nestedField) {
                    // if ($name === '<RULES>' || $name === '*') {
                    //     continue; // Skip the <RULES> key if it exists
                    // } <- This might not be needed since we always checked, processed
                    // and unset it above in the currentRules check
                    if (is_array($nestedField) && $name !== '*') {
                        // If the nested field is an array, we can recurse into it
                        // and pass the current error path for this nested field
                        $currentErrPath[$DXKey][$name] = [];
                        $currentValidData[$DXKey][$name] = null;

                        funk_validation_recursively_improved(
                            $c,
                            $inputData[$DXKey] ?? null,
                            $rulesOrNestedFields ?? [],
                            $currentErrPath[$DXKey],
                            $currentValidData[$DXKey]
                        );
                    }
                }
                // After loop check if the error path is empty
                if (empty($currentErrPath[$DXKey])) {
                    unset($currentErrPath[$DXKey]);
                    // If no errors were found, we can set the valid data
                    $currentValidData[$DXKey] = $currentInputData;
                } else {
                    unset($currentValidData[$DXKey]);
                }
            }

            // Handle "*" wildcard for Numbered Arrays (works when they are in
            // the $wildCardExist = $rulesOrNestedFields, not $DXKey === '*' yet!)
            if ($wildCardExist) {
                $wildCardRules = $rulesOrNestedFields['*']["<RULES>"] ?? null;

                // If Rules found for Numbered Array * we pass on the rules to the
                // validation function and then set the current error path.
                // Only if it all passes do we actually start iterating through the numbered array
                $actualCount = (is_array($currentInputData)
                    && array_is_list($currentInputData)) ? count($currentInputData) : 0;

                // If Rules for Numbered Array * exist, we can validate it
                if ($wildCardRules) {
                    $currentErrPath[$DXKey] = [];
                    funk_validation_validate_rules(
                        $c,
                        $currentInputData,
                        $DXKey,
                        $wildCardRules,
                        $currentErrPath[$DXKey]
                    );

                    // Only if it is empty do we actually iterate
                    if (empty($currentErrPath[$DXKey])) {
                        echo "No errors found for `$DXKey` with Wildcard Rules!\n";
                        unset($currentErrPath[$DXKey]);
                        unset($rulesOrNestedFields['*']["<RULES>"]);

                        // We now extract the number of iterations
                        // from the Wildcard Rules array, which should exist
                        // otherwise we set to 0
                        $iterations = 0;
                        if (
                            isset($wildCardRules['count']['value'])
                        ) {
                            $iterations = (int)$wildCardRules['count']['value'] ?? 0;
                        } else if (isset($wildCardRules['count']['value'])) {
                            $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                        } else if (isset($wildCardRules['exact']['value'])) {
                            $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                        } else if (isset($wildCardRules['size']['value'])) {
                            $iterations = (int)$wildCardRules['size']['value'] ?? 0;
                        } else if (isset($wildCardRules['between']['value'])) {
                            $iterations = (int)$wildCardRules['between']['value'][1] ?? 0;
                        }

                        // If iterations is larger than the actual count,
                        // we can set it to the actual count so we do not
                        // iterate more than the actual number of elements
                        $iterations = ($iterations > 0) ? min($iterations, $actualCount) : $actualCount;

                        // REMOVE LATER
                        echo "SET ARR COUNT TO: $iterations\n";

                        // Now we can recurse into the validation function for this
                        // numbered array element when iterations is greater than 0!
                        if ($iterations > 0) {
                            for ($index = 0; $index < $iterations; $index++) {

                                $currentErrPath[$DXKey][$index] = [];
                                $currentValidData[$DXKey][$index] = null;
                                funk_validation_recursively_improved(
                                    $c,
                                    $currentInputData[$index] ?? null,
                                    $rulesOrNestedFields['*'],
                                    $currentErrPath[$DXKey][$index],
                                    $currentValidData[$DXKey][$index]
                                );
                                // Unset if no errors were found
                                if (empty($currentErrPath[$DXKey][$index])) {
                                    unset($currentErrPath[$DXKey][$index]);
                                    $currentValidData[$DXKey][$index] = $currentInputData[$index];
                                }
                                // Unset non-existing/invalid data
                                else {
                                    unset($currentValidData[$DXKey][$index]);
                                }
                            }
                            // Also unset for the main DXKey if no errors were found
                            if (empty($currentErrPath[$DXKey])) {
                                unset($currentErrPath[$DXKey]);
                                //HM? $currentValidData[$DXKey] = $currentInputData;
                            }
                            // Unset non-existing/invalid data
                            else {
                                unset($currentValidData[$DXKey]);
                            }
                        }
                    }
                }
                // We found Wildcard * Indicator but not the Rules
                // so throw possible misconfiguration error!
                else {
                    $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION-NUMBERED-ARRAY-' . $DXKey] = "Validation Function for `$DXKey` with Wildcard * found but no Rules were defined for it!";
                    $currentErrPath[$DXKey] = "Failed to Validate Numbered Array in `$DXKey`. Tell the Developer about it since this is possibly a misconfiguration in the so called `returned validation array()`!";
                }
            }
        }

        // MAYBE EXPERIMENTAL: Might not work as intended in all cases, but otherwise nicely done!!! ^_^
        // When root key IS "*" meaning everything is shifted to the left where the first key
        // is the wildcard "*" and the rest are the nested fields meaning all must be different.
        if ($DXKey === '*') {
            $currentInputData = $inputData ?? null;
            $currentErrPath[$DXKey] = [];
            $currentValidData[$DXKey] = null;
            $wildCardRules = $rulesOrNestedFields["<RULES>"] ?? null;

            // If Rules found for Numbered Array * we pass on the rules to the
            // validation function and then set the current error path.
            // Only if it all passes do we actually start iterating through the numbered array
            $actualCount = (is_array($currentInputData)
                && array_is_list($currentInputData)) ? count($currentInputData) : 0;

            // If Rules for Numbered Array * exist, we can validate it
            if ($wildCardRules) {
                $currentErrPath[$DXKey] = [];
                funk_validation_validate_rules(
                    $c,
                    $currentInputData,
                    $DXKey,
                    $wildCardRules,
                    $currentErrPath[$DXKey]
                );

                // Only if it is empty do we actually iterate
                if (empty($currentErrPath[$DXKey])) {
                    echo "[* as ROOT] No errors found for `$DXKey` with Wildcard Rules!\n";
                    unset($currentErrPath[$DXKey]);
                    unset($rulesOrNestedFields["<RULES>"]);
                    unset($currentValidData[$DXKey]); // Delete "$c['v_data']['*'] = null"

                    // We now extract the number of iterations
                    // from the Wildcard Rules array, which should exist
                    // otherwise we set to 0
                    $iterations = 0;
                    if (
                        isset($wildCardRules['count']['value'])
                    ) {
                        $iterations = (int)$wildCardRules['count']['value'] ?? 0;
                    } else if (isset($wildCardRules['count']['value'])) {
                        $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                    } else if (isset($wildCardRules['exact']['value'])) {
                        $iterations = (int)$wildCardRules['exact']['value'] ?? 0;
                    } else if (isset($wildCardRules['size']['value'])) {
                        $iterations = (int)$wildCardRules['size']['value'] ?? 0;
                    } else if (isset($wildCardRules['between']['value'])) {
                        $iterations = (int)$wildCardRules['between']['value'][1] ?? 0;
                    }

                    // If iterations is larger than the actual count,
                    // we can set it to the actual count so we do not
                    // iterate more than the actual number of elements
                    $iterations = ($iterations > 0) ? min($iterations, $actualCount) : $actualCount;

                    // Now we can recurse into the validation function for this
                    // numbered array element when iterations is greater than 0!
                    if ($iterations > 0) {
                        for ($index = 0; $index < $iterations; $index++) {

                            $currentErrPath[$index] = [];
                            $currentValidData[$index] = null;
                            funk_validation_recursively_improved(
                                $c,
                                $currentInputData[$index] ?? null,
                                $rulesOrNestedFields,
                                $currentErrPath[$index],
                                $currentValidData[$index]
                            );
                            // Unset if no errors were found
                            if (empty($currentErrPath[$index])) {
                                unset($currentErrPath[$index]);
                            }
                            // Unset non-existing/invalid data
                            else {
                                unset($currentValidData[$index]);
                            }
                        }
                        // TODO: Maybe is needed after all in special case
                        // when root is numbered array?
                        // Also unset for the main DXKey if no errors were found
                        // if (empty($currentErrPath[$DXKey])) {
                        //     unset($currentErrPath[$DXKey]);
                        // }
                    }
                }
            }
            // We found Wildcard * Indicator but not the Rules
            // so throw possible misconfiguration error!
            else {
                $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION-NUMBERED-ARRAY-' . $DXKey] = "Validation Function for `$DXKey` with Wildcard * found but no Rules were defined for it!";
                $currentErrPath[$DXKey] = "Failed to Validate Numbered Array in `$DXKey`. Tell the Developer about it since this is possibly a misconfiguration in the so called `returned validation array()`!";
            }
        }
    }
}

// The main validation function for validating data in FunkPHP
// mapping to the "$_GET"/"$_POST" or "php://input" (JSON) variable ONLY!
function funk_use_validation(&$c, $optimizedValidationArray, $source, $onlyDataIfAllValid = true)
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

    // TODO: REMOVE THIS LINE WHEN DONE TESTING
    // This is just for testing purposes to see the input data
    var_dump("TEST DATA(GET/POST/JSON):", $inputData);

    // Now we can run the validation recursively and
    $c['v_ok'] = true;
    $c['v'] = [];
    $c['v_data'] = [];
    funk_validation_recursively_improved(
        $c,
        $inputData,
        $optimizedValidationArray,
        $c['v'],
        $c['v_data'],
    );

    // When this is set to true, it means that the validation
    // function has passed and no errors were found/added to $c['v']
    // Its default value is null meaning either no validation was run
    // or it failed and no errors were found/added to $c['v'] before this!
    // If validation passed, we can set the $c['v'] to null again
    if ($c['v_ok']) {
        $c['v'] = null;
        return true;
    }

    // Clear Valid Data Array if Validation failed but
    // only if "onlyDataIfAllValid" is set to true!
    if ($onlyDataIfAllValid) {
        $c['v_data'] = null;
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

// Validate that Input Data is a single character string (either any or
// based on validationValues) and is not empty meaning whitespace is not allowed
function funk_validate_char($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || strlen($inputData) !== 1) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a single character.";
    }
    // Only use validationValues if they are set
    // and are not empty, otherwise skip this check
    if (isset($validationValues)) {
        $validationValues = is_string($validationValues)
            ? [$validationValues] : $validationValues;

        // Check that single input character is in the allowed characters
        if (!in_array($inputData, $validationValues, true)) {
            $allowedChars = implode(', ', $validationValues);
            return (isset($customErr) && is_string($customErr))
                ? $customErr
                : "$inputName must be one of the following characters: $allowedChars.";
        }
    }

    return null;
}

// Validate that Input Data is a valid single digit (either
// any digit or based on validationValues)
function funk_validate_digit($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!preg_match('/^\d$/', $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a single digit (0-9).";
    }
    if (isset($validationValues)) {
        $validationValues = is_string($validationValues)
            ? [$validationValues] : $validationValues;

        // Check that single input is in the allowed digits
        if (!in_array($inputData, $validationValues, true)) {
            $allowedChars = implode(', ', $validationValues);
            return (isset($customErr) && is_string($customErr))
                ? $customErr
                : "$inputName must be one of the following digits: $allowedChars.";
        }
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
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a float number (must end with a decimal point).";
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

// Validate that Input Data unchecked in a boolean way
function funk_validate_unchecked($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        $inputData === false ||
        $inputData === 0 ||
        $inputData === "0" ||
        $inputData === "off" ||
        $inputData === "no" ||
        $inputData === "nej" || // Swedish easter egg
        $inputData === "false" ||
        $inputData === "unchecked" ||
        $inputData === "disabled" ||
        $inputData === "unselected"
    ) {
        return null;
    } else {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be unchecked in one way or another.";
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
// IMPORTANT: The regex unfortunately cannot match "@[a-zA-Z]\.[a-zA-Z]{2,}" meaning
// when there is just a single character before the dot and at least 2 characters after it!
function funk_validate_email($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
    }
    // IMPORTANT: This regex unfortunately cannot match "@[a-zA-Z]\.[a-zA-Z]{2,}" meaning
    // when there is just a single character before the dot and at least 2 characters after it!
    if (!preg_match('/^(?!.*\.\.)[a-zA-Z0-9](?:[a-zA-Z0-9._%+-]*[a-zA-Z0-9])?@(?:[a-zA-Z0-9](?!.*--)[a-zA-Z0-9-]*[a-zA-Z0-9]\.)+[a-zA-Z]{2,}$/', $inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
    }
    // Run optional additional validation if provided
    // Currently "tld" & "dns" are supported
    if (isset($validationValues)) {
        if (is_string($validationValues)) {
            $validationValues = [$validationValues];
        }
        // Run 'tld' if it is set in the validation values
        // where we check if the domain ends with valid TLD
        if (in_array('tld', $validationValues, true)) {
            $domain = strtolower(substr(strrchr($inputData, '@'), 1));
            // We now include "VALID_TLDS_TOP100" array which is prefined with top 100 tlds endings
            // and we loop through it to check if the domain ends with a valid TLD
            $validTldsTop100 = dirname(dirname(__DIR__)) . '/config/VALID_TLDS_TOP100.php';
            $validTldsTopAll = dirname(dirname(__DIR__)) . '/config/VALID_TLDS_ALL.php';
            $allTop100 = include_once $validTldsTop100;

            // Iterate through the top 100 TLDs to
            // check domain ends with a valid TLD
            $isValidTld = false;
            foreach ($allTop100 as $tld) {
                if (str_ends_with($domain, $tld)) {
                    $isValidTld = true;
                    break;
                }
            }
            // Only iterate all TLDs if
            // the top 100 did not match
            if (!$isValidTld) {
                $allTlds = include_once $validTldsTopAll;
                foreach ($allTlds as $tld) {
                    if (str_ends_with($domain, $tld)) {
                        $isValidTld = true;
                        break;
                    }
                }
            }
            // If the domain does not end with
            // a valid TLD, return an error
            if (!$isValidTld) {
                return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
            }
            // if it is valid then the optional DNS might run
            // instead or it will just return null (= no error)
        }
        // Run 'dns' if it is set in the validation values
        // where we check if the domain has a valid DNS record
        if (in_array('dns', $validationValues, true)) {
            $domain = substr(strrchr($inputData, '@'), 1);
            if (
                !checkdnsrr($domain, 'MX') // Check for MX records first (Mail Exchange)
                && !checkdnsrr($domain, 'A') // Check for A records (IPv4)
                && !checkdnsrr($domain, 'AAAA') // Check for AAAA records (IPv6)
            ) {
                return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
            }
        }
    }
    return null;
}

// Validate that Input Data is a valid email address by using the validationValue
// which should be a custom validation function name OR a regex pattern
function funk_validate_email_custom($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
    }
    // If validationValues is a string, we assume it is a regex pattern
    if (is_string($validationValues)) {
        if (!preg_match($validationValues, $inputData)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
        }
    } elseif (is_callable($validationValues)) {
        // If validationValues is a callable function, we call it
        $result = call_user_func($validationValues, $inputData);
        if ($result !== true) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
        }
    } else {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid email address.";
    }
    return null;
}

// Validate that Input Data is a string meaning it can be hashed as a password later.
// IMPORTANT: This does NOT validate the password strength, length, etc.! It only "signals"
// to the Validation system that a valid string field should be hashed as a password later.
// This is primarily for optional password fields that can be left empty
function funk_validate_password_hash($inputName, $inputData, $validationValues, $customErr = null)
{
    return null;
}

// Validate that Input Data is a valid password where the values in $validationValues
// the first value is the number of lowercases required in the password, the second value
// is the number of uppercases required in the password, and the third value is number of digits
// and the fourth value is the number of special characters required in the password
function funk_validate_password($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid password.";
    }
    // Convert to array if validationValues is a string
    if (is_string($validationValues)) {
        $validationValues = [$validationValues];
    }

    // We now use regex to validate the password where the first valuue
    // is the number of lowercases, the second value is the number of uppercases,
    // the third value is the number of digits, and the fourth value is the number of special characters
    $lowercaseCount = isset($validationValues[0]) ? (int)$validationValues[0] : 0;
    $uppercaseCount = isset($validationValues[1]) ? (int)$validationValues[1] : 0;
    $digitCount = isset($validationValues[2]) ? (int)$validationValues[2] : 0;
    $specialCharCount = isset($validationValues[3]) ? (int)$validationValues[3] : 0;

    // Count the number of lowercases first!
    $lowercasePattern = '/[a-z]/';
    if ($lowercaseCount > 0) {
        preg_match_all($lowercasePattern, $inputData, $matches);
        if (count($matches[0]) < $lowercaseCount) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $lowercaseCount lowercase letters.";
        }
    }

    // Count the number of uppercases next!
    $uppercasePattern = '/[A-Z]/';
    if ($uppercaseCount > 0) {
        preg_match_all($uppercasePattern, $inputData, $matches);
        if (count($matches[0]) < $uppercaseCount) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $uppercaseCount uppercase letters.";
        }
    }

    // Count the number of digits next!
    $digitPattern = '/[0-9]/';
    if ($digitCount > 0) {
        preg_match_all($digitPattern, $inputData, $matches);
        if (count($matches[0]) < $digitCount) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $digitCount digits.";
        }
    }

    // Count the number of special characters next!
    // YOU CAN CHANGE THE SPECIAL CHARACTERS TO YOUR LIKING!
    // Change below what are considered default special characters!
    $specialCharPattern = '/[!@#$%^&*()[\]\.,`?"\':{}|<>~]/';

    if ($specialCharCount > 0) {
        preg_match_all($specialCharPattern, $inputData, $matches);
        if (count($matches[0]) < $specialCharCount) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $specialCharCount special characters.";
        }
    }

    return null;
}

// Validate that Input Data is a valid password confirmation
function funk_validate_password_confirm($inputName, $inputData, $validationValues, $customErr = null)
{
    // Check both are strings and then compare them
    if (!is_string($inputData) || !is_string($validationValues)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid password confirmation.";
    }
    if ($inputData !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must match the original password.";
    }
}

// Validate that Input Data is a valid password with custom validation where $validationValues
// is the name of the custom validation function that will be called
function funk_validate_password_custom($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid password.";
    }
    if (is_callable($validationValues)) {
        $result = call_user_func($validationValues, $inputData);
        if ($result !== true) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid password.";
        }
    } else {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid password. Also, tell the Developer of the website that the Custom Password Validation Function was not found!";
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

// This function is here so that "stop_all_on_first_error" can be used as a validation rule.
// It stops ALL validation rules from running on the first error found. When compiled,
// it is added as the first root key as "<'STOP'>" before any other root keys!
function funk_validate_stop_all_on_first_error($inputName, $inputData, $validationValues, $customErr = null) {};

// Validate that Input Data is a valid stop condition which means stop running any rules
// if this rule is found in the validation rules and when any error occurs for a given field!
function funk_validate_stop($inputName, $inputData, $validationValues, $customErr = null) {};

// "Field" rule is just so you can specify what a field should be called when showing
// for the end-user and is never really used for validation purposes. End-user sees this if used!
// instead of the $inputName which is usually a key in $_POST/$_GET/JSON
function funk_validate_field($inputName, $inputData, $validationValues, $customErr = null) {};

// Validate that Input Data is of valid minimal length provided in $validationValues
// This is used ONLY for string inputs. This is "min" when it knows it is a string.
function funk_validate_minlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strlen($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at least $validationValues characters long.";
    }
    return null;
}

// Validate that Input Data is of valid maximum length provided in $validationValues
// This is used ONLY for string inputs. This is "max" when it knows it is a string.
function funk_validate_maxlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strlen($inputData) > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at most $validationValues characters long.";
    }
    return null;
}

// Validate that Input Data is of valid length provided in $validationValues
// This is used ONLY for string inputs. This is "between" when it knows it is a string.
function funk_validate_betweenlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        !is_string($inputData)
        || (mb_strlen($inputData) < $validationValues[0]
            || mb_strlen($inputData) > $validationValues[1])
    ) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be inclusively between {$validationValues[0]} and {$validationValues[1]} characters long.";
    }
    return null;
}

// Validate that Input Data is of valid minimum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "min" when it knows it is a number.
function funk_validate_minval($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_numeric($inputData) || $inputData < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at least $validationValues in value.";
    }
    return null;
}

// Validate that Input Data is of valid maximum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "max" when it knows it is a number.
function funk_validate_maxval($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_numeric($inputData) || $inputData > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be at most $validationValues in value.";
    }
    return null;
}

// Validate that Input Data is of valid minimum and maximum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "between" when it knows it is a number.
function funk_validate_betweenval($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        !is_numeric($inputData)
        || ($inputData < $validationValues[0]
            || $inputData > $validationValues[1])
    ) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be inclusively between {$validationValues[0]} and {$validationValues[1]} in value.";
    }
    return null;
}

// Validate that Input Data's array has minimum number of elements as in $validationValues
// This is used ONLY for array inputs. This is "min" when it knows it is a array.
function funk_validate_mincount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || count($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have at least $validationValues elements.";
    }
    return null;
}

// Validate that Input Data's array has maximum number of elements as in $validationValues
// This is used ONLY for array inputs. This is "max" when it knows it is a array.
function funk_validate_maxcount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || count($inputData) > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have at most $validationValues elements.";
    }
    return null;
}

// Validate that Input Data's array has minimum and maximum number of elements as in $validationValues
// This is used ONLY for array inputs. This is "between" when it knows it is a array.
function funk_validate_betweencount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (
        !is_array($inputData)
        || (count($inputData) < $validationValues[0]
            || count($inputData) > $validationValues[1])
    ) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have inclusively between {$validationValues[0]} and {$validationValues[1]} elements.";
    }
    return null;
}

// Validate that Input Data is of valid maximum value provided in $validationValues
// This is used ONLY for numerical inputs. This is "max" when it knows it is a number.
function funk_validate_exactval($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_numeric($inputData) || $inputData !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be exactly $validationValues in value.";
    }
    return null;
}
// Alias of "funk_validate_exactval"
function funk_validate_sizeval($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_numeric($inputData) || $inputData !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have a fixed value size of $validationValues in value.";
    }
    return null;
}

// Validate that Input Data is of valid exact length provided in $validationValues meaning
// it must be that length and not less or more. This is used ONLY for string inputs.
function funk_validate_exactlen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strlen($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be exactly $validationValues characters long.";
    }
    return null;
}
// Alias of "funk_validate_exactlen"
function funk_validate_sizelen($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strlen($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have a fixed size of $validationValues characters long.";
    }
    return null;
}

// Validate that Input Data's array has an exact number of elements as in $validationValues
// This is used ONLY for array inputs. This is "max" when it knows it is a array.
function funk_validate_exactcount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || count($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have exactly $validationValues elements.";
    }
    return null;
}
// Alias of "funk_validate_exactcount"
function funk_validate_sizecount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || count($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have a fixed size of $validationValues elements.";
    }
    return null;
}
// Alias of "funk_validate_exactcount"
function funk_validate_arraycount($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData) || count($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "Array $inputName must have a count of $validationValues elements.";
    }
    return null;
}

// Validate that Input Data is of valid maximum number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "min_digits" when it knows it is a number.
function funk_validate_min_digits($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_int($inputData) || strlen($inputData) < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $validationValues digits.";
    }
    return null;
}

// Validate that Input Data is of valid maximum number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "max_digits" when it knows it is a number.
function funk_validate_max_digits($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_int($inputData) || strlen($inputData) > $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at most $validationValues digits.";
    }
    return null;
}

// Validate that Input Data is of valid minimum and maximum number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "between_digits" when it knows it is a number.
function funk_validate_digits_between($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_int($inputData) || strlen($inputData) <= $validationValues[0] || strlen($inputData) >= $validationValues[1]) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have inclusively between {$validationValues[0]} and {$validationValues[1]} digits.";
    }
    return null;
}

// Validate that Input Data is of valid exact number of digits as in $validationValues
// This is used ONLY for numerical inputs. This is "digits" when it knows it is a number.
function funk_validate_digits($inputName, $inputData, $validationValues, $customErr = null)
{
    $regex = '/^\d+$/'; // Regex to check if input is a string of digits
    if (!is_int($inputData) || strlen($inputData) !== $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have exactly $validationValues digits.";
    }
    return null;
}

// Validate that Input Data is a valid hex color code
// This function checks if the input is a valid hex color code in the format #RRGGBB or #RGB
function funk_validate_color($inputName, $inputData, $validationValues, $customErr = null)
{
    // Run defasult validation if no $validationValues are provided (#RRGGBB)
    if (!isset($validationValues)) {
        if (!preg_match('/^#([a-fA-F0-9]{6})$/', $inputData)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid six hexadecimal color code.";
        } else {
            return null;
        }
    }

    // Prepare compatible patterns for different color formats that are supported
    $colorPatterns = [
        // #RRGGBB
        'hex6'      => '/^#([a-fA-F0-9]{6})$/',
        // #RGB (shorthand)
        'hex3'      => '/^#([a-fA-F0-9]{3})$/',

        // RGB: rgb(R, G, B) - R, G, B are integers 0-255, possibly with spaces
        // Allowing percentages too, but typically for (0-255)
        // RGBA: rgba(R, G, B, A) - A is float 0-1 or percentage
        'rgb'       => '/^rgb\(\s*((\d{1,3})\s*,\s*){2}(\d{1,3})\s*\)$/',
        'rgba'      => '/^rgba\(\s*((\d{1,3})\s*,\s*){2}(\d{1,3})\s*,\s*((0(\.\d+)?|1(\.0+)?|\d{1,2}%|100%))\s*\)$/',

        // HSL: hsl(H, S%, L%) - H is 0-360, S, L are 0-100%
        // HSLA: hsla(H, S%, L%, A) - A is float 0-1 or percentage
        'hsl'       => '/^hsl\(\s*((\d{1,3}|360)\s*,\s*){1}((\d{1,3}%)\s*,\s*){1}(\d{1,3}%)\s*\)$/',
        'hsla'      => '/^hsla\(\s*((\d{1,3}|360)\s*,\s*){1}((\d{1,3}%)\s*,\s*){1}(\d{1,3}%)\s*,\s*((0(\.\d+)?|1(\.0+)?|\d{1,2}%|100%))\s*\)$/',

        // CSS Color Keywords (e.g., "red", "blue", "transparent")
        'names'   => '/^(rebeccapurple|aliceblue|antiquewhite|aqua|aquamarine|azure|beige|bisque|black|blanchedalmond|blue|blueviolet|brown|burlywood|cadetblue|chartreuse|chocolate|coral|cornflowerblue|cornsilk|crimson|cyan|darkblue|darkcyan|darkgoldenrod|darkgray|darkgreen|darkgrey|darkkhaki|darkmagenta|darkolivegreen|darkorange|darkorchid|darkred|darksalmon|darkseagreen|darkslateblue|darkslategray|darkslategrey|darkturquoise|darkviolet|deeppink|deepskyblue|dimgray|dimgrey|dodgerblue|firebrick|floralwhite|forestgreen|fuchsia|gainsboro|ghostwhite|gold|goldenrod|gray|green|greenyellow|grey|honeydew|hotpink|indianred|indigo|ivory|khaki|lavender|lavenderblush|lawngreen|lemonchiffon|lightblue|lightcoral|lightcyan|lightgoldenrodyellow|lightgray|lightgreen|lightgrey|lightpink|lightsalmon|lightseagreen|lightskyblue|lightslategray|lightslategrey|lightsteelblue|lightyellow|lime|limegreen|linen|magenta|maroon|mediumaquamarine|mediumblue|mediumorchid|mediumpurple|mediumseagreen|mediumslateblue|mediumspringgreen|mediumturquoise|mediumvioletred|midnightblue|mintcream|mistyrose|moccasin|navajowhite|navy|oldlace|olive|olivedrab|orange|orangered|orchid|palegoldenrod|palegreen|paleturquoise|palevioletred|papayawhip|peachpuff|peru|pink|plum|powderblue|purple|red|rosybrown|royalblue|saddlebrown|salmon|sandybrown|seagreen|seashell|sienna|silver|skyblue|slateblue|slategray|slategrey|snow|springgreen|steelblue|tan|teal|thistle|tomato|turquoise|violet|wheat|white|whitesmoke|yellow|yellowgreen|transparent)$/i',
    ];

    // We now loop through the array of $validationValues and use the preg_match function
    if (is_string($validationValues)) {
        $validationValues = [$validationValues];
    }
    foreach ($validationValues as $value) {
        if (isset($colorPatterns[$value]) && preg_match($colorPatterns[$value], $inputData)) {
            return null; // Valid color format found
        }
    }
    // Here we return an error if no valid color format was found when $validationValues were provided
    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a valid color code in one of the supported formats: " . implode(', ', array_keys($colorPatterns)) . ".";
}

// Validate that Input Data is in uppercase, must be combiend with string validation
function funk_validate_lowercase($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData) || mb_strtolower($inputData) !== $inputData) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be in lowercase.";
    }
    return null;
}

// Validate that Input Data has a number of lowercases as specified in $validationValues
// This function checks if the input data is a string and if it contains the specified number of lowercases.
function funk_validate_lowercases($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a string.";
    }
    $lowercaseCount = preg_match_all('/[a-z]/', $inputData);
    if ($lowercaseCount < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $validationValues lowercase letters (a-z).";
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

// Validate that Input Data has a number of uppercases as specified in $validationValues
// This function checks if the input data is a string and if it contains the specified number of uppercases.
function funk_validate_uppercases($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a string.";
    }
    $lowercaseCount = preg_match_all('/[A-Z]/', $inputData);
    if ($lowercaseCount < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $validationValues uppercase letters (A-Z).";
    }
    return null;
}

// Validate that Input Data is has a certain number of digits as specified in $validationValues
// This function checks if the input data is a string and if it contains the specified number of digits.
function funk_validate_numbers($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a string.";
    }
    $digitCount = preg_match_all('/[0-9]/', $inputData);
    if ($digitCount < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $validationValues digits (0-9).";
    }
    return null;
}

// Validate that Input Data is has a certain number of special characters as specified in $validationValues
// This function checks if the input data is a string and if it contains the specified number of special characters.
function funk_validate_specials($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_string($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a string.";
    }
    // Define the special characters you want to check for
    // CHANGE IF NEEDED (ADDING OR REMOVING BELOW!)
    $specialChars = '!@#$%^&*()_+[]{}|;:,.<>?';
    $specialCharCount = 0;

    // Count the number of special characters in the input data
    for ($i = 0; $i < mb_strlen($inputData); $i++) {
        if (strpos($specialChars, mb_substr($inputData, $i, 1)) !== false) {
            $specialCharCount++;
        }
    }
    if ($specialCharCount < $validationValues) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have at least $validationValues valid special characters - any of these: `$specialChars`";
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

// Validate that Input Data's array all values are evaluated as arrays.
function funk_validate_elements_all_arrays($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_array($value)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only arrays!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as lists (numbered arrays).
function funk_validate_elements_all_lists($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_array($value) || array_keys($value) !== range(0, count($value) - 1)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be a numbered array!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as strings.
function funk_validate_elements_all_strings($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_string($value)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only strings!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as numbers (int, float, numeric).
function funk_validate_elements_all_numbers($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_numeric($value) || !is_int($value) || !is_float($value)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only numbers!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as INTEGERS (whole numbers)
function funk_validate_elements_all_integers($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_int($value)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only integers!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as FLOATS (decimal numbers)
function funk_validate_elements_all_floats($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_float($value)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only decimal numbers!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as BOOLEANS (true/false, 1/0, "1"/"0")
function funk_validate_elements_all_booleans($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_bool($value) && !in_array($value, [true, false, 1, 0, "1", "0"], true)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only booleans!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as CHECKED (true, 1, "1", "on", "yes", etc.)
function funk_validate_elements_all_checked($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (
            $value !== true &&
            $value !== 1 &&
            $value !== "1" &&
            $value !== "on" &&
            $value !== "yes" &&
            $value !== "ja" && // Swedish easter egg
            $value !== "true" &&
            $value !== "checked" &&
            $value !== "enabled" &&
            $value !== "selected"
        ) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only checked values!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as UNCHECKED (false, 0, "0", "off", "no", etc.)
function funk_validate_elements_all_unchecked($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (
            $value !== false &&
            $value !== 0 &&
            $value !== "0" &&
            $value !== "off" &&
            $value !== "no" &&
            $value !== "nej" && // Swedish easter egg
            $value !== "false" &&
            $value !== "unchecked" &&
            $value !== "disabled" &&
            $value !== "unselected"
        ) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only unchecked values!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as NULL
function funk_validate_elements_all_nulls($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_null($value)) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only null values!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are evaluated as single characters (strings of length 1)
function funk_validate_elements_all_chars($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    foreach ($inputData as $key => $value) {
        if (!is_string($value) || mb_strlen($value) !== 1) {
            return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array with only single character strings!";
        }
    }
    return null;
}

// Validate that Input Data's array all values are the data type in the following order stored in $validationValues
// for example, if $validationValues is ['string', 'number', 'boolean'], then the first value in the array must be a string,
// the second value must be a number, and the third value must be a boolean. This is used for validating arrays of mixed types.
// This also implies the count based on the number of elements in $validationValues!
function funk_validate_elements_this_type_order($inputName, $inputData, $validationValues, $customErr = null)
{
    if (!is_array($inputData)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must be an array.";
    }
    if (count($inputData) !== count($validationValues)) {
        return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName must have exactly " . count($validationValues) . " elements.";
    }
    foreach ($inputData as $key => $value) {
        $expectedType = $validationValues[$key];
        switch ($expectedType) {
            case 'char':
                if (!is_string($value) || mb_strlen($value) !== 1) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be a single string character.";
                }
                break;
            case 'null':
                if (!is_null($value)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be null.";
                }
                break;
            case 'string':
                if (!is_string($value)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be a string.";
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be a number.";
                }
                break;
            case 'boolean':
                if (!is_bool($value) && !in_array($value, [true, false, 1, 0, "1", "0"], true)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be a boolean.";
                }
                break;
            case 'array':
                if (!is_array($value)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be an array.";
                }
                break;
            case 'list':
                if (!is_array($value) || array_keys($value) !== range(0, count($value) - 1)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be a numbered array.";
                }
                break;
            case 'integer':
                if (!is_int($value)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be an integer.";
                }
                break;
            case 'float':
                if (!is_float($value)) {
                    return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName element at index $key must be a float.";
                }
                break;
            default:
                return (isset($customErr) && is_string($customErr)) ? $customErr : "$inputName has an invalid type '$expectedType' for element at index $key.";
        }
    }
}

// TODO: Fix
// Validate that specific value DOES EXIST in a specific database table=>column
function funk_validate_exists($inputName, $inputData, $validationValues, $customErr = null) {}

// Validate that specific value Does NOT EXIST in a specific database table=>column (thus unique)
function funk_validate_unique($inputName, $inputData, $validationValues, $customErr = null) {}
