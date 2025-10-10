<?php // ROUTE-related FUNCTIONS FOR FunPHP

// Function that returns a stored value in $c['req']['current_passed_value']["pipeline" is default!]
function funk_current_value(&$c, $currentStoredPassedValueForDefaultPipelineOrOtherKey = "pipeline")
{
    return $c['req']['current_passed_value'][$currentStoredPassedValueForDefaultPipelineOrOtherKey] ?? null;
}
// Shorthand version of funk_current_value() that uses the default "pipeline" key
function funk_cv(&$c, $currentStoredPassedValueForDefaultPipelineOrOtherKey = "pipeline")
{
    return $c['req']['current_passed_value'][$currentStoredPassedValueForDefaultPipelineOrOtherKey] ?? null;
}
function funk_current_fn_value(&$c, $key, $fnName)
{
    // Store error in $c['err'] if no key or fnName is provided (null or empty strings or not strings at all!)
    if (!isset($key) || !is_string($key) || empty($key)) {
        $c['err']['MAYBE']['funk_current_fn_value'][] = 'No Key provided to get Current Function Value!';
        return null;
    }
    if (!isset($fnName) || !is_string($fnName) || empty($fnName)) {
        $c['err']['MAYBE']['funk_current_fn_value'][] = 'No Function Name provided to get Current Function Value!';
        return null;
    }
    return $c['req']['current_passed_values'][$key][$fnName] ?? null;
}

// Function to skip the post-request pipeline
function funk_skip_post_request(&$c)
{
    $c['req']['skip_post-request'] = true;
}

// `pipeline` is the list of functions to always run for each request (unless any
// of the functions terminates it early!) This is the main entry point for each request!
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_request(&$c, $passedValue = null)
{
    if (
        isset($c['<ENTRY>']['pipeline']['request'])
        && is_array($c['<ENTRY>']['pipeline']['request'])
        && count($c['<ENTRY>']['pipeline']['request']) > 0
    ) {
        $count = count($c['<ENTRY>']['pipeline']['request']);
        $pipeDir = ROOT_FOLDER . '/pipeline/request/';
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }

            // Must not be null and either a String or an Array Key with a Value!
            // We use $pipeValueExists so we also can pass "null" as a value!
            $fnToRun = "";
            $pipeValue = null;
            $current_pipe = $c['<ENTRY>']['pipeline']['request'][$i] ?? null;
            if (
                $current_pipe === null ||
                (!is_string($current_pipe) && !is_array($current_pipe))
            ) {
                unset($c['<ENTRY>']['pipeline']['request'][$i]);
                $c['req']['deleted_pipeline']++;
                $c['err']['PIPELINE']['REQUEST']['funk_run_pipeline_request'][] = 'Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be a String or An Associative Array Key with a Value! (Value can be null, but that is probably not useful in most cases)';
                continue;
            }
            // Extract Function Name from the Array Key or String and store the value
            // in $c['req']['pipeline'] so it can be accessed anywhere during the request
            elseif (is_array($current_pipe)) {
                $fnToRun = key($current_pipe);
                $pipeValue = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_values']['pipeline']['request'][$fnToRun] = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_value']['pipeline'] = $current_pipe[$fnToRun] ?? null;
            } // "else" means it is a String so it has no value to store/pass on!
            else {
                $fnToRun = $current_pipe;
            }
            // First check if function already exists in $c['dispatchers']['pipeline']['request'] array!
            // If it exists and is callable,
            if (isset($c['dispatchers']['pipeline']['request'][$fnToRun])) {
                if (is_callable($c['dispatchers']['pipeline']['request'][$fnToRun])) {
                    $runPipe = $c['dispatchers']['pipeline']['request'][$fnToRun];
                    $c['req']['current_pipeline'] = $current_pipe;
                    $c['req']['pipeline#']++;
                    $c['req']['next_pipeline'] = $c['<ENTRY>']['pipeline']['request'][$i + 1] ?? null;
                    $runPipe($c, $pipeValue);
                }
            } else {
                // Only run Pipeline Function if dir, file and callable, then
                // run it and increment the number of ran pipeline functions
                $pipeToRun = $pipeDir . $fnToRun . '.php';
                if (file_exists($pipeToRun)) {
                    $runPipe = include_once $pipeToRun;
                    if (is_callable($runPipe)) {
                        $c['req']['current_pipeline'] = $current_pipe;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['next_pipeline'] = $c['<ENTRY>']['pipeline']['request'][$i + 1] ?? null;
                        $c['dispatchers']['pipeline']['request'][$fnToRun] = $runPipe;
                        $runPipe($c, $pipeValue);
                    } else {
                        $c['err']['PIPELINE']['REQUEST']['funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        $c['req']['current_pipeline'] = null;
                    }
                } else {
                    $c['err']['PIPELINE']['REQUEST']['funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST in `funkphp/pipeline/request/` Directory!';
                    $c['req']['current_pipeline'] = null;
                }
            }

            // Remove pipeline[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_pipeline']['request'][] = $current_pipe;
            unset($c['<ENTRY>']['pipeline']['request'][$i]);
            unset($c['req']['current_passed_value']['pipeline']);
            $c['req']['deleted_pipeline#']++;
        }
        // Set default settings for the next pipeline run
        $c['req']['current_pipeline'] = null;
        if (
            isset($c['<ENTRY>']['pipeline']['request'])
            && is_array($c['<ENTRY>']['pipeline']['request'])
            && count($c['<ENTRY>']['pipeline']['request']) === 0
        ) {
            $c['<ENTRY>']['pipeline']['request'] = null;
        }
        $c['req']['keep_running_pipeline'] = false;
    }
    // CUSTOM ERROR HANDLING HERE! - no matched middlewares (or change below to whatever you like)
    // IMPORTANT: No matched middlewares could mean misconfigured routes or no middlewares at all!
    else {
        $c['err']['MAYBE']['PIPELINE']['REQUEST']['funk_run_pipeline_request'][] = 'No Configured Pipeline Request Functions (`"<ENTRY>" => "pipeline"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\']` Key in the Pipeline Configuration File `funkphp/pipeline/pipeline.php` File!';
    }
}

