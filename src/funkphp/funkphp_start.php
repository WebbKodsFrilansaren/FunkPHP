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


// --- Example Usage (demonstrating the full flow) ---

// Developer's route definitions
$developerRoutes = [
    'GET' => [
        '/' => ['handler' => 'handle_root', /*...*/],
        '/users' => ['handler' => 'handle_user_root', /*...*/],
        '/users/{id}' => ['handler' => 'get_user_profile', /*...*/],
        '/users/{id}/profile' => ['handler' => 'get_user_profile_extended', /*...*/],
        '/users/{id}/profile/test' => ['handler' => 'get_user_profile_test_extended', /*...*/],
        '/about' => ['handler' => 'show_about_page', /*...*/],
        '/users/static' => ['handler' => 'show_static_user_page', /*...*/], // Added for testing
    ]
];

// Compiled Trie structure (without __END__)
$compiledTrie = [
    'GET' => [
        'users' => [
            '#' => [
                '{id}' => [
                    'profile' => ['test' => []] // Node exists, signifying structure
                ]
            ],
            'static' => [] // Node exists for /users/static
        ],
        'about' => [] // Node exists for /about
    ]
];


// --- Test Cases ---
run_router('GET', '/', $compiledTrie, $developerRoutes);
// Expected: Matches '/', Handler: handle_root

run_router('GET', '/users', $compiledTrie, $developerRoutes);
// Expected: 404 (Path structure exists but not defined as endpoint: /users)
//       	(Assuming /users endpoint isn't in $developerRoutes['GET'])

run_router('GET', '/users/99', $compiledTrie, $developerRoutes);
// Expected: Matches '/users/{id}', Handler: get_user_profile

// run_router('GET', '/users/abc', $compiledTrie, $developerRoutes);
// // Expected: Matches '/users/{id}', Handler: get_user_profile

// run_router('GET', '/users/123/profile', $compiledTrie, $developerRoutes);
// // Expected: Matches '/users/{id}/profile', Handler: get_user_profile_extended

run_router('GET', '/users/123/profile/', $compiledTrie, $developerRoutes);
run_router('GET', '/users/123/profile/test', $compiledTrie, $developerRoutes);
// // Expected: Matches '/users/{id}/profile', Handler: get_user_profile_extended (trailing / trimmed)

run_router('GET', '/users/123/settings', $compiledTrie, $developerRoutes);
// // Expected: 404 (Path structure mismatch... 'settings' not found after {id})

// run_router('GET', '/users/123/profile/extra', $compiledTrie, $developerRoutes);
// // Expected: 404 (Path structure mismatch... 'extra' not found after profile)

// run_router('GET', '/about', $compiledTrie, $developerRoutes);
// // Expected: Matches '/about', Handler: show_about_page

// run_router('GET', '/contact', $compiledTrie, $developerRoutes);
// // Expected: 404 (Path structure mismatch... 'contact' not found)

run_router('GET', '/users/static', $compiledTrie, $developerRoutes);
// // Expected: Matches '/users/static', Handler: show_static_user_page

// run_router('POST', '/users', $compiledTrie, $developerRoutes);
// // Expected: Method POST not supported... (or would match if defined)




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
