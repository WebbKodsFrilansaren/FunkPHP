<?php return function (&$c) {
    if ($c['req']['matched_handler'] !== null) {
        funk_run_matched_route_handler($c);
    } else {
        $c['err']['ROUTES'][] = "No Route Handler Matched or it Failed to Run!";
        $c['err']['MAYBE']['ROUTES'][] = "No Route Handler Matched. If you expected a Route to match, check your Routes file and ensure the Route exists and that a Handler File with a Handler Function has been added to it under the key `handler`. For example: `['handler' => 'r_handler_file' => 'r_handler_function']`.";
    }
};
