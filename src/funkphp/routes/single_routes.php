<?php

return [
    'GET' => [
        '/' => ['handler' => 'handle_root', /*...*/],
        '/users' => ['handler' => 'handle_user_root', /*...*/],
        '/users/:id' => ['handler' => 'get_user_profile', /*...*/],
        '/users/:id/profile' => ['handler' => 'get_user_profile_extended', /*...*/],
        '/users/:id/profile/test' => ['handler' => 'get_user_profile_test_extended', /*...*/],
        '/about' => ['handler' => 'show_about_page', /*...*/],
    ],
    'POST' => [
        '/users' => ['handler' => 'create_user', /*...*/],
        '/users/:id' => ['handler' => 'update_user', /*...*/],
        '/users/:id/profile' => ['handler' => 'update_user_profile', /*...*/],
    ],
    'PUT' => [
        '/users/:id' => ['handler' => 'update_user', /*...*/],
    ],
    'DELETE' => [
        '/users/:id' => ['handler' => 'delete_user', /*...*/],
    ],
];
