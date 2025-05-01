<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-01 19:48:46
return  [
    'ROUTES' =>
    [
        'GET' =>
        [
            '/' =>
            [
                'handler' => 'test',
            ],
            '/test' =>
            [
                'handler' => 't',
                'middlewares' => 'test3',
            ],
            '/test/2' =>
            [
                'handler' => 't_1',
                'middlewares' => 'test2',
            ],
        ],
        'POST' =>
        [
            '/users' =>
            [
                'handler' => 'test',
                'middlewares' => 'test3',
            ],
            '/users/:test' =>
            [
                'handler' => 'test2',
                'middlewares' => 'test2',
            ],
        ],
        'PUT' =>
        [],
        'DELETE' =>
        [
            '/test' =>
            [
                'handler' => 't',
            ],
            '/test2' =>
            [
                'handler' =>
                [
                    't' => 't2',
                ],
            ],
        ],
    ],
];
