<?php // This file processes the routes from "/routes/rdp_your_routes.php" and returns the found route

// Store found route (=correct REQUEST_URI, HTTP Method & Content-Type) + params or null if no route was found
$rdp_current_route = rdp_routes_return_existing_route($rdp_routes) ?? [];

var_dump($rdp_current_route);

// Redirect to another URI if the route has a redirect URI and a response code (this essentially restarts the routing process)
if ($rdp_current_route['redirect'] ?? null) rdp_routes_redirect_on_code($rdp_current_route['redirect'] ?? null, $rdp_current_route['code'] ?? null);


// When server error occurs due to lack of REQUEST_URI or REQUEST_METHOD
if ($rdp_current_route['code'] === "500") {
    http_response_code(500);
    exit;
}

// When a route was found BUT the content type is not allowed
elseif ($rdp_current_route['code'] === "415") {
    echo "<br>Route FOUND but INVALID CONTENT TYPE:<br>Method = " . $rdp_current_route['method'] . "<br>URI = " . $_SERVER['REQUEST_URI'] . " <br>Auth = " . $rdp_current_route['auth'] . "<br>Params = " . implode(', ', $rdp_current_route['params']);
}

// When a route was found BUT the user is not authenticated/authorized
elseif ($rdp_current_route['code'] === "403") {

    echo "<br>Route FOUND but NO ACCESS:<br>Method = " . $rdp_current_route['method'] . "<br>URI = " . $_SERVER['REQUEST_URI'] . " <br>Auth = " . $rdp_current_route['auth'] . "<br>Params = " . implode(', ', $rdp_current_route['params']);
}

// When a route was NOT found
elseif ($rdp_current_route['code'] === "404") {
    echo "<br>Route NOT found for:<br>Method = " . $rdp_current_route['method'] . "<br>URI = " . $_SERVER['REQUEST_URI'] . " <br>Auth = " . $rdp_current_route['auth'] . "<br>Params = " . implode(', ', $rdp_current_route['params']);
}

// When a route was found and the user is authenticated/authorized
elseif ($rdp_current_route['code'] === "200") {

    // Store any found params in $d['params'] to be used for data and/or page step(s)
    $d[$rdp_data_params_key_name] = $rdp_current_route['params'] ?? [];

    echo "<br>Route FOUND and AUTHORIZED:<br>Method = " . $rdp_current_route['method'] . "<br>URI = " . $_SERVER['REQUEST_URI'] . " <br>Auth = " . $rdp_current_route['auth'] . "<br>Params = " . implode(', ', $rdp_current_route['params']);
} // Process when no route was found
