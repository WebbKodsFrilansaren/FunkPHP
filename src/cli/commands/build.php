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
$manifestFlag = null; // contains all hashes needed
$embedPagesFlag = false; // imlpemented later
$compilePagesFlag = false;
$compressDeploymentFlag = false;
$showAllErrorsFlag = false; // implemented later
$allowModifiedCoreFlag = true; // implemented later
$ignoreUnknownConnsDriversFlag = false;
$skipCompilingValidationFlag = false; // implemented later
$skipCompilingSQLFlag = false; // implemented later

// This one keeps track of count, as well as names of added functions
// FIX: Add the name of functions to correct one here throughout building process,
// it ONLY happens when it has been 100 % validated to become a part of build output!
$COMPILE_STATS_TRACKER = [
    'GLOBAL' => [],
    'CLASSES' => [],
    'Ignored-FUNCTIONS' => [],
    'Core-FUNCTIONS' => [],
    'User-FUNCTIONS' => [],
    'Pages-REFERENCED' => [],
    'Pages-COMPILED' => [],
    'Pages-EMBEDDED' => [],
    'Pipeline-REQUEST' => [],
    'Pipeline-POST_RESPONSE' => [],
    'Pipeline-ROUTES' => [],
    'Pipeline-MIDDLEWARES' => [],
    'Data-SQL' => [],
    'Data-VALIDATION' => [],
    'Data-QUERY' => [],
];

$ROUTES_CONFIG_PARSED = [
    'USER_FNS_USED_FOR' => [],
    'ALL' => [
        'ALL_SNIPPETS_USED' => [ // Probably not needed as snippets are inserted as pure code during creation of a FN, or File->FN
            'VALID' => [],
            'INVALID' => [],
        ],
        'ALL_CLASSES_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_USER_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_CORE_FNS_USED' => [
            'VALID' => [],
            'TEMPLATE_REPLACED_VALID' => [],
            'TEMPLATE_REPLACED_INVALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_IGNORED_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_BLACKLISTED_FNS_USED' => [  // Use this to ALWAYS DISALLOW certain FNs? (but FNs from where though?)
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_WHITELISTED_FNS_USED' => [ // Use this to ONLY ALLOW certain FNs? (but FNs from where though?)
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_REQUEST_PIPELINE_FILES_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_ROUTES_ALIAS_USED' => [ // Enforce a unique ALIAS for every route, even across methods!
            'VALID' => [],
            'INVALID' => [],
        ],
        'ALL_ROUTES_PIPELINE_FILES_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_MIDDLEWARES_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_EXLUDE_MIDDLEWARES_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_VALIDATION_FILES_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_SQL_FILES_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_QUERY_FILES_FNS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_PAGES_USED' => [ // Not use this one due to not specific enough?
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_PAGES_REFERENCED_USED' => [ // This are when you use 'page' => 'FileName' for a Route Pipeline Key!
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_PAGES_COMPILED_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        // Maybe skip sub [errors]-folder and just include default ones and
        // also embed one inside of this command as default 500 and also scaffolding
        // with command like `php funk make:page err:404|400|500` and so on instead?
        'ALL_PAGES_COMPILED_[ERRORS]_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_PAGES_COMPONENTS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_PAGES_LAYOUTS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
        'ALL_PAGES_PARTIALS_USED' => [
            'VALID' => [],
            'INVALID' => [],
            'INVALID_PATHS' => [],
        ],
    ],
    'GLOBAL' => [
        'HEADERS_USED' => [ // from <CONFIG_GLOBAL>
            'VALID' => [
                'ADD' => [],
                'REMOVE' => []
            ],
            'INVALID' => [
                'ADD' => [],
                'REMOVE' => []
            ]
        ],
        'SRIS_USED' => [ // from <CONFIG_GLOBAL> IS ONLY ON THIS LEVEL!
            'VALID' => [],
            'INVALID' => [],
        ],
        'CSP_USED' => [ // from <CONFIG_GLOBAL>
            'VALID' => [],
            'INVALID' => []
        ],
        'RATE_LIMITING_USED' => [ // from <CONFIG_GLOBAL>
            'VALID' => [],
            'INVALID' => [],
        ],
        'PARAMS_USED' => [ // from <CONFIG_GLOBAL>
            'VALID' => [],
            'INVALID' => [],
        ],
        'DEFAULT_NO_ROUTE_MATCH_USED' => [ // from <CONFIG_GLOBAL>
            'VALID' => [],
            'INVALID' => []
        ],
    ],
    'METHODS' => [
        'GET' => [
            'MIDDLEWARES_USED' => [ // ONLY used middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'EXLUDE_MIDDLEWARES_USED' => [ // ONLY excluded middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'ROUTES_USED' => [ // But what do I mean with "ROUTES" here? Only keys?
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'HEADERS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ],
                'INVALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ]
            ],
            'CSP_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
            'RATE_LIMITING_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'DEFAULT_NO_ROUTE_MATCH_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
        ],
        'POST' => [
            'MIDDLEWARES_USED' => [ // ONLY used middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'EXLUDE_MIDDLEWARES_USED' => [ // ONLY excluded middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'ROUTES_USED' => [ // But what do I mean with "ROUTES" here? Only keys?
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'HEADERS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ],
                'INVALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ]
            ],
            'CSP_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
            'RATE_LIMITING_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'DEFAULT_NO_ROUTE_MATCH_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
        ],
        'PUT' => [
            'MIDDLEWARES_USED' => [ // ONLY used middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'EXLUDE_MIDDLEWARES_USED' => [ // ONLY excluded middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'ROUTES_USED' => [ // But what do I mean with "ROUTES" here? Only keys?
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'HEADERS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ],
                'INVALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ]
            ],
            'CSP_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
            'RATE_LIMITING_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'DEFAULT_NO_ROUTE_MATCH_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
        ],
        'PATCH' => [
            'MIDDLEWARES_USED' => [ // ONLY used middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'EXLUDE_MIDDLEWARES_USED' => [ // ONLY excluded middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'ROUTES_USED' => [ // But what do I mean with "ROUTES" here? Only keys?
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'HEADERS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ],
                'INVALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ]
            ],
            'CSP_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
            'RATE_LIMITING_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'DEFAULT_NO_ROUTE_MATCH_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
        ],
        'DELETE' => [
            'MIDDLEWARES_USED' => [ // ONLY used middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'EXLUDE_MIDDLEWARES_USED' => [ // ONLY excluded middlewares for this method then?
                'VALID' => [],
                'INVALID' => [],
            ],
            'ROUTES_USED' => [ // But what do I mean with "ROUTES" here? Only keys?
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'HEADERS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ],
                'INVALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ]
            ],
            'CSP_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
            'RATE_LIMITING_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'PARAMS_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => [],
            ],
            'DEFAULT_NO_ROUTE_MATCH_USED' => [ // from <CONFIG_METHOD>
                'VALID' => [],
                'INVALID' => []
            ],
        ],
    ],
    'ROUTES' => [
        ["<PLACEHOLDER_THIS_IS_ROUTE_URI>" => [
            'HEADERS_USED' => [ // from <SPECIFIC_ROUTE_URI_KEY>
                'VALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ],
                'INVALID' => [
                    'ADD' => [],
                    'REMOVE' => []
                ]
            ],
            'PARAMS_USED' => [ // from <SPECIFIC_ROUTE_URI_KEY>
                'VALID' => [],
                'INVALID' => [],
            ],
            'RATE_LIMITING_USED' => [ // from <SPECIFIC_ROUTE_URI_KEY>
                'VALID' => [],
                'INVALID' => [],
            ],
            // from <SPECIFIC_ROUTE_URI_KEY> | I really do NOT konw what I mean with "cache" for a specific route key though?
            // what would actually be "cached", where, for how long, and most importantly; WHY?
            'CACHE_USED' => [
                'VALID' => [],
                'INVALID' => []
            ],
            'CSP_USED' => [ // from <SPECIFIC_ROUTE_URI_KEY>
                'VALID' => [],
                'INVALID' => []
            ],
        ]],
    ]
];

// Initialize an array to hold the different compiled sections of the file
// and its sub parts so we can add sub parts as needed to the entire file!
$HTTPS_KERNEL_DISPATCH_FUNCTION_FOUND = false;
$deploymentBuffer = [];
$deploymentConfigBuffer = [];
$deploymentFunctionsBuffer = [];
$deploymentValidationBuffer = [];
$deploymentSQLBuffer = [];
$deploymentPagesBuffer = [];
$deploymentExtraFlagsBuffer = [];
if (!defined("FUNKPHP_FILE_PATH_DEPLOYMENT_FILE")) {
    cli_err("The Constant `FUNKPHP_FILE_PATH_DEPLOYMENT_FILE` containing exact Path to `/src/funkphp/FunkPHPDeployment.php` (FunkPHP Deployment Output File after Successful Building/Compiling) is NOT DEFINED! Please define it and rerun `php funk build` to try again!");
}
$deploymentPath = FUNKPHP_FILE_PATH_DEPLOYMENT_FILE;

// Files that will be loaded using "cli_file_status()"
$coreFunctionsFile = null;
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
            $compilePagesFlag = true;
            $compressDeploymentFlag = true;
        } else if ($flag === "--compile-pages") {
            $compilePagesFlag = true;
        } else if ($flag === "--embed-pages") {
            $embedPagesFlag = true;
        } else if ($flag === "--compress-deployment") {
            $compressDeploymentFlag = true;
        } else if ($flag === "--skip-compiling-validation") {
            $skipCompilingValidationFlag = true;
        } else if ($flag === "--skip-compiling-sql") {
            $skipCompilingSQLFlag = true;
        } else if ($flag === "--show-error-reporting-all-errors") {
            $showAllErrorsFlag = true;
        } else if ($flag === "--ignore-unknown-conns-drivers") {
            $ignoreUnknownConnsDriversFlag = true;
        } else if ($flag === "--allow-modified-core") {
            $allowModifiedCoreFlag = true;
        }
    }
}
cli_info_without_exit("### FunkCLI Compiling & Building `FunkPHPDeployment.php` with the following options:");
cli_info_without_exit("#### Allow Modified Core Files: " . ($allowModifiedCoreFlag ? "YES (the hashes of files in `src/funkphp/core`) will NOT be checked and you are on your own regarding what happens with the output)" : "NO"));
cli_info_without_exit("#### Ignore Unknown Connection Drivers: " . ($ignoreUnknownConnsDriversFlag ? "YES (even unknown types of credentials in `src/funkphp/config/conns.php`) will be added and included in the output)" : "NO"));
cli_info_without_exit("#### Skip Compiling Validation: " . ($skipCompilingValidationFlag ? "YES (Validation Functions will NOT be compiled before output)" : "NO"));
cli_info_without_exit("#### Skip Compiling SQL: " . ($skipCompilingValidationFlag ? "YES (SQL Functions will NOT be compiled before output)" : "NO"));
cli_info_without_exit("#### Do Include `error_reporting = E_ALL` in Deployment File: " . ($showAllErrorsFlag ? "YES (warning: sensitive info could be leaked in production!" : "NO"));
cli_info_without_exit("#### Do Compile Pages: " . ($compilePagesFlag ? "YES (pages will be compiled and output to 'funkphp/pages'" : "NO"));
cli_info_without_exit("#### Do Embed Pages: " . ($embedPagesFlag ? "YES (pages will be inside of the FunkPHPDeployment.php File)" : "NO"));
cli_info_without_exit("#### Do Compress Deployment: " . ($compressDeploymentFlag ? "YES (FunkPHPDeployment.php, pages and public_html folder will be in a single compresed file)" : "NO"));

