<?php

namespace FunkPHP\Middlewares\m_test;
// FunkCLI Created on 2025-09-09 06:55:30!

return function (&$c, $passedValue = null) {
	// Placeholder Comment so Regex works - Remove & Add Real Code!
	echo "HELLO from m_test Middleware!<br>";
	echo "Passed value from Routes: `" . (is_string($passedValue) ? $passedValue : '<No Value Passed>') . "`<br>";
};
