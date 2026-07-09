<?php

namespace funkphp\pipeline\request\pl_run_ini_sets;

function pl_run_ini_sets(&$c)
{
    $iniSets = $c['INI_SETS'] ?? [];
    foreach ($iniSets as $key => $value) {
        // Hard error on invalid configured $c['INI_SETS'] data
        if (!is_string($key) || empty($key) || !is_scalar($value)) {
            $err = 'Tell The Developer: Invalid Data Provided in $c[\'INI_SETS\'] Global Configuration Array. The Data must be an Associative Array with Non-Empty String Keys and Non-Empty Values that are either Strings, Numbers or Booleans. Thus, it is likely that the Developer have used a non-string for $key or a non-scalar/empty value for $value!';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        ini_set($key, $value);
    }
};