// The actual compiling & building steps
////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 Loading, Validating & Compiling `config.php`  //////////
////////////////////////////////////////////////////////////////////////////////////////////////////
cli_info_without_exit("G`### Step 1 STARTS ###` Loading, Validating & Compiling `config.php` File ('Config' in FunkGUI)...");
$configWarnsAndErrs = [];
$cConfig = null;

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #0 validate all constants, assumed static files are available and can be read right now
////////////////////////////////////////////////////////////////////////////////////////////////////
if (!defined('FUNKPHP_FILE_PATH_CLI_RESERVED')) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_CLI_RESERVED` containing exact Path to `/src/cli/core/cli_reserved.php` (The Array String List of Reserved Functions) is NOT DEFINED!");
}
if (
    !defined("FUNKPHP_FILE_MANIFEST_CORE")
    || !is_readable(FUNKPHP_FILE_MANIFEST_CORE)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_MANIFEST_CORE` containing exact Path to `/src/funkphp/core/manifest.php` (FunkPHP Manifest File with Version Number & Hashes) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE!");
}
if (
    !defined('FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL')
    || !is_readable(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL` containing Exact File Path to FunkPHP Core Functions (`/src/funkphp/core/functions.php`) needed to work properly is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. Your `User-defined Functions` you can add/edit/remove are found in `/src/funkphp/config/functions.php`! DO NOT modify FunkPHP Core Functions File!");
}
if (!defined("FUNKPHP_FILE_PATH_C_CONFIG_FILE") || !file_exists(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_C_CONFIG_FILE` containing exact Path to `/src/funkphp/config/c.php` (FunkPHP Configuration File) is NOT DEFINED or Exact File Path does NOT EXIST/IS WRONG.");
}
// File exists but is not readable?
if (!is_readable(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The `/src/funkphp/config/c.php` (FunkPHP Configuration File) exists but is NOT READABLE! Please review the File Permissions and try again!");
}
if (!defined("FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED")) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `/src/funkphp/config/functions.php` (User-defined Globally Available Functions) is NOT DEFINED!");
}
if (!file_exists(FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `/src/funkphp/config/functions.php` (User-defined Globally Available Functions) is DEFINED but its File NOT FOUND!");
}
if (!is_readable(FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_USER_DEFINED` containing exact Path to `/src/funkphp/config/functions.php` (User-defined Globally Available Functions) is DEFINED & FOUND but is NOT READABLE! File Permission issues?");
}
if (!defined("FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES")) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES` containing exact Path to `/src/funkphp/core/function_templates.php` (Dynamic Function Templates File for Core Functions) is NOT DEFINED!");
}
if (!file_exists(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES` containing exact Path to `/src/funkphp/core/function_templates.php` (Dynamic Function Templates File for Core Functions) is DEFINED but its File NOT FOUND!");
}
if (!is_readable(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL_TEMPLATES` containing exact Path to `/src/funkphp/core/function_templates.php` (Dynamic Function Templates File for Core Functions) is DEFINED & FOUND but is NOT READABLE! File Permission issues?");
}
if (
    !defined('FUNKPHP_FILE_PATH_ROUTES') || !is_readable(FUNKPHP_FILE_PATH_ROUTES)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_ROUTES` containing Exact File Path to `/src/funkphp/core/pipeline_routes.php` (Your defined Routes & Middlewares) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. File Permission issues?");
}
if (
    !defined('FUNKPHP_FILE_PATH_TROUTES') || !is_readable(FUNKPHP_FILE_PATH_TROUTES)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_TROUTES` containing Exact File Path to `/src/funkphp/core/compiled_routes.php` (Compiled Trie-Routes with Metadata) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. File Permission issues?");
}
if (
    !defined('FUNKPHP_FILE_PATH_PIPELINE') || !is_readable(FUNKPHP_FILE_PATH_PIPELINE)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_PIPELINE` containing Exact File Path to `/src/funkphp/core/pipeline_request.php` (Global Config & Pipeline Request & Post_Response) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. File Permission issues?");
}
if (!defined("FUNKPHP_PIPELINE_REQUEST_DIR") || !is_readable(FUNKPHP_PIPELINE_REQUEST_DIR)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_PIPELINE_REQUEST_DIR` (`/src/funkphp/pipeline/request`) IS NOT DEFINED when it should be?! Review Directory Permissions. Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again!");
}
if (!defined("FUNKPHP_PIPELINE_POST_RESPONSE_DIR") || !is_readable(FUNKPHP_PIPELINE_POST_RESPONSE_DIR)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_PIPELINE_POST_RESPONSE_DIR` (`/src/funkphp/pipeline/post_response`) IS NOT DEFINED or NOT READABLE when it should be?! Review Directory Permissions. Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again!");
}
if (
    !defined('FUNKPHP_FILE_PATH_CONNS_CONFIG') || !is_readable(FUNKPHP_FILE_PATH_CONNS_CONFIG)
) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_CONNS_CONFIG` containing Exact File Path to `/src/funkphp/config/conns.php` (Your Credentials for your Connections) is NOT DEFINED or FILE DOES NOT EXIST or FILE IS NOT READABLE. IMPORTANT: Look in your `/src/funkphp/config` as `/src/funkphp/config/README_IN_IDE.php` File should have been created _before you read this_. Rename that file to `conns.php` as it contains starting templates for how `Connection Profiles` should look like.");
}
if (!defined('FUNKPHP_CLASSES_DIR') || !is_readable(FUNKPHP_CLASSES_DIR)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_CLASSES_DIR` containing Exact Dir Path to `/src/funkphp/classes` (Your classes that are NOT Vendor-based) is NOT DEFINED or PATH DOES NOT EXIST or IS NOT READABLE. File Permission issues?");
}
if (!defined("FUNKPHP_ROUTES_DIR") || !is_readable(FUNKPHP_ROUTES_DIR)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_ROUTES_DIR` (`/src/funkphp/pipeline/routes`) IS NOT DEFINED when it should be?! Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again!");
}
if (!defined("FUNKPHP_MIDDLEWARES_DIR") || !is_readable(FUNKPHP_MIDDLEWARES_DIR)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_MIDDLEWARES_DIR` (`/src/funkphp/pipeline/middlewares`) IS NOT DEFINED when it should be?! Try Git/Versioning Control (for `/src/cli/funk`) to get it back or redownload it again!");
}
// END OF LARGE CONSTANTS checks!
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the all the needed Constants in order to continue the FunkPHP Deployment Build process and try again!");

// Attempt loading /src/funkphp/core/manifest.php
$manifest = require_once FUNKPHP_FILE_MANIFEST_CORE;
if (!is_array($manifest) || array_is_list($manifest)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The FunkPHP Manifest File with Version Number & Hashes (`/src/funkphp/core/manifest.php`) is NOT ARRAY when it must be!");
}
// Load `/src/funkphp/config/c.php` and verify it's an array
if (is_readable(FUNKPHP_FILE_PATH_C_CONFIG_FILE)) {
    $cConfig = require_once FUNKPHP_FILE_PATH_C_CONFIG_FILE;
    if (!is_array($cConfig) || array_is_list($cConfig)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The `/src/funkphp/config/c.php` (FunkPHP Configuration File) exists and is Readable but does NOT return an Associative Array! Please check the File Contents and try again!");
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

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
    'FUNKPHP_CUSTOM_ERROR_HANDLER',
    'FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION',
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
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Key '$key NOT FOUND' in `/src/funkphp/config/c.php` (FunkPHP Configuration File)! Please check the File Contents and try again!");
    }
}
foreach ($cConfig as $cKey => $val) {
    if (
        !in_array($cKey, $cArrayKeysThatMustExist)
        && ($cKey !== "<ENTRY>")  && ($cKey !== "ROUTES") // Only applicable to the local web dev environment!
        && ($cKey !== 0) && ($cKey !== 1) && ($cKey !== 2) && ($cKey !== 3) // 0-3 are the !defined parts in the $c returned array!
    ) {
        cli_warning_without_exit("IGNORED: Key '$cKey' in `/src/funkphp/config/c.php` (FunkPHP Configuration File) will be ignored. Any custom variables should be in `\$c['custom']!");
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 validate  `config.php` - BOOLEAN keys in $c[...]
////////////////////////////////////////////////////////////////////////////////////////////////////
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Must be a `Boolean` for `HTTPS Redirection` to work properly!',
    ["FUNKPHP_ONLINE", 'assert' => 'boolean']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Must be a `Boolean` for the app to work properly!',
    ["FUNKPHP_USE_HTTPS", 'assert' => 'boolean']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Must be a `Boolean` for whether to Prepare Request URI or not for each incoming HTTP(S) Request. This must be set to `true` if you want to use your own Custom URI Normalizer Function!',
    ["FUNKPHP_USE_PREPARE_URI", 'assert' => 'boolean']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Must be a `Boolean` for whether to Use `Vendor/Composer-based Classes` or not for each incoming HTTP(S) Request!',
    ["FUNKPHP_USE_VENDOR", 'assert' => 'boolean']
);
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

// If vendor wanna be used, validate it actually exists, but this will NOT validate the entire contents of vendor!
if ($cConfig['FUNKPHP_USE_VENDOR'] === true) {
    if (!defined('FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE') || !is_readable(FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Constant `FUNKPHP_FILE_PATH_VENDOR_AUTOLOAD_FILE` containing exact Path to `src/funkphp/vendor/autoload.php` IS NOT DEFINED, or FILE IS NOT FOUND or FILE IS NOT READABLE. Set `FUNKPHP_USE_VENDOR` (via FunkCLI or FunkGUI) to 'false' if you do not wanna use Vendor/Composer-based classes!");
        cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for Main Keys 'FUNKPHP_ONLINE', 'FUNKPHP_USE_HTTPS', 'FUNKPHP_USE_PREPARE_URI', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION' & 'INI_SETS' in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");
    }
    cli_info_without_exit("IMPORTANT ABOUT VENDOR/COMPOSER BEING USED: FunkCLI does NOT validate the contents inside of the `src/funkphp/vendor` Directory and/or that it even exists after deployment, it just assumes it exists when you use `funk_use_vendor()`!");
}
////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 validate  `config.php` - User-defined CUSTOM_Functions that must all be unique if used
////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE THAT CUSTOM SET FNS ARE NOT THE SAME SINCE EACH FUNCTION MUST BE UNIQUE
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Key for which optional `Custom Exception Handler` to use or default is used. Set to `null` if not used or a `Non-Empty String` that is a `Function Name (cannot start with cli_ OR funk_) in /src/funkphp/config/functions.php`. This applies for every HTTP(S) Request!',
    ["FUNKPHP_CUSTOM_EXCEPTION_HANDLER", 'assert' => 'null|!str_starts_with:cli_,funk_']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Key for which optional `Custom Shutdown Function` to use or default is used. Set to `null` if not used or a `Non-Empty String` that is a `Function Name (cannot start with cli_ OR funk_) in /src/funkphp/config/functions.php`. This applies for every HTTP(S) Request!',
    ["FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION", 'assert' => 'null|!str_starts_with:cli_,funk_']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Key for which optional `Custom Error Handler` to use or default is used. Set to `null` if not used or a `Non-Empty String` that is a `Function Name (cannot start with cli_ OR funk_) in /src/funkphp/config/functions.php`. This applies for every HTTP(S) Request!',
    ["FUNKPHP_CUSTOM_ERROR_HANDLER", 'assert' => 'null|!str_starts_with:cli_,funk_']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Key for which optional `Custom URI Normalizer Function` to use or default is used. Set to `null` if not used or a `Non-Empty String` that is a `Function Name (cannot start with cli_ OR funk_) in /src/funkphp/config/functions.php`. This applies for every HTTP(S) Request!',
    ["FUNKPHP_CUSTOM_URI_NORMALIZER", 'assert' => 'null|!str_starts_with:cli_,funk_']
);
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    'Key is needed (`Non-Empty String OR null`) to know whether to use a Custom (from `/src/funkphp/config/functions.php`) Pipeline Request Function (name cannot start with cli_ OR funk_) to `handle Route Matching` and `Execution of Route Middlewares & Route Pipeline Functions`!',
    ["FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION", 'assert' => 'null|!str_starts_with:cli_,funk_']
);
// VALIDATE that "INI_SETS" is an associative array
// Checking c['INI_SETS'] a quick since it is very optional
cli_assert_validation_build_warn_errs(
    $cConfig,
    $configWarnsAndErrs,
    'cli_err',
    "Optional Key for which `runtime PHP Settings` (from 'php.ini') that you can be 'ran on shared web hosting deployments' when it is not possible to edit the '.ini' file directly. `Each Array Value` should be an `Associative Array with a String|Boolean|Int|Float Value`. Set to 'Empty Array' if not used!",
    ["INI_SETS", 'assert' => 'array-empty|array-associative-scalars-non-empty']
);
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

// ADD CUSTOM to the GLOBAL $ROUTES_CONFIG_PARSED Validated Data Array that are defined
// Their names are now GUARANTEED to NOT start with "funk_" or "cli_", neither collide with one another!
if (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])) {
    if (in_array($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The User-defined Function `{$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']}` in `/src/funkphp/config/functions.php` and/or `/src/funkphp/config/functions.php` is a `Reserved Function Name`! Please change it in both places or set it to `null` to use default choice instead!");
    }
    $ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']] = "FUNKPHP_CUSTOM_EXCEPTION_HANDLER";
}
if (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])) {
    if (isset($ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']])) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The set `FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION` is already being used by `{$ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']]}`! Make sure all `User-defined Functions`  in `/src/funkphp/config/functions.php` are all Unique!");
    }
    if (in_array($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The User-defined Function `{$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']}` in `/src/funkphp/config/functions.php` and/or `/src/funkphp/config/functions.php` is a `Reserved Function Name`! Please change it in both places or set it to `null` to use default choice instead!");
    }
    $ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']] = "FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION";
}
if (isset($cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER'])) {
    if (isset($ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']])) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The set `FUNKPHP_CUSTOM_ERROR_HANDLER` is already being used by `{$ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']]}`! Make sure all `User-defined Functions` in `/src/funkphp/config/functions.php` are all Unique!");
    }
    if (in_array($cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The User-defined Function `{$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']}` in `/src/funkphp/config/functions.php` and/or `/src/funkphp/config/functions.php` is a `Reserved Function Name`! Please change it in both places or set it to `null` to use default choice instead!");
    }
    $ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']] = "FUNKPHP_CUSTOM_ERROR_HANDLER";
}
if (isset($cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'])) {
    if (isset($ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']])) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The set `FUNKPHP_CUSTOM_URI_NORMALIZER` is already being used by `{$ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']]}`! Make sure all `User-defined Functions` in `/src/funkphp/config/functions.php` are all Unique!");
    }
    if (in_array($cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The User-defined Function `{$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']}` in `/src/funkphp/config/functions.php` and/or `/src/funkphp/config/functions.php` is a `Reserved Function Name`! Please change it in both places or set it to `null` to use default choice instead!");
    }
    $ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']] = "FUNKPHP_CUSTOM_URI_NORMALIZER";
}
if (isset($cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'])) {
    if (isset($ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']])) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The set `FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION` is already being used by `{$ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']]}`! Make sure all `User-defined Functions` in `/src/funkphp/config/functions.php` are all Unique!");
    }
    if (in_array($cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'], $reserved_functions)) {
        cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The User-defined Function `{$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']}` in `/src/funkphp/config/functions.php` and/or `/src/funkphp/config/functions.php` is a `Reserved Function Name`! Please change it in both places or set it to `null` to use default choice instead!");
    }
    $ROUTES_CONFIG_PARSED['USER_FNS_USED_FOR'][$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']] = "FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION";
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for Main Keys 'FUNKPHP_ONLINE', 'FUNKPHP_USE_HTTPS', 'FUNKPHP_USE_PREPARE_URI', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_EXCEPTION_HANDLER', 'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION', 'FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION', & 'INI_SETS' in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE BASEURLS Array Subkeys Paths!
////////////////////////////////////////////////////////////////////////////////////////////////////
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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'BASEURL' Main Key in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE SESSION Array Subkeys Paths!
////////////////////////////////////////////////////////////////////////////////////////////////////
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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'SESSION' Main Key in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE CLASSES Array Subkeys Paths!
////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE CLASSES Array Subkeys Paths!
$classesWarnsErrs = [];
$classesBUFFER = [];
$classesBUFFER[] = "namespace funkphp\\classes {\n";
$classesChecks = [];
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes"], $configWarnsAndErrs, "cli_err");
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes", "vendor"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($classesChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with vendor-based class instances `(from /src/funkphp/vendor)` during runtime.");
$classesChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["classes", "user"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($classesChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array  as it will be filled with user-defined class instances `(from /src/funkphp/classes)` during runtime.");
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'classes' Main Key in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");
/// SCAN funkphp/classes dir as we do not know if there are any
$classDir = scandir(FUNKPHP_CLASSES_DIR);
foreach ($classDir as $classFile) {
    if ($classFile === '.' || $classFile === '..' || !str_ends_with($classFile, ".php")) continue;
    $klassPath = FUNKPHP_CLASSES_DIR . '/' .  $classFile;
    if (isset($ROUTES_CONFIG_PARSED['ALL']['ALL_CLASSES_USED']['INVALID_PATHS'][$klassPath])) {
        cli_build_warning_err_list($classesWarnsErrs, "cli_err", "The Class File `$klassPath` already VALIDATED AS INVALID CLASS FILE!");
        continue;
    }
    $klass = cli_file_status("funkphp/classes", $classFile);
    if (
        (!cli_status_helper($klass, [
            "class_exists",
            "namespace_exists",
            "classes_same_count"
        ]) || ($klass['namespace_name'] !== 'funkphp\\classes'))
    ) {
        $ROUTES_CONFIG_PARSED['ALL']['ALL_CLASSES_USED']['INVALID_PATHS'][$klassPath] = true;
        cli_build_warning_err_list($classesWarnsErrs, "cli_err", "The Class File `$classFile` is NOT a Valid Class File; it must have namespace `funkphp\\classes;` and then no comments after closing class '}'! (check for trailing comments!)");
        continue;
    } else if (count($klass['class_names_duplicates']) > 0) {
        $ROUTES_CONFIG_PARSED['ALL']['ALL_CLASSES_USED']['INVALID_PATHS'][$klassPath] = true;
        cli_build_warning_err_list($classesWarnsErrs, "cli_err", "Duplicate Class Names in (`" . join(", ", array_keys($klass['class_names_duplicates'])) .  "`) in Class File `$klassPath`!");
        continue;
    }
    foreach ($klass['classes'] as $singleKlassK => $singleKlassV) {
        if (isset($ROUTES_CONFIG_PARSED['ALL']['ALL_CLASSES_USED']['VALID'][$singleKlassK])) {
            cli_build_warning_err_list($classesWarnsErrs, "cli_err", "The Class `$singleKlassK` in File `$klassPath` already VALIDATED AS VALID CLASS!");
            continue;
        } else if (!$singleKlassV['class_name_ucfirst']) {
            $ROUTES_CONFIG_PARSED['ALL']['ALL_CLASSES_USED']['INVALID'][$singleKlassK] = true;
            cli_build_warning_err_list($classesWarnsErrs, "cli_err", "The Class Name `$singleKlassK` in File `$klassPath` must START WITH AN UPPERCASE LETTER!");
            continue;
        }
        $ROUTES_CONFIG_PARSED['ALL']['ALL_CLASSES_USED']['VALID'][$singleKlassK] = true;
        $classesBUFFER[] = $singleKlassV['class_raw'];
    }
}
// Either it will fail here OR complete the classes buffer array
cli_stop_from_warn_err_list($classesWarnsErrs, "Please Review (" . count($classesWarnsErrs) . ") Warnings/Errors above for 'classes' Files in `/src/funkphp/classes`!");
$classesBUFFER[] = "}\n"; // We do NOT add classes yet to the entire buffer though! We haven't even added the starting part of the file!

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE CONNECTIONS Array Subkeys Paths!
////////////////////////////////////////////////////////////////////////////////////////////////////
// VALIDATE CONNECTIONS Array Subkeys Paths!
$connectionsChecks = [];
$connectionsChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["connections"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($connectionsChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with Connections like Databases Instances `(from /src/funkphp/config/conns.php)` and other connection-related service-instances during runtime and now during compilation the Connections Configuration Array (`/src/funkphp/config/conns.php`) will be validated first and then be stored in `[\$c -> 'credentials']`.");
$connectionsChecks[] = cli_assert_array_keys_path($cConfig, FUNKPHP_FILE_PATH_C_CONFIG_FILE, ["credentials"], $configWarnsAndErrs, "cli_err");
cli_assert_final_value(end($connectionsChecks), $configWarnsAndErrs, "cli_err", 'array-empty', "Must be an Empty Array as it will be filled with First Validated Credentials stored in `/src/funkphp/config/conns.php`!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 LOAD & VALIDATE ARRAY inside `src/funkphp/config/conns.php`
////////////////////////////////////////////////////////////////////////////////////////////////////
// Load & validate the array structure inside of `src/funkphp/config/conns.php`
$connsPayload = require FUNKPHP_FILE_PATH_CONNS_CONFIG;
if (!is_array($connsPayload)) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Credentials File for your Connections (`src/funkphp/config/conns.php`) is NOT A VALID ARRAY. Each array key should be the name of a connection, and then include a driver and its corresponding connection details for that type of driver.");
    cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'connections' & 'credentials' in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) via FunkGUI or in `/src/funkphp/config/conns.php` and try again!");
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
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Connection Profile [{$profileName}] in `/src/funkphp/config/conns.php` must be defined as an Associative Array Configuration Block.");
            continue;
        }
        // Validate that 'driver' exists and is a valid string
        if (!isset($profileData['driver']) || !is_string($profileData['driver'])) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Connection Profile [{$profileName}] in `/src/funkphp/config/conns.php` is missing a Valid String 'driver' key needed to know how to parse other (optional) keys.");
            continue;
        }
        $driver = $profileData['driver'];
        // Verify we actually support this database engine blueprint
        if (!array_key_exists($driver, $driverBlueprints)) {
            if ($ignoreUnknownConnsDriversFlag) {
                cli_info_without_exit("Unknown Connection Profile `$profileName` ignored due to Compilation flag `--ignore-unknown-conns-drivers`.");
            } else {
                cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "Connection Profile [{$profileName}] in `/src/funkphp/config/conns.php` specifies an Unknown Framework Driver: '{$driver}'. Current Framework Drivers supported for Data Schema Validation are: `" . join(", ", array_keys($driverBlueprints)) . "`! Include the compilation flag `--ignore-unknown-conns-drivers` to ignore validating unknown driver types.");
            }
            continue;
        }
        $blueprint = $driverBlueprints[$driver];
        // 5. Cross-reference profile data keys against our blueprint matrix
        foreach ($blueprint as $keyName => $rules) {
            $keyExists = array_key_exists($keyName, $profileData);
            if (!$keyExists && $rules['required']) {
                cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "In `/src/funkphp/config/conns.php` - Profile [{$profileName}] ({$driver}): Missing required configuration parameter '{$keyName}'.");
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
                    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "In `/src/funkphp/config/conns.php` - Profile [{$profileName}] ({$driver}): Key '{$keyName}' must be an exact type mismatch! Expected data type: [{$expectedType}].");
                }
            }
        }
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'connections' & 'credentials' in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) via FunkGUI or in `/src/funkphp/config/conns.php`");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE REQ Array Subkeys Paths!
////////////////////////////////////////////////////////////////////////////////////////////////////
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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for 'req' Main Key and its Subkeys in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE 'd', 'v' (and its associated keys), 'p' & 'files'
////////////////////////////////////////////////////////////////////////////////////////////////////
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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Keys 'd','v','v_ok','v_ok_files','v_config','v_data','s_data','p','p_config' & 'files' in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 VALIDATE 'err' and its subkeys!
////////////////////////////////////////////////////////////////////////////////////////////////////
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
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the Main Key 'err' and its Subkeys in the `/src/funkphp/config/c.php` (FunkPHP Configuration File) and try again!");

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 ALMOST DONE!
////////////////////////////////////////////////////////////////////////////////////////////////////
// Here ### Step 1 is fully validated so now we insert starting point of the `FunkPHPDeployment.php` file into the $deploymentBuffer array for later writing to disk!
cli_success_without_exit("G`### Step 1 DONE ###` Validating & Adding `c.php` (FunkPHP Configuration File) SUCCESSFULLY!.");
$BUILD_VERSION = "<?php // FunkPHPDeployment.php | Created: " . date("Y-m-d H:i:s") . " | PHP Version: " .  PHP_VERSION . " | FunkPHP Version: " . (FUNKPHP_VERSION ?? "<Unknown Version>") . " | FunkCLI Version: " . (FUNKCLI_VERSION ?? "<Unknown Version>") . "\n\n";
$deploymentBuffer[] = "<?php \nnamespace { "; // Opening Global namespace for nows

