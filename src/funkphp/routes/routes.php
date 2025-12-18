<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-12-18 09:25:28
return  [
  'ROUTES' => 
   [
    'GET' => 
     [
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