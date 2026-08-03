<?php

$test = FunkPHP()
    ->config()
    ->setParamRule('test', '##i')
    ->setCSP("img-src", "data:", "https:")
    ->routes()
    ->GET()
    ->setParamRule('test', '##i');
dd($test);
