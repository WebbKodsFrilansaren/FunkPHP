<?php // THIS IS JUST OLD RDP (Route, Data, Page) CODE STUFF, MOSTLY FOR "GOIND DOWN MEMORY LANE"-PURPOSES!
/*** IMPORTANT: NONE OF THE CODE BELOW IS USED ANYWHERE IN FunkPHP - JUST FOR YOUR INFORMATION! ***/

// OK, FIRST IS JUST OLD "FunkPHP" code when I thought I could make a train of functions that would be called
// in a pipeline and then return the result of the last function in the pipeline! I figured out that it wasn't
// a good idea so I went with something else. But fun to check what could have been, don't ya think? ^_^
// This function works as a pipeline and processes the request through a series of functions
// The "outer" part means this is the one that takes the main step functions whereas
// the "inner" part means this is the one that takes the inner step functions such as
// the "o" (option(s)) key in the array each specific step function might come across!
function outerFunktionTrain(&$req, &$d, &$p, $globalConfig, $listOuterFunctionsNamesAsKeysWithTheirArgsAsAssociatedValues)
{
    function innerFunktionTrain(&$req, &$d, &$p, $globalConfig, $listOuterFunctionsNamesWithCorrespondingOptionsNames, $listOuterFunctionsNamesWithCorrespondingOptionsArgs) {}

    // Two functions to run the fail and ok functions that are inside
    // of the "o_ok" and "o_fail" options of the outer functions!
    function h_run_fail_functions($fnNameWithArg, $callerName, &$req, &$d, &$p, $globalConfig)
    {
        $runPriority = $globalConfig['fphp_o_fail_priorities'][$callerName] ?? null;
        $failFns = $fnNameWithArg ?? null;
        if ($runPriority == null || $failFns == null) {
            return fail("[h_run_fail_functions]: Optional Fail Function or its priorities not found for function $callerName.");
        }

        // failFns is an array where each element is "fnName=value" format so we need to iterate through it
        // and split each element by "=" to get the function name and its argument(s)
        $parsedFailFunctions = [];
        $finalFns = [];
        foreach ($failFns as $failFn) {
            $parts = explode("=", $failFn, 2); // Split by "=" and limit to 2 parts
            if (count($parts) == 2) {
                $parsedFailFunctions[$parts[0]] = $parts[1] ?? null;
                $finalFns[$runPriority[$parts[0]]][$parts[0]] = $parsedFailFunctions[$parts[0]] ?? null;
            }
        }
        ksort($finalFns);
        echo "<br>Parsed Fail Functions: <br>";
        //var_dump($parsedFailFunctions);
        var_dump($finalFns); // REMOVE LATER!!!

        // Now we have an associative array where the key is the function name and the value is the argument(s)
        // We need to order the functions based on their priorities. The final key sort (ksort) will be done later
        // in the code so that we can run them in the order of their priorities.
        // $orderedArgs = [];
        // foreach ($runPriority as $fnName => $priority) {
        //     if (isset($parsedFailFunctions[$fnName])) {
        //         $orderedArgs[$priority] = [$fnName => $parsedFailFunctions[$fnName]];
        //     }
        // }
    }
    function h_run_ok_functions($fnNameWithArg, $callerName, &$req, &$d, &$p, $globalConfig)
    {
        $runPriority = $globalConfig['fphp_o_ok_priorities'][$callerName] ?? null;
        $okFns = $fnNameWithArg ?? null;
        if ($runPriority == null || $okFns == null) {
            return fail("[h_run_fail_functions]: Optional Ok Function or its priorities not found for function $callerName.");
        }
        // okFns is an array where each element is "fnName=value" format so we need to iterate through it
        // and split each element by "=" to get the function name and its argument(s)
        $parsedokFunctions = [];
        $finalFns = [];
        foreach ($okFns as $okFn) {
            $parts = explode("=", $okFn, 2); // Split by "=" and limit to 2 parts
            if (count($parts) == 2) {
                $parsedokFunctions[$parts[0]] = $parts[1];
                $finalFns[$runPriority[$parts[0]]][$parts[0]] = $parsedokFunctions[$parts[0]] ?? null;
            }
        }
        ksort($finalFns);
        echo "<br>Parsed Ok Functions: <br>";
        //var_dump($parsedFailFunctions);
        var_dump($finalFns); // REMOVE LATER!!!

        // Now we have an associative array where the key is the function name and the value is the argument(s)
        // We need to order the functions based on their priorities. The final key sort (ksort) will be done later
        // in the code so that we can run them in the order of their priorities.
        // $orderedArgs = [];
        // foreach ($runPriority as $fnName => $priority) {
        //     if (isset($parsedokFunctions[$fnName])) {
        //         $orderedArgs[$priority] = [$fnName => $parsedokFunctions[$fnName]];
        //     }
        // }

    }

    // Loop through "$listOuterFunctionsNames" and turn the function names into the key of corresponding
    $fns = [];

    // Populate the $fns array with function names, arguments, and initial return value
    foreach ($listOuterFunctionsNamesAsKeysWithTheirArgsAsAssociatedValues as $functionName => $args) {
        $fns[$functionName] = [
            "fn_name" => $functionName ?? null,
            "args" => $args ?? [],
            "return_value" => "UNDEFINED",
            "o_ok" => h_has_ok_options($args[0]) ?? [],
            "o_fail" => h_has_fail_options($args[0]) ?? [],
        ];
    }

    // Now, you would typically loop through the $fns array (or based on a priority list)
    // to execute the functions and update their return values.
    // Pass reference to modify the original array!!!
    foreach ($fns as $functionName => &$functionData) {
        if ($functionData["fn_name"] == null) {
            echo "<br>Function name is null for function $functionName!<br>"; // REMOVE LATER!!!
            return fail("[outerFunktionTrain]: Function name is null for function $functionName!");
        } else if (!function_exists($functionName)) {
            echo "<br>Function $functionName does not exist!<br>"; // REMOVE LATER!!!
            return fail("[outerFunktionTrain]: Function $functionName does not exist!");
        }
        $argsToPass = $functionData["args"];
        $returnValue = call_user_func_array($functionName, $argsToPass);
        $functionData["return_value"] = $returnValue;

        // Check if user closed the connection (e.g., browser closed) and exit script so no further processing is done
        if (connection_aborted()) {
            break;
            exit;
        }

        // Check current return_value that it is not "UNDEFINED" and also NOT "err" key but true or 1:
        if ($functionData["return_value"] !== "UNDEFINED" && ($functionData["return_value"] === true || $functionData["return_value"] === 1)) {
            // Call the optional "o_ok" functions if they exist
            if (!empty($functionData["o_ok"])) {
                h_run_ok_functions($functionData['o_ok'], $functionName, $req, $d, $p, $globalConfig);
            }
        }

        // Check current return_value that it is not "UNDEFINED" and also NOT "err" key but false or 0:
        else if ($functionData["return_value"] !== "UNDEFINED" && ($functionData["return_value"] === false || $functionData["return_value"] === 0)) {
            // Call the optional "o_fail" functions if they exist
            if (!empty($functionData["o_fail"])) {
                h_run_fail_functions($functionData['o_fail'], $functionName,  $req, $d, $p, $globalConfig);
            }
        }

        // If value IS "UNDEFINED" here
        else if ($functionData["return_value"] === "UNDEFINED") {
        }

        // Return value is "err" key here
        else {
            fail("[outerFunktionTrain]: Return value is an error key when running function $functionName!");
        }

        // REMOVE LATER!!!
        echo "<br>Return value of $functionName: " . strval($functionData["return_value"]) . "<br>";
    }
}

