<?php
// Data Handler File - Created in FunkCLI on 2025-05-24 08:52:25!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test2(&$c) // <POST/test/:id>
{
    // Created in FunkCLI on 2025-05-24 08:52:25! Keep "};" on its
    // own new line without indentation no comment right after it!
    $v_file = funk_load_validation_file($c, "v_test=>v_test3");
    $validatePOST = funk_use_validation($c, $v_file($c, "v_test3"), "JSON");


    $test = [
        'v' => $c['v'],
        'v_ok' => $c['v_ok'],
        'v_data' => $c['v_data'],
        'v_config' => $c['v_config'],
        'err' => $c['err'],
    ];

    dj($test);
};



return function (&$c, $handler = "d_test") {
    return $handler($c);
};
