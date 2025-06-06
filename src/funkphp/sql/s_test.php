<?php

namespace FunkPHP\SQL\s_test;
// SQL Handler File - Created in FunkCLI on 2025-06-06 02:15:28!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function s_test(&$c) // <>
{
    // Created in FunkCLI on 2025-06-06 02:15:28! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile s s_test=>s_test`
    // to get SQL, Hydration & Binded Params in return statement below it!
    $DX = [
        '<CONFIG>' => [
            '[SUBQUERIES]' => [
                '[subquery1]' => 'SELECT COUNT(*)',
                '[subquery2]' => '(WHERE SELECT *)'
            ]
        ],
        'SELECT' => 'users:id,name',
        'FROM' => 'users',
        'JOINS' => '',
        'WHERE' => 'id = ?',
        'GROUP_BY' => '',
        'ORDER_BY' => '',
        'LIMIT' => '',
        'OFFSET' => '',
        '?_BINDED_PARAMS' => 'i',
        'HYDRATE' => 'table1:cols|table2:cols|table1=>table2',
    ];


    return array(
        "SELECT id AS users_id, name AS users_name FROM users WHERE users_id = ?",
        ["table1:cols", "table2:cols", "table1=>table2"],
        ["i"]
    );
};

return function (&$c, $handler = "s_test") {
    $base = is_string($handler) ? $handler : "";
    $full = __NAMESPACE__ . '\\' . $base;
    if (function_exists($full)) {
        return $full($c);
    } else {
        $c['err']['FAILED_TO_RUN_SQL_FUNCTION-' . 's_test'] = 'SQL function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
        return null;
    }
};
