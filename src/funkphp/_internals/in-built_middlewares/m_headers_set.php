<?php
return function (&$c) {
    // Set the header(s) for the HTTPS response
    $headersToSet = $c['HEADERS']['ADD'];
    foreach ($headersToSet as $header) {
        header($header);
    }
};
