<?php
// Data Handler File - Created in FunkCLI on 2025-05-23 04:50:47!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function d_test(&$c) // <POST/test/:id>
{
    // Created in FunkCLI on 2025-05-23 04:50:47! Keep "};" on its
    // own new line without indentation no comment right after it!
    echo "Test Data Handler!";
};

return function (&$c, $handler = "d_test") {
    $handler($c);
};
