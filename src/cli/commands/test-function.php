<?php // FunkCLI Command `php funk test-function|test-func|test-funk|test-fn|test-f`
// TEST-FUNCTION: Run a function within `/src/funkphp/<sub_folder>/<file_and_its_inside_function>`
// in order to test something ASAP instead of having to run it through the main pipeline!

/**
 * -----------------------
 * FUNKCLI DEFAULT COMMAND
 * -----------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU UNDERSTAND IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
if (!defined('FUNKPHP_DIR')) {
    cli_err('Expected Constant `FUNKPHP_DIR` in `/src/cli/funk` File was NOT FOUND when it should have been? Try restore the `/src/cli/funk` File using File Versioning Control Tool or by redownloading it from FunkPHP!');
}
if (!defined('FUNKPHP_FILE_PATH_CLI_FUNCTIONS_AND_ALSO_CLASSES') || !is_readable(FUNKPHP_FILE_PATH_CLI_FUNCTIONS_AND_ALSO_CLASSES)) {
    cli_err('Expected Constant `FUNKPHP_FILE_PATH_CLI_FUNCTIONS_AND_ALSO_CLASSES` in `/src/cli/funk` File was NOT FOUND when it should have been? OR; the Expected File `/src/cli/core/cli_classes_with_functions.php` was NOT FOUND when it should have been!? Try restore the `/src/cli/funk` File using File Versioning Control Tool or by redownloading it from FunkPHP!');
}
include_once FUNKPHP_FILE_PATH_CLI_FUNCTIONS_AND_ALSO_CLASSES;
$startPath = FUNKPHP_DIR . '/';
$parts = null;
$parts2 = null;
$folders = null;
$file = null;
$fn = null;
$arg_foldersFileFn = cli_get_cli_input_from_interactive_or_regular($args, 'test:function', 'folder_file_path_and_then_fn_name_inside');
// Folder+File Path is a Non-Empty String with at least 2
// commas meaning one folder, one file, one function at least?
if ((isset($arg_foldersFileFn))
    && (is_string($arg_foldersFileFn))
    && (!empty($arg_foldersFileFn))
    && (substr_count($arg_foldersFileFn, ',') > 1)
) {
    $parts = array_filter(explode(',', $arg_foldersFileFn));
    $parts2 = $parts;
    $fn = array_pop($parts);
    $startPath .= (join('/', $parts)) . '.php';
    if (!is_readable($startPath)) {
        cli_err('Provided & Parsed File Path (with expected Function `' . $fn . '` inside): `' . $startPath . '` was NOT FOUND or IS NOT READABLE. Check Path/File Permisssions!');
    }
    $content = include_once $startPath;
    if ($content === true) {
        cli_err('Provided & Parsed File Path (with expected Function `' . $fn . '` inside): `' . $startPath . '` was FOUND BUT IT SEEMS TO HAVE BEEN INCLUDED ALREADY?!');
    }
    // Try execute function in global scope, then try assumed namespace-scoped
    $c = []; // Fake pseudo global c/context/config variable to use!
    $fnToRun = ("\\funkphp\\" . join("\\", $parts2));
    if (function_exists($fn)) {
        cli_success_without_exit("Test-Running Found Global Function `$fn` in `$startPath` now!");
        $fn($c);
        cli_success("Test-Ran Function `$fn` in `$startPath` which did not exit script so doing it now!");
    } else if (function_exists($fnToRun)) {
        cli_success_without_exit("Test-Running Found Namespace Function `$fnToRun` in `$startPath` now!");
        $fnToRun($c);
        cli_success("Test-Ran Function `$fn` in `$startPath` which did not exit script so doing it now!");
    } else {
        cli_err('Provided & Parsed File Path (with expected Function `' . $fn . '` inside): `' . $startPath . '` was FOUND & INCLUDED but NO FOUND FUNCTION (Globally or Assumed Namespace-scoped) `' . $fn . '` to be tested! (executed)');
    }
} else {
    cli_err('Expected `$arg_foldersFileFn` to be a Non-Empty String containing at least two `,` (commas) for the `php funk test:function` Command!');
}
