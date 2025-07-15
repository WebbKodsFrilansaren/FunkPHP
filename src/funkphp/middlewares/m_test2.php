<?php return function (&$c, $mWValue = null) {
    echo "Middleware Test Function 2 !!! Running! This should pass no function!<br>\n";
    if ($mWValue) {
        echo "Middleware Value Passed: " . $mWValue . "\n<br>";
    } else {
        echo "No Middleware Value Passed.\n<br>";
    }
};
