<?php return function (&$c) {
    if ($c['req']['matched_middlewares'] !== null) {
        funk_run_matched_route_middleware($c);
    } else {
        $c['err']['MAYBE']['CONFIG'][] = 'No Configured Matched Route Middlewares (`"ROUTES" => "GET|POST|PUT|DELETE|PATCH" => "/route" => "middlewares" Key`) to load and run after Route Matching. If you expected Middlewares to run after Route Matching, check for the Route in the `funkphp/config/routes.php` File!';
    }
};
