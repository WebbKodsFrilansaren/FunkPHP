<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-11 16:40:26
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/test' =>
      [
        'handler' => 'r_test',
        'data' => 'd_test',
        'validation' => 'v_test',
      ],
      '/test/:id' =>
      [
        'handler' =>
        [
          'r_test' => 'r_by_id',
        ],
        'validation' =>
        [
          'v_test' => 'v_by_id',
        ],
      ],
    ],
    'POST' =>
    [],
    'PUT' =>
    [],
    'DELETE' =>
    [],
    'PATCH' =>
    [],
  ],
];