// The "happy" version of `pipeline` meaning no checks are made for a
// few bytecode instructions faster - maybe - so, use at own risk!!!
function funk_run_pipeline_request_happy(&$c, $passedValue = null) {}

// The "happy" version of `post-request` meaning no checks are made for a
// few bytecode instructions faster - maybe - so, use at own risk!!!
function funk_run_pipeline_post_request_happy(&$c, $passedValue = null) {}


// The "happy" version of `middlewares` meaning no checks are made for a
// few bytecode instructions faster - maybe - so, use at own risk!!!
function funk_run_matched_route_middleware_happy(&$c, $passedValue = null) {}

// The "happy" version of `route keys` meaning no checks are made for a
// few bytecode instructions faster - maybe - so, use at own risk!!!
function funk_run_matched_route_keys_happy(&$c, $passedValue = null) {}

// Run ANY matched Route Key Handler by providing a string
// which is the name of the current key inside of that key
function funk_run_matched_route_key(&$c, $key = null)
{
    // $key must be a non-empty string
    if (!isset($key) || !is_string($key) || empty($key)) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'No Route Key provided to run. Please provide a valid Route Key!';
        return;
    }
    // It must also exist in currently matched route
    if (!isset($c['req']['route_keys'][$key])) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` NOT found for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`. Please check your Route Keys in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
        return;
    }

    // We extract folder name, file name and function name based on whether
    // 'folder' => 'fileName' (here functionName becomes same as fileName) OR
    // 'folder' => ['fileName' => 'functionName']
    $matchKey = $c['req']['route_keys'][$key];
    $keyFolder = $key;
    $keyFile = '';
    $keyFn = '';
    if (is_string($matchKey)) {
        $keyFile = $matchKey;
        $keyFn = $matchKey;
    } elseif (is_array($matchKey)) {
        $keyFile = key($matchKey);
        $keyFn = $matchKey[$keyFile] ?? '';
    } else {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` must be a String or an Array with a Non-Empty String Value. No attempt to find a Route Key File was made!';
        return;
    }
    // We check whether a returned anonymous function
    // already exists in $c['dispatchers'][$key][$keyFile]
    // otherwise we add it and call it!
    if (isset($c['dispatchers'][$key][$keyFile])) {
        // Check if it is callable, and if i tis NOT callable,
        // we log an error since we ONLY store callables here!
        if (is_callable($c['dispatchers'][$key][$keyFile])) {
            return $c['dispatchers'][$key][$keyFile]($c, $keyFn);
        } else {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` File `' . $keyFile . '` is NOT a Callable Function. Please check your Route Key File in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
            return;
        }
    }
    // Not added yet so add if it exists and call it with the $keyFn!
    else {
        $pathToInclude = ROOT_FOLDER . '/' . $keyFolder . '/' . $keyFile . '.php';
        if (!is_readable($pathToInclude)) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` File `' . $keyFile . '` does NOT EXIST in `' . $keyFolder . '/` Directory! Please check your Route Key File in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
            return;
        }
        $c['dispatchers'][$key][$keyFile] = include_once $pathToInclude;
        return $c['dispatchers'][$key][$keyFile]($c, $keyFn);
    }
};

// Same as above but now it just iterates through all keys
function funk_run_matched_route_keys(&$c, $passedValue = null)
{
    foreach ($c['req']['route_keys'] as $key => $_) {
        // $key must be a non-empty string
        if (!is_string($key)) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'No Route Key provided to run. Please provide a valid Route Key!';
            return;
        }
        // It must also exist in currently matched route
        if (!isset($c['req']['route_keys'][$key])) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key `' . $key . '` NOT found for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`. Please check your Route Keys in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
            return;
        }

        // We extract folder name, file name and function name based on whether
        // 'folder' => 'fileName' (here functionName becomes same as fileName) OR
        // 'folder' => ['fileName' => 'functionName']
        $matchKey = $c['req']['route_keys'][$key];
        $keyFolder = $key;
        $keyFile = '';
        $keyFn = '';
        if (is_string($matchKey)) {
            $keyFile = $matchKey;
            $keyFn = $matchKey;
        } elseif (is_array($matchKey)) {
            $keyFile = key($matchKey);
            $keyFn = $matchKey[$keyFile] ?? '';
        } else {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key `' . $key . '` must be a String or an Array with a Non-Empty String Value. No attempt to find a Route Key File was made!';
            return;
        }
        // We check whether a returned anonymous function
        // already exists in $c['dispatchers'][$key][$keyFile]
        // otherwise we add it and call it!
        if (isset($c['dispatchers'][$key][$keyFile])) {
            // Check if it is callable, and if i tis NOT callable,
            // we log an error since we ONLY store callables here!
            if (is_callable($c['dispatchers'][$key][$keyFile])) {
                $c['dispatchers'][$key][$keyFile]($c, $keyFn);
            } else {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key `' . $key . '` File `' . $keyFile . '` is NOT a Callable Function. Please check your Route Key File in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
                return;
            }
        }
        // Not added yet so add if it exists and call it with the $keyFn!
        else {
            $pathToInclude = ROOT_FOLDER . '/' . $keyFolder . '/' . $keyFile . '.php';
            if (!is_readable($pathToInclude)) {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key `' . $key . '` File `' . $keyFile . '` does NOT EXIST in `' . $keyFolder . '/` Directory! Please check your Route Key File in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
                return;
            }
            $c['dispatchers'][$key][$keyFile] = include_once $pathToInclude;
            $c['dispatchers'][$key][$keyFile]($c, $keyFn);
        }
    }
};

