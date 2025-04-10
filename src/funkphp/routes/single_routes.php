<?php

return [
    'GET' => [
        '/' => ['handler' => 'handle_root', /*...*/],
        '/users' => ['handler' => 'handle_user_root', /*...*/],
        '/users/{id}' => ['handler' => 'get_user_profile', /*...*/],
        '/users/{id}/profile' => ['handler' => 'get_user_profile_extended', /*...*/],
        '/users/{id}/profile/test' => ['handler' => 'get_user_profile_test_extended', /*...*/],
        '/about' => ['handler' => 'show_about_page', /*...*/],
        '/users/static' => ['handler' => 'show_static_user_page', /*...*/], // Added for testing
    ],
    'POST' => [
        '/users/{id}/profile' => ['handler' => 'update_user_profile', /*...*/],
        '/users/{id}/profile/test' => ['handler' => 'update_user_profile_test', /*...*/],
    ],
    'PUT' => [
        '/users/{id}/profile' => ['handler' => 'update_user_profile', /*...*/],
        '/users/{id}/profile/test' => ['handler' => 'update_user_profile_test', /*...*/],
    ],
    'DELETE' => [
        '/users/{id}' => ['handler' => 'delete_user_profile', /*...*/],
        '/users/{id}/profile' => ['handler' => 'delete_user_profile_test', /*...*/],
    ],
];
