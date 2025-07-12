<?php return function (&$c) {
    if ($c['req']['matched_middlewares'] !== null) {
        funk_run_middleware_after_matched_routing($c);
    }
};
