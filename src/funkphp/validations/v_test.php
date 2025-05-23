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
        'user' => 'array|required|count:5',
        'user.*.name' => [
            'min:3',
            'max:50',
            'string',
            'required("Name is required!")',
            'nullable',
        ],
        'user.*.age' => 'min:18|max:120|max_digits:3|min_digits:2|required|integer("Age must be a number!")',
        'user.*.email' => 'email|required|min:6("Email must be at least six(6) characters long!")|max:100',
    ];

    return array(
        '<DX_KEYS>' =>
        array(
            'user' => 0,
            'user.*.name' => 1,
            'user.*.age' => 2,
            'user.*.email' => 3,
        ),
        'user' =>
        array(
            '<RULES>' =>
            array(
                'array' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'required' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'count' =>
                array(
                    'value' => 5,
                    'err_msg' => NULL,
                ),
            ),
            '*' =>
            array(
                'name' =>
                array(
                    '<RULES>' =>
                    array(
                        'min' =>
                        array(
                            'value' => 3,
                            'err_msg' => NULL,
                        ),
                        'max' =>
                        array(
                            'value' => 50,
                            'err_msg' => NULL,
                        ),
                        'string' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                        'required' =>
                        array(
                            'value' => NULL,
                            'err_msg' => 'Name is required!',
                        ),
                        'nullable' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                    ),
                ),
                'age' =>
                array(
                    '<RULES>' =>
                    array(
                        'min' =>
                        array(
                            'value' => 18,
                            'err_msg' => NULL,
                        ),
                        'max' =>
                        array(
                            'value' => 120,
                            'err_msg' => NULL,
                        ),
                        'max_digits' =>
                        array(
                            'value' => 3,
                            'err_msg' => NULL,
                        ),
                        'min_digits' =>
                        array(
                            'value' => 2,
                            'err_msg' => NULL,
                        ),
                        'required' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                        'integer' =>
                        array(
                            'value' => NULL,
                            'err_msg' => 'Age must be a number!',
                        ),
                    ),
                ),
                'email' =>
                array(
                    '<RULES>' =>
                    array(
                        'email' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                        'required' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                        'min' =>
                        array(
                            'value' => 6,
                            'err_msg' => 'Email must be at least six(6) characters long!',
                        ),
                        'max' =>
                        array(
                            'value' => 100,
                            'err_msg' => NULL,
                        ),
                    ),
                ),
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    $handler($c);
};
