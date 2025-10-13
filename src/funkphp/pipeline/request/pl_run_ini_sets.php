<?php
return function (&$c, $passedValue = null) {
    $iniSets = $c['INI_SETS'] ?? [];
    foreach ($iniSets as $key => $value) {
        // Hard error on invalid configured $c['INI_SETS'] data
        if (!is_string($key) || empty($key) || !is_scalar($value)) {
            $err = 'Tell The Developer: Invalid Data Provided in $c[\'INI_SETS\'] Global Configuration Array. The Data must be an Associative Array with Non-Empty String Keys and Non-Empty Values that are either Strings, Numbers or Booleans. Thus, it is likely that the Developer have used a non-string for $key or a non-scalar/empty value for $value!';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
        }
        ini_set($key, $value);
    }
    // If $passedValue is provided, we verify it is an associative array
    // and then start running the ini_set calls from it as well
    if (isset($passedValue)) {
        if (!is_array($passedValue) || array_is_list($passedValue) || empty($passedValue)) {
            $err = 'Tell The Developer: Invalid Data Provided to `pl_run_ini_sets` Pipeline Function. The Provided Data must be an Associative Array with Non-Empty String Keys and Non-Empty Values that are either Strings, Numbers or Booleans. Thus, it is likely that the Developer have used a non-string for $key or a non-scalar/empty value for $value!';
            funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
        }
        foreach ($passedValue as $key => $value) {
            // Hard error on invalid data to optional ini_set data
            if (!is_string($key) || empty($key) || !is_scalar($value)) {
                $err = 'Tell The Developer: Invalid Data Provided to `pl_run_ini_sets` Pipeline Function. The Provided Data must be an Associative Array with Non-Empty String Keys and Non-Empty Values that are either Strings, Numbers or Booleans. Thus, it is likely that the Developer have used a non-string for $key or a non-scalar/empty value for $value!';
                funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
            }
            ini_set($key, $value);
        }
    }
};
