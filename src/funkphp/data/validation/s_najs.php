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
			'best.*' => data("string"),
			'best.*.test' => data("string"),
			'test.*' => data("string"),
			'test.*.test' => data("string"),
			'btest' => data("string"),
		],
	];
	\cli_compile_validation_schema($test, "s_najs", "s_paj");
};
