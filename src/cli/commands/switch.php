<?php // FunkCLI Command `php funk switch`
// SWITCH: It changes the `/src/funkphp/public|public_html/index.php` file
// on its row where "     !is_readable(__DIR__ . '/../funkphp/FunkPHP.php')"
// and "require_once __DIR__ . '/../funkphp/FunkPHP.php';"
// to "     !is_readable(__DIR__ . '/../funkphp/FunkPHPDeployment.php')"
// and "require_once __DIR__ . '/../funkphp/FunkPHPDeployment.php';" respectively

/**
 * -----------------------
 * FUNKCLI DEFAULT COMMAND
 * -----------------------
 * DO NOT MANUALLY EDIT THIS FILE UNLESS YOU UNDERSTAND IT IN AND OUT.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
// Find /src/public|public_html/index.php and swap or just err out along the way!
$targetFilePath = null;
if (defined('FUNKPHP_FILE_PATH_PUBLIC_HTML_INDEX') && is_readable(FUNKPHP_FILE_PATH_PUBLIC_HTML_INDEX)) {
    $targetFilePath = FUNKPHP_FILE_PATH_PUBLIC_HTML_INDEX;
} elseif (defined('FUNKPHP_FILE_PATH_PUBLIC_INDEX') && is_readable(FUNKPHP_FILE_PATH_PUBLIC_INDEX)) {
    $targetFilePath = FUNKPHP_FILE_PATH_PUBLIC_INDEX;
}
if (!$targetFilePath) {
    cli_err('Failed to find/read from `/src/funkphp/public_html/index.php` AND `/src/funkphp/public/index.php` in order to swap between `FunkPHP.php-FunkPHPDeployment.php` Files! Try restore those files using File Versioning Control Tool OR by redownloading a new version of it from FunkPHP!');
}
$index = file_get_contents($targetFilePath);
if ($index === false) {
    cli_err("Failed to load/read from `{$targetFilePath}` to swap between `FunkPHP.php` and `FunkPHPDeployment.php`! Check File Permissions and/or try restore those files using File Versioning Control Tool OR by redownloading a new version of it from FunkPHP!");
}
$devPath  = "'/../funkphp/FunkPHP.php'";
$prodPath = "'/../funkphp/FunkPHPDeployment.php'";
$isDeploymentMode = (strpos($index, $prodPath) !== false);
if ($isDeploymentMode) {
    $index = str_replace($prodPath, $devPath, $index);
    $activeMode = 'DEVELOP (FunkPHP.php)';
} else {
    $index = str_replace($devPath, $prodPath, $index);
    $activeMode = 'DEPLOY (FunkPHPDeployment.php)';
}
if (!cli_crud_folder_php_file_atomic_write($index, $targetFilePath)) {
    cli_err("Failed to write updated entry file configuration back to `{$targetFilePath}`!");
}
cli_success("Switched to entry point: {$activeMode} in `{$targetFilePath}`!");
