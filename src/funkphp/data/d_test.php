<?php
//Data Handler File - This runs after Route Handler have ran for matched Route!

//DELIMITER_HANDLER_FUNCTION_START=d_test
function d_test(&$c) // <GET/test>
{

};
//DELIMITER_HANDLER_FUNCTION_END=d_test

//DELIMITER_HANDLER_FUNCTION_START=d_test2
function d_test2(&$c) // <GET/test2>
{

};
//DELIMITER_HANDLER_FUNCTION_END=d_test2

//NEVER_TOUCH_ANY_COMMENTS_START=d_test
return function (&$c, $handler = "d_test") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=d_test