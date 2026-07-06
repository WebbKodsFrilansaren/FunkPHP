<?php // FunkPHPDeployment.php | Created: 2026-07-06 18:44:07 | PHP Version: 8.3.6 | FunkPHP Version: 1.0.0 | FunkCLI Version: 1.0.0
define('FUNKPHP_DEPLOYED', true);
define('FUNKPHP_NO_VALUE', new stdClass());
define('FUNKPHP_ALLOW_INSTANCE_OVERWRITE', 1);
$c = array(
  'FUNKPHP_ONLINE' => false,
  'FUNKPHP_USE_HTTPS' => false,
  'FUNKPHP_USE_PREPARE_URI' => true,
  'FUNKPHP_USE_VENDOR' => true,
  'FUNKPHP_CUSTOM_EXCEPTION_HANDLER' => 'funk_handle_uncaught_exception',
  'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION' => 'funk_set_register_shutdown_function',
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
    'display_errors' => 1,
    'display_startup_errors' => 1,
    'error_reporting' => 1,
  ),
  'BASEURLS' =>
  array(
    'LOCAL' => 'http://webdev.local:81/funkphp',
    'ONLINE' => 'https://www.funkphp.com',
    'BASEURL' => 'localhost',
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
      'SESSION_DOMAIN' => 'webdev.local',
      'SESSION_SECURE' => false,
      'SESSION_HTTPONLY' => true,
      'SESSION_SAMESITE' => 'Lax',
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
    'base_url_absolute' => NULL,
    'base_url_relative' => NULL,
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
  'v_config' =>
  array(),
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
