<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=r_test
function r_test2(&$c) // <GET/test>
{};
//DELIMITER_HANDLER_FUNCTION_END=r_test2

//NEVER_TOUCH_ANY_COMMENTS_START=r_test1
return function (&$c, $handler = "r_test2") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=r_test1