<?php // TEST FILE! Use to test FunkCLI Commands and Features! Rewrite as needed!
$tests1 = [
    ['route' => 'get/:user',          'expected' => false],
    ['route' => '/:user/:user',       'expected' => false],
    ['route' => '/:user/:user2',      'expected' => true],
    ['route' => '/user-and-123',      'expected' => true],
    ['route' => '/user-_and-123',     'expected' => false],
];

$tests2 = [
    [
        'method' => 'get/',
        'expected' => true,
    ],
    [
        'method' => 'post/',
        'expected' => true,
    ],
    [
        'method' => 'put//',
        'expected' => false,
    ],
    [
        'method' => '/delete/',
        'expected' => false,
    ],
    [
        'method' => 'patch',
        'expected' => false,
    ],
    [
        'method' => 'g/',
        'expected' => true,
    ],

];


$tests3 = [
    ['methodRoute' => 'GET/:user',          'expected' => true],
    ['methodRoute' => 'POST/:user/:user',       'expected' => false],
    ['methodRoute' => 'POST/:user/:user2',      'expected' => true],
    ['methodRoute' => 'PUT/user-and-123',      'expected' => true],
    ['methodRoute' => 'delete/user-_and-123',     'expected' => false],
];

$tests4 = [
    ['route1' => '/users/:id', 'route2' => '/users/:user_id', 'expected' => true],
    ['route1' => '/users/:id/details', 'route2' => '/users/:user_id/details', 'expected' => true],
    ['route1' => '/users/:id', 'route2' => '/users/:id/details', 'expected' => false],
    ['route1' => '/users/:id', 'route2' => '/users/list', 'expected' => false],
];

cli_run_tests("Test 1: ROUTE IS VALID STRING", "cli_route_is_valid_string_VF", $tests1, 'route');
cli_run_tests("Test 2: ROUTE/METHOD IS VALID STRING", "cli_route_method_is_valid_string_VF", $tests2, 'method');
cli_run_tests("Test 3: METHOD/ROUTE IS VALID STRING", "cli_route_and_method_is_valid_string_VF", $tests3, 'methodRoute');
cli_run_tests(
    "Test 4: ROUTE IS SAME AS ANOTHER ROUTE",
    'cli_route_is_same_as_another_route_VF',
    $tests4,
    ['route1', 'route2']
);

echo PHP_EOL;
