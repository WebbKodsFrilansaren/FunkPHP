<?php // TEST PURPOSES ONLY! DELETE IF NOT NEEDED IN YOUR APP!

// Always include &$c so you can edit the ongoing request as
// needed and so other middlewares can use request processing!
return function (&$c) {
    echo "<br><br><br>MW 1 --------- I am a test middleware executed after route matching (Step 2)!<br><br><br>";
    //$c['req']['keep_running_middlewares'] = false;
};
