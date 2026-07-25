<?php

namespace funkphp\data\validation\s_najs;

use function data;
// FunkCLI Created on 2026-06-15 17:55:32!

function s_paj(&$c)
{
	// FunkCLI created 2026-06-15 17:55:32!
	$test = [
		'<CONFIG>' => ['stop_all_on_first_error' => false],
		'VALIDATION' => [
			'bigger.name'  => data("array"),
			'test' => data('string'),
			'testar' => "tes"
		],
	];
	\cli_compile_validation_schema($test, "s_najs", "s_paj");
};
