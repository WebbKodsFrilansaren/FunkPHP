<?php

namespace FunkPHP\Handlers\test;
// Route Handler File - Created in FunkCLI on 2025-07-16 04:37:24!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function test(&$c) // <GET/>
{
	// FunkCLI created 2025-07-16 04:37:24! Keep Closing Curly Bracket on its
	// own new line without indentation no comment right after it!
	echo "Hello, this is a test ROUTE handle AAAAAAAAAA!<br>";
};

return function (&$c, $handler = "test") {
	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c);
	} else {
		$c['err']['HANDLERS'][] = 'Route Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
