<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-10-10 00:13:35
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users' =>
      [
        0 =>
        [
          'try' =>
          [
            'test' =>
            [
              'test' => NULL,
            ],
          ],
        ],
      ],
      '/users/:id' =>
      [
        0 =>
        [
          'middlewares' =>
          [
            0 =>
            [
              'm_test' => "passed valiue from M-test",
            ],
            1 =>
            [
              'm_test2' => "passed value test2",
            ]
          ],
        ],
        1 =>
        [
          'try' =>
          [
            'test' =>
            [
              'test2' => "passed value from try=>test=>test2",
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
