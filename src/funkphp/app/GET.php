<?php
// src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()->GET()
    ->route("/test/test2")
    ->pipeFunction("test.test")
    ->pipeMiddleware("auth")
    ->removeHeader('Content-length')
    ->setCSP('script-src', '');
