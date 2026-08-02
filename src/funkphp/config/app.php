<?php

$test = FunkPHP()
    ->config()
    ->setParamRule("test", '/^t/')
    ->routes()
    ->GET()
    ->setParamRule('test', '##i')
    ->route('/:id/:test/:test2')
    ->setParamRule("test", "/^t/");

dd($test);
