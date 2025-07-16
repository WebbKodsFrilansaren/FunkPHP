<?php
return function (&$c) {
    // Try parse (possibly proxy) IP and check if it is valid
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ($ip === null || !is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
        critical_err_json_or_html(500);
    }

    // Finally try load exact IPs to match against
    $ips_exact = include dirname(__DIR__) . '/config/blocked/blocked_ips.php';
    if ($ips_exact === false) {
        $c['err']['PIPELINE']['m_match_denied_exact_ips'][] = 'Failed to Load List of Blocked IPs!';
        critical_err_json_or_html(500);
    }
    if (isset($ips_exact[$ip])) {
        critical_err_json_or_html(500);
    }
    return;
};
