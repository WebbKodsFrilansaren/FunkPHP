<?php // routes.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-07-10 21:34:42
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/authors/:id' =>
      [
        'handler' =>
        [
          'r_authors' => 'r_by_id',
        ],
        'data' =>
        [
          'd_authors' => 'd_by_id',
        ],
        'middlewares' =>
        [
          'm_test' => 'm_by_id',
          'm_test2' => 'm_by_id',
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
