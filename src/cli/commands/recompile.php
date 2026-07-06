<?php // FunkCLI COMMAND "recompile", alias is "rc" - takes no arguments!

/**
 * -----------------------
 * FUNKCLI DEFAULT COMMAND
 * -----------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU KNOW IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/

cli_sort_build_routes_compile_and_output($singleRoutesRoute);
// In JSON MODE we complete the request with a JSON response
// Exists script on success unless stopped by function beforehand!
if (JSON_MODE) {
    cli_send_json_response();
} else {
    exit;
}
