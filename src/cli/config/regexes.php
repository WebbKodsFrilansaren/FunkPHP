<?php // src/cli/regexes.php - FunkCLI Command Regexes Configurations File
// These are used by the different Command Files inside of src/cli/commands/
// but it is mainly used by the src/cli/commands.php's different Commands's Arguments
// under the key `regex`. For example: `make:route` => `args` => `method/route` => `regex`.
// Use these for your Command File via $cliRegex['YOUR_CHOSEN_KEY_STRING_NAME'] etc.
return [
    // Allows to match anything - use with extreme caution! ONLY use to skip the regex
    // check so you can jump directly to an external callable validator if needed!
    'catchAllRegex' => '/^(.+)$/i',

    // `command` OR `command:subcommand`
    'commandRegex' => '/^([a-zA-Z0-9_]+)(:[a-zA-Z_0-9]+)?$/i',

    // `arg1`, `arg2`, ... etc - used to establish JSON_MODE
    // (do NOT change without knowing exactly what happens!)
    'argRegex' => '/^(arg[0-9]+)$/',

    // `r:get/`, "r:get/users", "r:get/users/:id", "r:post/data", "r:delete/item/:item_id" etc
    'methodRouteRegex' => '/^r:(([a-z]+\/)|([a-z]+(\/[:]?[a-zA-Z0-9_-]+)+))$/i',

    // targets file `users.php` with `by_id` Function inside
    //`ff:users=>by_id` This function is used when folder are
    // already known and cannot be changed as with Validation & SQL!
    'fileFnRegex' => '/^ff:([a-z0-9_-]+)=>([a-z_][a-z0-9_]+)$/i',

    // `funkphp/routes/users/users.php with `update_user` Function inside
    //`fff:users=>users=>update_user`
    'folderFileFnRegex' => '/^fff:([a-z][a-z0-9_]+)=>([a-z0-9_-]+)=>([a-z_][a-z0-9_]+)$/i',

    // `t:table1` OR `t:table1,table2` OR `t:table1*2` OR `t:table1*2,table2` (\*\d+) part is optional!
    'tableRegexValidation' => '/^t:([a-z][a-z0-9_]*(\*[0-9]+)?)(,[a-z][a-z0-9_]*(\*[0-9]+)?)*$/i',

    // `t:s=table1` OR `t:s=table1,table2` OR `t:i=table1` OR `t:sd=table1,table2`. No (*\d+) part!
    'tableRegexSQL' => '/^t:(((sd|si|s|i|u|d)=))([a-z_][a-z0-9_]*)(,[a-z_][a-z0-9_]*)*$/i',

    // `get/:user` OR `get/users/:by_id` | Check if route ends with "/:something" meaning it is dynamic
    // This is not a (sub)command or argument regex but used by some commands to validate route syntax!
    'routeDynamicEndRegex' => '/\/:[a-zA-Z-_0-9]+$/i',

    // `n:middlewareName` or `n:pipeline_name` | For Middlewares & Pipeline Anonymous Function Files
    'nameOnlyRegex' => '/^(n):([a-z_][a-zA-Z_0-9]+)$/i',

    // plt:request OR plt:post
    'plTypeRegex' => '/^plt:(request|post)$/i',

    // Add more regexes as needed in the future!
    '' => '',
    '' => '',
];
