<?php

$test = FunkPHP()
    ->config()
    ->setSRIInternal(["test" => 'shasodhasdoiuashdoashd', "test2" => 'shasodhasdoiuashdoashd'])
    ->setSRIExternal(["test" => ['url' => 'https://yas.com', 'hash' => 'shasodhasdoiuashdoashd'], "test2" => ['url' => 'https://yas.com', 'hash' => 'shasodhasdoiuashdoashd']])
    ->routes()
    ->GET()
    ->setParamRule('test', '##i')
    ->route('/:id/:test/:test2')
    ->setParamRule("test", "/^t/");

dd($test);
