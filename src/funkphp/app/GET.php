<?php
// src/funkphp/app/GET.php - FunkPHP | FunkCLI recreated it 2026-08-10 04:31:02

/** @var FunkPHP $APP */
$APP->ROUTES()
    ->GET()
    ->_('GLOBAL METHOD LEVEL DEFAULTS')
    ->setParamRule("ida", "/[\d]+/")
    ->setNonces("settings_form_nonce")
    ->setHeader("X-Frame-Options", "DENY")
    // --------------------------------------------------------------------------
    // STATIC & PUBLIC ROUTES
    // --------------------------------------------------------------------------
    ->ROUTE("/")
    ->setAlias("home_page")
    //->setCache(['ttl' => 3600, 'strategy' => 'public'])
    ->pipeFunction("test.test")
    ->pipeResponse("page:test")

    ->ROUTE("/about-us")
    ->setAlias("about_page")
    //->setCache(['ttl' => 86400, 'strategy' => 'static'])
    ->pipeFunction("test.test")
    ->pipeResponse("page:test")

    // --------------------------------------------------------------------------
    // USER MANAGEMENT & POLYMORPHIC ROUTING
    // --------------------------------------------------------------------------
    ->ROUTE("/users")
    ->setAlias("user_list")
    ->pipeMiddleware("auth")
    ->pipeMiddleware("log_access")
    //->setRateLimit(['requests' => 60, 'window' => 60])
    //->pipeValidation("validate_user_query_params")
    //->pipeQuery("get_all_users_query")
    ->pipeResponse("json:data")

    ->ROUTE("/users/:identifier")
    ->setAlias("user_detail")
    ->setParamRulePolymorphic(
        "identifier",
        "by_id",
        "/^\d+$/",
        "by_username",
        "/^[a-zA-Z0-9_-]{3,20}$/",
        "by_uuid",
        "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/"
    )
    ->pipeMiddleware("auth")
    //->pipeCompiledValidation("compiled_user_identifier_check")
    //->pipeCompiledQuery("compiled_get_user_by_polymorphic_id")
    ->pipeResponse("json:test")

    ->ROUTE("/users/:identifier/settings")
    ->setAlias("user_settings")
    ->setParamRule("identifier", "/^\d+$/", "0")
    ->pipeMiddleware("auth")
    ->setExcludeMiddlewares("log_access")
    ->setNonces("settings_form_nonce")
    ->setHeader("Cache-Control", "no-store, private")
    ->pipeFunction("test.test")

    // --------------------------------------------------------------------------
    // API & DATA EXPORTS
    // --------------------------------------------------------------------------
    ->ROUTE("/api/v1/analytics/overview")
    ->setAlias("api_analytics_overview")
    ->pipeMiddleware("auth")
    //->setRateLimit(['requests' => 1000, 'window' => 3600])
    //->pipeCompiledSQL("compiled_fetch_monthly_analytics_sql")
    ->setCSP("default-src", "none")
    ->setCSP("connect-src", "self", "https://api.funkphp.com")
    ->pipeResponse("json:test")

    ->ROUTE("/api/v1/reports/export/:reportId")
    ->setAlias("api_report_export")
    ->setParamRule("reportId", "/^[a-zA-Z0-9_]+$/")
    ->pipeMiddleware("auth")
    //->pipeSQL("fetch_raw_report_data_sql")
    ->setHeader("Content-Disposition", 'attachment; filename="export.csv"')
    ->removeHeader("X-Frame-Options")
    ->setExcludeHeaders("X-Powered-By")
    ->pipeResponse("callback:testar5");
