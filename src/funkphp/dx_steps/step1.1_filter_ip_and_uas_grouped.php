<?php
// Step 1: IP & UA Filtering - check against allowed and denied IPs & UAs
// Step 1.1: UA Filtering Groups - check against denied User Agents globally and grouped routes
// "o" = options

//IP_FILTERING_GROUPS_START_DELIMTIER//
$fphp_ips_filtered_grouped = [
    "GET/" => [
        'denied' => [
            "ip_starts_with" => [],
            "ip_ends_with" => [],
            "exact_ips" => [],
            "o_ok" => [],
            "o_fail" => [],
        ],
        'allowed' => [
            "ip_starts_with" => [],
            "ip_ends_with" => [],
            "exact_ips" => [],
            "o_ok" => [],
            "o_fail" => [],
        ]
    ],
    "GET/test" => [
        'denied' => [
            "ip_starts_with" => [],
            "ip_ends_with" => [],
            "exact_ips" => [],
            "o_ok" => [],
            "o_fail" => [],
        ],
        'allowed' => [
            "ip_starts_with" => [],
            "ip_ends_with" => [],
            "exact_ips" => [],
            "o_ok" => [],
            "o_fail" => [],
        ]
    ],
];
//IP_FILTERING_GROUPS_END_DELIMTIER//

//UAS_FILTERING_GROUPS_START_DELIMTIER//
$fphp_uas_filtered_grouped = [
    "GET/" => [
        'denied' => [
            "contains" => [],
            "o_ok" => [],
            "o_fail" => []
        ],
        'allowed' => [
            "contains" => ["mozilla", "chrome", "safari", "firefox", "edge", "opera", "brave", "vivaldi"],
            "o_ok" => [],
            "o_fail" => []
        ]
    ],
    "GET/test" => [
        'denied' => [
            "contains" => [],
            "o_ok" => [],
            "o_fail" => []
        ],
        'allowed' => [
            "contains" => ["mozilla", "chrome", "safari", "firefox", "edge", "opera", "brave", "vivaldi"],
            "o_ok" => [],
            "o_fail" => []
        ]
    ],
];
//UAS_FILTERING_GROUPS_END_DELIMTIER//