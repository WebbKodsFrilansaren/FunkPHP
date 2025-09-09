<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-09-09 04:26:15
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
