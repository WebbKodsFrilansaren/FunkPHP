<?php // src/public_html/critical_err_html.php - When Critical Error Happens with NOT JSON header
// It defaults to ALWAYS show HTML Error Page then (unless You modify this file to do something else)
// and thus change the header("Content-Type: <something-else>") right here below.
// Write your custom HTML/Whatever Response here OR You can use the $status and $customMessage variables
// passed by critical_err_json_or_html() from src/public_html/index.php File to use the error
// that was passed by You or the FunkPHP Framework itself somewhere along the way!
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
        <p>Important files could not be loaded, so Please Tell the Developer to fix the website or the Web Hosting Service to Allow Reading the Necessary Folders &amp; Files!</p>
        <p><strong>Message to Developer for Debugging:</strong> `<?= $customMessage ?? "<No Custom Message Included OR `\$customMessage` Variable is NOT Available for some reason?!>" ?>`</p>
        <p>The Developer? Please check your Configuration and File permissions where you Develop and/or Host this Website!</p>
        <p class="center-text">Thanks in advance!<br>You are Awesome, anyway! ^_^</p>
    </div>
</body>

</html>