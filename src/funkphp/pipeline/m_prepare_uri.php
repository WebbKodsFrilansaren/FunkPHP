<?php
return function (&$c) {
    $uri = $_SERVER['REQUEST_URI'];
    $fphp_BASEURL_URI = $c['BASEURLS']['BASEURL_URI'] ?? '/';
    $uri = str_starts_with($_SERVER['REQUEST_URI'], $fphp_BASEURL_URI) ? "/" . ltrim(substr(strtok($_SERVER['REQUEST_URI'], "?"), strlen($fphp_BASEURL_URI)), '/') : strtok($_SERVER['REQUEST_URI'], "?");
    if ($uri === "") {
        $uri = "/";
    }
    if ((substr($uri, -1) === "/") && substr_count($uri, "/") > 1) {
        $uri = substr($uri, 0, -1);
    }
    $c['req']['uri'] = $uri;
};
