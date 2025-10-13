<?php
return function (&$c, $passedValue = null) {
    // Remove header(s) for the HTTPS reponse
    $headersToRemove = $c['HEADERS']['REMOVE'];
    foreach ($headersToRemove as $header) {
        header_remove($header);
    }
    // Allow optionally removed custom headers set as an array of strings to be passed to this pipeline function
    // but any invalid value (non-string) will cause a script-ending 500 error with a message to the developer
    if ($passedValue !== null && is_array($passedValue)) {
        foreach ($passedValue as $header) {
            if (is_string($header) && !empty($header)) {
                header_remove($header);
            } else {
                $err = 'Tell the Developer: The Headers Pipeline Function ran but WITHOUT a Valid Header Structure - Each Header must be a Non-Empty String!';
                funk_use_custom_error($c, ['json_or_page', ['json' => ["custom_error" => $err], 'page' => '500'], $err], 500);
            }
        }
    }
};
