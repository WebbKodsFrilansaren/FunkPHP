<?php
//Data Handler File - This runs after Route Handler have ran for matched Route!

//DELIMITER_HANDLER_FUNCTION_START=d_test2
function d_test2(&$c) // <GET/test>
{};
//DELIMITER_HANDLER_FUNCTION_END=d_test2

//NEVER_TOUCH_ANY_COMMENTS_START=d_test1
return function (&$c, $handler = "d_test2") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=d_test1