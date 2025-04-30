<?php // DATA_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-30 12:49:56
 return  [
  'MIDDLEWARES' => 
   [
    'GET' => 
     [
      '/test' => 
       [
        'handler' => 'testa2',
      ],
      '/users/:id' => 
       [
        'handler' => 
         [
          0 => 'MW_DATA_SPECIFIC_USER_ID',
          1 => 'SHEESH',
        ],
      ],
      '/users/:id/test' => 
       [
        'handler' => 'MW_DATA_SPECIFIC_USER_TEST',
      ],
    ],
    'POST' => 
     [
      '/users/:id' => 
       [
        'handler' => 'MW_DATA_POST_SPECIFIC_NEW_USER',
      ],
      '/users' => 
       [
        'handler' => 'MW_DATA_POST_NEW_USER',
      ],
    ],
    'PUT' => 
     [
      '/users/:id' => 
       [
        'handler' => 'MW_DATA_UPDATE_USER_ID',
      ],
    ],
    'DELETE' => 
     [
      '/users/:id' => 
       [
        'handler' => 'MW_DATA_DELETE_USER_ID',
      ],
    ],
  ],
];