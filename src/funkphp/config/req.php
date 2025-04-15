<?php

return [
    'current_step' => 0,
    'next_step' => 0,
    'no_match_in' => null,
    'matched_route' => null,
    'matched_route_data' => null,
    'matched_route_page' => null,
    'matched_params' => null,
    'matched_params_route' => null,
    'matched_params_data' => null,
    'matched_params_page' => null,
    'matched_middlewares' => null,
    'matched_middlewares_route' => null,
    'matched_middlewares_data' => null,
    'matched_middlewares_page' => null,
    'deleted_middlewares' => null,
    'keep_running_middlewares' => null,
    'current_middleware_running' => null,
    'next_middleware_to_run' => null,
    'matched_auth' => null,
    'matched_data' => null,
    'matched_csrf' => null,
    'matched_page' => null,
    'number_of_ran_middlewares' => 0,
    'number_of_deleted_middlewares' => 0,
    'cache_page_response' => null,
    'cache_json_response' => null,
    'code' => 418,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
    'uri' => null,
    'method' => $_SERVER['REQUEST_METHOD'] ?? null,
    'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? null,
    'query' => $_SERVER['QUERY_STRING'] ?? null,
];
