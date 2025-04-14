<?php
// IMPORTANT: Still under Development = NOTHING IS IMPLEMENTED YET! (NOTHING ACTUALLY HAPPENS!)
// (This will probably only be implemented if FunkPHP actually gets a user base to begin with!)
//
// DEFAULT Behavior when unexpected behavior occurs during each step!
// Unexpected behavior is when a variable is not set or is NULL when it shouldn't be!
//
// Step1_To_5_Key -> Context_Key -> Condition_Key -> Action_Key -> Action_ValueToUse OR CallbackFunctionWithSameAction_ValueToUse
//
/*******
 * AVAILABLE STEPS (keys):
 * - STEP_0 = Initialize Global Configuration
 *
 * - STEP_1 = Match Route & Middlewares
 *
 * - STEP_2 = Run Middlewares after matching the route
 *
 * - STEP_3 = Match Route & Middlewares
 *
 * - STEP_4 = Run Middlewares after matching the route
 *
 * - STEP_5 = Return a matched page after route and data matching!
 *
 *
 *******
 * AVAILABLE CONTEXTS (keys):
 * - req = Request Context (e.g. validating request values, matching route and middleware, validating CSRF)
 *
 * - auth = Authentication Context (e.g. validating authentication, authorization)
 *
 * - middlewares = Middlewares Context (e.g. matching, finding middleware file, running middleware)
 *
 * - db = Database Context (e.g. connection, querying, generation)
 *
 * - d = Data Context (e.g. matching, finding data file, validating, error genereation)
 *
 * - p = Page Context (e.g. matching, finding page file (and its parts/"partials"), rendering page)
 *
 *
 *******
 * AVAILABLE CONDITIONS (keys):
 * - IS_NULL = When value is still null when it shouldn't be
 *
 * - NOT_CALLABLE = When a value is not callable when it should be
 *
 * - EXCEPTION = When an exception occured somewhere when it shouldn't have
 *
 * - NOT_FOUND = When a FILE (middleware, data and/or page) was not found when it should have been found
 *
 * - FAILED = When a value is not valid when it should be valid (e.g. authentication failed)
 *
 *
 *******
 * AVAIALBLE ACTIONS (single_action/key => single_value/value OR let CallbackFunction use it) - what should happen, and its value:
 * - CODE = HTTP response code to return (int between 100 and 599)
 *
 * - REDIRECT = URL to redirect to ("/uriString") with 301 code, then exit
 *
 * - LOG = Log message to write to the log file ("string", "optionalFileLocation")
 *
 * - RETURN_JSON_ERROR = JSON Error with optional HttpCode and then exit ("Json_EncodedString", optionalHttpCode:int)
 *
 *
 *******/

