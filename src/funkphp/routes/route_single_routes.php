<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-29 21:03:51
return  [
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
    ],
    'POST' =>
    [
      '/test/:id' =>
      [
        'handler' => 'r_test',
        'data' => 'd_test',
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
