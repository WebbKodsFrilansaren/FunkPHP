<?php

namespace funkphp\pipeline\post_response\pl_debug;

function pl_debug(&$c, $passedValue = null)
{
    echo "&lt;THIS IS A DEBUG PIPELINE FUNCTION WHICH RUNS AFTER EVERYTHING ELSE!&gt;\n";
    vd(['DISPATCHERS_DEBUG' => $c['dispatchers']]);
    vd($c['req']);
    vd($c['err']);
};
