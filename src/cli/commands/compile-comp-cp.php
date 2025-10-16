<?php
// COMPILE: Compiles a Validation OR SQL File by using its $DX variable
// and (re)genereating a new return (); in the same Validation/SQL File.
// "_eval" is included so the Developer realizes that the eval() function
// will be used as part of this command to compile the file!
$validFirstParams = ['validation_eval', 'v_eval', 'sql_eval', 's_eval'];
$validationAliases = ['v_eval', 'validation_eval'];
$sqlAliases = ['sql_eval', 's_eval'];
// Validate $firstParam is one of the valid
// first parameters and then grab $file=>$fn
if (!in_array($firstParam, $validFirstParams)) {
    cli_err_syntax_without_exit("Invalid first parameter! Please provide a valid first parameter for the `compile` command, either `validation`, `v`, `sql`, or `s` ending with `_eval` since eval() function is needed for this command to run!");
    if (JSON_MODE) {
        cli_info("Use the following JSON format: `{ \"command\": \"compile:<first_param>\", \"arg1\": \"<arg1>\" }`");
    } else {
        cli_info("Type a Command and then up to 6 arguments, like this: `funk compile:<first_param> <arg1>`");
    }
}
if (str_starts_with($firstParam, "v")) {
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1, "v_");
} elseif (str_starts_with($firstParam, "s")) {
    [$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1, "s_");
}

