<?php
// Default redirect(s) for specific response code(s) based on the routing above
// IMPORTANT: These routes must also be defined in the $rdp_routes array above
$rdp_redirects = $rdp_redirects_externally ? [...$rdp_redirects_external] :
    [
        '403' => 'GET/login',
        '404' => 'GET/404',
        '415' => 'GET/415',
        '500' => 'GET/500'
    ];
