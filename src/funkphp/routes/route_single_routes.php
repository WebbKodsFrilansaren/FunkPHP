<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-11 13:46:04
return  [
  'ROUTES' => 
   [
    'GET' => 
     [
      '/test' => 
       [
        'handler' => 
         [
          'r_test1' => 'r_test2',
        ],
        'data' => 
         [
          'd_test1' => 'd_test2',
        ],
        'validation' => 
         [
          'v_test' => 'v_test',
        ],
      ],
    ],
    'POST' => 
     [
    ],
    'PUT' => 
     [
    ],
    'DELETE' => 
     [
    ],
    'PATCH' => 
     [
    ],
  ],
];