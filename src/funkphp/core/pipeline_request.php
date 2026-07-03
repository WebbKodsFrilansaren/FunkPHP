<?php
// pipeline_request.php - FunkPHP Framework | FunkCLI recreated it 2026-07-03 12:50:32
return  array(
  'pipeline' =>
  array(
    0 =>
    array(
      '<CONFIG_GLOBAL>' =>
      array(
        'global_headers' =>
        array(),
        'global_sris' =>
        array(),
        'global_nonces' =>
        array(),
        'global_csp' =>
        array(),
        'global_rate_limiting' => NULL,
        'global_param_rules' =>
        array(),
      ),
    ),
    'request' =>
    array(
      0 => 'pl_https_redirect',
      1 => 'pl_prepare_uri',
      2 => 'pl_run_ini_sets',
      3 => 'pl_match_denied_exact_ips',
      4 => 'pl_match_denied_methods',
      5 => 'pl_match_denied_uas',
      6 => 'pl_match_route_then_run_matched_middlewares_and_pipeline',
    ),
    'post_response' =>
    array(
      0 => 'pl_debug',
    ),
  ),
);
