<?php
// Step 1: IP Filtering - check against allowed and denied IPs
// Step 1.2: IP Filtering Groups - check against allowed and denied IPs for single routes
// This means checking if current IP can access the web app or not for single routes
// "o" = options

//IP_FILTERING_SINGLES_START_DELIMTIER//
$fphp_ip_filtered_singles_allow = [
    'GET/' => [],
    'GET/test/' => [],
];
$fphp_ip_filtered_singles_deny = [
    'GET/' => [],
    'GET/test/' => [],
];
//IP_FILTERING_SINGLES_END_DELIMTIER//
