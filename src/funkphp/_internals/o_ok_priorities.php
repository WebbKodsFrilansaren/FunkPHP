<?php
// List of optional functions with priorities for the "o_ok" options where the first
// key is the function that called and returned list of "o_ok".
// The optional functions are only executed if they exist here!
// IMPORTANT: Do NOT use same order number for different functions within same scope!
$fphp_o_ok_priorities = [
    "r_match_denied_global_ips" => [
        "ilog" => 1,
        "code" => 2,
        "redirect" => 3,
    ],
    "r_match_denied_global_uas" => [
        "ilog" => 1,
        "code" => 2,
        "redirect" => 3,
    ],
    "fphp_uas_filtered_grouped" => [
        "ilog" => 1,
        "code" => 2,
        "redirect" => 3,
    ],
    "fphp_ips_filtered_grouped" => [
        "ilog" => 1,
        "code" => 2,
        "redirect" => 3,
    ],
];
