<?php

/**
 * --------------------------------------
 * FUNKPHP CONSTANTS & INTERNAL FUNCTIONS
 * --------------------------------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
// Singleton Object Constant that indicates "no value"!
define('FUNKPHP_NO_VALUE', new stdClass());

// Constants only relevant for Localhost, so do not include this in Build/Deploy MegaFile!
define('NAMESPACE_PAGES', 'funkphp\\pages\\');
define('NAMESPACE_PIPELINE_REQUEST', 'funkphp\\pipes\\request\\');
define('NAMESPACE_PIPELINE_POST_RESPONSE', 'funkphp\\pipes\\post_response\\');
define('NAMESPACE_PIPELINE_MIDDLEWARES', 'funkphp\\pipes\\middlewares\\');
define('NAMESPACE_PIPELINE_ROUTES', 'funkphp\\pipes\\routes\\');
define('NAMESPACE_DATA_QUERY', 'funkphp\\data\\sql\\');
define('NAMESPACE_DATA_SQL', 'funkphp\\data\\sql\\');
define('NAMESPACE_DATA_VALIDATION', 'funkphp\\data\\validation\\');

// Constants for Localhost vs Online Usage

define('ROOT_FOLDER', dirname(__DIR__, 1)); // src/funkphp/
define('ROOT_PUBLIC_HTML', dirname(__DIR__, 2) . '/public_html'); // src/public_html
define('ROOT_APP', ROOT_FOLDER . '/app'); // src/funkphp/app
define('ROOT_CORE_APP', ROOT_FOLDER . '/core/app.php'); // src/funkphp/config/app.php
define('ROOT_APP_CONFIG', ROOT_FOLDER . '/app/CONFIG.php'); // src/funkphp/app/CONFIG.php
define('ROOT_APP_GET', ROOT_FOLDER . '/app/GET.php'); // src/funkphp/app/GET.php
define('ROOT_APP_POST', ROOT_FOLDER . '/app/POST.php'); // src/funkphp/app/POST.php
define('ROOT_APP_PUT', ROOT_FOLDER . '/app/PUT.php'); // src/funkphp/app/PUT.php
define('ROOT_APP_PATCH', ROOT_FOLDER . '/app/PATCH.php'); // src/funkphp/app/PATCH.php
define('ROOT_APP_DELETE', ROOT_FOLDER . '/app/DELETE.php'); // src/funkphp/app/DELETE.php
define('ROOT_APP_VENDOR', ROOT_FOLDER . '/vendor/autoload.php');
define('ROOT_CORE', ROOT_FOLDER . '/core'); // src/funkphp/core
define('ROOT_CONFIG', ROOT_FOLDER . '/config'); // src/funkphp/config
define('ROOT_MIDDLEWARES', ROOT_FOLDER . '/pipeline/middlewares'); // src/funkphp/FunkPHP
define('ROOT_PAGES', ROOT_FOLDER . '/pages'); // src/funkphp/pages
define('ROOT_PAGES_COMPILED', ROOT_FOLDER . '/pages/compiled'); // src/funkphp/pages/compiled
define('ROOT_PAGES_ERRORS', ROOT_FOLDER . '/pages/compiled/[errors]'); // src/funkphp/pages/compiled/[errors]
define('ROOT_PIPELINE', ROOT_FOLDER . '/pipes'); // src/funkphp/pipes
define('ROOT_PIPELINE_REQUEST', ROOT_FOLDER . '/pipes/request'); // src/funkphp/pipes/request
define('ROOT_PIPELINE_POST_RESPONSE', ROOT_FOLDER . '/pipes/post_response'); // src/funkphp/pipes/post-response
define('ROOT_ROUTES', ROOT_FOLDER . '/pipes/routes'); // src/funkphp/pipes/routes
define('ROOT_QUERY', ROOT_FOLDER . '/data/query'); // src/funkphp/data/query
define('ROOT_SQL', ROOT_FOLDER . '/data/sql'); // src/funkphp/data/sql
define('ROOT_VALIDATION', ROOT_FOLDER . '/data/validation'); // src/funkphp/data/validation

/***  HELPER-RELATED FUNCTIONS FOR FunkPHP ***/
/**
 * Enhanced Web & CLI dumper for FunkPHP.
 * Features built-in circular reference / recursion detection and max depth protection.
 */
