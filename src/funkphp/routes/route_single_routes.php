<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-12 11:22:57
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
      '/testar' => 
       [
        'handler' => 'r_test2',
      ],
      '/testar2' => 
       [
        'handler' => 
         [
          'r_test2' => 'r_test',
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