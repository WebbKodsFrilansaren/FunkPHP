<?php
// /src/funkphp/app/CONFIG.php - FunkPHP | FunkCLI recreated it 2026-08-13 14:23:01

/** @var FunkPHP $APP */
$APP->CONFIG()
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->pipeMiddleware('log_access')
    ->setHeaderRemove('server')
    ->pipeRequestFunction('use_cors')
    ->pipePostResponseFunction('debug')
    ->setDebug(true, true, false)
    ->setCSP('default-src', 'example.com')
    ->setCSP('font-src', 'nonce:testa');
