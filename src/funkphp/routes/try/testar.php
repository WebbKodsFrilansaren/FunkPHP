<?php

namespace FunkPHP\Try\testar;
// FunkCLI Created on 2025-10-13 14:30:39!

function testar(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
};

function testar2(&$c, $passedValue = null) // <N/A>
{
	// Placeholder Comment so Regex works - Remove & Add Real Code!
};

return function (&$c, $handler = "testar", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['TRY'][] = 'TRY Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
