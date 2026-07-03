<?php // pipeline_routes.php - FunkPHP | FunkCLI Modified it 2026-07-03 09:43:06
return array(
  'ROUTES' =>
  array(
    'GET' =>
    array(
      '/test' =>
      array(
        'config' =>
        array(
          'route_alias' => '',
          'route_param_rules' =>
          array(),
          'route_run_middlewares_before_pipeline' => true,
          'route_headers' =>
          array(),
          'route_rate_limiting' => NULL,
          'route_cache' => NULL,
          'route_sris' =>
          array(),
          'route_nonces' =>
          array(),
          'route_csp' =>
          array(),
        ),
        'middlewares' =>
        array(
          0 => 'mw_auth',
          1 => 'mw_auth',
          2 => 'mw_auth2',
        ),
        'pipeline' =>
        array(
          0 =>
          array(
            'test' => 'test2',
          ),
        ),
      ),
      '/test/test' =>
      array(
        'config' =>
        array(
          'route_alias' => '',
          'route_param_rules' =>
          array(),
          'route_run_middlewares_before_pipeline' => true,
          'route_headers' =>
          array(),
          'route_rate_limiting' => NULL,
          'route_cache' => NULL,
          'route_sris' =>
          array(),
          'route_nonces' =>
          array(),
          'route_csp' =>
          array(),
        ),
        'middlewares' =>
        array(),
        'pipeline' =>
        array(),
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
