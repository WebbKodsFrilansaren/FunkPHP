<?php

return [
    'GET' => [
        '/users' => ['handler' => 'MIDDLEWARE_USER', /*...*/],
        '/users/{id}/profile/test' => ['handler' => 'MIDDLEWARE_USER_PROFILE_TEST', /*...*/],
    ]
];
