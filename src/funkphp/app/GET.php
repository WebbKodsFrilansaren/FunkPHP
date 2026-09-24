<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setRateLimit()
    ->setNoRouteMatchPage('test')
    ->route("/test/:id")
    //->setParamRule('id', '*')
    ->setAlias('test_by_id')
    ->pipeFunction('test.test')
    ->route("/")
    ->pipeFunction('test.test')
    ->route("/test/test-2")
    ->pipeFunction('test.test');
