<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=posttest
function posttest(&$c) // <POST/get>
{

};
//DELIMITER_HANDLER_FUNCTION_END=posttest

//NEVER_TOUCH_ANY_COMMENTS_START=posttest
return function (&$c, $handler = "posttest") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=posttest