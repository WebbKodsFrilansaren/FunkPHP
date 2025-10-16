<?php
// NOTE: This file is included by the error handler.
// The variable $custom_error_message is automatically available here.
// Ensure $custom_error_message is a string, defaulting to a generic message if not set.
$display_message = $custom_error_message ?? 'The server encountered an internal error and was unable to complete your request.';

// Basic HTML structure for the error page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            background-color: #f7f7f9;
        }

        .container-500 {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .code {
            font-size: 6rem;
            font-weight: 800;
            color: #ef4444;
            /* Red 500 */
            margin-bottom: 0.5rem;
        }

        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            /* Gray 800 */
            margin-bottom: 1rem;
        }

        .message {
            font-size: 1rem;
            color: #4b5563;
            /* Gray 600 */
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .developer-note {
            font-size: 0.875rem;
            color: #9ca3af;
            /* Gray 400 */
            margin-top: 2rem;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="container-500">
        <div class="code">500</div>
        <div class="title">500 - Internal Server Error</div>
        <p class="message">
            <?php echo htmlspecialchars($display_message); ?>
        </p>
        <div class="developer-note">
            If you are the developer, this error might indicate a server misconfiguration or an issue with the application code. Please check the server logs for more details.
        </div>
    </div>
</body>

</html>