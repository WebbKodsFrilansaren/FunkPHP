<?php // IMPORTANT: All steps in 0 are meant to be "set and forget" in the sense that they are not meant to be changed after the initial setup of the application.
// Step 0.1 is setting all the global variables that are needed for the application to run properly.

//BASE_VARIABLES_START_DELIMTIER//
$req = null; // This is the request object that will be used to handle the request and response
$d = null; // This is the data object that will be used to store the data fetched by database operations
$p = null; // This is the page object that will be used to handle the page rendering and output (not needed for API requests)
//BASE_VARIABLES_END_DELIMTIER//

//BASEURL_START_DELIMTIER//
$fphp_BASEURL_LOCAL = "http://localhost:8080/funphp/src/public_html/";
$fphp_BASEURL_ONLINE = "https://"; // Change to your hardcoded online URL!
$fphp_BASEURL = $_SERVER['SERVER_NAME'] == "localhost"
    || $_SERVER['SERVER_NAME'] == "127.0.0.1" ? $fphp_BASEURL_LOCAL : $fphp_BASEURL_ONLINE;
$fphp_BASEURL_URI = "/funphp/src/public_html/"; // This changes to "/" in localhost so the experience is the same as online
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
$fphp_data_params_key_name = "params";
$fphp_data_request_key_name = "req"; // Probably not needed as $_SERVER has everything
$fphp_data_post_key_name = "post";
$fphp_data_get_key_name = "get";
$fphp_data_json_key_name = "json";
$fphp_data_files_key_name = "files";
$fphp_data_session_key_name = "session"; // This is never used in the app though!
//DATA_KEY_NAMES_END_DELIMTIER//

//STUFFING_REQ_VARIABLE_WITH_DATA_START_DELIMTIER//
$req['matched_route'] = null; // This is the matched route that will be used to handle the request
$req['matched_params'] = []; // This is the matched parameters that will be used to handle the request
$req['matched_auth'] = []; // This is the matched authentication that will be used to handle the request
$req['code'] = 418; // HTTP status code of the response (default is 418, meaning "I'm a teapot")
$req['ip'] = $_SERVER['REMOTE_ADDR']; // This is the IP address of the client making the request
$req['agent'] = $_SERVER['HTTP_USER_AGENT']; // This is the user agent string of the client making the request
$req['content_type'] = $_SERVER['CONTENT_TYPE'] ?? null; // This is the content type of the request (application/json, application/x-www-form-urlencoded, etc.)
$req['accept'] = $_SERVER['HTTP_ACCEPT']; // This is the accept header of the request (text/html, application/json, etc.)
$req['uri'] = r_prepare_uri($_SERVER['REQUEST_URI'], $fphp_BASEURL_URI); // This is the URI of the request
$req['method'] = $_SERVER['REQUEST_METHOD']; // This is the HTTP method of the request (GET, POST, etc.)
$req['protocol'] = $_SERVER['SERVER_PROTOCOL']; // This is the protocol of the request (HTTP/1.1, HTTP/2, etc.)
$req['query'] = $_SERVER['QUERY_STRING']; // This is the query string of the request (example.com?param=value, etc.)
//STUFFING_REQ_VARIABLE_WITH_DATA_END_DELIMTIER//