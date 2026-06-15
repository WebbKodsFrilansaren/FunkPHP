<?php

namespace funkphp\pipeline\request\pl_run_matched_route_keys;

function pl_run_matched_route_keys(&$c)
{
    // Must be a non-empty numbered array
    if (
        !isset($c['req']['route_keys'])
        || !is_array($c['req']['route_keys'])
        || !array_is_list($c['req']['route_keys'])
        || count($c['req']['route_keys']) === 0
    ) {
        $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = 'Route Keys for the Matched Route must be a Numbered Array! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
        $err = 'Tell the Developer: The Route Keys for the Matched Route must be a Numbered Array! This can also happen when You ONLY have Middlewares but no other `Route Key`! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }

    // Main Loop - each value is `Routes/folder=>FileName=>FunctionName`
    $routesDir = ROOT_ROUTES . '/';

    // New version where we only go for "Routes/FileName=>FunctionName
    foreach ($c['req']['route_keys'] as $idx => $dirFileFn) {
        $file = key($dirFileFn) ?? null;
        $fn = $dirFileFn[$file ?? ''] ?? null;

        if ($file === null || $fn === null) {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = '(1) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            $err = '(1) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $folderFile = $routesDir . $file . '.php';
        if (is_readable($folderFile)) {
            include_once $folderFile;
            $fileFnToRun = NAMESPACE_PIPELINE_ROUTES . $file . '\\' . $fn;
            if (is_callable($fileFnToRun)) {
                // If fnName is not found inside of file,
                // it will throw its own critical error!
                $rawRun = $fileFnToRun($c);
                continue;
            } // ERROR: File found but not function inside of it
            else {
                $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = '(2) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                $err = '(2) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        } // ERROR: File not found or not readable so hard error
        else {
            $c['err']['PIPELINE']['REQUEST']['funk_run_matched_route_keys'][] = '(3) Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            $err = '(3) Tell the Developer: The Route Key at Index `' . $idx . '` must be an Array with a Non-Empty String Key corresponding to the Folder+File Path where the Function Name would be inside of! Please check your Route Keys in `funkphp/core/pipeline_routes.php` for the Route `' . (is_string($c['req']['method']) ? $c['req']['method'] : '<No HTTP(S) Method Matched>') . (is_string($c['req']['route']) ? $c['req']['route'] : '<No Route Matched>') . '`!';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
};
