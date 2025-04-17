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

    // This is the end of Step 5, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
}
// This is essentially the end of the entire request process!
