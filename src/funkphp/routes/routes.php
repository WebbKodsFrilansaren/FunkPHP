<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-10-30 08:26:12
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/test' =>
      [
        0 =>
        [
          'try' =>
          [
            'test' => 'test3',
          ],
        ],
      ],
      '/users' =>
      [
        0 =>
        [
          'try' =>
          [
            'test' =>
            [
              'test2' => NULL,
            ],
          ],
        ],
        1 =>
        [
          'data' =>
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
