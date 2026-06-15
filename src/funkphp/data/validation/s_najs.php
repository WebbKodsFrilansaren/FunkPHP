<?php

namespace funkphp\data\validation\s_najs;
// FunkCLI Created on 2026-06-15 17:55:32!

function s_paj(&$c)
{
	// FunkCLI created 2026-06-15 17:55:32! Keep Closing Curly Bracket on its
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
