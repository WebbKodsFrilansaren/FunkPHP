<?php

namespace funkphp\pipeline\request\pl_https_redirect;

function pl_https_redirect(&$c)
{
    try {
        if (defined("FUNKPHP_IS_LOCAL") && !FUNKPHP_IS_LOCAL) {
            // Check if the connection is unencrypted
            $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1))
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            if (!$isHttps) {
                $host = $_SERVER['HTTP_HOST'] ?? 'www.funkphp.com';
                $uri  = $_SERVER['REQUEST_URI'] ?? '/';
                // Reconstruct the URL completely dynamically
                header("Location: https://" . $host . $uri, true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        $err = 'Tell the Developer: The HTTPS Redirect Pipeline Function failed to Redirect to HTTPS!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
};
