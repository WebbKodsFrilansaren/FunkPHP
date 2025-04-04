<?php // IMPORTANT: All steps in 0 are meant to be "set and forget" in the sense that they are not meant to be changed after the initial setup of the application.
// Step 0.3 is setting the default headers and starting the session for the application.
// Change as needed! They are following Zero Trust- & Least Privilege-principles and are meant to be secure by default.

//DEFAULT_HEADERS_ADD_START_DELIMTIER//
h_headers_set(
    "Content-Type: text/html; charset=utf-8", // Change this per matched route in your application
    "Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';",
    "x-frame-options: DENY",
    "x-content-type-options: nosniff",
    "x-xss-protection: 1; mode=block",
    "x-permitted-cross-domain-policies: none",
    "referrer-policy: strict-origin-when-cross-origin",
    "Access-Control-Allow-Origin: 'self'",
    "cross-origin-resource-policy: same-origin",
    "Cross-Origin-Embedder-Policy: require-corp",
    "Cross-Origin-Opener-Policy: same-origin",
    "Expect-CT: enforce, max-age=86400",
    "Strict-Transport-Security: max-age=31536000; includeSubDomains; preload"
);
//DEFAULT_HEADERS_ADD_END_DELIMTIER//

//DEFAULT_HEADERS_REMOVE_START_DELIMTIER//
h_headers_remove(
    "X-Powered-By",
    "Server",
    "X-AspNet-Version",
    "X-AspNetMvc-Version"
);
//DEFAULT_HEADERS_REMOVE_END_DELIMTIER//

//START_SESSION_START_DELIMTIER//
h_start_session();
//START_SESSION_END_DELIMTIER//