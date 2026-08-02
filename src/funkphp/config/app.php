<?php

$test = (FunkPHP()
    ->config()
    ->routes()
    ->GET()
    ->route("/test")
    ->pipeMiddleware('test'));

dd($test);
