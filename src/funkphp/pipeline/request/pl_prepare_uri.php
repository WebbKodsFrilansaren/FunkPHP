<?php
return function (&$c, $passedValue = null) {
    // $passedValues i not supported for this pipeline function as of yet
    if (isset($passedValue)) {
        $err = 'Tell The Developer - The Pipeline Function `pl_prepare_uri` does not support passed values ($passedValue) as of yet. Please change it back to: `{INT} => ["pl_prepare_uri" => null]` in the Pipeline Configuration.';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

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
