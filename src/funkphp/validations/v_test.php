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
        'user' => 'array|required|count:5|count:6|nullable',
        'age' => 'number|min:5|nullable',
    ];

    return array(
        '<DX_KEYS>' =>
        array(
            'user' => 0,
            'age' => 1,
        ),
        'user' =>
        array(
            'nullable' =>
            array(
                'value' => NULL,
                'err_msg' => NULL,
            ),
            'required' =>
            array(
                'value' => NULL,
                'err_msg' => NULL,
            ),
            'array' =>
            array(
                'value' => NULL,
                'err_msg' => NULL,
            ),
            'count' =>
            array(
                'value' => 6,
                'err_msg' => NULL,
            ),
        ),
        'age' =>
        array(
            'nullable' =>
            array(
                'value' => NULL,
                'err_msg' => NULL,
            ),
            'number' =>
            array(
                'value' => NULL,
                'err_msg' => NULL,
            ),
            'min' =>
            array(
                'value' => 5,
                'err_msg' => NULL,
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    $handler($c);
};
