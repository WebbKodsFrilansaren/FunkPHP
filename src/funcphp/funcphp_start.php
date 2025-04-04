<?php // ENTRY POINT OF EACH HTTPS REQUEST thanks to ".htaccess" file

include_once __DIR__ . '/functions/_includeAll.php';
include_once __DIR__ . '/dx_steps/_includeAll.php';

//REMOVE AFTER TESTING!
echo $req['ip'] . " ";
echo $req['uri'] . " ";
echo $req['query'] . " ";
echo strlen($req['uri']) . " ";

//STEP1.0: IP Filtering - check against denied IPs globally
if (r_match_denied_global_ips($req['ip'], $fphp_ip_filtered_globals)) {
    // Handle optionally set options when IP is denied
    if (ok(h_has_options($fphp_ip_filtered_globals['denied']))) {
        $options = h_extract_all_options($fphp_ip_filtered_globals['denied']);

        // Return custom code if set in the options
        if (ok($options['code'])) {
            return_code($options['code']);
        }
    }
    // Default response, change if needed
    return_code(418);
};

//STEP1.1: IP Filtering Grouped Routes - check against allowed and denied IPs grouped routes

//STEP1.2: IP Filtering Single Routes - check against allowed and denied IPs for single routes

echo "<br><br>";
echo '<br><br>Request Headers:<br>';
foreach ($_SERVER as $key => $value) {

    echo $key . ' => ' . $value . '<br>';
}

echo '<br>Response Headers:<br>';

foreach (headers_list() as $header) {
    echo $header . '<br>';
}
