<?php
// DEFAULT BASE URLs for the application - Change as needed!
return [
    'LOCAL' => 'http://localhost/funkphp/src/public_html/',
    // IMPORTANT: Change to your hardcoded online URL!
    'ONLINE' => 'https://www.funkphp.com/',
    'BASEURL' => (isset($_SERVER['SERVER_NAME'])
        && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === "127.0.0.1"))
        ? 'localhost'
        : 'https://funkphp.com',
    // This changes to "/" in localhost to match online experience
    'BASEURL_URI' => '/funkphp/src/public_html/',
];
