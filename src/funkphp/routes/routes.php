<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-07-25 16:44:22
return  [
  'ROUTES' =>
  [
    'GET' =>
    [],
    'POST' =>
    [
      '/test' =>
      [
        'middlewares' => ["auth", "log"],
        'handler' => ["test" => "test"],
        'data' => ["test" => "test"],
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
