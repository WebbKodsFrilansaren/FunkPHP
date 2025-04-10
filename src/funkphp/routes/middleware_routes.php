<?php

return [
    'GET' => [
        '/' => ['handler' => 'GET_CHECK_CONTENT_TYPE', /*...*/],
        '/users' => ['handler' => ['GET_VALIDATE_USER_IP', 'GET_VALIDATE_USER_AU'], /*...*/],
        '/users/:id' => ['handler' => 'GET_MIDDLEWARE_USER_ID', /*...*/],
    ],
    'POST' => [
        '/users' => ['handler' => 'POST_MIDDLEWARE_USER', /*...*/],
        '/users/:id' => ['handler' => 'POST_MIDDLEWARE_USER_PROFILE_TEST', /*...*/],
    ],
];