// Adding Starting Needed Constants First
$deploymentBuffer[] = "define('FUNKPHP_PAGES_DIR', __DIR__ . '/pages');\n";
$deploymentBuffer[] = "define('FUNKPHP_DEPLOYED', true);\n";
$deploymentBuffer[] = "define('FUNKPHP_NO_VALUE', new stdClass());\n";

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
$deploymentBuffer[] = implode("", $deploymentConfigBuffer);

// Adding optional /src/funkphp/vendor loading!
if ($cConfig['FUNKPHP_USE_VENDOR'] === true) {
    $deploymentBuffer[] = "require_once __DIR__ . '/vendor/autoload.php';\n";
}

// Now we FINALLY Load the Custom-related USER-DEFINED FUNCTIONS in from /src/funkphp/config/functions.php
// to see if we can actually insert them. Those we wanna look for are all unique and cannot start with
// "cli_" OR "funk_" as we will not even look for those user-defined named functions!
$userFunctionsFile = cli_file_status("funkphp/config", "functions.php");
//cli_dump($userFunctionsFile, false);
if (!cli_status_helper($userFunctionsFile, [
    "file_exists",
    "folder_readable",
])) {
    cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "The Configuration File `/src/funkphp/config/functions.php` (User-defined Globally Available Functions) WAS NOT FOUND or IS NOT READABLE! Review File Name and/or File Permissions and try again!");
}
// Now we look for each Custom-related USER-DEFINED Function to
//see that they are all there before we add them to the buffer!
else {
    if (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])) {
        if (
            !isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']])
            || !isset($userFunctionsFile['functions_via_tokenizer'][$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']])
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "User-Defined Function `{$cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER']}` was `NOT FOUND` in  `/src/funkphp/config/functions.php`! Make sure the `&\$c` is the first argument in its Function Argument List and remove any trailing comments after closing '}'!");
        }
    }
    if (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])) {
        if (
            !isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']])
            || !isset($userFunctionsFile['functions_via_tokenizer'][$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']])
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "User-Defined Function `{$cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION']}` was `NOT FOUND` in  `/src/funkphp/config/functions.php`! Make sure the `&\$c` is the first argument in its Function Argument List and remove any trailing comments after closing '}'!");
        }
    }
    if (isset($cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER'])) {
        if (
            !isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']])
            || !isset($userFunctionsFile['functions_via_tokenizer'][$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']])
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "User-Defined Function `{$cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER']}` was `NOT FOUND` in  `/src/funkphp/config/functions.php`! Make sure the `&\$c` is the first argument in its Function Argument List and remove any trailing comments after closing '}'!");
        }
    }
    if (isset($cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER'])) {
        if (
            !isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']])
            || !isset($userFunctionsFile['functions_via_tokenizer'][$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']])
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "User-Defined Function `{$cConfig['FUNKPHP_CUSTOM_URI_NORMALIZER']}` was `NOT FOUND` in  `/src/funkphp/config/functions.php`! Make sure the `&\$c` is the first argument in its Function Argument List and remove any trailing comments after closing '}'!");
        }
    }
    if (isset($cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'])) {
        if (
            !isset($userFunctionsFile['functions'][$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']])
            || !isset($userFunctionsFile['functions_via_tokenizer'][$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']])
        ) {
            cli_build_warning_err_list($configWarnsAndErrs, "cli_err", "User-Defined Function `{$cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION']}` was `NOT FOUND` in  `/src/funkphp/config/functions.php`! Make sure the `&\$c` is the first argument in its Function Argument List and remove any trailing comments after closing '}'!");
        }
    }
}
cli_stop_from_warn_err_list($configWarnsAndErrs, "Please Review (" . count($configWarnsAndErrs) . ") Warnings/Errors above for the `User-Defined Functions File /src/funkphp/config/functions.php` and try again!");

// NOW WE ARE GUARANTEED TO HAVE ALL AVAILABLE VALIDATED CUSTOM FUNCTION HANDLERS THAT ARE MEANT TO GO HERE!
// Adding custom or default Exception Handler Set
// 1. Exception Handler Deployment
$deploymentBuffer[] = "set_exception_handler(function (\\Throwable \$e) use (&\$c) {\n";
if (isset($cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'])) {
    $deploymentBuffer[] = "\\" . $cConfig['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'] . "(\$c, \$e);\n";
} else {
    $deploymentBuffer[] = "\\funk_default_exception_handler(\$c, \$e);\n";
}
$deploymentBuffer[] = "});\n";
// 2. Register Shutdown Function Deployment
$deploymentBuffer[] = "register_shutdown_function(function () use (&\$c) {\n";
if (isset($cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'])) {
    $deploymentBuffer[] = "\\" . $cConfig['FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION'] . "(\$c);\n";
} else {
    $deploymentBuffer[] = "\\funk_default_register_shutdown_function(\$c);\n";
}
$deploymentBuffer[] = "});\n";
// 3. Custom Error Handler Deployment (set_error_handler)
if (isset($cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER'])) {
    $deploymentBuffer[] = "set_error_handler(function (int \$errno, string \$errstr, string \$errfile, int \$errline) use (&\$c) {\n";
    $deploymentBuffer[] = "\\" . $cConfig['FUNKPHP_CUSTOM_ERROR_HANDLER'] . "(\$c, \$errno, \$errstr, \$errfile, \$errline);\n";
    $deploymentBuffer[] = "});\n";
}

// Add the Almighty Important `ob_start();` - FunkPHP Framework FOLLOWS "ob_start() FOR EVERY REQUEST - ALWAYS, YOU HAVE TO CHANGE IT MANUALLY!"
$deploymentBuffer[] = "ob_start();\n";

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 Adding the Functions now! (first USER-DEFINED, then CORE DEFINED)
////////////////////////////////////////////////////////////////////////////////////////////////////
// Adding the Functions now! (first user-defined, then in-built functions) where the
// user-defined with same name as the in-built is not allowed since both will be in global namespace!
// USER-DEFINED cannot start with "funk_" or "cli_" but can start with "funk_validate_" for custom validation
// functions.
cli_info_without_exit("G`### Step 2 STARTS ###` Loading, Validating & Compiling Core `functions.php` & User-defined `funkphp => config => functions.php` Files ('User-defined Functions' in 'Config' in FunkGUI)...");
$functionsWarnsAndErrs = [];

if (!$allowModifiedCoreFlag && (cli_get_hash_calculation_of_a_file(FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL) !== $manifest['hashes']['core']['funkphp/core/functions.php'][0])) {
    cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "The FunkPHP Core Functions File `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL` (`/src/funkphp/core/functions.php`) might be modified due to wrong calculated sha-256 hash value. Your User-defined Functions you should add/edit/remove are found in `/src/funkphp/config/functions.php`! DO NOT edit FunkPHP Core Functions File. Check your Git/File Versioning History to see if you can rollback any changes made to the FunkPHP Core Functions File, or Redownload the Files from an Official Source!");
    cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function Files (Core & User-defined) and try again!");
}

////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 Adding the Functions now! - first USER-DEFINED
////////////////////////////////////////////////////////////////////////////////////////////////////
// LOAD CORE FUNCTIONS & FUNCTION TEMPLATES FILES
$coreFunctionsTemplateFile = cli_file_status("funkphp/core", "function_templates.php");
$coreFunctionsFile = cli_file_status("funkphp/core", "functions.php");
foreach ($userFunctionsFile['functions'] as $fnNameUser => $fnValsUser) {
    if (
        isset($coreFunctionsFile['functions'][strtolower($fnNameUser)])
        || in_array(strtolower($fnNameUser), $reserved_functions, true)
    ) {
        $ROUTES_CONFIG_PARSED['ALL']['ALL_USER_FNS_USED']['INVALID'][$fnNameUser] = true;
        cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "User-defined Function '$fnNameUser' `(in /src/funkphp/config/functions.php`) is already used by FunkPHP/FunkCLI. Please choose rename the function (cannot start with `funk_` or `cli_`) or remove it!");
    } else if (
        (str_starts_with(strtolower($fnNameUser), "funk_") &&
            !str_starts_with(strtolower($fnNameUser), "funk_validate_"))
        || str_starts_with(strtolower($fnNameUser), "cli_")
        || (str_starts_with(strtolower($fnNameUser), "funk_validate_")
            && $fnValsUser['fn_exact_name'] !== $fnValsUser['fn_lowercased']
        )
    ) {
        $ROUTES_CONFIG_PARSED['ALL']['ALL_USER_FNS_USED']['INVALID'][$fnNameUser] = true;
        cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "User-defined Function '$fnNameUser' `(in /src/funkphp/config/functions.php`) starts with `funk_` (but not `funk_validate_`) or `cli_` which is not allowed. Please choose rename the function or remove it!");
    } else {
        $ROUTES_CONFIG_PARSED['ALL']['ALL_USER_FNS_USED']['VALID'][$fnNameUser] = true;
        $deploymentBuffer[] = $fnValsUser['fn_raw'] . "\n";
    }
}
cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for regarding User-defined `(in /src/funkphp/config/functions.php`) & In-built Functions `(in /src/funkphp/core/functions.php`)!");


////////////////////////////////////////////////////////////////////////////////////////////////////
////  STEP #1 Adding the Functions now! (CORE DEFINED with some TEMPLATE FUNCTIONS versions as well)
////////////////////////////////////////////////////////////////////////////////////////////////////
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
                $ROUTES_CONFIG_PARSED['ALL']['TEMPLATE_REPLACED_VALID']['INVALID'][$fnNameCore] = true;
                cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "Function '$fnNameCore' is marked for Token Replacement, but its Template was not found inside `/src/funkphp/core/function_templates.php`! The Function Structure must start with `function name(\&\$c`.");
                cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function (Templates) Files (Core & User-defined) (`/src/funkphp/core/function_templates.php`,`/src/funkphp/core/functions.php`,`/src/funkphp/config/functions.php`) and try again!");
            }
            $compiledTemplate = cli_function_template_token_replacer($functionsTemplatesArray[$fnNameCore], $templateRawCode);
            $ROUTES_CONFIG_PARSED['ALL']['ALL_IGNORED_FNS_USED']['VALID'][$fnNameCore] = true;
            $ROUTES_CONFIG_PARSED['ALL']['TEMPLATE_REPLACED_VALID']['VALID'][$fnNameCore] = true;
            $deploymentBuffer[] = $compiledTemplate . "\n";
        } else {
            $ROUTES_CONFIG_PARSED['ALL']['ALL_CORE_FNS_USED']['VALID'][$fnNameCore] = true;
            $deploymentBuffer[] = $fnValsCore['fn_raw'] . "\n";
        }
    }
}  // Core Functions not found by the helper function, but is it AllowedModifiedCore false?
else {
    if ($allowModifiedCore === false) {
        $ROUTES_CONFIG_PARSED['ALL']['ALL_CORE_FNS_USED']['INVALID'] = true;
        cli_build_warning_err_list($functionsWarnsAndErrs, "cli_err", "The FunkPHP Core Functions File `FUNKPHP_FILE_PATH_FUNCTIONS_INTERNAL` (`/src/funkphp/core/functions.php`) might be modified due to not finding Any Valid Structured Functions. Your User-defined Functions you should add/edit/remove are found in `/src/funkphp/config/functions.php`! DO NOT edit FunkPHP Core Functions File. Check your Git/File Versioning History to see if you can rollback any changes made to the FunkPHP Core Functions File, or Redownload the Files from an Official Source!");
        cli_stop_from_warn_err_list($functionsWarnsAndErrs, "Please Review (" . count($functionsWarnsAndErrs) . ") Warnings/Errors above for the Function Files (Core: `/src/funkphp/core/functions.php` & User-defined: `/src/funkphp/config/functions.php`) and try again!");
    } else {
        cli_warning_without_exit("FunkPHP Core Functions File in `/src/funkphp/core/functions.php` does NOT contain Any Valid Structured Functions (`function name(\&\$c){}`) or Any Functions at all. `Modified Core` is `set` to `ALLOWED` so it will be ignored. Cross your programming fingers for no errors now!");
    }
}
$deploymentBuffer[] = "}"; // Closing Global namespace for now
$deploymentBuffer[] = implode("", $classesBUFFER); // Now we add the classes buffer!

cli_success_without_exit("G`### Step 2 DONE ###` Validating & Adding User-defined Functions (`/src/funkphp/config/functions.php`), Core Functions (`/src/funkphp/core/functions.php`) SUCCESSFULLY!");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// NEXT UP FOR BUILD/COMPILE: Scoped Namespaces for pipeline_request (pl_) files!!! Will learn then if stuff even works
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$pipelineWarnsAndErrs = [];
cli_info_without_exit("G`### Step 3 STARTS ###` Loading, Validating & Compiling `pipeline_request.php`, `pipeline_routes.php` & `compiled_routes.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");
cli_info_without_exit("Recompiling & Rebuilding Routes (`/src/funkphp/core/pipeline_routes.php`) & Prefixed Routes (`/src/funkphp/core/compiled_routes.php`) using `cli_sort_build_routes_compile_and_output()`. If this step FAILS, the Building will ALSO Stop!");
[$TRIE, $RUTTER] = cli_sort_build_routes_compile_and_output($singleRoutesRoute, true); // $singleRoutesRoute is declared already `funk` File and also has default values if not existing!

cli_info_without_exit("G`### Step 3 CONTINUES ###` Loading, Validating & Compiling `pipeline_request.php`, `pipeline_routes.php` & `compiled_routes.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");
$pipelineFile = $singlePipeline; // $singleRoutesRoute is declared already `funk` File and also has default values if not existing!

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Key - GLOBAL HEADERS (add + remove), GLOBAL SRIS (internal + external)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
}), "Every External URL Key in in `[<CONFIG_GLOBAL> -> global_headers -> global_sris -> internal]` must start with `https://` for Security reasons!");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - GLOBAL CSP
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - GLOBAL RATE LIMITING
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_rate_limiting"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array|null', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_rate_limiting]` must be an Array or null!");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - GLOBAL PARAM RULES
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "<CONFIG_GLOBAL>", "global_param_rules"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-associative-strings', "All Values in `[pipeline -> <CONFIG_GLOBAL> -> global_param_rules]` must be Strings (empty or not) OR it must be an Empty Array!");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - GLOBAL DEFAULT NO ROUTE MATCH - Page, JSON, XML, Text & Callback
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - Request + Post_Response
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "request"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-list-strings-non-empty', "`[pipeline -> request]` must be a Numbered Array with Single Non-Empty String Values!");
$pipelineErrChecks[] = cli_assert_array_keys_path($pipelineFile, FUNKPHP_FILE_PATH_PIPELINE, ["pipeline", "post_response"], $pipelineWarnsAndErrs, "cli_err");
cli_assert_final_value(end($pipelineErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "`[pipeline -> post_response]` must be a Numbered Array with Single Non-Empty String Values OR an Empty Array!");
cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File (`/src/funkphp/core/pipeline_request.php`) in the Key `pipeline -> <CONFIG_GLOBAL>`. Please fix them and try again!");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - VALIDATE PARTS OF <GLOBAL_CONFIG> since it is only in pipeline_request.php file!
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// GLOBAL CONFIG PARAM RULES:
cli_dump($pipelineFile['pipeline']['<CONFIG_GLOBAL>']['global_param_rules'], false);
foreach ($pipelineFile['pipeline']['<CONFIG_GLOBAL>']['global_param_rules'] as $GLOBAL_PARAM_KEY => $GLOBAL_PARAM_VAL) {
    if (
        isset($ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['INVALID'][$GLOBAL_PARAM_KEY])
        || isset($ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['VALID'][$GLOBAL_PARAM_KEY])
    ) {
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Global Param Rule [pipeline -> <CONFIG_GLOBAL> -> global_param_rules -> $GLOBAL_PARAM_KEY] `$GLOBAL_PARAM_KEY` already added somehow?!");
        continue;
    }
    if (!is_string($GLOBAL_PARAM_VAL) || (empty(trim($GLOBAL_PARAM_VAL)))) {
        $ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['INVALID'][$GLOBAL_PARAM_KEY] = true;
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Global Param Rule [pipeline -> <CONFIG_GLOBAL> -> global_param_rules -> $GLOBAL_PARAM_KEY] `$GLOBAL_PARAM_KEY` is NOT a STRING or is Empty!");
        continue;
    }
    // Regex starts/ends with simplified []+|*?
    if (
        str_starts_with($GLOBAL_PARAM_VAL, '[')
        && (str_ends_with($GLOBAL_PARAM_VAL, ']+')
            || str_ends_with($GLOBAL_PARAM_VAL, ']*'))
    ) {
        $ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['VALID'][$GLOBAL_PARAM_KEY] = $GLOBAL_PARAM_VAL;
        continue;
    }
    // Regex starts with / and should be valid then?
    else if (str_starts_with($GLOBAL_PARAM_VAL, '/')) {
        try {
            if (@!preg_match($GLOBAL_PARAM_VAL, '')) {
                $ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['INVALID'][$GLOBAL_PARAM_KEY] = true;
                cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Global Param Rule [pipeline -> <CONFIG_GLOBAL> -> global_param_rules -> $GLOBAL_PARAM_KEY] `$GLOBAL_PARAM_KEY` started with `/` indicating a Regex but failed when tested!");
            };
            $ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['VALID'][$GLOBAL_PARAM_KEY] = $GLOBAL_PARAM_VAL;
        } catch (e) {
            $ROUTES_CONFIG_PARSED['GLOBAL']['PARAMS_USED']['INVALID'][$GLOBAL_PARAM_KEY] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Global Param Rule [pipeline -> <CONFIG_GLOBAL> -> global_param_rules -> $GLOBAL_PARAM_KEY] `$GLOBAL_PARAM_KEY` started with `/` indicating a Regex but failed when tested!");
            continue;
        }
    }
    // Last assumption is that the string is a user-defined fn in /src/funkphp/config/functions.php
    else {
        if (!isset($userFunctionsFile['functions'][$GLOBAL_PARAM_KEY])) {
        }
    }
    //cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "!");
}

cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File (`/src/funkphp/core/pipeline_request.php`) in the Key `pipeline -> <CONFIG_GLOBAL> -> PARAM_RUELS`. Please fix them and try again!");
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - ADD FILE=>FN TO THE DEPLOY-BUFFER!
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Now we start building namespace-scoped functions such as: 'funkphp\pipeline\request {}' & 'namespace 'funkphp\pipeline\post_response {}'

$DEFAULT_ERROR_FORMATTING = "\nRECOMMENDED: `1) ALWAYS lowercase Function Names everywhere. 2) ALWAYS USE Standard Formatting so every newline inside of Functions are indented at least once` OR it won't be found by the Compiler!";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - PIPELINE REQUEST FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add Pipeline Request Functions
$deploymentPipelineRequestBuffer[] = 'namespace funkphp\\pipeline\\request';
$deploymentPipelineRequestBuffer[] = " {\n";
$pipelineFileCount = 0;
foreach ($pipelineFile['pipeline']['request'] as $pipeRequestFn) {
    $pipelineFileCount++;
    if (isset($ROUTES_CONFIG_PARSED['ALL']['ALL_REQUEST_PIPELINE_FILES_FNS_USED']['VALID'][$pipeRequestFn])) {
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function File (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) has already been added? Duplicate use in `/src/funkphp/config/pipeline_request.php`?");
        continue;
    }
    $plReqStatus = cli_file_status(FUNKPHP_PIPELINE_REQUEST_DIR, $pipeRequestFn, true);
    if (!$plReqStatus['file_exists'] || !$plReqStatus['folder_readable']) { // file exists & is readable?
        $ROUTES_CONFIG_PARSED['ALL']['ALL_REQUEST_PIPELINE_FILES_FNS_USED']['INVALID'][$pipeRequestFn] = true;
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function File (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) was NOT FOUND or IS NOT READABLE!");
        cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> request` and try again! $DEFAULT_ERROR_FORMATTING");
    }
    if ($plReqStatus['namespace_name'] !== "funkphp\\pipeline\\request\\$pipeRequestFn") { // expected scoped namespace correct?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) was NOT FOUND in Expected `namespace funkphp\\pipeline\\request\\$pipeRequestFn;`!");
    }
    if (!isset($plReqStatus['functions'][$pipeRequestFn])) { // does function (name) exist?
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) was NOT FOUND in Expected Function `function $pipeRequestFn(&\$c) { // Code }`!");
    } else if (isset($plReqStatus['functions'][$pipeRequestFn])) {
        if (!$plReqStatus['functions_same_count']) {
            $ROUTES_CONFIG_PARSED['ALL']['ALL_REQUEST_PIPELINE_FILES_FNS_USED']['INVALID'][$pipeRequestFn] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) WAS FOUND USING Either Regex or Tokenizer but not both Indicating some Formatting Issue Inside File? (check for any trailing comment at the end of a function block(&\$c){} <-- here)");
        }
        if (!$plReqStatus['functions'][$pipeRequestFn]['fn_name_same_as_lowercased']) { // is function name lowercased?
            $ROUTES_CONFIG_PARSED['ALL']['ALL_REQUEST_PIPELINE_FILES_FNS_USED']['INVALID'][$pipeRequestFn] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) should only and always be lowercased!");
        }
        if (!str_starts_with(strtolower($pipeRequestFn), "pl_")) { // function name starts with "pl_"?
            $ROUTES_CONFIG_PARSED['ALL']['ALL_REQUEST_PIPELINE_FILES_FNS_USED']['INVALID'][$pipeRequestFn] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Request #$pipelineFileCount Function (`/src/funkphp/pipeline/request/$pipeRequestFn.php`) must start with `pl_` for the sake of consistency!");
        }
    }
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> request` and try again! $DEFAULT_ERROR_FORMATTING");

    // Notice & ignore for now that default `pl_https_kernel_dispatch` was included in Pipeline Request Array!
    if ($pipeRequestFn === "pl_https_kernel_dispatch") {
        $HTTPS_KERNEL_DISPATCH_FUNCTION_FOUND = true;
        continue;
    }
    $ROUTES_CONFIG_PARSED['ALL']['ALL_REQUEST_PIPELINE_FILES_FNS_USED']['VALID'][$pipeRequestFn] = true;
    $deploymentPipelineRequestBuffer[] = $plReqStatus['functions'][$pipeRequestFn]['fn_raw'] . "\n";
}
$deploymentPipelineRequestBuffer[] = " }\n"; // End namespace funkphp\pipeline\request {}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - PIPELINE POST_RESPONSE FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add Pipeline Post_Response Functions
$deploymentPipelineRequestBuffer[] = 'namespace funkphp\\pipeline\\post_response';
$deploymentPipelineRequestBuffer[] = " {\n";
$pipelineFileCount = 0;
foreach ($pipelineFile['pipeline']['post_response'] as $pipePostResponseFn) {
    $pipelineFileCount++;
    if (isset($ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['VALID'][$pipePostResponseFn])) {
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response #$pipelineFileCount Function File (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) has already been added? Duplicate use in `/src/funkphp/config/pipeline_request.php`?");
        continue;
    }
    $plReqStatus = cli_file_status(FUNKPHP_PIPELINE_POST_RESPONSE_DIR, $pipePostResponseFn, true);
    if (!$plReqStatus['file_exists'] || !$plReqStatus['folder_readable']) { // file exists & is readable?
        $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['INVALID'][$pipePostResponseFn] = true;
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "The Pipeline Post_Response Function File (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) was NOT FOUND or IS NOT READABLE!");
        cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> post_response` and try again! $DEFAULT_ERROR_FORMATTING");
    }
    if ($plReqStatus['namespace_name'] !== "funkphp\\pipeline\\post_response\\$pipePostResponseFn") { // expected scoped namespace correct?
        $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['INVALID'][$pipePostResponseFn] = true;
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response #$pipelineFileCount Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) was NOT FOUND in Expected `namespace funkphp\\pipeline\\post_response\\$pipePostResponseFn;`!");
    }
    if (!isset($plReqStatus['functions'][$pipePostResponseFn])) { // does function (name) exist?
        $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['INVALID'][$pipePostResponseFn] = true;
        cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response #$pipelineFileCount Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) was NOT FOUND in Expected Function `function $pipePostResponseFn(&\$c) { // Code }`!");
    } else if (isset($plReqStatus['functions'][$pipePostResponseFn])) {
        if (!$plReqStatus['functions_same_count']) {
            $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['INVALID'][$pipePostResponseFn] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response #$pipelineFileCount Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) WAS FOUND USING Either Regex or Tokenizer but not both Indicating some Formatting Issue Inside File? (check for any trailing comment at the end of a function block(&\$c){} <-- here)");
        }
        if (!$plReqStatus['functions'][$pipePostResponseFn]['fn_name_same_as_lowercased']) { // is function name lowercased?
            $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['INVALID'][$pipePostResponseFn] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response #$pipelineFileCount Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) should only and always be lowercased!");
        }
        if (!str_starts_with(strtolower($pipePostResponseFn), "pl_")) { // function name starts with "pl_"?
            $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['INVALID'][$pipePostResponseFn] = true;
            cli_build_warning_err_list($pipelineWarnsAndErrs, "cli_err", "Pipeline Post_Response #$pipelineFileCount Function (`/src/funkphp/pipeline/post_response/$pipePostResponseFn.php`) must start with `pl_` for the sake of consistency!");
        }
    }
    cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File (`/src/funkphp/core/pipeline_request.php`) in the Key `pipeline -> post_response` and try again! $DEFAULT_ERROR_FORMATTING");
    $ROUTES_CONFIG_PARSED['ALL']['ALL_POST_RESPONSE_PIPELINE_FILES_FNS_USED']['VALID'][$pipePostResponseFn] = true;
    $deploymentPipelineRequestBuffer[] = $plReqStatus['functions'][$pipePostResponseFn]['fn_raw'] . "\n";
}
$deploymentPipelineRequestBuffer[] = " }\n"; // End namespace funkphp\pipeline\post_response {}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - PIPELINE REQUEST SPECIAL CASE: `pl_https_kernel_dispatch` not found
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// STRONG CRITICAL WARNING if they skip the `pl_https_kernel_dispatch` which is the "trigger"
// to build the optimized route matching execution flow. Then it is all up to Dev to write their own!
if (!$HTTPS_KERNEL_DISPATCH_FUNCTION_FOUND) {
    cli_warning_without_exit("### ⚠️CRITICAL WARNING ### Expected `pl_https_kernel_dispatch` Pipeline Request Function NOT FOUND meaning the `Optimized Routing Matching Function` will `NOT BE PART OF THE BUILD`! (that String is needed to 'trigger' that Building step!");
    if (!isset($cConfig['FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION'])) {
        cli_warning_without_exit("⚠️A `Custom HTTPS Kernel Dispatch Pipeline Request Function` (`FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION` Key in `/src/funkphp/config/c.php`) WAS NOT FOUND meaning you must either write your own `pl_function` and add to the Pipeline Request Array that does all of it OR write a User-defined Function (in `/src/funkphp/config/functions.php`) that would be called if you write its Function Name in the `FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION` Key!");
    }
    cli_warning_without_exit("⚠️If you have no other Pipeline Request Function that can match the Routes inside of `/src/funkphp/core/compiled_routes.php` and execute matched Middlewares & Route Pipeline Functions in `/src/funkphp/core/pipeline_routes.php` then you might end up with a 'Dead-On-Compilation Build'!");
}
cli_stop_from_warn_err_list($pipelineWarnsAndErrs, "Please Review (" . count($pipelineWarnsAndErrs) . ") Warnings/Errors above for the Pipeline File in the Key `pipeline -> request` and try again! $DEFAULT_ERROR_FORMATTING");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Pipeline Keys - COMPLETE ADDING TO DEPLOY-BUFFER!
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add the valid Pipeline Request & Post_Response Functions to final buffer
// and remove them as they are no longer needed. If Dev wanna use them
// they will have to call them by:`\funkphp\pipeline\request|post_response\pl_name($c);`
$deploymentBuffer[] = implode("", $deploymentPipelineRequestBuffer);
$cConfig['pipeline'] = $pipelineFile['pipeline'];
unset($cConfig['pipeline']['request']);
unset($cConfig['pipeline']['post_response']);

