<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=t1
function t1(&$c) // <DELETE/test3>
{
    echo "<br>FUNCTION T1111111 from HANDLER \"t.php!\"";
};
//DELIMITER_HANDLER_FUNCTION_END=t1

//DELIMITER_HANDLER_FUNCTION_START=t2
function t2(&$c) // <DELETE/test3>
{
    echo "<br>FUNCTION t2222222 from HANDLER \"t.php!\"";
};
//DELIMITER_HANDLER_FUNCTION_END=t2

//DELIMITER_HANDLER_FUNCTION_START=t3
function t3(&$c) // <DELETE/test5>
{};
//DELIMITER_HANDLER_FUNCTION_END=t3

//DELIMITER_HANDLER_FUNCTION_START=t33
function t33(&$c) // <DELETE/test/:id/:id2>
{};
//DELIMITER_HANDLER_FUNCTION_END=t33

//DELIMITER_HANDLER_FUNCTION_START=t334
function t334(&$c) // <DELETE/test/:id/:id32>
{};
//DELIMITER_HANDLER_FUNCTION_END=t334

//DELIMITER_HANDLER_FUNCTION_START=t3344
function t3344(&$c) // <DELETE/test/:id/:id342>
{};
//DELIMITER_HANDLER_FUNCTION_END=t3344

//NEVER_TOUCH_ANY_COMMENTS_START=t
return function (&$c, $handler = "t1") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=t