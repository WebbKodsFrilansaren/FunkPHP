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
        $permissionErrors[] = "<strong>{$name}:</strong> Target path at '<code>{$path}</code>' does NOT EXIST.";
        continue;
    }
    if (!is_dir($realPath)) {
        $permissionErrors[] = "<strong>{$name}:</strong> Target path at'<code>{$realPath}</code>' is NOT A DIRECTORY.";
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
        <title>🛑 FunkPHP / FunkGUI - Environment Setup Required</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #1a1b26;
                color: #a9b1d6;
                padding: 40px;
            }

            .card {
                max-width: 700px;
                margin: 0 auto;
                background: #24283b;
                padding: 30px;
                border-radius: 8px;
                border: 1px solid #f7768e;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }

            h1 {
                color: #f7768e;
                margin-top: 0;
                font-size: 24px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            ul {
                background: #1a1b26;
                padding: 15px 20px 15px 35px;
                border-radius: 6px;
                border-left: 4px solid #f7768e;
                color: #ff9e64;
            }

            li {
                margin-bottom: 8px;
                font-family: monospace;
            }

            pre {
                background: #15161e;
                padding: 15px;
                border-radius: 6px;
                color: #73daca;
                overflow-x: auto;
                font-family: "Fira Code", monospace;
                border: 1px solid #414868;
            }

            .hint {
                color: #565f89;
                font-size: 14px;
                margin-top: 20px;
            }
        </style>
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
    <link rel="stylesheet" href="./core/styles.css">
    <script src="./core/funk.js" defer></script>
    <title>FunkGUI - Local GUI Panel for FunkPHP Framework</title>
</head>

<body>
    <div class="container">
        <h1>🛠️ FunkPHP Framework Local GUI</h1>
        <p>This web interface securely relays parameters to the underlying <code>src/cli/funk</code> compiler architecture.</p>
        <?= "The browser is running PHP as user: " . posix_getpwuid(posix_geteuid())['name']; ?>
        <div class="btn-group">
            <button class="btn" onclick="executeCliCommand('recompile')">Recompile Framework</button>
            <button class="btn" onclick="executeCliCommand('new:r', 'api/v1/users')">Create New Route</button>
            <button class="btn btn-danger" onclick="executeCliCommand('aliases')">Test Reserved Constraint</button>
        </div>

        <h3>Terminal/API Output Response:</h3>
        <pre id="terminal-console">Awaiting local directive...</pre>
    </div>

    <script>
        async function executeCliCommand(commandString, argument1 = null) {
            const consoleBox = document.getElementById('terminal-console');
            consoleBox.innerText = "Dispatching command payload to FunkCLI context...";

            // Match payload layout expected by src/cli/funk regexes
            const payload = {
                command: commandString,
                arg1: null,
                arg2: null,
                arg3: null,
                arg4: null,
                arg5: null,
                arg6: null
            };
            console.log("Constructed payload for CLI command:", payload);

            try {
                // Request points directly to this file, but specifies JSON intent
                console.log("Sending payload to FunkPHP GUI Gateway:", payload);
                const response = await fetch('/funkgui/', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const rawText = await response.text();

                try {
                    // Prettify the output if it's structural valid JSON
                    const parsedJson = JSON.parse(rawText);
                    consoleBox.innerText = JSON.stringify(parsedJson, null, 4);
                } catch {
                    // Fall back to displaying raw string output if text/formatting bled through
                    consoleBox.innerText = rawText;
                }

            } catch (err) {
                consoleBox.innerText = "Network Gateway Communication Error: " + err.message;
            }
        }
    </script>
</body>

</html>