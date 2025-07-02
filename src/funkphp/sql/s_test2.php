<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-10 11:21:07!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!




function s_test4(&$c) // <authors,articles>
{
	// Created in FunkCLI on 2025-07-02 22:46:59! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test4`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'SELECT',
			'<TABLES>' => ['authors', 'articles'],
			'[SUBQUERIES]' => [
				'[subquery_example_1]' => 'SELECT COUNT(*)',
				'[subquery_example_2]' => '(WHERE SELECT *)'
			]
		],
		'SELECT' => [
			'authors:id,name,email,description,longer_description,age,weight,nickname,updated_at',
			'articles:id,author_id,title,content,published,created_at,updated_at',
		],
		'FROM' => 'authors',
		// 'JOINS_ON' Syntax: `join_type=table1,table1_id,table2_ref_id`
		// Join Types: `inner|i`,`left|l`,`right|r` (Full Join NOT Available yet!)
		'JOINS_ON' => [ // Optional, make empty if not joining any tables!
			'inner=authors,articles_author_id,authors_id'
		],
		'WHERE' => '', // Optional, leave empty (or remove) if not used!
		'GROUP BY' => '', // Optional, leave empty (or remove) if not used!
		'HAVING' => '', // Optional, leave empty (or remove) if not used!
		'ORDER BY' => '', // Optional, leave empty (or remove) if not used!
		'LIMIT' => '', // Optional, leave empty (or remove) if not used!
		'OFFSET' => '', // Optional, leave empty (or remove) if not used!
		'<MATCHED_FIELDS>' => [ // What each Binded Param must match from a Validated Data Field Array (empty means same as TableName_ColumnKey)
			'authors_id' => '',
			'authors_name' => '',
			'authors_email' => '',
			'authors_description' => '',
			'authors_longer_description' => '',
			'authors_age' => '',
			'authors_weight' => '',
			'authors_nickname' => '',
			'authors_updated_at' => '',
			'articles_id' => '',
			'articles_author_id' => '',
			'articles_title' => '',
			'articles_content' => '',
			'articles_published' => '',
			'articles_created_at' => '',
			'articles_updated_at' => ''
		],
	];

	return array([]);
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
