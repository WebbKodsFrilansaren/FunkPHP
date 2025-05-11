<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-11 17:40:36
return  [
  'ROUTES' => 
   [
    'GET' => 
     [
      '/test' => 
       [
        'handler' => 
         [
          'r_test' => 'r_test',
        ],
        'data' => 
         [
          'd_test' => 'd_test',
        ],
        'validation' => 
         [
          'v_test' => 'v_test',
        ],
      ],
      '/test2' => 
       [
        'handler' => 
         [
          'r_test' => 'r_test2',
        ],
        'data' => 
         [
          'd_test' => 'd_test2',
        ],
        'validation' => 
         [
          'v_test' => 'v_test2',
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