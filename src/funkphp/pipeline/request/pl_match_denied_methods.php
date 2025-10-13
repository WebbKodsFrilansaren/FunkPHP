<?php // IN-BUILT MIDDLEWARE: Deny Any Matched HTTP(S) Methods From Config File!
return function (&$c, $passedValue = null) {
    // No need to do anything if $passedValue is null
    if ($passedValue === null) {
        return;
    } elseif (isset($passedValue)) {
        if (is_array($passedValue) && !empty($passedValue) && array_is_list($passedValue)) {
            $validMethods = ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS", "HEAD"];
            // Iteriate through $passedValue and validate each
            // is a non-empty string and a valid HTTP method
            foreach ($passedValue as $pm) {
                if (!is_string($pm) || empty($pm) || !in_array($pm, $validMethods)) {
                    $err = 'Tell the Developer: The Match Denied Methods Pipeline Function ran but WITHOUT a Valid Passed Value - Should Be A Non-Empty Numbered Array of Valid HTTP(S) Methods! (They must manually be uppercased!)';
                    funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
                }
            }
        } else {
            $err = 'Tell the Developer: The Match Denied Methods Pipeline Function ran but WITHOUT a Valid Passed Value - Should Be A Non-Empty Numbered Array of Valid HTTP(S) Methods! (They must manually be uppercased!)';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
        }
    }
    // Return null if $method is invalid method variable
    $method = $_SERVER['REQUEST_METHOD'] ?? null;
    if ($method === null || !is_string($method) || empty($method) || in_array(strtoupper($method), $passedValue)) {
        $err = 'Access Denied: The HTTP(S) Method `' . $method . '` is Blocked by Server Configuration!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '403'], $err], 403);
    }
    return; // All good here, continue request lifecycle
};
