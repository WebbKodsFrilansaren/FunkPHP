<?php
// DEFAULT CHECK THAT ALL NEEDED FILES EXIST OR WE THROW DEFAULT JSON ERROR
// OR DEFAULT HTML ERROR PAGE - YOU CAN CONFIGURE THIS RIGHT BELOW HERE!
function critical_err_json_or_html($status = 500)
{
    // Return JSON or HTML Error Response based on 'Accept' header
    if (
        isset($_SERVER['HTTP_ACCEPT'])
        && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            // - Default JSON Error Response - change as you wish!
            'status' => $status,
            'error' => 'FunkPHP Framework - Internal Error: Important files could not be loaded, so Please Tell the Developer to fix the website or the Web Hosting Service to allow for reading the necessary folders & files! If you are the Developer, please check your Configuration and File permissions where you Develop and/or Host this Website!Thanks in advance! You are Awesome, anyway! ^_^',
        ]);
        exit;
    } else {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        // - Default HTML Error Response - change as you wish!
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
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                }

                .container {
                    max-width: 350px;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 5px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                h1 {
                    color: #e74c3c;
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
                <p>Important files could not be loaded, so Please Tell the Developer to fix the website or the Web Hosting Service to allow for reading the necessary folders & files!</p>
                <p>If you are the Developer, please check your Configuration and File permissions where you Develop and/or Host this Website!</p>
                <p class="center-text">Thanks in advance!<br>You are Awesome, anyway! ^_^</p>
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
    !is_readable(__DIR__ . '/../funkphp/funkphp_start.php')
) {
    critical_err_json_or_html(500);
}
require_once __DIR__ . '/../funkphp/funkphp_start.php';
