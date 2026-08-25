<?php

namespace funkphp\pipeline\post_response;

function pl_debug(&$c, $passedValue = null)
{
    echo "&lt;THIS IS A DEBUG PIPELINE FUNCTION WHICH RUNS AFTER EVERYTHING ELSE!&gt;\n";
    dd(['DISPATCHERS_DEBUG' => $c['dispatchers']]);
    dd($c['req']);
    dd($c['err']);
};
