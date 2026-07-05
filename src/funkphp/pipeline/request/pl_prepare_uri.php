<?php

namespace funkphp\pipeline\request\pl_prepare_uri;

function pl_prepare_uri(&$c, $passedValue = null)
{
    // 1. Grab raw URI from server environment
    $rawUri = $_SERVER['REQUEST_URI'] ?? '/';

    // 2. Chop off query parameters and fragment injections instantly
    // Explode splits at '?' or '#' if a raw socket forged it
    $cleanPath = explode('?', $rawUri, 2)[0];
    $cleanPath = explode('#', $cleanPath, 2)[0];

    // 3. Resolve potential Subfolder installations (e.g., localhost/project/public/)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = dirname($scriptName);
    if ($baseUrl !== '/' && str_starts_with($cleanPath, $baseUrl)) {
        $cleanPath = substr($cleanPath, strlen($baseUrl));
    }

    // 4. Fallback safeguard: collapse duplicate slashes down to single slashes
    // Fixes Apache installations where merge_slashes isn't handling it
    $cleanPath = preg_replace('#/{2,#', '/', $cleanPath);

    // 5. Enforce clean boundary states: Strip trailing and leading slashes, then wrap in a root slash
    $cleanPath = trim($cleanPath, '/');

    // Result is guaranteed to be a uniform format: '/' or '/users' or '/blog/post/view'
    $c['req']['uri'] = ($cleanPath === '') ? '/' : '/' . $cleanPath;
};
