<?php // ROUTE-related FUNCTIONS FOR FunPHP

// Redirect to HTTPS if the application is online (not localhost) and not secured yet
function r_https_redirect()
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
function r_match_denied_methods()
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
    $methods = include dirname(dirname(__DIR__)) . '../config/BLOCKED_METHODS.php';
    if ($methods === false) {
        return ["err" =>  "[r_match_denied_methods]: Failed to load compiled methods!"];
    }
    if (isset($methods[$method])) {
        return true;
    }
    return false;
}

// Try match against denied IPs globally
function r_match_denied_exact_ips()
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
function r_run_middleware_after_matched_routing(&$c)
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
                } // CUSTOM ERROR HANDLING HERE! - not callable
                else {
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file
            else {
            }

            // Remove middleware[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_middlewares'][] = $current_mw;
            $c['req']['deleted_middlewares_route'][] = $current_mw;
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

// Exit middleware_
function r_exit_middleware_running_early_matched_routing(&$c)
{
    $c['req']['keep_running_middlewares'] === false;
}

// Try match against denied UAs globally (slower version apparently)
function r_match_denied_uas_slow_test($ua)
{
    // Return null if $ua is invalid UA variable
    if ($ua === "" || $ua === null || !is_string($ua)) {
        return null;
    }
    $startTime = microtime(true);

    // Load compiled UAs from file
    $uas = include dirname(__DIR__) . '/compiled/uas.php';
    if ($uas === false) {
        return ["err" =>  "[r_match_denied_uas]: Failed to load compiled UAs!"];
    }

    // First we lowercase the $ua and prepare to store the positions of the "ible" and "bot" words
    $ua = mb_strtolower($ua);
    $uaArrayToCompareAgainst = [];
    $ibleArr = [];
    $ible1Word = [];
    $ible2Words = [];
    $ible3Words = [];
    $ible4Words = [];
    $ible5Words = [];
    $bot1WordLeft = [];
    $bot2WordsLeft = [];
    $bot3WordsLeft = [];
    $bot4WordsLeft = [];
    $bot5WordsLeft = [];
    $botArr = [];
    $iblePos = -1;
    $botPos = -1;

    // Iteriate through the $ua string and check for the "ible" and "bot" words
    $len = mb_strlen($ua);
    for ($i = 0; $i < $len; $i++) {
        $char = $ua[$i];

        // For "ible;"
        if ($iblePos === -1) {
            if ($char === 'i' && count($ibleArr) === 0) {
                $ibleArr[] = 'i';
            } elseif ($char === 'b' && count($ibleArr) === 1 && isset($ua[$i - 1]) && $ua[$i - 1] === 'i') {
                $ibleArr[] = 'b';
            } elseif ($char === 'l' && count($ibleArr) === 2 && isset($ua[$i - 1]) && $ua[$i - 1] === 'b' && isset($ua[$i - 2]) && $ua[$i - 2] === 'i') {
                $ibleArr[] = 'l';
            } elseif ($char === 'e' && count($ibleArr) === 3 && isset($ua[$i - 1]) && $ua[$i - 1] === 'l' && isset($ua[$i - 2]) && $ua[$i - 2] === 'b' && isset($ua[$i - 3]) && $ua[$i - 3] === 'i') {
                $ibleArr[] = 'e';
            } elseif ($char === ';' && count($ibleArr) === 4 && isset($ua[$i - 1]) && $ua[$i - 1] === 'e' && isset($ua[$i - 2]) && $ua[$i - 2] === 'l' && isset($ua[$i - 3]) && $ua[$i - 3] === 'b' && isset($ua[$i - 4]) && $ua[$i - 4] === 'i') {
                $iblePos = $i;
                $ibleArr = [];
            } else if ($char !== 'i') {
                $ibleArr = []; // Reset if the sequence breaks
            }
        }

        // For "bot"
        if ($botPos === -1) {
            if ($char === 'b' && count($botArr) === 0) {
                $botArr[] = 'b';
            } elseif ($char === 'o' && count($botArr) === 1 && isset($ua[$i - 1]) && $ua[$i - 1] === 'b') {
                $botArr[] = 'o';
            } elseif ($char === 't' && count($botArr) === 2 && isset($ua[$i - 1]) && $ua[$i - 1] === 'o' && isset($ua[$i - 2]) && $ua[$i - 2] === 'b') {
                $botPos = $i;
                $botArr = [];
            } else if ($char !== 'b') {
                $botArr = []; // Reset if the sequence breaks
            }
        }
    }

    // Now we check if we found positions for both "ible"
    // and "bot" and store them to use for next 2-3 loops!
    $iblePos = $iblePos !== -1 ? $iblePos : -1;
    $botPos = $botPos !== -1 ? $botPos : -1;

    // If both are -1 return false, meaning we found no starting point for the next loops!
    if ($iblePos === -1 && $botPos === -1) {
        return false;
    }

    // Check for  " " after ";" to increase iblePos by 1
    if ($iblePos !== -1 && isset($ua[$iblePos + 1])  && $ua[$iblePos + 1] === " ") {
        $iblePos += 1;
    }

    // LOOP 1: Starting at "iblePos" and adding one character
    // to all 5 arrays ($ible1Word, $ible2Words, etc.) with "ible"
    // So we just check if the next character is a space or a semicolon or /
    $currentWord = []; // Adds one character a time
    for ($k = $iblePos; $k < $len; $k++) {
        // If not reached the end or found ";" or "/"
        if (isset($ua[$k])) {
            $char = $ua[$k];
            if ($char === ";" || $char === "/" || $char == "(") {
                if (count($currentWord) > 0) {
                    // Stringify current array word and push to all not full arrays
                    $currentWordStr = implode("", $currentWord);
                    if (count($ible1Word) < 1) {
                        $ible1Word[] = $currentWordStr;
                    }
                    if (count($ible2Words) < 2) {
                        $ible2Words[] = $currentWordStr;
                    }
                    if (count($ible3Words) < 3) {
                        $ible3Words[] = $currentWordStr;
                    }
                    if (count($ible4Words) < 4) {
                        $ible4Words[] = $currentWordStr;
                    }
                    if (count($ible5Words) < 5) {
                        $ible5Words[] = $currentWordStr;
                    }
                }
                // Exit loop cause we found typical ending characters of AI UA
                break;
            }
            // We now found a space meaning we can add the current word to each array is not full yet
            // meaning checking the count of each $ible1Word array (should be count less < 1) and so on.
            elseif ($char === " ") {
                if (count($currentWord) > 0) {
                    // Stringify current array word and push to all not full arrays
                    $currentWordStr = implode("", $currentWord);
                    if (count($ible1Word) < 1) {
                        $ible1Word[] = $currentWordStr;
                    }
                    if (count($ible2Words) < 2) {
                        $ible2Words[] = $currentWordStr;
                    }
                    if (count($ible3Words) < 3) {
                        $ible3Words[] = $currentWordStr;
                    }
                    if (count($ible4Words) < 4) {
                        $ible4Words[] = $currentWordStr;
                    }
                    if (count($ible5Words) < 5) {
                        $ible5Words[] = $currentWordStr;
                    }
                    $currentWord = []; // Reset the current word array for next iteration
                }
            } // Just add the current character to the current word array
            else {
                $currentWord[] = $char; // Add the character to the current word
            }
        }
    }
    // Now we add to $uaArrayToCompareAgainst with the $ible1Word, $ible2Words, etc.
    if (count($ible1Word) > 0) {
        $uaArrayToCompareAgainst[] = $ible1Word[0];
    }
    if (count($ible2Words) > 0) {
        $uaArrayToCompareAgainst[] = count($ible2Words) > 1 ? implode(" ", $ible2Words) : $ible2Words[0];
    }
    if (count($ible3Words) > 0) {
        $uaArrayToCompareAgainst[] = count($ible3Words) > 1 ? implode(" ", $ible3Words) : $ible3Words[0];
    }
    if (count($ible4Words) > 0) {
        $uaArrayToCompareAgainst[] = count($ible4Words) > 1 ? implode(" ", $ible4Words) : $ible4Words[0];
    }
    if (count($ible5Words) > 0) {
        $uaArrayToCompareAgainst[] = count($ible5Words) > 1 ? implode(" ", $ible5Words) : $ible5Words[0];
    }

    // LOOP 2: Starting at "botPos" and extracting up to 5 words to the right
    $currentWordLeft = [];
    for ($l = $botPos; $l < $len; $l--) {
        // If not reached the end or found ";" or "/"
        if (isset($ua[$l])) {
            $char = $ua[$l];
            if ($char === ";" || $char === "/") {
                if (count($currentWordLeft) > 0) {
                    // Stringify current array word and push to all not full arrays
                    $currentWordStr = implode("", array_reverse($currentWordLeft));
                    if (count($bot1WordLeft) < 1) {
                        $bot1WordLeft[] = $currentWordStr;
                    }
                    if (count($bot2WordsLeft) < 2) {
                        $bot2WordsLeft[] = $currentWordStr;
                    }
                    if (count($bot3WordsLeft) < 3) {
                        $bot3WordsLeft[] = $currentWordStr;
                    }
                    if (count($bot4WordsLeft) < 4) {
                        $bot4WordsLeft[] = $currentWordStr;
                    }
                    if (count($bot5WordsLeft) < 5) {
                        $bot5WordsLeft[] = $currentWordStr;
                    }
                }
                // Exit loop cause we found typical ending characters of AI UA
                break;
            }
            // We now found a space meaning we can add the current word to each array is not full yet
            // meaning checking the count of each $bot1Word array (should be count less < 1) and so on.
            elseif ($char === " ") {
                if (count($currentWordLeft) > 0) {
                    // Stringify current array word and push to all not full arrays
                    $currentWordStr = implode("", array_reverse($currentWordLeft));
                    if (count($bot1WordLeft) < 1) {
                        $bot1WordLeft[] = $currentWordStr;
                    }
                    if (count($bot2WordsLeft) < 2) {
                        $bot2WordsLeft[] = $currentWordStr;
                    }
                    if (count($bot3WordsLeft) < 3) {
                        $bot3WordsLeft[] = $currentWordStr;
                    }
                    if (count($bot4WordsLeft) < 4) {
                        $bot4WordsLeft[] = $currentWordStr;
                    }
                    if (count($bot5WordsLeft) < 5) {
                        $bot5WordsLeft[] = $currentWordStr;
                    }
                    $currentWordLeft = []; // Reset the current word array for next iteration
                }
            } // Just add the current character to the current word array
            else {
                $currentWordLeft[] = $char; // Add the character to the current word
            }
        }
    }

    // Add Left-side bot words to the comparison array
    if (count($bot1WordLeft) > 0) $uaArrayToCompareAgainst[] = $bot1WordLeft[0];
    if (count($bot2WordsLeft) > 0) $uaArrayToCompareAgainst[] = count($bot2WordsLeft) > 1 ? implode(" ", $bot2WordsLeft) : $bot2WordsLeft[0];
    if (count($bot3WordsLeft) > 0) $uaArrayToCompareAgainst[] = count($bot3WordsLeft) > 1 ? implode(" ", $bot3WordsLeft) : $bot3WordsLeft[0];
    if (count($bot4WordsLeft) > 0) $uaArrayToCompareAgainst[] = count($bot4WordsLeft) > 1 ? implode(" ", $bot4WordsLeft) : $bot4WordsLeft[0];
    if (count($bot5WordsLeft) > 0) $uaArrayToCompareAgainst[] = count($bot5WordsLeft) > 1 ? implode(" ", $bot5WordsLeft) : $bot5WordsLeft[0];

    // Loop through $uaArrayToCompareAgainst and compare against hashed $uas array
    // True = match found, false = no match found
    foreach ($uaArrayToCompareAgainst as $uaWord) {
        if (isset($uas[$uaWord])) {
            return true;
        }
    }
    return false;
}

// Try match against denied UAs globally (str_contains version, faster)
function r_match_denied_uas_fast()
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
function r_match_denied_uas_fast_test($ua = null)
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
function r_prepare_uri($uri, $fphp_BASEURL_URI)
{
    $uri = str_starts_with($_SERVER['REQUEST_URI'], $fphp_BASEURL_URI) ? "/" . ltrim(substr(strtok($_SERVER['REQUEST_URI'], "?"), strlen($fphp_BASEURL_URI)), '/') : strtok($_SERVER['REQUEST_URI'], "?");

    if ($uri === "") {
        $uri = "/";
    }

    $uri = str_replace(["./", "../"], '', $uri);

    $uri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');

    if ((substr($uri, -1) == "/") && substr_count($uri, "/") > 1) {
        $uri = substr($uri, 0, -1);
    }
    return $uri;
}

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function r_match_compiled_route(string $requestUri, array $methodRootNode): ?array
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
function r_match_developer_route(string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $handlerKey = "handler", string $mHandlerKey = "handler")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = r_match_compiled_route($uri, $compiledRouteTrie[$method]);
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
            $noMatchIn = "BOTH_MATCHED_ROUTE";

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
            $noMatchIn .= "DEVELOPER_SINGLE_ROUTES_ROUTE";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES_ROUTE";
    }
    return [
        "route" => $matchedRoute,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "middlewares" => $matchedMiddlewareHandlers,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
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
