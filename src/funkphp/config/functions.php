<?php
// src/funkphp/config/functions.php - FunkPHP | FunkCLI recreated it 2026-07-06 21:28:00

/*-----------------------------------------------------
    * FUNKPHP AUTOMATICALLY GENERATED/CREATED COMPILED FILE
    * -----------------------------------------------------
    */
// FunkPHP - User-defined Functions Available Globally!
// All functions below - as long as they do not conflict
// in naming with `cli|funk_FUNCTION_NAMES`, they will be
// available in the global namespace{} and should ALWAYS
// pass the `&$c` (IMPORTANT: remember the starting "&"!)
// global config/context variable! You can also pass any other
// variables, but most `funk_FUNCTION_NAME` do not care about other
// arguments after first one since they assume you use $c['shared']
// to grab data being shared between other functions!
// The names of the functions you define below here will be compared
// against the Array String List in `src/cli/core/cli_reserved.php`
// when you run the Compilation Command `php funk build`!
//
// Besides all that above, you can name your function anything you want!
function funak_handle_uncaught_exception(&$c, $e)
{
    // Regex
    $testing = "tset";
}

function funak_set_register_shutdown_function(&$c)
{
    // Regex
    $testing = "tset2";
}
function funk_validate_email(&$c)
{
    if (empty($c['email'])) {
        return false;
    } // 💥 THE REAL TRAP: 0 indentation!
    // Because this is flush to the left, '^}' matches here.
    // The regex stops here and drops the return statement below.

    return filter_var($c['email'], FILTER_VALIDATE_EMAIL);
}
