<?php

return [
    'GET' => [
        '/' => ['handler' => 'GET_MIDDLEWARE_ROOT', /*...*/],
        '/users' => ['handler' => 'GET_MIDDLEWARE_USER', /*...*/],
        '/users/:id' => ['handler' => 'GET_MIDDLEWARE_USER_ID', /*...*/],
    ],
    'POST' => [
        '/users' => ['handler' => 'POST_MIDDLEWARE_USER', /*...*/],
        '/users/:id' => ['handler' => 'POST_MIDDLEWARE_USER_PROFILE_TEST', /*...*/],
    ],
];
