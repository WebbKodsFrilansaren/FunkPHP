<?php  // FunkCLI COMMAND "php funk make:sql" - Creates SQL File=>Function unless any or both of them already exists
// This Command will create a new SQL File inside of `src/funkphp/sql/` unless it already exists. Then it
// will create a Function inside of that SQL File unless it already exists. The SQL File and the Function Name
// will automatically be prefixed with `s_` to indicate it is a SQL File.

// Find and/or Create the SQL File and Function
$file = null;
$fn = null;
$tablesProvided = null;
$arg_FolderFile = cli_get_cli_input_from_interactive_or_regular($args, 'make:sql', 'sqlFileFn');
[$file, $fn] = cli_extract_folder_file($arg_FolderFile, 's_');
$arg_sqlType = cli_get_cli_input_from_interactive_or_regular($args, 'make:sql', 'sqlQType');
$arg_tables = cli_get_cli_input_from_interactive_or_regular($args, 'make:sql', 'sqlTables');
$tablesProvided = strtolower($arg_sqlType) . '=' . strtolower($arg_tables);
$statusArray = cli_folder_and_php_file_status("funkphp/sql", $file);

// Folder must always exist or error out hard
if (!$statusArray['folder_path']) {
    cli_err_without_exit("This Folder SHOULD ALWAYS EXIST due to the nature of FunkPHP and its FunkCLI which auto-generates Default Folder on each Command!!");
    cli_info("Verify File Permissions for Subfolders in `funkphp/` and try again since this Folder should be recreated each time your run a Command to the FunkCLI!");
}
// Create new file when it does not exist
if (!$statusArray['file_exists']) {
    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "sql", null, $tablesProvided);
    if ($createStatus) {
        cli_success_without_exit("SUCCESSFULLY Created SQL File `$file.php` with SQL Function `$fn` in `funkphp/sql`!");
        cli_info_without_exit("The SQL File `$file.php` is now ready to be used in `funkphp/sql`.");
        cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_load_sql(&\$c, '$file', '$fn')` and then `funk_use_sql(&\$c, \$loadedSQLArray, \$optionalInputData, \$hydrateAfterQuery)`!");
    } else {
        cli_err("FAILED to Create SQL File `$file.php` with SQL Function `$fn` in `funkphp/sql`!");
    }
}
// Or try to create a new function in existing file
else {
    // Function already exists in the file
    cli_info_without_exit("SQL File `$file.php` already exists in `funkphp/sql`, SQL Function `$fn` will be created inside of it unless it already exists...");
    if (isset($statusArray['functions'][$fn])) {
        cli_err_without_exit("SQL Function `$fn` already exists in SQL File `$file.php` in `funkphp/sql`!");
        cli_info("Change SQL File and/or SQL Function Name and try again for `funkphp/sql`!");
    }
    // Function does not exist in the file so
    // crudType "create_only_new_fn_in_file"
    else {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "sql", null, $tablesProvided);
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created SQL Function `$fn` in SQL File `$file.php` in `funkphp/sql`!");
            cli_info_without_exit("The SQL File `$file.php` is now ready to be used in `funkphp/sql`.");
            cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_load_sql(&\$c, '$file', '$fn')` and then `funk_use_sql(&\$c, \$loadedSQLArray, \$optionalInputData, \$hydrateAfterQuery)`!");
        } else {
            cli_err("FAILED to Create SQL Function `$fn` in SQL File `$file.php` in `funkphp/sql`!");
        }
    }
}
