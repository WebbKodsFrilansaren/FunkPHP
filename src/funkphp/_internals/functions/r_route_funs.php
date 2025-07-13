<?php // ROUTE-related FUNCTIONS FOR FunPHP

// `pipeline` is the list of functions to always run for each request (unless any
// of the functions terminates it early!) This is the main entry point for each request!
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline(&$c)
{
    if (
        isset($c['<ENTRY>']['pipeline'])
        && is_array($c['<ENTRY>']['pipeline'])
        && count($c['<ENTRY>']['pipeline']) > 0
    ) {
        $count = count($c['<ENTRY>']['pipeline']);
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }

            // Check that it is a string and not null
            $current_mw = $c['<ENTRY>']['pipeline'][$i] ?? null;
            if ($current_mw === null || !is_string($current_mw)) {
                unset($c['<ENTRY>']['pipeline'][$i]);
                $c['req']['number_of_deleted_pipeline']++;
                $c['err']['CONFIG'][] = 'Pipeline Function at index ' .  $i . ' is not a valid string or is null!';
                continue;
            }

            // Only run middleware if dir, file and callable,
            // then run it and increment the number of ran middlewares
            $mwDir = dirname(dirname(__DIR__)) . '/pipeline/';
            $mwToRun = $mwDir . $current_mw . '.php';
            if (file_exists($mwToRun)) {
                $RunMW = include $mwToRun;
                if (is_callable($RunMW)) {
                    $c['req']['current_pipeline_running'] = $current_mw;
                    $c['req']['number_of_ran_pipeline']++;
                    $c['req']['next_pipeline_to_run'] = $c['<ENTRY>']['pipeline'][$i + 1] ?? null;
                    $RunMW($c);
                } // CUSTOM ERROR HANDLING HERE! - not callable (or change below to whatever you like)
                else {
                    $c['err']['CONFIG'][] = 'Pipeline Function at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    $c['req']['current_pipeline_running'] = null;
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file (or change below to whatever you like)
            else {
                $c['err']['CONFIG'][] = 'Pipeline Function at index '  .  $i . ' does NOT EXIST in `funkphp/config/pipeline/` Directory!';
                $c['req']['current_pipeline_running'] = null;
            }

            // Remove pipeline[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_pipeline'][] = $current_mw;
            unset($c['<ENTRY>']['pipeline'][$i]);
            $c['req']['number_of_deleted_pipeline']++;
        }
        // Set default settings for the next pipeline run
        $c['req']['current_pipeline_running'] = null;
        if (
            isset($c['<ENTRY>']['pipeline'])
            && is_array($c['<ENTRY>']['pipeline'])
            && count($c['<ENTRY>']['pipeline']) === 0
        ) {
            $c['<ENTRY>']['pipeline'] = null;
        }
        $c['req']['keep_running_pipeline'] = false;
    }
    // CUSTOM ERROR HANDLING HERE! - no matched middlewares (or change below to whatever you like)
    // IMPORTANT: No matched middlewares could mean misconfigured routes or no middlewares at all!
    else {
        $c['err']['MAYBE']['CONFIG'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\']` Key in the Pipeline Configuration File `funkphp/config/pipeline.php` File!';
    }
}

// Try run middlewares (recommended to do after matched routing)
// &$c is Global Config Variable with "everything"!
function funk_run_matched_route_middleware(&$c)
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
                $c['err']['MIDDLEWARES'][] = 'Middleware at index ' .  $i . ' is NOT a Valid String or Is Null!';
                continue;
            }

            // Only run middleware if dir, file and callable,
            // then run it and increment the number of ran middlewares
            $mwDir = dirname(dirname(__DIR__)) . '/middlewares/';
            $mwToRun = $mwDir . $current_mw . '.php';
            if (file_exists($mwToRun)) {
                $RunMW = include $mwToRun;
                if (is_callable($RunMW)) {
                    $c['req']['current_middleware_running'] = $current_mw;
                    $c['req']['number_of_ran_middlewares']++;
                    $c['req']['next_middleware_to_run'] = $c['req']['matched_middlewares'][$i + 1] ?? null;
                    $RunMW($c);
                } // CUSTOM ERROR HANDLING HERE! - not callable (or change below to whatever you like)
                else {
                    $c['err']['MIDDLEWARES'][] = 'Middleware Function at index ' .   $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    $c['req']['current_middleware_running'] = null;
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file (or change below to whatever you like)
            else {
                $c['err']['MIDDLEWARES'][] = 'Middleware File at index ' .  $i . ' does NOT EXIST in `funkphp/middlewares/` Directory!';
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
        $c['err']['MAYBE']['MIDDLEWARES'][] = 'No matched Middlewares to run after Route Matching. If you expected some, check the `middlewares` Key in the `funkphp/config/routes.php` File for the Matched Route: `' . ($c['req']['matched_route'] ?? '<No Route Matched>') . '`!';
    }
}

// Try run middlewares AFTER handled request (and this can
// also be due to being exited prematurely by the application)
// &$c is Global Config Variable with "everything"!
function funk_run_exit(&$c)
{
    if (
        isset($c['<ENTRY>']['exit'])
        && is_array($c['<ENTRY>']['exit'])
        && count($c['<ENTRY>']['exit']) > 0
    ) {
        $count = count($c['<ENTRY>']['exit']);
        for ($i = 0; $i < $count; $i++) {

            // Check that it is a string and not null
            $current_mw = $c['<ENTRY>']['exit'][$i] ?? null;
            if ($current_mw === null || !is_string($current_mw)) {
                unset($c['<ENTRY>']['exit'][$i]);
                $c['req']['number_of_deleted_exit']++;
                $c['err']['<ENTRY>']['EXIT'][] = 'Exit Function at index ' .  $i . ' is not a valid string or is null!';
                continue;
            }

            // Only run middleware if dir, file and callable,
            // then run it and increment the number of ran middlewares
            $mwDir = dirname(dirname(__DIR__)) . '/exit/';
            $mwToRun = $mwDir . $current_mw . '.php';
            if (file_exists($mwToRun)) {
                $RunMW = include $mwToRun;
                if (is_callable($RunMW)) {
                    $c['req']['current_exit_running'] = $current_mw;
                    $c['req']['number_of_ran_exit']++;
                    $c['req']['next_exit_to_run'] = $c['<ENTRY>']['exit'][$i + 1] ?? null;
                    $RunMW($c);
                } // CUSTOM ERROR HANDLING HERE! - not callable (or change below to whatever you like)
                else {
                    $c['err']['<ENTRY>']['EXIT'][] = 'Exit Function at index ' .  $i . ' is not callable!';
                    $c['req']['current_exit_running'] = null;
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file (or change below to whatever you like)
            else {
                $c['err']['<ENTRY>']['EXIT'][] = 'Exit File at index '  .  $i . ' does not exist or is not a directory!';
                $c['req']['current_exit_running'] = null;
            }

            // Remove middleware[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_exit'][] = $current_mw;
            unset($c['<ENTRY>']['exit'][$i]);
            $c['req']['number_of_deleted_exit']++;
        }
        // Set default settings for the next middleware run
        $c['req']['current_exit_running'] = null;
        if (
            isset($c['<ENTRY>']['exit'])
            && is_array($c['<ENTRY>']['exit'])
            && count($c['<ENTRY>']['exit']) === 0
        ) {
            $c['<ENTRY>']['exit'] = null;
        }
    } else {
        $c['err']['MAYBE']['<ENTRY>']['EXIT'][] = 'No Configured Exit Functions (`"<ENTRY>"" => "exit"` Key) to run after Request Handling. If you expected some, check the `[\'<ENTRY>\'][\'exit\']` Key in the Pipeline Configuration `funkphp/config/pipeline.php` File!';
    }
}

// Exit the Pipeline and stop running any further pipeline functions
// This is useful when you want to stop the pipeline early
function funk_abort_pipeline(&$c)
{
    // TODO:
    return;
}
// Same as above but used for the exit functions instead of the pipeline
function funk_exit_pipeline(&$c)
{
    // TODO:
    return;
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
            return ["route" => '/' . implode('/', $matchedPathSegments), "segments" => $matchedPathSegments, "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
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
function funk_match_developer_route(string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $mHandlerKey = "middlewares")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ''; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = funk_match_compiled_route($uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = 'COMPILED_ROUTE_KEY (' . mb_strtoupper($method) . ') & ';
    }

    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedPathSegments = $routeDefinition["segments"] ?? [];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $noMatchIn = 'ROUTE_MATCHED_BOTH';

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
            $noMatchIn .= 'DEVELOPER_ROUTES(funkphp/config/routes.php)';
        }
    } else {
        $noMatchIn .= 'COMPILED_ROUTES(funkphp/_internals/compiled/troute_route.php)';
    }
    // Return all Keys in matched Route and then overwrite some keys that are "hardcoded"
    return [
        ...$routeInfo ?? [],
        'route' => $matchedRoute,
        'segments' => $matchedPathSegments,
        'params' => $matchedRouteParams,
        'middlewares' => $matchedMiddlewareHandlers,
        'in' => $noMatchIn,
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
    } else {
        $c['err']['ROUTES']['funk_run_matched_route_handler'][] = 'Route Handler must be a String or an Array. No attempt to find a Handler File was made!';
        return;
    }

    // Finally check if the file exists and is readable, and then include it
    // and run the handler function with the $c variable as argument
    if (file_exists($handlerPath . '/' . $handler . '.php') && is_readable($handlerPath . '/' . $handler . '.php')) {
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
            $c['err']['ROUTES']['funk_run_matched_route_handler'][] = 'Route Handler Function `' . $handleString . '` is not callable!';
            return;
        }
    }
    // Handle error: file not found or not readable  (or just use default below)
    else {
        $c['err']['ROUTES']['funk_run_matched_route_handler'][] = 'Route Handler File not found or not readable!';
        return;
    }
}

// Function that validates dynamic parameters in a given route
// It uses the $c['req']['matched_params'] array so you only
// provide an array of ['param_key' => 'validationLogic'] pairs.
// It returns null when incorrectly used and true/false whether
// all provided parameters are valid or not.
function funk_params_are(&$c, $args)
{
    if (!isset($args) || !is_array($args)) {
        $c['err']['ROUTES']['funk_param_are'][] = 'No Parameters provided (by the Developer) to Validate for Current Route!';
        return null;
    }
    if (!isset($c['req']['matched_params']) || !is_array($c['req']['matched_params'])) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters (from the Visitor) to Validate for Current Route!';
        return null;
    }
    $params = $c['req']['matched_params'];

    // When all parameters are valid, return true
    return true;
}
// Same as above but only takes a single Dynamic Parameter Key
// and returns true/false whether it is valid or not. Returns null
// when incorrectly used or no matched parameters.
function funk_param_is(&$c, $param_key, $validation)
{
    if (!isset($c['req']['matched_params']) || !is_array($c['req']['matched_params'])) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters to Validate for Current Route!';
        return null;
    }
    $param = $c['req']['matched_params'][$param_key] ?? null;
    if ($param === null) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameter with Key `' . $param_key . '` to Validate for Current Route!';
        return null;
    }

    // When provided parameter is valid, return true
    return true;
}

