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
        '<CONFIG>' => null,
        'user_password' => 'password:1,1,1,1|required|between:5,15',
        'user_password_confirm' => 'password_confirm:user_password|required',
    ];

    return array(
        '<CONFIG>' =>
        array(
            'passwords_to_match' =>
            array(
                'user_password' => 'user_password_confirm',
            ),
        ),
        'user_password' =>
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
                        0 => 5,
                        1 => 15,
                    ),
                    'err_msg' => NULL,
                ),
            ),
        ),
        'user_password_confirm' =>
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
                    'value' => 'user_password',
                    'err_msg' => NULL,
                ),
            ),
        ),
    );
};

return function (&$c, $handler = "v_test") {
    return $handler($c);
};
