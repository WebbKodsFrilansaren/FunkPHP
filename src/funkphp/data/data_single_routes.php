<?php // DATA_SINGLE_ROUTES.PHP - FunkPHP Framework
return [
    'ROUTES' =>
    [
        'GET' => [
            '/users/:id' => ['handler' => 'validate_id_get_query_data',],
            '/users/:id/test' => ['handler' => 'another_one',],
        ],
        'POST' => [
            '/users/:id' => ['handler' => 'post_update_user_from_json_data',],
            '/users' => ['handler' => 'post_create_user_from_post_data',],
        ],
        'PUT' => [
            '/users/:id' => ['handler' => 'put_update_user_from_json_data',],
        ],
        'DELETE' => [
            '/users/:id' => ['handler' => 'delete_delete_user_from_json_data',],
        ],
    ]
];
