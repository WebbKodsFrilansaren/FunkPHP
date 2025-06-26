<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-10 11:21:07!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function s_test3(&$c) // <authors>
{
	// Created in FunkCLI on 2025-06-10 11:21:07! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test3`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'UPDATE',
			'<TABLES>' => "authors",
			'[SUBQUERIES]' => [
				"[test1]" => "(col1,col2) = (SELECT col3, col4 FROM t2 WHERE id = 15)",
			]
		],
		'UPDATE_SET' => 'authors:name,email',
		'WHERE' => 'id = ?',
		'<MATCHED_FIELDS>' => [ // What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey)
			'name' => '',
			'email' => '',
			'description' => '',
			'longer_description' => '',
			'age' => '',
			'weight' => '',
			'nickname' => '',
			'updated_at' => '',
			'id' => ''
		],
	];

	return array(
		'sql' => 'UPDATE authors SET name = ?, email = ? WHERE authors.id = ?;',
		'hydrate' =>
		array(),
		'bparam' => 'ssi',
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
