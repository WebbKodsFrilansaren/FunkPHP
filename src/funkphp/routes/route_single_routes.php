<?php
// This file was recreated by FunkCLI!
return '<CONFIG>' => [

        'middlewares_before_route_match' => [
            'm_https_redirect',
            'm_run_ini_sets',
            'm_set_session_cookie_params',
            'm_db_connect',
            'm_headers_set',
            'm_headers_remove',
            'm_start_session',
            'm_prepare_uri',
            'm_match_denied_exact_ips',
            'm_match_denied_methods',
            'm_match_denied_uas',
        ],
        'middlewares_after_handled_request' => [],
        'no_middlewares_match' => ['json' => [], 'page' => []],
        'no_route_match' => ['json' => [], 'page' => []],
        'no_data_match' => ['json' => [], 'page' => []],
        'no_page_match' => ['json' => [], 'page' => []],
    ],

    'ROUTES' => [
'GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => [], 'PATCH' => []]?>