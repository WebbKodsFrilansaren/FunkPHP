<?php // PIPELINE.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-07-10 21:34:42
return  [
    'pipeline' =>
    [
        'request' =>
        [
            0 => 'pl_https_redirect',
            1 => 'pl_prepare_uri',
            2 => 'pl_run_ini_sets',
            3 => 'pl_match_denied_exact_ips',
            4 => 'pl_match_denied_methods',
            5 => 'pl_match_denied_uas',
            6 => 'pl_match_route',
            7 => 'pl_run_matched_route_middlewares',
            8 => 'pl_run_matched_route_keys',
        ],
        'post_response' => [
            //0 => 'pl_debug'
        ],
    ],
];
