<?php
// Singleton Object Constant that indicates "no value"!
define("FUNKPHP_NO_VALUE", new stdClass());

// Constants for Localhost vs Online Usage AND Default URLs (Change to your own!)
// "localhost" & "127.0.0.1" is typical for local deployment
// "_" is typical for nginx in default!
define('FUNKPHP_IS_LOCAL', true);
define('FUNKPHP_LOCAL', "http://localhost/funkphp/src/public_html/");
define('FUNKPHP_ONLINE', "https://www.funkphp.com/");
define('FUNKPHP_PIPLINE_REQUEST_ENTRY', 'defensive'); // Choose between 'happy' or 'defensive' mode for pipeline request entry point!
define('FUNKPHP_USE_VENDOR', true); // Change to "false" if you intend to not use any Composer packages and want to remove the Composer autoloader from "FunkPHP.php" for better performance!
define("ROOT_FOLDER", dirname(__DIR__, 1)); // src/funkphp/
define("ROOT_CORE", ROOT_FOLDER . '/core'); // src/funkphp/core
define("ROOT_CLASSES", ROOT_FOLDER . '/classes'); // src/funkphp/classes
define("ROOT_CONFIG", ROOT_FOLDER . '/config'); // src/funkphp/config
define("ROOT_MIDDLEWARES", ROOT_FOLDER . '/pipeline/middlewares'); // src/funkphp/FunkPHP
define("ROOT_PAGES", ROOT_FOLDER . '/pages'); // src/funkphp/pages
define("ROOT_PAGES_COMPILED", ROOT_FOLDER . '/pages/compiled'); // src/funkphp/pages/compiled
define("ROOT_PAGES_ERRORS", ROOT_FOLDER . '/pages/compiled/[errors]'); // src/funkphp/pages/compiled/[errors]
define("ROOT_PIPELINE", ROOT_FOLDER . '/pipeline'); // src/funkphp/pipeline
define("ROOT_PIPELINE_REQUEST", ROOT_FOLDER . '/pipeline/request'); // src/funkphp/pipeline/request
define("ROOT_PIPELINE_POST_RESPONSE", ROOT_FOLDER . '/pipeline/post-response'); // src/funkphp/pipeline/post-response
define("ROOT_ROUTES", ROOT_FOLDER . '/pipeline/routes'); // src/funkphp/pipeline/routes
define("ROOT_SQL", ROOT_FOLDER . '/data/sql'); // src/funkphp/data/sql
define("ROOT_VALIDATION", ROOT_FOLDER . '/data/validation'); // src/funkphp/data/validation
define('FUNKPHP_ALLOW_INSTANCE_OVERWRITE', true); //
    //^Change to "true" to allow overwriting existing instances!
    // Related to  "'INSTANCES' => ['vendor' => [], 'classes' => []]," in "config.php" file!
