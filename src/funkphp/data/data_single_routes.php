<?php // DATA_SINGLE_ROUTES.PHP - FunkPHP Framework
// ENTER YOUR _MATCHING_ SINGLE ROUTES HERE (GET, POST, PUT, DELETE) | MIDDLESWARES ARE IN A SEPARATE FILE
// IMPORTANT: Routes must match here and in middleware in order for them to take effect!
//            (unless you are doing it in Middleware after matched Single Route!)
//
// $c['req']['matched_params'] contain the :dynamic_parts from the matched route!
//
// The handlers should be: 'get' ($_GET), 'post' ($_POST),  'json' (php://input) OR 'files' ($_FILES)
// what function that should be called when the route is matched. Alternatively, you can
// use handler syntax such as: 'get|post|json' -> 'validate' => [*validation_syntax_here_as_an_array*]
// to validate the data which is then saved directly to your $c['d'] array!
//
// IMPORTANT: After validation or your own custom handler function, the remaining
// variables inside of $_GET, $_POST, $_FILES & php://input will be deleted for safety reasons!
//
return [
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
];
