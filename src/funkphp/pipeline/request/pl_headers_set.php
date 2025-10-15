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
                $err = 'Tell the Developer: The Headers Pipeline Function ran but WITHOUT a Valid Header Structure - Each Header must be a Non-Empty String!';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        }
    }
};
