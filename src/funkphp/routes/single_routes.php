<?php

return [
    'GET' => [
        '/' => ['handler' => 'HOME_PAGE', /*...*/],
        '/users' => ['handler' => 'USERS_PAGE', /*...*/],
        '/users/:id' => ['handler' => 'USER_ID_PAGE', /*...*/],
        '/about/test' => ['handler' => 'ABOUT_PAGE', /*...*/],
    ],
    'POST' => [
        '/' => ['handler' => 'ROOT_post_create_user', /*...*/],
        '/users' => ['handler' => 'post_create_user', /*...*/],
        '/users/:id' => ['handler' => 'post_update_user', /*...*/],
    ],
    'PUT' => [
        '/usersPUT/:id' => ['handler' => 'put_update_user', /*...*/],
    ],
    'DELETE' => [
        '/usersDELETE/:id' => ['handler' => 'delete_delete_user', /*...*/],
    ],
];
