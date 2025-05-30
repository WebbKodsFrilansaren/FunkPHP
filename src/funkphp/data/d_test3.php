<?php

namespace FunkPHP\Data\d_test3;
// Data Handler File - Created in FunkCLI on 2025-05-30 22:34:02!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test3(&$c) // <GET/test2>
{
    // Created in FunkCLI on 2025-05-30 22:34:02! Keep "};" on its
    // own new line without indentation no comment right after it!
    $v_file = funk_load_validation_file($c, "v_test");
    $validatePOST = funk_use_validation($c, $v_file($c, "v_test2"), "GET");

    $test = [
        'v' => $c['v'],
        'v_ok' => $c['v_ok'],
        'v_data' => $c['v_data'],
        'v_config' => $c['v_config'],
        'err' => $c['err'],
    ];

    ddj($test);
};

return function (&$c, $handler = "d_test3") {
    $base = is_string($handler) ? $handler : "";
    $full = __NAMESPACE__ . '\\' . $base;
    if (function_exists($full)) {
        return $full($c);
    } else {
        $c['err']['FAILED_TO_RUN_DATA_FUNCTION-d_test3'] = 'Data Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
        return null;
    }
};
