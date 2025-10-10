<?php
return function (&$c, $passedValue = null) {
    echo "<p style='max-width:100%; text-align:center; font-weight:bold;'>&lt;THIS IS A DEBUG PIPELINE FUNCTION WHICH RUNS AFTER EVERYTHING ELSE!&gt</p>;\n";
    vd($c['err']);
    vd($c['req']);
};
