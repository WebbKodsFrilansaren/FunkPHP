<?php // STEP 4: Match, fetch, validate data from different sources

// Only run this step if the current step is 4
if ($c['req']['current_step'] === 4) {
    // This is the fifth step of the request, so we can run this step now!

    // Load Data Routes since we are at this step and need them!
    // GOTO "funkphp/data/data_single_routes.php" to Add Your Single Routes!
    // GOTO "funkphp/data/data_middleware_routes.php" to Add Your Middleware Routes!
    $c['ROUTES']['DATA'] = [
        'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/troute_data.php',
        'SINGLES' => include dirname(__DIR__) . '/data/data_single_routes.php',
        'MIDDLEWARES' => include dirname(__DIR__) . '/data/data_middleware_routes.php',
    ];

    // STEP 4: Match Data Route & Middlewares and then
    // store them in global $c(onfig) variable,
    // then free up memory by unsetting variable
    $FPHP_MATCHED_DATA_ROUTE = d_match_developer_data_route(
        $c['req']['matched_method'],
        $c['req']['matched_route'],
        $c['ROUTES']['DATA']['COMPILED'],
        $c['ROUTES']['DATA']['SINGLES'],
        $c['ROUTES']['DATA']['MIDDLEWARES']
    );

    $c['req']['matched_middlewares'] = $FPHP_MATCHED_DATA_ROUTE['middlewares'];
    $c['req']['matched_middlewares_data'] = $FPHP_MATCHED_DATA_ROUTE['middlewares'];
    $c['req']['no_matched_in'] = $FPHP_MATCHED_DATA_ROUTE['no_match_in'];
    unset($FPHP_MATCHED_DATA_ROUTE);


    // This is the end of Step 4, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 5; // Set next step to 5 (Step 5)

}
$c['req']['current_step'] = $c['req']['next_step'];
