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
function r_match_denied_uas($ua)
{
    $uas = include dirname(__DIR__) . '/compiled/uas.php';
    echo $ua;

    // First we lowercase the $ua
    $ua = mb_strtolower($ua);
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
            if (
                isset($routeDefinition["middlewares"]) && !empty($routeDefinition["middlewares"] && is_array($routeDefinition["middlewares"]))
            ) {
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (isset($developerMiddlewareRoutes[$method][$middleware]) && isset($developerMiddlewareRoutes[$method][$middleware][$mHandlerKey])) {
                        $matchedMiddlewareHandlers[] = $developerMiddlewareRoutes[$method][$middleware][$mHandlerKey] ?? null;
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
