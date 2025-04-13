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
// Middlwares & Singles
$developerMiddleRoutes = include __DIR__ . '/routes/middleware_routes.php';
$developerSingleRoutes = include __DIR__ . '/routes/single_routes.php';

// Built & compiled trie routes ("troute")
//$compiledRoutes =  r_build_compiled_routes($developerSingleRoutes, $developerMiddleRoutes);
//r_output_compiled_routes($compiledRoutes, "troute");

// Imported compiled trie routes ("troute")
$compiledTrie = include __DIR__ . '/_internals/compiled/troute.php';

$uaTests = include __DIR__ . '/tests/tests_uas.php';

foreach ($uaTests as $uaTest) {
    r_match_denied_uas($uaTest);
}

foreach ($uaTests as $uaTest) {
    r_match_denied_uas_simple($uaTest);
}

// --- Test Cases ---
//echo json_encode(r_match_developer_route($req['method'], $req['uri'], $compiledTrie, $developerSingleRoutes, $developerMiddleRoutes), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


// This part is only executed if the request was not properly handled by the pipeline!
// Feel free to add your own error handling here and/or easter egg!
//echo "<br>YOU SHOULD NOT SEE THIS! SO ERROR!<br>";
