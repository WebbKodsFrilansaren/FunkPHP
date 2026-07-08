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
        \funk_connect_redis_infrastructure($c);
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


function funk_use_global_default_no_route_match_response(&$c)
{ // Fast Content Negotiation Router Fallback
    //{{##comment_token##}}
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '*/*';
    $fallbackConfig = $config['method_default_no_route_match_response']
        ?? $globalConfig['global_default_no_route_match_response'];
    $chosenFormat = null;
    // PRIORITY 1: Explicit Browser requests always win
    if (str_contains($acceptHeader, 'text/html') && !empty($fallbackConfig['page'])) {
        $chosenFormat = 'page';
    }
    // PRIORITY 2: Strict API clients
    elseif (str_contains($acceptHeader, 'json') && !empty($fallbackConfig['json'])) {
        $chosenFormat = 'json';
    }
    // PRIORITY 3: Strict Data Feeds
    elseif (str_contains($acceptHeader, 'xml') && !empty($fallbackConfig['xml'])) {
        $chosenFormat = 'xml';
    }
    // PRIORITY 4: Plain text fallback
    elseif (str_contains($acceptHeader, 'text/plain') && !empty($fallbackConfig['text'])) {
        $chosenFormat = 'text';
    }
    // EXECUTION PHASE
    if ($chosenFormat && isset($fallbackConfig[$chosenFormat])) {
        $handler = $fallbackConfig[$chosenFormat];
        // Execute your built-in template renderer or user-defined function string here
        return cli_execute_fallback_handler($handler, $c);
    }
    // THE ULTIMATE ESCAPE HATCH: Pass complex edge-cases straight to the callback
    if (!empty($fallbackConfig['callback'])) {
        $callbackFn = $fallbackConfig['callback'];
        return $callbackFn($c); // The developer takes full programmatic control here
    }
    // System hard fallback if absolutely everything is unconfigured or null
    return cli_use_internal_core_404_response($c);
    //
}
