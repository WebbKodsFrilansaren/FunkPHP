<?php
// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
return [
    'INI_SETS' => include __DIR__ . '/ini_sets.php',
    'BASEURLS' => include __DIR__ . '/BASEURLS.php',
    'COOKIES' => include __DIR__ . '/COOKIES.php',
    'HEADERS' => include __DIR__ . '/HEADERS.php',
    'STATIC' => include __DIR__ . '/STATIC.php',
    'DEFAULT_VALUES' => include __DIR__ . '/DEFAULT_VALUES.php',
    'DEFAULT_ACTION' => include __DIR__ . '/DEFAULT_ACTION.php',
    'ROUTES' => [
        // Route matching Loads first:/"dx_steps/STEP3_ROUTE_MATCH_MIDDLEWARES.php"
        // Change their Loading Logic there if needed!
    ],
    // 'TABLES' is the array of SQL Tables that will be used to handle the database tables and their data!
    'TABLES' => include __DIR__ . '/tables.php',

    // 'req' is the array of request data which will also include changed data based
    // on matched route, middlewares (if any), data (if any) and page (if any), etc.
    'req' => include __DIR__ . '/req.php',
    'db' => include __DIR__ . '/db.php',
    // 'd' will ALWAYS store fetched database data and/or validated 'post', 'get', 'json' data!
    'd' => [
        "VALIDATION_FAILED" => null // This is by default null, but can be set to true or false based on the validation result!
    ],
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
        'FAILED_TO_RUN_STATIC' => false,
    ],

];
