<?php
return function (&$c, $passedValue = null) {
    echo "&lt;THIS IS A DEBUG PIPELINE FUNCTION WHICH RUNS AFTER EVERYTHING ELSE!&gt;\n";
    vd(['DISPATCHERS_DEBUG' => $c['dispatchers']]);
    vd($c['req']);
    vd($c['err']);
};
