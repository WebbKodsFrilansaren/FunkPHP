<?php

return [
    'GET' => [
        '/' => ['handler' => 'GET_MIDDLEWARE_ROOT', /*...*/],
        '/users' => ['mHandler' => 'GET_MIDDLEWARE_USER', /*...*/],
        '/users/:id' => ['mHandler' => 'GET_MIDDLEWARE_USER_ID', /*...*/],
    ],
    'POST' => [
        '/users' => ['mHandler' => 'POST_MIDDLEWARE_USER', /*...*/],
        '/users/:id' => ['mHandler' => 'POST_MIDDLEWARE_USER_PROFILE_TEST', /*...*/],
    ],
];
