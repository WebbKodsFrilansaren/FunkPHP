<?php
// Validation Handler File - Created in FunkCLI on 2025-05-13 13:40:40!
// Write your Validation Rules in the
// $DX variable and then run the command
// `php funkcli compile v v_test2=>$function_name`
// to get the optimized version below it!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function v_test3(&$c) // <GET/test3>
{
    // Created in FunkCLI on 2025-05-13 13:40:40! Keep "};" on its
    // own new line without indentation no comment right after it!
    $DX =
        ['testa' => 'required|email|unique:users,email'];

    return array(
        'testa' => 'required|email|unique:users,email',
    );
};


return function (&$c, $handler = "v_test2") {
    $handler($c);
};
