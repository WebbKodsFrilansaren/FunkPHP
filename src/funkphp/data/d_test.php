<?php
// Data Handler File - Created in FunkCLI on 2025-05-24 08:52:25!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test(&$c) // <POST/test/:id>
{
    // Created in FunkCLI on 2025-05-24 08:52:25! Keep "};" on its
    // own new line without indentation no comment right after it!
    $v_file = funk_use_validation_get_validation_array_or_err_out($c, "v_test");
    $validatePOST = funk_use_validation($c, $v_file, "JSON");
    $test = ['v' => $validatePOST];
    dj($test);
    var_dump($c['err']);
    var_dump($c['v_ok']);
};

return function (&$c, $handler = "d_test") {
    return $handler($c);
};
