<?php // STEP 4: Match, fetch, validate data from different sources

// Only run this step if the current step is 4
if ($c['req']['current_step'] === 4) {
    // This is the fifth step of the request, so we can run this step now!

    // Load Data Routes since we are at this step and need them!
    // GOTO "funkphp/data/data_single_routes.php" to Add Your Single Routes!
    // GOTO "funkphp/data/data_middleware_routes.php" to Add Your Middleware Routes!
    $c['ROUTES']['DATA'] = [
        //'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/data_troute.php',
        'SINGLES' => include dirname(__DIR__) . '/data/data_single_routes.php',
        'MIDDLEWARES' => include dirname(__DIR__) . '/data/data_middleware_routes.php',
    ];

    // This is the end of Step 4, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 5; // Set next step to 5 (Step 5)

}
$c['req']['current_step'] = $c['req']['next_step'];
