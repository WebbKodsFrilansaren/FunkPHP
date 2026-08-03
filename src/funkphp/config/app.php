<?php

$test = FunkPHP()
    ->CONFIG()
    ->setCSP('report-uri', '*')
    ->setNonces("a")
    ->ROUTES()
    ->GET()
    ->route("/:id")
    ->setNonces("b");

dd($test);
