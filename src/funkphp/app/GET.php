<?php
$APP->ROUTES()->GET()
    ->ROUTE("/:id")->pipeFunction("group:test_routes");
