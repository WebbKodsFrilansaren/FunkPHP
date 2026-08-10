<?php

$FUNK = FunkPHP();
$FUNK->CONFIG()
    ->setDebug(true, false, false, true, false, false, true)
    ->setUseVendor(true)
    ->setCompileFlag('NO_WARNINGS_ALLOWED')
    ->setBaseURLHost("wkf")
    ->setBaseURLLocal("http://wkf.com")
    ->setBaseURLOnline("https://www.funkphp.com")
    ->setBaseURLUri("/funkphp")
    ->setGroupPipeMiddlewares("test_mw", "auth", "auth2")
    ->setGroupPipePostResponse("test_post_response", "debug", "debug2")
    ->setGroupPipeRequest("test_request", "use_cors", "run_ini_sets")
    ->setGroupPipeRoute("test_routes", 'test.test', 'test.test2')
    ->setGroupPipeUserdefined('test_group', 'testar7', 'testar8')
    ->setDefaultURI_NormalizerHandler("testar1")
    ->setDefaultErrorHandler("testar2")
    ->setDefaultExceptionHandler("testar3")
    ->setDefaultRegisteredShutdownHandler("testar4")
    ->setDefaultKernelHandler("testar5")
    ->setINI_SET([
        'session.cache_limiter' => 'public',
        'session.use_strict_mode' => 8,
        'session.use_only_cookies' => 1,
        'session.cache_expire' => 30,
        'session.cookie_lifetime' => 0,
        'session.name' => 'fphp_id',
        'session.sid_length' => 192,
        'session.sid_bits_per_character' => 6,
        'display_errors' => 1,
        'display_startup_errors' => 1,
        'error_reporting' => 1,
    ])
    ->setSessionCookieOptions([
        'SESSION_DRIVER' => ' files',
        'SESSION_SECURE' => true,
        'SESSION_HTTPONLY' => true,
        'SESSION_DOMAIN' => 'funkphp',
        'SESSION_PATH' => '/',
        'SESSION_LIFETIME' => 28880,
        'SESSION_NAME' => 'fphp_test',
        'SESSION_SAMESITE' => 'Lax'
    ])
    ->setParamRule("id", "/[\d]+/", "0");

$FUNK->ROUTES()->GET()->ROUTE("/:id")->pipeFunction("group:test_routes");

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
