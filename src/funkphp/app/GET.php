<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()->GET()
    ->pipeMiddleware("auth3")
    ->route("/test2")
    ->pipeMiddleware("test2")
    ->setParamRule("test", "/i/", "yas");
