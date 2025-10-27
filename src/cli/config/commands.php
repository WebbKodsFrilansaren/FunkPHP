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
return [
    'make:route' => [
        'args' => [
            'method/route' => [
                'prompt' => 'Enter `method/route` for the new route (e.g., "get/" (this creates the root), "get/users", "post/users/:id" etc.):',
                'regex' => $cliRegex['methodRouteRegex'],
                'required' => true,
                'default' => null,
                'help' => 'If you want the root of a HTTPS Method, just use the method followed by a slash (e.g., "get/" or "post/").Notice otherwise that you NEVER end with the slash unless it is the root route for that method. OK:`get/users`. NOT OK:`get/users/`. You also have shorthands for the different HTTPS METHODS: "g|ge" for "get", "po|pos" for "post", "pu|put" for "put", "d|del" for "delete", "pa|pat" for "patch". Remember that you cannot use the same ":dynamic_param" parameter(s) more than once in the same route! OK:`get/users/:id/posts/:post_id`. NOT OK:`get/users/:id/posts/:id`.',
                'prefix' => 'r:'
            ],
            'folder/file/fn' => [
                'prompt' => 'Enter `Folder=>File=>Function` for the new route (e.g., "fff:users=>user_file=>func" OR "fff:users=>by_id". Last one is parsed as "fff:users=>by_id=>by_id"):',
                'regex' => $cliRegex['folderFileOptionalFnRegex'],
                'required' => false,
                'default' => null,
                'help' => 'The `Folder` is the folder in src/funkphp/routes/{folder} whereas the `File` is the file inside of that folder (without the .php extension). The optional `Function` is the function inside of that file that will be called for this route. If you do not provide a `Function`, the `File` name will be used as the function name. If you do not provide a `Folder`, the file will be created in src/funkphp/routes/ (the root routes folder). Example 1: "fff:users=>user_file=>func" creates src/funkphp/routes/users/user_file.php with function func(). Example 2: "fff:users=>by_id" creates src/funkphp/routes/users/by_id.php with function by_id(). If something already exists and/or is already added to possibly already existing `method/route`, then nothing is done. If files already existed but were not added to the `method/route`, it will be added now. If the file already exists but the function does not exist inside of that file, the function will be added to the file.',
                'prefix' => 'fff:'
            ],
        ]
    ],
];
