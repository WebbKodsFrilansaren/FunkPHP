<?php // ROUTE-related FUNCTIONS FOR FunPHP

// Redirect to HTTPS if the application is online (not localhost)
function r_https_redirect()
{
    try {
        if ($_SERVER['SERVER_NAME'] !== "localhost" ||  $_SERVER['SERVER_NAME'] !== "127.0.0.1") {
            // Only redirect if the connection is not secure
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
                global $fphp_BASEURL_ONLINE;
                header("Location: $fphp_BASEURL_ONLINE" . $_SERVER['REQUEST_URI'], true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        // Handle any exceptions that may occur
        error_log("Error in r_https_redirect: " . $e->getMessage());
    }
}

// Try match against denied IPs globally
function r_match_denied_global_ips($ip, $denied_ips)
{
    // First check $ip is a valid IP address
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return true; // Invalid IP address, so deny access
    }

    if (!isset($denied_ips['denied'])) {
        return false; // No denied IPs configured
    }

    $deniedConfig = $denied_ips['denied'];

    //Check if the IP address starts with any of the denied IPs
    if (isset($deniedConfig['ip_starts_with']) && is_array($deniedConfig['ip_starts_with'])) {
        if (array_any_element($deniedConfig['ip_starts_with'], 'str_starts_with', $ip, ["swap_args"])) {
            echo " | IP STARTS WITH FOUND! ";
            return true;
        }
    }

    // Check if the IP address ends with any of the denied IPs
    if (isset($deniedConfig['ip_ends_with']) && is_array($deniedConfig['ip_ends_with'])) {
        if (array_any_element($deniedConfig['ip_ends_with'], 'str_ends_with', $ip, ["swap_args"])) {
            echo " | IP ENDS WITH FOUND! ";
            return true;
        }
    }

    // Check if the IP address is an exact match with any of the denied IPs
    if (isset($deniedConfig['exact_ips']) && is_array($deniedConfig['exact_ips'])) {
        if (array_any_element($deniedConfig['exact_ips'], 'str_equals', $ip, ["swap_args"])) {
            echo " | IP EXACT MATCH FOUND! ";
            return true;
        }
    }

    return false; // IP address did not match any denied criteria

}

// Prepare $req['uri'] for consistent use in the app
function r_prepare_uri($uri, $fphp_BASEURL_URI)
{
    $uri = str_starts_with($_SERVER['REQUEST_URI'], $fphp_BASEURL_URI) ? "/" . ltrim(substr(strtok($_SERVER['REQUEST_URI'], "?"), strlen($fphp_BASEURL_URI)), '/') : strtok($_SERVER['REQUEST_URI'], "?");

    if ($uri === "") {
        $uri = "/";
    }

    $uri = str_replace(["./", "../"], '', $uri);

    $uri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');

    if ((substr($uri, -1) == "/") && substr_count($uri, "/") > 1) {
        $uri = substr($uri, 0, -1);
    }
    return $uri;
}
