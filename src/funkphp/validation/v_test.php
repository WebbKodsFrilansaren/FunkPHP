<?php

namespace FunkPHP\Validations\v_test;
// Validation Handler File - Created in FunkCLI on 2025-05-29 21:02:46!
// Write your Validation Rules in the
// $DX variable and then run the command
// `php funkcli compile v v_test=>$function_name`
// to get the optimized version below it!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function v_test(&$c) // <>
{
  // Created in FunkCLI on 2025-05-29 21:02:46! Keep "};" on its
  // own new line without indentation no comment right after it!
  // Run the command `php funkcli compile v v_test=>v_test3`
  // to get optimized version in return statement below it!
  $DX = [
    'date_test' => "required|date:Y-m-d",
    'one_digit' => "required|digit",
  ];

  return array(
    '<CONFIG>' => NULL,
    'date_test' =>
    array(
      '<RULES>' =>
      array(
        'required' =>
        array(
          'value' => NULL,
          'err_msg' => NULL,
        ),
        'date' =>
        array(
          'value' => 'Y-m-d',
          'err_msg' => NULL,
        ),
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
