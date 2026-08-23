<?php
// /src/funkphp/app/CONFIG.php - FunkPHP | FunkCLI recreated it 2026-08-13 14:23:01

/** @var FunkPHP $APP */
$APP->CONFIG()
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->setDefaultKernelHandler("test")
    ->setDefaultURI_NormalizerHandler('testar0')
    ->pipePostResponseFunction('debug')
    ->setDebug(true, false, false)
;
