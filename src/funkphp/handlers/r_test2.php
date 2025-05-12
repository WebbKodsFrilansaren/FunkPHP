<?php
//Route Handler File

//DELIMITER_HANDLER_FUNCTION_START=r_test
function r_test(&$c) // <GET/testar2>
{

};
//DELIMITER_HANDLER_FUNCTION_END=r_test

//DELIMITER_HANDLER_FUNCTION_START=r_test2
function r_test2(&$c) // <GET/testar3>
{

};
//DELIMITER_HANDLER_FUNCTION_END=r_test2

//NEVER_TOUCH_ANY_COMMENTS_START=r_test2
return function (&$c, $handler = "r_test") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=r_test2