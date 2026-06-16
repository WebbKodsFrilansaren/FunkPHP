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
    ['route1' => '/users/:id', 'route2' => '/users/:user_id-id', 'expected' => true],
    ['route1' => '/users/:id/details', 'route2' => '/users/:user_id/details', 'expected' => true],
    ['route1' => '/users/:id-test', 'route2' => '/users/:id/details', 'expected' => false],
    ['route1' => '/users/:id-test', 'route2' => '/users/list', 'expected' => false],
];

$tests5 = [
    [
        'routes' => [
            '/all' => [],
            '/all2' => [],
            '/all4' => [],
            '/unique-route' => [],
        ],
        'newRoute' => '/unique-route',
        'expected' => false,
    ],
    [
        'routes' => [
            '/all' => [],
            '/:id' => [],
        ],
        'newRoute' => '/:user',
        'expected' => false,
    ],
    [
        'routes' => [
            '/all' => [],
            '/:id' => [],
        ],
        'newRoute' => '/:id/:id2',
        'expected' => true,
    ],
];

$tests6 = [];

// cli_run_tests("Test 1: ROUTE IS VALID STRING", "cli_route_is_valid_string_VF", $tests1, 'route');
// cli_run_tests("Test 2: ROUTE/METHOD IS VALID STRING", "cli_route_method_is_valid_string_VF", $tests2, 'method');
// cli_run_tests("Test 3: METHOD/ROUTE IS VALID STRING", "cli_route_and_method_is_valid_string_VF", $tests3, 'methodRoute');
// cli_run_tests(
//     "Test 4: ROUTE IS SAME AS ANOTHER ROUTE",
//     'cli_route_is_same_as_another_route_VF',
//     $tests4,
//     ['route1', 'route2']
// );
// cli_run_tests("Test 5: NEW ROUTE IS UNIQUE IN ITS METHOD GROUP IN ROUTES", "cli_new_route_is_unique_in_its_method_group_VF", $tests5, ['routes', 'newRoute']);

// Mock data mimicking your exact production "source of truth" structure
$mockDeveloperRoutes = [

    // Pure parameter routes
    '/:users'                                   => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:users/:id'                               => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:users/:id/:profile'                      => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:users/:id/:profile/:image'               => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:team/:me/:prt/:task/:cot'    => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Pure static routes
    '/users'                                    => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/list'                               => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/mist'                               => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/list/all'                           => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/list/all/active'                    => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/admin/settings/system/cache'              => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Mixed routes
    '/users/:id'                                => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/:id/profile'                        => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/:id/profile/:image'                 => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/id/profile/:image'                  => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/:id/profile/image'                  => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/id/:profile/image'                  => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Static beginning, param ending
    '/blog/article/:slug'                       => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/blog/article/:slug/comments'              => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/blog/article/:slug/comments/:commentId'   => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Param beginning, static ending
    '/:locale/docs'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:locale/docs/install'                     => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:locale/docs/install/php'                 => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Alternating static/param
    '/shop/:category/products/:productId'       => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/shop/electronics/products/:productId'     => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/shop/:category/products/featured'         => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Deep routes
    '/a/b/c/d/e/f/g'                            => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/a/:b/c/:d/e/:f/g'                         => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:a/:b/:c/:d/:e/:f/:g'                     => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // API-like routes
    '/api/v1/users'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id'                         => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts'                   => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts/:postId'           => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts/:postId/comments'  => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Routes that normalize to same shape
    '/:user/:id'                                => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:member/:profileId'                       => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:author/:article/:comment'                => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Very short routes
    '/'                 => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/a'                                        => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:a'                                       => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Very deep route
    '/company/:companyId/departments/:departmentId/teams/:teamId/members/:memberId/tasks/:taskId'
    => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Your originals
    '/users/id/profile/image'                   => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/short/static'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],

];

$sortedResult = cli_prepare_binary_specificity_score($mockDeveloperRoutes);

// Visualizing the sorted compilation queue
echo sprintf(
    "%-100s | %-10s | %-11s | %-12s\n",
    "ROUTE PATH",
    "SEGMENTS",
    "BINARY MASK",
    "SPEC_SCORE"
);
echo str_repeat("-", 140) . "\n";

foreach ($sortedResult as $r) {
    echo sprintf(
        "%-100s | %-10d | %-11s | %-12d\n",
        $r['original_route'],
        $r['segment_count'],
        $r['binary_mask'],
        $r['binary_score']
    );
}


echo "All tests completed!" . PHP_EOL;
exit;
