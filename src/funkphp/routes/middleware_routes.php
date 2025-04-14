<?php
// ENTER YOUR MIDDLEWARE ROUTES HERE (GET, POST, PUT, DELETE) | SINGLES ARE IN A SEPARATE FILE
// WARNING: This is where you define your middleware routes, NOT YOUR SINGLE ROUTES!
// IMPORTANT: Both must match in order for middleware to take effect!
return [
    'GET' => [
        '/' => ['handler' => '',],
        '/users' => ['handler' => 'USERS_MW',],
        '/users/:id' => ['handler' => ['MW_USER_ID1', "MW_USER_ID2",],],
        '/users/:id/test' => ['handler' => 'USER_TEST',],
        '/about' => ['handler' => 'ABOUT_MW',],
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
