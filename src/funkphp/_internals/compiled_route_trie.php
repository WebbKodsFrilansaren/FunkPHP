<?php

return [
    'GET' => [
        // Assumes no middleware at root '/' for this example
        'users' => [
            '|' => [], // Middleware applies at /users level (sibling key)
            '#' => [
                '{id}' => [
                    // No middleware specifically at /users/{id}
                    'profile' => [
                        // No middleware specifically at /users/{id}/profile
                        'test' => ["|" => []]
                    ]
                ]
            ],
            'static' => [] // Node exists for /users/static
        ],
        'about' => [] // Node exists for /about
    ]
];
