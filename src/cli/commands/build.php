<?php // FunkCLI Command `php funk build|b`
// BUILD: Generates the `FunkPHPDeployment.php" mega file that is
// supposed to include: core, routes, & middlewares for deployment
// to a server. It can also optionally compile pages as well and/or
// compress all files into a single zip file for easier deployment!

/**
 * ---------------------
 * FUNKCLI Build Command
 * ---------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU UNDERSTAND IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/

$embedPages = false; // imlpemented later
$compilePages = false;
$compressDeployment = false;
$skipBrokenRoutes = false; // implemented later
$skipCompilingValidation = false; // implemented later
$skipCompilingSQL = false; // implemented later

// Initialize an array to hold the different compiled sections of the file
$deploymentBuffer = [];
$deploymentPath = FUNKPHP_FILE_PATH_DEPLOYMENT_FILE;

// Inside of $args[] array on 1 or 2 we can have the optional
// configs for "--compile-pages" and/or "--compress-deployment"
// or the single one "--both" for faster typing.
// The $args[1] is always the command so we do not look for it!
// So we iterate through $args on 1 & 2 to find those optional flags
foreach ($args as $arg) {
    if (is_string($arg)) {
        $flag = strtolower(trim($arg));
        if ($flag === "--both") {
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
        }
    }
}
cli_info_without_exit("### FunkCLI Compiling & Building `FunkPHPDeployment.php` with the following options:");
cli_info_without_exit("#### Skip Broken Routes: " . ($skipBrokenRoutes ? "YES" : "NO"));
cli_info_without_exit("#### Skip Compiling Validation: " . ($skipCompilingValidation ? "YES" : "NO"));
cli_info_without_exit("#### Skip Compiling SQL: " . ($skipCompilingValidation ? "YES" : "NO"));
cli_info_without_exit("#### Do Compile Pages: " . ($compilePages ? "YES (pages will be compiled and output to 'funkphp/pages'" : "NO"));
cli_info_without_exit("#### Do Embed Pages: " . ($embedPages ? "YES (pages will be inside of the FunkPHPDeployment.php File)" : "NO"));
cli_info_without_exit("#### Do Compress Deployment: " . ($compressDeployment ? "YES (FunkPHPDeployment.php, pages and public_html folder will be in a single compresed file)" : "NO"));

// The actual compiling & building steps
cli_info_without_exit("### Step 1: Loading, Validating & Compiling `config.php` File ('Config' in FunkGUI)...");
$configWarnsAndErrs = [];
$cConfig = null;

// We look for defined constant "FUNKPHP_FILE_PATH_C_CONFIG_FILE" which should point to the c.php file
// and we try to see if we can find it, read it, and whether it is an associative array which it should be!
if (!defined("FUNKPHP_FILE_PATH_DEPLOYMENT_FILE")) {
    cli_err("The Constant `FUNKPHP_FILE_PATH_DEPLOYMENT_FILE` containing exact Path to `FunkPHPDeployment.php` (FunkPHP Deployment Output File after Successful Building/Compiling) is NOT DEFINED! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
}
if (!defined("FUNKPHP_FILE_PATH_C_CONFIG_FILE") || !file_exists(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    cli_err("The Constant `FUNKPHP_FILE_PATH_C_CONFIG_FILE` containing exact Path to `c.php` (FunkPHP Configuration File) is NOT DEFINED or Exact File Path does NOT EXIST/IS WRONG! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
}
// File exists but is not readable?
if (!is_readable(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    cli_err("The `c.php` (FunkPHP Configuration File) exists but is NOT READABLE! Please check the File Permissions and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
}
// File exists, is readable, but is NOT an associative array? We need to check if it returns an array when included!
if (is_readable(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    $cConfig = require_once FUNKPHP_FILE_PATH_C_CONFIG_FILE;
    if (!is_array($cConfig) || array_is_list($cConfig)) {
        cli_err("The `c.php` (FunkPHP Configuration File) exists and is Readable but does NOT return an Associative Array! Please check the File Contents and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
    }
}

// The main keys that must exist on $c array level for FunkPHP! So we iterate
// through using array_key_exists now in $cCOnfig to see if those actually exist!
// those that do not exist we add to the "$configWarnsAndErrs" as errors!
$cArrayKeysThatMustExist = [
    'FUNKPHP_ONLINE',
    'INI_SETS',
    'BASEURLS',
    'SESSION',
    'shared',
    'custom',
    'classes',
    'connections',
    'req',
    'd',
    'v',
    'v_data',
    'p',
    'files',
    'err',
];
foreach ($cArrayKeysThatMustExist as $key) {
    if (!array_key_exists($key, $cConfig)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Key '$key' NOT FOUND in `c.php` (FunkPHP Configuration File)! Please check the File Contents and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
    }
}
foreach ($cConfig as $cKey => $val) {
    if (
        !in_array($cKey, $cArrayKeysThatMustExist)
        && ($cKey !== "<ENTRY>")  && ($cKey !== "ROUTES") // Only applicable to the local web dev environment!
        && ($cKey !== 0) && ($cKey !== 1) && ($cKey !== 2) // 0-2 are the !defined parts in the $c returned array!
    ) {
        cli_warning_without_exit("IGNORED: Key '$cKey' in `c.php` (FunkPHP Configuration File) will be ignored. Any custom variables should be in `\$c['custom']! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for 'BASEURL' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for 'SESSION' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

// VALIDATE CLASSES Array Subkeys Paths!
$classesChecks = [];
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes"], $configWarnsAndErrs, "cli_err");
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes", "vendor"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($classesChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with vendor-based class instances (from src/funkphp/vendor) during runtime.");
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes", "user"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($classesChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array  as it will be filled with user-defined class instances (from src/funkphp/classes) during runtime.");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for 'classes' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

// VALIDATE CONNECTIONS Array Subkeys Paths!
$connectionsChecks = [];
$connectionsChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["connections"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($connectionsChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with Connections like Databases (from src/funkphp/config/conns.php) and other connection-related services during runtime.");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for 'connections' Main Key in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

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

cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for 'req' Main Key and its Subkeys in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
exit;

cli_info_without_exit("### Step 2: Loading, Validating & Compiling `pipeline_request.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");
$pipelineWarnsAndErrs = [];

cli_info_without_exit("### Step 3: Loading, Validating & Compiling Core `functions.php` & User-defined `funkphp => config => functions.php` Files ('User-defined Functions' in 'Config' in FunkGUI)...");
$functionsWarnsAndErrs = [];

cli_info_without_exit("### Step 4: Loading, Validating, Rebuilding & Compiling `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' in 'Pipeline' in FunkGUI)...");
$routesWarnsAndErrs = [];

cli_info_without_exit("### Step 5: Loading, Validating, & Compiling Pipeline Functions (files in `src/funkphp/pipeline/routes/`) & Middlewares Functions (files in `src/funkphp/pipeline/middlewares) Used For Each Valid Route Compiled From `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' & 'Middlewares' in 'Pipeline' in FunkGUI)...");
$routesPipelineWarnsAndErrs = [];

cli_info_without_exit("### Step 6: Running any optional flags before finishing...");
$optionalFlagsWarnsAndErrs = [];

// This should happen if everything above went smoothly!
if (!cli_crud_folder_php_file_atomic_write(implode($deploymentBuffer), FUNKPHP_FILE_PATH_DEPLOYMENT_FILE)) {
    cli_err("Failed to write the otherwise Successfully Compiled `FunkPHPDeployment.php` File to the Disk! Please check the File Permissions and try again! Path: " . (FUNKPHP_FILE_PATH_DEPLOYMENT_FILE ?? "[NOT_DEFINED]"));
}
cli_success("### FunkCLI Successfully Compiled & Built `FunkPHPDeployment.php` with the following options:\n### Compile Pages: " . ($compilePages ? "YES" : "NO") . "\n### Compress Deployment: " . ($compressDeployment ? "YES" : "NO") . "\n### You can now deploy the `FunkPHPDeployment.php` file to your server for production use!");
exit;
