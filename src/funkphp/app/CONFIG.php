<?php
// /src/funkphp/app/CONFIG.php - FunkPHP | FunkCLI recreated it 2026-08-13 14:23:01

/** @var FunkPHP $APP */
$APP->CONFIG()
    //->setParamRule("id", "cb:test")
    ->setDefaultKernelHandler("test")
    ->pipeMiddleware("auth")
    ->setDebug(true, true, false);
