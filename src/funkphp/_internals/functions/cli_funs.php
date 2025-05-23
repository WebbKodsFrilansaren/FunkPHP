<?php

// Function takes a dotted string (e.g. "test.email" or "test.user.name")
// and converts it to a key with subkeys (e.g. ["test" => "email"]
// or ["test" => ["user" => "name"]])
function convert_dotted_string_to_key_with_subkeys($dottedString)
{
    // Check if the input is a valid string
    if (!is_string_and_not_empty($dottedString)) {
        cli_err_syntax("[convert_dotted_string_to_key_with_subkeys] Input for `\$dottedString` must be a non-empty string!");
    }

    // Regex that checks that it is a valid dotted string meaning it has at least one any character
    // that is NOT a dot and then one dot and then any character that is NOT a dot and so on.
    if (!preg_match("/^(([a-z_])([a-z_0-9]+)\.([a-z_0-9]+))(\.([a-z_0-9]+))*$/", $dottedString)) {
        cli_err_syntax("[convert_dotted_string_to_key_with_subkeys] Invalid dotted string format in `$dottedString`! Must be like: \"test.email\" or \"test_with_underscored_and_numbers1337.user.name\"!");
    }

    // Split the dotted string into parts
    $parts = explode('.', $dottedString);
    $result = [];
    $count = count($parts);
    $counter = 0;

    // Initialize the current reference to the result array
    $current = &$result;

    // Loop through each part and create nested arrays
    foreach ($parts as $part) {
        if (!is_string_and_not_empty($part)) {
            cli_err_syntax("[convert_dotted_string_to_key_with_subkeys] Each `\$part` of the dotted string must be a non-empty string!");
        }
        $current = &$current[$part];
    }

    return $result;
}
// Function that takes a value and sets it in a nested array
function set_value_in_nested_array(&$value) {}

// Function that creates a regex pattern to match a function name
// inside of a typical handler file with a handler function name!
// Such as:
// "function handlertype_functionName(&$c) // <METHOD/route>
// {
// <anything goes as long it is inside of the function and indented!>
// };"
function get_match_function_regex($fnName)
{
    // Check if the function name is valid
    if (!is_string_and_not_empty($fnName)) {
        cli_err_syntax("[cli_match_function_part] Function name must be a non-empty string!");
    }
    if (!preg_match("/^[a-z_][a-z0-9_]+$/", $fnName)) {
        cli_err_syntax("[cli_match_function_part] \"$fnName\" must use this string syntax: `[a-z_][a-z0-9_]+`!");
    }

    // Create regex pattern based on method and route
    // It can find things like (ignore the quotes):
    // "function post_user(&$c) // <POST/user>
    // {
    // };"
    // The matching is only valid if after "};" there is a new line otherwise it will be invalid!
    $regex = '/^function (' . $fnName . ')\(\&\$c\)\s*\/\/ <([A-Z]+)\/([a-z0-9_:\-\/]*)>\s*$.*?^};$/ims';
    return $regex;
}

// Function creates a regex pattern that can return the typical
// validation syntax rules that could be provided like:
// "'required("Name is required!")', 'string', 'min:3',
// min:6("Email must be at least six(6) characters long!")"
function get_rules_regex($type)
{
    // The different types of rules syntaxes that can be used
    if ($type === "ONLY_RULE_NAME") {
        return '/^([a-z_][a-z_0-9]+)$/';
    } elseif ($type === "ONLY_RULE_NAME_AND_VALUE") {
        return '/^([a-z_][a-z_0-9]+):(.+)$/';
    } elseif ($type === "ONLY_RULE_NAME_AND_ERROR") {
        return '/^([a-z_][a-z_0-9]+)\("([^"]+)"\)$/';
    } elseif ($type === "ONLY_RULE_NAME_AND_VALUE_AND_ERROR") {
        return '/^([a-z_][a-z_0-9]+):(.+)\("([^"]+)"\)$/';
    }
    // Here we error out since we did not find a valid type
    cli_err_syntax("[get_rules_regex - probably called by 'cli_convert_simple_validation_rules_to_optimized_validation()'] Type must be one of the following: 'ONLY_RULE_NAME', 'ONLY_RULE_NAME_AND_VALUE', 'ONLY_RULE_NAME_AND_ERROR' or 'ONLY_RULE_NAME_AND_VALUE_AND_ERROR'");
}

// Same as "get_match_function_regex" but it maches all functions
// like:"function handlertype_functionName(&$c) // <METHOD/route>"
// in order to know if the entire file now should be removed!
function get_match_all_functions_regex($handlerType)
{
    // Check if the function name is valid
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[get_match_all_functions_regex] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }

    // Create regex pattern based on method and route
    // It can find things like (ignore the quotes):
    // "function post_user(&$c) // <POST/user>
    // {
    // };"
    // The matching is only valid if after "};" there is a new line otherwise it will be invalid!
    $regex = '/^function (' . $handlerType . '_[a-z0-9_]+)\(\&\$c\)\s*\/\/ <([A-Z]+)\/([a-z0-9_:\-\/]*)>\s*$.*?^};$/ims';
    return $regex;
}

// Same as "get_match_all_functions_regex" but without
// capture groups besides entire matches of functions.
function get_match_all_functions_regex_without_capture_groups($handlerType)
{
    // Check if the function name is valid
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[get_match_all_functions_regex] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }

    // Create regex pattern based on method and route
    // It can find things like (ignore the quotes):
    // "function post_user(&$c) // <POST/user>
    // {
    // };"
    // The matching is only valid if after "};" there is a new line otherwise it will be invalid!
    $regex = '/^function ' . $handlerType . '_[a-z0-9_]+\(\&\$c\)\s*\/\/ <[A-Z]+\/[a-z0-9_:\-\/]*>\s*$.*?^};$/ims';
    return $regex;
}

// Function that creates a regex pattern
// to match the return function such as:
// "return function (&$c, $handler = "<defaultFunctionName>") {
// $handler($c);
// };"
function get_match_return_function_regex($fnName, $method, $route)
{
    // Check if the function name is valid
    if (!is_string_and_not_empty($fnName)) {
        cli_err_syntax("[get_match_return_function_regex] Function name must be a non-empty string!");
    }
    // Check if the method is valid
    if (!is_string_and_not_empty($method)) {
        cli_err_syntax("[get_match_return_function_regex] Method must be a non-empty string!");
    }
    // Check if the route is valid
    if (!is_string_and_not_empty($route)) {
        cli_err_syntax("[get_match_return_function_regex] Route must be a non-empty string!");
    }
    return '/^(return function)\s*\(&\$c, \$handler\s*=\s*.+$.*?^};/ims';
};

// Function that creates a regex pattern to match the $DX = []; part of
// the function inside of a Validation Handler file with a Function Name!
// Such as:
//     "$DX = [
//     "<Any Valid Array Data Structure>"
//     "];"
function get_match_dx_function_regex()
{
    // This matches "$DX = [multi-lines possible];" part
    // of the function within the provided regex string!
    return '/\$DX\s*=\s*\[.*?];$/ims';
};

// Function that creates a regex for the "return array();"
// whose default value is "return array([]);" and then after
// compilation it should contain the actual validation rules
function get_match_dx_return_regex()
{
    // This matches "return array(<whatever on multi-lines is inside>);"
    // where it MUST end on ");\n" or it is considered not matched!
    return '/return\s*array\(.*?\);$\n/ims';
}

// Function takes a table which is an array of columns and then
// generates a validation file based on the table structure where
// it uses the values from the data type, default value, typical default
// values for the data type, and the min and max values for the data type, etc.
function cli_generate_a_validation_from_a_table($table = null)
{
    // Load globals and verify $table is not empty and the root key is only one which is the table name
    global $dirs, $exactFiles, $settings, $argv, $tablesAndRelationshipsFile, $mysqlDataTypesFile;
    $validatedTable = [];
    $validationDir = $dirs['validations'];
    $validSQLDataTypes = $mysqlDataTypesFile;

    // Check if $table is null meaning we should include the tables.php file
    // and check if it exists and is readable and then whether a table inside of it
    // matches an argv passed through the FunkCLI command which should be the table name
    // in the form of "tablename" (all lowercase) and then we will parse it
    if ($table === null) {
        if (file_exists_is_readable_writable($exactFiles['tables'])) {
            $table = $tablesAndRelationshipsFile;
        } else {
            cli_err_syntax("The \"funkphp/config/tables.php\" file must exist and be readable!");
        }
        if (!isset($argv[3]) || !is_string_and_not_empty(trim($argv[3]))) {
            cli_err_syntax("Provide a Table name as a string!");
        }
        // Regex means that "table_name" is valid whereas "_table_name"|"1_table_name" are invalid!
        if (!preg_match("/^[a-z_][a-z0-9_]+$/", $argv[3])) {
            cli_err_syntax("\"$argv[3]\" must begin with a lowercased letter and then only contain lowercased letters, numbers and underscores!");
        }
        // Here we have a valid $table name so we check if it exists in the tables.php file
        if (!is_array($table) || !isset($table['tables'][$argv[3]])) {
            cli_err_syntax("Table \"$argv[3]\" not found in \"funkphp/config/tables.php\"!");
        }
        $tableCols = $table['tables'][$argv[3]];
        $table = [$argv[3] => $tableCols];
    }

    // Check if the $table is an array and not empty and that the root key is only one which is the table name
    is_array_and_not_empty($table) or cli_err_syntax("The provided Table must be a non-empty array!");
    if (count($table) !== 1) {
        cli_err_syntax("Root key should be only the Table name for the provided non-empty Table array!");
    }

    // Grab the table name from the root key
    $tableName = key($table);

    // Check if the file with the table name already exists and if it does then we will rename it do
    // "tableName_old.php"so we can use this new one instead but still keep the old one for reference!
    $validationFile = $validationDir . $tableName . ".php";
    if (!dir_exists_is_readable_writable($validationDir)) {
        cli_err_syntax("The \"funkphp/validations/\" directory must exist, be readable and writable!");
    }
    if (file_exists_is_readable_writable($validationFile)) {
        $oldValidationFile = $validationDir . $tableName . "_old.php";
        // Delete the old file if it exists and is readable and writable
        if (file_exists_is_readable_writable($oldValidationFile)) {
            unlink($oldValidationFile);
        }
        // Rename the old file to the new one
        if (!rename($validationFile, $oldValidationFile)) {
            cli_err_syntax("Failed to rename \"$tableName\" to \"$tableName.php\"!");
        } else {
            cli_info_without_exit("Renamed \"$tableName\" to \"$tableName" . "_old.php\"!");
        }
    }

    // Add the table name to the validated table array
    $validatedTable[$tableName] = [];

    // Now we loop through each column in the table and check if it is valid
    foreach ($table[$tableName] as $colName => $colKeys) {

        // We ignore the first column which is the ID column
        if ($colName === 'id') {
            continue;
        }
        // Grab column data type and also prepare what validation type it will become which is one
        // the following: "STRINGS", "NUMBERS", "INTS", "FLOATS", "DATETIMES", "BLOBS" and "TEXTS".
        $colType = $colKeys['type'] ?? null;
        $validationType = "";

        $colSigned = $colKeys['signed'] ?? null;
        $colUnsigned = $colKeys['unsigned'] ?? null;

        // We grab the column name and add it to the validation array
        $validatedTable[$tableName][$colName] = [];

        // We check that data type for current column is a valid one compared to
        // what is stored in "funkphp/_internals/supported_mysql_data_types.php"
        if (!isset($validSQLDataTypes[$colKeys['type']])) {
            cli_err_syntax_without_exit("[cli_generate_a_validation_from_a_table] Data Type \"{$colKeys['type']}\" not found in \"funkphp/_internals/supported_mysql_data_types.php\" of valid MySQL Data Types.");
            cli_info("Please add its key if you believe it should be included, and then retry in FunkCLI!");
        }

        // This stores the min, max, digits and other values for the current column data type matched with a SQL type
        $matchedSQLType = $validSQLDataTypes[$colKeys['type']];
        $validatedTable[$tableName][$colName]['MAP_TO'] = ['post' => $tableName . "_" . $colName, 'get' => $tableName . "_" . $colName, 'json' => $tableName . "_" . $colName];

        // We now add the type of value to the validated table array for the current column which is usually
        // grouped into: "STRINGS", "NUMBERS", "INTS", "FLOATS", "DATETIMES", "BLOBS" and "TEXTS".
        // The "DATETIMES" itself is grouped into: "DATE", "TIME", "DATETIME", "TIMESTAMP" and "YEAR".
        // These types are found in: "funkphp/_internals/supported_mysql_data_types.php" => ["DATATYPE"]["TYPE"]
        if (isset($validSQLDataTypes[$colKeys['type']]['TYPE'])) {
            $validatedTable[$tableName][$colName][$validSQLDataTypes[$colKeys['type']]['TYPE']] = ['err' => null];
            $validationType = $validSQLDataTypes[$colKeys['type']]['TYPE'];
        }

        // We now create a "required" based on whether "nullable" is set to true or false for the given column
        if (isset($colKeys['nullable']) && $colKeys['nullable'] === false) {
            $validatedTable[$tableName][$colName]['required'] = ['err' => null];
        }

        // We now create a "unique" based on whether "unique" is set to true or false for the given column
        if (isset($colKeys['unique']) && $colKeys['unique'] === true) {
            $validatedTable[$tableName][$colName]['unique'] = ['val' => [$tableName => $colName], 'err' => null];
            cli_warning_without_exit("Unique Rule applied for \"Table:$tableName => Column:$colName\", meaning it will validate against unique value");
            cli_warning_without_exit("in Column \"$colName\" in Table \"$tableName\". Verify this is correct or change it in \"validatons/{$tableName}.php\"!");
        }

        // We now create a "default" based on whether "default" is NOT set to null, and we insert its value. This value
        // is used during Validation if something is not provided but it must have a default value.
        if (isset($colKeys['default']) && $colKeys['default'] !== null) {
            $validatedTable[$tableName][$colName]['default'] = $colKeys['default'];
        }

        // We will now set MIN, MAX, MIN_DIGITS and MAX_DIGITS, based on $colType and $validationType.
        // We also take MIN|MAX_SIGNED and MIN|MAX_SIGNED into account for signed and unsigned values.
        echo "ColName: $colName | ColType: $colType | ValidationType: $validationType\n";
        echo "Matched SQL Type: ";

        // When there IS a default max value for the column based on its data type such as strings, floats, integers,
        // numbers and so on. We set max and min values for strings based on if it is "required" or not based on whether
        // the column is NOT NULL (nullable === false) or not.
        if (isset($colKeys['value'])) {
            // For "string" types, the max and min will represent the mb(strlen()) of the string
            if ($validationType === 'string' && $colType !== 'ENUM' && $colType !== 'SET') {
                $validatedTable[$tableName][$colName]['max'] = ["val" => $colKeys['value'], "err" => null];
                if (isset($validatedTable[$tableName][$colName]['required'])) {
                    $validatedTable[$tableName][$colName]['min'] = ["val" => 1, "err" => null];
                } else {
                    $validatedTable[$tableName][$colName]['min'] = ["val" => 0, "err" => null];
                }
            }
            // For ENUM or SET types, we set the in_array rule with the values value key
            elseif ($colType === 'ENUM' || $colType === 'SET') {
                $validatedTable[$tableName][$colName]['in_array'] = ["val" => $colKeys['value'], "err" => null];
            }
        }
        // There exists no value for the column so we set the max and min values based on the exact data type
        // based on whether it is a number or not. We also set the "digits" value for that column data type
        // where it is applicable.
        else {
            // MAX & MIN values for strings when value = null in the column
            if ($validationType === 'string') {
                if (isset($matchedSQLType['MAX'])) {
                    $validatedTable[$tableName][$colName]['max'] = ["val" => $matchedSQLType['MAX'], "err" => null];
                } else {
                    $validatedTable[$tableName][$colName]['max'] = ["val" => null, "err" => null];
                    cli_warning_without_exit("No max value found for Column \"$colName\" in Table \"$tableName\". Please check Data Type or fix after in \"validations/{$tableName}.php\"!");
                }
                if (isset($validatedTable[$tableName][$colName]['required'])) {
                    $validatedTable[$tableName][$colName]['min'] = ["val" => (isset($matchedSQLType['MIN']) ? $matchedSQLType['MIN'] : 1), "err" => null];
                } else {
                    $validatedTable[$tableName][$colName]['min'] = ["val" => 0, "err" => null];
                }
            }
            // MAX & MIN values for integers & floats when value = null in the column
            // It also checks for signed and unsigned values for the column by checking
            // "CAN_BE_(UN)SIGNED" isset and then if "$colSigned" or " $colUnsigned" is set.
            // If both "$colSigned" and " $colUnsigned" are unset then we set the default
            // value using the "MIN_SIGNED".
            elseif ($validationType === 'integer' || $validationType === 'float') {
                // When CAN_BE_(UN)SIGNED isset, the keys "MIN" AND "MAX" are replaced with
                // "MIN_SIGNED" and "MAX_SIGNED" for signed values and "MIN_UNSIGNED" and
                // "MAX_UNSIGNED" for unsigned values inside of the current "$matchedSQLType"!
                if (isset($matchedSQLType['CAN_BE_(UN)SIGNED'])) {
                    if ($colSigned === true && $colUnsigned === false) {
                        $validatedTable[$tableName][$colName]['max'] = ["val" => ($matchedSQLType['MAX_SIGNED'] ?? null), "err" => null];
                        $validatedTable[$tableName][$colName]['min'] = ["val" => ($matchedSQLType['MIN_SIGNED']  ?? null), "err" => null];
                    } elseif ($colSigned === false && $colUnsigned === true) {
                        $validatedTable[$tableName][$colName]['max'] = ["val" => ($matchedSQLType['MAX_UNSIGNED'] ?? null), "err" => null];
                        $validatedTable[$tableName][$colName]['min'] = ["val" => ($matchedSQLType['MIN_UNSIGNED'] ?? null), "err" => null];
                    } else {
                        $validatedTable[$tableName][$colName]['max'] = ["val" => ($matchedSQLType['MAX_SIGNED'] ?? null), "err" => null];
                        $validatedTable[$tableName][$colName]['min'] = ["val" => ($matchedSQLType['MIN_SIGNED'] ?? null), "err" => null];
                    }
                } else {
                    // If both "$colSigned" and " $colUnsigned" are unset then we set the default
                    // value using the "MIN_SIGNED".
                    if (isset($matchedSQLType['MIN'])) {
                        $validatedTable[$tableName][$colName]['max'] = ["val" => ($matchedSQLType['MAX'] ?? null), "err" => null];
                        $validatedTable[$tableName][$colName]['min'] = ["val" => ($matchedSQLType['MIN'] ?? null), "err" => null];
                    } else {
                        cli_warning_without_exit("No min value found for Column \"$colName\" in Table \"$tableName\". Please check Data Type or fix after in \"validations/{$tableName}.php\"!");
                    }
                }
                // We also set the "digits" value for that column data type where it is applicable.
                $validatedTable[$tableName][$colName]['min_digits'] = ["val" => ($matchedSQLType['MIN_DIGITS'] ?? null), "err" => null];
                $validatedTable[$tableName][$colName]['max_digits'] = ["val" => ($matchedSQLType['MAX_DIGITS'] ?? null), "err" => null];
            }
            // Setting default rules for "timestamp", "time", "year", "date" and "datetime" types
            elseif (
                $validationType === 'timestamp' || $validationType === 'time'
                || $validationType === 'year' || $validationType === 'date' || $validationType === 'datetime'
            ) {
                $validatedTable[$tableName][$colName]['min'] = ["val" => ($matchedSQLType['MIN']  ?? null), "err" => null];
                $validatedTable[$tableName][$colName]['min_digits'] = ["val" => ($matchedSQLType['MIN_DIGITS'] ?? null), "err" => null];
                $validatedTable[$tableName][$colName]['max'] = ["val" => ($matchedSQLType['MAX'] ?? null), "err" => null];
                $validatedTable[$tableName][$colName]['max_digits'] = ["val" => ($matchedSQLType['MAX_DIGITS'] ?? null), "err" => null];
            }
            // And finally the "blob" types which are usually used for binary data such as images, files, etc.
            elseif (
                $validationType === 'blob'
            ) {
                $validatedTable[$tableName][$colName]['min'] = ["val" => ($matchedSQLType['MIN']  ?? null), "err" => null];
                $validatedTable[$tableName][$colName]['max'] = ["val" => ($matchedSQLType['MAX'] ?? null), "err" => null];
            }
        }

        // We now do some qualitative guesses based on $colName: for example, if it contains "email" or "mail" we
        // will add the rule "email" which can be configured to validate against a specific email regex syntax.
        if (str_contains(strtolower($colName), "email")) {
            $validatedTable[$tableName][$colName]['email'] = ['val' => null, 'err' => null];
            cli_info_without_exit("Email Rule added based on guessing \"Table:$tableName => Column:$colName\". Tweak it further in \"validations/{$tableName}.php\"!");
        } elseif (str_contains(strtolower($colName), "url")) {
            $validatedTable[$tableName][$colName]['url'] = ['val' => null, 'err' => null];
            cli_info_without_exit("URL Rule added based on guessing \"Table:$tableName => Column:$colName\". Tweak it further in \"validations/{$tableName}.php\"!");
        } elseif (str_contains(strtolower($colName), "password")) {
            $validatedTable[$tableName][$colName]['password'] = [
                'val' => [
                    "min_length" => 12,
                    "min_lowercases" => 2,
                    "min_uppercases" => 2,
                    "min_numbers" => 2,
                    "min_specials" => 2,
                ],
                'err' => null
            ];
            $validatedTable[$tableName][$colName]['max'] = ['val' => 50, 'err' => null];
            cli_info_without_exit("Password Rule added based on guessing \"Table:$tableName => Column:$colName\". Tweak it further in \"validations/{$tableName}.php\"!");
        } elseif (str_contains(strtolower($colName), "phone")) {
            $validatedTable[$tableName][$colName]['phone'] = ['val' => null, 'err' => null];
            $validatedTable[$tableName][$colName]['max'] = ['val' => 24, 'err' => null];
            cli_info_without_exit("Phone Rule added based on guessing \"Table:$tableName => Column:$colName\". Tweak it further in \"validations/{$tableName}.php\"!");
        }
    }

    // Finally attempt to output the created Validation file
    $outputValidationFile = file_put_contents($validationFile, "<?php\nreturn " . cli_convert_array_to_simple_syntax($validatedTable));
    if ($outputValidationFile === false) {
        cli_err_syntax("FAILED creating Validation \"validations/$tableName.php\" for Table \"$tableName\"!");
    } else {
        cli_success_without_exit("SUCCESSFULLY created Validation \"validations/$tableName.php\" for Table \"$tableName\"!");
        cli_info_without_exit("Please fill out all the necessary 'MAP_TO' Keys for all Columns in \"validations/$tableName.php\".");
        cli_info_without_exit("These will be used to validate correct \"\$_POST\", \"\$_GET\" and \"JSON\" data when used by \"funk_validate()\"!");
    }
}

