<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-10 11:21:07!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!


function s_test5(&$c) // <authors>
{
	// FunkCLI created 2025-07-06 08:54:14! Keep Closing Curly Bracket on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test5`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'SELECT',
			'<TABLES>' => ['authors'],
			'<HYDRATION_MODE>' => 'simple|advanced', // Pick one or `simple` is used by default! (leave empty or remove line if not used!)
			'<HYDRATION_TYPE>' => 'array|object', // Pick one or `array` is used by default! (leave empty or remove line if not used!)
			'[SUBQUERIES]' => [
				'[subquery_example_1]' => 'SELECT COUNT(*)',
				'[subquery_example_2]' => '(WHERE SELECT *)'
			]
		],
		'FROM' => 'authors',
		// 'JOINS_ON' Syntax: `join_type=table2,table1_id,table2_ref_id`
		// Available Join Types: `inner|i|join|j|ij`,`left|l`,`right|r`
		'JOINS_ON' => [ // Optional, make empty if not joining any tables!
		],
		// Optional Keys, leave empty (or remove) if not used!
		'SELECT' => [
			'authors:id,name,email,description,longer_description,age,weight,nickname,updated_at',
		],
		'WHERE' => '',
		'GROUP BY' => '',
		'HAVING' => '',
		'ORDER BY' => 'id DESC|name ASC', // Example: `id DESC|title ASC`
		'LIMIT' => '',
		'OFFSET' => '',
		// Optional, leave empty if not used!
		'<HYDRATION>' => [],
		// What each Binded Param must match from a Validated Data
		// Field Array (empty means same as TableName_ColumnKey)
		'<MATCHED_FIELDS>' => [
			'id' => '',
			'name' => '',
			'email' => '',
			'description' => '',
			'longer_description' => '',
			'age' => '',
			'weight' => '',
			'nickname' => '',
			'updated_at' => ''
		],
	];

	return array(
		'sql' => 'SELECT authors.id AS authors_id, authors.name AS authors_name, authors.email AS authors_email, authors.description AS authors_description, authors.longer_description AS authors_longer_description, authors.age AS authors_age, authors.weight AS authors_weight, authors.nickname AS authors_nickname, authors.updated_at AS authors_updated_at FROM authors ORDER BY id DESC, name ASC;',
		'hydrate' =>
		array(),
		'bparam' => '',
		'fields' =>
		array(),
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
