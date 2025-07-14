<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-07-14 04:29:24
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/' =>
      [
        'handler' =>
        [
          'r_test' => 'r_test1',
        ],
      ],
      '/:id' =>
      [
        'handler' =>
        [
          'r_test' => 'r_test2',
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
