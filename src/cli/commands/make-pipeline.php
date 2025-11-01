<?php // FunkCLI COMMAND "php funk make:pipeline" - creates a new Pipeline File with a skeleton Pipeline Anonymous Function inside of it
$arg_plName  = null;
$arg_plType = null;
// 1. Find/create the Pipeline Name argument (e.g., "n:headers_set")
$arg_plName = cli_get_cli_input_from_interactive_or_regular($args, 'make:pipeline', 'pipeline_name');
$pipeline =  cli_extract_pipeline($arg_plName);
// 2. Get Pipeline type argument (request|post)
$arg_plType = cli_get_cli_input_from_interactive_or_regular($args, 'make:pipeline', 'pipeline_type');
$type = cli_extract_pipeline_type($arg_plType);
//////////////////////////////////////////////////
// ALWAYS MANDATORY: Create or Find the Pipeline!
//////////////////////////////////////////////////
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$plStatus = cli_pipeline_file_status($pipeline);
// Check if it exists already in either folder - if so, error out since we cannot
// allow overwriting existing Pipeline Files and neither allow for exact same
// names in different folders due to ambiguity and/or confusion in the long run!
if ($plStatus['exists_in_request_dir'] || $plStatus['exists_in_post_response_dir']) {
    cli_err_without_exit("Pipeline File `$pipeline.php` already exists in either the `request` or `post-response` Pipeline Subdirectory. Cannot overwrite existing Pipeline Files. Please choose a different Pipeline Name!");
    cli_info("The reason that You CANNOT Use Same Pipeline Name in both Pipeline Subdirectories is to avoid ambiguities and/or confusions in the long run!");
}
// Attempt creation & assume failure, otherwise show success
$createStatus = cli_create_pipeline_file($pipeline, $type, $plStatus);
if (!$createStatus) {
    cli_err("Failed to create Pipeline File '{$pipeline}.php' in the `" . ($type === 'req' ? 'funkphp/pipeline/request' : 'funkphp/pipeline/post-response') . "` Directory! Please check the Permissions of the Directory and try again!");
}
cli_success("Pipeline File '{$pipeline}.php' created successfully in the `" . ($type === 'req' ? 'funkphp/pipeline/request' : 'funkphp/pipeline/post-response') . "` Directory! You can now edit it to add your desired Pipeline Anonymous Functions as needed.");

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:pipeline` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
