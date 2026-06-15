<?php

namespace FunkPHP\Validation\s_testar;
// FunkCLI Created on 2025-11-11 06:21:55!

function s_testar(&$c, $passedValue = null) // <alones>
{
	// FunkCLI created 2025-11-11 06:21:55! Keep Closing Curly Bracket on its
	// own new line without indentation and no comment right after it!
	// Run the command `php funkcli compile v file=>fn`
	// to get optimized version in return statement below it!
	$DX = [
		'<CONFIG>' => '',
		'alones.name' => 'string|required|between:1,255',
		'alones.description' => 'string|required|nullable|between:<MIN>,<MAX>',
		'alones.created_at' => 'date|required|nullable|between:<MIN>,<MAX>',
		'alones.updated_at' => 'date|required|nullable|between:<MIN>,<MAX>',

	];

	return array([]);
};

return function (&$c, $handler = "s_testar", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['VALIDATION'][] = 'VALIDATION Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
