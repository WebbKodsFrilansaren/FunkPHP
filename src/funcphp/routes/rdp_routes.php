<?php  // This file includes all the files for the First Step (Routes) of each HTTPS request!

// Use externally created routes and/or redirects (.ini, .json, .sql or database)
// Function rdp_routes_external() is in "functions/rdp_routes.php".
// You can create these in the "rdp_gui" (FUTURE UPDATE) or manually.
$rdp_middleware_routes_externally = false;
$rdp_middleware_routes_external = rdp_middleware_routes_external() ?? [];
$rdp_routes_externally = false;
$rdp_routes_external = rdp_routes_external() ?? [];
$rdp_redirects_externally = false;
$rdp_redirects_external = rdp_routes_redirects_external() ?? [];


// Place your Middleware Routes and Routes inside of this file
require_once 'rdp_your_routes.php';


// Place your optional redirects inside of this file
// IMPORTANT: Their URIs must match the routes above!
require_once 'rdp_your_redirects.php';


// Process the routes before moving on to the next step which is retrieving data
require_once 'rdp_process_routes.php';
