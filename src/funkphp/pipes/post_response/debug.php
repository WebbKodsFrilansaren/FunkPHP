<?php

namespace funkphp\pipes\post_response;

function debug(&$c)
{
    if (isset($c['req']['prefers']) && $c['req']['prefers'] === 'json') {
        error_log("[DEBUG PIPE] Executed post-response for URI: " . ($c['req']['uri'] ?? '/'));
        return;
    }
    echo "<br/>WELCOME TO DEBUG POST-RESPONSE!";
    dd([$c['req'], $c['runtime']]);
};
