<?php // PAGE-related FUNCTIONS FOR FuncPHP

// Try match Single Page Route & Middleware routes! (start of step 5)
function p_match_developer_page_route(string $method, string $uri, array $compiledDataTrie, array $developerSingleDataRoutes, array $developerMiddlewareDataRoutes, string $handlerKey = "handler", string $mHandlerKey = "handler")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledDataTrie[$method])) {
        $routeDefinition = r_match_compiled_route($uri, $compiledDataTrie[$method]);
    } else {
        $noMatchIn = "COMPILED_ROUTE_KEY (" . mb_strtoupper($method) . ") & ";
    }

    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleDataRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleDataRoutes[$method][$routeDefinition["route"]];
            $matchedRouteHandler = $routeInfo[$handlerKey] ?? null;
            $noMatchIn = "BOTH_MATCHED_PAGE";

            // Add Any Matched Middlewares Handlers Defined By Developer
            // It loops through and only adds those that are non-empty strings
            // It does loop through arrays of non-empty strings! All values must
            // belong to the $mHandler key in the $developerMiddlewareRoutes array
            // or they will be ignored!
            if (
                isset($routeDefinition["middlewares"]) && !empty($routeDefinition["middlewares"] && is_array($routeDefinition["middlewares"]))
            ) {
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (isset($developerMiddlewareDataRoutes[$method][$middleware]) && isset($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey])) {
                        if (is_array($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey])) {
                            foreach ($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey] as $mHandler) {
                                if (is_string($mHandler) && !empty($mHandler)) {
                                    $matchedMiddlewareHandlers[] = $mHandler;
                                }
                            }
                        } elseif (is_string($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey]) && !empty($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey])) {
                            $matchedMiddlewareHandlers[] = $developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey];
                        } // If not array or non-empty string, skip
                    }
                }
            }
        } else {
            $noMatchIn .= "DEVELOPER_SINGLE_ROUTES_PAGE";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES_PAGE";
    }
    return [
        "route" => $matchedRoute,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "middlewares" => $matchedMiddlewareHandlers,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// Run the matched route handler (Step 5 after matched routing in Routes Page)
function p_run_matched_route_handler(&$c)
{
    // Grab Route Handler Path and check that it exists, is readable and callable and only then run it
    $handlerPath = dirname(dirname(__DIR__)) . '/handlers/P/' . ($c['req']['matched_handler_page'] ? $c['req']['matched_handler_page'] : "") . '.php';
    if (file_exists($handlerPath) && is_readable($handlerPath)) {
        $runHandler = include $handlerPath;
        if (is_callable($runHandler)) {
            $runHandler($c);
        }  // Handle error: not callable
        else {
            echo "[p_run_matched_route_handler]: Handler is not callable!";
        }
    } // Handle error: file not found or not readable
    else {
        echo "[p_run_matched_route_handler]: Handler file not found or not readable!";
    }
}

// Try run middlewares after matched data routing (end of step 5 but before rendering page)
// &$c is Global Config Variable with "everything"!
function p_run_middleware_after_matched_page_routing(&$c)
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
            $mwDir = dirname(dirname(__DIR__)) . '/middlewares/P/';
            $mwToRun = $mwDir . $current_mw . '.php';
            if (is_dir($mwDir) && file_exists($mwToRun)) {
                $RunMW = include $mwToRun;
                if (is_callable($RunMW)) {
                    $c['req']['current_middleware_running'] = $current_mw;
                    $c['req']['number_of_ran_middlewares']++;
                    $c['req']['next_middleware_to_run'] = $c['req']['matched_middlewares'][$i + 1] ?? null;
                    $RunMW($c);
                } // CUSTOM ERROR HANDLING HERE! - not callable
                else {
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file
            else {
            }

            // Remove middleware[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_middlewares'][] = $current_mw;
            $c['req']['deleted_middlewares_page'][] = $current_mw;
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
    // CUSTOM ERROR HANDLING HERE! - no matched middlewares
    else {
    }
}
