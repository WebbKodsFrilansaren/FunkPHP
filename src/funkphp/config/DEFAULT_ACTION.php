<?php
// IMPORTANT: Still under Development! (You can use "h_try_default_action" to test it though!)
// It is in "src/funkphp/_internals/functions/h_helper_funs.php" along with the other helper functions!
//
// (This will probably only be fully implemented if FunkPHP actually gets a user base to begin with!)
//
// DEFAULT Action when unexpected behavior (=return values) occurs during one or more context(s) during each step!
//
// Step1_To_5_Key -> Context_Key -> Condition_Key -> Action_Key -> Action_ValueToUse OR CallbackFunctionWithSameAction_ValueToUse
//IMPORTANT: You can ONLY have one Action/CallbackFunction per condition!
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
 * AVAILABLE CONTEXTS (keys within each step):
 * - req = Request Context (e.g. validating request values & CSRF, matching route & middleware)
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
 * AVAILABLE CONDITIONS (keys within each context, except for 'req' - see below):
 * - IS_NULL = When value is still null when it shouldn't be
 *
 * - WRONG_TYPE = When a value is not the expected type when it should be (e.g. string, int, array, etc.)
 *
 * - NOT_CALLABLE = When a value is not callable when it should be
 *
 * - EXCEPTION = When an exception occured somewhere when it shouldn't have
 *
 * - NO_MATCH = When a value was not found/matched during isset(), loop/in_array when it should have been found/matched
 *
 * - NOT_FOUND = When a FILE or DIR (middleware, data and/or page) was not found when it should have been found
 *
 * - FAILED = When a value is not valid when it should be valid (e.g. authentication failed)
 *
 * - UNIQUE CONDITIONS FOR THE 'REQ' CONTEXT:
 *   - METHOD_IS_NULL = When the request method is null when it shouldn't be
 *
 *   - CONTENT_TYPE_IS_NULL = When the request content type is null when it shouldn't be
 *
 *   - ACCEPT_IS_NULL = When the request accept header is null when it shouldn't be
 *
 *   - URI_IS_NULL = When the request URI is null when it shouldn't be
 *
 *   - QUERY_IS_NULL = When the request query is null when it shouldn't be
 *
 *   - MATCHED_ROUTE_IS_NULL = When the matched route is null when it shouldn't be
 *
 *   - MATCHED_DATA_IS_NULL = When the matched data is null when it shouldn't be
 *
 *   - MATCHED_PARAMS_IS_NULL = When the matched params are null when it shouldn't be
 *
 *   - MATCHED_MIDDLEWARES_IS_NULL = When the matched middlewares are null when it shouldn't be
 *
 *   - MATCHED_AUTH_IS_NULL = When the matched auth is null when it shouldn't be
 *
 *   - MATCHED_CSRF_IS_NULL = When the matched CSRF is null when it shouldn't be
 *
 *   - NO_MATCH_IN_IS_NULL = When the no match in is null when it shouldn't be
 *
 *   - KEEP_RUNNING_MWS_IS_NULL = When the keep running middlewares is null when it shouldn't be
 *
 *   - PROTOCOL_IS_NULL = When the request protocol is null when it shouldn't be
 *
 *   - CODE_IS_NULL = When the request code is null when it shouldn't be
 *
 *   - UA_IS_NULL = When the request user agent is null when it shouldn't be
 *
 *   - IP_IS_NULL = When the request IP is null when it shouldn't be
 *
 *
 *******
 * AVAIALBLE ACTIONS (keys within each condition - see below for more details):
 * - Your Callback Function Name ("string") = Calls callable string, uses "actionKeyValue" as the parameter
 *
 * - CODE = HTTP response code to return (int between 100 and 599), default: 500, then exit
 *
 * - REDIRECT = URL to redirect to ("/uriString") with hardcoded 301 code, then exit
 *
 * - LOG = Log message to write to the pre-configured log file ("string"), then continue execution
 *
 * - LOG_ERR = Log error message to write to the pre-configured log file ("string"), then continue execution
 *
 * - RENDER_PAGE = Page to render ("string", "optionalDataToPassToPage"), then exit
 *
 * - RETURN_JSON_CUSTOM = JSON Custom Object String ("Json_EncodedString"), then exit
 *
 * - RETURN_JSON_ERROR = JSON ['error' => ("Json_EncodedString")], then exit
 *
 * - SET_HEADER = Header to set ("string", "string") - (headerName, headerValue), then continue execution
 *
 *******/
// IMPORTANT: Still under Development! (You can use "h_try_default_action" to test it though!)
// It is in "src/funkphp/_internals/functions/h_helper_funs.php" along with the other helper functions!
// NOTICE: Calling the _EXAMPLE_ will just return an err key!
// EXAMPLE: 'STEP_0' => ['req' => ['METHOD_IS_NULL' => ['CODE' => 418]]]

