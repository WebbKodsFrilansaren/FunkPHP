<?php
// src/gui/index.php
# =========================================================================
# SYSTEM COMPATIBILITY CONFIGURATION
# =========================================================================
# =========================================================================
# 1. DETECT LINUX DISTRO & PACKAGE MANAGER ENVIRONMENT
# =========================================================================
$pkgManager = 'unknown';
if (file_exists('/usr/bin/apt')) {
    $pkgManager = 'apt';     // Ubuntu, Debian, Pop!_OS, Mint
} elseif (file_exists('/usr/bin/dnf') || file_exists('/usr/bin/yum')) {
    $pkgManager = 'dnf';     // Fedora, RedHat, CentOS, Rocky Linux
} elseif (file_exists('/usr/bin/pacman')) {
    $pkgManager = 'pacman';  // Arch Linux, Manjaro
}  // Check if Windows or MacOS instead
elseif (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
    $pkgManager = 'windows';
} elseif (strncasecmp(PHP_OS, 'DAR', 3) === 0) {
    $pkgManager = 'macos';
}
$ver = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
# =========================================================================
# 2. SYSTEM ARCHITECTURE COMPATIBILITY MATRIX
# =========================================================================
$minimumPhpVersion = '8.3.0';
// Structured metadata mapping modules directly to target environment package states
$requiredExtensions = [
    'posix'     => ['purpose' => 'Required for Linux Web User identity mapping.', 'apt' => 'common', 'dnf' => 'common', 'win_supported' => false],
    'ctype'     => ['purpose' => 'Required for character type check loops.', 'apt' => 'common', 'dnf' => 'common', 'win_supported' => true],
    'curl'      => ['purpose' => 'Required for internal API networking and package calls.', 'apt' => 'curl', 'dnf' => 'curl', 'win_supported' => true],
    'dom'       => ['purpose' => 'Required for advanced XML/HTML DOM node compiling.', 'apt' => 'xml', 'dnf' => 'xml', 'win_supported' => true],
    'fileinfo'  => ['purpose' => 'Required for secure asset upload profiling.', 'apt' => 'common', 'dnf' => 'common', 'win_supported' => true],
    'filter'    => ['purpose' => 'Required for framework-wide input validation.', 'apt' => null, 'dnf' => null, 'win_supported' => true],
    'hash'      => ['purpose' => 'Required for data encryption and routing hashes.', 'apt' => null, 'dnf' => null, 'win_supported' => true],
    'mbstring'  => ['purpose' => 'Required for safe multi-byte UTF-8 strings.', 'apt' => 'mbstring', 'dnf' => 'mbstring', 'win_supported' => true],
    'openssl'   => ['purpose' => 'Required for cryptographically secure tokens.', 'apt' => null, 'dnf' => null, 'win_supported' => true],
    'pcre'      => ['purpose' => 'Required for core framework regex route matching.', 'apt' => null, 'dnf' => null, 'win_supported' => true],
    'pdo'       => ['purpose' => 'Required for secure database operations.', 'apt' => 'common', 'dnf' => 'pdo', 'win_supported' => true],
    'session'   => ['purpose' => 'Required for cross-request state and tab logic.', 'apt' => null, 'dnf' => null, 'win_supported' => true],
    'tokenizer' => ['purpose' => 'Required for deep structural code compilation.', 'apt' => 'common', 'dnf' => 'common', 'win_supported' => true],
    'xml'       => ['purpose' => 'Required for handling structured config nodes.', 'apt' => 'xml', 'dnf' => 'xml', 'win_supported' => true],
];
$requiredWritablePaths = [
    'Backups Main Directory'    => __DIR__ . '/../backupsa',
    'Batteries Main Directory'    => __DIR__ . '/../batteries',
    'Batteries Middlewares Directory'    => __DIR__ . '/../batteries/middlewares',
    'Batteries Pipeline Main Directory'    => __DIR__ . '/../batteries/pipeline',
    'Batteries Pipeline Post-Response Directory'    => __DIR__ . '/../batteries/pipeline/post-response',
    'Batteries Pipeline Post-Request Directory'    => __DIR__ . '/../batteries/pipeline/post-request',
    'Batteries Pipeline Request Directory'    => __DIR__ . '/../batteries/pipeline/request',
    'CLI Main Directory'    => __DIR__ . '/../cli',
    'CLI Commands Directory'    => __DIR__ . '/../cli/commands',
    'CLI Config Directory'    => __DIR__ . '/../cli/config',
    'FunkPHP Main Directory'    => __DIR__ . '/../funkphp',
    'FunkPHP Core Directory'    => __DIR__ . '/../funkphp/core',
    'FunkPHP Middlewares Directory'    => __DIR__ . '/../funkphp/middlewares',
    'FunkPHP Pages Main Directory'    => __DIR__ . '/../funkphp/pages',
    'FunkPHP Pages Compiled Directory'    => __DIR__ . '/../funkphp/pages/compiled',
    'FunkPHP Pages Components Directory'    => __DIR__ . '/../funkphp/pages/components',
    'FunkPHP Pages Layouts Directory'    => __DIR__ . '/../funkphp/pages/layouts',
    'FunkPHP Pages Partials Directory'    => __DIR__ . '/../funkphp/pages/partials',
    'FunkPHP Pipeline Main Directory'    => __DIR__ . '/../funkphp/pipeline',
    'FunkPHP Pipeline Post-Response Directory'    => __DIR__ . '/../funkphp/pipeline/post-response',
    'FunkPHP Pipeline Request Directory'    => __DIR__ . '/../funkphp/pipeline/request',
    'FunkPHP Routes Directory'    => __DIR__ . '/../funkphp/routes',
    'FunkPHP SQL Directory'    => __DIR__ . '/../funkphp/sql',
    'FunkPHP Vendor Directory'    => __DIR__ . '/../funkphp/vendor',
    'FunkGUI Main Directory'    => __DIR__ . '/../gui',
    'Public_HTML Main Directory'    => __DIR__ . '/../public_html',
    'Schema Main Directory'    => __DIR__ . '/../schema',
    'Snippets Main Directory'    => __DIR__ . '/../snippets',
    'Tests Main Directory'    => __DIR__ . '/../tests',
];
$compatibilityErrors = [];
$missingPackagesQueue = [];
# =========================================================================
# 3. COMPATIBILITY DISCOVERY RUNTIME
# =========================================================================
// Phase A: PHP Version Assert
if (version_compare(PHP_VERSION, $minimumPhpVersion, '<')) {
    $compatibilityErrors[] = "<strong>PHP Runtime Engine:</strong> FunkPHP requires PHP <code>>= {$minimumPhpVersion}</code>. Your current environment runs <code>" . PHP_VERSION . "</code>.";
}
// Phase B: Module Assessment & Fix Construction
foreach ($requiredExtensions as $ext => $meta) {
    if (!extension_loaded($ext)) {
        // Handle special case: Extension is completely unsupported natively on this OS
        if ($pkgManager === 'windows' && $meta['win_supported'] === false) {
            // Skip blocking the developer entirely on Windows for Linux-only extensions,
            // or choose to treat it as a non-blocking environment warning.
            continue;
        }
        $fixInstruction = "";
        if ($pkgManager === 'apt' && !empty($meta['apt'])) {
            $pkgName = "php{$ver}-" . $meta['apt'];
            $missingPackagesQueue[] = $pkgName;
            $fixInstruction = "[Fix: Run `sudo apt install {$pkgName}`]";
        } elseif ($pkgManager === 'dnf' && !empty($meta['dnf'])) {
            $pkgName = "php-" . $meta['dnf'];
            $missingPackagesQueue[] = $pkgName;
            $fixInstruction = "[Fix: Run `sudo dnf install {$pkgName}`]";
        } elseif ($pkgManager === 'pacman') {
            $fixInstruction = "[Fix: Uncomment `extension={$ext}` in your `/etc/php/php.ini` file]";
        } elseif ($pkgManager === 'windows') {
            $fixInstruction = "[Fix: Open your active `php.ini` configuration (via WAMP/XAMPP control panel) and uncomment the line: `extension={$ext}` (delete the leading semicolon `;`)]";
        } elseif ($pkgManager === 'macos') {
            $fixInstruction = "[Fix: Open your active `php.ini` configuration (MAMP template or Laravel Herd settings) and ensure `extension={$ext}.so` is active]";
        } else {
            $fixInstruction = "[Fix: Enable module inside your environment's active `php.ini` setup or however it is done on your system]";
        }
        $compatibilityErrors[] = "<strong>PHP Extension [{$ext}]:</strong> The module is missing or disabled. (Purpose: {$meta['purpose']} {$fixInstruction})";
    }
}
// Phase C: Storage Write Assertions
foreach ($requiredWritablePaths as $name => $path) {
    $realPath = realpath($path);
    if (!$realPath) {
        $compatibilityErrors[] = "<strong>{$name}:</strong> Target path at '<code>{$path}</code>' does NOT EXIST.";
        continue;
    }
    if (!is_readable($realPath)) {
        $compatibilityErrors[] = "<strong>{$name}:</strong> The folder at '<code>{$realPath}</code>' is NOT READABLE by the Web Server.";
    }
    if (!is_writable($realPath)) {
        $compatibilityErrors[] = "<strong>{$name}:</strong> The folder at '<code>{$realPath}</code>' is NOT WRITABLE by the Web Server.";
    }
}
# =========================================================================
# 4. COMPREHENSIVE RECOVERY VIEW INTERPOLATION
# =========================================================================
if (!empty($compatibilityErrors) && ($_SERVER['HTTP_ACCEPT'] ?? '') !== 'application/json') {
    $server = isset($_SERVER['SERVER_SOFTWARE']) ? explode('/', $_SERVER['SERVER_SOFTWARE'])[0] : 'unknown_server';
    $currentUser = extension_loaded('posix') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown_user') : 'unknown_user';
    $projectRoot = realpath(dirname(__DIR__));
    $rootPerms = fileperms($projectRoot);
    $defaultPermsOctal = $rootPerms ? sprintf('%o', $rootPerms & 0777) : '755';
    // Compile the Unified Environment Package Setup Script
    $unifiedExtensionCommand = "";
    if (!empty($missingPackagesQueue)) {
        $uniquePackages = array_unique($missingPackagesQueue);
        if ($pkgManager === 'apt') {
            $unifiedExtensionCommand = "sudo apt update && sudo apt install -y " . implode(' ', $uniquePackages) . " && sudo systemctl restart php{$ver}-fpm";
        } elseif ($pkgManager === 'dnf') {
            $unifiedExtensionCommand = "sudo dnf install -y " . implode(' ', $uniquePackages) . " && sudo systemctl restart php-fpm";
        } elseif ($pkgManager === 'pacman') {
            $unifiedExtensionCommand = "# On Arch Linux, uncomment these extensions inside your /etc/php/php.ini configuration file, then run:\nsudo systemctl restart php-fpm";
        } elseif ($pkgManager === 'windows') {
            $isCliFix = false;
            $unifiedExtensionCommand = "1. Click your WAMP/XAMPP system tray icon.\n2. Navigate to the PHP Extensions menu selection panel.\n3. Click on the inactive modules to toggle them on instantly, then restart your web services.";
        } elseif ($pkgManager === 'macos') {
            $isCliFix = false;
            $unifiedExtensionCommand = "# If using MAMP: Toggle extensions via MAMP PRO Interface -> PHP Settings tab.\n# If using Laravel Herd: Navigate to Herd Preferences -> Extensions and check the boxes.";
        } else {
            $isCliFix = false;
            $unifiedExtensionCommand = "You are running an unrecognized or custom environment. Please enable the missing extensions manually via your active php.ini configuration file or however it is done on your system!";
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>FunkGUI - System Configuration Required</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./core/permissions.css">
    </head>

    <body style="background: #1e1e2e; color: #cdd6f4; font-family: sans-serif; padding: 40px;">
        <div style="max-width: 800px; margin: 0 auto; background: #252538; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); border: 1px solid #45475a;">
            <h1 style="color: #f38ba8; border-b: 2px solid #45475a; padding-bottom: 10px; margin-top: 0;">FunkPHP Framework Environment Check</h1>
            <p style="font-size: 1em; color: #a6adc8;">The following must be fixed in order to use FunkGUI, FunkCLI and FunkPHP as a whole.</p>
            <p style="font-size: 1em; color: #a6adc8;">The FunkGUI seems to run <strong><?= PHP_OS ?> PHP <?= $ver; ?></strong> on server <strong><?= $server ?></strong> via process <strong><?= htmlspecialchars($currentUser); ?></strong> as user <strong><?= get_current_user(); ?></strong> with Package Manager <strong><?= strtoupper($pkgManager); ?></strong>.</p>

            <h3 style="color: #f9e2af; margin-top: 25px;">Issues Detected (<?= count($compatibilityErrors); ?>):</h3>
            <ul style="background: #11111b; padding: 20px; border-radius: 6px; list-style-type: none; border: 1px solid #45475a;">
                <?php foreach ($compatibilityErrors as $error): ?>
                    <li style="margin-bottom: 12px; font-family: monospace; font-size: 1.1em; line-height: 1.5; border-left: 3px solid #f38ba8; padding-left: 10px;"><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($unifiedExtensionCommand)): ?>
                <h3 style="color: #a6e3a1; margin-top: 25px;">Quick Fix: Install Missing Modules</h3>
                <p style="font-size: 1em; color: #a6adc8;"><?= $isCliFix ? 'Run this combined command sequence inside your terminal to install all missing dependencies at once (and to also restart your current PHP version):' : 'Follow these environmental instructions to enable your missing extensions:' ?></p>
                <pre style="background: #11111b; padding: 15px; border-radius: 4px; border: 1px solid #a6e3a1/50; color: #a6e3a1; font-family: monospace; overflow-x: auto; font-size: 1.1em; white-space: pre-wrap; word-break: break-all;"><?= htmlspecialchars($unifiedExtensionCommand); ?></pre>
            <?php endif; ?>

            <?php if ($pkgManager !== 'windows' && $pkgManager !== 'unknown'): ?>
                <h3 style="color: #89b4fa; margin-top: 25px;">Quick Fix: Storage Permissions</h3>
                <p style="font-size: 1em; color: #a6adc8;">Run this command line to open up your storage components framework path access rules securely for local evaluation:</p>
                <pre style="background: #11111b; padding: 15px; border-radius: 4px; border: 1px solid #45475a; color: #89b4fa; font-family: monospace; overflow-x: auto; font-size: 1.1em;">chmod -R 777 <?= htmlspecialchars($projectRoot); ?></pre>

                <p style="font-size: 1em; color: #a6adc8;">You can reset storage paths back to environment defaults later via:</p>
                <pre style="background: #11111b; padding: 15px; border-radius: 4px; border: 1px solid #45475a; color: #89b4fa; font-family: monospace; overflow-x: auto; font-size: 1.1em;">chmod -R <?= htmlspecialchars($defaultPermsOctal); ?> <?= htmlspecialchars($projectRoot); ?></pre>
            <?php endif; ?>
        </div>
    </body>

    </html>
<?php
    exit;
}

// JSON: If JSON API request fails permission check, return cleanly formatted JSON
if (!empty($permissionErrors)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'Error',
        'messages' => [['type' => 'ERROR', 'message' => 'Environment Read and/or Write Permission Block. Start this File in the Web Browser for more info!']]
    ]);
    exit;
}

