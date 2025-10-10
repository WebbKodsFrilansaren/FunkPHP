<?php return function (&$c, $passedValue = null) {
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

            // Here a Middleware was successfully ran (and also added to dispatchers if it was included from file)
            // so we add some stats to the request info and also reset things
            $c['req']['completed_middlewares#']++;
            $c['req']['deleted_middlewares'][] = $mwToRun;
            unset($c['req']['matched_middlewares'][$i]);
            $c['req']['deleted_middlewares#']++;
            $c['req']['current_middleware'] = null;
            $c['req']['next_middleware'] = array_key_first($c['req']['matched_middlewares'][$i + 1]) ?? null;
        }

        // After MWs Loop, we set so MW Pipeline cannot run again
        $c['req']['keep_running_middlewares'] = false;
        $c['req']['current_middleware'] = null;
        $c['req']['matched_middlewares'] = null;
    } else {
        $c['err']['MAYBE']['CONFIG'][] = 'No Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: ' . ($c['req']['route'] !== null ? $c['req']['method'] . '/' . $c['req']['route'] : '<No Route Matched>') . 'Route Matching. If you expected Middlewares to run after Route Matching, check for the Route in the `funkphp/config/routes.php` File!';
    }
};
