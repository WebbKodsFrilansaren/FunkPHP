<?php
// IMPORTANT: Default 403 Page File provided by FUNKPHP. It has a hash value it is compared against to know if you changed it!
// You can change this Page as it is used when even the Error Handling fails for some reason. It is the last resort page!
// The variable $custom_error_message is automatically available here.
// Ensure $custom_error_message is a string, defaulting to a generic message if not set.
$display_message = $custom_error_message ?? 'The Requested Route OR Content is Forbidden for access.';

// Basic HTML structure for the error page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden Access | Have You Configured `->setNoRouteMatch` Yet?</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #181825;
            color: #cdd6f4;
            font-family: system-ui, -apple-system, sans-serif;
            display: grid;
            place-items: center;
            min-height: 100vh;
        }

        .container {
            text-align: center;
            padding: 2rem;
        }

        h1 {
            font-size: 5rem;
            font-weight: 800;
            color: rgb(162, 74, 255);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        p {
            font-size: 1.25rem;
            color: #a6adc8;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>403</h1>
        <?php echo htmlspecialchars($display_message); ?>
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
                Production Environment active. Internal Request Signatures are hidden.
            <?php endif; ?>
        </div>
    </div>
</body>

</html>