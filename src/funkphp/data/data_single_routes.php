<?php // DATA_SINGLE_ROUTES.PHP - FunkPHP Framework
// ENTER YOUR SINGLE ROUTES HERE (GET, POST, PUT, DELETE) | MIDDLESWARES ARE IN A SEPARATE FILE
// IMPORTANT: Routes must match here and in middleware in order for them to take effect!
//            (unless you are doing it in Middleware after matched Single Route!)
return [
    'GET' => [
        '/users/:id' => ['get' => 'validate_id_get_query_data',],
        '/users/:id/test' => ['get' => 'another_one',],
    ],
    'POST' => [
        '/users/:id' => ['json' => 'post_update_user_from_json_data',],
        '/users' => ['post' => 'post_create_user_from_post_data',],
    ],
    'PUT' => [
        '/users/:id' => ['json' => 'put_update_user_from_json_data',],
    ],
    'DELETE' => [
        '/users/:id' => ['json' => 'delete_delete_user_from_json_data',],
    ],
];
