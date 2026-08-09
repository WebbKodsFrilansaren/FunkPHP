<?php

$FUNK = FunkPHP();
$FUNK->CONFIG()
    ->setDebug(true, true, true)
    ->setCompileFlag('NO_WARNINGS_ALLOWED')
    ->setParamRule("id", "/a/", "mm");
$FUNK->ROUTES()
    ->GET()
    ->setParamRule("test", "/a/", "nope")
    ->ROUTE("/:iA")
    ->pipeFunction("test.test")
    ->pipeMiddleware("auth")
    ->pipeQuery("test.test")
    ->pipeSQL("s_paj.s_najs")
    ->pipeValidation("s_najs.s_paj")
    ->pipeResponse("callback:testar")
    ->setParamRule('ia', 'a/[\d]+/')
    ->pipeCompiledQuery("a")
    ->pipeHeader("test-test: a");

return $FUNK;
// 1. Inspect the FunkRoute instance ($test) to get the private $c property
// $routeReflection = new ReflectionObject($test);
// $cProperty = $routeReflection->getProperty('c'); // Name of the property holding C in FunkRoute
// $cProperty->setAccessible(true);
// $cInstance = $cProperty->getValue($test);

// // 2. Reflect on the C instance to call its private `run()` method
// $runMethod = new ReflectionMethod($cInstance, 'run');
// $runMethod->setAccessible(true); // Needed for PHP < 8.1, good practice to keep
// $runMethod->invoke($cInstance);  // Outputs: "TEST"
