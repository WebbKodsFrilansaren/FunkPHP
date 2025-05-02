<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-02 14:19:43
return  [
    'ROUTES' =>
    [
        'GET' =>
        [
            '/test' =>
            [
                'handler' =>
                [
                    'users2' => 'users4',
                ],
            ],
            '/users' =>
            [
                'handler' => 'users',
            ],
            '/users/:id' =>
            [
                'handler' =>
                [
                    'users' => 'by_id',
                ],
            ],
        ],
        'POST' =>
        [],
        'PUT' =>
        [],
        'DELETE' =>
        [],
    ],
];
