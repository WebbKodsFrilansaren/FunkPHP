<?php // ROUTE_Middleware_ROUTES.PHP - FunkPHP Framework
return     [
    'MIDDLEWARES' => [
        'GET' => [],
        'POST' => [
            '/' => ['handler' => 'POST_MIDDLEWARE_ROOT',],
            '/users' => ['handler' => 'POST_MIDDLEWARE_USER',],
            '/users/:id' => ['handler' => 'POST_MIDDLEWARE_USER_PROFILE_TEST',],
        ],
        'PUT' => [
            '/users/:id' => ['handler' => 'PUT_MIDDLEWARE_USER',],
        ],
        'DELETE' => [
            '/users/:id' => ['handler' => 'DELETE_MIDDLEWARE_USER',],
            '/users' => ['handler' => 'DELETE_MIDDLEWARE_USER',],
        ],
    ]
];
