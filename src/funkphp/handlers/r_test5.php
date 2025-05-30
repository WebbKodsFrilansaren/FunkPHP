<?php
namespace FunkPHP\Handlers\r_test5;
// Route Handler File - Created in FunkCLI on 2025-05-30 22:41:43!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function r_test5(&$c) // <GET/test3>
{
// Created in FunkCLI on 2025-05-30 22:41:43! Keep "};" on its
// own new line without indentation no comment right after it!

};

return function (&$c, $handler = "r_test5") { 
$base = is_string($handler) ? $handler : "";
$full = __NAMESPACE__ . '\\' . $base;
if (function_exists($full)) {
return $full($c);
} else {$c['err']['FAILED_TO_RUN_HANDLERS_FUNCTION-r_test5'] = 'Route Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
return null;
} };
