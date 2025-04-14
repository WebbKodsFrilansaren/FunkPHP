<?php // STEP 3: Run Middlewares after matching the route

// Check that middlewares array exists and is not empty in $c global variable
// Then run each middleware in the order they are defined as long as keep_running_mws is true.
// After each run, remove it from the array to avoid running it again.
if ($c['req']['matched_middlewares'] !== null) {
    r_run_middleware_after_matched_routing($c);
}

//r_run_middleware_after_matched_routing($c);

var_dump($c['req']);
// This is the end of Step 3, you can freely add any other checks you want here!
// You have all global (meta) data in $c variable, so you can use it as you please!