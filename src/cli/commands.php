<?php // src/cli/commands.php - FunkCLI Command Configurations File
// Each Command needs to have its own File inside of src/cli/commands/
// The key uses ":" while the file is "-" to separate the exact same words!
// Create a 'args' key as the subkey for the `command:subcommand` key
// and then create a key for each argument that the command needs!
/* See example below for command file "src/cli/commands/make-route.php":
    'make:route' => [
        'args' => [
            'method_route' => [
                'prompt' => 'Create New Route File',
                'regex' => $cliRegex['methodRouteRegex'],
                'required' => true,
                'default' => false,
                'help' => null,
            ]
        ]
    ],
    It only uses the argument 'method_route' that is defined in the 'args' subkey!
    IMPORTANT: Your regex should start with a `prefix:rest_of_Regex` format so you
    do not end up matching other arguments that are not meant for this command!
*/
return [
    'make:route' => [
        'args' => [
            'method_route' => [
                'prompt' => 'Create New Route File',
                'regex' => $cliRegex['methodRouteRegex'],
                'required' => true,
                'default' => false,
                'help' => null,
            ]
        ]
    ],
];
