<?php
//Validation Handler File - Write your Validation Rules
// in the $DX variable and then run the command
// `php funkcli compile v v_test=>$function_name`
// to get the optimized version below it!

//DELIMITER_HANDLER_FUNCTION_START=v_test
function v_test(&$c) // <GET/testar3>
{
    //DELIMITER_VALIDATION_USER_START=v_test

    $DX = [];

    //DELIMITER_VALIDATION_USER_END=v_test


    //DELIMITER_VALIDATION_COMPILED_START=v_test
    return [];
    //DELIMITER_VALIDATION_COMPILED_END=v_test
};
//DELIMITER_HANDLER_FUNCTION_END=v_test

//NEVER_TOUCH_ANY_COMMENTS_START=v_test
return function (&$c, $handler = "v_test") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=v_test