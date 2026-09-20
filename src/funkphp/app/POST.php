<?php
// src/funkphp/app/POST.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()->POST()
    ->setRateLimit(67, 69, 'ip', 'redis')
    ->pipeMiddleware('auth')
    ->route("/test")
    ->pipeFunction('test.test');
