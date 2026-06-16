<?php // TEST FILE! Use to test FunkCLI Commands and Features! Rewrite as needed!

// Mock data mimicking your exact production "source of truth" structure
$mockDeveloperRoutes = [
    // API-like routes
    '/api'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/:posts'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/posts/:id'                             => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/posts/:id/comments'                 => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id'                         => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts'                   => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts/:postId'           => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/api/v1/users/:id/posts/:postId/comments'  => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    // Routes that normalize to same shape
    '/:user/:id'                                => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:member/profileId'                       => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:author/:article/:comment'                => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    // Very short routes
    '/'                 => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/aa'                                        => ['config' => [], 'middlewares' => [], 'pipeline' => []],
    '/:a'                                       => ['config' => [], 'middlewares' => [], 'pipeline' => []],
];

$sortedResult = cli_prepare_binary_specificity_score_VF($mockDeveloperRoutes, "GET");
$ASTresult    = cli_build_flattened_routing_start_VF($sortedResult, "GET");

// Run through the wrapper
$compiledPHPCode = cli_compile_router_file_VF($ASTresult, "GET");

echo $compiledPHPCode;


// Visualizing the sorted compilation queue
// echo sprintf(
//     "%-100s | %-10s | %-11s | %-12s\n",
//     "ROUTE PATH",
//     "SEGMENTS",
//     "BINARY MASK",
//     "SPEC_SCORE"
// );
// echo str_repeat("-", 140) . "\n";

// foreach ($sortedResult as $r) {
//     echo sprintf(
//         "%-100s | %-10d | %-11s | %-12d\n",
//         $r['original_route'],
//         $r['segment_count'],
//         $r['binary_mask'],
//         $r['binary_score']
//     );
// }

echo "All tests completed!" . PHP_EOL;
exit;
