<?php
return function (&$c, $passedValue = null) {
    session_set_cookie_params([
        'lifetime' => $c['COOKIES']['SESSION_LIFETIME'],
        'path' => $c['COOKIES']['SESSION_PATH'],
        'domain' => $c['BASEURLS']['BASEURL'],
        'secure' => $c['COOKIES']['SESSION_SECURE'],
        'httponly' => $c['COOKIES']['SESSION_HTTPONLY'],
        'samesite' => $c['COOKIES']['SESSION_SAMESITE'],
    ]);
};
