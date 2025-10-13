<?php
return function (&$c, $passedValue = null) {
    // $passedValue is optional but must be a string otherwise
    if (!isset($passedValue)) {
        return; // All good, continue request processing!
    }
    if (!is_string($passedValue)) {
        $err = 'Tell The Developer: The "pl_match_denied_exact_ips" Pipeline Function requires a valid STRING as $passedValue that is the PATH to the "<path/to/blocked_ips_list.php>" file. It concatenates it with constant `ROOT_FOLDER` which is the root of the FunkPHP installation. For example: `ROOT_FOLDER . "/config/blocked/blocked_ips.php"`';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    // Try parse (possibly proxy) IP and check if it is valid
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ($ip === null || !is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
        $err = 'Tell the Developer: Failed to Parse Client IP Address!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    // For example: $passedValue = '/config/blocked/blocked_ips.php';
    $ips_exact = ROOT_FOLDER  . $passedValue;
    if (!is_readable($ips_exact)) {
        $err = 'Tell The Developer: Failed to Load List of Blocked Exact IPs from the provided $passedValue String. Make sure the File Exists and returns a valid ARRAY of Exact IPs!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    $ips_exact = include $ips_exact;

    // Must be an associative array only - but it can be empty
    if (
        !is_array($ips_exact)
        || (count($ips_exact) > 0 && array_is_list($ips_exact))
    ) {
        $err = 'Tell The Developer: The "<Path To Blocked Exact IPs>" File must return a valid NON-EMPTY ASSOCIATIVE ARRAY of BLOCKED Exact IPs, where each Key is the exact IP and its optional value can be a string just informing why it is blocked. For example: `[123.123.123.123 => ["scraping"]]`.';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    if (isset($ips_exact[$ip])) {
        $err = 'Access Denied: Your IP Address (' . htmlspecialchars($ip) . ') is Blocked!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '403'], $err], 403);
    }
    return; // Here it means, all good, continue request processing!
};
