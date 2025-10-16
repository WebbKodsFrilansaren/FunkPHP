<?php // FunkCLI COMMAND `php funk add|attach|link`
// ADD: Attach a Route_Key that already exists to a specific Method/Route
// assuming the Method/Route and/or the File+Function already exists
$middlewaresAliases = ['mw', 'middleware'];
$singlePipelineAliases = ['pl', 'pipeline'];
$singleAnonymousFolderlist = ["middlewares", "mw", "mws", "pls", "pl", "pipeline"];
$folderListThatNeedsTables = ['sql', 'validation', 'validation', 'v', 's'];
$folderListThatWillCauseWarning = [
    'routes',
    'cached',
    'classes',
    'backups',
    '_final_backups',
    'valid',
    'complete',
    'functions',
    'compiled',
    'components',
    'partials',
    'blocked',
    'config',
    '_internals',
    'batteries',
    'post-response',
    'request',
    'gui',
    'public_html',
    'cli',
    'test',
    'tests',
    'schema',
    'schemas',
];

// Issue a warning but still continue if the first parameter is in the list of folders that will cause a warning
// because these folders are already being used for other purposes but they could be used inside of "funkphp/routes/"
// which is where they will be put!
if (in_array($firstParam, $folderListThatWillCauseWarning)) {
    cli_warning_without_exit("The first parameter `$firstParam` is in the list of Folders that are already being used by FunkPHP. It will now be used as a subfolder in `funkphp/routes/`!");
    cli_info_without_exit("This is just a heads-up so you are not confused by seeing a folder with the same name in several places inside of FunkPHP!");
}

// Extract valid $file and $fn from $arg1
[$file, $fn] = cli_return_valid_file_n_fn_or_err_out($arg1);
$method = null;
$route = null;
// Passing $arg2 for Method/Route is optional so we check if it is set
if (isset($arg2) && is_string($arg2) && !empty($arg2)) {
    [$method, $route] =  cli_return_valid_method_n_route_or_err_out($arg2);
} else {
    cli_info_without_exit("No Method/Route provided! This means any created Function File and/or Function Name inside of /funkphp/routes/ will NOT be attached to any Method/Route!");
}
var_dump($file, $fn, $method, $route);

exit;
