<?php

// Each Middleware Route checks first HTTP METHOD and then if the REQUEST_URI starts with the given URI!
// This is used to perform actions before the actual route is processed, e.g., setting headers, cookies, etc.
$rdp_middleware_routes = $rdp_middleware_routes_externally ? [...$rdp_middleware_routes_external] :
    [];

// Each route is an array with the key being the
// allowed METHOD + REQUEST_URI and the value being
// an array with the elements in the following order:
// [0] = Allowed Content Type(s) separated by "|", e.g., "text/html|application/json"
// [1] = Authentication and/or Authorization needed: "all" for everyone, "user" for logged in, "admin" for admin
// [2] = OPTIONAL: Redirect to other URI dependning on response code (e.g. "403/login" to redirect to /login when 403, use "|" for multiple codes)
//       IMPORTANT: This essentially means that the routing starts over with the new URI
// ['options'] = OPTIONAL: An associative array with additional options for the route

$rdp_routes = $rdp_routes_externally ? [...$rdp_routes_external] :
    [
        /*** IMPORTANT ***/
        // 1. "/rdp/src/public_html/" is replaced with = "/" in localhost to match online
        // 2. The last "/" is always removed from the REQUEST_URI during route processing
        // 3. The QUERY_STRING is removed from the REQUEST_URI during route processing
        // 4. Use {params} in the REQUEST_URI to allow for and extract dynamic values from the URI

        'GET/' => ['text/html', 'all'],
        'GET/test' => ['text/html', 'user'],
        'GET/login' => ['text/html', 'all'],
        'POST/login' => ['application/x-www-form-urlencoded', 'all'],
        'POST/test' => ['application/x-www-form-urlencoded', 'user'],
        'GET/test/{id}' => ['text/html', 'all'],

    ];
