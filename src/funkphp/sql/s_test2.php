<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-08 08:56:57!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function s_test2(&$c) // <authors,comments>
{
    // Created in FunkCLI on 2025-06-08 08:56:57! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile s s_test2=>s_test2`
    // to get SQL, Hydration & Binded Params in return statement below it!
    $DX = [
        '<CONFIG>' => [
            '<QUERY_TYPE>' => 'SELECT',
            '<TABLES>' => 'authors,comments',
            '[SUBQUERIES]' => [
                '[subquery_example_1]' => 'SELECT COUNT(*)',
                '[subquery_example_2]' => '(WHERE SELECT *)'
            ]
        ],
    ];


    return array([]);
};



function s_test3(&$c) // <comments>
{
    // Created in FunkCLI on 2025-06-09 07:00:55! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile s s_test2=>s_test3`
    // to get SQL, Hydration & Binded Params in return statement below it!
    $DX = [
        '<CONFIG>' => [
            '<QUERY_TYPE>' => 'INSERT',
            '<TABLES>' => ["comments"],
            '[SUBQUERIES]' => [
                '[subquery_example_1]' => 'SELECT COUNT(*)',
                '[subquery_example_2]' => '(WHERE SELECT *)'
            ]
        ],
        'INSERT_INTO' => 'comments:test_number_that_is_unsigned,test_number_that_is_signed,article_id,content,author_id,comment_status,comment_type,created_at',
        // Leave 'VALUES' empty or NULL unless you wanna set hardcoded values. Otherwise, split on:`|` (for example: `table_column1 = numericValue|table_column2 = "stringValue"`)!
        'VALUES' => '',
    ];


    return array(
        'sql' => '',
        'hydrate' =>
        array(),
        'bind_params' =>
        array(),
    );
};

return function (&$c, $handler = "s_test2") {
    $base = is_string($handler) ? $handler : "";
    $full = __NAMESPACE__ . '\\' . $base;
    if (function_exists($full)) {
        return $full($c);
    } else {
        $c['err']['FAILED_TO_RUN_SQL_FUNCTION-' . 's_test2'] = 'SQL function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
        return null;
    }
};
