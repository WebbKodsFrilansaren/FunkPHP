<?php $mockDeveloperRoutes = [

    // Pure parameter routes
    '/:users' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:users/:id' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:users/:id/:profile' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:users/:id/:profile/:image' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:team/:member/:project/:task/:comment' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Pure static routes
    '/users' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/list' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/list/all' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/list/all/active' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/admin/settings/system/cache' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Mixed routes
    '/users/:id' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/:id/profile' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/:id/profile/:image' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/id/profile/:image' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/:id/profile/image' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/users/id/:profile/image' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Static beginning, param ending
    '/blog/article/:slug' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/blog/article/:slug/comments' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/blog/article/:slug/comments/:commentId' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Param beginning, static ending
    '/:locale/docs' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:locale/docs/install' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:locale/docs/install/php' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Alternating static/param
    '/shop/:category/products/:productId' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/shop/electronics/products/:productId' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/shop/:category/products/featured' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Deep routes
    '/a/b/c/d/e/f/g' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/a/:b/c/:d/e/:f/g' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:a/:b/:c/:d/:e/:f/:g' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // API-like routes
    '/api/v1/users' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts/:postId' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts/:postId/comments' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Routes that normalize to same shape
    '/:user/:id' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:member/:profileId' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:author/:article/:comment' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Very short routes
    '/' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/a' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:a' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Very deep route
    '/company/:companyId/departments/:departmentId/teams/:teamId/members/:memberId/tasks/:taskId'
    => ['config' => [], 'middlewares' => [], 'pipeline' => []],

    // Your originals
    '/users/id/profile/image' => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/short/static' => ['config' => [], 'middlewares' => [], 'pipeline' => []],

];

$sortedResult = cli_prepare_binary_specificity_score2($mockDeveloperRoutes);

// Visualizing the sorted compilation queue
echo sprintf(
    "%-30s | %-100s | %-11s | %-12s\n",
    "ROUTE PATH",
    "SEGMENTS",
    "BINARY MASK",
    "SPEC_SCORE"
);
echo str_repeat("-", 140) . "\n";

foreach ($sortedResult as $r) {
    echo sprintf(
        "%-30s | %-10d | %-11s | %-12d\n",
        $r['original_route'],
        $r['segment_count'],
        $r['binary_mask'],
        $r['binary_score']
    );
}

exit;
