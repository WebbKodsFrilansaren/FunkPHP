<?php
// Data Handler File - Created in FunkCLI on 2025-05-24 08:52:25!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test(&$c) // <POST/test/:id>
{
    // Created in FunkCLI on 2025-05-24 08:52:25! Keep "};" on its
    // own new line without indentation no comment right after it!
    $v_file = funk_use_validation_get_valid_validation_or_err_out($c, "v_test");
    var_dump($v_file);
    echo "Hello from d_test! (Data Handler)";
};

return function (&$c, $handler = "d_test") {
    return $handler($c);
};
