<?php  // FunkCLI COMMAND "php funk make:validation" - Creates Validation File=>Function unless any or both of them already exists
// This Command will create a new Validation File inside of `src/funkphp/validation/` unless it already exists. Then it
// will create a Function inside of that Validation File unless it already exists. The Validation File and the Function Name
// will automatically be prefixed with `v_` to indicate it is a Validation File. Tables are optional.

// Find and/or Create the Validation File and Function
$file = null;
$fn = null;
$tablesProvided = null;
$arg_FolderFile = cli_get_cli_input_from_interactive_or_regular($args, 'make:validation', 'validationFileFn');
[$file, $fn] = cli_extract_folder_file($arg_FolderFile, 's_');
$arg_tables = cli_get_cli_input_from_interactive_or_regular($args, 'make:validation', 'validationTables');
$tablesProvided = $arg_tables ? strtolower($arg_tables) : null;
$statusArray = cli_folder_and_php_file_status("funkphp/validation", $file);

// Folder must always exist or error out hard
if (!$statusArray['folder_path']) {
    cli_err_without_exit("This Folder SHOULD ALWAYS EXIST due to the nature of FunkPHP and its FunkCLI which auto-generates Default Folder on each Command!!");
    cli_info("Verify File Permissions for Subfolders in `funkphp/` and try again since this Folder should be recreated each time your run a Command to the FunkCLI!");
}
// Create new file when it does not exist
if (!$statusArray['file_exists']) {
    cli_info_without_exit("Validation File `$file.php` does not exist in `funkphp/validation`, it will be created along with Validation Function `$fn` inside of it...");
    $createStatus = cli_crud_folder_and_php_file($statusArray, "create_new_file_and_fn", $file, $fn, "validation", null, $tablesProvided);
    if ($createStatus) {
        cli_success_without_exit("SUCCESSFULLY Created Validation File `$file.php` with Validation Function `$fn` in `funkphp/validation`!");
        cli_info_without_exit("The Validation File `$file.php` is now ready to be used in `funkphp/validation`.");
        cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_use_validation(&\$c, '$file', '$fn')`!");
    } else {
        cli_err("FAILED to Create Validation File `$file.php` with Validation Function `$fn` in `funkphp/validation`!");
    }
}
// Or try to create a new function in existing file
else {
    // Function already exists in the file
    cli_info_without_exit("Validation File `$file.php` already exists in `funkphp/validation`, Validation Function `$fn` will be created inside of it unless it already exists...");
    if (isset($statusArray['functions'][$fn])) {
        cli_err_without_exit("Validation Function `$fn` already exists in Validation File `$file.php` in `funkphp/validation`!");
        cli_info("Change Validation File and/or Validation Function Name and try again for `funkphp/validation`!");
    }
    // Function does not exist in the file so
    // crudType "create_only_new_fn_in_file"
    else {
        $createStatus = cli_crud_folder_and_php_file($statusArray, "create_only_new_fn_in_file", $file, $fn, "validation", null, $tablesProvided);
        if ($createStatus) {
            cli_success_without_exit("SUCCESSFULLY Created Validation Function `$fn` in Validation File `$file.php` in `funkphp/validation`!");
            cli_info_without_exit("The Validation File `$file.php` is now ready to be used in `funkphp/validation`.");
            cli_info("Use it in your Route Function Files in `funkphp/routes/{SubFolder}` by calling `funk_use_validation(&\$c, '$file', '$fn')`!");
        } else {
            cli_err("FAILED to Create Validation Function `$fn` in Validation File `$file.php` in `funkphp/validation`!");
        }
    }
}
