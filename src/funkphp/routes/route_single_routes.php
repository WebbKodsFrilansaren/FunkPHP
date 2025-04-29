<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-29 17:17:14
return  [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/' =>
      [
        'handler' => 'ROOT_get_create_user',
      ],
      '/test' =>
      [
        'handler' => 't',
      ],
      '/test2' =>
      [
        'handler' => 't-1',
      ],
      '/test3' =>
      [
        'handler' => 't',
      ],
      '/test_all' =>
      [
        'handler' => 'testall',
      ],
      '/users' =>
      [
        'handler' => 'users_handler',
      ],
      '/users/:id' =>
      [
        'handler' => 'get_update_user',
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
