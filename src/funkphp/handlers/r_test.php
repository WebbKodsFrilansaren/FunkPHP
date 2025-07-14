<?php
namespace FunkPHP\Handlers\r_test;
// Route Handler File - Created in FunkCLI on 2025-07-14 04:29:24!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function r_test1(&$c) // <GET/>
{
// FunkCLI created 2025-07-14 04:29:24! Keep Closing Curly Bracket on its
// own new line without indentation no comment right after it!

};

return function (&$c, $handler = "r_test1") { 
	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c);
	} else {
		$c['err']['HANDLERS'][] = 'Route Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	} 
};
