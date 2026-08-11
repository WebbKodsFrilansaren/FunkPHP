<?php
// src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->setCSP('base-uri', 'none')
    ->setNonces('test', 'test2')
    ->pipeHeader('content-type', 'application/json')
    ->setNoRouteMatchCallback("testar8")
    ->setParamRule("ida", "/[\d]+/")
    ->pipeMiddleware("auth")
    ->ROUTE("/:id")
    ->setParamRulePolymorphic('id', 'nums', '/[\d]+/', 'text', '/[a-z0-9-_]+/i')
    //->setParamRule("id", "/[\d]+/")
    ->pipeHeader("content-type", 'text/html')
    ->setCSP('base-uri', 'none')
    ->setNonces('test', 'test2')
    ->removeHeader("content-typeb");
