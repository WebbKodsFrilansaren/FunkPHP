<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->pipeMiddleware('log_access')
    ->route("/users")
    ->pipeMiddleware("auth2")
    ->route("/users/:id")
    ->pipeMiddleware("log_access")
    ->route("/users/:id/test")
    ->pipeMiddleware("auth");
