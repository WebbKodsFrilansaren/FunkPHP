<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-10-31 09:31:41
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
    'DELETE' =>
    [],
    'PATCH' =>
    [],
    'PUT' =>
    [],
    'POST' =>
    [
      '/test' =>
      [
        0 =>
        [
          'test' =>
          [
            'test' =>
            [
              'test' => NULL,
            ],
          ],
        ],
        1 =>
        [
          'test' =>
          [
            'test' =>
            [
              'test' => NULL,
            ],
          ],
        ],
        2 =>
        [
          'test' =>
          [
            'test' =>
            [
              'test' => NULL,
            ],
          ],
        ],
      ],
    ],
  ],
];
