<?php // pipeline_request.php - FunkPHP Framework | This File Was Modified In FunkCLI 2026-06-16 13:15
return  [
    'pipeline' =>
    [
        '<CONFIG_GLOBAL>' => [
            'global_headers' => [],
            'global_rate_limiting' => null,
            'global_param_rules' => [],
        ],
        'request' =>
        [
            0 => 'pl_https_redirect',
            1 => 'pl_prepare_uri',
            2 => 'pl_run_ini_sets',
            3 => 'pl_match_denied_exact_ips',
            4 => 'pl_match_denied_methods',
            5 => 'pl_match_denied_uas',
            6 => 'pl_match_route_then_run_matched_middlewares_and_pipeline',
        ],
        'post_response' => [
            //0 => 'pl_debug'
        ],
    ],
];
