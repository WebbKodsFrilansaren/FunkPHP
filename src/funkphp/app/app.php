<?php $APP = FunkPHP();
require_once ROOT_APP_CONFIG;
$routeFiles = [
    ROOT_APP_GET,
    ROOT_APP_POST,
    ROOT_APP_PUT,
    ROOT_APP_PATCH,
    ROOT_APP_DELETE,
];
foreach ($routeFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
return $APP;
