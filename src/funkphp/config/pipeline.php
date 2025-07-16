<?php // PIPELINE.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-07-10 21:34:42
return  [
    'pipeline' =>
    [
        'request' =>
        [
            0 => 'm_https_redirect',
            1 => 'm_run_ini_sets',
            2 => 'm_set_session_cookie_params',
            3 => 'm_db_connect',
            4 => 'm_headers_set',
            5 => 'm_headers_remove',
            6 => 'm_start_session',
            7 => 'm_prepare_uri',
            8 => 'm_match_denied_exact_ips',
            9 => 'm_match_denied_methods',
            10 => 'm_match_denied_uas',
            11 => 'm_match_route',
            12 => 'm_run_matched_route_middlewares',
            13 => 'm_run_matched_route_handler',
            14 => 'm_run_matched_data_handler',
        ],
        'post-request' => [],
    ],
    'no_match' =>
    [
        'handler' => [
            'json' =>
            [],
            'page' =>
            [],
        ],
        'page' => [
            'json' =>
            [],
            'page' =>
            [],
        ],
    ],
];
