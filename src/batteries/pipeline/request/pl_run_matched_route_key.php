<?php
return function (&$c, $key) {
    // $key must be a non-empty string
    if (!isset($key) || !is_string($key) || empty($key)) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'No Route Key provided to run. Please provide a valid Route Key!';
        return;
    }
    // It must also exist in currently matched route
    if (!isset($c['req']['route_keys'][$key])) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` NOT found for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`. Please check your Route Keys in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
        return;
    }

    // We extract folder name, file name and function name based on whether
    // 'folder' => 'fileName' (here functionName becomes same as fileName) OR
    // 'folder' => ['fileName' => 'functionName']
    $matchKey = $c['req']['route_keys'][$key];
    $keyFolder = $key;
    $keyFile = '';
    $keyFn = '';
    if (is_string($matchKey)) {
        $keyFile = $matchKey;
        $keyFn = $matchKey;
    } elseif (is_array($matchKey)) {
        $keyFile = key($matchKey);
        $keyFn = $matchKey[$keyFile] ?? '';
    } else {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` must be a String or an Array with a Non-Empty String Value. No attempt to find a Route Key File was made!';
        return;
    }
    // We check whether a returned anonymous function
    // already exists in $c['dispatchers'][$key][$keyFile]
    // otherwise we add it and call it!
    if (isset($c['dispatchers'][$key][$keyFile])) {
        // Check if it is callable, and if i tis NOT callable,
        // we log an error since we ONLY store callables here!
        if (is_callable($c['dispatchers'][$key][$keyFile])) {
            $c['dispatchers'][$key][$keyFile]($c, $keyFn);
        } else {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` File `' . $keyFile . '` is NOT a Callable Function. Please check your Route Key File in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
            return;
        }
    }
    // Not added yet so add if it exists and call it with the $keyFn!
    else {
        $pathToInclude = ROOT_FOLDER . '/routes/' . $keyFolder . '/' . $keyFile . '.php';
        if (!is_readable($pathToInclude)) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_key'][] = 'Route Key `' . $key . '` File `' . $keyFile . '` does NOT EXIST in `' . $keyFolder . '/` Directory! Please check your Route Key File in `funkphp/config/routes.php` for the Route `' . ($c['req']['route'] ?? '<No Route Matched>') . '`!';
            return;
        }
        $c['dispatchers'][$key][$keyFile] = include_once $pathToInclude;
        $c['dispatchers'][$key][$keyFile]($c, $keyFn);
    }
};