// Quick Validate a $c['matched_params'][$param_key] is one of many types:
function funk_param_is_string(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a string, return true
    $param = $c['req']['matched_params'][$param_key] ?? null;
    return is_string($param) && !empty($param);
}
function funk_param_is_number(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a string, return true
    $param = $c['req']['matched_params'][$param_key] ?? null;
    return is_numeric($param);
}
function funk_param_is_integer(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_integer'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is an integer, return true
    $param = $c['req']['matched_params'][$param_key] ?? null;
    return is_int($param) || intval($param) == $param;
}
function funk_param_is_float(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_float'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a float, return true
    $param = $c['req']['matched_params'][$param_key] ?? null;
    return is_float($param) || (is_numeric($param) && strpos($param, '.') !== false && floatval($param) == $param);
}
function funk_param_is_regex(&$c, $param_key, $regexStr)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_regex'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    if (!isset($regexStr) || !is_string($regexStr) || empty($regexStr)) {
        $c['err']['ROUTES']['funk_param_is_regex'][] = 'No Regex String provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter matches the regex, return true
    $param = $c['req']['matched_params'][$param_key] ?? "";
    return preg_match($regexStr, $param) === 1;
}



// Check if the request is from localhost or 127.0.0.1
function funk_is_localhost(): bool
{
    if (isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] === "localhost" || $_SERVER['REMOTE_ADDR'] === "127.0.0.1")) {
        return true;
    } else {
        return false;
    }
}
