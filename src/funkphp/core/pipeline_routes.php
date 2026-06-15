<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2026-06-15 13:08:36
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/all' =>
      [
        0 =>
        [
          'middlewares' =>
          [
            0 => 'mw_test2',
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
      ],
      '/all2' =>
      [
        0 =>
        [
          'middlewares' =>
          [
            0 => 'mw_test2',
          ],
        ],
        1 =>
        [
          'test' => 'test',
        ],
      ],
      '/user' =>
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
