<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setNoRouteMatchPage('test')
    ->setRateLimit()
    // ->route("/")
    // ->pipeFunction('test.test')
    ->route("/test/test-2")
    ->pipeFunction('test.test')
    ->route("/test/:id2")
    ->pipeFunction('test.test')
    ->route("/test/id2/id3")
    ->pipeFunction('test.test')
    ->route("/test/id2/:id3")
    ->pipeFunction('test.test')
    ->route("/test/:id2/:id3")
    ->pipeFunction('test.test');
