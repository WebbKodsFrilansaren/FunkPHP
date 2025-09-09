<?php
return function (&$c, $passedValue = null) {
    $iniSets = $c['INI_SETS'] ?? [];
    foreach ($iniSets as $key => $value) {
        ini_set($key, $value);
    }
};
