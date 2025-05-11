<?php
//Validation Handler File - This runs after Route Handler have ran for matched Route!

//DELIMITER_HANDLER_FUNCTION_START=v_test
function v_test(&$c) // <GET/test>
{

};
//DELIMITER_HANDLER_FUNCTION_END=v_test

//NEVER_TOUCH_ANY_COMMENTS_START=v_test
return function (&$c, $handler = "v_test") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=v_test