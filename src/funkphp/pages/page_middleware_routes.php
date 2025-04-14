<?php // PAGES_Middleware_ROUTES.PHP - FunkPHP Framework
// ENTER YOUR MIDDLEWARE ROUTES HERE (GET, POST, PUT, DELETE) | SINGLES ARE IN A SEPARATE FILE
// WARNING: This is where you define your middleware routes, NOT YOUR SINGLE ROUTES!
// IMPORTANT: Both must match in order for middleware to take effect!
return [
    'GET' => [
        '/users/:id' => ['handler' => 'MW_DATA_SPECIFIC_USER_ID',],
        '/users/:id/test' => ['handler' => 'MW_DATA_SPECIFIC_USER_TEST',],
    ],
    'POST' => [
        '/users/:id' => ['handler' => 'MW_DATA_POST_SPECIFIC_NEW_USER',],
        '/users' => ['handler' => 'MW_DATA_POST_NEW_USER',],
    ],
    'PUT' => [
        '/users/:id' => ['handler' => 'MW_DATA_UPDATE_USER_ID',],
    ],
    'DELETE' => [
        '/users/:id' => ['handler' => 'MW_DATA_DELETE_USER_ID',],
    ],
];
