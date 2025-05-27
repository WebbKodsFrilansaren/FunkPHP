<?php
// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
return [
    'INI_SETS' => include __DIR__ . '/ini_sets.php',
    'BASEURLS' => include __DIR__ . '/BASEURLS.php',
    'COOKIES' => include __DIR__ . '/COOKIES.php',
    'HEADERS' => include __DIR__ . '/HEADERS.php',
    'STATIC' => include __DIR__ . '/STATIC.php',
    // Route matching Loads first:"STEP 3: Match Single Route and its associated Middlewares"
    // in "funkphp_start.php" file! Change their Loading Logic there if needed!
    'ROUTES' => [],
    // 'TABLES' is the array of SQL Tables that will be used to handle the database tables and their data!
    'TABLES' => include __DIR__ . '/tables.php',

    // 'req' is the array of request data which will also include changed data based
    // on matched route, middlewares (if any), data (if any) and page (if any), etc.
    'req' => include __DIR__ . '/req.php',

    // 'db' is the database object that will be used to handle the database connection & queries!
    'db' => include __DIR__ . '/db.php',

    // 'v' should be NULL but stores ANY founds errors during the validation process while
    // 'v_ok' will is true if not a single v['key']['optionalSubkey'] is set with error(s)!
    // The 'v_ok_files' is boolean for validating files and works the same way as 'v_ok'!
    // 'v_config' is a global array of validation configurations that can be accessed
    // when validating no matter how nested or not the validation is! It stores "password"
    // to for "password_confirm" to check against the "password" field, etc.
    // 'v_data' contains the validate data for a given validation process!
    'v' => null,
    'v_ok' => null,
    'v_ok_files' => null,
    'v_config' => [],
    'v_data' => null,

    // 'd' will ALWAYS store fetched database data (it does NOT store validation errors)
    'd' => null,

    // 'p' is the page object that will be used to handle the page rendering and output (not needed for API requests)!
    'p' => null,

    // 'files' is the array of uploaded files (if any) that will be used to handle the file uploads!
    'files' => null,

    // 'err(ors)' is an array of errors that will be filled when errors occur in the application!
    // so they can optionally be handled later in the application flow!
    'err' => [
        'FAILED_TO_MATCH_ROUTE' => false,
        'FAILED_TO_RUN_ROUTE_HANDLER' => false,
        'FAILED_TO_RUN_DATA_HANDLER' => false,
        'FAILED_TO_LOAD_VALIDATION_FILE' => false,
        'FAILED_TO_RUN_VALIDATION_FUNCTION' => false,
        'FAILED_TO_RUN_VALIDATION_FUNCTION_FILES' => false,
        'FAILED_TO_RUN_PAGE_HANDLER' => false,
        'FAILED_TO_RENDER_PAGE_FILE' => false,
        'FAILED_TO_LOAD_MIDDLEWARE' => false,
        'FAILED_TO_RUN_MIDDLEWARE' => false,
        'FAILED_TO_RUN_JSON' => false,
        'FAILED_TO_RUN_API' => false,
        'FAILED_TO_RUN_DB' => false,
        'FAILED_TO_RUN_CACHE' => false,
        'FAILED_TO_RUN_SESSION' => false,
        'FAILED_TO_RUN_COOKIE' => false,
        'FAILED_TO_RUN_HEADER' => false,
    ],

];
