<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-10 11:21:07!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!


function s_test0(&$c) // <authors>
{
	// Created in FunkCLI on 2025-06-27 19:08:35! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test0`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'UPDATE',
			'<TABLES>' => ["authors"],
			'[SUBQUERIES]' => [ // /!\: Subqueries are IGNORED when Query Type is `INSERT|UPDATE|DELETE`!
			]
		],
		'UPDATE_SET' => 'authors:name,email,description,longer_description,age,weight,nickname,updated_at',
		'WHERE' => 'authors:id = ?',
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
		'sql' => 'UPDATE authors SET name = ?, email = ?, description = ?, longer_description = ?, age = ?, weight = ?, nickname = ?, updated_at = ? WHERE authors.id = ?;',
		'hydrate' =>
		array(),
		'bparam' => 'ssssidssi',
		'fields' =>
		array(
			0 => 'authors_name',
			1 => 'authors_email',
			2 => 'authors_description',
			3 => 'authors_longer_description',
			4 => 'authors_age',
			5 => 'authors_weight',
			6 => 'authors_nickname',
			7 => 'authors_updated_at',
			8 => 'authors_id',
		),
	);
};


function s_test1(&$c) // <authors>
{
	// Created in FunkCLI on 2025-06-27 20:15:15! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test1`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'DELETE',
			'<TABLES>' => ["authors"],
			'[SUBQUERIES]' => [ // /!\: Subqueries are IGNORED when Query Type is `INSERT|UPDATE|DELETE`!
			]
		],
		'DELETE_FROM' => 'authors',
		'WHERE' => 'authors:id = ?',
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
		'sql' => 'DELETE FROM authors WHERE authors.id = ?;',
		'hydrate' =>
		array(),
		'bparam' => 'i',
		'fields' =>
		array(
			0 => 'authors_id',
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
