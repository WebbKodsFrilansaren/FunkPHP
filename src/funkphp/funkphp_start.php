<?php // ENTRY POINT OF EACH HTTPS REQUEST thanks to ".htaccess" file
include_once __DIR__ . '/_internals/functions/_includeAll.php';
include_once __DIR__ . '/dx_steps/_includeAll.php';

//REMOVE AFTER TESTING!
echo "User IP:'" . $req['ip'] . "'<br> ";
echo "User URI:'" . $req['uri'] . "'<br> ";
echo "User Query: " . $req['query'] . "<br> <br>";

// Load configurations and global variables
$fphp_global_config = h_load_config($fphp_all_global_variables_as_strings);
if (!ok($fphp_global_config)) {
    return_code(418);
    exit;
}

// Developer's route definitions
// Middlwares
$developerMiddleRoutes = include __DIR__ . '/routes/middleware_routes.php';

// Singles
$developerSingleRoutes = include __DIR__ . '/routes/single_routes.php';

// Compiled Trie structure where "#" indicates dynamic route and "|" indicates middleware
$compiledTrie = include __DIR__ . '/_internals/compiled_route_trie.php';

// --- Test Cases ---
run_router('GET', '/users/99', $compiledTrie, $developerSingleRoutes);
//run_router('GET', '/users/123/profile/', $compiledTrie, $developerRoutes);
run_router('GET', '/users/123/profile/test', $compiledTrie, $developerSingleRoutes);
// Expected: Matches '/users/{id}/profile/test', Handler: get_user_profile
run_router('GET', '/users/abc', $compiledTrie, $developerRoutes);
// Expected: Matches '/users/{id}', Handler: get_user_profile


// Run the main function to handle the request which is a pipeline of functions
// where each function can also call optional functions to handle the request!
outerFunktionTrain(
    $req,
    $d,
    $p,
    $fphp_global_config,
    [
        "r_match_denied_global_ips" // Deny IPs filtering globally
        => [
            $fphp_global_config['fphp_ips_filtered_globals'],
            $req['ip']
        ],
        "r_match_denied_global_uas" // Deny UAs filtering globally
        =>
        [
            $fphp_global_config['fphp_uas_filtered_globals'],
            $req['ua']
        ],
    ]
);

// This part is only executed if the request was not properly handled by the pipeline!
// Feel free to add your own error handling here and/or easter egg!
echo "YOU SHOULD NOT SEE THIS! SO ERROR!<br>";
// echo '<br>RESPONSE Headers:<br>';

// foreach (headers_list() as $header) {
//     echo $header . '<br>';
// }

// echo '<br>REQUEST Headers:<br>';
// foreach ($_SERVER as $key => $value) {

//     echo $key . ' => ' . $value . '<br>';
// }
