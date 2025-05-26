<?php
// Validation Handler File - Created in FunkCLI on 2025-05-24 08:52:30!
// Write your Validation Rules in the
// $DX variable and then run the command
// `php funkcli compile v v_test=>$function_name`
// to get the optimized version below it!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function v_test(&$c) // <POST/test/:id>
{

    // Created in FunkCLI on 2025-05-24 08:52:30! Keep "};" on its
    // own new line without indentation no comment right after it!
    // Run the command `php funkcli compile v v_test=>v_test`
    // to get optimized version in return statement below it!
    $DX = [
        'user' => 'required|string|between:1,5',
        'age' => 'required|integer|between:1,35|digits:2',
        'bio_gender' => 'char:m,f|required'
    ];

    return array(
        'user' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'string' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'between' =>
                array(
                    'value' =>
                    array(
                        0 => 1,
                        1 => 5,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'age' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'integer' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'between' =>
                array(
                    'value' =>
                    array(
                        0 => 1,
                        1 => 35,
                    ),
                    'err_msg' => NULL,
                ),
                'digits' =>
                array(
                    'value' => 2,
                    'err_msg' => NULL,
                ),
            ),
        ),
        'bio_gender' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'char' =>
                array(
                    'value' =>
                    array(
                        0 => 'm',
                        1 => 'f',
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    return $handler($c);
};
