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
function find_exact_structural_route_match(string $requestUri, array $methodRootNode): ?array
{
    // 1. Process the Request URI internally
    $path = parse_url($requestUri, PHP_URL_PATH);
    if ($path === false || $path === null) {
        $path = '/'; // Handle parse errors or empty paths
    }
    $path = trim(strtolower($path), '/');
    echo "Path:$path<br>";

    // Split into segments. array_filter removes empty elements (e.g., from '//')
    // array_values re-indexes the array.
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    echo "URI Segments: " . json_encode($uriSegments) . "<br>";
    $uriSegmentCount = count($uriSegments);

    // --- Traversal Logic ---
    $currentNode = $methodRootNode;
    $matchedPathSegments = []; // Stores the *definition* segments ('users', '{id}', etc.)
    $params = ["params" => []]; // Store parameters for placeholders
    $matchedMiddlewares = ["middlewares" => []]; // Stores the middlewares
    $segmentsConsumed = 0;

    echo "Current Node: " . json_encode($currentNode) . "<br>";
    echo "Matched segments so far: " . json_encode($matchedPathSegments) . "<br>";
    echo "Params so far: " . json_encode($params) . "<br>";
    echo "Middlewares so far: " . json_encode($matchedMiddlewares) . "<br>";

    // Handle root path '/' match
    if ($uriSegmentCount === 0) {
        // If the URI is '/', we have consumed 0 segments.
        // The caller will check if '/' is actually defined.
        // We return '/' as the potential definition string.
        return ['/', $params];
    }

    // Iterate through the segments from the URI
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];

        echo "Current UriSegment: " . json_encode($currentUriSegment) . "<br>";
        echo "Current Node: " . json_encode($currentNode) . "<br>";
        echo "Matched segments so far: " . json_encode($matchedPathSegments) . "<br>";
        echo "Params so far: " . json_encode($params) . "<br>";
        echo "Middlewares so far: " . json_encode($matchedMiddlewares, JSON_UNESCAPED_SLASHES) . "<br>";

        // First check for middleware at the current node and then just add
        // It is always at the same level as the next child node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares["middlewares"], "/" . implode('/', $matchedPathSegments));
        }


        // Prioritize literal match
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments[] = $currentUriSegment;
            $currentNode = $currentNode[$currentUriSegment];
            $segmentsConsumed++;
            continue;
        }

        // Then try placeholder match
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']); // Get e.g., ':id'
            echo "Placeholder Key: $placeholderKey<br>";
            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedPathSegments[] = ":" . $placeholderKey; // Add the placeholder definition
                $currentNode = $currentNode[':'][$placeholderKey];
                $params["params"][$placeholderKey] = $currentUriSegment;; // Store the actual value from the URI
                $segmentsConsumed++;
                continue;
            }
        }

        // No Match for this segment - URI doesn't match Trie structure
        return null;
    }

    echo "Current UriSegment: " . json_encode($currentUriSegment) . "<br>";
    echo "Current Node: " . json_encode($currentNode) . "<br>";
    echo "Matched segments so far: " . json_encode($matchedPathSegments) . "<br>";
    echo "Params so far: " . json_encode($params) . "<br>";
    echo "Middlewares so far: " . json_encode($matchedMiddlewares, JSON_UNESCAPED_SLASHES) . "<br>";

    // Final check to see if we are at a middleware node at the end of the loop
    if (isset($currentNode['|'])) {
        array_push($matchedMiddlewares["middlewares"], "/" . implode('/', $matchedPathSegments));
    }

    // After the loop: Check if we consumed the correct number of segments
    if ($segmentsConsumed === $uriSegmentCount) {
        // We have successfully consumed exactly the right number of segments.
        // Reconstruct the potential route definition string.
        // This string represents the structure we matched in the Trie.
        if (empty($matchedPathSegments)) {
            // This case should only be hit if $uriSegmentCount was 0 initially, handled above.
            // But as a safeguard, return '/'.
            return ['/', $params, $matchedMiddlewares];
        } else {
            echo "Final Matched Path Segments: " . json_encode($matchedPathSegments, JSON_UNESCAPED_SLASHES) . "<br>";
            echo "Final Extracted Params: " . json_encode($params) . "<br>";
            echo "Final Matched Middlewares: " . json_encode($matchedMiddlewares, JSON_UNESCAPED_SLASHES) . "<br>";

            return ['/' . implode('/', $matchedPathSegments), $params, $matchedMiddlewares];
        }
    } else {
        // Should not happen if the loop logic is correct, but as a safeguard:
        // This implies the loop exited prematurely or consumed fewer segments than expected.
        return null;
    }
}



function run_router(string $method, string $uri, array $compiledTrie, array $developerRoutes)
{
    echo "Request: $method $uri\n";

    $routeDefinition = null;
    if (isset($compiledTrie[$method])) {
        $routeDefinition = find_exact_structural_route_match($uri, $compiledTrie[$method]);
    } else {
        echo "  Method $method not supported in compiled Trie.<br>"; // Or 405 Method Not Allowed
        echo "----<br>";
        return;
    }

    if ($routeDefinition !== null) {
        echo "  Structurally matched definition: {$routeDefinition[0]}<br>";

        // **Final Check:** Does this structurally valid path exist in developer's definitions?
        if (isset($developerRoutes[$method][$routeDefinition[0]])) {
            $routeInfo = $developerRoutes[$method][$routeDefinition[0]];
            echo "<br>FOUND Route! Handler: " . $routeInfo['handler'] . "<br>";
            echo "FOUND PARAMS! " . json_encode($routeDefinition[1] ?? []) . "<br>";
            echo "FOUND MIDDLEWARES! " . json_encode($routeDefinition[2] ?? [], JSON_UNESCAPED_SLASHES) . "<br><br>";
        } else {
            echo "  404 Not Found (Path structure exists but not defined as endpoint)<br><br>";
        }
    } else {
        echo "  404 Not Found (Path structure mismatch or URI length wrong)<br><br>";
    }
    echo "----\n";
}
