<?php // STEP 3: Run Middlewares after matching the route

// Only run this step if the current step is 3
if ($c['req']['current_step'] === 3) {
    // This is the fourth step of the request, so we can run this step now!

    // GOTO: "funkphp/middlewares/" and copy&paste the "_TEMPLATE.php" file to create your own middlewares!
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
$c['req']['current_step'] = $c['req']['next_step'];
