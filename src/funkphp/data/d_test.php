<?php

namespace FunkPHP\Data\d_test;
// Data Handler File - Created in FunkCLI on 2025-07-16 08:13:39!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test(&$c) // <GET/t>
{
	// FunkCLI created 2025-07-16 08:13:39! Keep Closing Curly Bracket on its
	// own new line without indentation no comment right after it!
	echo "DATA NEW TEST!";
};

return function (&$c, $handler = "d_test") {
	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c);
	} else {
		$c['err']['DATA'][] = 'Data Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