// Try run middlewares AFTER handled request (and this can
// also be due to being exited prematurely by the application)
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_post_request(&$c, $passedValue = null)
{
    if ($c['req']['skip_post-request']) {
        $c['err']['MAYBE']['PIPELINE']['POST-REQUEST']['funk_run_pipeline_post_request'][] = 'Post-Request Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Request Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_request(&$c)` could have been ran for your HTTP(S) Request!';
        return;
    }
    if (
        isset($c['<ENTRY>']['pipeline']['post-request'])
        && is_array($c['<ENTRY>']['pipeline']['post-request'])
        && count($c['<ENTRY>']['pipeline']['post-request']) > 0
    ) {
        $count = count($c['<ENTRY>']['pipeline']['post-request']);
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }

            // Must not be null and either a String or an Array Key with a Value!
            // We use $pipeValueExists so we also can pass "null" as a value!
            $fnToRun = "";
            $pipeValue = null;
            $current_pipe = $c['<ENTRY>']['pipeline']['post-request'][$i] ?? null;
            if (
                $current_pipe === null ||
                (!is_string($current_pipe) && !is_array($current_pipe))
            ) {
                unset($c['<ENTRY>']['pipeline']['post-request'][$i]);
                $c['req']['deleted_pipeline#']++;
                $c['err']['PIPELINE']['POST-REQUEST']['funk_run_pipeline_post_request'][] = 'Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be a String or An Associative Array Key with a Value! (Value can be null, but that is probably not useful in most cases)';
                continue;
            }
            // Extract Function Name from the Array Key or String and store the value
            // in $c['req']['pipeline'] so it can be accessed anywhere during the request
            elseif (is_array($current_pipe)) {
                $fnToRun = key($current_pipe);
                $pipeValue = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_values']['pipeline']['post-request'][$fnToRun] = $current_pipe[$fnToRun] ?? null;
                $c['req']['current_passed_value']['pipeline'] = $current_pipe[$fnToRun] ?? null;
            } // "else" means it is a String so it has no value to store/pass on!
            else {
                $fnToRun = $current_pipe;
            }
            // First check if function already exists in $c['dispatchers']['pipeline']['request'] array!
            // If it exists and is callable,
            if (isset($c['dispatchers']['pipeline']['post-request'][$fnToRun])) {
                if (is_callable($c['dispatchers']['pipeline']['post-request'][$fnToRun])) {
                    $runPipe = $c['dispatchers']['pipeline']['post-request'][$fnToRun];
                    $c['req']['current_pipeline'] = $current_pipe;
                    $c['req']['completed_pipeline#']++;
                    $c['req']['next_pipeline'] = $c['<ENTRY>']['pipeline']['post-request'][$i + 1] ?? null;
                    $runPipe($c, $pipeValue);
                }
            } else {
                // Only run Pipeline Function if dir, file and callable, then
                // run it and increment the number of ran pipeline functions
                $pipeDir = ROOT_FOLDER . '/pipeline/post-request/';
                $pipeToRun = $pipeDir . $fnToRun . '.php';
                if (file_exists($pipeToRun)) {
                    $runPipe = include_once $pipeToRun;
                    if (is_callable($runPipe)) {
                        $c['req']['current_pipeline'] = $current_pipe;
                        $c['req']['completed_pipeline#']++;
                        $c['req']['next_pipeline'] = $c['<ENTRY>']['pipeline']['post-request'][$i + 1] ?? null;
                        $c['dispatchers']['pipeline']['post-request'][$fnToRun] = $runPipe;
                        $runPipe($c, $pipeValue);
                    } else {
                        $c['err']['PIPELINE']['POST-REQUEST']['funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                        $c['req']['current_pipeline'] = null;
                    }
                } else {
                    $c['err']['PIPELINE']['POST-REQUEST']['funk_run_pipeline_post_request'][] = 'Pipeline Post-Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST in `funkphp/pipeline/pipeline/post-request/` Directory!';
                    $c['req']['current_pipeline'] = null;
                }
            }

            // Remove pipeline[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_pipeline']['post-request'][] = $current_pipe;
            unset($c['<ENTRY>']['pipeline']['post-request'][$i]);
            unset($c['req']['current_passed_value']['pipeline']);
            $c['req']['deleted_pipeline#']++;
        }
        // Set default settings for the next pipeline run
        $c['req']['current_pipeline'] = null;
        if (
            isset($c['<ENTRY>']['pipeline']['post-request'])
            && is_array($c['<ENTRY>']['pipeline']['post-request'])
            && count($c['<ENTRY>']['pipeline']['post-request']) === 0
        ) {
            $c['<ENTRY>']['pipeline']['post-request'] = null;
        }
        $c['req']['keep_running_pipeline'] = false;
    }
    // CUSTOM ERROR HANDLING HERE! - no matched middlewares (or change below to whatever you like)
    // IMPORTANT: No matched middlewares could mean misconfigured routes or no middlewares at all!
    else {
        $c['err']['MAYBE']['PIPELINE']['POST-REQUEST']['funk_run_pipeline_post_request'][] = 'No Configured Pipeline Post-Request Functions (`"<ENTRY>" => "pipeline"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\']` Key in the Pipeline Configuration File `funkphp/pipeline/pipeline.php` File!';
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
function funk_abort_exit(&$c)
{
    // TODO:
    return;
}
// Abort the middlewares and stop running any further middlewares
function funk_abort_middlewares(&$c)
{
    // TODO:
    return;
}

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function funk_match_compiled_route(&$c, string $requestUri, array $methodRootNode): ?array
{
    // Prepare & and extract URI Segments and remove empty segments
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);

    // Prepare variables to store the current node,
    // matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = ['uri' => $uriSegments, 'route' => []]; // Start with empty string to make implode work correctly
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;

    // EDGE-CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
        }
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }

    // Iterate URI segments when more than 0
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        /// First try match "|" middleware node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
        }

        // Then try match literal route
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments['route'][] = $currentUriSegment;
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
                $matchedPathSegments['route'][] = ":" . $placeholderKey;
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
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
    }

    // Return matched route, params & middlewares
    // if all consumed segments matched
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments['route'])) {
            return ["route" => '/' . implode('/', $matchedPathSegments['route']), "segments" => $matchedPathSegments, "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
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
function funk_match_developer_route(&$c, string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes)
{
    // Prepare return values
    $matchedRoute = null;
    $matchedPathSegments = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ''; // Use as debug value
    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = funk_match_compiled_route($c, $uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = 'NO MATCH FOR COMPILED_ROUTE_KEY (' . mb_strtoupper($method) . ') & ';
        return false;
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
            $c['req']['route'] = $matchedRoute;
            $c['req']['segments'] = $matchedPathSegments;
            $c['req']['params'] = $matchedRouteParams;
            $c['req']['matched_in'] = $noMatchIn;
            // We remove 'middlewares' from the matched route since it will
            // be array merged with all middleware-matched URI segments!
            if (isset($routeInfo[0]['middlewares'])) {
                $routeInfo = array_splice($routeInfo, 1, null, true);
            }
            $c['req']['route_keys'] = [...$routeInfo ?? []];
            // Add Any Matched Middlewares
            if (
                isset($routeDefinition["middlewares"])
                && is_array($routeDefinition["middlewares"])
                && !empty($routeDefinition["middlewares"])
            ) {
                // Each 'middlewares' key is an numbered array so
                // we can use array_merge so always keep the order
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (
                        isset($developerSingleRoutes[$method][$middleware])
                        && isset($developerSingleRoutes[$method][$middleware][0]['middlewares'])
                    ) {
                        $matchedMiddlewareHandlers = array_merge($matchedMiddlewareHandlers, $developerSingleRoutes[$method][$middleware][0]['middlewares']);
                    }
                }
            }
            $c['req']['matched_middlewares'] = $matchedMiddlewareHandlers;
            return true;
        } else {
            $noMatchIn .= 'NO MATCH IN DEVELOPER_ROUTES(funkphp/config/routes.php)';
        }
    } else {
        $noMatchIn .= 'NO MATCH IN COMPILED_ROUTES(funkphp/_internals/compiled/troute_route.php)';
    }
    // Return all Keys in matched Route and then overwrite some keys that are "hardcoded"
    return false;
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
    if (!isset($c['req']['params']) || !is_array($c['req']['params'])) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters (from the Visitor) to Validate for Current Route!';
        return null;
    }
    $params = $c['req']['params'];

    // When all parameters are valid, return true
    return true;
}
// Same as above but only takes a single Dynamic Parameter Key
// and returns true/false whether it is valid or not. Returns null
// when incorrectly used or no matched parameters.
function funk_param_is(&$c, $param_key, $validation)
{
    if (!isset($c['req']['params']) || !is_array($c['req']['params'])) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters to Validate for Current Route!';
        return null;
    }
    $param = $c['req']['params'][$param_key] ?? null;
    if ($param === null) {
        $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameter with Key `' . $param_key . '` to Validate for Current Route!';
        return null;
    }

    // When provided parameter is valid, return true
    return true;
}

// Quick Validate a $c['params'][$param_key] is one of many types:
function funk_param_is_string(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a string, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_string($param) && !empty($param);
}
function funk_param_is_number(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a string, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_numeric($param);
}
function funk_param_is_integer(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_integer'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is an integer, return true
    $param = $c['req']['params'][$param_key] ?? null;
    return is_int($param) || intval($param) == $param;
}
function funk_param_is_float(&$c, $param_key)
{
    if (!isset($param_key)) {
        $c['err']['ROUTES']['funk_param_is_float'][] = 'No Parameter Key provided to Validate for Current Route!';
        return null;
    }
    // When provided parameter is a float, return true
    $param = $c['req']['params'][$param_key] ?? null;
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
    $param = $c['req']['params'][$param_key] ?? "";
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
