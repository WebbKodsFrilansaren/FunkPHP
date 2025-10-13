<?php // PIPELINE.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-07-10 21:34:42
return  [
    'pipeline' =>
    [
        'request' =>
        [
            0 => ['pl_https_redirect' => null],
            1 => ['pl_run_ini_sets' => null],
            2 => ['pl_set_session_cookie_params' => null],
            3 => ['pl_db_connect' => null],
            4 => ['pl_headers_set' => null],
            5 => ['pl_headers_remove' => null],
            6 => ['pl_start_session' => null],
            7 => ['pl_prepare_uri' => null],
            8 => ['pl_match_denied_exact_ips' => '/config/blocked/blocked_ips.php'],
            9 => ['pl_match_denied_methods' => null],
            10 => ['pl_match_denied_uas' => '/config/blocked/blocked_uas.php'],
            11 => ['pl_match_route' => 'null'],
            12 => ['pl_run_matched_route_middlewares' => 'defensive'],
            13 => ['pl_run_matched_route_keys' => 'defensive'],
        ],
        'post-request' => [
            0 => ['pl_debug' => null]
        ],
    ],
    'no_match' =>
    [
        'route' => [
            'json' =>
            [],
            'page' =>
            [],
            'callback' =>
            [],
        ],
        'page' => [
            'json' =>
            [],
            'page' =>
            [],
            'callback' =>
            [],
        ],
    ],
];
