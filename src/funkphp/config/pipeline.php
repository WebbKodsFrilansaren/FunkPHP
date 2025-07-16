<?php // PIPELINE.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-07-10 21:34:42
return  [
    'pipeline' =>
    [
        'request' =>
        [
            0 => 'pl_https_redirect',
            1 => 'pl_run_ini_sets',
            2 => 'pl_set_session_cookie_params',
            3 => 'pl_db_connect',
            4 => 'pl_headers_set',
            5 => 'pl_headers_remove',
            6 => 'pl_start_session',
            7 => 'pl_prepare_uri',
            8 => 'pl_match_denied_exact_ips',
            9 => 'pl_match_denied_methods',
            10 => 'pl_match_denied_uas',
            11 => 'pl_match_route',
            12 => 'pl_run_matched_route_keys',
            13 => 'pl_run_matched_route_data',
        ],
        'post-request' => [],
    ],
    'no_match' =>
    [
        'route' => [
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
