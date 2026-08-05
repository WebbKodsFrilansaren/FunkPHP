<?php

$FUNK = FunkPHP();

$FUNK->CONFIG()
    ->setParamRule("id", '/[\d]+/');

$FUNK->ROUTES()
    ->GET()
    ->route("/:id/:id2")
    ->route("/:id/:id2/static")
    ->route("/users/:id2/profile")
    ->POST()
    ->route("/users/:id2/profile/:id");

// 1. Inspect the FunkRoute instance ($test) to get the private $c property
// $routeReflection = new ReflectionObject($test);
// $cProperty = $routeReflection->getProperty('c'); // Name of the property holding C in FunkRoute
// $cProperty->setAccessible(true);
// $cInstance = $cProperty->getValue($test);

// // 2. Reflect on the C instance to call its private `run()` method
// $runMethod = new ReflectionMethod($cInstance, 'run');
// $runMethod->setAccessible(true); // Needed for PHP < 8.1, good practice to keep
// $runMethod->invoke($cInstance);  // Outputs: "TEST"
dd($FUNK, 'Check (private) errors->all', true);
