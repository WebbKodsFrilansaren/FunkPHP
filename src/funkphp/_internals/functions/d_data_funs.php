<?php // DATABASE FUNCTIONS FOR FuncPHP
// This file contains functions related to database operations and/or configurations.

// Return the database connection object or an error message
function d_connect_db($dbHost, $dbUser, $dbPass, $dbName, $dbPort = 3306, $dbCharset = 'utf8mb4')
{
    // Attempt connecting to the database creating a new mysqli object
    try {
        // Create a new mysqli object with the provided parameters
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
        $conn->set_charset($dbCharset);

        // No error reporting for production environment
        if ($_SERVER['SERVER_NAME'] !== "localhost" && $_SERVER['SERVER_NAME' !== '127.0.0.1']) {
            mysqli_report(MYSQLI_REPORT_OFF); // No MySQL errors
            error_reporting(0);   // Also no PHP errors
        }
        return success($conn);
    } catch (Exception $e) {
        // Return error message if connection fails
        return fail("[d_connect_db]: DB Connection failed: " . $e->getMessage());
    }
}

// Try run middlewares after matched data routing (end of step 3)
// &$c is Global Config Variable with "everything"!
function d_run_middleware_after_matched_data_routing(&$c)
{
    if (isset($c['req']['matched_middlewares']) && is_array($c['req']['matched_middlewares']) && count($c['req']['matched_middlewares']) > 0) {
        $count = count($c['req']['matched_middlewares']);
        $c['req']['keep_running_middlewares'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_middlewares'] === false) {
                break;
            }

            // Check that it is a string and not null
            $current_mw = $c['req']['matched_middlewares'][$i] ?? null;
            if ($current_mw === null || !is_string($current_mw)) {
                h_try_default_action($c, "STEP_3", "middlewares", "IS_NULL", "<Action>", "<Value>");
                unset($c['req']['matched_middlewares'][$i]);
                $c['req']['number_of_deleted_middlewares']++;
                continue;
            }

            // Only run middleware if dir, file and callable,
            // then run it and increment the number of ran middlewares
            $mwDir = dirname(dirname(__DIR__)) . '/middlewares/';
            $mwToRun = $mwDir . $current_mw . '.php';
            if (is_dir($mwDir) && file_exists($mwToRun)) {
                $RunMW = include $mwToRun;
                if (is_callable($RunMW)) {
                    $c['req']['current_middleware_running'] = $current_mw;
                    $c['req']['number_of_ran_middlewares']++;
                    $c['req']['next_middleware_to_run'] = $c['req']['matched_middlewares'][$i + 1] ?? null;
                    $RunMW($c);
                } // CUSTOM ERROR HANDLING HERE! - not callable
                else {
                    h_try_default_action($c, "STEP_3", "middlewares", "NOT_CALLABLE", "<Action>", "<Value>");
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file
            else {
                h_try_default_action($c, "STEP_3", "middlewares", "NOT_FOUND", "<Action>", "<Value>");
            }

            // Remove middleware[$i] from the array after trying to run
            // it (it is removed even if it was not callable/existed!)
            $c['req']['deleted_middlewares'][] = $current_mw;
            $c['req']['deleted_middlewares_data'][] = $current_mw;
            unset($c['req']['matched_middlewares'][$i]);
            $c['req']['number_of_deleted_middlewares']++;
        }
        // Set default settings for the next middleware run
        $c['req']['current_middleware_running'] = null;
        if (
            isset($c['req']['matched_middlewares'])
            && is_array($c['req']['matched_middlewares'])
            && count($c['req']['matched_middlewares']) === 0
        ) {
            $c['req']['matched_middlewares'] = null;
        }
        $c['req']['keep_running_middlewares'] = false;
    }
    // CUSTOM ERROR HANDLING HERE! - no matched middlewares
    else {
        h_try_default_action($c, "STEP_3", "middlewares", "IS_NULL", "<Action>", "<Value>");
    }
}

// Try match single data route & data middleware routes! (start of step 4)
function d_match_developer_data_route(string $method, string $uri, array $compiledDataTrie, array $developerSingleDataRoutes, array $developerMiddlewareDataRoutes, string $handlerKey = "handler", string $mHandlerKey = "handler")
{
    // Prepare return values
    $matchedRoute = null;
    $matchedRouteHandler = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ""; // Use as debug value

    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledDataTrie[$method])) {
        $routeDefinition = r_match_compiled_route($uri, $compiledDataTrie[$method]);
    } else {
        $noMatchIn = "COMPILED_ROUTE_KEY (" . mb_strtoupper($method) . ") & ";
    }

    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedRouteParams = $routeDefinition["params"] ?? null;

        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleDataRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleDataRoutes[$method][$routeDefinition["route"]];
            $matchedRouteHandler = $routeInfo[$handlerKey] ?? null;
            $noMatchIn = "BOTH_MATCHED";

            // Add Any Matched Middlewares Handlers Defined By Developer
            // It loops through and only adds those that are non-empty strings
            // It does loop through arrays of non-empty strings! All values must
            // belong to the $mHandler key in the $developerMiddlewareRoutes array
            // or they will be ignored!
            if (
                isset($routeDefinition["middlewares"]) && !empty($routeDefinition["middlewares"] && is_array($routeDefinition["middlewares"]))
            ) {
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (isset($developerMiddlewareDataRoutes[$method][$middleware]) && isset($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey])) {
                        if (is_array($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey])) {
                            foreach ($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey] as $mHandler) {
                                if (is_string($mHandler) && !empty($mHandler)) {
                                    $matchedMiddlewareHandlers[] = $mHandler;
                                }
                            }
                        } elseif (is_string($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey]) && !empty($developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey])) {
                            $matchedMiddlewareHandlers[] = $developerMiddlewareDataRoutes[$method][$middleware][$mHandlerKey];
                        } // If not array or non-empty string, skip
                    }
                }
            }
        } else {
            $noMatchIn .= "DEVELOPER_SINGLE_ROUTES";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES";
    }
    return [
        "route" => $matchedRoute,
        "$handlerKey" => $matchedRouteHandler,
        "params" => $matchedRouteParams,
        "middlewares" => $matchedMiddlewareHandlers,
        "no_match_in" => $noMatchIn, // Use as debug value
    ];
}

