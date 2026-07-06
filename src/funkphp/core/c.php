<?php
// c.php - FunkPHP | FunkCLI recreated it 2026-07-05 13:28:42

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
require_once __DIR__ . '/CONSTANTS.php';
// GLOBAL CONFIGURATIONS in "$c" variable in "funkphp/funkphp_start.php"
// Configure as needed using FunkCLI and/or FunkGUI!
// IMPORTANT: Do NOT store sensitive data here (e.g passwords/API-keys)

return array(
  !defined('FUNKPHP_DEPLOYED') ? define('FUNKPHP_DEPLOYED', false) : null,
  !defined('FUNKPHP_IS_LOCAL') ? define('FUNKPHP_IS_LOCAL', true) : null,
  !defined('FUNKPHP_LOCAL') ? define('FUNKPHP_LOCAL', 'http://localhost/funkphp/src/public_html/') : null,
  !defined('FUNKPHP_ONLINE') ? define('FUNKPHP_ONLINE', 'https://www.funkphp.com/') : null,
  'FUNKPHP_ONLINE' => false,
  'INI_SETS' =>
  array(
    'session.cache_limiter' => 'public',
    'session.use_strict_mode' => 8,
    'session.use_only_cookies' => 1,
    'session.cache_expire' => 30,
    'session.cookie_lifetime' => 0,
    'session.name' => 'fphp_id',
    'session.sid_length' => 192,
    'session.sid_bits_per_character' => 6,
    'display_errors' => FUNKPHP_IS_LOCAL ? 1 : 0,
    'display_startup_errors' => FUNKPHP_IS_LOCAL ? 1 : 0,
    'error_reporting' => FUNKPHP_IS_LOCAL ? E_ALL : 0,
  ),
  'BASEURLS' =>
  array(
    'LOCAL' => FUNKPHP_LOCAL,
    'ONLINE' => FUNKPHP_ONLINE,
    'BASEURL' => FUNKPHP_IS_LOCAL ? 'localhost' : FUNKPHP_ONLINE,
    'BASEURL_URI' => '/funkphp/src/public_html/',
  ),
  'SESSION' =>
  array(
    'driver' => 'files',
    'COOKIES' =>
    array(
      'SESSION_NAME' => 'fphp_id',
      'SESSION_LIFETIME' => 28800,
      'SESSION_PATH' => '/',
      'SESSION_DOMAIN' => FUNKPHP_IS_LOCAL ? 'localhost' : $_SERVER['SERVER_NAME'],
      'SESSION_SECURE' => FUNKPHP_IS_LOCAL ? false : true,
      'SESSION_HTTPONLY' => true,
      'SESSION_SAMESITE' => FUNKPHP_IS_LOCAL ? 'Lax' : 'Strict',
    ),
  ),
  '<ENTRY>' =>
  array(),
  'ROUTES' =>
  array(),
  'shared' =>
  array(),
  'custom' => NULL,
  'classes' =>
  array(
    'vendor' =>
    array(),
    'user' =>
    array(),
  ),
  'connections' =>
  array(),
  'req' =>
  array(
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'time' => $_SERVER['REQUEST_TIME'] ?? time(),
    'uri' => NULL,
    'query' => $_SERVER['QUERY_STRING'] ?? null,
    'matched_in' => NULL,
    'route' => NULL,
    'params' => NULL,
    'segments' => NULL,
    'auth' => NULL,
    'matched_config' => NULL,
    'matched_pipeline' =>
    array(),
    'matched_middlewares' => NULL,
    'skip_post_response' => false,
    'current_pipeline' => NULL,
    'next_pipeline' => NULL,
    'current_middleware' => NULL,
    'next_middleware' => NULL,
    'keep_running_pipeline' => NULL,
    'keep_running_middlewares' => NULL,
    'keep_running_exit' => NULL,
    'code' => 418,
    'log' =>
    array(),
    'ua' => NULL,
    'content_type' => NULL,
    'accept' => NULL,
    'protocol' => NULL,
  ),
  'd' => NULL,
  'v' => NULL,
  'v_ok' => NULL,
  'v_ok_files' => NULL,
  'v_config' => NULL,
  'v_data' => NULL,
  'p' => NULL,
  'files' => NULL,
  'err' =>
  array(
    'MAYBE' =>
    array(),
    'FUNCTIONS' =>
    array(),
    'CLASSES' =>
    array(),
    'CONNECTIONS' =>
    array(),
    'PIPELINE' =>
    array(),
    'MIDDLEWARES' =>
    array(),
    'PAGE' =>
    array(),
    'VALIDATION' =>
    array(),
    'SQL' =>
    array(),
    'QUERY' =>
    array(),
  ),
);
