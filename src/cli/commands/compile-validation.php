<?php // FunkCLI COMMAND "php funk compile:validation" - Compiles Validation Function inside of Validation File to Optimized Validation Rules
// This Command will compile the Validation Rules inside of a given Validation Function and create Optimized
// Validation Rules which are faster to execute at Runtime. It does this by reading the Validation File and then
// finding the Function inside of it. It then extracts the $DX variable and the return array and uses that to create
// the optimized rules which are then written back to the same Validation Function, replacing the old return array.
// This Command requires confirmation via the `confirm:eval` argument to ensure that the user is aware that the `eval`
// PHP function will be used to parse the $DX variable as a PHP Array. This is a safety mechanism to avoid accidental overwrites
// of existing compiled Validation Functions AND also to confirm that the user is aware of the possible dangers of using `eval`.

// Find & Extract Folder=>File and then the Confirm:eval Argument
$file = null;
$fn = null;
$arg_FolderFile = cli_get_cli_input_from_interactive_or_regular($args, 'compile:validation', 'validationFileFn');
[$file, $fn] = cli_extract_folder_file($arg_FolderFile, 'v_');
$arg_confirmEval = cli_get_cli_input_from_interactive_or_regular($args, 'compile:validation', 'confirmEvalRegex');

// Then go ahead try to compile the Validation Function inside of the Validation File
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
$result = cli_crud_folder_php_file_atomic_write($newFileRaw, $statusArray['file_path']);
if ($result === false) {
    cli_err("FAILED to Write the Compiled Optimized Validation Rules to the File `funkphp/validation/$file.php`! Verify File Permissions and try again!");
} else {
    cli_success_without_exit("SUCCESSFULLY COMPILED Validation Rules to Optimized Rules in Validation Function \"$fn\" in \"funkphp/validation/$file.php\".");
    cli_info("IMPORTANT: Open it in an IDE and press CMD+S or CTRL+S to autoformat the Validation File again!");
}

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `compile:validation` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
