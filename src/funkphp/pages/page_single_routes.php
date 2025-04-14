<?php // SINGLE ROUTES FOR Pages
// ENTER YOUR SINGLE ROUTES HERE (GET, POST, PUT, DELETE) | MIDDLESWARES ARE IN A SEPARATE FILE
// IMPORTANT: Routes must match here and in middleware in order for them to take effect!
return [
    'GET' => [
        '/users/:id' => ['page' => 'page_file_name_to_template_return',],
        '/users/:id/test' => ['page' => 'page_file_name_to_template_return2',],
    ],
    'POST' => [
        '/users/:id' => ['page' => 'page_file_name_to_template_return3',],
        '/users' => ['page' => 'page_file_name_to_template_return4',],
    ],
    'PUT' => [
        '/users/:id' => ['page' => 'page_file_name_to_template_return4',],
    ],
    'DELETE' => [
        '/users/:id' => ['page' => 'page_file_name_to_template_return4',],
    ],
];
