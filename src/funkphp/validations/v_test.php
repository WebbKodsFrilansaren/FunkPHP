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
        '*.*' => 'array|between:1,5',
        '*.*.name' => 'float|between:400,5000|min_digits:3|max_digits:4|decimals:2',
    ];

    return array(
        '*' =>
        array(
            '*' =>
            array(
                '<RULES>' =>
                array(
                    'array' =>
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
                'name' =>
                array(
                    '<RULES>' =>
                    array(
                        'float' =>
                        array(
                            'value' => NULL,
                            'err_msg' => NULL,
                        ),
                        'between' =>
                        array(
                            'value' =>
                            array(
                                0 => 400,
                                1 => 5000,
                            ),
                            'err_msg' => NULL,
                        ),
                        'min_digits' =>
                        array(
                            'value' => 3,
                            'err_msg' => NULL,
                        ),
                        'max_digits' =>
                        array(
                            'value' => 4,
                            'err_msg' => NULL,
                        ),
                        'decimals' =>
                        array(
                            'value' => 2,
                            'err_msg' => NULL,
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
