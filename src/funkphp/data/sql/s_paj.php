<?php

namespace funkphp\data\sql\s_paj;
// FunkCLI Created on 2026-06-15 17:53:17!

function s_najs(&$c)
{
	// FunkCLI created 2026-06-15 17:53:17! Keep Closing Curly Bracket on its
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
