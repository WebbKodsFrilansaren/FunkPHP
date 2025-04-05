<?php
// Step 1: IP Filtering - check against allowed and denied IPs
// Step 1.0: IP Filtering Groups - check against denied IPs globally
// This means checking if current IP can access the web app or not
// "o" = options

//IP_FILTERING_GLOBALS_START_DELIMTIER//
$fphp_ip_filtered_globals = [
    'denied' => [
        "ip_starts_with" => ["127.0"],
        "ip_ends_with" => [],
        "exact_ips" => [""],
        "o" => ["code=418"]
    ],
];
//IP_FILTERING_GLOBALS_END_DELIMTIER//

//IP_FILTERING_GROUPS_START_DELIMTIER//
$fphp_ip_filtered_grouped = [
    "GET/" => [
        'denied' => [
            "ip_starts_with" => [""],
            "ip_ends_with" => [],
            "exact_ips" => [""],
            "o" => [""]
        ],
        'allowed' => [
            "ip_starts_with" => [""],
            "ip_ends_with" => [],
            "exact_ips" => [""],
            "o" => [""],
        ]
    ],
];
//IP_FILTERING_GROUPS_END_DELIMTIER//