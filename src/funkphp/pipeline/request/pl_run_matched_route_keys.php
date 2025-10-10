<?php return function (&$c, $passedValue = null) {
    if (
        $passedValue === null
        || !is_string($passedValue)
        || !in_array($passedValue, ['defensive', 'happy'])
    ) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Passed Value to `funk_run_matched_route_middlewares` Pipeline Function must be either `defensive` or `happy` or `null` (default). No attempt to run any Matched Route Keys for Matched Route was made!';
        critical_err_json_or_html(500, 'Tell the Developer: The Run Matched Route Keys Pipeline Function ran but WITHOUT a Valid Passed Value - Must be either `defensive` or `happy`!');
    }

    // 'defensive' = we check almost everything and output error to user if something gets wrong
    if ($passedValue === 'defensive') {
        if (isset($c['req']['route_keys'])) {
            // Must be a numbered array
            if (!is_array($c['req']['route_keys']) || array_values($c['req']['route_keys']) !== $c['req']['route_keys']) {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Keys for the Matched Route must be a Numbered Array! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                critical_err_json_or_html(500, 'Tell the Developer: The Route Keys for the Matched Route must be a Numbered Array! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!');
            }
        }

        // Main Loop - each value is `Routes/folder=>FileName=>FunctionName=>$passedValue`
        $routesDir = ROOT_FOLDER . '/routes/';
        foreach ($c['req']['route_keys'] as $idx => $dirFileFn) {
            // $dirFileFn must be an array since its main structure is 'folder' => 'fileName' => 'functionName' => $passedValue
            // so it is array=>array=>array=>$optionalValue so we just check 3 arrays otherwise hard error!
            if (
                !is_array($dirFileFn)
                || array_is_list($dirFileFn)
                || count($dirFileFn) !== 1
            ) {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                critical_err_json_or_html(500, 'Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!');
            }
            // Ok folder is associative array, we get its key and check that its value (filename) is also an associative array
            $folder = key($dirFileFn);
            if (
                !is_array($dirFileFn[$folder])
                || array_is_list($dirFileFn[$folder])
                || count($dirFileFn[$folder]) !== 1
            ) {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                critical_err_json_or_html(500, 'Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!');
            }
            // Ok filename is also associative array, we get its key and check that its value (function name) is also an associative array
            $fileName = key($dirFileFn[$folder]);
            if (
                !is_array($dirFileFn[$folder][$fileName])
                || array_is_list($dirFileFn[$folder][$fileName])
                || count($dirFileFn[$folder][$fileName]) !== 1
            ) {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                critical_err_json_or_html(500, 'Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!');
            }
            // Ok all three are associative arrays, so we can now check that the folder=>filename actually exists
            // after we first have checked if it already exists in the $c['dispatchers'] array
            // otherwise we include_once it and store the returned anonymous function in $c['dispatchers
            $fnName = key($dirFileFn[$folder][$fileName]);
            $passedValue = $dirFileFn[$folder][$fileName][$fnName] ?? null;
            $folderFile = $routesDir . $folder . '/' . $fileName . '.php';

            if ( // if = run already exists in $c['dispatchers'] so can reuse
                isset($c['dispatchers']['routes'][$folder][$fileName])
                && is_callable($c['dispatchers']['routes'][$folder][$fileName])
            ) {
                $runRouteKey = $c['dispatchers']['routes'][$folder][$fileName];
                $runRouteKey($c, $fnName, $passedValue);
                continue;
            } // else if = not stored in $c['dispatchers'] yet so we add it if we can
            else if (is_readable($folderFile)) {
                $runRouteKey = include_once $folderFile;
                if (is_callable($runRouteKey)) {
                    // If fnName is not found inside of file,
                    // it will throw its own critical error!
                    $c['dispatchers']['routes'][$folder][$fileName] = $runRouteKey;
                    $runRouteKey($c, $fnName, $passedValue);
                    continue;
                } // ERROR: File found but not function inside of it
                else {
                    $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! The Folder/Function File `' . $folder . '/' . $fileName . '.php` does NOT RETURN a Callable Function in `funkphp/routes/` Directory! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                    critical_err_json_or_html(500, 'Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! The Folder/Function File `' . $folder . '/' . $fileName . '.php` does NOT RETURN a Callable Function in `funkphp/routes/` Directory! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!');
                }
            } // ERROR: File not found or not readable so hard error
            else {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! The Folder/Function File `' . $folder . '/' . $fileName . '.php` does NOT EXIST in `funkphp/routes/` Directory! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                critical_err_json_or_html(500, 'Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! The Folder/Function File `' . $folder . '/' . $fileName . '.php` does NOT EXIST/IS NOT READABLE in `funkphp/routes/` Directory! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!');
            }
        }
    }

    // 'happy' = we assume almost everything is correct and just run the matched route keys
    else if ($passedValue === 'happy') {
        // Assume $c['req']['route_keys'] exists, is a numbered array, and is correctly structured.
        $routesDir = ROOT_FOLDER . '/routes/';
        foreach ($c['req']['route_keys'] as $dirFileFn) {
            // Assume the deeply nested associative array structure is perfect.
            $folder = key($dirFileFn);
            $fileName = key($dirFileFn[$folder]);
            $fnName = key($dirFileFn[$folder][$fileName]);
            $passedValue = $dirFileFn[$folder][$fileName][$fnName] ?? null;
            $folderFile = $routesDir . $folder . '/' . $fileName . '.php';
            // if = Run already dispatched function, otherwise reuse OR error out
            if (isset($c['dispatchers'][$folder][$fileName])) {
                $runRouteKey = $c['dispatchers'][$folder][$fileName];
                $runRouteKey($c, $fnName, $passedValue); // Pass the inner handler name
                continue;
            }
            $runRouteKey = include_once $folderFile;
            if (is_callable($runRouteKey)) {
                $c['dispatchers'][$folder][$fileName] = $runRouteKey;
                $runRouteKey($c, $fnName, $passedValue);
                continue;
            }
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! The Folder/Function File `' . $folder . '/' . $fileName . '.php` does NOT RETURN a Callable Function in `funkphp/routes/` Directory! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            critical_err_json_or_html(500, 'Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder where the Function File with corresponding Function Name would be inside of! The Folder/Function File `' . $folder . '/' . $fileName . '.php` does NOT RETURN a Callable Function in `funkphp/routes/` Directory! Please check your Route Keys in `funkphp/routes/routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . '/' . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`. IMPORTANT: This happened in `happy` mode!');
        }
    }
};
