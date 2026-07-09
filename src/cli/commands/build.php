<?php // FunkCLI Command `php funk build|b`
// BUILD: Generates the `FunkPHPDeployment.php" mega file that is
// supposed to include: core, routes, & middlewares for deployment
// to a server. It can also optionally compile pages as well and/or
// compress all files into a single zip file for easier deployment!

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
$manifest = null; // contains all hashes needed
$embedPages = false; // imlpemented later
$compilePages = false;
$compressDeployment = false;
$showAllErrors = false; // implemented later
$allowModifiedCore = true; // implemented later
$ignoreUnknownConnsDrivers = false; // implemented later
$skipBrokenRoutes = false; // implemented later
$skipCompilingValidation = false; // implemented later
$skipCompilingSQL = false; // implemented later

// Initialize an array to hold the different compiled sections of the file
// and its sub parts so we can add sub parts as needed to the entire file!
$HTTPS_KERNEL_DISPATCH_FUNCTION_FOUND = false;
//
$deploymentBuffer = [];
$deploymentConfigBuffer = [];
$deploymentFunctionsBuffer = [];
$deploymentValidationBuffer = [];
$deploymentSQLBuffer = [];
$deploymentPagesBuffer = [];
$deploymentExtraFlagsBuffer = [];
$deploymentPath = FUNKPHP_FILE_PATH_DEPLOYMENT_FILE;

// Files that will be loaded using "cli_folder_and_php_file_status()"
$coreFunctionsFile = null;
$pipelineRequestArrayFile = null;
$pipelineRoutesArrayFile = null;
$userFunctionsFile = null;

// Inside of $args[] array on 1 or 2 we can have the optional
// configs for "--compile-pages" and/or "--compress-deployment"
// or the single one "--both" for faster typing.
// The $args[1] is always the command so we do not look for it!
// So we iterate through $args on 1 & 2 to find those optional flags
foreach ($args as $arg) {
    if (is_string($arg)) {
        $flag = strtolower(trim($arg));
        if ($flag === "--both-compile-and-embed-pages") {
            $compilePages = true;
            $compressDeployment = true;
        } else if ($flag === "--compile-pages") {
            $compilePages = true;
        } else if ($flag === "--embed-pages") {
            $embedPages = true;
        } else if ($flag === "--compress-deployment") {
            $compressDeployment = true;
        } else if ($flag === "--skip-broken-routes") {
            $skipBrokenRoutes = true;
        } else if ($flag === "--skip-compiling-validation") {
            $skipCompilingValidation = true;
        } else if ($flag === "--skip-compiling-sql") {
            $skipCompilingSQL = true;
        } else if ($flag === "--show-error-reporting-all-errors") {
            $showAllErrors = true;
        } else if ($flag === "--ignore-unknown-conns-drivers") {
            $ignoreUnknownConnsDrivers = true;
        } else if ($flag === "--allow-modified-core") {
            $allowModifiedCore = true;
        }
    }
}
cli_info_without_exit("### FunkCLI Compiling & Building `FunkPHPDeployment.php` with the following options:");
cli_info_without_exit("#### Allow Modified Core Files: " . ($allowModifiedCore ? "YES (the hashes of files in `src/funkphp/core`) will NOT be checked and you are on your own regarding what happens with the output)" : "NO"));
cli_info_without_exit("#### Ignore Unknown Connection Drivers: " . ($ignoreUnknownConnsDrivers ? "YES (even unknown types of credentials in `src/funkphp/config/conns.php`) will be added and included in the output)" : "NO"));
cli_info_without_exit("#### Skip Broken Routes: " . ($skipBrokenRoutes ? "YES (invalid routes will NOT be pruned in output)" : "NO"));
cli_info_without_exit("#### Skip Compiling Validation: " . ($skipCompilingValidation ? "YES (Validation Functions will NOT be compiled before output)" : "NO"));
cli_info_without_exit("#### Skip Compiling SQL: " . ($skipCompilingValidation ? "YES (SQL Functions will NOT be compiled before output)" : "NO"));
cli_info_without_exit("#### Do Include `error_reporting = E_ALL` in Deployment File: " . ($showAllErrors ? "YES (warning: sensitive info could be leaked in production!" : "NO"));
cli_info_without_exit("#### Do Compile Pages: " . ($compilePages ? "YES (pages will be compiled and output to 'funkphp/pages'" : "NO"));
cli_info_without_exit("#### Do Embed Pages: " . ($embedPages ? "YES (pages will be inside of the FunkPHPDeployment.php File)" : "NO"));
cli_info_without_exit("#### Do Compress Deployment: " . ($compressDeployment ? "YES (FunkPHPDeployment.php, pages and public_html folder will be in a single compresed file)" : "NO"));

// The actual compiling & building steps
cli_info_without_exit("G`### Step 1 STARTS ###` Loading, Validating & Compiling `config.php` File ('Config' in FunkGUI)...");
$configWarnsAndErrs = [];
$cConfig = null;

// Attempt loading /src/funkphp/core/manifest.php
if (
    !defined("FUNKPHP_FILE_MANIFEST_CORE")
    || !file_exists(FUNKPHP_FILE_MANIFEST_CORE)
    || !is_readable(FUNKPHP_FILE_MANIFEST_CORE)
) {
    cli_err("The Constant `FUNKPHP_FILE_MANIFEST_CORE` containing exact Path to `/src/funkphp/core/manifest.php` (FunkPHP Manifest File with Version Number & Hashes) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE! Path: `" . (FUNKPHP_FILE_MANIFEST_CORE ?? "[NOT_DEFINED]") . "`");
}
$manifest = require_once FUNKPHP_FILE_MANIFEST_CORE;
if (!is_array($manifest) || array_is_list($manifest)) {
    cli_err("The FunkPHP Manifest File with Version Number & Hashes (`/src/funkphp/core/manifest.php`) is NOT ARRAY when it must be! Path: `" . (FUNKPHP_FILE_MANIFEST_CORE ?? "[NOT_DEFINED]") . "`");
}

