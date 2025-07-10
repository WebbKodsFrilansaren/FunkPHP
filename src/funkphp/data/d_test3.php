<?php

namespace FunkPHP\Data\d_test3;
// Data Handler File - Created in FunkCLI on 2025-05-30 22:34:02!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test3(&$c) // <GET/test2>
{
    $test = funk_load_sql($c, "s_test2", "s_test5");
    var_dump($c['err']);
    //ddj($c['err']);
    // Created in FunkCLI on 2025-05-30 22:34:02! Keep "};" on its
    // own new line without indentation no comment right after it!
    //$v_test = funk_use_validation($c, "v_test", "v_test2", "GET");

    // $test = [
    //     'v' => $c['v'],
    //     'v_ok' => $c['v_ok'],
    //     'v_data' => $c['v_data'],
    //     'v_config' => $c['v_config'],
    //     'err' => $c['err'],
    // ];

    // ddj($test);
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