// The main validation function for validating data in FunkPHP
function d_validate(&$c, array $data_keys_and_associated_validation_rules_values)
{
    $errors = [];

    // It has a start function so it can also be used recursively!
    function start_validate(&$c, $data_keys_and_associated_validation_rules_values) {}

    // The Available Validation Rules
    function mb_minlen(&$c, $data, $value)
    {
        if (mb_strlen($value) < $data['min']) {
            $errors[] = "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    }
    function mb_maxlen(&$c, $data, $value)
    {
        if (mb_strlen($value) > $data['max']) {
            $errors[] = "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    }
    function minlen(&$c, $data, $value)
    {
        if (strlen($value) < $data['min']) {
            $errors[] = "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    }
    function maxlen(&$c, $data, $value)
    {
        if (strlen($value) > $data['max']) {
            $errors[] = "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    }
    function minmax(&$c, $data, $value)
    {
        if ($value < $data['min'] || $value > $data['max']) {
            $errors[] = "The field {$data['name']} must be between {$data['min']} and {$data['max']}.";
        }
    }
    function minval(&$c, $data, $value)
    {
        if ($value < $data['min']) {
            $errors[] = "The field {$data['name']} must be at least {$data['min']}.";
        }
    }
    function required(&$c, $data, $value)
    {
        if (!isset($value) || empty($value)) {
            $errors[] = "The field {$data['name']} is required.";
        }
    }
    function string(&$c, $data, $value)
    {
        if (!is_string($value)) {
            $errors[] = "The field {$data['name']} must be a string.";
        }
    }
    function int(&$c, $data, $value)
    {
        if (!is_int($value)) {
            $errors[] = "The field {$data['name']} must be an integer.";
        }
    }
    function float(&$c, $data, $value)
    {
        if (!is_float($value)) {
            $errors[] = "The field {$data['name']} must be a float.";
        }
    }
    function bool(&$c, $data, $value)
    {
        if (!is_bool($value)) {
            $errors[] = "The field {$data['name']} must be a boolean.";
        }
    }
    function hex(&$c, $data, $value)
    {
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $value)) {
            $errors[] = "The field {$data['name']} must be a valid hex color code.";
        }
    }
    // TODO: Improve this!
    function email(&$c, $data, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "The field {$data['name']} must be a valid email address.";
        }
    }
    function url(&$c, $data, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = "The field {$data['name']} must be a valid URL.";
        }
    }
    // TODO: Improve this!
    function phone(&$c, $data, $value)
    {
        if (!preg_match('/^\+?[0-9]{1,4}?[-. ]?\(?[0-9]{1,4}?\)?[-. ]?[0-9]{1,4}[-. ]?[0-9]{1,9}$/', $value)) {
            $errors[] = "The field {$data['name']} must be a valid phone number.";
        }
    }
}
