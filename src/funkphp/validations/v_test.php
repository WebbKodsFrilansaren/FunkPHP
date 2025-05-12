<?php
//Validation Handler File - Write your Validation Rules
// in the $DX variable and then run the command
// `php funkcli compile v v_test=>$function_name`
// to get the optimized version below it!

//DELIMITER_HANDLER_FUNCTION_START=v_test
function v_test(&$c) // <GET/test>
{
    //DELIMITER_VALIDATION_USER_START=v_test

    $DX = [
        'input_filed1' => 'required("Must be filled!")|string|min:10|max:111',
        'input_filed2' => 'required|string|email|min:10|max:50',
        'input_filed3' => 'required|integer|min:10|max:50',
    ];

    //DELIMITER_VALIDATION_USER_END=v_test


    //DELIMITER_VALIDATION_COMPILED_START=v_test
    return array(
        'input_filed1' =>
        ['required' => [
            'err' => 'Must be filled!'
        ], 'string' => [
            'err' => null
        ], ['min' => [
            'err' => null,
            'val' => '10'
        ]], ['max' => [
            'err' => null,
            'val' => 111
        ]]],
        'input_filed2' => ['required' => [
            'err' => null
        ], 'string' => [
            'err' => null
        ], 'email' => [
            'err' => null
        ], ['min' => [
            'err' => null,
            'val' => '10'
        ]], ['max' => [
            'err' => null,
            'val' => 50
        ]]],
        'input_filed3' => ['required' => [
            'err' => 'Must be filled!'
        ], 'integer' => [
            'err' => null
        ], ['min' => [
            'err' => null,
            'val' => '10'
        ]], ['max' => [
            'err' => null,
            'val' => 50
        ]]],

    );
    //DELIMITER_VALIDATION_COMPILED_END=v_test
};
//DELIMITER_HANDLER_FUNCTION_END=v_test

//DELIMITER_HANDLER_FUNCTION_START=v_test2
function v_test2(&$c) // <GET/test2>
{
    //DELIMITER_VALIDATION_USER_START=v_test2

    $DX = [];

    //DELIMITER_VALIDATION_USER_END=v_test2


    //DELIMITER_VALIDATION_COMPILED_START=v_test2
    return [];
    //DELIMITER_VALIDATION_COMPILED_END=v_test2
};
//DELIMITER_HANDLER_FUNCTION_END=v_test2

//NEVER_TOUCH_ANY_COMMENTS_START=v_test
return function (&$c, $handler = "v_test") {
    $handler($c);
};
//NEVER_TOUCH_ANY_COMMENTS_END=v_test