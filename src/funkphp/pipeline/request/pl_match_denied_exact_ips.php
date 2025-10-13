<?php
return function (&$c, $passedValue = null) {
    // Try parse (possibly proxy) IP and check if it is valid
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ($ip === null || !is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
        $err = 'Tell the Developer: Failed to Parse Client IP Address!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    // Finally try load exact IPs to match against
    $ips_exact = include ROOT_FOLDER . '/config/blocked/blocked_ips.php';
    if ($ips_exact === false) {
        $c['err']['PIPELINE']['REQUEST']['m_match_denied_exact_ips'][] = 'Failed to Load List of Blocked IPs!';
        $err = 'Tell the Developer: Failed to Load List of Blocked IPs!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    if (isset($ips_exact[$ip])) {
        $err = 'Access Denied: Your IP Address (' . htmlspecialchars($ip) . ') is Blocked!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '403'], $err], 403);
    }
    return; // Here it means, all good, continue request processing!
};
