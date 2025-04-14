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

// Try match against denied UAs globally (slower version apparently)
function r_match_denied_uas_slow($ua)
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
function r_match_developer_route(string $method, string $uri, array $compiledTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $handlerKey = "handler", string $mHandlerKey = "handler")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledTrie[$method])) {
        $routeDefinition = r_match_compiled_route($uri, $compiledTrie[$method]);
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
            $noMatchIn = "BOTH_MATCHED";

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
            $noMatchIn .= "DEVELOPER_SINGLE_ROUTES";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES";
    }
    return [
        "route" => $matchedRoute,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "middlewares" => $matchedMiddlewareHandlers,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// Convert PHP array() syntax to simplified [] syntax
function r_convert_array_to_simple_syntax(array $array): string | null | array
{
    // Must be non-empty array
    if (!is_array($array)) {
        return ["err" => "[r_convert_array_to_simple_syntax]: Must be a non-empty array!"];
    }

    // Check if the array is empty
    if (empty($array)) {
        ["err" => "[r_convert_array_to_simple_syntax]: Must be a non-empty array!"];
    }

    // Prepare array and parse state variables
    $str = mb_str_split(var_export($array, true));
    $arrStack = [];
    $arrayLetters = ["a", "r", "r", "a", "y", " "];
    $quotes = ["'", '"'];
    $inStr = false;
    $converted = "";

    // Check if first character is "a"
    if ($str[0] !== "a") {
        return ["err" => "[r_convert_array_to_simple_syntax]: Invalid var_export array syntax! Expected 'array ('"];
    }


    // Parse on each character of the prepared string
    for ($i = 0; $i < count($str); $i++) {
        $c = $str[$i];

        // If inside string and is not a quote
        if ($inStr && (!in_array($c, $quotes) && $c !== "\\")) {
            $converted .= $c;
            continue;
        }
        // If inside string with escaped character, just skip it
        elseif ($inStr && ($c === "\\")) {
            $i++;
            continue;
        }
        // If inside string and is a quote
        elseif ($inStr && (in_array($c, $quotes))) {
            $converted .= $c;
            $inStr = false;
            continue;
        }

        // If not inside string and is a quote
        if (!$inStr && empty($arrStack) && (in_array($c, $quotes))) {
            $inStr = true;
            $converted .= $c;
            continue;
        }

        // If not inside string and next character is "a" from "array (" & not from false boolean
        if (!$inStr && empty($arrStack)  && $c === "a" && $str[$i + 1] !== "l") {
            $arrStack[] = $c;
            continue;
        }

        // If not inside string and next character is one from:"rray ("
        if (!$inStr && !empty($arrStack)) {
            if (count($arrStack) < 5 && in_array($c, $arrayLetters)) {
                $arrStack[] = $c;
                continue;

                // If not inside string and next character is "(" from "array ("
            } elseif (count($arrStack) === 5 && $c === "(") {
                $converted .= "[";
                unset($arrStack);
                continue;
            }
        }

        // If outside string and ")"
        if (!$inStr && $c === ")") {
            $converted .= "]";
            continue;
        }
        $converted .= $c;
    }

    // Return the finalized string varaible
    $converted .= ";";
    return $converted;
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

// Build Compiled Route from Developer's Defined Routes
function r_build_compiled_routes(array $developerSingleRoutes, array $developerMiddlewareRoutes)
{
    // Only localhost can run this function (meaning you cannot run this in production!)
    if (!r_is_localhost()) {
        ["err" => "[r_build_compiled_route]: This function can only be run locally!"];
    }
    // Both arrays must be non-empty arrays
    if (!is_array($developerSingleRoutes)) {
        return ["err" => "[r_build_compiled_route]: '\$developerSingleRoutes' Must be a non-empty array!"];
    } elseif (!is_array($developerMiddlewareRoutes)) {
        return ["err" => "[r_build_compiled_route]: '\$developerMiddlewareRoutes' Must be a non-empty array!"];
    }
    if (empty($developerSingleRoutes)) {
        ["err" => "[r_build_compiled_route]: '\$developerSingleRoutes' Must be a non-empty array!"];
    } else if (empty($developerMiddlewareRoutes)) {
        ["err" => "[r_build_compiled_route]: Must '\$developerMiddlewareRoutes' be a non-empty array!"];
    }

    // Prepare compiled route array to return and other variables
    $compiledTrie = [];
    $GETSingles = $developerSingleRoutes["GET"] ?? null;
    $POSTSingles = $developerSingleRoutes["POST"] ?? null;
    $PUTSingles = $developerSingleRoutes["PUT"] ?? null;
    $DELETESingles = $developerSingleRoutes["DELETE"] ?? null;

    // Using method below, iterate through each HttpMethod and then add it to the $compiledTrie array
    function addMethodRoutes($singleRoutes)
    {
        // Begin with just getting the key names and no other nested values inside of them:
        // For example:  '/users' => ['handler' => 'USERS_PAGE', /*...*/], only gets the '/users' key name
        // and not the value inside of it. This is done by using array_keys() to get the keys of the array.
        $keys = array_keys($singleRoutes) ?? [];
        $compiledTrie = [];

        // Iterate through each key in the array and add it to the $compiledTrie array
        foreach ($keys as $key) {

            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/") {
                $compiledTrie["/"] = [];
                continue;
            }

            // Split the route into segments
            $splitRouteSegments = explode("/", trim($key, "/"));

            // Initialize the current node in the trie
            $currentNode = &$compiledTrie;

            // Iterate through each segment of the route
            foreach ($splitRouteSegments as $segment) {
                // WHEN DYNAMIC PARAMETER ROUTE SEGMENT
                if (str_starts_with($segment, ":")) {
                    // Create when not exist
                    if (!isset($currentNode[':'])) {
                        $currentNode[':'] = [];
                    }
                    // And insert param as next nested key and/or move to next node
                    $paramName = substr($segment, 1);
                    if (!isset($currentNode[':'][$paramName])) {
                        $currentNode[':'][$paramName] = [];
                    }
                    $currentNode = &$currentNode[':'][$paramName];
                }
                // WHEN LITERAL ROUTE SEGMENT
                else {
                    // Insert if not exist and/or move to next node
                    if (!isset($currentNode[$segment])) {
                        $currentNode[$segment] = [];
                    }
                    $currentNode = &$currentNode[$segment];
                }
            }
        }
        // Return the compiled trie for the method
        return $compiledTrie;
    }

    // Add the middleware routes to the compiled trie
    function addMiddlewareRoutes($middlewareRoutes, &$compiledTrie)
    {
        // Only extract the keys from the middleware routes
        $keys = array_keys($middlewareRoutes) ?? [];

        // The way we insert "|" to signify a middleware is to just go through all segments for each key
        // and when we are at the last segment that is the node we insert "|" and then we move on to key.
        foreach ($keys as $key) {
            // Ignore empty keys or null values & handle special case for "/"
            if ($key === "" || $key === null || $key === false || $key === "") {
                continue;
            }
            if ($key === "/") {
                $compiledTrie["|"] = [];
                continue;
            }

            // Now split key into segments and iterate through each segment
            $splitRouteSegments = explode("/", trim($key, "/"));

            // Now we just navigate to the last segment and add the middleware node "|".
            // We just check what it is and then just navigate,
            $currentNode = &$compiledTrie;

            // So we just check one of three things: is there a literal route to navigate to?
            // is there a dynamic route to navigate to? or is it a middleware node? WE JUST NAVIGATE TO IT
            // until we run out of segments, that means we have reached the node where we insert the middleware node "|".
            foreach ($splitRouteSegments as $segment) {
                // SPECIAL CASE: Navigate past any middleware node "|" but not at root node!
                if (isset($currentNode['|']) && !empty($currentNode['|'])) {
                    $currentNode = &$currentNode['|'];
                }

                // LITERAL ROUTE SEGMENT
                if (isset($currentNode[$segment])) {
                    $currentNode = &$currentNode[$segment];
                    continue;
                }

                // DYNAMIC ROUTE SEGMENT
                elseif (str_starts_with($segment, ":")) {
                    $paramName = substr($segment, 1);
                    $currentNode = &$currentNode[':'][$paramName];
                    continue;
                }
            }

            // Now we are at the last segment, we just add the middleware node "|"
            // and then we add the middleware route to it.
            if (!isset($currentNode['|'])) {
                $currentNode['|'] = [];
            }
        }
    }

    // First add the single routes to the compiled trie
    $compiledTrie['GET'] = addMethodRoutes($GETSingles);
    $compiledTrie['POST'] = addMethodRoutes($POSTSingles);
    $compiledTrie['PUT'] = addMethodRoutes($PUTSingles);
    $compiledTrie['DELETE'] = addMethodRoutes($DELETESingles);

    // Then add the middlewares to the compiled trie and return it
    addMiddlewareRoutes($developerMiddlewareRoutes["GET"] ?? [], $compiledTrie['GET']);
    addMiddlewareRoutes($developerMiddlewareRoutes["POST"] ?? [], $compiledTrie['POST']);
    addMiddlewareRoutes($developerMiddlewareRoutes["PUT"] ?? [], $compiledTrie['PUT']);
    addMiddlewareRoutes($developerMiddlewareRoutes["DELETE"] ?? [], $compiledTrie['DELETE']);

    return $compiledTrie;
}

// Output Compiled Route to File or Return as String
function r_output_compiled_routes(array $compiledTrie, string $outputFileNameFolderIsAlways_compiled_routes = "null")
{
    // Only localhost can run this function (meaning you cannot run this in production!)
    if (!r_is_localhost()) {
        ["err" => "[r_output_compiled_routes]: This function can only be run locally!"];
    }
    // Check if the compiled route is empty
    if (!is_array($compiledTrie)) {
        return ["err" => "[r_output_compiled_routes]: Compiled Routes Must Be A Non-Empty Array!"];
    }
    if (empty($compiledTrie)) {
        return ["err" => "[r_output_compiled_routes]: Compiled Routes Must Be A Non-Empty Array!"];
    }

    // TODO: Add the audit function check here!

    // Output either to file destiation or in current folder as datetime in file name
    $datetime = date("Y-m-d_H-i-s");
    $outputDestination = $outputFileNameFolderIsAlways_compiled_routes === "null" ? dirname(__DIR__) . "/compiled_routes/troute_" . $datetime . ".php" : dirname(__DIR__) . "\/compiled_routes\/" . $outputFileNameFolderIsAlways_compiled_routes . ".php";

    // Check if file already exists
    if (file_exists($outputDestination)) {
        echo "FILE EXISTS. THIS OVERWRITES THE FILE!<br>";
    }


    $result = null;
    if ($outputFileNameFolderIsAlways_compiled_routes !== "null") {
        $result = file_put_contents(dirname(__DIR__) . "/compiled/" . $outputFileNameFolderIsAlways_compiled_routes . ".php", "<?php\nreturn " . r_convert_array_to_simple_syntax($compiledTrie));
    } else {
        $result = file_put_contents($outputDestination, "<?php\nreturn " . r_convert_array_to_simple_syntax($compiledTrie));
    }
    if ($result === false) {
        echo "[r_output_compiled_routes-ERROR]: Compiled routes was NOT written to: $outputDestination\n<br>";
    } else {
        echo "[r_output_compiled_routes-SUCCESS: Compiled routes written to: $outputDestination\n<br>";
    }
}

// Audit Developer's Defined Routes
function r_audit_developer_routes(array $developerSingleRoutes, array $developerMiddlewareRoutes): array
{
    // Only localhost can run this function (meaning you cannot run this in production!)
    if (!r_is_localhost()) {
        ["err" => "[r_audit_developer_routes]: This function can only be run locally!"];
    }
    // Both arrays must be non-empty arrays
    if (!is_array($developerSingleRoutes)) {
        return ["err" => "[r_audit_developer_routes]: '\$developerSingleRoutes' Must be a non-empty array!"];
    } elseif (!is_array($developerMiddlewareRoutes)) {
        return ["err" => "[r_audit_developer_routes]: '\$developerMiddlewareRoutes' Must be a non-empty array!"];
    }
    if (empty($developerSingleRoutes)) {
        ["err" => "[r_audit_developer_routes]: '\$developerSingleRoutes' Must be a non-empty array!"];
    } else if (empty($developerMiddlewareRoutes)) {
        ["err" => "[r_audit_developer_routes]: Must '\$developerMiddlewareRoutes' be a non-empty array!"];
    }

    // Prepare result variable
    $auditResult = [];

    return $auditResult;
}
