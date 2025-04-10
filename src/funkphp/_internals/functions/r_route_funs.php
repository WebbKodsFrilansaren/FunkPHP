<?php // ROUTE-related FUNCTIONS FOR FunPHP

// Redirect to HTTPS if the application is online (not localhost)
function r_https_redirect()
{
    try {
        if ($_SERVER['SERVER_NAME'] !== "localhost" ||  $_SERVER['SERVER_NAME'] !== "127.0.0.1") {
            // Only redirect if the connection is not secure
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
                global $fphp_BASEURL_ONLINE;
                header("Location: $fphp_BASEURL_ONLINE" . $_SERVER['REQUEST_URI'], true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        // Handle any exceptions that may occur
        error_log("Error in r_https_redirect: " . $e->getMessage());
    }
}

// Try match against denied IPs globally
function r_match_denied_global_ips($denied_ips, $ip)
{
    // First check $ip is a valid IP address
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return true; // Invalid IP address, so deny access
    }

    if (!isset($denied_ips['denied'])) {
        return false; // No denied IPs configured
    }

    $deniedConfig = $denied_ips['denied'];

    //Check if the IP address starts with any of the denied IPs
    if (isset($deniedConfig['ip_starts_with']) && is_array($deniedConfig['ip_starts_with'])) {
        if (array_any_element($deniedConfig['ip_starts_with'], 'str_starts_with', $ip, ["swap_args"])) {
            echo " | DENIED IP STARTS WITH FOUND!<br>";
            return true;
        }
    }

    // Check if the IP address ends with any of the denied IPs
    if (isset($deniedConfig['ip_ends_with']) && is_array($deniedConfig['ip_ends_with'])) {
        if (array_any_element($deniedConfig['ip_ends_with'], 'str_ends_with', $ip, ["swap_args"])) {
            echo " | DENIED IP ENDS WITH FOUND!<br>";
            return true;
        }
    }

    // Check if the IP address is an exact match with any of the denied IPs
    if (isset($deniedConfig['exact_ips']) && is_array($deniedConfig['exact_ips'])) {
        if (array_any_element($deniedConfig['exact_ips'], 'str_equals', $ip, ["swap_args"])) {
            echo " | DENIED IP EXACT MATCH FOUND!<br>";
            return true;
        }
    }

    echo " | DENIED IP NOT FOUND! - Move on!<br>";
    return false; // IP address did not match any denied criteria

}

// Try match against denied UAs globally
function r_match_denied_global_uas($denied_uas, $ua)
{
    if (!isset($denied_uas['denied'])) {
        return false; // No denied UAs configured or passed
    }

    // Check if the User-Agent contains any of the denied UAs
    $deniedConfig = $denied_uas['denied'];
    if (isset($deniedConfig['contains']) && is_array($deniedConfig['contains'])) {
        if (array_any_element($deniedConfig['contains'], 'str_contains', $ua, ["swap_args"])) {
            echo " | DENIED UAS CONTAINS FOUND!<br>";
            return true;
        }
    }

    echo " | DENIED UA NOT FOUND! - Move on!<br>";
    return false; // IP address did not match any denied criteria
}

// Prepare $req['uri'] for consistent use in the app
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

/* @param array $methodRootNode The root node of the Trie for the specific HTTP method.
** @return string|null The reconstructed route definition string on exact structural match, otherwise null.
*/
function r_match_compiled_route(string $requestUri, array $methodRootNode): ?array
{
    // 1. Process the Request URI internally
    $path = parse_url($requestUri, PHP_URL_PATH);
    if ($path === false || $path === null) {
        $path = '/'; // Handle parse errors or empty paths
    }
    $path = trim(strtolower($path), '/');

    // Split into segments. array_filter removes empty elements (e.g., from '//')
    // array_values re-indexes the array.
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);
    echo "<br>Extracted URI Segments: " . json_encode($uriSegments) . " (count: $uriSegmentCount)<br>";

    // Prepare variables to store the current node, matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = [];
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;

    echo "<br>PATH TO MATCH: \"/$path\"<br>";
    echo "START NODE: " . json_encode($currentNode) . "<br>";
    echo "MATCHED SEGMENTS: " . json_encode($matchedPathSegments) . " | PARAMS: " . json_encode($matchedParams) .  " | MIDDLEWARES: " . json_encode($matchedMiddlewares) . "<br><br>";

    // Handle EDGE CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
        }
        echo "FINAL MATCHED: " . json_encode($matchedPathSegments, JSON_UNESCAPED_SLASHES) . " | FINAL PARAMS: " . json_encode($matchedParams) . " | FINAL MIDDLEWARES: " . json_encode($matchedMiddlewares, JSON_UNESCAPED_SLASHES) . "<br>";
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }

    // Iterate through the segments from the URI
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        echo "STEP " . $i + 1 . ": AT NODE: " . json_encode($currentNode, JSON_PRETTY_PRINT) . " | URI: $currentUriSegment <br>";
        echo "MATCHED SEGMENTS: " . json_encode($matchedPathSegments, JSON_PRETTY_PRINT) . " | PARAMS: " . json_encode($matchedParams) .  " | MIDDLEWARES: " . json_encode($matchedMiddlewares, JSON_PRETTY_PRINT) . "<br><br>";

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

        // Or try match dynamic route ":" indicator node
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']); // Get e.g., ':id'
            echo "Placeholder key:" . json_encode($placeholderKey) . "<br>";
            echo "FOUND DYNAMIC ROUTE KEY: \"$placeholderKey\"<br><br>";

            // Only store param and matched URI segment if not null
            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedParams[$placeholderKey] = $currentUriSegment;
                $matchedPathSegments[] = ":" . $placeholderKey;
                $currentNode = $currentNode[':'][$placeholderKey];
                $segmentsConsumed++;
                continue;
            }
        }

        // No Match for this segment - URI doesn't match Trie structure
        return null;
    }

    echo "AT NODE: " . json_encode($currentNode, JSON_PRETTY_PRINT) . "| URI: $currentUriSegment <br>";
    echo "MATCHED SEGMENTS: " . json_encode($matchedPathSegments, JSON_PRETTY_PRINT) . " | PARAMS: " . json_encode($matchedParams, JSON_PRETTY_PRINT) .  " | MIDDLEWARES: " . json_encode($matchedMiddlewares, JSON_UNESCAPED_SLASHES) . "<br><br>";

    // Final check to see if we are at a middleware node at the end of the loop
    if (isset($currentNode['|'])) {
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments));
    }

    // After the loop: Check if we consumed the correct number of segments
    if ($segmentsConsumed === $uriSegmentCount) {
        // We have successfully consumed exactly the right number of segments.
        // Reconstruct the potential route definition string.
        // This string represents the structure we matched in the Trie.
        if (empty($matchedPathSegments)) {
            // This case should only be hit if $uriSegmentCount was 0 initially, handled above.
            // But as a safeguard, return '/'.
            return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
        } else {
            echo "FINAL MATCHED: " . json_encode($matchedPathSegments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . " | FINAL PARAMS: " . json_encode($matchedParams, JSON_PRETTY_PRINT) . " | FINAL MIDDLEWARES: " . json_encode($matchedMiddlewares, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "<br>";

            return ["route" => '/' . implode('/', $matchedPathSegments), "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
        }
    } else {
        // Should not happen if the loop logic is correct, but as a safeguard:
        // This implies the loop exited prematurely or consumed fewer segments than expected.
        return null;
    }
}

function r_match_developer_route(string $method, string $uri, array $compiledTrie, array $developerSingleRoutes, array $developerMiddlewareRoutes, string $handlerKey = "handler", string $mHandlerKey = "mHandler")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = "";

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

            // Add Any Matched Middlewares Handlers Defined By Developer!
            if (
                isset($routeDefinition["middlewares"]) && !empty($routeDefinition["middlewares"])
            ) {
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (isset($developerMiddlewareRoutes[$method][$middleware]) && isset($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey])) {
                        $matchedMiddlewareHandlers[] = $developerMiddlewareRoutes[$method][$middleware][$mHandlerKey] ?? null;
                    }
                }
            }
            // Now also check in the middleware
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
        "no_match_in" => $noMatchIn,
    ];
}