return [
    'STEP_0' =>
    [
        'req' =>
        [
            '_EXAMPLE_' => ['availableActionOrCustomCallback' => 'actionKeyValueUsedByEither'],
            'METHOD_IS_NULL' => [],
            'CONTENT_TYPE_IS_NULL' => [],
            'ACCEPT_IS_NULL' => [],
            'URI_IS_NULL' => [],
            'QUERY_IS_NULL' => [],
            'MATCHED_ROUTE_IS_NULL' => [],
            'MATCHED_DATA_IS_NULL' => [],
            'MATCHED_PARAMS_IS_NULL' => [],
            'MATCHED_MIDDLEWARES_IS_NULL' => [],
            'MATCHED_AUTH_IS_NULL' => [],
            'MATCHED_CSRF_IS_NULL' => [],
            'NO_MATCH_IN_IS_NULL' => [],
            'KEEP_RUNNING_MWS_IS_NULL' => [],
            'PROTOCOL_IS_NULL' => [],
            'CODE_IS_NULL' => [],
            'UA_IS_NULL' => [],
            'IP_IS_NULL' => [],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
    ],
    'STEP_1' => [
        'req' =>
        [
            'METHOD_IS_NULL' => [],
            'CONTENT_TYPE_IS_NULL' => [],
            'ACCEPT_IS_NULL' => [],
            'URI_IS_NULL' => [],
            'QUERY_IS_NULL' => [],
            'MATCHED_ROUTE_IS_NULL' => [],
            'MATCHED_DATA_IS_NULL' => [],
            'MATCHED_PARAMS_IS_NULL' => [],
            'MATCHED_MIDDLEWARES_IS_NULL' => [],
            'MATCHED_AUTH_IS_NULL' => [],
            'MATCHED_CSRF_IS_NULL' => [],
            'NO_MATCH_IN_IS_NULL' => [],
            'KEEP_RUNNING_MWS_IS_NULL' => [],
            'PROTOCOL_IS_NULL' => [],
            'CODE_IS_NULL' => [],
            'UA_IS_NULL' => [],
            'IP_IS_NULL' => [],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'NO_MATCH' => [],
            'EXCEPTION' => [],
            'FAILED' => [],
            'WRONG_TYPE' => [],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
    ],
    'STEP_2' => [
        'req' =>
        [
            'METHOD_IS_NULL' => [],
            'CONTENT_TYPE_IS_NULL' => [],
            'ACCEPT_IS_NULL' => [],
            'URI_IS_NULL' => [],
            'QUERY_IS_NULL' => [],
            'MATCHED_ROUTE_IS_NULL' => [],
            'MATCHED_DATA_IS_NULL' => [],
            'MATCHED_PARAMS_IS_NULL' => [],
            'MATCHED_MIDDLEWARES_IS_NULL' => [],
            'MATCHED_AUTH_IS_NULL' => [],
            'MATCHED_CSRF_IS_NULL' => [],
            'NO_MATCH_IN_IS_NULL' => [],
            'KEEP_RUNNING_MWS_IS_NULL' => [],
            'PROTOCOL_IS_NULL' => [],
            'CODE_IS_NULL' => [],
            'UA_IS_NULL' => [],
            'IP_IS_NULL' => [],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'NO_MATCH' => [],
            'EXCEPTION' => [],
            'FAILED' => [],
            'WRONG_TYPE' => [],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
    ],
    'STEP_3' => [
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'NO_MATCH' => [],
            'EXCEPTION' => [],
            'FAILED' => [],
            'WRONG_TYPE' => [],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
    ],
    'STEP_4' => [
        'req' =>
        [
            'METHOD_IS_NULL' => [],
            'CONTENT_TYPE_IS_NULL' => [],
            'ACCEPT_IS_NULL' => [],
            'URI_IS_NULL' => [],
            'QUERY_IS_NULL' => [],
            'MATCHED_ROUTE_IS_NULL' => [],
            'MATCHED_DATA_IS_NULL' => [],
            'MATCHED_PARAMS_IS_NULL' => [],
            'MATCHED_MIDDLEWARES_IS_NULL' => [],
            'MATCHED_AUTH_IS_NULL' => [],
            'MATCHED_CSRF_IS_NULL' => [],
            'NO_MATCH_IN_IS_NULL' => [],
            'KEEP_RUNNING_MWS_IS_NULL' => [],
            'PROTOCOL_IS_NULL' => [],
            'CODE_IS_NULL' => [],
            'UA_IS_NULL' => [],
            'IP_IS_NULL' => [],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'NO_MATCH' => [],
            'EXCEPTION' => [],
            'FAILED' => [],
            'WRONG_TYPE' => [],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
    ],
    'STEP_5' => [
        'req' =>
        [
            'METHOD_IS_NULL' => [],
            'CONTENT_TYPE_IS_NULL' => [],
            'ACCEPT_IS_NULL' => [],
            'URI_IS_NULL' => [],
            'QUERY_IS_NULL' => [],
            'MATCHED_ROUTE_IS_NULL' => [],
            'MATCHED_DATA_IS_NULL' => [],
            'MATCHED_PARAMS_IS_NULL' => [],
            'MATCHED_MIDDLEWARES_IS_NULL' => [],
            'MATCHED_AUTH_IS_NULL' => [],
            'MATCHED_CSRF_IS_NULL' => [],
            'NO_MATCH_IN_IS_NULL' => [],
            'KEEP_RUNNING_MWS_IS_NULL' => [],
            'PROTOCOL_IS_NULL' => [],
            'CODE_IS_NULL' => [],
            'UA_IS_NULL' => [],
            'IP_IS_NULL' => [],
        ],
        'auth' => [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'NO_MATCH' => [],
            'EXCEPTION' => [],
            'FAILED' => [],
            'WRONG_TYPE' => [],
        ],
        'middlewares' =>
        [
            'IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'db' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'WRONG_TYPE' => [],
        ],
        'd' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
        'p' =>
        [
            'IS_NULL' => [],
            'DATA_IS_NULL' => [],
            'NOT_CALLABLE' => [],
            'EXCEPTION' => [],
            'NOT_FOUND' => [],
            'NO_MATCH' => [],
            'WRONG_TYPE' => [],
        ],
    ],
];
