<?php

namespace FunkPHP\SQL\s_test2;
// SQL Handler File - Created in FunkCLI on 2025-06-10 11:21:07!
// Write your SQL Query, Hydration & optional Binded Params in the
// $DX variable and then run the command
// `php funkcli compile s s_test2=>$function_name`
// to get an array with SQL Query, Hydration Array and optionally Binded Params below here!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!


function s_test5(&$c) // <authors,articles,comments>
{
	// Created in FunkCLI on 2025-07-03 00:20:51! Keep "};" on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test5`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'SELECT',
			'<TABLES>' => ['authors', 'articles', 'comments'],
			'[SUBQUERIES]' => [
				'[subquery_example_1]' => 'SELECT COUNT(*)',
				'[subquery_example_2]' => '(WHERE SELECT *)'
			]
		],
		'SELECT' => [
			'authors',
			'articles!:id',
			'comments:id,article_id,content,author_id,created_at',
		],
		'FROM' => 'authors',
		// 'JOINS_ON' Syntax: `join_type=table1,table1_id,table2_ref_id`
		// Join Types: `inner|i|join|j|ij`,`left|l`,`right|r` (Full Join NOT Available yet!)
		'JOINS_ON' => [ // Optional, make empty if not joining any tables!
			'inner=authors,articles_author_id,authors_id',
			'inner=articles,comments_article_id,articles_id',
			'inner=authors,comments_author_id,authors_id'
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
			'articles_updated_at' => '',
			'comments_id' => '',
			'comments_article_id' => '',
			'comments_content' => '',
			'comments_author_id' => '',
			'comments_created_at' => ''
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
