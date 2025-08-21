<?php // SECOND CLI FUNCTIONS FILE SINCE SECOND ONE STARTED TO BECOME TOO LARGE!

// Two functions that are used to output messages in the CLI
// and to send JSON responses when in JSON_MODE (web browser access)
function cli_output(string $type, string $message, bool $do_exit = false, int $exit_code = 0): void
{
    // Access the global message array and
    // determine prefix and color for CLI output
    global $funk_response_messages;
    $prefix = '';
    $color = '';
    switch ($type) {
        case MSG_TYPE_ERROR:
            $prefix = '[FunkCLI - ERROR]: ';
            $color = ANSI_RED;
            $exit_code = ($exit_code === 0) ? 1 : $exit_code;
            break;
        case MSG_TYPE_SYNTAX_ERROR:
            $prefix = '[FunkCLI - SYNTAX ERROR]: ';
            $color = ANSI_RED;
            $exit_code = ($exit_code === 0) ? 1 : $exit_code;
            break;
        case MSG_TYPE_SUCCESS:
            $prefix = '[FunkCLI - SUCCESS]: ';
            $color = ANSI_GREEN;
            break;
        case MSG_TYPE_INFO:
            $prefix = '[FunkCLI - INFO]: ';
            $color = ANSI_BLUE;
            break;
        case MSG_TYPE_WARNING:
            $prefix = '[FunkCLI - WARNING]: ';
            $color = ANSI_YELLOW;
            break;
        case MSG_TYPE_IMPORTANT:
            $prefix = '[FunkCLI - IMPORTANT]: ';
            $color = ANSI_YELLOW;
            break;
        default:
            $type = 'UNKOWN';
            $prefix = '[FunkCLI - UNKOWN MESSAGE TYPE]: '; // Fallback for unknown types
            $color = ANSI_RESET;
            break;
    }

    // Check if we are in JSON_MODE (web browser access)
    // If not JSON_MODE, we assume & output to CLI!
    // Any message that includes $do_exit true will
    // return built JSON response & exit or just exit CLI
    if (defined('JSON_MODE') && JSON_MODE) {
        $funk_response_messages[] = [
            'type' => $type,
            'message' => $message
        ];
        if ($do_exit) {
            cli_send_json_response();
        }
    } else {
        echo $color . $prefix . $message . ANSI_RESET . "\n";
        if ($do_exit) {
            exit($exit_code);
        }
    }
}
function cli_send_json_response(): void
{
    global $funk_response_messages;
    $overall_json_status = 'Success';
    foreach ($funk_response_messages as $msg) {
        if ($msg['type'] === MSG_TYPE_ERROR || $msg['type'] === MSG_TYPE_SYNTAX_ERROR) {
            $overall_json_status = 'Error';
            break;
        }
    }
    if ($overall_json_status === 'Success') {
        http_response_code(200);
        foreach ($funk_response_messages as $msg) {
            if ($msg['type'] === MSG_TYPE_WARNING) {
                $overall_json_status .= ' with Warning(s)';
                break;
            }
        }
    } else {
        http_response_code(400);
        foreach ($funk_response_messages as $msg) {
            if ($msg['type'] === MSG_TYPE_INFO) {
                $overall_json_status .= ' with (Important) Info';
                break;
            }
        }
    }
    $response = [
        'status' => $overall_json_status,
        'messages' => $funk_response_messages,
        'data' => []
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit();
}

// Returns "fileName"=>"functionName" based on the provided string
// If no function name is provided, it returns "fileName"=>"fileName"
function cli_return_valid_file_n_fn_or_err_out($string, $prefix = null)
{
    global $reserved_functions;
    // $string must be a non-empty string, then we lowercase it
    if (!isset($string) || !is_string($string) || empty($string)) {
        cli_err_without_exit('[cli_match_file_and_fn()]: This function expects a Non-Empty String (probably missing in $arg1) | $arg2 is the optional Method/Route part!');
        cli_info_without_exit('[cli_match_file_and_fn()]: Use either "fileName" (Regex: [a-z_][a-z_0-9]*) OR "fileName=>functionName" (Regex: [a-z_][a-z_0-9]*=>[a-z_][a-z_0-9.]*)!');
        cli_info('[cli_match_file_and_fn()]: IMPORTANT: Your provided String will ALWAYS be lowercased automatically before any further processing!');
    }
    $string = strtolower(trim($string));
    // Matches a string like "fileName" or "fileName=>functionName"
    // If only "fileName" is provided, it will use the same name for the function
    $regex = '/^([a-z_][a-z_0-9]*)(?:=>([a-z_][a-z_0-9.]*))?$/i';
    $file = '';
    $fn = '';
    // Preg_match to find the file and function names
    if (preg_match($regex, $string, $matches)) {
        $file = $matches[1];
        $fn = isset($matches[2]) ? $matches[2] : $file;
    } else {
        cli_err_without_exit('[cli_match_file_and_fn()]: Invalid Syntax for File and/or Function Name! (probably in $arg1) | $arg2 is the optional Method/Route part!');
        cli_info_without_exit('[cli_match_file_and_fn()]: Use either "fileName" (Regex: [a-z_][a-z_0-9]*) OR "fileName=>functionName" (Regex: [a-z_][a-z_0-9]*=>[a-z_][a-z_0-9.]*)!');
        cli_info('[cli_match_file_and_fn()]: IMPORTANT: Your provided String will ALWAYS be lowercased automatically before any further processing!');
    }
    // Add prefix to both variables if provided
    // and then check against reserved functions
    // Add _v1 to middlewares and pipelines since they
    // can come in different versions and are meant to
    // be reused in different projects whereas the rest
    // are supposed to be project-specific and not reused
    if (isset($prefix) && is_string($prefix) && !empty($prefix)) {
        $file = $prefix . $file;
        $fn = $prefix . $fn;
        if ($prefix === 'pl_' || $prefix === 'm_') {
            $file .= '_v1';
            $fn .= '_v1';
        }
    }
    if (in_array($file, $reserved_functions)) {
        cli_err_without_exit('[cli_match_file_and_fn()]: File Name `' . $file . '` is a Reserved Function Name. Please choose a different name! (probably see $arg1)');
        cli_info('[cli_match_file_and_fn()]: The majority of Reserved Function Names usually start with "funk_" or "cli_" prefix, so please avoid using those prefixes for Your Custom Functions!');
    }
    if (in_array($fn, $reserved_functions)) {
        cli_err_without_exit('[cli_match_file_and_fn()]: Function Name `' . $fn . '` is a Reserved Function Name. Please choose a different name! (probably see $arg1)');
        cli_info('[cli_match_file_and_fn()]: The majority of Reserved Function Names usually start with "funk_" or "cli_" prefix, so please avoid using those prefixes for Your Custom Functions!');
    }
    cli_info_without_exit('OK! Parsed File => Function: `' . $file . '=>' . $fn . '` (Function is ignored for Folders such as `middlewares` & `pipeline`!)');
    return [$file, $fn];
}

// Returns valid extracted method and route or errors out
function cli_return_valid_method_n_route_or_err_out($string)
{
    if (!isset($string) || !is_string($string) || empty($string)) {
        cli_err_without_exit('Method/Route String must be a Non-Empty String using the following Syntax: `method/route/segments/with/optional/:params`!');
        cli_info_without_exit('For the Method, use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info_without_exit('OR Use any of its shorthand versions: "g", "po", "pu", "d" OR "del", "pa"');
        cli_info_without_exit('For the Route, write either "/route/segments" or "/route/segments/with/:params" (where :params is a Dynamic URI Segment of the Route)');
    }
    $string = trim(strtolower($string));
    $method = '';
    $extractedMethod = '';
    $methodRegex = '/^(delete|patch|post|put|del|get|pa|po|pu|ge|g|d)\/.*/i';
    $methodConvert = [
        'delete' => 'DELETE',
        'patch' => 'PATCH',
        'post' => 'POST',
        'put' => 'PUT',
        'get' => 'DELETE',
        'del' => 'DELETE',
        'pa' => 'PATCH',
        'po' => 'POST',
        'pu' => 'PUT',
        'ge' => 'GET',
        'g' => 'GET',
        'd' => 'DELETE'
    ];
    $routeRegex = '/\/(:[a-zA-Z0-9_-]+)|\/([a-zA-Z0-9_-]+)/i';
    $route = '';
    $routeParams = [];
    // First we check that $methodRegex matches the start of $string
    if (!preg_match($methodRegex, $string, $methodMatches)) {
        cli_err_without_exit('Invalid Method Syntax! Use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info_without_exit('OR Use any of its shorthand versions: "g" or "ge", "po", "pu", "d" OR "del", "pa"');
        cli_info('A Single `/` is needed if you mean the Root Route `/` of that Method!');
    }
    $extractedMethod = $methodMatches[1];
    $method = $methodConvert[$methodMatches[1]] ?? '';
    if ($method === '') {
        cli_err_without_exit('Invalid Method Syntax! Use one of the following: "get", "post", "put", "delete", "patch"');
        cli_info('OR Use any of its shorthand versions: "g" or "ge", "po", "pu", "d" OR "del", "pa"');
    }
    // We separate the method from the route to validate route
    $routeString = substr($string, strlen($extractedMethod));
    // SPECIAL CASE: If the route is just `/`, we return it as is
    if ($routeString === '/') {
        return [$method, $routeString];
    }
    // Filter out leading slashes and ensure no double (or more) slashes
    $routeString = preg_replace('/\/+/', '/', $routeString);
    if (!preg_match_all($routeRegex,  $routeString, $routeMatches)) {
        cli_err_without_exit('Invalid Route Syntax! Use either "/route/segments" or "/route/segments/with/:params" (where :params is a Dynamic URI Segment of the Route)');
        cli_info('A Single `/` is needed if you mean the Root Route `/` of that Method as in `get/` OR `g/` and so on!');
    }
    // We iterate through $routeMatches[0] to error
    // out on duplicate route parameters. Otherwise
    // we return finalized $method and $route!
    foreach ($routeMatches[0] as $match) {
        if (str_starts_with($match, '/:')) {
            if (in_array($match, $routeParams)) {
                cli_err_without_exit('Duplicate Route Parameter `' .  $match . '` found in the Route `' .  $method . $routeString . '`!');
                cli_info('Fix so each Route Parameter (`/:param`) is unique and does not repeat in the Route Definition!');
            } else {
                $routeParams[] = $match;
            }
        }
        $route .= $match;
    }
    cli_info_without_exit("OK! Parsed Method & Route: `$method$route` (it is ONLY used where applicable!)");
    return [$method, $route];
}

// Returns a generated $DX Part based on provided $arg5
// which should contain "table1,table2,etc3" or just "table1"
function cli_created_sql_or_validation_fn($sqlOrValidation, $sv_tables)
{
    // Prepare general variables for either SQL or Validation
    global $dirs, $tablesAndRelationshipsFile, $mysqlDataTypesFile;
    $tables = $tablesAndRelationshipsFile ?? null;
    $types = $mysqlDataTypesFile ?? null;
    $date = date("Y-m-d H:i:s");
    $finalString = '';
    if ($tables === null || !is_array($tables)) {
        cli_err_syntax_without_exit("`Tables.php` File not found! Please check your `funkphp/config/tables.php` File!");
        cli_info("Make sure you have a valid `tables.php` File in `funkphp/config/` directory!");
    }
    if (!isset($tables['tables']) || !is_array($tables['tables']) || empty($tables['tables'])) {
        cli_err_syntax_without_exit("`Tables.php` File does not contain valid `tables` key! Please check your `funkphp/config/tables.php` File!");
        cli_info("Your `tables` array key in your `tables.php` File in `funkphp/config/` directory CANNOT be empty and should Have At Least One Table!");
    }
    if (!isset($tables['relationships']) || !is_array($tables['relationships'])) {
        cli_err_syntax_without_exit("`tables.php` File does not contain valid `relationships` key! Please check your `funkphp/config/tables.php` File!");
        cli_info("Your `relationships` array key in your `Tables.php` File in `funkphp/config/` directory must exist and CAN be empty!");
    }
    if (!isset($tables['mappings']) || !is_array($tables['mappings'])) {
        cli_err_syntax_without_exit("`tables.php` File does not contain valid `mappings` key! Please check your `funkphp/config/tables.php` File!");
        cli_info("Your `mappings` array key in your `Tables.php` File in `funkphp/config/` directory must exist and CAN be empty!");
    }

    // Prepare SQL String based on Tables
    if ($sqlOrValidation === 'sql') {
        $tbs = [];
        $queryType = null;
        $availableQueryTypes = [
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'SELECT_DISTINCT',
            'SELECT_INTO',
        ];
        $processTables = null;
        // SQL Tables must start with "sd=", "si=", "s=", "i=", "u=", or "d="
        if (!preg_match('/^((sd|si|s|i|u|d)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $sv_tables)) {
            cli_err_without_exit('[cli_created_sql_or_validation_fn()]: Invalid SQL Tables Syntax! Use either "sd=table1,table2,etc3" or just "s=table1" with optional numbers with * at the end of each table name like "s=table1*2"!');
            cli_info('[cli_created_sql_or_validation_fn()]: The Regex Syntax for SQL Tables: `/^((sd|si|s|i|u|d)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/`!');
        }
        // Extract the "sd=", "si=", "s=", "i=", "u=", or "d=" part from the "table1,table2,etc3" part of the string
        // Uppercase the query type and validate it against the available query types after transoforming shorthands
        // to full query types (s=SELECT, i=INSERT, u=UPDATE, d=DELETE, sd=SELECT_DISTINCT, si=SELECT_INTO, st=SELECT_TOP)
        $queryType = strtoupper(substr($sv_tables, 0, strpos($sv_tables, '=')));
        $processTables = str_contains(substr($sv_tables, strpos($sv_tables, '=') + 1), ",")
            ? explode(',', substr($sv_tables, strpos($sv_tables, '=') + 1))
            : [substr($sv_tables, strpos($sv_tables, '=') + 1)];
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
        if (!in_array($queryType, $availableQueryTypes)) {
            cli_err_syntax_without_exit("Invalid Query Type: \"$queryType\". Available Query Types: " . implode(', ', quotify_elements($availableQueryTypes)) . ".");
            cli_err_without_exit('[cli_created_sql_or_validation_fn()]: Invalid SQL Tables Syntax! Use either "sd=table1,table2,etc3" or just "s=table1" with optional numbers with * at the end of each table name like "s=table1*2"!');
            cli_info('[cli_created_sql_or_validation_fn()]: The Regex Syntax for SQL Tables: `/^((sd|si|s|i|u|d)=[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/`!');
        }

        // Validate that all the provided tables exist in the
        // Tables.php file are valid named and not duplicates!
        foreach ($processTables as $table) {
            if (!preg_match('/^[a-z_][a-z_0-9]*$/i', $table)) {
                cli_err_syntax_without_exit("Invalid Table Name: \"$table\". Use only alphanumeric characters and underscores!");
                cli_info("Example: \"table1\" or \"table_1\" or \"table_1_2\" - do not use spaces or special characters!");
            }
            if (!array_key_exists($table, $tables['tables'])) {
                cli_err_syntax_without_exit("Table \"$table\" not found in `funkphp/config/tables.php`! Available Tables: " . implode(', ', quotify_elements(array_keys($tables['tables']))));
                cli_info("Make sure you have a valid `tables.php` File in `funkphp/config/` directory with at least one table in the ['tables'] Array!");
            }
            if (array_key_exists($table, $tbs)) {
                cli_err_syntax_without_exit("Table \"$table\" already added. Only use one Table once!");
                cli_info("Make sure you have a valid `tables.php` File in `funkphp/config/` directory with at least one table in the ['tables'] Array!");
            }
            $tbs[$table]['cols'] = $tables['tables'][$table];
        }
        // For queryType "INSERT", "UPDATE", "DELETE" we ONLY allow one table to be provided!
        if (count($tbs) > 1) {
            if (in_array($queryType, ['INSERT', 'UPDATE', 'DELETE'])) {
                cli_err_syntax_without_exit("For Query Type \"$queryType\" you can ONLY provide ONE Table! Provided Tables: " . implode(', ', quotify_elements(array_keys($tbs))));
                cli_info_without_exit("Please provide ONLY ONE Table for Query Types `INSERT`, `UPDATE` or `DELETE` in the FunkCLI command!");
                cli_info("Syntax Example: `php funkcli create s handlerFile=>fnName queryType table1` (at least one Table must be provided)!");
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
        $finalString = "\t// FunkCLI created $date! Keep Closing Curly Bracket on its\n\t// own new line without indentation and no comment right after it!\n\t// Run the command `php funk compile:s_eval s_file=>s_fn`\n\t// to get SQL, Hydration & Binded Params in return statement below it!\n\t\$DX = [$DXPART\t];\n\n\treturn array([]);";
    } // END OF SQL TABLES PROCESSING

    // Prepare Validation String based on Tables
    elseif ($sqlOrValidation === 'validation') {
        // Validation Tables can only include "table1,table2,etc3" or just "table1"
        // with optional numbers with * at the end of each table name like "table1*2"
        if (!preg_match('/^([a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/', $sv_tables)) {
            cli_err_without_exit('[cli_created_sql_or_validation_fn()]: Invalid Validation Tables Syntax! Use either "table1,table2,etc3" or just "table1" with optional numbers with * at the end of each table name like "table1*2"!');
            cli_info('[cli_created_sql_or_validation_fn()]: The Regex Syntax for Validation Tables: `/^([a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/`!');
        }

        // Prepare the tables string for the
        // DXPART. Split on "," if it exists
        $times = [];
        $processTables = str_contains($sv_tables, ",")
            ? explode(',', $sv_tables)
            : [$sv_tables];

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
        $finalString = "\t// FunkCLI created $date! Keep Closing Curly Bracket on its\n\t// own new line without indentation and no comment right after it!\n\t// Run the command `php funkcli compile v file=>fn`\n\t// to get optimized version in return statement below it!\n\t\$DX = [$DXPART\n\t];\n\n\treturn array([]);";
    } // END OF VALIDATION TABLES PROCESSING

    // UNEXPECTED CASE: If $sqlOrValidation is neither "sql" nor "validation" - not allowed!
    else {
        cli_err_without_exit('[cli_created_sql_or_validation_fn()]: $sqlOrValidation must be either "sql" or "validation"!');
        cli_info('[cli_created_sql_or_validation_fn()]: Use either "sql" for SQL Functions or "validation" for Validation Functions!');
    }

    // Return the final string which is the DXPART with the return statement at the end
    return $finalString;
}

// Returns default created function files (a single anoynomous function file
// OR a named function file with a return function at the end). Also handles
// special cases using $arg5 which are for "funkphp/sql" and "funkphp/validation"
function cli_default_created_fn_files($type, $methodAndRoute, $folder, $file, $fn = null, $tables = null)
{
    // Validate $type is a non-empty string and either "named" or "anonymous"
    if (!isset($type) || !is_string($type) || empty($type) || !in_array($type, [
        'named_not_new_file',
        'named_and_new_file',
        'anonymous',
        'sql_new_file_and_fn',
        'sql_only_new_fn',
        'validation_new_file_and_fn',
        'validation_only_new_fn'
    ])) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $type must be a Non-Empty String!');
        cli_info('[cli_default_created_fn_files()]: Use either "named_not_new_file", "named_and_new_file", "anonymous", "sql_new_file_and_fn", "sql_only_new_fn", "validation_new_file_and_fn" or "validation_only_new_fn" as the `$type`! (first arg)');
        return null;
    }
    // Validate $methodAndRoute is a non-empty string which can be any characters except whitespaces or new lines
    if ($methodAndRoute === null) {
        $methodAndRoute = "N/A";
    }
    if (!isset($methodAndRoute) || !is_string($methodAndRoute) || empty($methodAndRoute) || !preg_match('/^(([a-zA-Z]+\/)|([a-zA-Z]+(\/[:]?[a-zA-Z0-9]+)+))$/i', $methodAndRoute)) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $methodAndRoute must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info("[cli_default_created_fn_files()]: The Regex Syntax for Method/Route:`/^(([a-zA-Z]+\/)|([a-zA-Z]+(\/[:]?[a-zA-Z0-9]+)+))$/i`!");
        return null;
    }
    // Validate that $folder is a non-empty string and matches the regex
    if (!isset($folder) || !is_string($folder) || empty($folder) || !preg_match('/^[a-z_][a-z_0-9\/-]*$/i', $folder)) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $folder must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_default_created_fn_files()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9\/-]*)`! (you do NOT need to add a leading slash `/` to the string)');
        return null;
    }
    // Validate that $file is a non-empty string and matches the regex
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $file must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_default_created_fn_files()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
        return null;
    }
    // Validate that if set, $fn is a non-empty string matching a regex
    if (isset($fn) && (!is_string($fn) || empty($fn) || !preg_match('/^[a-z_][a-z_0-9]*$/i', $fn))) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $fn must be A Valid Non-Empty String! (any whitespace is NOT allowed)');
        cli_info('[cli_default_created_fn_files()]: Use the following Function Syntax (Regex):`[a-z_][a-z_0-9]*)`!');
        return null;
    }
    // Validate that if set, $tables is a non-empty string matching a regex
    var_dump($tables);
    if (
        isset($tables) &&
        (!is_string($tables)
            || empty($tables)
            || !preg_match('/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i', $tables)
            || (!str_contains($folder, "sql")
                && !str_contains($folder, "validation")))
    ) {
        cli_err_without_exit('[cli_default_created_fn_files()]: $arg5 (tables) must be A Valid Non-Empty String! (any whitespace is NOT allowed)');
        cli_info_without_exit("Regex Used For SQL+Validation Tables: `/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i`!");
        cli_info('[cli_default_created_fn_files()]: It is ONLY for `funkphp/sql` AND `funkphp/validation`, so `$folder` must be `sql` OR `validation`!');
        return null;
    }
    // Replace the "/" to "\" in the $folder and then uppercase each first letter
    // after each slash. This is to ensure that the namespace is correct
    $folder = str_replace('/', '\\', $folder);
    $folder = ucwords($folder, '\\');
    $folderUPPER = strtoupper($folder);

    // Remove ".php" from the $file if it exists and then
    if (str_ends_with($file, '.php')) {
        $file = substr($file, 0, -4);
    }
    // String parts of default created Files:
    $entireCreatedString = '';
    $newFilesString = '';
    $typePartString = '';

    // Parts belonging to new files created like namespace & some comments
    $namespaceString = "<?php\n\nnamespace FunkPHP\\$folder\\$file;\n";
    $newFilesString .= $namespaceString;
    $createdOnCommentString = "// FunkCLI Created on " . date('Y-m-d H:i:s') . "!\n\n";
    $newFilesString .= $createdOnCommentString;

    // Based on $type, we create the necessary File (or just updated File!)
    // When just anonmyous function is needed (usually for middlewares & pipeline functions)
    if ($type === 'anonymous') {
        $typePartString .= "return function (&\$c, \$passedValue = null) {\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // When a named function is needed but file ALREADY EXISTS
    elseif ($type === 'named_not_new_file') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$methodAndRoute>\n";
        $typePartString .= "{\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // When a named function is needed and file DOES NOT EXIST
    elseif ($type === 'named_and_new_file') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$methodAndRoute>\n";
        $typePartString .= "{\n\t// Placeholder Comment so Regex works - Remove & Add Real Code!\n};\n\n";
        $typePartString .= "return function (&\$c, \$handler = \"$fn\", \$passedValue = null) {\n";
        $typePartString .= "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
        $typePartString .= "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
        $typePartString .= "\n\tif (function_exists(\$full)) {";
        $typePartString .= "\n\t\treturn \$full(\$c, \$passedValue);";
        $typePartString .= "\n\t} else {";
        $typePartString .= "\n\t\t\$c['err']['ROUTES']['$folderUPPER'][] = '$folderUPPER Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
        $typePartString .= "\n\t\treturn null;";
        $typePartString .= "\n\t}\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // Special-case #1: "funkphp/sql" folder
    // New SQL FILE
    elseif ($type === 'sql_new_file_and_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("sql", $tables);
        $typePartString .= "\n};\n\n";
        $typePartString .= "return function (&\$c, \$handler = \"$fn\", \$passedValue = null) {\n";
        $typePartString .= "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
        $typePartString .= "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
        $typePartString .= "\n\tif (function_exists(\$full)) {";
        $typePartString .= "\n\t\treturn \$full(\$c, \$passedValue);";
        $typePartString .= "\n\t} else {";
        $typePartString .= "\n\t\t\$c['err']['ROUTES']['$folderUPPER'][] = '$folderUPPER Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
        $typePartString .= "\n\t\treturn null;";
        $typePartString .= "\n\t}\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    } // Only NEW SQL FUNCTION in existing file
    elseif ($type === 'sql_only_new_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("sql", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // Special-case #2: "funkphp/validation" folder
    // New Validation FILE
    elseif ($type === 'validation_new_file_and_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("validation", $tables);
        $typePartString .= "\n};\n\n";
        $typePartString .= "return function (&\$c, \$handler = \"$fn\", \$passedValue = null) {\n";
        $typePartString .= "\n\t\$base = is_string(\$handler) ? \$handler : \"\";";
        $typePartString .= "\n\t\$full = __NAMESPACE__ . '\\\\' . \$base;";
        $typePartString .= "\n\tif (function_exists(\$full)) {";
        $typePartString .= "\n\t\treturn \$full(\$c, \$passedValue);";
        $typePartString .= "\n\t} else {";
        $typePartString .= "\n\t\t\$c['err']['ROUTES']['$folderUPPER'][] = '$folderUPPER Function `' . \$full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';";
        $typePartString .= "\n\t\treturn null;";
        $typePartString .= "\n\t}\n};\n";
        $entireCreatedString .= $newFilesString . $typePartString;
    }
    // Only NEW Validation FUNCTION in existing file
    elseif ($type === 'validation_only_new_fn') {
        $typePartString .= "function $fn(&\$c, \$passedValue = null) // <$tables>\n";
        $typePartString .= "{\n\t";
        $typePartString .= cli_created_sql_or_validation_fn("validation", $tables);
        $typePartString .= "\n};\n\n";
        $entireCreatedString .= $typePartString;
    }
    // Catch the IMPOSSIBLE edge-case!
    else {
        cli_err_without_exit('[cli_default_created_fn_files()]: Invalid $type provided! Use either "named_not_new_file", "named_and_new_file", "anonymous", "sql" or "validation"!');
        cli_info('[cli_default_created_fn_files()]: The fact You are seeing this strongly suggests you have called the function directly instead of letting other functions calling it indirectly and you have probably removed the first safety-check at the top of the function!');
        return null;
    }

    return $entireCreatedString;
}

