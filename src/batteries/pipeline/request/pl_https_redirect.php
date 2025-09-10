<?php
return function (&$c, $passedValue = null) {
    try {
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== "localhost" &&  $_SERVER['SERVER_NAME'] !== "127.0.0.1") {
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
                global $c;
                // We check if the url ended in "/" and if so we remove it
                $onlineURL = $c['BASEURLS']['ONLINE'] ? rtrim($c['BASEURLS']['ONLINE'], "/") : $c['BASEURLS']['ONLINE'];
                header("Location: $onlineURL" . $_SERVER['REQUEST_URI'], true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        $c['err']['PIPELINE']['REQUEST']['pl_https_redirect'][] = 'Failed to Redirect to HTTPS! ' . $e->getMessage();
        critical_err_json_or_html(500);
    }
};
