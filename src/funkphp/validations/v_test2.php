<?php
//Validation Handler File - Write your Validation Rules
// in the $DX variable and then run the command
// `php funkcli compile v v_test2=>$function_name`
// to get the optimized version below it!

//DELIMITER_HANDLER_FUNCTION_START=v_test2
function v_test2(&$c) // <GET/testar3>
{
    //DELIMITER_VALIDATION_USER_START=v_test2

    $DX = [];

    //DELIMITER_VALIDATION_USER_END=v_test2


    //DELIMITER_VALIDATION_COMPILED_START=v_test2
    return [];
    //DELIMITER_VALIDATION_COMPILED_END=v_test2
};
//DELIMITER_HANDLER_FUNCTION_END=v_test2

//DELIMITER_HANDLER_FUNCTION_START=v_test3
function v_test3(&$c) // <GET/testar2>
{
    //DELIMITER_VALIDATION_USER_START=v_test3

    $DX = [];

    //DELIMITER_VALIDATION_USER_END=v_test3


    //DELIMITER_VALIDATION_COMPILED_START=v_test3
    return [];
    //DELIMITER_VALIDATION_COMPILED_END=v_test3
};
//DELIMITER_HANDLER_FUNCTION_END=v_test3

//NEVER_TOUCH_ANY_COMMENTS_START=v_test2
return function (&$c, $handler = "v_test2") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=v_test2