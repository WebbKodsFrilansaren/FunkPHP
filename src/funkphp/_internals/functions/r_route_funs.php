<?php // ROUTE-related FUNCTIONS FOR FunPHP

// Redirect to HTTPS if the application is online (not localhost) and not secured yet
function funk_https_redirect()
{
    try {
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== "localhost" &&  $_SERVER['SERVER_NAME'] !== "127.0.0.1") {
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
                global $c;
                // We check if the url ended in "/" and if so we remove it
                $onlineURL = $c['BASEURLS']['ONLINE'] ? rtrim($c['BASEURLS']['ONLINE'], "/") : $c['BASEURLS']['ONLINE'];
                header("Location: $onlineURL" . $_SERVER['REQUEST_URI'], true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        // Change this if you wanna redirect somewhere else or log the error!
        echo "[r_https_redirect-ERROR]: " . $e->getMessage();
    }
}

// Try match against denied methods globally (or when just invalid)
function funk_match_denied_methods()
{
    // Return null if $method is invalid method variable
    $method = $_SERVER['REQUEST_METHOD'] ?? null;
    if ($method === "" || $method === null || !is_string($method)) {
        return true;
    }
    $method = strtoupper($method);

    // Then check $method is a valid HTTP method
    if (!in_array($method, ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS", "HEAD"])) {
        return true; // Invalid HTTP method, so deny access
    }

    // Finally try load blocked methods to match against
    $methods = include dirname(dirname(__DIR__)) . '/config/BLOCKED_METHODS.php';
    if ($methods === false) {
        return ["err" =>  "[r_match_denied_methods]: Failed to load compiled methods!"];
    }
    if (isset($methods[$method])) {
        return true;
    }
    return false;
}

// Try match against denied IPs globally
function funk_match_denied_exact_ips()
{
    // Try parse IP and check if it is valid
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if ($ip === "" || $ip === null || !is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return true;
    }

    // Finally try load exact IPs to match against
    $ips_exact = include dirname(dirname(__DIR__)) . '/config/BLOCKED_IPS.php';
    if ($ips_exact === false) {
        return ["err" =>  "[r_match_denied_exact_ips]: Failed to load compiled IPs!"];
    }
    if (isset($ips_exact[$ip])) {
        return true;
    }
    return false;
}

// Try run middlewares after matched routing (step 2)
// &$c is Global Config Variable with "everything"!
function funk_run_middleware_after_matched_routing(&$c)
{
    if (isset($c['req']['matched_middlewares']) && is_array($c['req']['matched_middlewares']) && count($c['req']['matched_middlewares']) > 0) {
        $count = count($c['req']['matched_middlewares']);
        $c['req']['keep_running_middlewares'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_middlewares'] === false) {
                break;
            }

            // Check that it is a string and not null
            $current_mw = $c['req']['matched_middlewares'][$i] ?? null;
            if ($current_mw === null || !is_string($current_mw)) {

                unset($c['req']['matched_middlewares'][$i]);
                $c['req']['number_of_deleted_middlewares']++;
                continue;
            }

            // Only run middleware if dir, file and callable,
            // then run it and increment the number of ran middlewares
            $mwDir = dirname(dirname(__DIR__)) . '/middlewares/';
            $mwToRun = $mwDir . $current_mw . '.php';
            if (is_dir($mwDir) && file_exists($mwToRun)) {
                $RunMW = include $mwToRun;
                if (is_callable($RunMW)) {
                    $c['req']['current_middleware_running'] = $current_mw;
                    $c['req']['number_of_ran_middlewares']++;
                    $c['req']['next_middleware_to_run'] = $c['req']['matched_middlewares'][$i + 1] ?? null;
                    $RunMW($c);
                } // CUSTOM ERROR HANDLING HERE! - not callable (or change below to whatever you like)
                else {
                    $c['err']['FAILED_TO_RUN_MIDDLEWARE'] = true;
                    $c['req']['current_middleware_running'] = null;
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file (or change below to whatever you like)
            else {
                $c['err']['FAILED_TO_RUN_MIDDLEWARE'] = true;
                $c['req']['current_middleware_running'] = null;
            }

            // Remove middleware[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_middlewares'][] = $current_mw;
            unset($c['req']['matched_middlewares'][$i]);
            $c['req']['number_of_deleted_middlewares']++;
        }
        // Set default settings for the next middleware run
        $c['req']['current_middleware_running'] = null;
        if (
            isset($c['req']['matched_middlewares'])
            && is_array($c['req']['matched_middlewares'])
            && count($c['req']['matched_middlewares']) === 0
        ) {
            $c['req']['matched_middlewares'] = null;
        }
        $c['req']['keep_running_middlewares'] = false;
    }
    // CUSTOM ERROR HANDLING HERE! - no matched middlewares (or change below to whatever you like)
    // IMPORTANT: No matched middlewares could mean misconfigured routes or no middlewares at all!
    else {
    }
}

// Exit middleware_
function r_exit_middleware_running_early_matched_routing(&$c)
{
    $c['req']['keep_running_middlewares'] === false;
}

// Try match against denied UAs globally (str_contains version, faster)
function funk_match_denied_uas_fast()
{
    // Try parse UA and check if it is valid
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    if ($ua === "" || $ua === null || !is_string($ua)) {
        return true;
    }
    $ua = mb_strtolower($ua);

    // Finally try load blocked UAs to match against
    $uas = include dirname(dirname(__DIR__)) . '/config/BLOCKED_UAS.php';
    if ($uas === false) {
        return ["err" =>  "[r_match_denied_uas]: Failed to load list of blocked UAs!"];
    }
    foreach (array_keys($uas) as $deniedUa) {
        if (str_contains($ua, $deniedUa)) {
            return true;
        }
    }
    return false;
}

// Try match against denied UAs globally (str_contains version, faster - for testing purposes)
function funk_match_denied_uas_fast_test($ua = null)
{
    // Try parse UA and check if it is valid
    if ($ua === null) {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
    if ($ua === "" || $ua === null || !is_string($ua)) {
        return true;
    }
    $ua = mb_strtolower($ua);

    // Finally try load blocked UAs to match against
    $uas = include dirname(dirname(__DIR__)) . '/config/BLOCKED_UAS.php';
    if ($uas === false) {
        return ["err" =>  "[r_match_denied_uas]: Failed to load list of blocked UAs!"];
    }
    foreach (array_keys($uas) as $deniedUa) {
        if (str_contains($ua, $deniedUa)) {
            return true;
        }
    }
    return false;
}

// Prepare $req['uri'] for consistent use in the app CHANGE and/or UPDATE
// this function if you need to filter the REQUEST_URI in more ways!
function funk_prepare_uri($uri, $fphp_BASEURL_URI)
{
    $uri = str_starts_with($_SERVER['REQUEST_URI'], $fphp_BASEURL_URI) ? "/" . ltrim(substr(strtok($_SERVER['REQUEST_URI'], "?"), strlen($fphp_BASEURL_URI)), '/') : strtok($_SERVER['REQUEST_URI'], "?");

    if ($uri === "") {
        $uri = "/";
    }

    if ((substr($uri, -1) == "/") && substr_count($uri, "/") > 1) {
        $uri = substr($uri, 0, -1);
    }
    return $uri;
}

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function funk_match_compiled_route(string $requestUri, array $methodRootNode): ?array
{
    // Prepare & and extract URI Segments and remove empty segments
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);

    // Prepare variables to store the current node,
    // matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = [];
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;

    // EDGE-CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }

    // Iterate URI segments when more than 0
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        /// First try match "|" middleware node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }

        // Then try match literal route
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments[] = $currentUriSegment;
            $currentNode = $currentNode[$currentUriSegment];
            $segmentsConsumed++;
            continue;
        }

        // Or try match dynamic route ":" indicator node and
        // only store param and matched URI segment if not null
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']);

            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedParams[$placeholderKey] = $currentUriSegment;
                $matchedPathSegments[] = ":" . $placeholderKey;
                $currentNode = $currentNode[':'][$placeholderKey];
                $segmentsConsumed++;
                continue;
            }
        }

        // No matched "|", ":" or literal route in Compiled Routes!
        return null;
    }

    // EDGE-CASE: Add middleware at last node if it exists
    if (isset($currentNode['|'])) {
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
    }

    // Return matched route, params & middlewares
    // if all consumed segments matched
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments)) {
            return ["route" => '/' . implode('/', $matchedPathSegments), "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
        }
        // EDGE-CASE: 0 consumed segments,
        // return null instead of matched
        else {
            return null;
        }
    }
    // EDGE-CASES: Return null when impossible(?)/unexpected behavior
    else {
        return null;
    }
    return null;
}

