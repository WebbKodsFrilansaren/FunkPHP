<?php
// pipeline_request.php - FunkPHP Framework | FunkCLI recreated it 2026-07-05 08:55:15

/**
 * -----------------------------------------------------
 * FUNKPHP AUTOMATICALLY GENERATED/CREATED COMPILED FILE
 * -----------------------------------------------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkPHP will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
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
