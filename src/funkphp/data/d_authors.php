<?php

namespace FunkPHP\Data\d_authors;
// Data Handler File - Created in FunkCLI on 2025-07-10 21:34:42!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_by_id(&$c) // <GET/authors/:id>
{
    // FunkCLI created 2025-07-10 21:34:42! Keep Closing Curly Bracket on its
    // own new line without indentation no comment right after it!
    $test = funk_load_sql($c, 's_test2', 's_test5');
};

return function (&$c, $handler = "d_by_id") {
    $base = is_string($handler) ? $handler : "";
    $full = __NAMESPACE__ . '\\' . $base;
    if (function_exists($full)) {
        return $full($c);
    } else {
        $c['err']['DATA'][] = 'Data Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
        return null;
    }
};
