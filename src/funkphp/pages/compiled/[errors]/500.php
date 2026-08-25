<?php
// IMPORTANT: Default 403 Page File provided by FUNKPHP. It has a hash value it is compared against to know if you changed it!
// You can change this Page as it is used when even the Error Handling fails for some reason. It is the last resort page!
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
            max-width: 768px;
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
        <div class="developer-note" style="text-align: left;">
            If you are the developer, this error might indicate a server misconfiguration or an issue with the application code. Please check the server logs for more details.<br /><br /> If this is running in FUNKPHP_IS_LOCAL set to True you should see a var_dump right now!<br /><br />
            <?php
            $localIps = ['127.0.0.1', '::1', '192.168.122.1'];  // Add Your Own Local IP Addresses Here In Order To See Debug Dumps When Running Locally
            $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', $localIps, true) || str_ends_with($_SERVER['HTTP_HOST'] ?? '', '.local');
            if ($isLocal && isset($c['req'])): ?>

                <?php if (!function_exists('dd')): ?>
                    <strong>Local Environment Debug Dump:</strong>
                    <pre style="text-align: left; background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 0.75rem; color: #334155;"><?php var_dump($c['req']); ?></pre>
                <?php else: ?>
                    <?php dd($c['req'], 'LOCAL ENVIRONMENT DEBUG DUMP', false); ?>
                <?php endif ?>

            <?php else: ?>
                Production Environment active. Internal request signatures are hidden.
            <?php endif; ?>
        </div>
    </div>
</body>

</html>