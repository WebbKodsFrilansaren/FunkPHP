<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=r_users
function r_users(&$c) // <GET/users>
{
    echo "<h1>Hello from r_users handler! (Step 3 - Route Handler)</h1>";
};
//DELIMITER_HANDLER_FUNCTION_END=r_users

//NEVER_TOUCH_ANY_COMMENTS_START=r_users
return function (&$c, $handler = "r_users") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=r_users