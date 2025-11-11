<?php

namespace FunkPHP\Sql\s_test;
// FunkCLI Created on 2025-08-21 23:12:34!

function s_test(&$c, $passedValue = null) // <s=authors>
{
	// FunkCLI created 2025-08-21 23:12:34! Keep Closing Curly Bracket on its
	// own new line without indentation no comment right after it!
	// Run the command `php funk compile:s_eval file=>fn`
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
		// 'JOINS_ON' Syntax: `join_type=table2,table1(table1Col),table2(table2Col)`
		// Available Join Types: `inner|i|join|j|ij`,`left|l`,`right|r`
		// Example: `inner=books,authors(id),books(author_id)`
		'JOINS_ON' => [ // Optional, make empty if not joining any tables!
		],
		// Optional Keys, leave empty (or remove) if not used!
		'SELECT' => [
			'authors:id,name,email,description,longer_description,age,weight,nickname,updated_at',
		],
		'WHERE' => '',
		'GROUP BY' => '',
		'HAVING' => '',
		'ORDER BY' => '',
		'LIMIT' => '',
		'OFFSET' => '',
		// Optional, leave empty if not used!
		'<HYDRATION>' => ["authors"],
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
		'qtype' => 'SELECT',
		'sql' => 'SELECT authors.id AS authors_id, authors.id AS authors_id, authors.name AS authors_name, authors.email AS authors_email, authors.description AS authors_description, authors.longer_description AS authors_longer_description, authors.age AS authors_age, authors.weight AS authors_weight, authors.nickname AS authors_nickname, authors.updated_at AS authors_updated_at FROM authors;',
		'hydrate' =>
		array(
			'key' =>
			array(
				'authors' =>
				array(
					'pk' => 'authors_id',
					'cols' =>
					array(
						0 => 'authors_name',
						1 => 'authors_email',
						2 => 'authors_description',
						3 => 'authors_longer_description',
						4 => 'authors_age',
						5 => 'authors_weight',
						6 => 'authors_nickname',
						7 => 'authors_updated_at',
						8 => 'id',
					),
					'with' =>
					array(),
				),
			),
			'mode' => 'simple',
			'type' => 'array',
		),
		'bparam' => '',
		'fields' =>
		array(),
	);
};

function s_test2(&$c, $passedValue = null) // <select=alones>
{
	// FunkCLI created 2025-11-11 06:01:59! Keep Closing Curly Bracket on its
	// own new line without indentation and no comment right after it!
	// Run the command `php funk compile:s_eval s_file=>s_fn`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'SELECT',
			'<TABLES>' => ['alones'],
			'<HYDRATION_MODE>' => 'simple|advanced', // Pick one or `simple` is used by default! (leave empty or remove line if not used!)
			'<HYDRATION_TYPE>' => 'array|object', // Pick one or `array` is used by default! (leave empty or remove line if not used!)
			'[SUBQUERIES]' => [
				'[subquery_example_1]' => 'SELECT COUNT(*)',
				'[subquery_example_2]' => '(WHERE SELECT *)'
			]
		],
		'FROM' => 'alones',
		// 'JOINS_ON' Syntax: `join_type=table2,table1(table1Col),table2(table2Col)`
		// Available Join Types: `inner|i|join|j|ij`,`left|l`,`right|r`
		// Example: `inner=books,authors(id),books(author_id)`
		'JOINS_ON' => [ // Optional, make empty if not joining any tables!
		],
		// Optional Keys, leave empty (or remove) if not used!
		'SELECT' => [
			'alones:id,name,description,created_at,updated_at',
		],
		'WHERE' => '',
		'GROUP BY' => '',
		'HAVING' => '',
		'ORDER BY' => '',
		'LIMIT' => '',
		'OFFSET' => '',
		// Optional, leave empty if not used!
		'<HYDRATION>' => [],
		// What each Binded Param must match from a Validated Data
		// Field Array (empty means same as TableName_ColumnKey)
		'<MATCHED_FIELDS>' => [
			'id' => '',
			'name' => '',
			'description' => '',
			'created_at' => '',
			'updated_at' => ''
		],
	];

	return array([]);
};

return function (&$c, $handler = "s_test", $passedValue = null) {

	$base = is_string($handler) ? $handler : "";
	$full = __NAMESPACE__ . '\\' . $base;
	if (function_exists($full)) {
		return $full($c, $passedValue);
	} else {
		$c['err']['ROUTES']['SQL'][] = 'SQL Function `' . $full . '` not found in namespace `' . __NAMESPACE__ . '`. Does it exist as a callable function in the File?';
		return null;
	}
};
