<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setNoRouteMatchText("nothing in GET!")
    ->setNoRouteMatchPage('test')
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->setHeaderRemove('server')
    ->route("/users")
    ->route("/users/:id")
    ->setParamRuleMismatchJSON(['err' => 'no match'], 404)
    ->route("/users/static/even/longer/uri")
    ->pipeFunctionsThenResponse('test.test', 'page:test', 200);