// Function takes a SQL file and parses the CREATE TABLE(); statement
// and then stores it in funkphp/config/tables.php file as a PHP array
function cli_parse_a_sql_table_file()
{
    // Load globals and verify $argv is not empty string and ends with .sql
    cli_info_without_exit("IMPORTANT #1: \"php funkcli add table\" command is NOT meant for actual Table Migration.");
    cli_info_without_exit("It is ONLY meant for providing structure for more efficient Data Hydration!");
    cli_info_without_exit("IMPORTANT #2: The function cli_convert_array_to_simple_syntax() in \"funkphp/_internals/functions/cli_funs.php\" which converts ");
    cli_info_without_exit("array() to array[] ignores quotes inside of other qoutes. For example, \"Yours' truly\" would become \"Yours truly\".");
    cli_info_without_exit("KEEP THAT IN MIND: If you wanna use `DEFAULT \"Qouted Value with '\"Quotes\"' Inside\"` as it must be manually added inside \"config/Tables.php\"");

    global $argv, $dirs, $exactFiles, $settings, $tablesAndRelationshipsFile, $mysqlDataTypesFile;
    $sqlFile = null;
    if (!is_string_and_not_empty(trim($argv[3] ?? null))) {
        cli_err_syntax("Provide a SQL file from \"funkphp/sql/\" folder as a string!");
    }

    // Trim, add .sql extension if not already, and check that file exsts in /sql/ folder
    $argv[3] = strtolower(trim($argv[3]));
    if (!str_ends_with($argv[3], ".sql")) {
        $argv[3] .= ".sql";
    }
    if (file_exists_is_readable_writable($dirs['sql'] . $argv[3])) {
        $sqlFile = file_get_contents($dirs['sql'] . $argv[3]);
    } else {
        cli_err_syntax("\"{$argv[3]}\" must must exist in\"funkphp/sql/\"!");
    }

    // Check that the tables.php file exists and is writable, then load it
    if (!file_exists_is_readable_writable($exactFiles['tables'])) {
        cli_err_syntax("The \"funkphp/config/tables.php\" file must exist and be writable!");
    }

    // Prepare variables to store the tables.php file and parsed table
    $tablesFile = $tablesAndRelationshipsFile;
    $tableName = null;
    $parsedTable = [];

    // Check that keys "tables", "relationships" & "mappings" exist in the tables.php file
    if (
        !isset($tablesFile['tables']) || !is_array($tablesFile['tables'])
        || !isset($tablesFile['relationships']) || !is_array($tablesFile['relationships'])
        || !isset($tablesFile['mappings']) || !is_array($tablesFile['mappings'])
    ) {
        cli_err_syntax("The \"funkphp/config/tables.php\" file must contain the three keys: \"tables\", \"relationships\" & \"mappings\" at root level!");
    }

    // Inform but continue that "CREATE TABLE AS" (using other tables) is not supported
    if (preg_match("/^CREATE TABLE\s+([a-zA-Z0-9_]+)\s*AS/", $sqlFile, $matches)) {
        cli_info_without_exit("You cannot use \"CREATE TABLE AS\" in the SQL file. Please use \"CREATE TABLE\" instead!");
    }

    // Check that file starsts with "CREATE TABLE a-zA-Z0-9_\s+()" or error out
    if (!preg_match("/^CREATE TABLE\s+([a-zA-Z0-9_]+)\s*\(/", $sqlFile, $matches)) {
        cli_err_syntax("\"{$argv[3]}\" must start with \"CREATE TABLE /[a-zA-Z0-9_]+/ (\"");
    }
    // Parse out the table name and check if it is valid
    $tableName = $matches[1] ?? null;
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $tableName)) {
        cli_err_syntax("Invalid table name \"$tableName\". Should just be: \"[a-zA-Z0-9_]+\"");
    }
    // Check if the table name ends with "s" and if not add it, and inform the Developer that it happened
    if (!str_ends_with($tableName, "s")) {
        cli_info_without_exit("Table name \"$tableName\" was not pluralized.");
        $tableName .= "s";
        cli_info_without_exit("Table name is now \"$tableName\" for consistency reasons - just a heads up!");
    }
    // Check if the table name already exists in the tables.php file (as a key under "tables")
    if (isset($tablesFile['tables'][$tableName])) {
        cli_err_syntax("Table \"$tableName\" already exists in \"funkphp/config/tables.php\"!");
    }

    // Prepare the parsed table array with the table name
    $parsedTable[$tableName] = [];

    /* PREPARING TO PARSE LINE BY LINE IN THE SQL TABLE WHOSE NAME IS VALID! */
    // We now split on "\n" and iterate through each line of the SQL file
    // (this will skip the first line which is just the table name already parsed!)
    // We also remove first element, trim trailing spaces, remove empty lines,
    // the ending ");" and also all trailing "," at the end of each line. A final check
    // is that the first element must start with "id" or we error out. Just to keep things
    // consistent for all added tables in the tables.php file and all functions interacting
    // with them. But before that we will loop through each line and check if there are any
    // duplicates because that is just trolling or mistakes from the End-Developer's side!
    $sqlLines = explode("\n", $sqlFile);
    array_shift($sqlLines);
    $sqlLines = array_map('trim', $sqlLines);
    $sqlLines = array_filter($sqlLines, function ($line) {
        return !empty($line) && $line !== ");";
    });
    $sqlLines = array_map(function ($line) {
        return rtrim($line, ",\r\n\t ");
    }, $sqlLines);
    if (!str_starts_with(strtolower($sqlLines[0]), "id ")) {
        cli_err_syntax("First Table Column in the Table SQL File must be: \"id\"!");
    }
    // Check for duplicates in the SQL lines and error out if found. In each line we
    // split on " " and check if the first element is already in a duplicate array.
    // If it is we error out, otherwise we add it to the duplicate array.
    $duplicates = [];
    foreach ($sqlLines as $line) {
        if (
            str_starts_with($line, "//")
            || str_starts_with($line, "--")
            || empty(trim($line))
            || str_starts_with($line, "FOREIGN KEY")
        ) {
            continue;
        }
        $lineParts = explode(" ", $line);
        if (isset($duplicates[$lineParts[0]])) {
            cli_err_syntax("Duplicate Column Name \"{$lineParts[0]}\". Please fix \"sql/{$argv[3]}\" and retry!");
        } else {
            $duplicates[$lineParts[0]] = true;
        }
    }

    // Load typical MySQL Data Types with min & max lengths and number of digits (if at all applicable)
    // to compare against during parsing. This is also used when creating the validation file!
    $mysqlDataTypes = $mysqlDataTypesFile;

    // Finally we start iterating through each line by splitting on
    foreach ($sqlLines as $index => $line) {
        $lineParts = explode(" ", $line);
        $currentDataType = "";

        // Special Case #1: First Column (thus first index at 0) "ID" must be "BIGINT AUTO_INCREMENT PRIMARY KEY" for the first element
        // for consistency reasons. We check if it is the first element and if it is not we error out.
        if ($index === 0) {
            if ($line !== "id BIGINT AUTO_INCREMENT PRIMARY KEY") {
                cli_err_syntax("First Column \"{$lineParts[0]}\" must be \"id BIGINT AUTO_INCREMENT PRIMARY KEY\". Please fix \"sql/{$argv[3]}\" and try again!");
            } else {
                $parsedTable[$tableName][$lineParts[0]] = [
                    "joined_name" => $tableName . "_" . $lineParts[0],
                    "auto_increment" => true,
                    "type" => "BIGINT",
                    "value" => null,
                    "primary_key" => true,
                    "nullable" => false,
                    "default" => null,
                ];
                continue;
            }
        }

        // Special Case #2: The $line starts with "//" or "--" meaning a comment to just ignore it
        // and continue to the next line. We also check if the line is empty after trimming it.
        if (str_starts_with($line, "//") || str_starts_with($line, "--") || empty(trim($line))) {
            cli_info_without_exit("Skipping commented \"$line\"");
            continue;
        }

        // Special Case #3: The $line starts with "FOREIGN KEY" meaning we need to parse it by getting the
        // regex that I wrote myself for once instead of help from LLMs. Kinda incredible, right?! ^_^
        if (str_starts_with($line, "FOREIGN KEY")) {
            $foreignKeyRegex = "/FOREIGN KEY \(([a-zA-Z09_]+)\) REFERENCES ([a-zA-Z09_]+)\(([a-zA-Z09]+)\)/";

            // At match, grab variables and check all NOT being null first
            if (preg_match($foreignKeyRegex, $line, $matches)) {
                $thisTableFK = $matches[1] ?? null;
                $otherTable = $matches[2] ?? null;
                $otherTablePK = $matches[3] ?? null;
                if (!isset($thisTableFK) || !isset($otherTable) || !isset($otherTablePK)) {
                    cli_err_syntax("Foreign Key \"{$line}\" is missing one or more of the following: \"this_table_column\", \"other_table_name\" or \"other_table_primary_key\". Please fix \"sql/{$argv[3]}\" and try again!");
                } else {
                    // Check if the other table exists in the tables.php file
                    if (!isset($tablesFile['tables'][$otherTable])) {
                        cli_err_syntax("Foreign Key \"{$thisTableFK}\" references Table \"$otherTable\" not found in \"funkphp/config/tables.php\". First add Table \"$otherTable\", or fix \"sql/{$argv[3]}\" and try again!");
                    } else {
                        // Add the foreign key to the parsed table array and merge it with the existing one...
                        if (isset($parsedTable[$tableName][$thisTableFK])) {
                            $parsedTable[$tableName][$thisTableFK] = array_merge($parsedTable[$tableName][$thisTableFK], [
                                "joined_name" => $tableName . "_" . $thisTableFK,
                                "foreign_key" => true,
                                "references" => $otherTable,
                                "references_column" => $otherTablePK,
                                "referenced_joined" => $otherTable . "_" . $otherTablePK,
                            ]);
                        }
                        // ...unless it doesn't exist
                        else {
                            $parsedTable[$tableName][$thisTableFK] = [
                                "joined_name" => $tableName . "_" . $thisTableFK,
                                "foreign_key" => true,
                                "references" => $otherTable,
                                "references_column" => $otherTablePK,
                                "referenced_joined" => $otherTable . "_" . $otherTablePK,
                            ];
                        }
                        cli_info_without_exit("Foreign Key \"{$thisTableFK}\" added to Table \"$tableName\" which references Table \"$otherTable\".");
                        cli_info_without_exit("IMPORTANT: You must MANUALLY ADD the Relationship Between the Two Tables using \"php funkcli add relationship [$tableName=>$otherTable|$otherTable=>$tableName]\" command!");
                        continue;
                    }
                }
            }
            // Line started with "FOREIGN KEY" but no match found so we error out
            else {
                cli_err_syntax_without_exit("\"$line\" started with \"FOREIGN KEY\" but failed to match. Please fix \"sql/{$argv[3]}\" and try again!");
                cli_info_without_exit("Expected Syntax:\"FOREIGN KEY (existing_column_name_in_this_table) REFERENCES other_existing_referenced_table(id)\"");
                cli_info("Anything after is not matched so you can include things such as \"ON DELETE CASCADE\", \"ON UPDATE CASCADE\", etc.");
            }
        } // This step succeeds it will continue to the next line and skip the rest of the code below!

        // Special Case #4: The $line starts with "CONSTRAINT" or "CHECK" so we just inform the Developer
        // that these are currently not supported and we will skip them for now, meaning we will not parse them,
        // but we will continue to the next line and skip the rest of the code below!
        if (str_starts_with(strtoupper($line), "CONSTRAINT")) {
            cli_info_without_exit("Skipping \"{$line}\" as parsing \"CONSTRAINT\" is not implemented.");
            cli_warning_without_exit("If you use CONSTRAINT to add a Foreign Key, please start with \"FOREIGN KEY\" instead!");
            continue;
        }
        if (str_starts_with(strtoupper($line), "CHECK")) {
            cli_info_without_exit("Skipping \"{$line}\" as parsing \"CHECK\" is not implemented.");
            continue;
        }
        if (str_starts_with(strtoupper($line), "PRIMARY KEY")) {
            cli_info_without_exit("Skipping \"{$line}\" as a PK has already been added if you reached this far!");
            continue;
        }

        // FOR FIRST two ELEMENTS[0-1] we assume a valid column name and
        // data type with optional value inside of two () brackets.
        // the regex ^[a-zA-Z_][a-zA-Z0-9_]*$ and check if it is valid
        if (!preg_match("/^([a-zA-Z_][a-zA-Z0-9_]+)\s*(([a-zA-Z0-9_]+)(\((.+)\))*)*/", $line, $matches)) {
            cli_err_without_exit("Column \"{$lineParts[0]}\" should start with valid Column Name and then valid Data Type syntax!");
            cli_info_without_exit("For example: `columnName` `dataType(optionalValueInsideOfParentheses)` (ignoring the backticks!)");
            cli_info("Examples: `comment VARCHAR(255)` OR `like_counter INT` (ignoring the backticks!)");
        }
        // Otherwise we add it to the parsed table array
        else {
            // Insert column name or error out if it is not valid
            if (isset($matches[1])) {
                $parsedTable[$tableName][$matches[1]] = [
                    "joined_name" => $tableName . "_" . $matches[1],
                ];
            } else {
                cli_err_syntax("Column \"{$lineParts[0]}\" should start with valid Column Name and then valid Data Type syntax!");
                cli_info_without_exit("For example: `columnName` `dataType(optionalValueInsideOfParentheses)` (ignoring the backticks!)");
                cli_info("Examples: `comment VARCHAR(255)` OR `like_counter INT` (ignoring the backticks!)");
            }
            // Insert data type or error out if it is not valid
            if (isset($matches[3])) {
                $matches[3] = strtoupper($matches[3]);
                if (!isset($mysqlDataTypes[$matches[3]])) {
                    cli_err_syntax("[cli_parse_a_sql_table_file] Data Type \"{$matches[3]}\" not found in \"funkphp/_internals/supported_mysql_data_types.php\" of valid MySQL Data Types.");
                    cli_info("Please add its key if you believe it should be included, and then retry in FunkCLI!");
                } else {
                    $parsedTable[$tableName][$matches[1]]["type"] = $matches[3];
                }
            }
            // Insert optional value or just null
            if (isset($matches[5])) {
                // Special case when data type is ENUM or SET, we store it as string converted to array
                if ($matches[3] === "ENUM" || $matches[3] === "SET") {
                    $parsedArray = cli_try_parse_listed_string_as_array($matches[5]);
                    // Error out if the array is empty or not valid (too long)
                    if (is_array($parsedArray) && count($parsedArray) > 0) {
                        if ($matches[3] === "ENUM" && count($parsedArray) > 65535) {
                            cli_err_syntax("ENUM value \"{$matches[5]}\" is too long. Please fix \"sql/{$argv[3]}\" and try again!");
                        } elseif ($matches[3] === "SET" && count($parsedArray) > 64) {
                            cli_err_syntax("SET value \"{$matches[5]}\" is too long. Please fix \"sql/{$argv[3]}\" and try again!");
                        }
                    } else {
                        cli_warning_without_exit("ENUM/SET value \"{$matches[5]}\" is not valid. Please fix \"sql/{$argv[3]}\" after this!");
                    }
                    $parsedTable[$tableName][$matches[1]]["value"] = $parsedArray;
                }
                // Certain types will NOT get a value assigned to them!
                elseif (in_array($matches[3], $mysqlDataTypes["INVALID_VALUES_FOR_NUMBER_TYPES"])) {
                    $parsedTable[$tableName][$matches[1]]["value"] = null;
                }
                // Try to parse and store as a number or string
                else {
                    $parsedTable[$tableName][$matches[1]]["value"] = cli_try_parse_number($matches[5]);
                }
            }
            // Either it doesn't exist or is null so we set it to null
            else {
                $parsedTable[$tableName][$matches[1]]["value"] = null;
            }
        }

        // FOR REMAINING ELEMENTS[2-n] we first concatenate element[0-1] and remove them from the $line string
        // so we don't accidentally parse them again. Then we will start to check for things like "NOT NULL", "UNIQUE"
        // and so on! We begin now with removing the first two elements from the $line string.
        $line = trim(preg_replace("/^([a-zA-Z_][a-zA-Z0-9_]+)\s*(([a-zA-Z0-9_]+)(\((.+)\))*)*/", "", $line));

        // If there are no more elements in the $line string we just continue to the next line
        // This also means that the column is nullable and not unique
        if (empty($line)) {
            $parsedTable[$tableName][$lineParts[0]]["nullable"] = true;
            $parsedTable[$tableName][$lineParts[0]]["unique"] = false;
            continue;
        }

        // Check for uneven numbers of quotes in the $line string and warn the Developer
        if (substr_count($line, '"') % 2 !== 0 || substr_count($line, "'") % 2 !== 0) {
            cli_warning_without_exit("Uneven numbers of quotes left in `$line`. Values might get clipped when saving to \"tables.php\"!");
            cli_warning_without_exit("Quotes inside of quotes might be ignored during parsing!");
        }

        // Check for "NOT NULL" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("NOT NULL", $line) === "NOT NULL") {
            $parsedTable[$tableName][$lineParts[0]]["nullable"] = false;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["nullable"] = true;
        }

        // Check for "UNIQUE" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("UNIQUE", $line) === "UNIQUE") {
            $parsedTable[$tableName][$lineParts[0]]["unique"] = true;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["unique"] = false;
        }

        // Check for "UNSIGNED" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("UNSIGNED", $line) === "UNSIGNED") {
            $parsedTable[$tableName][$lineParts[0]]["unsigned"] = true;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["unsigned"] = false;
        }

        // Check for "SIGNED" and add to parsed array if found
        if (cli_find_string_outside_quotes_improved("SIGNED", $line) === "SIGNED") {
            $parsedTable[$tableName][$lineParts[0]]["signed"] = true;
        } else {
            $parsedTable[$tableName][$lineParts[0]]["signed"] = false;
        }

        // Try match DEFAULT and its value or just set it to null
        // (ambiguity due to DEFAULT NULL is possible as well!)
        $defaultPattern = "/DEFAULT\s*(NOW\(\)|(NULL)|(\d+\.*\d+)|(\d+)|(CURRENT_DATE)|(CURRENT_TIMESTAMP)|(CURRENT_TIME)|(\"{1}(.*)\"{1})|(\'{1}(.*)\'{1})|\(.+\))/i";
        if (preg_match($defaultPattern, $line, $matches)) {
            $defaultValue = $matches[1] ?? null;
            if (isset($defaultValue)) {
                // Convert $defaultValue to a number if it is parsed as a number
                if (is_numeric($defaultValue)) {
                    // Check what data type is $currentDataType and convert it to that type
                    if (isset($currentDataType)) {
                        if ($currentDataType === "BIGINT" || $currentDataType === "INT" || $currentDataType === "SMALLINT" || $currentDataType === "MEDIUMINT") {
                            $defaultValue = (int)$defaultValue;
                        } elseif ($currentDataType === "FLOAT" || $currentDataType === "DOUBLE" || $currentDataType === "DOUBLE PRECISION") {
                            $defaultValue = (float)$defaultValue;
                        } elseif ($currentDataType === "DECIMAL" || $currentDataType === "NUMERIC") {
                            $defaultValue = (string)$defaultValue;
                        } else {
                            $defaultValue = (int)$defaultValue;
                        }
                    } else {
                        $defaultValue = (int)$defaultValue;
                    }
                }
                // Else means it is a string, we remove the quotes at the beginning and end
                else {
                    if (str_starts_with($defaultValue, '"') && str_ends_with($defaultValue, '"')) {
                        $defaultValue = substr($defaultValue, 1, -1);
                    } elseif (str_starts_with($defaultValue, "'") && str_ends_with($defaultValue, "'")) {
                        $defaultValue = substr($defaultValue, 1, -1);
                    }
                }
                $parsedTable[$tableName][$lineParts[0]]["default"] = $defaultValue;
            }
            // No matched default value found so we set it to null
            else {
                $parsedTable[$tableName][$lineParts[0]]["default"] = null;
            }
        }
        // No match after "DEFAULT " (or no DEFALT key at all) so we set it to null
        else {
            $parsedTable[$tableName][$lineParts[0]]["default"] = null;
        }
    }

    // Finally add the entire parsed table to the Tables.php file's array!
    $tablesFile['tables'][$tableName] = $parsedTable[$tableName];

    // Now we add the table to the tables.php file and also pass it to the validation function which might fail
    // but the recompiling will still run first and if that fails then the validation won't run!
    cli_info_without_exit("Attempting recompiling tables with newly added Table \"$tableName\"...");
    cli_output_tables_file($tablesFile);
}

