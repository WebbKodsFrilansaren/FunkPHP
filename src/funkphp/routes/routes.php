<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2026-06-04 11:55:33
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/user' =>
      [],
      '/usera' =>
      [],
      '/users' =>
      [],
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
        2 =>
        [
          'final' =>
          [
            'test_final' => NULL,
          ],
        ],
      ],
      '/usersy' =>
      [],
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
