<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-30 22:41:43
return  [
  '<CONFIG>' =>
  [
    'middlewares_before_route_match' =>
    [
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
      'm_match_denied_uas'
    ],
    'middlewares_after_successful_request' => [],
    'no_middlewares_match' =>
    [
      'json' =>
      [],
      'page' =>
      [],
    ],
    'no_route_match' =>
    [
      'json' =>
      [],
      'page' =>
      [],
    ],
    'no_data_match' =>
    [
      'json' =>
      [],
      'page' =>
      [],
    ],
    'no_page_match' =>
    [
      'json' =>
      [],
      'page' =>
      [],
    ],

  ],
  'ROUTES' =>
  [
    'GET' =>
    [
      '/test/:id' =>
      [
        'handler' =>
        [
          'r_test' => 'r_test2',
        ],
        'data' =>
        [
          'd_test' => 'd_test2',
        ],
      ],
      '/test2' =>
      [
        'handler' => 'r_test3',
        'data' => 'd_test3',
      ],
      '/test3' =>
      [
        'handler' => 'r_test5',
      ],
    ],
    'POST' =>
    [
      '/test/:id' =>
      [
        'handler' =>
        [
          'r_test' => 'r_test2',
        ],
        'data' =>
        [
          'd_test' => 'd_test2',
        ],
      ],
    ],
    'PUT' =>
    [],
    'DELETE' =>
    [],
    'PATCH' =>
    [],
  ],
];
