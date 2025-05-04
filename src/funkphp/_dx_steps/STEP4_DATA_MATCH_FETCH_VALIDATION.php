<?php // STEP 4: Match, fetch, validate data from different sources

// Only run this step if the current step is 4
if ($c['req']['current_step'] === 4) {
    // This is the fourth(4) step of the request, below you can do
    // anything you want before running the matched data handler.

    // Run the matched data handler if it exists
    if ($c['req']['matched_data'] !== null) {
        funk_run_matched_data_handler($c);
    }
    // OPTIONAL Handling: Edit or just remove, doesn't matter!
    // matched_data doesn't exist? What then or just move on?
    else {
    }

    // matched_data failed to run? What then or just move on?
    if ($c['err']['FAILED_TO_RUN_DATA_HANDLER']) {
    }

    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 5; // Set next step to 5 (Step 5)

}
$c['req']['current_step'] = $c['req']['next_step'];
