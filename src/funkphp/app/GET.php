<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->setParamRule('id', '/[\d]{1,2}/', 0)
    ->setHeaderRemove('server')
    ->pipeMiddleware('log_access')
    ->route("/users/:id")
    ->pipeMiddleware('log_access')
    ->pipeFunction("test.test")
    ->setHeaderAdd("Content-Type", 'application/json')
    ->setHeaderRemove('server')
    ->setCSP('default-src', 'self')
    ->setAlias('test')
    //->setParamRule('id', '/a/')
    ->setCache(3600, 'redis', null, true)
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->pipeResponse('json:test')
    ->setCSP('font-src', 'nonce:test');
