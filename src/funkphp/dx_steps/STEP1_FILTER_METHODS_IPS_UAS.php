<?php // STEP 1: Globally Filter Allowed Methods, IPs and User Agents (UAs)

// Match against denied and invalid methods | true = denied, false = allowed
$FPHP_INVALID_METHOD = r_match_denied_methods();
if ($FPHP_INVALID_METHOD) {
    // If desired, response behavior edit as you please!
    echo "HTTP 418: I'm a teapot!";
    http_response_code(418);
    exit;
}
unset($FPHP_INVALID_METHOD);

// Match against denied and invalid exact IPs | true = denied, false = allowed
$FPHP_INVALID_IP = r_match_denied_exact_ips();
if ($FPHP_INVALID_IP) {
    // If desired, response behavior edit as you please!
    echo "HTTP 418: I'm a teapot!";
    http_response_code(418);
    exit;
}
unset($FPHP_INVALID_IP);

// Match against denied UAs | true = denied, false = allowed
$FPHP_INVALID_UA = r_match_denied_uas_fast();
if ($FPHP_INVALID_UA) {
    // If desired, edit response behavior as you please!
    echo "HTTP 418: I'm a teapot!";
    http_response_code(418);
    exit;
}
unset($FPHP_INVALID_UA);

// This is the end of Step 1, you can freely add any other checks you want here!
// You have all global (meta) data in $c variable, so you can use it as you please!