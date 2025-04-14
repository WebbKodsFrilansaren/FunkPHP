<?php // STEP 5: Return a matched page after route and data matching!

// Load Page Routes since we are at this step and need them!
// GOTO "funkphp/page/single_routes.php" to Add Your Single Routes!
// GOTO "funkphp/page/middleware_routes.php" to Add Your middlewares!
$c['ROUTES']['PAGE'] = [
    //'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/page_troute.php',
    'SINGLES' => include dirname(__DIR__) . '/pages/page_single_routes.php',
    'MIDDLEWARES' => include dirname(__DIR__) . '/pages/page_middleware_routes.php',
];
