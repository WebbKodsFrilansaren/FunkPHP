<?php // TEST PURPOSES ONLY! DELETE IF NOT NEEDED IN YOUR APP!

// Always include &$c so you can edit the ongoing request as
// needed and so other middlewares can use request processing!
return function (&$c) {
    echo "<br><br>MW 2 ----------- I AM SECOND MIDDLEWARE!!!!!!!!!!!! (Step 2)!<br><br><br><br>";
};
