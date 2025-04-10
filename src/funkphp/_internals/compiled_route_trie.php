<?php

return [
    'GET' => [ // This is "/" level
        'users' => [
            '|' => ['|' => []], // Middleware applies at /users level (sibling key)
            ':' => [
                'id' => [
                    '|' => [],
                ],
            ],
        ],
        'test' => [],
    ],
    'POST' => [
        'users' => [
            '|' => [], // Middleware applies at /users level (sibling key)
            ':' => [
                'id' => [
                    "|" => [],
                ],
            ],
        ],
    ],
];
