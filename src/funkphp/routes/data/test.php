<?php

namespace FunkPHP\Data\test;
// FunkCLI Created on 2025-07-25 13:33:51!

function test(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
	echo "HI FROM DATA TEST FUNCTION! Should run for GET/users<br>";
	$reuseClass = funk_use_class($c, 'classes', 'testClassInstance');
	$reuseClass->hello();
	$test2 = funk_use_class($c, 'classes', (new \FunkPHP\Classes\Test()), 'testClassInstance');
	vd($c['err']);
	vd($c['INSTANCES']);
};

function test2(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
};

return function (&$c, $handler = "test", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['DATA'][] = 'DATA Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
