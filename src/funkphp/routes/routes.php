<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-10-31 09:21:20
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/test' =>
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
              'm_test' => 'passed valiue from M-test',
            ],
            1 =>
            [
              'm_test2' => 'passed value test2',
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
