<?php

namespace funkphp\data\validation\s_najs;

use function data;
// FunkCLI Created on 2026-06-15 17:55:32!

function s_paj(&$c)
{
	// FunkCLI created 2026-06-15 17:55:32! Keep Closing Curly Bracket on its
	// own new line without indentation and no comment right after it!
	// Run the command `php funkcli compile v file=>fn`
	// to get optimized version in return statement below it!
	$test = [
		'<CONFIG>' => ['stop_all_on_first_error' => false],
		'VALIDATION' => [
			'bigger.name'  => data("array", "Name must be an associative array!", "associative")->keys_in_array_depths(['meta.author.name', 'config.settings']),
			'test' => data('string')->in_allowed("test,test")
		],
	];
	\cli_compile_validation_schema($test);
};
