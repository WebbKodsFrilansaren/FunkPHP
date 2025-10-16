<?php // FunkCLI COMMAND "recompile", alias is "rc" - takes no arguments!
cli_sort_build_routes_compile_and_output($singleRoutesRoute);
// In JSON MODE we complete the request with a JSON response
// Exists script on success unless stopped by function beforehand!
if (JSON_MODE) {
    cli_send_json_response();
}
return; // Needed in CLI-Mode since function NEVER exits script on success!