// Function tries to parse a number by first checking if it
// is a numeric, and then whether it is float, int or string
function cli_try_parse_number($number)
{
    if (!is_string_and_not_empty($number)) {
        cli_err_syntax("[cli_try_parse_number]: Expects a string as input for \$number!");
    }
    $number = trim($number);
    if (is_numeric($number)) {
        if (strpos($number, '.') !== false) {
            return (float)$number;
        } else {
            return (int)$number;
        }
    } else {
        return $number;
    }
}

// Function that takes a string like "1,2,3,4,5" or "('a', 'b', 'c')" and tries
// to parse it as an array and return it as an aray instead of a string.
function cli_try_parse_listed_string_as_array($stringedList)
{
    if (!is_string_and_not_empty($stringedList)) {
        cli_err_syntax("[cli_try_parse_listed_string_as_array]: Expects a string as input!");
    }
    $array = [];
    $stringedList = trim($stringedList);
    if (str_starts_with($stringedList, "(") && str_ends_with($stringedList, ")")) {
        $stringedList = substr($stringedList, 1, -1);
    }

    // We split on "," and remove any quotes around the
    // string and trim any whitespace around the string
    $stringedList = explode(",", $stringedList);
    foreach ($stringedList as $key => $value) {
        $value = trim($value);
        if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
            $value = substr($value, 1, -1);
        } elseif (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }
        $array[] = $value;
    }
    return $array ? array_values($array) : null;
}

// Function that finds a string that is NOT inside of quotes
// by iterating one character at a time and checking if it is inside quotes
function cli_find_string_outside_quotes($needle, $haystack)
{
    // Check that both are strings and not empty
    if (!is_string_and_not_empty($needle) || !is_string_and_not_empty($haystack)) {
        cli_err_syntax("[cli_find_string_outside_quotes]: Expects two non-empty strings as input!");
    }
    // Prepare variables
    $currentBuiltString = [];
    $insideQuotes = false;
    $splittedString = str_split($needle);

    // We iterate through each character in the haystack string
    // and we check first if we are inside of quotes or not
    // and if we are inside of quotes we skip the current character
    // and also reset the $currentBuiltString array to empty.
    // As we iterate through each character that is outside of quotes
    // we then check if the current character is equal to the first character of the $needle string
    // or the next one and so on. If it is we add it to the $currentBuiltString array otherwise
    // we reset the $currentBuiltString array to empty and continue iterating.
    // If we reach the end of the haystack string and the $currentBuiltString array is equal to the $needle string
    // we return the $currentBuiltString array as a string. Otherwise we return "".
    foreach (str_split($haystack) as $index => $char) {
        if ($char === '"' || $char === "'") {
            // Check first that previous character is not a backslash
            // and then toggle the $insideQuotes variable
            if ($index > 0 && $haystack[$index - 1] !== "\\") {
                $insideQuotes = !$insideQuotes;
            }
            continue;
        }
        if ($insideQuotes) {
            $currentBuiltString = [];
            continue;
        }
        // If first character is the first in the $needle string and the $currentBuiltString is empty
        if (
            $char === $splittedString[0] && empty($currentBuiltString)
            && $index > 0 && $haystack[$index - 1] === " "
        ) {
            $currentBuiltString[] = $char;
            continue;
        }
        // Here we check if $char is the next character in the $needle string
        // by taking the current count + 1 in the $currentBuiltString array and checking if it is equal to the $needle string
        // If it is we add it to the $currentBuiltString array otherwise we reset the $currentBuiltString array to empty.
        // We also check if the $currentBuiltString array is equal to the $needle string and return it as a string.
        // Otherwise we return "".
        if (
            count($currentBuiltString) < count($splittedString)
            && $char === $splittedString[count($currentBuiltString)]
        ) {
            $currentBuiltString[] = $char;
        } elseif (count($currentBuiltString) == count($splittedString)) {
            return implode("", $currentBuiltString);
        } elseif ($char !== $splittedString[count($currentBuiltString)]) {
            $currentBuiltString = [];
        }
    }
    return implode("", $currentBuiltString);
}

// Improved version of the cli_find_string_outside_quotes() function
// using some help from LLMs to handle cases like NOT matching word SIGNED
// because it is inside of the word UNSIGNED and similar cases like that.
function cli_find_string_outside_quotes_improved($needle, $haystack)
{
    // Check that both are strings and not empty
    if (!is_string_and_not_empty($needle) || !is_string_and_not_empty($haystack)) {
        cli_err_syntax("[cli_find_string_outside_quotes_improved]: Expects two non-empty strings as input!");
    }
    // Prepare variables
    $currentBuiltString = [];
    $insideQuotes = false;
    $splittedString = str_split($needle);

    // We iterate through each character in the haystack string
    // and we check first if we are inside of quotes or not
    // and if we are inside of quotes we skip the current character
    // and also reset the $currentBuiltString array to empty.
    // As we iterate through each character that is outside of quotes
    // we then check if the current character is equal to the first character of the $needle string
    // or the next one and so on. If it is we add it to the $currentBuiltString array otherwise
    // we reset the $currentBuiltString array to empty and continue iterating.
    // If we reach the end of the haystack string and the $currentBuiltString array is equal to the $needle string
    // we return the $currentBuiltString array as a string. Otherwise we return "".
    foreach (str_split($haystack) as $index => $char) {
        if ($char === '"' || $char === "'") {
            // Check first that previous character is not a backslash
            // and then toggle the $insideQuotes variable
            if ($index > 0 && $haystack[$index - 1] !== "\\") {
                $insideQuotes = !$insideQuotes;
            }
            continue;
        }
        if ($insideQuotes) {
            $currentBuiltString = [];
            continue;
        }
        // If first character is the first in the $needle string and the $currentBuiltString is empty
        if (
            $char === $splittedString[0] && empty($currentBuiltString)
            && $index > 0 && $haystack[$index - 1] === " "
        ) {
            $currentBuiltString[] = $char;
            continue;
        }
        // Here we check if $char is the next character in the $needle string
        // by taking the current count + 1 in the $currentBuiltString array and checking if it is equal to the $needle string
        // If it is we add it to the $currentBuiltString array otherwise we reset the $currentBuiltString array to empty.
        // We also check if the $currentBuiltString array is equal to the $needle string and return it as a string.
        // Otherwise we return "".
        // Check if we are currently building a potential match string
        // If we are already building, check if the current character is the next expected character
        if (count($currentBuiltString) > 0) {
            if (count($currentBuiltString) < count($splittedString) && $char === $splittedString[count($currentBuiltString)]) {
                // Character matches the next expected one, continue building
                $currentBuiltString[] = $char;
            }
            // Else if we already have a full match from the previous iteration, return it
            elseif (count($currentBuiltString) == count($splittedString)) {
                return implode("", $currentBuiltString);
            }
            // The current character does NOT match the next expected character in the sequence.
            // Reset the built string because the sequence is broken.
            // Now, check if the CURRENT character could be the START of a *new* potential match (after a reset)
            // This handles cases like "abab" searching for "aba", when it sees the second 'a', it resets and could start a new match.
            else {
                $currentBuiltString = [];
                // Check the character *before* the current one for a word boundary
                // A word boundary is the start of the string, or a non-word character (\W or [^a-zA-Z0-9_])
                if ($char === $splittedString[0]) {
                    if ($index === 0 || ($index > 0 && !ctype_alnum($haystack[$index - 1]) && $haystack[$index - 1] !== '_')) {
                        $currentBuiltString[] = $char;
                    }
                }
            }
        } else {
            // We are NOT currently building a potential match string.
            // Check if the current character could be the START of a match.
            // Check the character *before* the current one for a word boundary
            if ($char === $splittedString[0]) {
                if ($index === 0 || ($index > 0 && !ctype_alnum($haystack[$index - 1]) && $haystack[$index - 1] !== '_')) {
                    $currentBuiltString[] = $char;
                }
            }
        }
    }
    // After loop return fully matched or not
    if (count($currentBuiltString) == count($splittedString)) {
        return implode("", $currentBuiltString);
    } else {
        return "";
    }
}

// Outputs the tables.php file based on the array passed to it
function cli_output_tables_file($array)
{
    // Load globals and verify non-empty array and that file exists to written to
    global $dirs, $exactFiles, $settings;
    if (!is_array_and_not_empty($array)) {
        cli_err_syntax("The provided array must be a non-empty array!");
    }
    if (!file_exists_is_readable_writable($exactFiles['tables'])) {
        cli_err_syntax("The \"funkphp/config/tables.php\" file must exist and be writable!");
    }
    // Check for the keys "tables" and "relationships" in the array at the root level
    if (
        !isset($array['tables']) || !is_array($array['tables'])
        || !isset($array['relationships']) || !is_array($array['relationships'])
        || !isset($array['mappings']) || !is_array($array['mappings'])
    ) {
        cli_err_syntax("The \"funkphp/config/tables.php\" file must contain the three keys: \"tables\", \"relationships\" & \"mappings\" at root level!");
    }

    // Attempt to write to the Tables.php file and check if it was successful
    $result = file_put_contents($exactFiles['tables'], "<?php\nreturn " . cli_convert_array_to_simple_syntax($array));
    if ($result === false) {
        cli_err_syntax("FAILED recompiling Tables in \"funkphp/config/tables.php\"!");
    } else {
        cli_success_without_exit("Recompiled Tables in \"funkphp/config/tables.php\"!");
    }
}

// Function takes a a valid array with simplified Validation Rules Syntax and converts
// it to highly optimized validation rules that are then returned as an array
function cli_convert_simple_validation_rules_to_optimized_validation($validationArray, $handlerFile, $fnName)
{
    global $dirs, $exactFiles, $settings;
    // Validate it is an associative array - not a list
    if (!is_array_and_not_empty($validationArray)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty Associative Array as input for `\$validationArray`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (array_is_list($validationArray)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty Associative Array as input for `\$validationArray`!");
        cli_info("Here it probably means that the \"\$DX\" variable is a List Array, empty or not?");
    }

    // Both $handlerFile and $fnName must be non-empty strings
    if (!is_string_and_not_empty($handlerFile)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty String as input for `\$handlerFile`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (!is_string_and_not_empty($fnName)) {
        cli_err_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation]: Expects a Non-Empty String as input for `\$fnName`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // Prepare variables to store the converted validation rules
    $convertedValidationArray = [];
    $existsTableColsToCheck = [];
    $currentDXKey = null;
    $currentRules = null;
    $currentRuleForCurrentDXKey = null;
    $currentRuleValueForCurrentDXKeyValue = null;
    $currentRuleErrMsgForCurrentDXKeyValue = null;

    // Regex patterns to match the validation rules
    $regexType = [
        'ONLY_RULE_NAME' => '/^([a-z_][a-z_0-9]+)$/',
        'ONLY_RULE_NAME_AND_VALUE' => '/^([a-z_][a-z_0-9]+):(.+)$/',
        'ONLY_RULE_NAME_AND_ERROR' => '/^([a-z_][a-z_0-9]+)\("([^"]+)"\)$/',
        'ONLY_RULE_NAME_AND_VALUE_AND_ERROR' => '/^([a-z_][a-z_0-9]+):(.+)\("([^"]+)"\)$/'
    ];

    // Priority order of validation rules (required and the data
    // type must always be first for each $currentDXKey!). 'nullable'
    // must come very early to allow for data that are actually null!
    $priorityOrder = [
        'nullable',
        'required',
        'string',
        'integer',
        'float',
        'boolean',
        'number',
        'date',
        'array',
        'enum',
        'email',
        'url',
        'ip',
        'uuid',
        'phone',
        'regex',
        'object',
        'json',
    ];
    include_once($dirs['functions'] . "d_data_funs.php");

    // We first verify for any duplicates in the array keys.
    // This should be impossible since same key name would just
    // overwrite the previous one, but we still check for it.
    $duplicates = [];
    foreach ($validationArray as $key => $value) {
        if (isset($duplicates[$key])) {
            cli_err_syntax_without_exit("Duplicate Validation Key `$key` found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax("Please make sure all keys (the matching data to validate) are unique!");
        }
        $duplicates[$key] = [];
    }

    // We verify each array key is a non-empty string and we verify each value
    // is either a non-empty string or a single array of non-empty strings!
    foreach ($validationArray as $key => $value) {
        if (!is_string_and_not_empty($key)) {
            cli_err_syntax_without_exit("An empty or non-string key found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax("Please make sure all keys (the matching data to validate) are non-empty strings!");
        }
        if (!is_string($value) && !is_array($value)) {
            cli_err_syntax_without_exit("Invalid Validation Rules for \$DX key `$key` found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax_without_exit("Please make sure all Validation Rules are either non-empty strings as elements");
            cli_err_syntax("in a single array OR just a single non-empty string with Validation Rules separated with `|`!");
        }
        if ((is_string($value) && empty($value)) || (is_array($value) && empty($value))) {
            cli_err_syntax_without_exit("No Validation Rules found for \$DX key `$key` found in Validation `$handlerFile.php=>$fnName`!");
            cli_err_syntax_without_exit("Please make sure all Validation Rules are either non-empty strings as elements");
            cli_err_syntax("in a single array OR just a single non-empty string with Validation Rules separated with `|`!");
        }
        // If array, each element must be a non-empty string!
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                if (!is_string_and_not_empty($subValue)) {
                    cli_err_syntax_without_exit("An empty or non-string Validation Rule found for \$DX key `$key` in Validation `$handlerFile.php=>$fnName`!");
                    cli_err_syntax_without_exit("Please make sure all Validation Rules are either non-empty strings as elements");
                    cli_err_syntax("in a single array OR just a single non-empty string with Validation Rules separated with `|`!");
                }
            }
        }
    }

    // Now we finally start converting the validation rules to optimized validation rules
    foreach ($validationArray as $DXkey => $Rules) {

        // Prepare the current rule for the current key and add
        // it to the converted validation array as its own key
        // since each rule will be a subkey there and each rule
        // will also have subkeys such as "value" and "err_msg".
        // The "value" is after ":" and the "err_msg" is inside ("")
        // and it is optional and is otherwise set to null.
        $currentDXKey = $DXkey;
        $convertedValidationArray[$currentDXKey] = [];

        if (str_contains($currentDXKey, ".") && !str_contains($currentDXKey, "*")) {
            if (!preg_match("/^(([a-z_])([a-z_0-9]+)\.([a-z_0-9]+))(\.([a-z_0-9]+))*$/", $currentDXKey)) {
                cli_err_syntax("[cli_convert_simple_validation_rules_to_optimized_validation] Invalid Nested Validation Key in `$currentDXKey`! Valid Syntax is: `user.email`, `user.email.primary` and so on!");
            }
        }

        // We now check if the $Rules is a string and if it is a string then we check
        // if it has "|" meaning we should split it into an array of rules. If it is just a
        // single rule string we still convert it to an array with a single element.
        // If it is an array we just use it as is.
        if (is_string($Rules) && str_contains($Rules, "|")) {
            $currentRules = explode("|", $Rules);
        } elseif (is_string($Rules) && !str_contains($Rules, "|")) {
            $currentRules = [$Rules];
        } else {
            $currentRules = $Rules;
        }

        // We now iterate through each rule by first checking it against the $regexType
        // array to see if it matches any of them. If not, then it is invalid rule syntax
        // and we error out. If it is valid we then check if it is a rule with a value.
        foreach ($currentRules as $singleRule) {
            // $singleRule is a rule name with a value and an error message?
            if (preg_match($regexType['ONLY_RULE_NAME_AND_VALUE_AND_ERROR'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = $matches[2];
                $currentRuleErrMsgForCurrentDXKeyValue = $matches[3];
            }
            // $singleRule is a rule name with a value but no error message?
            elseif (preg_match($regexType['ONLY_RULE_NAME_AND_VALUE'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = $matches[2];
                $currentRuleErrMsgForCurrentDXKeyValue = null;
            }
            // $singleRule is a rule name with an error message but no value?
            elseif (preg_match($regexType['ONLY_RULE_NAME_AND_ERROR'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = null;
                $currentRuleErrMsgForCurrentDXKeyValue = $matches[2];
            }
            // $singleRule is just a rule name with no value nor error message?
            elseif (preg_match($regexType['ONLY_RULE_NAME'], $singleRule, $matches)) {
                $currentRuleForCurrentDXKey = $matches[1];
                $currentRuleValueForCurrentDXKeyValue = null;
                $currentRuleErrMsgForCurrentDXKeyValue = null;
            } else {
                cli_err_syntax_without_exit("Invalid Validation Rule Syntax for `$singleRule` in Validation `$handlerFile.php=>$fnName`!");
                cli_err_syntax_without_exit("Please make sure all Validation Rules are using one of the valid Validation Rule Syntaxes supported in FunkPHP!");
                cli_info("Examples of valid Validation Rule Syntaxes:\n`required` OR `required(\"This is required!\")`\n`min:3` OR `min:3(\"This is too short!\")`\n`between:3,50` OR `between:3,50(\"This is too short or too long!\")");
            }

            // We check if "$currentRuleForCurrentDXKey" actually exists
            // as a funk_validate_function and if it does not exist we error out.
            if (!function_exists('funk_validate_' . $currentRuleForCurrentDXKey)) {
                cli_err_syntax_without_exit("Validation Rule \"$currentRuleForCurrentDXKey\" not found in Validation Handler File \"$handlerFile.php\".");
                cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
                cli_info("It must start as a function: `function $currentRuleForCurrentDXKey()` or it will not be found!");
            }

            // We check if the "$currentRuleValueForCurrentDXKeyValue" contains a
            // "," meaning we should split it into an array of values that is the value then!
            // We also trim all the values in the array to remove any whitespace around them.
            if (str_contains($currentRuleValueForCurrentDXKeyValue, ",")) {
                $currentRuleValueForCurrentDXKeyValue = explode(",", $currentRuleValueForCurrentDXKeyValue);
                foreach ($currentRuleValueForCurrentDXKeyValue as $subKey => $subValue) {
                    $currentRuleValueForCurrentDXKeyValue[$subKey] = trim($subValue);
                }

                // We then check if each element is actually a number but that is stringified
                // so we turn it back to a number again!
                foreach ($currentRuleValueForCurrentDXKeyValue as $subKey => $subValue) {
                    if (is_numeric($subValue)) {
                        $currentRuleValueForCurrentDXKeyValue[$subKey] = cli_try_parse_number($subValue);
                    }
                }
            }

            // Add the current rule to the converted validation array
            $convertedValidationArray[$currentDXKey]["<FUNKPHP_VALIDATION_RULES>"][$currentRuleForCurrentDXKey] = [
                "value" => is_string($currentRuleValueForCurrentDXKeyValue)
                    ? cli_try_parse_number($currentRuleValueForCurrentDXKeyValue) : $currentRuleValueForCurrentDXKeyValue,
                "err_msg" => $currentRuleErrMsgForCurrentDXKeyValue,
            ];
        }

        // We will now sort some keys for the given $DXKey were are at. For example: we need 'required'
        // and the data type ('string', 'int', 'float', etc.) to be at the top of the array
        // so we can check for them first and then check for the rest of the rules. This will help the
        // actual Validation function which will need to check for some rules before others in order to
        // call the correct validation function (e.g. knowing to call 'funk_validate_minlen' or
        // 'funk_validate_minval' based on whether data type is a string or a number).
        $sortedRulesForField = [];
        $createdRules = $convertedValidationArray[$currentDXKey];

        // First Add priority rules first, in their defined order
        // and then add remaining rules in the order they were found
        foreach ($priorityOrder as $ruleName) {
            if (isset($createdRules[$ruleName])) {
                $sortedRulesForField[$ruleName] = $createdRules[$ruleName];
                unset($createdRules[$ruleName]);
            }
        }
        foreach ($createdRules as $ruleName => $details) {
            $sortedRulesForField[$ruleName] = $details;
        }

        // Finally add the priority sorted rules to the converted validation array
        $convertedValidationArray[$currentDXKey] = $sortedRulesForField;

        // We check if the key contains a "." and if it does we need to split it
        // and then we need to rebuild the nested keys in the converted validation array
        // and then we need to set the value to the $sortedRulesForField array.
        if (
            str_contains($currentDXKey, ".")
            && !str_contains($currentDXKey, "*")
            && preg_match("/^(([a-z_])([a-z_0-9]+)\.([a-z_0-9]+))(\.([a-z_0-9]+))*$/", $currentDXKey)
        ) {
            $nestedKeys = explode(".", $currentDXKey);
            $nestedKeyCount = count($nestedKeys);
            $currentNestedKey = array_shift($nestedKeys);
            $currentNestedArray = &$convertedValidationArray[$currentNestedKey];

            // We need to check if the current nested key already exists
            // and if it does not exist we need to create it as an empty array
            if (!isset($currentNestedArray) || !is_array($currentNestedArray)) {
                $currentNestedArray = [];
            }

            // We now iterate through the remaining nested keys and create them
            // as empty arrays until we reach the last key which will be the value
            foreach ($nestedKeys as $key) {
                if (!isset($currentNestedArray[$key]) || !is_array($currentNestedArray[$key])) {
                    $currentNestedArray[$key] = [];
                }
                $currentNestedArray = &$currentNestedArray[$key];
            }
            // Finally set the value to the sorted rules for the field
            $currentNestedArray = $sortedRulesForField;
            unset($convertedValidationArray[$currentDXKey]);
        }
    }

    // Return the converted validation array
    return $convertedValidationArray;
}

// Compiles a $DX Validation [] to an optmized validation array that is returned within the same
// function that is used to validate the data. This is used to optimize the validation process!
// VERY IMPORTANT WARNING: This function calls a function which uses eval() to parse the validation file!!!
function cli_compile_dx_validation_to_optimized_validation()
{
    // Load globals, check for the argv[3] argument and prepare valid HandlerFile=>HandlerFunctionName
    global $dirs, $exactFiles, $settings, $delimiters, $argv, $dirs;
    if (!isset($argv[3]) || !is_string_and_not_empty($argv[3])) {
        cli_err("cli_compile_dx_validation_to_optimized_validation() expects a string as input!");
    }
    $handlerDir = $dirs['validations'] ?? "";
    [$handlerFile, $fnName] = get_valid_handlerVar_or_err_out($argv[3], "v");

    // Check that dir exists and is readable
    if (!dir_exists_is_readable_writable($handlerDir)) {
        cli_err("Validation Directory \"$handlerDir\" not found or non-readable/writable!");
    }
    // Then check file exists and is readable
    if (!file_exists_is_readable_writable($handlerDir . $handlerFile . ".php")) {
        cli_err("Validation Handler file \"$handlerFile.php\" not found in \"funkphp/validations/\" or not readable!");
    }

    // Prepare regex and find the function name in the file
    $fnNameRegex = get_match_function_regex($fnName);
    $dxVarRegex = get_match_dx_function_regex();
    $dxReturnRegex = get_match_dx_return_regex();
    $fileContent = file_get_contents($handlerDir . $handlerFile . ".php");
    $matchedFn = preg_match($fnNameRegex, $fileContent, $matches);

    if (!$matchedFn) {
        cli_err("Validation Function \"$fnName\" not found in Validation Handler File \"funkphp/validations/$handlerFile.php\". Check for mispellings or typos?");
    }

    // We store found match and now try find the $DX variable in that part
    $matchedEntireFnName = $matches[0] ?? null;
    $matchedEntireFnCopy = $matchedEntireFnName;
    $matchedDX = preg_match($dxVarRegex, $matchedEntireFnName, $matches2);
    if (!$matchedDX) {
        cli_err_without_exit("The \"\$DX\" variable not found in Validation Function \"$fnName\" in Validation Handler File \"$handlerFile.php\".");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found!");
    }

    // We store found match and now try find the return statement within "$matchedEntireFnName"
    $matchedSimpleSyntax = $matches2[0] ?? null;

    // We use eval() to try to parse the $matchedSimpleSyntax
    // as a typical array and then check that it is an array
    $evalCode = null;
    try {
        $evalCode = "\nreturn $matchedSimpleSyntax";
        $evalCode = eval($evalCode);
    } catch (Throwable $e) {
        cli_err_without_exit("The \"\$DX\" variable was found but could not be parsed as a valid PHP Array!");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found!");
    }
    if ($evalCode === null) {
        cli_err_without_exit("The \"\$DX\" variable was found but could not be parsed as a valid PHP Array!");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found!");
    }
    if (is_array($evalCode)) {
        cli_info_without_exit("Found \"\$DX\" variable parsed as a valid PHP Array!");
    }

    $matchedReturn = preg_match($dxReturnRegex, $matchedEntireFnName, $matches3);
    if (!$matchedReturn) {
        cli_err_without_exit("The \"return array();\" statement not found in Validation Function \"$fnName\" in Validation Handler File \"$handlerFile.php\".");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
        cli_info("The last part of the array() - `);` - must be indented to the same level as the \"return array (\" part!");
    }
    $matchedReturnStmt = $matches3[0] ?? null;

    // This contains the optimized validation rules which will then replace the "$matchedReturnStmt"
    // The function can error out on its own so we do not need to check for the return value!
    $optimizedRuleArray = cli_convert_simple_validation_rules_to_optimized_validation($evalCode, $handlerFile, $fnName);

    // Convert the optimized rule array to a string with ";\n" at the end
    $optimizedRuleArrayAsStringWithReturnStmt = "return " . var_export($optimizedRuleArray, true) . ";\n";

    // We will now use "$matchedEntireFnName" and replace the "$matchedReturnStmt" with the optimized rule array
    $replaced = str_replace($matchedReturnStmt, $optimizedRuleArrayAsStringWithReturnStmt, $matchedEntireFnName);

    // We now replace the "$matchedEntireFnCopy" part of the fileContent with the new $replaced string
    $newFileContent = str_replace($matchedEntireFnCopy, $replaced, $fileContent);

    // Output the file to replace the original file
    $result = file_put_contents($handlerDir . $handlerFile . ".php", $newFileContent);
    if ($result === false) {
        cli_err("FAILED compiling Validation Rules to Optimized Rules in Validation Function \"$fnName\" in \"$handlerFile.php\". Permissions issue?");
    } else {
        cli_success_without_exit("SUCCESSFULLY COMPILED Validation Rules to Optimized Rules in Validation Function \"$fnName\" in \"funkphp/validations/$handlerFile.php\".");
        cli_info("IMPORTANT: Open it in an IDE and press CMD+S or CTRL+S to autoformat the Validation File again!");
    }
}

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function cli_match_compiled_route(string $requestUri, array $methodRootNode): ?array
{
    // Prepare & and extract URI Segments and remove empty segments
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);

    // Prepare variables to store the current node,
    // matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = [];
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;

    // EDGE-CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }

    // Iterate URI segments when more than 0
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        /// First try match "|" middleware node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }

        // Then try match literal route
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments[] = $currentUriSegment;
            $currentNode = $currentNode[$currentUriSegment];
            $segmentsConsumed++;
            continue;
        }

        // Or try match dynamic route ":" indicator node and
        // only store param and matched URI segment if not null
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']);

            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedParams[$placeholderKey] = $currentUriSegment;
                $matchedPathSegments[] = ":" . $placeholderKey;
                $currentNode = $currentNode[':'][$placeholderKey];
                $segmentsConsumed++;
                continue;
            }
        }

        // No matched "|", ":" or literal route in Compiled Routes!
        return null;
    }

    // EDGE-CASE: Add middleware at last node if it exists
    if (isset($currentNode['|'])) {
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
    }

    // Return matched route, params & middlewares
    // if all consumed segments matched
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments)) {
            return ["route" => '/' . implode('/', $matchedPathSegments), "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
        }
        // EDGE-CASE: 0 consumed segments,
        // return null instead of matched
        else {
            return null;
        }
    }
    // EDGE-CASES: Return null when impossible(?)/unexpected behavior
    else {
        return null;
    }
    return null;
}

// TRIE ROUTER STARTING POINT: Match Returned Matched Compiled Route With Developer's Defined Route
function cli_match_developer_route(string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $handlerKey = "handler", string $mHandlerKey = "middlewares")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = cli_match_compiled_route($uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = "COMPILED_ROUTE_KEY (" . mb_strtoupper($method) . ") & ";
    }

    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $matchedRouteHandler = $routeInfo[$handlerKey] ?? null;
            $noMatchIn = "ROUTE_MATCHED_BOTH";
        } else {
            $noMatchIn .= "DEVELOPER_ROUTES(route_single_routes.php)";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES(troute_route.php)";
    }
    return [
        "method" => $method,
        "route" => $matchedRoute,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// Rebuilds the Single Routes Route file (funkphp/routes/route_single_routes.php) based on valid array
function cli_rebuild_single_routes_route_file($singleRouteRoutesFileArray): bool
{
    global $exactFiles, $dirs, $settings;
    if (!is_array($singleRouteRoutesFileArray) || empty($singleRouteRoutesFileArray)) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/routes/route_single_routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['ROUTES'])) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/routes/route_single_routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['routes']) || !is_writable($dirs['routes'])) {
        cli_err("[cli_rebuild_single_routes_file] Routes directory (funkphp/routes/) must be a valid directory and writable!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_routes']) && !is_writable($exactFiles['single_routes'])) {
        cli_err("[cli_rebuild_single_routes_file] Routes file (funkphp/routes/route_single_routes.php) must be writable. It is not!");
    }
    return file_put_contents(
        $exactFiles['single_routes'],
        cli_get_prefix_code("route_singles_routes_start")
            . cli_convert_array_to_simple_syntax($singleRouteRoutesFileArray)
    );
}

