<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-09-09 07:38:05
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users' =>
      [
        'middlewares' =>
        [
          'auth' => NULL,
        ],
        'try' =>
        [
          'test' =>
          [
            'test' => NULL,
          ],
        ],
      ],
      '/users/:id' =>
      [],
    ],
    'POST' =>
    [],
    'DELETE' =>
    [],
    'PATCH' =>
    [],
    'PUT' =>
    [],
  ],
];
