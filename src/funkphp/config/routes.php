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
        'middlewares' =>
        [
          'm_test' => 'Middleware Test Value',
          'm_test2',
          'm_test2' => 'Middleware Test Value 2, we now passed a value to this middleware!',
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
