<?php // TEST PURPOSES ONLY! DELETE IF NOT NEEDED IN YOUR APP!

/*** HOW TO USE THIS FILE: ***/
// Write your Middleware inside of the returned function
// Make sure the filename is exactly as in 'handler' => 'middleware_name' in the routes file. If you
// use 'handler' => 'folder/middleware_name', then you can put this file in that "middleware/folder"!

// Always include &$c so you can edit the ongoing request as
// needed and so other middlewares can use request processing!
return function (&$c) {};
