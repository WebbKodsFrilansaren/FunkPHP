<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=test
function test(&$c) // <GET/get>
{

};
//DELIMITER_HANDLER_FUNCTION_END=test

//NEVER_TOUCH_ANY_COMMENTS_START=test
return function (&$c, $handler = "test") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=test