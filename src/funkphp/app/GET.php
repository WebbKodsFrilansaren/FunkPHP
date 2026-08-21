<?php
// /src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()->GET()
    ->route("/")
    ->ROUTEPrefixReset()
    ->ROUTEPrefixSet("/users2")
    ->route("/test2")
    ->setExcludeMiddlewares("auth");