// This SQL Function creates the $type string by taking
// a reference variable (&$type) and returning the type
// of the $value variable which is then added to a variable
// that is NOT inside of this function but referred to!
// It is used by "rdp_sql_execute_statement_query" function!
function rdp_sql_binded_value_type(&$type, $value, $forcedType = "")
{
    // First check if $forcedType was written
    // Forced type is "s","i","d" or "b" for string, integer, double or blob
    if ($forcedType != "") {
        $type .= $forcedType;
        return;
    }
    // Check type of $value and add correct type to referenced $type
    // meaning it is NOT inside of this function but it is being changed
    // by the function itself!

    // Check if the string represents a numeric value
    // This is to check against "-1" and similar negative values
    if (is_numeric($value)) {
        // If so, treat it as a float or an integer depending on its value
        if ((int)$value == $value) {
            $type .= 'i'; // Treat as integer
        } else {
            $type .= 'd'; // Treat as double (float)
        }
    }
    // Is value an integer?
    elseif (is_int($value)) {
        $type .= 'i';
    }
    // Is value a float?
    elseif (is_float($value)) {
        $type .= 'd';
    }
    // Is value a string?
    elseif (is_string($value)) {
        $type .= 's';
    }
    // Then it must be blob!
    else {
        $type .= 'b';
    }
}

