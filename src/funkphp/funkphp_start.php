<?php // ENTRY POINT OF EACH HTTPS REQUEST thanks to ".htaccess" file

// Load all functions needed for the FunkPHP Framework Web Application
include_once __DIR__ . '/_internals/functions/_includeAll.php';

// $c is the global configuration array that will be used throughout the application
$c = include __DIR__ . '/config/_all.php';

// Load all the Developer steps that are needed to run the application
include_once __DIR__ . '/dx_steps/_includeAll.php';

// This part is only executed if the request was not properly handled by the pipeline!
// Feel free to add your own error handling here and/or easter egg!
echo "<br>YOU SHOULD NOT SEE THIS! SO ERROR!<br>";
