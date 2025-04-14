<?php
// IMPORTANT: Still under Development = NOTHING IS IMPLEMENTED YET! (NOTHING ACTUALLY HAPPENS!)
// (This will probably only be implemented if FunkPHP actually gets a user base to begin with!)
//
// DEFAULT Behavior when unexpected behavior occurs during each step!
// Unexpected behavior is when a variable is not set or is NULL when it shouldn't be!
//
// For example: each request should have a valid http method set and not null!
// It is first divided into what STEP_# the unexpected behavior occurs in,
// and then what $variable it occured in, and then what single
// action should be taken! with what single value of that action!
/**
 * EXAMPLE: "'STEP_0' => ['req' => ['method' =>
 *            'NOT_FOUND'=> ['CODE' => 400]"
 * Example above means during Step 0 there was no valid HTTP method set,
 * and it should have been set to a valid HTTP method. So, the default
 * behavior is to return a 400 Bad Request code with no redirect.
 *
 * AVAILABLE BEHAVIORS (keys):
 * - NOT_FOUND = When value returned NULL when it shouldn't
 *  (e.g. a request should ALWAYS come with a valid method)
 *  even if it is not in the list of allowed methods.
 *
 * AVAIALBLE BEHAVIORS (single_action/key => single_value/value) - what should happen, and its value:
 * - CODE = HTTP response code to return (default: 200)
 * - REDIRECT = URL to redirect to (default: [])
 * - LOG = Log message to write to the log file (default: [])
 * - DB = Database query to run (default: [])
 **/

// IMPORTANT: Still under Development = NOTHING IS IMPLEMENTED YET! (NOTHING ACTUALLY HAPPENS!)
return [
    'STEP_0' =>
    [
        'req' => [
            'method' => ['NOT_FOUND' => []],
            'uri' => ['NOT_FOUND' => []],
        ],
        'db' => ['NOT_FOUND' => []],
    ],
    'STEP_1' => [
        'req' => [
            'method' => ['NOT_FOUND' => []],
            'uri' => ['NOT_FOUND' => []],
        ],
        'db' => ['NOT_FOUND' => []],
    ],
    'STEP_2' => [
        'req' => [
            'method' => ['NOT_FOUND' => []],
            'uri' => ['NOT_FOUND' => []],
        ],
        'db' => ['NOT_FOUND' => []],
    ],
    'STEP_3' => [
        'req' => [
            'method' => ['NOT_FOUND' => []],
            'uri' => ['NOT_FOUND' => []],
        ],
        'db' => ['NOT_FOUND' => []],
    ],
    'STEP_4' => [
        'req' => [
            'method' => ['NOT_FOUND' => []],
            'uri' => ['NOT_FOUND' => []],
        ],
        'db' => ['NOT_FOUND' => []],
    ],
    'STEP_5' => [
        'req' => [
            'method' => ['NOT_FOUND' => []],
            'uri' => ['NOT_FOUND' => []],
        ],
        'db' => ['NOT_FOUND' => []],
    ],
];
