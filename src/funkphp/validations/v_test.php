<?php
// Validation Handler File - Created in FunkCLI on 2025-05-23 05:11:13!
// Write your Validation Rules in the
// $DX variable and then run the command
// `php funkcli compile v v_test=>$function_name`
// to get the optimized version below it!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function v_test(&$c) // <POST/test/:id>
{

    // Created in FunkCLI on 2025-05-23 05:11:13! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile v v_test=>v_test`
    // to get optimized version in return statement below it!
    $DX = [
        'test_name' => 'required|string|min:3|max:50',
        'test_age' => 'required|integer|min:18|max:120|max_digits:3|min_digits:2',
        'test.email' => 'email|required|min:6|max:100',
    ];


    return array([]);
};

return function (&$c, $handler = "v_test") {
    $handler($c);
};
