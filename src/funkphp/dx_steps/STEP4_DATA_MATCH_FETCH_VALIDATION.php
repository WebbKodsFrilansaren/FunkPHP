<?php // STEP 4: Match, fetch, validate data from different sources

// Load Data Routes since we are at this step and need them!
// GOTO "funkphp/data/single_routes.php" to Add Your Single Routes!
// GOTO "funkphp/data/middleware_routes.php" to Add Your middlewares!
$c['ROUTES']['DATA'] = [
    //'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/data_troute.php',
    'SINGLES' => include dirname(__DIR__) . '/data/data_single_routes.php',
    'MIDDLEWARES' => include dirname(__DIR__) . '/data/data_middleware_routes.php',
];
