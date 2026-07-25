<?php

namespace funkphp\data\validation\s_najs;

use function data;
// FunkCLI Created on 2026-06-15 17:55:32!

function s_paj(&$c)
{
	$a = 'custom errors with a \' and here again escaped \\\'!?';
	// FunkCLI created 2026-06-15 17:55:32! Keep Closing Curly Bracket on its
	// own new line without indentation and no comment right after it!
	// Run the command `php funkcli compile v file=>fn`
	// to get optimized version in return statement below it!
	cli_dump([
		'<CONFIG>' => ['stop_all_on_first_error' => false],
		'VALIDATION' => [
			'bigger.name'  => data("array", "Name must be an associative array!", "associative")->keys_in_array_depths(['meta.author.name', 'config.settings']),
			'test' => data('string:10-15', "Dimensional array\" with 2' and then 2' elements please!")->starts_with(["O'Reilly", "Another ' to test it!"], "custom errors with a ' and here again escaped \'!?")
		],
	]);
};
