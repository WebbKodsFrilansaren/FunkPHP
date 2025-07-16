<?php return function (&$c) {
    if ($c['req']['matched_data'] !== null) {
        funk_run_matched_data_handler($c);
    } else {
        $c['err']['DATA'][] = "Route Handler Failed to Load or Run, so Data Handler will not be run.";
        $c['err']['MAYBE']['DATA'][] = "No Data Handler Matched. If you expected a Data Handler to match, check your Routes file and ensure the Route exists and that a Data Handler File with a Data Handler Function has been added to it under the key `data`. For example: `['data' => 'd_data_file' => 'd_data_function']`.";
    }
};