// Compile based on Validation OR SQL File,
// also handle impossible edge-case!
// IF Compile VALIDATION
if (in_array($firstParam, $validationAliases)) {
    $statusArray = cli_folder_and_php_file_status("funkphp/validation", $file);
    // File_path must exist, otherwise we cannot write
    // to it as part of the completed compile process
    if (!$statusArray['file_path']) {
        cli_err("Validation File Path `funkphp/validation/$file.php` does NOT Exist! Provide a Valid Validation File=>Function to Compile where the Exact File Path also exists!");
    }
    // Validate file exists, is readable, writable, and contains the correct function
    // and that its $DX variable is set and also its return array is set so both can be used!
    if (!$statusArray['file_exists']) {
        cli_err("Validation File `funkphp/validation/$file.php` does NOT Exist in `funkphp/validation`! Provide a valid Validation File=>Function to Compile!");
    }
    if (!$statusArray['file_readable']) {
        cli_err("Validation File `funkphp/validation/$file.php` is NOT Readable! Please check the File Permissions and try again!");
    }
    if (!$statusArray['file_writable']) {
        cli_err("Validation File `funkphp/validation/$file.php` is NOT Writable! Please check the File Permissions and try again!");
    }
    if (!isset($statusArray["functions"][$fn])) {
        cli_err("Validation File `funkphp/validation/$file.php` does NOT contain a Function named `$fn`! Please provide a Valid Function Name inside `$file.php` to Compile!");
    }
    if (!isset($statusArray["functions"][$fn]['dx_raw'])) {
        cli_err_without_exit("Validation Function `$fn` inside File `funkphp/validation/$file.php` does NOT contain a \$DX Variable which should be an Array! Please provide a Valid \$DX Variable inside the Function `$fn` to Compile!");
        cli_info("Make sure it is indented using CMD+S or CTRL+S to autoformat the Validation File!");
    }
    if (!isset($statusArray["functions"][$fn]['return_raw'])) {
        cli_err_without_exit("Validation Function `$fn` inside File `funkphp/validation/$file.php` does NOT contain the needed return array()! Please provide a Valid return Array inside the Function `$fn` to Compile!");
        cli_info("Make sure it is indented using CMD+S or CTRL+S to autoformat the Validation File!");
    }

    // Attempt using eval() to extract the $DX variable which should be parsed as a valid PHP Array
    $matchedSimpleSyntax = $statusArray["functions"][$fn]['dx_raw'];
    $matchedReturnStmt = $statusArray["functions"][$fn]['return_raw'];
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
        cli_info_without_exit("Found \"\$DX\" Variable Parsed as a Valid PHP Array!");
    }

    // This contains the optimized validation rules which will then replace the "$matchedReturnStmt"
    // The function can error out on its own so we do not need to check for the return value!
    $optimizedRuleArray = cli_convert_simple_validation_rules_to_optimized_validation($evalCode, $file, $fn);

    // Convert the optimized rule array to a string with ";\n" at the end
    $optimizedRuleArrayAsStringWithReturnStmt = "return " . var_export($optimizedRuleArray, true) . ";\n";

    // Copy of the Entire Function which contains BOTH the $DX and the return array
    $fileRawCopy = $statusArray["file_raw"]["entire"];
    $fnCopy = $statusArray["functions"][$fn]['fn_raw'];
    $fnReturnCopy = $statusArray["functions"][$fn]['return_raw'];
    $replaced = str_replace(
        $matchedReturnStmt,
        $optimizedRuleArrayAsStringWithReturnStmt,
        $fnCopy
    );
    $newFileRaw = str_replace(
        $fnCopy,
        $replaced,
        $fileRawCopy
    );
    $result = file_put_contents($statusArray['file_path'], $newFileRaw);
    if ($result === false) {
        cli_err("FAILED to Write the Compiled Optimized Validation Rules to the File `funkphp/validation/$file.php`! Verify File Permissions and try again!");
    } else {
        cli_success_without_exit("SUCCESSFULLY COMPILED Validation Rules to Optimized Rules in Validation Function \"$fn\" in \"funkphp/validation/$file.php\".");
        cli_info("IMPORTANT: Open it in an IDE and press CMD+S or CTRL+S to autoformat the Validation File again!");
    }
}
// IF Compile SQL
elseif (in_array($firstParam, $sqlAliases)) {
    $statusArray = cli_folder_and_php_file_status("funkphp/sql", $file);
    // File_path must exist, otherwise we cannot write
    // to it as part of the completed compile process
    if (!$statusArray['file_path']) {
        cli_err("SQL File Path `funkphp/sql/$file.php` does NOT Exist! Provide a Valid SQL File=>Function to Compile where the Exact File Path also exists!");
    }
    // Validate file exists, is readable, writable, and contains the correct function
    // and that its $DX variable is set and also its return array is set so both can be used!
    if (!$statusArray['file_exists']) {
        cli_err("SQL File `funkphp/sql/$file.php` does NOT Exist in `funkphp/sql`! Provide a valid SQL File=>Function to Compile!");
    }
    if (!$statusArray['file_readable']) {
        cli_err("SQL File `funkphp/sql/$file.php` is NOT Readable! Please check the File Permissions and try again!");
    }
    if (!$statusArray['file_writable']) {
        cli_err("SQL File `funkphp/sql/$file.php` is NOT Writable! Please check the File Permissions and try again!");
    }
    if (!isset($statusArray["functions"][$fn])) {
        cli_err("SQL File `funkphp/sql/$file.php` does NOT contain a Function named `$fn`! Please provide a Valid Function Name inside `$file.php` to Compile!");
    }
    if (!isset($statusArray["functions"][$fn]['dx_raw'])) {
        cli_err_without_exit("SQL Function `$fn` inside File `funkphp/sql/$file.php` does NOT contain a \$DX Variable which should be an Array! Please provide a Valid \$DX Variable inside the Function `$fn` to Compile!");
        cli_info("Make sure it is indented using CMD+S or CTRL+S to autoformat the SQL File!");
    }
    if (!isset($statusArray["functions"][$fn]['return_raw'])) {
        cli_err_without_exit("SQL Function `$fn` inside File `funkphp/sql/$file.php` does NOT contain the needed return array()! Please provide a Valid return Array inside the Function `$fn` to Compile!");
        cli_info("Make sure it is indented using CMD+S or CTRL+S to autoformat the SQL File!");
    }

    // Attempt using eval() to extract the $DX variable which should be parsed as a valid PHP Array
    $matchedSimpleSyntax = $statusArray["functions"][$fn]['dx_raw'];
    $matchedReturnStmt = $statusArray["functions"][$fn]['return_raw'];
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
        cli_info_without_exit("Found \"\$DX\" Variable Parsed as a Valid PHP Array!");
    }

    // This contains the optimized validation rules which will then replace the "$matchedReturnStmt"
    // The function can error out on its own so we do not need to check for the return value!
    $optimizedSQLArray = cli_convert_simple_sql_query_to_optimized_sql($evalCode, $file, $fn);

    // We validate the optimized SQL Query String by using the Prepared Statement that should not fail
    // If it fails, we will catch the exception and inform the Developer. It could fail due to actual
    // invalid SQL String Syntax or because of a mismatch between the Table Configuration in `tables.php`
    // and the actual Table in the MySQL DBMS (e.g. phpMyAdmin, Adminer, etc.) assuming it exists!
    $dbConnect =  cli_db_connect();
    $queryToTest = $optimizedSQLArray['sql'] ?? null;
    if ($queryToTest === null || !is_string_and_not_empty($queryToTest)) {
        cli_err_without_exit("The optimized SQL Query is Empty or NOT a Valid String in SQL Function \"$fn\" in \"$file.php\".");
        cli_info("Check if indeed the `sql` key was provided from the returned Optimized SQL Array Variable?");
    }
    try {
        cli_info_without_exit("Testing the Optimized SQL Query String from SQL Function \"$fn\" in \"$file.php\".");
        $stmt = $dbConnect->prepare($queryToTest);
    } catch (mysqli_sql_exception $e) {
        cli_err_without_exit("The Optimized SQL Query String FAILED during Statement Preparing (from SQL Function \"$fn\" in \"$file.php\").");
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
    cli_success_without_exit("[COMPILED SQL STRING VALIDATED IN DBMS] The SQL Query String in SQL Function \"$fn\" in \"$file.php\" was Successfully Validated with 0 Errors When Sending it Prepared to the local MySQL DBMS!");
    cli_info_without_exit("Attempting adding the entire Optimized SQL Array as the returned value in SQL Function \"$fn\" in \"$file.php\"!");
    // Convert the optimized SQL array to a string with ";\n" at the end
    $optimizedSQLArrayAsStringWithReturnStmt = "return " . var_export($optimizedSQLArray, true) . ";\n";

    // Copy of the Entire Function which contains BOTH the $DX and the return array
    $fileRawCopy = $statusArray["file_raw"]["entire"];
    $fnCopy = $statusArray["functions"][$fn]['fn_raw'];
    $fnReturnCopy = $statusArray["functions"][$fn]['return_raw'];
    $replaced = str_replace(
        $matchedReturnStmt,
        $optimizedSQLArrayAsStringWithReturnStmt,
        $fnCopy
    );
    $newFileRaw = str_replace(
        $fnCopy,
        $replaced,
        $fileRawCopy
    );
    $result = file_put_contents($statusArray['file_path'], $newFileRaw);
    if ($result === false) {
        cli_err("FAILED compiling SQL Query to Optimized SQL in SQL Function \"$fn\" in \"$file.php\". Verify File Permissions and try again!");
    } else {
        cli_success_without_exit("[ENTIRE SQL ARRAY COMPILED] SUCCESSFULLY COMPILED SQL Query to Optimized SQL in SQL Function \"$fn\" in \"funkphp/sql/$file.php\".");
        cli_info("IMPORTANT: Open it in an IDE and press CMD+S or CTRL+S to autoformat the SQL Handler File again!");
    }
}
// ELSE IMPOSSIBLE EDGE-CASE
else {
    cli_err("Impossible Edge-Case where \$firstParam is NOT a Valid Validation OR SQL File Type! Please provide a valid first parameter for the `compile` command, either `validation`, `v`, `sql`, or `s`!");
}
