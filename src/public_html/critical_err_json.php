<?php // src/public_html/critical_err_json.php - When Critical Error Happens with JSON Accept Header
// Write your custom JSON Response here OR You can use the $status and $customMessage variables
// passed by critical_err_json_or_html() from src/public_html/index.php File to use the error
// that was passed by You or the FunkPHP Framework itself somewhere along the way!
return [
    'status' => $status ?? 500,
    'internal_error' => $customMessage ?? 'FunkPHP Framework - Internal Error: Important Files could not be Loaded and/or Executed, so Please Tell the Developer to fix the website or the Web Hosting Service to allow for reading the necessary folders & files! If you are the Developer, please check your Configuration and File permissions where you Develop and/or Host this Website!Thanks in advance! You are Awesome, anyway! ^_^',

];