function dd(mixed $data, string $headerOptionalMsg = '', bool $exit = true, bool $ignoreC = true, bool $colorizeAccentGravedText = true): void
{
    if (php_sapi_name() === 'cli' && function_exists('cli_dump')) {
        cli_dd($data, $exit);
        return;
    }
    global $c;
    $metrics = [
        'nulls'          => 0,
        'strings'        => 0,
        'strings-empty'  => 0,
        'booleans'       => 0,
        'booleans-true'  => 0,
        'booleans-false' => 0,
        'integers'       => 0,
        'floats'         => 0,
        'arrays'         => 0,
        'arrays-empty'   => 0,
        'arrays-lists'   => 0,
        'arrays-assocs'  => 0,
        'objects'        => 0,
        'others'         => 0,
    ];
    $render = function ($data, $key = null, $isList = false, array $seenObjects = [], int $depth = 0) use (&$render, &$metrics, $colorizeAccentGravedText): string {
        if ($depth > 25) {
            return "<div class=\"fd-row\"><span class=\"fd-null\">*MAX DEPTH EXCEEDED:{$depth}*</span></div>";
        }
        $prefix = '';
        if ($key !== null) {
            $safeKey = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
            $prefix = $isList
                ? "<span class=\"fd-idx\">[{$safeKey}]</span> "
                : "<span class=\"fd-key\">'{$safeKey}'</span> <span class=\"fd-type\">=&gt;</span> ";
        }
        $openAttr = ($depth <= 2) ? ' open' : '';
        if (is_array($data)) {
            $metrics['arrays']++;
            $count = count($data);
            $isListArr = array_is_list($data);
            $typeLabel = $isListArr ? '[List]' : '[Assoc]';
            if ($isListArr) $metrics['arrays-lists']++;
            else $metrics['arrays-assocs']++;
            if ($count === 0) {
                $metrics['arrays-empty']++;
                return "<div class=\"fd-row\">{$prefix}<span class=\"fd-type\">{$typeLabel}(0) []</span></div>";
            }
            $html = "<details class=\"fd-details\"{$openAttr}>";
            $html .= "<summary class=\"fd-summary\">{$prefix}<span class=\"fd-type\">{$typeLabel}({$count}) [</span></summary>";
            $html .= "<div class=\"fd-tree-body\">";
            foreach ($data as $k => $v) {
                $html .= $render($v, $k, $isListArr, $seenObjects, $depth + 1);
            }
            $html .= "</div>";
            $html .= "<div class=\"fd-close-bracket\"><span class=\"fd-type\">]</span></div>";
            $html .= "</details>";
            return $html;
        } elseif (is_object($data)) {
            $metrics['objects']++;
            $className = get_class($data);
            $objHash = spl_object_hash($data);
            if (isset($seenObjects[$objHash])) {
                return "<div class=\"fd-row\">{$prefix}<span class=\"fd-type\">{{$className}}</span> <span class=\"fd-null\">*RECURSION* (AT DEPTH:{$depth})</span></div>";
            }
            $seenObjects[$objHash] = true;
            $properties = (array)$data;
            $count = count($properties);
            if ($count === 0) {
                return "<div class=\"fd-row\">{$prefix}<span class=\"fd-type\">{{$className}} {}</span></div>";
            }
            $html = "<details class=\"fd-details\"{$openAttr}>";
            $html .= "<summary class=\"fd-summary\">{$prefix}<span class=\"fd-type\">{{$className}} ({$count}) {</span></summary>";
            $html .= "<div class=\"fd-tree-body\">";
            foreach ($properties as $k => $v) {
                $k = str_replace("\0*\0", '(protected) ', $k);
                $k = preg_replace('/^\0[^\0]+\0/', '(private) ', $k);
                $html .= $render($v, $k, false, $seenObjects, $depth + 1);
            }
            $html .= "</div>";
            $html .= "<div class=\"fd-close-bracket\"><span class=\"fd-type\">}</span></div>";
            $html .= "</details>";
            return $html;
        } elseif (is_string($data)) {
            $metrics['strings']++;
            $len = strlen($data);
            if ($len === 0) $metrics['strings-empty']++;
            $safeStr = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($colorizeAccentGravedText && str_contains($safeStr, '`')) {
                $safeStr = preg_replace('/`([^`]+)`/', '<span class="fd-gravel">$1</span>', $safeStr);
            }
            return "<div class=\"fd-row\">{$prefix}<span class=\"fd-str\">\"{$safeStr}\"</span> <span class=\"fd-meta\">(str:{$len})</span></div>";
        } elseif (is_int($data)) {
            $metrics['integers']++;
            return "<div class=\"fd-row\">{$prefix}<span class=\"fd-num\">{$data}</span> <span class=\"fd-meta\">(int)</span></div>";
        } elseif (is_float($data)) {
            $metrics['floats']++;
            return "<div class=\"fd-row\">{$prefix}<span class=\"fd-num\">{$data}</span> <span class=\"fd-meta\">(flt)</span></div>";
        } elseif (is_bool($data)) {
            $metrics['booleans']++;
            if ($data) $metrics['booleans-true']++;
            else $metrics['booleans-false']++;
            $boolStr = $data ? 'true' : 'false';
            return "<div class=\"fd-row\">{$prefix}<span class=\"fd-bool\">{$boolStr}</span> <span class=\"fd-meta\">(bool)</span></div>";
        } elseif (is_null($data)) {
            $metrics['nulls']++;
            return "<div class=\"fd-row\">{$prefix}<span class=\"fd-null\">null</span></div>";
        } else {
            $metrics['others']++;
            $type = gettype($data);
            return "<div class=\"fd-row\">{$prefix}<span class=\"fd-null\">[Type: {$type}]</span></div>";
        }
    };
    $treeHtml = $render($data);
    if (!$ignoreC && $c) {
        $treeHtmlC = $render($c);
    }
?>
    <div class="funk-web-dump">
        <style>
            .funk-web-dump {
                background: #181825;
                color: #cdd6f4;
                font-family: 'Fira Code', 'Cascadia Code', Consolas, Monaco, monospace;
                font-size: 13px;
                line-height: 1.5;
                padding: 16px;
                margin: 12px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
                border: 1px solid #313244;
            }

            .funk-web-dump header {
                color: #cba6f7;
                font-weight: bold;
                font-size: 15px;
                margin-bottom: 8px;
                border-bottom: 1px solid #45475a;
                padding-bottom: 6px;
            }

            .funk-web-dump h1 {
                color: #cba6f7;
                font-weight: bold;
                font-size: 14px;
                margin-bottom: 4px;
                padding-bottom: 3px;
            }

            .funk-web-dump details.fd-details {
                margin: 2px 0;
            }

            .funk-web-dump summary.fd-summary {
                cursor: pointer;
                user-select: none;
                list-style: none;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .funk-web-dump summary.fd-summary::-webkit-details-marker {
                display: none;
            }

            .funk-web-dump summary.fd-summary::before {
                content: '▶';
                display: inline-block;
                width: 12px;
                font-size: 9px;
                color: #a6adc8;
                transition: transform 0.15s ease;
            }

            .funk-web-dump details[open]>summary.fd-summary::before {
                transform: rotate(90deg);
                color: #f5e0dc;
            }

            .funk-web-dump .fd-tree-body {
                padding-left: 18px;
                border-left: 1px dashed #45475a;
                margin-left: 5px;
                margin-top: 2px;
                margin-bottom: 2px;
            }

            .funk-web-dump .fd-close-bracket {
                padding-left: 18px;
                margin-left: 5px;
            }

            .funk-web-dump .fd-row {
                margin: 2px 0;
                padding-left: 17px;
            }

            .funk-web-dump .fd-key {
                color: #89dceb;
                font-weight: bold;
            }

            .funk-web-dump .fd-idx {
                color: #74c7ec;
            }

            .funk-web-dump .fd-type {
                color: #6c7086;
            }

            .funk-web-dump .fd-str {
                color: #a6e3a1;
            }

            .funk-web-dump .fd-num {
                color: #89b4fa;
            }

            .funk-web-dump .fd-bool {
                color: #f9e2af;
                font-weight: bold;
            }

            .funk-web-dump .fd-null {
                color: #f38ba8;
                font-weight: bold;
            }

            .funk-web-dump .fd-meta {
                color: #585b70;
                font-size: 11px;
            }

            .funk-web-dump footer {
                margin-top: 14px;
                padding-top: 8px;
                border-top: 1px solid #45475a;
                font-size: 11px;
                color: #a6adc8;
            }

            .funk-web-dump div.metrics-top {
                margin-top: 8px;
                padding-top: 4px;
                margin-bottom: 8px;
                font-size: 11px;
                color: #a6adc8;
            }

            .funk-web-dump .fd-val {
                color: #a6e3a1;
                font-weight: bold;
            }

            .funk-web-dump .fd-gravel {
                color: #f3aff9;
                font-weight: bold;
            }
        </style>
        <header><?= (strlen(trim($headerOptionalMsg)) > 0 ? "$headerOptionalMsg" : '[FunkDump]') ?></header>
        <div class="metrics-top" style="font-size:11px; border-bottom: 1px solid #45475a; padding-bottom:8px;">
            <strong>COUNTS:</strong>
            Objects: <span class="fd-val"><?= $metrics['objects'] ?></span> |
            Arrays: <span class="fd-val"><?= $metrics['arrays'] ?></span>
            <span class="fd-meta">(Empty: <?= $metrics['arrays-empty'] ?> | Lists: <?= $metrics['arrays-lists'] ?> | Assocs: <?= $metrics['arrays-assocs'] ?>)</span> |
            Strings: <span class="fd-val"><?= $metrics['strings'] ?></span> |
            Numbers: <span class="fd-val"><?= $metrics['integers'] + $metrics['floats'] ?></span> |
            Booleans: <span class="fd-val"><?= $metrics['booleans'] ?></span> |
            Nulls: <span class="fd-val"><?= $metrics['nulls'] ?></span>
        </div>
        <?php if (!$ignoreC && $c): ?>
            <h1>[FunkPHP $c Variable]</h1>
            <div class="fd-content" style="margin-top:0.5rem;">
                <?= $treeHtmlC ?? '' ?>
            </div>
        <?php endif ?>
        <?php if (!$ignoreC): ?>
            <h1>[FunkDump]</h1>
        <?php endif ?>
        <div class="fd-content" style="margin-top:0.5rem;">
            <?= $treeHtml ?>
        </div>
        <footer>
            <strong>COUNTS:</strong>
            Objects: <span class="fd-val"><?= $metrics['objects'] ?></span> |
            Arrays: <span class="fd-val"><?= $metrics['arrays'] ?></span>
            <span class="fd-meta">(Empty: <?= $metrics['arrays-empty'] ?> | Lists: <?= $metrics['arrays-lists'] ?> | Assocs: <?= $metrics['arrays-assocs'] ?>)</span> |
            Strings: <span class="fd-val"><?= $metrics['strings'] ?></span> |
            Numbers: <span class="fd-val"><?= $metrics['integers'] + $metrics['floats'] ?></span> |
            Booleans: <span class="fd-val"><?= $metrics['booleans'] ?></span> |
            Nulls: <span class="fd-val"><?= $metrics['nulls'] ?></span>
        </footer>
    </div>
<?php
    if ($exit) {
        exit(1);
    }
}

function funk_return_download($filePath, $fileName = null, $statusCode = 200)
{
    // Set the content type to application/octet-stream and the status code, then return the file response
    header('Content-Type: application/octet-stream', true, $statusCode);
    header('Content-Disposition: attachment; filename="' . ($fileName ?? basename($filePath)) . '"');
    readfile($filePath);
    exit;
}

// FUNKPHP SESSION-BASED FUNCTIONS
// The unified way to read session values across FunkPHP
function funk_session_get(&$c, string $key, $default = null)
{
    \funk_internal_session_started_or_start_it($c);
    return $_SESSION[$key] ?? $default;
}
// The unified way to write session values across FunkPHP
function funk_session_set(&$c, string $key, $value): void
{
    \funk_internal_session_started_or_start_it($c);
    $_SESSION[$key] = $value;
}

// Function to destroy the session and optionally set other cookies using funk_session_cookie_set as an array
function funk_session_destroy(&$c, $set_other_cookies_with_h_setcookie_as_array = [], $redirect = null)
{
    // If session is active, destroy it
    if (session_id() || session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
        \funk_session_cookie_set($c, session_name(), '', time() - 3600);
        \funk_session_cookie_set($c, "csrf", '', time() - 3600);

        // Optional funk_session_cookie_set to set other cookies
        if (!empty($set_other_cookies_with_h_setcookie_as_array)) {
            foreach ($set_other_cookies_with_h_setcookie_as_array as $cookie) {
                \funk_session_cookie_set(...$cookie);
            }
        }
    }
    // Redirect to the specified URI if provided
    if ($redirect) {
        header("Location: $redirect");
        exit;
    }
}

// Function to set a cookie with the specified parameters
function funk_session_cookie_set(&$c, $name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true, $samesite = 'strict')
{
    // Set the cookie with the specified parameters
    setcookie($name, $value, [
        'expires' => $expire,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}
function funk_generate_csrf(&$c, string $currentUri, ?int $lifetimeSeconds = null): string
{
    if (\funk_session_get($c, '_funk_csrf') === null) {
        $_SESSION['_funk_csrf'] = [];
    }
    // 1. Generate a completely unique, unpredictable token string
    $token = hash('sha256', random_bytes(32));
    // 2. Store the URI and expiration metadata INSIDE the array payload
    $_SESSION['_funk_csrf'][$token] = [
        'uri' => $currentUri,
        'expires' => ($lifetimeSeconds === null) ? null : (time() + $lifetimeSeconds)
    ];
    // Optional: Keep the session array from growing forever if a user opens 100 tabs
    if (count($_SESSION['_funk_csrf']) > 99) {
        array_shift($_SESSION['_funk_csrf']); // Drop the oldest token
    }
    return $token;
}

/***  ROUTE-RELATED PHP FUNCTIONS FOR FUNKPHP ***/

/**
 * CUSTOM ERROR HANDLER: Outputs a raw HTML string directly to the client.
 *
 * This is used for simple, non-templated HTML error responses.
 *
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw HTML string to be echoed as the response body.
 * @return void              Sends the HTML response and terminates execution via `exit()`.
 */
function funk_use_error_raw_html(&$c, int $errCode, string $errMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($erCode)
        || !is_int($erCode)
        || $erCode < 100
        || $erCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_html_string()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_html_string()` Function. This should be a non-empty string!');
    }
    // Set the response code & header for HTML and output the message
    http_response_code($errCode);
    header('Content-Type: text/html; charset=utf-8');
    echo $errMsg;
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Outputs a raw plain text string directly to the client.
 *
 * This is typically used for simple API errors or basic, non-formatted text responses.
 *
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw plain text string to be echoed as the response body.
 * @return void              Sends the plain text response and terminates execution via `exit()`.
 */
function funk_use_error_raw_plain(&$c, int $errCode, string $errMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_plain_text()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_plain_text()` Function. This should be a non-empty string!');
    }
    // Set response code & header for plain text and output the message
    http_response_code($errCode);
    header('Content-Type: text/plain; charset=utf-8');
    echo $errMsg;
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Outputs a raw XML string directly to the client.
 *
 * This is used for providing error responses compatible with older SOAP/XML-based APIs.
 *
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg     The raw XML string to be echoed as the response body.
 * @return void              Sends the XML response and terminates execution via `exit()`.
 */
function funk_use_error_xml(&$c, int $errCode, string $errMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_xml()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_xml()` Function. This should be a non-empty string!');
    }
    // Set response code & header for XML and output the message
    http_response_code($errCode);
    header('Content-Type: application/xml; charset=utf-8');
    echo $errMsg;
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Displays a user-friendly error by including a specified HTML error page.
 *
 * This function clears output buffering, performs validation, sets appropriate security headers,
 * and includes the target error page file. It then terminates execution.
 *
 * NOTE ON HTML PAGE: The provided error message ($errMsg) is injected into the local scope
 * of the included error page file using the variable **$custom_error_message**.
 *
 * @param array $c           The global context array (passed by reference).
 * @param int $errCode       The HTTP status code associated with the error (100-599).
 * @param string $errMsg      The human-readable error message. This message is accessible inside
 * the included page file via the variable **$custom_error_message**.
 * @param string $pageName    The filename (without '.php' extension) of the custom error page
 * located in the 'ROOT_FOLDER/page/complete/[errors]/' directory. Must be a readable file.
 * @return void              Sends the HTML response and terminates execution via `exit()`.
 */
function funk_use_error_page(&$c, int $errCode, string $errMsg, string $pageName)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_page()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_page()` Function. This should be a non-empty string!');
    }
    // When $pageName is not a string or empty or file not readable
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_handle_error_page()` Function. This should be a non-empty string that is also a readable file inside `/pages/compiled/[errors]/` directory!');
    }
    // Headers that also support <styles> tag inline
    header('Content-Type: text/html; charset=utf-8');
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
    try {
        $custom_error_message = $errMsg;
        include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
    } catch (\Throwable $e) {
        \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_page()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Executes a user-defined callback function to handle an error.
 *
 * This function clears the output buffer, performs validation on the error code
 * and callback, and then executes the callback, passing the global context ($c)
 * and optional custom data to it. The function exits execution after the callback
 * runs successfully or fails critically.
 *
 * IMPORTANT: Database Credentials are cleared before calling the callback function so they need to be set again if needed!
 *
 * @param array $c                     The global context array (passed by reference). 1st Argument passed to the Callback.
 * @param int $errCode                 The HTTP status code associated with the error (100-599).
 * @param string $errMsg               The Primary Error Message passed as the 2nd Argument after $c.
 * @param string $callbackName         The String name of the Callable Function or method to execute.
 * @param mixed $optionalCallbackData  Optional Data passed as the 3rd Argument to the Callback Function.
 * @return void                        Sends response and exits execution via `exit()`.
 */
function funk_use_error_callback(&$c, int $errCode, string $errMsg, string $callbackName, $optionalCallbackData = null)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_callback()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsg is not a string or empty
    if (
        !isset($errMsg)
        || !is_string($errMsg)
        || empty($errMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_callback()` Function. This should be a non-empty string!');
    }
    // When $callbackName is not a string or empty or not callable
    if (
        !isset($callbackName)
        || !is_string($callbackName)
        || empty($callbackName)
        || !is_callable($callbackName)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_handle_error_callback()` Function. This should be a non-empty string that is also callable!');
    }
    // Set response code, call function and exit
    http_response_code($errCode);
    try {
        $callbackName($c, $errMsg, $optionalCallbackData);
    } catch (\Throwable $e) {
        \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_callback()` Function with the following Error Message:`' . $e->getMessage() . '`.');
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Throws a standard PHP Exception to halt execution and be caught by a global handler.
 *
 * This function is intended for internal flow control where the error handling logic
 * is implemented higher up in the call stack (e.g., a global exception handler).
 * It does not set an HTTP status code or clear output buffering.
 *
 * @param array $c                 The global context array (passed by reference).
 * @param string $exceptionErrMsg  The message to be included in the new \Exception object.
 * @return void
 * @throws \Exception              Always throws a new \Exception with the provided message.
 */
function funk_use_error_throw(&$c, string $exceptionErrMsg)
{
    // The `funk_use_error_throw()` does not set any HTTP status code
    // OR "eating" output buffering since it just throws an exception
    // When $exceptionErrMsg is not a string or empty
    if (
        !isset($exceptionErrMsg)
        || !is_string($exceptionErrMsg)
        || empty($exceptionErrMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_throw()` Function. This should be a non-empty string!');
    }
    throw new Exception($exceptionErrMsg);
}

/**
 * CUSTOM ERROR HANDLER: Returns a JSON response. Accepts either a Direct Data Structure
 * or a Callable (string/closure) that must return the Data Structure when invoked.
 *
 * IMPORTANT: Database Credentials are cleared before calling the callable function
 * (the one optionally used for JSON Generation) so they need to be set again if needed!
 *
 * @param array $c      Global context array (by reference).
 * @param int $errCode  The HTTP status code.
 * @param mixed $jsonObjectOrCallableThatReturnsJSON The JSON data (array/object) OR a string/callable that returns JSON Data.
 * @return void         Sends response and exits.
 */
function funk_use_error_json(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_json()` Function. This should be an integer between 100 and 599!');
    }
    // When $jsonObjectOrStringThatReturnsJSON is not an Object/Array, nor a String that is also Callable
    if (
        !isset($jsonObjectOrStringThatReturnsJSON)
        || (
            !is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON)
            && (
                !is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)
            )
        )
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_handle_error_json()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // Set the response code for both JSON
    http_response_code($errCode);
    // Retrieve JSON Payload either directly or by verified callable
    $jsonData = $jsonObjectOrStringThatReturnsJSON;
    if (is_string($jsonData) && is_callable($jsonData)) {
        try {
            $jsonData = $jsonData($c);
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
        }
    }
    // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (\JsonException $e) {
        \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_json()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Determines error response based on the client's Accept header,
 * choosing between JSON (for APIs) or a dedicated HTML error page (as the universal fallback).
 *
 * Execution Logic:
 * 1. If the client accepts 'application/json' or 'text/json', a JSON response is generated.
 * 2. If JSON is NOT accepted (i.e., any other Accept header, or none at all), the specified
 * HTML error page is served as the guaranteed fallback.
 *
 * NOTE ON HTML PAGES: For the HTML error page, the custom message (passed in **$pageErrMsg**) is made
 * available to the included file via the variable **$custom_error_message**.
 *
 * IMPORTANT: Database Credentials are cleared before calling the callable function
 * (the one optionally used for JSON Generation) so they need to be set again if needed!
 *
 * @param array $c                                The global context array (passed by reference).
 * @param int $errCode                            The HTTP status code associated with the error (100-599).
 * @param mixed $jsonObjectOrStringThatReturnsJSON The source of the JSON payload. This must be an array, object,
 * or a string/callable that returns an array/object.
 * @param string $pageName                        The filename (without '.php') of the custom error page in the
 * 'ROOT_FOLDER/page/complete/[errors]/' directory. Must be a readable file.
 * @param string $pageErrMsg                      The human-readable message used exclusively for:
 * - The custom message on the HTML error page (**$custom_error_message**).
 * @return void                                   Sends the appropriate response headers/content and terminates execution via `exit()`.
 */
function funk_use_error_json_or_page(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON, string $pageName, string $pageErrMsg)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page()` Function. This should be an Integer between 100 and 599!');
    }
    // When $pageErrMsg is not a string or empty
    if (
        !isset($pageErrMsg)
        || !is_string($pageErrMsg)
        || empty($pageErrMsg)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String!');
    }
    // When $jsonObjectOrStringThatReturnsJSON is not an Object/Array, nor a String that is also Callable
    if (
        !isset($jsonObjectOrStringThatReturnsJSON)
        || (
            !is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON)
            && (
                !is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)
            )
        )
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // When $pageName is not a string or empty or the file does not exist in the expected folder
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String and it must exist as a file in the `src/funkphp/pages/compiled/[errors]` directory!');
    }
    // Set the response code for both JSON and Page
    http_response_code($errCode);
    // JSON Response
    if (
        isset($c['req']['accept'])
        && is_string($c['req']['accept'])
        && !empty($c['req']['accept'])
        && (str_contains($c['req']['accept'], 'application/json') || str_contains($c['req']['accept'], 'text/json'))
    ) {
        // Retrieve JSON Payload either directly or by verified callable
        $jsonData = $jsonObjectOrStringThatReturnsJSON;
        if (is_string($jsonData) && is_callable($jsonData)) {
            try {
                $jsonData = $jsonData($c);
            } catch (\Throwable $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
        }
    }
    // Otherwise we return a Page even if that was not explicitly requested
    else {
        // Headers that also support <styles> tag inline
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        try {
            $custom_error_message = $pageErrMsg;
            include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
        }
    }
    exit();
}

/**
 * CUSTOM ERROR HANDLER: Provides flexible error handling based on the client's Accept header.
 *
 * This function attempts to handle the error in the following order:
 * 1. **HTML:** If the client accepts 'text/html', it includes the specified error page.
 * 2. **JSON:** If the client accepts 'application/json' or 'text/json', it encodes and returns the provided JSON data/callable result.
 * 3. **CALLBACK:** If neither HTML nor JSON is accepted, it executes the specified user-defined callback function.
 *
 * IMPORTANT ABOUT CALLBACK: Database Credentials are cleared before calling the callback function so they need to be set again if needed!
 *
 * NOTE ON HTML PAGES: For HTML error pages, the custom message is made available to the included file
 * via the variable **$custom_error_message**.
 *
 * @param array $c                               The global context array (passed by reference).
 * @param int $errCode                           The HTTP status code associated with the error (100-599).
 * @param string $errMsgForPageAndCallback       The human-readable message used for:
 * - The custom message on the HTML error page ($custom_error_message).
 * - The second argument passed to the callable function.
 * @param mixed $jsonObjectOrStringThatReturnsJSON The source of the JSON payload. This must be an array, object,
 * or a string/callable that returns an array/object.
 * @param string $pageName                       The filename (without '.php') of the custom error page in the
 * 'ROOT_FOLDER/page/complete/[errors]/' directory.
 * @param string $callableName                   The string name of the callable function to execute if neither
 * HTML nor JSON is accepted.
 * @param mixed $optionalCallbackData            Optional data passed as the third argument to the callback function.
 * @return void                                  Sends response headers/content and terminates execution via `exit()`.
 */
function funk_use_error_json_or_page_or_callback(&$c, int $errCode, string $errMsgForPageAndCallback, $jsonObjectOrStringThatReturnsJSON, string $pageName, string $callableName, $optionalCallbackData = null)
{
    // Clear any previous use of output buffering - although the Framework should not really use ob_start
    // during request pipeline, only during post_response pipeline since all data there is only for server
    if (ob_get_level() > 0) {
        ob_clean();
    }
    // When error code is NOT integer or within wrong range
    if (
        !isset($errCode)
        || !is_int($errCode)
        || $errCode < 100
        || $errCode > 599
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be an integer between 100 and 599!');
    }
    // When $errMsgForPageAndCallback is not a string or empty
    if (
        !isset($errMsgForPageAndCallback)
        || !is_string($errMsgForPageAndCallback)
        || empty($errMsgForPageAndCallback)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String!');
    }
    // When $pageName is not a string or empty or the file does not exist in the expected folder
    if (
        !isset($pageName)
        || !is_string($pageName)
        || empty($pageName)
        || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String and it must exist as a file in the `src/funkphp/pages/compiled/[errors]` directory!');
    }
    // $callableName is not a string or empty or not callable
    if (
        !isset($callableName)
        || !is_string($callableName)
        || empty($callableName)
        || !is_callable($callableName)
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String that is also Callable!');
    }
    // When $jsonObjectOrStringThatReturnsJSON is not an Object/Array, nor a String that is also Callable
    if (
        !isset($jsonObjectOrStringThatReturnsJSON)
        || (
            !is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON)
            && (
                !is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)
            )
        )
    ) {
        \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
    }
    // Set response code and check if Accept header contains text/html, application/json or text/json
    // If none of those headers then we call the callback function. We always exit nonetheless!
    http_response_code($errCode);
    // HTML Response
    if (
        isset($c['req']['accept'])
        && is_string($c['req']['accept'])
        && !empty($c['req']['accept'])
        && str_contains($c['req']['accept'], 'text/html')
    ) {
        // Headers that also support <styles> tag inline
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        try {
            $custom_error_message = $errMsgForPageAndCallback;
            include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
        }
    }
    // JSON Response
    else if (
        isset($c['req']['accept'])
        && is_string($c['req']['accept'])
        && !empty($c['req']['accept'])
        && (str_contains($c['req']['accept'], 'application/json') || str_contains($c['req']['accept'], 'text/json'))
    ) {
        // Retrieve JSON Payload either directly or by verified callable
        $jsonData = $jsonObjectOrStringThatReturnsJSON;
        if (is_string($jsonData) && is_callable($jsonData)) {
            try {
                $jsonData = $jsonData($c);
            } catch (\Throwable $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        // Now $jsonData is guaranteed to be the final data structure (or null/invalid)
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
        }
    }
    // CALLBACK Response
    else {
        try {
            $callableName($c, $errMsgForPageAndCallback, $optionalCallbackData);
        } catch (\Throwable $e) {
            critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` Function with the following Error Message:`' . $e->getMessage() . '`');
        }
    }
    exit();
}

// Function stores a user-focused message that is meant to be used in the final output (HTML page or JSON output)
function funk_collect_output_message(&$c, $level, $key, $message)
{
    // All three variables must be non-empty strings!
    if (
        !isset($level)
        || !is_string($level)
        || empty($level)
        || !in_array(strtolower($level), [ // Add more below in array as needed
            'info',
            'warning',
            'error',
            'debug',
            'critical',
            'notice',
            'alert',
            'emergency',
            'success',
            'failure',
        ])
        || !isset($key)
        || !is_string($key)
        || empty($key)
        || !isset($message)
        || !is_string($message)
        || empty($message)
    ) {
        error_log('FunkPHP: Invalid Parameters Passed to funk_collect_output_message() Function. Expected 3 Non-Empty String:s: [Level, Key, Message]!');
        return;
    }
    $c['req']['user_messages'][] = [
        'level'   => strtolower($level),
        'key'     => mb_strtoupper($key),
        'message' => $message,
    ];
}

/**
 * Pushes a log message into the global configuration object.
 * This log array is typically persisted (e.g., written to disk)
 * in the framework's shutdown function.
 *
 * @param array $c The global configuration array, passed by reference.
 * @param string $logMessage The message to log.
 * @param string $logType Optional type identifier (e.g., 'CRITICAL','FATAL', 'WARN','INFO' - these are just examples, and You decide what to use!).
 * @return void
 */
function funk_use_log(&$c, string $logMessage, string $logType = 'WARN'): void
{
    // Ensure the log structure exists, otherwise create it
    // and log that it was created due to not existing
    if (!isset($c['req']['log']) || !is_array($c['req']['log'])) {
        $c['req']['log'] = [];
        funk_use_log($c, 'The Log Array Did Not Exist, so it was Created Automatically!', 'INFO');
        return;
    }
    // Add the log entry with timestamp and type
    $c['req']['log'][] = [
        'timestamp' => time(),
        'type' => strtoupper($logType),
        'message' => $logMessage
    ];
    return;
}

/**
 * Placeholder for the final function that saves the log array to a file.
 * This function should be called within the application's shutdown handler.
 *
 * @param array $c The global configuration array, passed by reference.
 * @return void
 */
function funk_save_log(&$c): void
{
    // TODO: Add support later for different ways of saving (file, db, etc.)
    // Implementation needed here to serialize and write $c['req']['log']
    // to a persistent location (e.g., a file or database).
    // For now, we will simply log to the PHP error log for visibility.
    if (!empty($c['req']['log'])) {
        error_log("--- FUNKPHP POST-RESPONSE LOGS ---");
        error_log(print_r($c['req']['log'], true));
        error_log("--- END LOGS ---");
    }
    return;
}
// Function that clears the log array
function funk_clear_log(&$c, $saveFirst = false)
{
    if ($saveFirst === true) {
        \funk_save_log($c);
    }
    if (!isset($c['req']['log']) || !is_array($c['req']['log'])) {
        $c['err']['FUNCTIONS']['funk_clear_log'][] = 'The Log Array Did Not Exist, so it was Created Automatically!';
        \funk_use_log($c, 'The Log Array Did Not Exist, so it was Created Automatically!', 'INFO');
    } else {
        $c['req']['log'] = [];
        \funk_use_log($c, 'The Log Array was Cleared Successfully!', 'INFO');
    }
    return;
}

/**
 * Check if Current Request accepts specific Mime Type using its shorthand name (e.g. `json` for `application/json`).
 *
 * The check is done inside of `$c['req']['accepts][$contentType]` using isset() returning `TRUE|FALSE`
 * @param 'html'|'json'|'jsonapi'|'jsonproblem'|'jsonhal'|'xml'|'xhtml'|'soap'|'atom'|'rss'|'text'|'markdown'|'csv'|'css'|'js'|'form'|'formdata'|'binary'|'webp'|'avif'|'png'|'jpg'|'gif'|'svg'|'mp3'|'ogg'|'mp4'|'webm'|'image'|'media'|string $contentType
 */
function funk_req_accepts(&$c, $contentType): bool
{
    if (isset($c['req']['accepts'][$contentType])) {
        return true;
    }
    return false;
}

/**
 * Check if Current Request Prefers specific Mime Type using its shorthand name (e.g. `json` for `application/json`).
 *
 * The check is done inside of `$c['req']['prefers][$contentType]` using isset() returning `TRUE|FALSE`
 * @param 'html'|'json'|'jsonapi'|'jsonproblem'|'jsonhal'|'xml'|'xhtml'|'soap'|'atom'|'rss'|'text'|'markdown'|'csv'|'css'|'js'|'form'|'formdata'|'binary'|'webp'|'avif'|'png'|'jpg'|'gif'|'svg'|'mp3'|'ogg'|'mp4'|'webm'|'image'|'media'|string $contentType
 */
function funk_req_prefers(&$c, $contentType): bool
{
    if (isset($c['req']['prefers'][$contentType])) {
        return true;
    }
    return false;
}

/**
 * Set (OVERWRITE(!)) Response Header for this Current Matched Route.
 *
 * @param 'Accept-Ranges'|'Access-Control-Allow-Credentials'|'Access-Control-Allow-Headers'|'Access-Control-Allow-Methods'|'Access-Control-Allow-Origin'|'Access-Control-Expose-Headers'|'Access-Control-Max-Age'|'Age'|'Allow'|'Alt-Svc'|'Cache-Control'|'Clear-Site-Data'|'Content-Disposition'|'Content-Encoding'|'Content-Language'|'Content-Location'|'Content-Range'|'Content-Security-Policy'|'Content-Security-Policy-Report-Only'|'Content-Type'|'Cross-Origin-Embedder-Policy'|'Cross-Origin-Opener-Policy'|'Cross-Origin-Resource-Policy'|'ETag'|'Expires'|'Last-Modified'|'Location'|'Origin-Trial'|'Permissions-Policy'|'Pragma'|'Referrer-Policy'|'Retry-After'|'Server-Timing'|'Strict-Transport-Security'|'Timing-Allow-Origin'|'Vary'|'WWW-Authenticate'|'X-Content-Type-Options'|'X-Frame-Options'|'X-RateLimit-Limit'|'X-RateLimit-Remaining'|'X-RateLimit-Reset'|'X-Request-ID'|'X-XSS-Protection'|string $headerName Header Name
 * @param string $value Header Value (e.g., "nosniff")
 */
function funk_set_header(&$c, $headerName, $value)
{
    $c['runtime']['route']['headers']['add'][strtolower(trim($headerName))] = $headerName . ': ' . $value;
}

/**
 * Remove a Header already registered (but NOT that was set via `->setHeaderAdd()` OR `funk_set_header()`!)
 *
 * @param 'Accept-Ranges'|'Access-Control-Allow-Credentials'|'Access-Control-Allow-Headers'|'Access-Control-Allow-Methods'|'Access-Control-Allow-Origin'|'Access-Control-Expose-Headers'|'Access-Control-Max-Age'|'Age'|'Allow'|'Alt-Svc'|'Cache-Control'|'Clear-Site-Data'|'Content-Disposition'|'Content-Encoding'|'Content-Language'|'Content-Location'|'Content-Range'|'Content-Security-Policy'|'Content-Security-Policy-Report-Only'|'Content-Type'|'Cross-Origin-Embedder-Policy'|'Cross-Origin-Opener-Policy'|'Cross-Origin-Resource-Policy'|'ETag'|'Expires'|'Last-Modified'|'Location'|'Origin-Trial'|'Permissions-Policy'|'Pragma'|'Referrer-Policy'|'Retry-After'|'Server-Timing'|'Strict-Transport-Security'|'Timing-Allow-Origin'|'Vary'|'WWW-Authenticate'|'X-Content-Type-Options'|'X-Frame-Options'|'X-RateLimit-Limit'|'X-RateLimit-Remaining'|'X-RateLimit-Reset'|'X-Request-ID'|'X-XSS-Protection'|string $headerName Header Name
 */
function funk_remove_header(&$c, string $headerName)
{
    if (!headers_sent()) {
        header_remove($headerName);
    }
}

/**
 * Return HTML Page Response from the `/src/funkphp/pages/` Directory.
 *
 * *IMPORTANT*: This clears the `Content-Type` Header to set HTML Content Type.
 *
 * Clears any previously registered Content-Type headers, sets `text/html`,
 * emits all pending route response headers, requires the requested page file,
 * and terminates execution (via `exit()`).
 *
 * @param array $c Main application state/context array (passed by reference).
 * @param string $pageNameWithoutExtension The filename under `/pages/` without the `.php` extension (e.g., `"home"` or `"auth/login"`).
 * @param int $code HTTP response status code (default: `200`).
 *
 * @return never
 */
function funk_return_response_page(&$c, string $pageNameWithoutExtension, int $code = 200)
{
    header_remove('content-type');
    if (isset($c['runtime']['route']['headers']['add']['content-type'])) {
        unset($c['runtime']['route']['headers']['add']['content-type']);
    }
    http_response_code($code);
    funk_set_header($c, 'content-type', 'text/html');
    funk_internal_send_response_headers($c);
    if (defined('ROOT_FOLDER')) {
        require_once ROOT_FOLDER . '/pages/' . $pageNameWithoutExtension . '.php';
    } else {
        \funk_use_error_json_or_page($c, 500, ['internal_server_error' => 'Failed to load a `User-defined Page` to Return a Response. This means the `Page` does NOT exist in the Expected Folder `/pages/`.'], '500', 'Failed to use a `User-defined Function` to Return a Response. This means the Function-name does NOT exist.');
    }
    exit;
}

/**
 * Return a JSON Response using Payload Data stored inside `$c['d']`.
 *
 * *IMPORTANT*: This clears the `Content-Type` Header to set JSON Content Type.
 *
 * Clears any previously registered Content-Type headers, sets `application/json`,
 * emits pending route headers, outputs the value found at `$c['d'][$c_data_key_with_JSON_encoded_Data]`
 * (auto-encoding to JSON if array/object), and terminates execution (via `exit()`).
 *
 * @param array $c Main application state/context array (passed by reference).
 * @param string $c_data_key_with_JSON_encoded_Data The key name inside `$c['d']` containing stringified JSON or raw data array/object.
 * @param int $code HTTP Response Status Code (default: `200`).
 *
 * @return never
 */
function funk_return_response_json(&$c, string $c_data_key_with_JSON_encoded_Data, int $code = 200)
{
    header_remove('content-type');
    if (isset($c['runtime']['route']['headers']['add']['content-type'])) {
        unset($c['runtime']['route']['headers']['add']['content-type']);
    }
    http_response_code($code);
    funk_set_header($c, 'content-type', 'application/json');
    funk_internal_send_response_headers($c);
    if (isset($c['d'][$c_data_key_with_JSON_encoded_Data])) {
        echo is_string($c['d'][$c_data_key_with_JSON_encoded_Data])
            ? $c['d'][$c_data_key_with_JSON_encoded_Data]
            : json_encode($c['d'][$c_data_key_with_JSON_encoded_Data]);
    }
    exit;
}

/**
 * Return a Raw Plain Text Response.
 *
 * *IMPORTANT*: This clears the `Content-Type` Header to set Plain Text Content Type.
 *
 * Clears any previously registered Content-Type headers, sets `text/plain`,
 * emits all pending route response headers, outputs the provided raw string,
 * and terminates execution (via `exit()`).
 *
 * @param array $c Main application state/context array (passed by reference).
 * @param string $rawTextString The Plain Text Payload to echo.
 * @param int $code HTTP Response Status Code (default: `200`).
 *
 * @return never
 */
function funk_return_response_text(&$c, string $rawTextString, int $code = 200)
{
    header_remove('content-type');
    if (isset($c['runtime']['route']['headers']['add']['content-type'])) {
        unset($c['runtime']['route']['headers']['add']['content-type']);
    }
    http_response_code($code);
    funk_set_header($c, 'content-type', 'text/plain');
    funk_internal_send_response_headers($c);
    echo $rawTextString;
    exit;
}

/**
 * Run a Custom User-Defined Function as the Returned Response
 *
 * Provides full control over the Response Process by delegating execution to a Custom
 * Function (defined in `/src/funkphp/config/functions.php`). The target Function must handle
 * its own Status Code, Response Headers (including clearing/setting Content-Type), and (if any) Payload/Output
 * before this Function explicitly terminates it (via `exit()`).
 *
 * @param array $c Main application state/context array (passed by reference).
 * @param string $userDefinedFunctionName The Name of the Custom Callable from `/src/funkphp/config/functions.php`.
 *
 * @return never
 */
function funk_return_response_callback(&$c, string $userDefinedFunctionName)
{
    if (function_exists($userDefinedFunctionName)) {
        $userDefinedFunctionName($c);
        exit;
    }
    \funk_use_error_json_or_page($c, 500, ['INTERNAL_SERVER_ERROR' => 'Failed to use a `User-defined Function` to Return a Response. This means the Function-name does NOT exist.'], '500', 'Failed to use a `User-defined Function` to Return a Response. This means the Function-name does NOT exist.');
}

function funk_req_param_valid(&$c, string $param): bool
{
    if (
        isset($c['req']['param_valid'][$param])
        && $c['req']['param_valid'][$param] === true
    ) {
        return true;
    }
    return false;
}

function funk_req_params_valid(&$c): bool
{
    if (
        isset($c['req']['params_valid'])
        && $c['req']['params_valid'] === true
    ) {
        return true;
    }
    return false;
}


/***  DATA-RELATED PHP FUNCTIONS FOR FUNKPHP ***/
// Function that either creates and returns a new database connection or returns
// an already existing one in $c['DATABASES'][<$dbKey>] if it exists
function funk_db_conn(&$c, $dbKey)
{
    // Set error and return null if no dbKey provided
    if (!isset($dbKey) || !is_string($dbKey)) {
        $c['err']['DATABASES']['funk_db_conn'][] = 'Invalid or missing $dbKey passed to funk_db_conn().';
        return null;
    }
    // First check if the connection already exists and thus just return it by reference
    if (isset($c['DATABASES'][$dbKey])) {
        return $c['DATABASES'][$dbKey];
    }
    // Can be used for all if/else statements below
    // TODO: Change $credentials source to $c['CONNECTIONS'] later!
    $credentials = null; // "null" for now!
    if ($credentials === null) {
        $c['err']['DATABASES']['funk_db_conn'][] = "No database configuration found for key '$dbKey'.";
        return null;
    }
    // 'driver' = mysqli
    if ($credentials['driver'] === 'mysqli') {
        $host = $credentials['host'] ?? 'localhost';
        $user = $credentials['user'] ?? 'root';
        $password = $credentials['password'] ?? '';
        $database = $credentials['database'] ?? '';
        $port = $credentials['port'] ?? 3306;
        $charset = $credentials['charset'] ?? 'utf8mb4';

        // Attempt creating a new mysqli connection
        try {
            $mysqli = new mysqli($host, $user, $password, $database, $port);
            // Check for connection errors
            if ($mysqli->connect_error) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . $mysqli->connect_error;
                return null;
            }

            // Set the charset
            if (!$mysqli->set_charset($charset)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Error loading character set ' . $charset . ' for ' . $dbKey . ': ' . $mysqli->error;
                // Not returning null here since connection is still valid
            }

            // Store the connection in the global array by reference
            $c['DATABASES'][$dbKey] = $mysqli;

            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = pgsql
    else if ($credentials['driver'] === 'pgsql') {
        $host = $credentials['host'] ?? 'localhost';
        $user = $credentials['user'] ?? 'postgres';
        $password = $credentials['password'] ?? '';
        $database = $credentials['database'] ?? '';
        $port = $credentials['port'] ?? 5432;
        $charset = $credentials['charset'] ?? 'utf8';
        $connString = "host=$host port=$port dbname=$database user=$user password=$password options='--client_encoding=$charset'";

        // Attempt creating a new pgsql connection
        try {
            $pgsql = pg_connect($connString);
            // Check for connection errors
            if ($pgsql === false) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . pg_last_error(null);
                return null;
            }
            // Store the connection in the global array by reference
            $c['DATABASES'][$dbKey] = $pgsql;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = mongodb
    elseif ($credentials['driver'] === 'mongodb') {
        $host = $credentials['host'] ?? 'localhost';
        $user = $credentials['user'] ?? '';
        $password = $credentials['password'] ?? '';
        $database = $credentials['database'] ?? '';
        $port = $credentials['port'] ?? 27017;
        $charset = $credentials['charset'] ?? 'utf8';
        // Build the MongoDB connection URI
        $authPart = ($user && $password) ? $user . ':' . $password . '@' : '';
        $uri = 'mongodb://' . $authPart . $host . ':' . $port;
        // Attempt creating a new MongoDB connection
        try {
            // Ensure the MongoDB extension is loaded
            if (!class_exists('MongoDB\Client')) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'MongoDB extension is not installed or enabled.';
                return null;
            }
            // Create a new MongoDB client
            $mongoClient = new \MongoDB\Client($uri);
            // Select the database
            $mongoDB = $mongoClient->selectDatabase($database);
            // Store the connection in the global array by reference
            $c['DATABASES'][$dbKey] = $mongoDB;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = redis
    elseif ($credentials['driver'] === 'redis') {
        $host = $credentials['host'] ?? '127.0.0.1';
        $port = $credentials['port'] ?? 6379;
        $password = $credentials['password'] ?? null;
        $database = $credentials['database'] ?? 0;
        // Attempt creating a new Redis connection
        try {
            // Ensure the Redis extension is loaded
            if (!class_exists('\Redis')) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis extension is not installed or enabled.';
                return null;
            }
            $redis = new \Redis();
            // 1. Connect
            if (!$redis->connect($host, $port)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis connection failed for ' . $dbKey;
                return null;
            }
            // 2. Authenticate (if password provided)
            if ($password !== null && !$redis->auth($password)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis authentication failed for ' . $dbKey;
                $redis->close(); // Important to close a failed connection
                return null;
            }
            // 3. Select Database
            if (!$redis->select($database)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Redis database selection failed for ' . $dbKey . ' (DB: ' . $database . ')';
                $redis->close();
                return null;
            }
            // Store the connection object
            $c['DATABASES'][$dbKey] = $redis;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (\Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }
    // 'driver' = memcached
    elseif ($credentials['driver'] === 'memcached') {
        $host = $credentials['host'] ?? '127.0.0.1';
        $port = $credentials['port'] ?? 11211;
        // Attempt creating a new Memcached connection
        try {
            // Ensure the Memcached extension is loaded
            // Note: Use '\Memcached' (modern) over '\Memcache' (legacy)
            if (!class_exists('\Memcached')) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached extension is not installed or enabled.';
                return null;
            }
            $memcached = new \Memcached();
            // Memcached uses addServer to connect. It returns true/false on success.
            if (!$memcached->addServer($host, $port)) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached failed to add server for ' . $dbKey . '.';
                return null;
            }
            // Optional: Check if the server is actually available (e.g., ping)
            // Note: Memcached connections are often "lazy," but we can check the status.
            $stats = $memcached->getStats();
            if (empty($stats) || !isset($stats["$host:$port"]) || $stats["$host:$port"]['pid'] === -1) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached server ' . $host . ':' . $port . ' is unavailable.';
                return null;
            }
            // Store the connection object
            $c['DATABASES'][$dbKey] = $memcached;
            return $c['DATABASES'][$dbKey];
        }
        // Or return null when failed
        catch (\Exception $ex) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
            return null;
        }
    }

    // TODO: Add more here later
    // DRIVER NOT SUPPORTED YET - this is always the last one
    else {
        $c['err']['DATABASES']['funk_db_conn'][] = 'Database driver "' . $credentials['driver'] . '" for key `' . $dbKey . '` is not supported in current version of FunkPHP.';
        return null;
    }
}

/*****************************************/
/* INTERNAL Functions For FunkPHP        */
/* These should NEVER be called directly */
/* inside any Pipe Function              */
/*****************************************/
// Start Session using defined Session Driver or default 'files'
function funk_internal_session_started_or_start_it(&$c)
{ // If already active in this request lifecycle, exit instantly (Zero overhead)
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // Lazy infrastructure allocation: Connect to Redis/DB only when a session is actually requested!
    if (($c['SESSION']['driver'] ?? 'files') === 'redis') {
        // funk_connect_redis_infrastructure($c); TODO: FIX LATER Or remove?
    }
    // Configure native cookie settings right before booting
    // Pass the raw, pre-verified array straight to PHP. No runtime IF statements required!
    session_set_cookie_params([
        'lifetime' => $c['SESSION']['COOKIES']['SESSION_LIFETIME'] ?? 0,
        'path' => $c['SESSION']['COOKIES']['SESSION_PATH'] ?? '/',
        'domain' => $c['SESSION']['COOKIES']['SESSION_DOMAIN'] ?? '',
        'secure' => $c['SESSION']['COOKIES']['SESSION_SECURE'] ?? true,
        'httponly' => true,
        'samesite' => $c['SESSION']['COOKIES']['SESSION_SAMESITE'] ?? 'Lax',
    ]);
    // If it fails to start a session, throw an error and exit with a 500 Internal Server Error
    if (!session_start()) {
        $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
        funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }
}

/* INTERNAL RATE LIMITER (ALL LEVELS) & INERNAL ROUTE CACHE (Route Exclusive) */
function funk_internal_rate_limiter(&$c, int $maxRequestsPerWindowSize, int $windowSizeSecs, string|array $by = 'ip', $driver = 'redis') {}

function funk_internal_route_cache(&$c, int $ttl, string $driver = 'redis', string|array|null $varyBy = null, bool $private = false) {}
/**
 * Checks if an IP (IPv4 or IPv6) matches an IP/CIDR string.
 */
function funk_internal_is_ip_trusted(&$c, string $ip, array $trustedList): bool
{
    if (empty($ip)) {
        return false;
    }
    // Flatten nested arrays if passed like ['ip4' => [...], 'ip6' => [...]]
    $flatList = [];
    foreach ($trustedList as $key => $val) {
        if (is_array($val)) {
            $flatList = array_merge($flatList, $val);
        } else {
            $flatList[] = $val;
        }
    }
    if (in_array('*', $flatList, true) || in_array($ip, $flatList, true)) {
        return true;
    }
    $ipBin = @inet_pton($ip);
    if ($ipBin === false) {
        return false;
    }
    $isIPv4 = (strlen($ipBin) === 4);
    foreach ($flatList as $trusted) {
        if (!str_contains($trusted, '/')) {
            if ($ip === $trusted) {
                return true;
            }
            continue;
        }
        [$range, $netmask] = explode('/', $trusted, 2);
        $rangeBin = @inet_pton($range);
        if ($rangeBin === false || strlen($rangeBin) !== strlen($ipBin)) {
            continue;
        }
        $netmask = (int)$netmask;
        $maxBits = $isIPv4 ? 32 : 128;
        if ($netmask < 0 || $netmask > $maxBits) {
            continue;
        }
        $maskBin = '';
        $fullBytes = (int)($netmask / 8);
        $remainderBits = $netmask % 8;
        if ($fullBytes > 0) {
            $maskBin .= str_repeat("\xFF", $fullBytes);
        }
        if ($remainderBits > 0) {
            $maskBin .= chr(0xFF << (8 - $remainderBits));
        }
        $maskBin = str_pad($maskBin, $isIPv4 ? 4 : 16, "\x00", STR_PAD_RIGHT);
        if (($ipBin & $maskBin) === ($rangeBin & $maskBin)) {
            return true;
        }
    }
    return false;
}
/**
 * Resolves true client IP using Custom Resolver,
 * Trusted Proxies, or REMOTE_ADDR fallback.
 */
function funk_internal_resolve_ip(&$c): string
{
    $remoteAddr     = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $trustedProxies = $c['runtime']['trusted_ip_proxies'] ?? [];
    $ipHeaders      = $c['runtime']['trusted_ip_headers'] ?? [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP'
    ];
    if (empty($trustedProxies) || !funk_internal_is_ip_trusted($c, $remoteAddr, $trustedProxies)) {
        return $remoteAddr;
    }
    foreach ($ipHeaders as $headerKey) {
        if (!empty($_SERVER[$headerKey])) {
            $rawHeader = $_SERVER[$headerKey];
            $ipList    = array_map('trim', explode(',', $rawHeader));
            for ($i = count($ipList) - 1; $i >= 0; $i--) {
                $candidateIp = $ipList[$i];

                if (filter_var($candidateIp, FILTER_VALIDATE_IP)) {
                    if (!funk_internal_is_ip_trusted($c, $candidateIp, $trustedProxies)) {
                        return $candidateIp;
                    }
                }
            }
        }
    }
    return $remoteAddr;
}
// Default FunkPHP Exception Handler that catches any uncaught exceptions and returns
// a JSON or HTML error response depending on the Accept Header of the request. It is
// used unless a user-defined Exception Handler is set by the Developer creating one
// own using the "funk_handle_uncaught_exception()" in "/src/funkphp/config/functions.php" file.
function funk_internal_exception_handler(&$c, \Throwable $e)
{
    $c['err']['INTERNAL'][] = "UNCAUGHT EXCEPTION: " . $e->getMessage();
    \funk_use_log($c, "UNCAUGHT EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(), 'CRIT');

    // Debug only happens when NOT online and when debug is to show errors
    $isDebug = ((defined('FUNKPHP_ONLINE') && FUNKPHP_ONLINE === false && isset($c['debug']['show_errors'])) ? true : false);
    if ($isDebug) {
        $file = $e->getFile();
        $line = $e->getLine();
        $msg  = htmlspecialchars($e->getMessage());
        $type = get_class($e);
        $snippet = funk_internal_render_code_snippet($file, $line);
        $htmlOutput = "
        <div style='font-family: system-ui, -apple-system, sans-serif; background:#121212; color:#f1f1f1; padding:20px; min-height:100vh;'>
            <h1 style='color:#ff5555; margin:0 0 10px 0;'>{$type}</h1>
            <h2 style='font-size:18px; color:#e0e0e0; font-weight:normal; margin:0 0 20px 0;'>{$msg}</h2>
            <p style='color:#888; margin-bottom:5px;'>Exception triggered in <strong>{$file}</strong> on line <strong>{$line}</strong></p>
            {$snippet}
            <h3>Stack Trace</h3>
            <pre style='background:#1e1e1e; padding:15px; border-radius:6px; overflow-x:auto; color:#b0b0b0; font-size:12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
        </div>";
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        echo $htmlOutput;
        exit;
    }
    $err = 'An unexpected Internal Server Error occurred. Please check the Application Logs.';
    \funk_use_error_json_or_page($c, 500, ["internal_server_error" => $err], '500', $err);
}
/**
 * Internal Default Error Handler
 * Converts standard PHP errors/warnings into ErrorException so they
 * get caught by the Exception Handler.
 */
function funk_internal_error_handler(int $severity, string $message, string $file, int $line): bool
{
    // Respect the error_reporting setting (e.g. ignore @ operator)
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
}
/**
 * Extracts lines around an error to render a visual code snippet in HTML.
 */
function funk_internal_render_code_snippet(string $filePath, int $errorLine, int $padding = 5): string
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return '<div style="padding:10px; background:#222; color:#888;">Source File Unavailable</div>';
    }
    $lines = file($filePath);
    $start = max(0, $errorLine - $padding - 1);
    $end   = min(count($lines), $errorLine + $padding);
    $html = '<div style="background:#1e1e1e; color:#d4d4d4; font-family: monospace; border-radius:6px; overflow:hidden; margin:15px 0;">';
    $html .= '<div style="background:#2d2d2d; color:#aaa; padding:6px 12px; font-size:12px; border-bottom:1px solid #333;">' . htmlspecialchars($filePath) . '</div>';
    $html .= '<table style="width:100%; border-collapse:collapse; font-size:13px; line-height:1.4;">';
    for ($i = $start; $i < $end; $i++) {
        $lineNum = $i + 1;
        $isErrorLine = ($lineNum === $errorLine);
        $rowBg = $isErrorLine ? 'background:#44171a;' : 'background:#1e1e1e;';
        $numColor = $isErrorLine ? 'color:#ff6b6b; font-weight:bold;' : 'color:#555;';
        $codeColor = $isErrorLine ? 'color:#ffffff; font-weight:bold;' : 'color:#d4d4d4;';
        $codeContent = htmlspecialchars($lines[$i]);
        $html .= "<tr style='{$rowBg}'>";
        $html .= "<td style='width:40px; text-align:right; padding:2px 10px; {$numColor} user-select:none;'>{$lineNum}</td>";
        $html .= "<td style='padding:2px 10px; {$codeColor} white-space:pre-wrap;'>{$codeContent}</td>";
        $html .= "</tr>";
    }
    $html .= '</table></div>';
    return $html;
}
// Match Trie Route (only used locally)
function funk_internal_match_route_trie(&$c, string $requestUri, array $methodRootNode)
{
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);
    $currentNode = $methodRootNode;
    $matchedPathSegments = ['route' => []];
    $matchedParams = [];
    $segmentsConsumed = 0;
    if ($uriSegmentCount === 0) {
        if (!isset($currentNode['/'])) {
            return false;
        }
        $c['req']['matched_route'] = true;
        $c['req']['segments'] = ['/'];
        $c['req']['route'] = '/';
        return true;
    }
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments['route'][] = $currentUriSegment;
            $currentNode = $currentNode[$currentUriSegment];
            $segmentsConsumed++;
            continue;
        }
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']);
            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedParams[$placeholderKey] = $currentUriSegment;
                $c['req']['params'][$placeholderKey] = $currentUriSegment;
                $matchedPathSegments['route'][] = ":" . $placeholderKey;
                $currentNode = $currentNode[':'][$placeholderKey];
                $segmentsConsumed++;
                continue;
            }
        }
        return false;
    }
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments['route'])) {
            $c['req']['segments'] = $matchedPathSegments['route'];
            $c['req']['route'] = ('/' . implode('/', $matchedPathSegments['route']));
            $c['req']['matched_route'] = true;
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function funk_internal_validate_params(&$c)
{
    $allParamsValid = true;
    if (isset($c['runtime']['route']['hasParams'])) {
        $noOfParams = count($c['req']['params']);
        if (isset($c['runtime']['route']['params'])) {
            if ($noOfParams !== count($c['runtime']['route']['params'])) {
                $c['req']['params_valid'] = false;
                return;
            }
            foreach ($c['runtime']['route']['params'] as $rParam => $rParDetails) {
                // Param has polymorphic?
                if (isset($rParDetails['pairs'])) {
                    $anyPairMatch = false;
                    $c['req']['param_variant'][$rParam] = null;
                    $matchedrPairName = null;
                    foreach ($rParDetails['pairs'] as $rPairName => $rPairPattern) {
                        if (preg_match($rPairPattern, $c['req']['params'][$rParam])) {
                            $anyPairMatch = true;
                            $matchedrPairName = $rPairName;
                            break;
                        } else {
                            continue;
                        }
                    }
                    // Polymorphic pattern match, its name becomes the valid param
                    // it is already validated in route that no other param with
                    // or without polymorphic patterns would collide so
                    if ($anyPairMatch) {
                        $c['req']['param_valid'][$matchedrPairName] = true;
                    } else {
                        $c['req']['param_valid'][$rParam] = false;
                        $allParamsValid = false;
                    }
                }
                // Here regular param but with callback?
                elseif (isset($rParDetails['callback'])) {
                    if (function_exists($rParDetails['callback'])) {
                        if ($rParDetails['callback'](
                            $c,
                            $c['req']['params'][$rParam]
                        ) === true) {
                            $c['req']['param_valid'][$rParam] = true;
                        } else {
                            $c['req']['params'][$rParam] = (isset($rParDetails['default']) ? $rParDetails['default'] : $c['req']['params'][$rParam]);
                            $c['req']['param_valid'][$rParam] = false;
                            $allParamsValid = false;
                        }
                    } else {
                        \funk_use_error_json_or_page($c, 500, ['internal_server_error' => 'Expected `User-Defined Function` in `/src/funkphp/config/functions.php` was Not Found for Validating a Param Rule.'], '500', 'Expected `User-Defined Function` in `/src/funkphp/config/functions.php` was Not Found for Validating a Param Rule.');
                    }
                }
                // Here just regular param with pattern
                else {
                    if (!preg_match($rParDetails['pattern'], $c['req']['params'][$rParam])) {
                        $c['req']['params'][$rParam] = (isset($rParDetails['default']) ? $rParDetails['default'] : $c['req']['params'][$rParam]);
                        $c['req']['param_valid'][$rParam] = false;
                        $allParamsValid = false;
                    } else {
                        $c['req']['param_valid'][$rParam] = true;
                    }
                }
            }
        } else {
            $c['req']['params_valid'] = false;
            return;
        }
    }
    if ($allParamsValid) {
        $c['req']['params_valid'] = true;
    }
}

// Retrieve what content UA accepts
function funk_internal_negotiate_content(mixed &$c): array
{
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (empty(trim($acceptHeader))) {
        return [[], null];
    }
    $mimeTypeToShortHand = [
        'text/html'                 => 'html',
        'application/xhtml+xml'     => 'xhtml',
        'application/json'          => 'json',
        'text/json'                 => 'json',
        'application/vnd.api+json'  => 'jsonapi',
        'application/problem+json'  => 'jsonproblem',
        'application/ld+json'       => 'jsonld',
        'application/hal+json'      => 'jsonhal',
        'application/wasm'          => 'wasm',
        'application/xml'           => 'xml',
        'application/octet-stream'  => 'stream',
        'text/xml'                  => 'xml',
        'text/plain'                => 'text',
        'text/csv'                  => 'csv',
        'text/calendar'             => 'calendar',
        'text/vcard'                => 'vcard',
        'text/markdown'             => 'markdown',
        'image/webp'                => 'webp',
        'image/avif'                => 'avif',
        'image/x-icon'              => 'ico',
        'image/bmp'                 => 'bmp',
        'image/apng'                => 'apng',
        'image/png'                 => 'png',
        'image/jpeg'                => 'jpg',
        'image/jpg'                 => 'jpg',
        'image/gif'                 => 'gif',
        'image/tiff'                => 'tiff',
        'image/heic'                => 'heic',
        'image/svg+xml'             => 'svg',
        'audio/mpeg'                => 'mp3',
        'audio/wav'                 => 'wav',
        'audio/flac'                => 'flac',
        'video/mp4'                 => 'mp4',
    ];
    $userCustomAccepts = $c['runtime']['request_accepts'] ?? [];
    $types = [];
    $c['req']['accepts']['json']  = false;
    $c['req']['accepts']['html']  = false;
    $c['req']['accepts']['xml']   = false;
    $c['req']['accepts']['text']  = false;
    $c['req']['accepts']['image'] = false;
    $c['req']['accepts']['media'] = false;
    foreach (explode(',', $acceptHeader) as $part) {
        $segments  = explode(';', trim($part));
        $mediaType = trim($segments[0]);
        if (empty($mediaType)) {
            continue;
        }
        // Assume equal weight
        $q = 1.0;
        foreach (array_slice($segments, 1) as $param) {
            $param = trim($param);
            if (str_starts_with($param, 'q=')) {
                $q = (float) substr($param, 2);
                break;
            }
        }
        // Skip q>=0
        if ($q <= 0) {
            continue;
        }
        // First check user-defined ones, then static
        // lookups and finally generic ones
        if (isset($userCustomAccepts[$mediaType])) {
            $alias = $userCustomAccepts[$mediaType];
            $c['req']['accepts'][$alias] = true;
        }
        if (isset($mimeTypeToShortHand[$mediaType])) {
            $alias = $mimeTypeToShortHand[$mediaType];
            $c['req']['accepts'][$alias] = true;
        }
        if ($mediaType === 'application/json' || $mediaType === 'text/json' || str_ends_with($mediaType, '+json')) {
            $c['req']['accepts']['json'] = true;
        } else if ($mediaType === 'text/html' || $mediaType === 'application/xhtml+xml') {
            $c['req']['accepts']['html'] = true;
        } else if ($mediaType === 'application/xml' || $mediaType === 'text/xml' || str_ends_with($mediaType, '+xml')) {
            $c['req']['accepts']['xml'] = true;
        } else if ($mediaType === 'text/plain' || $mediaType === 'text/markdown' || $mediaType === 'text/csv') {
            $c['req']['accepts']['text'] = true;
        } else if (str_starts_with($mediaType, 'image/')) {
            $c['req']['accepts']['image'] = true;
        } else if (str_starts_with($mediaType, 'audio/') || str_starts_with($mediaType, 'video/')) {
            $c['req']['accepts']['media'] = true;
        } else if ($mediaType === '*/*') {
            $c['req']['accepts']['json']  = true;
            $c['req']['accepts']['html']  = true;
            $c['req']['accepts']['xml']   = true;
            $c['req']['accepts']['text']  = true;
            $c['req']['accepts']['media'] = true;
            $c['req']['accepts']['audio'] = true;
            $c['req']['accepts']['video'] = true;
            $c['req']['accepts']['image'] = true;
        }
        $types[] = ['type' => $mediaType, 'q' => $q];
    }
    // Sort based on q value and also try find preferred one
    usort($types, fn($a, $b) => $b['q'] <=> $a['q']);
    $sortedList = array_column($types, 'type');
    $topMime    = $sortedList[0] ?? null;
    $prefers = null;
    if ($topMime !== null) {
        if (isset($userCustomAccepts[$topMime])) {
            $prefers = $userCustomAccepts[$topMime];
        } else if (isset($mimeTypeToShortHand[$topMime])) {
            $prefers = $mimeTypeToShortHand[$topMime];
        } else if (str_ends_with($topMime, '+json')) {
            $prefers = 'json';
        } else if (str_ends_with($topMime, '+xml')) {
            $prefers = 'xml';
        } else if ($topMime === '*/*') {
            $prefers = 'html';
        } else {
            $prefers = $topMime;
        }
    }
    return [$sortedList, $prefers];
}


function funk_internal_handle_nonces(&$c, $nonce) {}

function funk_internal_handle_sri_internal(&$c, $nonce) {}

function funk_internal_handle_sri_external(&$c, $nonce) {}

function funk_internal_send_response_headers(&$c)
{
    if (headers_sent()) {
        $c['err']['INTERNAL'][] = "Headers have already been sent when they should be sent by `funk_internal_send_response_headers()` Function. Check if You have used `header()` anywhere inside Any of Your Pipe Functions (including Request Pipes, Middleware Pipes, Route Pipes & Post-Response Pipes).";
        return;
    }
    // First remove any previously added header from 3rd party;
    // IMPORTANT: this does NOT remove any added via `funk_set_header()`
    if (isset($c['runtime']['route']['headers']['remove'])) {
        foreach ($c['runtime']['route']['headers']['remove'] as $routeHeaderRemove) {
            header_remove($routeHeaderRemove);
        }
    }
    if (isset($c['runtime']['route']['headers']['add'])) {
        foreach ($c['runtime']['route']['headers']['add'] as $routeHeaderAddK => $routeHeaderAddV) {
            header($routeHeaderAddV);
        }
    }
    /* CSP PARTS! - Must first get from Global, Method then Route OR
    Maybe it should be that during compile(), every Route already has
    all available headers to grab and thus return here? */
    $cspParts = [];
    $cspDirectives = ['placeholder' => ['placeholder2']];
    foreach ($cspDirectives as $directive => $sources) {
        // If count is 0 (because no nonces were ever evaluated for this directive), SKIP IT!
        if (empty($sources)) {
            continue;
        }
        $cspParts[] = $directive . ' ' . implode(' ', $sources);
    }
    if (!empty($cspParts)) {
        header('Content-Security-Policy: ' . implode('; ', $cspParts));
    }
}
// Send ONLY Global Headers, used only when things stops already globally without route match
function funk_internal_send_global_headers(&$c)
{
    if (isset($c['runtime']['global_headers']['remove']) && !empty($c['runtime']['global_headers']['remove'])) {
        foreach ($c['runtime']['global_headers']['remove'] as $ghr) {
            header_remove($ghr);
        }
    }
    if (isset($c['runtime']['global_headers']['add']) && !empty($c['runtime']['global_headers']['add'])) {
        foreach ($c['runtime']['global_headers']['add'] as $gh) {
            header($gh);
        }
    }
}
// Send ONLY Method Headers, used only when things stops already without route match in matched method though
function funk_internal_send_method_headers(&$c)
{
    $method = $c['req']['method'];
    if (isset($c['runtime']['method_headers']['remove'][$method]) && !empty($c['runtime']['method_headers']['remove'][$method])) {
        foreach ($c['runtime']['method_headers']['remove'][$method] as $mhr) {
            header_remove($mhr);
        }
    }
    if (isset($c['runtime']['method_headers']['add'][$method]) && !empty($c['runtime']['method_headers']['add'][$method])) {
        foreach ($c['runtime']['method_headers']['add'][$method] as $mh) {
            header($mh);
        }
    }
}
// Handle No Route Match (CONFIG means it was globally otherwise current Method)
// When this is called, we do have Acceptable Content so we can check that and also
// check against what is actually configured
function funk_internal_handle_no_route_match(&$c, $globalOrMethod)
{
    // Set boolean on whether Post-Response Pipe should
    // run or not due to NoMatchMethod/Match
    if (
        isset($c['runtime']['SKIP_POST_RESPONSE_ON_NO_MATCH'])
        && $c['runtime']['SKIP_POST_RESPONSE_ON_NO_MATCH'] === true
    ) {
        $c['runtime']['SKIP_POST_RESPONSE'] = true;
    }
    if ($globalOrMethod === 'CONFIG') {
        // early return when no configured no route match handling
        if (!isset($c['runtime']['NO_ROUTE_MATCH'])) {
            return;
        }
        $prefers = $c['req']['prefers'];
        if ($prefers === 'json' && isset($c['runtime']['NO_ROUTE_MATCH']['JSON'])) {
            funk_internal_send_global_headers($c);
            header("content-type: application/json; charset=utf-8");
            http_response_code($c['runtime']['NO_ROUTE_MATCH']['JSON']['code']);
            echo $c['runtime']['NO_ROUTE_MATCH']['JSON']['JSON'];
            exit;
        } else if ($prefers === 'html' && isset($c['runtime']['NO_ROUTE_MATCH']['PAGE'])) {
            funk_internal_send_global_headers($c);
            header("content-type: text/html; charset=utf-8");
            http_response_code($c['runtime']['NO_ROUTE_MATCH']['PAGE']['code']);
            if (defined(FUNKPHP_ONLINE)) {
                include_once ROOT_FOLDER . '/pages/' . $c['runtime']['NO_ROUTE_MATCH']['PAGE']['page'] . '/.php';
                exit;
            } else {
                if (
                    file_exists($c['runtime']['NO_ROUTE_MATCH']['PAGE']['path'])
                    && is_readable($c['runtime']['NO_ROUTE_MATCH']['PAGE']['path'])
                ) {
                    include_once $c['runtime']['NO_ROUTE_MATCH']['PAGE']['path'];
                    exit;
                }
            }
        } else if ($prefers === 'text' && isset($c['runtime']['NO_ROUTE_MATCH']['TEXT'])) {
            funk_internal_send_global_headers($c);
            header("content-type: text/plain; charset=utf-8");
            http_response_code($c['runtime']['NO_ROUTE_MATCH']['TEXT']['code']);
            echo $c['runtime']['NO_ROUTE_MATCH']['TEXT']['text'];
            exit;
        } else if (isset($c['runtime']['NO_ROUTE_MATCH']['CALLBACK'])) {
            funk_internal_send_global_headers($c);
            if (function_exists($c['runtime']['NO_ROUTE_MATCH']['CALLBACK'])) {
                $c['runtime']['NO_ROUTE_MATCH']['CALLBACK']($c);
                exit;
            }
        }
        // No match between preferred content type AND configured no route match
        return;
    } else {
        // early return when no configured no route match handling for method
        if (!isset($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod])) {
            return;
        }
        $prefers = $c['req']['prefers'];
        if ($prefers === 'json' && isset($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['JSON'])) {
            funk_internal_send_method_headers($c);
            header("content-type: application/json; charset=utf-8");
            http_response_code($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['JSON']['code']);
            echo $c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['JSON']['JSON'];
            exit;
        } else if ($prefers === 'html' && isset($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['PAGE'])) {
            funk_internal_send_method_headers($c);
            header("content-type: text/html; charset=utf-8");
            http_response_code($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['PAGE']['code']);
            if (defined(FUNKPHP_ONLINE)) {
                require ROOT_FOLDER . '/pages/' . $c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['PAGE']['page'] . '/.php';
                exit;
            } else {
                if (
                    file_exists($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['PAGE']['path'])
                    && is_readable($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['PAGE']['path'])
                ) {
                    require $c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['PAGE']['path'];
                    exit;
                }
            }
        } else if ($prefers === 'text' && isset($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['TEXT'])) {
            funk_internal_send_method_headers($c);
            header("content-type: text/plain; charset=utf-8");
            http_response_code($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['TEXT']['code']);
            echo $c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['TEXT']['text'];
            exit;
        } else if (isset($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['CALLBACK'])) {
            funk_internal_send_method_headers($c);
            if (function_exists($c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['CALLBACK'])) {
                $c['runtime']['NO_ROUTE_MATCH_METHOD'][$globalOrMethod]['CALLBACK']($c);
                exit;
            }
        }
        // no match between preferred content type AND configured no route match
        return;
    }
}
// When there is no configured no match route globally nor for non-matched method
function funk_internal_handle_no_no_route_match(&$c)
{
    if (
        isset($c['runtime']['SKIP_POST_RESPONSE_ON_NO_MATCH'])
        && $c['runtime']['SKIP_POST_RESPONSE_ON_NO_MATCH'] === true
    ) {
        $c['runtime']['SKIP_POST_RESPONSE'] = true;
    }
    funk_internal_send_global_headers($c);
    $message = (isset($c['runtime']['NO_NO_MATCH_MESSAGE'])
        && is_string($c['runtime']['NO_NO_MATCH_MESSAGE'])
        && trim($c['runtime']['NO_NO_MATCH_MESSAGE']) !== '')
        ? $c['runtime']['NO_NO_MATCH_MESSAGE']
        : (htmlspecialchars('404 | No Content or Page Found <br/>Are You the Developer, Web Administrator or General Web Master?<br/> There is NO Configured Global `->setNoRouteMatch&lt;Variant&gt;` Yet 😱!'));
    if ($c['req']['prefers'] === 'json') {
        http_response_code(404);
        header("content-type: application/json; charset=utf-8");
        echo json_encode(['internal_server_error' => html_entity_decode($message, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'), 'status' => 404]);
        exit;
    }
    // Set HTTP status code & headers BEFORE sending HTML output
    http_response_code(404);
    header("content-type: text/html; charset=utf-8");
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - No Content or Page Found | Have You Configured `->setNoRouteMatch` Yet?</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #181825;
            color: #cdd6f4;
            font-family: system-ui, -apple-system, sans-serif;
            display: grid;
            place-items: center;
            min-height: 100vh;
        }
        .container { text-align: center; padding: 2rem; }
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
        <h1>404</h1>
        <p>{$message}</p>
    </div>
</body>
</html>
HTML;
    echo $html;
    exit;
}

/******************************************/
/*** PAGE-RELATED Functions For FunkPHP ***/
/******************************************/

/*************************************************/
/*** ENTRY-POINT-RELATED Functions For FunkPHP ***/
/*************************************************/
/* Global entry point for initializing FunkPHP in `/src/funkphp/config/app.php` */
function FunkPHP()
{
    return new FunkPHP(new C);
}
