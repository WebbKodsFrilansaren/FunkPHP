<?php // function_templates.php - Used to build customized in-built FunkPHP Functions

/**
 * ----------------------------------------------------
 * FUNKPHP FUNCTION TEMPLATES FILE DURING BUILDING STEP
 * ----------------------------------------------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkPHP will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/

// Function that starts a session if it is not already started.
// It also connects to Redis infrastructure if the session driver is set to Redis.
// This function is used to ensure that a session is available for reading
// and writing session values.
function funk_session_started_or_start_it(&$c)
{
    // If already active in this request lifecycle, exit instantly (Zero overhead)
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // Lazy infrastructure allocation: Connect to Redis/DB only when a session is actually requested!
    if (($c['SESSION']['driver'] ?? 'files') === 'redis') {
        //\funk_connect_redis_infrastructure($c);
    }
    // Configure native cookie settings right before booting
    // Pass the raw, pre-verified array straight to PHP. No runtime IF statements required!
    session_set_cookie_params([
        'lifetime' => '{{##session_lifetime##}}',
        'path' => '{{##session_path##}}',
        'domain' => '{{##session_domain##}}',
        'secure' => '{{##session_secure##}}',
        'httponly' => '{{##session_httponly##}}',
        'samesite' => '{{##session_samesite##}}',
    ]);
    // If it fails to start a session, throw an error and exit with a 500 Internal Server Error
    if (!session_start()) {
        $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
}