// Build Compiled Route from Developer's Defined Routes
function cli_build_compiled_routes(array $developerSingleRoutes, array $developerMiddlewareRoutes)
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    // Only localhost can run this function (meaning you cannot run this in production!)
    // Both arrays must be non-empty arrays
    if (!is_array($developerSingleRoutes)) {
        echo "[ERROR]: '\$developerSingleRoutes' Must be a non-empty array!\n";
        exit;
    } elseif (!is_array($developerMiddlewareRoutes)) {
        echo "[ERROR]: '\$developerMiddlewareRoutes' Must be a non-empty array!\n";
        exit;
    }
    if (empty($developerSingleRoutes)) {
        echo "[ERROR]: Must '\$developerSingleRoutes' be a non-empty array!\n";
        exit;
    } else if (empty($developerMiddlewareRoutes)) {
        echo "[ERROR]: Must '\$developerMiddlewareRoutes' be a non-empty array!\n";
        exit;
    }

    // Prepare compiled route array to return and other variables
    $compiledTrie = [];
    $GETSingles = $developerSingleRoutes["GET"] ?? [];
    $POSTSingles = $developerSingleRoutes["POST"] ?? [];
    $PUTSingles = $developerSingleRoutes["PUT"] ?? [];
    $DELETESingles = $developerSingleRoutes["DELETE"] ?? [];
    $PATCHSingles = $developerSingleRoutes["PATCH"] ?? [];

    // Using method below, iterate through each HttpMethod and then add it to the $compiledTrie array
    $addMethods = function ($singleRoutes) {
        // Begin with just getting the key names and no other nested values inside of them:
        // For example:  '/users' => ['handler' => 'USERS_PAGE', /*...*/], only gets the '/users' key name
        // and not the value inside of it. This is done by using array_keys() to get the keys of the array.
        $keys = array_keys($singleRoutes) ?? [];
        $compiledTrie = [];

        // Iterate through each key in the array and add it to the $compiledTrie array
        foreach ($keys as $key) {

            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/") {
                $compiledTrie["/"] = [];
                continue;
            }

            // Split the route into segments
            $splitRouteSegments = explode("/", trim($key, "/"));

            // Initialize the current node in the trie
            $currentNode = &$compiledTrie;

            // Iterate through each segment of the route
            foreach ($splitRouteSegments as $segment) {
                // WHEN DYNAMIC PARAMETER ROUTE SEGMENT
                if (str_starts_with($segment, ":")) {
                    // Create when not exist
                    if (!isset($currentNode[':'])) {
                        $currentNode[':'] = [];
                    }
                    // And insert param as next nested key and/or move to next node
                    $paramName = substr($segment, 1);
                    if (!isset($currentNode[':'][$paramName])) {
                        $currentNode[':'][$paramName] = [];
                    }
                    $currentNode = &$currentNode[':'][$paramName];
                }
                // WHEN LITERAL ROUTE SEGMENT
                else {
                    // Insert if not exist and/or move to next node
                    if (!isset($currentNode[$segment])) {
                        $currentNode[$segment] = [];
                    }
                    $currentNode = &$currentNode[$segment];
                }
            }
        }
        // Return the compiled trie for the method
        return $compiledTrie;
    };

    // Add the middleware routes to the compiled trie
    $addMiddlewareRoutes = function ($middlewareRoutes, &$compiledTrie) {
        // Only extract the keys from the middleware routes
        //$keys = array_keys($middlewareRoutes) ?? [];
        $keys = $middlewareRoutes ?? [];


        // The way we insert "|" to signify a middleware is to just go through all segments for each key
        // and when we are at the last segment that is the node we insert "|" and then we move on to key.
        foreach ($keys as $key => $value) {
            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/" && isset($value['middlewares']) && !empty($value['middlewares'])) {
                $compiledTrie["|"] = [];
                continue;
            }

            // Now split key into segments and iterate through each segment
            $splitRouteSegments = explode("/", trim($key, "/"));

            // Now we just navigate to the last segment and add the middleware node "|".
            // We just check what it is and then just navigate,
            $currentNode = &$compiledTrie;

            // So we just check one of three things: is there a literal route to navigate to?
            // is there a dynamic route to navigate to? or is it a middleware node? WE JUST NAVIGATE TO IT
            // until we run out of segments, that means we have reached the node where we insert the middleware node "|".
            foreach ($splitRouteSegments as $segment) {
                // SPECIAL CASE: Navigate past any middleware node "|" but not at root node!
                if (isset($currentNode['|']) && !empty($currentNode['|'])) {
                    $currentNode = &$currentNode['|'];
                }

                // LITERAL ROUTE SEGMENT
                if (isset($currentNode[$segment])) {
                    $currentNode = &$currentNode[$segment];
                    continue;
                }

                // DYNAMIC ROUTE SEGMENT
                elseif (str_starts_with($segment, ":")) {
                    $paramName = substr($segment, 1);
                    $currentNode = &$currentNode[':'][$paramName];
                    continue;
                }
            }

            // Now we are at the last segment, we just add the middleware node "|"
            // and then we add the middleware route to it.
            if (!isset($currentNode['|']) && isset($value['middlewares']) && !empty($value['middlewares'])) {
                $currentNode['|'] = [];
            }
        }
    };

    // First add the single routes to the compiled trie
    $compiledTrie['GET'] = $addMethods($GETSingles);
    $compiledTrie['POST'] = $addMethods($POSTSingles);
    $compiledTrie['PUT'] = $addMethods($PUTSingles);
    $compiledTrie['DELETE'] = $addMethods($DELETESingles);
    $compiledTrie['PATCH'] = $addMethods($PATCHSingles);

    // Then add the middlewares to the compiled trie and return it
    $addMiddlewareRoutes($developerMiddlewareRoutes["GET"] ?? [], $compiledTrie['GET']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["POST"] ?? [], $compiledTrie['POST']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["PUT"] ?? [], $compiledTrie['PUT']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["DELETE"] ?? [], $compiledTrie['DELETE']);
    $addMiddlewareRoutes($developerMiddlewareRoutes["PATCH"] ?? [], $compiledTrie['PATCH']);

    return $compiledTrie;
}

