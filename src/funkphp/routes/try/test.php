<?php

namespace FunkPHP\Try\test;

use FunkPHP\Classes\Test;
// FunkCLI Created on 2025-09-10 10:23:15!

function test2(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
	echo "HI FROM TRY TEST2 FUNCTION! Should run for GET/users/:id\n";
	$test = funk_use_safe_mutate($c, ["req", "auth"], TEST_2(), [], ["boolean", "integer"], null);
	echo "Returned value Using TEST_2() is: " . print_r($test, true) . "\n";
};

function test(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
	echo "HI FROM TRY TEST FUNCTION! Should run for GET/users\n";
};

return function (&$c, $handler = "test2", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['TRY'][] = 'TRY Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		critical_err_json_or_html(500, 'Tell the Developer: The TRY Function `' . $full . '` could not be found! Please check the Function exists in the File and is in the correct Namespace!');
	}
};
