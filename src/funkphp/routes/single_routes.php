<?php

return [
    'GET' => [
        '/' => ['handler' => 'ROOT_PAGE', /*...*/],
        '/users' => ['handler' => 'USERS_PAGE', /*...*/],
        '/users/:id' => ['handler' => 'USER_ID_PAGE', /*...*/],
        '/about' => ['handler' => 'ABOUT_PAGE', /*...*/],
    ],
    'POST' => [
        '/users' => ['handler' => 'post_create_user', /*...*/],
        '/users/:id' => ['handler' => 'post_update_user', /*...*/],
    ],
    'PUT' => [
        '/users/:id' => ['handler' => 'put_update_user', /*...*/],
    ],
    'DELETE' => [
        '/users/:id' => ['handler' => 'delete_delete_user', /*...*/],
    ],
];
