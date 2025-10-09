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
          'middlewares' =>
          [
            0 =>
            [
              'test1_mw_users' => NULL,
            ],
            1 =>
            [
              'test2_mw_users' => NULL,
            ],
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
          'middlewares' =>
          [
            0 =>
            [
              'test2_mw_user_id' => NULL,
            ],
            1 =>
            [
              'test1_mw_user_id' => NULL,
            ]
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
