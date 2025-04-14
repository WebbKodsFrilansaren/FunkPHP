<?php

return [
    'matched_route' => null,
    'matched_data' => null,
    'matched_params' => null,
    'matched_middlewares' => null,
    'matched_auth' => null,
    'matched_csrf' => null,
    'no_match_in' => null,
    'number_of_ran_middlewares' => 0,
    'keep_running_middlewares' => null,
    'current_middleware_running' => null,
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
