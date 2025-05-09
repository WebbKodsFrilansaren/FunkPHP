<?php
//Handler File - This runs after Middlewares have ran after matched Route!

//DELIMITER_HANDLER_FUNCTION_START=r_test
function r_test(&$c) // <GET/test>
{
    $validate = [
        'authors' =>
        [
            'email',
            'name',
        ],
        'articles' =>
        [
            'title',
            'content',
            'published',
        ],
        'comments' =>
        [
            'content',
        ],
    ];
    funk_use_validation($c, $validate, 'post');
};
//DELIMITER_HANDLER_FUNCTION_END=r_test

//NEVER_TOUCH_ANY_COMMENTS_START=r_test
return function (&$c, $handler = "r_test") {
    $handler($c['err']);
};
//NEVER_TOUCH_ANY_COMMENTS_END=r_test