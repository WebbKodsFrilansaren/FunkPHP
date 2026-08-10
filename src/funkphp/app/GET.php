<?php
// src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->pipeHeader('content-type', 'application/json')
    ->setNoRouteMatchCallback("testar8")
    ->setParamRule("id", "/[\d]+/")
    ->pipeMiddleware("auth")
    ->pipeMiddleware("auth")
    ->ROUTE("/:id")
    ->setParamRule("id", "/[\d]+/")
    ->pipeHeader("content-type", 'text/html')
    ->removeHeader("content-typeb");
