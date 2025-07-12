<?php
return function (&$c) {
    // Try parse IP and check if it is valid
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if ($ip === "" || $ip === null || !is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
        critical_err_json_or_html(500);
    }

    // Finally try load exact IPs to match against
    $ips_exact = include dirname(__DIR__) . '/config/BLOCKED_IPS.php';
    if ($ips_exact === false) {
        $c['err']['FAILED_TO_RUN_MIDDLEWARE-m_match_denied_exact_ips'] = 'Failed to Load List of Blocked IPs!';
        critical_err_json_or_html(500);
    }
    if (isset($ips_exact[$ip])) {
        critical_err_json_or_html(500);
    }
    return;
};
