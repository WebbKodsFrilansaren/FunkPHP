<?php
// pipeline_request.php - FunkPHP | FunkCLI recreated it 2026-07-08 14:22:13

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
    '<CONFIG_GLOBAL>' =>
    array(
      'global_headers' =>
      array(
        'add' =>
        array(
          0 => 'Content-Security-Policy: default-src \'none\'; img-src \'self\'; script-src \'self\'; connect-src \'none\'; style-src \'self\'; object-src \'none\'; frame-ancestors \'none\'; form-action \'self\'; font-src \'self\'; base-uri \'self\';',
          1 => 'x-frame-options: DENY',
          2 => 'x-content-type-options: nosniff',
          3 => 'x-xss-protection: 1; mode=block',
          4 => 'x-permitted-cross-domain-policies: none',
          5 => 'referrer-policy: strict-origin-when-cross-origin',
          6 => 'Access-Control-Allow-Origin: \'self\'',
          7 => 'cross-origin-resource-policy: same-origin',
          8 => 'Cross-Origin-Embedder-Policy: require-corp',
          9 => 'Cross-Origin-Opener-Policy: same-origin',
          10 => 'Expect-CT: enforce, max-age=86400',
          11 => 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload',
        ),
        'remove' =>
        array(
          "X-Powered-By",
          "Server",
          "X-AspNet-Version",
          "X-AspNetMvc-Version"
        )
      ),
      'global_sris' =>
      array(),
      'global_nonces' =>
      array(),
      'global_csp' =>
      array(),
      'global_rate_limiting' => NULL,
      'global_param_rules' =>
      array(),
      'global_default_no_route_match_response' => [
        'page' => null,
        'json' => null,
        'xml' => null,
        'text' => null,
        'callback' => null,
      ]
    ),
    'request' =>
    array(
      0 => 'pl_run_ini_sets',
      1 => 'pl_match_denied_exact_ips',
      2 => 'pl_match_denied_methods',
      3 => 'pl_match_denied_uas',
      4 => 'pl_https_kernel_dispatch',
    ),
    'post_response' =>
    array(
      0 => 'pl_debug',
    ),
  ),
);
