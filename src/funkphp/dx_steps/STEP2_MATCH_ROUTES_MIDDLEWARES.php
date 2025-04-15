<?php // STEP 2: Match Single Route and its associated Middlewares

// This is the third step of the request, so we can run this step now!
if ($c['req']['current_step'] === 2) {
    // Load URI Routes since we are at this step and need them!
    // GOTO "funkphp/routes/route_single_routes.php" to Add Your Single Routes!
    // GOTO "funkphp/routes/route_middleware_routes.php" to Add Your middlewares!
    $c['ROUTES'] = [
        'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/troute.php',
        'SINGLES' => include dirname(__DIR__) . '/routes/route_single_routes.php',
        'MIDDLEWARES' => include dirname(__DIR__) . '/routes/route_middleware_routes.php',
    ];

    // STEP 2: Match Route & Middlewares and then
    // store them in global $c(onfig) variable,
    // then free up memory by unsetting variable
    $FPHP_MATCHED_ROUTE = r_match_developer_route(
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'],
        $c['ROUTES']['SINGLES'],
        $c['ROUTES']['MIDDLEWARES']
    );
    $c['req']['matched_route'] = $FPHP_MATCHED_ROUTE['route'];
    $c['req']['matched_params'] = $FPHP_MATCHED_ROUTE['params'];
    $c['req']['matched_middlewares'] = $FPHP_MATCHED_ROUTE['middlewares'];
    $c['req']['no_matched_in'] = $FPHP_MATCHED_ROUTE['no_match_in'];
    unset($FPHP_MATCHED_ROUTE);

    // This is the end of Step 2, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 3; // Set next step to 3 (Step 3)
}
// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];
