<?php // TEST PURPOSES ONLY! DELETE IF NOT NEEDED IN YOUR APP!

/*** HOW TO USE THIS FILE: ***/
// Write your Data Processing Handler inside of the returned function
// Make sure the filename is exactly as in 'get|post|json|files' => 'validate_data_filename' in the routes file. If you
// use 'get|post|json|files' => 'folder/validate_data_filename', then you can put this file in that "data/folder"!

// Always include &$c so you can edit the ongoing request as
// needed and so other middlewares can use request processing!
return function (&$c) {};
