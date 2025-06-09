<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-09 16:29:22!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function s_test3(&$c) // <authors>
{
	// Created in FunkCLI on 2025-06-09 16:29:22! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test3`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'INSERT',
			'<TABLES>' => ["authors"],
			'[SUBQUERIES]' => [ // /!\: Subqueries are IGNORED when Query Type is `INSERT|UPDATE|DELETE`!
				'[subquery_example_1]' => 'SELECT COUNT(*)',
				'[subquery_example_2]' => '(WHERE SELECT *)'
			]
		],
		'INSERT_INTO' => 'authors:name,email,description,longer_description,age,weight,nickname,updated_at',
		'<MATCHED_FIELDS>' => [ // What each Binded Param must match from a Data Field Array (empty means same as key)
			'name' => '',
			'email' => '',
			'description' => '',
			'longer_description' => '',
			'age' => '',
			'weight' => '',
			'nickname' => '',
			'updated_at' => '',
		],
	];

	return array(
		'sql' => 'INSERT INTO authors (name,email,description,longer_description,age,weight,nickname,updated_at) VALUES (?,?,?,?,?,?,?,?);',
		'hydrate' =>
		array(),
		'bparam' => 'ssssidss',
		'fields' =>
		array(
			0 => 'name',
			1 => 'email',
			2 => 'description',
			3 => 'longer_description',
			4 => 'age',
			5 => 'weight',
			6 => 'nickname',
			7 => 'updated_at',
		),
	);
};

return function (&$c, $handler = "s_test3") {
	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c);
	} else {
		$c['err']['FAILED_TO_RUN_SQL_FUNCTION-' . 's_test2'] = 'SQL function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`!';
		return null;
	}
};
