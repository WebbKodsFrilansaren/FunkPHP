<?php
// This file was recreated by FunkCLI!
return [
    'ROUTES' =>
    [
        'GET' => [
            "/" => [
                'handler' => 'test',
                //'middlewares' => "test",
            ],
        ],
        'POST' => [
            '/users' => [
                'handler' => 'test',
                'middlewares' => "test3",
            ],
            '/users/:test' => [
                'handler' => 'test2',
                'middlewares' => "test2",
            ],
        ],
        'PUT' => [],
        'DELETE' => [],
    ]
];
