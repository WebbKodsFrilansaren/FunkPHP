<?php

namespace FunkPHP\Try\test;
// FunkCLI Created on 2025-09-09 06:40:20!

function test(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
};

return function (&$c, $handler = "test", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['TRY'][] = 'TRY Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
