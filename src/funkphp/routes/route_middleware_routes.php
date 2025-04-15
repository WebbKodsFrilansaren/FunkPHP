<?php // ROUTE_Middleware_ROUTES.PHP - FunkPHP Framework
return [
    'GET' => [
        '/users' => ['handler' => ['R/users_id/MW_R_TEST', 'R/users_id/MW_R_TEST2'],],
        '/users/:id' => ['handler' => ['R/users_id/MW_R_USER_ID1', "R/users_id/MW_R_USER_ID2",],],
        '/users/:id/test' => ['handler' => 'MW_R_USER_TEST',],
        '/about' => ['handler' => 'MW_R_ABOUT',],
    ],
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
];
