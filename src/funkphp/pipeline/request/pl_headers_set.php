<?php
return function (&$c, $passedValue = null) {
    // Set the header(s) for the HTTPS response
    $headersToSet = $c['HEADERS']['ADD'];
    foreach ($headersToSet as $header) {
        header($header);
    }
    // Allow optionally added custom headers set as an array of strings to be passed to this pipeline function
    // but any invalid value (non-string) will cause a script-ending 500 error with a message to the developer
    if ($passedValue !== null && is_array($passedValue)) {
        foreach ($passedValue as $header) {
            if (is_string($header) && !empty($header)) {
                header($header);
            } else {
                $c['err']['PIPELINE']['REQUEST']['HEADERS'][] = 'An Optional Header Value passed to Headers Pipeline Function is not a Valid String Value: `' . var_export($header, true) . '`.';
                $err = 'Tell the Developer: The Headers Pipeline Function ran but WITHOUT a Valid Header Structure - Each Header must be a Non-Empty String!';
                funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
            }
        }
    }
};