// Returns an array of status of $folder & $file and whether they:
// - exist, - are readable, - are writable, - the number of functions
// and each function its $DX and/or return array(). Also the entire file
// is read into a raw string and each function is as well so CRUD can
// be done for that file assuming its a PHP file with functions. If
// `return function` exists in it (like middlewares), it's included.
function cli_folder_and_php_file_status($folder, $file)
{
    // QoL fix for $folder if it is a string and starts with a slash
    if (is_string($folder) && str_starts_with(trim($folder), "/")) {
        $folder = substr(trim($folder), 1);
    }
    // Validate both are non-empty strings and match the regex
    if (!isset($folder) || !is_string($folder) || empty($folder) || !preg_match('/^[a-z_][a-z_0-9\/-]*$/i', $folder)) {
        cli_err_without_exit('[cli_folder_and_php_file_status()]: $folder must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_folder_and_php_file_status()]: Use the following Directory Syntax (Regex):`[a-z_][a-z_0-9\/-]*)`! (you do NOT need to add a leading slash `/` to the string)');
    }
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_folder_and_php_file_status()]: $file must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_folder_and_php_file_status()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
    }
    // Consistently get '$folder' . '/' . $file . '.php' always!
    $folder = trim($folder);
    $providedFolder  = $folder; // Original folder for reference
    $file = trim($file);
    $filename = '<UNKNOWN>';
    $singleFolder = '<UNKNOWN>';
    if (str_ends_with($folder, '/')) {
        $folder = rtrim($folder, '/');
    }
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    if (str_starts_with($file, '/')) {
        $file = ltrim($file, '/');
    }
    $folder = PROJECT_DIR . '/' . $folder;
    // $singleFolder is the last part of the folder path
    $singleFolder = basename($folder);
    $filename = $file;
    $file = $folder . '/' . $file;

    // If file exists and is readable, check if function exists
    // by first reading the file and then checking if
    // the function name is in the file content using regex!
    $fnRegex = '/^function\s+([a-zA-Z_][a-zA-Z0-9_]*)\(&\$[^)]*\)(.*?^};)?$/ims';
    $dxRegex = '/\$DX\s*=\s*\[\s*\'.*?];$/ims';
    $returnRegex = '/return\s*array\(.*?\);$\n/ims';
    $returnFnRegex = '/^(?:(<\?php\s*))?(return function)\s*\(&\$c\s*.+$.*?^};/ims';
    $fns = null;
    $fileRaw = null;
    $fileReturnRaw = null;
    if (is_file($file) && is_readable($file)) {
        $fileCnt = file_get_contents($file);
        if (!$fileCnt) {
            cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT Read the File `' . $file . '` when it SHOULD have been Readable. This means that Named Functions, their $DX and/or Return arrays(), OR Anonymous Function Files CANNOT be retrieved for use!');
        } else {
            $fileRaw = $fileCnt;
            if (preg_match($returnFnRegex, $fileRaw, $fileReturnMatch)) {
                $fileReturnRaw = $fileReturnMatch[0] ?? null;
            } else {
                cli_warning_without_exit('[cli_folder_and_php_file_status()]: Could NOT find the Expected Anoynmous `return function` in the File `' . $file . '` when it SHOULD have been Found. This means it will NOT be possible to add any new Functions to this File (unless it is a Single Anonymous Function File) since it needs that matched part to add new functions from. This is due to the Regex: `/^(?:(<\?php\s*))?(return function)\s*\(&\$c\s*.+$.*?^};/ims` that cannot match `return function(&$c){};`!');
            }
            if (preg_match_all($fnRegex, $fileCnt, $fnsMatches)) {
                foreach ($fnsMatches[1] as $idx => $fn) {
                    $fns[$fn] = [
                        'fn_raw' => $fnsMatches[0][$idx] ?? null,
                        'dx_raw' => null,
                        'return_raw' => null
                    ];
                    // We now use the index to match for $DX and return arrays
                    if (preg_match($dxRegex, $fnsMatches[0][$idx], $dxMatch)) {
                        $fns[$fn]['dx_raw'] = $dxMatch[0] ?? null;
                    }
                    if (preg_match($returnRegex, $fnsMatches[0][$idx], $returnMatch)) {
                        $fns[$fn]['return_raw'] = $returnMatch[0] ?? null;
                    }
                }
            }
        }
    }
    return [
        'folder_provided_path' => $providedFolder ?? null,
        'folder_name' => $singleFolder ?? null,
        'folder_path' => ((is_string($folder) && is_dir($folder) && is_readable($folder) && is_writable($folder)) ? $folder : null),
        'folder_exists' => is_dir($folder),
        'folder_readable' => is_readable($folder),
        'folder_writable' => is_writable($folder),
        'file_name' => $filename,
        'file_path' => ((is_file($file) && is_readable($file) && is_writable($file)) ? $file : null),
        'file_exists' => is_file($file),
        'file_readable' => is_readable($file),
        'file_writable' => is_writable($file),
        'functions' => (isset($fns) ? $fns : []),
        'file_raw' => ['entire' => $fileRaw ?? null, 'return function' => $fileReturnRaw ?? null],
    ];
}

