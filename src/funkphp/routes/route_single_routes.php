<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-27 05:34:42
return  [
  'ROUTES' => 
   [
    'GET' => 
     [
      '/' => 
       [
        'handler' => 'ROOT_get_create_user',
      ],
      '/tests' => 
       [
        'handler' => 'get_test',
      ],
      '/users/:id' => 
       [
        'handler' => 'get_update_user',
      ],
      '/users' => 
       [
        'handler' => 'users_handler',
      ],
      '/users2' => 
       [
        'handler' => 'users_handler-1',
      ],
      '/users3' => 
       [
        'handler' => 'users_handler-2',
      ],
      '/users4' => 
       [
        'handler' => 'users_handler-3',
      ],
      '/users5' => 
       [
        'handler' => 'users_handler-4',
      ],
      '/users6' => 
       [
        'handler' => 'users_handler-5',
      ],
      '/users7' => 
       [
        'handler' => 'users_handler-6',
      ],
    ],
    'POST' => 
     [
      '/' => 
       [
        'handler' => 'ROOT_post_create_user',
      ],
      '/users' => 
       [
        'handler' => 'post_create_user',
      ],
      '/users/:id' => 
       [
        'handler' => 'post_update_user',
      ],
    ],
    'PUT' => 
     [
      '/users/:id' => 
       [
        'handler' => 'put_update_user',
      ],
    ],
    'DELETE' => 
     [
      '/users/:id' => 
       [
        'handler' => 'delete_delete_user',
      ],
      '/users' => 
       [
        'handler' => 'delete_delete_user',
      ],
    ],
  ],
];