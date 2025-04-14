<?php // STEP 1: Globally Filter Allowed Methods, IPs and User Agents (UAs)

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
        "contains" => [], // see "_internals/deny_uas.php"
        "o_ok" => ["ilog=DENIED UAS blocked!", "code=418", "redirect=https://www.lol.com"],
        "o_fail" => ["code=200", "ilog=OK UA, not blocked", "redirect=https://www.lol.com"],
    ],
];
//UAS_FILTERING_GLOBALS_END_DELIMTIER//

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