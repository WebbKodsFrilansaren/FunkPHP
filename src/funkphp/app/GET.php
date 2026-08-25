<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setNoRouteMatchText("nothing in GET!")
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->setParamRule('id', '/[\d]{1,2}/', 0)
    ->setHeaderAdd('server', 'funkphp')
    ->pipeMiddleware('log_access')
    ->route("/")
    ->route("/users/:id")
    ->pipeMiddleware('log_access')
    ->pipeFunction("test.test")
    ->setHeaderRemove('server')
    ->setCSP('default-src', 'self')
    ->setAlias('test')
    ->setCache(3600, 'redis', null, true)
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->setHeaderAdd('Content-Type', 'text/html; charset=utf-8')
    ->setCSP('font-src', 'nonce:test');
