<?php
return function (&$c) {
    // Try parse UA and check if it is valid
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    if ($ua === "" || $ua === null || !is_string($ua)) {
        critical_err_json_or_html(500);
    }
    $ua = mb_strtolower($ua);

    // Finally try load blocked UAs to match against
    $uas = include dirname(__DIR__) . '/config/blocked/blocked_uas.php';
    if ($uas === false) {
        $c['err']['PIPELINE']['m_match_denied_uas'][] = 'Failed to Load List of Blocked User Agents!';
        critical_err_json_or_html(500);
    }
    foreach (array_keys($uas) as $deniedUa) {
        if (str_contains($ua, $deniedUa)) {
            critical_err_json_or_html(500);
        }
    }
    return;
};
