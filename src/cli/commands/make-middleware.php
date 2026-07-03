<?php // FunkCLI COMMAND "php funk make:middleware" - creates a new Middleware File with a skeleton Middleware Anonymous Function inside of it
// it can also attach to an optionally provided - only existing - Method/Route. Does NOT create Method/Route!
$ROUTES = $singleRoutesRoute['ROUTES'];
// Structure the correct folder name based on the first parameter,
// $folderType based on first parameter, and also initial $routeKey
// $routeKey is only applicable to "routes" and "middlewares"!
$arg_middleware  = null;
$middleware = null;
$newMWFile = null;
$arg_methodRoute = null;
$method = null;
$route = null;
$matchedRoute = null;
$createStatus = null;
$folder = null;
$optionalCodeSnippets = ''; // Optional code snippets to add inside created MW

// 1. Find/create the Middleware Name argument (e.g., "n:auth" -> mw_auth)
$arg_middleware = cli_get_cli_input_from_interactive_or_regular($args, 'make:middleware', 'middleware_name');
$middleware =  cli_extract_middleware($arg_middleware);
// 2. Find/create optional the Method/Route argument (e.g., "r:get/users")
$arg_methodRoute = cli_get_cli_input_from_interactive_or_regular($args, 'make:middleware', 'method/route');
if ($arg_methodRoute) {
    [$method, $route] = cli_extract_method_route($arg_methodRoute);
}
///////////////////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Create or Find the Middleware!
///////////////////////////////////////////////////////////////////////////////////////////
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_folder_and_php_file_status("funkphp/pipeline/middlewares", $middleware);
// When Main Middlewares folder doesn't even exist or is accessible as it should by default!
if (
    !$statusArray['folder_exists'] || !$statusArray['folder_readable']
    || !$statusArray['folder_writable']
) {
    cli_err("FunkPHP Middlewares Folder:`funkphp/pipeline/middlewares/` does NOT exist or is NOT Readable/Writable. Permission issues? Command Stopped!");
}
// When Middleware file does not exist we can attempt creating it!
if (!$statusArray['file_exists']) {
    $mwString = "<?php\n\nnamespace funkphp\\pipeline\\middlewares\\$middleware;\n// FunkCLI Created File on " . date('Y-m-d H:i:s') . "!\n\nfunction $middleware(&\$c)\n{\n\t// Placeholder Comment so Regex works - Remove & Add Your Own Code!\n$optionalCodeSnippets\n};\n";
    $newFilePath = $statusArray['folder_path'] . '/' . $statusArray['file_name'];
    // Attempt creating new Middleware File or error out
    if (cli_crud_folder_php_file_atomic_write($mwString, $newFilePath)) {
        cli_success_without_exit("Created New Middleware File for Middleware `$middleware` at Path:`$newMWFile`! Adding it to any optionally provided Method/Route now... IMPORTANT: If it already exists in the Method/Route it will still be added AGAIN as the last Middleware but a warning will also be shown!");
    } else {
        cli_err("Failed to Create New Middleware File for Middleware `$middleware`, probably due to Folder and/or File Permissions in your FunkPHP Project Middlewares Folder:`funkphp/pipeline/middlewares/`. Command Stopped!");
    }
}  // Middleware already exists so we just inform that and move on
else {
    cli_success_without_exit("Found Existing Middleware File for Middleware `$middleware` at Path:`$newMWFile`! Adding it to any optionally provided Method/Route now... IMPORTANT: If it already exists in the Method/Route it will still be added AGAIN as the last Middleware but a warning will also be shown!");
}
// We exit if no optional Method/Route argument was provided
if (!$arg_methodRoute) {
    cli_info_without_exit("No `Method/Route` Argument was provided so only the Middleware File `$middleware` was created. Command Done!");
    cli_success("Found/Created Middleware File without adding it to a Method/Route. Command Completed Successfully!");
}
/////////////////////////////////////////////////////////
// OPTIONAL: Adding Created/Found Middleware to the
// Method/Route if it exists, otherwise it will say it
// does not and say that only the Middleware was created!
/////////////////////////////////////////////////////////
// Method/Route must exist exactly in order to add MW to it!
if (!isset($ROUTES[$method][$route])) {
    cli_warning_without_exit("The optionally provided Method/Route:`$method$route` does NOT exist in `funkphp/pipeline/pipeline_routes.php` so could NOT add the Middleware to it.");
    cli_info("Middleware File `$middleware` was Created/Found but the optionally provided Method/Route:`$method$route` does NOT exist in `funkphp/pipeline/pipeline_routes.php` so could NOT add the Middleware to it. Command Done!");
}
// 'middlewares' key must exist and be a numbered array before adding to it or error out!
if (!isset($ROUTES[$method][$route]['middlewares']) || !array_is_list($ROUTES[$method][$route]['middlewares'])) {
    cli_err("The optionally provided Method/Route:`$method$route` does NOT have a `middlewares` key that is a Numbered Array in `funkphp/pipeline/pipeline_routes.php` so could NOT add the Middleware to it. Command Stopped!");
}
// Here we can add it, and we also check if it already exists so we can utter
// a warning of possible unwanted repeated Middleware execution in the future!
if (in_array($middleware, $ROUTES[$method][$route]['middlewares'])) {
    cli_warning_without_exit("The optionally provided Method/Route:`$method$route` already has the Middleware `$middleware` in its `middlewares` key in `funkphp/pipeline/pipeline_routes.php`. It will be added AGAIN as the last Middleware but a warning is shown here!");
}
$ROUTES[$method][$route]['middlewares'][] = $middleware;
cli_sort_build_routes_compile_and_output(["ROUTES" => $ROUTES]);
cli_success("Successfully Added Middleware `$middleware` to the optionally provided Method/Route:`$method$route` in `funkphp/pipeline/pipeline_routes.php`. Command Completed Successfully!");

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:middleware` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
