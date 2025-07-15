<?php return function (&$c, $mwValue = null) {
    echo "Middleware Test Function Running!\n<br>";
    if ($mwValue) {
        echo "Middleware Value Passed: " . $mwValue . "\n<br>";
    } else {
        echo "No Middleware Value Passed.\n<br>";
    }
};
