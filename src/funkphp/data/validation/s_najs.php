<?php

namespace funkphp\data\validation\s_najs;

use function string, integer, float, boolean, number, phone, password, email, object, arr, all;
// FunkCLI Created on 2026-06-15 17:55:32!

function s_paj(&$c)
{
	// FunkCLI created 2026-06-15 17:55:32! Keep Closing Curly Bracket on its
	// own new line without indentation and no comment right after it!
	// Run the command `php funkcli compile v file=>fn`
	// to get optimized version in return statement below it!
	cli_dump([
		'<CONFIG>' => ['stop_all_on_first_error' => false],
		'VALIDATION' => [
			'name'  => all("string", "Name must be a string!")
				->nullable()->required("A Username is required!")->min(10, "Must be 10 at least!")
		],
	]);
};
