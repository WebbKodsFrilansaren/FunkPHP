<?php

$test = FunkPHP()
    ->config()
    ->setSessionCookiePath("/a/a")
    ->setSessionCookieDomain("best.com")
    ->routes()
    ->GET()
    ->setParamRule('test', '##i')
    ->route('/:id/:test/:test2')
    ->setParamRule("test", "/^t/");

dd($test);
