<?php
// DEFAULT HEADERS SET & REMOVED FOR EVERY RESPONSE - Change as needed!
return [
    'ADD' => [
        "Content-Type: text/plain; charset=utf-8", // Change this per matched route in your application
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
    ],
    'REMOVE' => [
        "X-Powered-By",
        "Server",
        "X-AspNet-Version",
        "X-AspNetMvc-Version"
    ]
];
