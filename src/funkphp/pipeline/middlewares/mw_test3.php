<?php

namespace FunkPHP\Middlewares\mw_test3;
// FunkCLI Created on 2025-11-03 14:23:00!

return function (&$c, $passedValue = null) {
	// Placeholder Comment so Regex works - Remove & Add Your Own Code!
	echo "YO A MIDDLEWARE TEST HERE! I am 'mw_test3' and the passed value is: " . $passedValue ? is_string($passedValue) ? $passedValue : json_encode($passedValue) : "NULL|Could not be turned into a string value!";
};