// SQL Execute Statement Query Function that takes a SQL Query and an array of parameters and returns the result set
// Function that executes a SQL Query with parameters and returns the result set or null when anything failed.
// It takes a mysqli connection, a query string, an array of parameters, a fetch type (ASSOC, NUM, BOTH) and
// a boolean for returning the last inserted id. Last part only works for INSERT queries.
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
function rdp_sql_execute_statement_query($mysqli, $query, $params = array(), $fetchType = "ASSOC", $return_last_inserted_id = false)
{
    // Prepare the query
    $stmt = $mysqli->prepare($query);

    // Check for errors in query preparation
    if (!$stmt) {
        return null;
    }

    // Santizie the parameters with escape string and strip tags before binding them
    $params = array_map(function ($param) use ($mysqli) {
        return $mysqli->real_escape_string(($param));
    }, $params);

    // Bind parameters dynamically
    if (!empty($params)) {
        // Get the types of parameters by calling the valueType function
        $types = '';

        // Loop through the parameters and get their types either automatically and/or forced
        foreach ($params as $index => $param) {
            // Check if the index of the array element is associative in terms of being a string
            // instead of a number and then send it to the binded_value_type function with the forced type
            if (mb_strtolower($index) == "s" || mb_strtolower($index) == "i" || mb_strtolower($index) == "d" || mb_strtolower($index) == "b") {
                rdp_sql_binded_value_type($types, $param, mb_strtolower($index));
            } // Otherwise call the binded_value_type  function normally
            else {
                // Call the binded_value_type  function to get the type of the parameter
                rdp_sql_binded_value_type($types, $param);
            }
        }

        // Make the array a list if it is not already (string keys were used)
        if (!array_is_list($params)) {
            $params = array_values($params);
        }

        // Bind parameters (where params is a list array)
        $stmt->bind_param($types, ...$params);

        // Check for errors in parameter binding
        if ($stmt->errno) {
            $stmt->close();
            return null;
        }
    }

    // Execute the query
    $stmt->execute();

    // Check for errors in query execution
    if ($stmt->errno) {
        $stmt->close();
        return null;
    }


    // Check if query started with SELECT
    if (str_starts_with(strtoupper($query), "SELECT")) {
        // Get & return the result set or just null
        $result = $stmt->get_result();
        if ($result) {
            // Check fetch type and return the result set according to it
            $rows = null;
            if (mb_strtoupper($fetchType) == "ASSOC") {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
            } elseif (mb_strtoupper($fetchType) == "NUM") {
                $rows = $result->fetch_all(MYSQLI_NUM);
            } elseif (mb_strtoupper($fetchType) == "BOTH") {
                $rows = $result->fetch_all(MYSQLI_BOTH);
            } else {
                $rows = $result->fetch_all();
            }
            $stmt->close();
            return $rows;
        } else {
            $stmt->close();
            return null;
        }
    }
    // Otherwise check if it it is INSERT, UPDATE or DELETE so we check affected rows
    elseif (
        str_starts_with(strtoupper($query), "INSERT")
        || str_starts_with(strtoupper($query), "UPDATE")
        || str_starts_with(strtoupper($query), "DELETE")
    ) {
        // Get & return the affected rows or just null
        $result = $stmt->affected_rows;
        $stmt->close();
        if ($result > 0) {
            // Return the last inserted id if requested and the query was an INSERT
            if ($return_last_inserted_id && str_starts_with(strtoupper($query), "INSERT")) {
                return $mysqli->insert_id;
            } else {
                return true;
            }
        } else {
            return null;
        }
    }  // Otherwise it is a different query type besides CRUD, so fetch any result and return it
    else {
        // Get & return the result set or just null
        $result = $stmt->get_result();
        if ($result) {
            $stmt->close();
            return $result;
        } else {
            $stmt->close();
            return null;
        }
    }
}

