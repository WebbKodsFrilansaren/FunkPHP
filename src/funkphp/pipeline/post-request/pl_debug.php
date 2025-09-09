<?php
return function (&$c, $passedValue = null) {
    echo "&lt;THIS IS A DEBUG PIPELINE FUNCTION WHICH RUNS AFTER EVERYTHING ELSE!&gt;\n";
    vd($c['err']);
    vd($c['req']);
};
