<?php
//Data Handler File - This runs after Route Handler have ran for matched Route!

//DELIMITER_HANDLER_FUNCTION_START=d_users
function d_users(&$c) // <GET/users>
{
    echo "<h1>Hello from d_users handler! (Step 4 - Data Handler)</h1";
};
//DELIMITER_HANDLER_FUNCTION_END=d_users

//NEVER_TOUCH_ANY_COMMENTS_START=d_users
return function (&$c, $handler = "d_users") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=d_users