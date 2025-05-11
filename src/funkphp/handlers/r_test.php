<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=r_test
function r_test(&$c) // <GET/test>
{

};
//DELIMITER_HANDLER_FUNCTION_END=r_test

//DELIMITER_HANDLER_FUNCTION_START=r_by_id
function r_by_id(&$c) // <GET/test/:id>
{

};
//DELIMITER_HANDLER_FUNCTION_END=r_by_id

//NEVER_TOUCH_ANY_COMMENTS_START=r_test
return function (&$c, $handler = "r_test") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=r_test