<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-27 06:45:58
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/' =>
      [
        'handler' => 'ROOT_get_create_user',
      ],
      '/users/:id' =>
      [
        'handler' => 'get_update_user',
      ],
      '/users' =>
      [
        'handler' => 'users_handler',
      ],
      '/test_all' =>
      [
        'handler' => 'testall',
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
      '/users2' =>
      [
        'handler' => 'users_p',
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
