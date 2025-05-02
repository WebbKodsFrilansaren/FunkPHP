<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=patchtest
function patchtest(&$c) // <PATCH/get>
{

};
//DELIMITER_HANDLER_FUNCTION_END=patchtest

//NEVER_TOUCH_ANY_COMMENTS_START=patchtest
return function (&$c, $handler = "patchtest") {
$handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=patchtest