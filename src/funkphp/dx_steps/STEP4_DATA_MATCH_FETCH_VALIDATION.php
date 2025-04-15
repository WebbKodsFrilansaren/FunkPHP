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
    // (string) because PHP Intelesense doesn't understand the actual variables being passed!
    $FPHP_MATCHED_DATA_ROUTE = d_match_developer_data_route(
        (string)$c['req']['matched_method'],
        (string)$c['req']['matched_route'],
        $c['ROUTES']['DATA']['COMPILED'],
        $c['ROUTES']['DATA']['SINGLES'],
        $c['ROUTES']['DATA']['MIDDLEWARES']
    );

    // Check if middlewares still exist and if so
    // merge them with the new matched middlewares
    if (isset($c['req']['matched_middlewares']) && is_array($c['req']['matched_middlewares'])) {
        $c['req']['matched_middlewares'] = array_merge($c['req']['matched_middlewares'], $FPHP_MATCHED_DATA_ROUTE['middlewares']);
    } else {
        $c['req']['matched_middlewares'] = $FPHP_MATCHED_DATA_ROUTE['middlewares'];
    }
    $c['req']['matched_middlewares_data'] = $FPHP_MATCHED_DATA_ROUTE['middlewares'];

    // This one doesn't have any middleawres since we just grabbed all possible middlewares
    // for matched Data Route, so we can just set it to the new matched middlewares!
    $c['req']['matched_middlewares_data'] = $FPHP_MATCHED_DATA_ROUTE['middlewares'];
    $c['req']['no_matched_in'] = $FPHP_MATCHED_DATA_ROUTE['no_match_in'];
    $c['req']['matched_handler_data'] = $FPHP_MATCHED_DATA_ROUTE['handler'];
    $c['req']['matched_params_data'] = $FPHP_MATCHED_DATA_ROUTE['params'];
    unset($FPHP_MATCHED_DATA_ROUTE);

    var_dump($c['req']);

    // This is the end of Step 4, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 5; // Set next step to 5 (Step 5)

}
$c['req']['current_step'] = $c['req']['next_step'];
