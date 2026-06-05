<?php
// src/gui/index.php

// Directories & Sub-directories to validate that they exist, are dirs and readable+writable
$requiredWritablePaths = [
    'Backups Main Directory'    => __DIR__ . '/../backups',
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

$permissionErrors = [];
foreach ($requiredWritablePaths as $name => $path) {
    // Realpath checks through any symlinks, just in case
    $realPath = realpath($path);
    if (!$realPath) {
        $permissionErrors[] = "<strong>{$name}:</strong> Target path at '<code>{$path}</code>' does NOT EXIST. Please make sure it is created and accessible by the Web Server.";
        continue;
    }
    if (!is_dir($realPath)) {
        $permissionErrors[] = "<strong>{$name}:</strong> Target path at'<code>{$realPath}</code>' is NOT A DIRECTORY. Please make sure it is a folder and accessible by the Web Server.";
        continue;
    }
    if (!is_readable($realPath)) {
        $permissionErrors[] = "<strong>{$name}:</strong> The folder at '<code>{$realPath}</code>' is NOT READABLE by the Web Server.";
    }
    if (!is_writable($realPath)) {
        $permissionErrors[] = "<strong>{$name}:</strong> The folder at '<code>{$realPath}</code>' is NOT WRITABLE by the Web Server.";
    }
}

// HTML: If errors exist, HALT everything and show a gorgeous recovery screen
if (!empty($permissionErrors) && ($_SERVER['HTTP_ACCEPT'] ?? '') !== 'application/json') {
    $currentUser = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown_web_user';
    // Calculate the parent root directory that needs the permission fix
    $projectRoot = realpath(dirname(__DIR__));
    // Fetch the current system permissions of the root directory
    $rootPerms = fileperms($projectRoot);
    // Mask out the file type data and convert the integer to a clean octal string (e.g., "755")
    $defaultPermsOctal = $rootPerms ? sprintf('%o', $rootPerms & 0777) : '755';
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>🛑 FunkGUI in FunkPHP Framework - Permissions in Environment Setup Required</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="./core/permissions.css">
    </head>

    <body>
        <div class="card">
            <h1>🛑 FunkPHP / FunkGUI Permission Check</h1>
            <p>You must Allow certain Folders in your FunkPHP Project to be Accessible+Writable in order to use FunkGUI.</p>
            <p>The Browser is currently running PHP as Web User: `<strong><?= htmlspecialchars($currentUser); ?>`</strong>.</p>
            <p>Don't worry about the number of possible issues. Just scroll to the bottom to see the 99,9 % One-Liner Simple Fix!</p>
            <h3>Issues Detected (<?= count($permissionErrors) ?? 'Unknown number?'; ?>):</h3>
            <ul>
                <?php foreach ($permissionErrors as $error): ?>
                    <li><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <h3>💡 Quick Fix (Copy & Paste into Terminal):</h3>
            <p>Run This Command inside Your Terminal to Grant the Web Server Permission for FunkGUI to work:</p>
            <pre>chmod -R 777 <?= htmlspecialchars($projectRoot); ?></pre>
            <p>You can Reset Permissions Back to Your Environment Default (<strong><?= htmlspecialchars($defaultPermsOctal); ?></strong>) by running this command:</p>
            <pre>chmod -R <?= htmlspecialchars($defaultPermsOctal); ?> <?= htmlspecialchars($projectRoot); ?></pre>
            <div class="hint">
                <em><strong>IMPORTANT:</strong> Using 777 is ONLY recommended for Local Web Development Environments! (remember to reset back to default if you stop using FunkGUI and/or for best practice!)</em>
            </div>
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="./core/gui.css">
    <script src="./core/funk.js" defer></script>
    <title>FunkGUI - Local GUI Panel for FunkPHP Framework</title>
</head>

<body>
    <div class="container">
        <h1>FunkGUI - Local FunkPHP Framework Utility</h1>
        <p>FunkGUI makes JSON requests to FunkCLI via <code>src/cli/funk</code> that then does the work and returns JSON formatted responses with Info, Warnings, Errors and/or Successes (see in Terminal).</p>
        <small class="user-badge">Running PHP as user: <?= htmlspecialchars(posix_getpwuid(posix_geteuid())['name']); ?></small>

        <div class="tab-menu">
            <button class="tab-btn active" data-target="tab-dashboard">Dashboard</button>
            <button class="tab-btn" data-target="tab-routes">Route Studio</button>
            <button class="tab-btn" data-target="tab-config">Project Config</button>
        </div>

        <hr class="divider">

        <div class="tab-container">

            <div id="tab-dashboard" class="tab-content active">
                <h3>Quick Actions</h3>
                <div class="btn-group">
                    <button class="btn" onclick="executeCliCommand('recompile')">Recompile Framework</button>
                    <button class="btn btn-danger" onclick="executeCliCommand('aliases')">Test Reserved Constraint</button>
                </div>
            </div>

            <div id="tab-routes" class="tab-content">
                <h3>Interactive Route Studio</h3>
                <p>Manage and map your architectural endpoints visually without touching structural files directly.</p>
                <div class="btn-group">
                    <button class="btn" onclick="executeCliCommand('new:r', 'api/v1/users')">Quick Create Default Users Route</button>
                </div>
            </div>

            <div id="tab-config" class="tab-content">
                <h3>Global Project Configuration</h3>
                <p>Manage environmental variables, database arrays, and local routing contexts.</p>
            </div>

        </div>

        <h3>FunkCLI Terminal/API Output Response:</h3>
        <input /* type="text" id="cli-command-input" placeholder="Type a FunkCLI command here and press Enter..." onkeydown="handleCommandInput(event)" * />
        <div id="terminal-console">FunkCLI output will show up here...</div>
    </div>

</html>