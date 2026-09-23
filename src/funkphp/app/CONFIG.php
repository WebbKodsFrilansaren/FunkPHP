<?php
// /src/funkphp/app/CONFIG.php - FunkPHP | FunkCLI recreated it 2026-08-13 14:23:01

/** @var FunkPHP $APP */
$APP->CONFIG()
    ->setCompileFlag('ALLOW_GHOST_ROUTES')
    ->setDebug(true, false, true)
    ->setCompileFlag('OUTPUT_OVERRIDE_DEBUG')
    ->setNoRouteMatchJSON(["err" => "nothing found"], 404)
    ->setNoRouteMatchPage('test', 404)
    ->setNoRouteMatchText("nothing in GLOBAL!")
    ->setNoRouteMatchCallback('test')
    //->setCustomExceptionHandler('test')
    // //->setDisablePostResponseOnNoMatch(true)
    // //->setParamRule('id2', '*')
    // ->setINI_SET([
    //     'session.cache_limiter' => 'public',
    //     'session.use_strict_mode' => 8,
    //     'session.use_only_cookies' => 1,
    //     'session.cache_expire' => 30,
    //     'session.cookie_lifetime' => 0,
    //     'session.name' => 'fphp_id',
    //     'session.sid_length' => 192,
    //     'session.sid_bits_per_character' => 6,
    //     'display_errors'          => 1,
    //     'display_startup_errors'  => 1,
    //     'error_reporting'         => (string)E_ALL,
    // ])
    // //->setHeaderAdd('Allow', 'all')
    // ->pipeMiddlewares('log_access', 'auth')
    // ->setRateLimit(60, 60, 'ip', 'redis')
    ->pipeRequestFunction('req_test');
    // ->pipePostResponseFunction('debug');
