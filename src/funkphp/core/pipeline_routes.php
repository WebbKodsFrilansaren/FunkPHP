<?php // pipeline_routes.php - FunkPHP | FunkCLI Modified it 2026-06-16 11:34:04
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/all' =>
      [
        'config' =>
        [
          'route_alias' => '',
          'route_headers' =>
          [],
          'route_rate_limiting' => NULL,
          'route_cache' => NULL,
          'route_param_rules' =>
          [],
        ],
        'middlewares' =>
        [],
        'pipeline' =>
        [
          0 =>
          [
            'test' => 'test',
          ],
        ],
      ],
      '<CONFIG_METHOD>' =>
      [
        'method_headers' =>
        [],
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        [],
      ],
    ],
    'POST' =>
    [
      '<CONFIG_METHOD>' =>
      [
        'method_headers' =>
        [],
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        [],
      ],
    ],
    'PUT' =>
    [
      '<CONFIG_METHOD>' =>
      [
        'method_headers' =>
        [],
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        [],
      ],
    ],
    'DELETE' =>
    [
      '<CONFIG_METHOD>' =>
      [
        'method_headers' =>
        [],
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        [],
      ],
    ],
    'PATCH' =>
    [
      '<CONFIG_METHOD>' =>
      [
        'method_headers' =>
        [],
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        [],
      ],
    ],
  ],
];
