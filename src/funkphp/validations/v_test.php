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
        '*' => 'list|between:1,5|nullable|field:Super arrayen',
        '*.test' => 'string|required|min:5|max:10',
        '*.arr_test2.*' => 'list|between:1,5|required',
        '*.arr_test2.*.test' => 'string|required|min:5|max:10',
    ];

    return array(
        '*' =>
        array(
            '<RULES>' =>
            array(
                'field' =>
                array(
                    'value' => 'Super arrayen',
                    'err_msg' => NULL,
                ),
                'nullable' =>
                array(
                    'value' => NULL,
                    'err_msg' => NULL,
                ),
                'list' =>
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
            'test' =>
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
                    'min' =>
                    array(
                        'value' => 5,
                        'err_msg' => NULL,
                    ),
                    'max' =>
                    array(
                        'value' => 10,
                        'err_msg' => NULL,
                    ),
                ),
            ),
            'arr_test2' =>
            array(
                '*' =>
                array(
                    '<RULES>' =>
                    array(
                        'required' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                        'list' =>
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
                    'test' =>
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
                            'min' =>
                            array(
                                'value' => 5,
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
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    return $handler($c);
};
