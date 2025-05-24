<?php
// Route Handler File - Created in FunkCLI on 2025-05-24 08:52:21!
// IMPORTANT: CMD+S or CTRL+S to autoformat each time function is added!

function r_test(&$c) // <POST/test/:id>
{
    // Created in FunkCLI on 2025-05-24 08:52:21! Keep "};" on its
    // own new line without indentation no comment right after it!
    echo "Hello from r_test! (Route Handler)";
};

return function (&$c, $handler = "r_test") {
    return $handler($c);
};
