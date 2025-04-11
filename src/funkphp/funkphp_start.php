<?php // ENTRY POINT OF EACH HTTPS REQUEST thanks to ".htaccess" file
include_once __DIR__ . '/_internals/functions/_includeAll.php';
include_once __DIR__ . '/dx_steps/_includeAll.php';

//REMOVE AFTER TESTING!
// echo "User IP:'" . $req['ip'] . "'<br> ";
// echo "User URI:'" . $req['uri'] . "'<br> ";
// echo "User Query: " . $req['query'] . "<br> <br>";

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

echo "Converted: " . r_convert_array_to_simple_syntax($compiledTrie) . "<br>";
echo "<br>";


// --- Test Cases ---
echo json_encode(r_match_developer_route($req['method'], $req['uri'], $compiledTrie, $developerSingleRoutes, $developerMiddleRoutes), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Run the main function to handle the request which is a pipeline of functions
// where each function can also call optional functions to handle the request!
// outerFunktionTrain(
//     $req,
//     $d,
//     $p,
//     $fphp_global_config,
//     [
//         "r_match_denied_global_ips" // Deny IPs filtering globally
//         => [
//             $fphp_global_config['fphp_ips_filtered_globals'],
//             $req['ip']
//         ],
//         "r_match_denied_global_uas" // Deny UAs filtering globally
//         =>
//         [
//             $fphp_global_config['fphp_uas_filtered_globals'],
//             $req['ua']
//         ],
//     ]
// );

// This part is only executed if the request was not properly handled by the pipeline!
// Feel free to add your own error handling here and/or easter egg!
echo "<br>YOU SHOULD NOT SEE THIS! SO ERROR!<br>";
