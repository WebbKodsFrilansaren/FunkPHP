<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-11-03 14:23:00
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
            1 =>
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
