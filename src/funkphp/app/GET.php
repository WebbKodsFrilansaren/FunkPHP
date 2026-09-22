<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setRateLimit()
    ->route("/")
    ->pipeFunction('test.test')
    ->route("/test")
    ->pipeFunction('test.test')
    ->route("/test/test-2")
    ->pipeFunction('test.test')
    ->route("/:id")
    ->pipeFunction('test.test')
    ->route("/test2/:id/:id2/test")
    ->pipeFunction('test.test');
