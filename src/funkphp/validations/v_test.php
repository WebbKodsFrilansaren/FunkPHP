<?php

namespace FunkPHP\Validations\v_test;
// Validation Handler File - Created in FunkCLI on 2025-05-29 21:02:46!
// Write your Validation Rules in the
// $DX variable and then run the command
// `php funkcli compile v v_test=>$function_name`
// to get the optimized version below it!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function v_test2(&$c) // <>
{
  return array(
    '<CONFIG>' =>
    array(
      'passwords_to_match' =>
      array(
        'user_password' => 'user_password_confirm',
      ),
    ),
    'one_digit' =>
    array(
      '<RULES>' =>
      array(
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'digit' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
      ),
    ),
    'user_password' =>
    array(
      '<RULES>' =>
      array(
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'password' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'between' =>
        array(
          'value' =>
          array(
            0 => 5,
            1 => 10,
          ),
          'err_msg' => NULL,
        ),
      ),
    ),
    'user_password_confirm' =>
    array(
      '<RULES>' =>
      array(
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'password_confirm' =>
        array(
          'value' => 'user_password',
          'err_msg' => NULL,
        ),
      ),
    ),
  );
  // Created in FunkCLI on 2025-05-29 21:02:46! Keep "};" on its
  // own new line without indentation no comment right after it!
  // Run the command `php funkcli compile v v_test=>v_test3`
  // to get optimized version in return statement below it!
  $DX = [
    'one_digit' => "required|digit",
    'user_password' => "required|password|between:5,10",
    'user_password_confirm' => "required|password_confirm:user_password",
  ];
};


return function (&$c, $handler = "v_test3") {
  $base = is_string($handler) ? $handler : "";
  $full = __NAMESPACE__ . '\\' . $base;
  if (function_exists($full)) {
    return $full($c);
  } else {
    $c['err']['FAILED_TO_RUN_VALIDATION_FUNCTION-' . 'v_test'] = 'Validation function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
    return null;
  }
};
