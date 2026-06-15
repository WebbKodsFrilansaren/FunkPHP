<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2026-06-15 17:43:44
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
      '/all4' =>
      [
        0 =>
        [
          'najs' => 'paj',
        ],
        1 =>
        [
          'najs' => 'paj2',
        ],
        2 =>
        [
          'najs' => 'paj2',
        ],
        3 =>
        [
          'najs' => 'paj3',
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
