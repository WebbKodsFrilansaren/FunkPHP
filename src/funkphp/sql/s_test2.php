<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-09 18:07:54!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function s_test3(&$c) // <authors>
{
	// Created in FunkCLI on 2025-06-09 18:07:54! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test3`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'INSERT',
			'<TABLES>' => ["authors"],
			'[SUBQUERIES]' => [ // /!\: Subqueries are IGNORED when Query Type is `INSERT|UPDATE|DELETE`!
			]
		],
		'INSERT_INTO' => 'authors:name,email',
		'<MATCHED_FIELDS>' => [ // What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey)
			'name' => '',
			'email' => '',
		],
	];

	return array(
		'sql' => 'INSERT INTO authors (name,email) VALUES (?,?);',
		'hydrate' =>
		array(),
		'bparam' => 'ss',
		'fields' =>
		array(
			0 => 'authors_name',
			1 => 'authors_email',
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
