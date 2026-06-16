<?php // pipeline_routes.php - FunkPHP | FunkCLI Modified it 2026-06-16 11:49:06
return array(
  'ROUTES' =>
  array(
    'GET' =>
    array(
      '/all' =>
      array(
        'config' =>
        array(
          'route_alias' => '',
          'route_headers' =>
          array(),
          'route_rate_limiting' => NULL,
          'route_cache' => NULL,
          'route_param_rules' =>
          array(),
        ),
        'middlewares' =>
        array(
          0 => 'mw_test2',
        ),
        'pipeline' =>
        array(
          0 =>
          array(
            'test' => 'test',
          ),
        ),
      ),
      '<CONFIG_METHOD>' =>
      array(
        'method_headers' =>
        array(),
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        array(),
      ),
    ),
    'POST' =>
    array(
      '<CONFIG_METHOD>' =>
      array(
        'method_headers' =>
        array(),
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        array(),
      ),
    ),
    'PUT' =>
    array(
      '<CONFIG_METHOD>' =>
      array(
        'method_headers' =>
        array(),
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        array(),
      ),
    ),
    'DELETE' =>
    array(
      '<CONFIG_METHOD>' =>
      array(
        'method_headers' =>
        array(),
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        array(),
      ),
    ),
    'PATCH' =>
    array(
      '<CONFIG_METHOD>' =>
      array(
        'method_headers' =>
        array(),
        'method_rate_limiting' => NULL,
        'method_param_rules' =>
        array(),
      ),
    ),
  ),
);
