<?php // ROUTE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-05-01 20:01:02
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
                'handler' => ['t1' => 't1'],
                'data' => ['dataTest' => 'dataTest2'],
                'page' => 'pageTest',
            ],
            '/test/2' =>
            [
                'handler' => 't1',
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
            '/test/:id/:id2' =>
            [
                'handler' =>
                [
                    't' => 't33',
                ],
            ],
            '/test/:id/:id32' =>
            [
                'handler' =>
                [
                    't' => 't334',
                ],
            ],
            '/test/:id/:id342' =>
            [
                'handler' =>
                [
                    't' => 't3344',
                ],
            ],
            '/test2' =>
            [
                'handler' =>
                [
                    't' => 't2',
                ],
            ],
            '/test3' =>
            [
                'handler' =>
                [
                    't' => 't2',
                ],
            ],
            '/test5' =>
            [
                'handler' =>
                [
                    't' => 't3',
                ],
            ],
        ],
    ],
];
