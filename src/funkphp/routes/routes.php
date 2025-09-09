<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-09-09 06:59:25
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users' =>
      [
        'middlewares' => ['m_auth_v1'],
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
