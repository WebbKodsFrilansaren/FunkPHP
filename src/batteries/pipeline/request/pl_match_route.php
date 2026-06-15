<?php

namespace funkphp\pipeline\request\pl_match_route;

function pl_match_route(&$c, $passedValue = null)
{
    // `pl_match_route` - Match the Request URI & Method to a Route configured
    $c['ROUTES'] = [];
    if (!is_readable(ROOT_CORE . '/pipeline_routes.php')) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/core/pipeline_routes.php` not found or is not readable!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    } elseif (!is_readable(ROOT_CORE . '/compiled_routes.php')) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/core/compiled_routes.php` not found or is not readable!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    } else {
        $c['ROUTES'] = [
            'COMPILED' => include_once ROOT_CORE . '/compiled_routes.php',
            'DEVELOPER' => include_once ROOT_CORE . '/pipeline_routes.php',
        ];
    }
    if (
        !isset($c['ROUTES'])
        || !is_array($c['ROUTES'])
        || empty($c['ROUTES'])
        || !isset($c['ROUTES']['COMPILED'])
        || !is_array($c['ROUTES']['COMPILED'])
        || empty($c['ROUTES']['COMPILED'])
    ) {
        $err = 'Tell The Developer: The Compiled Routes in File `funkphp/core/compiled_routes.php` seems empty, please check!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    if (
        !isset($c['ROUTES']['DEVELOPER'])
        || !is_array($c['ROUTES']['DEVELOPER'])
        || empty($c['ROUTES']['DEVELOPER'])
        || !isset($c['ROUTES']['DEVELOPER']['ROUTES'])
        || !is_array($c['ROUTES']['DEVELOPER']['ROUTES'])
        || empty($c['ROUTES']['DEVELOPER']['ROUTES'])
    ) {
        $err = 'Tell The Developer: The Developer Routes in File `funkphp/core/pipeline_routes.php` seems empty, please check!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
    // Try match route and if it fails, we check if we should
    $FPHP_MATCHED_ROUTE = funk_match_developer_route(
        $c,
        $c['req']['method'],
        $c['req']['uri'],
        $c['ROUTES']['COMPILED'] ?? [],
        $c['ROUTES']['DEVELOPER']['ROUTES'] ?? [],
    );
    // Return JSON/Page
    if (!$FPHP_MATCHED_ROUTE) {
        http_response_code(404); // This can be changed throughout the function here below if needed
        // Check if 'accept' is json or html/page (only use callback if it is NOT json or html/page)
        $accept = $c['req']['accept'] ?? null;
        // Accept is JSON and it is configured
        if (str_contains($accept, 'json')) {
            header('Content-Type: application/json; charset=utf-8');
            $jsonData = ["status" => "404", "body" => "PAGE OR CONTENT NOT FOUND?! The requested Route was NOT FOUND on this Server."];
            try { // Assume it is valid JSON data if not a function
                echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                exit(); // Exit if json doesn't do it and let post-request run unless disabled before this pipeline function ran
            } catch (\JsonException $e) {
                $err = 'No Route Matched (JSON Encoding Error Thrown) - Tell The Developer: The Pipepline `pl_match_route` Function needs a default Configured JSON Response OR Page to return OR a Callback Function to run in the case of No Matched Route. For example: `11 => ["pl_match_route" => ["no_match" => ["json" => "null", "page" => "404", "callback" => "null"]]]`. If the `json` key is a string, it will look for a function called that and use its return value as the JSON Encoded. If the `json` key is an array, it will be JSON Encoded as is. The `page` key must be a valid path or the default internal 404 Page will be used if not found. ONLY use the `callback` key if you need more things to do before returning any kind of response. Its string value is the function it will look for and execute. After any of these keys are ran exit() will be ran and `post-request` will run unless disabled before this pipeline function ran.';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
        }
        // Accept is HTML and it is configured
        else if (str_contains($accept, 'text/html')) {
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            $page = ROOT_PAGES_COMPILED . '/[errors]/404.php';
            if (!is_readable($page)) {
                $err = 'No Route Matched (configured "no_match => page => ..." Page NOT FOUND or NOT READABLE - if you wanna Use the Default Error Pages you must specify "/[errors]/{HttpErrorResponseCode}" - for example: `["page" => "/[errors]/404"]`) - Tell The Developer: The Pipepline `pl_match_route` Function needs a default Configured JSON Response OR Page to return OR a Callback Function to run in the case of No Matched Route. For example: `11 => ["pl_match_route" => ["no_match" => ["json" => "null", "page" => "404", "callback" => "null"]]]`. If the `json` key is a string, it will look for a function called that and use its return value as the JSON Encoded. If the `json` key is an array, it will be JSON Encoded as is. The `page` key must be a valid path or the default internal 404 Page will be used if not found. ONLY use the `callback` key if you need more things to do before returning any kind of response. Its string value is the function it will look for and execute. After any of these keys are ran exit() will be ran and `post-request` will run unless disabled before this pipeline function ran.';
                funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
            }
            include_once $page;
            exit(); // Exit if page doesn't do it and let post-request run unless disabled before this pipeline function ran

        } // <Add more "else ifs" if you wanna support more Content-Types before the catch-all-callback below>
        // Expected at least callback out of the 3 keys
        else {
            $err = 'No Route Matched (no Matching Configured Key based on Accept Request Header provided in the `no_match` key. This is because only two keys are configured allowing for this special case!) - Tell The Developer: The Pipepline `pl_match_route` Function needs a default Configured JSON Response OR Page to return OR a Callback Function to run in the case of No Matched Route. For example: `11 => ["pl_match_route" => ["no_match" => ["json" => "null", "page" => "404", "callback" => "null"]]]`. If the `json` key is a string, it will look for a function called that and use its return value as the JSON Encoded. If the `json` key is an array, it will be JSON Encoded as is. The `page` key must be a valid path or the default internal 404 Page will be used if not found. ONLY use the `callback` key if you need more things to do before returning any kind of response. Its string value is the function it will look for and execute. After any of these keys are ran exit() will be ran and `post-request` will run unless disabled before this pipeline function ran.';
            funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
    // When matched, data is stored in $c['req'] and it is up to the Developer to do whatever they want with it!
    // Recommended is to first use `pl_run_matched_route_middlewares` to run any matched middlewares and then
    // use the `pl_run_matched_route_keys` to run the matched Route Keys that has been stored after the match!
};
