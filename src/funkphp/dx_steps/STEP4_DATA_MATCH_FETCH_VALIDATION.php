<?php // STEP 4: Match, fetch, validate data from different sources

// Only run this step if the current step is 4
if ($c['req']['current_step'] === 4) {
    // This is the fifth step of the request, so we can run this step now!

    // TODO: Add logic to handle the "data" key from the matched routed request matched_route => 'data'

    // You have all global (meta) data in $c variable, so you can use it as you please!
    $c['req']['next_step'] = 5; // Set next step to 5 (Step 5)

}
$c['req']['current_step'] = $c['req']['next_step'];
