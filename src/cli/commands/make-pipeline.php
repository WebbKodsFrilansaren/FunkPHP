<?php // FunkCLI COMMAND "php funk make:pipeline" - creates a new Pipeline File with a skeleton Pipeline Anonymous Function inside of it
$arg_plName  = null;
$arg_plType = null;
$createStatus = null;
// 1. Find/create the Pipeline Name argument (e.g., "n:headers_set")
$arg_plName = cli_get_cli_input_from_interactive_or_regular($args, 'make:pipeline', 'pipeline_name');
$pipeline =  cli_extract_pipeline($arg_plName);

// 2. Get Pipeline type argument (request|post)
$arg_plType = cli_get_cli_input_from_interactive_or_regular($args, 'make:pipeline', 'pipeline_type');
$type = cli_extract_pipeline_type($arg_plType);

///////////////////////////////////////////////////////////////////////////////////////////
// ALWAYS MANDATORY: Create or Find the Pipeline!
///////////////////////////////////////////////////////////////////////////////////////////
// Grab status for the folder and file so we can check whether
// we can even access it, if it exists, is writable, etc.
$statusArray = cli_pipeline_file_status($pipeline);
var_dump("Pipeline Type:", $arg_plType, $statusArray); // For Debugging Purposes Only - Remove Later!

// Catch outside of all possible if/else/switch statements. Could happen during Refactoring this Command File!
cli_err("You are outside of the `make:pipeline` Command when it should have been caught/handled before ending up here. As a result it will terminate here now! Please report this as a Bug at `https://www.GitHub/WebbKodsFrilansaren/FunkPHP`!");
