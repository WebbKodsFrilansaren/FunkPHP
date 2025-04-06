<?php
// Step 1: IP Filtering - check against allowed and denied IPs
// Step 1.0: IP Filtering Groups - check against denied IPs globally
// This means checking if current IP can access the web app or not
// "o_ok" = options when ok result, "o_fail" = options when not ok result

//IP_FILTERING_GLOBALS_START_DELIMTIER//
$fphp_ips_filtered_globals = [
    'denied' => [
        "ip_starts_with" => ["127.0"],
        "ip_ends_with" => [],
        "exact_ips" => [],
        "o_ok" => ["code=418", "ilog=DENIED IP blocked!", "redirect=https://www.lol.com"],
        "o_fail" => ["ilog=IP IS OK, not blocked", "redirect=https://www.lol.com", "code=201"],
    ],
];
//IP_FILTERING_GLOBALS_END_DELIMTIER//

//UAS_FILTERING_GLOBALS_START_DELIMTIER//
$fphp_uas_filtered_globals = [
    'denied' => [
        "contains" => $fphp_denied_uas_ais, // see "_internals/deny_uas.php"
        "o_ok" => ["ilog=DENIED UAS blocked!", "code=418", "redirect=https://www.lol.com"],
        "o_fail" => ["code=200", "ilog=OK UA, not blocked", "redirect=https://www.lol.com"],
    ],
];
//UAS_FILTERING_GLOBALS_END_DELIMTIER//
