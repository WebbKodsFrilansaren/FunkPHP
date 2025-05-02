<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=users
function users(&$c) // <GET/users>
{};
//DELIMITER_HANDLER_FUNCTION_END=users

//DELIMITER_HANDLER_FUNCTION_START=by_id
function by_id(&$c) // <GET/users/:id>
{};
//DELIMITER_HANDLER_FUNCTION_END=by_id

//NEVER_TOUCH_ANY_COMMENTS_START=users
return function (&$c, $handler = "users") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=users