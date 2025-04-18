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
                }
            } // CUSTOM ERROR HANDLING HERE! - no dir or file
            else {
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
            $noMatchIn = "BOTH_MATCHED_DATA";

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
            $noMatchIn .= "DEVELOPER_SINGLE_ROUTES_DATA";
        }
    } else {
        $noMatchIn .= "COMPILED_ROUTES_DATA";
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
    $start_validate = function (&$c, $data_keys_and_associated_validation_rules_values) use ($errors) {};

    // The Available Validation Rules
    $utf8 = function (&$c, $data, $value, $customErr = null) {
        if (!mb_check_encoding($value, 'UTF-8')) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid UTF-8 string.";
        }
    };
    $mb_minlen = function (&$c, $data, $value, $customErr = null) {
        if (mb_strlen($value) < $data['min']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    };
    $mb_maxlen = function (&$c, $data, $value, $customErr = null) {
        if (mb_strlen($value) > $data['max']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    };
    $minlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) < $data['min']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']} characters long.";
        }
    };
    $maxlen = function (&$c, $data, $value, $customErr = null) {
        if (strlen($value) > $data['max']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at most {$data['max']} characters long.";
        }
    };
    $minmax = function (&$c, $data, $value, $customErr = null) {
        if ($value < $data['min'] || $value > $data['max']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be between {$data['min']} and {$data['max']}.";
        }
    };
    $minval  = function (&$c, $data, $value, $customErr = null) {
        if ($value < $data['min']) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be at least {$data['min']}.";
        }
    };
    $required = function (&$c, $data, $value, $customErr = null) {
        if (!isset($value) || empty($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} is required.";
        }
    };
    $string  = function (&$c, $data, $value, $customErr = null) {
        if (!is_string($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a string.";
        }
    };
    $int  = function (&$c, $data, $value, $customErr = null) {
        if (!is_int($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be an integer.";
        }
    };
    $float  = function (&$c, $data, $value, $customErr = null) {
        if (!is_float($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a float.";
        }
    };
    $bool  = function (&$c, $data, $value, $customErr = null) {
        if (!is_bool($value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a boolean.";
        }
    };
    $hex  = function (&$c, $data, $value, $customErr = null) {
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid hex color code.";
        }
    };
    // TODO: Improve this!
    $email  = function (&$c, $data, $value, $customErr = null) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid email address.";
        }
    };
    $url =  function (&$c, $data, $value, $customErr = null) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid URL.";
        }
    };
    // TODO: Improve this!
    $phone = function (&$c, $data, $value, $customErr = null) {
        if (!preg_match('/^\+?[0-9]{1,4}?[-. ]?\(?[0-9]{1,4}?\)?[-. ]?[0-9]{1,4}[-. ]?[0-9]{1,9}$/', $value)) {
            $errors[] = $customErr ? $customErr : "The field {$data['name']} must be a valid phone number.";
        }
    };
}
