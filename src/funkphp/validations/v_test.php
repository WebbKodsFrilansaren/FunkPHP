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
        'a.*' => 'list|count:1|required',
        'a.*.user.*' => 'list|count:1|required',
        'a.*.user.*.name' => 'string|required',
        'a.*.user.*.age' => 'integer|required',
        'a.*.user.*.gender' => 'char:f|required',
        'a.*.user.*.hobbies.*' => 'list|required|count:1',
        'a.*.user.*.hobbies.*.sports' => 'string|required',
    ];

    return array(
        'a' =>
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
                    'count' =>
                    array(
                        'value' => 1,
                        'err_msg' => NULL,
                    ),
                ),
                'user' =>
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
                            'count' =>
                            array(
                                'value' => 1,
                                'err_msg' => NULL,
                            ),
                        ),
                        'name' =>
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
                            ),
                        ),
                        'gender' =>
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
                                    'value' => 'f',
                                    'err_msg' => NULL,
                                ),
                            ),
                        ),
                        'hobbies' =>
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
                                    'count' =>
                                    array(
                                        'value' => 1,
                                        'err_msg' => NULL,
                                    ),
                                ),
                                'sports' =>
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
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    return $handler($c);
};
