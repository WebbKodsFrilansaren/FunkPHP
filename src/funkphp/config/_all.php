<?php
// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure the included files below here separately as needed!
return [
    'INI_SETS' => include __DIR__ . '/ini_sets.php',
    'BASEURLS' => include __DIR__ . '/BASEURLS.php',
    'COOKIES' => include __DIR__ . '/COOKIES.php',
    'HEADERS' => include __DIR__ . '/HEADERS.php',
    'STATIC' => include __DIR__ . '/STATIC.php',
    'DEFAULT_ACTION' => include __DIR__ . '/DEFAULT_ACTION.php',
    'ROUTES' => [
        // Route matching Loads first:/"dx_steps/STEP2_MATCH_ROUTES_MIDDLEWARES.php"
        // Change their Loading Logic there if needed!

        // Data matching Loads first:/"dx_steps/STEP4_DATA_MATCH_FETCH_VALIDATION.php"
        // Change their Loading Logic there if needed!

        // Page matching Loads first:/"dx_steps/STEP5_PAGE_MATCH_AFTER_DATA_MATCH.php"
        // Change their Loading Logic there if needed!
    ],
    'req' => include __DIR__ . '/req.php',
    'db' => include __DIR__ . '/db.php',
    // 'd' will ALWAYS store fetched database data and/or validated 'post', 'get', 'json' data!
    'd' => null,
    // 'p' is the page object that will be used to handle the page rendering and output (not needed for API requests)!
    'p' => null,
    // 'files' is the array of uploaded files (if any) that will be used to handle the file uploads!
    'files' => null
];
