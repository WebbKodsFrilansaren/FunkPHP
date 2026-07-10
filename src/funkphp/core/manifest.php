<?php // src/funkphp/core/manifest.php

/* ----------------
 * FUNKPHP Manifest
 * ----------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU WANNA PLAY THE GAME OF UNDEFINED BEHAVIOR!
 * If you are currently editing this file to see if FunkCLI/FunkPHP/FunkGUI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/

return [
    'name' => 'FunkPHP',
    'version' => '1.0.0',
    'release_date' => '2026-11-11',
    'hashes' => [
        'classes' => [],
        'config' => [],
        'data' => [],
        'pipeline' => [],
        'schemas' => [],
        'snippets' => [],
        'tests' => [],
        'pages' => [
            'compiled_errors' => [
                'funkphp/pages/compiled/[errors]/403.php' => ['ecb4618bac290c5bd84063631c35931db1b0c303336fb348e332146934266b82', '2026-07-07 01:18 Local'],
                'funkphp/pages/compiled/[errors]/404.php' => ['344fdc4eb934d01af1b3df5c78384a6d861c9e3fc697ce2097c860ba22029d2d', '2026-07-07 01:18 Local'],
                'funkphp/pages/compiled/[errors]/405.php' => ['c6ecb2de90cdd71da99e02f5871b4f2e37c17efed725d3e0d38a63bdec4bc6b4', '2026-07-07 01:18 Local'],
            ],
            'compiled' => [],
        ],
        'core' => [
            'funkphp/core/c.php' => ['', ''],
            'funkphp/core/compiled_routes.php'    => ['', ''],
            'funkphp/core/CONSTANTS.php'    => ['', ''],
            'funkphp/core/functions_templates.php'    => ['', ''],
            'funkphp/core/functions.php'    => ['8b067c3b086abc7e8f7b7951efcaff20f8dc89fc67d304ea1fa0d346a3e09fc5', '2026-07-10 09:16 Local'],
            'funkphp/core/pipeline_request.php'    => ['', ''],
            'funkphp/core/pipeline_routes.php'    => ['', ''],
            'funkphp/core/tables.php'    => ['', ''],
            'funkphp/core/valid_mysql_datatypes.php'    => ['', ''],
            'funkphp/core/valid_mysql_operators.php'    => ['', ''],
        ],
        'cli' => [
            'cli/core/cli_functions.php' => ['', ''],
            'cli/core/cli_reserved.php' => ['', ''],
            'cli/core/commands/build.php' => ['', ''],
            'cli/core/commands/compile-sql.php' => ['', ''],
            'cli/core/commands/compile-validation.php' => ['', ''],
            'cli/core/commands/add-handler.php' => ['', ''],
            'cli/core/commands/add-middleware.php' => ['', ''],
            'cli/core/commands/add-pipeline.php' => ['', ''],
            'cli/core/commands/make-function.php' => ['', ''],
            'cli/core/commands/make-handler.php' => ['', ''],
            'cli/core/commands/make-middleware.php' => ['', ''],
            'cli/core/commands/make-pipeline.php' => ['', ''],
            'cli/core/commands/make-route.php' => ['', ''],
            'cli/core/commands/make-sql.php' => ['', ''],
            'cli/core/commands/make-validation.php' => ['', ''],
            'cli/core/commands/make-query.php' => ['', ''],
            'cli/core/commands/recompile.php' => ['', ''],
            'cli/core/commands/swap-.php' => ['', ''],
            'cli/core/commands/test.php' => ['', ''],
        ],
    ]
];
