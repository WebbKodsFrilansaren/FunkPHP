<?php
// Route Handler for Route Route: GET/test2
// File created in FunkCLI!

function t_1(&$c)
{
    echo "Hello from t-1! This is the default handler!";
    echo "<br>";
};

return function (&$c, $handler = "t_1") {
    $handler($c);
};
