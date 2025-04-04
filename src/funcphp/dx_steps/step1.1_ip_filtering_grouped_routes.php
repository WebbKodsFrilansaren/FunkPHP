<?php
// Step 1: IP Filtering - check against allowed and denied IPs
// Step 1.1: IP Filtering Grouped Routes - check against allowed and denied IPs grouped routes
// This means checking if current IP can access certain grouped routes or not
// "o" = options

//IP_FILTERING_GROUPS_START_DELIMTIER//
$fphp_ip_filtered_groups_allow = [
    'GET/' => [],
    'GET/test/' => [],
];
$fphp_ip_filtered_groups_deny = [
    'GET/' => [],
    'GET/test/' => [],
];
//IP_FILTERING_GROUPS_END_DELIMTIER//
