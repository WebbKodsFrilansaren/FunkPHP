<?php
return function (&$c, $passedValue = null) {
    try {
        if (
            defined("FUNKPHP_LOCAL") &&
            !FUNKPHP_LOCAL
        ) {
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
                // We check if the url ended in "/" and if so we remove it
                $onlineURL = $c['BASEURLS']['ONLINE'] ? rtrim($c['BASEURLS']['ONLINE'], "/") : $c['BASEURLS']['ONLINE'];
                header("Location: $onlineURL" . $_SERVER['REQUEST_URI'], true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        $err = 'Tell the Developer: The HTTPS Redirect Pipeline Function failed to Redirect to HTTPS!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
};
