<?php // FunkCLI Command `php funk build|b`
// BUILD: Generates the `FunkPHPDeployment.php" mega file that is
// supposed to include: core, routes, & middlewares for deployment
// to a server. It can also optionally compile pages as well and/or
// compress all files into a single zip file for easier deployment!
$embedPages = false; // imlpemented later
$compilePages = false;
$compressDeployment = false;
$skipBrokenRoutes = false; // implemented later

// Inside of $args[] array on 1 or 2 we can have the optional
// configs for "--compile-pages" and/or "--compress-deployment"
// or the single one "--both" for faster typing.
// The $args[1] is always the command so we do not look for it!
// So we iterate through $args on 1 & 2 to find those optional flags
if (count($args) > 0) {
    for ($i = 0; $i <= 1; $i++) {
        if (isset($args[$i]) && is_string($args[$i]) && !empty($args[$i])) {
            if (strtolower($args[$i]) === "--both") {
                $compilePages = true;
                $compressDeployment = true;
            } else if (strtolower($args[$i]) === "--compile-pages") {
                $compilePages = true;
            } else if (strtolower($args[$i]) === "--compress-deployment") {
                $compressDeployment = true;
            }
        }
    }
}
cli_info_without_exit("### FunkCLI Compiling & Building `FunkPHPDeployment.php` with the following options:");
cli_info_without_exit("#### Compile Pages: " . ($compilePages ? "YES" : "NO"));
cli_info_without_exit("#### Compress Deployment: " . ($compressDeployment ? "YES" : "NO"));

// The actual compiling & building steps
cli_info_without_exit("### Step 1: Loading, Validating & Compiling `config.php` File ('Config' in FunkGUI)...");

cli_info_without_exit("### Step 2: Loading, Validating & Compiling `pipeline_request.php` ('Request' & 'Post_Response' in 'Pipeline' in FunkGUI) File...");

cli_info_without_exit("### Step 3: Loading, Validating & Compiling Core `functions.php` & User-defined `funkphp => config => functions.php` Files ('User-defined Functions' in 'Config' in FunkGUI)...");

cli_info_without_exit("### Step 4: Loading, Validating, Rebuilding & Compiling `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' in 'Pipeline' in FunkGUI)...");

cli_info_without_exit("### Step 5: Loading, Validating, & Compiling Pipeline Functions (files in `src/funkphp/pipeline/routes/`) & Middlewares Functions (files in `src/funkphp/pipeline/middlewares) Used For Each Valid Route Compiled From `compiled_routes.php` & `pipeline_routes.php` Files ('Routes' & 'Middlewares' in 'Pipeline' in FunkGUI)...");

cli_info_without_exit("### Step 6: Running any optional flags before finishing...");


// This should happen if everything above went smoothly!
cli_success("### FunkCLI Successfully Compiled & Built `FunkPHPDeployment.php` with the following options:\n### Compile Pages: " . ($compilePages ? "YES" : "NO") . "\n### Compress Deployment: " . ($compressDeployment ? "YES" : "NO") . "\n### You can now deploy the `FunkPHPDeployment.php` file to your server for production use!");
exit;
