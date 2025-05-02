<?php // STEP 2: Globally Filter Allowed Methods, IPs and User Agents (UAs)

// Only Run Step 1 if the current step is 1 (the second step of the request)
if ($c['req']['current_step'] === 2) {
    // Match against denied and invalid methods | true = denied, false = allowed
    $FPHP_INVALID_METHOD = funk_match_denied_methods();
    if ($FPHP_INVALID_METHOD) {
        // If desired, response behavior edit as you please!
        echo "I'm a funky teapot!";
        http_response_code(418);
        exit;
    }
    unset($FPHP_INVALID_METHOD);

    // Match against denied and invalid exact IPs | true = denied, false = allowed
    $FPHP_INVALID_IP = funk_match_denied_exact_ips();
    if ($FPHP_INVALID_IP) {
        // If desired, response behavior edit as you please!
        echo "I'm a funky teapot!";
        http_response_code(418);
        exit;
    }
    unset($FPHP_INVALID_IP);

    // Match against denied UAs | true = denied, false = allowed
    $FPHP_INVALID_UA = funk_match_denied_uas_fast();
    if ($FPHP_INVALID_UA) {
        // If desired, edit response behavior as you please!
        echo "I'm a funky teapot!";
        http_response_code(418);
        exit;
    }
    unset($FPHP_INVALID_UA);

    // This is the end of Step 2, you can freely add any other checks you want here!
    // You have all global (meta) data in $c variable, so you can use it as you please!
    // Maybe you want to globally block specific Content-Type headers or something else?


    // Set next step to 3 (Step 3)
    $c['req']['next_step'] = 3;
}
// This sets next step. If you wanna do something more before that, do that before this line!
$c['req']['current_step'] = $c['req']['next_step'];
