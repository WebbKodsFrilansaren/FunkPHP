<?php

$test = FunkPHP()
    ->config()
    ->removeHeader("x-powered-by")
    ->routes()
    ->GET()
    ->setParamRule('test', '##i')
    ->route('/:id/:test/:test2')
    ->pipeHeader("test: test")
    ->removeHeader("test2");

dd($test);
