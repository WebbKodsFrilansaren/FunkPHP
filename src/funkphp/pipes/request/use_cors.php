<?php

namespace funkphp\pipes\request\use_cors;
// FunkCLI Created File on 2026-07-05 10:17:21!

function use_cors(&$c)
{
	// Placeholder Comment so Regex works - Remove & Add Your Own Code!
	if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
		// These strings are hardcoded directly by FunkGUI during compilation!
		header("Access-Control-Allow-Origin: " . $c['config']['cors']['ALLOW_ORIGIN']);
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
		header("Access-Control-Max-Age: 86400"); // Cache preflight for 24 hours
		header("HTTP/1.1 204 No Content");
		exit;
	} // For standard requests (GET/POST), still attach the Origin allowance header
	header("Access-Control-Allow-Origin: " . $c['config']['cors']['ALLOW_ORIGIN']);
};