// Function that expects and uses the array from the function above
// "cli_folder_and_php_file_status()" to either Create a new Folder
// with a PHP File that is just Anoymous Function (middlewares &
// pipeline functions) OR to Create a new Folder with a PHP File
// that has a Named Function (like route handlers, etc.) meaning it
// has a return function at the end of the file and named functions
// inside it. $crudType is = "create" or "delete" where "create" will
// also act as "update" if the file already exists and it just adds
// a new function to the file. If $crudType is "delete" it deletes
// a named function from the file and if that is the last named
// function, it deletes the file as well so it counts the named functions
// first because count(1) means it can just delete the file instead.
// $arg5 is for "tables" or other special cases that are ONLY for
// "funkphp/sql" AND "funkphp/validation" folders!
function cli_crud_folder_and_php_file($statusArray, $crudType, $file, $fn = null, $folderType = null, $methodAndRoute = null, $table = null)
{
    global $reserved_functions; // List of reserved functions meaning $fn CANNOT be one of them!
    // Assume success is true, if any error occurs we just set it to false
    $success = true;
    // $statusArray must be an array with the structure
    // returned by "cli_folder_and_php_file_status()"
    if (!isset($statusArray) || !is_array($statusArray) || empty($statusArray)) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $statusArray must be a Non-Empty Array returned by "cli_folder_and_php_file_status()"!');
        cli_info("It needs the following Keys: 'folder_name', 'folder_path', 'folder_exists', 'folder_readable', 'folder_writable', 'file_name', 'file_path', 'file_exists', 'file_readable', 'file_writable', 'functions' and 'file_raw'!");
        return null;
    }
    $requiredKeys = [
        'folder_provided_path',
        'folder_name',
        'folder_path',
        'folder_exists',
        'folder_readable',
        'folder_writable',
        'file_name',
        'file_path',
        'file_exists',
        'file_readable',
        'file_writable',
        'functions',
        'file_raw'
    ];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $statusArray)) {
            cli_err_without_exit('[cli_crud_folder_and_php_file()]: $statusArray must contain the Key: `' . $key . '`!');
            cli_info("It needs the following Keys: 'folder_name', 'folder_path', 'folder_exists', 'folder_readable', 'folder_writable', 'file_name', 'file_path', 'file_exists', 'file_readable', 'file_writable', 'functions' and 'file_raw'!");
            return null;
        }
    }
    // $crudType must be a string and either "create" or "delete"
    if (
        !isset($crudType)
        || !is_string($crudType)
        || empty($crudType)
        || !in_array($crudType, [
            'create_new_anonymous_file',
            'create_new_file_and_fn',
            'create_only_new_fn_in_file',
            'delete'
        ])
    ) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $crudType must be a Non-Empty String using one of the following Valid String Values! (see INFO next below)');
        cli_info('[cli_crud_folder_and_php_file()]: Valid String Values: "create_new_anonymous_file", "create_new_file_and_fn", "create_only_new_fn" OR "delete" as the $crudType!');
        return null;
    }
    // $file must be a string and match the regex, we also perform some QoL fixes
    if (!isset($file) || !is_string($file) || empty($file) || !preg_match('/^[a-z_][a-z_0-9\.]*$/i', $file)) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $file must be A Valid Non-Empty String! (whitespace is NOT allowed)');
        cli_info('[cli_crud_folder_and_php_file()]: Use the following File Syntax (Regex):`[a-z_][a-z_0-9\.]*)`! (you do NOT need to add a leading slash `/` to the string and NOT `.php` File Extension)');
        return null;
    }
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    if (str_starts_with($file, '/')) {
        $file = ltrim($file, '/');
    }
    // If $fn is set it must be a string and match
    // the regex and not in reserved functions
    if (isset($fn) && (!is_string($fn)
        || empty($fn)
        || !preg_match('/^[a-z_][a-z_0-9]*$/i', $fn)
        || in_array($fn, $reserved_functions))) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $fn must be A Valid Non-Empty String! (any whitespace is NOT allowed)');
        cli_info_without_exit('[cli_crud_folder_and_php_file()]: The Function Name cannot be a Reserved Function Name which is usually prefixed with "funk_" or "cli_"!');
        cli_info('[cli_crud_folder_and_php_file()]: Use the following Function Syntax (Regex):`[a-z_][a-z_0-9]*)`! (you do NOT need to add a leading slash `/` to the string)');
        return null;
    }

    // Extract data from the $statusArray for
    // easier flow of the function from here on
    $folder_provided_path = $statusArray['folder_provided_path'] ?? null;
    $folder_name = $statusArray['folder_name'];
    $folder_path = $statusArray['folder_path'];
    $folder_exists = $statusArray['folder_exists'];
    $folder_readable = $statusArray['folder_readable'];
    $folder_writable = $statusArray['folder_writable'];
    $file_name = $statusArray['file_name'];
    $file_path = $statusArray['file_path'];
    $file_exists = $statusArray['file_exists'];
    $file_readable = $statusArray['file_readable'];
    $file_writable = $statusArray['file_writable'];
    $functions = $statusArray['functions'];
    $file_raw_entire = $statusArray['file_raw']['entire'];
    $file_raw_return_fn = $statusArray['file_raw']['return function'];

    // This is the assumed full path if a file
    // does not exist and must be created
    $outputNewFile = $folder_path . '/' . $file_name;

    // $table is optional and if set, it must be a string and match the regex
    // for special cases for the folders "funkphp/sql" or "funkphp/validation"
    if (
        isset($table) &&
        (!is_string($table)
            || empty($table)
            || !preg_match('/^(((sd|si|s|i|u|d)=)?[a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i', $table)
            || (!str_contains($folder_provided_path, "funkphp/sql")
                && !str_contains($folder_provided_path, "funkphp/validation")))
    ) {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: $table (or "arg3") must be A Valid Non-Empty String with at least 1 table or several separated by commas! (NO Whitespace is allowed)');
        cli_info('[cli_crud_folder_and_php_file()]: It is meant ONLY for `funkphp/sql` AND `funkphp/validation`!');
        return null;
    }

    // "create_XYZ" CRUD Type which either creates a new folder+new file if not
    // existing OR updates the existing file by adding a new function to it
    // ONLY Single Anonymous Function File is created
    if ($crudType === 'create_new_anonymous_file') {
        // SPECIAL-CASE: 'pipeline' Folder Type can have their files
        // either in "pipeline/post-request" OR "pipeline/request"
        // so we check if the file already exists in either of those and
        // error out if it does! Since these should not exist if being created!
        if ($folderType === 'pipeline') {
            if ($file_exists) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline` Folder!');
                return false;
            } elseif (file_exists($folder_path . '/post-request' . '/' . $file)) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline/post-request` Folder!');
                return false;
            } elseif (file_exists($folder_path . '/request' . '/' . $file)) {
                cli_err_without_exit('Pipeline Function File `' . $file_name . '` already exists in the `funkphp/pipeline/request` Folder!');
                return false;
            }
        }
        // Not Special-case but middlewares are also anonymous functions
        // meaning they should not exist in the folder if they should be created!
        elseif ($folderType === 'middlewares') {
            if ($file_exists) {
                cli_err_without_exit('Middleware Function File `' . $file_name . '` already exists in the `funkphp/middlewares` Folder!');
                return false;
            }
        }
        $newFile = cli_default_created_fn_files('anonymous', "N/A", $folder_name, $file_name, null, null);

        // If $newFile is not a string, we error out
        if (!is_string($newFile) || empty($newFile)) {
            cli_err_without_exit('FAILED to create a New Anonymous Function File for Folder `' . $folder_name . '` and File `' . $file_name . '`!');
            cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
            return false;
        }
        // It worked, so we now output it in the folder path with the file name
        if (!$folder_exists || !$folder_readable || !$folder_writable) {
            cli_err_without_exit('Folder `' . $folder_name . '` does NOT exist or is NOT Readable/Writable!');
            cli_info_without_exit('Verify Folder Path `' . $folder_path . '` exists AND is Readable/Writable.');
            return false;
        }
        $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
        if (!$tryOuput) {
            cli_err_without_exit('FAILED to Create a New Anonymous Function File `' . $file_name . '` in Folder `' . $folder_name . '`!');
            cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
            return false;
        } else {
            return true; // Success, file created successfully
        }
    }
    // A NEW FILE WITH A NAMED FUNCTION is created!
    elseif ($crudType === 'create_new_file_and_fn') {
        // NEW ROUTE SubFolder With New File & Fn
        if ($folderType === 'routes') {
            $newFile = cli_default_created_fn_files('named_and_new_file', $methodAndRoute, $folder_name, $file_name, $fn);
            // If $newFile is not a string, we error out
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a New Named Function File for Folder `' . $folder_name . '` and File `' . $file_name . '` with Function Name `' . $fn .  '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            // Folder has already been created so we just try output file
            $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New Anonymous Function File `' . $file_name . '` in Folder `' . $folder_name . '` with Function Name `' . $fn .  '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
        // NEW FILE WITH A NAMED FUNCTION in "funkphp/sql"
        elseif ($folderType === 'sql') {
            $newFile = cli_default_created_fn_files('sql_new_file_and_fn', null, $folder_name, $file_name, $fn, $table);
            // If $newFile is not a string, we error out
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a SQL Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to create a SQL Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
        // NEW FILE WITH A NAMED FUNCTION in "funkphp/validation"
        elseif ($folderType === 'validation') {
            $newFile = cli_default_created_fn_files('validation_new_file_and_fn', null, $folder_name, $file_name, $fn, $table);
            // If $newFile is not a string, we error out
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a Validation Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            $tryOuput = cli_crud_folder_php_file_atomic_write($newFile, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to create a Validation Handler File `' . $file_name . '` with Function Name `' . $fn .  '` in `' . $folder_name . '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
    }
    // A NEW FUNCTION is created in an EXISTING FILE
    elseif ($crudType === 'create_only_new_fn_in_file') {
        // NEW FUNCTION in EXISTING ROUTE SubFolder With Existing File & Fn
        if ($folderType === 'routes') {
            $newFile = cli_default_created_fn_files('named_not_new_file', $methodAndRoute, $folder_name, $file_name, $fn);
            if (!is_string($newFile) || empty($newFile)) {
                cli_err_without_exit('FAILED to create a New Named Function (`' . $fn . '`) for Folder `' . $folder_name . '` and File `' . $file_name . '`!');
                cli_info_without_exit('This is because an Invalid String (or not a String at all) was provided!');
                return false;
            }
            // Concatenate the new function to the existing file by adding it to the
            // "return function" key of the file_raw_entire
            $newFile .= $file_raw_return_fn;

            // We now replace the entire raw part with the $newFile since that now
            // contains the new function as well as the return function at the end
            $fileRaw = str_replace($file_raw_return_fn, $newFile, $file_raw_entire);
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New Named Function (`' . $fn . '`) in the File `' . $file_name . '` in Folder `' . $folder_name . '`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file updated successfully
            }
        }
        // NEW FUNCTION in EXISTING "funkphp/sql" File
        elseif ($folderType === 'sql') {
            $newFile = cli_default_created_fn_files('sql_only_new_fn', null, $folder_name, $file_name, $fn, $table);
            $newFile .= $file_raw_return_fn;
            $fileRaw = str_replace($file_raw_return_fn, $newFile, $file_raw_entire);
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New SQL Function (`' . $fn . '`) in the File `' . $file_name . '` in `funkphp/sql`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
                return false;
            } else {
                return true; // Success, file created successfully
            }
        }
        // NEW FUNCTION in EXISTING "funkphp/validation" File
        elseif ($folderType === 'validation') {
            $newFile = cli_default_created_fn_files('validation_only_new_fn', null, $folder_name, $file_name, $fn, $table);
            $newFile .= $file_raw_return_fn;
            $fileRaw = str_replace($file_raw_return_fn, $newFile, $file_raw_entire);
            $tryOuput = cli_crud_folder_php_file_atomic_write($fileRaw, $outputNewFile);
            if (!$tryOuput) {
                cli_err_without_exit('FAILED to Create a New Validation Function (`' . $fn . '`) in the File `' . $file_name . '` in `funkphp/validation`!');
                cli_info_without_exit('Verify that Folder Path `' . $folder_path . '` exists AND is Readable/Writable!');
            } else {
                return true; // Success, file created successfully
            }
        }
    }
    // "delete" CRUD Type which deletes a named function from the file
    // and if that was the last named function, it deletes the file as well
    // meaning for just an anonymous function file, it deletes the file
    elseif (($crudType === 'delete')) {
        // First we check that the folder AND file exist, are readable & writable
        if (!$folder_exists || !$folder_readable || !$folder_writable) {
            cli_err_without_exit('Folder `' . $folder_name . '` does NOT exist or is NOT readable/writable!');
            cli_info_without_exit('Please check the Folder Path `' . $folder_path . '` and ensure it exists AND is readable/writable.');
            cli_info_without_exit('Because of this, it has not been determined whether the intended File actually exists in the possibly correct folder!');
            return false;
        }
        if (!$file_exists || !$file_readable || !$file_writable) {
            cli_err_without_exit('File `' . $file_name . '` does NOT exist or is NOT readable/writable!');
            return false;
        }
        // If $fn is not set, we assume we want to delete the entire file
        if (!isset($fn) || empty($fn)) {
            // Safety-check, $functions should be an empty array now if we assumed
            // this was a file with just a single anonymous function!
            if (is_array($functions) && !empty($functions)) {
                cli_err_without_exit('Function(s) FOUND in the File `' . $file_name . '` when trying to Delete it as a File with a Single Anonymous Function!');
                cli_important_without_exit('Manually VALIDATE that the File `' . $file_name . '` is indeed a File with ONLY a Single Anonymous Function!');
                return false;
            }
            if (unlink($file_path)) {
                cli_success_without_exit('File `' . $file_name . '` Deleted SUCCESSFULLY!');
            } else {
                cli_err_without_exit('FAILED to Delete the File `' . $file_name . '`!');
                return false;
            }
        }
        // If $fn IS set, we must check if it exists in the file first and then
        // we check if it is the last named function in the file because then
        // we can just delete file instead of just the named function
        elseif (isset($fn) && is_string($fn) && !empty($fn)) {
            // $fn does NOT exist in the file, so we error out
            if (!array_key_exists($fn, $functions)) {
                cli_err_without_exit('Function `' . $fn . '` does NOT exist in the File `' . $file_name . '`!');
                cli_info_without_exit('Please check the File `' . $file_name . '` and ensure the Function `' . $fn . '` exists in it!');
                return false;
            }
            // $fn DOES exist in the file, so we check if it is the last named function
            else {
                if (count($functions) === 1) {
                    // If it is the last named function, we delete the file
                    if (unlink($file_path)) {
                        cli_success_without_exit('Function `' . $fn . '` Deleted SUCCESSFULLY from the File `' . $file_name . '`!');
                        cli_success_without_exit('File `' . $file_name . '` Deleted SUCCESSFULLY due to no more Named Functions in it!');
                    } else {
                        cli_err_without_exit('FAILED to Delete the File `' . $file_name . '`!');
                        return false;
                    }
                } else {
                    // If it is NOT the last named function, we just remove it from the file
                    $fnRaw = $functions[$fn]['fn_raw'] ?? null;
                    if ($fnRaw) {
                        $fileRaw = str_replace($fnRaw, '', $file_raw_entire);
                        if (file_put_contents($file_path, $fileRaw) !== false) {
                            cli_success_without_exit('Function `' . $fn . '` Deleted SUCCESSFULLY from the File `' . $file_name . '`!');
                            cli_info_without_exit('`' . count($functions) - 1 . '` Function(s) left in the File `' . $file_name . '`!');
                        } else {
                            cli_err_without_exit('FAILED to Delete the Function `' . $fn . '` from the File `' . $file_name . '`!');
                            return false;
                        }
                    } else {
                        cli_err_without_exit('Function `' . $fn . '` does NOT exist in the File `' . $file_name . '`!');
                        return false;
                    }
                }
            }
        }
    }
    // Impossible edge-case, we should never reach here
    else {
        cli_err_without_exit('[cli_crud_folder_and_php_file()]: Invalid $crudType provided! Use either "create_new_anonymous_file", "create_new_file_and_fn", "create_only_new_fn_in_file" or "delete" as the $crudType!');
        cli_info('[cli_crud_folder_and_php_file()]: The fact You are seeing this strongly suggests you have called the function directly instead of letting other functions calling it indirectly and you have probably removed the first safety-check at the top of the function!');
        return false;
    }

    // Return the boolean value of $success (false means failed)
    return $success;
}

// Function that writes $fileContent to a temp file and then renames it
// to the $file_path so it is an atomic write operation. Good stuff!
function cli_crud_folder_php_file_atomic_write($fileContent, $file_path)
{
    // Write the content to the temporary file
    $tempFilePath = $file_path . '.tmp';
    if (file_put_contents($tempFilePath, $fileContent) === false) {
        cli_err_without_exit('FAILED to Write Content to Temporary File `' . $tempFilePath . '`!');
        return false;
    }
    // Rename the temporary file to the actual file path
    // Also clean up the temp file if rename fails (@unlink)
    // @ means ignore errors if the temp file does not exist
    if (!rename($tempFilePath, $file_path)) {
        @unlink($tempFilePath);
        cli_err_without_exit('FAILED to Rename Temporary File `' . $tempFilePath . '` back to Correct File Path `' . $file_path . '`!');
        return false;
    }
    return true;
}

// Function that adds Method/Route with its File=>Fn unless Method/Route already
// exists, then it checks if the File=>Fn Key exists for that Method/Route and it
// adds if not. Otherwise it just returns from function. It NEVER exits script!
// IMPORTANT: When successfully adding it does NOT rebuild the Route Files!!!
function cli_add_route_with_file_n_fn_or_return(&$currentRoutes, $method, $route, $file, $fn)
{
    // $currentRoutes must be an array (it can be empty)
    if (!isset($currentRoutes) || !is_array($currentRoutes)) {
        cli_err_without_exit('$currentRoutes must be a Non-Empty Array! NO ROUTE WITH FILE=>FN ADDED!');
        return false;
    }
    // $method, $route, $file and $fn must all be non-empty strings
    if (!isset($method) || !is_string($method) || empty($method)) {
        cli_err_without_exit('$method must be A Valid Non-Empty String! NO ROUTE WITH FILE=>FN ADDED!');
        return false;
    }
    if (!isset($route) || !is_string($route) || empty($route)) {
        cli_err_without_exit('$route must be A Valid Non-Empty String! NO ROUTE WITH FILE=>FN ADDED!');
        return false;
    }
    if (!isset($file) || !is_string($file) || empty($file)) {
        cli_err_without_exit('$file must be A Valid Non-Empty String! NO ROUTE WITH FILE=>FN ADDED!');
        return false;
    }
    if (!isset($fn) || !is_string($fn) || empty($fn)) {
        cli_err_without_exit('$fn must be A Valid Non-Empty String! NO ROUTE WITH FILE=>FN ADDED!');
        return false;
    }
    global $singleTroute;
}
