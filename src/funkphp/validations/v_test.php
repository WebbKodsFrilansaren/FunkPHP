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

function v_test3(&$c) // <>
{
  // Created in FunkCLI on 2025-06-04 20:40:36! Keep "};" on its
  // own new line without indentation no comment right after it!
  // Run the command `php funkcli compile v v_test=>v_test3`
  // to get optimized version in return statement below it!
  $DX = [
    '<CONFIG>' => '[]',
    'table_col1_name' => 'string|required|nullable|between:3,50',
    'table_col2_email' => 'email|required|between:6,50',
    'table_col3_age' => 'integer|required|between:18,100',
    'table_col4_length' => 'float|nullable|decimals:2',
  ];


  return array(
    '<CONFIG>' => NULL,
    'table_col1_name' =>
    array(
      '<RULES>' =>
      array(
        'nullable' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'string' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'between' =>
        array(
          'value' =>
          array(
            0 => 3,
            1 => 50,
          ),
          'err_msg' => NULL,
        ),
      ),
    ),
    'table_col2_email' =>
    array(
      '<RULES>' =>
      array(
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'email' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'between' =>
        array(
          'value' =>
          array(
            0 => 6,
            1 => 50,
          ),
          'err_msg' => NULL,
        ),
      ),
    ),
    'table_col3_age' =>
    array(
      '<RULES>' =>
      array(
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'integer' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'between' =>
        array(
          'value' =>
          array(
            0 => 18,
            1 => 100,
          ),
          'err_msg' => NULL,
        ),
      ),
    ),
    'table_col4_length' =>
    array(
      '<RULES>' =>
      array(
        'nullable' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'float' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'decimals' =>
        array(
          'value' => 2,
          'err_msg' => NULL,
        ),
      ),
    ),
  );
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