// IMPORTANT: Still under Development = NOTHING IS IMPLEMENTED YET! (NOTHING ACTUALLY HAPPENS!)
return [
    'STEP_0' =>
    [
        'req' =>
        [
            'method' => [
                'IS_NULL' => []
            ],
            'uri' => [
                'IS_NULL' => []
            ],
            'query' => [
                'IS_NULL' => []
            ],
            'matched_route' => [
                'IS_NULL' => []
            ],
            'matched_data' => [
                'IS_NULL' => []
            ],
            'matched_params' => [
                'IS_NULL' => []
            ],
            'matched_middlewares' =>
            [
                'IS_NULL' => []
            ],
            'matched_auth' => [
                'IS_NULL' => []
            ],
            'matched_csrf' => [
                'IS_NULL' => []
            ],
            'no_match_in' => [
                'IS_NULL' => []
            ],
            'keep_running_mws' =>
            [
                'IS_NULL' => []
            ],
            'protocol' => [
                'IS_NULL' => []
            ],
            'code' => [
                'IS_NULL' => []
            ],
            'ua' => [
                'IS_NULL' => []
            ],
            'ip' => [
                'IS_NULL' => []
            ],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
    ],
    'STEP_1' => [
        'req' =>
        [
            'method' => [
                'IS_NULL' => []
            ],
            'uri' => [
                'IS_NULL' => []
            ],
            'query' => [
                'IS_NULL' => []
            ],
            'matched_route' => [
                'IS_NULL' => []
            ],
            'matched_data' => [
                'IS_NULL' => []
            ],
            'matched_params' => [
                'IS_NULL' => []
            ],
            'matched_middlewares' =>
            [
                'IS_NULL' => []
            ],
            'matched_auth' => [
                'IS_NULL' => []
            ],
            'matched_csrf' => [
                'IS_NULL' => []
            ],
            'no_match_in' => [
                'IS_NULL' => []
            ],
            'keep_running_mws' =>
            [
                'IS_NULL' => []
            ],
            'protocol' => [
                'IS_NULL' => []
            ],
            'code' => [
                'IS_NULL' => []
            ],
            'ua' => [
                'IS_NULL' => []
            ],
            'ip' => [
                'IS_NULL' => []
            ],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'FAILED' => []
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
    ],
    'STEP_2' => [
        'req' =>
        [
            'method' => [
                'IS_NULL' => []
            ],
            'uri' => [
                'IS_NULL' => []
            ],
            'query' => [
                'IS_NULL' => []
            ],
            'matched_route' =>
            [
                'IS_NULL' => []
            ],
            'matched_data' =>
            [
                'IS_NULL' => []
            ],
            'matched_params' =>
            [
                'IS_NULL' => []
            ],
            'matched_middlewares' =>
            [
                'IS_NULL' => []
            ],
            'matched_auth' => [
                'IS_NULL' => []
            ],
            'matched_csrf' => [
                'IS_NULL' => []
            ],
            'no_match_in' => [
                'IS_NULL' => []
            ],
            'keep_running_mws' =>
            [
                'IS_NULL' => []
            ],
            'protocol' => [
                'IS_NULL' => []
            ],
            'code' => [
                'IS_NULL' => []
            ],
            'ua' => [
                'IS_NULL' => []
            ],
            'ip' => [
                'IS_NULL' => []
            ],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'FAILED' => []
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
    ],
    'STEP_3' => [
        'req' =>
        [
            'method' => [
                'IS_NULL' => []
            ],
            'uri' => [
                'IS_NULL' => []
            ],
            'query' => [
                'IS_NULL' => []
            ],
            'matched_route' => [
                'IS_NULL' => []
            ],
            'matched_data' => [
                'IS_NULL' => []
            ],
            'matched_params' => [
                'IS_NULL' => []
            ],
            'matched_middlewares' =>
            [
                'IS_NULL' => []
            ],
            'matched_auth' => [
                'IS_NULL' => []
            ],
            'matched_csrf' => [
                'IS_NULL' => []
            ],
            'no_match_in' => [
                'IS_NULL' => []
            ],
            'keep_running_mws' =>
            [
                'IS_NULL' => []
            ],
            'protocol' => [
                'IS_NULL' => []
            ],
            'code' => [
                'IS_NULL' => []
            ],
            'ua' => [
                'IS_NULL' => []
            ],
            'ip' => [
                'IS_NULL' => []
            ],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'FAILED' => []
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
    ],
    'STEP_4' => [
        'req' =>
        [
            'method' => [
                'IS_NULL' => []
            ],
            'uri' => [
                'IS_NULL' => []
            ],
            'query' => [
                'IS_NULL' => []
            ],
            'matched_route' => [
                'IS_NULL' => []
            ],
            'matched_data' => [
                'IS_NULL' => []
            ],
            'matched_params' =>
            [
                'IS_NULL' => []
            ],
            'matched_middlewares' =>
            [
                'IS_NULL' => []
            ],
            'matched_auth' => [
                'IS_NULL' => []
            ],
            'matched_csrf' => [
                'IS_NULL' => []
            ],
            'no_match_in' => [
                'IS_NULL' => []
            ],
            'keep_running_mws' =>
            [
                'IS_NULL' => []
            ],
            'protocol' => [
                'IS_NULL' => []
            ],
            'code' => [
                'IS_NULL' => []
            ],
            'ua' => [
                'IS_NULL' => []
            ],
            'ip' => [
                'IS_NULL' => []
            ],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'FAILED' => []
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
    ],
    'STEP_5' => [
        'req' =>
        [
            'method' => [
                'IS_NULL' => []
            ],
            'uri' => [
                'IS_NULL' => []
            ],
            'query' => [
                'IS_NULL' => []
            ],
            'matched_route' => [
                'IS_NULL' => []
            ],
            'matched_data' => [
                'IS_NULL' => []
            ],
            'matched_params' =>
            [
                'IS_NULL' => []
            ],
            'matched_middlewares' =>
            [
                'IS_NULL' => []
            ],
            'matched_auth' => [
                'IS_NULL' => []
            ],
            'matched_csrf' => [
                'IS_NULL' => []
            ],
            'no_match_in' => [
                'IS_NULL' => []
            ],
            'keep_running_mws' =>
            [
                'IS_NULL' => []
            ],
            'protocol' => [
                'IS_NULL' => []
            ],
            'code' => [
                'IS_NULL' => []
            ],
            'ua' => [
                'IS_NULL' => []
            ],
            'ip' => [
                'IS_NULL' => []
            ],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'FAILED' => []
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => []
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => []
        ],
    ],
];
