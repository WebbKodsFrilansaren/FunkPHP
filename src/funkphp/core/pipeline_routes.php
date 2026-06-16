<?php // pipeline_routes.php - FunkPHP | FunkCLI Modified it 2026-06-16 10:47:52
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
        [
          0 => 'mw_test2',
        ],
        'pipeline' =>
        [
          0 =>
          [
            'test' => 'test',
          ],
        ],
      ],
    ],
    'DELETE' =>
    [],
    'PATCH' =>
    [],
    'PUT' =>
    [],
    'POST' =>
    [],
  ],
];
