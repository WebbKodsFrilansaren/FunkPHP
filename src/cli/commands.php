<?php // src/cli/commands.php - FunkCLI Command Configurations File
// Each Command needs to have its own File inside of src/cli/commands/
// The key uses ":" while the file is "-" to separate the exact same words!
// Each Key MUST have the keys: 'prompt' (string), 'regex' (regex string),
// 'required' (true|false), 'default' (string|null), 'help' (string|null)
return [
    'make:route' => [
        'prompt' => 'Create New Route File',
        'regex' => 'Creates a new Route File inside of FunkPHP Routes Folder and optionally adds it to a specific Method/Route',
        'required' => true,
        'default' => false,
        'help' => null,
    ],
];
