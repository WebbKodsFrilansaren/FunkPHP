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
        'array_test' => 'array|size:1|required',
        'string_test' => 'string|between:1,3|required',
        'int_test' => 'integer|between:1,3|required',
        'float_test' => 'float|between:1,3|required',
        'password_custom_test' => 'password_custom:test|between:12,15|required',
        'password_test' => 'password:1,1,1,1|between:12,15|required',
        "password_confirm_test" => 'password_confirm:password_custom_test|required',
    ];

    return array(
        'array_test' =>
        array(
            '<RULES>' =>
            array(
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
                'size' =>
                array(
                    'value' => 1,
                    'err_msg' => NULL,
                ),
            ),
        ),
        'string_test' =>
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
                        1 => 3,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'int_test' =>
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
                        1 => 3,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'float_test' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'float' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'between' =>
                array(
                    'value' =>
                    array(
                        0 => 1,
                        1 => 3,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'password_custom_test' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'password_custom' =>
                array(
                    'value' => 'test',
                    'err_msg' => NULL,
                ),
                'between' =>
                array(
                    'value' =>
                    array(
                        0 => 12,
                        1 => 15,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'password_test' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'password' =>
                array(
                    'value' =>
                    array(
                        0 => 1,
                        1 => 1,
                        2 => 1,
                        3 => 1,
                    ),
                    'err_msg' => NULL,
                ),
                'between' =>
                array(
                    'value' =>
                    array(
                        0 => 12,
                        1 => 15,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'password_confirm_test' =>
        array(
            '<RULES>' =>
            array(
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'password_confirm' =>
                array(
                    'value' => 'password_custom_test',
                    'err_msg' => NULL,
                ),
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    return $handler($c);
};
