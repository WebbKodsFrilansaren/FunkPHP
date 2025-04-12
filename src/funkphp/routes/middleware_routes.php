<?php
// ENTER YOUR MIDDLEWARE ROUTES HERE (GET, POST, PUT, DELETE) | SINGLES ARE IN A SEPARATE FILE
// WARNING: This is where you define your middleware routes, NOT YOUR SINGLE ROUTES!
// IMPORTANT: Both must match in order for middleware to take effect!
return [
    'GET' => [
        '/' => ['handler' => 'ROOT_MW', /*...*/],
        '/users' => ['handler' => 'USERS_MW', /*...*/],
        '/users/:id' => ['handler' => 'USER_ID_MW', /*...*/],
        '/about' => ['handler' => 'ABOUT_MW', /*...*/],
    ],
    'POST' => [
        '/users' => ['handler' => 'POST_MIDDLEWARE_USER', /*...*/],
        '/users/:id' => ['handler' => 'POST_MIDDLEWARE_USER_PROFILE_TEST', /*...*/],
    ],
    'PUT' => [
        '/users/:id' => ['handler' => 'PUT_MIDDLEWARE_USER', /*...*/],
    ],
    'DELETE' => [
        '/users/:id' => ['handler' => 'DELETE_MIDDLEWARE_USER', /*...*/],
        '/users' => ['handler' => 'DELETE_MIDDLEWARE_USER', /*...*/],
    ],
];
