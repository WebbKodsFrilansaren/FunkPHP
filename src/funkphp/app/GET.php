<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setNoRouteMatchText("nothing in GET!")
    ->setRateLimit(60, 60, 'ip', 'redis')
    ->setHeaderRemove('server')
    ->route("/users/:id/:id2")
    ->setParamRuleMismatchJSON(['err' => 'no match'], 404)
    ->setParamRuleMismatchPage('test')
    ->pipeFunctions("test.test")
    ->pipeResponse('page:test', 200);
