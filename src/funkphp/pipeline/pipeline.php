<?php // PIPELINE.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-07-10 21:34:42
return  [
    'pipeline' =>
    [
        'request' =>
        [
            0 => ['pl_https_redirect' => null],
            1 => ['pl_run_ini_sets' => null],
            2 => ['pl_set_session_cookie_params' => null],
            3 => ['pl_headers_set' => null],
            4 => ['pl_headers_remove' => null],
            5 => ['pl_start_session' => null],
            6 => ['pl_prepare_uri' => null],
            7 => ['pl_match_denied_exact_ips' => null],
            8 => ['pl_match_denied_methods' => null],
            9 => ['pl_match_denied_uas' => null],
            10 => ['pl_match_route' => [
                'no_match' => [
                    'json' => ['custom_error' => '404 Not Found: The requested resource could not be found on this server.'],
                    'page' => '/[errors]/404',
                    'callback' => 'TEST_FUNCTION_REMOVE_LATER',
                ]
            ]],
            11 => ['pl_run_matched_route_middlewares' => 'defensive'],
            12 => ['pl_run_matched_route_keys' => 'defensive'],
        ],
        'post-response' => [
            //0 => ['pl_debug' => null]
        ],
    ],
];
