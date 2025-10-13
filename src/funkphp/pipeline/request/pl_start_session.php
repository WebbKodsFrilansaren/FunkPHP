<?php
return function (&$c, $passedValue = null) {
    if (!session_id() || session_status() === PHP_SESSION_NONE) {
        if (!session_start()) {
            $c['err']['PIPELINE']['REQUEST']['pl_start_session'][] = 'FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
            $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
        } else {
            // If session started successfully, set a session variable
            $_SESSION['FPHP_SESS_START'] = true;
        }
    }
};
