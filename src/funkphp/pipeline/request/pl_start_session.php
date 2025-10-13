<?php
return function (&$c, $passedValue = null) {
    // This pipeline function does NOt accept any $passedValue arguments!
    if (isset($passedValue)) {
        $err = 'Tell The Developer: The `pl_start_session` Pipeline Function does NOT accept any $passedValue arguments. Its sole purpose is Session Initiation, and passing data here Risks Overwriting Resumed Session Data. Kindly change back to: `$passedValue => null`!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    if (!session_id() || session_status() === PHP_SESSION_NONE) {
        if (!session_start()) {
            $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
        } else {
            // If session started successfully, set a session variable
            $_SESSION['STARTED'] = true;
        }
    }
};
