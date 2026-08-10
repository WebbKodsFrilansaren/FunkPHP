<?php
$APP->CONFIG()
    ->setDebug(true, false, false, true, false, false, true)
    ->setUseVendor(true)
    ->setCompileFlag('NO_WARNINGS_ALLOWED')
    ->setBaseURLHost("wkf")
    ->setBaseURLLocal("http://wkf.com")
    ->setBaseURLOnline("https://www.funkphp.com")
    ->setBaseURLUri("/funkphp")
    // GROUPED Pipes
    ->setGroupPipeMiddlewares("test_mw", "auth", "auth2")
    ->setGroupPipePostResponse("test_post_response", "debug", "debug2")
    ->setGroupPipeRequest("test_request", "use_cors", "run_ini_sets")
    ->setGroupPipeRoute("test_routes", 'test.test', 'test.test2')
    ->setGroupPipeUserdefined('test_group', 'testar7', 'testar8')
    // DEFAULT Global Handlers
    ->setDefaultURI_NormalizerHandler("testar1")
    ->setDefaultErrorHandler("testar2")
    ->setDefaultExceptionHandler("testar3")
    ->setDefaultKernelHandler("testar5")
    // ini_set()
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
    // session_cookie_set()
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
    // GLOBAL PARAMS
    ->setParamRule("id", "/[\d]+/", "0")
    // GLOBAL REQUEST PIPES
    ->pipeRequestFunction("use_cors")
    ->pipeRequestFunction("group:test_request")
    // GLOBAL POST_RESPONSE PIPES
    ->pipePostResponseFunction("debug")
    ->pipePostResponseFunction("group:test_post_responsea");
