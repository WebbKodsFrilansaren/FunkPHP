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
        'firstname' => 'exact:5|required|list:test|regex:/^[a-zA-Z]+$/("Invalid regex for firstname!")',
        'lastname' => 'min:2|max:2|required|float|decimals:0,20',
        '*.interests' => 'string|min:3|max:20|required|nullable',
        '*.tags' => 'string|min:3|max:10|required|nullable',
    ];

    return array(
        'firstname' =>
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
                'exact' =>
                array(
                    'value' => 5,
                    'err_msg' => NULL,
                ),
                'regex' =>
                array(
                    'value' => '/^[a-zA-Z]+$/',
                    'err_msg' => 'Invalid regex for firstname!',
                ),
            ),
        ),
        'lastname' =>
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
                'min' =>
                array(
                    'value' => 2,
                    'err_msg' => NULL,
                ),
                'max' =>
                array(
                    'value' => 2,
                    'err_msg' => NULL,
                ),
                'decimals' =>
                array(
                    'value' =>
                    array(
                        0 => 0,
                        1 => 20,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        '*' =>
        array(
            'interests' =>
            array(
                '<RULES>' =>
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
                    'string' =>
                    array(
                        'value' => NULL,
                        'err_msg' => NULL,
                    ),
                    'min' =>
                    array(
                        'value' => 3,
                        'err_msg' => NULL,
                    ),
                    'max' =>
                    array(
                        'value' => 20,
                        'err_msg' => NULL,
                    ),
                ),
            ),
            'tags' =>
            array(
                '<RULES>' =>
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
                    'string' =>
                    array(
                        'value' => NULL,
                        'err_msg' => NULL,
                    ),
                    'min' =>
                    array(
                        'value' => 3,
                        'err_msg' => NULL,
                    ),
                    'max' =>
                    array(
                        'value' => 10,
                        'err_msg' => NULL,
                    ),
                ),
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    return $handler($c);
};
