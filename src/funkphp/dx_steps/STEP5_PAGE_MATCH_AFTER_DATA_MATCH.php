<?php // STEP 5: Return a matched page after route and data matching!

// Only run this step if the current step is 5
if ($c['req']['current_step'] === 5) {
    // This is the last step of the request, so we can run this step now!

    // Load Page Routes since we are at this step and need them!
    // GOTO "funkphp/pages/page_single_routes.php" to Add Your Single Routes!
    // GOTO "funkphp/pages/page_middleware_routes.php" to Add Your middlewares!
    $c['ROUTES']['PAGE'] = [
        'COMPILED' => include dirname(__DIR__) . '/_internals/compiled/troute_page.php',
        'SINGLES' => include dirname(__DIR__) . '/pages/page_single_routes.php',
        'MIDDLEWARES' => include dirname(__DIR__) . '/pages/page_middleware_routes.php',
    ];
    // BEFORE STEP 5: Do anything you want here before matching the page route and middlewares!

    // STEP 5: Match Page Route & Middlewares and then
    // store them in global $c(onfig) variable,
    // then free up memory by unsetting variable
    // (string) because PHP Intelesense doesn't understand the actual variables being passed!
    $FPHP_MATCHED_PAGE_ROUTE = p_match_developer_page_route(
        (string)$c['req']['matched_method'],
        (string)$c['req']['matched_route'],
        $c['ROUTES']['PAGE']['COMPILED'],
        $c['ROUTES']['PAGE']['SINGLES']['ROUTES'],
        $c['ROUTES']['PAGE']['MIDDLEWARES']['MIDDLEWARES'],
    );

    // Check if middlewares still exist and if so
    // merge them with the new matched middlewares
    if (isset($c['req']['matched_middlewares']) && is_array($c['req']['matched_middlewares'])) {
        $c['req']['matched_middlewares'] = array_merge($c['req']['matched_middlewares'], $FPHP_MATCHED_PAGE_ROUTE['middlewares']);
    } else {
        $c['req']['matched_middlewares'] = $FPHP_MATCHED_PAGE_ROUTE['middlewares'];
    }
    $c['req']['matched_middlewares_page'] = $FPHP_MATCHED_PAGE_ROUTE['middlewares'];

    // This one doesn't have any middlewares since we just grabbed all possible middlewares
    // for matched Data Route, so we can just set it to the new matched middlewares!
    $c['req']['matched_middlewares_page'] = $FPHP_MATCHED_PAGE_ROUTE['middlewares'];
    $c['req']['no_matched_in'] = $FPHP_MATCHED_PAGE_ROUTE['no_match_in'];
    $c['req']['matched_handler_page'] = $FPHP_MATCHED_PAGE_ROUTE['handler'];
    $c['req']['matched_params_page'] = $FPHP_MATCHED_PAGE_ROUTE['params'];
    $c['req']['matched_route_page'] = $FPHP_MATCHED_PAGE_ROUTE['route'];
    unset($FPHP_MATCHED_PAGE_ROUTE);

    // Run the matched route handler if it exists and is not empty.
    // Even if not null, file may not exist; the function checks that.
    if ($c['req']['matched_handler_page'] !== null) {
        p_run_matched_route_handler($c);
    }
    // matched_handler_data doesn't exist? What then or just move on?
    else {
    }

    // GOTO: "funkphp/middlewares/P/" and copy&paste the "_TEMPLATE.php" file to create your own middlewares!
    // OR use the FunkCLI "php funkcli add mw middleware_name page|METHOD/route_path"
    // You specificy "page|" first to indicate ofr what type of route you want to create a middleware for.
    // Check that middlewares array exists and is not empty in $c global variable
    // Then run each middleware in the order they are defined as long as keep_running_mws is true.
    // After each run, remove it from the array to avoid running it again.
    if ($c['req']['matched_middlewares'] !== null) {
        p_run_middleware_after_matched_page_routing($c);
    }

    // TODO: Add matching page step. Add running middleware step. Add the Template Engine function too!

    // This is the end of Step 5, you can freely add any final code here!
    // You have all global (meta) data in $c variable, so you can use it as you please!

}
// This is essentially the end of the entire request process!
