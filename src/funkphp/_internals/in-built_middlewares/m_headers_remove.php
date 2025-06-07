<?php
return function (&$c) {
    // Remove header(s) for the HTTPS reponse
    $headersToRemove = $c['HEADERS']['REMOVE'];
    foreach ($headersToRemove as $header) {
        header_remove($header);
    }
};
