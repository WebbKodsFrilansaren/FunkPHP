<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-08-24 20:21:11
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users/' =>
      [],
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
