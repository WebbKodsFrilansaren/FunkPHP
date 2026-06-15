<?php

namespace funkphp\pipeline\request\pl_run_matched_route_middlewares;

function pl_run_matched_route_middlewares(&$c)
{
    // 'defensive' = we check almost everything and output error to user if something gets wrong
    if (isset($c['req']['matched_middlewares'])) {
        // Must be a numbered array
        if (!is_array($c['req']['matched_middlewares']) || !array_is_list($c['req']['matched_middlewares'])) {
            $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the `middlewares` Key is not a numbered array, please check the `funkphp/config/routes.php` File!';
            $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - Should Be A Numbered Array!';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }

        // Initialize loop, it will stop running when "false" is set to "keep_running_middlewares"
        $count = count($c['req']['matched_middlewares']);
        $mwDir = ROOT_MIDDLEWARES . '/';
        $c['req']['keep_running_middlewares'] = true;

        // Main MWs Loop
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_middlewares'] === false) {
                break;
            }

            // Current Middleware must be an associative array!
            $mwToRun = "";
            $current_mw = $c['req']['matched_middlewares'][$i] ?? null;
            if (!is_string($current_mw)) {
                $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: ' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . 'Route Matching. But one of the `middlewares` Key items is NOT a String (the Middleware Handler Name). Please see `funkphp/core/pipeline_routes.php` File OR by using the FunkGUI!';
                $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - Each Middleware must be an Associative Array with Only One key (the Middleware File Name)!';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }

            // Prepare Middleware to Run and either run if it already exists
            // stored in the $c['dispatchers'] or include the file and run it!
            $mwToRun = $current_mw;
            $c['req']['current_middleware'] = $mwToRun;
            $mwFileToRun = $mwDir . $mwToRun . '.php';
            if (is_readable($mwFileToRun)) {
                include_once $mwFileToRun;
                $mwFnToRun = NAMESPACE_PIPELINE_MIDDLEWARES . $mwToRun . '\\' . $mwToRun;
                if (is_callable($mwFnToRun)) {
                    $rawRun = $mwFnToRun($c);
                }
                // ERROR: Middleware found in middlewares folder but it is not callable!
                else {
                    $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the Middleware `' . $mwToRun . '` was found in the `funkphp/middlewares/` Folder but it is not a valid callable function closure, please check the `funkphp/middlewares/' . $mwToRun . '.php` File!';
                    $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - A Middleware File was found in the `funkphp/middlewares/` Folder but it is Not A Valid Callable Function Closure!';
                    funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
                }
            }
            // ERROR: Middleware File Not Found in dispatchers OR in middlewares folder!
            else {
                $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the Middleware `' . $mwToRun . '` was not found in the `funkphp/middlewares/` Folder or it was not properly loaded in the Config File `funkphp/config/_all.php` under the `dispatchers` Key!';
                $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - A Middleware File was not found in the `funkphp/middlewares/` Folder or it was not properly loaded in the Config File `funkphp/config/_all.php` under the `dispatchers` Key!';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }

            // Here a Middleware was successfully ran (and also added to dispatchers if it was
            // included from file) so we add some stats to the request info and also reset things
            unset($c['req']['matched_middlewares'][$i]);
            $c['req']['current_middleware'] = null;
            $c['req']['next_middleware'] = isset($c['req']['matched_middlewares'][$i + 1]) && is_array($c['req']['matched_middlewares'][$i + 1]) ? array_key_first($c['req']['matched_middlewares'][$i + 1]) : null;
        }

        // After MWs Loop, we set so MW Pipeline cannot run again
        $c['req']['keep_running_middlewares'] = false;
        $c['req']['current_middleware'] = null;
        $c['req']['matched_middlewares'] = null;
    } else {
        $c['err']['MAYBE']['CONFIG'][] = 'No Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: ' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . 'Route Matching. If you expected Middlewares to run after Route Matching, check for the Route in the `funkphp/config/routes.php` File!';
    }
};
