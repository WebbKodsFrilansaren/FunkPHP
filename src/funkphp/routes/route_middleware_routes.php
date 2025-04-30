<?php // ROUTE_Middleware_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-30 12:49:56
 return  [
  'MIDDLEWARES' => 
   [
    'GET' => 
     [
      '/' => 
       [
        'handler' => 'GET_MIDDLEWARE_ROOT',
      ],
      '/test' => 
       [
        'handler' => 
         [
          0 => 'testa2',
        ],
      ],
    ],
    'POST' => 
     [
      '/' => 
       [
        'handler' => 'POST_MIDDLEWARE_ROOT',
      ],
      '/users' => 
       [
        'handler' => 'POST_MIDDLEWARE_USER',
      ],
      '/users/:id' => 
       [
        'handler' => 'POST_MIDDLEWARE_USER_PROFILE_TEST',
      ],
    ],
    'PUT' => 
     [
      '/users/:id' => 
       [
        'handler' => 'PUT_MIDDLEWARE_USER',
      ],
    ],
    'DELETE' => 
     [
      '/users/:id' => 
       [
        'handler' => 'DELETE_MIDDLEWARE_USER',
      ],
      '/users' => 
       [
        'handler' => 'DELETE_MIDDLEWARE_USER',
      ],
    ],
  ],
];