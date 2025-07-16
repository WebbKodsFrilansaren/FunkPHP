<?php
return function (&$c) {
    if (!session_id() || session_status() === PHP_SESSION_NONE) {
        if (!session_start()) {
            $c['err']['FAILED_TO_START_SESSION'] = 'Failed to start session.';
        } else {
            // If session started successfully, set a session variable
            $_SESSION['FPFP_SESS_START'] = true;
        }
    }
};
