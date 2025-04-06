<?php // IMPORTANT: All steps in 0 are meant to be "set and forget" in the sense that they are not meant to be changed after the initial setup of the application.
// Step 0.1 is setting all the global variables that are needed for the application to run properly.

//BASEURL_START_DELIMTIER//
$fphp_BASEURL_LOCAL = "http://localhost:8080/funkphp/src/public_html/";
$fphp_BASEURL_ONLINE = "https://"; // Change to your hardcoded online URL!
$fphp_BASEURL = $_SERVER['SERVER_NAME'] == "localhost"
    || $_SERVER['SERVER_NAME'] == "127.0.0.1" ? $fphp_BASEURL_LOCAL : $fphp_BASEURL_ONLINE;
$fphp_BASEURL_URI = "/funkphp/src/public_html/"; // This changes to "/" in localhost so the experience is the same as online
//BASEURL_END_DELIMTIER//

//BASE_STATIC_FILE_PATHS_START_DELIMTIER//
$fphp_BASE_STATIC_FILE_PATHS = [
    "css" => ["css", "styles"],
    "js" => ["js", "javascript"],
    "files" => ["files", "fls"],
    "fonts" => ["fonts", "fnt"],
    "images" => ["images", "img"],
    "temp" => ["temp", "tmp"],
    "videos" => ["videos", "vid"],
];
//BASE_STATIC_FILE_PATHS_END_DELIMTIER//

//DATA_KEY_NAMES_START_DELIMTIER//
$fphp_data_keys = [
    "params" => "params", // This is the parameters that will be used to handle the request
    "req" => "req", // Probably not needed as $_SERVER has everything
    "post" => "post", // This is the POST data that will be used to handle the request
    "get" => "get", // This is the GET data that will be used to handle the request
    "json" => "json", // This is the JSON data that will be used to handle the request
    "files" => "files", // This is the files data that will be used to handle the request
    "session" => "session", // This is the session data that will be used to handle the request (not needed in this app)
];
//DATA_KEY_NAMES_END_DELIMTIER//

//ALL_GLOBAL_VARIABLES_START_DELIMTIER//
$fphp_all_global_variables_as_strings =
    [
        "fphp_BASEURL_LOCAL",
        "fphp_BASEURL_ONLINE",
        "fphp_BASEURL",
        "fphp_BASEURL_URI",
        "fphp_BASE_STATIC_FILE_PATHS",
        "fphp_data_keys",
        "fphp_DEFAULT_SESSION_COOKIE_NAME",
        "fphp_DEFAULT_SESSION_COOKIE_LIFETIME",
        "fphp_DEFAULT_SESSION_COOKIE_PATH",
        "fphp_DEFAULT_SESSION_COOKIE_DOMAIN",
        "fphp_DEFAULT_SESSION_COOKIE_SECURE",
        "fphp_DEFAULT_SESSION_COOKIE_HTTPONLY",
        "fphp_DEFAULT_SESSION_COOKIE_SAMESITE",
        "fphp_ips_filtered_globals",
        "fphp_ips_filtered_grouped",
        "fphp_uas_filtered_globals",
        "fphp_uas_filtered_grouped",
        "fphp_denied_uas_ais",
        "fphp_denied_uas_others",
        "fphp_o_fail_priorities",
        "fphp_o_ok_priorities",
    ];
//ALL_GLOBAL_VARIABLES_END_DELIMTIER//