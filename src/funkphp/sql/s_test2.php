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
	// FunkCLI created 2025-07-07 18:06:23! Keep Closing Curly Bracket on its
	// own new line without indentation no comment right after it!
	// Run the command `php funkcli compile s s_test2=>s_test5`
	// to get SQL, Hydration & Binded Params in return statement below it!
	$DX = [
		'<CONFIG>' => [
			'<QUERY_TYPE>' => 'SELECT',
			'<TABLES>' => ['authors', 'articles', 'comments'],
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
			//'inner=comments,comments(author_id),authors(id)',
		],
		// Optional Keys, leave empty (or remove) if not used!
		'SELECT' => [
			'authors:id,name',
			//'comments:id,content,author_id',
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

	return array(
		'sql' => 'SELECT authors.id AS authors_id, authors.name AS authors_name FROM authors;',
		'hydrate' =>
		array(
			'mode' => 'simple',
			'type' => 'array',
			'key' => NULL,
		),
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
