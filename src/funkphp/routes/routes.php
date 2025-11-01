<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-11-01 15:10:30
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users/:id' =>
      [
        0 =>
        [
          'middlewares' =>
          [
            0 =>
            [
              'mw_test3' => NULL,
            ],
          ],
        ],
        1 =>
        [
          'try' =>
          [
            'test' =>
            [
              'test2' => NULL,
            ],
          ],
        ],
      ],
    ],
    'DELETE' =>
    [],
    'PATCH' =>
    [],
    'PUT' =>
    [],
    'POST' =>
    [],
  ],
];
