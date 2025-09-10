<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-09-10 10:24:49
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users' =>
      [
        0 =>
        [
          'middlewares' =>
          [
            0 => ['auth' => NULL],
          ],
        ],
        1 =>
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
