<?php // DEFAULT Custom Errors Configuration - Change as needed!
return [
    'pl_db_connect' => [],
    'pl_headers_remove' => [],
    'pl_headers_set' => [],
    'pl_https_redirect' => [],
    'pl_match_denied_exact_ips' => [],
    'pl_match_denied_methods' => [],
    'pl_match_denied_uas' => [],
    'pl_match_route' => [],
    'pl_prepare_uri' => [],
    'pl_run_ini_sets' => [],
    'pl_run_matched_route_key' => [],
    'pl_run_matched_route_keys' => [],
    'pl_run_matched_route_middlewares' => [],
    'pl_set_session_cookie_params' => [],
    'pl_start_session' => [],
    // Syntax example to know it's `routes/try/test.php -> function test2`, NOT mandatory though!
    // but helps to not get confused with similar names and impossible for naming conflicts since
    // folders=>files=>functions are always unique
    'routes/try=>test=>test2' => []
];