// IF ALL WORKS FINE, THEN WE CHECK THE ACCEPT HEADER TO DECIDE WHICH PIPELINE TO ROUTE TO
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';

# =========================================================================
# PIPELINE 1: JSON PASS-THROUGH GATEWAY
# =========================================================================
if (strpos($acceptHeader, 'application/json') !== false) {
    $cliPath = __DIR__ . '/../cli/funk';

    if (!is_readable($cliPath)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'type' => 'ERROR',
            'message' => 'FunkPHP GUI Gateway Error - The Core CLI Engine File was not found or is unreadable at: ' . $cliPath
        ]);
        exit;
    }

    // Since src/cli/funk natively reads php://input and handles its own headers/exits,
    // we just include it directly. It will seamlessly take over the current web request!
    require $cliPath;
    exit;
}

# =========================================================================
# PIPELINE 2: TEXT/HTML (Dashboard Render)
# =========================================================================
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="./core/gui.css">
    <script src="./core/funk.js" defer></script>
    <title>FunkGUI - Local GUI Panel for FunkPHP Framework</title>
</head>

<body class="bg-[#1e1e2e] text-[#cdd6f4] p-5 min-h-full font-sans antialiased">

    <div class="max-w-3xl mx-auto my-10 bg-[#252538] p-8 rounded-lg shadow-2xl border border-[#45475a]/30">

        <h1 class="text-2xl font-bold text-[#89b4fa] border-b border-[#45475a] pb-3 m-0">
            FunkGUI - Local FunkPHP Framework Utility
        </h1>

        <p class="text-sm text-[#xl-slate] text-slate-400 mt-3 leading-relaxed">
            FunkGUI makes JSON requests to FunkCLI via <code class="bg-[#11111b] px-1.5 py-0.5 rounded text-[#f5e0dc] font-mono text-xs">src/cli/funk</code> that then does the work and returns JSON formatted responses with Info, Warnings, Errors and/or Successes.
        </p>

        <div class="inline-block bg-[#45475a]/60 text-xs text-slate-300 px-2.5 py-1 rounded mt-3 font-mono">
            Running PHP as user: <?= htmlspecialchars(posix_getpwuid(posix_geteuid())['name']); ?>
        </div>

        <div class="flex gap-1 mt-8 border-b border-[#45475a]">
            <button class="tab-btn active" data-target="tab-dashboard">Dashboard</button>
            <button class="tab-btn" data-target="tab-routes">Route Studio</button>
            <button class="tab-btn" data-target="tab-config">Project Config</button>
        </div>

        <div class="my-6">

            <div id="tab-dashboard" class="tab-content active">
                <h3 class="text-base font-bold text-slate-200 mb-3">Quick Actions</h3>
                <div class="flex gap-3">
                    <button class="btn btn-success" onclick="executeCliCommand('recompile')">Recompile Framework</button>
                    <button class="btn btn-danger" onclick="executeCliCommand('aliases')">Test Reserved Constraint</button>
                </div>
            </div>

            <div id="tab-routes" class="tab-content">
                <h3 class="text-base font-bold text-slate-200 mb-2">Interactive Route Studio</h3>
                <p class="text-sm text-slate-400 mb-4">Manage and map your architectural endpoints visually without touching structural files directly.</p>
                <div class="flex gap-3">
                    <button class="btn btn-success" onclick="executeCliCommand('new:r', 'api/v1/users')">Quick Create Default Users Route</button>
                </div>
            </div>

            <div id="tab-config" class="tab-content">
                <h3 class="text-base font-bold text-slate-200 mb-2">Global Project Configuration</h3>
                <p class="text-sm text-slate-400">Manage environmental variables, database arrays, and local routing contexts.</p>
            </div>

        </div>

        <hr class="border-[#45475a] my-6">

        <h3 class="text-base font-bold text-slate-300 mb-2">FunkCLI Terminal/API Output Response:</h3>

        <input
            type="text"
            id="cli-command-input"
            placeholder="Type a FunkCLI command here and press Enter..."
            onkeydown="handleCommandInput(event)"
            class="w-full p-3 mb-4 rounded border border-[#45475a] bg-[#11111b] text-[#f5e0dc] font-mono text-sm focus:outline-none focus:border-[#89b4fa] transition-colors placeholder-slate-600" />

        <div id="terminal-console" class="w-full p-4 rounded border border-[#45475a] bg-[#11111b] text-[#f5e0dc] font-mono text-sm min-h-[120px] overflow-x-auto box-border flex flex-col gap-3">
            FunkCLI output shows up here...
        </div>

    </div>

</body>

</html>