<?php

$test = FunkPHP()
    ->CONFIG()
    ->setCSP('')
    ->setNonces("a")
    ->ROUTES()
    ->GET()
    ->route("/:id");

dd($test);
