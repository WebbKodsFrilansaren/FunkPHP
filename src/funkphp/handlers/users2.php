<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=users4
function users4(&$c) // <GET/test>
{

};
//DELIMITER_HANDLER_FUNCTION_END=users4

//NEVER_TOUCH_ANY_COMMENTS_START=users2
return function (&$c, $handler = "users4") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=users2