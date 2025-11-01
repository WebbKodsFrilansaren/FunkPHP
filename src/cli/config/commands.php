<?php // src/cli/commands.php - FunkCLI Command Configurations File
// Each Command needs to have its own File inside of src/cli/commands/
// The key uses ":" while the file is "-" to separate the exact same words!
// Create a 'args' key as the subkey for the `command:subcommand` key
// and then create a key for each argument that the command needs!
/* See example below for command file "src/cli/commands/make-route.php":
    'make:route' => [
        'args' => [
            'method/route' => [
                'prompt' => 'Create New Route File',
                'regex' => $cliRegex['methodRouteRegex'],
                'required' => true,
                'default' => false,
                'help' => null,
                'prefix' => 'r:'
                'external_callable_validator' => 'method_route', // Name of the function in external_callable_validators.php
            ]
        ]
    ],
    It only uses the argument 'method/route' that is defined in the 'args' subkey!
    IMPORTANT: Your regex should start with a `prefix:rest_of_Regex` format so you
    do not end up matching other arguments that are not meant for this command!
    Example:'methodRouteRegex' => '/^r:(([a-z]+\/)|([a-z]+(\/[:]?[a-zA-Z0-9_-]+)+))$/i',
    The 'prefix' key is to add the prefix to the user input before validation since
    in interactive mode you should not need to write the prefix yourself!
*/
/*
    SUPER IMPORTANT: `aliases` key is a reserved key that MUST be defined since it
    is used to define command aliases (shortcuts) for commands defined below it.
    If not used just define it as an empty array like so: 'aliases' => []
*/
return [
    'aliases' => [
        'rc' => 'recompile',
        'make:r' => 'make:route',
        'make:rutt' => 'make:route',
        'make:h' => 'make:handler',
        'make:mw' => 'make:middleware',
        'make:pl' => 'make:pipeline',
    ],
    'commands' => [
        'make:route' => [
            'args' => [
                'method/route' => [
                    'prompt' => 'Enter `method/route` to create or target an existing METHOD/Route (e.g., "get/" (this creates the root), "get/users", "post/users/:id" where `:id` is a dynamic param, etc.):',
                    'regex' => $cliRegex['methodRouteRegex'],
                    'required' => true,
                    'default' => null,
                    'help' => 'If you want the root of a HTTPS Method, just use the method followed by a slash (e.g., "get/" or "post/").Notice otherwise that you NEVER end with the slash unless it is the root route for that method. OK:`get/users`. NOT OK:`get/users/`. You also have shorthands for the different HTTPS METHODS: "g|ge" for "get", "po|pos" for "post", "pu|put" for "put", "d|del" for "delete", "pa|pat" for "patch". Remember that you cannot use the same ":dynamic_param" parameter(s) more than once in the same route! OK:`get/users/:id/posts/:post_id`. NOT OK:`get/users/:id/posts/:id`.',
                    'prefix' => 'r:',
                    'external_callable_validator' => 'method_route',
                ],
                'folder/file/fn' => [
                    'prompt' => 'Enter `Folder=>File=>Function` to create a folder with a file inside of it with a new function inside of it (e.g., "users=>user_file=>func" meaning `src/funkphp/routes/users/user_file.php` would be created with the named function `func(&$c, $passedValue = null){}` inside of it - it will be added to the created/target method/route if it all went OK!):',
                    'regex' => $cliRegex['folderFileFnRegex'],
                    'required' => false,
                    'default' => null,
                    'help' => 'The `Folder` is the folder in src/funkphp/routes/{folder} whereas the `File` is the file inside of that folder (without the .php extension). The optional `Function` is the function inside of that file that will be called for this route. If you do not provide a `Function`, the `File` name will be used as the function name. If you do not provide a `Folder`, the file will be created in src/funkphp/routes/ (the root routes folder). Example 1: "fff:users=>user_file=>func" creates src/funkphp/routes/users/user_file.php with function func().',
                    'prefix' => 'fff:',
                    'external_callable_validator' => null,
                ],
            ],
            'config' => [
                // Add any special config for this command or its sub-commands here!
            ],
        ],
        'make:handler' => [
            'args' => [
                'method/route' => [
                    'prompt' => 'Enter `method/route` to create or target an existing METHOD/Route (e.g., "get/" (this creates the root), "get/users", "post/users/:id" where `:id` is a dynamic param, etc. - the provided `Folder=>File=>Function` will be added to it if it was successfully created!):',
                    'regex' => $cliRegex['methodRouteRegex'],
                    'required' => false,
                    'default' => null,
                    'help' => 'If you want the root of a HTTPS Method, just use the method followed by a slash (e.g., "get/" or "post/").Notice otherwise that you NEVER end with the slash unless it is the root route for that method. OK:`get/users`. NOT OK:`get/users/`. You also have shorthands for the different HTTPS METHODS: "g|ge" for "get", "po|pos" for "post", "pu|put" for "put", "d|del" for "delete", "pa|pat" for "patch". Remember that you cannot use the same ":dynamic_param" parameter(s) more than once in the same route! OK:`get/users/:id/posts/:post_id`. NOT OK:`get/users/:id/posts/:id`.',
                    'prefix' => 'r:',
                    'external_callable_validator' => 'method_route',
                ],
                'folder/file/fn' => [
                    'prompt' => 'Enter `Folder=>File=>Function` to create a folder with a file inside of it with a new function inside of it (e.g., "users=>user_file=>func" meaning `src/funkphp/routes/users/user_file.php` would be created with the named function `func(&$c, $passedValue = null){}` inside of it):',
                    'regex' => $cliRegex['folderFileFnRegex'],
                    'required' => true,
                    'default' => null,
                    'help' => 'The `Folder` is the folder in src/funkphp/routes/{folder} whereas the `File` is the file inside of that folder (without the .php extension). The optional `Function` is the function inside of that file that will be called for this route. If you do not provide a `Function`, the `File` name will be used as the function name. If you do not provide a `Folder`, the file will be created in src/funkphp/routes/ (the root routes folder). Example 1: "fff:users=>user_file=>func" creates src/funkphp/routes/users/user_file.php with function func().',
                    'prefix' => 'fff:',
                    'external_callable_validator' => null,
                ],
            ],
            'config' => [
                // Add any special config for this command or its sub-commands here!
            ],
        ],
        'make:middleware' => [
            'args' => [
                'middleware_name' => [
                    'prompt' => 'Enter Middleware Name (you can omit the automatically added `mw_` prefix - e.g., "auth" creates `mw_auth` Middleware in `src/funkphp/middlewares/mw_auth.php`):',
                    'regex' => $cliRegex['nameOnlyRegex'],
                    'required' => true,
                    'default' => null,
                    'help' => 'The Middleware Name must start with the Regex:`[a-z_]`. After that the only valid characters to use are the following Regex:`[a-z0-9_]`. The actual Middleware File created will be prefixed with `mw_` automatically - unless you provide mw_ - so if you provide "auth" as the name, the created file will be `mw_auth.php` in `src/funkphp/middlewares/` Folder with a skeleton Middleware Anonymous Function inside of it:`return function(&$c, $passedValue = null){}`. You can also skip File Extension since it will be added automatically.',
                    'prefix' => 'n:',
                    'external_callable_validator' => null,
                ],
                'method/route' => [
                    'prompt' => 'Enter an existing `method/route` to add the Middleware to (e.g., "get/" (this targets the root), "get/users", "post/users/:id" where `:id` is a dynamic param, etc. - the found/created Middleware will be added to it if the Middleware was successfully found/created!):',
                    'regex' => $cliRegex['methodRouteRegex'],
                    'required' => false,
                    'default' => null,
                    'help' => 'If you want the root of a HTTPS Method, just use the method followed by a slash (e.g., "get/" or "post/").Notice otherwise that you NEVER end with the slash unless it is the root route for that method. OK:`get/users`. NOT OK:`get/users/`. You also have shorthands for the different HTTPS METHODS: "g|ge" for "get", "po|pos" for "post", "pu|put" for "put", "d|del" for "delete", "pa|pat" for "patch". Remember that you cannot use the same ":dynamic_param" parameter(s) more than once in the same route! OK:`get/users/:id/posts/:post_id`. NOT OK:`get/users/:id/posts/:id`.',
                    'prefix' => 'r:',
                    'external_callable_validator' => 'method_route',
                ],
            ],
            'config' => [
                // Add any special config for this command or its sub-commands here!
            ],
        ],
        'make:pipeline' => [
            'args' => [
                'pipeline_name' => [
                    'prompt' => 'Enter Pipeline Name (e.g., "pl_json_api", "json_api", or "security_flow";  `pl_` is added automatically as prefix):',
                    'regex' => $cliRegex['nameOnlyRegex'],
                    'required' => true,
                    'default' => null,
                    'help' => 'The Pipeline Name must start with the Regex:`[a-z_]`. After that the only valid characters to use are the following Regex:`[a-z0-9_]`. The actual Pipeline File created will be prefixed with `pl_` automatically - unless you provide pl_ - so if you provide "auth" as the name, the created file will be `pl_auth.php` in `src/funkphp/pipeline/request` OR `src/funkphp/pipeline/post-request` Folder (choose with next CLI Argument) with a skeleton Pipeline Anonymous Function inside of it:`return function(&$c, $passedValue = null){}`. You can also skip File Extension since it will be added automatically.',
                    'prefix' => 'n:',
                    'external_callable_validator' => null,
                ],
                'pipeline_type' => [
                    'prompt' => 'Enter Pipeline Type (req|post) where `req` means `request` and `post` means `post-response`. This is needed to place it in correct Pipeline Subdirectory. Default is',
                    'regex' => $cliRegex['plTypeRegex'],
                    'required' => true,
                    'default' => 'req',
                    'help' => 'The pipeline type determines which folder the file is created in: "req" for `src/funkphp/pipeline/request/` or "post" for `src/funkphp/pipeline/post-response/`. Defaults to "req".',
                    'prefix' => 'plt:',
                    'external_callable_validator' => null,
                ]

            ],
            'config' => [
                // Add any special config for this command or its sub-commands here!
            ],
        ],
    ]
];
