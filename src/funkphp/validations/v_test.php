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
        'testa' => 'string|required',
        'test.*' => 'array|nullable',
        'test.*.testa' => 'string|required',
        'test.*.test.*' => 'array|required',
        'test.*.test.*.test3' => 'string|required',
    ];

    return array(
        'testa' =>
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
            ),
        ),
        'test' =>
        array(
            '*' =>
            array(
                '<RULES>' =>
                array(
                    'nullable' =>
                    array(
                        'value' => NULL,
                        'err_msg' => NULL,
                    ),
                    'array' =>
                    array(
                        'value' => NULL,
                        'err_msg' => NULL,
                    ),
                ),
                'testa' =>
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
                    ),
                ),
                'test' =>
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
                            'array' =>
                            array(
                                'value' => NULL,
                                'err_msg' => NULL,
                            ),
                        ),
                        'test3' =>
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
