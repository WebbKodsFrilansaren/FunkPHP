<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2026-01-22 03:44:37
return  [
  'ROUTES' => 
   [
    'GET' => 
     [
      '/user' => 
       [
      ],
      '/users' => 
       [
      ],
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
       [
      ],
    ],
    'DELETE' => 
     [
    ],
    'PATCH' => 
     [
    ],
    'PUT' => 
     [
    ],
    'POST' => 
     [
    ],
  ],
];