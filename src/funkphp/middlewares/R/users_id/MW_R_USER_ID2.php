<?php // TEST PURPOSES ONLY! DELETE IF NOT NEEDED IN YOUR APP!

// Always include &$c so you can edit the ongoing request as
// needed and so other middlewares can use request processing!
return function (&$c) {
    echo "<br><h1>MW 4 --------- I am a FOURTH middleware executed after route matching (Step 2)!</h1><br>";
};
