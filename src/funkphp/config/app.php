<?php

$FUNK = FunkPHP();

$FUNK->CONFIG()->pipeHeader("Ab: af")
    ->ROUTES()
    ->GET()
    ->setNoRouteMatchPage("a");

// 1. Inspect the FunkRoute instance ($test) to get the private $c property
// $routeReflection = new ReflectionObject($test);
// $cProperty = $routeReflection->getProperty('c'); // Name of the property holding C in FunkRoute
// $cProperty->setAccessible(true);
// $cInstance = $cProperty->getValue($test);

// // 2. Reflect on the C instance to call its private `run()` method
// $runMethod = new ReflectionMethod($cInstance, 'run');
// $runMethod->setAccessible(true); // Needed for PHP < 8.1, good practice to keep
// $runMethod->invoke($cInstance);  // Outputs: "TEST"
dd($FUNK, 'Check (private) errors->all');
