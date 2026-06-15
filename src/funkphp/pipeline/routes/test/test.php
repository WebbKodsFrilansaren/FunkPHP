<?php

namespace FunkPHP\Routes\Test\test;
// FunkCLI Created on 2025-10-31 09:14:22!

function test(&$c, $passedValue = null) // <GET/users>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!

	// Return JSOn if accept method is application/json just to test FunKGUIs testing sending from its file!
	if (\str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
		header('Content-Type: application/json');
		echo json_encode([
			"message" => "This is a test JSON response from the test function!",
			"passedValue" => ($passedValue ?? "None")
		]);
		exit;
	}

	echo "<h1 style='font-size:12px;'>Testing with HTML tags to see how the cURL Request Test functionality in FunkGUI will react to it!</h1>";
	echo "<div>";
	echo "<p>This is a test paragraph to see how the cURL Request Test functionality in FunkGUI will react to it!</p>";
	echo "</div>";
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