cli_info_without_exit("G`### Step 4 STARTS ###` Loading, Validating, Rebuilding & Compiling `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' in 'Pipeline' in FunkGUI)...");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Routes File=>Fn & Middlewares Files - Prepare stuff before starting with it
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// $TRIE === Compiled Prefix Router, it has faster info instead of calculating it manually
// like how many (most+least) URI segments each HTTP(S) method has (used later for optimize route matching)
// $RUTTER === Developer's Routes; they were recompiled before we reached this point so they could
// not be changed maliciously. They (METHODS/ROUTES) should be guaranteed by pre-recompilation to be unique
// in each method with no conflicting same-level dynamic URI segments (e.g. GET/:test and GET/:test2)
$routesWarnsAndErrs = []; // Warns&Errs BOTH for $RUTTER and/or $TRIE
$NO_ROUTES = false;

cli_stop_from_warn_err_list($routesWarnsAndErrs, "Please Review (" . count($routesWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Pipeline Routes: `/src/funkphp/core/pipeline/routes` & Middlewares Files: `/src/funkphp/pipeline/middlewares`) and try again!");
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Routes File=>Fn & Middlewares Files - Prepare special-edge case (no routes!)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// BUILD/COMPILE Routes File=>Fn & Middlewares Files - ITERATE THROUGH METHODS => ROUTES => ROUTE (with File->Fn + MWs)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Before we iterate through each METHOD/Route we gonna check for the
// the <CONFIG_METHOD> for each METHOD first and valid keys=>values!
// METHODS verified in this order: GET,POST,PUT,DELETE,PATCH
$METHODNamesArray = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
foreach ($METHODNamesArray as $MethodName) {
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_headers"], $routesWarnsAndErrs, "cli_err");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_headers", "add"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-strings|array-empty', "All Values in `[ROUTES -> $MethodName -><CONFIG_METHOD> -> method_headers -> add]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_headers", "remove"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $pipelineWarnsAndErrs, "cli_err", 'array-strings|array-empty', "All Values in `[ROUTES -> $MethodName <CONFIG_METHOD> -> method_headers -> remove]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp"], $routesWarnsAndErrs, "cli_err");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "connect-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp-> connect-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "font-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> font-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "frame-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> frame-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "base-uri"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> base-uri]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "form-action"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> form-action]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "object-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> object-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "default-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> default-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "script-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> script-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "style-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> style-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_csp", "img-src"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_csp -> img-src]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_rate_limiting"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array|null', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_rate_limiting]` must be an Array or null!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_param_rules"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-associative-strings', "All Values in `[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_param_rules]` must be Strings (empty or not) OR it must be an Empty Array!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_default_no_route_match_response"], $routesWarnsAndErrs, "cli_err");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_default_no_route_match_response", "page"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'string|null', "`[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_default_no_route_match_response -> page]` must be a String or Null!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_default_no_route_match_response", "json"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array|null', "`[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_default_no_route_match_response -> json]` must be an Array or Null!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_default_no_route_match_response", "xml"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'string|null', "`[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_default_no_route_match_response -> xml]` must be a String or Null!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_default_no_route_match_response", "text"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'string|null', "`[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_default_no_route_match_response -> text]` must be a String or Null!");
    $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", $MethodName, "<CONFIG_METHOD>", "method_default_no_route_match_response", "callback"], $routesWarnsAndErrs, "cli_err");
    cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'string|null', "`[ROUTES -> $MethodName -> <CONFIG_METHOD> -> method_default_no_route_match_response -> callback]` must be a String or Null!");
}
cli_stop_from_warn_err_list($routesWarnsAndErrs, "Please Review (" . count($routesWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Routes: `/src/funkphp/core/pipeline_routes.php` & Compiled Routes: `/src/funkphp/core/compiled_routes.php`) and try again!");

// NOW WE CAN FINALLY GO THROUGH EACH ROUTE ($RUTT) IN EACH METHOD ($METOD)!
// When there ARE ROUTES TO Validate, Parse & Output!
// "VALID" | "INVALID" are to reuse
$FOUND_ROUTES_FILE_FNS = ['VALID' => [], 'INVALID' => []]; // It stores like "fileName" => "fnName"
$FOUND_ROUTES_MW_FNS = ['VALID' => [], 'INVALID' => []];  // It stores like "mwName" => "mwfnName"
if (!$NO_ROUTES) { //START-BLOCK:ROUTES TO Validate, Parse & Output!
    foreach ($RUTTER['ROUTES'] as $METODKey => $METOD) { // more Easter Eggs for those who know!
        // Check if current $METOD has zero routes (because others might have though)
        foreach ($METOD as $RUTT => $DATA) {
            if ($RUTT === '<CONFIG_METHOD>') {
                continue; // Skip <CONFIG_METHOD> as it has already been validated!
            }
            // Validate first main 4 keys for found route!
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config"], $routesWarnsAndErrs, "cli_err");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_alias"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'string', "The Value in `[ROUTES -> $METODKey -> $RUTT -> route_alias]` must be a String (empty allowed)!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_param_rules"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-associative-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> route_alias]` must be an Associative Array with Non-Empty String Values OR an Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_headers"], $routesWarnsAndErrs, "cli_err");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_headers", "add"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-strings|array-empty', "The Value in `[ROUTES -> $METODKey -> $RUTT -> route_headers -> add]` must be a String (empty allowed)!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_headers", "remove"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-strings|array-empty', "The Value in `[ROUTES -> $METODKey -> $RUTT -> route_headers -> remove]` must be a String (empty allowed)!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_rate_limiting"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array|null', "All Values in `[ROUTES -> $METODKey -> $RUTT -> method_rate_limiting]` must be an Array or null!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_cache"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array|null', "All Values in `[ROUTES -> $METODKey -> $RUTT -> route_cache]` must be an Array or null!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp"], $routesWarnsAndErrs, "cli_err");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "connect-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> connect-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "font-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> font-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "frame-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> frame-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "base-uri"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> base-uri]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "form-action"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> form-action]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "object-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> object-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "default-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> default-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "script-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> script-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "style-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> style-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "config", "route_csp", "img-src"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> config -> route_csp -> img-src]` must be an Array of Only Non-Empty Strings OR An Empty Array!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "middlewares"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> middlewares]` must be an Empty Array OR a Numbered Array with Only Non-Empty Strings!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "exclude_middlewares"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-empty|array-list-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> exclude_middlewares]` must be an Empty Array OR a Numbered Array with Only Non-Empty Strings!");
            $routesMethodsErrChecks[] = cli_assert_array_keys_path($RUTTER, FUNKPHP_FILE_PATH_ROUTES, ["ROUTES", "$METODKey", "$RUTT", "pipeline"], $routesWarnsAndErrs, "cli_err");
            cli_assert_final_value(end($routesMethodsErrChecks), $routesWarnsAndErrs, "cli_err", 'array-associative-strings-non-empty', "All Values in `[ROUTES -> $METODKey -> $RUTT -> pipeline]` must be an Associative Array with Non-Empty Strings Values!");
            // SPECIAL EDGE CASE: If 'pipeline' array is empty, there would be no pipeline functions to run after middlewares?
            if (empty($DATA['pipeline'])) {
                cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #0]: Missing at least 'One Pipeline File->Function Handler' from `/src/funkphp/pipeline/routes`. Each Route must map to least one of those!");
            }
            // Now the 4 main keys have been validated so now we can iterate through and start building to the 3 arrays
            cli_stop_from_warn_err_list($routesWarnsAndErrs, "Please Review (" . count($routesWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Routes: `/src/funkphp/core/pipeline_routes.php` & Compiled Routes: `/src/funkphp/core/compiled_routes.php`) and try again!");

            // MIDDLEWARES
            $mwCount = 0;
            $FOUND_MWS_LOCAL = [];
            foreach ($DATA['middlewares'] as $DATAmw) {
                $mwCount++;
                // First check if we already have stored it and either add error if invalid one or continue if valid one
                $mwDirFilePath = FUNKPHP_MIDDLEWARES_DIR . '/' . $DATAmw . '.php';
                if (isset($FOUND_ROUTES_MW_FNS['INVALID'][$DATAmw])) {
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` DOES NOT EXIST OR HAS INVALID STRUCTURE! Path: `" . $mwDirFilePath . "`");
                    continue;
                } else if (isset($FOUND_ROUTES_MW_FNS['VALID'][$DATAmw])) {
                    if (isset($FOUND_MWS_LOCAL[$DATAmw])) {
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` ALREADY EXISTS IN Middlewares Array - Duplicates Not Allowed for Middlewares! Path: `" . $mwDirFilePath . "`");
                        continue;
                    }
                    $FOUND_MWS_LOCAL[$DATAmw] = true;
                    continue;
                }
                $findMW = cli_file_status(FUNKPHP_MIDDLEWARES_DIR, $DATAmw, true, true);
                if (!$findMW['file_exists'] || !$findMW['file_readable']) { // Middleware File not found or not readable so we add this named key so it can be found faster next time
                    $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmw] = true;
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` was NOT FOUND in Expected Path OR it is NOT READABLE! Path: `" . $mwDirFilePath . "`");
                }
                // Middleware File found & Readable, so let's validate its content and add it to VALID if all OK
                // otherwise, we add to
                if ($findMW['file_exists']) {
                    if (!isset($findMW['functions'][$DATAmw])) { // MW Function not exist?
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmw] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` FOUND but its Function was NOT FOUND! Path: `" . $mwDirFilePath . "`");
                    }
                    // MW Function incorrect namespace name?
                    else if ($findMW['namespace_name'] !== ('funkphp\\pipeline\\middlewares\\' . $DATAmw)) {
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmw] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` FOUND but it has INVALID Namespace structure. Expected: `funkphp\pipeline\middleware\\$DATAmw;`! Path: `" . $mwDirFilePath . "`");
                    }
                    // MW Function not all lowercased?
                    else if (!$findMW['functions'][$DATAmw]['fn_name_same_as_lowercased']) {
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmw] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` FOUND but is `NOT ALL LOWERCASED`! Path: `" . $mwDirFilePath . "`");
                    }
                    // MW Function was not found same by regex vs tokenizer?
                    else if (!$findMW['functions_same_count']) {
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmw] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> middlewares #$mwCount]: The Middleware Function File `$DATAmw` FOUND but not both ways (`Regex & Tokenizer`). Function must start with `(&\$c)` in arguments, then check if the Function has trailing comments after the Function Closing `}`! Path: `" . $mwDirFilePath . "`");
                    }
                    // ALL OK HERE! We store the function and continue
                    else {
                        $FOUND_ROUTES_MW_FNS['VALID'][$DATAmw] = $findMW['functions'][$DATAmw]['fn_raw'];
                        $FOUND_MWS_LOCAL[$DATAmw] = true;
                        continue;
                    }
                }
            }
            // EXCLUDE_MIDDLEWARES
            $mwCount = 0;
            $FOUND_MWS_LOCAL2 = [];
            // SPECIAL EDGE CASE: Current Route is "/" root so no middlewares below can be excluded!
            if ($RUTT === "/") {
                if (count($DATA['exclude_middlewares']) > 0) {
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares]: Cannot Exclude Middlewares on Root Level since that would mean to impossibly look one level below it. Path: `" . $plDirFilePath . "`");
                }
            }
            foreach ($DATA['exclude_middlewares'] as $DATAmwx) {
                $mwCount++;
                $mwDirFilePath = FUNKPHP_MIDDLEWARES_DIR . '/' . $DATAmwx . '.php';
                if (isset($FOUND_MWS_LOCAL[$DATAmwx])) { // exclude_middlewares that should also be part of the middlewares on the same route? doesn't make any sense!
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` IS ALREADY IN `middlewares` Key for this Route. Cannot exclude it at the same time! Path: `" . $mwDirFilePath . "`");
                    continue;
                }
                // Then check if we already have stored it and either add error if invalid one or continue if valid one
                if (isset($FOUND_ROUTES_MW_FNS['INVALID'][$DATAmwx])) {
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` DOES NOT EXIST OR HAS INVALID STRUCTURE! Path: `" . $mwDirFilePath . "`");
                    continue;
                } else if (isset($FOUND_ROUTES_MW_FNS['VALID'][$DATAmwx])) {
                    if (isset($FOUND_MWS_LOCAL2[$DATAmwx])) {
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` ALREADY EXISTS IN Middlewares Array - Duplicates Not Allowed for Excluded Middlewares! Path: `" . $mwDirFilePath . "`");
                        continue;
                    }
                    $FOUND_MWS_LOCAL2[$DATAmwx] = true;
                    continue;
                }
                $findMW = cli_file_status(FUNKPHP_MIDDLEWARES_DIR, $DATAmwx, true, true);
                if (!$findMW['file_exists'] || !$findMW['file_readable']) { // Middleware File not found or not readable so we add this named key so it can be found faster next time
                    $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmwx] = true;
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` was NOT FOUND in Expected Path OR it is NOT READABLE! Path: `" . $mwDirFilePath . "`");
                }
                // Middleware File found & Readable, so let's validate its content and add it to VALID if all OK
                // otherwise, we add to
                if ($findMW['file_exists']) {
                    if (!isset($findMW['functions'][$DATAmwx])) { // MW Function not exist?
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmwx] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` FOUND but its Function was NOT FOUND! Path: `" . $mwDirFilePath . "`");
                    }
                    // MW Function incorrect namespace name?
                    else if ($findMW['namespace_name'] !== ('funkphp\\pipeline\\middlewares\\' . $DATAmwx)) {
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmwx] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` FOUND but it has INVALID Namespace structure. Expected: `funkphp\pipeline\middleware\\$DATAmwx;`! Path: `" . $mwDirFilePath . "`");
                    }
                    // MW Function not all lowercased?
                    else if (!$findMW['functions'][$DATAmwx]['fn_name_same_as_lowercased']) {
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmwx] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` FOUND but is `NOT ALL LOWERCASED`! Path: `" . $mwDirFilePath . "`");
                    }
                    // MW Function was not found same by regex vs tokenizer?
                    else if (!$findMW['functions_same_count']) {
                        $FOUND_ROUTES_MW_FNS['INVALID'][$DATAmwx] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware Function File `$DATAmwx` FOUND but not both ways (`Regex & Tokenizer`). Function must start with `(&\$c)` in arguments, then check if the Function has trailing comments after the Function Closing `}`! Path: `" . $mwDirFilePath . "`");
                    }
                    // SPECIAL INHERITENCE CHECK for exclude_middlewares (do they exist in all their levels
                    // below them?) and we will have already checked that it is not already "/" level!
                    // IMPORTANT: We might check for Middlewares that otherwise do exist as files+fns but not
                    // in those checked inherited subroutes so we cannot say it is invalid due that!
                    else if (!cli_inherited_middleware_exist($RUTTER['ROUTES'], $METODKey, $RUTT, $DATAmwx)) {
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> exclude_middlewares #$mwCount]: The Middleware `$DATAmwx` CANNOT BE EXCLUDED because none of its Parent Routes have it! Path: `$mwDirFilePath`");
                    }
                    // ALL OK HERE! We store the function and continue
                    else {
                        $FOUND_ROUTES_MW_FNS['VALID'][$DATAmwx] = $findMW['functions'][$DATAmwx]['fn_raw'];
                        $FOUND_MWS_LOCAL2[$DATAmwx] = true;
                        continue;
                    }
                }
            }
            // PIPELINE
            $plCount = 0;
            $FOUND_PL_LOCAL = [];
            foreach ($DATA['pipeline'] as $DATAplFile => $DATAplFn) {
                $plCount++;
                // $DATAplFile = key($DATAplFn1);
                // $DATAplFn = $DATAplFn1[$DATAplFile];

                $plDirFilePath = FUNKPHP_ROUTES_DIR . '/' . $DATAplFile . '.php';
                if (isset($FOUND_ROUTES_FILE_FNS['INVALID'][$DATAplFile][$DATAplFn])) { // Invalid File->FN already confirmed, so could never be used!
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) DOES NOT EXIST OR HAS INVALID STRUCTURE! Path: `" . $plDirFilePath . "`");
                    continue;
                }
                // Valid File->FN already confirmed, but is it already in its own array?
                else if (isset($FOUND_ROUTES_FILE_FNS['VALID'][$DATAplFile][$DATAplFn])) {
                    if (isset($FOUND_PL_LOCAL[$DATAplFile][$DATAplFn])) {
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) ALREADY EXISTS IN Pipeline Array - Duplicates Not Allowed for Middlewares! Path: `" . $plDirFilePath . "`");
                        continue;
                    }
                    $FOUND_PL_LOCAL[$DATAplFile][$DATAplFn] = true;
                    continue;
                }
                $findpl = cli_file_status(FUNKPHP_ROUTES_DIR, $DATAplFile, true, true);
                // Pipeline File not found or not readable so we add this named key so it can be found faster next time
                if (!$findpl['file_exists'] || !$findpl['file_readable']) {
                    $FOUND_ROUTES_FILE_FNS['INVALID'][$DATAplFile][$DATAplFn] = true;
                    cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) File was NOT FOUND in Expected Path OR it is NOT READABLE! Path: `" . $plDirFilePath . "`");
                }
                // Pipeline File found so let's check if its Function exist
                if ($findpl['file_exists']) {
                    if (!isset($findpl['functions'][$DATAplFn])) { // Route PL Function not exist?
                        $FOUND_ROUTES_FILE_FNS['INVALID'][$DATAplFile][$DATAplFn] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) File FOUND but its Function was NOT FOUND! Path: `" . $plDirFilePath . "`");
                    }
                    // Route PL Function incorrect namespace name?
                    else if ($findpl['namespace_name'] !== ('funkphp\\pipeline\\routes\\' . $DATAplFile)) {
                        $FOUND_ROUTES_FILE_FNS['INVALID'][$DATAplFile][$DATAplFn] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) File FOUND but it has INVALID Namespace structure. Expected: `funkphp\pipeline\routes\\$DATApl;`! Path: `" . $plDirFilePath . "`");
                    }
                    // Route PL Function not all lowercased?
                    else if (!$findpl['functions'][$DATAplFn]['fn_name_same_as_lowercased']) {
                        $FOUND_ROUTES_FILE_FNS['INVALID'][$DATAplFile][$DATAplFn] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) File FOUND but is `NOT ALL LOWERCASED`! Path: `" . $plDirFilePath . "`");
                    }
                    // Route PL Function was not found same by regex vs tokenizer?
                    else if (!$findpl['functions_same_count']) {
                        $FOUND_ROUTES_FILE_FNS['INVALID'][$DATAplFile][$DATAplFn] = true;
                        cli_build_warning_err_list($routesWarnsAndErrs, "cli_err", "For [$METODKey$RUTT -> pipeline #$plCount]: The Route Pipline File->Function (`$DATAplFile -> $DATAplFn`) File FOUND but not both ways (`Regex & Tokenizer`). Function must start with (`(&\$c)`) in arguments, then check if the Function has trailing comments after the Function Closing `}`! Path: `" . $plDirFilePath . "`");
                    } else {
                        $FOUND_ROUTES_FILE_FNS['VALID'][$DATAplFile][$DATAplFn] = $findpl['functions'][$DATAplFn]['fn_raw'];
                        $FOUND_PL_LOCAL[$DATAplFile][$DATAplFn] = true;
                        continue;
                    }
                }
            }
        }
    }
    cli_stop_from_warn_err_list($routesWarnsAndErrs, "Please Review (" . count($routesWarnsAndErrs) . ") Warnings/Errors above for the Pipeline Files (Routes: `/src/funkphp/pipeline/routes` & Middlewares: `/src/funkphp/pipeline/middlewares`) and try again!");
} //END-BLOCK:ROUTES TO Validate, Parse & Output!

// PUT ALL MIDDLEWARES TOGETHER (ONE NS)
$deploymentMiddlewaresBuffer = [];
$deploymentMiddlewaresBuffer[] = "namespace funkphp\\pipeline\\middlewares {";
foreach ($FOUND_ROUTES_MW_FNS['VALID'] as $mwName => $mwRawCode) {
    $COMPILE_STATS_TRACKER['Pipeline-MIDDLEWARES'][] = $mwName;
    $deploymentMiddlewaresBuffer[] = $mwRawCode . "\n";
}
$deploymentMiddlewaresBuffer[] = "}"; // Close Middlewares Namespace
// THEN PUT ALL ROUTES (File->FN) which becomes (NS -> FNs)
$deploymentPipelineRoutesBuffer = [];
foreach ($FOUND_ROUTES_FILE_FNS['VALID'] as $fileName => $functions) {
    // Open a dedicated namespace for this specific pipeline file
    $deploymentPipelineRoutesBuffer[] = "namespace funkphp\\pipeline\\routes\\{$fileName} {";
    foreach ($functions as $fnName => $fnRawCode) {
        $COMPILE_STATS_TRACKER['Pipeline-ROUTES'][] = $fnName;
        $deploymentPipelineRoutesBuffer[] = $fnRawCode . "\n";
    }
    $deploymentPipelineRoutesBuffer[] = "}"; // Close this specific file's namespace
}
$deploymentBuffer[] = implode("", $deploymentMiddlewaresBuffer);
$deploymentBuffer[] = implode("", $deploymentPipelineRoutesBuffer);
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
Please check the File Permissions for the File Path above and try again!
==========================================================================================================================");
}
// Notify about super-important message if no routes were compiled but without any errors and/or warnings
if ($NO_ROUTES) {
    cli_warning_without_exit("
==========================================================================================================================
    SUPER-IMPORTANT: SCROLL UP TO READ SUPER-IMPORTANT MESSAGE REGARDING THE COMPILATION! ^_^ (look for `CRITICAL NOTICE`)
==========================================================================================================================");
}

// Prepare
$activeFlags = [];

if (!empty($compilePages))           $activeFlags[] = "    [+] --compile-pages           (Page layout caching enabled)";
if (!empty($embedPages))             $activeFlags[] = "    [+] --embed-pages             (Static asset embedding enabled)";
if (!empty($compressDeployment))     $activeFlags[] = "    [+] --compress-deployment      (Whitespace & comment stripping active)";
if (!empty($skipCompilingValidation)) $activeFlags[] = "    [-] --skip-compiling-validation (Validation checks bypassed)";
if (!empty($skipCompilingSQL))        $activeFlags[] = "    [-] --skip-compiling-sql        (SQL compilation skipped)";
if (!empty($showAllErrors))          $activeFlags[] = "    [!] --show-error-reporting-all-errors (Verbose error reporting active)";
if (!empty($ignoreUnknownConnsDrivers)) $activeFlags[] = "    [!] --ignore-unknown-conns-drivers   (Driver verification bypassed)";
if (!empty($allowModifiedCore))      $activeFlags[] = "    [!] --allow-modified-core      (Core signature verification skipped)";
// Fallback text if a developer runs a raw compiler build without args
$flagsOutput = empty($activeFlags)
    ? "    • None (Default Core Optimization Build)\n"
    : implode("\n", $activeFlags) . "\n";

// Output stats of the number of global functions, namespaced functions, middlewares, classes, etc. compiled.
// now it is numbered, but we will include names also so it can easily be seen what is used and NOT used.
// FIX: Also include an IGNORED list
$compilationStatsOutput = "";
if (isset($COMPILE_STATS_TRACKER)) {
    // 1. Core Functions & Custom Routines
    $cntCoreFns    = count($COMPILE_STATS_TRACKER['Core-FUNCTIONS'] ?? []);
    $cntUserFns    = count($COMPILE_STATS_TRACKER['User-FUNCTIONS'] ?? []);
    $cntIgnoredFns = count($COMPILE_STATS_TRACKER['Ignored-FUNCTIONS'] ?? []);
    $cntClasses    = count($COMPILE_STATS_TRACKER['CLASSES'] ?? []);
    // 2. The Functional Pipeline Stages
    $cntPlReq      = count($COMPILE_STATS_TRACKER['Pipeline-REQUEST'] ?? []);
    $cntPlMws      = count($COMPILE_STATS_TRACKER['Pipeline-MIDDLEWARES'] ?? []);
    $cntPlRoutes   = count($COMPILE_STATS_TRACKER['Pipeline-ROUTES'] ?? []);
    $cntPlPost     = count($COMPILE_STATS_TRACKER['Pipeline-POST_RESPONSE'] ?? []);
    // 3. UI Template Pages Engine
    $cntPagesRef   = count($COMPILE_STATS_TRACKER['Pages-REFERENCED'] ?? []);
    $cntPagesComp  = count($COMPILE_STATS_TRACKER['Pages-COMPILED'] ?? []);
    $cntPagesEmb   = count($COMPILE_STATS_TRACKER['Pages-EMBEDDED'] ?? []);
    // 4. Data Layer Optimization Metrics
    $cntDataSql    = count($COMPILE_STATS_TRACKER['Data-SQL'] ?? []);
    $cntDataVal    = count($COMPILE_STATS_TRACKER['Data-VALIDATION'] ?? []);
    $cntDataQry    = count($COMPILE_STATS_TRACKER['Data-QUERY'] ?? []);
    // 5. Structure the aligned CLI reporting visual tree
    $compilationStatsOutput = "\n[ COMPILED METRICS SUMMARY ]\n"
        . "  ├── GLOBAL FUNCTIONS/CLASSES\n"
        . "  │   ├── Core Functions Inline:         " . $cntCoreFns . "\n"
        . "  │   ├── User Functions Compiled:       " . $cntUserFns . "\n"
        . "  │   ├── Static Classes Parsed:         " . $cntClasses . "\n"
        . "  │   └── Dead Functions Ignored:        " . $cntIgnoredFns . "\n"
        . "  │\n"
        . "  ├── PIPELINE FUNCTIONS\n"
        . "  │   ├── Pipeline Request FNs:          " . $cntPlReq . "\n"
        . "  │   ├── Pipeline Middlewares:          " . $cntPlMws . "\n"
        . "  │   ├── Namespaced Route FNs:          " . $cntPlRoutes . "\n"
        . "  │   └── Post-Response FNs:             " . $cntPlPost . "\n"
        . "  │\n"
        . "  ├── PAGES & REFERENCES TO THEM\n"
        . "  │   ├── Unique Pages Referenced:       " . $cntPagesRef . "\n"
        . "  │   ├── Structural Pages Compiled:     " . $cntPagesComp . "\n"
        . "  │   └── Inlined Assets Embedded:       " . $cntPagesEmb . "\n"
        . "  │\n"
        . "  └── QUERY & VALIDATION & OPTIMIZATIONS\n"
        . "      ├── Pre-Compiled SQL Blocks:       " . $cntDataSql . "\n"
        . "      ├── Compiled Validation Rules:     " . $cntDataVal . "\n"
        . "      └── Memoized Pure Queries:         " . $cntDataQry . "\n";
}
// Print final success payload
cli_dump($ROUTES_CONFIG_PARSED['ALL'], false);
cli_success("
==========================================================================================================================
FunkCLI SUCCESSFULLY Compiled `" . FUNKPHP_FILE_PATH_DEPLOYMENT_FILE . "`
You can now Deploy the `FunkPHPDeployment.php` File to Your Server for Production use!

[ ACTIVE ENGINE OPTIONS ]
" . $flagsOutput . $compilationStatsOutput . "

FunkCLI SUCCESSFULLY Compiled `" . FUNKPHP_FILE_PATH_DEPLOYMENT_FILE . "`
You can now Deploy the `FunkPHPDeployment.php` File to Your Server for Production use!
==========================================================================================================================");
exit;
