<?php
// DEFAULT CHECK THAT ALL NEEDED FILES EXIST OR WE THROW DEFAULT JSON ERROR
// OR DEFAULT HTML ERROR PAGE - YOU CAN CONFIGURE THIS RIGHT BELOW HERE!
function critical_err_json_or_html($status = 500, $customMessage = "<No Custom Message Included!>")
{
    // Return JSON if 'Accept' Header includes 'application/json', otherwise always
    // return HTML Error Page (unless You modify inside the `critical_err_html.php`
    // and/or `critical_err_json.php` files to do something else!)
    if (
        isset($_SERVER['HTTP_ACCEPT'])
        && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        http_response_code($status);
        header('Content-Type: application/json');
        try {
            echo json_encode(require_once __DIR__ . '/critical_err_json.php', JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            echo json_encode([
                'status' => $status,
                'internal_error' => 'Tell The Developer - Not Only Did FunkPHP Framework Catch A Critical Error, It Also Failed To Include The Necessary Custom-Made JSON Response in `/critical_err_json.php` File. Please check your Installation, Filenames, and/or File Permissions! Additionally, JSON Encoding Failed with Error: `' . $e->getMessage() . '`',
            ]);
        }
        exit;
    }
    // DEFAULT TO HTML ERROR PAGE (unless it has been modified inside the `critical_err_html.php` file)
    else {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        $htmlFilePath = __DIR__ . '/critical_err_html.php';
        // If the custom file is unreadable, fall through to the hardcoded default HTML below
        if (!is_readable($htmlFilePath)) {
            $e = new \Exception("Required HTML error file not readable at: " . $htmlFilePath);
        } else {
            try {
                echo require_once $htmlFilePath;
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
                <p><strong>Tell The Developer:</strong> Not Only Did FunkPHP Framework Catch A Critical Error, It Also Failed To Include The Necessary Custom-Made HTML Response in `/critical_err_html.php` File. Please check your Installation, Filenames, and/or File Permissions!</p>
                <p>Are You The Developer? Verify Installation Paths, Filenames, File Permissions and/or Global Configuration of the FunkPHP Framework where This Website Is Deployed!</p>
                <p class="center-text">Thanks in advance!<br>You are Always Awesome! ^_^</p>
            </div>
        </body>

        </html>
<?php
        exit;
    }
}
// Include the file inside of "FunkPHP" folder
// which is outside of public_html folder
if (
    !is_readable(__DIR__ . '/../funkphp/FunkPHP.php')
) {
    critical_err_json_or_html(500, "Tell The Developer - FunkPHP Framework Startup File `src/funkphp/FunkPHP.php` Was NOT FOUND or Is NOT READABLE. Please check your Installation, Filenames, and/or File Permissions where the Website is deployed!");
}
require_once __DIR__ . '/../funkphp/FunkPHP.php';
