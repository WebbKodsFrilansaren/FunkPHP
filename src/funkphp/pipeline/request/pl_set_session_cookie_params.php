<?php
return function (&$c, $passedValue = null) {

    // The  $passedValue is not used in this function, instead we tell the developer
    // to refer to the $c['COOKIES'] array in the config/_all.php file to configure!
    if (isset($passedValue)) {
        $err = 'Tell The Developer: The "pl_set_session_cookie_params" Pipeline Function does NOT accept Any $passedValue. Instead, refer to the `$c["COOKIES"] in the Configuration File to Configure Your Session Cookies!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }

    // Grab Configured Session Cookies Config (FUNKPHP_IS_LOCAL is from config/_all.php)
    $isLocal = FUNKPHP_IS_LOCAL ?? false;
    $validSameSiteValues = ['Lax', 'Strict', 'None'];
    $cookieParams = [
        'lifetime' => $c['COOKIES']['SESSION_LIFETIME'] ?? null,
        'path'     => $c['COOKIES']['SESSION_PATH'] ?? null,
        'domain'   => $c['COOKIES']['SESSION_DOMAIN'] ?? null,
        'secure'   => $c['COOKIES']['SESSION_SECURE'] ?? null,
        'httponly' => $c['COOKIES']['SESSION_HTTPONLY'] ?? null,
        'samesite' => $c['COOKIES']['SESSION_SAMESITE'] ?? null,
    ];

    // Validate 'lifetime'- must be set and a valid INTEGER >= 0
    if (
        !isset($cookieParams['lifetime'])
        || !is_int($cookieParams['lifetime'])
        || $cookieParams['lifetime'] < 0
    ) {
        $err = 'Tell The Developer: The "lifetime" value in the `$c["COOKIES"]` array (that is: $c["COOKIES"] => "SESSION_LIFETIME") in the Configuration File must be set to a valid INTEGER that is either 0 or larger than 0. Using 0 means the cookie will expire when the browser is closed.';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Validate 'path' - must be set and a valid STRING starting with '/'
    else if (
        !isset($cookieParams['path'])
        || !is_string($cookieParams['path'])
        || !str_starts_with($cookieParams['path'], '/')
    ) {
        $err = 'Tell The Developer: The "path" value in the `$c["COOKIES"]` array (that is: $c["COOKIES"] => "SESSION_PATH") in the Configuration File must be set to a valid STRING starting with `/`. Using only ' / ' means the cookie is available within the entire domain which is recommended if it is an API or a Member Website where authentication/authorization is required right from the start.';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Validate 'path' - must be set and a valid STRING (empty means current domain)
    else if (
        !isset($cookieParams['domain'])
        || !is_string($cookieParams['domain'])
    ) {
        $err = 'Tell The Developer: The "domain" value in the `$c["COOKIES"]` array (that is: $c["COOKIES"] => "SESSION_DOMAIN") in the Configuration File must be set to a valid non-empty STRING. Using "localhost" for local development and your actual domain name for production is recommended.';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Validate 'secure' - must be set and a valid BOOLEAN (local can optionally use https but prod never only just http)
    else if (
        !isset($cookieParams['secure'])
        || !is_bool($cookieParams['secure'])
        || ($isLocal === false && $cookieParams['secure'] === false)
    ) {
        $err = 'Tell The Developer: The "secure" value in the `$c["COOKIES"]` array (that is: $c["COOKIES"] => "SESSION_SECURE") in the Configuration File must be set to a valid BOOLEAN. It should be set to `false` for local development (localhost) and `true` for production (live websites with HTTPS). Setting it to `true` on localhost will prevent the cookie from being set.';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Validate 'httponly' - must be set and always a true boolean since
    // it never makes sense to ever set it false, whether dev or prod
    else if (!isset($cookieParams['httponly']) || $cookieParams['httponly'] !== true) {
        $err = 'Tell The Developer: The "httponly" value in the `$c["COOKIES"]` array (that is: $c["COOKIES"] => "SESSION_HTTPONLY") in the Configuration File must be set to the BOOLEAN value `true`. This is for security reasons to help mitigate the risk of client side script accessing the protected cookie data. These days it should always be set to `true` no matter what.';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Validate 'samesite' - should be a valid STRING of either 'Lax', 'Strict' or 'None'
    else if (
        !isset($cookieParams['samesite'])
        || !is_string($cookieParams['samesite'])
        || !in_array($cookieParams['samesite'], $validSameSiteValues, true)
        || ($cookieParams['samesite'] === 'None' && !$cookieParams['secure'])
    ) {
        $err = 'Tell The Developer: The "samesite" value in the `$c["COOKIES"]` array (that is: $c["COOKIES"] => "SESSION_SAMESITE") in the Configuration File must be set to one of the following STRING values: "Lax", "Strict" or "None". It is recommended to use "Lax" for local development (localhost) and "Strict" for production (live websites with HTTPS). Only use "None" if you truly must due to the use of iframes, cross-site tracking, or third-party cookies. Remember that if you use "None", the "secure" value must be set to `true` as well. If you have set `None` but are seeing this, then `secure` is probably false when it should be set to `true`. Also check your SSL/TLS Certificates for your HTTPS Production Deployment!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }

    // Here we have already validated all the Session
    // Cookie Params in the config/_all.php file!
    session_set_cookie_params($cookieParams);
};
