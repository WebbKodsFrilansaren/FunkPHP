<?php // This file includes all the data, besides the global variables, used by the application!
// Here you configure what data is needed for each HTTPS request based on the routing step.

// Place your data sanitization/validation/retrieval here based on route and method
require_once 'rdp_your_data.php';

// Process the data here based on the current route
require_once 'rdp_process_data.php';
