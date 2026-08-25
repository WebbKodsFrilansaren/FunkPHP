<?php

namespace funkphp\pipes\post_response;

function debug(&$c)
{
    // Skip visual HTML debugging if the request expects JSON/binary data or if headers were already set to JSON
    $isJson = ($c['req']['prefers'] === 'json' || !empty($c['req']['accepts']['json']));

    if ($isJson) {
        // Log to error log or header instead of stdout during JSON responses
        error_log("[DEBUG PIPE] Executed post-response for URI: " . ($c['req']['uri'] ?? '/'));
        return;
    }

    // Safe to output HTML debug content
    echo "<div style='background:#111;color:#00ff00;padding:10px;font-family:monospace;'>";
    echo "&lt;THIS IS A DEBUG PIPELINE FUNCTION WHICH RUNS AFTER EVERYTHING ELSE!&gt;";
    echo "</div>\n";
};
