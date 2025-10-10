<?php return function (&$c, $passedValue = null) {
    if (
        $passedValue === null
        || !is_string($passedValue)
        || !in_array($passedValue, ['defensive', 'happy'])
    ) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_middlewares'][] = 'Passed Value to `funk_run_matched_route_middlewares` Pipeline Function must be either `defensive` or `happy` or `null` (default). No attempt to run any Matched Route Middlewares was made!';
        critical_err_json_or_html(500, 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Passed Value - Must be either `defensive` or `happy`!');
    }

    // 'defensive' = we check everything and output error to user if something gets wrong
    if ($passedValue === 'defensive') {
        if (isset($c['req']['matched_middlewares'])) {
            // Must be a numbered array
            if (!is_array($c['req']['matched_middlewares']) || !array_is_list($c['req']['matched_middlewares'])) {
                $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . '/' . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the `middlewares` Key is not a numbered array, please check the `funkphp/config/routes.php` File!';
                critical_err_json_or_html(500, 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - Should Be A Numbered Array!');
            }

            // Initialize loop, it will stop running when "false" is set to "keep_running_middlewares"
            $count = count($c['req']['matched_middlewares']);
            $mwDir = ROOT_FOLDER . '/middlewares/';
            $c['req']['keep_running_middlewares'] = true;

            // Main MWs Loop
            for ($i = 0; $i < $count; $i++) {
                if ($c['req']['keep_running_middlewares'] === false) {
                    break;
                }

                // Current Middleware must be an associative array!
                $mwToRun = "";
                $current_mw = $c['req']['matched_middlewares'][$i] ?? null;
                if (!is_array($current_mw) || array_is_list($current_mw) || empty($current_mw) || count($current_mw) !== 1) {
                    $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: ' . ($c['req']['route'] !== null ? $c['req']['method'] . '/' . $c['req']['route'] : '<No Route Matched>') . 'Route Matching. But one of the `middlewares` Key items is not an associative array with only one key (the Middleware Handler Name), please check the `funkphp/config/routes.php` File!';
                    critical_err_json_or_html(500, 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - Each Middleware must be an Associative Array with Only One key (the Middleware File Name)!');
                }

                // Prepare Middleware to Run and either run if it already exists
                // stored in the $c['dispatchers'] or include the file and run it!
                $mwToRun = array_key_first($current_mw);
                $c['req']['current_middleware'] = $mwToRun;
                $mwFileToRun = $mwDir . $mwToRun . '.php';
                if ( // if = run already loaded middleware from dispatchers
                    isset($c['dispatchers']['middlewares'][$mwToRun])
                    && is_callable($c['dispatchers']['middlewares'][$mwToRun])
                ) {
                    $RunMW = $c['dispatchers']['middlewares'][$mwToRun];
                    $RunMW($c, $current_mw);
                }  // else if = include the file from middlewares folder and add it to dispatchers
                elseif (is_readable($mwFileToRun)) {
                    $RunMW = include_once $mwFileToRun;
                    if (is_callable($RunMW)) {
                        $c['dispatchers']['middlewares'][$mwToRun] = $RunMW; // Store for possible reuse
                        $RunMW($c, $current_mw);
                    }
                    // ERROR: Middleware found in middlewares folder but it is not callable!
                    else {
                        $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . '/' . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the Middleware `' . $mwToRun . '` was found in the `funkphp/middlewares/` Folder but it is not a valid callable function closure, please check the `funkphp/middlewares/' . $mwToRun . '.php` File!';
                        critical_err_json_or_html(500, 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - A Middleware File was found in the `funkphp/middlewares/` Folder but it is Not A Valid Callable Function Closure!');
                    }
                }
                // ERROR: Middleware File Not Found in dispatchers OR in middlewares folder!
                else {
                    $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . '/' . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the Middleware `' . $mwToRun . '` was not found in the `funkphp/middlewares/` Folder or it was not properly loaded in the Config File `funkphp/config/_all.php` under the `dispatchers` Key!';
                    critical_err_json_or_html(500, 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - A Middleware File was not found in the `funkphp/middlewares/` Folder or it was not properly loaded in the Config File `funkphp/config/_all.php` under the `dispatchers` Key!');
                }

                // Here a Middleware was successfully ran (and also added to dispatchers if it was
                // included from file) so we add some stats to the request info and also reset things
                $c['req']['completed_middlewares#']++;
                $c['req']['deleted_middlewares'][] = $mwToRun;
                unset($c['req']['matched_middlewares'][$i]);
                $c['req']['deleted_middlewares#']++;
                $c['req']['current_middleware'] = null;
                $c['req']['next_middleware'] = isset($c['req']['matched_middlewares'][$i + 1]) && is_array($c['req']['matched_middlewares'][$i + 1]) ? array_key_first($c['req']['matched_middlewares'][$i + 1]) : null;
            }

            // After MWs Loop, we set so MW Pipeline cannot run again
            $c['req']['keep_running_middlewares'] = false;
            $c['req']['current_middleware'] = null;
            $c['req']['matched_middlewares'] = null;
        } else {
            $c['err']['MAYBE']['CONFIG'][] = 'No Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: ' . ($c['req']['route'] !== null ? $c['req']['method'] . '/' . $c['req']['route'] : '<No Route Matched>') . 'Route Matching. If you expected Middlewares to run after Route Matching, check for the Route in the `funkphp/config/routes.php` File!';
        }
    }
    // 'happy' = we assume everything is correct and just run the middlewares
    // TODO: Write the 'happy' version (should go much faster!)
    else if ($passedValue === 'happy') {
        // Assume $c['req']['matched_middlewares'] exists, is a numbered array, and is correctly structured.
        if (isset($c['req']['matched_middlewares'])) {
            $count = count($c['req']['matched_middlewares']);
            $mwDir = ROOT_FOLDER . '/middlewares/';
            $c['req']['keep_running_middlewares'] = true;
            // Main MWs Loop
            for ($i = 0; $i < $count; $i++) {
                // Short-circuit check is still necessary for middleware control
                // as middlewares can be interrupted by setting this flag to false.
                if ($c['req']['keep_running_middlewares'] === false) {
                    break;
                }
                $current_mw = $c['req']['matched_middlewares'][$i];
                $mwToRun = array_key_first($current_mw);
                // Set context (useful even in 'happy' mode)
                $c['req']['current_middleware'] = $mwToRun;
                // 1. Run already loaded middleware from dispatchers
                if (isset($c['dispatchers']['middlewares'][$mwToRun])) {
                    $RunMW = $c['dispatchers']['middlewares'][$mwToRun];
                    $RunMW($c, $current_mw);
                }
                // 2. Include file, store, and run (assume callable and readable)
                else {
                    // NOTE: We rely on PHP's internal errors (or production error handling)
                    // if the file is missing or if the included value is not callable.
                    $RunMW = include_once $mwDir . $mwToRun . '.php';
                    // We must still check if it's callable for safety before running and storing
                    // since a non-callable would cause a fatal error.
                    if (is_callable($RunMW)) {
                        $c['dispatchers']['middlewares'][$mwToRun] = $RunMW;
                        $RunMW($c, $current_mw);
                    }
                    // In a true 'happy' path, if it fails here, you might intentionally let PHP throw a fatal error
                    // to avoid the overhead of the detailed error logging/exit in the 'defensive' mode.
                    // For now, we'll continue with cleanup, assuming a developer error or misconfiguration.
                }
                // Cleanup and Stats - This is standard procedure, keep it.
                $c['req']['completed_middlewares#']++;
                $c['req']['deleted_middlewares'][] = $mwToRun;
                unset($c['req']['matched_middlewares'][$i]);
                $c['req']['deleted_middlewares#']++;
                $c['req']['current_middleware'] = null;
                // Use the safe oneliner you just created
                $c['req']['next_middleware'] = isset($c['req']['matched_middlewares'][$i + 1])
                    && is_array($c['req']['matched_middlewares'][$i + 1])
                    ? array_key_first($c['req']['matched_middlewares'][$i + 1])
                    : null;
            }
            // After MWs Loop, finalize state
            $c['req']['keep_running_middlewares'] = false;
            $c['req']['current_middleware'] = null;
            $c['req']['matched_middlewares'] = null;
        }
        // NOTE: The 'happy' path intentionally does not log an error if $c['req']['matched_middlewares'] is null.
        // It simply assumes 'no middlewares were intended to run' and finishes silently.
    }
};
