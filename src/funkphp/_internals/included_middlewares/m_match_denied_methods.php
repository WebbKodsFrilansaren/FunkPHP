<?php // IN-BUILT MIDDLEWARE: Deny Any Matched HTTP(S) Methods From Config File!
return function (&$c) {
    // Return null if $method is invalid method variable
    $method = $_SERVER['REQUEST_METHOD'] ?? null;
    if ($method === "" || $method === null || !is_string($method)) {
        critical_err_json_or_html(500);
    }
    $method = strtoupper($method);

    // Then check $method is a valid HTTP method
    if (!in_array($method, ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS", "HEAD"])) {
        critical_err_json_or_html(500); // Invalid HTTP method, so deny access
    }

    // Finally try load blocked methods to match against
    $methods = include dirname(dirname(__DIR__)) . '/config/BLOCKED_METHODS.php';

    if ($methods === false) {
        $c['err']['FAILED_TO_RUN_MIDDLEWARE-m_match_denied_methods'] = 'Failed to Load List of Blocked HTTP(S) Methods!';
        critical_err_json_or_html(500);
    }
    if (isset($methods[$method])) {
        critical_err_json_or_html(500);
    }
    return;
};
