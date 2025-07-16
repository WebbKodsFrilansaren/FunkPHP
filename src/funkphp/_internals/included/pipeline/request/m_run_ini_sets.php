<?php
return function (&$c) {
    $iniSets = $c['INI_SETS'] ?? [];
    foreach ($iniSets as $key => $value) {
        ini_set($key, $value);
    }
};
