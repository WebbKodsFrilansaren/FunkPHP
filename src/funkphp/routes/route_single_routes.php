<?php
// ENTER YOUR SINGLE ROUTES HERE (GET, POST, PUT, DELETE) | MIDDLESWARES ARE IN A SEPARATE FILE
// IMPORTANT: Routes must match here and in middleware in order for them to take effect!
return [
    'GET' => [
        'BEFORE_MATCH' => ['handler' => 'BEFORE_MATCH'],
        '/users' => ['handler' => 'USRES_ROOT_PAGE',],
        '/users/:id' => ['handler' => 'USER_ID_PAGE',],
        '/users/:id/test' => ['handler' => 'USER_ID_PAGE',],
        '/about' => ['handler' => 'ABOUT_PAGE',],
        '/about/test' => ['handler' => 'ABOUT_PAGE',],
    ],
    'POST' => [
        '/' => ['handler' => 'ROOT_post_create_user',],
        '/users' => ['handler' => 'post_create_user',],
        '/users/:id' => ['handler' => 'post_update_user',],
    ],
    'PUT' => [
        '/users/:id' => ['handler' => 'put_update_user',],
    ],
    'DELETE' => [
        '/users/:id' => ['handler' => 'delete_delete_user',],
        '/users' => ['handler' => 'delete_delete_user',],
    ],
];
