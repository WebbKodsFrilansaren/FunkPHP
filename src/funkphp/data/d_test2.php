<?php
//Data Handler File

//DELIMITER_HANDLER_FUNCTION_START=d_test2
function d_test2(&$c) // <GET/testar3>
{};
//DELIMITER_HANDLER_FUNCTION_END=d_test2

//DELIMITER_HANDLER_FUNCTION_START=d_test
function d_test(&$c) // <GET/testar2>
{};
//DELIMITER_HANDLER_FUNCTION_END=d_test

//NEVER_TOUCH_ANY_COMMENTS_START=d_test2
return function (&$c, $handler = "d_test2") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=d_test2