// SQL Function to check if a single column value exists in a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to check if a single column value exists first!
function rdp_sql_check_single_column_value_exists($mysqli, $table, $column, $value)
{
    // Prepare the SQL Query
    $query = "SELECT COUNT(1) FROM `$table` WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $value,
    ];

    // Execute the SQL Query
    $result = rdp_sql_execute_statement_query($mysqli, $query, $params, "NUM");

    // Return true if the value exists, false otherwise
    return $result[0][0] > 0;
}

// SQL Function to create a user into the "rdp_users" table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate email/username NOT already taken first!
function rdp_sql_create_single_user($mysqli, $user, $return_last_inserted_id = false)
{
    // Prepare the SQL Query
    $query = "INSERT INTO rdp_users (user_id, user_username, user_fullname, user_email, user_password, user_created_at, user_updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepare the parameters
    $params = [
        $user['user_id'],
        $user['user_username'],
        $user['user_fullname'],
        $user['user_email'],
        $user['user_password'],
        $user['user_created_at'],
        $user['user_updated_at'],
    ];

    // Execute the SQL Query with or without returning the last inserted id
    return rdp_sql_execute_statement_query($mysqli, $query, $params, [], $return_last_inserted_id);
}

// SQL Function to delete a single row from chosen table by custom value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to delete row first!
function rdp_sql_delete_single_row_by_single_value($mysqli, $table, $where_column, $where_value)
{
    // Prepare the SQL Query
    $query = "DELETE FROM `$table` WHERE `$where_column` = ?";

    // Prepare the parameters
    $params = [
        $where_value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to delete a single row from chosen table by id
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to delete row first!
function rdp_sql_delete_single_row_by_single_id($mysqli, $table, $id)
{
    // Prepare the SQL Query
    $query = "DELETE FROM `$table` WHERE id = ?";

    // Prepare the parameters
    $params = [
        $id,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to delete a user from the "rdp_users" table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to delete user first!
function rdp_sql_delete_single_user($mysqli, $user_id)
{
    // Prepare the SQL Query
    $query = "DELETE FROM rdp_users WHERE user_id = ?";

    // Prepare the parameters
    $params = [
        $user_id,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to insert a single row into a chosen table WITHOUT returning the last inserted id
// IMPROTANT: "user_id" is NOT the last insert id, but it is used as Foreign Key in all default RDP tables!
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to insert row first!
function rdp_sql_insert_single_row_without_last_insert_id($mysqli, $table, $columnsSeparatedByCommas, $valuesAsAnArray)
{
    // Check if $columns is an array or a string and if string check if "," or ", " is used
    $columns = "";
    if (!is_string($columnsSeparatedByCommas) || !is_array($valuesAsAnArray)) {
        return null;
    } elseif (is_string($columnsSeparatedByCommas)) {
        if (!str_contains($columnsSeparatedByCommas, ",")) {
            $columns = $columnsSeparatedByCommas;
        } elseif (mb_strpos($columnsSeparatedByCommas, ", ") !== false) {
            $columns = str_replace(", ", ",", $columnsSeparatedByCommas);
        } elseif (mb_strpos($columnsSeparatedByCommas, ",") !== false) {
            $columns = str_replace(",", ",", $columnsSeparatedByCommas);
        }
    }

    // Prepare the SQL Query
    $query = "INSERT INTO `$table` ($columns) VALUES (";

    // Add the correct amount of question marks to the query
    for ($i = 0; $i < count($valuesAsAnArray); $i++) {
        $query .= "?";
        if ($i < count($valuesAsAnArray) - 1) {
            $query .= ", ";
        }
    }

    // Close the query
    $query .= ")";

    // Prepare the parameters
    $params = $valuesAsAnArray;

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, [], false);
}

// SQL Function to insert a single row into a chosen table WITH returning the last inserted id
// IMPROTANT: "user_id" is NOT the last insert id, but it is used as Foreign Key in all default RDP tables!
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to insert row first!
function rdp_sql_insert_single_row_with_last_insert_id($mysqli, $table, $columnsSeparatedByCommas, $valuesAsAnArray)
{
    // Check if $columns is an array or a string and if string check if "," or ", " is used
    $columns = "";
    if (!is_string($columnsSeparatedByCommas) || !is_array($valuesAsAnArray)) {
        return null;
    } elseif (is_string($columnsSeparatedByCommas)) {
        if (!str_contains($columnsSeparatedByCommas, ",")) {
            $columns = $columnsSeparatedByCommas;
        } elseif (mb_strpos($columnsSeparatedByCommas, ", ") !== false) {
            $columns = str_replace(", ", ",", $columnsSeparatedByCommas);
        } elseif (mb_strpos($columnsSeparatedByCommas, ",") !== false) {
            $columns = str_replace(",", ",", $columnsSeparatedByCommas);
        }
    }

    // Prepare the SQL Query
    $query = "INSERT INTO `$table` ($columns) VALUES (";

    // Add the correct amount of question marks to the query
    for ($i = 0; $i < count($valuesAsAnArray); $i++) {
        $query .= "?";
        if ($i < count($valuesAsAnArray) - 1) {
            $query .= ", ";
        }
    }

    // Close the query
    $query .= ")";

    // Prepare the parameters
    $params = $valuesAsAnArray;

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, [], true);
}

// SQL Function to insert into one table and then use its last insert id to insert into a second table with a foreign key (last insert id)
// IMPROTANT: "user_id" is NOT the last insert id, but it is used as Foreign Key in all default RDP tables!
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to insert row first!
function rdp_sql_insert_into_two_tables_with_last_insert_id($mysqli, $table1, $columnsSeparatedByCommas1, $valuesAsAnArray1, $table2, $columnsSeparatedByCommas2whereLastColumnIsForeignKey, $valuesAsAnArray2whereLastInsertIdIsLastElement)
{
    // Insert into the first table and get the last inserted id
    $last_inserted_id = rdp_sql_insert_single_row_with_last_insert_id($mysqli, $table1, $columnsSeparatedByCommas1, $valuesAsAnArray1);

    // Check if the first insert was successful
    if ($last_inserted_id) {

        // Insert into the second table with the last inserted id
        $valuesAsAnArray2whereLastInsertIdIsLastElement[] = $last_inserted_id;
        return rdp_sql_insert_single_row_without_last_insert_id($mysqli, $table2, $columnsSeparatedByCommas2whereLastColumnIsForeignKey, $valuesAsAnArray2whereLastInsertIdIsLastElement);
    } else {
        return null;
    }
}

// SQL Function to select a single row from a chosen table by any column value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select single row first!
function rdp_sql_select_single_row_by_single_value($mysqli, $table, $column, $value, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT * FROM `$table` WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, $fetchType);
}

// SQL Function to select chosen columns from a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select columns first!
function rdp_sql_select_columns_from_single_table($mysqli, $table, $columns, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT $columns FROM `$table`";

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, [], $fetchType);
}

// SQL Function to select chosen columns from a chosen table by any column value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select columns first!
function rdp_sql_select_columns_by_single_value($mysqli, $table, $columns, $column, $value, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT $columns FROM `$table` WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, $fetchType);
}

// SQL Function to select and return a single value from a single column from a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select single column value first!
function rdp_sql_select_single_value_from_single_column($mysqli, $table, $select_col, $where_col, $colval, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT `$select_col` FROM `$table` WHERE `$where_col` = ?";

    // Prepare the parameters
    $params = [
        $colval,
    ];

    // Execute the SQL Query
    $result = rdp_sql_execute_statement_query($mysqli, $query, $params, $fetchType);

    // Return the result or null
    return $result ? $result[0][$select_col] : null;
}

// SQL Function to retrieve all values of a single column from a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to retrieve all values of a single column first!
function rdp_sql_select_all_values_of_single_column($mysqli, $table, $column)
{
    // Prepare query and check for errors
    $query = "SELECT `$column` FROM `$table`";

    // Execute the SQL Query
    $result = rdp_sql_execute_statement_query($mysqli, $query, [], "ASSOC");

    // Return the result as an array of the column or null
    if ($result) {
        $column = array_column($result, $column);
        return $column;
    } else {
        return null;
    }
}

// SQL Function to update single column value from a chosen table by chosen value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to update single column value first!
function rdp_sql_update_single_column_value_by_single_value($mysqli, $table, $column, $value, $new_value)
{
    // Prepare the SQL Query
    $query = "UPDATE `$table` SET `$column` = ? WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $new_value,
        $value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to update a user in the "rdp_users" table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to update user first!
function rdp_sql_update_single_user($mysqli, $user)
{
    // Prepare the SQL Query
    $query = "UPDATE rdp_users SET user_username = ?, user_fullname = ?, user_email = ?, user_password = ?, user_updated_at = ? WHERE user_id = ?";

    // Prepare the parameters
    $params = [
        $user['user_username'],
        $user['user_fullname'],
        $user['user_email'],
        $user['user_password'],
        $user['user_updated_at'],
        $user['user_id'],
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to update a single row from a chosen table by chosen value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to update single row first!
function rdp_sql_update_single_row_by_single_column_value($mysqli, $table, $set_columnsSeparatedByCommas, $newValuesAsAnArray, $where_column, $where_value)
{
    // Check if $set_columns is an array or a string and if string check if "," or ", " is used
    $set_columns = "";
    if (!is_string($set_columnsSeparatedByCommas) || !is_array($newValuesAsAnArray)) {
        return null;
    } elseif (is_string($set_columnsSeparatedByCommas)) {
        if (!str_contains($set_columnsSeparatedByCommas, ",")) {
            $set_columns = $set_columnsSeparatedByCommas . " = ?";
        } elseif (mb_strpos($set_columnsSeparatedByCommas, ", ") !== false) {
            $set_columns = str_replace(", ", " = ?, ", $set_columnsSeparatedByCommas) . " = ?";
        } elseif (mb_strpos($set_columnsSeparatedByCommas, ",") !== false) {
            $set_columns = str_replace(",", " = ?,", $set_columnsSeparatedByCommas) . " = ?";
        }
    }
    // Prepare the SQL Query
    $query = "UPDATE `$table` SET $set_columns WHERE `$where_column` = ?";

    // Prepare the parameters
    $params = [
        ...$newValuesAsAnArray,
        $where_value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}
