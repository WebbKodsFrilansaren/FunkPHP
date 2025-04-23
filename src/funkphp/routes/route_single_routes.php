<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework
return [
    'ROUTES' => [
        'GETa' => [
            '/' => ['handler' => 'ROOT_get_create_user',],
            '/tests' => ['handler' => 'get_create_user',],
            '/users/:id' => ['handler' => 'get_update_user',],
        ],
        'POST' => [
            '/' => ['handler' => 'ROOT_post_create_user',],
            '/users' => ['handler' => 'post_create_user',],
            '/users/:id' => ['handler' => 'post_update_user',],
        ],
        'PUT' => [
            '/users/:id' => ['handler' => 'put_update_user',],
        ],
        'DELETE' => [
            '/users/:id' => ['handler' => 'delete_delete_user',],
            '/users' => ['handler' => 'delete_delete_user',],
        ],
    ]
];
