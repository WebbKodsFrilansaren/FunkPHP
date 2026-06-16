<?php

namespace funkphp\pipeline\request\pl_match_route_then_run_matched_middlewares_and_pipeline;

function pl_match_route_then_run_matched_middlewares_and_pipeline(&$c)
{

    /* TRY MATCH A VALID ROUTE OR ERROR OUT ! */
    $c['ROUTES'] = [];
    if (!is_readable(ROOT_CORE . '/pipeline_routes.php')) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/core/pipeline_routes.php` not found or is not readable!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    } elseif (!is_readable(ROOT_CORE . '/compiled_routes.php')) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/core/compiled_routes.php` not found or is not readable!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    } else {
        $c['ROUTES'] = [
            'COMPILED' => include_once ROOT_CORE . '/compiled_routes.php',
            'DEVELOPER' => include_once ROOT_CORE . '/pipeline_routes.php',
        ];
    }
    if (
        !isset($c['ROUTES'])
        || !is_array($c['ROUTES'])
        || empty($c['ROUTES'])
        || !isset($c['ROUTES']['COMPILED'])
        || !is_array($c['ROUTES']['COMPILED'])
        || empty($c['ROUTES']['COMPILED'])
    ) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/core/compiled_routes.php` seems empty, please check!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    if (
        !isset($c['ROUTES']['DEVELOPER'])
        || !is_array($c['ROUTES']['DEVELOPER'])
        || empty($c['ROUTES']['DEVELOPER'])
        || !isset($c['ROUTES']['DEVELOPER']['ROUTES'])
        || !is_array($c['ROUTES']['DEVELOPER']['ROUTES'])
        || empty($c['ROUTES']['DEVELOPER']['ROUTES'])
    ) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/core/pipeline_routes.php` seems empty, please check!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Try match route and if it fails, we check if we should
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c,
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'] ?? [],
        $c['ROUTES']['DEVELOPER']['ROUTES'] ?? [],
    );
    // Return JSON/Page when no match!
    if (!$FPHP_MATCHED_ROUTE) {
        http_response_code(404); // This can be changed throughout the function here below if needed
        // Check if 'accept' is json or html/page (only use callback if it is NOT json or html/page)
        $accept = $c['req']['accept'] ?? null;
        // Accept is JSON and it is configured
        if (str_contains($accept, 'json')) {
            header('Content-Type: application/json; charset=utf-8');
            $jsonData = ["status" => "404", "body" => "PAGE OR CONTENT NOT FOUND?! The requested Route was NOT FOUND on this Server."];
            try { // Assume it is valid JSON data if not a function
                echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                exit(); // Exit if json doesn't do it and let post-request run unless disabled before this pipeline function ran
            } catch (\JsonException $e) {
                $err = 'No Route Matched (JSON Encoding Error Thrown) - Tell The Developer: The Pipepline `pl_match_route` Function needs a default Configured JSON Response OR Page to return OR a Callback Function to run in the case of No Matched Route. For example: `11 => ["pl_match_route" => ["no_match" => ["json" => "null", "page" => "404", "callback" => "null"]]]`. If the `json` key is a string, it will look for a function called that and use its return value as the JSON Encoded. If the `json` key is an array, it will be JSON Encoded as is. The `page` key must be a valid path or the default internal 404 Page will be used if not found. ONLY use the `callback` key if you need more things to do before returning any kind of response. Its string value is the function it will look for and execute. After any of these keys are ran exit() will be ran and `post-request` will run unless disabled before this pipeline function ran.';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        }
        // Accept is HTML and it is configured
        else if (str_contains($accept, 'text/html')) {
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            $page = ROOT_PAGES_COMPILED . '/[errors]/404.php';
            if (!is_readable($page)) {
                $err = 'No Route Matched (configured "no_match => page => ..." Page NOT FOUND or NOT READABLE - if you wanna Use the Default Error Pages you must specify "/[errors]/{HttpErrorResponseCode}" - for example: `["page" => "/[errors]/404"]`) - Tell The Developer: The Pipepline `pl_match_route` Function needs a default Configured JSON Response OR Page to return OR a Callback Function to run in the case of No Matched Route. For example: `11 => ["pl_match_route" => ["no_match" => ["json" => "null", "page" => "404", "callback" => "null"]]]`. If the `json` key is a string, it will look for a function called that and use its return value as the JSON Encoded. If the `json` key is an array, it will be JSON Encoded as is. The `page` key must be a valid path or the default internal 404 Page will be used if not found. ONLY use the `callback` key if you need more things to do before returning any kind of response. Its string value is the function it will look for and execute. After any of these keys are ran exit() will be ran and `post-request` will run unless disabled before this pipeline function ran.';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            include_once $page;
            exit(); // Exit if page doesn't do it and let post-request run unless disabled before this pipeline function ran

        } // <Add more "else ifs" if you wanna support more Content-Types before the catch-all-callback below>
        // Expected at least callback out of the 3 keys
        else {
            $err = 'No Route Matched (no Matching Configured Key based on Accept Request Header provided in the `no_match` key. This is because only two keys are configured allowing for this special case!) - Tell The Developer: The Pipepline `pl_match_route` Function needs a default Configured JSON Response OR Page to return OR a Callback Function to run in the case of No Matched Route. For example: `11 => ["pl_match_route" => ["no_match" => ["json" => "null", "page" => "404", "callback" => "null"]]]`. If the `json` key is a string, it will look for a function called that and use its return value as the JSON Encoded. If the `json` key is an array, it will be JSON Encoded as is. The `page` key must be a valid path or the default internal 404 Page will be used if not found. ONLY use the `callback` key if you need more things to do before returning any kind of response. Its string value is the function it will look for and execute. After any of these keys are ran exit() will be ran and `post-request` will run unless disabled before this pipeline function ran.';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
    // When matched, data is stored in $c['req'] and it is up to the Developer to do whatever they want with it!
    // Recommended is to first use `pl_run_matched_route_middlewares` to run any matched middlewares and then
    // use the `pl_run_matched_route_keys` to run the matched Route Keys that has been stored after the match!

    /* RUN MATCHED MIDDLEWARES IF ANY */
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

    /* RUN MATCHED PIPELINE IF ANY */
    // Must be a non-empty numbered array
    if (
        !isset($c['req']['matched_pipeline'])
        || !is_array($c['req']['matched_pipeline'])
        || !array_is_list($c['req']['matched_pipeline'])
        || count($c['req']['matched_pipeline']) === 0
    ) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_pipeline'][] = 'Route Keys for the Matched Route must be a Numbered Array! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
        $err = 'Tell the Developer: The Route Keys for the Matched Route must be a Numbered Array! This can also happen when You ONLY have Middlewares but no other `Route Key`! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Main Loop - each value is `Routes=>FileName=>FunctionName`
    $routesDir = ROOT_ROUTES . '/';
    // New version where we only go for "FileName=>FunctionName
    foreach ($c['req']['matched_pipeline'] as $idx => $dirFileFn) {
        $file = key($dirFileFn) ?? null;
        $fn = $dirFileFn[$file ?? ''] ?? null;

        if ($file === null || $fn === null) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_pipeline'][] = '(1) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            $err = '(1) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $folderFile = $routesDir . $file . '.php';
        if (is_readable($folderFile)) {
            include_once $folderFile;
            $fileFnToRun = NAMESPACE_PIPELINE_ROUTES . $file . '\\' . $fn;
            if (is_callable($fileFnToRun)) {
                // If fnName is not found inside of file,
                // it will throw its own critical error!
                $rawRun = $fileFnToRun($c);
                continue;
            } // ERROR: File found but not function inside of it
            else {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_pipeline'][] = '(2) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                $err = '(2) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        } // ERROR: File not found or not readable so hard error
        else {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_pipeline'][] = '(3) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            $err = '(3) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
};