// Output Compiled Route to File or Return as String
function cli_output_compiled_routes(array $compiledTrie, string $outputFileNameFolderIsAlways_compiled_routes = "null")
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }
    // Check if the compiled route is empty
    if (!is_array($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }
    if (empty($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }

    // Output either to file destiation or in current folder as datetime in file name
    $datetime = date("Y-m-d_H-i-s");
    $outputDestination = $outputFileNameFolderIsAlways_compiled_routes === "null" ? dirname(__DIR__) . "/compiled/troute_" . $datetime . ".php" : dirname(__DIR__) . "/compiled/" . $outputFileNameFolderIsAlways_compiled_routes . ".php";

    $result = null;
    if ($outputFileNameFolderIsAlways_compiled_routes !== "null") {
        $result = file_put_contents(dirname(__DIR__) . "/compiled/" . $outputFileNameFolderIsAlways_compiled_routes . ".php", "<?php\nreturn " . cli_convert_array_to_simple_syntax($compiledTrie));
    } else {
        $result = file_put_contents($outputDestination, "<?php\nreturn " . cli_convert_array_to_simple_syntax($compiledTrie));
    }
    if ($result === false) {
        echo "\033[31m[FunkCLI - ERROR]: FAILED to Recompile Trie Route: \"funkphp/_internals/compiled/troute_route.php\"!\n\033[0m";
    } else {
        echo "\033[32m[FunkCLI - SUCCESS]: Recompiled Trie Route: \"funkphp/_internals/compiled/troute_route.php\"!\n\033[0m";
    }
}

// Convert PHP array() syntax to simplified [] syntax
function cli_convert_array_to_simple_syntax(array $array): string | null | array
{
    // Must be non-empty array
    if (!is_array($array)) {
        cli_err_syntax("Array must be a non-empty array!");
        exit;
    }

    // Check if the array is empty
    if (empty($array)) {
        cli_err_syntax("Array must be a non-empty array!");
        exit;
    }

    // Prepare array and parse state variables
    $str = mb_str_split(var_export($array, true));
    $arrStack = [];
    $arrayLetters = ["a", "r", "r", "a", "y", " "];
    $quotes = ["'", '"'];
    $inStr = false;
    $converted = "";

    // Check if first character is "a"
    if ($str[0] !== "a") {
        echo "[cli_convert_array_to_simple_syntax - ERROR]: The array should start with the letter 'a' as in array()!\n";
        exit;
    }

    // Parse on each character of the prepared string
    for ($i = 0; $i < count($str); $i++) {
        $c = $str[$i];

        // If inside string and is not a quote
        if ($inStr && (!in_array($c, $quotes) && $c !== "\\")) {
            $converted .= $c;
            continue;
        }
        // If inside string with escaped character, just skip it
        elseif ($inStr && ($c === "\\")) {
            $i++;
            continue;
        }
        // If inside string and is a quote
        elseif ($inStr && (in_array($c, $quotes))) {
            $converted .= $c;
            $inStr = false;
            continue;
        }

        // If not inside string and is a quote
        if (!$inStr && empty($arrStack) && (in_array($c, $quotes))) {
            $inStr = true;
            $converted .= $c;
            continue;
        }

        // If not inside string and next character is "a" from "array (" & not from false boolean
        if (!$inStr && empty($arrStack)  && $c === "a" && $str[$i + 1] !== "l") {
            $arrStack[] = $c;
            continue;
        }

        // If not inside string and next character is one from:"rray ("
        if (!$inStr && !empty($arrStack)) {
            if (count($arrStack) < 5 && in_array($c, $arrayLetters)) {
                $arrStack[] = $c;
                continue;

                // If not inside string and next character is "(" from "array ("
            } elseif (count($arrStack) === 5 && $c === "(") {
                $converted .= "[";
                unset($arrStack);
                continue;
            }
        }

        // If outside string and ")"
        if (!$inStr && $c === ")") {
            $converted .= "]";
            continue;
        }
        $converted .= $c;
    }

    // Return the finalized string varaible
    $converted .= ";";
    return $converted;
}

// Restore essentially the "funkphp" folder and all its subfolders if they do not exist!
function cli_restore_default_folders_and_files()
{
    if ($_SERVER['SCRIPT_NAME'] !== 'funkcli') {
        exit;
    }

    // Prepare what folders to loop through and create if they don't exist!
    $folderBase = dirname(dirname(__DIR__));
    $folders = [
        "$folderBase",
        "$folderBase/_BACKUPS/",
        "$folderBase/_BACKUPS/_FINAL_BACKUPS/",
        "$folderBase/_BACKUPS/compiled/",
        "$folderBase/_BACKUPS/data/",
        "$folderBase/_BACKUPS/handlers/",
        "$folderBase/_BACKUPS/middlewares/",
        "$folderBase/_BACKUPS/pages/",
        "$folderBase/_BACKUPS/routes/",
        "$folderBase/_BACKUPS/sql/",
        "$folderBase/_BACKUPS/templates/",
        "$folderBase/_BACKUPS/validations/",
        "$folderBase/_internals/",
        "$folderBase/_internals/compiled/",
        "$folderBase/_internals/functions/",
        "$folderBase/_internals/templates/",
        "$folderBase/cached/",
        "$folderBase/cached/pages/",
        "$folderBase/cached/json/",
        "$folderBase/cached/files/",
        "$folderBase/config/",
        "$folderBase/data/",
        "$folderBase/_dx_steps/",
        "$folderBase/middlewares/",
        "$folderBase/pages/",
        "$folderBase/pages/complete/",
        "$folderBase/pages/parts/",
        "$folderBase/routes/",
        "$folderBase/tests/",
        "$folderBase/templates/",
        "$folderBase/sql/",
        "$folderBase/validations/",
    ];

    // Prepare default files that doesn't exist if certain folders don't exist
    $defaultFiles = [
        "$folderBase/_internals/compiled/troute_route.php",
        "$folderBase/routes/route_single_routes.php",
    ];

    // Create folderBase if it does not exist
    if (!is_dir($folderBase)) {
        mkdir($folderBase, 0777, true);
    }
    // Loop through each folder and create it if it does not exist
    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
            echo "\033[32m[FunkCLI - SUCCESS]: Recreated folder: $folder\n\033[0m";
        }
    }
    // Loop through files, and create them if they don't exist
    foreach ($defaultFiles as $file) {
        if (!file_exists($file)) {
            // Recreate default files based on type ("troute", "middleware routes" or "single routes")
            if (str_contains($file, "troute")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [];\n?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } elseif (str_contains($file, "single")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [\n'ROUTES' => \n['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [],]];\n?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            }
        }
    }
}

// Check if a Data File Handler in data/ exists
function cli_data_handler_file_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_data_handler_file_exists] Data Handler File name must be a non-empty string!");
    }
    // Lowercase the file name
    $fileName = strtolower($fileName);
    // Add ".php" if not already
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Add "d_" if not already
    if (!str_starts_with($fileName, "d_")) {
        $fileName = "d_" . $fileName;
    }
    // Return true if file exists in handlers/D/ folder, false otherwise
    if (file_exists($dirs['data'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if a Page File in pages/ exists
function cli_page_file_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_page_file_exists] Page File name must be a non-empty string!");
    }
    // Lowercase the file name
    $fileName = strtolower($fileName);
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in handlers/P/ folder, false otherwise
    if (file_exists($dirs['pages'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if a Route File Handler in handlers/ exists
function cli_route_handler_file_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_route_handler_file_exists] Route Handler File name must be a non-empty string!");
    }
    // Lowercase the file name
    $fileName = strtolower($fileName);
    // Add ".php" if not already
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Add "r_" if not already
    if (!str_starts_with($fileName, "r_")) {
        $fileName = "r_" . $fileName;
    }
    // Return true if file exists in handlers/ folder, false otherwise
    if (file_exists($dirs['handlers'] . $fileName)) {
        return true;
    }
    return false;
}

// Check if a Middleware Handler in middlewares/ exists
function cli_middleware_exists($fileName): bool
{
    // Load globals, verify & transform string with .php if not already
    global $argv,
        $settings,
        $dirs,
        $exactFiles;
    if (!is_string($fileName) || empty($fileName)) {
        cli_err_syntax("[cli_middleware_exists] Middleware Route Handler File name must be a non-empty string!");
    }
    if (!str_starts_with($fileName, "m_")) {
        $fileName = "m_" . $fileName;
    }
    if (!str_ends_with($fileName, ".php")) {
        $fileName .= ".php";
    }
    // Return true if file exists in middlewares/R/ folder, false otherwise
    if (file_exists($dirs['middlewares'] . $fileName)) {
        return true;
    }
    return false;
}

// Output file until success (by waiting one second and retrying with new file name that is the file name + new datetime and extension    )
function cli_output_file_until_success($outputPathWithoutExtension, $extension, $outputData, $customSuccessMessage = "")
{
    // First check not empty strings
    if (
        !is_string($outputPathWithoutExtension) ||  !is_string($extension) || !is_string($outputData)
        || $outputPathWithoutExtension === "" || $extension === "" || $outputData === ""
    ) {
        cli_err_syntax("Output path, extension and data must be non-empty strings!");
    }

    // Check extension is valid (starting with ".") and ending with only characters
    if (!str_starts_with($extension, ".")) {
        cli_err_syntax("Output extension must start with '.' and only contain characters!");
    }

    // Check preg_match for extension which is (.[a-zA-Z0-9-_]+$)
    if (!preg_match("/\.[a-zA-Z0-9-_]+$/", $extension)) {
        cli_err_syntax("Output extension must start with '.' and only contain characters (a-zA-Z0-9-_)!");
    }

    // Check that output path exists (each folder in the path must exist)
    $outputPath = dirname($outputPathWithoutExtension);
    if (!is_dir($outputPath)) {
        cli_err_syntax("Output path must be a valid directory. Path: $outputPath is not!");
    }
    if (!is_writable($outputPath)) {
        cli_err_syntax("Output path must be writable! Path: $outputPath is not!");
    }

    // Now create first datetime string and file name and try to write it (by checking if that exact output file path exists)
    // If it exists, we wait one second and try again with new datetime string and file name
    $datetime = date("Y-m-d_H-i-s");
    $success = false;
    $outputFilePath = $outputPathWithoutExtension . "_" . $datetime . $extension;
    while (!$success) {
        if (file_exists($outputFilePath)) {
            cli_info_without_exit("Output file already exists: $outputFilePath! Trying again in 1 second...");
            sleep(1);
            $datetime = date("Y-m-d_H-i-s");
            $outputFilePath = $outputPathWithoutExtension . "_" . $datetime . $extension;
        } else {
            // Try to write the file
            $result = file_put_contents($outputFilePath, $outputData);
            if ($result === false) {
                cli_err("Output file FAILED to write: $outputFilePath!");
            } else {
                if ($customSuccessMessage !== "") {
                    cli_success_without_exit($customSuccessMessage);
                    $success = true;
                } else {
                    cli_success_without_exit("Output file written SUCCESSFULLY: $outputFilePath!");
                    $success = true;
                }
            }
        }
    }
}

// Backup batch of files based on the array of files (string values) to backup
// Function uses "cli_backup_file_until_success"!
function cli_backup_batch($arrayOfFilesToBackup)
{
    // Check if the array is a non-empty array
    if (!is_array($arrayOfFilesToBackup) || empty($arrayOfFilesToBackup)) {
        cli_err_syntax("Array of files to backup must be a non-empty array!");
    }

    // Load $dirs, $exactFiles as globals
    global $dirs, $exactFiles, $settings;

    // Prepare paths for all possible that could be backed up
    // Backup paths
    $backupFinalsPath = $dirs['backups_finals'];
    $backupCompiledPath = $dirs['backups_compiled'];
    $backupRouteRoutePath = $dirs['backups_routes'];
    $backupDataRoutePath = $dirs['backups_data'];
    $backupPageRoutePath = $dirs['backups_pages'];

    // Single Route Routes (including Middlewares)
    $oldTrouteRouteFile = $exactFiles['troute_route'];
    $oldSingleRouteRouteFile = $exactFiles['single_routes'];

    // Now backup the old route files based on provided $filesString
    // Loop through each file in the array and backup it
    foreach ($arrayOfFilesToBackup as $fileString) {
        if ($fileString === "troutes") {
            // Routes
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_BACKUPS']) {
                cli_backup_file_until_success($backupCompiledPath . "troute_route", ".php", $oldTrouteRouteFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['COMPILED_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "troute_route", ".php", $oldTrouteRouteFile);
            }
            continue;
        }
        if ($fileString === "routes") {
            // Single Route Routes & Middlewares
            if ($settings['ALWAYS_BACKUP_IN']['ROUTES_IN_BACKUPS']) {
                cli_backup_file_until_success($backupRouteRoutePath . "route_single_routes", ".php", $oldSingleRouteRouteFile);
            }
            if ($settings['ALWAYS_BACKUP_IN']['ROUTES_IN_FINAL_BACKUPS']) {
                cli_backup_file_until_success($backupFinalsPath . "route_single_routes", ".php", $oldSingleRouteRouteFile);
            }
            continue;
        }
        if ($fileString === "data") {
            if ($settings['ALWAYS_BACKUP_IN']['DATA_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['DATA_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "pages") {
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "handlers") {
            if ($settings['ALWAYS_BACKUP_IN']['HANDLERS_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['HANDLERS_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "middlewares") {
            if ($settings['ALWAYS_BACKUP_IN']['MIDDLEWARES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['MIDDLEWARES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "templates") {
            if ($settings['ALWAYS_BACKUP_IN']['TEMPLATES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['TEMPLATES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "sql") {
            if ($settings['ALWAYS_BACKUP_IN']['SQL_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['SQL_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "tests") {
            if ($settings['ALWAYS_BACKUP_IN']['TESTS_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['TESTS_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "config") {
            if ($settings['ALWAYS_BACKUP_IN']['CONFIG_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['CONFIG_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "cached") {
            if ($settings['ALWAYS_BACKUP_IN']['CACHED_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['CACHED_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "validations") {
            if ($settings['ALWAYS_BACKUP_IN']['VALIDATIONS_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['VALIDATIONS_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
    }
}

// Delete a Single Route from the Route file (funkphp/routes/route_single_routes.php)
// and delete its associated Handler Function (and Handler File if last function)
// It does NOT delete validation files, or page files unless specifically specified!
function cli_delete_a_route()
{
    // Load globals and validate input
    global
        $argv, $dirs, $exactFiles,
        $settings,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax_without_exit("Provide a valid Route to delete from the Route file!\nExample: \"php funkcli delete [route|r] [method/route_name]\"");
        cli_info("IMPORTANT: Its associated Handler Function (and Handler File if last function) will be deleted as well!\n");
    }
    // argv[4] is optional and can be used to delete the validation handler
    $deleteValidationHandler = false;
    if (isset($argv[4]) && is_string($argv[4]) && strtolower($argv[4]) === "with_validation") {
        $deleteValidationHandler = true;
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $deleteRoute = trim(strtolower($argv[3]));
    $oldRoute = $deleteRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($deleteRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that provided route exists
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute])) {
        cli_err("Route: \"$method$validRoute\" does not exist. Another HTTP Method or was it deleted already?");
    }

    // HERE we found the route so we can delete it
    // First backup all associated route files if settings allow it
    cli_backup_batch(
        [
            "troutes",
            "routes",
        ]
    );
    // Grab handlers for 'handler' and 'data' from the route array
    $middlewares = $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'] ?? null;
    $handler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['handler'] ?? null;
    $datahandler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['data'] ?? null;
    $validationHandler = $singleRoutesRoute['ROUTES'][$method][$validRoute]['validation'] ?? null;

    // Then we unset() each matched route
    unset($singleRoutesRoute['ROUTES'][$method][$validRoute]);
    cli_success_without_exit("Deleted Route \"$method$validRoute\" from Routes File!");

    // Then we rebuild and recompile Routes
    cli_rebuild_single_routes_route_file($singleRoutesRoute);
    $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
    cli_output_compiled_routes($compiledRouteRoutes, "troute_route");

    // Send the handler variable to delete it (this will
    // also delete file if it's the last function in it!)
    // But we only call them if they are not null or empty strings
    if ($handler !== null && !empty($handler)) {
        delete_handler_file_with_fn_or_just_fn_or_err_out("r", $handler);
    }
    if ($datahandler !== null && !empty($datahandler)) {
        // We check if the data handler exists before deleting it
        delete_handler_file_with_fn_or_just_fn_or_err_out("d", $datahandler);
    }
    // Only delete the validation handler if it is not null or empty string
    // and if the user provided the "with_validation" argument
    if ($validationHandler !== null && !empty($validationHandler)) {
        if ($deleteValidationHandler) {
            // We check if the validation handler exists before deleting it
            delete_handler_file_with_fn_or_just_fn_or_err_out("v", $validationHandler);
        } else {
            if (is_string($validationHandler)) {
                cli_info_without_exit("Validation Handler \"$validationHandler\" for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            } elseif (is_array($validationHandler) && array_is_list($validationHandler)) {
                cli_info_without_exit("Validation Handler \"$validationHandler[0]\" for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            } elseif (is_array($validationHandler)) {
                cli_info_without_exit("Validation Handler \"{$validationHandler[key($validationHandler)]}\" for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            } else {
                cli_info_without_exit("Validation Handler for \"$method$validRoute\" was NOT deleted since \"with_validation\" argument was not provided!");
            }
        }
    }

    // If "middlewares" is not null and not empty string/array
    // then we list the middlewares and inform that the middlewwares
    // where deleted from route but not as files!
    if ($middlewares !== null && !empty($middlewares)) {
        if (is_string($middlewares)) {
            cli_info_without_exit("\"$method$validRoute\" used the following middleware: \"$middlewares\" from \"funkphp/middlewares/\"!");
        } elseif (is_array($middlewares) && array_is_list($middlewares)) {
            // Join all as a string with ", " separator
            $middlewares = implode(", ", $middlewares);
            cli_info_without_exit("\"$method$validRoute\" used the following middleware(s): \"$middlewares\" from \"funkphp/middlewares/\"!");
        }
    }
}

// Delete a Handler Function or entire Handler File if it is the last function in it
function cli_delete_a_handler_function_or_entire_file($handlerVar)
{
    // Load globals and validate input
    global
        $argv, $dirs, $exactFiles,
        $settings;

    // $handlerVar must either be a string or an array with a single string value!
    if (!is_string($handlerVar) && !is_array($handlerVar)) {
        cli_err_syntax_without_exit("The Handler argument must be 1) One string or 2) One array with one string!");
        cli_err_syntax("Example: \"[HandlerFile|HandlerFile=>Function] (the variable structure, not as a string!)\"");
    }

    // If it is a string, check that it is valid and not empty
    if (is_string($handlerVar) && empty($handlerVar)) {
        cli_err_syntax("\"$handlerVar\" must be a non-empty string!");
    }

    // Prepare what is the handler file, function name, and handlers folder
    $handlerFile = null;
    $fnName = null;
    $handlersFolder = $dirs['handlers'];

    // If it is a string, check for "=>" because this function is either called by deleting a route
    // or just by deleting a handler function directly meaning the handlerFile=>Function would be
    // passed as a string and not as an array with one string value in the case of deleting a route.
    if (is_string($handlerVar)) {
        if (strpos($handlerVar, '=>') !== false) {
            [$handlerFile, $fnName] = explode('=>', $handlerVar);
            $handlerFile = trim($handlerFile);
            $fnName = trim($fnName);
        } else {
            $handlerFile = $handlerVar;
            $fnName = $handlerFile;
        }
    } elseif (is_array($handlerVar)) {
        $handlerFile = key($handlerVar);
        $fnName = $handlerVar[$handlerFile];
    }

    // Check that the handler file and function name are not empty strings with invalid characters
    if (!preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" Route Handler File must be a lowercase string containing only letters, numbers and underscores!");
    }
    if (!preg_match('/^[a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" Route Handler Function Name must be a lowercase string containing only letters, numbers and underscores!");
    }

    // We now check if $fnName and $handlerFile both start with "r_" and if not
    // then we add it to the handler file name. This to not conflict with other
    // types of handlers that might be included into the global scope of functions
    // such as data ("d_"), page ("p_") and/or middleware ("m_") handlers.
    if (!str_starts_with($handlerFile, "r_")) {
        $handlerFile = "r_" . $handlerFile;
    }
    if (!str_starts_with($fnName, "r_")) {
        $fnName = "r_" . $fnName;
    }

    // We now check if the handler file exists in the handlers folder, add .php if not
    if (!file_exists($handlersFolder . $handlerFile . ".php")) {
        cli_err("Route Handler File \"$handlerFile\" not found in \"funkphp/handlers/\"!");
    }

    // We now read the file content and check for the delimiter function name
    // as such: "//NEVER_TOUCH_ANY_COMMENTS_START|END=$handlerFile". Both
    // must exist otherwise we cannot be certain it is a valid handler file.
    $fileContent = file_get_contents($handlersFolder . $handlerFile . ".php");
    if (
        strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName") === false
        || strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_END=$fnName") === false
    ) {
        cli_err("Route Handler Function \"$fnName\" in Route Handler \"$handlerFile\" not found or invalid structure!");
    }

    // We now match the number of "//DELIMITER_HANDLER_FUNCTION_START" and "//DELIMITER_HANDLER_FUNCTION_END"
    // in order to know how many functions are in the file. If it is 1, we then check if it is the last function
    // and thus delete entire file. If it is more than 1, we just delete the function and leave the file intact.
    // We do this by using preg_match_all() to count the number of matches in the file content.
    $startMatches = preg_match_all("/\/\/DELIMITER_HANDLER_FUNCTION_START=/", $fileContent, $matchesStart);
    $endMatches = preg_match_all("/\/\/DELIMITER_HANDLER_FUNCTION_END=/", $fileContent, $matchesEnd);
    if ($startMatches === false || $endMatches === false) {
        cli_err("Failed to find the Route Handler Functions in the Handler File \"$handlerFile\"!");
    }

    // If matches are uneven, the file structure is invalid and we cannot delete it
    if ($startMatches !== $endMatches) {
        cli_err("The Route Handler File \"$handlerFile\" has an invalid structure! Every \"//DELIMITER_HANDLER_FUNCTION_START=\" should have a matching \"//DELIMITER_HANDLER_FUNCTION_END=\"!");
    }

    // We now check if the number of matches is 1, meaning it is the last
    // function in the file and thus we delete the entire file. If it is
    // more than 1, we just delete the function and leave the file intact.
    if ($startMatches === 1 && $endMatches === 1) {
        // TODO: Add Backup Fn that backups the file before deleting!
        // Delete the entire file
        if (unlink($handlersFolder . $handlerFile . ".php")) {
            cli_success_without_exit("Deleted Route Handler File \"handlers/$handlerFile.php\" and Route Handler Function \"$fnName\"!");
            return; // We return here since we deleted the file and don't need to run the rest of the code
        } else {
            cli_err("FAILED to delete Route Handler File \"handlers/$handlerFile.php\" and Route Handler Function \"$fnName\"!");
        }
    }
    // Here we know we have more than 1 match and that we have same number of matches
    // We now wanna find: //DELIMITER_HANDLER_FUNCTION_START=$fnName and //DELIMITER_HANDLER_FUNCTION_END=$fnName
    // in order to find the starting position and ending position of the function in the file content so we can
    // just replace/delete that part of the file content and then write it back to the file.
    $startPos = strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_START=$fnName");
    $endPos = strpos($fileContent, "//DELIMITER_HANDLER_FUNCTION_END=$fnName") + mb_strlen("//DELIMITER_HANDLER_FUNCTION_END=$fnName") + 1;
    if ($startPos === false || $endPos === false) {
        cli_err("Failed to find the Route Handler Function \"$fnName\" in the Route Handler File \"handlers/$handlerFile.php\"!");
    }
    // Start position should NOT be larger than end position!
    if ($startPos > $endPos) {
        cli_err("The Route Handler File \"handlers/$handlerFile.php\" has an invalid structure! The start position is larger than the end position for \"$fnName\"!");
    }
    // We now replace the function in the file content with an empty string and write it back to the file
    $fileContent = substr_replace($fileContent, "", $startPos, $endPos - $startPos);

    // We write back the file content to the file and check if it was successful
    if (file_put_contents($handlersFolder . $handlerFile . ".php", $fileContent) !== false) {
        cli_success_without_exit("Deleted Route Handler Function \"$fnName\" from Route Handler File \"handlers/$handlerFile.php\"!");
    } else {
        cli_err("FAILED to delete Route Handler Function \"$fnName\" from Route Handler File \"handlers/$handlerFile.php\"!");
    }
}

// All-in-one function to Sort all keys in ROUTES, build Route file, recompile and output them!
function cli_sort_build_routes_compile_and_output($singleRoutesRootArray)
{
    // Validate input
    if (!is_array($singleRoutesRootArray) || empty($singleRoutesRootArray) || !isset($singleRoutesRootArray['ROUTES'])) {
        cli_err_syntax("The Routes Array must be a non-empty array starting with the ROUTES key!");
    }

    // Loop through each key below ROUTES and sort the keys
    // and values in the array by the key name (route name)
    foreach ($singleRoutesRootArray['ROUTES'] as $key => $value) {
        if (is_array($value)) {
            ksort($singleRoutesRootArray['ROUTES'][$key]);
        }
    }

    // First backup all associated route files if settings allow it
    cli_backup_batch(
        [
            "troutes",
            "routes",
        ]
    );

    // Then we rebuild and recompile Routes
    $rebuild = cli_rebuild_single_routes_route_file($singleRoutesRootArray);
    if ($rebuild) {
        cli_success_without_exit("Rebuilt Route file \"funkphp/routes/route_single_routes.php\"!");
    } else {
        cli_err("FAILED to rebuild Route file \"funkphp/routes/route_single_routes.php\". File permissions issue?");
    }
    $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRootArray['ROUTES'], $singleRoutesRootArray['ROUTES']);
    cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
}

// Add a Route to the Route file (funkphp/routes/) WITH
// a [RouteHandlerFile[=>RouteHandlerFunctionName]] too!
function cli_add_a_route()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;

    // Get Handler File, Function Name & Arrow and parsed Route
    [$handlerFile, $fnName, $arrow] =  get_handler_and_fn_from_argv4_or_err_out("r");
    [$method, $validRoute] = get_matched_route_from_argv3_or_err_out("r");

    // Check if the exact route already exists in the route file
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]) ?? null) {
        cli_err("\"$method$validRoute\" already exists in Routes!");
    }

    // Now we check against conflicting routes (dynamic routes) and if it exists, we error
    $findDynamicRoute = cli_match_developer_route($method, $validRoute, include_once $exactFiles['troute_route'], $singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
    if ($findDynamicRoute['route'] !== null) {
        cli_err_without_exit("Found Dynamic Route \"{$findDynamicRoute['method']}{$findDynamicRoute['route']}\" in Trie Routes would conflict with \"$method$validRoute\".");
        cli_info("Run `php funkcli compile r` to rebuild Trie Routes if you manually removed that Route you want to add again!");
    }

    // Prepare handlers folders and then attempt to create the handler
    // file with a function (or just a function in existing one)
    $handlersDir = $dirs['handlers'];
    create_handler_file_with_fn_or_fn_or_err_out("r", $handlersDir, $handlerFile, $fnName, $method, $validRoute, $argv[5] ?? null);

    // If we are here, that means we managed to add a handler with a function
    // name to a file so now we add route to the route file and then compile it!
    if ($arrow) {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
            'handler' => [$handlerFile => $fnName],
        ];
    } else {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = [
            'handler' => $handlerFile,
        ];
    }
    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Added Route \"$method$validRoute\" to \"funkphp/routes/route_single_routes.php\" with Handler \"$handlerFile\" and Function \"$fnName\"!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Adds a 'data' OR 'validation' handler to an existing route (errors out on non-existing)
function cli_add_a_data_or_a_validation_handler($handlerType)
{
    // Load globals and check $handlerType is valid (a string that is either 'v' or 'd')
    global $argv, $settings, $dirs, $exactFiles, $singleRoutesRoute;
    if (!is_string($handlerType) || empty($handlerType) || !in_array($handlerType, ['v', 'd'])) {
        cli_err_syntax("Handler Type must be a non-empty string that is either 'v' or 'd'!");
    }

    // Get Handler File, Function Name & Arrow and parsed Route
    [$handlerFile, $fnName, $arrow] =  get_handler_and_fn_from_argv4_or_err_out($handlerType);
    [$method, $validRoute] = get_matched_route_from_argv3_or_err_out($handlerType);
    $handlersPrefix = $handlerType === 'v' ? "Validation" : "Data";
    $handlersKeyPrefix = $handlerType === 'v' ? "validation" : "data";

    // Check if the exact route does not exist the route file
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute]) ?? null) {
        cli_err_without_exit("Route \"$method$validRoute\" not found in Routes. Add it first before adding a $handlersPrefix Handler!");
        cli_info("You can only add a $handlersPrefix Handler to an existing Route to not cause any undesired inconsistent behaviors!");
    }

    // Check that a data/validation handler does not already exist for the route
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute][$handlersKeyPrefix]) && !empty($singleRoutesRoute['ROUTES'][$method][$validRoute][$handlersKeyPrefix])) {
        cli_err_without_exit("A Data Handler for Route \"$method$validRoute\" already exists!");
        cli_info("Use command \"php funkcli delete $handlersKeyPrefix [method/route] [handlerFile[=>Function]]\" to delete it first!");
    }

    // When data/validation handler is empty which it should not be so we error out
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute][$handlersKeyPrefix]) && empty($singleRoutesRoute['ROUTES'][$method][$validRoute][$handlersKeyPrefix])) {
        cli_err("$handlersPrefix Handler for Route \"$method$validRoute\" is empty. Consider deleting it OR manually adding a $handlersPrefix Handler to it!");
    }

    // If we are here, that means we managed to add a data/validation handler with a function
    // name to a file so now we add route to the route file and then compile it!
    $handlersDir = $handlerType === 'v' ? $dirs['validations'] : $dirs['data'];
    create_handler_file_with_fn_or_fn_or_err_out($handlerType, $handlersDir, $handlerFile, $fnName, $method, $validRoute, $argv[5] ?? null);

    // If we are here, that means we managed to add a data/validation handler with a function
    // name to a file so now we add route to the route file and then compile it!
    if ($arrow) {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = array_merge($singleRoutesRoute['ROUTES'][$method][$validRoute], [
            $handlersKeyPrefix => [$handlerFile => $fnName],
        ]);
    } else {
        $singleRoutesRoute['ROUTES'][$method][$validRoute] = array_merge($singleRoutesRoute['ROUTES'][$method][$validRoute], [
            $handlersKeyPrefix => $handlerFile,
        ]);
    }

    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Added $handlersPrefix Handler \"$handlerFile\" and $handlersPrefix Function \"$fnName\" to Route \"$method$validRoute\" in the Routes File!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Add a single Middleware file to middleware folder (funkphp/middlewares/)
function cli_add_a_middleware()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;

    // Retrieve valid middleware name & the method and route from the arguments
    [$argv[4], $method, $validRoute] = get_valid_mw_string_and_matched_route_or_err_out("\nSyntax: `php funkcli add mw [Method/route] [Middleware_handler]`\nExample: `php funkcli add mw GET/users/:id validateUserId`");

    // Check that the exact route already exists in the route file
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute])) {
        cli_err("The Route \"$method$validRoute\" does not exist. Add it first!");
    }

    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']) && cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
        cli_err("Middleware \"$argv[4]\" already exists in \"$method$validRoute\"!");
    }

    // We will now check if the middleware already exists one URI level down the
    // current route. This means that if the route is GET/users/:id, we will check if
    // the middleware exists in GET/users/, and then GET/. We first split the route
    // on "/" and then loop through that array and
    $splittedURI = explode("/", trim($validRoute, "/"));
    $currentParentUri = '';

    // First check default root "/" route for the given method
    $checkUri = '/';
    if (
        isset($singleRoutesRoute['ROUTES'][$method][$checkUri])
        && cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$checkUri]['middlewares'] ?? null)
    ) {
        cli_err_without_exit("Middleware \"$argv[4]\" already exists in \"$method$checkUri\"!");
        cli_err("Adding it would to \"$method$validRoute\" would cause it to run twice!");
    }

    // Now we loop through the $splittedURI array and check if
    // the middleware exists when adding each segment of the URI
    foreach ($splittedURI as $uriSegment) {
        $currentParentUri .= '/' . $uriSegment;
        if (
            isset($singleRoutesRoute['ROUTES'][$method][$currentParentUri])
            && cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$currentParentUri]['middlewares'] ?? null)
        ) {
            cli_err_without_exit("Middleware \"$argv[4]\" already exists in \"$method$currentParentUri\"!");
            cli_err("Adding it would to \"$method$validRoute\" would cause it to run twice!");
        }
    }

    // Here we know the middleware can be added to the
    // current Route so prepare middleware folder & file name
    $mwDir = $dirs['middlewares'];
    $mwName = str_ends_with($argv[4], ".php") ? $argv[4] : $argv[4] . ".php";

    // We check if file exists already because then we do not need to create it.
    if (file_exists($mwDir . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[4].php\" already exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        // Create the middleware file with the function name and return a success message
        $date = date("Y-m-d H:i:s");
        $outputHandlerRoute = file_put_contents(
            $mwDir . $mwName,
            "<?php\n// Middleware \"$mwName\" \n// File created in FunkCLI on $date!\n\nreturn function (&\$c) {\n};\n?>"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Created new Middleware \"$argv[4].php\" in \"funkphp/middlewares/$mwName\"!");
        } else {
            cli_err("FAILED to create Middleware \"$argv[4]\". File permissions issue?");
        }
    }
    // File now created if not existed, so now we add it to the 'middlewares' handler (or create it if not existed)
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
        $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'][] = $argv[4];
    } else {
        $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'] = [$argv[4]];
    }
    cli_success_without_exit("Added Middleware \"$argv[4]\" to \"$method$validRoute\"!");
    // Finally we show success message and then sort, build, compile and output the routes
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Delete a single Middleware from a given method/route (does NOT delete the MW file!)
function cli_delete_a_middleware()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nfunkcli delete [mw|middleware] [method/route] [Middleware_name]\nExample: 'php funkcli delete mw GET/users/:id validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[4])) {
        cli_err_syntax("\"{$argv[4]}\" - Middleware name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Now we check if it starts with "m_" and if it doesn't then we add it so we can check if it exists in the middlewares folder
    // because it is ALWAYS with a "m_" to not conflict with other types of handlers that might use the same name in the future!
    if (!str_starts_with($argv[4], "m_")) {
        $argv[4] = "m_" . $argv[4];
    }

    // Grab middlewares folder and file name with .php extension
    // and then check if the file exists in the middlewares folder
    $mwFolder = $dirs['middlewares'];
    $mwName = str_ends_with($argv[4], ".php") ? $argv[4] : $argv[4] . ".php";
    if (file_exists($mwFolder . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[4].php\" exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        cli_err_without_exit("Middleware \"$argv[4].php\" not found in \"funkphp/middlewares\"!");
        cli_info("Maybe misspelled file name if you are already using \"$argv[4]\" in other routes?");
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    // Check that route actually exists for the given method in the route file
    if (!isset($singleRoutesRoute['ROUTES'][$method][$validRoute])) {
        cli_err("Route \"$method$validRoute\" does not exist. It cannot have the middleware \"$argv[4]\"!");
    }
    // Now check if "middleware" key exists in the route and if it does, check if the middleware exists in it
    if (isset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
        if (!cli_value_exists_as_string_or_in_array($argv[4], $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
            cli_err("Middleware \"$argv[4]\" not found in Route \"$method$validRoute\"!");
        } else {
            // Remove the middleware from the route, first check if it is an array or string
            if (is_array($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
                $key = array_search($argv[4], $singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']);
                unset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'][$key]);
                // Also remove middleware key if it is empty after removing the middleware
                if (empty($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares'])) {
                    unset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']);
                }
            } else {
                unset($singleRoutesRoute['ROUTES'][$method][$validRoute]['middlewares']);
            }
            // If successful, we show success message and then sort, build, compile and output the routes
            cli_sort_build_routes_compile_and_output($singleRoutesRoute);
            cli_success_without_exit("Removed Middleware \"$argv[4]\" from Route \"$method$validRoute\"!");
            cli_info_without_exit("The Middleware \"$argv[4].php\" still exists in \"funkphp/middlewares/\"!");
        }
    } else {
        cli_err("Route \"$method$validRoute\" has no middlewares!");
    }
}

// Delete a single Middleware from all methods with routes (does NOT delete the MW file!)
function cli_delete_a_middleware_from_all_routes()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax("Should be at least three(3) non-empty string arguments!\nphp funkcli delete [mw_from_all_routes|middleware_from_all_routes] [method/route] [Middleware_name]\nExample: 'php funkcli delete mw_from_all_routes validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[3])) {
        cli_err_syntax("\"{$argv[3]}\" - Middleware name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Now we check if it starts with "m_" and if it doesn't then we add it so we can check if it exists in the middlewares folder
    // because it is ALWAYS with a "m_" to not conflict with other types of handlers that might use the same name in the future!
    if (!str_starts_with($argv[3], "m_")) {
        $argv[3] = "m_" . $argv[3];
    }

    // Grab middlewares folder and file name with .php extension
    // and then check if the file exists in the middlewares folder
    $mwFolder = $dirs['middlewares'];
    $mwName = str_ends_with($argv[3], ".php") ? $argv[3] : $argv[3] . ".php";
    if (file_exists($mwFolder . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[3].php\" exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        cli_err_without_exit("Middleware \"$argv[3].php\" not found in \"funkphp/middlewares\"!");
        cli_info("Maybe misspelled file name if you are already using \"$argv[3]\" in other routes?");
    }

    // We will now loop through all routes and check if the middleware exists in them
    $removeCount = 0;
    foreach ($singleRoutesRoute['ROUTES'] as $method => $routes) {
        foreach ($routes as $route => $routeData) {
            // Check if the route has the middleware in it, and if it does, remove it; be it a string or inside an array
            if (isset($routeData['middlewares']) && cli_value_exists_as_string_or_in_array($argv[3], $routeData['middlewares'])) {
                if (is_array($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                    $key = array_search($argv[3], $singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'][$key]);
                    // Also remove middleware key if it is empty after removing the middleware
                    if (empty($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                        unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    }
                } else {
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                }
                cli_info_without_exit("Removed Middleware \"$argv[3]\" from Route \"$method$route\"!");
                $removeCount++;
            }
        }
    }

    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Removed Middleware \"$argv[3]\" from $removeCount Routes!");
    cli_info_without_exit("The Middleware \"$argv[3].php\" still exists in \"funkphp/middlewares/\"!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Delete an actual Middleware file from the middlewares folder (funkphp/middlewares/)
// This also removes it from every route it is used in, so be careful with this one!
function cli_delete_a_middleware_file()
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $singleRoutesRoute;
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax("Should be at least three(3) non-empty string arguments!\nphp funkcli delete [mw_from_all_routes|middleware_from_all_routes] [method/route] [Middleware_name]\nExample: 'php funkcli delete mw_from_all_routes validateUserId'");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[3])) {
        cli_err_syntax("\"{$argv[3]}\" - Middleware name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Now we check if it starts with "m_" and if it doesn't then we add it so we can check if it exists in the middlewares folder
    // because it is ALWAYS with a "m_" to not conflict with other types of handlers that might use the same name in the future!
    if (!str_starts_with($argv[3], "m_")) {
        $argv[3] = "m_" . $argv[3];
    }

    // Grab middlewares folder and file name with .php extension
    // and then check if the file exists in the middlewares folder
    $mwFolder = $dirs['middlewares'];
    $mwName = str_ends_with($argv[3], ".php") ? $argv[3] : $argv[3] . ".php";
    if (file_exists($mwFolder . $mwName)) {
        cli_info_without_exit("Middleware \"$argv[3].php\" exists in \"funkphp/middlewares/$mwName\"!");
    } else {
        cli_err_without_exit("Middleware \"$argv[3].php\" not found in \"funkphp/middlewares\"!");
        cli_info("Maybe misspelled file name if you are already using \"$argv[3]\" in other routes?");
    }

    // We now try to unlink the file and check if it was successful
    if (unlink($mwFolder . $mwName)) {
        // TODO: Add a backup of the file to a backup folder before deleting it
        cli_success_without_exit("Deleted Middleware \"$argv[3].php\" from \"funkphp/middlewares/$mwName\"!");
        cli_info_without_exit("Moving on to removing it from all Routes that use it...");
    } else {
        cli_err_without_exit("FAILED to delete Middleware \"$argv[3].php\". File permissions issue?");
        cli_info("No Middleware handlers have been removed from the Routes since the file was not deleted!");
    }

    // We will now loop through all routes and check if the middleware exists in them
    $removeCount = 0;
    foreach ($singleRoutesRoute['ROUTES'] as $method => $routes) {
        foreach ($routes as $route => $routeData) {
            // Check if the route has the middleware in it, and if it does, remove it; be it a string or inside an array
            if (isset($routeData['middlewares']) && cli_value_exists_as_string_or_in_array($argv[3], $routeData['middlewares'])) {
                if (is_array($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                    $key = array_search($argv[3], $singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'][$key]);
                    // Also remove middleware key if it is empty after removing the middleware
                    if (empty($singleRoutesRoute['ROUTES'][$method][$route]['middlewares'])) {
                        unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                    }
                } else {
                    unset($singleRoutesRoute['ROUTES'][$method][$route]['middlewares']);
                }
                cli_info_without_exit("Removed Middleware \"$argv[3]\" from Route \"$method$route\"!");
                $removeCount++;
            }
        }
    }

    // Show success message and then sort, build, compile and output the routes
    cli_success_without_exit("Removed Middleware \"$argv[3]\" from $removeCount Routes after deleting the file!");
    cli_sort_build_routes_compile_and_output($singleRoutesRoute);
}

// Batched function of compiling and outputting routing files
function cli_compile_batch($arrayOfRoutesToCompileAndOutput)
{
    // Check if the array is a non-empty array
    if (!is_array($arrayOfRoutesToCompileAndOutput) || empty($arrayOfRoutesToCompileAndOutput)) {
        cli_err_syntax("Array of Routing Files to Compile & Output must be a non-empty array!");
    }

    // Load global routing files
    global $singleRoutesRoute;

    foreach ($arrayOfRoutesToCompileAndOutput as $routeString) {
        if ($routeString === "troutes") {
            $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRoute['ROUTES'], $singleRoutesRoute['ROUTES']);
            cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
            continue;
        }
    }
}

// Backup all files in a folder to another folder
function cli_backup_all_files_in_folder_to_another_folder($backupFolderDestinationWithoutExtension, $ext, $backupFolder)
{
    // Check that all three arguments are non-empty strings!
    if (
        !is_string($backupFolderDestinationWithoutExtension) ||  !is_string($ext) || !is_string($backupFolder)
        || $backupFolderDestinationWithoutExtension === "" || $ext === "" || $backupFolder === ""
    ) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder destination, file extension and backup folder must be non-empty strings!");
    }

    // Check that both dirs exist, are readable and writable
    if (!is_dir($backupFolderDestinationWithoutExtension)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder destination must be a valid directory. Path: $backupFolderDestinationWithoutExtension is not!");
    }
    if (!is_writable($backupFolderDestinationWithoutExtension)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder destination must be writable! Path: $backupFolderDestinationWithoutExtension is not!");
    }
    if (!is_dir($backupFolder)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder must be a valid directory. Path: $backupFolder is not!");
    }
    if (!is_readable($backupFolder)) {
        cli_err_syntax("[cli_backup_all_files_in_folder_to_another_folder] Backup folder must be readable! Path: $backupFolder is not!");
    }
    // We will now loop through the $backupFolder and call the cli_backup_file_until_success() function for each file in the folder
    // and that is not a folder itself. Those are just ignored (continue;)
    $files = scandir($backupFolder);
    $countOfCopiedFiles = 0;
    foreach ($files as $file) {
        if (is_dir($backupFolder . "/" . $file)) {
            continue;
        }
        // Check if the file ends with the extension
        if (str_ends_with($file, $ext)) {
            // Call the cli_backup_file_until_success() function for each file in the folder
            cli_backup_file_until_success($backupFolderDestinationWithoutExtension, $ext, $backupFolder . "/" . $file);
            $countOfCopiedFiles++;
        }
    }
    // Check if we copied any files
    if ($countOfCopiedFiles === 0) {
        cli_info("No files copied from $backupFolder to $backupFolderDestinationWithoutExtension!");
    } else {
        cli_success_without_exit("Copied $countOfCopiedFiles files from $backupFolder to $backupFolderDestinationWithoutExtension!");
    }
}

// Output backup file until success (by waiting one second and retrying with new file name that is the file name + new datetime and extension    )
function cli_backup_file_until_success($backupDestinationWithoutExtension, $extension, $backupData)
{
    // Check non-empty strings in all three variables
    if (
        !is_string($backupDestinationWithoutExtension) ||  !is_string($extension) || !is_string($backupData)
        || $backupDestinationWithoutExtension === "" || $extension === "" || $backupData === ""
    ) {
        cli_err_syntax("Backup destination, extension and exact backup data must be non-empty strings!");
    }

    // Check extension is valid (starting with ".") and ending with only characters
    if (!str_starts_with($extension, ".")) {
        cli_err_syntax("Backup extension must start with '.' and only contain characters!");
    }

    // Check preg_match for extension which is (.[a-zA-Z0-9-_]+$)
    if (!preg_match("/\.[a-zA-Z0-9-_]+$/", $extension)) {
        cli_err_syntax("Backup extension must start with '.' and only contain characters (a-zA-Z0-9-_)!");
    }

    // Check that backup destination exists (each folder in the path must exist)
    $backupDestination = dirname($backupDestinationWithoutExtension);
    if (!is_dir($backupDestination)) {
        cli_err_syntax("Backup destination must be a valid directory. Path: $backupDestination is not!");
    }
    if (!is_writable($backupDestination)) {
        cli_err_syntax("Backup destination must be writable! Path: $backupDestination is not!");
    }

    // Check that backup data file exists (each folder in the path must exist)
    if (!is_file($backupData)) {
        cli_err_syntax("Backup data file must be a valid file. Path: $backupData is not!");
    }
    if (!is_readable($backupData)) {
        cli_err_syntax("Backup data file must be readable! Path: $backupData is not!");
    }

    // Get the contents from the $backupData file before we write it to the backup file
    $backupData = file_get_contents($backupData);

    // Now we use the cli_backup_file_until_success function to create the backup file
    cli_output_file_until_success($backupDestinationWithoutExtension, $extension, $backupData, "Backup file written successfully: $backupDestinationWithoutExtension!");
}

// Restore a backup file from the backup directory to the restore file path (it also deletes the backup file after restoring it!)
function cli_restore_file($backupDirPath, $restoreFilePath, $fileStartingName)
{
    // Check non-empty strings in all variables
    if (
        !is_string($backupDirPath) ||  !is_string($restoreFilePath) || !is_string($fileStartingName)
        || $backupDirPath === "" || $restoreFilePath === "" || $fileStartingName === ""

    ) {
        cli_err_syntax("Backup Dir Path, Restore File Path and File Starting Name must be non-empty strings!");
    }

    // We check if backup dir path is a valid directory
    if (!is_dir($backupDirPath)) {
        cli_err_syntax("Backup Dir Path must be a valid directory. Path: $backupDirPath is not!");
    }

    // We check if backup dir path is readable
    if (!is_readable($backupDirPath)) {
        cli_err_syntax("Backup Dir Path must be readable! Path: $backupDirPath is not!");
    }

    // Lowercase the file starting name
    $fileStartingName = strtolower($fileStartingName);

    // We check if backup dir has any files in it. We sort descnding so we
    // get the latest file first due to the date time stamp in the file name
    $files = scandir($backupDirPath, SCANDIR_SORT_DESCENDING);
    if (count($files) <= 2) {
        cli_err_syntax("Backup Dir Path must have at least one file in it! Path: $backupDirPath has no files!");
    }

    // We loop through all the files in the backup dir path and check if they start with the file starting name
    // and if they do, we check if the file is readable and then we copy it to the restore file path
    foreach ($files as $file) {
        // Check if the file starts with the file starting name
        if (str_starts_with(strtolower($file), $fileStartingName)) {

            // Check if the file is readable
            if (!is_readable($backupDirPath . "/" . $file)) {
                cli_err("Backup file must be readable! Path: $backupDirPath/$file is not!");
            }

            // Copy the file to the restore file path and delete the backup file after restoring it
            copy($backupDirPath . "/" . $file, $restoreFilePath);
            unlink($backupDirPath . "/" . $file);
            cli_success_without_exit("Backup File Restored: $restoreFilePath!");
            return;
        }
    }
    // If we reach here, it means we didn't find any files that start with the file starting name
    cli_err("No Backup File in $backupDirPath starting with \"$fileStartingName\"!");
}

// Retrieve starting code for files created by the CLI
function cli_get_prefix_code($keyString)
{
    $currDate = date("Y-m-d H:i:s");
    $prefixCode = [
        "route_singles_routes_start" => "<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\nreturn ",
        "route_middleware_routes_start" => "<?php // ROUTE_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "data_middleware_routes_start" => "<?php // DATA_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "page_middleware_routes_start" => "<?php // PAGES_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "data_singles_routes_start" => "<?php // DATA_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return ",
        "page_singles_routes_start" => "<?php // PAGE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI $currDate\n return",
    ];

    return $prefixCode[$keyString] ?? null;
}

// Get a unique file name for a given directory and starting file name (so it checks with the starting file name and then adds a number to it)
function cli_get_unique_filename_for_dir($dirPath, $startingFileName, $middlewareException = false)
{
    // Check both are non-empty strings
    if (
        !is_string($dirPath) ||  !is_string($startingFileName)
        || $dirPath === "" || $startingFileName === ""
    ) {
        cli_err_syntax("Directory Path and Starting File Name must be non-empty strings!");
    }

    // Check if the starting file name is valid (it must not contain any special characters)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $startingFileName)) {
        cli_err_syntax("Starting File Name must only contain letters, numbers and underscores!");
    }

    // Check if the directory path is a valid directory
    if (!is_dir($dirPath)) {
        cli_err_syntax("Directory Path must be a valid directory. Path: $dirPath is not!");
    }

    // Check if the directory path is writable
    if (!is_writable($dirPath)) {
        cli_err_syntax("Directory Path must be writable! Path: $dirPath is not!");
    }

    // First do a quick check if the combined dir path and starting file name exists
    // add ".php" to the end of the file name
    $filePath = $dirPath . "/" . $startingFileName . ".php";
    if (file_exists($filePath)) {
        // If it exists, we need to add a number to the end of the file name
        $i = 1;
        while (file_exists($dirPath . "/" . $startingFileName . "_" . $i . ".php")) {
            $i++;
        }
        return $startingFileName . "-" . $i . ".php";
    }
    // If it doesn't exist, we just return the starting file name with ".php" added to the end of it
    return $startingFileName . ".php";
}

// Delete all files in a given directory, but not folders inside of it though!
function cli_delete_all_files_in_directory_except_other_directories($directoryPath)
{
    // Check if the directory path is a valid directory
    if (!is_dir($directoryPath)) {
        cli_err_syntax("Directory Path must be a valid directory. Path: $directoryPath is not!");
    }

    // Check if the directory path is writable
    if (!is_writable($directoryPath)) {
        cli_err_syntax("Directory Path must be writable! Path: $directoryPath is not!");
    }

    // Get all files in the directory
    $files = scandir($directoryPath);
    $filecount = count($files);

    // Loop through all files and delete them
    foreach ($files as $file) {
        // Check if file is not a directory and not "." or ".." and only then delete it
        if (is_dir($directoryPath . "/" . $file) || $file === "." || $file === "..") {
            continue;
        }
        unlink($directoryPath . "/" . $file);
    }
    cli_success_without_exit("$filecount Files Deleted in: $directoryPath!");
}

// Validate start syntax for route string before processing the rest of the string
// Valid ones are: "GET/g", "POST/po", "PUT/pu", "DELETE/d", "PATCH/pa"
function cli_valid_route_start_syntax($routeString)
{
    // First check that string is non-empty string
    if (!is_string($routeString) || empty($routeString)) {
        cli_err_syntax("Route string must be a non-empty string starting with a valid HTTP Method and then the Route!");
    }
    // Then we check if it starts with one of the valid ones
    if (str_starts_with($routeString, "get/") || str_starts_with($routeString, "post/") || str_starts_with($routeString, "put/") || str_starts_with($routeString, "delete/") || str_starts_with($routeString, "patch/")) {
        return true;
    } elseif (str_starts_with($routeString, "g/") || str_starts_with($routeString, "po/") || str_starts_with($routeString, "pu/") || str_starts_with($routeString, "d/") || str_starts_with($routeString, "pa/")) {
        return true;
    } else {
        return false;
    }
}

// Prepares a valid route string to by validating starting syntax and extracting the method from it
function cli_prepare_valid_route_string($addRoute)
{
    // Grab the route to add and validate correct starting syntax
    // first: get/put/post/delete/ or its short form g/pu/po/d/
    if (!cli_valid_route_start_syntax($addRoute)) {
        cli_err_syntax("Route string must start with one of the valid ones:\n'GET/' (or g/)'\n'POST/' (or po/)\n'PUT/'(or pu/)\n'DELETE/' (or d/)\n'PATCH/' (or pa/)");
    }
    // Try extract the method from the route string
    $method = cli_extracted_parsed_method_from_valid_start_syntax($addRoute);
    if ($method === null) {
        cli_err("Failed to parse the Method the Route string must start with (all of these below are valid):\n'GET/' (or g/)'\n'POST/' (or po/)\n'PUT/'(or pu/)\n'DELETE/' (or d/)\n'PATCH/' (or pa/)");
    }
    // Split route oon first "/" and add a a "/" to beginning of the route string
    // and then parse the rest of the string to build the route and its parameters
    $addRoute = explode("/", $addRoute, 2)[1] ?? null;
    $addRoute = "/" . $addRoute;
    $validRoute = cli_parse_rest_of_valid_route_syntax_better($addRoute);

    return [
        $method,
        $validRoute,
    ];
}

// Extract the method from the route string and parse the rest of the string
function cli_extracted_parsed_method_from_valid_start_syntax($routeString)
{
    // We now extract the method from the string and then begin
    // parsing the rest of the string character by character
    // to build the route and its parameters.
    $extractedMethod = explode("/", $routeString)[0];
    if ($extractedMethod == "get") {
        $parsedMethod = "GET";
    } elseif ($extractedMethod == "post") {
        $parsedMethod = "POST";
    } elseif ($extractedMethod == "put") {
        $parsedMethod = "PUT";
    } elseif ($extractedMethod == "delete") {
        $parsedMethod = "DELETE";
    } elseif ($extractedMethod == "g") {
        $parsedMethod = "GET";
    } elseif ($extractedMethod == "po") {
        $parsedMethod = "POST";
    } elseif ($extractedMethod == "pu") {
        $parsedMethod = "PUT";
    } elseif ($extractedMethod == "d") {
        $parsedMethod = "DELETE";
    } elseif ($extractedMethod == "pa") {
        $parsedMethod = "PATCH";
    } elseif ($extractedMethod == "pa") {
        $parsedMethod = "PATCH";
    } else {
        $parsedMethod = null;
    }
    return $parsedMethod ?? null;
}

// Parse the rest of the route string after the method has been extracted
// and return the valid built route string with
function cli_parse_rest_of_valid_route_syntax_better($routeString)
{
    // Variables for states and possible characters
    $BUILTRoute = "";
    $lastAddedC = "";
    $BUILTParam = "";
    $PARAMS = [];
    $IN_DYNAMIC = false;
    $IN_STATIC = false;
    $NEW_SEGMENT = false;
    $NUMS_N_CHARS = array_flip(
        array_merge(
            range('a', 'z'),
            range('0', '9'),
        )
    );
    $SEPARATORS = [
        "-" => [],
        "_" => [],
    ];
    $PARAM_CHAR = [":" => []];
    // Prepare segments by splitting the route string
    //  by "/" and also deleting empty segments
    $path = trim($routeString, '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    // Edge case: if the route string is empty, we just return "/"
    if (count($uriSegments) === 0) {
        return "/";
    }
    // Implode again and add a "/" to the beginning of the string
    $path = "/" . implode("/", $uriSegments);
    $len = strlen($path);
    // We now loop through.
    for ($i = 0; $i < $len; $i++) {
        $c = $path[$i];
        // Special case: only one character in the string which means we just
        // return "/"
        if ($len === 1) {
            return "/";
        }
        // First char is ALWAYS a "/"!
        if ($i === 0) {
            $BUILTRoute .= "/";
            $lastAddedC = "/";
            $NEW_SEGMENT = true;
            continue;
        }
        // Check if we are at the end of the string
        if ($i === $len - 1) {
            // Only allowed chars are: NUMS_N_CHARS
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                // Check if we in param building and if so, we
                // add the param to the params array unless it already exists
                if ($IN_DYNAMIC) {
                    $BUILTParam .= $c;
                    if (in_array($BUILTParam, $PARAMS)) {
                        cli_err_syntax("Duplicate parameter found in Route: \"$BUILTParam\"!");
                    }
                    $PARAMS[] = $BUILTParam;
                    $BUILTParam = "";
                }
                continue;
            }
            // Since we are at the end of the string, we check
            // if in dynamic building and if so, we add the
            //  param to the params array unless it already exists
            if ($IN_DYNAMIC) {
                if (in_array($BUILTParam, $PARAMS)) {
                    cli_err_syntax("Duplicate parameter found in Route: \"$BUILTParam\"!");
                }
                if ($BUILTParam !== "") {
                    // Check if built param ends with "_" or "-" and remove it
                    if (isset($SEPARATORS[$BUILTParam[strlen($BUILTParam) - 1]])) {
                        $BUILTParam = substr($BUILTParam, 0, -1);
                    }
                    $PARAMS[] = $BUILTParam;
                }
                $BUILTParam = "";
            }
            continue;
        }
        // First check if we are inside of a new segment building
        if ($NEW_SEGMENT) {
            // If new segment, then only allowed chars are: NUMS_N_CHARS or PARAM_CHAR
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $NEW_SEGMENT = false;
                $IN_STATIC = true;
                continue;
            }
            // Here a new ":" param starts!
            if (isset($PARAM_CHAR[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $NEW_SEGMENT = false;
                $IN_DYNAMIC = true;
                continue;
            }

            // Continue cause no allowed char is found!
            continue;
        }
        // Check if we are inside of a parameter building (meaning the previous char was ":")
        if ($IN_DYNAMIC) {
            // Check if next is a "/" meaning we reached the end of the static segment
            if ($c === "/") {
                // Here we check if the last added char was a separator too so we remove it
                // from the built route string before adding the "/", also from param string
                if (isset($SEPARATORS[$lastAddedC]) || isset($SEPARATORS[$c])) {
                    $BUILTRoute = substr($BUILTRoute, 0, -1);
                    $BUILTParam = substr($BUILTParam, 0, -1);
                }
                // Edge case when ":" appears right before "/"
                if ($lastAddedC === ":") {
                    $BUILTRoute = substr($BUILTRoute, 0, -1);
                    $IN_DYNAMIC = false;
                    $NEW_SEGMENT = true;
                    continue;
                }
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $IN_DYNAMIC = false;
                $NEW_SEGMENT = true;
                // We add the param to the params array unless it already exists
                if (in_array($BUILTParam, $PARAMS)) {
                    cli_err_syntax("Duplicate parameter found: $BUILTParam!");
                }
                // Add and reset the param string if not empty
                if ($BUILTParam !== "") {
                    $PARAMS[] = $BUILTParam;
                }
                $BUILTParam = "";
                continue;
            }
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $BUILTParam .= $c;
                $lastAddedC = $c;
                continue;
            }
            // In static, we only allow a separator char if the last added char was a separator too
            // like "_" or "-" and if so, we check if the current char is a separator too
            // meaning we will just ignore the current char and continue
            if ((isset($SEPARATORS[$lastAddedC]) || isset($PARAM_CHAR[$lastAddedC])) && isset($SEPARATORS[$c])) {
                continue;
            }
            // We allow a separator char if the last added char was a num or char
            // and if so, we check if the current char is a separator too
            if (!isset($SEPARATORS[$lastAddedC]) && isset($SEPARATORS[$c])) {
                $BUILTRoute .= $c;
                $BUILTParam .= $c;
                $lastAddedC = $c;
                continue;
            }
        }
        // Check if we are inside of a static building
        if ($IN_STATIC) {
            // Check if next is a "/" meaning we reached the end of the static segment
            if ($c === "/") {
                // Here we check if the last added char was a separator too so we remove it
                // from the built route string before adding the "/"
                if (isset($SEPARATORS[$lastAddedC]) || isset($SEPARATORS[$c])) {
                    $BUILTRoute = substr($BUILTRoute, 0, -1);
                }
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                $IN_STATIC = false;
                $NEW_SEGMENT = true;
                continue;
            }
            // In static, we first check if current char is just a num or char
            // and if so, we just add it to the built route string
            if (isset($NUMS_N_CHARS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                continue;
            }
            // In static, we only allow a separator char if the last added char was a separator too
            // like "_" or "-" and if so, we check if the current char is a separator too
            // meaning we will just ignore the current char and continue
            if (isset($SEPARATORS[$lastAddedC]) && isset($SEPARATORS[$c])) {
                continue;
            }
            // We allow a separator char if the last added char was a num or char
            // and if so, we check if the current char is a separator too
            if (!isset($SEPARATORS[$lastAddedC]) && isset($SEPARATORS[$c])) {
                $BUILTRoute .= $c;
                $lastAddedC = $c;
                continue;
            }
        }
    }
    // If more than 1 params, first extract last param from the params array
    // then check if it ends with "-" or "_" and if so, we remove it from that
    // param and check if both params are the same and thus throw error
    // otherwise add it again!
    if (count($PARAMS) > 1) {
        $lastParam = array_pop($PARAMS);
        if (isset($SEPARATORS[$lastParam[strlen($lastParam) - 1]])) {
            $lastParam = substr($lastParam, 0, -1);
            if (in_array($lastParam, $PARAMS)) {
                cli_err_syntax("Duplicate parameter found: $lastParam!");
            }
            $PARAMS[] = $lastParam;
        } else {
            if (in_array($lastParam, $PARAMS)) {
                cli_err_syntax("Duplicate parameter found: $lastParam!");
            }
            $PARAMS[] = $lastParam;
        }
    }
    if ($BUILTRoute === "" || $BUILTRoute === "/:") {
        $BUILTRoute = "/";
    }
    // We now remove "/:", "/", "-", "_" trailing at the end of the string
    if (strlen($BUILTRoute) > 2) {
        if (str_ends_with($BUILTRoute, "/:")) {
            $BUILTRoute = substr($BUILTRoute, 0, -2);
        } elseif (
            str_ends_with($BUILTRoute, "/")
            || str_ends_with($BUILTRoute, ":")
            || str_ends_with($BUILTRoute, "-")
            || str_ends_with($BUILTRoute, "_")
        ) {
            $BUILTRoute = substr($BUILTRoute, 0, -1);
        }
    }
    return $BUILTRoute;
}

// CLI Functions to show errors and success messages with colors
function cli_err_syntax($string)
{
    echo "\033[31m[FunkCLI - SYNTAX ERROR]: $string\n\033[0m";
    exit;
}
function cli_err($string)
{
    echo "\033[31m[FunkCLI - ERROR]: $string\n\033[0m";
    exit;
}
function cli_err_without_exit($string)
{
    echo "\033[31m[FunkCLI - ERROR]: $string\n\033[0m";
}
function cli_err_syntax_without_exit($string)
{
    echo "\033[31m[FunkCLI - SYNTAX ERROR]: $string\n\033[0m";
}
function cli_err_command($string)
{
    echo "\033[31m[FunkCLI - COMMAND ERROR]: $string\n\033[0m";
    exit;
}
function cli_success($string)
{
    echo "\033[32m[FunkCLI - SUCCESS]: $string\n\033[0m";
    exit;
}
function cli_info($string)
{
    echo "\033[34m[FunkCLI - INFO]: $string\n\033[0m";
    exit;
}
function cli_success_without_exit($string)
{
    echo "\033[32m[FunkCLI - SUCCESS]: $string\n\033[0m";
}
function cli_info_without_exit($string)
{
    echo "\033[34m[FunkCLI - INFO]: $string\n\033[0m";
}
function cli_info_multiline($string)
{
    echo "\033[34m[FunkCLI - INFO]: $string\n\033[0m";
}
function cli_warning($string)
{
    echo "\033[33m[FunkCLI - WARNING]: $string\n\033[0m";
    exit;
}
function cli_success_with_warning_same_line($string1, $string2)
{
    echo "\033[32m[FunkCLI - SUCCESS + WARNING]: $string1\033[0m";
    echo "\033[33m$string2\n\033[0m";
    exit;
}
function cli_err_with_info_same_line($string1, $string2)
{
    echo "\033[31m[FunkCLI - ERROR + INFO]: $string1\033[0m";
    echo "\033[34m$string2\n\033[0m";
    exit;
}
function cli_err_with_info_same_line_without_exit($string1, $string2)
{
    echo "\033[31m[FunkCLI - ERROR + INFO]: $string1\033[0m";
    echo "\033[34m$string2\n\033[0m";
}
function cli_err_with_warning_same_line($string1, $string2)
{
    echo "\033[31m[FunkCLI - ERROR + WARNING]: $string1\033[0m";
    echo "\033[33m$string2\n\033[0m";
    exit;
}
function cli_err_with_warning_same_line_without_exit($string1, $string2)
{
    echo "\033[31m[FunkCLI - ERROR + WARNING]: $string1\033[0m";
    echo "\033[33m$string2\n\033[0m";
}
function cli_success_with_info_same_line($string1, $string2)
{
    echo "\033[32m[FunkCLI - SUCCESS + INFO]: $string1\033[0m";
    echo "\033[34m$string2\n\033[0m";
    exit;
}
function cli_success_with_info_same_line_without_exit($string1, $string2)
{
    echo "\033[32m[FunkCLI - SUCCESS]: $string1\033[0m";
    echo "\033[34m$string2\n\033[0m";
}
function cli_success_with_warning_same_line_without_exit($string1, $string2)
{
    echo "\033[32m[FunkCLI - SUCCESS]: $string1\033[0m";
    echo "\033[33m$string2\n\033[0m";
}
function cli_warning_without_exit($string)
{
    echo "\033[33m[FunkCLI - WARNING]: $string\n\033[0m";
}

// Function loops through all function files in funkphp/_internals/functions/
// and preg matchdes "function ([a-zA-Z0-9_]+)" and then adds the function name to an
// array which is then converted to a [] array string using cli_convert_array_to_simple_syntax
// and then the FunkCLI file is open and the line "$reserved_functions = [...];" is replaced with the new array string
function cli_update_reserved_functions_list()
{
    global $dirs;
    $dir = $dirs['functions'];
    if (!dir_exists_is_readable_writable($dir)) {
        cli_err("Directory $dir does not exist or is not readable/writable!");
    }

    // Get all files in the directory
    $files = scandir($dir);
    $reserved_functions = [];

    // Loop through all files and check if they are PHP files
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === "php") {
            // Check that file name ends with "_funs.php" or exit
            if (!str_ends_with($file, "_funs.php")) {
                cli_info_without_exit("File $file not valid function file! Skipping it...");
                continue;
            }
            // Get the contents of the file
            $contents = file_get_contents($dir . $file);
            // Use preg_match to find all function names in the file
            // The line MUST begin with "function " and then a space and then the function name
            preg_match_all("/^function\s+([a-zA-Z0-9_]+)\(/m", $contents, $matches);
            // Add the function names to the reserved_functions array
            foreach ($matches[1] as $function_name) {
                $reserved_functions[] = $function_name;
            }
        }
    }

    // Convert the array to a string using cli_convert_array_to_simple_syntax
    $reserved_functions_string = cli_convert_array_to_simple_syntax($reserved_functions);
    $count = count($reserved_functions);

    // Replace all /\d+ => / with "" to remove the array keys
    $reserved_functions_string = preg_replace("/\d+\s*=>\s*/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\n/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\',/", "',\n", $reserved_functions_string, 1);
    echo "RESULT: $count Functions total!\nCOPY & PASTE THIS INTO FunkCLI at the \"\$reserved_functions = [...];\" lines!\n---------------------------------------------------------------------------\n\$reserved_functions = $reserved_functions_string\n---------------------------------------------------------------------------";
}

// Function that takes a variable ($existsInWhat) and then checks if a given value
// already exists in it as a string or in an array. It returns true if it does, false otherwise.
function cli_value_exists_as_string_or_in_array($valueThatExists, $existsInWhat)
{
    if ($existsInWhat === null) {
        return false;
    }
    if (is_array($existsInWhat)) {
        $existsInWhat = array_map('strtolower', $existsInWhat);
        return in_array($valueThatExists, $existsInWhat);
    } elseif (is_string($existsInWhat)) {
        $existsInWhat = strtolower($existsInWhat);
        return $valueThatExists === $existsInWhat;
    } else {
        return false;
    }
}

// Function takes a key and a value to add to it and then checks if referenced
// &$addToWhat exists or not, and if does exist, then it checks if it is an array
// otherwise it adds the key with value or it adds/pushes to the current array.
function cli_add_value_as_string_or_to_array($keyToAdd, $valueToAdd, &$addToWhat)
{
    if (array_key_exists($keyToAdd, $addToWhat)) {
        if (is_array($addToWhat[$keyToAdd])) {
            $addToWhat[$keyToAdd][] = $valueToAdd;
        } elseif (is_string($addToWhat[$keyToAdd])) {
            $addToWhat[$keyToAdd] = [$addToWhat[$keyToAdd], $valueToAdd];
        }
    } else {
        $addToWhat[$keyToAdd] = $valueToAdd;
    }
}

// Shorthand Boolean functions to check combined
// things for files, dirs and/or different data types
function dir_exists_is_readable_writable($dirPath)
{
    return is_dir($dirPath) && is_readable($dirPath) && is_writable($dirPath);
}
function file_exists_is_readable_writable($filePath)
{
    return is_file($filePath) && is_readable($filePath) && is_writable($filePath);
}
function is_array_and_not_empty($array)
{
    return isset($array) && is_array($array) && !empty($array);
}
function is_string_and_not_empty($string)
{
    return isset($string) && is_string($string) && !empty($string);
}

// Function takes a 'arrayKey' => 'singleStringvalue' and converts it to a
// string in the style:"arrayKey=>singleStringvalue". This is used in very
// sensitive places so we will return "<INVALID>=><INVALID>" instead of halting.
function flatten_single_array_key_to_a_string($arrayKeyWithSingleStringValue)
{
    if (is_array($arrayKeyWithSingleStringValue) && count($arrayKeyWithSingleStringValue) === 1) {
        $key = key($arrayKeyWithSingleStringValue);
        $value = $arrayKeyWithSingleStringValue[$key];
        if (is_string($key) && is_string($value)) {
            return strtolower($key) . "=>" . strtolower($value);
        } else {
            return "<INVALID>=><INVALID>";
        }
    } else {
        return "<INVALID>=><INVALID>";
    }
}

// Returns $handler $fnName from $argv[4] OR errors out
// For (route) "handlers", "data" & "validations" handlers
function get_handler_and_fn_from_argv4_or_err_out($handlerType)
{
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $reserved_functions;
    if (!is_string($handlerType) || empty($handlerType)) {
        cli_err_syntax("[get_handler_and_fn_from_argv4_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[get_handler_and_fn_from_argv4_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : "Validation");
    $handlerDir = $handlerType === "r" ? "handlers" : ($handlerType === "d" ? "data" : "validations");

    if (!isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("[get_handler_and_fn_from_argv4_or_err_out] Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add $handlerPrefix [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add $handlerPrefix get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
    }

    // Check if "$argv[4]" contains "=>" and split it into
    // handler & function name or just use $handlerFile name.
    $handlerFile = null;
    $fnName = null;
    $arrow = null;
    if (strpos($argv[4], '=>') !== false) {
        [$handlerFile, $fnName] = explode('=>', $argv[4]);
        $handlerFile = trim($handlerFile);
        $fnName = trim($fnName);
        $arrow = true;
    } else {
        $handlerFile = $argv[4];
        $fnName = null;
    }

    // Preg_match both (unless null) handler file and function name
    if ($handlerFile !== null && !preg_match('/^[a-z_][a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" - $handlerPrefix Handler name must start with [a-z_] and then lowercase letters, numbers and underscores!");
    }
    if ($fnName !== null && !preg_match('/^[a-z_][a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" - $handlerPrefix Function name must start with [a-z_] and then lowercase letters, numbers and underscores!");
    }

    // Check that both fnName and handlerFile are not reserved functions
    if ($fnName !== null && in_array($fnName, $reserved_functions)) {
        cli_err_syntax("\"{$fnName}\" - Function is a reserved function name!");
    }
    if ($handlerFile !== null && in_array($handlerFile, $reserved_functions)) {
        cli_err_syntax("\"{$handlerFile}\" - Handler is a reserved function name!");
    }

    // Function name is optional, so if not provided, we set it to the handler file name since
    // that is the default name for the function in the handler file when the file is created
    if ($fnName === null) {
        $fnName = $handlerFile;
    }
    if (!str_starts_with($fnName, $handlerType . "_")) {
        $fnName = $handlerType . "_" . $fnName;
    }
    if (!str_starts_with($handlerFile, $handlerType . "_")) {
        $handlerFile = $handlerType . "_" . $handlerFile;
    }
    // Inform parsed handler and function name and return them
    cli_info_without_exit("Parsed $handlerPrefix Handler: \"funkphp/$handlerDir/$handlerFile.php\" and $handlerPrefix Function: \"$fnName\"");
    return [$handlerFile, $fnName, $arrow];
}

// Returns $method, $validRoute from $argv[3] OR errors out
// For (route) "handlers", "data" & "validations" handlers
function get_matched_route_from_argv3_or_err_out($handlerType)
{
    global $argv;
    if (!is_string($handlerType) || empty($handlerType)) {
        cli_err_syntax("[get_handler_and_fn_from_argv4_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[get_handler_and_fn_from_argv4_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : "Validation");

    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        cli_err_syntax("[get_matched_route_from_argv3_or_err_out] Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add $handlerPrefix [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add $handlerPrefix get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
    }
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$method$validRoute\"");

    return [$method, $validRoute];
}

// Returns [$argv[4], $method, $validRoute] or errors out
// For middleware handlers in funkphp/middlewares/
function get_valid_mw_string_and_matched_route_or_err_out($syntaxExample)
{
    // Load globals and validate input
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $reserved_functions;
    if (!is_string($syntaxExample) || empty($syntaxExample)) {
        cli_err_syntax("[get_valid_mw_string_and_matched_route_or_err_out] Middleware Syntax Example must be a non-empty string!");
    }
    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3]) || !isset($argv[4]) || !is_string($argv[4]) || empty($argv[4])) {
        cli_err_syntax("[get_valid_mw_string_and_matched_route_or_err_out] Should be at least four(4) non-empty string arguments!$syntaxExample");
    }

    // Check now that handler $argv[4] is a string containg only letters, numbers and underscores!
    if (!preg_match('/^[a-z0-9_]+$/', $argv[4])) {
        cli_err_syntax("[get_valid_mw_string_and_matched_route_or_err_out] \"{$argv[4]}\" - Middleware Name must be a lowercased string containing only letters, numbers and underscores!");
    }

    // Now we check if the middleware name starts with "m_" and if not we add it to the name
    // This is to avoid conflicts with other handler files that might use the same name in the future
    if (!str_starts_with($argv[4], "m_")) {
        $argv[4] = "m_" . $argv[4];
    }
    // Now we check that it doesn't conflict with any reserved functions
    if (in_array($argv[4], $reserved_functions)) {
        cli_err_syntax("[get_valid_mw_string_and_matched_route_or_err_out] \"{$argv[4]}\" is a reserved function name!");
    }

    // Prepare the route string by trimming, validating starting, ending and middle parts of it
    $addRoute = trim(strtolower($argv[3]));
    $oldRoute = $addRoute;
    [$method, $validRoute] = cli_prepare_valid_route_string($addRoute);
    cli_info_without_exit("ROUTE: " . "\"$oldRoute\"" . " parsed as: \"$validRoute\"");

    return [$argv[4], $method, $validRoute];
}

// Returns a valid string or array or errors out
function get_valid_string_or_array_or_err_out($stringOrArray)
{
    // Not string or not array
    if (!is_string($stringOrArray) && !is_array($stringOrArray)) {
        cli_err_syntax("[get_valid_string_or_array_or_err_out] Must be a non-empty string or non-empty array!");
        return $stringOrArray;
    }
    // Array but no elements
    elseif (is_array($stringOrArray) && count($stringOrArray) < 1) {
        cli_err_syntax("[get_valid_string_or_array_or_err_out] Must be a non-empty string or non-empty array!");
        return $stringOrArray;
    }
    // Now we have a valid stirng or array so now we check if it is a string and validate its regex is: [a-z_][a-z0-9_]+
    // We do the same for array by looping through the array and checking if each element is a string
    elseif (is_string($stringOrArray)) {
        if (!preg_match('/^[a-z_][a-z0-9_]+$/', $stringOrArray)) {
            cli_err_syntax("[get_valid_string_or_array_or_err_out] String must start with [a-z_] and then lowercase letters, numbers and underscores!");
        } else {
            return $stringOrArray;
        }
    } elseif (is_array($stringOrArray)) {
        foreach ($stringOrArray as $key => $value) {
            if (!is_string($value) || !preg_match('/^[a-z_][a-z0-9_]+$/', $value)) {
                cli_err_syntax("[get_valid_string_or_array_or_err_out] Array must contain only strings that start with [a-z_] and then lowercase letters, numbers and underscores!");
            }
        }
        // If we reach here, we have a valid array so we return it
        return $stringOrArray;
    }
}

// Function that takes a handler file name, function name,
// and correct dir and whether "r", or "d" to create
// either a new handler file or a new function in an
// already existing handler file. Can error out!
function create_handler_file_with_fn_or_fn_or_err_out($handlerType, $handlersDir, $handlerFile, $fnName, $method, $validRoute, $customCode = null)
{
    global $dirs;
    // Validate the handler type and set the handler prefix and directory path
    if (!is_string($handlerType) || empty($handlerType)) {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }

    // Prepare correct handler prefix and directory path and date for the file to either create or add to
    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : "Validation");
    $handlerDirPath = $handlerType === "r" ? "handlers" : ($handlerType === "d" ? "data" : "validations");
    $templateDirs = $dirs['templates'];
    $date = date("Y-m-d H:i:s");
    $outputHandlerRoute = null;
    $validationLimiterStrings = $handlerType === 'v' ? "// Created in FunkCLI on $date! Keep \"};\" on its\n// own new line without indentation no comment right after it!\n// Run the command `php funkcli compile v $handlerFile=>$fnName`\n// to get optimized version in return statement below it!\n\$DX = [];\n\n\nreturn array([]);\n" : "";
    $customCodeString = "";
    $returnFunctionRegex = get_match_return_function_regex($fnName, $method, $validRoute) ?? "";

    // If $customCode not null, then we retrieve a valid string or array or error out
    // If $customCode now is a string we check if that file exists using
    // the $templateDirs path and if it does exist then we file_content it and remove the beginning "<?php"
    // part of that and store it in $customCodeString
    if ($customCode) {
        $customCode = get_valid_string_or_array_or_err_out($customCode);
    }
    if (is_string($customCode)) {
        $customCodeString = file_get_contents($templateDirs . $customCode . ".php");
        if ($customCodeString === false) {
            cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$templateDirs/$customCode.php\" not found!");
        }
        // Remove the first line "<?php" from the string
        $customCodeString = preg_replace('/^<\?php\s*/', '', $customCodeString);
    } elseif (is_array($customCode)) {
        foreach ($customCode as $value) {
            $filePath = $templateDirs . $value . ".php";
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$filePath\" not found!");
            } else {
                $processedContent = preg_replace('/^<\?php\s*/', '', $fileContent);
                $customCodeString .= $processedContent;
            }
        }
    }
    // If dir not found or not readable/writable, we exit
    if (!dir_exists_is_readable_writable($handlersDir)) {
        cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$handlersDir\" not found or non-readable/writable!");
    }

    // When file does not exist we create it
    if (!file_exists($handlersDir . $handlerFile . ".php")) {
        // Special case 'v' for validation handlers, we also use the Validation string
        if ($handlerType === 'v') {
            $outputHandlerRoute = file_put_contents(
                $handlersDir . $handlerFile . ".php",
                "<?php\n// $handlerPrefix Handler File - Created in FunkCLI on $date!\n// Write your Validation Rules in the\n// \$DX variable and then run the command\n// `php funkcli compile v $handlerFile=>\$function_name`\n// to get the optimized version below it!\n// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!\n\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n$customCodeString\n$validationLimiterStrings\n};\n\nreturn function (&\$c, \$handler = \"$fnName\") {\n\$handler(\$c);\n};"
            );
        }
        // For "route" & "data" handlers, we just add the function name after file creation
        else {
            // Create the handler file with the function name and return a success message
            $outputHandlerRoute = file_put_contents(
                $handlersDir . $handlerFile . ".php",
                "<?php\n// $handlerPrefix Handler File - Created in FunkCLI on $date!\n// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!\n\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n// Created in FunkCLI on $date! Keep \"};\" on its\n// own new line without indentation no comment right after it!\n$customCodeString\n};\n\nreturn function (&\$c, \$handler = \"$fnName\") {\n\$handler(\$c);\n};\n"
            );
        }
        if ($outputHandlerRoute) {
            cli_success_without_exit("Added $handlerPrefix Handler \"funkphp/$handlerDirPath/$handlerFile.php\" with $handlerPrefix Function \"$fnName\" in \"funkphp/data/$handlerFile.php\"!");
            if ($customCodeString !== "") {
                cli_info_without_exit("Added custom code from \"templates/$customCode.php\" to the Handler File!");
            }
            return;
        } else {
            cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: FAILED to create $handlerPrefix Handler \"funkphp/$handlerDirPath/$handlerFile.php\". File permissions issue?");
        }
    }

    // When file does exist we check if the function name is already used
    if (file_exists($handlersDir . $handlerFile . ".php")) {
        // If file is NOT readable/writable, we exit
        if (!file_exists_is_readable_writable($handlersDir . $handlerFile . ".php")) {
            cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$handlersDir/$handlerFile.php\" not found or non-readable/writable!");
        }
        $fileContent = file_get_contents($handlersDir . $handlerFile . ".php");

        // Now we check if the function name is already used
        $matchFnRegex = get_match_function_regex($fnName);
        if (preg_match($matchFnRegex, $fileContent, $matches)) {
            cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$fnName\" - Function name already exists in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        } else {
            cli_info_without_exit("Function \"$fnName\" not already used in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        }

        // Here we match the return function block to insert the new function that is not already used
        if (preg_match($returnFunctionRegex, $fileContent, $matches, PREG_OFFSET_CAPTURE)) {

            // $matches[0] now contains an array: [matched string, offset]
            $matchedString = $matches[0][0]; // The actual string that matched
            $matchOffset = $matches[0][1];   // The byte offset of the match in $fileContent

            // Construct the string for the *new* function definition only.
            // DO NOT include $matches[0] in this string; we will insert it separately.
            $newFunctionString = '';
            // Assuming $validationLimiterStrings and $date are defined elsewhere
            if ($handlerType === 'v') {
                $newFunctionString = "\nfunction {$fnName}(&\$c) // <{$method}{$validRoute}>{\n{$validationLimiterStrings}\n$customCodeString\n};\n\n";
            } else {
                $newFunctionString = "\nfunction {$fnName}(&\$c) // <{$method}{$validRoute}>\n{\n// Created in FunkCLI on {$date}! Keep \"};\" on its\n// own new line without indentation no comment right after it!\n$customCodeString\n};\n\n";
            }

            // --- Now, perform the insertion into $fileContent ---
            // Use substr_replace to insert $newFunctionString at $matchOffset
            // The length to replace is 0, which means insert without replacing anything.
            $fileContent = substr_replace(
                $fileContent,         // The original string
                $newFunctionString,   // The string to insert
                $matchOffset,         // The position to insert at
                0                     // The number of characters to replace (0 means insert)
            );

            // Attempt outputting the modified content back to the file
            $outputHandlerRoute = file_put_contents(
                $handlersDir . $handlerFile . ".php",
                $fileContent
            );
            if ($outputHandlerRoute) {
                cli_success_without_exit("Added $handlerPrefix Function \"$fnName\" to \"funkphp/$handlerDirPath/$handlerFile.php\"!");
                if ($customCodeString !== "") {
                    cli_info_without_exit("Added custom code from \"templates/$customCode.php\" to the Handler File!");
                }
                return;
            } else {
                cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: FAILED to create $handlerPrefix Handler \"funkphp/$handlerDirPath/$handlerFile.php\". File permissions issue?");
            }
        } else {
            // The 'return function' block was not found - the file structure is invalid
            cli_err_without_exit("[create_handler_file_with_fn_or_fn_or_err_out]: Invalid handler file structure.");
            cli_err("Could not find the 'return function(...) {...};' block in \"funkphp/{$handlerDirPath}/{$handlerFile}.php\".");
            return false; // Exit the function as the file structure is unexpected
        }
    }
}

// Returns [$handlerFile, $fnName] or errors out (used to validate
// Handler and Function name in Route, Data & Validation handler Files)
function get_valid_handlerVar_or_err_out($handlerVar, $handlerType)
{
    if (!is_string($handlerType) || empty($handlerType)) {
        cli_err_syntax("[get_valid_handlerVar_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[get_valid_handlerVar_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }

    // $handlerVar must either be a string or an array with a single string value!
    if (!is_string($handlerVar) && !is_array($handlerVar)) {
        cli_err_syntax_without_exit("[get_valid_handlerVar_or_err_out]: The Handler argument must be 1) One string or 2) One array with one string!");
        cli_err_syntax("Example: \"[HandlerFile|HandlerFile=>Function] (the variable structure, not as a string!)\"");
    }

    // If it is a string, check that it is valid and not empty
    if (is_string($handlerVar) && empty($handlerVar)) {
        cli_err_syntax("[get_valid_handlerVar_or_err_out]: \"$handlerVar\" must be a non-empty string!");
    }

    $handlerTypeName = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : "Validation");

    // If it is a string, check for "=>" because this function is either called by deleting a route
    // or just by deleting a handler function directly meaning the handlerFile=>Function would be
    // passed as a string and not as an array with one string value in the case of deleting a route.
    if (is_string($handlerVar)) {
        if (strpos($handlerVar, '=>') !== false) {
            [$handlerFile, $fnName] = explode('=>', $handlerVar);
            $handlerFile = trim($handlerFile);
            $fnName = trim($fnName);
        } else {
            $handlerFile = $handlerVar;
            $fnName = $handlerFile;
        }
    } elseif (is_array($handlerVar)) {
        $handlerFile = key($handlerVar);
        $fnName = $handlerVar[$handlerFile];
    }
    // We now check if $fnName and $handlerFile both start with "d_" and if not
    // then we add it to the data handler file name. This to not conflict with other
    // types of handlers that might be included into the global scope of functions
    // such as route ("r_"), page ("p_") and/or middleware ("m_") handlers.
    if (!str_starts_with($handlerFile, $handlerType . "_")) {
        $handlerFile = $handlerType . "_" . $handlerFile;
    }
    if (!str_starts_with($fnName, $handlerType . "_")) {
        $fnName = $handlerType . "_" . $fnName;
    }

    // Remove ".php" from handlerFile if it exists
    if (str_ends_with($handlerFile, ".php")) {
        $handlerFile = substr($handlerFile, 0, -4);
    }

    // Check that the handler file and function name are not empty strings with invalid characters
    if (!preg_match('/^[a-z0-9_]+$/', $handlerFile)) {
        cli_err_syntax("\"{$handlerFile}\" $handlerTypeName Handler File must be a lowercase string containing only letters, numbers and underscores!");
    }
    if (!preg_match('/^[a-z0-9_]+$/', $fnName)) {
        cli_err_syntax("\"{$fnName}\" $handlerTypeName Function Name must be a lowercase string containing only letters, numbers and underscores!");
    }

    return [$handlerFile, $fnName];
}

// Function that takes a handler file name, function name,
// and correct dir and whether "r", or "d" to delete
// either a new handler file or a new function in an
function delete_handler_file_with_fn_or_just_fn_or_err_out($handlerType, $handlerVar)
{
    // Load globals
    global $dirs;

    // Validate the handler type and set the handler prefix and directory path
    if (!is_string($handlerType) || empty($handlerType)) {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if (!is_string($handlerVar) && !is_array($handlerVar)) {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler variable must be a non-empty string or an array!");
    }

    // Get valid handler file and function name
    [$handlerFile, $fnName] = get_valid_handlerVar_or_err_out($handlerVar, $handlerType);

    // Prepare correct handler prefix, directory path
    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : "Validation");
    $handlerDirShort = $handlerType === "r" ? "handlers" : ($handlerType === "d" ? "data" : "validations");
    $handlerDirPath = $handlerType === "r" ? $dirs['handlers'] : ($handlerType === "d" ? $dirs['data'] : $dirs['validations']);
    $fnNameRegex = get_match_function_regex($fnName);

    // If dir not found or not readable/writable, we exit
    if (!dir_exists_is_readable_writable($handlerDirPath)) {
        cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$handlerDirPath\" not found or non-readable/writable!");
    }

    // If Handler file does not exist or is not readable/writable, we exit
    if (!file_exists_is_readable_writable($handlerDirPath . $handlerFile . ".php")) {
        cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$handlerDirPath$handlerFile.php\" not found or is non-readable/writable!");
    }

    // Read in file content and preg_match the function name
    $fileContent = file_get_contents($handlerDirPath . $handlerFile . ".php");
    $matchedFn = preg_match($fnNameRegex, $fileContent, $matches);
    //    $matchedAllFn = preg_match_all(get_match_all_functions_regex_without_capture_groups($handlerType), $fileContent, $matches2);

    // If function name is found we first replace it with an empty string
    // inside of $fileContent in order to remove it from the file!
    if ($matchedFn && isset($matches[0])) {
        $fileContent = str_replace($matches[0] . "\n", "", $fileContent);
        cli_success_without_exit("Removed $handlerPrefix Function \"$fnName\" from \"funkphp/$handlerDirShort/$handlerFile.php\"!");
        $matchedAllFn = preg_match_all(get_match_all_functions_regex_without_capture_groups($handlerType), $fileContent, $matches2);

        // If no functions are left in the file, we delete the file
        if (isset($matches2[0]) && count($matches2[0]) === 0) {
            // If no functions are left in the file, we delete the file
            unlink($handlerDirPath . $handlerFile . ".php");
            cli_success_without_exit("Deleted $handlerPrefix Handler \"$handlerFile.php\" File \"funkphp/$handlerDirShort/$handlerFile.php\"!");
            return;
        }
        // Otherwise we just write the file content back to the file with the function removed
        else {
            file_put_contents($handlerDirPath . $handlerFile . ".php", $fileContent);
        }
    } else {
        cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$fnName\" - Function name not found in \"funkphp/$handlerDirPath$handlerFile.php\"!");
    }
}
