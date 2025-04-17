<?php  // PAGES_SINGLE_ROUTES.PHP - FunkPHP Framework
return [
    'ROUTES' => [
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
    ]
];
