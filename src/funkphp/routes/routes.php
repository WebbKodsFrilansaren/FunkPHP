<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-08-21 20:21:19
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/test2' =>
      [],
    ],
    'POST' =>
    [
      '/test' =>
      [
        'middlewares' =>
        [
          0 => 'auth',
          1 => 'log',
        ],
        'handler' =>
        [
          'test' => 'test',
        ],
        'data' =>
        [
          'test' => 'test',
        ],
        'page' => 'test',
      ],
    ],
    'DELETE' =>
    [],
    'PATCH' =>
    [],
    'PUT' =>
    [],
  ],
];
