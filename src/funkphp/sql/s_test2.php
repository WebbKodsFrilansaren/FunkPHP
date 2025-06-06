<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-07 13:29:25!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function s_test(&$c) // <authors>
{
    // Created in FunkCLI on 2025-06-07 13:29:25! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile s s_test2=>s_test`
    // to get SQL, Hydration & Binded Params in return statement below it!
    $DX = [
        '<CONFIG>' => [
            '[QUERY_TYPE]' => 'SELECT',
            '[TABLES]' => 'authors',
            '[SUBQUERIES]' => [
                '[subquery_example_1]' => 'SELECT COUNT(*)',
                '[subquery_example_2]' => '(WHERE SELECT *)'
            ]
        ],
    ];


    return array([]);
};


function s_test2(&$c) // <authors,comments>
{
    // Created in FunkCLI on 2025-06-07 13:30:01! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile s s_test2=>s_test2`
    // to get SQL, Hydration & Binded Params in return statement below it!
    $DX = [
        '<CONFIG>' => [
            '[QUERY_TYPE]' => 'SELECT',
            '[TABLES]' => 'authors,comments',
            '[SUBQUERIES]' => [
                '[subquery_example_1]' => 'SELECT COUNT(*)',
                '[subquery_example_2]' => '(WHERE SELECT *)'
            ]
        ],
    ];


    return array([]);
};

return function (&$c, $handler = "s_test") {
    $base = is_string($handler) ? $handler : "";
    $full = __NAMESPACE__ . '\\' . $base;
    if (function_exists($full)) {
        return $full($c);
    } else {
        $c['err']['FAILED_TO_RUN_SQL_FUNCTION-' . 's_test2'] = 'SQL function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
        return null;
    }
};
