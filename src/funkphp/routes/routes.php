<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-09-09 05:15:20
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users/:id' =>
      [],
    ],
    'POST' =>
    [
      '/test' =>
      [
        'middlewares' =>
        [
          0 =>
          [
            'auth' => 'jwt',
          ],
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
