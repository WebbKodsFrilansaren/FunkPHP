<?php // src/public_html/critical_err_json.php - When Critical Error Happens with JSON Accept Header
// Write your custom JSON Response here OR You can use the $status and $customMessage variables
// passed by critical_err_json_or_html() from src/public_html/index.php File to use the error
// that was passed by You or the FunkPHP Framework itself somewhere along the way!

// If this file is manually navigated to in the web brower which it shouldn't if
// it has been correctly ignored in the .htaccess file OR nginx Configuration File!
if (
    isset($_SERVER['HTTP_ACCEPT'])
    && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false
) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
    $htmlFilePath = __DIR__ . '/critical_err_html.php';
    $customMessage = "The `critical_err_json.php` file was probably accessed manually in the Web Browser, which it shouldn't be! Please include to ignore that specific file in your .htaccess OR nginx Configuration File, dear Developer!";
    // If the custom file is unreadable, fall through to the hardcoded default HTML below
    if (!is_readable($htmlFilePath)) {
        $e = new \Exception("Required HTML Error File NOT Readable at: " . $htmlFilePath);
    } else {
        try {
            require_once $htmlFilePath;
            exit;
        }
        // This 'catch' will just fall through to the hardcoded default HTML below
        catch (\Throwable $e) {
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>FunkPHP Framework - Internal Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                min-height: 100vh;
                margin: 0;
            }

            body pre {
                justify-self: end;
                align-self: flex-end;
                align-content: end;
                background-color: #eee;
                padding: 10px;
                white-space: pre-wrap;
                white-space: -moz-pre-wrap;
                white-space: -pre-wrap;
                white-space: -o-pre-wrap;
                word-wrap: break-word;
            }

            .container {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                max-width: 420px;
                margin-top: 50px;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 50px;
                padding: 20px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            h1 {
                color: #e74c3c;
                text-align: center;
            }

            p {
                font-size: 16px;
                line-height: 1.5;
            }

            a {
                color: #3498db;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }

            .center-text {
                text-align: center;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>FunkPHP Framework - Internal Error</h1>
            <p><strong>Tell The Developer:</strong> Not Only Did FunkPHP Framework Catch A Critical Error, It Also Failed To Include The Necessary Custom-Made HTML Response in `/public_html/critical_err_html.php` File. Please check your Installation, Filenames, and/or File Permissions!</p>
            <p>Are You The Developer? Verify Installation Paths, Filenames, File Permissions and/or Global Configuration of the FunkPHP Framework where This Website Is Deployed!</p>
            <p class="center-text">Thanks in advance!<br>You are Always Awesome! ^_^</p>
            <p class="center-text"><i>(It also appears that the `/public_html/critical_err_json.php` file was accessed manually in the Web Browser. Well, you can include to ignore that specific file in your .htaccess OR nginx Configuration File, dear Developer!)</i></p>
        </div>
    </body>

    </html>
<?php
    exit;
}
// Return true JSON meaning the file was probably NOT manually navigated to by a user
return [
    'status' => $status ?? 500,
    'internal_error' => $customMessage ?? 'FunkPHP Framework - Internal Error: Important Files could not be Loaded and/or Executed, so Please Tell the Developer to fix the website or the Web Hosting Service to allow for reading the necessary folders & files! If you are the Developer, please check your Configuration and File permissions where you Develop and/or Host this Website!',

];
