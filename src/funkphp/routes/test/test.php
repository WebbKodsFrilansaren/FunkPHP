<?php

namespace FunkPHP\Routes\Test\test;
// FunkCLI Created on 2025-10-31 09:14:22!

function test(&$c, $passedValue = null) // <GET/users>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
};

return function (&$c, $handler = "test", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['TEST'][] = 'TEST Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