// We look for defined constant "FUNKPHP_FILE_PATH_C_CONFIG_FILE" which should point to the c.php file
// and we try to see if we can find it, read it, and whether it is an associative array which it should be!
if (!defined("FUNKPHP_FILE_PATH_DEPLOYMENT_FILE")) {
    cli_err("The Constant `FUNKPHP_FILE_PATH_DEPLOYMENT_FILE` containing exact Path to `FunkPHPDeployment.php` (FunkPHP Deployment Output File after Successful Building/Compiling) is NOT DEFINED! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
}
if (!defined("FUNKPHP_FILE_PATH_C_CONFIG_FILE") || !file_exists(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    cli_err("The Constant `FUNKPHP_FILE_PATH_C_CONFIG_FILE` containing exact Path to `c.php` (FunkPHP Configuration File) is NOT DEFINED or Exact File Path does NOT EXIST/IS WRONG! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
}
// File exists but is not readable?
if (!is_readable(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    cli_err("The `c.php` (FunkPHP Configuration File) exists but is NOT READABLE! Please check the File Permissions and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
}
// File exists, is readable, but is NOT an associative array? We need to check if it returns an array when included!
if (is_readable(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    $cConfig = require_once FUNKPHP_FILE_PATH_C_CONFIG_FILE;
    if (!is_array($cConfig) || array_is_list($cConfig)) {
        cli_err("The `c.php` (FunkPHP Configuration File) exists and is Readable but does NOT return an Associative Array! Please check the File Contents and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
    }
}

// The main keys that must exist on $c array level for FunkPHP! So we iterate
// through using array_key_exists now in $cConfig to see if those actually exist!
// those that do not exist we add to the "$configWarnsAndErrs" as errors!
$cArrayKeysThatMustExist = [
    'FUNKPHP_ONLINE',
    'FUNKPHP_USE_HTTPS',
    'FUNKPHP_USE_PREPARE_URI',
    'FUNKPHP_USE_VENDOR',
    'FUNKPHP_CUSTOM_URI_NORMALIZER',
    'FUNKPHP_CUSTOM_EXCEPTION_HANDLER',
    'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION',
    'FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION',
    'INI_SETS',
    'BASEURLS',
    'SESSION',
    'shared',
    'custom',
    'classes',
    'credentials',
    'connections',
    'req',
    'd',
    'v',
    'v_ok',
    'v_ok_files',
    'v_config',
    'v_data',
    'p',
    'files',
    'err',
];
foreach ($cArrayKeysThatMustExist as $key) {
    if (!array_key_exists($key, $cConfig)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Key '$key' NOT FOUND in `c.php` (FunkPHP Configuration File)! Please check the File Contents and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
    }
}
foreach ($cConfig as $cKey => $val) {
    if (
        !in_array($cKey, $cArrayKeysThatMustExist)
        && ($cKey !== "<ENTRY>")  && ($cKey !== "ROUTES") // Only applicable to the local web dev environment!
        && ($cKey !== 0) && ($cKey !== 1) && ($cKey !== 2) && ($cKey !== 3) // 0-3 are the !defined parts in the $c returned array!
    ) {
        cli_warning_without_exit("IGNORED: Key '$cKey' in `c.php` (FunkPHP Configuration File) will be ignored. Any custom variables should be in `\$c['custom']! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE "FUNKPHP_ONLINE" Key and that "INI_SETS" is an associative array
$fphpo_iniChecks = [];
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_ONLINE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'boolean', "Key is needed for `pl_https_redirect` to work properly! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_USE_HTTPS"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'boolean', "Key is needed to work properly! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_USE_PREPARE_URI"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'boolean', "Key is needed to know whether to Prepare Request URI or not for each incoming HTTP(S) Request! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_USE_VENDOR"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'boolean', "Key is needed to know whether to Use Vendor/Composer-based Classes or not for each incoming HTTP(S) Request! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
// If vendor wanna be used, validate it actually exists, but this will NOT validate the entire contents of vendor!
if ($cConfig['FUNKPHP_USE_VENDOR'] === true) {
    if (
        !defined('FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE')
        || !file_exists(FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE)
        || !is_readable(FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE)
    ) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE` containing exact Path to `src/funkphp/vendor/autoload.php` IS NOT DEFINED, or FILE IS NOT FOUND or FILE IS NOT READABLE. Set `FUNKPHP_USE_VENDOR` (via FunkCLI or FunkGUI) to false if you do not wanna use Vendor/Composer-based classes! Path: `" . (FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE ?? "[NOT_DEFINED]") . "`");
        cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for Main Keys 'FUNKPHP_ONLINE', 'FUNKPHP_USE_HTTPS', 'FUNKPHP_USE_PREPARE_URI', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION' & 'INI_SETS' in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
    }
    cli_info_without_exit("IMPORTANT ABOUT VENDOR/COMPOSER BEING USED: FunkCLI does NOT validate the contents inside of the `src/funkphp/vendor` Directory and/or that it even exists after deployment, it just assumes it exists when you use `funk_use_vendor()`!");
}
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_CUSTOM_EXCEPTION_HANDLER"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'string|null', "Key is needed to know which Custom Exception Handler to use (or default is used; set it a string or null) for each incoming HTTP(S) Request! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'string|null', "Key is needed to know  Custom Registered Shutdown Function to use (or default is used; set it a string or null) for each incoming HTTP(S) Request! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
$fphpo_iniChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["INI_SETS"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_iniChecks), $configWarnsAndErrs, "cli_err", 'array-associative|array-empty', "Key is for optionally running an Array of `ini_set()` that cannot be manually set in php.ini due to shared host environments or other kind of permission reason. Leave it as an Empty Array if not used! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
// Load Custom user-defined functions (NOT classes(!)) when its constant is defined, file exists and is readable.
if (
    !defined("FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED")
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is NOT DEFINED! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}
if (
    !file_exists(FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is DEFINED but its File NOT FOUND! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}
if (
    !is_readable(FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is DEFINED & FOUND but is NOT READABLE! File Permission issues? Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}
// Load User Functions globally by now inside $userFunctionsFile
// but first quick check if both are existing non-empty strings but are exactly the same
// meaning same handler for both different types? Not recommended, so we do not allow.
if (
    isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
    && isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
    && is_string($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
    && is_string($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
    && !empty(trim($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']))
    && !empty(trim($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']))
    && $cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'] === $cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Exception Handler `{$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']}` & the Custom Registered Shutdown Function `{$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']}` are exactly the same in `src/funkphp/core/c.php` (Global Configuration Array File)! Check Function Names in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one(s)! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}
$userFunctionsFile = cli_folder_and_php_file_status("funkphp/config", "functions.php", false, true);
var_dump($userFunctionsFile);
exit;
if (!$userFunctionsFile['file_exists'] || !$userFunctionsFile['folder_readable']) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configuration File `src/funkphp/config/functions.php` (User-defined Globally Available Functions) WAS NOT FOUND or IS NOT READABLE when it should have been? This might now show other errors below this one!");
}
if (!$userFunctionsFile['functions_same_count']) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configuration File `src/funkphp/config/functions.php` (User-defined Globally Available Functions) WAS FOUND USING Either Regex or Tokenizer but not both Indicating some Formatting Issue Inside File? (check for any trailing comment at the end of a function block{} <-- here)");
}
if (count($userFunctionsFile['fn_names_duplicates']) > 0) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "There are Duplicate Function Names (`" . join(", ", array_keys($userFunctionsFile['fn_names_duplicates'])) .  "`) in `src/funkphp/config/functions.php` (User-defined Globally Available Functions)! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for `src/funkphp/config/functions.php` (User-defined Globally Available Functions) and try again! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}
if (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])) {
    if (!isset($userFunctionsFile["functions"][$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']])) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Exception Handler `{$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']}` NOT FOUND in `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is DEFINED & FOUND but is NOT READABLE! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
}
if (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])) {
    if (!isset($userFunctionsFile["functions"][$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']])) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Registered Shutdown Function `{$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']}` NOT FOUND in `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is DEFINED & FOUND but is NOT READABLE! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
}
// c['FUNKPHP_CUSTOM_URI_NORMALIZER'] must be a string or null and it cannot be the same function as
// custom exception handler function OR custom register shutdown function!
$fphpo_customChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_CUSTOM_URI_NORMALIZER"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_customChecks), $configWarnsAndErrs, "cli_err", 'string|null', "Key is needed to know whether to use Custom URI Normalizer Function or Default one that prepares a Normalized Request URI for each incoming HTTP(S) Request! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for Main Keys 'FUNKPHP_ONLINE', 'FUNKPHP_USE_HTTPS', 'FUNKPHP_USE_PREPARE_URI', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION' & 'INI_SETS' in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
if (isset($cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'])) {
    if (isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']])) {
        if (
            $userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']]['fn_starts_with_cli']
            || $userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']]['fn_starts_with_funk']
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Request URI Normalizer Function `{$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']}` in `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is STARTS with `cli_` or `funk_` in its name which is NOT ALLOWED! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
        }
    } else {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Request URI Normalizer Function `{$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']}` is NOT FOUND in `src/funkphp/config/functions.php` (User-defined Globally Available Functions)! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
    if (
        isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
        && isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
        && is_string($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
        && is_string($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
        && !empty(trim($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']))
        && !empty(trim($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']))
        && ($cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'] === $cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']
            || $cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'] === $cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
    ) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Request URI Normalizer Function `{$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']}` in `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is SAME AS CUSTOM REGISTER SHUTDOWN FUNCTION or CUSTOM EXCEPTION HANDLER FUNCTION! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
}
// Check that Reserved Functions Array $reserved_functions is available by now
if (!defined('FUNKPHP_FILE_PATH_CLI_RESERVED')) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `src/cli/core/cli_reserved.php` (The Array String List of Reserved Functions) is NOT DEFINED! Path: `" . (FUNKPHP_FILE_PATH_CLI_RESERVED ?? "[NOT_DEFINED]") . "`");
}
if (!is_array($reserved_functions) || empty($reserved_functions)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Array String List of Reserved Functions is NOT AN ARRAY or IS EMPTY! Path: `" . (FUNKPHP_FILE_PATH_CLI_RESERVED ?? "[NOT_DEFINED]") . "`");
}
// User-defined Functions should not conflict with reserved function names used by FunkCLI and FunkPHP
if (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])) {
    if (in_array($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Registered Shutdown Function `{$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']}` HAS CONFLICTING FUNCTION NAME with The Array String List of Reserved Function Names `src/cli/core/cli_reserved.php`. Please change your Function Name in `src/funkphp/config/functions.php` and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). OR set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
}
if (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])) {
    if (in_array($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom Exception Handler `{$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']}` HAS CONFLICTING FUNCTION NAME with The Array String List of Reserved Function Names `src/cli/core/cli_reserved.php`. Please change your Function Name in `src/funkphp/config/functions.php` and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). OR set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
}

// Validate $c['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'] that allow for a custom HTTPS Kernel Dispatcher Function meaning
// it would replace the in-built one that is triggered by having the 'pl_https_kernel_dispatch' in Pipeline Request Array!
$fphpo_customChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($fphpo_customChecks), $configWarnsAndErrs, "cli_err", 'string|null', "Key is needed to know whether to use a Custom (from `/src/funkphp/config/functions.php`) Pipeline Request Function to `handle Route Matching` and `Execution of Route Middlewares & Route Pipeline Functions`! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
if (isset($cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'])) {
    if (in_array($cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom HTTPS Kernel Dispatch Pipeline Request Function `{$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']}` HAS CONFLICTING FUNCTION NAME with The Array String List of Reserved Function Names `src/cli/core/cli_reserved.php`. Please change your Function Name in `src/funkphp/config/functions.php` and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). OR set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
    if (isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']])) {
        if (
            $userFunctionsFile['functions'][$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']]['fn_starts_with_cli']
            || $userFunctionsFile['functions'][$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']]['fn_starts_with_funk']
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom HTTPS Kernel Dispatch Pipeline Request Function `{$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']}` in `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is STARTS with `cli_` or `funk_` in its name which is NOT ALLOWED! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
        }
    } else {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configured Custom HTTPS Kernel Dispatch Pipeline Request Function `{$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']}` is NOT FOUND in `src/funkphp/config/functions.php` (User-defined Globally Available Functions)! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
    if (
        (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']) && $cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'] === $cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])
        || (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']) && $cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'] === $cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])
        || (isset($cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']) && $cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'] === $cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'])
    ) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Configured Custom HTTPS Kernel Dispatch Pipeline Request Function `{$cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']}` in `src/funkphp/config/functions.php` (User-defined Globally Available Functions) is SAME AS CUSTOM REGISTER SHUTDOWN FUNCTION, CUSTOM EXCEPTION HANDLER FUNCTION or CUSTOM REQUEST URI NORMALIZER FUNCTION! Check Function Name in Your Functions and/or in Configuration File via FunkCLI/FunkGUI (`src/funkphp/core/c.php`). Set it to `null` if you wanna use default one! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    }
}

cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for Main Keys 'FUNKPHP_ONLINE', 'FUNKPHP_USE_HTTPS', 'FUNKPHP_USE_PREPARE_URI', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION', 'FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION', & 'INI_SETS' in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE BASEURLS Array Subkeys Paths!
$baseURLSChecks = [];
$baseURLSChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["BASEURLS", "LOCAL"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($baseURLSChecks), $configWarnsAndErrs, "cli_err", '/^http:\/\//', "Must start with http://, then you can do any way you want! Example: 'http://localhost/funkphp/src/public_html/' or 'http://my-app.local/'");
$baseURLSChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["BASEURLS", "ONLINE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($baseURLSChecks), $configWarnsAndErrs, "cli_err", '/^https:\/\//', "Must start with https://, then you can do any way you want! Example: 'https://www.funkphp.com/' or 'https://my-app.com/'");
$baseURLSChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["BASEURLS", "BASEURL"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($baseURLSChecks), $configWarnsAndErrs, "cli_err", 'string');
$baseURLSChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["BASEURLS", "BASEURL_URI"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(
    end($baseURLSChecks),
    $configWarnsAndErrs,
    "cli_err",
    '/^\/[a-zA-Z0-9_\-\/]*$/',
    "Must start with a leading slash '/'. Example: '/api/v1/users' or '/my-app/'"
);
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'BASEURL' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE SESSION Array Subkeys Paths!
$sessionChecks = [];
// 1. Session Driver
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "driver"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", '/(^files$)|(^redis$)/', "Must be either 'files' or 'redis'.");
// 2. Cookie Structural Branch Layer Check & 3. Cookie Name Check
cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES"], $configWarnsAndErrs, "cli_err");
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_NAME"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", '/^[a-zA-Z0-9_\-]+$/', "Must be a valid alphanumeric cookie name identifier (underscores and hyphens allowed). Example: 'fphp_id'");
// 4. Session Lifetime (Positive Integer check via Callback)
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_LIFETIME"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", (function ($val) {
    return is_int($val) && $val >= 0;
}), "Must be a positive integer representing seconds. Example: 28800 (8 hours), or 0 (expires when browser closes).");
// 5. Session Cookie URL Path Path Matching
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_PATH"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", '/^\/[a-zA-Z0-9_\-\/]*$/', "Must be a valid URL scope root path starting with a forward slash. Example: '/' or '/app/'");
// 6. Session Domain Check
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_DOMAIN"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", 'string', "Must be a valid domain string matching your server context. Example: 'localhost' or 'my-app.com'");
// 7. Session Secure (HTTPS enforced flag)
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_SECURE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", 'bool', "Must be a boolean value indicating if cookies are restricted to HTTPS transfers.");
// 8. Session HttpOnly (XSS cross-site scripting shield flag)
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_HTTPONLY"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", 'bool', "Must be a boolean value locking script access to the session identifier.");
// 9. Session SameSite Strategy (Custom Whitelist Array check via Callback)
$sessionChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["SESSION", "COOKIES", "SESSION_SAMESITE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($sessionChecks), $configWarnsAndErrs, "cli_err", (function ($val) {
    return in_array($val, ['Lax', 'Strict', 'None'], true);
}), "Must Match Native Browser Specifications Exactly: 'Lax', 'Strict', or 'None'.");
// Halt execution loop immediately if any errors crept into the pipeline setup
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'SESSION' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE CLASSES Array Subkeys Paths!
$classesChecks = [];
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes"], $configWarnsAndErrs, "cli_err");
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes", "vendor"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($classesChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with vendor-based class instances (from src/funkphp/vendor) during runtime.");
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes", "user"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($classesChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array  as it will be filled with user-defined class instances (from src/funkphp/classes) during runtime.");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'classes' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE CONNECTIONS Array Subkeys Paths!
$connectionsChecks = [];
$connectionsChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["connections"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($connectionsChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with Connections like Databases Instances (from src/funkphp/config/conns.php) and other connection-related service-instances during runtime and now during compilation the Connections Configuration Array (`src/funkphp/config/conns.php`) will be validated first and then be stored in [\$c -> 'credentials'].");
$connectionsChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["credentials"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($connectionsChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with First Validated Credentials stored in `src/funkphp/config/conns.php`!");

// Load & validate the array structure inside of `src/funkphp/config/conns.php`
if (
    !defined('FUNKPHP_FILE_PATH_CONNS_CONFIG')
    || !file_exists(FUNKPHP_FILE_PATH_CONNS_CONFIG)
    || !is_readable(FUNKPHP_FILE_PATH_CONNS_CONFIG)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_CONNS_CONFIG` containing Exact File Path to Your Credentials for your Connections is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. IMPORTANT: Look in your `src/funkphp/config` as `src/funkphp/config/README_IN_IDE.php` File should have been created _before you read this_. Rename that file to `conns.php` as it contains starting templates for how Connection Profiles should look like. Paths: " . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . " & " . (FUNKPHP_FILE_PATH_README_IN_IDE_CONFIG ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'connections' & 'credentials' in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
}
$connsPayload = require FUNKPHP_FILE_PATH_CONNS_CONFIG;
if (!is_array($connsPayload)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Credentials File for your Connections (`src/funkphp/config/conns.php`) is NOT A VALID ARRAY. Each array key should be the name of a connection, and then include a driver and its corresponding connection details for that type of driver. Path: `" . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'connections' & 'credentials' in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");
}
// 3. Define the Blueprint Schemas based on the 'driver' key
$driverBlueprints = [
    'mysqli' => [
        'host'     => ['type' => 'string',  'required' => true],
        'user'     => ['type' => 'string',  'required' => true],
        'password' => ['type' => 'string',  'required' => true],
        'database' => ['type' => 'string',  'required' => true],
        'port'     => ['type' => 'integer', 'required' => true],
        'charset'  => ['type' => 'string',  'required' => false],
    ],
    'pdo_mysql' => [
        'host'     => ['type' => 'string',  'required' => true],
        'user'     => ['type' => 'string',  'required' => true],
        'password' => ['type' => 'string',  'required' => true],
        'database' => ['type' => 'string',  'required' => true],
        'port'     => ['type' => 'integer', 'required' => false],
        'charset'  => ['type' => 'string',  'required' => false],
    ],
    'pdo_pgsql' => [
        'host'     => ['type' => 'string',  'required' => true],
        'user'     => ['type' => 'string',  'required' => true],
        'password' => ['type' => 'string',  'required' => true],
        'database' => ['type' => 'string',  'required' => true],
        'port'     => ['type' => 'integer', 'required' => false], // Defaults to 5432 in PHP
        'sslmode'  => ['type' => 'string',  'required' => false], // e.g., 'require', 'disable'
    ],
    // --- KEY-VALUE & CACHING STACKS ---
    'redis' => [
        'host'     => ['type' => 'string',  'required' => true],
        'port'     => ['type' => 'integer', 'required' => false], // Defaults to 6379
        'password' => ['type' => 'string',  'required' => false],
        'database' => ['type' => 'integer', 'required' => false], // Redis DB index (e.g., 0)
        'timeout'  => ['type' => 'integer', 'required' => false],
    ],
    'memcached' => [
        // Useful for clustering. If you prefer simple singular setup, change type to string/integer
        'servers'  => ['type' => 'array',   'required' => true],  // Expects [['host', port, weight]]
    ],
    // --- DOCUMENT & NOSQL STACKS ---
    'mongodb' => [
        // MongoDB heavily favors unified connection strings (DSN) over separate parameters
        'dsn'      => ['type' => 'string',  'required' => true],  // e.g., 'mongodb://user:pass@host:27017'
        'database' => ['type' => 'string',  'required' => true],
        'options'  => ['type' => 'array',   'required' => false], // Optional driver array tuning options
    ],
    'dynamodb' => [
        'region'   => ['type' => 'string',  'required' => true],  // e.g., 'us-east-1'
        'version'  => ['type' => 'string',  'required' => true],  // AWS SDK requires a string date like 'latest'
        'key'      => ['type' => 'string',  'required' => false], // Optional if using IAM Roles/ENV
        'secret'   => ['type' => 'string',  'required' => false],
        'endpoint' => ['type' => 'string',  'required' => false], // 🌟 Vital for routing to LocalStack in dev!
    ],
];
// 4. Loop over every profile defined by the developer
if (is_array($connsPayload)) {
    foreach ($connsPayload as $profileName => $profileData) {
        if (!is_array($profileData)) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Connection Profile [{$profileName}] must be defined as an Associative Array Configuration Block. Path: `" . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");
            continue;
        }
        // Validate that 'driver' exists and is a valid string
        if (!isset($profileData['driver']) || !is_string($profileData['driver'])) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Connection Profile [{$profileName}] is missing a Valid String 'driver' key needed to know how to parse other (optional) keys. Path: `" . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");
            continue;
        }
        $driver = $profileData['driver'];
        // Verify we actually support this database engine blueprint
        if (!array_key_exists($driver, $driverBlueprints)) {
            if ($ignoreUnknownConnsDrivers) {
                cli_info_without_exit("Unknown Connection Profile `$profileName` ignored due to Compilation flag `--ignore-unknown-conns-drivers`.");
            } else {
                cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Connection Profile [{$profileName}] specifies an Unknown Framework Driver: '{$driver}'. Current Framework Drivers supported for Data Schema Validation are: `" . join(", ", array_keys($driverBlueprints)) . "`! Include the compilation flag `--ignore-unknown-conns-drivers` to ignore validating unknown driver types. Path: `" . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");
            }
            continue;
        }
        $blueprint = $driverBlueprints[$driver];
        // 5. Cross-reference profile data keys against our blueprint matrix
        foreach ($blueprint as $keyName => $rules) {
            $keyExists = array_key_exists($keyName, $profileData);
            if (!$keyExists && $rules['required']) {
                cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Profile [{$profileName}] ({$driver}): Missing required configuration parameter '{$keyName}'. Path: `" . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");
                continue;
            }
            if ($keyExists) {
                $actualValue = $profileData[$keyName];
                $expectedType = $rules['type'];
                // Explicit strict primitive type evaluations
                $typeMatches = false;
                if ($expectedType === 'string' && is_string($actualValue))   $typeMatches = true;
                if ($expectedType === 'integer' && is_int($actualValue))     $typeMatches = true;
                if ($expectedType === 'boolean' && is_bool($actualValue))    $typeMatches = true;
                if ($expectedType === 'array' && is_array($actualValue))      $typeMatches = true;
                if (!$typeMatches) {
                    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Profile [{$profileName}] ({$driver}): Key '{$keyName}' must be an exact type mismatch! Expected data type: [{$expectedType}]. Path: `" . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");
                }
            }
        }
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'connections' & 'credentials' in the `c.php` (FunkPHP Configuration File) and try again! Paths: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . " & " . (FUNKPHP_FILE_PATH_CONNS_CONFIG ?? "[NOT_DEFINED]") . "`");

// VALIDATE REQ Array Subkeys Paths!
$reqChecks = [];
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'array-associative', "This Associative Array will include Request Information that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "method"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'string|null', "This is the HTTP(S) Request Method (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, etc.) that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "ip"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'string|null', "This is the IP Address of the Client making the HTTP(S) Request that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "time"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'integer|null', "This is the Timestamp of the HTTP(S) Request that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "uri"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the Server-provided Request URI of the HTTP(S) Request that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "query"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'string|null', "This is the Server-provided Request Query String of the HTTP(S) Request that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "base_url_absolute"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'string|null', "This is the FunkPHP-provided Base URL Absolute String of the HTTP(S) Request used for URL Tracking during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "base_url_relative"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'string|null', "This is the FunkPHP-provided Base URL Relative String of the HTTP(S) Request used for URL Tracking during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "auth"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the FunkPHP-provided Auth Value depending on implemention that can be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "route"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This holds the raw matched dynamic route string path template from your route definitions file.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "params"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This will hold extracted dynamic URI route parameters mapped as an associative array upon routing matching completion.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "segments"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This will store exploded URL string path fragments used internally by FunkPHP for structural pattern matching analysis.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "skip_post_response"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'boolean', "This is a boolean toggle flag used to control whether background post-response fast-cgi processes should run or terminate early.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "keep_running_exit"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is a dedicated lifecycle termination pointer used to intercept deep call-stack errors and exit safely.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "code"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'integer', "This is the initial baseline fallback HTTP status code (Defaults to 418 I'm a teapot) until updated by execution loops. You can change its default value in FunkGUI/FunkCLI if needed.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "log"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This must be an Empty Array at boot. It acts as the runtime trace tracker logging debugging events during the current request.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "ua"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This will house the browser client's HTTP User Agent identifier string once incoming server traffic arrives.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "content_type"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This tracks the incoming message payload body payload specification header (e.g., application/json).");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "accept"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This tracks the HTTP Accept response format expectations declared by the browser or API request platform client.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "protocol"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This identifies the incoming server transport protocol details (e.g., HTTP/1.1, HTTP/2, HTTP/3).");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'req' Main Key and its Subkeys in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE 'd', 'v' (and its associated keys), 'p' & 'files' which should ALL be just null at this point!
$dvpfChecks = [];
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["d"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `d` is meant to store ANY data you wanna output later on a HTML Page/View or maybe Return in a JSON Response after a Matched Route during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["v"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `v` is meant to store ANY Validation Errors for a given funk_use_validation() call. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["v_ok"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `v_ok` is meant to store the Boolean Flag for a given funk_use_validation() call for whether it was all OK/validated or not. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["v_ok_files"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `v_ok_files` is meant to Store Referenced Validated Files a given funk_use_validation() call. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["v_config"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Main Key `v_config` is meant to Store Current Global Validation Configuration provided by a given funk_use_validation() call. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["v_data"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `v_data` is meant to Store Validated Data a given funk_use_validation() call. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["p"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `p` is meant to Store Page-related that can be used after a Matched Route during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["files"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `files` is meant to Store Uploaded Files (if applicable). Can be used any time during a Valid HTTP(S) Request!");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'd','v','v_ok','v_ok_files','v_config','v_data','s_data','p','p_config' & 'files' in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE 'err' and its subkeys!
$cErrChecks = [];
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-keys:MAYBE,FUNCTIONS,CLASSES,CONNECTIONS,PIPELINE,MIDDLEWARES,PAGE,VALIDATION,SQL,QUERY', "Main Key `err` is relevant Errors for certain parts during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "MAYBE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "FUNCTIONS"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "CLASSES"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "CONNECTIONS"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "PIPELINE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "MIDDLEWARES"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "PAGE"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "VALIDATION"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "SQL"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
$cErrChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["err", "QUERY"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($cErrChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "This Subkey in `err` should be empty and is filled out during a HTTP(S) Request!");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Key 'err' and its Subkeys in the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// VALIDATE Certain Constants by is using !defined() and error out if not found!
cli_assert_final_value(NULL, $configWarnsAndErrs, "cli_err", 'constants:FUNKPHP_NO_VALUE,FUNKPHP_ALLOW_INSTANCE_OVERWRITE', "These Constants are a MUST for FunkPHPDeployment.php to 'function' properly!");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Required Constants from the `c.php` (FunkPHP Configuration File) and try again! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`");

// Here ### Step 1 is fully validated so now we insert starting point of the `FunkPHPDeployment.php` file into the $deploymentBuffer array for later writing to disk!
cli_success_without_exit("G`### Step 1 DONE ###` Validating & Adding `c.php` (FunkPHP Configuration File) SUCCESSFULLY! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`.");
$BUILD_VERSION = "<?php // FunkPHPDeployment.php | Created: " . date("Y-m-d H:i:s") . " | PHP Version: " .  PHP_VERSION . " | FunkPHP Version: " . (FUNKPHP_VERSION ?? "<Unknown Version>") . " | FunkCLI Version: " . (FUNKCLI_VERSION ?? "<Unknown Version>") . "\n";
$deploymentBuffer[] = "<?php \nnamespace { "; // Opening Global namespace for nows

// Adding Starting Needed Constants First
$deploymentBuffer[] = "define('FUNKPHP_PAGES_DIR', __DIR__ . '/pages');\n";
$deploymentBuffer[] = "define('FUNKPHP_DEPLOYED', true);\n";
$deploymentBuffer[] = "define('FUNKPHP_NO_VALUE', new stdClass());\n";
$deploymentBuffer[] = "define('FUNKPHP_ALLOW_INSTANCE_OVERWRITE'," . FUNKPHP_ALLOW_INSTANCE_OVERWRITE .  ");\n";

// Adding the keys, to the $c variable because there are 4 dynamic values like "$_SERVER['REQUEST_METHOD'] ?? 'GET'" we
// have to add it as pure string instead of var_export! Then we add the $c array file to the entire current Deployment Buffer
// We ALSO REMOVE two entries that are only relevant when running local web dev WITHOUT compiled build!
$cReqReplacements = [
    "'##TOKEN_REQ_METHOD##'" => "\$_SERVER['REQUEST_METHOD'] ?? 'GET'",
    "'##TOKEN_REQ_IP##'" => "\$_SERVER['REMOTE_ADDR'] ?? null",
    "'##TOKEN_REQ_TIME##'" => "\$_SERVER['REQUEST_TIME'] ?? time()",
    "'##TOKEN_REQ_QUERY_STRING##'" => "\$_SERVER['QUERY_STRING'] ?? null",
];
$cConfig['req']['method'] = "##TOKEN_REQ_METHOD##";
$cConfig['req']['ip'] = "##TOKEN_REQ_IP##";
$cConfig['req']['time'] =  "##TOKEN_REQ_TIME##";
$cConfig['req']['query'] = "##TOKEN_REQ_QUERY_STRING##";
unset($cConfig['<ENTRY>']);
unset($cConfig['ROUTES']);
unset($cConfig['req']['current_pipeline']);
unset($cConfig['req']['next_pipeline']);
unset($cConfig['req']['current_middleware']);
unset($cConfig['req']['keep_running_pipeline']);
unset($cConfig['req']['next_middleware']);
unset($cConfig['req']['matched_in']);
unset($cConfig['req']['matched_config']);
unset($cConfig['req']['matched_pipeline']);
unset($cConfig['req']['matched_middlewares']);
$cConfig['credentials'] = $connsPayload;
$compilationSecretToken = "'{{##CONFIG_TOKEN_STRING_THAT_IS_REPLACED_##FUNKPHP_COMPILE_" . bin2hex(random_bytes(32)) . "LATER_BY_COMPLETE_CONFIG_IT_NEEDS_PIPELINE_KEYS_FIRST##}}'##";
$deploymentConfigBuffer[] = "\$c = $compilationSecretToken;\n";
//$deploymentConfigBuffer[] = cli_replace_string_tokens_in_var_exported_string($cReqReplacements, var_export($cConfig, "true")) . ";\n";
$deploymentBuffer[] = implode("", $deploymentConfigBuffer);

// Adding optional /src/funkphp/vendor loading!
if ($cConfig['FUNKPHP_USE_VENDOR'] === true) {
    $deploymentBuffer[] = "require_once __DIR__ . '/vendor/autoload.php';\n";
}

// Adding custom or default Exception Handler Set
$deploymentBuffer[] = "set_exception_handler(function (\\Throwable \$e) use (&\$c) {\n";
if (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])) {
    $deploymentBuffer[] = "\\" . $cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'] . "(\$c, \$e);\n";
} else {
    $deploymentBuffer[] = "\\funk_default_exception_handler(\$c, \$e);\n";
}
$deploymentBuffer[] = "});\n";

// Adding custom or default Register Shutdown Function Set
$deploymentBuffer[] = "register_shutdown_function(function () use (&\$c) {\n";
if (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])) {
    $deploymentBuffer[] = "\\" . $cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'] . "(\$c);";
} else {
    $deploymentBuffer[] = "\\funk_default_register_shutdown_function(\$c);\n";
}
$deploymentBuffer[] = "});\n";

// Add the Almighty Important `ob_start();` - FunkPHP Framework FOLLOWS "ob_start() FOR EVERY REQUEST - ALWAYS, YOU HAVE TO CHANGE IT MANUALLY!"
$deploymentBuffer[] = "ob_start();\n";

// Adding the Functions now! (first user-defined, then in-built functions) where the
// user-defined with same name as the in-built is not allowed since both will be in global namespace!
// USER-DEFINED cannot start with "funk_" or "cli_" but can start with "funk_validate_" for custom validation
// functions.
cli_info_without_exit("G`### Step 2 STARTS ###` Loading, Validating & Compiling Core `functions.php` & User-defined `funkphp => config => functions.php` Files ('User-defined Functions' in 'Config' in FunkGUI)...");
$functionsWarnsAndErrs = [];
if (
    !$allowModifiedCore &&
    (!defined('FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL')
        || !file_exists(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL)
        || !is_readable(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL))
) {
    cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL` containing Exact File Path to FunkPHP Core Functions (`/src/funkphp/core`) needed to work properly is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. Your User-defined Functions you can add/edit/remove are found in `/src/funkphp/config/functions.php`! DO NOT edit FunkPHP Core Functions File! Paths: " . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL ?? "[NOT_DEFINED]") . " & " . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function Files (Core & User-defined) and try again! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}
if (!$allowModifiedCore && (cli_get_hash_calculation_of_a_file(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL) !== $manifest['hashes']['core']['funkphp/core/functions.php'][0])) {
    cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "The FunkPHP Core Functions File `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL` (`funkphp/core/functions.php`) might be modified due to wrong calculated sha-256 hash value. Your User-defined Functions you should add/edit/remove are found in `/src/funkphp/config/functions.php`! DO NOT edit FunkPHP Core Functions File. Check your Git/File Versioning History to see if you can rollback any changes made to the FunkPHP Core Functions File, or Redownload the Files from an Official Source! Paths: " . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL ?? "[NOT_DEFINED]") . " & " . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function Files (Core & User-defined) and try again! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
}

if (!cli_file_constant_defined_file_exists_is_readable("FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES")) {
    cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES` containing exact File Path to FunkPHP Core Functions Templates File (`funkphp/core/function_templates.php`) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. File Permission issues? DO NOT edit FunkPHP Core Functions File. Check your Git/File Versioning History to see if you can rollback any changes made to the FunkPHP Core Functions Templates File, or Redownload the Files from an Official Source! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function (Templates) Files (Core & User-defined) and try again! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES ?? "[NOT_DEFINED]") . "`");
}
$coreFunctionsTemplateFile = cli_folder_and_php_file_status("funkphp/core", "function_templates.php");
$coreFunctionsFile = cli_folder_and_php_file_status("funkphp/core", "functions.php");
foreach ($userFunctionsFile['functions'] as $fnNameUser => $fnValsUser) {
    if (
        isset($coreFunctionsFile['functions'][strtolower($fnNameUser)])
        || in_array(strtolower($fnNameUser), $reserved_functions, true)
    ) {
        cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "User-defined function '$fnNameUser' is already used by FunkPHP/FunkCLI. Please choose rename the function (cannot start with `funk_` or `cli_`) or remove it! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    } else if (
        (str_starts_with(strtolower($fnNameUser), "funk_") &&
            !str_starts_with(strtolower($fnNameUser), "funk_validate_"))
        || str_starts_with(strtolower($fnNameUser), "cli_")
        || (str_starts_with(strtolower($fnNameUser), "funk_validate_")
            && $fnValsUser['fn_exact_name'] !== $fnValsUser['fn_lowercased']
        )
    ) {
        cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "User-defined function '$fnNameUser' starts with `funk_` (but not `funk_validate_`) or `cli_` which is not allowed. Please choose rename the function or remove it! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    } else {
        $deploymentBuffer[] = $fnValsUser['fn_raw'] . "\n";
    }
}
cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for regarding User-defined & In-built Functions! Paths: " . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . " & " . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL ?? "[NOT_DEFINED]") . "`");

// Replace values for a dynamic function stored in /src/funkphp/core/function_templates.php that
// are replaced if the function exists and this is then added as the default Core Function to ouput!
$functionsTemplatesArray = [
    "funk_session_started_or_start_it" => [
        "{{##session_lifetime##}}" =>  $cConfig['SESSION']['COOKIES']['SESSION_LIFETIME'],
        "{{##session_path##}}"  =>  $cConfig['SESSION']['COOKIES']['SESSION_PATH'],
        "{{##session_domain##}}"  =>  $cConfig['SESSION']['COOKIES']['SESSION_DOMAIN'],
        "{{##session_secure##}}"  =>  $cConfig['SESSION']['COOKIES']['SESSION_SECURE'],
        "{{##session_httponly##}}"  =>  $cConfig['SESSION']['COOKIES']['SESSION_HTTPONLY'],
        "{{##session_samesite##}}"  =>  $cConfig['SESSION']['COOKIES']['SESSION_SAMESITE'],
    ],
    "" => ["" => "", "" => "", "" => "", "" => "",],
    "" => ["" => "", "" => "", "" => "", "" => "",],
    "" => ["" => "", "" => "", "" => "", "" => "",],
    "" => ["" => "", "" => "", "" => "", "" => "",],
    "" => ["" => "", "" => "", "" => "", "" => "",],
    "" => ["" => "", "" => "", "" => "", "" => "",],
];

// Add Core Functions and also replace some of them with their dynamic counter-parts from function_templates.php
// in /src/funkphp/core/function_templates.php
// $excludedCoreFunctionsInBuild = some Core Functions are NOT needed as
// they will be either statically inserted or optimized inside of the build!
$excludedCoreFunctionsInBuild = [
    "funk_default_exception_handler" => true,
    "funk_default_register_shutdown_function" => true,
    "funk_match_compiled_route" => true,
    "funk_match_developer_route" => true,
    "funk_run_pipeline_request" => true,
    "funk_run_pipeline_post_response" => true,
];
if (isset($coreFunctionsFile['functions'])) {
    foreach ($coreFunctionsFile['functions'] as $fnNameCore => $fnValsCore) {
        if (isset($excludedCoreFunctionsInBuild[$fnNameCore])) {
            continue;
        } else if (isset($functionsTemplatesArray[$fnNameCore])) {
            $templateRawCode = $coreFunctionsTemplateFile['functions'][$fnNameCore]['fn_raw'] ?? null;
            if (!$templateRawCode) {
                cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "Function '$fnNameCore' is marked for Token Replacement, but its Template was not found inside `/src/funkphp/core/function_templates.php`! The Function Structure must start with `function name(\&\$c`. Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES ?? "[NOT_DEFINED]") . "`");
                cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function (Templates) Files (Core & User-defined) and try again! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES ?? "[NOT_DEFINED]") . "`");
            }
            $compiledTemplate = cli_function_template_token_replacer($functionsTemplatesArray[$fnNameCore], $templateRawCode);
            $deploymentBuffer[] = $compiledTemplate . "\n";
        } else {
            $deploymentBuffer[] = $fnValsCore['fn_raw'] . "\n";
        }
    }
}  // Core Functions not found by the helper function, but is it AllowedModifiedCore false?
else {
    if ($allowModifiedCore === false) {
        cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "The FunkPHP Core Functions File `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL` (`/src/funkphp/core/functions.php`) might be modified due to not finding Any Valid Structured Functions. Your User-defined Functions you should add/edit/remove are found in `/src/funkphp/config/functions.php`! DO NOT edit FunkPHP Core Functions File. Check your Git/File Versioning History to see if you can rollback any changes made to the FunkPHP Core Functions File, or Redownload the Files from an Official Source! Paths: " . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL ?? "[NOT_DEFINED]") . " & " . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
        cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function Files (Core & User-defined) and try again! Path: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`");
    } else {
        cli_warning_without_exit("FunkPHP Core Functions File in `/src/funkphp/core/functions.php` does NOT contain Any Valid Structured Functions (`function name(\&\$c){}`) or Any Functions at all. Modified Core is set to ALLOWED so it will be ignored.");
    }
}
$deploymentBuffer[] = "}"; // Closing Global namespace for now
cli_success_without_exit("G`### Step 2 DONE ###` Validating & Adding User-defined Functions (`/src/funkphp/config/functions.php`), Core Functions (`/src/funkphp/core/functions.php`) SUCCESSFULLY! Paths: `" . (FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED ?? "[NOT_DEFINED]") . "`" . " & `" . (FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL ?? "[NOT_DEFINED]") . "`");

// NEXT UP FOR BUILD/COMPILE: Scoped Namespaces for pipeline_request (pl_) files!!! Will learn then if stuff even works
$pipelineWarnsAndErrs = [];
cli_info_without_exit("G`### Step 3 STARTS ###` Loading, Validating & Compiling `pipeline_request.php`, `pipeline_routes.php` & `compiled_routes.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");
if (!cli_file_constant_defined_file_exists_is_readable("FUNKPHP_FILE_PATH_ROUTES")) {
    cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The FunkPHP Core Routes File `FUNKPHP_FILE_PATH_ROUTES` (`/src/funkphp/core/pipeline_routes.php`) does NOT EXIST or IS NOT READABLE or its DEFINED CONSTANT IS UNDEFINED! Try rebuilding the Route Files using `php funk rc` and try again! Path: `" . (FUNKPHP_FILE_PATH_ROUTES ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Routes, Compiled Routes & Pipeline) and try again! Path: `" . (FUNKPHP_FILE_PATH_ROUTES ?? "[NOT_DEFINED]") . "`");
}
if (!cli_file_constant_defined_file_exists_is_readable("FUNKPHP_FILE_PATH_TROUTES")) {
    cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The FunkPHP Core Compiled Routes File `FUNKPHP_FILE_PATH_TROUTES` (`/src/funkphp/core/compiled_routes.php`) does NOT EXIST or IS NOT READABLE or its DEFINED CONSTANT IS UNDEFINED! Try rebuilding the Route Files using `php funk rc` and try again! Path: `" . (FUNKPHP_FILE_PATH_TROUTES ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Routes, Compiled Routes & Pipeline) and try again! Path: `" . (FUNKPHP_FILE_PATH_TROUTES ?? "[NOT_DEFINED]") . "`");
}
cli_info_without_exit("Recompiling & Rebuilding Routes (`/src/funkphp/core/pipeline_routes.php`) & Prefixed Routes (`/src/funkphp/core/compiled_routes.php`) using `cli_sort_build_routes_compile_and_output()`. If this step FAILS, the Building will ALSO Stop!");
[$TRIE, $RUTTER] = cli_sort_build_routes_compile_and_output($singleRoutesRoute, true); // $singleRoutesRoute is declared already `funk` File and also has default values if not existing!

cli_info_without_exit("G`### Step 3 CONTINUES ###` Loading, Validating & Compiling `pipeline_request.php`, `pipeline_routes.php` & `compiled_routes.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");
if (!cli_file_constant_defined_file_exists_is_readable("FUNKPHP_FILE_PATH_PIPELINE")) {
    cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The FunkPHP Core Compiled Routes File `FUNKPHP_FILE_PATH_PIPELINE` (`/src/funkphp/core/pipeline_routes.php`) does NOT EXIST or IS NOT READABLE or its DEFINED CONSTANT IS UNDEFINED! Try rebuilding the Route Files using `php funk rc` and try again! Path: `" . (FUNKPHP_FILE_PATH_PIPELINE ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Routes, Compiled Routes & Pipeline) and try again! Path: `" . (FUNKPHP_FILE_PATH_PIPELINE ?? "[NOT_DEFINED]") . "`");
}
$pipelineFile = $singlePipeline;

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline"], $pipelineWarnsAndErrs, "cli_err");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>"], $pipelineWarnsAndErrs, "cli_err");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_headers"], $pipelineWarnsAndErrs, "cli_err");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_headers", "add"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-strings|array-empty', "All Values in `[<CONFIG_GLOBAL> -> global_headers -> add]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_headers", "remove"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-strings|array-empty', "All Values in `[<CONFIG_GLOBAL> -> global_headers -> remove]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_sris"], $pipelineWarnsAndErrs, "cli_err");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_sris", "internal"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-associative-strings', "All Values in `[<CONFIG_GLOBAL> -> global_sris -> internal]` must be Single Associative Arrays with Single String Values (empty allowed) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_sris", "external"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-associative-strings', "All Values in `[<CONFIG_GLOBAL> -> global_sris -> external]` must be Single Associative Arrays with Single String Values (empty allowed) OR it must be an Empty Array!");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", (function ($keyValuePairs) {
    if (isset($keyValuePairs) && is_array($keyValuePairs) && count($keyValuePairs) > 0) {
        foreach ($keyValuePairs as $KVKey => $KVVAL) {
            if (!str_starts_with(strtolower($KVKey), "https://")) return false;
        }
        return true;
    } else {
        return true;
    }
}), "Every External URL Key in in `[<CONFIG_GLOBAL> -> global_headers -> global_sris -> internal]` must start with `https://` for security reasons!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp"], $pipelineWarnsAndErrs, "cli_err");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "connect-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp-> connect-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "font-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> font-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "frame-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> frame-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "base-uri"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> base-uri]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "form-action"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> form-action]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "object-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> object-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "default-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> default-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "script-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> script-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "style-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> style-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_csp", "img-src"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_csp -> img-src]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_rate_limiting"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array|null', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_rate_limiting]` must be an Array or null!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_param_rules"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-associative-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_param_rules]` must be Strings (empty or not) OR it must be an Empty Array!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_default_no_route_match_response"], $pipelineWarnsAndErrs, "cli_err");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_default_no_route_match_response", "page"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'string|null', "`[pipeline -> <CONFIG_GLOBAL> -> global_default_no_route_match_response -> page]` must be a String or Null!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_default_no_route_match_response", "json"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array|null', "`[pipeline -> <CONFIG_GLOBAL> -> global_default_no_route_match_response -> json]` must be an Array or Null!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_default_no_route_match_response", "xml"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'string|null', "`[pipeline -> <CONFIG_GLOBAL> -> global_default_no_route_match_response -> xml]` must be a String or Null!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_default_no_route_match_response", "text"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'string|null', "`[pipeline -> <CONFIG_GLOBAL> -> global_default_no_route_match_response -> text]` must be a String or Null!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_default_no_route_match_response", "callback"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'string|null', "`[pipeline -> <CONFIG_GLOBAL> -> global_default_no_route_match_response -> callback]` must be a String or Null!");

$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "request"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-list-strings-non-empty', "`[pipeline -> request]` must be a Numbered Array with Single Non-Empty String Values!");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "post_response"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "`[pipeline -> post_response]` must be a Numbered Array with Single Non-Empty String Values OR an Empty Array!");

cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> <CONFIG_GLOBAL>` and try again! Path: `" . (FUNKPHP_FILE_PATH_PIPELINE ?? "[NOT_DEFINED]") . "`");

// Now we start building namespace-scoped functions such as: 'funkphp\pipeline\request {}' & 'namespace 'funkphp\pipeline\post_response {}'
if (!defined("FUNKPHP_PIPELINE_REQUEST_DIR")) {
    cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_PIPELINE_REQUEST_DIR` (`/src/funkphp/pipeline/request`) IS NOT DEFINED when it should be?! Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again! Path: `" . (FUNKPHP_PIPELINE_REQUEST_DIR ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Pipeline Request Files) and try again! Path: `" . (FUNKPHP_PIPELINE_REQUEST_DIR ?? "[NOT_DEFINED]") . "`");
}
if (!defined("FUNKPHP_PIPELINE_POST_RESPONSE_DIR")) {
    cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_PIPELINE_POST_RESPONSE_DIR` (`/src/funkphp/pipeline/post_response`) IS NOT DEFINED when it should be?! Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again! Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "`");
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files  (Pipeline Post_Response Files) and try again! Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "`");
}

$DEFAULT_ERROR_FORMATTING = "\nRECOMMENDED: `1) ALWAYS lowercase Function Names everywhere. 2) ALWAYS USE Standard Formatting so every newline inside of Functions are indented at least once` OR it won't be found by the Compiler!";
// Add Pipeline Request Functions
$deploymentPipelineRequestBuffer[] = 'namespace funkphp\\pipeline\\request';
$deploymentPipelineRequestBuffer[] = " {\n";
foreach ($pipelineFile['pipeline']['request'] as $pipeRequestFn) {
    $plReqStatus = cli_folder_and_php_file_status(FUNKPHP_PIPELINE_REQUEST_DIR, $pipeRequestFn, true);
    if (!$plReqStatus['file_exists'] || !$plReqStatus['folder_readable']) { // file exists & is readable?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The Pipeline Request Function File (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) was NOT FOUND or IS NOT READABLE! Dir Path: `" . (FUNKPHP_PIPELINE_REQUEST_DIR ?? "[NOT_DEFINED]") . "`");
        cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> request` and try again! Path: `" . (FUNKPHP_FILE_PATH_PIPELINE ?? "[NOT_DEFINED]") . "` $DEFAULT_ERROR_FORMATTING");
    }
    if ($plReqStatus['namespace_name'] !== "funkphp\\pipeline\\request\\$pipeRequestFn") { // expected scoped namespace correct?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) was NOT FOUND in Expected `namespace funkphp\\pipeline\\request\\$pipeRequestFn;`! Dir Path: `" . (FUNKPHP_PIPELINE_REQUEST_DIR ?? "[NOT_DEFINED]") . "`");
    }
    if (!isset($plReqStatus['functions'][$pipeRequestFn])) { // does function (name) exist?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) was NOT FOUND in Expected Function `function $pipeRequestFn(&\$c) { // Code }`!");
    } else if (isset($plReqStatus['functions'][$pipeRequestFn])) {
        if (!$plReqStatus['functions_same_count']) {
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request Response Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) WAS FOUND USING Either Regex or Tokenizer but not both Indicating some Formatting Issue Inside File? (check for any trailing comment at the end of a function block{} <-- here)");
        }
        if (!$plReqStatus['functions'][$pipeRequestFn]['fn_name_same_as_lowercased']) { // is function name lowercased?
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) should only and always be lowercased!");
        }
        if (!str_starts_with(strtolower($pipeRequestFn), "pl_")) { // function name starts with "pl_"?
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) must start with `pl_` for the sake of consistency!");
        }
    }
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> request` and try again! Path: `" . (FUNKPHP_FILE_PATH_PIPELINE ?? "[NOT_DEFINED]") . "` $DEFAULT_ERROR_FORMATTING");

    // Notice & ignore for now that default `pl_https_kernel_dispatch` was included in Pipeline Request Array!
    if ($pipeRequestFn === "pl_https_kernel_dispatch") {
        $HTTPS_KERNEL_DISPATCH_FUNCTION_FOUND = true;
        continue;
    }
    $deploymentPipelineRequestBuffer[] = $plReqStatus['functions'][$pipeRequestFn]['fn_raw'] . "\n";
}
$deploymentPipelineRequestBuffer[] = " }\n"; // End namespace funkphp\pipeline\request {}

// Add Pipeline Post_Response Functions
$deploymentPipelineRequestBuffer[] = 'namespace funkphp\\pipeline\\post_response';
$deploymentPipelineRequestBuffer[] = " {\n";
foreach ($pipelineFile['pipeline']['post_response'] as $pipePostResponseFn) {
    $plReqStatus = cli_folder_and_php_file_status(FUNKPHP_PIPELINE_POST_RESPONSE_DIR, $pipePostResponseFn, true);
    if (!$plReqStatus['file_exists'] || !$plReqStatus['folder_readable']) { // file exists & is readable?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The Pipeline Post_Response Function File (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) was NOT FOUND or IS NOT READABLE! Dir Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "`");
        cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> post_response` and try again! Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "` $DEFAULT_ERROR_FORMATTING");
    }
    if ($plReqStatus['namespace_name'] !== "funkphp\\pipeline\\post_response\\$pipePostResponseFn") { // expected scoped namespace correct?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) was NOT FOUND in Expected `namespace funkphp\\pipeline\\post_response\\$pipePostResponseFn;`! Dir Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "`");
    }
    if (!isset($plReqStatus['functions'][$pipePostResponseFn])) { // does function (name) exist?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) was NOT FOUND in Expected Function `function $pipePostResponseFn(&\$c) { // Code }`!");
    } else if (isset($plReqStatus['functions'][$pipePostResponseFn])) {
        if (!$plReqStatus['functions_same_count']) {
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) WAS FOUND USING Either Regex or Tokenizer but not both Indicating some Formatting Issue Inside File? (check for any trailing comment at the end of a function block{} <-- here)");
        }
        if (!$plReqStatus['functions'][$pipePostResponseFn]['fn_name_same_as_lowercased']) { // is function name lowercased?
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) should only and always be lowercased!");
        }
        if (!str_starts_with(strtolower($pipePostResponseFn), "pl_")) { // function name starts with "pl_"?
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) must start with `pl_` for the sake of consistency!");
        }
    }
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> post_response` and try again! Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "` $DEFAULT_ERROR_FORMATTING");
    $deploymentPipelineRequestBuffer[] = $plReqStatus['functions'][$pipePostResponseFn]['fn_raw'] . "\n";
}
$deploymentPipelineRequestBuffer[] = " }\n"; // End namespace funkphp\pipeline\post_response {}

// STRONG CRITICAL WARNING if they skip the `pl_https_kernel_dispatch` which is the "trigger"
// to build the optimized route matching execution flow. Then it is all up to Dev to write their own!
if (!$HTTPS_KERNEL_DISPATCH_FUNCTION_FOUND) {
    cli_warning_without_exit("### ⚠️CRITICAL WARNING ### Expected `pl_https_kernel_dispatch` Pipeline Request Function NOT FOUND meaning the Optimized Routing Matching Function will NOT BE PART OF THE BUILD! (that String is needed to 'trigger' that Building step!");
    if (!isset($cConfig['FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'])) {
        cli_warning_without_exit("⚠️A `Custom HTTPS Kernel Dispatch Pipeline Request Function` (`FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION` Key in `/src/funkphp/config/c.php`) WAS NOT FOUND meaning you must either write your own `pl_function` and add to the Pipeline Request Array that does all of it OR write a User-defined Function (in `/src/funkphp/config/functions.php`) that would be called if you write its Function Name in the `FUNKPHP_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION` Key!");
    }
    cli_warning_without_exit("⚠️If you have no other Pipeline Request Function that can match the Routes inside of `/src/funkphp/core/compiled_routes.php` and execute matched Middlewares & Route Pipeline Functions in `/src/funkphp/core/pipeline_routes.php` then you might end up with a 'Dead-On-Compilation Build'!");
}
cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> request` and try again! Path: `" . (FUNKPHP_PIPELINE_POST_RESPONSE_DIR ?? "[NOT_DEFINED]") . "` $DEFAULT_ERROR_FORMATTING");

// Add the valid Pipeline Request & Post_Response Functions to final buffer
// and remove them as they are no longer needed. If Dev wanna use them
// they will have to call them by:`\funkphp\pipeline\request|post_response\pl_name($c);`
$deploymentBuffer[] = implode("", $deploymentPipelineRequestBuffer);
$cConfig['pipeline'] = $pipelineFile['pipeline'];
unset($cConfig['pipeline']['request']);
unset($cConfig['pipeline']['post_response']);

cli_info_without_exit("G`### Step 4 STARTS ###` Loading, Validating, Rebuilding & Compiling `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' in 'Pipeline' in FunkGUI)...");

// $TRIE === Compiled Prefix Router, it has faster info instead of calculating it manually
// like how many (most+least) URI segments each HTTP(S) method has (used later for optimize route matching)
// $RUTTER === Developer's Routes; they were recompiled before we reached this point so they could
// not be changed maliciously. They (METHODS/ROUTES) should be guaranteed by pre-recompilation to be unique
// in each method with no conflicting same-level dynamic URI segments (e.g. GET/:test and GET/:test2)
$routesWarnsAndErrs = []; // Warns&Errs BOTH for $RUTTER and/or $TRIE
$deploymentPipelineRoutesBuffer = []; // $RUTTER (Routes namespace {})
$deploymentPipelineRoutesBuffer[] = "namespace funk\\pipeline\\routes {\n";
$deploymentMiddlewaresBuffer = []; // Middlewares namespace {}
$deploymentMiddlewaresBuffer[] = "namespace funkphp\\pipeline\\middlewares {\n";
$deploymentMegaRouteMatchBuffer = []; // Optimized Match Routing+Direct Function Calls to $RUTTER=>route=>middlewares+pipeline
$NO_ROUTES = false;

if (!defined("FUNKPHP_ROUTES_DIR")) {
    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_ROUTES_DIR` (`/src/funkphp/pipeline/routes`) IS NOT DEFINED when it should be?! Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again! Path: `" . (FUNKPHP_ROUTES_DIR ?? "[NOT_DEFINED]") . "`");
}
if (!defined("FUNKPHP_MIDDLEWARES_DIR")) {
    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_MIDDLEWARES_DIR` (`/src/funkphp/pipeline/middlewares`) IS NOT DEFINED when it should be?! Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again! Path: `" . (FUNKPHP_MIDDLEWARES_DIR ?? "[NOT_DEFINED]") . "`");
}
cli_stop_from_warn_err_list($routesWarnsAndErrs, "Please Review (" . count($routesWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Pipeline Routes & Middlewares Files) and try again! Paths: `" . (FUNKPHP_ROUTES_DIR ?? "[NOT_DEFINED]") . "` & `" . (FUNKPHP_MIDDLEWARES_DIR ?? "[NOT_DEFINED]") . "`");


//$test = cli_folder_and_php_file_status(FUNKPHP_ROUTES_DIR, "test", true);




// SPECIAL EDGE CASE BELOW: When there are no routes compiled (like just trying out command & opening FunkPHPDeployment.php)
if ($TRIE['METADATA']['<ALL>']['totalAllRoutes'] === 0) {
    $NO_ROUTES = true;
    cli_warning_without_exit("
==========================================================================================================================
CRITICAL NOTICE: ZERO ROUTES COMPILED (New Project? File Permission?)
==========================================================================================================================
The Compiler successfully generated 'compiled_routes.php',
but found exactly (0) defined routes.
What this means for your application:

1) Every single incoming request will instantly trigger your
`default 404/Error Pipeline` or `other default behavior`.

2) If this is a `brand new project, this is normal!` Create
Routes in FunkGUI or via FunkCLI `php funk make:r` and then
add `middlewares` to them via `php funk make:mw` and then
function handlers to them via `php funk make:h`!

3) If this is an Existing App, `Check Your File Paths` or
ensure your Route Arrays are not accidentally empty.

Paths to consider checking:
- `/src/funkphp/core/c.php` (The Configuration Array known as '\&\$c')
- `/src/funkphp/config/functions.php` (Your Custom-defined Globally Available Functions)
- `/src/funkphp/core/pipeline_request.php` (Pipeline Request Array - runs for each HTTP(S) Request)
- `/src/funkphp/core/pipeline_routes.php` (Routes Array - your Routes, Middlewares & Route Pipeline Functions end up here)
==========================================================================================================================");
}
// When there ARE ROUTES TO Validate, Parse & Output!
if (!$NO_ROUTES) { //START-BLOCK:ROUTES TO Validate, Parse & Output!
    foreach ($RUTTER as $METOD => $RUTT) { // more Easter Eggs for those who know!
        // Check if current $METOD has zero routes (because others might have though)
    }
} //END-BLOCK:ROUTES TO Validate, Parse & Output!


// Closing namespaces { '}' <- This one for namespace funkphp\pipeline\middlewares|routes!
$deploymentPipelineRoutesBuffer[] = " }\n"; // } Closing of Routes namespace {}
$deploymentMiddlewaresBuffer[] = " }\n"; // } Closing of Middlewares namespace {}

//////////////////////////////////
// Correct execution order for GOTO Labels: Route Matching Generator Functions???
//$sortedResult = cli_prepare_binary_specificity_score_VF("mockDeveloperRoutes", "GET");
// $ASTresult    = cli_build_flattened_routing_start_VF("sortedResult", "GET");
// Run through the wrapper
//$compiledPHPCode = cli_compile_router_file_VF($ASTresult, "GET");
//echo $compiledPHPCode;



cli_info_without_exit("G`### Step 5 STARTS ###` Loading, Validating, & Compiling Route Pipeline Functions (files in `src/funkphp/pipeline/routes`) & Middlewares Functions (files in `src/funkphp/pipeline/middlewares`) Used For Each Valid Route Compiled From `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' & 'Middlewares' in 'Pipeline' in FunkGUI)...");
$routesPipelineWarnsAndErrs = [];

cli_info_without_exit("G`### Step 6 STARTS ###` Loading, Validating, & Compiling Any Pages (files in `src/funkphp/pages`) used ('Pages' with 'Layouts', 'Components' & 'Compiled' in FunkGUI)...");
$routesPipelineWarnsAndErrs = [];

// Map the relative page path to its expected factory-default hash
$factorySignatures = $manifest['hashes']['pages']['compiled_errors'];
cli_info_without_exit("Auditing Default Error Page Signatures for potential local debug leakages...");
foreach ($factorySignatures as $pagePath => $factoryHash) {
    // Build the exact file path dynamically
    $fullPath = PROJECT_DIR . '/' . $pagePath;
    // Skip if the developer completely deleted the file to use alternative setups
    if (!file_exists($fullPath)) {
        continue;
    }
    // Calculate current state using your helper function
    $currentHash = cli_get_hash_calculation_of_a_file($fullPath);
    if ($currentHash === $factoryHash[0]) {
        cli_warning_without_exit(
            "The file `$pagePath` matches the Factory Default. It contains loopback IP checks and local variable dumping. Consider creating a Custom Version for Production Security that fits your needs!"
        );
    }
}


cli_info_without_exit("G`### Step 7 STARTS ###` Running any optional flags before finishing...");
$optionalFlagsWarnsAndErrs = [];

// This should happen if everything above went smoothly!
$FINAL_C_CONFIG = cli_replace_string_tokens_in_var_exported_string($cReqReplacements, var_export($cConfig, "true")) . "\n";
$COMPLETE_DEPLOYMENT_BUFFER = implode($deploymentBuffer);
$COMPLETE_DEPLOYMENT_BUFFER =  cli_php_strip_whitespace_and_optimize(str_replace($compilationSecretToken, $FINAL_C_CONFIG, $COMPLETE_DEPLOYMENT_BUFFER));
$COMPLETE_DEPLOYMENT_BUFFER = substr_replace($COMPLETE_DEPLOYMENT_BUFFER, $BUILD_VERSION, 0, 6);

// IMPORTANT: Might go back to "cli_php_strip_whitespace_string" instead if "\" auto-adding causes more issues rather than help?
if (!cli_crud_folder_php_file_atomic_write($COMPLETE_DEPLOYMENT_BUFFER, FUNKPHP_FILE_PATH_DEPLOYMENT_FILE)) {
    cli_err("
==========================================================================================================================
FAILED to Write the otherwise Successfully Compiled `/src/funkphp/FunkPHPDeployment.php` File to the Disk!?
Please check the File Permissions and try again! Path: `" . (FUNKPHP_FILE_PATH_DEPLOYMENT_FILE ?? "[NOT_DEFINED]") . "`
==========================================================================================================================");
}
// Notify about super-important message if no routes were compiled but without any errors and/or warnings
if ($NO_ROUTES) {
    cli_warning_without_exit("
==========================================================================================================================
    SUPER-IMPORTANT: SCROLL UP TO READ SUPER-IMPORTANT MESSAGE REGARDING THE COMPILATION! ^_^ (look for `CRITICAL NOTICE`)
==========================================================================================================================");
}
cli_success_without_exit("### FunkCLI Successfully Compiled & Built `/src/funkphp/FunkPHPDeployment.php` ###");
cli_success_without_exit("Compiled with the following options:\n- Compile Pages: " . ($compilePages ? "YES" : "NO") . "\n- Compress Deployment: " . ($compressDeployment ? "YES" : "NO"));
cli_success("### You can now deploy the `FunkPHPDeployment.php` File to Your Server for Production use!");
exit;
