<?php
return function (&$c, $passedValue = null) {
    // Set the header(s) for the HTTPS response
    $headersToSet = $c['HEADERS']['ADD'];
    foreach ($headersToSet as $header) {
        header($header);
    }
};