// TRIE ROUTER STARTING POINT: Match Returned Matched Compiled Route With Developer's Defined Route
function funk_match_developer_route(string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $handlerKey = "handler", string $mHandlerKey = "middlewares")
{
    // Prepare return values
    $matchedData = null;
    $matchedPage = null;
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = funk_match_compiled_route($uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = "COMPILED_ROUTE_KEY (" . mb_strtoupper($method) . ") & ";
    }

    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $matchedRouteHandler = $routeInfo[$handlerKey] ?? null;
            $noMatchIn = "ROUTE_MATCHED_BOTH";
            $matchedData = $routeInfo["data"] ?? null;
            $matchedPage = $routeInfo["page"] ?? null;

            // Add Any Matched Middlewares Handlers Defined By Developer
            // It loops through and only adds those that are non-empty strings
            // It does loop through arrays of non-empty strings! All values must
            // belong to the $mHandler key in the $developerMiddlewareRoutes array
            // or they will be ignored!
            if (
                isset($routeDefinition["middlewares"]) && !empty($routeDefinition["middlewares"] && is_array($routeDefinition["middlewares"]))
            ) {
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (isset($developerMiddlewareRoutes[$method][$middleware]) && isset($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey])) {
                        if (is_array($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey])) {
                            foreach ($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey] as $mHandler) {
                                if (is_string($mHandler) && !empty($mHandler)) {
                                    $matchedMiddlewareHandlers[] = $mHandler;
                                }
                            }
                        } elseif (is_string($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey]) && !empty($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey])) {
                            $matchedMiddlewareHandlers[] = $developerMiddlewareRoutes[$method][$middleware][$mHandlerKey];
                        } // If not array or non-empty string, skip
                    }
                }
            }
        } else {
            $noMatchIn .= "DEVELOPER_ROUTES(route_single_routes.php)";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES(troute_route.php)";
    }
    return [
        "route" => $matchedRoute,
        'data' => $matchedData,
        'page' => $matchedPage,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "middlewares" => $matchedMiddlewareHandlers,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// Run the matched route handler (Step 3 after matched routing in Routes Route)
function funk_run_matched_route_handler(&$c)
{
    // Grab Route Handler Path and prepare whether it is a string
    // or array to match "handler" or ["handler" => "fn"]
    $handlerPath = dirname(dirname(__DIR__)) . '/handlers/';
    $handler = "";
    $handleString = null;
    if (is_string($c['req']['matched_handler'])) {
        $handler = $c['req']['matched_handler'];
    } elseif (is_array($c['req']['matched_handler'])) {
        $handler = key($c['req']['matched_handler']);
        $handleString = $c['req']['matched_handler'][$handler] ?? null;
    }

    // Finally check if the file exists and is readable, and then include it
    // and run the handler function with the $c variable as argument
    if (file_exists("$handlerPath/$handler.php") && is_readable("$handlerPath/$handler.php")) {
        $runHandler = include_once "$handlerPath/$handler.php";
        if (is_callable($runHandler)) {
            if (!is_null($handleString)) {
                $runHandler($c, $handleString);
            } else {
                $runHandler($c);
            }
        }
        // Handle error: not callable (or just use default below)
        else {
            $c['err']['FAILED_TO_RUN_ROUTE_HANDLER'] = true;
            return;
        }
    }
    // Handle error: file not found or not readable  (or just use default below)
    else {
        $c['err']['FAILED_TO_RUN_ROUTE_HANDLER'] = true;
        return;
    }
}

// Check if the request is from localhost or 127.0.0.1
function r_is_localhost(): bool
{
    if (isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] === "localhost" || $_SERVER['REMOTE_ADDR'] === "127.0.0.1")) {
        return true;
    } else {
        return false;
    }
}
