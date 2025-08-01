<?php // FIRST CLI FUNCTIONS FILE
function get_match_function_regex_new($fnName)
{
    // Check if the function name is valid
    if (!is_string_and_not_empty($fnName) || !preg_match("/^[a-z_][a-z0-9_]+$/", $fnName)) {
        cli_err_syntax("[get_match_function_regex_new] Function name must be a Non-Empty String using the Regex Syntax `^[a-z_][a-z0-9_]+$`!");
    }
    $regex = '/^function (' . $fnName . ')\s*\(&\$c\)\s*\/\/\s*<(GET|POST|PATCH|DELETE|PUT)\/([a-zA-Z_0-9:\/]+)+>([^\n]+)*\n{((\s\S*)*\n)};$/img';
    return $regex;
}
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

// Same as "get_match_function_regex" but it maches all functions
// like:"function handlertype_functionName(&$c) // <METHOD/route>"
// in order to know if the entire file now should be removed!
function get_match_all_functions_regex($handlerType)
{
    // Check if the function name is valid
    if (
        $handlerType !== "r"
        && $handlerType !== "d"
        && $handlerType !== "v"
        && $handlerType !== "s"
    ) {
        cli_err_syntax("[get_match_all_functions_regex] Handler type must be a non-empty string. Choose between: 'r', 'd', 's', or 'v'");
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
function get_match_all_functions_regex_without_capture_groups($handlerType, $sqlAndValidation = null)
{
    // Check if the function name is valid
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v" && $handlerType !== "s") {
        cli_err_syntax("[get_match_all_functions_regex] Handler type must be a non-empty string. Choose between: 'r','d', 's', or 'v'");
    }

    // Create regex pattern based on method and route
    // It can find things like (ignore the quotes):
    // "function post_user(&$c) // <POST/user>
    // {
    // };"
    // The matching is only valid if after "};" there is a new line otherwise it will be invalid!
    $regex = null;

    // Different regex for different handler types
    if ($handlerType === "v" || $handlerType === "s") {
        $regex = '/^function ' . $handlerType . '_[a-z0-9_]+\(\&\$c\)\s*\/\/\s*<[a-z_,\-0-9\*]*>\s*$.*?^};$/ims';
    } else {
        $regex = '/^function ' . $handlerType . '_[a-z0-9_]+\(\&\$c\)\s*\/\/ <[A-Z]+\/[a-z0-9_:\-\/]*>\s*$.*?^};$/ims';
    }
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
    // Everything inside of the $DX
    // array must be [''] and NOT [""]
    return '/\$DX\s*=\s*\[\s*\'.*?];$/ims';
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

// Function to connect to local MySQL database!
// Configure its connection: /funkphp/config/db_config.php
function cli_db_connect()
{
    global $exactFiles;
    $dbConfig = include_once $exactFiles['db_local'];
    try {
        $conn = new mysqli($dbConfig['DB_HOST'], $dbConfig['DB_USER'], $dbConfig['DB_PASSWORD'], $dbConfig['DB_NAME'], $dbConfig['DB_PORT']);
        $conn->set_charset('utf8mb4');
    } catch (Exception $e) {
        if ($conn === null) {
            cli_err_syntax("Database Connection Failed. Check Database Connection Configuration in \"funkphp/config/db_config.php\"!");
        }
    }
    return $conn;
}

// Function takes a SQL file and parses the CREATE TABLE(); statement
// and then stores it in funkphp/config/tables.php file as a PHP array
function cli_parse_a_sql_table_file()
{
    // Load globals and verify $argv is not empty string and ends with .sql
    cli_info_without_exit("IMPORTANT #1: \"php funkcli add table\" command is NOT meant for actual Table Migration.");
    cli_info_without_exit("It is ONLY meant for structuring efficient Data Validation, SQL Query Building & Data Hydration!");
    cli_info_without_exit("IMPORTANT #2: The function cli_convert_array_to_simple_syntax() in \"funkphp/_internals/functions/cli_funs.php\" which converts ");
    cli_info_without_exit("array() to array[] ignores quotes inside of other qoutes. For example, \"Yours' truly\" will become \"Yours truly\".");
    cli_info_without_exit("KEEP THAT IN MIND: If you wanna use `DEFAULT \"Qouted Value with '\"Quotes\"' Inside\"` it must be manually added inside \"config/Tables.php\"");

    global $argv, $dirs, $exactFiles, $settings, $tablesAndRelationshipsFile, $mysqlDataTypesFile;
    $sqlFile = null;
    if (!is_string_and_not_empty(trim($argv[3] ?? null))) {
        cli_err_syntax("Provide a SQL File from \"funkphp/schemas/\" folder as a string!");
    }

    // Trim, add .sql extension if not already, and check that file exsts in /sql/ folder
    $argv[3] = strtolower(trim($argv[3]));
    if (!str_ends_with($argv[3], ".sql")) {
        $argv[3] .= ".sql";
    }
    if (file_exists_is_readable_writable($dirs['schemas'] . $argv[3])) {
        $sqlFile = file_get_contents($dirs['schemas'] . $argv[3]);
    } else {
        cli_err_syntax("\"{$argv[3]}\" must must exist in\"funkphp/schemas/\"!");
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
    if (!preg_match("/^CREATE TABLE\s+(IF NOT EXISTS\s*)*([a-zA-Z0-9_]+)\s*\(/i", $sqlFile, $matches)) {
        cli_err_syntax("\"{$argv[3]}\" must start with \"CREATE TABLE /[a-zA-Z0-9_]+/ (\"");
    }
    // Parse out the table name and check if it is valid
    $tableName = $matches[2] ?? null;
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
        return !empty($line)
            && !str_starts_with($line, ")")
            && !str_starts_with($line, "PRIMARY KEY")
            && !str_starts_with($line, "CHECK")
            && !str_starts_with($line, "CONSTRAINT");
    });
    cli_warning_without_exit("DELETED ALL SQL LINES Starting with: \"PRIMARY KEY\", \"CHECK\", \"CONSTRAINT\" & \")\"!");
    cli_info_without_exit("If you prefer `PRIMARY KEY`, `CHECK`, `CONSTRAINT` for PK & FK, please don't when adding `Tables` to the `funkphp/config/tables.php` File though!");
    $sqlLines = array_map(function ($line) {
        return rtrim($line, ",\r\n\t ");
    }, $sqlLines);
    if (!str_starts_with(strtolower($sqlLines[0]), "id ")) {
        cli_err_syntax_without_exit("First Table Column in the Table SQL File must be: `id`!");
        cli_info_without_exit("Its syntax must be exactly: `id BIGINT AUTO_INCREMENT PRIMARY KEY,`!");
        cli_info("Other tables referencing to this Table must reference to the `id` column!");
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
                    'binding' => 'i',
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
            $foreignKeyRegex = "/FOREIGN KEY \(([a-zA-Z0-9_]+)\) REFERENCES ([a-zA-Z0-9_]+)\(([a-zA-Z0-9]+)\)/";

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
                    $parsedTable[$tableName][$matches[1]]["binding"] = $mysqlDataTypes[$matches[3]]["BINDING"] ?? "<UNKNOWN_BINDING_PARAM_VALUE>";
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
    cli_success_without_exit("Parsed Table \"$tableName\" from SQL File \"schemas/{$argv[3]}\"!");
    cli_success_without_exit("You find it in `config/tables.php` => ['tables']['$tableName']!");

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

    // Loop through and add any missing keys to the relationships
    // and mappings arrays based on the newly added Table!
    foreach ($array['tables'] as $tableName => $tableData) {
        // Add relationships for the table if they do not exist and also update existing ones
        if (!isset($array['relationships'][$tableName]) || !is_array($array['relationships'][$tableName])) {
            $array['relationships'][$tableName] = [];
            foreach ($tableData as $columnName => $columnData) {
                if (isset($columnData['foreign_key']) && $columnData['foreign_key'] === true) {
                    // If a FK, we create the relationship between FK Table and PK Table
                    $array['relationships'][$tableName][$columnData['references']] = [
                        'local_column' => $columnName,
                        'foreign_column' => 'id',
                        'local_table' => $tableName,
                        'foreign_table' => $columnData['references'],
                        'direction' => 'fk_to_pk',
                    ];
                    $array['relationships'][$columnData['references']][$tableName] = [
                        'local_column' => 'id',
                        'foreign_column' => $columnName,
                        'local_table' => $columnData['references'],
                        'foreign_table' => $tableName,
                        'direction' => 'pk_to_fk',
                    ];
                } else {
                    // If it is not a foreign key, we just skip it
                    continue;
                }
            }
            cli_info_without_exit("Added Relationships for Table \"$tableName\" in \"funkphp/config/tables.php\"!");
        }
        // Add mappings for the table if they do not exist (for JSON, POST & GET for each Column!)
        if (!isset($array['mappings'][$tableName]) || !is_array($array['mappings'][$tableName])) {
            $array['mappings'][$tableName] = [];
            foreach ($tableData as $columnName => $columnData) {
                $array['mappings'][$tableName][$columnName]['json'] = $columnData['joined_name'] ?? $tableName . "_" . $columnName;
                $array['mappings'][$tableName][$columnName]['post'] = $columnData['joined_name'] ?? $tableName . "_" . $columnName;
                $array['mappings'][$tableName][$columnName]['get'] = $columnData['joined_name'] ?? $tableName . "_" . $columnName;
            }
            cli_info_without_exit("Added Mappings for Table \"$tableName\" in \"funkphp/config/tables.php\"!");
        }
    }

    // --- (FROM LLM!!!) NEW LOGIC FOR AUTOMATIC MANY-TO-MANY RELATIONSHIP DETECTION ---
    // This loop runs after all direct 1:M/M:1 relationships have been set up.
    cli_info_without_exit("Analyzing for `Many-to-Many (m_to_m) Relationships` by splitting Table Names on `_` and Checking for Pivot Tables...");
    foreach ($array['tables'] as $tableName => $tableData) {
        // Heuristic: Check if the table name looks like a pivot table (e.g., 'table1_table2')
        if (strpos($tableName, '_') !== false) {
            $parts = explode('_', $tableName);
            // We expect pivot tables to typically join two main tables
            if (count($parts) === 2) {
                $table1 = $parts[0]; // e.g., 'authors'
                $table2 = $parts[1]; // e.g., 'tags'
                // Ensure both parsed table names actually exist as main tables
                if (isset($array['tables'][$table1]) && isset($array['tables'][$table2])) {
                    $pivotLocalKey = null; // Column in pivot table linking to $table1 (e.g., 'author_id')
                    $pivotForeignKey = null; // Column in pivot table linking to $table2 (e.g., 'tag_id')
                    // Iterate through the pivot table's columns to find its foreign keys
                    // and identify which main table each FK references.
                    foreach ($tableData as $columnName => $columnDef) {
                        if (isset($columnDef['foreign_key']) && $columnDef['foreign_key'] === true) {
                            if ($columnDef['references'] === $table1) {
                                $pivotLocalKey = $columnName;
                            } elseif ($columnDef['references'] === $table2) {
                                $pivotForeignKey = $columnName;
                            }
                        }
                    }
                    // If both required foreign keys were found in the pivot table's definition,
                    // then we can confidently define a many-to-many relationship.
                    if ($pivotLocalKey !== null && $pivotForeignKey !== null) {
                        // Define Many-to-Many from $table1 to $table2
                        // Only add if it doesn't already exist or if it's not already a many_to_many
                        if (
                            !isset($array['relationships'][$table1][$table2])
                            || !is_array($array['relationships'][$table1][$table2])
                            || (isset($array['relationships'][$table1][$table2]['type'])
                                && $array['relationships'][$table1][$table2]['type'] !== 'many_to_many')
                        ) {
                            $array['relationships'][$table1][$table2] = [
                                'direction' => 'm_to_m',
                                'local_table' => $table1,
                                'foreign_table' => $table2,
                                'pivot_table' => $tableName,         // The current pivot table name (e.g., 'authors_tags')
                                'pivot_local_key' => $pivotLocalKey, // FK in pivot pointing to 'local_table' (e.g., 'author_id')
                                'pivot_foreign_key' => $pivotForeignKey, // FK in pivot pointing to 'foreign_table' (e.g., 'tag_id')
                            ];
                            cli_info_without_exit("Automatically added Many-to-Many Relationship: `{$table1}=>{$table2}(via:{$tableName})`.");
                        }

                        // Define Many-to-Many from $table2 to $table1 (inverse direction)
                        // This allows hydration like tags=>authors(via:authors_tags)
                        if (
                            !isset($array['relationships'][$table2][$table1])
                            || !is_array($array['relationships'][$table2][$table1])
                            || (isset($array['relationships'][$table2][$table1]['type'])
                                && $array['relationships'][$table2][$table1]['type'] !== 'many_to_many')
                        ) {
                            $array['relationships'][$table2][$table1] = [
                                'direction' => 'm_to_m',
                                'local_table' => $table2,
                                'foreign_table' => $table1,
                                'pivot_table' => $tableName,
                                'pivot_local_key' => $pivotForeignKey, // Swapped for inverse direction
                                'pivot_foreign_key' => $pivotLocalKey,  // Swapped for inverse direction
                            ];
                            cli_info_without_exit("Automatically added Many-to-Many Relationship (Inverse): `{$table2}=>{$table1}(via:{$tableName})`.");
                        }
                    }
                }
            }
        }
    }
    // --- (FROM LLM!!!) END NEW LOGIC ---

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

    // List of all supported data types where we check so not
    // two are used for the same $$currentDXKey since each
    // given single input must be of only one data type!
    $dataTypeRules = [
        'string',
        'char',
        'digit',
        'integer',
        'float',
        'boolean',
        'number',
        'date',
        'array',
        'list',
        'email',
        'email_custom',
        'password',
        'password_custom',
        'password_confirm',
        'url',
        'ip',
        'ip4',
        'ip6',
        'uuid',
        'phone',
        'object',
        'json',
        'enum',
        'set',
        'checked',
        'unchecked',
        'file',
        'image',
        'video',
        'audio',
    ];

    // When this value turns true, then we will add it as a special root key
    // before when finally returning the converted validation array.
    $stop_on_first_error = false;

    // Priority order of validation rules (required and the data
    // type must always be first for each $currentDXKey!). 'nullable'
    // must come very early to allow for data that are actually null!
    $priorityOrder = [
        // Special properites
        'stop_all_on_first_error',
        'field',
        'nullable',
        'stop',
        'required',
        // Data types
        'string',
        'char',
        'email',
        'email_custom',
        'password',
        'password_custom',
        'password_confirm',
        'url',
        'ip',
        'ip4',
        'ip6',
        'uuid',
        'phone',
        'date',
        'json',
        'integer',
        'digit',
        'float',
        'boolean',
        'number',
        'array',
        'list',
        'set',
        'enum',
        'object',
        'unchecked',
        'checked',
        'file',
        'image',
        'video',
        'audio',
        // Data "measurements"
        'between',
        'betweenlen',
        'betweenval',
        'betweencount',
        'exact',
        'exactlen',
        'exactval',
        'exactcount',
        'count',
        'mincount',
        'maxcount',
        'min',
        'minlen',
        'minval',
        'max',
        'maxlen',
        'maxval',
        'digits',
        'digits_between',
        'min_digits',
        'max_digits',
        'decimals',
        // Other types of data validation
        'color',
        'lowercase',
        'lowercases',
        'uppercase',
        'uppercases',
        'numbers',
        'specials',
        // Regex
        'regex',
        'not_regex',
        // Arrays
        'array_keys',
        'array_keys_exact',
        'array_values',
        'array_values_exact',
        // Elements in arrays
        'elements_all_arrays',
        'elements_all_lists',
        'elements_all_numbers',
        'elements_all_chars',
        'elements_all_strings',
        'elements_all_integers',
        'elements_all_floats',
        'elements_all_booleans',
        'elements_all_checked',
        'elements_all_unchecked',
        'elements_all_nulls',
        'elements_this_type_order',
        'any_of_these_values',
        // Always last
        'exists',
        'unique',
        'password_hash',
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

    // List of available Global Config Rules
    $globalConfigRules = [
        'show_v_data_only_if_all_valid' => [],
        'stop_all_first' => [], // Alias for 'stop_all_on_first_error'
        'stop_all_on_first_error' => [],
        'passwords_to_match' => [],
    ];

    // We now check for the "'<CONFIG>'" key in $validationArray which is a configuration
    // key that will always be processed first so we grab it and remove it from the array.
    if (isset($validationArray['<CONFIG>']) && $validationArray['<CONFIG>'] !== null) {
        if (!is_array($validationArray['<CONFIG>']) || empty($validationArray['<CONFIG>'])) {
            cli_warning_without_exit("The Global Validation `<CONFIG>` Key is empty - no Global Config Rules have been added!");
            cli_info_without_exit("You can add Global Config Rules to the Validation Array by adding a key `<CONFIG>` with an Array of Rules.");
            $convertedValidationArray['<CONFIG>'] = null;
        } else {
            // We initialize the `<CONFIG>` key in the converted validation array
            // and here we will add all the valid configuration rules that are found
            $convertedValidationArray['<CONFIG>'] = [];

            // Now we check if the `<CONFIG>` key has any rules and if it does, we process them
            foreach ($validationArray['<CONFIG>'] as $configKey => $configVal) {
                // Check that config rule is valid ($globalConfigRules) or err out
                if (!isset($globalConfigRules[$configKey])) {
                    cli_err_syntax_without_exit("Invalid Global Config Rule `$configKey` found in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Use any - once - of the following available Global Config Rules:\n" . implode(",\n", quotify_elements($globalConfigRules)) . "!");
                }

                // NOW WE ADD THE CONFIG RULES THAT EXIST!
                // If "stop_all_on_first_error", add it to the CONFIG key
                if (($configKey === 'stop_all_on_first_error'
                        || $configKey === 'stop_all_first')
                    && $configVal === true
                ) {
                    $convertedValidationArray['<CONFIG>']['stop_all_on_first_error'] = true;
                    cli_success_without_exit("GLOBAL CONFIG RULE ADDED: `stop_all_on_first_error` in Validation `$handlerFile.php=>$fnName`!");
                }

                // If "show_v_data_only_if_all_valid", add it to the CONFIG key
                if ($configKey === 'show_v_data_only_if_all_valid' && $configVal === true) {
                    $convertedValidationArray['<CONFIG>']['show_v_data_only_if_all_valid'] = true;
                    cli_success_without_exit("GLOBAL CONFIG RULE ADDED: `show_v_data_only_if_all_valid` in Validation `$handlerFile.php=>$fnName`!");
                }
            }
        }

        // Finally, remove the `<CONFIG>` key from the validation array since we processed it
        unset($validationArray['<CONFIG>']);
    } else {
        $convertedValidationArray['<CONFIG>'] = null;
        unset($validationArray['<CONFIG>']);
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

        if (str_contains($currentDXKey, ".")) {
            if (!preg_match("/^(\*|(([a-z_]*)([a-z_0-9]+))\.(\*|[a-z_][a-z_0-9]+))(\.(\*|[a-z_][a-z_0-9]+))*$/", $currentDXKey)) {
                cli_err_syntax_without_exit("[cli_convert_simple_validation_rules_to_optimized_validation] Invalid Nested Validation Key in `$currentDXKey`!");
                cli_info("Valid Syntax is: `user.email`, `user.email.primary`, `user.*.email`, `user.*.name` and so on.\nThe `*` character means the key before it indicates this is a numbered array and any keys after it are its subkeys for each element in that numbered array!");
            }
        }

        // Special case: using regex rule inside of string when "|" exists
        // which could cause issues if "|" is used as part of regex rule value!
        // This warning only happens if there is a "|" right after splittig on
        // "regex:" which means You, the Developer, should convert it to an array!
        if (is_string($Rules) && str_contains($Rules, "|") && str_contains($Rules, "regex:")) {
            // First split on "regex:" and then check if that split still contains "|"
            $regexParts = explode("regex:", $Rules);
            if (count($regexParts) > 1 && str_contains($regexParts[1], "|")) {
                // If it does, we warn the user to rewrite the string as an array
                // to avoid issues with regex rule value containing "|"
                cli_warning_without_exit("Please convert string `$Rules` to an array to be able ");
                cli_warning("to use the `regex:` rule due to possible conflicts of `|` inside of the `regex:` rule. Truly sorry for the sudden spontaneous nuisance!");
            }
        }

        // We now check if the $Rules is a string and if it is a string then we check
        // if it has "|" meaning we should split it into an array of rules. If it is just a
        // single rule string we still convert it to an array with a single element.
        // If it is an array we just use it as is.
        if (is_string($Rules) && str_contains($Rules, "|")) {
            // Array filter empty values to avoid empty rules
            $currentRules = explode("|", $Rules);
            $currentRules = array_filter($currentRules, function ($rule) {
                return is_string_and_not_empty($rule);
            });
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
                cli_err_syntax_without_exit("Validation Rule \"$currentRuleForCurrentDXKey\" not found in \"_internals/functions/d_data_funs.php\".");
                cli_info("It must start as a function: `function funk_validate_$currentRuleForCurrentDXKey()` or it will not be found!");
            }

            // We check if the "$currentRuleValueForCurrentDXKeyValue" contains a
            // "," meaning we should split it into an array of values that is the value then!
            // We also trim all the values in the array to remove any whitespace around them.
            if (str_contains($currentRuleValueForCurrentDXKeyValue, ",")) {
                $currentRuleValueForCurrentDXKeyValue = explode(",", $currentRuleValueForCurrentDXKeyValue);
                foreach ($currentRuleValueForCurrentDXKeyValue as $subKey => $subValue) {
                    $currentRuleValueForCurrentDXKeyValue[$subKey] = trim($subValue);
                }

                // We then check if each element is actually a number but that
                // is stringified so we turn it back to a number again!
                foreach ($currentRuleValueForCurrentDXKeyValue as $subKey => $subValue) {
                    if (is_numeric($subValue)) {
                        $currentRuleValueForCurrentDXKeyValue[$subKey] = cli_try_parse_number($subValue);
                    }
                }
            }

            // Check if duplicate rule name is found for the current $currentDXKey which is not allowed
            if (isset($convertedValidationArray[$currentDXKey]["<RULES>"][$currentRuleForCurrentDXKey])) {
                cli_err_syntax_without_exit("Duplicate Validation Rule `$currentRuleForCurrentDXKey` found for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_err_syntax("Please make sure all Validation Rules are unique for each key!");
            }

            // Add the current rule to the converted validation array
            $convertedValidationArray[$currentDXKey]["<RULES>"][$currentRuleForCurrentDXKey] = [
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
        $createdRules = $convertedValidationArray[$currentDXKey]["<RULES>"];

        // First Add priority rules first, in their defined order
        // and then add remaining rules in the order they were found.
        // We also make sure only one data type rule is used for each key!
        $addedDataTypeRule = false;
        $firstDataTypeRule = "";
        foreach ($priorityOrder as $ruleName) {
            if (isset($createdRules[$ruleName])) {
                if (in_array($ruleName, $dataTypeRules)) {
                    if ($addedDataTypeRule) {
                        cli_err_syntax_without_exit("Multiple Data Type Rule `$ruleName` (while Data Type Rule `$firstDataTypeRule` already exists) found for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_err_syntax_without_exit("Please make sure only one Data Type Rule is used for each key!");
                        cli_info("Use ONLY ONE of these Data Type Rules for each single Input Data: `string`, `integer`, `float`, `boolean`, `number`, `date`, `array`,\n\t\t  `email`, `email_custom`, `password`,`password_custom`, `password_confirm`, `url`, `ip`, `uuid`, `phone`, `object`, `json`, `enum`,\n\t\t  `set`, `ip4`, `ip6`, `checked`, `file`, `audio`, `video`, `image`, or `list`, `char`, or `digit`!");
                    }
                    $addedDataTypeRule = true;
                }
                $firstDataTypeRule = $ruleName;
                $sortedRulesForField[$ruleName] = $createdRules[$ruleName];
                unset($createdRules[$ruleName]);
            }
        }
        foreach ($createdRules as $ruleName => $details) {
            $sortedRulesForField[$ruleName] = $details;
        }

        // A final check that there is actually one data type rule added or
        // we error out since each $currentDXKey must have a data type rule!
        $noDataTypeRule = true;
        foreach ($dataTypeRules as $dataTypeRule) {
            if (isset($sortedRulesForField[$dataTypeRule])) {
                $noDataTypeRule = false;
                break;
            }
        }
        if ($noDataTypeRule) {
            cli_err_syntax_without_exit("No Data Type Rule found for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
            cli_info("Use ONLY ONE of these Data Type Rules for each single Input Data: `string`, `integer`, `float`, `boolean`, `number`, `date`, `array`,\n\t\t  `email`, `email_custom`, `password`,`password_custom`, `password_confirm`, `url`, `ip`, `uuid`, `phone`, `object`, `json`, `enum`,\n\t\t  `set`, `ip4`, `ip6`, `checked`, `file`, `audio`, `video`, `image`, or `list`, `char`, or `digit`!");
        }


        /*
            MANY SPECIAL CASES ARE CHECKED HERE - START:
            This includes special cases both
            for Rules and the $DXKey itself.
        */
        // First we get the current data type rule for the $currentDXKey
        // so we can make special case checks for it like not a negative
        // number for "min" rule when string type is used, etc.
        $categorizedDataTypeRules = [
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
            'number_types' => [
                'digit' => true,
                'integer' => true,
                'float' => true,
                'number' => true,
            ],
            'array_types' => [
                'array' => true,
                'list' => true,
                'set' => true,
            ],
            'file_types' => [
                'file' => true,
                'image' => true,
                'audio' => true,
                'video' => true,
            ],
            'complex_types' => [
                'null' => true,
                'object' => true,
                'unchecked' => true,
                'checked' => true,
                'enum' => true,
                'boolean' => true,
            ],
        ];
        $foundTypeRule = null;
        $foundTypeCat = null;
        foreach ($sortedRulesForField as $ruleName => $ruleConfig) {
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
                if (isset($categorizedDataTypeRules['file_types'][$ruleName])) {
                    $foundTypeCat = 'file_types';
                }
                break;
            }
        }

        // LIST OF COMMON DATE FORMATS IN STRINGS THAT CAN
        // BE CONVERTED TO THEIR FORMATTED DATE STRINGS
        $commonDateFormats = [
            "ATOM" => "Y-m-d\\TH:i:sP",
            "COOKIE" => "l, d-M-Y H:i:s T",
            "ISO8601" => "Y-m-d\\TH:i:sO",
            "ISO8601_EXPANDED" => "X-m-d\\TH:i:sP",
            "RFC822" => "D, d M y H:i:s O",
            "RFC850" => "l, d-M-y H:i:s T",
            "RFC1036" => "D, d M y H:i:s O",
            "RFC1123" => "D, d M Y H:i:s O",
            "RFC7231" => "D, d M Y H:i:s \\G\\M\\T",
            "RFC2822" => "D, d M Y H:i:s O",
            "RFC3339" => "Y-m-d\\TH:i:sP",
            "RFC3339_EXTENDED" => "Y-m-d\\TH:i:s.vP",
            "RSS" => "D, d M Y H:i:s O",
            "W3C" => "Y-m-d\\TH:i:sP",
        ];
        $validDateFormatCharacters = [
            "d",
            'D',
            'j',
            'l',
            'N',
            'S',
            'w',
            'z',
            'W',
            'F',
            'm',
            'M',
            'n',
            't',
            'L',
            'o',
            'X',
            'x',
            'Y',
            'y',
            'a',
            'A',
            'B',
            'g',
            'G',
            'h',
            'H',
            'i',
            's',
            'u',
            'v',
            'e',
            'I',
            'O',
            'P',
            'T',
            'Z',
            'c',
            'r',
            'U',
            '.',
            ':',
            '-',
            '+',
            '/',
            '\\',
            ' ',
        ];
        $timezoneAwareFormats =  [
            "ATOM",
            "COOKIE",
            "ISO8601",
            "ISO8601_EXPANDED",
            "RFC822",
            "RFC850",
            "RFC1036",
            "RFC1123",
            "RFC7231",
            "RFC2822",
            "RFC3339",
            "RFC3339_EXTENDED",
            "RSS",
            "W3C"
        ];

        // LIST OF RULES THAT SHOULD NOT HAVE A VALUE
        // AND THUS A WARNING WILL BE SHOWN IF THEY DO!
        $theseRulesShouldHaveNoValue = [
            'required',
            'nullable',
            'lowercase',
            'uppercase',
            'string',
            'integer',
            'float',
            'boolean',
            'number',
            'array',
            'list',
            'unchecked',
            'checked',
            'file',
            'image',
            'video',
            'audio',
            'object',
            'enum',
            'set',
            'json',
            'url',
            'ip',
            'ip4',
            'ip6',
            'uuid',
            'phone',
            'elements_all_chars', // single characters per element
            'elements_all_arrays',
            'elements_all_lists', // numbered arrays
            'elements_all_strings',
            'elements_all_nulls',
            'elements_all_numbers',
            'elements_all_integers',
            'elements_all_floats',
            'elements_all_booleans',
            'elements_all_checked',
            'elements_all_unchecked',
        ];

        // LIST OF RULES that must have a value, but this array
        // does not specify what kind of values but does provide
        // a quick first check using a loop below!
        $theseRulesMustHaveValues = [
            'elements_this_type_order',
            'uppercases',
            'lowercases',
            'numbers',
            'specials',
            'min_digits',
            'max_digits',
            'min',
            'max',
            'field',
            'count',
            'exact',
            'count',
            'digits',
            'between',
            'array_keys',
            'array_values',
            'array_keys_exact',
            'array_values_exact',
            'any_of_these_values'
        ];

        // List of specific values for specific data types
        // that are allowed to be used as value(s) for the rule.
        $allowedSpecificRuleValuesForDataTypes = [
            'email' => ['dns', 'tld'],
        ];

        // List of other Rules that some Data Types Rules
        // ONLY can have. For example `digit` can only have
        // "required","nullable" & "field" but nothing else.
        // So this array is compared so no other rules are
        // used when the specific data type is used.
        $allowedOtherRulesForSpecificDataTypeRule = [
            'digit' => [
                'required',
                'nullable',
                'field'
            ],
            'email' => [
                'required',
                'nullable',
                'field',
                'min',
                'max',
                'between',
                'regex',
                'not_regex',
                'unique',
                'exists',
            ],
            'set' => [
                'required',
                'nullable',
                'field',
                'any_of_these_values',
                'regex',
                'not_regex',
                'unique',
                'exists',
            ],
            'enum' => [
                'required',
                'nullable',
                'field',
                'any_of_these_values',
                'regex',
                'not_regex',
                'unique',
                'exists',
            ],
        ];

        // Special cases for any rule found in $theseRulesShouldHaveNoValue that has a value
        // when it should not (but we do not error out but just warn that the value will be ignored).
        foreach ($theseRulesShouldHaveNoValue as $ruleName) {
            if (isset($sortedRulesForField[$ruleName]) && isset($sortedRulesForField[$ruleName]['value'])) {
                cli_warning_without_exit("The `$ruleName` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a value set!");
                cli_info_without_exit("This has been set to `null` since the `$ruleName` Rule does not use a value!");
                $sortedRulesForField[$ruleName]['value'] = null;
            }
        }

        // Special case for any rule found in $theseRulesMustHaveValues
        // that does not have a value when it should (but we do error out).
        foreach ($theseRulesMustHaveValues as $ruleName) {
            if (isset($sortedRulesForField[$ruleName])) {
                // If the rule is in $theseRulesMustHaveValues, we check if it has a value
                // and if it does not, we error out.
                if (!isset($sortedRulesForField[$ruleName]['value']) || empty($sortedRulesForField[$ruleName]['value'])) {
                    cli_err_syntax_without_exit("The `$ruleName` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must have a non-empty value!");
                    cli_info("Specify a non-empty value for the `$ruleName` Rule. This could be several values separated by commas. You will be informed!");
                }
            }
        }

        // Iterate through all rules and see if they are a key in
        // $allowedSpecificRuleValuesForDataTypes. If they are, loop
        // through all its values and check if the value
        foreach ($sortedRulesForField as $ruleKey => $ruleName) {
            // If the rule is a data type rule, we check if it has a value
            // and if it does, we check if it is in the allowed specific rule values
            // for that data type. If not, we error out.
            if (isset($allowedSpecificRuleValuesForDataTypes[$ruleKey]) && isset($sortedRulesForField[$ruleKey]['value'])) {
                $ruleValue = $sortedRulesForField[$ruleKey]['value'];
                if (is_string($ruleValue) && !in_array($ruleValue, $allowedSpecificRuleValuesForDataTypes[$ruleKey] ?? [])) {
                    cli_err_syntax_without_exit("Invalid Value for `$ruleKey` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    $transformed = "";
                    foreach ($allowedSpecificRuleValuesForDataTypes[$ruleKey] as $allowedValue) {
                        $transformed .= "`$allowedValue`, ";
                    }
                    $transformed = rtrim($transformed, ", ");
                    cli_info("Allowed Values for `$ruleKey` Rule are: $transformed!");
                }
                // If the rule value is an array, we check if all its
                // values are in the allowed specific rule values!
                else if (is_array($ruleValue)) {
                    foreach ($ruleValue as $value) {
                        if (!in_array($value, $allowedSpecificRuleValuesForDataTypes[$ruleKey] ?? [])) {
                            cli_err_syntax_without_exit("Invalid Value for `$ruleKey` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            $transformed = "";
                            foreach ($allowedSpecificRuleValuesForDataTypes[$ruleKey] as $allowedValue) {
                                $transformed .= "`$allowedValue`, ";
                            }
                            $transformed = rtrim($transformed, ", ");
                            cli_info("Allowed Values for `$ruleKey` Rule are: $transformed!");
                        }
                    }
                }
            }
        }

        // Special case where we check for a "$currentDXKey" that ends with a "*"
        // that its type is "list", otherweise we error out. We also check for
        // the "min", "max", "count", "exact" and "between" rules to see if they
        // are set, otherwise we warn the user that it could lead to infinite loop!
        if (str_ends_with($currentDXKey, "*")) {
            if (!isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `$currentDXKey` key in Validation `$handlerFile.php=>$fnName` must the Array Numbering Data Type `list`!");
                cli_info("Specify `list` Data Type for `$currentDXKey` to use the Array Numbering `*` Character at the end of the Key!");
            }
            // We check if the rule "min" exists while "max" does not exist so we warn
            // about that it could lead to infinite loop in the validation function
            if (
                !isset($sortedRulesForField['max'])
                && !isset($sortedRulesForField['count'])
                && !isset($sortedRulesForField['size'])
                && !isset($sortedRulesForField['exact'])
                && !isset($sortedRulesForField['between'])
            ) {
                cli_err_without_exit("There are no Array Elements Limiting Rule(s) set for the Numbered Array `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("Add `between`,`count`,`exact`, `size` or `max` Rule to prevent CPU/DoS exploits!");
                cli_info_without_exit("Just set a very high number to prevent infinite loops while still processing as many as you think you will need!");
                cli_info("The Value in your `between` (the higher value),`count`,`exact`, `size` or `max` Rule will set the number of iterations for the Numbered Array `$currentDXKey`!");
            }
        }

        // Special case ofr "stop_all_on_first_error" Rule
        // meaning we set the $stop_on_first_error to true
        // and if we see this rule again we will just remove it
        // as it is just a special root key that is used
        // to stop all validation on the first error found.
        if (isset($sortedRulesForField['stop_all_on_first_error'])) {
            if ($stop_on_first_error) {
                cli_info_without_exit("The `stop_all_on_first_error` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is already set!");
            } else {
                cli_success_without_exit("Special Root Key Rule `stop_all_on_first_error` in `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is set!");
                cli_info_without_exit("This will remove further occurrences of this Rule in the Validation Rules for this Key!");
                $stop_on_first_error = true;
                unset($sortedRulesForField['stop_all_on_first_error']);
            }
            unset($sortedRulesForField['stop_all_on_first_error']);
        }

        // Special case for 'field' Rule it can ONLY have
        // a single string as a value, so we check that!
        if (isset($sortedRulesForField['field'])) {
            if (!is_string($sortedRulesForField['field']['value']) || empty($sortedRulesForField['field']['value'])) {
                cli_err_syntax_without_exit("The `field` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must have a non-empty string value!");
                cli_info("Specify a non-empty string as the value for the `field` Rule!");
            }
            // if it has an error message we remove it and inform
            if (isset($sortedRulesForField['field']['err_msg']) && !empty($sortedRulesForField['field']['err_msg'])) {
                cli_warning_without_exit("The `field` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has an error message set!");
                cli_info_without_exit("The `field` Rule does not use an error message, so it has been set to null!");
                $sortedRulesForField['field']['err_msg'] = null;
            }
        }

        // Special case for 'email" Rule
        if (isset($sortedRulesForField['email'])) {
            foreach ($sortedRulesForField as $ruleName => $ruleConfig) {
                if (
                    $ruleName !== 'email'
                    && isset($allowedOtherRulesForSpecificDataTypeRule['email'])
                    && !in_array($ruleName, $allowedOtherRulesForSpecificDataTypeRule['email'])
                ) {
                    cli_err_syntax_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` cannot have the `$ruleName` Rule!");
                    cli_info("The `email` Rule can ONLY have the following additional Rules:\n" . implode(",\n", quotify_elements($allowedOtherRulesForSpecificDataTypeRule['email'])) . "!");
                }
            }
            // Check if the 'max' value is lower than 6 then we warn that the email would be
            // too short as its max length.
            if (isset($sortedRulesForField['min'])) {
                $minValue = $sortedRulesForField['min']['value'];
                if (is_numeric($minValue) && $minValue < 6) {
                    cli_warning_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `min` Rule with a value less than 6!");
                    cli_info_without_exit("This means the email would be too short to be valid. Consider increasing the min value to at least 6!");
                }
            }
            if (isset($sortedRulesForField['max'])) {
                $maxValue = $sortedRulesForField['max']['value'];
                if (is_numeric($maxValue) && $maxValue < 6) {
                    cli_warning_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `max` Rule with a value less than 6!");
                    cli_info_without_exit("This means the email would be too short to be valid. Consider increasing the max value to at least 6!");
                }
            }
            // The first value of `between` Rule should also be at least 6 or warn
            if (isset($sortedRulesForField['between'])) {
                $betweenValue = $sortedRulesForField['between']['value'];
                if (is_array($betweenValue) && isset($betweenValue[0]) && is_numeric($betweenValue[0]) && $betweenValue[0] < 6) {
                    cli_warning_without_exit("The `email` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `between` Rule with a first value less than 6!");
                    cli_info_without_exit("This means the email would be too short to be valid. Consider increasing the first value to at least 6!");
                }
            }
        }

        // Special case for 'date' Rule
        if (isset($sortedRulesForField['date'])) {
            if (!isset($sortedRulesForField['date']['value'])) {
                cli_warning_without_exit("The `date` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` does not have a date format value set!");
                cli_info_without_exit("The `date` Rule will use the default date format of 'Y-m-d H:i:s' instead when being called!");
            }
            // If values exist for Date Rule then we iterate through each value
            // to check if it exists in $commonDateFormats and thus we replace
            // it with actual formatted date string. This is OPTIONAL though!
            elseif (isset($sortedRulesForField['date']['value'])) {
                $dateValue = $sortedRulesForField['date']['value'];
                if (is_string($dateValue)) {
                    if (array_key_exists($dateValue, $commonDateFormats)) {
                        $sortedRulesForField['date']['value'] = $commonDateFormats[$dateValue];
                    }
                }
                // If the date value is an array, we check each value against the common date formats
                elseif (is_array($dateValue)) {
                    foreach ($dateValue as $index => $subValue) {
                        if (array_key_exists($subValue, $commonDateFormats)) {
                            $dateValue[$index] = $commonDateFormats[$subValue];
                        }
                    }
                    // Reassign the possibly modified array back to the date value
                    $sortedRulesForField['date']['value'] = $dateValue;
                }

                // Get updated date value after checking against common formats
                $dateValue = $sortedRulesForField['date']['value'];

                // We now iterate through each character in the date value
                // to check that it only uses any of the valid date format characters
                if (is_string($dateValue)) {
                    $dateChars = str_split($dateValue);
                    foreach ($dateChars as $char) {
                        if (!in_array($char, $validDateFormatCharacters)) {
                            cli_err_syntax_without_exit("Invalid Date Format Character `$char` in `date` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            cli_info("The `date` Rule Value must only use valid date format characters:\n" . implode(",\n", quotify_elements($validDateFormatCharacters)) . ". Character list is based on: https://www.php.net/manual/en/datetime.format.php");
                        }
                    }
                } elseif (is_array($dateValue)) {
                    foreach ($dateValue as $subValue) {
                        $subChars = str_split($subValue);
                        foreach ($subChars as $char) {
                            if (!in_array($char, $validDateFormatCharacters)) {
                                cli_err_syntax_without_exit("Invalid Date Format Character `$char` in `date` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                                cli_info("The `date` Rule Value must only use valid date format characters:\n" . implode(",\n", quotify_elements($validDateFormatCharacters)) . ". Character list is based on: https://www.php.net/manual/en/datetime.format.php");
                            }
                        }
                    }
                }
            }
        }

        // Special case for "digit" Rule. We check that its value is (whether a single string
        // or an array of values) are all single digits (0-9) and if not, we error out. We also
        // check for duplicates in the digits and if there are duplicates, we error out.
        if (isset($sortedRulesForField['digit'])) {
            if (isset($sortedRulesForField['digit']['value'])) {
                $digitValue = $sortedRulesForField['digit']['value'];
                // Check if the string is a single digit
                if (is_string($digitValue)) {

                    if (!preg_match('/^[0-9]$/', $digitValue)) {
                        cli_err_syntax_without_exit("Invalid `digit` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("The `digit` Rule Value must be a Single Digit (0-9)!");
                    }
                } elseif (is_array($digitValue)) {
                    // Check that each value in the array is a single digit
                    foreach ($digitValue as $subValue) {
                        if (!preg_match('/^[0-9]$/', $subValue)) {
                            cli_err_syntax_without_exit("Invalid `digit` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            cli_info("If several Digits are used, the `digit` Rule Value must be an Array of only Single Digits (0-9)!");
                        }
                    }
                    // Check for duplicates in the array
                    if (count($digitValue) !== count(array_unique($digitValue))) {
                        cli_err_syntax_without_exit("Duplicate Digits found in `digit` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("All Digits must be unique in the `digit` Rule Value Array!");
                    }
                } else {
                    cli_err_syntax_without_exit("Invalid `digit` Rule Value Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("The `digit` Rule Value must be a Single Digit or an Array of Single Digits (0-9)!");
                }
            }
            // We loop through "$sortedRulesForField" to see if the "digit" Rule
            // has any other rules that are NOT inside of "$allowedOtherRulesForSpecificDataTypeRule"
            // meaning they cannot be used with the "digit" Rule and we error out.
            foreach ($sortedRulesForField as $ruleName => $ruleConfig) {
                if (
                    $ruleName !== 'digit'
                    && isset($allowedOtherRulesForSpecificDataTypeRule['digit'])
                    && !in_array($ruleName, $allowedOtherRulesForSpecificDataTypeRule['digit'])
                ) {
                    cli_err_syntax_without_exit("The `digit` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` cannot have the `$ruleName` Rule!");
                    cli_info("The `digit` Rule can ONLY have the following additional Rules:\n" . implode(",\n", quotify_elements($allowedOtherRulesForSpecificDataTypeRule['digit'])) . "!");
                }
            }
        }

        // Special cases for "password" Rule
        if (isset($sortedRulesForField['password'])) {
            // We loop through "$validationArray" to see if there is a "password_custom" rule
            // in any of the other $DXKeys and if there is, we warn the user that
            // they should also use "password_confirm" to increase security.
            $foundPasswordConfirm = false;
            foreach ($validationArray as $key => $value) {
                if (is_string($value) && str_contains($value, 'password_confirm')) {
                    $foundPasswordConfirm = true;
                    break;
                }
            }
            if (!$foundPasswordConfirm) {
                cli_warning_without_exit("The `password` Data Type Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is recommended (and also optional) to also have an accompanying `password_confirm` Data Type in another `\$DX Key`!");
            }
            // Check that current $DXKey also has a "between" Rule
            if (!isset($sortedRulesForField['between'])) {
                cli_err_syntax_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` need a `between` Rule!");
                cli_info("Add the `between` Rule to `$currentDXKey` to use the `password` Rule!");
            }
            // If the "between" Rule's first value is shorter than 12 characters, we warn but allow it
            if (isset($sortedRulesForField['between'])) {
                $betweenValue = $sortedRulesForField['between']['value'];
                if ($betweenValue[0] < 12) {
                    cli_warning_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has a `between` Rule with a first value less than 12!");
                    cli_info_without_exit("Allow passwords shorter than 12 characters? Otherwise change the first value to at least 12!");
                }
            }
            // Check that current $DXKey also has a "required" Rule or just warn
            // because you might wanna use the `password` Rule without `required` Rule
            if (!isset($sortedRulesForField['required'])) {
                cli_err_syntax_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` need a `required` Rule!");
                cli_info_without_exit("If `$currentDXKey` should be optional as a `password`, use the `string` Data Type with the `password_hash` Rule instead!");
                cli_info("This will password_hash the value stored in `$currentDXKey` after ALL validation has passed for all Input Data!");
            }
            if (isset($sortedRulesForField['nullable'])) {
                cli_err_syntax_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` cannot use `nullable` Rule!");
                cli_info_without_exit("If `$currentDXKey` should be optional as a `password`, use the `string` Data Type with the `password_hash` Rule instead!");
                cli_info("This will \"password_hash\" the value stored in `$currentDXKey` after ALL validation has passed for the value stored in `$currentDXKey`!");
            }
            // Check if the `password` has any values stored, and otherwise just warn and inform what each
            // value means in what order if they wanna use it.
            if (!isset($sortedRulesForField['password']['value']) || empty($sortedRulesForField['password']['value'])) {
                cli_warning_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has no value set!");
                cli_warning_without_exit("This means you have NO CHECKS on the password length or complexity - just a heads up!");
                cli_info_without_exit("Values for the `password` Rule are parsed in this order:number_of_uppercases,number_of_lowercases,number_of_digits,number_of_specials");
                cli_info_without_exit("Or specify them using `uppercases:INT`, `lowercases:INT`, `specials:INT`, `numbers:INT` as additional Rules for the `password` Data Type Rule!");
                cli_info_without_exit("EXAMPLE:`password:2,2,2,2` means 2 uppercases, 2 lowercases, 2 digits and 2 specials!");
                cli_info_without_exit("If you wanna change what is considered a special character, either use `password_custom` Data Type Rule for your very own");
                cli_info_without_exit("Custom Password Validation Logic OR edit `_internals/functions/d_data_funs.php` in the `funk_validate_password` function!");
            }
            // If `password` rule has values, check that each value are all integers!
            if (isset($sortedRulesForField['password']['value'])) {
                $passwordValues = $sortedRulesForField['password']['value'];
                if (is_string($passwordValues)) {
                    // Check that string is a single integer by trying to parse it
                    if (!is_int($passwordValues)) {
                        cli_err_syntax_without_exit("Invalid `password` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info_without_exit("Specify an Array of Integers as the value for the `password` Rule. Up to 4 Integers are allowed!");
                        cli_info("First=number of uppercases, Second=number of lowercases, Third=number of digits, Fourth=number of specials!");
                    }
                } elseif (is_array($passwordValues)) {
                    if (count($passwordValues) < 1 || count($passwordValues) > 4) {
                        cli_err_syntax_without_exit("Invalid `password` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info_without_exit("Specify an Array of Integers as the value for the `password` Rule. Up to 4 Integers are allowed!");
                        cli_info("First=number of uppercases, Second=number of lowercases, Third=number of digits, Fourth=number of specials!");
                    }
                    foreach ($passwordValues as $value) {
                        if (!is_int($value)) {
                            cli_err_syntax_without_exit("Invalid `password` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                            cli_info_without_exit("Specify an Array of Integers as the value for the `password` Rule. Up to 4 Integers are allowed!");
                            cli_info("First=number of uppercases, Second=number of lowercases, Third=number of digits, Fourth=number of specials!");
                        }
                    }
                }
            }
            // If `password` rule has an error message, we warn about how it only returns a single error default message
            // even if you check four uppercases, lowercases, digits and specials at the same time. Recommended to add a
            // custom error message to the `password` Rule so it is more informative.
            if (!isset($sortedRulesForField['password']['err_msg'])) {
                cli_warning_without_exit("The `password` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has no Custom Error Message set!");
                cli_info_without_exit("The Default Error Message only returns about one thing at a time (uppercase missing, digit missing, etc.)");
                cli_info_without_exit("Recommended to add a custom error message to the `password` Rule so it is more informative!");
            }
            // Here, the "password" rule passed all checks
            // so we now add it '<CONFIG>' => 'passwords_to_match' array!
            // When we come acropss 'password_confirm' Rule we will add it as the value to this!
            $convertedValidationArray['<CONFIG>']['passwords_to_match'][$currentDXKey] = "";
        }

        // Special cases for "password_confirm" Rule
        if (isset($sortedRulesForField['password_confirm'])) {
            $foundPasswordCustom = false;
            foreach ($validationArray as $key => $value) {
                if (is_string($value) && str_contains($value, 'password') && !str_contains($value, 'password_confirm')) {
                    $foundPasswordCustom = true;
                    break;
                }
            }
            if (!$foundPasswordCustom) {
                cli_warning_without_exit("The `password_confirm` Data Type Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is recommended to also have an accompanying `password` Data Type in another `\$DX Key`!");
            }
            // Check that password_confirm has a value
            if (!isset($sortedRulesForField['password_confirm']['value']) || empty($sortedRulesForField['password_confirm']['value'])) {
                cli_err_syntax_without_exit("The `password_confirm` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must have a value!");
                cli_info("Specify the Password Field (the field with a `password` Data Type Rule) as the value for the `password_confirm` Rule!");
            }
            // Check that any $validationArray that has the value from password_confirm
            if (isset($sortedRulesForField['password_confirm']['value'])) {
                $passwordConfirmValue = $sortedRulesForField['password_confirm']['value'];
                // If the value is not a string, we error out
                if (!is_string_and_not_empty($passwordConfirmValue)) {
                    cli_err_syntax_without_exit("Invalid `password_confirm` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify a Non-Empty String as the value for the `password_confirm` Rule!");
                }
                // If the value is not a valid key in $validationArray, we error out
                if (!isset($validationArray[$passwordConfirmValue])) {
                    cli_err_syntax_without_exit("Invalid `password_confirm` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info_without_exit("Specify a Valid Key (using the Data Type `password` or `password_custom`) from the \$DX Array as the value for the `password_confirm` Rule which it should confirm against.");
                    cli_info("This Data Type (`password` or `password_custom`) must be on the same Key Level as the `password_confirm` Rule that should confirm against it!");
                }
            }
            // Check that current $DXKey ONLY has "required" and "password_confirm" Rules meaning the count
            // should be 2, otherwise we error out since it is not allowed to have other rules
            if (count($sortedRulesForField) !== 2 || !isset($sortedRulesForField['required']) || !isset($sortedRulesForField['password_confirm'])) {
                cli_err_syntax_without_exit("The `password_confirm` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` must only have `required` and `password_confirm` Rules!");
                cli_info("Remove any other Rules from `$currentDXKey` to use the `password_confirm` Rule!");
            }
            // All checks passed, so we add the `password_confirm` Rule to the corresponding "passwords_to_match" array
            // here its value is the key of the `password` Rule that it should confirm against. If it does not exist
            // we will error out later when we try to match the passwords so we error out here!
            if (!isset($convertedValidationArray['<CONFIG>']['passwords_to_match'][$sortedRulesForField['password_confirm']['value']])) {
                cli_err_syntax_without_exit("The `password_confirm` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` has no corresponding `password` Rule to confirm against!");
                cli_info("Specify a Valid Key (using the Data Type `password` or `password_custom`) from the \$DX Array as the value for the `password_confirm` Rule which it should confirm against.");
                cli_info("This Data Type (`password` or `password_custom`) must be on the same Key Level as the `password_confirm` Rule that should confirm against it!");
            } else {
                $convertedValidationArray['<CONFIG>']['passwords_to_match'][$sortedRulesForField['password_confirm']['value']] = $currentDXKey;
            }
        }

        // Special cases for the "between" Rule
        if (isset($sortedRulesForField['between'])) {
            $betweenValue = $sortedRulesForField['between']['value'];
            // If between is not an array, we error out
            if (!is_array($betweenValue)) {
                cli_err_syntax_without_exit("Invalid `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify Two Numbers (separated with a comma) as the value for the `between` Rule!");
            }
            // If between is not two numbers, we error out
            if (count($betweenValue) !== 2 || !is_numeric($betweenValue[0]) || !is_numeric($betweenValue[1])) {
                cli_err_syntax_without_exit("Invalid `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify Two Numbers (separated with a comma) as the values for the `between` Rule!");
            }
            if ((is_float($betweenValue[0]) || is_float($betweenValue[1])) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `between` rule with decimal value(s)!");
            }
            if ($betweenValue[0] > $betweenValue[1]) {
                cli_err_syntax_without_exit("The `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` First Number is Larger than the Second Number!");
                cli_info("Specify the First Number as less than or equal to the Second Number for the `between` Rule!");
            }
            if ($betweenValue[0] === $betweenValue[1]) {
                cli_warning_without_exit("The `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal between Both Numbers!");
                cli_info_without_exit("Recommended is to use `exact` but this will work without issues, might be confusing though!");
            }
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($betweenValue[0])
                && ($betweenValue[0] < 0 || $betweenValue[1] < 0)
            ) {
                cli_err_syntax_without_exit("Invalid `between` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the First Value in the Array for the `between` Rule when Data Type is a String or an array!");
            }
            // Cannot use "min", "max", "size", "exact", rules with "between" rule since it is confusing
            if (
                isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['exact'])
                || isset($sortedRulesForField['any_of_these_values'])
            ) {
                cli_err_syntax_without_exit("The `between` Rule does not work with `min`, `max`, `size`, `exact` or `any_of_these_values`, Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `between` Rule is meant to be a range which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `min`, `max`, `size`, `exact`, `any_of_these_values`, Rules to use the `between` Rule - or vice versa!");
            }
        }

        // Special cases for the "decimals" Rule
        if (isset($sortedRulesForField['decimals']) && !isset($sortedRulesForField['float'])) {
            cli_err_syntax_without_exit("The `decimals` Rule needs `float` data type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
            cli_info("Specify the `float` data type rule for `$currentDXKey` if you want to use the `decimals` rule!");
        }
        if (isset($sortedRulesForField['decimals'])) {
            $decimalsValue = $sortedRulesForField['decimals']['value'];
            if (is_array($decimalsValue)) {
                if (count($decimalsValue) > 2 || count($decimalsValue) < 1) {
                    cli_err_syntax_without_exit("Invalid `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify either one(1) or two(2) integers as the value for the `decimals` rule!");
                }
                if ($decimalsValue[0] > 20 || (count($decimalsValue) > 1 && $decimalsValue[1] > 20)) {
                    cli_warning_without_exit("Dangerous `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info_without_exit("Specify integers between 0 and 20 as the value for the `decimals` rule!");
                }
                if ($decimalsValue[0] > $decimalsValue[1]) {
                    cli_warning_without_exit("Dangerous `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info_without_exit("Specify the First Integer as less than or equal to the Second Integer for the `decimals` rule!");
                }
                if ($decimalsValue[0] === $decimalsValue[1]) {
                    cli_warning_without_exit("The `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal between Both Numbers!");
                    cli_info_without_exit("Recommended is to use `exact` but this will work without issues, might be confusing though!");
                }
                foreach ($decimalsValue as $subValue) {
                    if (!is_int($subValue)) {
                        cli_err_syntax_without_exit("Invalid `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify only Integers (whole numbers) as the value for the `decimals` rule!");
                    }
                }
            } elseif (!is_int($decimalsValue)) {
                cli_err_syntax_without_exit("Invalid `decimals` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an integer or an array of integers (whole numbers) as the value for the `decimals` rule!");
            }
        }

        // Special cases for the "min" Rule
        if (isset($sortedRulesForField['min'])) {
            $minValue = $sortedRulesForField['min']['value'];
            // If min is not a number, we error out
            if (!is_numeric($minValue)) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Number as the value for the `min` rule!");
            }
            // When min is float but data type is NOT float, we error out
            if (is_float($minValue) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `min` rule with a decimal value!");
            }
            // If min is negative when data type is a string type, we error out
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($minValue)
                && $minValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `min` rule when Data Type is a String or an Array!");
            }
            // min is set but not max so warn about getting larger data than desired
            if (isset($sortedRulesForField['max']) && $sortedRulesForField['max']['value'] < $minValue) {
                cli_warning_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the `max` Rule Value!");
                cli_info_without_exit("This could lead to processing more than desired, consider adding a `max` Rule or changing to the `between` Rule instead!");
            }
            // min is set but not max so warn about getting larger data than desired
            if (!isset($sortedRulesForField['max'])) {
                cli_warning_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is set but the `max` Rule is not!");
                cli_info_without_exit("This could lead to processing more than desired, consider adding a `max` Rule or changing to the `between` Rule instead!");
            }
        }

        // Special cases for the "max" Rule
        if (isset($sortedRulesForField['max'])) {
            $maxValue = $sortedRulesForField['max']['value'];
            // If max is not a number, OR it is a float but not data type float or number, we error out
            if (!is_numeric($maxValue)) {
                cli_err_syntax_without_exit("Invalid `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Number as the value for the `max` rule!");
            }
            if (is_float($maxValue) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `max` rule with a decimal value!");
            }
            // If max is negative when data type is a string type, we error out
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($maxValue)
                && $maxValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `max` rule when Data Type is a String or an Array!");
            }
        }

        // Special case for "min" + "max" Rule
        if (isset($sortedRulesForField['min']) && isset($sortedRulesForField['max'])) {
            $minValue = $sortedRulesForField['min']['value'];
            $maxValue = $sortedRulesForField['max']['value'];
            // If min is larger than max, we error out
            if (is_numeric($minValue) && is_numeric($maxValue) && $minValue > $maxValue) {
                cli_err_syntax_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the `max` Rule Value!");
                cli_info("Specify the `min` Rule Value as less than the `max` Rule Value for `$currentDXKey`!");
            }
            if (is_numeric($minValue) && is_numeric($maxValue) && $minValue === $maxValue) {
                cli_warning_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal to the `max` Rule Value!");
                cli_info_without_exit("Recommended is to use `exact` but this will work without issues, might be confusing though!");
            }
        }

        // Special cases for the "count" Rule (affects arrays & lists)
        if (isset($sortedRulesForField['count'])) {
            $countValue = $sortedRulesForField['count']['value'];
            // If count is NOT used with "array" or "list" data type, we error out
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list']) && !isset($sortedRulesForField['set'])) {
                cli_err_syntax_without_exit("The `count` Rule must be used with Data Type `array`, `list` or `set` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array`, `list` OR `set` Data Type Rule for `$currentDXKey` to use the `count` rule!");
            }
            // If count is not a number, we error out
            if (!is_int($countValue)) {
                cli_err_syntax_without_exit("Invalid `count` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `count` Rule!");
            }
            // If count is negative when data type is a array type, we error out
            if (
                ($foundTypeCat === 'array_types')
                && is_int($countValue)
                && $countValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `count` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `count` Rule when Data Type is a String or an Array!");
            }
            // If count value is 0 but there is required rule, we error out
            if ($countValue === 0 && isset($sortedRulesForField['required'])) {
                cli_err_syntax_without_exit("The `count` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is 0 but there is a `required` Rule!");
                cli_info("Remove the `required` Rule or set the `count` Rule Value to 1 or more for `$currentDXKey`!");
            }
        }

        // Special cases for the "exact" Rule
        if (isset($sortedRulesForField['exact'])) {
            $exactValue = $sortedRulesForField['exact']['value'];

            // If data type is string typed then we strval force the exact value
            if (is_numeric($exactValue) && $foundTypeCat === "string_types") {
                $sortedRulesForField['exact']['value'] = strval($sortedRulesForField['exact']['value']);
                cli_info_without_exit("The `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Numberic while Data Type is String-typed!");
                cli_info("Its Rule Value converted to String Value for `$currentDXKey`. Change this manually back to numeric value if you intended to use an exact numeric value!");
            }

            // 'exact' Rule shouldn't not be combined with 'count', 'between', 'min', or 'max' Rules
            // as 'exact' is a strict rule that expects a strict single value.
            if (
                isset($sortedRulesForField['count']) || isset($sortedRulesForField['between'])
                || isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['digits'])
                || isset($sortedRulesForField['min_digits']) || isset($sortedRulesForField['max_digits'])
                || isset($sortedRulesForField['decmials'])
            ) {
                cli_err_syntax_without_exit("The `exact` Rule does not work with `count`, `decimals`, `size`, `between`, `digits`, `min_digits`, `max_digits`, `min`, or `max` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `exact` Rule is meant to be exact which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `count`, `decimals`, `size`, `between`, `digits`, `min_digits`, `max_digits`, `min`, `max` Rules to use the `exact` Rule - or vice versa!");
            }
            // $exactValue is NOT numeric but "number_types" data type is used, we error out
            if (
                !is_numeric($exactValue)
                && ($foundTypeCat === 'number_types')
            ) {
                cli_err_syntax_without_exit("Invalid `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Valid Single Number value for the `exact` Rule if you intend to use a Numeric Data Type or change Data Type to a String!");
            }
            // $exactValue is a string but data type is NOT string, we error out
            if (is_string($exactValue) && !isset($sortedRulesForField['string'])) {
                cli_err_syntax_without_exit("Invalid `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `string` Data Type Rule for `$currentDXKey` to use the `exact` rule with a string value!");
            }
            // $exactValue is a float value but data type is NOT float nor number, we error out
            if (is_float($exactValue) && !isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number'])) {
                cli_err_syntax_without_exit("Invalid `exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `exact` rule with a decimal value!");
            }
        }

        // Special cases for the "regex" & "no_regex" Rules
        if (isset($sortedRulesForField['regex'])) {
            $regexValue = $sortedRulesForField['regex']['value'];
            if ($foundTypeCat !== 'number_types' && $foundTypeCat !== 'string_types') {
                cli_err_syntax_without_exit("The `regex` Rule cannot be used with Array-typed Data Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a \"stringifiable\" Data Type Rule (string, numeric, etc.) for `$currentDXKey` to use the `regex` rule!");
            }
        }
        if (isset($sortedRulesForField['no_regex'])) {
            $noRegexValue = $sortedRulesForField['no_regex']['value'];
            if ($foundTypeCat !== 'number_types' && $foundTypeCat !== 'string_types') {
                cli_err_syntax_without_exit("The `no_regex` Rule cannot be used with Array-typed Data Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a \"stringifiable\" Data Type Rule (string, numeric, etc.) for `$currentDXKey` to use the `no_regex` rule!");
            }
        }

        // Special cases for the "size" Rule
        if (isset($sortedRulesForField['size'])) {
            $sizeValue = $sortedRulesForField['size']['value'];
            if (!is_numeric($sizeValue)) {
                cli_err_syntax_without_exit("Invalid `size` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Number as the value for the `size` rule!");
            }
            // When size is float but data type is NOT float, we error out
            if (is_float($sizeValue) && (!isset($sortedRulesForField['float']) && !isset($sortedRulesForField['number']))) {
                cli_err_syntax_without_exit("Invalid `size` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `float` OR `number` Data Type Rule for `$currentDXKey` to use the `size` rule with a decimal value!");
            }
            // If size is negative when data type is a string type, we error out
            if (
                ($foundTypeCat === 'string_types' || $foundTypeCat === 'array_types')
                && is_numeric($sizeValue)
                && $sizeValue < 0
            ) {
                cli_err_syntax_without_exit("Invalid `size` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Number as the value for the `size` rule when Data Type is a String or an Array!");
            }
        }

        // Special cases for the "min_digits" & "max_digits" Rules
        if (isset($sortedRulesForField['min_digits'])) {
            $minDigitsValue = $sortedRulesForField['min_digits']['value'];
            if (!is_int($minDigitsValue)) {
                cli_err_syntax_without_exit("Invalid `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `min_digits` rule!");
            }
            if ($minDigitsValue < 1) {
                cli_err_syntax_without_exit("Invalid `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Integer as the value for the `min_digits` rule!");
            }
        }
        if (isset($sortedRulesForField['max_digits'])) {
            $maxDigitsValue = $sortedRulesForField['max_digits']['value'];
            if (!is_int($maxDigitsValue)) {
                cli_err_syntax_without_exit("Invalid `max_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `max_digits` rule!");
            }
            if ($maxDigitsValue < 1) {
                cli_err_syntax_without_exit("Invalid `max_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Integer as the value for the `max_digits` rule!");
            }
        }
        if (isset($sortedRulesForField['min_digits']) && isset($sortedRulesForField['max_digits'])) {
            $minDigitsValue = $sortedRulesForField['min_digits']['value'];
            $maxDigitsValue = $sortedRulesForField['max_digits']['value'];
            // If min_digits is larger than max_digits, we error out
            if (is_numeric($minDigitsValue) && is_numeric($maxDigitsValue) && $minDigitsValue > $maxDigitsValue) {
                cli_err_syntax_without_exit("The `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the `max_digits` Rule Value!");
                cli_info("Specify the `min_digits` Rule Value as less than the `max_digits` Rule Value for `$currentDXKey`!");
            }
            if (is_numeric($minDigitsValue) && is_numeric($maxDigitsValue) && $minDigitsValue === $maxDigitsValue) {
                cli_warning_without_exit("The `min_digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Equal to the `max_digits` Rule Value!");
                cli_info_without_exit("Recommended is to use `digits` but this will work without issues, might be confusing though!");
            }
            // When both are used we cannot use "digits" Rule since it is a special case
            if (isset($sortedRulesForField['digits'])) {
                cli_err_syntax_without_exit("The `min_digits` and `max_digits` Rules cannot be used with the `digits` Rule for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Remove the `digits` Rule to use the `min_digits` and `max_digits` Rules - or vice versa!");
            }
            // When between are used and one of its number values have fewer
            // or more digits than the `min_digits` and `max_digits` Rules, we error out
            if (isset($sortedRulesForField['between'])) {
                $betweenValue = $sortedRulesForField['between']['value'];
                if (
                    (is_int($betweenValue[0]) && strlen((string)$betweenValue[0]) !== $minDigitsValue)
                    || (is_int($betweenValue[1]) && strlen((string)$betweenValue[1]) !== $maxDigitsValue)
                ) {
                    cli_err_syntax_without_exit("The `min_digits` and `max_digits` Rule Values for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` do not match the `between` Rule Value!");
                    cli_info("Specify the `min_digits` and `max_digits` Rule Values as equal to the number of digits in the `between` Rule Value for `$currentDXKey`!");
                }
            }
        }

        // Special cases for the "digits" Rule
        if (isset($sortedRulesForField['digits'])) {
            $digitsValue = $sortedRulesForField['digits']['value'];
            if (!is_int($digitsValue)) {
                cli_err_syntax_without_exit("Invalid `digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single Integer as the value for the `digits` rule!");
            }
            if ($digitsValue < 1) {
                cli_err_syntax_without_exit("Invalid `digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Non-Negative Integer as the value for the `digits` rule!");
            }
            if (isset($sortedRulesForField['min_digits']) || isset($sortedRulesForField['max_digits'])) {
                cli_err_syntax_without_exit("The `digits` Rule cannot be used with `min_digits` or `max_digits` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Remove `min_digits` or `max_digits` Rules to use the `digits` Rule - or vice versa!");
            }
            // if "min" or "max" is set, then its number of digits must be equal to the "digits" rule
            if (isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])) {
                $minValue = $sortedRulesForField['min']['value'] ?? null;
                $maxValue = $sortedRulesForField['max']['value'] ?? null;
                if (
                    (is_numeric($minValue) && strlen((string)$minValue) !== $digitsValue)
                    || (is_numeric($maxValue) && strlen((string)$maxValue) !== $digitsValue)
                ) {
                    cli_err_syntax_without_exit("The `digits` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` does not match the `min` and/or `max` Rule Value!");
                    cli_info("Specify the `digits` Rule Value as equal to the number of digits in the `min` and/or `max` Rule Value for `$currentDXKey`!");
                }
            }
        }

        // Special case for "color" Rule
        if (isset($sortedRulesForField['color'])) {
            $colorValue = $sortedRulesForField['color']['value'];
            if (!isset($sortedRulesForField['string'])) {
                cli_err_syntax_without_exit("The `color` Rule need the Data Type `string` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `string` Data Type Rule for `$currentDXKey` to use the `color` rule!");
            }
            if (!is_string($colorValue)) {
                cli_err_syntax_without_exit("Invalid `color` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Single String as the value for the `color` rule!");
            }
            if (!preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $colorValue)) {
                cli_err_syntax_without_exit("Invalid `color` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a Valid Six Character-based Hex Color String as the value for the `color` rule!");
            }
        }

        // Special case for "lowercase" & "uppercase" Rules
        if (isset($sortedRulesForField['lowercase']) || isset($sortedRulesForField['uppercase'])) {
            if ($foundTypeCat !== 'string_types') {
                cli_err_syntax_without_exit("The `lowercase` and/or `uppercase` Rule need a String-like  Data Type for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify a String-like Data Type Rule for `$currentDXKey` to use the `lowercase` and/or `uppercase` rule!");
            }
            if (isset($sortedRulesForField['lowercase']) && isset($sortedRulesForField['uppercase'])) {
                cli_err_syntax_without_exit("Cannot combine the `lowercase` and `uppercase` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Remove one of the Rules to use the other for `$currentDXKey`!");
            }
        }

        // Special case for "array_keys" Rule (which
        // checks if the array has specific keys)
        if (isset($sortedRulesForField['array_keys'])) {
            $arrayKeysValue = $sortedRulesForField['array_keys']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_keys` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_keys` rule!");
            }
            if (!is_array($arrayKeysValue)) {
                cli_err_syntax_without_exit("Invalid `array_keys` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Strings as the value for the `array_keys` rule!");
            }
            foreach ($arrayKeysValue as $key) {
                if (!is_string($key) && !is_int($key)) {
                    cli_err_syntax_without_exit("Invalid `array_keys` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings and/or Integers as the value for the `array_keys` rule!");
                }
            }
        }

        // Special case for "array_values" Rule (which checks if the array
        // has specific values inside of it without caring about the keys)
        if (isset($sortedRulesForField['array_values'])) {
            $arrayValuesValue = $sortedRulesForField['array_values']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_values` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_values` rule!");
            }
            if (!is_array($arrayValuesValue)) {
                cli_err_syntax_without_exit("Invalid `array_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Primitive Values (strings, numbers, booleans, nulls, etc.) as the value for the `array_values` rule!");
            }
            foreach ($arrayValuesValue as $value) {
                if (!is_string($value) && !is_numeric($value) && !is_bool($value) && !is_null($value)) {
                    cli_err_syntax_without_exit("Invalid `array_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings, Numbers, Booleans and/or Nulls as the value for the `array_values` rule!");
                }
            }
            // "min" rule demanding more the set array values than actually specified
            if (isset($sortedRulesForField['min']) && is_int($sortedRulesForField['min']['value'])) {
                $minValue = $sortedRulesForField['min']['value'];
                if ($minValue > count($arrayValuesValue)) {
                    cli_err_syntax_without_exit("The `min` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Larger than the Number of Values in the `array_values` Rule!");
                    cli_info("Specify the `min` Rule Value as less than or equal to the Number of Values in the `array_values` Rule for `$currentDXKey`!");
                }
            }
            // "max" rule is less than the set array values than actually specified
            if (isset($sortedRulesForField['max']) && is_int($sortedRulesForField['max']['value'])) {
                $maxValue = $sortedRulesForField['max']['value'];
                if ($maxValue < count($arrayValuesValue)) {
                    cli_err_syntax_without_exit("The `max` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName` is Less than the Number of Values in the `array_values` Rule!");
                    cli_info("Specify the `max` Rule Value as greater than or equal to the Number of Values in the `array_values` Rule for `$currentDXKey`!");
                }
            }
        }

        // Special case for "array_keys_exact" Rule (which
        // checks if the array has specific keys and no more)
        if (isset($sortedRulesForField['array_keys_exact'])) {
            $arrayKeysExactValue = $sortedRulesForField['array_keys_exact']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_keys_exact` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_keys_exact` rule!");
            }
            if (!is_array($arrayKeysExactValue)) {
                cli_err_syntax_without_exit("Invalid `array_keys_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Strings as the value for the `array_keys_exact` rule!");
            }
            foreach ($arrayKeysExactValue as $key) {
                if (!is_string($key) && !is_int($key)) {
                    cli_err_syntax_without_exit("Invalid `array_keys_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings and/or Integers as the value for the `array_keys_exact` rule!");
                }
            }
            // "min", "max", "between", "exact", "size", rules are not allowed with "array_keys_exact"
            // since its exact number of elements with values implies its count automatically
            if (
                isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['between']) || isset($sortedRulesForField['exact'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['count'])
            ) {
                cli_err_syntax_without_exit("The `array_keys_exact` Rule cannot be used with `min`, `max`, `between`, `exact`, `size`, or `count` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `array_keys_exact` Rule is meant to be exact which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `min`, `max`, `between`, `exact`, `size`, or `count` Rules to use the `array_keys_exact` Rule!");
            }
        }

        // Special case for "array_values_exact" Rule (which checks if the array
        // has specific values inside of it without caring about the keys)
        if (isset($sortedRulesForField['array_values_exact'])) {
            $arrayValuesExactValue = $sortedRulesForField['array_values_exact']['value'];
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `array_values_exact` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `array_values_exact` rule!");
            }
            if (!is_array($arrayValuesExactValue)) {
                cli_err_syntax_without_exit("Invalid `array_values_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Primitive Values (strings, numbers, booleans, nulls, etc.) as the value for the `array_values_exact` rule!");
            }
            foreach ($arrayValuesExactValue as $value) {
                if (!is_string($value) && !is_numeric($value) && !is_bool($value) && !is_null($value)) {
                    cli_err_syntax_without_exit("Invalid `array_values_exact` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array mixed with Strings, Numbers, Booleans and/or Nulls as the value for the `array_values_exact` rule!");
                }
            }
            // "min", "max", "between", "exact", "size", rules are not allowed with "array_values_exact"
            // since its exact number of elements with values implies its count automatically
            if (
                isset($sortedRulesForField['min']) || isset($sortedRulesForField['max'])
                || isset($sortedRulesForField['between']) || isset($sortedRulesForField['exact'])
                || isset($sortedRulesForField['size']) || isset($sortedRulesForField['count'])
            ) {
                cli_err_syntax_without_exit("The `array_values_exact` Rule cannot be used with `min`, `max`, `between`, `exact`, `size`, or `count` Rules for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info_without_exit("The `array_values_exact` Rule is meant to be exact which conflicts with other 'exact'-like Rules or scalar-like Rules!");
                cli_info("Remove `min`, `max`, `between`, `exact`, `size`, or `count` Rules to use the `array_values_exact` Rule!");
            }
        }

        // Special case for "elements_this_type_order" Rule which must have a
        // "array" or "list" data type set first, and then it can be used
        if (isset($sortedRulesForField['elements_this_type_order'])) {
            // If data type is not "array" or "list", we error out
            if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                cli_err_syntax_without_exit("The `elements_this_type_order` Rule need the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `elements_this_type_order` rule!");
            }
            // Convert to array if needed
            $values = is_string($sortedRulesForField['elements_this_type_order']['value']) ?
                [$sortedRulesForField['elements_this_type_order']['value']] :
                $sortedRulesForField['elements_this_type_order']['value'];

            // Iterate and make sure each element is of one of the allowed types
            $allowedTypes = [
                'array',
                'list',
                'checked',
                'unchecked',
                'char',
                'string',
                'number',
                'boolean',
                'null',
                'float',
                'integer'
            ];
            foreach ($values as $value) {
                if (!in_array($value, $allowedTypes, true)) {
                    cli_err_syntax_without_exit("Invalid `elements_this_type_order` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                    cli_info("Specify an Array of Allowed Types (array, list, checked, unchecked, char, string, number, boolean, null, float, integer) as the value for the `elements_this_type_order` rule!");
                }
            }
        }

        // Special case for "elements_any_of_these_values" Rule which checks
        // if a specific value is one of the valid values for the field
        if (isset($sortedRulesForField['any_of_these_values'])) {
            // First check that it has values in its value key and that it must be an array
            $values = $sortedRulesForField['any_of_these_values']['value'] ??  null;
            if (!is_array($values) || empty($values)) {
                cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                cli_info("Specify an Array of Primitive Values (strings, numbers, booleans, or nulls) as the value for the `any_of_these_values` rule!");
            }
            // Then we check that matching values are used based on what main data type it is!
            // For example, if it is a "boolean", it should only have "true", "false", 0 or 1 as values
            if (isset($sortedRulesForField['boolean'])) {
                $validValues = [true, false, 0, 1];
                foreach ($values as $value) {
                    if (!in_array($value, $validValues, true)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array of Valid Boolean Values (true, false, 0, 1) as the value for the `any_of_these_values` rule!");
                    }
                }
            } elseif (isset($sortedRulesForField['null'])) {
                // If it is a "null" data type, it should only have "null" as value
                foreach ($values as $value) {
                    if (!is_null($value)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array with only `null` as the value for the `any_of_these_values` rule!");
                    }
                }
            } elseif (isset($sortedRulesForField['string'])) {
                // If it is a "string" data type, it should only have strings as values
                foreach ($values as $value) {
                    if (!is_string($value)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array of Strings as the value for the `any_of_these_values` rule!");
                    }
                }
            } elseif (isset($sortedRulesForField['number']) || isset($sortedRulesForField['float']) || isset($sortedRulesForField['integer'])) {
                // If it is a "number", "float", or "integer" data type, it should only have numbers as values
                foreach ($values as $value) {
                    if (!is_numeric($value)) {
                        cli_err_syntax_without_exit("Invalid `any_of_these_values` Rule Value for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!");
                        cli_info("Specify an Array of Numbers (integers or floats) as the value for the `any_of_these_values` rule!");
                    }
                }
            }
        }

        // Special case for "elements_all_X" Rule which checks if all elements
        // in an array are of data type X, and if not, it errors out. This needs
        //  the "array" OR "list" data type to be set first though!
        $elementsAllRules = [
            'elements_all_arrays',
            'elements_all_checked',
            'elements_all_unchecked',
            'elements_all_lists',
            'elements_all_chars',
            'elements_all_strings',
            'elements_all_numbers',
            'elements_all_booleans',
            'elements_all_nulls',
            'elements_all_floats',
            'elements_all_integers',

        ];
        foreach ($elementsAllRules as $ruleName) {
            if (isset($sortedRulesForField[$ruleName])) {
                if (!isset($sortedRulesForField['array']) && !isset($sortedRulesForField['list'])) {
                    cli_err_syntax_without_exit(
                        "The `{$ruleName}` Rule needs the Data Type `array` or `list` for `$currentDXKey` in Validation `$handlerFile.php=>$fnName`!"
                    );
                    cli_info(
                        "Specify the `array` OR `list` Data Type Rule for `$currentDXKey` to use the `{$ruleName}` rule!"
                    );
                }
            }
        }

        /*
            MANY SPECIAL CASES ARE CHECKED HERE - END:
        */

        // Finally add the priority sorted rules to the converted validation array
        $convertedValidationArray[$currentDXKey]["<RULES>"] = $sortedRulesForField;

        // We check if the key contains a "." and if it does we need to split it
        // and then we need to rebuild the nested keys in the converted validation array
        // and then we need to set the value to the $sortedRulesForField array.
        if (
            str_contains($currentDXKey, ".")
            && preg_match("/^((\*|([a-z_]*)([a-z_0-9]+))\.(\*|[a-z_][a-z_0-9]+))(\.(\*|[a-z_][a-z_0-9]+))*$/", $currentDXKey)
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
            $currentNestedArray["<RULES>"] = $sortedRulesForField;
            unset($convertedValidationArray[$currentDXKey]);
        }
    }

    // We now grab all the array keys from the converted validation array
    // to validate for each key that contains a "*" also has a key that begins
    // with "key.*" before "key.*.subkey" and so on. The "key.*" is what would
    // hod the rules for that numbered array like count, min, max, etc. (what is
    // applicable to arrays). We error out when for example "key.*.subkey" is used
    // but the "key.*" is not used as a key in the validation array.
    $arrayKeys = array_keys($validationArray);

    // But first we loop through to find if ANY key contains "*.*" implying
    // a multi-dimensional array which is NOT supported yet so this will error out
    // and say it has not been implemented yet.
    foreach ($arrayKeys as $currentKey) {
        // If the key does not contain "*.*" we skip it
        if (!str_contains($currentKey, "*.*")) {
            continue;
        }
        // If the key contains "*.*" we error out and say it is not supported yet
        cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` contains a Multi-Dimensional Array which is not supported yet in FunkPHP!");
        cli_info_without_exit("Specify Single-Dimensional Arrays with keys containing only one `*` per level.");
        cli_info("For example: `key.*` or `key.*.subkey` but not `key.*.*.subkey`!");
    }
    foreach ($arrayKeys as $currentKey) {
        // Key doesn't contain "*", so we skip it
        if (!str_contains($currentKey, "*")) {
            continue;
        }
        // When we find a key that contains "*", but does not end with "*",
        // we split on the last ".", and check if the first part exists
        // anywhere in the validation array keys. Error out if it does not exist.
        if (str_contains($currentKey, "*") && !str_ends_with($currentKey, "*")) {
            $lastSplit = strrpos($currentKey, ".");
            $firstPart = substr($currentKey, 0, $lastSplit);
            if (!in_array($firstPart, $arrayKeys)) {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` requires the Key `$firstPart` to exist in the Validation Array!");
                cli_info("Add the Key `$firstPart` to the Validation Array to use the Key `$currentKey`!");
            }
        }
        // When a key ends with "*", we check that it actually has any subkeys
        // by looping through all keys to see if they start with the current key
        if (str_contains($currentKey, "*") && str_ends_with($currentKey, "*")) {
            $hasSubkeys = false;
            foreach ($arrayKeys as $key) {
                if (str_starts_with($key, $currentKey) && $key !== $currentKey) {
                    $hasSubkeys = true;
                    break;
                }
            }
            if (!$hasSubkeys) {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` requires at least one Subkey to exist!");
                cli_info("Add a Subkey (as in `$currentKey.SubKey`) to the \$DX Array that starts with `$currentKey` to use it!");
            }
        }
    }

    // Special case: if we have "*" as a key, we need to check that
    // all other keys also start with "*." because now we are saying that
    // the entire thing begins as a numbered array!
    if (array_key_exists("*", $convertedValidationArray)) {
        // If we have "*" as a key, we need to check that all other keys
        // start with "*." because now we are saying that the entire thing
        // begins as a numbered array!
        foreach ($arrayKeys as $currentKey) {
            if (!str_starts_with($currentKey, "*.") && $currentKey !== "*") {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` in Validation `$handlerFile.php=>$fnName` must start with `*.` when `*` is used as a root key!");
                cli_info("Change the Key `$currentKey` to start with `*.$currentKey` to use it!");
            }
        }
    }

    // We loop through the array keys again to check if any key
    // ends with ".*" and then we check if any other key ends
    // without ".*" but starts with the same key. This means
    // an associative key is competing with a numbered array key
    foreach ($arrayKeys as $currentKey) {
        // If the key does not end with ".*", we skip it
        if (!str_ends_with($currentKey, ".*")) {
            continue;
        }
        // If the key ends with ".*", we check if any other key
        // starts with the same key but does not end with ".*"
        $baseKey = substr($currentKey, 0, -2); // Remove ".*" from the end
        foreach ($arrayKeys as $otherKey) {
            if ($otherKey === $baseKey) {
                cli_err_syntax_without_exit("The Validation Key `$currentKey` (indicating a numbered array) in Validation `$handlerFile.php=>$fnName` conflicts with the Key `$otherKey` which is on the same key level!");
                cli_err_syntax_without_exit("It is invalid JSON to have both a Numbered Array[0-9] and an ['Associative_Key'] at the same Key Level!");
                cli_info_without_exit("Associative Key `['$otherKey'] = {values}` will conflict with Numbered Key(s) `['$baseKey'][0-9] = {values}`!");
                cli_info("Change the Key `$currentKey` to not end with `.*` (thus removing its numbered array function) or change the Key `$otherKey`!");
            }
        }
    }

    // Return the finally finalized converted validation array!
    return $convertedValidationArray;
}

// PROBABLY DEPRECATED NOW
// Compiles a $DX Validation [] to an optmized validation array that is returned within the same
// function that is used to validate the data. This is used to optimize the validation process!
// VERY IMPORTANT WARNING: This function calls a function which uses eval() to parse the validation file!!!
// PROBABLY DEPRECATED NOW
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
        cli_err("Validation Handler file \"$handlerFile.php\" not found in \"funkphp/validation/\" or not readable!");
    }

    // Prepare regex and find the function name in the file
    $fnNameRegex = '/^function (' . $fnName . ')\(\&\$c\)\s*\/\/ <[a-z_,\-0-9\*]*>\s*$.*?^};$/ims';
    $dxVarRegex = get_match_dx_function_regex();
    $dxReturnRegex = get_match_dx_return_regex();
    $fileContent = file_get_contents($handlerDir . $handlerFile . ".php");
    $matchedFn = preg_match($fnNameRegex, $fileContent, $matches);

    if (!$matchedFn) {
        cli_err("Validation Function \"$fnName\" not found in Validation Handler File \"funkphp/validation/$handlerFile.php\". Check for mispellings or typos?");
    }

    // We store found match and now try find the $DX variable in that part
    $matchedEntireFnName = $matches[0] ?? null;
    $matchedEntireFnCopy = $matchedEntireFnName;
    $matchedDX = preg_match($dxVarRegex, $matchedEntireFnName, $matches2);
    if (!$matchedDX) {
        cli_err_without_exit("The \"\$DX\" variable not found in Validation Function \"$fnName\" in Validation Handler File \"$handlerFile.php\".");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found. Only single quotes `['<DXarray>']` are allowed!");
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
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found.  Only single quotes `['<DXarray>']` are allowed!");
    }
    if ($evalCode === null) {
        cli_err_without_exit("The \"\$DX\" variable was found but could not be parsed as a valid PHP Array!");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the Validation Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found.  Only single quotes `['<DXarray>']` are allowed!");
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
        cli_success_without_exit("SUCCESSFULLY COMPILED Validation Rules to Optimized Rules in Validation Function \"$fnName\" in \"funkphp/validation/$handlerFile.php\".");
        cli_info("IMPORTANT: Open it in an IDE and press CMD+S or CTRL+S to autoformat the Validation File again!");
    }
}

// Simple Helper function that validates that a $binding (i, d, s or b)
// is correct based on provided $value. Returns true or false. Value
// "?" is always true, meaning it is a placeholder for any value!
function cli_validate_correct_binding_with_provided_value($binding, $value)
{
    // Both variables must be non empty strings! However, we do cast $value to string
    // to ensure that it is a string, even if it is a number or boolean. Since it is
    // also meant to validate whole numbers such as (i) and decimals/floats such as (d)!
    if (!is_string_and_not_empty($binding) || !is_string_and_not_empty((string)$value)) {
        cli_err("[cli_validate_correct_binding_with_provided_value]: Expects Non-Empty Strings as input for `\$binding` and `\$value`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not a String at all?");
    }
    // $binding must be one of the following: i, d, s, b
    // i = integer, d = double/float, s = string, b = blob
    // '?' is placeholder which means it is always true!
    if (!in_array($binding, ['i', 'd', 's', 'b',], true)) {
        cli_err("[cli_validate_correct_binding_with_provided_value]: Expects a valid binding type as input for `\$binding`! (i, d, s or b)");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not a String at all?");
    }
    // If value is "?" then it is always true
    if ($value === '?') return true;

    // Validate binding types: String
    if ($binding === 's') {
        if (is_string($value)) {
            return true;
        }
        return false;
    }
    // Validate binding types: Integers
    elseif ($binding === "i") {
        if (preg_match('/^-?\d+$/', $value)) {
            return true;
        }
        return false;
    }
    // Validate binding types: Doubles/Floats
    elseif ($binding === "d") {
        if (preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return true;
        }
        return false;
    }
    // Validate binding types: Blobs
    elseif ($binding === 'b') {
        if (is_string($value) && !empty($value)) {
            return true;
        }
        return false;
    }

    cli_err_without_exit("[cli_validate_correct_binding_with_provided_value]: Despite impossible, reached the end of Function Call without return value?!");
    cli_info("Check All References to this Function Call and ensure that the Binding Type is Correct!");
    return false;
}

// Simple Helper Function that can check if a `table:col` exists in the `tables.php` file
// and if it does it returns the binding type for that specific column. If it gets a string
// without ":" then it assumes it is a unique col based that can be found within $allTbs
// as long as that Table also exists in $tbs which is the currently tables being used.
function cli_find_valid_tb_col_and_binding_or_return_null($tbs, $tbColToCheck)
{
    if (!is_string_and_not_empty($tbColToCheck)) {
        cli_err_without_exit("[cli_find_valid_tb_col_and_binding_or_return_null]: Expects a Non-Empty String as input for `\$tbColToCheck`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not a String at all?");
    }
    if (!is_array_and_not_empty($tbs) || !array_is_list($tbs)) {
        cli_err_without_exit("[cli_find_valid_tb_col_and_binding_or_return_null]: Expects a Non-Empty List Array as input for `\$tbs`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    global $tablesAndRelationshipsFile;
    $allTbs = $tablesAndRelationshipsFile['tables'] ?? [];
    $binding = null;
    $found = false;
    $foundTable = null;
    $foundCol = null;

    // If it contains ":", we assume it is a table:col format
    if (str_contains($tbColToCheck, ":")) {
        [$tableName, $colName] = explode(":", $tbColToCheck, 2);
        if (!in_array($tableName, $tbs) || !array_key_exists($tableName, $allTbs)) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => null, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        if (!array_key_exists($colName, $allTbs[$tableName])) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => null, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        $foundCol = $colName;
        return [
            "found" => true,
            "binding" => $allTbs[$tableName][$colName]['binding'] ?? null,
            "found_table" => $tableName,
            "found_col" => $foundCol
        ];
    }
    // If it does not contain ":", we assume it is a unique column name
    // because that should be checked before this function is called
    else if (!str_contains($tbColToCheck, ":")) {
        // Iterate through all tables to find the column
        foreach ($allTbs as $tb => $colKey) {
            if (!in_array($tb, $tbs, true)) {
                continue;
            }
            foreach ($colKey as $k => $col) {
                if ($tbColToCheck === $k) {
                    $foundTable = $tb;
                    break 2; // Break out of both foreach loops
                }
            }
        }
        if (!$foundTable) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => null, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        // We found the table and column, now return the binding
        $binding = $allTbs[$foundTable][$tbColToCheck]['binding'] ?? null;
        if (!$binding) {
            return ["checked_col" => strtolower($tbColToCheck), "found" => false, "binding" => null, "found_table" => $foundTable, "found_col" => null, "tbs_checked" => array_map('strtolower', $tbs)];
        }
        $found = true;
        $foundCol = $tbColToCheck;
        return ["checked_col" => strtolower($tbColToCheck), "found" => $found, "binding" => $binding, "found_table" => $foundTable, "found_col" => $foundCol, "tbs_checked" => array_map('strtolower', $tbs)];
    }
    cli_err_without_exit("[cli_find_valid_tb_col_and_binding_or_return_null]: Reached the end of Function Call without return value?!");
    cli_info("Check All References to this Function Call and ensure that the `Col`|`Table:Col` Argument is Correct!");
}

// Function that parses the condition clauses such as WHERE
// a Simple SQL Query to an Optimized SQL Array! The
// &$builtBindedParamsString is to add the necessary
// "?" placeholders based on how many are used within
// the parsed Where clause that would be returned!
function cli_parse_condition_clause_sql($tbs, $where, $queryType, $sqlArray, $validCols, &$builtBindedParamsString, &$builtFieldsArray, &$allAliases, $whereOrHaving = null, &$aggAliases,  &$aliasesTbCol = null)
{
    // Prepare variables and also validate the input
    // $where = The actual CONDITION String to parse.
    // Since this function was first only for WHERE Conditions
    // but it is now more generalized to handle any condition clause!
    global $tablesAndRelationshipsFile, $mysqlOperatorSyntax;
    $parsedCondition = "";
    $allTbs = $tablesAndRelationshipsFile['tables'] ?? [];
    $singleTable = count($tbs) === 1;
    $uniqueCols = null;
    $tbsWithCols  = null;
    $relations = $tablesAndRelationshipsFile['relationships'] ?? [];

    // Keep track of even amount of left and right parentheses
    // to ensure they are balanced in the WHERE clause!
    $leftParenthesisCount = 0;
    $rightParenthesisCount = 0;

    // Special Starting Parts For Each $wPart when NOT ColName OR Table:ColName
    $specialSyntaxStart = [
        'AND=',
        'AND(=',
        'OR=',
        'OR(=',
        'NOT=',
        'NOT(=',
        'EXISTS=',
        'EXISTS(=',
        'IN=',
        'IN(=',
        'BETWEEN=',
        'BETWEEN(=',
    ];

    // Starting Parts for Aggregate Functions - but Agg Funcs are NOT allowed in WHERE clause!
    // so we use this to check and then error out if they are used in the WHERE clause!
    $aggregateFunctionsStart = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()/i";

    // $tbs must be an array and not empty
    if (!is_array_and_not_empty($tbs)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty Associative Array as input for `\$tables`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $relations must be an array but can be empty
    if (!is_array($relations)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects an Associative Array as input for `\$relations`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $where must be a string and not empty
    if (!is_string_and_not_empty($where)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty String as input for `\$where`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $queryType must be a string and not empty and be one of the allowed query types
    $allowedQueryTypes = [
        'SELECT_DISTINCT',
        'SELECT_INTO',
        'SELECT',
        'INSERT',
        'UPDATE',
        'DELETE'
    ];
    if (!is_string_and_not_empty($queryType) || !in_array($queryType, $allowedQueryTypes, true)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty String as input for `\$queryType` that is one of: " . implode(", ", $allowedQueryTypes) . "!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $sqlArray must be an array and not empty and not a list!
    if (!is_array_and_not_empty($sqlArray) || array_is_list($sqlArray)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty Associative Array as input for `\$sqlArray`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $validCols must be an array and not empty and not a list!
    // It must also contain the array keys "uniqueCols" and "table:col"
    if (!is_array_and_not_empty($validCols) || array_is_list($validCols) || !isset($validCols['uniqueCols']) || !isset($validCols['table:col'])) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty Associative Array as input for `\$validCols` that contains the keys: \"uniqueCols\" and \"table:col\"!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    $uniqueCols = $validCols['uniqueCols'];
    $tbsWithCols  = $validCols['table:col'];

    // $builtBindedParamsString must be a string but can be empty
    if (!is_string($builtBindedParamsString)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a String as input for `\$builtBindedParamsString`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    // $builtFieldsArray must be an array that is a list but can be empty
    if (!is_array($builtFieldsArray) || !array_is_list($builtFieldsArray)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a List Array as input for `\$builtFieldsArray`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // $whereOrHaving must be a string and not empty (either "WHERE" or "HAVING")
    if ($whereOrHaving === null) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects `WHERE` or `HAVING` for `\$whereOrHaving`!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (!is_string_and_not_empty($whereOrHaving)) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects a Non-Empty String as input for `\$whereOrHaving` that is either `WHERE` or `HAVING`!");
    }
    if ($whereOrHaving !== 'WHERE' && $whereOrHaving !== 'HAVING') {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Expects `WHERE` or `HAVING` for `\$whereOrHaving` but got: \"$whereOrHaving\"!");
        cli_info("This might mean that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // We split the $where on "|" string by spaces to get each part or turn the single string into an array
    $where = str_contains(trim($where), "|") ? explode("|", $where) : [$where];
    $wPartRegex = '/^(\[[A-Za-z_\-0-9]+]|COUNT\(DISTINCT [a-zA-Z0-9\-_:=]+\)|[()=A-Za-z_\-0-9:\*]+)\s+(\[[A-Za-z_\-0-9]+]|[+\-*\/%=&|^!<>]+|ALL|AND|BETWEEN|EXISTS|IN|LIKE|NOT|SOME)\s+(.*)$/i';

    // Regexes for Aggregate Functions. First to see if it starts with any of the aggregate functions
    // and the second to see if it is a complete aggregate function with a table and/or column name
    // and the special case of COUNT(*) which is a special case of the COUNT function!
    $aggregateFunctionsStart = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()/i";
    $aggFuncRegex = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()([a-zA-Z0-9_:\*]+)*\)$/i";
    $aggFuncValidStarts = [
        'count(distinct=' => 'count_distinct_',
        'count(distinct ' => 'count_distinct_',
        'count(' => 'count_',
        'count(*)' => 'count_all_',
        'sum(' => 'sum_',
        'avg(' => 'avg_',
        'min(' => 'min_',
        'max(' => 'max_',
    ];
    $aggFuncAliasValidStartRegex = "/^(avg_|sum_|min_|max_|count_|count_all|count_distinct_)([a-zA-Z0-9_]+)$/i";
    // Commands that You might wanna SELECT but are not allowed to do
    // through FunkPHP so you get an error message about it!
    $disallowedCommands = [
        "BIN(",
        "BINARY",
        "CASE",
        "CAST",
        "COALESCE(",
        "CONVERT(",
        "CONV(",
        "CONNECTION_ID()",
        "CURRENT_USER",
        "DATABASE()",
        "IF(",
        "IFNULL(",
        "ISNULL(",
        "LAST_INSERT_ID()",
        "SESSION_USER()",
        "SYSTEM_USER()",
        "USER()",
        "VERSION()",
    ];

    // SPECIAL REGEXES that always are ran before the main $wPartRegex!
    // TODO: Might get implemented or not at a later stage!
    $tableCol_BETWEEN_VAL1_AND_VAL2 = "/^([a-zA-Z0-9_:])+\s*(BETWEEN)\s*([a-zA-Z0-9]+)\s*(AND)\s*([a-zA-Z0-9]+)$/i";
    $tableCol_NOT_BETWEEN_VAL1_AND_VAL2 = "/^([a-zA-Z0-9_:])+\s*(NOT BETWEEN)\s*([a-zA-Z0-9]+)\s*(AND)\s*([a-zA-Z0-9]+)$/i";
    $tableCol_IN_VAL_ARRAY = "/^([a-zA-Z0-9_:]+)\s*(NOT IN)+\s*\((.*)\)$/i";
    $tableCol_NOT_IN_VAL_ARRAY = "/^([a-zA-Z0-9_:]+)\s*(NOT IN)+\s*\((.*)\)$/i";
    $tableCol_NOT_LIKE_VAL = "/^([a-zA-Z0-9_:]+)\s*(NOT LIKE)+\s*(.*)$/i";
    $NOT_tableCol_OPERATOR_VAL = "/^(NOT)\s*([a-zA-Z0-9_:]+)\s*([+\-*\/%=&|^!<>])+\s*(.*)$/i";


    // We now iterate through each part and we use regex to parse the Condition clause where it should
    // begin with a column name/tableName:columnName, followed by an operator, and then a value.
    foreach ($where as $index => $wPart) {
        // SPECIAL CASE: Escaping any parsing using {} meaning it will just be added as is
        // This is useful when current Parsing Logic is not supported yet but the Developer
        // knows what they are doing and just wanna add their Condition Clause Logic without
        // having to wait for a new FunkPHP Version to be released! ;-P
        if (str_starts_or_ends_not_with($wPart, "{", "}")) {
            cli_err_syntax_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Starting/Ending { or } but not the other way around!");
            cli_info_without_exit("You need BOTH to Start and End with { respectively } to Escape the Condition Clause Logic!");
            cli_info("When you use { and } you are telling FunkPHP to just add the Condition Clause Logic \"As Is\" WITHOUT any parsing! However, this does NOT allow You to DROP/ALTER/TRUNCATE Tables and/or entire Databases! (for obvious reasons)");
        }
        if (str_starts_ends_with($wPart, "{", "}")) {
            if (str_contains(strtoupper($wPart), "DROP TABLE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `DROP TABLE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            if (str_contains(strtoupper($wPart), "DROP DATABASE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `DROP DATABASE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            if (str_contains(strtoupper($wPart), "ALTER TABLE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `ALTER TABLE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            if (str_contains(strtoupper($wPart), "TRUNCATE TABLE")) {
                cli_err_syntax("[cli_parse_condition_clause_sql]: /!\ GOTTA STOP YOU RIGHT THERE! /!\ You are using a `TRUNCATE TABLE` Statement in the Condition Clause. This is where FunkPHP draws the line; that is something you gotta do manually as DB Admin instead!");
            }
            // Remove the {}, add it "as is" to the parsed
            // condition inform the Developer and continue!
            $wPart = substr(trim($wPart), 1, -1);
            $parsedCondition .= " " . trim($wPart);
            cli_warning_without_exit("[cli_parse_condition_clause_sql]: Added \"As Is\" Escaped Condition Clause Logic: `$wPart` in Query Type: \"$queryType\". Hopefully it still works when sent as Prepared Statement!");
            cli_info_without_exit("IMPORTANT: If you are using placeholders `?` in the `$wPart` you must add them to the `bparam` Key in the SQL Array and also add the `<MATCHED_FIELDS>` keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
            continue;
        }
        if (preg_match($aggregateFunctionsStart, $wPart) && $whereOrHaving === 'WHERE') {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to using an Aggregate Function in the WHERE clause!");
            cli_info_without_exit("Aggregate Functions like COUNT(), SUM(), AVG(), MIN(), MAX() are NOT allowed in the Condition Clause for `WHERE` Key, only for `HAVING` Key!");
            cli_info("If you want to use Aggregate Functions, use them in the SELECT and/or GROUP BY Clauses instead!");
        }
        // Condition Clause can't start with a special syntax start
        if ($index === 0) {
            if (str_starts_with($wPart, "(") && str_ends_with($wPart, ")")) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to starting with a parenthesis '(' or ')'!");
                cli_info_without_exit("Starting a Condition Clause using () to indicate a Tuple or Row Constructor is not supported as of yet in FunkPHP!");
                cli_info_without_exit("If you wanna use a [SubQuery] means you should start with `[` and end with `]`. This allows you to use Tuples, Row Constructors and such.");
                cli_info("IMPORTANT: Using [SubQuery] means you lose the Validation Parsing Logic and you must add the `?` Placeholders  in the `bparam` Key and the <MATCHED_FIELDS> keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
            }
            foreach ($specialSyntaxStart as $specialStart) {
                if (str_starts_with($wPart, $specialStart)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to starting with a special syntax start: \"$specialStart\"!");
                    cli_info("The first part of the Condition clause cannot start with a special syntax start like:\n" . implode(",\n", quotify_elements($specialSyntaxStart)) . "! If you wanna use a [SubQuery] you should start with `[` and end with `]`!");
                }
            }
        }

        // If it is ONLY  ")", we just add it and
        // increase the count of left or right parentheses
        if ($wPart === ')') {
            $rightParenthesisCount++;
            $parsedCondition .= $wPart;
            continue;
        }

        // $wPart might be an entire [SubQuery] so we check if it starts with "[" and ends with "]".
        // It does NOT apply when Query Type is INSERT since Subqueries are not allowed there!
        if (preg_match('/^\[[A-Za-z_\-0-9]+\]$/', $wPart)) {
            if ($queryType === 'INSERT') {
                cli_err_syntax_without_exit("[cli_parse_condition_clause_sql]: Subqueries are NOT allowed for Query Type `$queryType`!");
                cli_err_syntax("Subquery Syntax is ONLY used for UPDATE, DELETE, SELECT or SELECT_DISTINCT Queries!");
            }
            if (!isset($validCols['subqueries'][$wPart])) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$wPart` not being found in the SubQueries Array!");
            }
            $parsedCondition .= $wPart . " ";
            cli_info_without_exit("[cli_parse_condition_clause_sql]: Found SINGLE AND ONLY A Subquery Syntax: \"$wPart\" in Query Type: \"$queryType\". This is handled outside of this Parsing Process. Continuing...");
            continue;
        }

        // Now we finally match the $wPart against the regex to parse it
        $wMatch = preg_match($wPartRegex, trim($wPart), $wMatches);
        if (!$wMatch) {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to no match at all!");
            cli_info("This might be due to a missing/invalid operator. Valid Operators:\n" . implode(",\n", quotify_elements($mysqlOperatorSyntax['all'])) . "!");
        }
        if ($wMatches[1] === null || $wMatches[2] === null || $wMatches[3] === null) {
            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to one or more parts being null (Table with Column Name or Table Column Name, Operator, and/or Value)!");
        }
        $mCol = $wMatches[1] ?? null;
        $mOperator = $wMatches[2] ?? null;
        $mValue = $wMatches[3] ?? null;

        // None of the matched parts in the Condition Clause can be Aggregate Function
        // for "WHERE" Key since Aggregate Functions are not allowed in the WHERE clause!
        if ($whereOrHaving === 'WHERE') {
            if (
                (preg_match($aggregateFunctionsStart, $mCol)
                    || preg_match($aggregateFunctionsStart, $mOperator)
                    || preg_match($aggregateFunctionsStart, $mValue))
            ) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to using an Aggregate Function in the WHERE clause!");
                cli_info_without_exit("Aggregate Functions like COUNT(), SUM(), AVG(), MIN(), MAX() are NOT allowed in the Condition clause!");
                cli_info("If you want to use Aggregate Functions, use them in the SELECT and/or GROUP BY Clauses instead!");
            }
        }

        // Check $mCol begins with any of the special syntax starts. If so we
        // extract that and the $mCol separated by the special syntax start.
        $specialSyntax = "";
        foreach ($specialSyntaxStart as $specialStart) {
            if (str_starts_with($mCol, $specialStart)) {
                [$specialSyntax, $mCol] = explode("=", $mCol, 2);
                if (str_contains($specialSyntax, "(")) {
                    $leftParenthesisCount++;
                }
                break;
            }
        }

        //var_dump($mCol, $mOperator, $mValue, $specialSyntax);

        // SPECIAL CASE: 'HAVING' Key called this Condition Clause Parser, so we
        // will check if it is like when being used in `SELECT Key with Agg Funcs
        // and all that!
        if ($whereOrHaving === 'HAVING') {
            if (preg_match($aggregateFunctionsStart, $mCol)) {
                if (preg_match($aggFuncRegex, $mCol, $aggMatches)) {
                    // There are 4 different cases now to handle in following order to add the parsed Condition Clause String:
                    // 1. Aggregate Function with Table and/or Column Name (table:col)
                    // 2. Aggregate Function with just table_col Alias Name
                    // 3. Aggregate Function with just agg_func Alias Name (agg_func_table_col unless count(*), then count_table)
                    // 4. Aggregate Function with just Column Name (col)
                    $aggName = strtolower($aggMatches[1]) ?? null;
                    $aggTbAndOrCol = strtolower($aggMatches[2]) ?? null;
                    $aggAliasOrTbDotCol = true; // True = it is a Agg Func Alias Name, False = it is Table:Col after Col => Table:Col or already Table:Col
                    var_dump($aggName, $aggTbAndOrCol);
                    // If the Aggregate Function is not in the valid starts
                    // array then it is not a valid Aggregate Function!
                    if (!isset($aggFuncValidStarts[$aggName])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Aggregate Function: \"$mCol\" in Query Type: \"$queryType\" due to not being a valid Aggregate Function!");
                        cli_info("Valid Aggregate Functions start with:\n" . strtoupper(implode(",\n", quotify_elements(array_keys($aggFuncValidStarts)))) . "!");
                    }
                    // If it contains ":" we assume "table:col" so we just replace ":" with "_"
                    // and then check if it is a valid table:col or alias name!
                    if (str_contains($aggTbAndOrCol, ":")) {
                        if (!in_array($aggTbAndOrCol, $validCols['table:col'], true)) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table:Col Name: `$aggTbAndOrCol` in the Table and Column Array!");
                            cli_info("Valid Table and Column Array is:\n" . implode(",\n", quotify_elements($validCols['table:col'])) . "!");
                        }
                        $aggTbAndOrCol = str_replace(":", "_", $aggTbAndOrCol);
                        if (!isset($aliasesTbCol)) {
                            $aliasesTbCol[$aggTbAndOrCol] = [
                                'tb' => explode("_", $aggTbAndOrCol)[0] ?? "<UNKNOWN_TABLE>",
                                'col' => explode("_", $aggTbAndOrCol)[1] ?? "<UNKNOWN_COLUMN>",
                            ];
                        }
                        $aggAliasOrTbDotCol = false;
                    }
                    // If it starts with any of the valid starts "avg_,"sum_", "min_", "max_", etc.
                    elseif (preg_match($aggFuncAliasValidStartRegex, $aggTbAndOrCol)) {
                        // No need to do anything here, we check valid Aggregate Function Alias Names later down!
                    }
                    // Else we assume it is a column
                    else {
                        if (isset($aggFuncAliasValidStarts)) {
                        } elseif (!in_array($aggTbAndOrCol, $validCols['uniqueCols'], true)) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Column Name: `$aggTbAndOrCol` in the Unique Columns Array!");
                            cli_info("Valid Unique Columns Array is:\n" . implode(",\n", quotify_elements($validCols['uniqueCols'])) . "! (`$aggTbAndOrCol` not being here means it could be ambigious to match Column to Table)");
                        }
                        $findTbToCol = cli_find_valid_tb_col_and_binding_or_return_null($tbs, $aggTbAndOrCol);
                        if (!$findTbToCol['found']) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table|Table_Col|Alias Name: `$aggTbAndOrCol` in the Aliases Table & Column Array!");
                            cli_info("Valid Aliases Array is:\n" . implode(",\n", quotify_elements(array_keys($aliasesTbCol))) . "!");
                        } else {
                            $aggTbAndOrCol = $findTbToCol['found_table'] . "_" . $findTbToCol['found_col'];
                            if (!isset($aliasesTbCol)) {
                                $aliasesTbCol[$aggTbAndOrCol] = [
                                    'tb' => $findTbToCol['found_table'],
                                    'col' => $findTbToCol['found_col'],
                                ];
                            }
                            $aggAliasOrTbDotCol = false;
                        }
                    }
                    // Validate its Table and Column exist in the global Aliases Table and Column Array
                    // (it includes both regular aliases and aggregate function aliases!)
                    if (!isset($aliasesTbCol[$aggTbAndOrCol])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table|Table_Col|Alias Name: `$aggTbAndOrCol` in the Aliases Table & Column Array!");
                        cli_info("Valid Aliases Array ais:\n" . implode(",\n", quotify_elements(array_keys($aliasesTbCol))) . "!");
                    }
                    // Aggregate Function is SUM or AVG meaning we need to check that $mValue is numerical OR ?
                    if (str_starts_with($aggName, "avg(") || str_starts_with($aggName, "sum(")) {
                        if (!is_numeric($mValue) && $mValue !== '?') {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" expecting a Numerical Value or '?' but got: \"$mValue\" for `$aggTbAndOrCol`!");
                            cli_info("Either Change the Value to a Numerical Value or '?' or change the Aggregate Function to something else that does not require a Numerical Value!");
                        }
                    }
                    // Validate that $mOperator is a valid operator
                    if (!in_array($mOperator, $mysqlOperatorSyntax['all_except_worded'])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" expecting a valid Operator but got: \"$mOperator\"!");
                        cli_info("Valid Operators are:\n" . implode(",\n", quotify_elements($mysqlOperatorSyntax['all'])) . "! (excluding the Worded Operators in the HAVING Key case!)");
                    }
                    $binding = cli_find_valid_tb_col_and_binding_or_return_null($tbs, ($aliasesTbCol[$aggTbAndOrCol]['tb'] ?? "<UNKNOWN_TABLE>") . ":" . ($aliasesTbCol[$aggTbAndOrCol]['col'] ?? "<UNKNOWN_COLUMN>"));

                    if (!$binding['found']) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a valid Table|Table_Col|Alias Name: `$aggTbAndOrCol` in the Aliases Table & Column Array!");
                        cli_info("Valid Aliases Array is:\n" . implode(",\n", quotify_elements(array_keys($aliasesTbCol))) . "!");
                    }
                    // No binding found when it should though
                    if (!isset($binding['binding'])) {
                        cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a Valid Binding Type for Value: `$mValue` (binding type is missing/null)!");
                        cli_info("Valid Binding Types are:\n" . implode(",\n", quotify_elements(['i', 'd', 's', 'b'])) . "!");
                    }

                    // If TableCol's binding is numeric and its value is then we know it is valid binding type with value!
                    if (($binding['binding'] === 'i' || $binding['binding'] === 'd') && is_numeric($mValue)) {
                        // Empty so "else" does not execeute!
                    }
                    // Otherwise we check! (always returns true when $mValue is '?')
                    else {
                        $validValueBinding = cli_validate_correct_binding_with_provided_value($binding['binding'], $mValue);
                        if (!$validValueBinding) {
                            cli_err_without_exit("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Aggregate Function: \"$aggName\" not having a Valid Binding Type for Value: `$mValue`!");
                            cli_info("`$mValue` might be Blob or String when it should be Numeric or vice versa. Valid Binding Types are:\n" . implode(",\n", quotify_elements(['i', 'd', 's', 'b'])) . "!");
                        }
                    }
                    // Finally we can add to the parsed condition for HAVING Key!
                    // and additional ? placeholder to `fields` if applying!
                    if (!empty($specialSyntax)) {
                        if ($parsedCondition !== ' ' && str_contains($specialSyntax, "(")) {
                            $parsedCondition .= " ";
                        }
                        $parsedCondition .= " " . $specialSyntax;
                    }
                    $aggName = strtoupper($aggName);

                    // True = it is a Agg Func Alias Name, False = it is Table:Col after Col => Table:Col or already Table:Col
                    if (!$aggAliasOrTbDotCol) {
                        // We preg_replace ONLY the first "_" with "." to make it table.col
                        $aggTbAndOrCol = preg_replace('/_/', '.', $aggTbAndOrCol, 1);
                    }

                    // Add parsed condition for HAVING Key
                    $parsedCondition .= " $aggName$aggTbAndOrCol) $mOperator $mValue ";

                    $mCol = $binding['found_col'] ?? null;
                    $singleTb = $binding['found_table'] ?? null;
                    if (!isset($singleTb) || !isset($mCol)) {
                        cli_err("[cli_parse_condition_clause_sql - on HAVING Key]: Huh? Added Parsed HAVING Condition but could then not add to `fields` Key in return() Array?! Due to `cli_find_valid_tb_col_and_binding_or_return_null` returning null for Table or Column Name!?");
                    }

                    // Add to `builtFieldsArray` if it is not already there when ? Placeholder since that means
                    // we need to grab that value from some validated input field (as defined in `fields` Key!)
                    $singleTb = $singleTb . "_" . $aggFuncValidStarts[strtolower($aggName)];
                    // Remove trailing "_" if it exists in $singleTb now!
                    if (str_ends_with($singleTb, "_")) {
                        $singleTb = substr($singleTb, 0, -1);
                    }
                    if ($mValue === '?') {
                        if (isset($builtFieldsArray)) {
                            if (isset($validCols["matchedFields"][$mCol])) {
                                if (is_string($validCols["matchedFields"][$mCol]) && !empty($validCols["matchedFields"][$mCol])) {
                                    $mCol = $validCols["matchedFields"][$mCol];
                                }
                            }

                            if (!in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                                $builtFieldsArray[] = $singleTb . "_" . $mCol;
                            } elseif (in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                                // We iterate adding +1 to the name until it is unique
                                $i = 1;
                                while (in_array($singleTb . "_" . $mCol . "_$i", $builtFieldsArray)) {
                                    $i++;
                                }
                                $builtFieldsArray[] = $singleTb . "_" . $mCol . "_$i";
                            }
                        }
                    }

                    // Continue to next array element of the Condition Clause
                    continue;
                } else {
                    cli_err("[cli_parse_condition_clause_sql - on HAVING Key]: Invalid Aggregate Function: \"$mCol\" in Query Type: \"$queryType\" due to not being a valid Aggregate Function!");
                }
            }
        }

        // Check $mCol is either in $uniqueCols or in $tbsWithCols
        // after first checking if it is just a [Subquery]
        if (str_starts_with($mCol, '[') && str_ends_with($mCol, ']')) {
            if ($queryType === 'INSERT') {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
            }
            if (!isset($validCols['subqueries'][$mCol])) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mCol` not being found in the SubQueries Array!");
            }
            $parsedCondition .= " $mCol ";
            cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mCol) in \"$wPart\" in Query Type: \"$queryType\" where Table:Column otherwise would be. This is replaced later. Continuing...");
        } elseif (str_starts_with($mCol, "(") && str_ends_with($mCol, ")")) {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$mCol\" in Query Type: \"$queryType\" due to using parenthesis '(' or ')' outside it!");
            cli_info_without_exit("Using () to indicate a Tuple or Row Constructor is not supported as of yet in FunkPHP!");
            cli_info_without_exit("If you wanna use a [SubQuery] means you should start with `[` and end with `]`. This allows you to use Tuples, Row Constructors and such.");
            cli_info("IMPORTANT: Using [SubQuery] means you lose the Validation Parsing Logic and you must add the `?` Placeholders  in the `bparam` Key and the <MATCHED_FIELDS> keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
        }
        // For 'WHERE' Key
        if ($whereOrHaving === 'WHERE') {
            if (
                !str_contains($mCol, ":") && !in_array($mCol, $uniqueCols, true)
            ) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to `$mCol` not being found in the Unique Columns (col1,col2,etc) Array!");
                cli_info_without_exit("When not found in Unique Columns Array, it becomes ambigious which Table it belongs to!");
                cli_info_without_exit("Valid Unique Columns Array is:\n" . implode(",\n", quotify_elements($uniqueCols)) . "!");
                cli_info("If you are only using on Table suddenly for your SQL Query, change your `<TABLES>` Key also to only have that one Table OR Write `correctTable:$mCol` exactly in the Condition Clause!");
            } elseif (
                str_contains($mCol, ":") &&
                !in_array($mCol, $tbsWithCols, true)
            ) {
                cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to `$mCol` not being found in the Table with Columns (table1:col1,table1:col2,etc2:etc1) Array!");
                cli_info_without_exit("Valid Table with Columns Array is:\n" . implode(",\n", quotify_elements($tbsWithCols)) . "!");
                cli_info("If you are only using on Table suddenly for your SQL Query, change your `<TABLES>` Key also to only have that one Table OR Write `correctTable:$mCol` exactly in the Condition Clause!");
            }
        }

        // Check that $mOperator is a valid operator after first checking if it is a [Subquery]
        if (str_starts_with($mOperator, '[') && str_ends_with($mOperator, ']')) {
            if ($queryType === 'INSERT') {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
            }
            if (!isset($validCols['subqueries'][$mOperator])) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mOperator` not being found in the SubQueries Array!");
            }
            $parsedCondition .= " $mOperator ";
            cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mOperator) in \"$wPart\" in Query Type: \"$queryType\" where Operator otherwise would be. This is replaced later. Continuing...");
        } elseif (str_starts_with($mOperator, "(") && str_ends_with($mOperator, ")")) {
            cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$mOperator\" in Query Type: \"$queryType\" due to using parenthesis '(' or ')' outside it!");
            cli_info_without_exit("Using () to indicate a Tuple or Row Constructor is not supported as of yet in FunkPHP!");
            cli_info_without_exit("If you wanna use a [SubQuery] means you should start with `[` and end with `]`. This allows you to use Tuples, Row Constructors and such.");
            cli_info("IMPORTANT: Using [SubQuery] means you lose the Validation Parsing Logic and you must add the `?` Placeholders  in the `bparam` Key and the <MATCHED_FIELDS> keys in the `fields` Key manually after successfully compilation! (in current version of FunkPHP)");
        } elseif (!in_array($mOperator, $mysqlOperatorSyntax['all'], true)) {
            cli_err("[cli_parse_where_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to operator `$mOperator` not being a valid MySQL Operator!");
        }

        // Special Case where $mOperator is NOT "=" meaning it could affect more than
        // one row table so we warn the Developer about it but still allow it. It applies
        // to the Query Types DELETE and UPDATE where it could cause issues if not careful!
        if (
            $mOperator !== '=' && ($queryType === 'DELETE' || $queryType === 'UPDATE')
            && (!str_starts_with($mOperator, '[') && !str_ends_with($mOperator, ']'))
        ) {
            cli_warning_without_exit("[cli_parse_condition_clause_sql]: Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" has an Operator that is NOT `=`!");
            cli_info_without_exit("This could lead to affecting more Table Rows than desired unless you really want that!");
        }

        // Now we start adding to the $parsedCondition string. First we check if "$specialSyntax" is not empty
        // meaning we should add that before the column name/tableName:columnName Operator Value parts!
        // not work correctly now due to just being added despite ")" should be at the end of the WHERE clause!
        if (!empty($specialSyntax)) {
            if ($parsedCondition !== ' ' && str_contains($specialSyntax, "(")) {
                $parsedCondition .= " ";
            }
            $parsedCondition .= " " . $specialSyntax;
        }

        // There are two cases now: Either we have a Single Table to
        // check against and add based on or we have several ones.
        // SINGLE TABLE CASE:
        if ($singleTable) {
            // $mValue contains [SubQuery] meaning it starts with "[" and ends with "]"
            if (str_ends_with($mValue, "]") && str_starts_with($mValue, "[")) {
                // We check if the $mValue is a valid SubQuery and if it is, we add it to the $parsedCondition
                // string as is. It is handled outside of this Parsing Process.
                if ($queryType === 'INSERT') {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
                }
                if (!isset($validCols['subqueries'][$mValue])) {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mValue` not being found in the SubQueries Array!");
                }
                $parsedCondition .= " $mValue ";
                cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mValue) in \"$wPart\" in Query Type: \"$queryType\" where a Value otherwise would be. This is replaced later. Continuing...");
                continue;
            }

            // When $mValue is NOT a [SubQuery]
            $singleTb = $tbs[0] ?? null;
            if (!is_string_and_not_empty($singleTb)) {
                cli_err("[cli_parse_where_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Single Table not being a valid string!");
            }

            // If $mCol contains a ":", we know it is a tableName:columnName
            // so we can extract the table name and column name from it. But
            // we also make sure it exists in both unique columns and table with columns arrays!
            // When $singleTable case, it should ALWAYS exist in both arrays!
            if (str_contains($mCol, ":")) {
                if (!in_array($mCol, $tbsWithCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Array of `Table:Column`!");
                    cli_info("A Table might be missing from `<TABLES>` Key OR it has too many Tables if you SUDDENLY changed to Only Query One! Available Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
                [$singleTb, $mCol] = explode(":", $mCol, 2);
                if (!in_array($mCol, $uniqueCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Unique Columns Array!");
                    cli_info("A Table might be missing from `<TABLES>` Key OR it has too many Tables if you SUDDENLY changed to Only Query One! Available Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
            }

            // Then we try find correct binding type for the column in the table
            $correctBinding = $allTbs[$singleTb][$mCol]['binding'] ?? null;
            $expectedBinding = "";
            if (!$correctBinding) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` in table `$singleTb` NOT having a Valid Binding Type!");
            }
            if ($correctBinding === 's') {
                $expectedBinding = "String";
            } elseif ($correctBinding === 'i') {
                $expectedBinding = "Integer";
            } elseif ($correctBinding === 'd') {
                $expectedBinding = "Double";
            } elseif ($correctBinding === 'b') {
                $expectedBinding = "Blob";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Column `$mCol` in Table `$singleTb` having an Invalid Binding Type!");
            }

            // Extract column from $mCol IF it contains a ":" and then add it
            // with the already validated operator to the $parsedCondition string.
            $mCol = str_contains($mCol, ":") ? explode(":", $mCol, 2)[1] : $mCol;
            $parsedCondition .= " $singleTb.$mCol $mOperator ";

            // Now we need to check if the $mValue is a valid type whether it is a
            // a placeholder ? or an actual value. If it is a placeholder, we
            // add it to the $builtBindedParamsString and if it is a value, we
            // add it to the $parsedCondition string with proper escaping.
            if ($mValue === '?') {
                // If it is a placeholder, we add it to the $builtBindedParamsString
                $builtBindedParamsString .= $correctBinding;
                $parsedCondition .= "? ";

                // We also add the field to the builtFieldsArray based on either any provided
                // <MATCHED_FIELDS> unique field name or just using default tableName_ColumnName
                if (isset($builtFieldsArray)) {
                    if (isset($validCols["matchedFields"][$mCol])) {
                        if (is_string($validCols["matchedFields"][$mCol]) && !empty($validCols["matchedFields"][$mCol])) {
                            $mCol = $validCols["matchedFields"][$mCol];
                        }
                    }
                    if (!in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                        $builtFieldsArray[] = $singleTb . "_" . $mCol;
                    } elseif (in_array($singleTb . "_" . $mCol, $builtFieldsArray)) {
                        // We iterate adding +1 to the name until it is unique
                        $i = 1;
                        while (in_array($singleTb . "_" . $mCol . "_$i", $builtFieldsArray)) {
                            $i++;
                        }
                        $builtFieldsArray[] = $singleTb . "_" . $mCol . "_$i";
                    }
                }
            }
            // It is a hardcoded provided Value
            elseif (is_string($mValue) || is_numeric($mValue)) {
                if (is_numeric($mValue)) {
                    // It expects an Integer so we regex to validate it is ONLY integers without any decimals
                    if ($correctBinding === 'i') {
                        if (!preg_match('/^-?\d+$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$singleTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects a Double so we regex to validate it is a valid double with optional decimals
                    elseif (($correctBinding === 'd')) {
                        if (!preg_match('/^-?\d+(\.\d+)?$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$singleTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects either a String or Blob so we know the provided numeric value is invalid
                    elseif (($correctBinding === 'b') || ($correctBinding === 's')) {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$singleTb` expects `$expectedBinding`!");
                    }
                } elseif (is_string($mValue)) {
                    if ($correctBinding !== 's' && $correctBinding !== 'b') {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value `$mValue`. Column `$mCol` in Table `$singleTb` does NOT expect a String but `$expectedBinding`!");
                    }
                    $mValue = "'" . $mValue . "'";
                }
                //$builtBindedParamsString .= $correctBinding; ?? Needed when it is hardcoded value?
                $parsedCondition .= "$mValue ";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to the provided value NOT being a Valid String/Blob or Numeric Value!");
            }
        }

        // ? IMPORTANT: This might not work as intended with multiple tables yet, despite being pretty much
        // the same as the single table case. It differs mainly from having to find the correct Table first
        // based on the $mCol which might or might not already include the table name. It might include just a
        // unique column meaning we must find it manually in the $allTbs array!
        // MULTIPLE TABLES CASE:
        else {
            // $mValue contains [SubQuery] meaning it starts with "[" and ends with "]"
            if (str_ends_with($mValue, "]") && str_starts_with($mValue, "[")) {
                // We check if the $mValue is a valid SubQuery and if it is, we add it to the $parsedCondition
                // string as is. It is handled outside of this Parsing Process.
                if ($queryType === 'INSERT') {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQueries not being allowed in INSERT Queries!");
                }
                if (!isset($validCols['subqueries'][$mValue])) {
                    cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to SubQuery `$mValue` not being found in the SubQueries Array!");
                }
                $parsedCondition .= " $mOperator $mValue ";
                cli_info_without_exit("[cli_parse_condition_clause_sql]: Found a Subquery Syntax ($mValue) in \"$wPart\" in Query Type: \"$queryType\" where a Value otherwise would be. This is replaced later. Continuing...");
                continue;
            }
            // We need to find the correct Table for the $mCol when ":" is missing
            // because we now have multiple tables and the $mCol might just be
            // a unique column name without the table name giving us no table!
            $correctTb = null;

            // ":" missing, so find correct Table manually
            if (!str_contains($mCol, ":")) {
                // Before we start looping through the tables, we check if the $mCol is in the unique columns
                // because if it is not, then we can never know if we match a correct table since it could
                // multiple tables have the same column name!
                if (!in_array($mCol, $uniqueCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Unique Columns Array!");
                    cli_info("Column `$mCol` might be in multiple tables at once making it impossible to find the correct one. Please use `table:column$mCol` syntax to specify the Table explicitly for this Column!");
                }
                // We only loop through tables that are actually in the $tbs array
                foreach ($allTbs as $tb => $colKey) {
                    if (!in_array($tb, $tbs, true)) {
                        continue;
                    }
                    foreach ($colKey as $k => $col) {
                        if ($mCol === $k) {
                            // We found the correct Table for the $mCol
                            $correctTb = $tb;
                            break 2; // Break out of both foreach loops
                        }
                    }
                }
                // If we did not find the correct Table, we error out
                if (!$correctTb) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Column `$mCol` not being found in any of the Tables!");
                    cli_info("A Table might be missing from `tables.php` File. Checked Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
            }
            // We can just extract the correct Table from the $mCol when it contains a ":"
            else {
                if (!in_array($mCol, $tbsWithCols, true)) {
                    cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` not being found in the Array of `Table:Column`!");
                    cli_info("A Table might be missing from `<TABLES>` Key OR it has too many Tables if you SUDDENLY changed to Only Query One! Available Tables:\n" . implode(",\n", quotify_elements($tbs)) . "!");
                }
                [$correctTb, $mCol] = explode(":", $mCol, 2) ?? null;
            }

            // Now we finally process the $correctTb and $mCol as usual (just like with the single table case)
            if (!is_string_and_not_empty($correctTb)) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to correct Table NOT being a Valid String!");
            }
            $correctBinding = $allTbs[$correctTb][$mCol]['binding'] ?? null;
            $expectedBinding = "";
            if (!$correctBinding) {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to column `$mCol` in table `$correctTb` NOT having a Valid Binding Type. It was not found in the `tables.php` File!");
            }
            if ($correctBinding === 's') {
                $expectedBinding = "String";
            } elseif ($correctBinding === 'i') {
                $expectedBinding = "Integer";
            } elseif ($correctBinding === 'd') {
                $expectedBinding = "Double";
            } elseif ($correctBinding === 'b') {
                $expectedBinding = "Blob";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to Column `$mCol` in Table `$correctTb` having an Invalid Binding Type!");
            }

            // Extract column from $mCol IF it contains a ":" and then add it
            // with the already validated operator to the $parsedCondition string.
            $mCol = str_contains($mCol, ":") ? explode(":", $mCol, 2)[1] : $mCol;
            $parsedCondition .= " $correctTb.$mCol $mOperator ";

            // Now we need to check if the $mValue is a valid type whether it is a
            // a placeholder ? or an actual value. If it is a placeholder, we
            // add it to the $builtBindedParamsString and if it is a value, we
            // add it to the $parsedCondition string with proper escaping.
            if ($mValue === '?') {
                // If it is a placeholder, we add it to the $builtBindedParamsString
                $builtBindedParamsString .= $correctBinding;
                $parsedCondition .= "? ";

                // We also add the field to the builtFieldsArray based on either any provided
                // <MATCHED_FIELDS> unique field name or just using default tableName_ColumnName
                if (isset($builtFieldsArray)) {
                    if (isset($validCols["matchedFields"][$mCol])) {
                        if (is_string($validCols["matchedFields"][$mCol]) && !empty($validCols["matchedFields"][$mCol])) {
                            $mCol = $validCols["matchedFields"][$mCol];
                        }
                    }
                    if (!in_array($correctTb . "_" . $mCol, $builtFieldsArray)) {
                        $builtFieldsArray[] = $correctTb . "_" . $mCol;
                    } elseif (in_array($correctTb . "_" . $mCol, $builtFieldsArray)) {
                        // We iterate adding +1 to the name until it is unique
                        $i = 1;
                        while (in_array($correctTb . "_" . $mCol . "_$i", $builtFieldsArray)) {
                            $i++;
                        }
                        $builtFieldsArray[] = $correctTb . "_" . $mCol . "_$i";
                    }
                }
            }
            // It is a hardcoded provided Value
            elseif (is_string($mValue) || is_numeric($mValue)) {
                if (is_numeric($mValue)) {
                    // It expects an Integer so we regex to validate it is ONLY integers without any decimals
                    if ($correctBinding === 'i') {
                        if (!preg_match('/^-?\d+$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$correctTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects a Double so we regex to validate it is a valid double with optional decimals
                    elseif (($correctBinding === 'd')) {
                        if (!preg_match('/^-?\d+(\.\d+)?$/', $mValue)) {
                            cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$correctTb` expects `$expectedBinding`!");
                        }
                    }
                    // It expects either a String or Blob so we know the provided numeric value is invalid
                    elseif (($correctBinding === 'b') || ($correctBinding === 's')) {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value provided. Column `$mCol` in Table `$correctTb` expects `$expectedBinding`!");
                    }
                } elseif (is_string($mValue)) {
                    if ($correctBinding !== 's' && $correctBinding !== 'b') {
                        cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to value `$mValue`. Column `$mCol` in Table `$correctTb` does NOT expect a String but `$expectedBinding`!");
                    }
                    $mValue = "'" . $mValue . "'";
                }
                $parsedCondition .= "$mValue ";
            } else {
                cli_err("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to provided value NOT being a Valid String/Blob or Numeric Value!");
            }
        }
    } // END OF LOOP THROUGH EACH CONDITION CLAUSE PART

    // If not even amount of opening and closing () then
    // we err out and tell the Developer to fix it! ^_^
    if ($leftParenthesisCount !== $rightParenthesisCount) {
        cli_err_without_exit("[cli_parse_condition_clause_sql]: Invalid Condition Clause Part: \"$wPart\" in Query Type: \"$queryType\" due to unbalanced parentheses!");
        cli_info_without_exit("Match the number of left parentheses `(` with the number of right parentheses `)`!");
        cli_info("You do this by adding `|)` at the end of the Condition Clause or where necessary to get the Priority Order as needed!");
    }

    // Add last closing parenthesis if we had any opening parentheses?
    if ($leftParenthesisCount === $rightParenthesisCount && $leftParenthesisCount > 0) {
        $parsedCondition = rtrim($parsedCondition);
    }

    // FINALLY RETURN THE PARSED Condition Clause!
    $parsedCondition = rtrim($parsedCondition);
    return $parsedCondition;
}

// Function that uses a DFS Algo to parse all possible JOINS_ON using
// the $fromTb as the starting point. It returns an array of possible JOINS!
function cli_parse_joins_on_DFS($fromTb, $availableTableNames, $relationships)
{
    $visitedTables = [];
    $suggestedJoins = [];
    $processedJoinPairs = [];
    $stack = [$fromTb];

    // Starting Table does not exist in the relationships or has no relationships
    if (!isset($relationships[$fromTb]) || empty($relationships[$fromTb])) {
        return [];
    }

    // Iterate while there are still Tables to process in the stack
    // (tables with possible relationships to process)
    while (!empty($stack)) {
        // Grab the last Table from the stack
        $currentTable = array_pop($stack);

        // Skip already visited Tables to avoid cycles
        if (in_array($currentTable, $visitedTables)) {
            continue;
        }
        // Now it means we are visiting this one so mark it as visited
        $visitedTables[] = $currentTable;

        // When current Table has no relationships, continue
        if (
            !isset($relationships[$currentTable])
            || !is_array($relationships[$currentTable])
            || empty($relationships[$currentTable])
        ) {
            continue;
        }

        // Iterate through each related Table and its relationship details
        foreach ($relationships[$currentTable] as $relatedTable => $relationshipDetails) {
            // Skip those already visited to avoid cycles
            if (!in_array($relatedTable, $availableTableNames)) {
                continue;
            }

            // Prepare and grab details for the (INNER) JOIN
            // $relatedTable is Table related with $currentTable
            $joinString = "'inner=$relatedTable,";
            $table1 = $relationshipDetails['local_table'];
            $col1 = $relationshipDetails['local_column'];
            $table2 = $relationshipDetails['foreign_table'];
            $col2 = $relationshipDetails['foreign_column'];

            // We Pair, Sort & Create a Unique Identifier for the JOIN
            // so we avoid duplicates in the JOINs Array!
            $sortedPair = [$table1, $table2];
            sort($sortedPair);
            $joinIdentifier = implode('-', $sortedPair);

            // Here we check if the JOIN Identifier is already processed
            // to avoid duplicates in the suggested JOINs Array!
            // So, continue to next if that is the case!
            if (in_array($joinIdentifier, $processedJoinPairs)) {
                continue;
            }

            // Finalize the JOIN String and add it to the suggested JOINs Array
            // and the processed JOIN Pairs Array to avoid duplicates!
            $joinString .= "$table1($col1),$table2($col2)'";
            $suggestedJoins[] = $joinString;
            $processedJoinPairs[] = $joinIdentifier;

            // Add the related Table to the stack for further processing if it has not
            // been fully visited yet! Each Table can have multiple relationships!
            if (!in_array($relatedTable, $visitedTables)) {
                $stack[] = $relatedTable;
            }
        }
    }
    // Return any possible JOINs based on the relationships starting from the $fromTb
    return $suggestedJoins;
}

// Function takes a string of joined tables (like "table1=>table2=>table3" or
// maybe just "table1=>table2") and parses its relationship from left to right
// and adds it to the current provided $currentFinalHydrateKey which is the
// "return array('hydrate' => 'key' => array())" for Hydration purposes.
// $keepGoing is a reference to a boolean that indicates whether to keep going
// or not during the Hydration Compilation Process that starts with the following:
// `$hydratedKey = null;\n if (isset($hydrationKey))){...}`
function cli_parse_joined_tables_order($tablesString, &$currentFinalHydrateKey, &$keepGoing, $selectedCols)
{
    global $tablesAndRelationshipsFile;
    $tables = $tablesAndRelationshipsFile['tables'] ?? [];
    $relationships = $tablesAndRelationshipsFile['relationships'] ?? [];

    // Validate the input parameters before anything else!
    if (!isset($relationships) || !is_array($relationships) || empty($relationships)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: No Relationships found in the `tablesAndRelationships.php` File!");
        cli_info_without_exit("Please ensure that the file is properly Configured with Relationships between Tables.");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_string_and_not_empty($tablesString) || !preg_match('/^([a-zA-Z0-9_]+)((=>){1}([a-zA-Z0-9_]+)(\(via:[a-zA-Z0-9_]+\))*)*$/i', $tablesString)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Non-Empty String as input for `\$tablesString`!");
        cli_info_without_exit("This probably means that the `tablesString` is NOT a String or that it IS Empty.");
        cli_info_without_exit("The Hydration Key must be a Non-Empty String representing the Hydration Key in the Format:\n`table` to Hydrate a Single Table OR\n`table=>table2` to Hydrate Two(2) Tables based on valid JOINING between them OR\n`table=>table2=>table3` to Hydrate Three(3) Tables based on valid JOINING between them\n(and so on to hydrate multiple joined tables)");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_array($currentFinalHydrateKey)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Non-Empty Array as input for `\$currentFinalHydrateKey`!");
        cli_info_without_exit("This probably means that the `currentFinalHydrateKey` is NOT an Array. (It CAN be Empty!)");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_array_and_not_empty($selectedCols)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Non-Empty Array as input for `\$selectedCols`!");
        cli_info_without_exit("This probably means that the `selectedCols` is NOT an Array or that it IS Empty.");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    if (!is_bool($keepGoing)) {
        $keepGoing = false;
        cli_warning_without_exit("[cli_parse_joined_tables_order]: Expects a Boolean as input for `\$keepGoing`!");
        cli_info_without_exit("This probably means that the `keepGoing` is NOT a boolean or that it is not set.\nIMPORTANT: It has now been set to false which probably means the remaining code for Hydration Compilation will NOT be executed.");
        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
        return;
    }
    // Turn into an array by either splitting by "=>" or just using it as a single array element
    $tablesString = str_contains($tablesString, "=>") ? explode("=>", $tablesString) : [$tablesString];

    // Single Table Case: It is NOT needed to have a relationship but the tables must exists of course!
    if (count($tablesString) === 1) {
        if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $tablesString[0], $matches)) {
            $tb1 = $matches[1];
            $viaTb = $matches[3];
        }
        if (!isset($tables[$tablesString[0]]) || !is_array($tables[$tablesString[0]]) || empty($tables[$tablesString[0]])) {
            $keepGoing = false;
            cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tablesString[0]}` in the `tables.php` File!");
            cli_info_without_exit("Verify that the `{$tablesString[0]}` Table exists in the `tables` Key in the `tables.php` File!.");
            cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
            return;
        }
        if (!isset($currentFinalHydrateKey[$tablesString[0]])) {
            // Remove `id` since we already have it as the Primary Key ('pk')
            unset($selectedCols[$tablesString[0]][$tablesString[0] . "_id"]);
            $currentFinalHydrateKey[$tablesString[0]] = [
                'pk' => $tablesString[0] . "_id",
                'cols' => array_keys($selectedCols[$tablesString[0]]),
                'with' => [],
            ];
        }
    }
    // Multiple Tables Case: For each table in the string we will add a table and then check the relationship
    // from previous table since the idea is that you should chain correctly ("table1=>table2=>table3") so we
    // only need to check relationships from the previous table to the next one. And if a table already exists
    // we adjust the position for the $currentFinalHydrateKey[$table] so we do not accidentally overwrite it!
    elseif (count($tablesString) > 1) {
        $maxIdx = count($tablesString) - 1; // Maximum Index of the Tables String
        $prevTB = ""; // Previous Table to use for the next table
        $prevPK = ""; // Previous Primary Key to use for the next table
        $nextFK  = ""; // Next Foreign Key to use for the next table
        $nextLevel = &$currentFinalHydrateKey; // Reference to the current level of the $currentFinalHydrateKey

        foreach ($tablesString as $idx => $tbStr) {
            // SPECIAL CASE: If the Table String contains a "via:" then we need to
            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $tbStr, $matches)) {
                $tb1 = $matches[1];
                $viaTb = $matches[3];

                // Validate all 3 Tables exist
                if (!isset($tables[$prevTB]) || !is_array($tables[$prevTB]) || empty($tables[$prevTB])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$prevTB}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                if (!isset($tables[$tb1]) || !is_array($tables[$tb1]) || empty($tables[$tb1])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tb1}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$tb1}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                if (!isset($tables[$viaTb]) || !is_array($tables[$viaTb]) || empty($tables[$viaTb])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$viaTb}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$viaTb}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                // Check that intermediate Table (viaTb) has a valid relationship with the previous Table (prevTB)
                // since it is the intermediate Table that connects the previous Table (prevTB) with the current Table (tbStr)
                if (!isset($relationships[$prevTB][$viaTb]) || !is_array($relationships[$prevTB][$viaTb]) || empty($relationships[$prevTB][$viaTb])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$viaTb}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$viaTb}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                // Check that intermediate Table (viaTb) has a valid relationship with the current Table (tb1) that is
                // supposed to connect directly to the previous Table (prevTB) via the intermediate Table (viaTb)
                if (
                    !isset($relationships[$viaTb][$tb1])
                    || !is_array($relationships[$viaTb][$tb1])
                    || empty($relationships[$viaTb][$tb1])
                    || !isset($relationships[$tb1][$viaTb])
                    || !is_array($relationships[$tb1][$viaTb])
                    || empty($relationships[$tb1][$viaTb])
                ) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$viaTb}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$viaTb}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }

                // Check that the previous Table (prevTB) has a valid relationship with the current Table (tb1)
                // which is a Many-to-Many relationship via the intermediate Table (viaTb)
                if (!isset($relationships[$prevTB][$tb1]) || !is_array($relationships[$prevTB][$tb1]) || empty($relationships[$prevTB][$tb1])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$tb1}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$tb1}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }

                // Table already exists, so we just adjust the position
                if (isset($nextLevel[$tb1])) {
                    $prevTB = $tb1;
                    $nextLevel = &$nextLevel[$tb1]['with'];
                }
                // Table does not exist, so we create it and since this is larger than 0 index,
                elseif (!isset($nextLevel[$tb1])) {
                    // Validate all necessary relationships keys exist first!
                    if (
                        !isset($relationships[$viaTb][$prevTB]['local_table'])
                        || !isset($relationships[$viaTb][$prevTB]['local_column'])
                        || !isset($relationships[$viaTb][$tb1]['local_table'])
                        || !isset($relationships[$viaTb][$tb1]['local_column'])
                    ) {
                        $keepGoing = false;
                        cli_warning_without_exit("[cli_parse_joined_tables_order]: Necessary Relationships Keys for the Tables `{$viaTb}` and/or `{$prevTB}` are missing in the `relationships` Key in the `tables.php` File!");
                        cli_info_without_exit("Verify that necessary Many-to-Many Relationships for the Tables `{$viaTb}` and/or `{$prevTB}` exist in the `relationships` Key in the `tables.php` File!.");
                        cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                        return;
                    }

                    // Grab the Many-to-Many relationship between the previous Table (prevTB) and the current Table (tb1)
                    // via the intermediate Table (viaTb) to use it for the pivot table and foreign keys.
                    $mmRel = $relationships[$prevTB][$tb1];

                    // We delete PK from the 'cols' array since it is not needed
                    // and we will use the PK as the primary key for the Table.
                    unset($selectedCols[$tb1][$tb1 . "_id"]);
                    $nextLevel[$tb1] = [
                        'pk' => $tb1 . "_id",
                        'fk' => null,
                        'pivot' => [
                            'table' => $mmRel['pivot_table'],
                            'fk_to_parent_pivot_col' => $mmRel['local_table'] . "_" . $mmRel['pivot_local_key'],
                            'fk_to_child_pivot_col' => $mmRel['foreign_table'] . "_" . $mmRel['pivot_foreign_key'],
                        ],
                        'cols' => array_keys($selectedCols[$tb1]),
                        'with' => [],
                    ];
                    // KEEPING THIS FOR FUTURE USE IF NEEDED
                    // IF THE ABOVE VERSION DOES NOT WORK AS INTENDED!
                    // $nextLevel[$tb1] = [
                    //     'pk' => $tb1 . "_id",
                    //     'fk' => null,
                    //     'pivot' => [
                    //         'table' => $viaTb,
                    //         'fk_to_parent_pivot_col' => $relationships[$viaTb][$prevTB]['local_table'] . "_" . $relationships[$viaTb][$prevTB]['local_column'],
                    //         'fk_to_child_pivot_col' => $relationships[$viaTb][$tb1]['local_table'] . "_" . $relationships[$viaTb][$tb1]['local_column'],
                    //     ],
                    //     'cols' => array_keys($selectedCols[$tb1]),
                    //     'with' => [],
                    // ];
                }
                // Continue to next Table level, cause we either fail before we reach this or we succeed
                continue;
            }
            // OTHERWISE CHECK AS NORMAL
            if (!isset($tables[$tbStr]) || !is_array($tables[$tbStr]) || empty($tables[$tbStr])) {
                $keepGoing = false;
                cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tbStr}` in the `tables.php` File!");
                cli_info_without_exit("Verify that the `{$tbStr}` Table exists in the `tables` Key in the `tables.php` File!.");
                cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                return;
            }

            // First Table level (index 0)
            if ($idx === 0) {
                // First Table already exists, so we just adjust the position
                // and store previous Table & Primary Key & next Foreign Key
                if (isset($nextLevel[$tbStr])) {
                    $prevTB = $tbStr;
                    $nextLevel = &$nextLevel[$tbStr]['with'];
                }
                // First Table does not exist, so we create it
                elseif (!isset($nextLevel[$tbStr])) {
                    unset($selectedCols[$tbStr][$tbStr . "_id"]);
                    $nextLevel[$tbStr] = [
                        'pk' => $tbStr . "_id",
                        'cols' => array_keys($selectedCols[$tbStr]),
                        'with' => [],
                    ];
                    // Then we store the previous Table and Primary Key
                    // and readjust the position of the $nextLevel
                    $prevTB = $tbStr;
                    $nextLevel = &$nextLevel[$tbStr]['with'];
                }
            }
            // All other levels of Tables (index > 1)
            elseif ($idx > 0) {
                if (!isset($tables[$tbStr]) || !is_array($tables[$tbStr]) || empty($tables[$tbStr])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Table Name: `{$tbStr}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$tbStr}` Table exists in the `tables` Key in the `tables.php` File!.");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                if (!isset($relationships[$prevTB][$tbStr]) || !is_array($relationships[$prevTB][$tbStr]) || empty($relationships[$prevTB][$tbStr])) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship between Tables: `{$prevTB}` and `{$tbStr}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$tbStr}` Tables have a valid Relationship in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }
                // There are two possible relationships between previous Table and current Table:
                $correctRel = $relationships[$prevTB][$tbStr];
                $correctFK = null;
                // 1. Previous Table is the Primary Key (PK) and current Table is the Foreign Key (FK)
                if ($correctRel['direction'] === 'pk_to_fk') {
                    $correctFK = $correctRel['foreign_table'] . "_" . $correctRel['foreign_column'] ?? null;
                }
                // 2. Previous Table is the Foreign Key (FK) and current Table is the Primary Key (PK)
                elseif ($correctRel['direction'] === 'fk_to_pk') {
                    $correctFK = $correctRel['local_table'] . "_" . $correctRel['local_column'] ?? null;
                }
                if ($correctFK === null) {
                    $keepGoing = false;
                    cli_warning_without_exit("[cli_parse_joined_tables_order]: Invalid Relationship Direction between Tables: `{$prevTB}` and `{$tbStr}` in the `tables.php` File!");
                    cli_info_without_exit("Verify that the `{$prevTB}` and `{$tbStr}` Tables have a valid Relationship Direction in the `relationships` Key in the `tables.php` File!");
                    cli_info_without_exit("IMPORTANT: The Hydration Compilation Will Stop Here - But the SQL String Compiling will continue...!");
                    return;
                }

                // Next Table already exists, so we just adjust the position
                if (isset($nextLevel[$tbStr])) {
                    $prevTB = $tbStr;
                    $nextLevel = &$currentFinalHydrateKey[$tbStr]['with'];
                }
                // Next Table does not exist, so we create it and since this is larger than 0 index,
                // we also add the 'fk' which we know from $nextFK which is the previous table's
                elseif (!isset($nextLevel[$tbStr])) {
                    unset($selectedCols[$tbStr][$tbStr . "_id"]);
                    $nextLevel[$tbStr] = [
                        'fk' => $correctFK,
                        'pk' => $tbStr . "_id",
                        'cols' => array_keys($selectedCols[$tbStr]),
                        'with' => [],
                    ];
                    // Then we store the previous Table and Primary Key
                    // and readjust the position of the $nextLevel
                    $prevTB = $tbStr;
                    $nextLevel = &$nextLevel[$tbStr]['with'];
                }
            }
        }
    }
}

// Compiles a $DX SQL [] to an optmized SQL array that is returned within the same
// function that is used to validate the data. This is used to optimize the SQL process!
// VERY IMPORTANT WARNING: This function calls a function which uses eval() to parse the SQL file!!!
function cli_convert_simple_sql_query_to_optimized_sql($sqlArray, $handlerFile, $fnName)
{
    global $dirs, $exactFiles, $settings, $tablesAndRelationshipsFile;

    // Validate it is an associative array - not a list
    if (!is_array_and_not_empty($sqlArray)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty Associative Array as input for `\$sqlArray`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (array_is_list($sqlArray)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty Associative Array as input for `\$sqlArray`!");
        cli_info("Here it probably means that the \"\$DX\" variable is a List Array, empty or not?");
    }

    // Both $handlerFile and $fnName must be non-empty strings
    if (!is_string_and_not_empty($handlerFile)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty String as input for `\$handlerFile`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }
    if (!is_string_and_not_empty($fnName)) {
        cli_err_without_exit("[cli_convert_simple_sql_query_to_optimized_sql]: Expects a Non-Empty String as input for `\$fnName`!");
        cli_info("This probably means that the \"\$DX\" variable is an Empty Array, or not an Array at all?");
    }

    // Anonymous Functions that are used repeatedly
    $extractColsWithoutIdFromTable = function ($table, $tableString) {
        $cols = (str_contains($tableString, ",")) ? explode(",", substr($tableString, strlen($table) + 1)) : [substr($tableString, strlen($table) + 1)];
        $cols = array_map('trim', $cols);
        $cols = array_filter($cols, function ($col) {
            return $col !== 'id';
        });
        return array_values($cols);
    };
    $extractColsWithIdFromTable = function ($table, $tableString) {
        $cols = (str_contains($tableString, ",")) ? explode(",", substr($tableString, strlen($table) + 1)) : [substr($tableString, strlen($table) + 1)];
        $cols = array_map('trim', $cols);
        $cols = array_filter($cols, function ($col) {
            return $col !== '';
        });
        return array_values($cols);
    };

    // Prepare variables to store the
    // converted SQL Query Array
    $convertedSQLArray = ["qtype" => "", "sql" => "", "hydrate" => [], "bparam" => "", "fields" => [],];
    $builtSQLString = "";
    $builtHydrateArray = [];
    $builtBindedParamsString = "";
    $builtFieldsArray = [];
    $tables = $tablesAndRelationshipsFile['tables'] ?? [];
    $cols = ['uniqueCols' => [], 'table:col' => [], 'subqueries' => null];
    $relationships = $tablesAndRelationshipsFile['relationships'] ?? [];

    // List of available Global Config Rules - these will be checked against
    $globalConfigRules = [
        '<QUERY_TYPE>' => [
            'SELECT_DISTINCT',
            'SELECT_INTO',
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE'
        ],
        '<TABLES>' => [], // The table name as a string
        '[SUBQUERIES]' => [],
        '<HYDRATION_MODE>' => [],
        '<HYDRATION_TYPE>' => [],
    ];

    // List of keys that are ignored based on query type (this is just to inform the Developer)
    $ignoredKeysByQueryType = [
        'SELECT_DISTINCT' => [
            "INSERT",
            "INTO",
            "DELETE",
            "UPDATE",
            "SET",
            "VALUES",
            "GROUP BY",
            "HAVING",
        ],
        'SELECT_INTO' => [
            "INSERT",
            "DELETE",
            "UPDATE",
            "SET",
            "VALUES",
        ],
        'SELECT' => [
            "INSERT",
            "INTO",
            "DELETE",
            "UPDATE",
            "SET",
            "VALUES",
        ],
        'INSERT' => [
            "INTO",
            "JOIN",
            "JOINS",
            "ON",
            "SELECT",
            "DELETE",
            "UPDATE",
            "SET",
            "FROM",
            "VALUES",
            "WHERE",
            "ORDER BY",
            "GROUP BY",
            "HAVING",
            "LIMIT",
            "OFFSET",
            "<HYDRATION>"
        ],
        'UPDATE' => [
            "INTO",
            "JOIN",
            "JOINS",
            "ON",
            "SELECT",
            "DELETE",
            "INSERT",
            "FROM",
            "VALUES",
            "ORDER BY",
            "GROUP BY",
            "HAVING",
            "LIMIT",
            "OFFSET",
            "<HYDRATION>"
        ],
        'DELETE' => [
            "INTO",
            "JOIN",
            "JOINS",
            "ON",
            "SELECT",
            "INSERT",
            "UPDATE",
            "SET",
            "VALUES",
            "ORDER BY",
            "GROUP BY",
            "HAVING",
            "LIMIT",
            "OFFSET",
            "<HYDRATION>"
        ]
    ];

    // List of minimum required keys for each query type
    $minimumRequiredKeysByQueryType = [
        'SELECT_DISTINCT' => ['SELECT_DISTINCT', 'FROM'],
        'SELECT_INTO' => ['SELECT', 'INTO'],
        'SELECT' => ['SELECT', 'FROM'],
        'INSERT' => ['INSERT_INTO',],
        'UPDATE' => ['UPDATE_SET',],
        'DELETE' => ['DELETE_FROM']
    ];

    // List of valid binding types (s,i,d,b)
    // s = string, i = integer, d = double, b = blob
    $validBindingTypes = [
        's' => [],
        'i' => [],
        'd' => [],
        'b' => []
    ];

    // List of shorthand names of valid JOIN Types
    $validJoinTypes = [
        'i' => 'INNER JOIN',
        'inner' => 'INNER JOIN',
        'ij' => 'INNER JOIN',
        'join' => 'INNER JOIN',
        'j' => 'INNER JOIN',
        'l' => 'LEFT JOIN',
        'left' => 'LEFT JOIN',
        'r' => 'RIGHT JOIN',
        'right' => 'RIGHT JOIN',
    ];

    // "'<CONFIG>' key(s)
    $configKey = $sqlArray['<CONFIG>'] ?? null;
    $configQTKey = $configKey['<QUERY_TYPE>'] ?? null;
    $configTBKey = $configKey['<TABLES>'] ?? null;
    $hydrationModeKey = $configKey['<HYDRATION_MODE>'] ?? null;
    $hydrationTypeKey = $configKey['<HYDRATION_TYPE>'] ?? null;
    $configSubQsKey = $configKey['[SUBQUERIES]'] ?? null;
    $cols['subqueries'] = $configSubQsKey ?? null;
    $validFieldsKey = $sqlArray['<MATCHED_FIELDS>'] ?? null;
    $cols['matchedFields'] = $validFieldsKey ?? null;

    // If "$configKey" not null, we check it is an array and not empty
    // and then we iterate through the keys to check for valid keys
    if (is_array_and_not_empty($configKey)) {
        // If "$configKey" is an array, we check for valid keys
        foreach ($configKey as $key => $value) {
            // If the key is not in the global config rules, we error out
            if (!array_key_exists($key, $globalConfigRules)) {
                cli_err_syntax_without_exit("Invalid Config Key `$key` in SQL Query `$handlerFile.php=>$fnName`!");
                cli_info("Valid Config Keys are: " . implode(", ", array_keys($globalConfigRules)) . ".");
            }
        }
    } else {
        cli_err_syntax("No Config Key `<CONFIG>` found in SQL Array `$handlerFile.php=>$fnName`. It and its `[QUERY_TYPE]` key must be set!");
    }

    // Validate that $configQTKey is set and is a valid query type
    // then store it in the convertedSQLArray['qtype']!
    if (!isset($configQTKey) || !is_string_and_not_empty($configQTKey)) {
        cli_err_syntax_without_exit("No Config Key `<QUERY_TYPE>` found in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("Valid Query Types are:\n" . implode(",\n", quotify_elements($globalConfigRules['[QUERY_TYPE]'])) . ".");
    } elseif (!in_array(strtoupper($configQTKey), $globalConfigRules['<QUERY_TYPE>'], true)) {
        cli_err_syntax_without_exit("Invalid Config Key `<QUERY_TYPE>` value `$configQTKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("Valid Query Types are:\n" . implode(",\n", quotify_elements($globalConfigRules['[QUERY_TYPE]'])) . ".");
    }
    $convertedSQLArray['qtype'] = strtoupper($configQTKey);

    // Validate that $configTBKey is set and is a valid table name
    if (!isset($configTBKey) || empty($configTBKey)) {
        cli_err_syntax_without_exit("Invalid Config Key `<TABLES>` value in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<TABLES>` key must be a Non-Empty String or Array representing the Table name(s)!");
    }
    // If $configTBKey is a string, we convert it to an array meaning splitting on "," if it exists
    // or just wrapping it in an array if it is a single table name
    if (is_string($configTBKey) && is_string_and_not_empty($configTBKey)) {
        // If it is a string, we split it by comma and trim each element
        if (str_contains($configTBKey, ',')) {
            $configTBKey = array_map('trim', explode(',', $configTBKey));
        } elseif (str_contains($configTBKey, '|')) {
            $configTBKey = array_map('trim', explode('|', $configTBKey));
        } else {
            $configTBKey = [trim($configTBKey)];
        }
    } elseif (!is_array($configTBKey)) {
        cli_err_syntax_without_exit("Invalid Config Key `<TABLES>` value in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<TABLES>` key must be a Non-Empty String or Array representing the Table name(s)!");
    }

    // Loop through all Tables and check if they are valid (and exist in $tables[] array!)
    if (isset($configTBKey) && is_array_and_not_empty($configTBKey)) {
        foreach ($configTBKey as $tableName) {
            // If the table name is not a string or empty, we error out
            if (!is_string_and_not_empty($tableName)) {
                cli_err_syntax_without_exit("Invalid Table Name `$tableName` in SQL Array `$handlerFile.php=>$fnName`!");
                cli_info("Table Names must be Non-Empty Strings!");
            }
            // If the table name is not in the $tables array, we error out
            if (!array_key_exists($tableName, $tables)) {
                cli_err_syntax_without_exit("Table Name `$tableName` from SQL Array `$handlerFile.php=>$fnName` not found in `config/tables.php` File!");
                cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
            }
        }
    }

    // <HYDRATION_MODE> & <HYDRATION_TYPE> Keys are only available for `SELECT` Queries!
    if (isset($hydrationModeKey) && is_string_and_not_empty($hydrationModeKey) && $configQTKey !== 'SELECT') {
        cli_err_syntax_without_exit("Invalid Config Key `<HYDRATION_MODE>` value `$hydrationModeKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<HYDRATION_MODE>` Key is only available for `SELECT` Queries! Please remove it or change the `<QUERY_TYPE>` to `SELECT`.");
    }
    if (isset($hydrationTypeKey) && is_string_and_not_empty($hydrationTypeKey) && $configQTKey !== 'SELECT') {
        cli_err_syntax_without_exit("Invalid Config Key `<HYDRATION_TYPE>` value `$hydrationTypeKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("The `<HYDRATION_TYPE>` Key is only available for `SELECT` Queries! Please remove it or change the `<QUERY_TYPE>` to `SELECT`.");
    }

    // $cols contains all unique columns and table:col pairs which are used to
    // build the SQL Query by only checking against valid/existing table and columns
    // from `tables.php` file and also by the tables being used ($configTBKey).
    $singleTable = count($configTBKey) === 1;
    $removeDuplicateCols = [];
    foreach ($tables as $tb => $colKey) {
        // Only do this for the tables actually being processed!
        if (!in_array($tb, $configTBKey, true)) {
            continue;
        }
        foreach ($colKey as $k => $col) {
            if (isset($col['primary_key']) || isset($col['foreign_key'])) {
                $cols['table:col'][] = "$tb:$k";
                // Special case for Single Table Queries (only 1 Table provided)
                // Then we know PK and FK are Unique Columns in that table!
                if ($singleTable) {
                    $cols['uniqueCols'][] = $k;
                }
            }
            // If it is in the array, we add it to the $removeDuplicateCols array
            elseif (in_array($k, $cols['uniqueCols'], true)) {
                if (!in_array($k, $removeDuplicateCols, true)) {
                    $removeDuplicateCols[] = $k;
                }
                $cols['table:col'][] = "$tb:$k";
            }
            // If it is not in the array we add it to the $uniqueCols array
            elseif (!in_array($k, $cols['uniqueCols'], true)) {
                $cols['uniqueCols'][] = $k;
                $cols['table:col'][] = "$tb:$k";
            }
            continue;
        }
    }
    $cols['uniqueCols'] = array_diff($cols['uniqueCols'], $removeDuplicateCols);

    // If "$configSubQsKey" is not null (or empty), we make sure each array key
    // starts and ends with "[" and "]" and that its array element value is a non-empty string
    if (isset($configSubQsKey) && is_array_and_not_empty($configSubQsKey)) {
        // If the configSubQsKey is not an array, we error out
        if (!is_array($configSubQsKey)) {
            cli_err_syntax_without_exit("Invalid Config Key `[SUBQUERIES]` value in SQL Array `$handlerFile.php=>$fnName`!");
            cli_info("The `[SUBQUERIES]` key must be an Array representing the Subqueries!");
        }
        // If the configSubQsKey is an array, we check each key
        foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
            // If the subquery key does not start with "[" or end with "]", we error out
            if (!str_starts_with($subQueryKey, "[") || !str_ends_with($subQueryKey, "]")) {
                cli_err_syntax_without_exit("Invalid Subquery Key `$subQueryKey` in SQL Array `$handlerFile.php=>$fnName`!");
                cli_info("Subquery Keys must start with `[` and end with `]`!");
            }
            // If the subquery value is not a non-empty string, we error out
            if (!is_string_and_not_empty($subQueryValue)) {
                cli_err_syntax_without_exit("Invalid Subquery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName`!");
                cli_info("Subquery Values must be Non-Empty Strings (or just remove the key if not used)!");
            }
        }
    }

    // We iterate through $ignoredKeysByQueryType to add what keys would be ignored based
    // on query type ($configQTKey) and build and array and then we inform the Developer
    // that these keys are ignored based on the query type.
    $ignoredKeys = [];
    foreach ($sqlArray as $key => $keys) {
        // If the key is not in the ignored keys by query type, we skip it
        if (!array_key_exists($configQTKey, $ignoredKeysByQueryType)) {
            continue;
        }
        // If the key is in the ignored keys by query type, we add it to the ignored keys array
        if (in_array($key, $ignoredKeysByQueryType[$configQTKey], true)) {
            $ignoredKeys[] = $key;
        }
    }

    // We iterate through $minimumRequiredKeysByQueryType and make sure the current
    // query type ($configQTKey) has all the minimum required keys set in the SQL Array
    if (
        isset($minimumRequiredKeysByQueryType[$configQTKey])
        && is_array_and_not_empty($minimumRequiredKeysByQueryType[$configQTKey])
    ) {
        foreach ($minimumRequiredKeysByQueryType[$configQTKey] as $requiredKey) {
            // If the required key is not in the SQL Array, we error out
            if (!array_key_exists($requiredKey, $sqlArray)) {
                cli_err_syntax_without_exit("Missing Required Key `$requiredKey` in SQL Array `$handlerFile.php=>$fnName` for Query Type `$configQTKey`!");
                cli_info("The `$requiredKey` key must be set in the SQL Array for this Query Type!");
            }
        }
    }

    // If we have more than 1 tables when the query type is INSERT|UPDATE|DELETE, we error out
    if (
        in_array($configQTKey, ['INSERT', 'UPDATE', 'DELETE'], true)
        && is_array_and_not_empty($configTBKey)
        && count($configTBKey) > 1
    ) {
        cli_err_syntax_without_exit("Multiple Tables found in SQL Array `$handlerFile.php=>$fnName` for Query Type `$configQTKey`!");
        cli_info("For `$configQTKey` queries, only one table can be specified in `<TABLES>` key!");
    }

    // WE NOW PROCESS BASED ON $configQTKey VALUE (the Query Type)
    // BEFORE RETURNING THE FINALLY CONVERT_SQL_ARRAY VARIABLE!!!
    // THIS IS THE MAIN AND MOST IMPORTANT PART OF THE ENTIRE FUNCTION!!!

    // INSERT
    if ($configQTKey === 'INSERT') {
        $insertTb = $configTBKey[0] ?? null;
        $insertCols = "";
        $insertValues = "";
        if (!isset($insertTb) || !is_string_and_not_empty($insertTb)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for INSERT Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }

        // We will now check that "INSERT_INTO" key starts with "table_name:" since it
        // must be the same table name as the one chosen in "<CONFIG>['<TABLES>']"!
        $insertIntoKey = $sqlArray['INSERT_INTO'] ?? null;
        if (!isset($insertIntoKey) || !is_string_and_not_empty($insertIntoKey)) {
            cli_err_syntax_without_exit("No `INSERT_INTO` Key found in SQL Array `$handlerFile.php=>$fnName` for Query Type `$configQTKey`!");
            cli_info("The `INSERT_INTO` key must be a Non-Empty String representing the Table name(s)!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($insertIntoKey, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `$insertIntoKey` Key!");
            cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }
        if (!str_starts_with($insertIntoKey, $insertTb . ":")) {
            cli_err_syntax_without_exit("The `INSERT_INTO` Key in SQL Array `$handlerFile.php=>$fnName` does not start with the Table Name `$insertTb:`!");
            cli_info("The `INSERT_INTO` key must start with the Table Name followed by a colon `:`!");
        }

        // We extract columns based on ":" and then on optional "," (if there are more than one column)
        $insertCols = $extractColsWithoutIdFromTable($insertTb, $insertIntoKey);

        // We iterate through the True Table Columns to check if any of those
        // who are nullable === false are not in the $insertCols array and if so
        // we error out since those columns must be set in the INSERT Query!
        foreach ($tables[$insertTb] as $tKey => $tCol) {
            // Skip the ID column since it is auto-incremented
            if ($tKey === 'id') {
                continue;
            }
            // If the column is not nullable, we check if it is in the $insertCols
            // array and if it does not have a default value, we error out
            if (
                isset($tCol['nullable'])
                && !$tCol['nullable']
                && !in_array($tKey, $insertCols, true)
                && !isset($tCol['default'])
            ) {
                cli_err_syntax_without_exit("Column `$tKey` in Table `$insertTb` is NOT nullable (and without a Default Value) and must be included in the INSERT Query!");
                cli_info("Include the Column `$tKey` in the `INSERT_INTO` key of the SQL Array `$handlerFile.php=>$fnName`!");
            }
        }

        // We check that the columns are valid (exists in $tables[$insertTb])
        // and create the Binded Params String while we're at it!
        foreach ($insertCols as $col) {
            // If the column is not a string or empty, we error out
            if (!is_string_and_not_empty($col)) {
                cli_err_syntax_without_exit("Invalid Column Name in SQL Array `$handlerFile.php=>$fnName` for INSERT Query!");
                cli_info("Column Names must be Non-Empty Strings!");
            }
            // If the column is not in the table, we error out
            if (!array_key_exists($col, $tables[$insertTb])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$insertTb`!");
                cli_info("Valid Column Names are:\n" . implode(",\n", quotify_elements(array_keys($tables[$insertTb]))) . ".");
            }
            if (!isset($tables[$insertTb][$col]['binding'])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a Binding defined in `config/tables.php` for Table `$insertTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$insertTb`!");
            }
            if (!isset($validBindingTypes[$tables[$insertTb][$col]['binding']])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a VALID Binding defined in `config/tables.php` for Table `$insertTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$insertTb`!");
            }
            // The "fields" key in the $convertedSQLArray is used to store matching fields
            // so the Binded Params can use the correct values from a given array!
            if (isset($validFieldsKey) && is_array_and_not_empty($validFieldsKey)) {
                if (!isset($validFieldsKey[$col])) {
                    cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` is NOT in the <MATCHED_FIELDS> Array!");
                    cli_info("Valid Fields are:\n" . implode(",\n", quotify_elements($validFieldsKey)) . ".");
                } else {
                    $builtFieldsArray[] = !empty($validFieldsKey[$col]) ? $validFieldsKey[$col] : $insertTb . '_' . $col;
                }
            }
            $builtBindedParamsString .= $tables[$insertTb][$col]['binding'];
            if (!empty($builtFieldsArray)) {
                $convertedSQLArray['fields'] = $builtFieldsArray;
            }
        }

        // We count the $insertCols and create equally many ? as $insertValues
        // Then we implode the $insertCols and create the final SQL string(s)
        $insertValues = str_repeat("?,", count($insertCols) - 1) . "?";
        $insertCols = implode(",", $insertCols);
        $builtSQLString .= "INSERT INTO $insertTb ($insertCols) VALUES ($insertValues);";
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for INSERT Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the INSERT Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // UPDATE
    elseif ($configQTKey === 'UPDATE') {
        $updateTb = $configTBKey[0] ?? null;
        $whereTb = $sqlArray['WHERE'] ?? null;
        $updateCols = "";
        $updateColsWithPlaceholders = [];
        $whereWithPlaceholders = [];

        if (!isset($updateTb) || !is_string_and_not_empty($updateTb)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for UPDATE Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }
        $updateIntoKey = $sqlArray['UPDATE_SET'] ?? null;
        if (!isset($updateIntoKey) || !is_string_and_not_empty($updateIntoKey)) {
            cli_err_syntax_without_exit("No `UPDATE_SET` Key found in SQL Array `$handlerFile.php=>$fnName` for update Query!");
            cli_info("The `UPDATE_SET` key must be a Non-Empty String representing the Table name(s)!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($updateIntoKey, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `$updateIntoKey` Key!");
            cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }
        if (!str_starts_with($updateIntoKey, $updateTb . ":")) {
            cli_err_syntax_without_exit("The `UPDATE_SET` Key in SQL Array `$handlerFile.php=>$fnName` does not start with the Table Name `$updateTb:`!");
            cli_info("The `UPDATE_SET` key must start with the Table Name followed by a colon `:`!");
        }
        $updateCols = $extractColsWithIdFromTable($updateTb, $updateIntoKey);

        // We check that the columns are valid (exists in $tables[$updateTb])
        // and create the Binded Params String while we're at it!
        foreach ($updateCols as $key => $col) {
            // If the column is not a string or empty, we error out
            if (!is_string_and_not_empty($col)) {
                cli_err_syntax_without_exit("Invalid Column Name in SQL Array `$handlerFile.php=>$fnName` for update Query!");
                cli_info("Column Names must be Non-Empty Strings!");
            }
            // If the column is not in the table, we error out
            if (!array_key_exists($col, $tables[$updateTb])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$updateTb`!");
                cli_info("Valid Column Names are:\n" . implode(",\n", quotify_elements(array_keys($tables[$updateTb]))) . ".");
            }
            if (!isset($tables[$updateTb][$col]['binding'])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a Binding defined in `config/tables.php` for Table `$updateTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$updateTb`!");
            }
            if (!isset($validBindingTypes[$tables[$updateTb][$col]['binding']])) {
                cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` does NOT have a VALID Binding defined in `config/tables.php` for Table `$updateTb`!");
                cli_info("Make sure the Column `$col` has a binding ('s', 'd', 'i' or 'b') defined in the `config/tables.php` file for Table `$updateTb`!");
            }
            if ($col === 'id') {
                cli_err_syntax_without_exit("Column Name `id` (Primary Key) from SQL Array `$handlerFile.php=>$fnName` is not allowed in the UPDATE_SET Key of UPDATE Query!");
                cli_info("The `id` column is auto-incremented and should not be updated. It can however be used in the WHERE Key to indicate which rows should be affected by the UPDATE Query!");
            }
            // The "fields" key in the $convertedSQLArray is used to store matching fields
            // so the Binded Params can use the correct values from a given array!
            if (isset($validFieldsKey) && is_array_and_not_empty($validFieldsKey)) {
                if (!isset($validFieldsKey[$col])) {
                    cli_err_syntax_without_exit("Column Name `$col` from SQL Array `$handlerFile.php=>$fnName` is NOT in the <MATCHED_FIELDS> Array!");
                    cli_info("Valid Fields are:\n" . implode(",\n", quotify_elements($validFieldsKey)) . ".");
                } else {
                    $builtFieldsArray[] = !empty($validFieldsKey[$col]) ? $validFieldsKey[$col] : $updateTb . '_' . $col;
                }
            }
            $updateColsWithPlaceholders[] = "$col = ?";
            $builtBindedParamsString .= $tables[$updateTb][$col]['binding'];
            if (!empty($builtFieldsArray)) {
                $convertedSQLArray['fields'] = $builtFieldsArray;
            }
        }

        // If the WHERE clause is set, we parse its condition and add it to the SQL Array
        // We also pass the "$builtBindedParamsString" as reference to add the necessary
        // "?" placeholders based on how many are used within the Parsed Where Clause!
        if (isset($whereTb) && is_string_and_not_empty($whereTb)) {
            $whereTb = cli_parse_condition_clause_sql($configTBKey, $whereTb, "UPDATE", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "WHERE", $aggAliases ?? []);
            // If $whereTb is no longer a string after parsing, we error out
            if (!is_string_and_not_empty($whereTb)) {
                cli_err_syntax_without_exit("Invalid `WHERE` Key String found in SQL Array `$handlerFile.php=>$fnName` for UPDATE Query after being processed by `cli_parse_where_clause_sql` Function!");
                cli_info("The `WHERE` Key must be a Non-Empty String representing the WHERE clause after being parsed by the `cli_parse_where_clause_sql` Function!");
            }
        }

        // We count the $insertCols and create equally many ? as $insertValues
        // Then we implode the $insertCols and create the final SQL string(s)
        $updateCols = implode(", ", $updateColsWithPlaceholders);
        $builtSQLString .= "UPDATE $updateTb SET $updateCols";
        $builtSQLString .= (isset($whereTb) && is_string($whereTb) && !empty($whereTb)) ? " WHERE $whereTb" : "";
        $builtSQLString .= ";";
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // We will now replace every [SubQuery] in the $builtSQLString by iterating
        // through the $configSubQsKey array and replacing the [SubQuery] with the
        // actual SubQuery string from the $configSubQsKey array.
        if (isset($configSubQsKey) && is_array($configSubQsKey) && count($configSubQsKey) > 0) {
            foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
                // If the subquery value is not a string or empty, we error out
                if (!is_string_and_not_empty($subQueryValue)) {
                    cli_err_syntax_without_exit("Invalid SubQuery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName` for SubQuery Key `$subQueryKey`!");
                    cli_info("The SubQuery Value must be a Non-Empty String representing the SubQuery!");
                }
                // Replace the [SubQuery] with the actual SubQuery string
                $builtSQLString = str_replace($subQueryKey, $subQueryValue, $builtSQLString);
            }
        }
        // We finally remove all extra spaces and newlines from the built SQL string
        // and then add it to the converted SQL Array
        $builtSQLString = preg_replace('/\( /', '(', $builtSQLString);
        $builtSQLString = preg_replace('/ \)/', ')', $builtSQLString);
        $builtSQLString = preg_replace('/\s+/', ' ', $builtSQLString);
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['fields'] = $builtFieldsArray;

        // When the WHERE clause is missing we strongly warn about it to the Developer but still allow it.
        // The warning is about that you would change ALL rows in the table if you do not specify a WHERE clause!
        if (!isset($whereTb) || !is_string_and_not_empty($whereTb)) {
            cli_warning_without_exit("No `WHERE` Key found in SQL Array `$handlerFile.php=>$fnName` for UPDATE Query:\n`$builtSQLString`!");
            cli_warning_without_exit("This means that ALL Rows in the Table `$updateTb` will be Updated with the same provided values!");
            cli_info_without_exit("If this is truly your intention, just ignore this warning above and continue as usual!");
        }

        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for UPDATE Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the UPDATE Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // DELETE
    elseif ($configQTKey === 'DELETE') {
        $deleteTb = $configTBKey[0] ?? null;
        $whereTb = $sqlArray['WHERE'] ?? null;

        if (!isset($deleteTb) || !is_string_and_not_empty($deleteTb)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for DELETE Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }
        $deleteIntoKey = $sqlArray['DELETE_FROM'] ?? null;
        if (!isset($deleteIntoKey) || !is_string_and_not_empty($deleteIntoKey)) {
            cli_err_syntax_without_exit("No `DELETE_FROM` Key found in SQL Array `$handlerFile.php=>$fnName` for update Query!");
            cli_info("The `DELETE_FROM` key must be a Non-Empty String representing the Table name(s)!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($deleteIntoKey, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `$deleteIntoKey` Key!");
            cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }
        // We check that $deleteTB is the exact same as  $configTBKey since
        // it should be ONLY one table name in the DELETE Query that you
        // be able to delete from per SQL String!
        if (
            !is_string_and_not_empty($deleteTb)
            || !is_string_and_not_empty($deleteIntoKey)
            || ($deleteTb !== $deleteIntoKey)
        ) {
            cli_err_syntax_without_exit("The `DELETE_FROM` Key in SQL Array `$handlerFile.php=>$fnName` does not match the Table Name `$deleteTb`!");
            cli_info("The `DELETE_FROM` key must match the Table Name in `<TABLES>` key since you should only delete from one Table per SQL String!");
        }

        // We check that $deleteTB is the exact same as  $configTBKey since
        // it should be ONLY one table name in the DELETE Query that you
        // be able to delete from per SQL String!
        if (
            !is_string_and_not_empty($deleteTb)
            || !is_string_and_not_empty($deleteIntoKey)
            || ($deleteTb !== $deleteIntoKey)
        ) {
            cli_err_syntax_without_exit("The `DELETE_FROM` Key in SQL Array `$handlerFile.php=>$fnName` does not match the Table Name `$deleteTb`!");
            cli_info("The `DELETE_FROM` key must match the Table Name in `<TABLES>` key since you should only delete from one Table per SQL String!");
        }

        // If the WHERE clause is set, we parse its condition and add it to the SQL Array
        // We also pass the "$builtBindedParamsString" as reference to add the necessary
        // "?" placeholders based on how many are used within the Parsed Where Clause!
        if (isset($whereTb) && is_string_and_not_empty($whereTb)) {
            $whereTb = cli_parse_condition_clause_sql($configTBKey, $whereTb, "DELETE", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "WHERE", $aggAliases ?? []);
            // If $whereTb is no longer a string after parsing, we error out
            if (!is_string_and_not_empty($whereTb)) {
                cli_err_syntax_without_exit("Invalid `WHERE` Key String found in SQL Array `$handlerFile.php=>$fnName` for UPDATE Query after being processed by `cli_parse_where_clause_sql` Function!");
                cli_info("The `WHERE` Key must be a Non-Empty String representing the WHERE clause after being parsed by the `cli_parse_where_clause_sql` Function!");
            }
        }

        // We count the $insertCols and create equally many ? as $insertValues
        // Then we implode the $insertCols and create the final SQL string(s)
        $builtSQLString .= "DELETE FROM $deleteTb";
        $builtSQLString .= (isset($whereTb) && is_string($whereTb) && !empty($whereTb)) ? " WHERE $whereTb" : "";
        $builtSQLString .= ";";
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // We will now replace every [SubQuery] in the $builtSQLString by iterating
        // through the $configSubQsKey array and replacing the [SubQuery] with the
        // actual SubQuery string from the $configSubQsKey array.
        if (isset($configSubQsKey) && is_array($configSubQsKey) && count($configSubQsKey) > 0) {
            foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
                // If the subquery value is not a string or empty, we error out
                if (!is_string_and_not_empty($subQueryValue)) {
                    cli_err_syntax_without_exit("Invalid SubQuery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName` for SubQuery Key `$subQueryKey`!");
                    cli_info("The SubQuery Value must be a Non-Empty String representing the SubQuery!");
                }
                // Replace the [SubQuery] with the actual SubQuery string
                $builtSQLString = str_replace($subQueryKey, $subQueryValue, $builtSQLString);
            }
        }
        // We finally remove all extra spaces and newlines from the built SQL string
        // and then add it to the converted SQL Array
        $builtSQLString = preg_replace('/\( /', '(', $builtSQLString);
        $builtSQLString = preg_replace('/ \)/', ')', $builtSQLString);
        $builtSQLString = preg_replace('/\s+/', ' ', $builtSQLString);
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['fields'] = $builtFieldsArray;

        // When the WHERE clause is missing we strongly warn about it to the Developer but still allow it.
        // The warning is about that you would change ALL rows in the table if you do not specify a WHERE clause!
        if (!isset($whereTb) || !is_string_and_not_empty($whereTb)) {
            cli_warning_without_exit("No `WHERE` Key found in SQL Array `$handlerFile.php=>$fnName` for DELETE Query:\n`$builtSQLString`!");
            cli_warning_without_exit("This means that ALL Rows in the Table `$deleteTb` will be DELETED that match the provided Condition(s)!");
            cli_info_without_exit("If this is truly your intention, just ignore this warning above and continue as usual!");
        }

        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for DELETE Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the DELETE Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // SELECT DISTINCT
    elseif ($configQTKey === 'SELECT_DISTINCT') {
        cli_err("<NOT SUPPORTED/IMPLEMENTED YET IN FUNKPHP! SCRIPT STOPPED!>");
        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for SELECT_DISTINCT Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the SELECT_DISTINCT Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // SELECT INTO
    elseif ($configQTKey === 'SELECT_INTO') {
        cli_err("<NOT SUPPORTED/IMPLEMENTED YET IN FUNKPHP! SCRIPT STOPPED!>");
        // Report success and inform about ignored keys
        cli_success_without_exit("Built SQL String for SELECT_INTO Query: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the SELECT_INTO Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // SELECT
    elseif ($configQTKey === 'SELECT') {
        $selectTbs = $configTBKey ?? null;
        // SELECTed Tables!
        $currentlySelectedTbs = [];
        // JOINS_ON-joined Tables which might be needed by
        // GROUP BY Key to use. You can have more JOINed
        // Tables than the ones SELECTed in the SELECT Key!
        $joinedTables = [];
        $joinedTablesWithRef = [];
        $allAliases = [];
        $aliasesTbCol = [];
        $selectedCols = []; // This is used by "<HYDRATION>" to know which columns are selected (using AS/aliases)
        // To check for duplicate aliases later since agg functions
        // could be used multiple times on the same table+column(s)!
        $aggAliases = [];
        $selectedTbsColsStr = "";
        $joinsStr = "";
        $whereStr = "";
        $groupByStr = "";
        $havingStr = "";
        $orderByStr = "";
        $limitStr = "";
        $offsetStr = "";
        $selectTb = $sqlArray['SELECT'] ?? null;
        $fromTb = $sqlArray['FROM'] ?? null;
        $joinsTb = $sqlArray['JOINS_ON'] ?? null;
        $whereTb = $sqlArray['WHERE'] ?? null;
        $groupByTb = $sqlArray['GROUP BY'] ?? null;
        $havingTb = $sqlArray['HAVING'] ?? null;
        $orderByTb = $sqlArray['ORDER BY'] ?? null;
        $limitTb = $sqlArray['LIMIT'] ?? null;
        $offsetTb = $sqlArray['OFFSET'] ?? null;
        $hydrationKey = $sqlArray['<HYDRATION>'] ?? null;
        $hydrationMode = "";
        $hydrationType = "";

        // If <HYDRATION_MODE> is set, it should be a string but can be empty or must be
        // "simple", "advanded" or "simple|advanced" (simple is assumed as default then)
        if (isset($hydrationModeKey) && !is_string($hydrationModeKey)) {
            cli_err_syntax_without_exit("Invalid <HYDRATION_MODE> Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The <HYDRATION_MODE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `simple`, `advanced` or `simple|advanced`! (here `simple` is assumed as default)");
        }
        if (isset($hydrationTypeKey) && !is_string($hydrationTypeKey)) {
            cli_err_syntax_without_exit("Invalid <HYDRATION_TYPE> Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The <HYDRATION_TYPE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `array`, `object` or `array|object`! (here `array` is assumed as default)");
        }
        if (is_string_and_not_empty($hydrationModeKey)) {
            $hydrationModeKey = strtolower($hydrationModeKey);
            if (!in_array($hydrationModeKey, ['simple', 'advanced', 'simple|advanced'], true)) {
                cli_err_syntax_without_exit("Invalid `<HYDRATION_MODE>` Key Value found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The <HYDRATION_MODE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `simple`, `advanced` or `simple|advanced`! (here `simple` is assumed as default)");
            }
            if ($hydrationModeKey === 'simple' || $hydrationModeKey === 'simple|advanced') {
                $hydrationMode = "simple";
            } else {
                $hydrationMode = "advanced";
            }
        }
        if (is_string_and_not_empty($hydrationTypeKey)) {
            $hydrationTypeKey = strtolower($hydrationTypeKey);
            if (!in_array($hydrationTypeKey, ['array', 'object', 'array|object'], true)) {
                cli_err_syntax_without_exit("Invalid `<HYDRATION_MODE>` Key Value found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The <HYDRATION_MODE> Key must be a String (leave empty or remove entirely if not used) representing the Hydration Mode!\nValid Values are: `array`, `object` or `array|object`! (here `array` is assumed as default)");
            }
            if ($hydrationTypeKey === 'array' || $hydrationTypeKey === 'array|object') {
                $hydrationType = "array";
            } else {
                $hydrationType = "object";
            }
        }

        // $selectTbs cannot be null or empty array/string
        if (!isset($selectTbs) || empty($selectTbs)) {
            cli_err_syntax_without_exit("No Table Name found in SQL Array['<TABLES'>] `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `<TABLES>` key must be a Non-Empty Array representing the Table name(s)!");
        }

        // $selectTb cannot be null, an associative array or an empty list array
        if (!isset($selectTb) || !array_is_list($selectTb) || empty($selectTb)) {
            cli_err_syntax_without_exit("No `SELECT` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `SELECT` key must be a Non-Empty List Array representing the Table name(s)! For example:\n`table_name:col1,col2,col3` OR\n`table_name!:col1`\nSecond examples selects all columns except `col1`!");
        }

        // $selectTb listed array must all be complete string (not empty strings)
        foreach ($selectTb as $selectTbName) {
            if (!is_string_and_not_empty($selectTbName)) {
                cli_err_syntax_without_exit("Invalid Data Types found in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("Each Array Element in the `SELECT` Key must be a Non-Empty String representing the Table Name and optionally Columns to select from that Table!\nSyntax Example: `table_name:col1,col2,col3` OR `table_name!:col1`.\nThe second example selects all columns except `col1`!");
            }
        }

        // $fromTb cannot be null or empty string
        if (!isset($fromTb) || !is_string_and_not_empty($fromTb)) {
            cli_err_syntax_without_exit("No `FROM` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `FROM` key must be a Non-Empty String representing the Primary Table (and ONLY a single one) to SELECT and/or JOIN from!");
        }
        // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
        // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
        if (str_starts_ends_with($fromTb, "{", "}")) {
            cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `FROM` Key!");
            cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
        }

        // We now loop through $selectTb and check whether the $fromTb table exists in at least one of the
        // $selectTbs tables and if not, we error out since the FROM table must be one of the SELECT tables!
        $tbFound = false;
        foreach ($selectTbs as $selectTbName) {
            if (str_starts_with($selectTbName, $fromTb)) {
                $tbFound = true;
                break;
            }
        }
        if (!$tbFound) {
            cli_err_syntax_without_exit("The `FROM` Key Table `$fromTb` was not found in the `SELECT` Key Tables in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info("The `FROM` Key must contain one of the Tables in the `SELECT` Key since you need a starting Table for your SELECT Query!");
        }
        // Regexes for Aggregate Functions. First to see if it starts with any of the aggregate functions
        // and the second to see if it is a complete aggregate function with a table and/or column name
        // and the special case of COUNT(*) which is a special case of the COUNT function!
        $aggregateFunctionsStart = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()/i";
        $aggFuncRegex = "/^(COUNT\(DISTINCT[ |=]|COUNT\(\*\)|COUNT\(|SUM\(|AVG\(|MIN\(|MAX\()([a-zA-Z0-9_:\*]+)*\)$/i";
        $aggTableColRegex = "/^([a-zA-Z0-9_]+):([a-zA-Z0-9_]+)$/i";
        $aggColRegex = "/^([a-zA-Z0-9_]+)$/i";
        $aggFuncValidStarts = [
            'count(distinct=' => 'count_distinct_',
            'count(distinct ' => 'count_distinct_',
            'count(' => 'count_',
            'count(*)' => 'count_all_',
            'sum(' => 'sum_',
            'avg(' => 'avg_',
            'min(' => 'min_',
            'max(' => 'max_',
        ];

        // Commands that You might wanna SELECT but are not allowed to do
        // through FunkPHP so you get an error message about it!
        $disallowedCommands = [
            "BIN(",
            "BINARY",
            "CASE",
            "CAST",
            "COALESCE(",
            "CONVERT(",
            "CONV(",
            "CONNECTION_ID()",
            "CURRENT_USER",
            "DATABASE()",
            "IF(",
            "IFNULL(",
            "ISNULL(",
            "LAST_INSERT_ID()",
            "SESSION_USER()",
            "SYSTEM_USER()",
            "USER()",
            "VERSION()",
        ];

        // PARSING THE "SELECT" Key (all selected tables and columns)
        // We loop through $selectTb which we know now are all valid non-empty strings.
        // We will check, validate & build the SELECT part of the SQL String based on
        // different cases:
        foreach ($selectTb as $selectTbName) {
            if (!is_string_and_not_empty($selectTbName)) {
                cli_err_syntax_without_exit("Invalid Data Type found in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("Each Array Element in the `SELECT` Key must be a Non-Empty String representing the Table Name and optionally Columns to select from that Table!\nSyntax Example: `table_name:col1,col2,col3` OR `table_name!:col1`.\nThe second example selects all columns except `col1`!");
            }
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (str_starts_ends_with($selectTbName, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `SELECT` Key!");
                cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            if (array_str_starts_with($disallowedCommands, strtoupper($selectTbName))) {
                cli_err_syntax_without_exit("The `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` contains a Disallowed Command: `$selectTbName`!");
                cli_info_without_exit("The Following Commands are NOT allowed in the `SELECT` Key:\n" . implode(",\n", quotify_elements($disallowedCommands)) . ".");
                cli_info("You will have to MANUALLY write a SQL String that runs/SELECTs that specific Command or use some of the FunkPHP's in-built SQL Functions that run some of those specific Commands!");
            }
            // Lowercase entire string to make it case-insensitive
            $selectTbName = strtolower($selectTbName);
            // CASE 1: Starts with any of the Aggregate Functions
            if (preg_match($aggregateFunctionsStart, $selectTbName)) {
                if (preg_match($aggFuncRegex, $selectTbName, $aggFuncMatches)) {
                    $aggFunc = $aggFuncMatches[1];
                    $aggTbWithCol = $aggFuncMatches[2];

                    // Incorrect Aggregate Function Format despite matching
                    if (!isset($aggFuncValidStarts[$aggFunc])) {
                        cli_err_syntax_without_exit("Invalid Aggregate Function Format (`$selectTbName`) in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        cli_info("The Aggregate Function must start with one of the following:\n" . implode(",\n", quotify_elements(array_keys($aggFuncValidStarts))) . "!");
                    }

                    // SPECIAL CASE: COUNT(*) which is a special case of the COUNT function and thus we must get
                    // the table name from the $fromTb variable since it does not have a table name!
                    if ($aggFuncMatches[0] === 'count(*)') {
                        if (!in_array($fromTb, $selectTbs, true)) {
                            cli_err_syntax_without_exit("Table Name `$fromTb` from `FROM` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                            cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                        }
                        // We add the COUNT(*) as a special case without table and column
                        // Only add if not already in the currently selected tables and also
                        // so you can use multiple agg functions on the same table without
                        // JOINS_ON complaining about "mulitple tables selected"!
                        if (!in_array($fromTb, $currentlySelectedTbs, true)) {
                            $currentlySelectedTbs[] = $fromTb;
                        }

                        // Rename and add to the selectedTbsColsStr and the aggAliases
                        // so you can reuse same aggregate function on the same table
                        // without conflicting with the aliases!
                        $as_name = $aggFuncValidStarts[$aggFunc] . $fromTb;
                        $i = 0;
                        while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                            $i++;
                            $as_name = $as_name . "_$i";
                        }
                        $aggAliases[] = $as_name;
                        $allAliases[] = $as_name;
                        $selectedCols[$fromTb][] = $as_name;
                        $aliasesTbCol[$as_name] = [
                            'tb' => $fromTb,
                            'col' => '*',
                        ];
                        $selectedTbsColsStr .= strtoupper($aggFunc) . "*) AS " . $as_name . ",\n";
                        continue;
                    }

                    // Incorrect Table/Column Format despite matching
                    if (!preg_match($aggTableColRegex, $aggTbWithCol)) {
                        cli_err_syntax_without_exit("Invalid Table/Column Format (`$aggTbWithCol`) in Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        cli_info("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                    }

                    // Extract Table and Column from the Aggregate Function
                    // to check against Valid Table & Column from `tables.php`!
                    [$aggTb, $aggCol] = explode(":", $aggTbWithCol, 2);
                    if (isset($tables[$aggTb]) && is_array_and_not_empty($tables[$aggTb])) {
                        // Validate correct Table:Column Binding (d or i when SUM() or AVG() is used!)
                        if (isset($tables[$aggTb][$aggCol]) && is_array_and_not_empty($tables[$aggTb][$aggCol])) {
                            if (($aggFunc === 'sum(' || $aggFunc === 'avg(') &&
                                ($tables[$aggTb][$aggCol]['binding'] !== 'd' &&
                                    $tables[$aggTb][$aggCol]['binding'] !== 'i')
                            ) {
                                cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` must have a Binding of 'd' or 'i' for Table `$aggTb`! (it has `{$tables[$aggTb][$aggCol]['binding']}`)");
                                cli_info_without_exit("Valid Bindings for Column `$aggCol` are: 'd' (double) or 'i' (integer) for SUM and AVG Aggregate Functions!");
                                cli_info("SUM() and AVG() Aggregate Functions can only be used on Numeric Columns ('d' or 'i' as binding)!");
                            }
                            // Only add if not already in the currently selected tables and also
                            // so you can use multiple agg functions on the same table without
                            // JOINS_ON complaining about "mulitple tables selected"!
                            if (!in_array($aggTb, $currentlySelectedTbs, true)) {
                                $currentlySelectedTbs[] = $aggTb;
                            }
                            $as_name = $aggFuncValidStarts[$aggFunc] . $tables[$aggTb][$aggCol]['joined_name'];
                            $i = 0;
                            while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                                $i++;
                                $as_name = $as_name . "_$i";
                            }
                            $aggAliases[] = $as_name;
                            $allAliases[] = $as_name;
                            $selectedCols[$aggTb][$as_name] = $aggCol;
                            $selectedCols[$aggTb][$tables[$aggTb][$aggCol]['joined_name']] = $aggCol;
                            $aliasesTbCol[$as_name] = [
                                'tb' => $aggTb,
                                'col' => $aggCol,
                            ];
                            $aliasesTbCol[$tables[$aggTb][$aggCol]['joined_name']] = [
                                'tb' => $aggTb,
                                'col' => $aggCol,
                            ];
                            // We remove "=" if it is inside of $aggFunc
                            // which is special case for COUNT(DISTINCT=)
                            if (str_contains($aggFunc, '=')) {
                                $aggFunc = str_replace('=', ' ', $aggFunc);
                            }
                            $selectedTbsColsStr .= strtoupper($aggFunc) . "$aggTb.$aggCol) AS " . $as_name . ",\n";
                            continue;
                        } else {
                            cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$aggTb`!");
                            cli_info("Valid Column Names for Table `$aggTb` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$aggTb]))) . ".");
                        }
                    } else {
                        cli_err_syntax_without_exit("Table Name `$aggTb` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `tables.php` File!");
                        cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . ".");
                    }
                }
                // When it failed to match despite matching the start regex, we error out
                else {
                    cli_err_syntax_without_exit("Invalid Aggregate Function Format (`$selectTbName`) in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info_without_exit("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                    cli_info("Check for Correct Punctuation such as:`table:column` and NOT:`table.column`!");
                }
            }
            // CASE 2: Only Table Name (no columns) is given so
            // add all columns from that table if it is valid table!
            elseif (
                !str_contains($selectTbName, ":")
                && !str_contains($selectTbName, "!")
                && !str_contains($selectTbName, ",")
            ) {
                if (!in_array($selectTbName, $selectTbs, true)) {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                }
                // Table exists, so we add all columns from that table.
                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                    $currentlySelectedTbs[] = $selectTbName;
                    foreach ($tables[$selectTbName] as $colKey => $singleTbCols) {
                        $selectedTbsColsStr .= $selectTbName . ".$colKey AS " . $singleTbCols['joined_name'] . ",\n";
                        $allAliases[] = $singleTbCols['joined_name'];
                        $selectedCols[$selectTbName][$singleTbCols['joined_name']] = $colKey;
                        // $aliasesTbCol[$as_name] = [ // DO I REALLY NEED THIS? Since $as_name is for agg Funcs?
                        //     'tb' => $selectTbName,
                        //     'col' => $colKey,
                        // ];
                        $aliasesTbCol[$singleTbCols['joined_name']] = [
                            'tb' => $selectTbName,
                            'col' => $colKey,
                        ];
                    }
                } else {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` has no columns defined in `config/tables.php`!");
                    cli_info("Make sure the Table `$selectTbName` has columns defined in `config/tables.php` file!");
                }
                continue;
            }
            // CASE 3: Table Name with "!" (excludes column) so kinda like above but
            // but we exclude the spelled out Columns and include the rest from Table.
            // This means Aggregate Functions are not available to use here!
            elseif (
                str_contains($selectTbName, "!:")
            ) {
                // Split the Table Name and Column(s) by "!:"
                [$selectTbName, $excludedCols] = explode("!:", $selectTbName, 2);

                // We check if Table exists otherwise we error out
                if (!in_array($selectTbName, $selectTbs, true)) {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                }

                // $excludedCols becomes an array and is also split on "," if multiple columns are excluded
                $excludedCols = str_contains($excludedCols, ",") ? explode(",", $excludedCols) : [$excludedCols];

                // Table exists, so we add all columns from that table.
                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                    $currentlySelectedTbs[] = $selectTbName;
                    // First we check that the excluded columns are valid meaning that they should exist
                    // in table, we just do not wanna select/include them in the SQL String.
                    foreach ($excludedCols as $excludedCol) {
                        // If the excluded column is not a string or empty, we error out
                        if (!is_string_and_not_empty($excludedCol)) {
                            cli_err_syntax_without_exit("Invalid Excluded Column Name in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("Excluded Column Names must be Non-Empty Strings!");
                        }
                        // You are NOT supposed to use Aggregate Functions here since
                        // you are defining what columns to exclude from the table!
                        if (preg_match($aggregateFunctionsStart, $excludedCol)) {
                            cli_err_syntax_without_exit("Aggregate Functions (`$excludedCol`) are NOT allowed in Syntax `$selectTbName:" . implode(",", $excludedCols) .  "` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info_without_exit("You can ONLY Exclude Columns from the Table when using the Syntax `table!:col1,col2,etc`, NOT add Aggregate Functions to them! (like `SUM(col1)`, `AVG(col2)` etc.)");
                            cli_info("To use Aggregate Functions, you must use the Syntax `table:AGG_FUNC(col1),col2,etc` OR `AGG_FUNC(table:col)` instead!");
                        }
                        // If the excluded column is not in the table, we error out
                        if (!array_key_exists($excludedCol, $tables[$selectTbName])) {
                            cli_err_syntax_without_exit("Excluded Column Name `$excludedCol` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$selectTbName`!");
                            cli_info("Valid Column Names for Table `$selectTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$selectTbName]))) . ".");
                        }
                    }

                    foreach ($tables[$selectTbName] as $colKey => $singleTbCols) {
                        // Only add the column if it is NOT in the excluded columns array
                        if (!in_array($colKey, $excludedCols, true)) {
                            $selectedTbsColsStr .= $selectTbName . ".$colKey AS " . $singleTbCols['joined_name'] . ",\n";
                            $allAliases[] = $singleTbCols['joined_name'];
                            $selectedCols[$selectTbName][$singleTbCols['joined_name']] =  $colKey;
                            // $aliasesTbCol[$as_name] = [ // DO I REALLY NEED THIS? Since $as_name is for agg Funcs?
                            //     'tb' => $selectTbName,
                            //     'col' => $colKey,
                            // ];
                            $aliasesTbCol[$singleTbCols['joined_name']] = [
                                'tb' => $selectTbName,
                                'col' => $colKey,
                            ];
                        }
                    }
                } else {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` has no columns defined in `config/tables.php`!");
                    cli_info("Make sure the Table `$selectTbName` has columns defined in `config/tables.php` file!");
                }
                continue;
            }
            // CASE 4: Table Name with ":" (selects specific columns) so
            elseif (
                str_contains($selectTbName, ":")
                && !str_contains($selectTbName, "!")
                && !str_contains($selectTbName, "!:")
            ) {
                // If the string ends with ":" after removing all whitespace, we error out
                if (str_ends_with(trim($selectTbName), ":")) {
                    cli_err_syntax_without_exit("Invalid Table Name Format in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Table Name Formats are:\n`table_name` (this selects all columns!) OR\n`table_name:col1,col2,col3` (this selects only these 3 columns!) OR\n`table_name!:col1` (this selects all columns except `col1`!)");
                }
                // Split the Table Name and Column(s) by "!:"
                [$selectTbName, $includedCols] = explode(":", $selectTbName, 2);

                // We check if Table exists otherwise we error out
                if (!in_array($selectTbName, $selectTbs, true)) {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . ".");
                }

                // $includedCols becomes an array and is also split on "," if multiple columns are included
                $includedCols = str_contains($includedCols, ",") ? explode(",", $includedCols) : [$includedCols];
                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                    $currentlySelectedTbs[] = $selectTbName;
                    foreach ($includedCols as $includedCol) {
                        if (!is_string_and_not_empty($includedCol)) {
                            cli_err_syntax_without_exit("Invalid Included Column Name in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("Included Column Names must be Non-Empty Strings!");
                        }

                        // SPECIAL CASE FOR EACH `col` in `table` that could have Agg Function as part of its name!
                        // Like: `authors:id,AVG(age)`, so we need to handle that too!
                        if (preg_match($aggregateFunctionsStart, $includedCol)) {
                            if (preg_match($aggFuncRegex, $includedCol, $aggFuncMatches)) {
                                $aggFunc = $aggFuncMatches[1];
                                $aggTbWithCol = $aggFuncMatches[2];

                                // Incorrect Aggregate Function Format despite matching
                                if (!isset($aggFuncValidStarts[$aggFunc])) {
                                    cli_err_syntax_without_exit("Invalid Aggregate Function Format (`$includedCol`) in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                    cli_info("The Aggregate Function must start with one of the following:\n" . implode(",\n", quotify_elements(array_keys($aggFuncValidStarts))) . "!");
                                }

                                // SPECIAL CASE: COUNT(*) which is a special case of the COUNT function and thus we must get
                                // the table name from the $fromTb variable since it does not have a table name!
                                if ($aggFuncMatches[0] === 'count(*)') {
                                    if (!in_array($selectTbName, $selectTbs, true)) {
                                        cli_err_syntax_without_exit("Table Name `$selectTbName` from `FROM` Key in SQL Array `$handlerFile.php=>$fnName` not found in `<TABLES>` Key!");
                                        cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements($selectTbs)) . " OR add it to the `<TABLES>` Key in SQL Array `$handlerFile.php=>$fnName` and recompile!");
                                    }

                                    // Rename and add to the selectedTbsColsStr and the aggAliases
                                    // so you can reuse same aggregate function on the same table
                                    // without conflicting with the aliases!
                                    $as_name = $aggFuncValidStarts[$aggFunc] . $selectTbName;
                                    $i = 0;
                                    while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                                        $i++;
                                        $as_name = $as_name . "_$i";
                                    }
                                    $aggAliases[] = $as_name;
                                    $allAliases[] = $as_name;
                                    $selectedCols[$selectTbName][$as_name] = $selectTbName;
                                    $aliasesTbCol[$as_name] = [
                                        'tb' => $selectTbName,
                                        'col' => '*',
                                    ];
                                    $selectedTbsColsStr .= strtoupper($aggFunc) . "*) AS " . $as_name . ",\n";
                                    continue;
                                }

                                // Incorrect Column Format despite matching
                                if (!preg_match($aggColRegex, $aggTbWithCol)) {
                                    cli_err_syntax_without_exit("Invalid Table/Column Format (`$aggTbWithCol`) in Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                    cli_info("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                                }

                                // We are reusing something that otherwise wouldn't know the table name
                                // so we try rewrite as little as possible so just reassign the variables
                                $aggCol = $aggTbWithCol;
                                if (isset($tables[$selectTbName]) && is_array_and_not_empty($tables[$selectTbName])) {
                                    // Validate correct Table:Column Binding (d or i when SUM() or AVG() is used!)
                                    if (isset($tables[$selectTbName][$aggCol]) && is_array_and_not_empty($tables[$selectTbName][$aggCol])) {
                                        if (($aggFunc === 'sum(' || $aggFunc === 'avg(') &&
                                            ($tables[$selectTbName][$aggCol]['binding'] !== 'd' &&
                                                $tables[$selectTbName][$aggCol]['binding'] !== 'i')
                                        ) {
                                            cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` must have a Binding of 'd' or 'i' for Table `$selectTbName`! (it has `{$tables[$selectTbName][$aggCol]['binding']}`)");
                                            cli_info_without_exit("Valid Bindings for Column `$aggCol` are: 'd' (double) or 'i' (integer) for SUM and AVG Aggregate Functions!");
                                            cli_info("SUM() and AVG() Aggregate Functions can only be used on Numeric Columns ('d' or 'i' as binding)!");
                                        }

                                        // Prepare correct alias name for the aggregate function
                                        $as_name = $aggFuncValidStarts[$aggFunc] . $tables[$selectTbName][$aggCol]['joined_name'];
                                        $i = 0;
                                        while (in_array($as_name, $aggAliases, true) || in_array($as_name, $allAliases, true)) {
                                            $i++;
                                            $as_name = $as_name . "_$i";
                                        }
                                        $aggAliases[] = $as_name;
                                        $allAliases[] = $as_name;
                                        $selectedCols[$selectTbName][$as_name] = $aggCol;
                                        $aliasesTbCol[$as_name] = [
                                            'tb' => $selectTbName,
                                            'col' => $aggCol,
                                        ];
                                        $aliasesTbCol[$tables[$selectTbName][$aggCol]['joined_name']] = [
                                            'tb' => $selectTbName,
                                            'col' => $aggCol,
                                        ];
                                        // We remove "=" if it is inside of $aggFunc
                                        // which is special case for COUNT(DISTINCT=)
                                        if (str_contains($aggFunc, '=')) {
                                            $aggFunc = str_replace('=', ' ', $aggFunc);
                                        }
                                        $selectedTbsColsStr .= strtoupper($aggFunc) . "$selectTbName.$aggCol) AS " . $as_name . ",\n";
                                        continue;
                                    } else {
                                        cli_err_syntax_without_exit("Column Name `$aggCol` from Aggregate Function `$aggFunc` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$selectTbName`!");
                                        cli_info("Valid Column Names for Table `$selectTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$selectTbName]))) . ".");
                                    }
                                }
                            }
                            // When it failed to match despite matching the start regex, we error out
                            else {
                                cli_err_syntax_without_exit("Invalid Aggregate Function Format in Column `$includedCol` for Table `$selectTbName` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                cli_info_without_exit("The Aggregate Function must follow the Format: `AGGREGATE_FUNC(tableName:colName)` or `AGGREGATE_FUNC(*)` for COUNT(*)!");
                                cli_info("Check for Correct Punctuation such as:`table:column` and NOT:`table.column`!");
                            }
                        }

                        // Otherwise, just check as regular Table Column!
                        if (!array_key_exists($includedCol, $tables[$selectTbName])) {
                            cli_err_syntax_without_exit("Included Column Name `$includedCol` from SQL Array `$handlerFile.php=>$fnName` not found in Table `$selectTbName`!");
                            cli_info("Valid Column Names for Table `$selectTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$selectTbName]))) . ".");
                        }

                        // Here we just add the column to the selectedTbsColsStr since we know it exists!
                        $selectedTbsColsStr .= $selectTbName . ".$includedCol AS " . $tables[$selectTbName][$includedCol]['joined_name'] . ",\n";
                        $allAliases[] = $tables[$selectTbName][$includedCol]['joined_name'];
                        $selectedCols[$selectTbName][$tables[$selectTbName][$includedCol]['joined_name']] = $includedCol;
                        $aliasesTbCol[$tables[$selectTbName][$includedCol]['joined_name']] = [
                            'tb' => $selectTbName,
                            'col' => $includedCol,
                        ];
                        continue;
                    }
                } else {
                    cli_err_syntax_without_exit("Table Name `$selectTbName` from `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` has no Columns defined in the `tables.php` File!");
                    cli_info("Make sure the Table `$selectTbName` has columns defined in `config/tables.php` File!");
                }
                continue;
            }
            // No valid Table Name Format found in `SELECT` Key so we error out
            else {
                cli_err_syntax_without_exit("Invalid Table Name Format in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("Valid Table Name Formats are:\n`table_name` (this selects all columns!) OR\n`table_name:col1,col2,col3` (this selects only these 3 columns!) OR\n`table_name!:col1` (this selects all columns except `col1`!)");
            }
        }
        // Remove last "," from the $selectedTbsColsStr if it exists
        if (str_ends_with($selectedTbsColsStr, ",\n")) {
            $selectedTbsColsStr = substr($selectedTbsColsStr, 0, -2);
        }
        // Iterate through Table ($selTb) and if we do NOT find `id` Column in it, we just
        // add it to the selectedCols $selectedCols[$selTb][] = 'id'; and to the start of
        // the $selectedTbsColsStr so it is always selected by default! We also write WARNING
        // that it has been added because it was not since it is really NOT useful to NOT use it!
        foreach ($selectedCols as $selTb => $selCol) {
            if (!array_key_exists('id', $selCol)) {
                $selectedCols[$selTb]['id'] = 'id'; // Add 'id' Column to the selectedCols
                $selectedTbsColsStr = "$selTb.id AS $selTb" . "_id,\n" . $selectedTbsColsStr; // Add it to the start of the selectedTbsColsStr
                cli_warning_without_exit("Table `$selTb` in `SELECT` Key in SQL Array `$handlerFile.php=>$fnName` did not have `id` Column so it was added by default! (`$selTb.id AS $selTb`)");
            }
        }

        // PARSING THE "FROM" Key (the primary table to select from/join/work with)
        // We already know FROM Key is a valid string and not empty so now we check
        // if the FROM table is in the SELECT tables and if it is not, we error out
        if (!in_array($fromTb, $currentlySelectedTbs, true)) {
            cli_err_syntax_without_exit("Table Name `$fromTb` from `FROM` Key in SQL Array `$handlerFile.php=>$fnName` not found in `SELECT` Key!");
            cli_info("Currently SELECTed Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . " and it needs the Table `$fromTb` included somewhere!");
        }

        // PARSING THE "JOINS_ON" Key (the JOINs to perform)
        // We check if it is empty and if it is then $currentlySelectedTbs
        // must only be 1 since you must use JOIN when selecting more
        // than 1 Table in current version of FunkPHP!
        if (
            count($currentlySelectedTbs) > 1
            && (!isset($joinsTb)
                || !is_array($joinsTb)
                || !array_is_list($joinsTb)
                || empty($joinsTb))
        ) {
            cli_err_syntax_without_exit("No `JOINS_ON` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
            cli_info_without_exit("The `JOINS_ON` Key must be a Non-Empty Array and must be used when you are SELECTing more than 1 Table in the `SELECT` Key!");
            cli_info("IMPORTANT: You can use `JOINS_ON` Key to Join several Tables while still just SELECTing from 1 Table in the `SELECT` Key!");
        }

        // When we are SELECTing more than 1 Table, we must use JOINs to
        // join them together so here we start parsing the JOINS_ON Key!
        $tables_were_joined = false;
        if (isset($joinsTb) && !empty($joinsTb)) {
            if (is_string($joinsTb) && str_starts_ends_with($joinsTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `JOINS_ON` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            if (!is_array($joinsTb) || !array_is_list($joinsTb)) {
                cli_err_syntax_without_exit("No `JOINS_ON` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `JOINS_ON` Key must be a Non-Empty List Array representing the JOINs to perform between the Tables in the `SELECT` Key when more than one Table is being SELECTed!");
            }
            $joinsREGEX = '/^([a-zA-Z]+)=([a-zA-Z_0-9]+),([a-zA-Z_0-9]+)\(([a-zA-Z_0-9]+)\),([a-zA-Z_0-9]+)\(([a-zA-Z_0-9]+)\)$/i';
            $joinedTables[] = $fromTb; // We always join the FROM table first

            // Iterate through each joinTb in the JOINS_ON Key
            foreach ($joinsTb as $joinTb) {
                // If the joinTb is not a string or empty, we error out
                if (!is_string_and_not_empty($joinTb)) {
                    cli_err_syntax_without_exit("Invalid Join Table String found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Each Join Table String must be a Non-Empty String representing the JOIN condition between two Tables!");
                }
                // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
                // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
                if (str_starts_ends_with($joinTb, "{", "}")) {
                    cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `JOINS_ON` Key!");
                    cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
                }

                // If the joinTb does not match the regex, we error out
                if (!preg_match($joinsREGEX, $joinTb, $matches)) {
                    cli_err_syntax_without_exit("Invalid Join Table String found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Each Join Table Array Element String must match the following format:\n`join_type=join_table,table2(table2Col),table1(table1Col)`\nExample: `inner=articles,authors(id),articles(author_id)`");
                }

                // We extract Join Type, TB names
                // and Columns from the matches
                [
                    $joinType,
                    $joinTable,
                    $table1,
                    $table1Col,
                    $table2,
                    $table2Col
                ] = array_slice($matches, 1);

                // We lowercase all variables to ensure consistency
                $joinType = strtolower($joinType);
                $joinTable = strtolower($joinTable);
                $table1 = strtolower($table1);
                $table2 = strtolower($table2);
                $table1Col = strtolower($table1Col);
                $table2Col = strtolower($table2Col);

                // We check if the joinType is valid based on list of valid join types
                if (!isset($validJoinTypes[$joinType])) {
                    cli_err_syntax_without_exit("Invalid Join Type `$joinType` found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Join Types are:\n" . implode(",\n", quotify_elements(array_keys($validJoinTypes))) . "! (based on \$validJoinTypes)");
                }

                // We check that $joinTable is a valid table name
                if (!array_key_exists($joinTable, $tables)) {
                    cli_err_syntax_without_exit("Invalid Join Table Name `$joinTable` found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . "! (based on `tables.php` File)");
                }

                // We check that $joinTable is not already joined
                // to avoid joining the same table multiple times
                if (in_array($joinTable, $joinedTables, true)) {
                    cli_err_syntax_without_exit("Table `$joinTable` is already marked as joined in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info_without_exit("You can only join a Table once in the `JOINS_ON` Key! (based on `tables.php` File)");
                    cli_info_without_exit("Current Joined Tables are: " . implode("->", quotify_elements($joinedTables)) . "!");
                    cli_info("IMPORTANT: During SQL Handler Function Generation, you ALWAYS get all possible Relationships (all Tables that can be joined), but You have to choose unique ones after each `Join Type=`!");
                }
                // Otherwise add it to the joined tables
                $joinedTables[] = $joinTable;

                // We add tables and their references that can be used by <Hydration> Key later!
                if ($table1Col === substr($table1, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table1] = substr($table1, 0, -1) . "_id";
                } elseif ($table1Col === substr($table2, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table1] = substr($table2, 0, -1) . "_id";
                } elseif ($table2Col === substr($table2, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table2] = substr($table2, 0, -1) . "_id";
                } elseif ($table2Col === substr($table1, 0, -1) . "_id") {
                    $joinedTablesWithRef[$table2] = substr($table1, 0, -1) . "_id";
                }

                // We check that $table2 and $table1 are valid tables
                if (!array_key_exists($table2, $tables) || !array_key_exists($table1, $tables)) {
                    cli_err_syntax_without_exit("Invalid Table Name(s) (`$table2` or `$table1`) found in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info("Valid Table Names are:\n" . implode(",\n", quotify_elements(array_keys($tables))) . "! (based on `tables.php` File)");
                }

                // We check that $table2 actually has any relationships defined
                if (!isset($relationships[$table2]) || !is_array($relationships[$table2]) || empty($relationships[$table2])) {
                    cli_err_syntax_without_exit("Table `$table2` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query has no relationships defined in `config/tables.php` File! ('relationships' Key)");
                    cli_info("Make sure the Table `$table2` has relationships defined in `config/relationships.php` file!");
                }

                // We check $table2 has a relationship with $table1
                if (!array_key_exists($table1, $relationships[$table2])) {
                    cli_err_syntax_without_exit("Table `$table2` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query has no relationship with Table `$table1` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }

                // We get the related table from the relationships array
                // and check that it is valid by it having keys 'local_column',
                // 'foreign_column', 'local_table', 'foreign_table' & 'direction'
                // and that all those are non-emtpy string! Also, the key
                // 'direction' must be either 'pk_to_fk' or 'fk_to_pk'!
                $relatedTable = $relationships[$table2][$table1];
                if (
                    !is_array_and_not_empty($relatedTable)
                    || !array_key_exists('local_column', $relatedTable)
                    || !array_key_exists('foreign_column', $relatedTable)
                    || !array_key_exists('local_table', $relatedTable)
                    || !array_key_exists('foreign_table', $relatedTable)
                    || !array_key_exists('direction', $relatedTable)
                    || !is_string_and_not_empty($relatedTable['local_column'])
                    || !is_string_and_not_empty($relatedTable['foreign_column'])
                    || !is_string_and_not_empty($relatedTable['local_table'])
                    || !is_string_and_not_empty($relatedTable['foreign_table'])
                    || !is_string_and_not_empty($relatedTable['direction'])
                    || ($relatedTable['direction'] !== 'pk_to_fk'
                        && $relatedTable['direction'] !== 'fk_to_pk')
                ) {
                    cli_err_syntax_without_exit("Invalid Relationship Definition found in `config/relationships.php` File for Table `$table2` with Table `$table1`!");
                    cli_info("Each Relationship Definition must have the Keys: 'local_column', 'foreign_column', 'local_table', 'foreign_table' & 'direction' as Non-Empty Strings!");
                }

                // When $table1 does not match any of the local or foreign tables in the relationship
                // we error out since it must be one of the two tables in the relationship!
                if ($table1 !== $relatedTable['local_table'] && $table1 !== $relatedTable['foreign_table']) {
                    cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Table (`{$relatedTable['local_table']}`) or Foreign Table (`{$relatedTable['foreign_table']}`) in the relationship with Table `$table2` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }
                // We check same for $table2
                if ($table2 !== $relatedTable['local_table'] && $table2 !== $relatedTable['foreign_table']) {
                    cli_err_syntax_without_exit("Table `$table2` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Table (`{$relatedTable['local_table']}`) or Foreign Table (`{$relatedTable['foreign_table']}`) in the relationship with Table `$table1` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table1` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }
                // We now have two possible cases and can check the rest based on whatever $table1 is the local or foreign table in the relationship
                if ($table1 === $relatedTable['local_table']) {
                    // This means $table1Col should be the local column
                    // and $table2Col should be the foreign column
                    // and $table2 should be the foreign table
                    if (
                        $table2 !== $relatedTable['foreign_table']
                        || $table2Col !== $relatedTable['foreign_column']
                        || $table1Col !== $relatedTable['local_column']
                    ) {
                        cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Column (`{$relatedTable['local_column']}`) or Foreign Column (`{$relatedTable['foreign_column']}`) in the Relationship with Table `$table2` defined in `config/relationships.php` File!");
                        cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                    }
                } elseif ($table1 === $relatedTable['foreign_table']) {
                    // This means $table1Col should be the foreign column
                    // and $table2Col should be the local column
                    // and $table2 should be the local table
                    if (
                        $table2 !== $relatedTable['local_table']
                        || $table2Col !== $relatedTable['local_column']
                        || $table1Col !== $relatedTable['foreign_column']
                    ) {
                        cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Column (`{$relatedTable['local_column']}`) or Foreign Column (`{$relatedTable['foreign_column']}`) in the Relationship with Table `$table2` defined in `config/relationships.php` File!");
                        cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                    }
                }
                // This means error out since $table1 is not a valid table in the relationship
                else {
                    cli_err_syntax_without_exit("Table `$table1` in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query does not match Local Table (`{$relatedTable['local_table']}`) or Foreign Table (`{$relatedTable['foreign_table']}`) in the relationship with Table `$table2` defined in `config/relationships.php` File!");
                    cli_info("Provide a Table which `$table2` has a relationship with defined in `config/tables.php` File! ('relationships' Key)");
                }

                // Finally we build the JOIN String based on the joinType
                // and the tables and columns we have validated above.
                $joinType = $validJoinTypes[$joinType];
                $joinsStr .= "$joinType $joinTable ON $table1.$table1Col = $table2.$table2Col ";
            }

            // You cannot SELECT more Tables than you have
            // joined (but the reverse is possible!)
            if (count($currentlySelectedTbs) > count($joinedTables)) {
                cli_err_syntax_without_exit("You cannot SELECT more Tables than you have joined in `JOINS_ON` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info_without_exit("Currently Selected Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . ".");
                cli_info_without_exit("Currently Joined Tables are: `" . implode("->", $joinedTables) . "`.");
                cli_info("Following Tables must be joined in `JOINS_ON` Key:\n" . implode(",\n", quotify_elements(array_diff($currentlySelectedTbs, $joinedTables))) . " OR remove them from the `SELECT` Key!");
            }

            // Remove last ",\n" from the $joinsStr if it exists
            if (str_ends_with($joinsStr, " ")) {
                $joinsStr = substr($joinsStr, 0, -1);
            }
            // If we reached here we can set tables_were_joined to true
            // so hydration parsing can know this at the end!
            $tables_were_joined = true;
        }

        // PARSING THE OPTIONAL "WHERE" Key (the WHERE clause to filter results) using Condition Clause Function if the "WHERE" Key is a Non-Empty String!
        $whereStr = isset($whereTb) && is_string($whereTb) && !empty($whereTb) ? cli_parse_condition_clause_sql($configTBKey, $whereTb, "SELECT", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "WHERE", $aggAliases) : "";

        // PARSING THE OPTIONAL Keys "GROUP_BY" & "HAVING" (the GROUP BY clause
        // to group results and HAVING clause to filter grouped results)
        if (isset($groupByTb) && is_string($groupByTb) && !empty($groupByTb)) {
            if (!is_string($groupByTb)) {
                cli_err_syntax_without_exit("Invalid `GROUP BY` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `GROUP BY` Key must be a String representing the Columns to Group by! (leave empty or remove if not used)\nSyntax Examples:\n`table:col1,col2`|`col1,col2` (to Group by Columns from a Single Table) OR\n`table1:col1|table2:col2` (to Group by Columns from Multiple Tables).");
            }
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (str_starts_ends_with($groupByTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `GROUP BY` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // We split $groupByTb on "|" if it exists
            // or just turn single string into an array!
            $groupByTb = trim($groupByTb);
            if (str_contains($groupByTb, "|")) {
                $groupByTb = explode("|", $groupByTb);
            } else {
                $groupByTb = [$groupByTb];
            }

            // Iterate through to validate that we can actually group by specific
            // `table:col` OR `table:col` OR `col` (when single Table)!
            foreach ($groupByTb as $groupTB) {
                $groupTB = strtolower($groupTB); // Lowercase for consistency
                // CASE 1: Does NOT include ":"
                if (!str_contains($groupTB, ":")) {
                    // We split on "," if it exists
                    // or just turn single string into an array!
                    if (str_contains($groupTB, ",")) {
                        $groupColNames = explode(",", $groupTB);
                    } else {
                        $groupColNames = [$groupTB];
                    }
                    // Validate that the column names exist in the currently selected tables
                    foreach ($groupColNames as $groupColName) {
                        // If the column name is not a string or empty, we error out
                        if (!is_string_and_not_empty($groupColName)) {
                            cli_err_syntax_without_exit("Invalid Column Name in `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                            cli_info("Column Names must be Non-Empty Strings!");
                        }
                        // If the Column Name is not in the `uniqueCols` array that means they need to manually write `table:col`
                        if (!in_array($groupColName, $cols['uniqueCols'], true)) {
                            cli_err_syntax_without_exit("Column Name `$groupColName` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in Unique Column Names Array! (making it ambiguous)");
                            cli_info_without_exit("Valid Unique Column Names are:\n" . implode(",\n", quotify_elements($cols['uniqueCols'])) . ".");
                            cli_info("Alternatively, you can rewrite it as `table:col1,col2,etc` to Group one ore more Column(s) from a Specific Table!");
                        }
                    }
                    $groupByStr .= implode(",", $groupColNames) . ",\n";
                    continue;
                }
                // CASE 2: Includes ":" so we split on ":"
                elseif (str_contains($groupTB, ":")) {
                    [$groupTbName, $groupColNames] = explode(":", $groupTB, 2);
                    // Validate $groupTbName exists EITHER in the `SELECT` Key or in
                    // the `JOINS_ON` Key. That is; it has been SELECTed or JOINed!
                    if (
                        !in_array($groupTbName, $currentlySelectedTbs, true)
                        && !in_array($groupTbName, $joinedTables, true)
                    ) {
                        cli_err_syntax_without_exit("Table Name `$groupTbName` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in `SELECT` Key!");
                        cli_info("Currently SELECTed Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . ".");
                    }
                    // Validate $groupColName exists in the table when it is a single column
                    if (!str_contains($groupColNames, ",") && !array_key_exists($groupColNames, $tables[$groupTbName])) {
                        cli_err_syntax_without_exit("Column Name `$groupColNames` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$groupTbName`!");
                        cli_info("Valid Column Names for Table `$groupTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$groupTbName]))) . ".");
                    }
                    // Validate $groupColNames exists in the table when it is multiple columns (so we split on ",")
                    // and check every column name on the same table as with the single column case!
                    if (str_contains($groupColNames, ",")) {
                        $groupColNames = explode(",", $groupColNames);
                        foreach ($groupColNames as $groupColName) {
                            if (!is_string_and_not_empty($groupColName)) {
                                cli_err_syntax_without_exit("Invalid Column Name in `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                cli_info("Column Names must be Non-Empty Strings!");
                            }
                            if (!array_key_exists($groupColName, $tables[$groupTbName])) {
                                cli_err_syntax_without_exit("Column Name `$groupColName` from `GROUP BY` Key in SQL Array `$handlerFile.php=>$fnName` not found in Table `$groupTbName`!");
                                cli_info("Valid Column Names for Table `$groupTbName` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$groupTbName]))) . ".");
                            }
                        }
                        // All Columns are VALID for this table, so let's build the GROUP BY String
                        // by iterating through each again and adding `table.Col`
                        foreach ($groupColNames as $groupColName) {
                            $groupByStr .= "$groupTbName.$groupColName, ";
                        }
                        $groupByStr .= "\n";
                        continue;
                    }

                    // We end up here when only a single column is specified
                    // like `table:col` so we just add it directly!
                    $groupByStr .= "$groupTbName.$groupColNames,\n";
                    continue;
                }
            }
            // Remove ", " from the end of $groupByStr if it exists
            if (str_ends_with($groupByStr, ",\n")) {
                $groupByStr = substr($groupByStr, 0, -2);
            }
        }
        if (isset($havingTb)) {
            if (!is_string($havingTb)) {
                cli_err_syntax_without_exit("Invalid `HAVING` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `HAVING` Key must be a String representing the Condition(s) to Filter Grouped Results! (leave empty or remove if not used)");
            }
            // If GROUP BY was never correctly parsed (or added at all) we error out since
            // HAVING is dependent on GROUP BY being present!
            if (is_string($havingTb) && !empty($havingTb) && empty($groupByStr)) {
                cli_err_syntax_without_exit("No `GROUP BY` Key found in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `HAVING` Key must be used with a Non-Empty `GROUP BY` Key to Filter Grouped Results!");
            }
            $havingStr = isset($havingTb) && is_string($havingTb) && !empty($havingTb) ? cli_parse_condition_clause_sql($configTBKey, $havingTb, "SELECT", $convertedSQLArray, $cols, $builtBindedParamsString, $builtFieldsArray, $allAliases, "HAVING", $aggAliases, $aliasesTbCol) : "";
        }

        // PARSING THE OPTIONAL "ORDER BY" Key (the ORDER BY clause to sort results)
        if (isset($orderByTb)) {
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (str_starts_ends_with($orderByTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `ORDER BY` Key!");
                cli_info("Only `WHERE` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // Must be a string it if is set, otherwise we error out
            if (!is_string($orderByTb)) {
                cli_err_syntax_without_exit("Invalid `ORDER BY` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `ORDER BY` Key must be a String representing the Columns to Order by! (leave empty or remove if not used)");
            }
            // Only start parsing when it is a Non-Empty String!
            elseif (is_string($orderByTb) && !empty($orderByTb)) {
                // Trim string, then split on "|" if it exists
                // or just turn single string into an array!
                $orderByTb = trim($orderByTb);
                if (str_contains($orderByTb, "|")) {
                    $orderByTb = explode("|", $orderByTb);
                } else {
                    $orderByTb = [$orderByTb];
                }
                // Iterate through to validate that we can actually order by specific
                // `table:col,ASC|DESC` OR `table:col ASC|DESC` OR `col,ASC|DESC`!
                // "NULLS FIRST" & "NULLS LAST" are not supported yet in FunkPHP!
                $notSupportedYet = ["NULLS FIRST", "NULLS LAST"];
                $orderByRegex = "/^(([a-zA-Z0-9_]+):)*(\?|[a-zA-Z0-9_]+)[ |,]{1}(ASC|DESC)$/i";
                foreach ($orderByTb as $obTB) {
                    if (str_contains($obTB, "?")) {
                        cli_err_syntax_without_exit("`ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query contains the `?` Placeholder!");
                        cli_info_without_exit("The `ORDER BY` Key does NOT support `?` Placeholder Syntax!");
                        cli_info("Following SQL Key(s) Support the Placeholder `?`:\n" . implode(",\n", quotify_elements(["WHERE", "HAVING", "SET", "VALUES"])) . ".");
                    }
                    // If $notSupportedYet is found in current string, we error out
                    if (str_contains($obTB, $notSupportedYet[0]) || str_contains($obTB, $notSupportedYet[1])) {
                        cli_err_syntax_without_exit("`ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query contains Unsupported Syntax: `$obTB`!");
                        cli_info("The `ORDER BY` Key does NOT yet support `NULLS FIRST` or `NULLS LAST` Syntax in FunkPHP!");
                    }
                    // Check preg_match for current $obTB against the regex
                    if (!preg_match($orderByRegex, $obTB, $matches)) {
                        cli_err_syntax_without_exit("Invalid `ORDER BY` Key value `$obTB` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        cli_info("The `ORDER BY` Key must be a String representing the Columns to Order by in the Format: `table:col ASC|DESC`, `table:col,ASC|DESC`, `col ASC|DESC` or `col,ASC|DESC`!");
                    }
                    $obTable = isset($matches[2]) ? $matches[2] : ""; // Table Name if exists
                    $obCol = $matches[3] ?? null; // Column Name
                    $obOrder = $matches[4] ?? null; // Order (ASC or DESC)

                    // If $obTable is empty we assume a unique column OR
                    // its possible alias from $allAliases Array!
                    if (empty($obTable)) {
                        // If the column is not in the unique columns or aliases, we error out
                        if (!in_array($obCol, $cols['uniqueCols'], true) && !in_array($obCol, $allAliases, true)) {
                            cli_err_syntax_without_exit("Column `$obCol` from `ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query not found in Unique Columns or Aliases! (not being unique means it is ambiguous to what Table it belongs to)");
                            cli_info_without_exit("Valid Unique Columns are:\n" . implode(",\n", quotify_elements($cols['uniqueCols'])) . ".");
                            cli_info("Valid Unique Aliases are:\n" . implode(",\n", quotify_elements($allAliases)) . ".");
                        }
                        // Otherwise we add it to the order by string
                        $orderByStr .= "$obCol $obOrder, ";
                    } else {
                        // If the table is not in the currently selected tables, we error out
                        if (!in_array($obTable, $currentlySelectedTbs, true)) {
                            cli_err_syntax_without_exit("Table `$obTable` from `ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query not found in `SELECT` Key!");
                            cli_info("Currently SELECTed Tables are:\n" . implode(",\n", quotify_elements($currentlySelectedTbs)) . ".");
                        }
                        // If the column is not in the table columns, we error out
                        if (!array_key_exists($obCol, $tables[$obTable])) {
                            cli_err_syntax_without_exit("Column `$obCol` from `ORDER BY` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query not found in Table `$obTable`!");
                            cli_info("Valid Columns for Table `$obTable` are:\n" . implode(",\n", quotify_elements(array_keys($tables[$obTable]))) . ".");
                        }
                        // Otherwise we add it to the order by string
                        $orderByStr .= "$obTable.$obCol $obOrder, ";
                    }
                }

                // Remove ", " from the end of the $orderByStr if it exists
                if (str_ends_with($orderByStr, ", ")) {
                    $orderByStr = substr($orderByStr, 0, -2);
                }
            }
        }

        // PARSING THE OPTIONAL Keys: "LIMIT" & "OFFSET" Key (LIMIT is number of rows, OFFSET is where you start from results)
        if (isset($limitTb)) {
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (is_string($limitTb) && str_starts_ends_with($limitTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `LIMIT` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // 'LIMIT' key does allow a '?' as a placeholder for the limit value
            if ($limitTb === '?') {
                $limitStr = '?';
                $builtBindedParamsString .= "i";
                if (!in_array("limit", $builtFieldsArray, true)) {
                    $builtFieldsArray[] = "limit";
                } else {
                    $i = 1;
                    while (in_array("limit_$i", $builtFieldsArray, true)) {
                        $i++;
                    }
                    $builtFieldsArray[] = "limit_$i";
                }
            } elseif (is_string($limitTb) && strlen($limitTb) === 0) { // This is not error out on an Allowed Empty String!
            } elseif (is_numeric($limitTb) && (int)$limitTb >= 0) { // Must Be Positive Integer!
                $limitStr = (string)$limitTb;
            } else {
                cli_err_syntax_without_exit("Invalid `LIMIT` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `LIMIT` Key must be a Non-Negative Integer, or an Empty String (`''`) to omit it. (or just remove the entire Key)");
            }
        }
        if (isset($offsetTb)) {
            // Escape hatched SQL Queries (starting & ending with "{}") are ONLY
            // for only "WHERE" Keys in UPDATE, DELETE & SELECT Query Types!
            if (is_string($offsetTb) && str_starts_ends_with($offsetTb, "{", "}")) {
                cli_err_syntax_without_exit("Escaped SQL Syntax (starting & ending with `{}`) is NOT supported in `OFFSET` Key!");
                cli_info("Only `WHERE` & `HAVING` Keys in UPDATE, DELETE & SELECT Queries support Escaped SQL Syntax!");
            }
            // 'LIMIT' key does allow a '?' as a placeholder for the limit value
            if ($offsetTb === '?') {
                $offsetStr = '?';
                $builtBindedParamsString .= "i";
                if (!in_array("offset", $builtFieldsArray, true)) {
                    $builtFieldsArray[] = "offset";
                } else {
                    $i = 1;
                    while (in_array("offset_$i", $builtFieldsArray, true)) {
                        $i++;
                    }
                    $builtFieldsArray[] = "offset_$i";
                }
            } elseif (is_string($offsetTb) && strlen($offsetTb) === 0) { // This is not error out on an Allowed Empty String!
            } elseif (is_numeric($offsetTb) && (int)$offsetTb >= 0) { // Must Be Positive Integer!
                $offsetStr = (string)$offsetTb;
            } else {
                cli_err_syntax_without_exit("Invalid `OFFSET` Key value in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                cli_info("The `OFFSET` Key must be a Non-Negative Integer, or an Empty String (`''`) to omit it. (or just remove the entire Key)");
            }
        }

        // This is where all parts of the SQL String are stitched together
        $builtSQLString .= isset($selectedTbsColsStr) && is_string_and_not_empty($selectedTbsColsStr) ? "SELECT $selectedTbsColsStr" : "";
        $builtSQLString .= isset($fromTb) && is_string_and_not_empty($fromTb) ? " FROM $fromTb" : "";
        $builtSQLString .= isset($joinsStr) && is_string_and_not_empty($joinsStr) ? " $joinsStr" : "";
        $builtSQLString .= isset($whereStr) && is_string_and_not_empty($whereStr) ? " WHERE $whereStr" : "";
        $builtSQLString .= isset($groupByStr) && is_string_and_not_empty($groupByStr) ? " GROUP BY $groupByStr" : "";
        $builtSQLString .= isset($havingStr) && is_string_and_not_empty($havingStr) ? " HAVING $havingStr" : "";
        $builtSQLString .= isset($orderByStr) && is_string_and_not_empty($orderByStr) ? " ORDER BY $orderByStr" : "";
        $builtSQLString .= isset($limitStr) && is_string_and_not_empty($limitStr) ? " LIMIT $limitStr" : "";
        $builtSQLString .= isset($offsetStr) && is_string_and_not_empty($offsetStr) ? " OFFSET $offsetStr" : "";
        $builtSQLString .= ";";
        $convertedSQLArray['bparam'] = $builtBindedParamsString;

        // We will now replace every [SubQuery] in the $builtSQLString by iterating
        // through the $configSubQsKey array and replacing the [SubQuery] with the
        // actual SubQuery string from the $configSubQsKey array.
        if (isset($configSubQsKey) && is_array($configSubQsKey) && count($configSubQsKey) > 0) {
            foreach ($configSubQsKey as $subQueryKey => $subQueryValue) {
                // If the subquery value is not a string or empty, we error out
                if (!is_string_and_not_empty($subQueryValue)) {
                    cli_err_syntax_without_exit("Invalid SubQuery Value `$subQueryValue` in SQL Array `$handlerFile.php=>$fnName` for SubQuery Key `$subQueryKey`!");
                    cli_info("The SubQuery Value must be a Non-Empty String representing the SubQuery!");
                }
                // Replace the [SubQuery] with the actual SubQuery string
                $builtSQLString = str_replace($subQueryKey, $subQueryValue, $builtSQLString);
            }
        }
        // We finally remove all extra spaces and newlines from the built SQL string
        // and then add it to the converted SQL Array
        $builtSQLString = preg_replace('/\( /', '(', $builtSQLString);
        $builtSQLString = preg_replace('/ \)/', ')', $builtSQLString);
        $builtSQLString = preg_replace('/\s+/', ' ', $builtSQLString);
        $convertedSQLArray['sql'] = $builtSQLString;
        $convertedSQLArray['fields'] = $builtFieldsArray;

        // TODO: PARSING The <HYDRATION> Key and then setting final values!
        // IMPORTANT: This step CAN fail and still generate the SQL String!
        // So, it just gives a strong warning that the hydration failed meaning
        // its value in 'key' will be an empty array instead of actual hydration!
        $hydratedKey = [];
        if (isset($hydrationKey)) {
            // HERE PARSING ACTUALLY BEGINS WHEN IT IS A NON-EMPTY ARRAY!
            if (is_array($hydrationKey) && !empty($hydrationKey)) {
                // PARSE "SIMPLE" Hydration Mode
                if ($hydrationMode === 'simple' || $hydrationMode === 'simple|advanced') {
                    // IN "SIMPLE" Mode it is a single string so we try get it or throw
                    // warning+info but do NOT exit since it is not critical!
                    if (isset($hydrationKey)) {
                        // This is checked for each step so we can exit early
                        // and without having mass-use if / else everywhere!
                        $hydrationKeys = $hydrationKey ?? [];
                        $keepGoing = true;
                        $uniqueTbs = [];
                        foreach ($hydrationKeys as $hydrationKey) {
                            if ($keepGoing) {
                                if (!is_string_and_not_empty($hydrationKey) || !preg_match('/^([a-zA-Z0-9_]+)((=>){1}([a-zA-Z0-9_]+)(\(via:[a-zA-Z0-9_]+\))*)*$/i', $hydrationKey)) {
                                    $keepGoing = false;
                                    cli_warning_without_exit("Invalid Hydration Key `$hydrationKey` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                                    cli_info_without_exit("The Hydration Key must be a Non-Empty String representing the Hydration Key in the Format:\n`table` to Hydrate a Single Table OR\n`table=>table2` to Hydrate Two(2) Tables based on valid JOINING between them OR\n`table=>table2=>table3` to Hydrate Three(3) Tables based on valid JOINING between them\n(and so on to hydrate multiple joined tables)");
                                    cli_info_without_exit("Hydration will not be applied to the results of this query!");
                                }
                            }
                            if ($keepGoing) {
                                $hydrationKey = str_contains($hydrationKey, "=>") ? explode("=>", $hydrationKey) : [$hydrationKey];
                                foreach ($hydrationKey as $hydrationTb) {
                                    // Check for special case: `table(via:table2)` which is
                                    // used for pivoting tables in the hydration process!
                                    if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydrationTb, $matches)) {
                                        $tb1 = $matches[1];
                                        $viaTb = $matches[3];
                                        if (!in_array($tb1, $uniqueTbs, true)) {
                                            $uniqueTbs[] = $tb1;
                                        }
                                        if (!in_array($viaTb, $uniqueTbs, true)) {
                                            $uniqueTbs[] = $viaTb;
                                        }
                                    } elseif (!in_array($hydrationTb, $uniqueTbs, true)) {
                                        $uniqueTbs[] = $hydrationTb; // Add to unique tables
                                    }
                                }
                            }
                        }
                        // Must be same number of uniquely hydrated tables as
                        // selected tables so we can hydrate them properly!
                        if (count($uniqueTbs) !== 0 && count($selectedCols)) {
                            if (count($uniqueTbs) !== count($selectedCols)) {
                                $keepGoing = false;
                                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Fewer/More Tables:\n" . implode(",", quotify_elements($uniqueTbs)) . "\nthan were Selected using the `SELECT` Key:\n" . implode(",", quotify_elements(array_keys($selectedCols))) . "!");
                                cli_info_without_exit("Make sure You have same Number of Tables to Hydrate as in the `SELECT` Key UNLESS you are just Hydrating A Single Table!");
                                cli_info_without_exit("The Hydration Compilation Will be Skipped for this Query!");
                            }
                        }
                        // Here we do the main parsing/compiling of each Hydration Key Array Element!
                        // and finally adding it to the 'hydrate' => 'key' Key!
                        if ($keepGoing) {
                            foreach ($hydrationKeys as $hydrationKey) {
                                // We send the hydrationKey to the cli_parse_joined_tables_order function
                                // to parse the hydration key and get the hydrated key array. It will split
                                // on "=>" and also on special case of "table(via:table2)" to add to 'hydrate' => 'key'!
                                cli_parse_joined_tables_order(strtolower(trim($hydrationKey)), $hydratedKey, $keepGoing, $selectedCols);

                                // Then we perform tons of other checks to make sure no needed
                                // primary+forieng columns are missing so it can actually be used!
                                if ($keepGoing) {
                                    $hydrationKey = str_contains($hydrationKey, "=>") ? explode("=>", $hydrationKey) : [$hydrationKey];
                                }
                                // Check if fewer tables were selected than should be hydrated
                                // since "$tables_were_joined" = true means we have joined tables
                                // meaning we might have a valid case for "=>" aving been used!
                                if ($keepGoing) {
                                    // if ($tables_were_joined) {
                                    //     if (count($hydrationKey) !== count($joinedTables)) {
                                    //         $keepGoing = false;
                                    //         cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Fewer/More Tables:\n" . implode(",", quotify_elements($hydrationKey)) . " than were Joined using the `JOINS_ON` Key: " . implode(",", quotify_elements((!empty($joinedTables) ? $joinedTables : ["<No Joined Tables>"]))) . "!");
                                    //         cli_info_without_exit("You have Joined Tables in the `JOINS_ON` Key but the Hydration Key More/Fewer Table(s) Hydrate!");
                                    //         cli_info_without_exit("Make sure You have same Number of Tables to Hydrate as in the `JOINS_ON` Key UNLESS you are just Hydrating A Single Table!");
                                    //     }
                                    // } elseif (count($hydrationKey) > 1 && count($hydrationKey) !== count($joinedTables)) {
                                    //     $keepGoing = false;
                                    //     cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Fewer/More Tables:\n" . implode(",", quotify_elements($hydrationKey)) . " than were Joined using the `JOINS_ON` Key: " . implode(",", quotify_elements((!empty($joinedTables) ? $joinedTables : ["<No Joined Tables>"]))) . "!");
                                    //     cli_info_without_exit("You have Joined Tables in the `JOINS_ON` Key but the Hydration Key More/Fewer Table(s) Hydrate!");
                                    //     cli_info_without_exit("Make sure You have same Number of Tables to Hydrate as in the `JOINS_ON` Key UNLESS you are just Hydrating A Single Table!");
                                    // }
                                }
                                // Check that the tables to hydrate are actually valid ones
                                // by comparing against the keys in the array $selectedCols!
                                if ($keepGoing) {
                                    // Check for the Tables Joined if they were!
                                    if ($tables_were_joined) {
                                        foreach ($joinedTables as $joined) {
                                            if (!array_key_exists($joined, $selectedCols)) {
                                                $keepGoing = false;
                                                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$joined` which was NOT Selected in the `SELECT` Key!");
                                                cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                            }
                                            foreach ($hydrationKey as $hydTb) {
                                                // Check for special case: `table(via:table2)` which means checking two tables!
                                                if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                    $tb1 = $matches[1];
                                                    $viaTb = $matches[3];
                                                    if (!array_key_exists($tb1, $selectedCols)) {
                                                        $keepGoing = false;
                                                        cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$tb1` which was NOT Selected in the `SELECT` Key!");
                                                        cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                        cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                    } elseif (!array_key_exists($viaTb, $selectedCols)) {
                                                        $keepGoing = false;
                                                        cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$viaTb` which was NOT Selected in the `SELECT` Key!");
                                                        cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                        cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                    }
                                                } elseif (!array_key_exists($hydTb, $selectedCols)) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$hydTb` which was NOT Selected in the `SELECT` Key!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                    cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                }
                                            }
                                        }
                                    }
                                    // Otherwise just check the "=>"-splitted Tables in <HYDRATION> Key!
                                    else {
                                        foreach ($hydrationKey as $hydTb) {
                                            // Check for special case: `table(via:table2)` which means checking two tables!
                                            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                $tb1 = $matches[1];
                                                $viaTb = $matches[3];
                                                if (!array_key_exists($tb1, $selectedCols)) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$tb1` which was NOT Selected in the `SELECT` Key!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                    cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                } elseif (!array_key_exists($viaTb, $selectedCols)) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$viaTb` which was NOT Selected in the `SELECT` Key!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                    cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                }
                                            } elseif (!array_key_exists($hydTb, $selectedCols)) {
                                                $keepGoing = false;
                                                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table `$hydTb` which was NOT Selected in the `SELECT` Key!");
                                                cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key!");
                                                cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                            }
                                        }
                                    }
                                }
                                // Check that all tables to be hydrated have the "id" column
                                // by checking the $selectedCols array for the "id" key and in
                                // the case of joined tables, the "referencesTable_id" key!
                                // Since we have already validated all tables exist,
                                // we can directly iterate through $selectedCols starting
                                // with the first table and check if it has the "id" column!
                                // in any of its string values (the keys)
                                if ($keepGoing) {
                                    if ($tables_were_joined) {
                                        foreach ($joinedTables as $joined) {
                                            if (!isset($joinedTablesWithRef[$joined])) {
                                                continue; // Skipping tables we know cannot have the ref key we are looking for!
                                            } else {
                                                $refTbCol = $joinedTablesWithRef[$joined];
                                                $foundRef = false; // Reset for each table so all must pass!
                                                foreach ($selectedCols[$joined] as $refCol => $refVal) {
                                                    if ($refVal === $refTbCol) {
                                                        $foundRef = true; // Found the referencesTable_id Column!
                                                        break;
                                                    }
                                                }
                                                // One or more `referencesTable_id` NOT found so we "warn out"
                                                if (!$foundRef) {
                                                    $keepGoing = false;
                                                    cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Joined Tables without the necessary `OtherTableNameInPlural_id` Column!");
                                                    cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key and that they have the `referencesTable_id` Column!");
                                                    cli_info_without_exit("Valid & JOINED Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                                }
                                            }
                                        }

                                        // We also check all Tables to Hydrate all have the `id` Column
                                        foreach ($hydrationKey as $hydTb) {
                                            $foundId = false; // Reset for each table so all must pass!
                                            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                $tb1 = $matches[1];
                                                $viaTb = $matches[3];
                                                foreach ($selectedCols[$tb1] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                                $foundId = false;
                                                foreach ($selectedCols[$viaTb] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                foreach ($selectedCols[$hydTb] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        // One or more `id` NOT found so we "warn out"
                                        if (!$foundId) {
                                            $keepGoing = false;
                                            cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table(s) without the `id` Column!");
                                            cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key and that they have the `id` Column!");
                                            cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                        }
                                    }
                                    // Otherwise, we only need to check the `id` column for all the single Table!
                                    else {
                                        $foundId = false;
                                        foreach ($hydrationKey as $hydTb) {
                                            if (preg_match('/^([a-zA-Z0-9_]+){1}(\(via:([a-z-A-Z-0-9_]+)\)){1}$/i', $hydTb, $matches)) {
                                                $tb1 = $matches[1];
                                                $viaTb = $matches[3];
                                            } else {
                                                foreach ($selectedCols[$hydTb] as $colName => $colValue) {
                                                    if ($colValue === 'id') {
                                                        $foundId = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        // No `id` found so we warn and set $keepGoing to false
                                        if (!$foundId) {
                                            $keepGoing = false;
                                            cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set to Hydrate Table(s) without the `id` Column!");
                                            cli_info_without_exit("Make sure You have Selected the Table(s) to Hydrate in the `SELECT` Key and that they have the `id` Column!");
                                            cli_info_without_exit("Valid Tables to Hydrate are:\n" . implode(",\n", quotify_elements(array_keys($selectedCols))) . ".");
                                        }
                                    }
                                }
                            }
                        }
                        // Here it means we are all 100 % done with 0 errors!
                        if ($keepGoing) {
                            $convertedSQLArray['hydrate']['key'] = $hydratedKey;
                            cli_success_without_exit("[HYDRATION COMPILATION SUCCEEDED] Hydration Key(s) Parsed Successfully in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                        }
                    }
                }
                // PARSE "ADVANCED" Hydration Mode - Would probably support M:M:M:and_so_on?
                elseif ($hydrationMode === 'advanced') {
                    cli_warning_without_exit("<CURRENTLY NOT IMPLEMENTED YET IN FUNKPHP!>");
                    cli_info_without_exit("The `advanced` Hydration Mode is currently not implemented in FunkPHP!");
                    cli_info_without_exit("Hydration Will NOT be Compiled During This SELECT Query Compilation!");
                }
                // This should not happen, but if it does, we error out without exit
                else {
                    cli_err_syntax_without_exit("Invalid Hydration Mode `$hydrationMode` in SQL Array `$handlerFile.php=>$fnName` for SELECT Query!");
                    cli_info_without_exit("Valid Hydration Modes are: `simple`, `advanced` or `simple` is used by default when `simple|advanced` is set!");
                    cli_info_without_exit("Hydration Will NOT be Compiled During This SELECT Query Compilation!");
                }
            }
            // If not array, empty or just invalid data type,
            // we warn the user that no hydration parsing was made!
            else {
                cli_warning_without_exit("The `<HYDRATION>` Key in SQL Array `$handlerFile.php=>$fnName` for SELECT Query Type is set BUT Empty or NOT An Array!");
                cli_info_without_exit("Hydration Will NOT be Compiled During This SELECT Query Compilation!");
            }
        }
        $convertedSQLArray['hydrate']['mode'] = $hydrationMode;
        $convertedSQLArray['hydrate']['type'] = $hydrationType;


        // Report success and inform about ignored keys
        cli_success_without_exit("[SQL STRING BUILT] Built SQL String: `$builtSQLString`");
        if (is_array($ignoredKeys) && !empty($ignoredKeys)) {
            cli_warning_without_exit("The Following Found Keys were IGNORED for the SELECT Query Type:\n" . implode(",\n", quotify_elements($ignoredKeys)));
            cli_info_without_exit("Feel free to remove them from the SQL Array to not confuse Yourself!");
        }
    }
    // You should never reach this point, but if you do, we error out
    else {
        cli_err_syntax_without_exit("Invalid Config Key `<QUERY_TYPE>` value `$configQTKey` in SQL Array `$handlerFile.php=>$fnName`!");
        cli_info("Valid Query Types are:\n" . implode(",\n", quotify_elements($globalConfigRules['<QUERY_TYPE>'])) . ".");
    }

    // FINALLY AFTER ALL THAT: Return the converted SQL Array
    return $convertedSQLArray;
}

// PROBABLY DEPRECATED NOW
// Compiles a $DX SQL [] to an optmized SQL array that is returned within the same
// function that is used to validate the data. This is used to optimize the SQL process!
// VERY IMPORTANT WARNING: This function calls a function which uses eval() to parse the SQL file!!!
// PROBABLY DEPRECATED NOW
function cli_compile_dx_sql_to_optimized_sql()
{
    // Load globals, check for the argv[3] argument and prepare valid HandlerFile=>HandlerFunctionName
    global $dirs, $exactFiles, $settings, $delimiters, $argv, $dirs;
    if (!isset($argv[3]) || !is_string_and_not_empty($argv[3])) {
        cli_err("cli_compile_dx_sql_to_optimized_sql() expects a string as input for `\$argv[3]`!");
    }
    $handlerDir = $dirs['sql'] ?? "";
    [$handlerFile, $fnName] = get_valid_handlerVar_or_err_out($argv[3], "s");

    // Check that dir exists and is readable
    if (!dir_exists_is_readable_writable($handlerDir)) {
        cli_err("SQL Directory \"$handlerDir\" not found or non-readable/writable!");
    }
    // Then check file exists and is readable
    if (!file_exists_is_readable_writable($handlerDir . $handlerFile . ".php")) {
        cli_err("SQL Handler file \"$handlerFile.php\" not found in \"funkphp/sql/\" or not readable!");
    }

    // Prepare regex and find the function name in the file
    $fnNameRegex = '/^function (' . $fnName . ')\(\&\$c\)\s*\/\/ <[a-z_,\-0-9\*]*>\s*$.*?^};$/ims';
    $dxVarRegex = get_match_dx_function_regex();
    $dxReturnRegex = get_match_dx_return_regex();
    $fileContent = file_get_contents($handlerDir . $handlerFile . ".php");
    $matchedFn = preg_match($fnNameRegex, $fileContent, $matches);

    if (!$matchedFn) {
        cli_err("SQL Function \"$fnName\" not found in SQL Handler File \"funkphp/sql/$handlerFile.php\". Check for mispellings or typos?");
    }

    // We store found match and now try find the $DX variable in that part
    $matchedEntireFnName = $matches[0] ?? null;
    $matchedEntireFnCopy = $matchedEntireFnName;
    $matchedDX = preg_match($dxVarRegex, $matchedEntireFnName, $matches2);
    if (!$matchedDX) {
        cli_err_without_exit("The \"\$DX\" variable not found in SQL Function \"$fnName\" in SQL Handler File \"$handlerFile.php\".");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the SQL Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found. Only single quotes `['<DXarray>']` are allowed!");
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
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the SQL Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found.  Only single quotes `['<DXarray>']` are allowed!");
    }
    if ($evalCode === null) {
        cli_err_without_exit("The \"\$DX\" variable was found but could not be parsed as a valid PHP Array!");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the SQL Handler File!");
        cli_info("It must start as an array: `\$DX = ['<anything_inside_here>'];` or it will not be found.  Only single quotes `['<DXarray>']` are allowed!");
    }
    if (is_array($evalCode)) {
        cli_info_without_exit("Found \"\$DX\" variable parsed as a valid PHP Array!");
    }

    $matchedReturn = preg_match($dxReturnRegex, $matchedEntireFnName, $matches3);
    if (!$matchedReturn) {
        cli_err_without_exit("The \"return array();\" statement not found in SQL Function \"$fnName\" in SQL Handler File \"$handlerFile.php\".");
        cli_info_without_exit("Make sure it is intended using CMD+S or CTRL+S to autoformat the SQL Handler File!");
        cli_info("The last part of the array() - `);` - must be indented to the same level as the \"return array (\" part!");
    }
    $matchedReturnStmt = $matches3[0] ?? null;

    // This contains the optimized SQL Query which will then replace the "$matchedReturnStmt"
    // The function can error out on its own so we do not need to check for the return value!
    $optimizedSQLArray = cli_convert_simple_sql_query_to_optimized_sql($evalCode, $handlerFile, $fnName);

    // We validate the optimized SQL Query String by using the Prepared Statement that should not fail
    // If it fails, we will catch the exception and inform the Developer. It could fail due to actual
    // invalid SQL String Syntax or because of a mismatch between the Table Configuration in `tables.php`
    // and the actual Table in the MySQL DBMS (e.g. phpMyAdmin, Adminer, etc.) assuming it exists!
    $dbConnect =  cli_db_connect();
    $queryToTest = $optimizedSQLArray['sql'] ?? null;
    if ($queryToTest === null || !is_string_and_not_empty($queryToTest)) {
        cli_err_without_exit("The optimized SQL Query is Empty or NOT a Valid String in SQL Function \"$fnName\" in \"$handlerFile.php\".");
        cli_info("Check if indeed the `sql` key was provided from the returned Optimized SQL Array Variable?");
    }
    try {
        cli_info_without_exit("Testing the Optimized SQL Query String from SQL Function \"$fnName\" in \"$handlerFile.php\".");
        $stmt = $dbConnect->prepare($queryToTest);
    } catch (mysqli_sql_exception $e) {
        cli_err_without_exit("The Optimized SQL Query String FAILED during Statement Preparing (from SQL Function \"$fnName\" in \"$handlerFile.php\").");
        cli_info_without_exit("This means either\n1) Actual invalid SQL String Syntax that somehow slipped through the Compilation Stage when it shouldn't have, or that \n2) The Table and its configuration added in `config/tables.php` DOES NOT MATCH the Table with the same name in your local MySQL DBMS (e.g. phpMyAdmin, Adminer, etc.) assuming it exists!");

        // We show some guessing info based on what "$e->getMessage()" contains.

        if (is_string($e->getMessage()) && str_contains($e->getMessage(), "Unknown column") && str_contains($e->getMessage(), "field list")) {
            cli_info_without_exit("MAYBE: The MySQLi Error might indicate a missing/misspelled Column Name OR You might need a JOIN with the Table that contains that `unknown column`. Also check your `FROM` Key that it includes the Table you want queried!");
        } elseif (is_string($e->getMessage()) && str_contains($e->getMessage(), "Table ")  && str_contains($e->getMessage(), "doesn't exist")) {
            cli_info_without_exit("MAYBE: The MySQLi Error might indicate that the Table does not exist in your local MySQL DBMS (e.g. phpMyAdmin, Adminer, etc.) or that you have a typo in the Table Name in your SQL Array!");
        } elseif (is_string($e->getMessage()) && str_contains($e->getMessage(), "Unknown column") && str_contains($e->getMessage(), "in 'where clause'")) {
            cli_info_without_exit("MAYBE: The MySQLi Error might indicate a missing/misspelled Column Name in the `WHERE` Clause of your SQL Query String. Check your `WHERE` Key in the SQL Array!");
        } elseif (is_string($e->getMessage()) && str_contains($e->getMessage(), "Unknown column")) {
        }

        cli_info("INTERNAL MySQLi ERROR: \"" . $e->getMessage() . "\"");
    }
    cli_success_without_exit("[COMPILED SQL STRING VALIDATED IN DBMS] The SQL Query String in SQL Function \"$fnName\" in \"$handlerFile.php\" was Successfully Validated with 0 Errors When Sending it Prepared to the local MySQL DBMS!");
    cli_info_without_exit("Attempting adding the entire Optimized SQL Array as the returned value in SQL Function \"$fnName\" in \"$handlerFile.php\"!");
    // Convert the optimized SQL array to a string with ";\n" at the end
    $optimizedSQLArrayAsStringWithReturnStmt = "return " . var_export($optimizedSQLArray, true) . ";\n";

    // We will now use "$matchedEntireFnName" and replace the "$matchedReturnStmt" with the optimized SQL array
    $replaced = str_replace($matchedReturnStmt, $optimizedSQLArrayAsStringWithReturnStmt, $matchedEntireFnName);

    // We now replace the "$matchedEntireFnCopy" part of the fileContent with the new $replaced string
    $newFileContent = str_replace($matchedEntireFnCopy, $replaced, $fileContent);

    // Output the file to replace the original file
    $result = file_put_contents($handlerDir . $handlerFile . ".php", $newFileContent);
    if ($result === false) {
        cli_err("FAILED compiling SQL Query to Optimized SQL in SQL Function \"$fnName\" in \"$handlerFile.php\". Permissions issue?");
    } else {
        cli_success_without_exit("[ENTIRE SQL ARRAY COMPILED] SUCCESSFULLY COMPILED SQL Query to Optimized SQL in SQL Function \"$fnName\" in \"funkphp/sql/$handlerFile.php\".");
        cli_info("IMPORTANT: Open it in an IDE and press CMD+S or CTRL+S to autoformat the SQL Handler File again!");
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
        // When no match for root node
        if (!isset($currentNode['/'])) {
            return null;
        }
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
            $noMatchIn .= "DEVELOPER_ROUTES(funkphp/config/routes.php)";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES(funkphp/_internals/compiled/troute_route.php)";
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
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/config/routes.php) must be a non-empty array!");
    }
    if (!isset($singleRouteRoutesFileArray['ROUTES'])) {
        cli_err_syntax("[cli_rebuild_single_routes_file] Single Route Routes File Array (funkphp/config/routes.php) must start with a 'ROUTES' key!");
    }
    // Check that dir exist, is writable and is a directory
    if (!is_dir($dirs['routes']) || !is_writable($dirs['routes'])) {
        cli_err("[cli_rebuild_single_routes_file] Directory for `routes.php` (funkphp/config/) must be a Writable Directory. Check it exists and/or its File Permission!");
    }
    // Check that if file exists, it can be overwritten
    if (file_exists($exactFiles['single_routes']) && !is_writable($exactFiles['single_routes'])) {
        cli_err("[cli_rebuild_single_routes_file] Routes file (funkphp/config/routes.php) must be writable. It is not!");
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
                    // And update param as next nested key and/or move to next node
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
    // Check if the compiled route is empty
    if (!is_array($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }
    if (empty($compiledTrie)) {
        cli_err_syntax("Compiled Routes Must Be A Non-Empty Array!");
    }
    $result = null;
    $result = file_put_contents(FUNKPHP_INTERNALS_COMPILED_DIR .  "/troute_route.php", "<?php\nreturn " . cli_convert_array_to_simple_syntax($compiledTrie));
    if ($result === false) {
        cli_err("Failed to write Compiled Routes to file: \"funkphp/_internals/compiled/troute_route.php\". Check File Permissions?");
    } else {
        cli_success_without_exit("Successfully wrote Compiled Routes to file: \"funkphp/_internals/compiled/troute_route.php\"!");
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
    // Prepare what folders to loop through and create if they don't exist!
    $folderBase = PROJECT_DIR;
    $folders = [
        "$folderBase",
        "$folderBase/BACKUPS/",
        "$folderBase/BACKUPS/_FINAL_BACKUPS/",
        "$folderBase/BACKUPS/compiled/",
        "$folderBase/BACKUPS/pipeline/",
        "$folderBase/BACKUPS/pipeline/request",
        "$folderBase/BACKUPS/pipeline/post-request",
        "$folderBase/BACKUPS/middlewares/",
        "$folderBase/BACKUPS/page/",
        "$folderBase/BACKUPS/config/",
        "$folderBase/BACKUPS/schema/",
        "$folderBase/BACKUPS/sql/",
        "$folderBase/BACKUPS/template/",
        "$folderBase/BACKUPS/test/",
        "$folderBase/BACKUPS/validation/",
        "$folderBase/batteries/",
        "$folderBase/batteries/middlewares/",
        "$folderBase/batteries/pipeline/",
        "$folderBase/batteries/pipeline/post-request/",
        "$folderBase/batteries/pipeline/request/",
        "$folderBase/cli/",
        "$folderBase/funkphp/",
        "$folderBase/funkphp/_internals/",
        "$folderBase/funkphp/_internals/compiled/",
        "$folderBase/funkphp/_internals/functions/",
        "$folderBase/funkphp/cached/",
        "$folderBase/funkphp/cached/pages/",
        "$folderBase/funkphp/cached/json/",
        "$folderBase/funkphp/cached/files/",
        "$folderBase/funkphp/config/",
        "$folderBase/funkphp/middlewares/",
        "$folderBase/funkphp/pipeline/",
        "$folderBase/funkphp/pipeline/request",
        "$folderBase/funkphp/pipeline/post-request",
        "$folderBase/funkphp/page/",
        "$folderBase/funkphp/page/complete/",
        "$folderBase/funkphp/page/components/",
        "$folderBase/funkphp/page/partials/",
        "$folderBase/funkphp/routes/",
        "$folderBase/funkphp/sql/",
        "$folderBase/funkphp/validation/",
        "$folderBase/gui/",
        "$folderBase/public_html/",
        "$folderBase/public_html/css/",
        "$folderBase/public_html/fonts/",
        "$folderBase/public_html/images/",
        "$folderBase/public_html/js/",
        "$folderBase/schema/",
        "$folderBase/template/",
        "$folderBase/test/",
    ];

    // Prepare default files that doesn't exist if certain folders don't exist
    $defaultFiles = [
        "$folderBase/funkphp/_internals/compiled/troute_route.php",
        "$folderBase/funkphp/pipeline/pipeline.php",
        "$folderBase/funkphp/routes/routes.php",
        "$folderBase/public_html/.htaccess",
        "$folderBase/cli/.htaccess",
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
    $date = date("Y-m-d H:i:s");
    foreach ($defaultFiles as $file) {
        if (!file_exists($file)) {
            // Recreate default files based on type ("troute", "middleware routes" or "single routes")
            if (str_contains($file, "troute_route")) {
                file_put_contents($file, "<?php\n// This file was recreated by FunkCLI!\nreturn [];\n?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } elseif (str_contains($file, "routes")) {
                file_put_contents($file, "<?php\n// Routes.php - FunkPHP Framework | FunkCLI recreated it $date\nreturn [
            'ROUTES' => ['GET' =>[],'POST' =>[],'PUT' =>[],'DELETE' =>[],'PATCH' =>[],],];?>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } else if (str_contains($file, "pipeline")) {
                file_put_contents($file, "<?php\n// Pipeline.php - FunkPHP Framework | FunkCLI recreated it $date\nreturn  [
            'pipeline' =>['request' => [
            0 => 'pl_https_redirect',
            1 => 'pl_run_ini_sets',
            2 => 'pl_set_session_cookie_params',
            3 => 'pl_db_connect',
            4 => 'pl_headers_set',
            5 => 'pl_headers_remove',
            6 => 'pl_start_session',
            7 => 'pl_prepare_uri',
            8 => 'pl_match_denied_exact_ips',
            9 => 'pl_match_denied_methods',
            10 => 'pl_match_denied_uas',
            11 => 'pl_match_route',
            12 => 'pl_run_matched_route_middlewares',
            12 => 'pl_run_matched_route_keys',
            ],'post-request' => [],],'no_match' => [],];");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } else if (str_contains($file, "public_html/.htaccess")) {
                file_put_contents($file, "# This file was recreated by FunkCLI!\nRewriteEngine On\nRewriteRule ^([^\.]+)$ $1.php [NC]\nRewriteRule ^.*$ index.php [L,QSA]");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            } else if (str_contains($file, "cli/.htaccess")) {
                file_put_contents($file, "<Files \"funk\">\nSetHandler application/x-httpd-php\n</Files>");
                echo "\033[32m[FunkCLI - SUCCESS]: Recreated file: $file\n\033[0m";
                continue;
            }
        }
    }
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
        if ($fileString === "page") {
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_BACKUPS']) {
            }
            if ($settings['ALWAYS_BACKUP_IN']['PAGES_IN_FINAL_BACKUPS']) {
            }
            continue;
        }
        if ($fileString === "handler") {
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
        if ($fileString === "template") {
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
        if ($fileString === "test") {
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
        if ($fileString === "validation") {
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
        cli_success_without_exit("Rebuilt Route file \"funkphp/routes/routes.php\"!");
    } else {
        cli_err("FAILED to rebuild Route file \"funkphp/routes/routes.php\". File permissions issue?");
    }
    $compiledRouteRoutes = cli_build_compiled_routes($singleRoutesRootArray['ROUTES'], $singleRoutesRootArray['ROUTES']);
    cli_output_compiled_routes($compiledRouteRoutes, "troute_route");
}

// Create a Validation File and/or Handler (not Route-dependent!)
function cli_create_validation_file_and_or_handler()
{
    // Get valid handlerFile=>fnName or error out
    global $argv, $settings, $dirs, $exactFiles, $mysqlDataTypesFile, $tablesAndRelationshipsFile;
    [$handlerFile, $fnName] = get_handler_and_fn_from_argv4_or_err_out("v", 3);

    // Prepare dirs and strings
    $handlersDir =  $dirs['validations'];
    $handlerDirPath = "validation";
    $date = date("Y-m-d H:i:s");
    $outputHandlerRoute = null;
    $handlerBaseFullStringRow1 = "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
    $handlerBaseFullStringRow2 = "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
    $handlerBaseFullStringRow3 = "\n\tif (function_exists(\$full)) {";
    $handlerBaseFullStringRow4 = "\n\t\treturn \$full(\$c);";
    $handlerBaseFullStringRow5 = "\n\t} else {";
    $handlerBaseFullStringRow6 = "\n\t\t\$c['err']['VALIDATION'][] = 'Validation Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the Validation Handler File?';";
    $handlerBaseFullStringRow7 = "\n\t\treturn null;";
    $handlerBaseFullStringRow8 = "\n\t}";
    $handlerBaseFullString =
        $handlerBaseFullStringRow1
        . $handlerBaseFullStringRow2 . $handlerBaseFullStringRow3
        . $handlerBaseFullStringRow4 . $handlerBaseFullStringRow5
        . $handlerBaseFullStringRow6 . $handlerBaseFullStringRow7
        . $handlerBaseFullStringRow8;


    // Default DXPart Value when no tables are provided
    $DXPART = "\n\t\t'<CONFIG>' => '',\n\t\t'table_col1_name' => 'string|required|nullable|between:3,50',\n\t\t'table_col2_email' => 'email|required|between:6,50',\n\t\t'table_col3_age' => 'integer|required|between:18,100',\n\t\t'table_col4_length' => 'float|nullable|decimals:2',";

    // When tables ARE provided, we try to parse and use them instead as default $DXPART Value!
    $usedTables = $argv[4] ?? "";
    if (isset($argv[4])) {
        if (!is_string($argv[4]) || empty(trim($argv[4]))) {
            cli_err_syntax("Included Tables for the created Validation File=>Function must be a Non-Empty String!");
        }
        $argv[4] = strtolower($argv[4]);
        if (!preg_match('/([a-z_][a-z_0-9]+\*?[0-9]*,?)+$/i', $argv[4])) {
            cli_err_syntax_without_exit("Included Tables for the created Validation File=>Function must be a valid String with Table Names and Optional Numbers (e.g. \"table1,table2*2,table3\").");
            cli_info("Example: \"table1,table2*2,table3\" will create rules for `table1` as a single object, `table2` as an array with 2 elements, and `table3` as a single object just like `table1`!");
        }

        // Prepare the tables string for the
        // DXPART. Split on "," if it exists
        $times = [];
        $tables = null;
        $processTables = str_contains($argv[4], ',') ? explode(',', $argv[4])  : [$argv[4]];

        // Extract the number from "*" if it exists and add foreach table to the $times array
        foreach ($processTables as $table) {
            if (str_contains($table, '*')) {
                $parts = explode('*', $table);
                if (count($parts) !== 2 || !is_numeric($parts[1]) || (int)$parts[1] <= 0) {
                    cli_err_syntax_without_exit("Invalid Table Format: \"$table\". Use \"table_name*integer\" or just \"table_name\" for each Table!");
                    cli_info("Even if you do not know the Array Number, just specify a very high integer to prevent infinite loops during Validation!");
                }
                // do not allow duplicates
                if (array_key_exists($parts[0], $times)) {
                    cli_err_syntax("Table \"$parts[0]\" already added. Only use one Table once!");
                }
                $times[$parts[0]] = (int)$parts[1];
            }
            // Default to 1 if no number is specified (as in:`table_name`)
            else {
                if (array_key_exists($table, $times)) {
                    cli_err_syntax("Table \"$table\" already added. Only use one Table once!");
                }
                $times[$table] = 1;
            }
        }

        // We now load the Tables.php file and grab the keys from $times
        // to validate that all the tables exist in the Tables.php file!
        $tables =  $tablesAndRelationshipsFile ?? null;
        $types = $mysqlDataTypesFile ?? null;
        if ($tables === null || $types === null) {
            cli_err_syntax("`Tables.php` or `MySQLDataTypes.php` File not found! Please check your `funkphp/config/tables.php` & `funkphp/config/VALID_MYSQL_DATATYPES.php` Files!");
        }
        foreach ($times as $table => $count) {
            if (!array_key_exists($table, $tables['tables'])) {
                cli_err_syntax("Table \"$table\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
            }
        }

        // their subkeys are actual columns who contain info about what apprioriate rules
        // they should have! Prepare the DXPART string for the validation limiter
        $currDXPart = "";
        $currTable = null;
        $currTablePrefix = "";
        $entireDXPART = "";

        // We now iterate through each table and its count and we use $tbName to find
        // the correct Table in the Tables.php file and the $tbCount to know whether it is
        // an array (e.g. `table_name*2`) or a single object (e.g. `table_name`) of the table.
        foreach ($times as $tbName => $tbCount) {
            $passwordColNameTemp = "";
            $currTable = $tables['tables'][$tbName] ?? null;
            if ($currTable === null) {
                cli_err_syntax("Table \"$tbName\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
            }
            // Set correct prefix for the table based on its count. When array/list we also add the first
            // part of the $DXPART which indicates it is a list of items with the table prefix with a
            // specific count of elements that all other fields (keys) from the table must include!
            if ($tbCount > 1) {
                $currTablePrefix = $tbName . ".*.";
                $entireDXPART .= wrappify_arrowed_string("$tbName.*",  "list|count:$tbCount|required");
            } else {
                $currTablePrefix = $tbName . ".";
            }
            // Now we loop through the selected Table and its keys where each key is the column name
            // which is then the "fieldName" in the DXPART string.
            foreach ($currTable as $key => $subKey) {
                $currDXPart = "";
                // We skip the primary key 'id' column so this must be added manually by Developer!
                // If it s a Foreign Key or Primary Key, we skip it as well for now!
                if ($key === 'id' && isset($subKey['primary_key'])) {
                    continue;
                }

                // We set some possible default rules to insert into the current DXPART
                $dataType = $subKey['type'] ?? null;
                $nullable = isset($subKey['nullable']) && $subKey['nullable'] === true ?
                    "required|nullable|" : "required|";
                $between = isset($subKey['value']) && !is_array($subKey['value']) ? "between:1," . $subKey['value'] . "|" : "between:<MIN>,<MAX>|";
                if (($dataType === 'SET' || $dataType === 'ENUM')) {
                    $between = "";
                }
                $unique = isset($subKey['unique']) && $subKey['unique'] === true ?
                    "unique:$tbName,$key|" : "";
                $exists =  isset($subKey['references']) && isset($subKey['references_column']) ?
                    "exists:" . $subKey['references'] . "," . $subKey['references_column'] . "|" : "";
                $anyValues = "";

                // First check is guessing the data type for the current column based on its 'type'
                // and its key name. For example if it contains 'email' we assume it is an email if
                // the 'type' is a string within the $types variable which contains all possible valid
                // MySQL data types.
                // It is considered valid `email` type if it s a string MySQL data type and
                // the key name contains 'email' in it.
                if (
                    str_contains($key, "email")
                    && isset($types['STRINGS'][$dataType])
                ) {
                    $currDXPart .= "email|";
                }
                // It is considered valid `password` if it is a string MySQL data type and
                // the key name contains 'password' in it while NOT containing "confirm" since
                // that should be handled separately ("password" "confirm" field that is).
                elseif (
                    str_contains($key, "password")
                    && !str_contains($key, "confirm")
                    && isset($types['STRINGS'][$dataType])
                ) {
                    $currDXPart .= "password|";
                    // Store the password column name temporarily to be binded to the
                    // possibly "confirm" field later on if they exist in same table.
                    $passwordColNameTemp = $key;
                }
                // It is considered valid `password_confirm` if it is a string MySQL data
                // typoe and the key name contains both 'password' and 'confirm' in it!
                elseif (
                    str_contains($key, "password")
                    && str_contains($key, "confirm")
                    && isset($types['STRINGS'][$dataType])
                ) {
                    if (!empty($passwordColNameTemp)) {
                        $currDXPart .= "password_confirm:$passwordColNameTemp|";
                    } else {
                        $currDXPart .= "password_confirm:<UNKNOWN_PLEASE_USE_PASSWORD_COLUMN_NAME_HERE>|";
                    }
                }
                // It is considered valid `string` if it is a string MySQL data type
                elseif (isset($types['STRINGS'][$dataType])) {
                    $currDXPart .= "string|";
                }
                // It is considered valid `integer`
                elseif (isset($types['INTS'][$dataType])) {
                    $currDXPart .= "integer|";
                }
                // It is considered valid `float`
                elseif (isset($types['FLOATS'][$dataType])) {
                    $currDXPart .= "float|";
                }
                // It is considered valid `datetimes` datatype
                elseif (isset($types['DATETIMES'][$dataType])) {
                    $currDXPart .= "date|";
                }
                // It is considered valid `blobs` datatype
                elseif (isset($types['BLOBS'][$dataType])) {
                    $currDXPart .= "string|";
                }
                // When it is boolean
                elseif ($dataType === 'BOOLEAN') {
                    $currDXPart .= "boolean|";
                }
                // When it is ENUM or SET
                elseif ($dataType === 'SET' || $dataType === 'ENUM') {
                    $currDXPart .= strtolower($dataType) . "|";
                    $anyValues = is_array($subKey['value']) ?
                        "any_of_these_values:" . implode(',', $subKey['value']) . "|" : "";
                }
                // UNKNOWN DATA TYPE
                else {
                    $currDXPart .= "<!UNKNOWN_DATA_TYPE_CHOOSE_ONE_FOR_THIS_TABLE_COLUMN!>|";
                }

                // We now add $nullable, $between and $unique to the current DXPart if they are not empty strings
                $currDXPart .= $nullable;
                if (!empty($between)) {
                    $currDXPart .= $between;
                }
                if (!empty($unique)) {
                    $currDXPart .= $unique;
                }
                if (!empty($exists)) {
                    $currDXPart .= $exists;
                }
                if (!empty($anyValues)) {
                    $currDXPart .= $anyValues;
                }

                // FINALLY FOR EACH ITERATION ADD THe $currDXPart to the $entireDXPART
                // and then reset the $currDXPart to an empty string for the next iteration.
                // But we remove the trailing "|" if it exists.
                if (str_ends_with($currDXPart, "|")) {
                    $currDXPart = substr($currDXPart, 0, -1);
                }
                $entireDXPART .= wrappify_arrowed_string($currTablePrefix . $key, $currDXPart);
            }
        }
        $DXPART = "\n\t\t'<CONFIG>' => '',\n\t\t" . $entireDXPART;
    }

    // Prepare the validation limiter strings and return function regex
    $validationLimiterStrings = "\t// FunkCLI created $date! Keep Closing Curly Bracket on its\n\t// own new line without indentation no comment right after it!\n\t// Run the command `php funkcli compile v $handlerFile=>$fnName`\n\t// to get optimized version in return statement below it!\n\t\$DX = [$DXPART\n\t];\n\n\treturn array([]);";
    $returnFunctionRegex = '/^(return function)\s*\(&\$c, \$handler\s*=\s*.+$.*?^};/ims';

    // If dir not found or not readable/writable, we exit
    if (!dir_exists_is_readable_writable($handlersDir)) {
        cli_err("[cli_create_validation_file_and_or_handler]: \"$handlersDir\" not found or non-readable/writable!");
    }

    // When file does not exist we create it
    if (!file_exists($handlersDir . $handlerFile . ".php")) {
        $outputHandlerRoute = file_put_contents(
            $handlersDir . $handlerFile . ".php",
            "<?php\n\nnamespace FunkPHP\Validation\\$handlerFile;\n// Validation Handler File - Created in FunkCLI on $date!\n// Write your Validation Rules in the\n// \$DX variable and then run the command\n// `php funkcli compile v $handlerFile=>\$function_name`\n// to get the optimized version below it!\n// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!\n\nfunction $fnName(&\$c) // <$usedTables>\n{\n$validationLimiterStrings\n};\n\nreturn function (&\$c, \$handler = \"$fnName\") { $handlerBaseFullString \n};"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Added Validation Handler \"funkphp/$handlerDirPath/$handlerFile.php\" with Validation Function \"$fnName\" in \"funkphp/validation/$handlerFile.php\"!");
            return;
        } else {
            cli_err("[cli_create_validation_file_and_or_handler]: FAILED to create Validation Handler \"funkphp/$handlerDirPath/$handlerFile.php\". File permissions issue?");
        }
    }

    // When file does exist we check if the function name is already used
    if (file_exists($handlersDir . $handlerFile . ".php")) {
        // If file is NOT readable/writable, we exit
        if (!file_exists_is_readable_writable($handlersDir . $handlerFile . ".php")) {
            cli_err("[cli_create_validation_file_and_or_handler]: \"$handlersDir/$handlerFile.php\" not found or non-readable/writable!");
        }
        $fileContent = file_get_contents($handlersDir . $handlerFile . ".php");

        // Now we check if the function name is already used
        $matchFnRegex = '/^function (' . $fnName . ')\(\&\$c\)\s*\/\/\s*<[a-z_,\-0-9\*]*>\s*$.*?^};$/ims';
        if (preg_match($matchFnRegex, $fileContent, $matches)) {
            cli_err("[cli_create_validation_file_and_or_handler]: \"$fnName\" - Function name already exists in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        } else {
            cli_info_without_exit("Function \"$fnName\" available in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        }

        // Here we match the return function block to insert the new function that is not already used
        if (preg_match($returnFunctionRegex, $fileContent, $matches, PREG_OFFSET_CAPTURE)) {

            // $matches[0] now contains an array: [matched string, offset]
            $matchedString = $matches[0][0]; // The actual string that matched
            $matchOffset = $matches[0][1];   // The byte offset of the match in $fileContent

            // Construct the string for the *new* function definition only.
            // DO NOT include $matches[0] in this string; we will insert it separately.
            // Assuming $validationLimiterStrings and $date are defined elsewhere
            $newFunctionString = "\nfunction {$fnName}(&\$c) // <$usedTables>\n{\n{$validationLimiterStrings}\n};\n\n";

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
                cli_success_without_exit("Added Validation Function \"$fnName\" to existing Validation Handler File \"funkphp/$handlerDirPath/$handlerFile.php\"!");
                return;
            } else {
                cli_err("[cli_create_validation_file_and_or_handler]: FAILED to create Validation \"$fnName\" Function in \"funkphp/$handlerDirPath/$handlerFile.php\". File permissions issue?");
            }
        } else {
            // The 'return function' block was not found - the file structure is invalid
            cli_err_without_exit("[cli_create_validation_file_and_or_handler]: Invalid handler file structure.");
            cli_err("Could not find the 'return function(...) {...};' block in \"funkphp/$handlerDirPath/$handlerFile.php\".");
        }
    }
}

// Create a SQL File and/or Handler (not Route-dependent!)
function cli_create_sql_file_and_or_handler()
{
    // Get valid handlerFile=>fnName or error out
    global $argv, $settings, $dirs, $exactFiles, $mysqlDataTypesFile, $tablesAndRelationshipsFile;
    [$handlerFile, $fnName] = get_handler_and_fn_from_argv4_or_err_out("s", 3, "php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    // Prepare dirs and strings
    $handlersDir =  $dirs['sql'];
    $handlerDirPath = "sql";
    $date = date("Y-m-d H:i:s");
    $outputHandlerRoute = null;
    $handlerBaseFullStringRow1 = "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
    $handlerBaseFullStringRow2 = "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
    $handlerBaseFullStringRow3 = "\n\tif (function_exists(\$full)) {";
    $handlerBaseFullStringRow4 = "\n\t\treturn \$full(\$c);";
    $handlerBaseFullStringRow5 = "\n\t} else {";
    $handlerBaseFullStringRow6 = "\n\t\t\$c['err']['SQL']['$handlerFile'][] = 'SQL Handler Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist in the File as callable function?';";
    $handlerBaseFullStringRow7 = "\n\t\treturn null;";
    $handlerBaseFullStringRow8 = "\n\t}";
    $handlerBaseFullString =
        $handlerBaseFullStringRow1
        . $handlerBaseFullStringRow2 . $handlerBaseFullStringRow3
        . $handlerBaseFullStringRow4 . $handlerBaseFullStringRow5
        . $handlerBaseFullStringRow6 . $handlerBaseFullStringRow7
        . $handlerBaseFullStringRow8;

    // Validate tables are provided and then lowercase them
    if (!isset($argv[5]) || !is_string($argv[5]) || empty(trim($argv[5]))) {
        cli_err_syntax_without_exit("Included Tables for the created SQL File=>Function must be a Non-Empty String!");
        cli_info_without_exit("Example: \"table1\" or \"table1,table2,table3\" first one will use only one table where the latter one will use all three provided tables!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }
    $argv[5] = strtolower($argv[5]);

    // Load Tables.php file and validate that it exists, is array and
    // its keys are valid ('tables', 'relationships' & 'mappings')!
    // and then split $argv[4] on "," (if it exists) to get the tables
    // and validate all the provided tables exist in the Tables.php file!
    $tables = $tablesAndRelationshipsFile ?? null;
    if ($tables === null || !is_array($tables)) {
        cli_err_syntax_without_exit("`Tables.php` File not found! Please check your `funkphp/config/tables.php` File!");
        cli_info_without_exit("Make sure you have a valid `Tables.php` File in `funkphp/config/` directory!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }
    if (!isset($tables['tables']) || !is_array($tables['tables']) || empty($tables['tables'])) {
        cli_err_syntax_without_exit("`Tables.php` File does not contain valid `tables` key! Please check your `funkphp/config/tables.php` File!");
        cli_info_without_exit("Your `tables` array key in your `Tables.php` File in `funkphp/config/` directory CANNOT be empty and should have at least one table!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }
    if (!isset($tables['relationships']) || !is_array($tables['relationships'])) {
        cli_err_syntax_without_exit("`Tables.php` File does not contain valid `relationships` key! Please check your `funkphp/config/tables.php` File!");
        cli_info_without_exit("Your `relationships` array key in your `Tables.php` File in `funkphp/config/` directory must exist and CAN be empty!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }
    if (!isset($tables['mappings']) || !is_array($tables['mappings'])) {
        cli_err_syntax_without_exit("`Tables.php` File does not contain valid `mappings` key! Please check your `funkphp/config/tables.php` File!");
        cli_info_without_exit("Your `mappings` array key in your `Tables.php` File in `funkphp/config/` directory must exist and CAN be empty!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }
    // Split on "," if it exists, otherwise just use the single table
    $processTables = str_contains($argv[5], ',') ? explode(',', $argv[5])  : [$argv[5]];

    // Validate that all the provided tables exist in the
    // Tables.php file are valid named and not duplicates!
    $tbs = [];
    foreach ($processTables as $table) {
        if (!preg_match('/^[a-z_][a-z_0-9]*$/i', $table)) {
            cli_err_syntax_without_exit("Invalid Table Name: \"$table\". Use only alphanumeric characters and underscores!");
            cli_info_without_exit("Example: \"table1\" or \"table_1\" or \"table_1_2\" - do not use spaces or special characters!");
            cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
        }
        if (!array_key_exists($table, $tables['tables'])) {
            cli_err_syntax_without_exit("Table \"$table\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
            cli_info_without_exit("Make sure you have a valid `Tables.php` File in `funkphp/config/` directory with at least one table in the ['tables'] Array!");
            cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
        }
        if (array_key_exists($table, $tbs)) {
            cli_err_syntax_without_exit("Table \"$table\" already added. Only use one Table once!");
            cli_info_without_exit("Make sure you have a valid `Tables.php` File in `funkphp/config/` directory with at least one table in the ['tables'] Array!");
            cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
        }
        $tbs[$table]['cols'] = $tables['tables'][$table];
    }

    // Validate Query Type has been provided, then uppercase it
    // & validate it that it is one of the available query types!
    if (!isset($argv[4]) || !is_string($argv[4]) || empty(trim($argv[4]))) {
        cli_err_syntax_without_exit("Query Type for the created SQL File=>Function must be a Non-Empty String!");
        cli_info_without_exit("Available Query Types (case-insensitive): 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'SELECT_DISTINCT', 'SELECT_INTO', or 'SELECT_TOP' - only provide ONE of these!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }
    // Uppercase the query type and validate it against the available query types after transoforming shorthands
    // to full query types (s=SELECT, i=INSERT, u=UPDATE, d=DELETE, sd=SELECT_DISTINCT, si=SELECT_INTO, st=SELECT_TOP)
    $queryType = strtoupper($argv[4]);
    if (str_starts_with($queryType, 'S')) {
        $queryType = 'SELECT';
    } elseif (str_starts_with($queryType, 'I')) {
        $queryType = 'INSERT';
    } elseif (str_starts_with($queryType, 'U')) {
        $queryType = 'UPDATE';
    } elseif (str_starts_with($queryType, 'D')) {
        $queryType = 'DELETE';
    } elseif (str_starts_with($queryType, 'SD')) {
        $queryType = 'SELECT_DISTINCT';
    } elseif (str_starts_with($queryType, 'SI')) {
        $queryType = 'SELECT_INTO';
    }
    $availableQueryTypes = [
        'SELECT',
        'INSERT',
        'UPDATE',
        'DELETE',
        'SELECT_DISTINCT',
        'SELECT_INTO',
    ];
    if (!in_array($queryType, $availableQueryTypes)) {
        cli_err_syntax_without_exit("Invalid Query Type: \"$queryType\". Available Query Types: " . implode(', ', quotify_elements($availableQueryTypes)) . ".");
        cli_info_without_exit("Pick one of those as the fith argument in the FunkCLI command to create a SQL Handler File and/or Function!");
        cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1,table2,table3` (at least one Table must be provided)!");
    }

    // For queryType "INSERT", "UPDATE", "DELETE" we ONLY allow one table to be provided!
    if (count($tbs) > 1) {
        if (in_array($queryType, ['INSERT', 'UPDATE', 'DELETE'])) {
            cli_err_syntax_without_exit("For Query Type \"$queryType\" you can ONLY provide ONE Table! Provided Tables: " . implode(', ', quotify_elements(array_keys($tbs))));
            cli_info_without_exit("Please provide ONLY ONE Table for Query Types `INSERT`, `UPDATE` or `DELETE` in the FunkCLI command!");
            cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1` (at least one Table must be provided)!");
        }
    }

    // If optional sixth argument is provided and it is a string with 'exclude_default', we will loop
    // through the Table and remove any cols that has the default key that is NOT null and NOT empty.
    // since this optional argument is used to allow for default values to be used instead of provided ones!
    // This only applies to Query Type 'INSERT' since it is the only one that allows for default values to be used!
    if (in_array($queryType, ['INSERT'])) {
        if (isset($argv[6]) && is_string($argv[6]) && !empty(trim($argv[6])) && strtolower($argv[6]) === 'exclude_default') {
            foreach ($tbs as $tbName => $tbData) {
                foreach ($tbData['cols'] as $colName => $colData) {
                    // If the column has a default value that is not null or empty, we remove it from the array
                    if (isset($colData['default']) && !is_null($colData['default']) && $colData['default'] !== '') {
                        unset($tbs[$tbName]['cols'][$colName]);
                    }
                }
            }
        }
    }

    // Default values added to the $DXPART variable
    $hydrationModeStr = "\n\t\t\t'<HYDRATION_MODE>' => 'simple|advanced', // Pick one or `simple` is used by default! (leave empty or remove line if not used!)";
    $hydrationTypeStr = "\n\t\t\t'<HYDRATION_TYPE>' => 'array|object', // Pick one or `array` is used by default! (leave empty or remove line if not used!)";
    $chosenQueryType = "'<CONFIG>' =>[\n\t\t\t'<QUERY_TYPE>' => '$queryType',\n\t\t\t'<TABLES>' => ['" . implode('\',\'', array_keys($tbs)) . "']," . ($queryType === 'SELECT' ? $hydrationModeStr : "") . ($queryType === 'SELECT' ? $hydrationTypeStr : "");
    $subQueriesEmpty = ($queryType === 'INSERT' || $queryType === 'UPDATE' || $queryType === 'DELETE') ? "" : "\t\t\t\t'[subquery_example_1]' => 'SELECT COUNT(*)',\n\t\t\t\t'[subquery_example_2]' => '(WHERE SELECT *)'";
    $subQueries = "\n\t\t\t'[SUBQUERIES]' => [\n$subQueriesEmpty\t\t\t]\n\t\t],";
    $DXPART = $chosenQueryType . $subQueries;
    $queryTypePart = "";

    // TODO: Fix all below statements!
    // When 'INSERT'
    if ($queryType === 'INSERT') {
        // Remove the 'id' column from the array since that is auto-incremented
        $tbsColsExceptId = array_keys($tbs[array_key_first($tbs)]['cols']);
        $tbName = key($tbs);
        array_shift($tbsColsExceptId);
        $valCols = $tbsColsExceptId;
        $tbsColsExceptId = implode(',', $tbsColsExceptId);
        $tbsColsExceptId = key($tbs) . ':' . $tbsColsExceptId;
        $queryTypePart .= "\n\t\t'INSERT_INTO' => '$tbsColsExceptId',";
        $bindedValidatedData = "\n\t\t'<MATCHED_FIELDS>' => [// What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey) \n\t\t\t\t'" . implode('\' => \'\',\'', $valCols) . "'=> '',],\n";
        $queryTypePart .= $bindedValidatedData;
        $DXPART .= $queryTypePart;
    }
    // When 'UPDATE'
    elseif ($queryType === 'UPDATE') {
        // Remove the 'id' column from the array since that is auto-incremented
        $tbsColsExceptId = array_keys($tbs[array_key_first($tbs)]['cols']);
        $tbName = key($tbs);
        array_shift($tbsColsExceptId);
        $valCols = $tbsColsExceptId;
        $tbsColsExceptId = implode(',', $tbsColsExceptId);
        $tbsColsExceptId = key($tbs) . ':' . $tbsColsExceptId;
        $queryTypePart .= "'UPDATE_SET' => '$tbsColsExceptId',\n\t\t'WHERE' => '$tbName:id = ?',";
        $bindedValidatedData = "\n\t\t'<MATCHED_FIELDS>' => [// What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey) \n\t\t\t\t'" . implode('\' => \'\',\'', $valCols) . "'=> '','id' => ''],\n";
        $queryTypePart .= $bindedValidatedData;
        $DXPART .= $queryTypePart;
    }
    // When 'DELETE'
    elseif ($queryType === 'DELETE') {
        // Remove the 'id' column from the array since that is auto-incremented
        $tbsColsExceptId = array_keys($tbs[array_key_first($tbs)]['cols']);
        $tbName = key($tbs);
        array_shift($tbsColsExceptId);
        $valCols = $tbsColsExceptId;
        $tbsColsExceptId = implode('|', $tbsColsExceptId);
        $tbsColsExceptId = key($tbs) . ':' . $tbsColsExceptId;
        $queryTypePart .= "'DELETE_FROM' => '$tbName',\n\t\t'WHERE' => '$tbName:id = ?',";
        $bindedValidatedData = "\n\t\t'<MATCHED_FIELDS>' => [// What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey) \n\t\t\t\t'" . implode('\' => \'\',\'', $valCols) . "'=> '','id' => ''],\n";
        $queryTypePart .= $bindedValidatedData;
        $DXPART .= $queryTypePart;
    }
    // When regular 'SELECT'
    elseif ($queryType === 'SELECT') {
        // We want 'id' this time around!
        $tbsColsWithId = [];
        $valCols = [];
        foreach ($tbs as $tbName => $tbData) {
            $tbsColsWithId[$tbName] = $tbData['cols'];
            $valCols[$tbName] = array_keys($tbData['cols']);
        }

        // We now add the 'FROM' which is always a must for every SELECT query!
        $queryTypePart .= "'FROM' => '";
        $queryTypePart .= array_key_first($tbs);
        $queryTypePart .= "',\n\t\t";

        // We now add the 'JOINS' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "// 'JOINS_ON' Syntax: `join_type=table2,table1(table1Col),table2(table2Col)`\n\t\t// Available Join Types: `inner|i|join|j|ij`,`left|l`,`right|r`\n\t\t// Example: `inner=books,authors(id),books(author_id)`\n\t\t'JOINS_ON' => [// Optional, make empty if not joining any tables!";
        $queryTypePart .= "\n\t\t\t\t";

        // We automatically generate all the possible JOINs (inner default) based
        // on what tables were provided in the $tbs array (the '<TABLES>' Key)!
        // Iterate through all defined tables in your full schema (from tables.php)
        $suggestedJoins = cli_parse_joins_on_DFS(array_key_first($tbs), array_keys($tbs), $tables['relationships']);
        if (!empty($suggestedJoins)) {
            $queryTypePart .= implode(",\n\t\t\t\t", $suggestedJoins);
        }
        $queryTypePart .= "],\n\t\t// Optional Keys, leave empty (or remove) if not used!\n\t\t"; // END OF 'JOINS_ON' Key!

        // We add the 'SELECT' part to the $queryTypePart which is the first part of the DXPART
        // and always a must for every SELECT query!
        // We now add the tables with the columns to the $queryTypePart for each table
        // which is inside of " $valCols" in the style: table => col1,col2,col3, we just
        // create a string that is added like `table1:col1,col2,col3|table2:col1,col2,col3`
        $queryTypePart .= "'SELECT' => ";
        $queryTypePart .= "[";
        foreach ($valCols as $tbName => $cols) {
            $queryTypePart .= "'$tbName:" . implode(',', $cols) . "',\n";
        }
        $queryTypePart .= "],\n\t\t";

        // We now add the 'WHERE' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "'WHERE' => '', \n\t\t";

        // We now add the 'GROUP BY' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "'GROUP BY' => '',\n\t\t";

        // We now add the 'HAVING' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "'HAVING' => '',\n\t\t";

        // We now add the 'ORDER BY' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "'ORDER BY' => '',\n\t\t";

        // We now add the 'LIMIT' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "'LIMIT' => '',\n\t\t";

        // We now add the 'OFFSET' which is OPTIONAL for every SELECT query!
        $queryTypePart .= "'OFFSET' => '',\n\t\t// Optional, leave empty if not used!\n\t\t";

        $queryTypePart .= "'<HYDRATION>' => [],\n\t\t";

        // $allValCols will include all the columns from all the tables by concatenating
        // the table name with the column name in the style: table1_col1, table1_col2, table2_col1, etc.
        // When there is only 1 Table though, we just grab all colum nnamens without the table name!
        $allValCols = [];
        foreach ($valCols as $tbName => $cols) {
            if (count($tbs) > 1) {
                foreach ($cols as $col) {
                    $allValCols[] = $tbName . '_' . $col;
                }
            } else {
                $allValCols = array_merge($allValCols, $cols);
            }
        }

        $bindedValidatedData = "// What each Binded Param must match from a Validated Data\n\t\t\t\t// Field Array (empty means same as TableName_ColumnKey)\n\t\t\t\t'<MATCHED_FIELDS>' => [\n\t\t\t\t'" . implode('\' => \'\',\'', $allValCols) . "'=> ''],\n";
        $queryTypePart .= $bindedValidatedData;
        $DXPART .= $queryTypePart;
    }
    // When 'SELECT_DISTINCT'
    elseif ($queryType === 'SELECT_DISTINCT') {
    }
    // When 'SELECT_INTO'
    elseif ($queryType === 'SELECT_INTO') {
    }
    // When invalid Query Type which should not happen at this point
    else {
        cli_err_syntax_without_exit("Invalid Query Type: \"$queryType\". Available Query Types: " . implode(', ', quotify_elements($availableQueryTypes)) . ".");
        cli_info("Pick one of those as the fith argument in the FunkCLI command to create a SQL Handler File and/or Function!");
    }


    // Default DXPart Value when no tables are provided
    // $DXPART = "'<CONFIG>' => [\n'[QUERY_TYPE]' => [\n// Choose ONLY ONE below for EACH\n// Single created SQL Query Function!\n'SELECT DISTINCT','SELECT INTO','SELECT TOP',
    //         'SELECT','INSERT','INSERT INTO','UPDATE','DELETE'],'[SUBQUERIES]' => [\n'[subquery1]' => 'SELECT COUNT(*)',\n'[subquery2]' => '(WHERE SELECT *)']],\n'SELECT/INSERT/UPDATE/DELETE(CHOOSE-ONE-PER-SQL-FUNCTION!)' => '',\n'FROM' => '',\n'INTO' => '',\n'JOINS' => '',\n'WHERE' => '',\n'GROUP_BY' => '',\n'HAVING' => '',\n'ORDER_BY' => '',\n'LIMIT' => '',\n'OFFSET' => '',\n'VALUES' => '',\n'?_BINDED_PARAMS' => '',\n'HYDRATE' => 'table1:cols|table2:cols|table1=>table2',";

    // Prepare the validation limiter strings and return function regex
    $sqlLimiterStrings = "\t// FunkCLI created $date! Keep Closing Curly Bracket on its\n\t// own new line without indentation no comment right after it!\n\t// Run the command `php funkcli compile s $handlerFile=>$fnName`\n\t// to get SQL, Hydration & Binded Params in return statement below it!\n\t\$DX = [$DXPART\t];\n\n\treturn array([]);";
    $returnFunctionRegex = '/^(return function)\s*\(&\$c, \$handler\s*=\s*.+$.*?^};/ims';
    $usedTables = implode(",", array_keys($tbs)) ?? ""; // Inserted inbetween "<>" in the function name comment

    // If dir not found or not readable/writable, we exit
    if (!dir_exists_is_readable_writable($handlersDir)) {
        cli_err("[cli_create_sql_file_and_or_handler]: \"$handlersDir\" not found or non-readable/writable!");
    }

    // When file does not exist we create it
    if (!file_exists($handlersDir . $handlerFile . ".php")) {
        $outputHandlerRoute = file_put_contents(
            $handlersDir . $handlerFile . ".php",
            "<?php\nnamespace FunkPHP\SQL\\$handlerFile;\n// SQL Handler File - Created in FunkCLI on $date!\n// Write your SQL Query, Hydration & optional Binded Params in the\n// \$DX variable and then run the command\n// `php funkcli compile s $handlerFile=>\$function_name`\n// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!\n// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!\nfunction $fnName(&\$c) // <$usedTables>\n{\n$sqlLimiterStrings\n};\n\nreturn function (&\$c, \$handler = \"$fnName\") { $handlerBaseFullString \n};"
        );
        if ($outputHandlerRoute) {
            cli_success_without_exit("Added SQL Handler \"funkphp/$handlerDirPath/$handlerFile.php\" with SQL Function \"$fnName\" in \"funkphp/sql/$handlerFile.php\" with Query Type \"$queryType\" using Tables:\"$usedTables\"!");
            return;
        } else {
            cli_err("[cli_create_sql_file_and_or_handler]: FAILED to create SQL Handler \"funkphp/$handlerDirPath/$handlerFile.php\". File permissions issue?");
        }
    }

    // When file does exist we check if the function name is already used
    if (file_exists($handlersDir . $handlerFile . ".php")) {
        // If file is NOT readable/writable, we exit
        if (!file_exists_is_readable_writable($handlersDir . $handlerFile . ".php")) {
            cli_err("[cli_create_sql_file_and_or_handler]: \"$handlersDir/$handlerFile.php\" not found or non-readable/writable!");
        }
        $fileContent = file_get_contents($handlersDir . $handlerFile . ".php");

        // Now we check if the function name is already used
        $matchFnRegex = '/^function (' . $fnName . ')\(\&\$c\)\s*\/\/\s*<[a-z_,\-0-9\*]*>\s*$.*?^};$/ims';
        if (preg_match($matchFnRegex, $fileContent, $matches)) {
            cli_err("[cli_create_sql_file_and_or_handler]: \"$fnName\" - Function name already exists in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        } else {
            cli_info_without_exit("Function \"$fnName\" available in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        }

        // Here we match the return function block to insert the new function that is not already used
        if (preg_match($returnFunctionRegex, $fileContent, $matches, PREG_OFFSET_CAPTURE)) {

            // $matches[0] now contains an array: [matched string, offset]
            $matchedString = $matches[0][0]; // The actual string that matched
            $matchOffset = $matches[0][1];   // The byte offset of the match in $fileContent

            // Construct the string for the *new* function definition only.
            // DO NOT include $matches[0] in this string; we will insert it separately.
            // Assuming $validationLimiterStrings and $date are defined elsewhere
            $newFunctionString = "\nfunction {$fnName}(&\$c) // <$usedTables>\n{\n{$sqlLimiterStrings}\n};\n\n";

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
                cli_success_without_exit("Added SQL Function \"$fnName\" to existing SQL Handler File \"funkphp/$handlerDirPath/$handlerFile.php\" with Query Type \"$queryType\" using Tables:\"$usedTables\"!");
                return;
            } else {
                cli_err("[cli_create_sql_file_and_or_handler]: FAILED to create SQL \"$fnName\" Function in \"funkphp/$handlerDirPath/$handlerFile.php\". File permissions issue?");
            }
        } else {
            // The 'return function' block was not found - the file structure is invalid
            cli_err_without_exit("[cli_create_sql_file_and_or_handler]: Invalid Handler File structure.");
            cli_err("Could not find the 'return function(...) {...};' block in \"funkphp/$handlerDirPath/$handlerFile.php\".");
            return false; // Exit the function as the file structure is unexpected
        }
    }
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

//TODO: PLACEHOLDER DELETE LATER!
function cli_prepare_valid_route_string()
{
    return;
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

// Retrieve starting code for files created by the CLI
function cli_get_prefix_code($keyString)
{
    $currDate = date("Y-m-d H:i:s");
    $prefixCode = [
        "route_singles_routes_start" => "<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it $currDate\nreturn ",
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

// CLI Functions to show errors and success messages with colors
function cli_err_syntax($string)
{
    cli_output(MSG_TYPE_SYNTAX_ERROR, $string, true, 1);
}
function cli_err($string)
{
    cli_output(MSG_TYPE_ERROR, $string, true, 1);
}
function cli_err_without_exit($string)
{
    cli_output(MSG_TYPE_ERROR, $string, false);
}
function cli_err_syntax_without_exit($string)
{
    cli_output(MSG_TYPE_SYNTAX_ERROR, $string, false);
}
function cli_err_command($string)
{
    cli_output(MSG_TYPE_ERROR, $string, true, 1);
}
function cli_success($string)
{
    cli_output(MSG_TYPE_SUCCESS, $string, true, 0);
}
function cli_info($string)
{
    cli_output(MSG_TYPE_INFO, $string, true, 0);
}
function cli_success_without_exit($string)
{
    cli_output(MSG_TYPE_SUCCESS, $string, false);
}
function cli_info_without_exit($string)
{
    cli_output(MSG_TYPE_INFO, $string, false);
}
function cli_info_multiline($string)
{
    cli_output(MSG_TYPE_INFO, $string, false);
}
function cli_warning($string)
{
    cli_output(MSG_TYPE_WARNING, $string, true, 0);
}
function cli_warning_without_exit($string)
{
    cli_output(MSG_TYPE_WARNING, $string, false);
}
function cli_important($string)
{
    cli_output(MSG_TYPE_IMPORTANT, $string, true, 0);
}
function cli_important_without_exit($string)
{
    cli_output(MSG_TYPE_IMPORTANT, $string, false);
}
function cli_success_with_warning_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, true);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS + WARNING]: " . $string1 . ANSI_RESET;
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
        exit(0);
    }
}
function cli_err_with_info_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, true, 1); // Exit after info in this case
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + INFO]: " . $string1 . ANSI_RESET;
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
        exit(1);
    }
}
function cli_err_with_info_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, false);
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + INFO]: " . $string1 . ANSI_RESET;
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
    }
}
function cli_err_with_warning_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, true, 1);
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + WARNING]: " . $string1 . ANSI_RESET;
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
        exit(1);
    }
}
function cli_err_with_warning_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_ERROR, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, false);
    } else {
        echo ANSI_RED . "[FunkCLI - ERROR + WARNING]: " . $string1 . ANSI_RESET;
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
    }
}
function cli_success_with_info_same_line($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, true, 0);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS + INFO]: " . $string1 . ANSI_RESET;
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
        exit(0);
    }
}
function cli_success_with_info_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_INFO, $string2, false);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS]: " . $string1 . ANSI_RESET; // Note: original prefix might be different here
        echo ANSI_BLUE . $string2 . ANSI_RESET . "\n";
    }
}
function cli_success_with_warning_same_line_without_exit($string1, $string2)
{
    if (defined('JSON_MODE') && JSON_MODE) {
        cli_output(MSG_TYPE_SUCCESS, $string1, false);
        cli_output(MSG_TYPE_WARNING, $string2, false);
    } else {
        echo ANSI_GREEN . "[FunkCLI - SUCCESS]: " . $string1 . ANSI_RESET; // Note: original prefix might be different here
        echo ANSI_YELLOW . $string2 . ANSI_RESET . "\n";
    }
}

// Function loops through all function files in funkphp/_internals/functions/
// and preg matchdes "function ([a-zA-Z0-9_]+)" and then adds the function name to an
// array which is then converted to a [] array string using cli_convert_array_to_simple_syntax
// and then the FunkCLI file is open and the line "$reserved_functions = [...];" is replaced with the new array string
function cli_update_reserved_functions_list()
{
    global $dirs;
    $dir = $dirs['functions'];
    $dir2 = $dirs['cli'];
    if (!dir_exists_is_readable_writable($dir)) {
        cli_err("Directory $dir does not exist or is not readable/writable!");
    }
    if (!dir_exists_is_readable_writable($dir2)) {
        cli_err("Directory $dir2 does not exist or is not readable/writable!");
    }

    // Get all files in the directory
    $files = scandir($dir);
    $files2 = scandir($dir2);
    $reserved_functions = [];

    // Loop through all files and check if they are PHP files
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === "php") {
            // Check that file name ends with "_funs.php" or exit
            if (!str_ends_with($file, "_funs.php")) {
                cli_info_without_exit("File `$file` not valid function file! Skipping it...");
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
    foreach ($files2 as $file2) {
        if (pathinfo($file2, PATHINFO_EXTENSION) === "php") {
            if (!str_starts_with($file2, "cli_funs")) {
                cli_info_without_exit("File `$file2` not valid function file! Skipping it...");
                continue;
            }
            // Get the contents of the file
            $contents = file_get_contents($dir2 . $file2);
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
    // We now save an array of those functions in cli_reserved.php which is in the cli folder!
    $output = file_put_contents(
        $dir2 . "cli_reserved.php",
        "<?php\n// FunkPHP Framework - FunkCLI Created it " . date("Y-m-d H:i:s") . "\n" .
            "// This file contains all reserved functions in the FunkPHP Framework and FunkCLI.\n" .
            "// It is used to check if a function is reserved (used by FunkPHP/FunkCLI) or not.\n" .
            "return \n" . $reserved_functions_string . " // Functions Count: $count"
    );
    if ($output === false) {
        cli_err("FAILED to Write to File `$dir2" . "cli_reserved.php`! Check File Permissions?");
    } else {
        cli_success("Reserved Functions List Updated! Total Functions: $count");
    }
}
// Same as above but also returns the newly generated file as an array
function cli_update_reserved_functions_list_and_return_as_array()
{
    global $dirs;
    $dir = $dirs['functions'];
    $dir2 = $dirs['cli'];
    if (!dir_exists_is_readable_writable($dir)) {
        cli_err("Directory $dir does not exist or is not readable/writable!");
    }
    if (!dir_exists_is_readable_writable($dir2)) {
        cli_err("Directory $dir2 does not exist or is not readable/writable!");
    }
    $files = scandir($dir);
    $files2 = scandir($dir2);
    $reserved_functions = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === "php") {
            // Check that file name ends with "_funs.php" or exit
            if (!str_ends_with($file, "_funs.php")) {
                cli_info_without_exit("File `$file` not valid function file! Skipping it...");
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
    foreach ($files2 as $file2) {
        if (pathinfo($file2, PATHINFO_EXTENSION) === "php") {
            if (!str_starts_with($file2, "cli_funs")) {
                cli_info_without_exit("File `$file2` not valid function file! Skipping it...");
                continue;
            }
            $contents = file_get_contents($dir2 . $file2);
            preg_match_all("/^function\s+([a-zA-Z0-9_]+)\(/m", $contents, $matches);
            foreach ($matches[1] as $function_name) {
                $reserved_functions[] = $function_name;
            }
        }
    }
    $reserved_functions_string = cli_convert_array_to_simple_syntax($reserved_functions);
    $count = count($reserved_functions);
    $reserved_functions_string = preg_replace("/\d+\s*=>\s*/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\n/", "", $reserved_functions_string);
    $reserved_functions_string = preg_replace("/\',/", "',\n", $reserved_functions_string, 1);
    $output = file_put_contents(
        $dir2 . "cli_reserved.php",
        "<?php\n// FunkPHP Framework - FunkCLI Created it " . date("Y-m-d H:i:s") . "\n" .
            "// This file contains all reserved functions in the FunkPHP Framework and FunkCLI.\n" .
            "// It is used to check if a function is reserved (used by FunkPHP/FunkCLI) or not.\n" .
            "return \n" . $reserved_functions_string . " // Functions Count: $count"
    );
    if ($output === false) {
        cli_warning_without_exit("FAILED to Write to File `$dir2" . "cli_reserved.php`! Check File Permissions? ZERO Functions Included as a result!");
        cli_info_without_exit("This means a Function that is already being used by a globally included FunkPHP Function could be added and causing function redeclaration(s) as a result!");
        return [];
    } else {
        return include_once $dir2 . "cli_reserved.php";
    }
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
    return isset($string) && is_string($string) && mb_strlen(trim($string)) > 0;
}

// Function that takes a string and returns it with quotes such as (", ' or `) around it.
// Default is backtick (`) but can be changed to single quote (') or double quote (").
function quotify_string($string, $type = "`")
{
    if (!is_string($string) || empty($string)) {
        cli_err_syntax("[quotify_string]: String must be a non-empty string!");
    }
    if (!in_array($type, ["'", '"', '`'])) {
        cli_err_syntax("[quotify_string]: Type must be one of the following: ' (single quote), \" (double quote) or ` (backtick)!");
    }
    return $type . $string . $type;
}

// Function takes two strings and returns them in the format:
// "'string1' => 'string2'" or ""string1" => "string2"" or "`string1` => `string2`"
function wrappify_arrowed_string($string1, $string2, $type = "'")
{
    if (!is_string($string1) || empty($string1)) {
        cli_err_syntax("[wrappify_arrowed_string]: First string must be a Non-Empty String!");
    }
    if (!is_string($string2) || empty($string2)) {
        cli_err_syntax("[wrappify_arrowed_string]: Second string must be a Non-Empty String!");
    }
    if (!in_array($type, ["'", '"', '`'])) {
        cli_err_syntax("[wrappify_arrowed_string]: Type must be one of the following: ' (single quote), \" (double quote) or ` (backtick)!");
    }
    return $type . $string1 . "$type => $type" . $string2 . $type . ",\n\t\t";
}

// Function same above but for arrays, it takes an array and returns all its elements
// quotified with the given type (default is backtick `).
function quotify_elements($array, $type = "`")
{
    if (!is_array($array) || empty($array)) {
        cli_err_syntax("[quotify_elements]: Array must be a Non-Empty Array!");
    }
    if (!in_array($type, ["'", '"', '`'])) {
        cli_err_syntax("[quotify_elements]: Type must be one of the following: ' (single quote), \" (double quote) or ` (backtick)!");
    }
    $quotedArray = [];
    foreach ($array as $element) {
        // Element must be string, number or boolean we type cast it to string
        if (!is_string($element) && !is_numeric($element) && !is_bool($element)) {
            cli_err_syntax("[quotify_elements]: All elements in the Array must be Strings, Numbers or Booleans!");
        }
        // Type cast the element to string and then quotify it
        $element = (string)$element;
        $quotedArray[] = quotify_string($element, $type);
    }
    return $quotedArray;
}

// Function returns true/false if String starts+ends with given strings
function str_starts_ends_with($str, $start, $end)
{
    return str_starts_with($str, $start) && str_ends_with($str, $end);
}

// Function returns true/false if String does not start OR ends with given string
function str_starts_or_ends_not_with($str, $start, $end)
{
    if (str_starts_with($str, $start) && !str_ends_with($str, $end)) {
        return true;
    } elseif (!str_starts_with($str, $start) && str_ends_with($str, $end)) {
        return true;
    }
    return false;
}

// Function that returns true if any of the elements
// get true for str_contains, otherwise returns false
function array_str_contains($arr, $containValue)
{
    if (!is_array($arr) || empty($arr)) {
        cli_err_syntax("[array_contains]: First argument must be a Non-Empty Array!");
    }
    if (!is_string($containValue) && !is_numeric($containValue) && !is_bool($containValue)) {
        cli_err_syntax("[array_contains]: Second argument must be a String, Number or Boolean!");
    }
    foreach ($arr as $value) {
        if (is_string($value) && str_contains((string)$containValue, $value)) {
            return true;
        }
    }
    return false;
}

// Function that returns true if any of the elements
// get true for str_starts_with, otherwise returns false
function array_str_starts_with($arr, $startsWith)
{
    if (!is_array($arr) || empty($arr)) {
        cli_err_syntax("[array_str_starts_with]: First argument must be a Non-Empty Array!");
    }
    if (!is_string($startsWith) && !is_numeric($startsWith) && !is_bool($startsWith)) {
        cli_err_syntax("[array_str_starts_with]: Second argument must be a String, Number or Boolean!");
    }
    foreach ($arr as $value) {
        if (is_string($value) && str_starts_with((string)$startsWith, $value)) {
            return true;
        }
    }
    return false;
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
function get_handler_and_fn_from_argv4_or_err_out($handlerType, $argvNumber = 4, $defaultSyntax = null)
{
    global $argv,
        $settings,
        $dirs,
        $exactFiles,
        $reserved_functions;
    if (!is_string($handlerType) || empty($handlerType)) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if (
        $handlerType !== "r"
        && $handlerType !== "d"
        && $handlerType !== "v"
        && $handlerType !== "s"
    ) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Handler type must be a non-empty string. Choose between: 'r','d','v' or 's'");
    }
    // Check that "$argvNumber" is a either a valid integer or a string that can be cast to an integer
    if (!isset($argv[$argvNumber]) || !is_string($argv[$argvNumber]) || empty($argv[$argvNumber])) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add [handlerType] [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add $handlerType get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
    } else if (!is_numeric($argvNumber) || (is_string($argvNumber) && !ctype_digit($argvNumber))) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Argument number must be a valid integer or a string that can be cast to an integer!");
    }

    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : ($handlerType === "v" ? "Validation" : "SQL"));
    $handlerDir = $handlerType === "r" ? "handler" : ($handlerType === "d" ? "data" : ($handlerType === "v" ? "validation" : "sql"));

    if (!isset($argv[$argvNumber]) || !is_string($argv[$argvNumber]) || empty($argv[$argvNumber])) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add Validation [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add Validation get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
    }

    // Check if "$argv[4]" contains "=>" and split it into
    // handler & function name or just use $handlerFile name.
    $handlerFile = null;
    $fnName = null;
    $arrow = null;
    if (strpos($argv[$argvNumber], '=>') !== false) {
        [$handlerFile, $fnName] = explode('=>', $argv[$argvNumber]);
        $handlerFile = trim($handlerFile);
        $fnName = trim($fnName);
        $arrow = true;
    } else {
        $handlerFile = $argv[$argvNumber];
        $fnName = null;
    }

    // Preg_match both (unless null) handler file and function name
    if ($handlerFile !== null && !preg_match('/^[a-z_][a-z0-9_]+$/', $handlerFile)) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("\"{$handlerFile}\" - Validation Handler name must start with [a-z_] and then lowercase letters, numbers and underscores!");
    }
    if ($fnName !== null && !preg_match('/^[a-z_][a-z0-9_]+$/', $fnName)) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("\"{$fnName}\" - Validation Function name must start with [a-z_] and then lowercase letters, numbers and underscores!");
    }

    // Check that both fnName and handlerFile are not reserved functions
    if ($fnName !== null && in_array($fnName, $reserved_functions)) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("\"{$fnName}\" - Function is a reserved function name!");
    }
    if ($handlerFile !== null && in_array($handlerFile, $reserved_functions)) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
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
function get_matched_route_from_argv3_or_err_out($handlerType, $defaultSyntax = null)
{
    global $argv;
    if (!is_string($handlerType) || empty($handlerType)) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v") {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Handler type must be a non-empty string. Choose between: 'r','d', or 'v'");
    }
    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : "Validation");

    if (!isset($argv[3]) || !is_string($argv[3]) || empty($argv[3])) {
        if (is_string($defaultSyntax) && !empty($defaultSyntax)) {
            cli_info_without_exit("Syntax Example: `$defaultSyntax`");
        }
        cli_err_syntax("Should be at least four(4) non-empty string arguments!\nSyntax: php funkcli add $handlerPrefix [method/route] [handlerFile[=>handleFunction]]\nExample: 'php funkcli add $handlerPrefix get/users/:id users=>getUser'\nIMPORTANT: Writing [handlerFile] is parsed as [handlerFile=>handlerFile]!");
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
    if ($handlerType !== "r" && $handlerType !== "d") {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler type must be a non-empty string. Choose between: 'r', or 'd'");
    }

    // Prepare correct handler prefix and directory path and date for the file to either create or add to
    $handlerPrefix = $handlerType === "r" ? "Route" :  "Data";
    $handlerDirPath = $handlerType === "r" ? "handlers" : "data";
    $upperCaseHDP = strtoupper($handlerDirPath);
    $handlerDirPathFirstUC = ucfirst($handlerDirPath);
    $handlerDirPathUPPERCASE = strtoupper($handlerDirPath);
    $templateDirs = $dirs['templates'];
    $date = date("Y-m-d H:i:s");
    $outputHandlerRoute = null;
    $customCodeString = "";
    $failedToRunFunction = "FAILED_TO_RUN_" . $handlerDirPathUPPERCASE . "_FUNCTION-" . $handlerFile;
    $returnFunctionRegex = get_match_return_function_regex($fnName, $method, $validRoute) ?? "";
    $handlerBaseFullStringRow1 = "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
    $handlerBaseFullStringRow2 = "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
    $handlerBaseFullStringRow3 = "\n\tif (function_exists(\$full)) {";
    $handlerBaseFullStringRow4 = "\n\t\treturn \$full(\$c);";
    $handlerBaseFullStringRow5 = "\n\t} else {";
    $handlerBaseFullStringRow6 = "\n\t\t\$c['err']['$upperCaseHDP'][] = '$handlerPrefix Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
    $handlerBaseFullStringRow7 = "\n\t\treturn null;";
    $handlerBaseFullStringRow8 = "\n\t}";
    $handlerBaseFullString =
        $handlerBaseFullStringRow1
        . $handlerBaseFullStringRow2 . $handlerBaseFullStringRow3
        . $handlerBaseFullStringRow4 . $handlerBaseFullStringRow5
        . $handlerBaseFullStringRow6 . $handlerBaseFullStringRow7
        . $handlerBaseFullStringRow8;

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
        // Create the handler file with the function name and return a success message
        $outputHandlerRoute = file_put_contents(
            $handlersDir . $handlerFile . ".php",
            "<?php\nnamespace FunkPHP\\$handlerDirPathFirstUC\\$handlerFile;\n// $handlerPrefix Handler File - Created in FunkCLI on $date!\n// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!\n\nfunction $fnName(&\$c) // <$method$validRoute>\n{\n// FunkCLI created $date! Keep Closing Curly Bracket on its\n// own new line without indentation no comment right after it!\n$customCodeString\n};\n\nreturn function (&\$c, \$handler = \"$fnName\") { $handlerBaseFullString \n};\n"
        );

        if ($outputHandlerRoute) {
            cli_success_without_exit("Added $handlerPrefix Handler \"funkphp/$handlerDirPath/$handlerFile.php\" with $handlerPrefix Function \"$fnName\" in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
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
            cli_info_without_exit("Function \"$fnName\" available in \"funkphp/$handlerDirPath/$handlerFile.php\"!");
        }

        // Here we match the return function block to insert the new function that is not already used
        if (preg_match($returnFunctionRegex, $fileContent, $matches, PREG_OFFSET_CAPTURE)) {

            // $matches[0] now contains an array: [matched string, offset]
            $matchedString = $matches[0][0]; // The actual string that matched
            $matchOffset = $matches[0][1];   // The byte offset of the match in $fileContent

            // Construct the string for the *new* function definition only.
            // DO NOT include $matches[0] in this string; we will insert it separately.
            $newFunctionString = '';
            $newFunctionString = "\nfunction {$fnName}(&\$c) // <{$method}{$validRoute}>\n{\n// FunkCLI created $date! Keep Closing Curly Bracket on its\n// own new line without indentation no comment right after it!\n$customCodeString\n};\n\n";


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
            cli_err_without_exit("[create_handler_file_with_fn_or_fn_or_err_out]: Invalid handler file structure.\nHave You Saved the File since last time Added Function to it?\nOtherwise, the `return function(...) {...};` block ends up not being found by the regex due to not being intended correctly!");
            cli_err("Could not find the 'return function(...) {...};' block in \"funkphp/{$handlerDirPath}/{$handlerFile}.php\". Try saving the file first to ensure the structure is correct.\nIf you have not saved the file, the regex will not match the expected structure, and this function will not be able to add the new function.");
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
    if (
        $handlerType !== "r" && $handlerType !== "d"
        && $handlerType !== "v" && $handlerType !== "s"
    ) {
        cli_err_syntax("[get_valid_handlerVar_or_err_out] Handler type must be a non-empty string. Choose between: 'r', 'd', 's', or 'v'");
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

    $handlerTypeName = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : ($handlerType === "v" ? "Validation" : "SQL"));

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
    if ($handlerType !== "r" && $handlerType !== "d" && $handlerType !== "v" && $handlerType !== "s") {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler type must be a non-empty string. Choose between: 'r','d', 'v' or 's'");
    }
    if (!is_string($handlerVar) && !is_array($handlerVar)) {
        cli_err_syntax("[create_handler_file_with_fn_or_fn_or_err_out] Handler variable must be a non-empty string or an array!");
    }

    // Get valid handler file and function name
    [$handlerFile, $fnName] = get_valid_handlerVar_or_err_out($handlerVar, $handlerType);

    // Prepare correct handler prefix, directory path (r = route, d = data, v = validation, s = sql)
    $handlerPrefix = $handlerType === "r" ? "Route" : ($handlerType === "d" ? "Data" : ($handlerType === "v" ? "Validation" : "SQL"));
    $handlerDirShort = $handlerType === "r" ? "handler" : ($handlerType === "d" ? "data" : ($handlerType === "v" ? "validation" : "sql"));
    $handlerDirPath = $handlerType === "r" ? $dirs['handlers'] : ($handlerType === "d" ? $dirs['data'] : ($handlerType === "v" ? $dirs['validations'] : $dirs['sql']));
    $fnNameRegex = null;

    // Route & Data Handlers use a different regex for matching function names
    if ($handlerType  !== 'v' && $handlerType !== 's') {
        $fnNameRegex = get_match_function_regex($fnName);
    }
    //  Validation & SQL Handlers use a different regex for matching function names
    else {
        $fnNameRegex = '/^function (' . $fnName . ')\(\&\$c\)\s*\/\/\s*<[a-z_,\-0-9\*]*>\s*$.*?^};$/ims';
    }

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
            cli_success_without_exit("No Functions Left in File - Deleted $handlerPrefix Handler \"$handlerFile.php\" File \"funkphp/$handlerDirShort/$handlerFile.php\"!");
            return;
        }
        // Otherwise we just write the file content back to the file with the function removed
        else {
            file_put_contents($handlerDirPath . $handlerFile . ".php", $fileContent);
        }
    } else {
        cli_err("[create_handler_file_with_fn_or_fn_or_err_out]: \"$fnName\" - Function Name not found in \"funkphp/$handlerDirPath$handlerFile.php\"!");
    }
}
