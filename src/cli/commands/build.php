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
$showAllErrors = false; // implemented later
$skipBrokenRoutes = false; // implemented later
$skipCompilingValidation = false; // implemented later
$skipCompilingSQL = false; // implemented later

// Initialize an array to hold the different compiled sections of the file
// and its sub parts so we can add sub parts as needed to the entire file!
$deploymentBuffer = [];
$deploymentConfigBuffer = [];
$deploymentFunctionsBuffer = [];
$deploymentPipelineRequestBuffer = [];
$deploymentPipelinePostResponseBuffer = [];
$deploymentPipelineRoutesBuffer = [];
$deploymentValidationBuffer = [];
$deploymentSQLBuffer = [];
$deploymentExtraFlagsBuffer = [];
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
        } else if ($flag === "--show-error-reporting-all-errors") {
            $showAllErrors = true;
        }
    }
}
cli_info_without_exit("### FunkCLI Compiling & Building `FunkPHPDeployment.php` with the following options:");
cli_info_without_exit("#### Skip Broken Routes: " . ($skipBrokenRoutes ? "YES (invalid routes will NOT be pruned in output)" : "NO"));
cli_info_without_exit("#### Skip Compiling Validation: " . ($skipCompilingValidation ? "YES (Validation Functions will NOT be compiled before output)" : "NO"));
cli_info_without_exit("#### Skip Compiling SQL: " . ($skipCompilingValidation ? "YES (SQL Functions will NOT be compiled before output)" : "NO"));
cli_info_without_exit("#### Do Include `error_reporting = E_ALL` in Deployment File: " . ($showAllErrors ? "YES (warning: sensitive info could be leaked in production!" : "NO"));
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
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Key '$key' NOT FOUND in `c.php` (FunkPHP Configuration File)! Please check the File Contents and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));
    }
}
foreach ($cConfig as $cKey => $val) {
    if (
        !in_array($cKey, $cArrayKeysThatMustExist)
        && ($cKey !== "<ENTRY>")  && ($cKey !== "ROUTES") // Only applicable to the local web dev environment!
        && ($cKey !== 0) && ($cKey !== 1) && ($cKey !== 2) && ($cKey !== 3) // 0-3 are the !defined parts in the $c returned array!
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
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "query"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'string|null', "This is the Server-provided Request Query String of the HTTP(S) Request that will be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "auth"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the FunkPHP-provided Auth Value depending on implemention that can be used after a Matched Route during a Valid HTTP(S) Request!");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "matched_in"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the location identifier where the router found a match (e.g., 'web', 'api') and must stay initialized as NULL.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "route"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This holds the raw matched dynamic route string path template from your route definitions file.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "params"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This will hold extracted dynamic URI route parameters mapped as an associative array upon routing matching completion.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "segments"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This will store exploded URL string path fragments used internally by FunkPHP for structural pattern matching analysis.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "matched_config"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This carries custom meta-configuration state properties linked directly to the specific matched endpoint.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "matched_pipeline"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must start as a pristine Empty Array. It acts as the compiled queue container holding pipeline processing layers for this request loop.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "matched_middlewares"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This holds a sequential list of verified HTTP middleware layer keys that will execute sequentially before the controller functions trigger.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "skip_post_response"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'boolean', "This is a boolean toggle flag used to control whether background post-response fast-cgi processes should run or terminate early.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "current_pipeline"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the internal runtime pointer monitoring the actively running functional request pipeline segment.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "next_pipeline"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the internal runtime pointer indicating the upcoming functional step scheduled next within the pipeline flow queue.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "current_middleware"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the internal runtime pointer identifying which middleware interceptor layer is actively evaluating the state snapshot.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "next_middleware"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is the internal runtime pointer declaring the target middleware layer awaiting execution authority next.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "keep_running_pipeline"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is a execution state sentinel parameter used by the pipeline loop to handle async execution continuations.");
$reqChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["req", "keep_running_middlewares"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($reqChecks), $configWarnsAndErrs, "cli_err", 'null', "This is a structural control variable used to maintain context within nested onion-style middleware loops.");
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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for 'req' Main Key and its Subkeys in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

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
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `v_config` is meant to Store Current Global Validation Configuration provided by a given funk_use_validation() call. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["v_data"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `v_data` is meant to Store Validated Data a given funk_use_validation() call. Can be used any time during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["p"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `p` is meant to Store Page-related that can be used after a Matched Route during a Valid HTTP(S) Request!");
$dvpfChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["files"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($dvpfChecks), $configWarnsAndErrs, "cli_err", 'null', "Main Key `files` is meant to Store Uploaded Files (if applicable). Can be used any time during a Valid HTTP(S) Request!");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for the Main Keys 'd','v','v_ok','v_ok_files','v_config','v_data','s_data','p','p_config' & 'files' in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for the Main Key 'err' and its Subkeys in the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

// VALIDATE Certain Constants by is using !defined() and error out if not found!

cli_assert_final_value(NULL, $configWarnsAndErrs, "cli_err", 'constants:FUNKPHP_DEPLOYED,FUNKPHP_ONLINE,FUNKPHP_NO_VALUE,FUNKPHP_ALLOW_INSTANCE_OVERWRITE', "These Constants are a MUST for FunkPHPDeployment.php to 'function' properly!");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings and/or Errors above for the Required Constants from the `c.php` (FunkPHP Configuration File) and try again! Path: " . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]"));

// Here ### Step 1 is fully validated so now we insert starting point of the `FunkPHPDeployment.php` file into the $deploymentBuffer array for later writing to disk!
cli_success_without_exit("### Step 1: Validated `c.php` (FunkPHP Configuration File) Successfully! All Required Keys & Subkeys Exist and are Valid! Path: `" . (FUNKPHP_FILE_PATH_C_CONFIG_FILE ?? "[NOT_DEFINED]") . "`.");
$deploymentBuffer[] = "<?php // FunkPHPDeployment.php | Created: " . date("Y-m-d H:i:s") . " | PHP Version: " .  PHP_VERSION . " | FunkPHP Version: " . (FUNKPHP_VERSION ?? "<Unknown Version>") . " | FunkCLI Version: " . (FUNKCLI_VERSION ?? "<Unknown Version>") . "\n";

// Adding Starting Needed Constants First


exit;
$deploymentBuffer[] = "define('FUNKPHP_DEPLOYED', true);\n";
$deploymentBuffer[] = "define('FUNKPHP_ONLINE',\'" . FUNKPHP_ONLINE .  "'\n";
$deploymentBuffer[] = "define('FUNKPHP_NO_VALUE', new stdClass());\n";
$deploymentBuffer[] = "define('FUNKPHP_ALLOW_INSTANCE_OVERWRITE', true);\n";


cli_info_without_exit("### Step 2: Loading, Validating & Compiling Core `functions.php` & User-defined `funkphp => config => functions.php` Files ('User-defined Functions' in 'Config' in FunkGUI)...");
$functionsWarnsAndErrs = [];


cli_info_without_exit("### Step 3: Loading, Validating & Compiling `pipeline_request.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");
$pipelineWarnsAndErrs = [];

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
