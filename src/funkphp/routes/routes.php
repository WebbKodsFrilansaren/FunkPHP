<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-09-09 07:38:05
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users' =>
      [
        'middlewares' =>
        [
          'auth' => NULL,
          '$onFail' => "do_something"
        ],
        'handler' =>
        [
          'test' =>
          [
            'test' => NULL,
          ],
        ],
        'data' =>
        [
          'test' =>
          [
            'test' => NULL,
          ],
        ],
      ],
      '/users_v2' =>
      [
        'middlewares' =>
        [
          'auth' => NULL,
        ],
        '$start' => [
          'handler' => [
            'test' => [
              'test' => NULL,
              '$next' => [
                'data' => [
                  'test' => [
                    'test' => NULL
                  ]
                ]
              ]
            ]
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
