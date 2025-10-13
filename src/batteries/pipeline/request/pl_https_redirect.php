<?php
return function (&$c, $passedValue = null) {
    try {
        if (
            isset($_SERVER['SERVER_NAME'])
            && $_SERVER['SERVER_NAME'] !== "localhost"
            &&  $_SERVER['SERVER_NAME'] !== "127.0.0.1"
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
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
};
