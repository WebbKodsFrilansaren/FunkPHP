<?php // src/cli/config/external_callable_validators.php - FunkCLI External Callable Validators Configurations File
// Where You Define Your External Callable Validators For CLI Command Arguments Here!
// These are used by the different Command Files inside of src/cli/commands/
// For consistency, each external callable validator should accept a argument that is the data
// to more complexly validate and return true if it is valid otherwise false if it is invalid.
// Name each function "cli_external_validator_YOUR_VALIDATOR_NAME" where YOUR_VALIDATOR_NAME
// is the name you will use to reference this validator in the Command File argument
// It expects these functions to be called ""cli_external_validator_" + 'YOUR_VALIDATOR_NAME'.
// Example: If you have a validator function named "method_route" then the full function name
// would be "cli_external_validator_method_route" and you would use 'method_route' in the Command File
// under the key 'external_callable_validator' for the given argument you want to validate with this function!

// Used by "make:route" command's "method/route" argument in src/cli/config/commands.php
// Prefix `cli_external_validator_` is REQUIRED for all external callable validators!
function cli_external_validator_method_route($matchedRegexMethodRouteString)
{
    if (!is_string($matchedRegexMethodRouteString) || empty(trim($matchedRegexMethodRouteString))) {
        cli_err_without_exit('The Provided Method/Route String must be a Non-Empty String.');
        return false;
    }

    // Remove any prefix regex if present (e.g., r:)
    $prefixRegex = '/^([a-z]+:)/i';
    $matchedRegexMethodRouteString = preg_replace($prefixRegex, '', $matchedRegexMethodRouteString, 1);

    // Valid method/route string to start with?
    $regex = '/^(([a-z]+\/)|([a-z]+(\/[:]?[a-zA-Z0-9_-]+)+))$/i';
    if (!preg_match($regex, $matchedRegexMethodRouteString)) {
        cli_err_without_exit('The Provided Method/Route String does not match the expected Method/Route Syntax.');
        return false;
    }

    // Valid method start? (GET, POST, DELETE, PUT, PATCH or their shorthands)
    $methodRegex = '/^((delete|patch|post|put|del|get|pa|po|pu|ge|g|d)(\/.*))$/i';
    if (!preg_match($methodRegex, $matchedRegexMethodRouteString)) {
        cli_err_without_exit('The Provided Method/Route String does not start with a valid HTTPS Method.');
        return false;
    }
    // We split on the first "/" to get the method and route parts. There is a special case
    // where it is the root (get/) which means route is just "/".
    $firstSlashPos = strpos($matchedRegexMethodRouteString, '/');
    $methodPart = '';
    $routePart = '';
    $method = null;
    $route = null;
    if ($firstSlashPos !== false) {
        $methodPart = substr($matchedRegexMethodRouteString, 0, $firstSlashPos);
        $routePart = substr($matchedRegexMethodRouteString, $firstSlashPos);
        // Handle root route case and
        // normalize method to full name
        if ($routePart === '/') {
            $routePart = '/';
        }
        $methodMap = [
            'g' => 'GET',
            'ge' => 'GET',
            'get' => 'GET',
            'po' => 'POST',
            'pos' => 'POST',
            'post' => 'POST',
            'd' => 'DELETE',
            'del' => 'DELETE',
            'delete' => 'DELETE',
            'pu' => 'PUT',
            'put' => 'PUT',
            'pa' => 'PATCH',
            'patch' => 'PATCH'
        ];
        // If provided method does not exist in $methodMap we error
        if (array_key_exists($methodPart, $methodMap)) {
            $method = $methodMap[$methodPart];
        } else {
            cli_err_without_exit('The Provided Method in the Method/Route String is not a recognized HTTPS Method. Use these ones only:`' . implode(', ', array_keys($methodMap)) . '`.');
            return false;
        }
        // We match all /:dynamic_params in the route part (if any) and error out on
        // any duplicates (e.g., /users/:id/posts/:id is invalid since :id is used twice)
        $paramsRegex = '/(\/:[a-zA-Z0-9_-]+)/i';
        $paramsMatches = preg_match_all($paramsRegex, $routePart, $paramsFound);
        if ($paramsMatches && !empty($paramsFound) && isset($paramsFound[0])) {
            $foundParams = $paramsFound[0];
            $uniqueParams = array_unique($foundParams);
            if (count($foundParams) !== count($uniqueParams)) {
                cli_err_without_exit('The Provided Route in the Method/Route String contains duplicate dynamic parameters. Check your `/:dynamic_route_param` for:`' . $routePart . '`.');
                return false;
            }
        }
        $route = $routePart;
    }  // Handle impossible case
    else {
        cli_err_without_exit('Unexpected Error while extracting `$method` & `$route` out of Method/Route String.');
        return false;
    }
    return true;
}
