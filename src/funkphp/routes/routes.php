<?php // Routes.php - FunkPHP Framework | FunkCLI Modified it 2025-11-01 10:54:42
return  [
  'ROUTES' => 
   [
    'GET' => 
     [
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