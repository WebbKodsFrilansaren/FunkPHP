<?php

namespace funkphp\pipeline\request\pl_https_kernel_dispatch;

function pl_https_kernel_dispatch(&$c)
{
    try {
        // When FUNKPHP_USE_HTTPS is set to true, we redirect to HTTPS if the request is not already HTTPS
        if ($c['FUNKPHP_USE_HTTPS'] === true) {
            $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1))
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            if (!$isHttps) {
                $host = $_SERVER['HTTP_HOST'] ?? 'www.funkphp.com';
                $uri  = $_SERVER['REQUEST_URI'] ?? '/';
                // Reconstruct the URL completely dynamically
                header("Location: https://" . $host . $uri, true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        $err = 'Tell the Developer: The HTTPS Redirection in `pl_http_kernel_dispatch` Failed to Redirect to HTTPS despite being set to do it! Path: /src/funkphp/core/pipeline_request.php (or use FunkGUI)';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    try {
        // When FUNKPHP_USE_PREPARE_URI is set to true, we prepare the URI for route matching
        // also check if custom uri normalizer should be used instead or not!
        if (
            !isset($c['FUNKPHP_USE_PREPARE_URI'])
            || (!is_bool($c['FUNKPHP_USE_PREPARE_URI']))
        ) {
            $err = 'Tell the Developer: The Configuration Key `FUNKPHP_USE_PREPARE_URI` is NOT boolean! It should be `true` or `false`! Path: /src/funkphp/core/c.php (or use FunkGUI)';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        if (
            isset($c['FUNKPHP_CUSTOM_URI_NORMALIZER'])
            && (!is_string($c['FUNKPHP_CUSTOM_URI_NORMALIZER']))
        ) {
            $err = 'Tell the Developer: The Configuration Key `FUNKPHP_CUSTOM_URI_NORMALIZER` is NOT a String! Set it to `null` if not used! Path: /src/funkphp/core/c.php (or use FunkGUI)';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        if (($c['FUNKPHP_USE_PREPARE_URI'] === false)
            && (isset($c['FUNKPHP_USE_PREPARE_URI'])
                && is_string($c['FUNKPHP_CUSTOM_URI_NORMALIZER']))
        ) {
            $err = 'Tell the Developer: The Configuration Key `FUNKPHP_CUSTOM_URI_NORMALIZER` is SET but the Configuration Key `FUNKPHP_USE_PREPARE_URI` is false meaning the Custom URI Normalizer will not run?! Please set `FUNKPHP_USE_PREPARE_URI` to `true` or set `FUNKPHP_CUSTOM_URI_NORMALIZER` to `null` instead!  Path: /src/funkphp/core/c.php (or use FunkGUI)';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        if ($c['FUNKPHP_USE_PREPARE_URI'] === true) {
            if (isset($c['FUNKPHP_CUSTOM_URI_NORMALIZER'])) {
                if (!is_callable($c['FUNKPHP_CUSTOM_URI_NORMALIZER'])) {
                    $err = 'Tell the Developer: The Configuration Key `FUNKPHP_CUSTOM_URI_NORMALIZER` is SET but it is NOT a Callable Function to Prepare the Request URI (see "/src/funkphp/core/c.php" and/or "/src/funkphp/config/functions.php" or FunKGUI). Set it to `null` if not used!';
                    \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
                } else {
                    $c['FUNKPHP_CUSTOM_URI_NORMALIZER']($c);
                }
            } else {
                // 1. Grab raw URI from server environment
                $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
                // 2. Chop off query parameters and fragment injections instantly
                // Explode splits at '?' or '#' if a raw socket forged it
                $cleanPath = explode('?', $rawUri, 2)[0];
                $cleanPath = explode('#', $cleanPath, 2)[0];
                // 3. Resolve potential Subfolder installations (e.g., localhost/project/public/)
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $baseUrl = dirname($scriptName);
                if ($baseUrl !== '/' && str_starts_with($cleanPath, $baseUrl)) {
                    $cleanPath = substr($cleanPath, strlen($baseUrl));
                }
                // 4. Fallback safeguard: collapse duplicate slashes down to single slashes
                // Fixes Apache installations where merge_slashes isn't handling it
                $cleanPath = preg_replace('#/{2,#', '/', $cleanPath);
                // 5. Enforce clean boundary states: Strip trailing and leading slashes, then wrap in a root slash
                $cleanPath = trim($cleanPath, '/');
                // Result is guaranteed to be a uniform format: '/' or '/users' or '/blog/post/view'
                $c['req']['uri'] = ($cleanPath === '') ? '/' : '/' . $cleanPath;
            }
        }
        // When no FUNKPHP_USE_PREPARE_URI is set, we just use the raw REQUEST_URI as is
        else {
            $c['req']['uri'] = $_SERVER['REQUEST_URI'] ?? '/';
        }
    } catch (Exception $e) {
        $err = 'Tell the Developer: The Request URI Preparing in `pl_http_kernel_dispatch` Failed to Prepare the Request URI Before Matching Route!';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }

    // INSERT DYNAMIC BASE URL TRACKING
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $scriptName ?: $_SERVER['SCRIPT_NAME'] ?: '';
    $baseUrl = $baseUrl ? $baseUrl : dirname($scriptName);
    $c['req']['base_url_absolute'] = rtrim($protocol . $host . $baseUrl, '/');
    $c['req']['base_url_relative'] = ($baseUrl === '/') ? '' : $baseUrl;

    /* TRY MATCH A VALID ROUTE OR ERROR OUT ! */
    $c['ROUTES'] = [];
    if (!defined("ROOT_CORE")) {
        $err = 'TELL THE DEVELOPER: The Defined Constant `ROOT_CORE` in /src/funkphp/core/CONSTANTS.php is NOT DEFINED?! Please fix directly or via FunkGUI.';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    if (!is_readable(ROOT_CORE . '/pipeline_routes.php')) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/core/pipeline_routes.php` not found or is not readable!';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    } elseif (!is_readable(ROOT_CORE . '/compiled_routes.php')) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/core/compiled_routes.php` not found or is not readable!';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
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
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
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
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Try match route and if it fails, we check if we should
    $FPHP_MATCHED_ROUTE = \funk_match_developer_route(
        $c,
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED']['TRIE'] ?? [],
        $c['ROUTES']['DEVELOPER']['ROUTES'] ?? [],
    );
    // Return JSON/Page when no match!
    // When (no) matched, data is stored in $c['req'] and it is up to the Developer to do whatever they want with it!
    // We matched so we NOW store some server-provided metadata
    $c['req']['query']        = $_SERVER['QUERY_STRING'] ?? null;
    $c['req']['ua']           = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $c['req']['content_type'] = $_SERVER['CONTENT_TYPE'] ?? null;
    $c['req']['accept']       = $_SERVER['HTTP_ACCEPT'] ?? null;
    $c['req']['protocol']     = $_SERVER['SERVER_PROTOCOL'] ?? null;

    if (!$FPHP_MATCHED_ROUTE) {
        http_response_code(404); // This can be changed throughout the function here below if needed
        // Check if 'accept' is json or html/page (only use callback if it is NOT json or html/page)
        $accept = $c['req']['accept'] ?? null;
        if (!isset($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response'])) {
            $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND?! Please fix directly or via FunkGUI.';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        // Accept is JSON
        if (str_contains($accept, 'json')) {
            if (!isset($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['json'])) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["json"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND despite Accept Header being "json"?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            header('Content-Type: application/json; charset=utf-8');
            $jsonData = $c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['json'] ?? null;
            try { // Assume it is valid JSON data if not a function
                echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                exit(); // Exit if json doesn't do it and let post-request run unless disabled before this pipeline function ran
            } catch (\JsonException $e) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["json"]` Key in `/src/funkphp/core/pipeline_request.php` is MALFORMED or DOES NOT EXIST AT ALL despite Accept Header being "json"?! Please fix directly or via FunkGUI';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        }
        // Accept is text/html (a Page)
        else if (str_contains($accept, 'text/html')) {
            if (
                !isset($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['page'])
                || !is_string($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['page'])
                || empty(trim($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['page']))
            ) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["page"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND or IS NOT A VALID STRING despite Accept Header being "text/html"?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            if (!defined("ROOT_PAGES_COMPILED")) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["page"]` Key in `/src/funkphp/core/pipeline_request.php` that needs to use the constant `ROOT_PAGES_COMPILED`; the constant was NOT FOUND despite Accept Header being "text/html"?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            $page = ROOT_PAGES_COMPILED . $c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['page'] . '.php';
            if (!is_readable($page)) {
                $err = 'TELL THE DEVELOPER: The Page `' . $page . '` as defined in `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["page"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND (it should NOT end with .php, that is added on to automatically!) despite Accept Header being "text/html"?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            include_once $page;
            exit();
        }
        // Accept is text/plain
        else if (str_contains($accept, 'text/plain')) {
            if (
                !isset($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['text'])
                || !is_string($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['text'])
                || empty(trim($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['text']))
            ) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["text"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND or IS NOT STRING or IS EMPTY despite Accept Header being "text/plain"?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            header('Content-Type: text/plain; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            echo $c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['text'];
            exit();
        }
        // Accept is text/plain
        else if (str_contains($accept, 'xml')) {
            if (
                !isset($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['xml'])
                || !is_string($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['xml'])
                || empty(trim($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['xml']))
            ) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["xml"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND or IS NOT STRING or IS EMPTY despite Accept Header being "xml"?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            header('Content-Type: text/xml; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            echo $c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['xml'];
            exit();
        }
        // Final check: callback defined?
        else {
            if (
                !isset($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['callback'])
                || !is_string($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['callback'])
                || empty(trim($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['callback']))
                || !is_callable($c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['callback'])
            ) {
                $err = 'TELL THE DEVELOPER: The `$c["<ENTRY>"]["pipeline"]["<CONFIG_GLOBAL>"]["global_default_no_route_match_response"]["callback"]` Key in `/src/funkphp/core/pipeline_request.php` was NOT FOUND or IS NOT STRING or IS EMPTY or IS NOT CALLABLE?! Please fix directly or via FunkGUI.';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            } else {
                $c['<ENTRY>']['pipeline']['<CONFIG_GLOBAL>']['global_default_no_route_match_response']['callback']($c);
                exit();
            }
        }
    }

    /* RUN MATCHED MIDDLEWARES IF ANY */
    // 'defensive' = we check almost everything and output error to user if something gets wrong
    if (
        isset($c['req']['matched_middlewares'])
        && (isset($c['req']['matched_config']['route_run_middlewares_before_pipeline'])
            && $c['req']['matched_config']['route_run_middlewares_before_pipeline'] === true)
    ) {
        // Must be a numbered array
        if (!is_array($c['req']['matched_middlewares']) || !array_is_list($c['req']['matched_middlewares'])) {
            $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the `middlewares` Key is not a numbered array, please check the `funkphp/config/routes.php` File!';
            $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - Should Be A Numbered Array!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }

        // Initialize loop, it will stop running when "false" is set to "keep_running_middlewares"
        $count = count($c['req']['matched_middlewares']);
        if (!defined("ROOT_MIDDLEWARES")) {
            $err = 'TELL THE DEVELOPER: The Defined Constant `ROOT_MIDDLEWARES` in /src/funkphp/core/CONSTANTS.php is NOT DEFINED?! Please fix directly or via FunkGUI.';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        if (!defined("NAMESPACE_PIPELINE_MIDDLEWARES")) {
            $err = 'TELL THE DEVELOPER: The Defined Constant `NAMESPACE_PIPELINE_MIDDLEWARES` in /src/funkphp/core/CONSTANTS.php is NOT DEFINED?! Please fix directly or via FunkGUI.';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $mwDir = ROOT_MIDDLEWARES . '/';

        // Main MWs Loop
        for ($i = 0; $i < $count; $i++) {

            // Current Middleware must be an associative array!
            $mwToRun = "";
            $current_mw = $c['req']['matched_middlewares'][$i] ?? null;
            if (!is_string($current_mw)) {
                $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: ' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . 'Route Matching. But one of the `middlewares` Key items is NOT a String (the Middleware Handler Name). Please see `funkphp/core/pipeline_routes.php` File OR by using the FunkGUI!';
                $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - Each Middleware must be an Associative Array with Only One key (the Middleware File Name)!';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
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
                    \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
                }
            }
            // ERROR: Middleware File Not Found in dispatchers OR in middlewares folder!
            else {
                $c['err']['MIDDLEWARES'][] = 'Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Possibly Matched Route: `' . ($c['req']['route'] !== null ? $c['req']['method'] . $c['req']['route'] : '<No Route Matched>') . '` Route Matching. But the Middleware `' . $mwToRun . '` was not found in the `funkphp/middlewares/` Folder or it was not properly loaded in the Config File `funkphp/config/_all.php` under the `dispatchers` Key!';
                $err = 'Tell the Developer: The Middlewares Pipeline Function ran but WITHOUT a Valid Middleware Structure - A Middleware File was not found in the `funkphp/middlewares/` Folder or it was not properly loaded in the Config File `funkphp/config/_all.php` under the `dispatchers` Key!';
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }

            // Here a Middleware was successfully ran (and also added to dispatchers if it was
            // included from file) so we add some stats to the request info and also reset things
            unset($c['req']['matched_middlewares'][$i]);
            $c['req']['current_middleware'] = null;
            $c['req']['next_middleware'] = isset($c['req']['matched_middlewares'][$i + 1]) && is_array($c['req']['matched_middlewares'][$i + 1]) ? array_key_first($c['req']['matched_middlewares'][$i + 1]) : null;
        }

        // After MWs Loop, we set so MW Pipeline cannot run again
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
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Main Loop - each value is `Routes=>FileName=>FunctionName`
    if (!defined("ROOT_ROUTES")) {
        $err = 'TELL THE DEVELOPER: The Defined Constant `ROOT_ROUTES` in /src/funkphp/core/CONSTANTS.php is NOT DEFINED?! Please fix directly or via FunkGUI.';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    if (!defined("NAMESPACE_PIPELINE_ROUTES")) {
        $err = 'TELL THE DEVELOPER: The Defined Constant `NAMESPACE_PIPELINE_ROUTES` in /src/funkphp/core/CONSTANTS.php is NOT DEFINED?! Please fix directly or via FunkGUI.';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    $routesDir = ROOT_ROUTES . '/';
    // New version where we only go for "FileName=>FunctionName
    foreach ($c['req']['matched_pipeline'] as $idx => $dirFileFn) {
        $file = key($dirFileFn) ?? null;
        $fn = $dirFileFn[$file ?? ''] ?? null;

        if ($file === null || $fn === null) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_pipeline'][] = '(1) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            $err = '(1) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
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
                \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        } // ERROR: File not found or not readable so hard error
        else {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_pipeline'][] = '(3) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            $err = '(3) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
}
