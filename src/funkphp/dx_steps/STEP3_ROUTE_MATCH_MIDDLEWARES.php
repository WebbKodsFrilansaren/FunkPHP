<?php // STEP 3: Match Single Route and its associated Middlewares

// This is the third step of the request, so we can run this step now!
if ($c['req']['current_step'] === 3) {
    // Load URI Routes since we are at this step and need them!
    // GOTO "funkphp/routes/route_single_routes.php" to Add Your Single Routes!
    // GOTO "funkphp/routes/route_middleware_routes.php" to Add Your middlewares!
    $c['ROUTES'] = [
        'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/troute_route.php',
        'SINGLES' => include dirname(__DIR__) . '/routes/route_single_routes.php',
    ];
    // BEFORE STEP 3: Do anything you want here before matching the route and middlewares!

    // STEP 3: Match Route & Middlewares and then
    // store them in global $c(onfig) variable,
    // then free up memory by unsetting variable
    $FPHP_MATCHED_ROUTE = r_match_developer_route(
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'],
        $c['ROUTES']['SINGLES']['ROUTES'],
        $c['ROUTES']['SINGLES']['ROUTES'],
    );

    $c['req']['matched_method'] = $c['req']['method'];
    $c['req']['matched_route'] = $FPHP_MATCHED_ROUTE['route'];
    $c['req']['matched_handler_route'] = $FPHP_MATCHED_ROUTE['handler'];
    $c['req']['matched_params'] = $FPHP_MATCHED_ROUTE['params'];
    $c['req']['matched_params_route'] = $FPHP_MATCHED_ROUTE['params'];
    $c['req']['matched_middlewares'] = $FPHP_MATCHED_ROUTE['middlewares'];
    $c['req']['matched_middlewares_route'] = $FPHP_MATCHED_ROUTE['middlewares'];
    $c['req']['no_matched_in'] = $FPHP_MATCHED_ROUTE['no_match_in'];
    unset($FPHP_MATCHED_ROUTE);

    // Run the matched route handler if it exists and is not empty.
    // Even if not null, file may not exist; the function checks that.
    if ($c['req']['matched_handler_route'] !== null) {
        r_run_matched_route_handler($c);
    }
    // matched_handler_route doesn't exist? What then or just move on?
    else {
    }

    // GOTO: "funkphp/middlewares/R/" and copy&paste the "_TEMPLATE.php" file to create your own middlewares!
    // OR use the FunkCLI "php funkcli add mw middleware_name route|METHOD/route_path"
    // You specificy "route|" first to indicate ofr what type of route you want to create a middleware for.
    // Check that middlewares array exists and is not empty in $c global variable
    // Then run each middleware in the order they are defined as long as keep_running_mws is true.
    // After each run, remove it from the array to avoid running it again.
    if ($c['req']['matched_middlewares'] !== null) {
        r_run_middleware_after_matched_routing($c);
    }
    // This is the end of Step 3, you can freely add any other checks you want here!

    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 4; // Set next step to 4 (Step 4)
}
// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];
