<?php

$test = FunkPHP()
    ->CONFIG()
    ->setCSP('report-uri', '*')
    ->ROUTES()
    ->GET()
    ->route("/:id");

dd($test);
