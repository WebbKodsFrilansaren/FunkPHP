<?php
return function (&$c, $passedValue = null) {

    // The $passedValue is a must for this function and is the path to where the file is
    if (!isset($passedValue) || !is_string($passedValue)) {
        $err = 'Tell The Developer: The "pl_match_denied_uas" Pipeline Function requires a valid STRING as $passedValue that is the PATH to the "<path/to/blocked_uas_list.php>" file. It concatenates it with constant `ROOT_FOLDER` which is the root of the FunkPHP installation. For example: `ROOT_FOLDER . "/config/blocked/blocked_uas_list.php"`';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    // Try parse UA and check if it is valid
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    if (!isset($ua) || !is_string($ua) || $ua === "") {
        $err = 'Tell The Developer: The User Agent (UA) is either missing or invalid. Make sure Your Client sends a valid User Agent string in the "User-Agent" HTTP Header!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    // For example: $passedValue = '/config/blocked/blocked_uas.php';
    $uas_path = ROOT_FOLDER  . $passedValue;
    if (!is_readable($uas_path)) {
        $err = 'Tell The Developer: Failed to Load List of Blocked User Agents from the provided $passedValue String. Make sure the File Exists and returns a valid ARRAY of BLOCKED User Agents!';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }
    $uas_path = include $uas_path;

    // Must be an associative array only!
    if (
        !is_array($uas_path)
        || empty($uas_path)
        || array_is_list($uas_path)
    ) {
        $err = 'Tell The Developer: The "config/blocked/blocked_uas.php" File must return a valid NON-EMPTY ASSOCIATIVE ARRAY of BLOCKED User Agents, where each key is a part of the User Agent (UA) string to BLOCK. For example: ["badbot" => [], "evilscanner" => [], "maliciousua" => [], "and_so_on" => []]. This is because it iterates through each key (`"unique_ua_key" => []`) and uses "str_contains()" to check if the UA contains any of the BLOCKED parts.';
        funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
    }

    // Finally, check if the UA contains any of the BLOCKED parts, strotolower is expensive
    // so only do it when we know we have a valid UA and a valid array of BLOCKED UAs
    $ua = mb_strtolower($ua);
    foreach (array_keys($uas_path) as $deniedUa) {
        if (str_contains($ua, $deniedUa)) {
            $err = 'Access Denied: Your User Agent (UA) has been BLOCKED From Accessing This Resource!';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '403'], $err], 403);
        }
    }
    return;
};
