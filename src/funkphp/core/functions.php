<?php

/**
 * -----------------------------
 * FUNKPHP CONSTANTS & FUNCTIONS
 * -----------------------------
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
        cli_dump($data, $exit);
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
    // Recursive HTML UL/LI Generator with recursion tracking
    $render = function ($data, $key = null, $isList = false, array $seenObjects = [], int $depth = 0) use (&$render, &$metrics): string {
        // Max depth guard (prevents potential infinite array-reference loops)
        if ($depth > 25) {
            return "<span class=\"fd-null\">*MAX DEPTH EXCEEDED*</span>";
        }
        $prefix = '';
        if ($key !== null) {
            $safeKey = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
            $prefix = $isList
                ? "<span class=\"fd-idx\">[{$safeKey}]</span> "
                : "<span class=\"fd-key\">'{$safeKey}'</span> <span class=\"fd-type\">=&gt;</span> ";
        }
        if (is_array($data)) {
            $metrics['arrays']++;
            $count = count($data);
            $isListArr = array_is_list($data);
            $typeLabel = $isListArr ? 'List-Array' : 'Assoc-Array';
            if ($isListArr) $metrics['arrays-lists']++;
            else $metrics['arrays-assocs']++;
            if ($count === 0) {
                $metrics['arrays-empty']++;
                return "{$prefix}<span class=\"fd-type\">{$typeLabel}(0) []</span>";
            }
            $html = "{$prefix}<span class=\"fd-toggle\">▼</span> <span class=\"fd-type\">{$typeLabel}({$count}) [</span>";
            $html .= "<ul class=\"fd-tree\">";
            foreach ($data as $k => $v) {
                $html .= "<li>" . $render($v, $k, $isListArr, $seenObjects, $depth + 1) . "</li>";
            }
            $html .= "</ul><span class=\"fd-type\">]</span>";
            return $html;
        } elseif (is_object($data)) {
            $metrics['objects']++;
            $className = get_class($data);
            $objHash = spl_object_hash($data);
            // Circular reference detection!
            if (isset($seenObjects[$objHash])) {
                return "{$prefix}<span class=\"fd-type\">Object('{$className}')</span> <span class=\"fd-null\">*RECURSION*</span>";
            }
            // Mark object as seen in this branch
            $seenObjects[$objHash] = true;
            $properties = (array)$data;
            $count = count($properties);
            if ($count === 0) {
                return "{$prefix}<span class=\"fd-type\">Object('{$className}') {}</span>";
            }
            $html = "{$prefix}<span class=\"fd-toggle\">▼</span> <span class=\"fd-type\">Object('{$className}') ({$count}) {</span>";
            $html .= "<ul class=\"fd-tree\">";
            foreach ($properties as $k => $v) {
                $k = str_replace("\0*\0", '(protected) ', $k);
                $k = preg_replace('/^\0[^\0]+\0/', '(private) ', $k);
                $html .= "<li>" . $render($v, $k, false, $seenObjects, $depth + 1) . "</li>";
            }
            $html .= "</ul><span class=\"fd-type\">}</span>";
            return $html;
        } elseif (is_string($data)) {
            $metrics['strings']++;
            $len = strlen($data);
            if ($len === 0) $metrics['strings-empty']++;
            $safeStr = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return "{$prefix}<span class=\"fd-str\">\"{$safeStr}\"</span> <span class=\"fd-meta\">(string:{$len})</span>";
        } elseif (is_int($data)) {
            $metrics['integers']++;
            return "{$prefix}<span class=\"fd-num\">{$data}</span> <span class=\"fd-meta\">(integer)</span>";
        } elseif (is_float($data)) {
            $metrics['floats']++;
            return "{$prefix}<span class=\"fd-num\">{$data}</span> <span class=\"fd-meta\">(float)</span>";
        } elseif (is_bool($data)) {
            $metrics['booleans']++;
            if ($data) $metrics['booleans-true']++;
            else $metrics['booleans-false']++;
            $boolStr = $data ? 'true' : 'false';
            return "{$prefix}<span class=\"fd-bool\">{$boolStr}</span> <span class=\"fd-meta\">(boolean)</span>";
        } elseif (is_null($data)) {
            $metrics['nulls']++;
            return "{$prefix}<span class=\"fd-null\">null</span>";
        } else {
            $metrics['others']++;
            $type = gettype($data);
            return "{$prefix}<span class=\"fd-null\">[Type: {$type}]</span>";
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

            .funk-web-dump ul.fd-tree {
                list-style: none;
                margin: 2px 0;
                padding-left: 20px;
                border-left: 1px dashed #45475a;
            }

            .funk-web-dump li {
                margin: 2px 0;
            }

            .funk-web-dump .fd-toggle {
                cursor: pointer;
                user-select: none;
                color: #f5e0dc;
                display: inline-block;
                width: 14px;
                font-size: 11px;
                transition: transform 0.1s ease;
            }

            .funk-web-dump .fd-toggle.collapsed {
                transform: rotate(-90deg);
                color: #a6adc8;
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

            .funk-web-dump div .metrics-top {
                margin-top: 8x;
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

        <header>[FunkDump]<?= (strlen($headerOptionalMsg) > 0 ? " - $headerOptionalMsg" : '') ?></header>
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
        <script>
            document.querySelectorAll('.funk-web-dump .fd-toggle').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const parent = this.parentElement;
                    const tree = parent.querySelector(':scope > .fd-tree');
                    if (tree) {
                        if (tree.style.display === 'none') {
                            tree.style.display = 'block';
                            this.classList.remove('collapsed');
                        } else {
                            tree.style.display = 'none';
                            this.classList.add('collapsed');
                        }
                    }
                });
            });
            <?php if ($colorizeAccentGravedText): ?>
                document.querySelectorAll('.funk-web-dump .fd-str').forEach(function(el) {
                    if (el.textContent.includes('`')) {
                        el.innerHTML = el.innerHTML.replace(/`([^`]+)`/g, '<span class="fd-gravel">$1</span>');
                    }
                });
            <?php endif; ?>
        </script>
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
function funk_session_started_or_start_it(&$c)
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
// The unified way to read session values across FunkPHP
function funk_session_get(&$c, string $key, $default = null)
{
    \funk_session_started_or_start_it($c);
    return $_SESSION[$key] ?? $default;
}
// The unified way to write session values across FunkPHP
function funk_session_set(&$c, string $key, $value): void
{
    \funk_session_started_or_start_it($c);
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

// FUNKPHP GENERIC RANDOMIZER FUNCTIONS
// This function uses the "The Random\Randomizer class" to generate a unique password
function funk_generate_random_password(&$c, $length = 20, $returnHashed = false)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();
    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];
    $special = [
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        '[',
        '\\',
        ']',
        '^',
        '_',
        '`',
        '{',
        '|',
        '}',
        '~',
    ];
    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers, $special);
    $total = count($all) - 1;

    // Prepare empty password string
    $password = '';

    // Add random characters to the password until it reaches the desired length
    while (strlen($password) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total); // Get a random index using the randomizer
        $password .= $all[$randomCharIndex];
    }

    // Split the password, shuffle it and join it back together using shuffleArray from randomizer class!
    $password = $randomizer->shuffleArray(str_split($password));
    $password = implode('', $password);

    // Return a hashed password if needed
    if ($returnHashed) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // Otherwise, return the generated password
    return $password;
}

/***  ROUTE-RELATED PHP FUNCTIONS FOR FUNKPHP ***/
// Default FunkPHP Exception Handler that catches any uncaught exceptions and returns
// a JSON or HTML error response depending on the Accept Header of the request. It is
// used unless a user-defined Exception Handler is set by the Developer creating one
// own using the "funk_handle_uncaught_exception()" in "/src/funkphp/config/functions.php" file.
function funk_default_exception_handler(&$c, $e)
{
    $c['err']['UNCAUGHT_EXCEPTION'] = $e;
    \funk_use_log($c, "UNCAUGHT EXCEPTION BY DEVELOPER: " . $e->getMessage(), 'CRIT');
    $err = 'Tell the Developer: An Uncaught Exception Occurred: `' . $e->getMessage() . '` Please check the Logs for more details.';
    \funk_use_error_json_or_page($c, 500, ["internal_error" => $err], '500', $err);
}

// Default FunkPHP Registered Shutdown Function which runs after a request has been
// handled. It is used unless a user-defined register_shutdown_function is set by
// the Developer creating one own using the "funk_set_register_shutdown_function()"
// in the "/src/funkphp/config/functions.php" file.
function funk_default_register_shutdown_function(&$c)
{
    if (
        isset($c['pipeline']['post_response'])
        && is_array($c['pipeline']['post_response'])
        && array_is_list($c['pipeline']['post_response'])
        && !empty($c['pipeline']['post_response'])
    ) {
        \funk_run_pipeline_post_response($c);
    } else {
        $c['err']['MAYBE']['PIPELINE']['funk_run_post_request'][] = 'No Configured Post-Response Pipeline Functions (`"<ENTRY>" => "pipeline" => "post_response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post_response\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File!';
    }
}

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
    // if (ob_get_level() > 0) {
    //     ob_clean();
    // }
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

// `pipeline` is the list of functions to always run for each request (unless any
// of the functions terminates it early!) This is the main entry point for each request!
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_request(&$c)
{
    // 'defensive' = we check almost everything and output error to user if something gets wrong
    // Must be a non-empty numbered array
    if (
        !isset($c['<ENTRY>']['pipeline']['request'])
        || !is_array($c['<ENTRY>']['pipeline']['request'])
        || !array_is_list($c['<ENTRY>']['pipeline']['request'])
        || count($c['<ENTRY>']['pipeline']['request']) === 0
    ) {
        $c['err']['PIPELINE']['funk_run_pipeline_request'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "request"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'request\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File!';
        $err = 'Tell the Developer: No Pipeline Functions to run? Please check the `[\'pipeline\'][\'request\']` Key in the `funkphp/core/pipeline_request.php` File!';
        \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
    }

    // Prepare for main loop to run each pipeline function
    $count = count($c['<ENTRY>']['pipeline']['request']);
    $pipeDir = ROOT_FOLDER . '/pipeline/request/';
    for ($i = 0; $i < $count; $i++) {
        // $current pipeline function should be a single associative array with a single value (which can be null)
        $current_pipe = $c['<ENTRY>']['pipeline']['request'][$i] ?? null;
        if (
            !isset($current_pipe)
            || !is_string($current_pipe)
        ) {
            $c['err']['PIPELINE']['funk_run_pipeline_request'][] = 'Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. Must be a String!';
            $err = 'Tell the Developer: Pipeline Request Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. Must be a String!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $fnToRun = $current_pipe;
        $pipeToRun = $pipeDir . $fnToRun . '.php';

        if (!is_readable($pipeToRun)) {
            $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
            $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
        $runPipe = include_once $pipeToRun;
        $pipeFnToRun = NAMESPACE_PIPELINE_REQUEST . $fnToRun . '\\' . $fnToRun;
        if (is_callable($pipeFnToRun)) {
            $rawRun = $pipeFnToRun($c);
        }
        // HARD ERROR to not allow to pass security checks
        else {
            $c['err']['PIPELINE']['function funk_run_pipeline_request'][] = 'Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
            $err = 'Tell the Developer: Pipeline Request Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }

        // Clean up before running the next pipeline function
        $c['req']['current_pipeline'] = $current_pipe;
        unset($c['<ENTRY>']['pipeline']['request'][$i]);
        $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['request'][$i + 1])
            && is_array($c['<ENTRY>']['pipeline']['request'][$i + 1])
            ? array_key_first($c['<ENTRY>']['pipeline']['request'][$i + 1])
            : null;
    }

    // Default values after either 'defensive' or 'happy' mode has run
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['request'] = null;
}

// Try run middlewares AFTER handled request (and this can
// also be due to being exited prematurely by the application)
// &$c is Global Config Variable with "everything"!
function funk_run_pipeline_post_response(&$c)
{
    // Use ob_start() to "swallow" any possibly unwanted output to the client
    // but before starting, check if it already exists and clear its previous
    // contents if it does!
    ob_start();

    // We only run post_response pipelines if not skipped by the application!
    // and they are also optional, so it can be skipped if not configured!
    if ($c['req']['skip_post_response']) {
        $c['err']['MAYBE']['PIPELINE']['POST-RESPONSE']['funk_run_pipeline_post_response'][] = 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!';
        funk_use_log($c, 'Post-Response Pipeline was skipped by the Application for HTTP(S) Request:' . (isset($c['req']['method']) && is_string($c['req']['method']) && !empty($c['req']['method'])) ?: "<UNKNOWN_METHOD>" . (isset($c['req']['route']) && is_string($c['req']['route']) && !empty($c['req']['route'])) ?: "<UNKNOWN_ROUTE>" . '. No Post-Response Pipeline Functions were run. If you expected some, check where the Function `funk_skip_post_response(&$c)` could have been ran for your HTTP(S) Request!', 'INFO');
        ob_end_clean();
        return;
    }

    // 'defensive' = we check almost everything and output error to user if something gets wrong
    // Must be a non-empty numbered array if it is set
    if (
        isset($c['<ENTRY>']['pipeline']['post_response'])
    ) {
        if (
            !is_array($c['<ENTRY>']['pipeline']['post_response'])
            || !array_is_list($c['<ENTRY>']['pipeline']['post_response'])
            || count($c['<ENTRY>']['pipeline']['post_response']) === 0
        ) {
            $c['err']['PIPELINE']['funk_run_pipeline_post_response'][] = 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post_response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post_response\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File!';
            funk_use_log($c, 'No Configured Pipeline Functions (`"<ENTRY>" => "pipeline" => "post_response"`) to run. Check the `[\'<ENTRY>\'][\'pipeline\'][\'post_response\']` Key in the Pipeline Configuration File `funkphp/core/pipeline_request.php` File! - Function stops here!', 'CRITICAL');
            ob_end_clean();
            return;
        }
        // Prepare for main loop to run each pipeline function
        $count = count($c['<ENTRY>']['pipeline']['post_response']);
        $pipeDir = ROOT_FOLDER . '/pipeline/post_response/';
        $c['req']['keep_running_pipeline'] = true;
        for ($i = 0; $i < $count; $i++) {
            if ($c['req']['keep_running_pipeline'] === false) {
                break;
            }
            // $current pipeline function should be a single associative array with a single value (which can be null)
            $current_pipe = $c['<ENTRY>']['pipeline']['post_response'][$i] ?? null;
            if (
                !isset($current_pipe)
                || !is_string($current_pipe)
            ) {
                $c['err']['PIPELINE']['funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must be a String!';
                funk_use_log($c, 'Pipeline Post-Response Function at index ' .  $i . ' is either NULL or NOT a Valid Data Type. It must a String - Function stops here!', 'CRITICAL');
                ob_end_clean();
                return;
            }
            $fnToRun = $current_pipe;
            $pipeToRun = $pipeDir . $fnToRun . '.php';
            // if = pipeline already exists in dispatchers, so reuse it but with newly passed value!
            if (isset($c['dispatchers']['pipeline']['post_response'][$fnToRun])) {
                if (is_callable($c['dispatchers']['pipeline']['post_response'][$fnToRun])) {
                    $runPipeKey = $c['dispatchers']['pipeline']['post_response'][$fnToRun];
                    // Clean up before running the next pipeline function
                    $c['req']['current_pipeline'] = $current_pipe;
                    unset($c['<ENTRY>']['pipeline']['post_response'][$i]);
                    $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        && is_array($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        ? array_key_first($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        : null;
                    $rawRun = $runPipeKey($c);
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    \funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
            }
            // else = pipeline does not exist yet, so include, store and run it with passed value!
            else {
                if (!is_readable($pipeToRun)) {
                    $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory!';
                    \funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index '  .  $i . ' does NOT EXIST (or is NOT READABLE) in `funkphp/pipeline/request/` Directory! - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
                $runPipe = include_once $pipeToRun;
                if (is_callable($runPipe)) {
                    $c['dispatchers']['pipeline']['post_response'][$fnToRun] = $runPipe;
                    // Clean up before running the next pipeline function
                    $c['req']['current_pipeline'] = $current_pipe;
                    unset($c['<ENTRY>']['pipeline']['post_response'][$i]);
                    $c['req']['next_pipeline'] = isset($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        && is_array($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        ? array_key_first($c['<ENTRY>']['pipeline']['post_response'][$i + 1])
                        : null;
                    $rawRun = $runPipe($c);
                }
                // HARD ERROR to not allow to pass security checks
                else {
                    $c['err']['PIPELINE']['function funk_run_pipeline_post_response'][] = 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };`';
                    \funk_use_log($c, 'Pipeline Post-Response Function (`' . $fnToRun . '`) at index ' .  $i . ' is NOT CALLABLE for some reason. Each Function File should be in the style of: `<?php return function (&$c) { ... };` - Function stops here!', 'CRITICAL');
                    ob_end_clean();
                    return;
                }
            }
        }
    }
    // Default values after either 'defensive' or 'happy' mode has run
    ob_end_clean(); // Clear any possibly unwanted output to the client
    $c['req']['current_pipeline'] = null;
    $c['req']['keep_running_pipeline'] = false;
    $c['<ENTRY>']['pipeline']['post_response'] = null;
}

// Match Compiled Route with URI Segments, used by "r_match_developer_route"
function funk_match_compiled_route(&$c, string $requestUri, array $methodRootNode): ?array
{
    // Prepare & and extract URI Segments and remove empty segments
    $path = trim(strtolower($requestUri), '/');
    $uriSegments = empty($path) ? [] : array_values(array_filter(explode('/', $path)));
    $uriSegmentCount = count($uriSegments);
    // Prepare variables to store the current node,
    // matched segments, parameters, and middlewares
    $currentNode = $methodRootNode;
    $matchedPathSegments = ['uri' => $uriSegments, 'route' => []]; // Start with empty string to make implode work correctly
    $matchedParams = [];
    $matchedMiddlewares = [];
    $segmentsConsumed = 0;
    // EDGE-CASE: '/' and include middleware at root node if it exists
    if ($uriSegmentCount === 0) {
        // When no match for root node
        if (!isset($currentNode['/'])) {
            return null;
        }
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
        }
        return ["route" => '/', "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
    }
    // Iterate URI segments when more than 0
    for ($i = 0; $i < $uriSegmentCount; $i++) {
        $currentUriSegment = $uriSegments[$i];
        /// First try match "|" middleware node
        if (isset($currentNode['|'])) {
            array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
        }
        // Then try match literal route
        if (isset($currentNode[$currentUriSegment])) {
            $matchedPathSegments['route'][] = $currentUriSegment;
            $currentNode = $currentNode[$currentUriSegment];
            $segmentsConsumed++;
            continue;
        }
        // Or try match dynamic route ":" indicator node and
        // only store param and matched URI segment if not null
        if (isset($currentNode[':'])) {
            $placeholderKey = key($currentNode[':']);
            if ($placeholderKey !== null && isset($currentNode[':'][$placeholderKey])) {
                $matchedParams[$placeholderKey] = $currentUriSegment;
                $matchedPathSegments['route'][] = ":" . $placeholderKey;
                $currentNode = $currentNode[':'][$placeholderKey];
                $segmentsConsumed++;
                continue;
            }
        }
        // No matched "|", ":" or literal route in Compiled Routes!
        return null;
    }
    // EDGE-CASE: Add middleware at last node if it exists
    if (isset($currentNode['|'])) {
        array_push($matchedMiddlewares, "/" . implode('/', $matchedPathSegments['route']));
    }
    // Return matched route, params & middlewares
    // if all consumed segments matched
    if ($segmentsConsumed === $uriSegmentCount) {
        if (!empty($matchedPathSegments['route'])) {
            return ["route" => '/' . implode('/', $matchedPathSegments['route']), "segments" => $matchedPathSegments, "params" => $matchedParams, "middlewares" => $matchedMiddlewares];
        }
        // EDGE-CASE: 0 consumed segments,
        // return null instead of matched
        else {
            return null;
        }
    }
    // EDGE-CASES: Return null when impossible(?)/unexpected behavior
    else {
        return null;
    }
    return null;
}

// TRIE ROUTER STARTING POINT: Match Returned Matched Compiled Route With Developer's Defined Route
function funk_match_developer_route(&$c, string $method, string $uri, array $compiledRouteTrie, array $developerSingleRoutes)
{
    // Prepare return values
    $matchedRoute = null;
    $matchedPathSegments = null;
    $matchedRouteParams = null;
    $matchedMiddlewareHandlers = [];
    $routeDefinition = null;
    $noMatchIn = ''; // Use as debug value
    // Try match HTTP Method Key in Compiled Routes
    if (isset($compiledRouteTrie[$method])) {
        $routeDefinition = \funk_match_compiled_route($c, $uri, $compiledRouteTrie[$method]);
    } else {
        $noMatchIn = 'NO MATCH FOR COMPILED_ROUTE_KEY (' . mb_strtoupper($method) . ') & ';
        return false;
    }
    // When Matched Compiled Route, try match Developer's defined route
    if ($routeDefinition !== null) {
        $matchedRoute = $routeDefinition["route"];
        $matchedPathSegments = $routeDefinition["segments"] ?? [];
        $matchedRouteParams = $routeDefinition["params"] ?? null;
        // If Compiled Route Matches Developers Defined Route!
        if (isset($developerSingleRoutes[$method][$routeDefinition["route"]])) {
            $routeInfo = $developerSingleRoutes[$method][$routeDefinition["route"]];
            $noMatchIn = 'ROUTE_MATCHED_BOTH';
            $c['req']['route'] = $matchedRoute;
            $c['req']['segments'] = $matchedPathSegments;
            $c['req']['params'] = $matchedRouteParams;
            $c['req']['matched_in'] = $noMatchIn;
            $c['req']['matched_config'] = $routeInfo['config'] ?? [];
            $c['req']['matched_pipeline'] = $routeInfo['pipeline'] ?? [];
            // Add Any Matched Middlewares
            if (
                isset($routeDefinition["middlewares"])
                && is_array($routeDefinition["middlewares"])
                && !empty($routeDefinition["middlewares"])
            ) {
                // Each 'middlewares' key is an numbered array so
                // we can use array_merge so always keep the order
                foreach ($routeDefinition["middlewares"] as $middleware) {
                    if (
                        isset($developerSingleRoutes[$method][$middleware])
                        && isset($developerSingleRoutes[$method][$middleware]['middlewares'])
                    ) {
                        $matchedMiddlewareHandlers = array_merge($matchedMiddlewareHandlers, $developerSingleRoutes[$method][$middleware]['middlewares']);
                    }
                }
            }
            $c['req']['matched_middlewares'] = $matchedMiddlewareHandlers;
            return true;
        } else {
            $noMatchIn .= 'NO MATCH IN DEVELOPER_ROUTES(funkphp/core/pipeline_routes.php)';
        }
    } else {
        $noMatchIn .= 'NO MATCH IN COMPILED_ROUTES(funkphp/core/compiled_routes.php)';
    }
    // Return all Keys in matched Route and then overwrite some keys that are "hardcoded"
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

/******************************************/
/*** PAGE-RELATED Functions For FunkPHP ***/
/******************************************/
function funk_internal_nonces(&$c, $nonce) {}

function funk_internal_sri_internal(&$c, $nonce) {}

function funk_internal_sri_external(&$c, $nonce) {}

function funk_internal_send_headers(&$c)
{
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

/**************************************
 * CLASSES USED BY FunkPHP FOR IDE $DX!
 *************************************/
/*
 * FunkPHP Classes used in the `/src/funkphp/config/app.php`
*/
/*
 * Class C is the "source of truth" regarding app state, app configuration (globally, on method leve, on route level)
 * such as `request, post-response, routes, middlewares, individual routse and their piped functions`
*/
class C
{
    // ARRAY LISTS of $FORBIDDEN and $ALLOWED
    private array $FORBIDDEN = [
        'headers' => ['set-cookie', 'content-length', 'transfer-encoding', 'connection'],
        'functions_in_regular_functions' => [
            'funk_session_started_or_start_it',
            'funk_session_cookie_set',
            'funk_default_exception_handler',
            'register_shutdown_function',
            'set_exception_handler',
            'set_error_handler',
        ],
    ];
    private array $ALLOWED = [
        'csp-directives' => [ // used by setCSP() (global,method,route)
            'default-src',
            'script-src',
            'script-src-elem',
            'script-src-attr',
            'style-src',
            'style-src-elem',
            'style-src-attr',
            'img-src',
            'font-src',
            'connect-src',
            'media-src',
            'object-src',
            'child-src',
            'frame-src',
            'worker-src',
            'manifest-src',
            'prefetch-src',
            'base-uri',
            'form-action',
            'frame-ancestors',
            'sandbox',
            'require-trusted-types-for',
            'trusted-types',
            'report-uri',
            'report-to'
        ]
    ];
    // The actual written config line by line starting with FunkPHP()
    public array $FunkPHPFluentAPI = [
        'CONFIG' => [],
        'METHODS' => [],
        'ALL' => []
    ];
    // $errors contain all errors + categorized errors
    // $WARNINGS contain warnings meaning compiling/running will happen
    // but developer will be known about possible issues such as dangerous
    // function calls, early exists, evals(), and so on. But they are never stopped
    // unless configured so (if $this->NoWarningsAllowed is set to TRUE).
    private array $errors = [
        'ERRORS' => 0,
        'INTERNAL' => [],
        'CONFIG' => [],
        'METHODS' => []
    ];
    private array $compileErrors = [];
    private array $compileWarnings = [];
    private array $WARNINGS = [];
    private array $compileFlags = [];
    // Valid + Invalid batches, compile() only starts if $invalidBatches is empty!
    private array $validBatches = [];
    private array $invalidBatches = [];
    // $cached = (Attempted) Access to any file/function and/or file=>function in a DRY fashion!
    private array $cached = [
        //'placeholderRoutes' => [],
        'placeholderParamContexts' => [],
        'placeholderUNSUEDParams' => null,
        'placeHolderUsedUserDefinedEngineFNS' => [],
        'placeholderUsedUserDefinedFunctions' => [],
        'placeholderUsedUserDefinedClasses' => [],
        'placeholderMiddlewareInvertIindex' => [],
        'file_user_defined_functions' => null,
        'file_user_defined_classes' => null,
        'file_user_defined_tables' => null,
        'files_pipes_request' => null,
        'files_pipes_post_response' => null,
        'files_pipes_middlewares' => null,
        'files_pipes_routes' => null,
        'files_data_sql' => null,
        'files_data_query' => null,
        'files_data_validation' => null,
        'file_data_sql_compiled' => null,
        'file_data_query_compiled' => null,
        'file_data_validation_compiled' => null,
        'file_core_functions' => null,
        'file_manifest' => null,
    ];
    // Add this later to finalized/compiled $c ($this->compiled['c'])
    // in order to reduce the amount of info on the screen during dev.
    private array $cAddLater = [
        'shared' => [],
        'classes' => ['vendor' => [], 'user' => []],
        'credentials' => null,
        'connections' => [],
        'req' => [
            'method' => '##TOKEN_REQ_METHOD##',
            'ip'     => '##TOKEN_REQ_IP##',
            'time'   => '##TOKEN_REQ_TIME##',
            'uri' => null,
            'query' => '##TOKEN_REQ_QUERY_STRING##',
            'base_url_absolute' => null,
            'base_url_relative' => null,
            'matched_in' => null,
            'route' => null,
            'params' => null,
            'segments' => null,
            'auth' => null,
            'matched_config' => null,
            'matched_pipes' => [],
            'matched_middlewares' => null,
            'skip_post_response' => false,
            'keep_running_exit' => null,
            'code' => 418,
            'log' => [],
            'ua' => null,
            'content_type' => null,
            'accept' => null,
            'protocol' => null,
        ],
        'd' => null,
        'v' => null,
        'v_ok' => null,
        'v_ok_files' => null,
        'v_config' => [],
        'v_data' => null,
        'p' => null,
        'files' => null,
        'err' => [],
    ];
    // $compiled = The entire compiled code that can either be executed as is OR
    // be exported to the `/src/funkphp/FunkPHPDeployment.php` File!
    private array $compiled = [
        'config' => [
            'NO_ROUTE_MATCH' => [],
            'pipes' => ['request' => [], 'middlewares' => [], 'post_response' => [],],
            'params' => [],
            'headers' => [],
            'csp' => [],
            'nonces' => [],
            'INI_SETS' => [],
        ],
        'methods' => [],
        'routes' => ['trie' => [], 'trie_metadata' => []],
        'pages' => [],
        'data' => [],
        // This is the $c Variable that is then assigned automatically globally.
        'c' => [
            'FUNKPHP_ONLINE' => false,
            'FUNKPHP_USE_HTTPS' => false,
            "FUNKPHP_USE_VENDOR" => true,
            "FUNKPHP_CUSTOM_EXCEPTION_HANDLER" => null,
            "FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION" => null,
            "FUNKPHP_CUSTOM_ERROR_HANDLER" => null,
            "FUNKPHP_CUSTOM_URI_NORMALIZER" => null,
            "FUNKPHP_CUSTOM_HTTPS_KERNEL" => null,
            // 'session.cache_limiter' => 'public',
            // 'session.use_strict_mode' => 8,
            // 'session.use_only_cookies' => 1,
            // 'session.cache_expire' => 30,
            // 'session.cookie_lifetime' => 0,
            // 'session.name' => 'fphp_id',
            // 'session.sid_length' => 192,
            // 'session.sid_bits_per_character' => 6,
            // 'display_errors'          => 1,
            // 'display_startup_errors'  => 1,
            // 'error_reporting'         => 1,
            'BASEURLS' => [
                'LOCAL' => null,
                'ONLINE' => null,
                'BASEURL_URI' =>  null,
                'HOST' => null,
            ],
            'SESSION' => [
                'driver' => 'files',
                'COOKIES' => []
            ],
        ],
    ];

    // NAVIGATION VARIABLES+METHODS IN IDE ->config()
    private ?FunkConfig $configScope = null;
    private ?FunkRoutes $routesScope = null;
    // Default booleans for compile(), run()
    private bool $FUNKPHP_COMPILED = false;
    private bool $FUNKPHP_COMPILED_SUCCESS = false;
    private bool $FUNKPHP_RAN = false;
    private array $debug = ['ON_OR_OFF' => false, 'SHOW_VALID_BATCHES' => false, 'SHOW_INVALID_BATCHES' => false, 'SHOW_CACHED' => false, 'SHOW_COMPILED' => false, 'SHOW_ALL' => false,];

    // Helper function to build the $FunkPHPFluentAPI
    // using var_export($var,true). It throws away last optional values like [] & null
    private function exportShortSyntax(mixed $var): string
    {
        if ($var === null) {
            return 'null';
        }
        if (is_bool($var)) {
            return $var ? 'true' : 'false';
        }
        if (is_int($var) || is_float($var)) {
            return (string)$var;
        }
        if (is_string($var)) {
            return var_export($var, true);
        }
        if (is_array($var)) {
            if (empty($var)) {
                return '[]';
            }
            $elements = [];
            $expectedIndex = 0;
            foreach ($var as $key => $val) {
                $exportedValue = $this->exportShortSyntax($val);
                if ($key === $expectedIndex) {
                    $elements[] = $exportedValue;
                    $expectedIndex++;
                } else {
                    $exportedKey = var_export($key, true);
                    $elements[] = "{$exportedKey} => {$exportedValue}";
                }
            }
            return '[' . implode(', ', $elements) . ']';
        }
        return var_export($var, true);
    }
    private function appendFunkPHPFluentAPI(string $methodName, mixed ...$vars): string
    {
        // Pop trailing optional empty arrays/nulls from arguments
        // as they are usually optional default values in most FNs
        while (!empty($vars)) {
            $last = end($vars);
            if ($last === null || (is_array($last) && empty($last))) {
                array_pop($vars);
            } else {
                break;
            }
        }
        $exported = array_map(function ($var) {
            return $this->exportShortSyntax($var);
        }, $vars);
        return '->' . $methodName . '(' . implode(', ', $exported) . ')';
    }
    // Helper function to auto-quote keywords if developer passed
    // unquoted e.g. 'self' -> "'self'" when configuring CSP!
    private function formatCSPSources(array $sources): array
    {
        $keywordsToQuote = [
            'self',
            'none',
            'unsafe-inline',
            'unsafe-eval',
            'strict-dynamic',
            'unsafe-hashes',
            'wasm-unsafe-eval',
            'report-sample',
            'inline-speculation-rules'
        ];
        $cleaned = [];
        foreach ($sources as $source) {
            $trimmed = trim($source);
            if ($trimmed === '') {
                continue;
            }
            if (str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'") && strlen($trimmed) > 2) {
                $cleaned[] = $trimmed;
                continue;
            }
            // $keywordsToQuote are all lowercased so check if current one is that one needing the ''-wrap
            $lower = strtolower($trimmed);
            if (in_array($lower, $keywordsToQuote, true)) {
                $cleaned[] = "'{$lower}'";
            } else {
                $cleaned[] = $trimmed;
            }
        }
        return array_values(array_unique($cleaned));
    }
    /* !!! SMALL HELPER FUNCTIONS FOR $this->cached and all File I/O !!! */
    // ROOT_FOLDER constant must exist as string ending with `src/funkphp`
    private function rootFolderExistOrSetError(): bool
    {
        if (
            !defined("ROOT_FOLDER")
            || (!is_string(ROOT_FOLDER))
            || trim(ROOT_FOLDER) === ''
            || !str_ends_with(ROOT_FOLDER, 'src/funkphp')
        ) {
            $err = "`[Class C->rootFolderExistOrSetError() in /src/funkphp/core/functions.php]:` Expected `ROOT_FOLDER` Constant Not Defined or is not ending with `src/funkphp` as a Non-Empty String. It is supposed to be defined in `/src/funkphp/core/CONSTANTS.php`. Verify the integrity of that File.";
            $this->errors[] = ['type' => 'internal', 'err' => $err];
            return false;
        }
        return true;
    }
    // Most Strings must be Non-Empty & Lowercased!
    private function nonEmptyLowercaseStrNotStartWithCLIorFunk(string $str): bool
    {
        if (
            !is_string($str) || trim($str) === ''
            || ($str !== strtolower($str))
            || !preg_match('/^[a-z_][a-z0-9_]*$/', $str)
            || (str_starts_with($str, 'cli_'))
            || (str_starts_with($str, 'funk_'))
        ) {
            return false;
        }
        return true;
    }
    // Validate it is "filename.fnname" (both File & FN must have FN naming convention)
    private function nonEmptyLowercaseStrThatIsFileAndFunctionWithDot(string $str): bool
    {
        if (
            !is_string($str) || trim($str) === ''
            || ($str !== strtolower($str))
            || (str_starts_with($str, 'cli_'))
            || (str_starts_with($str, 'funk_'))
            || !preg_match('/^[a-z_][a-z0-9_]*\.[a-z_][a-z0-9_]*$/', $str)
        ) {
            return false;
        }
        return true;
    }
    // Validate it is either: (group:groupName) OR a valid FN Name
    private function nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk(string $str): bool
    {
        if (
            !is_string($str) || trim($str) === ''
            || ($str !== strtolower($str))
            || !preg_match('/^((group:)?[a-z_][a-z0-9_]*)$/', $str)
            || (str_starts_with($str, 'cli_'))
            || (str_starts_with($str, 'funk_'))
        ) {
            return false;
        }
        return true;
    }
    // Validate it is either: (group:GroupName) OR a valid FileFN Name (fileName.functionName)
    private function nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk(string $str): bool
    {
        if (
            !is_string($str) || trim($str) === ''
            || ($str !== strtolower($str))
            || !preg_match('/^(group:[a-z_][a-z0-9_]*)|([a-z_][a-z0-9_]*\.[a-z_][a-z0-9_]*)$/', $str)
            || (str_starts_with($str, 'cli_'))
            || (str_starts_with($str, 'funk_'))
        ) {
            return false;
        }
        return true;
    }
    // Autoload any non-existing $this->cached[$key] that is either always a file with functions OR classes
    private function cachedCreateKeyIfNullAndOptionalFileName(string $key, string $optionalFileName = '1_NO_FILE_NAME_PROVIDED_1'): void
    {
        if ($key === 'file_user_defined_functions') {
            $this->cached[$key] = $this->file_status('/config', 'functions');
        } elseif ($key === 'file_user_defined_classes') {
            $this->cached[$key] = $this->file_status('/config', 'classes');
        } elseif ($key === 'file_user_defined_tables') {
            $this->cached[$key] = $this->file_status('/config', 'tables');
        } elseif ($key === 'files_pipes_request') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/request', $optionalFileName);
            }
        } elseif ($key === 'files_pipes_post_response') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/post_response', $optionalFileName);
            }
        } elseif ($key === 'files_pipes_middlewares') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/middlewares', $optionalFileName);
            }
        } elseif ($key === 'files_routes') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/routes', $optionalFileName);
            }
        } elseif ($key === 'files_pages') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages', $optionalFileName);
            }
        } elseif ($key === 'files_pages_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages/compiled', $optionalFileName);
            }
        } elseif ($key === 'files_data_sql') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/sql', $optionalFileName);
            }
        } elseif ($key === 'files_data_query') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/query', $optionalFileName);
            }
        } elseif ($key === 'files_data_validation') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/validation', $optionalFileName);
            }
        } elseif ($key === 'files_data_sql_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/compiled/sql', $optionalFileName);
            }
        } elseif ($key === 'files_data_query_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/compiled/query', $optionalFileName);
            }
        } elseif ($key === 'files_data_validation_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/compiled/validation', $optionalFileName);
            }
        } elseif ($key === 'file_core_functions') {
            $this->cached[$key] = $this->file_status('/core', 'functions');
        } elseif ($key === 'file_manifest') {
            $this->cached[$key] = $this->file_status('/core', 'manifest');
        } else {
            $err = "[Class C->\$this->cachedCreateKeyIfNull()]: Unknown `{$key}` Value passed when it expected one of those defined in \$this->cached in Class C. Report this Internal Error to the Official FunkPHP repository.";
            $this->errors[] = ['type' => 'internal', 'err' => $err];
        }
        return;
    }
    // Function find either a Key=>FN|CLASS OR Key=>File=>FN|CLASS

    /* AUTO-LOAD AND CHECK IF FILES EXIST (Does NOT check actual FNs except UserDefined One!) */
    private function cachedPageFileCOMPILEDExists(string $page): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages_compiled', $page);
        $pageFound = false;
        if (
            isset($this->cached['files_pages_compiled'][$page]['file_exists'])
            && $this->cached['files_pages_compiled'][$page]['file_exists'] === true
        ) {
            $pageFound = true;
        }
        return $pageFound;
    }
    private function cachedPageFileNOT_COMPILEDExists(string $page): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages', $page);
        $pageFound = false;
        if (
            isset($this->cached['files_pages'][$page]['file_exists'])
            && $this->cached['files_pages'][$page]['file_exists'] === true
        ) {
            $pageFound = true;
        }
        return $pageFound;
    }
    private function cachedPageFileEITHER_TYPEExists(string $page): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages_compiled', $page);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages', $page);
        $pageFound = false;
        if (
            isset($this->cached['files_pages'][$page]['file_exists'])
            && $this->cached['files_pages'][$page]['file_exists'] === true
        ) {
            $pageFound = true;
        } else if (
            isset($this->cached['files_pages_compiled'][$page]['file_exists'])
            && $this->cached['files_pages_compiled'][$page]['file_exists'] === true
        ) {
            $pageFound = true;
        }
        return $pageFound;
    }
    private function cachedMiddlewareFileExists(string $middleware): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
        $middlewareFound = false;
        if (
            isset($this->cached['files_pipes_middlewares'][$middleware]['file_exists'])
            && $this->cached['files_pipes_middlewares'][$middleware]['file_exists'] === true
        ) {
            $middlewareFound = true;
        }
        return $middlewareFound;
    }
    private function cachedRequestFileExists(string $requestFN): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_request', $requestFN);
        $requestFNFound = false;
        if (
            isset($this->cached['files_pipes_request'][$requestFN]['file_exists'])
            && $this->cached['files_pipes_request'][$requestFN]['file_exists'] === true
        ) {
            $requestFNFound = true;
        }
        return $requestFNFound;
    }
    private function cachedPostResponseFileExists(string $postResponseFN): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_post_response', $postResponseFN);
        $postResponseFNFound = false;
        if (
            isset($this->cached['files_pipes_post_response'][$postResponseFN]['file_exists'])
            && $this->cached['files_pipes_post_response'][$postResponseFN]['file_exists'] === true
        ) {
            $postResponseFNFound = true;
        }
        return $postResponseFNFound;
    }
    private function cachedRoutesFileExists(string $routesFile): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_routes', $routesFile);
        $routesFileFound = false;
        if (
            isset($this->cached['files_routes'][$routesFile]['file_exists'])
            && $this->cached['files_routes'][$routesFile]['file_exists'] === true
        ) {
            $routesFileFound = true;
        }
        return $routesFileFound;
    }
    private function cachedQueryFileExists(string $QueryFile): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_data_query', $QueryFile);
        $fileFound = false;
        if (
            isset($this->cached['files_data_query'][$QueryFile]['file_exists'])
            && $this->cached['files_data_query'][$QueryFile]['file_exists'] === true
        ) {
            $fileFound = true;
        }
        return $fileFound;
    }
    private function cachedSQLFileExists(string $SQLFile): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_data_sql', $SQLFile);
        $fileFound = false;
        if (
            isset($this->cached['files_data_sql'][$SQLFile]['file_exists'])
            && $this->cached['files_data_sql'][$SQLFile]['file_exists'] === true
        ) {
            $fileFound = true;
        }
        return $fileFound;
    }
    private function cachedValidationFileExists(string $ValidationFile): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_data_validation', $ValidationFile);
        $fileFound = false;
        if (
            isset($this->cached['files_data_validation'][$ValidationFile]['file_exists'])
            && $this->cached['files_data_validation'][$ValidationFile]['file_exists'] === true
        ) {
            $fileFound = true;
        }
        return $fileFound;
    }
    private function cachedUserDefinedFNExists(string $userDefinedFunction): bool
    {
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $FNFound = false;
        if (
            isset($this->cached['file_user_defined_functions']['file_exists'])
            && $this->cached['file_user_defined_functions']['file_exists'] === true
        ) {
            if (isset($this->cached['file_user_defined_functions']['functions'][strtolower(trim($userDefinedFunction))])) {
                $FNFound = true;
            }
        }
        return $FNFound;
    }
    // These 2 functions check things like eval(), early exit(), which can be used to inform
    // developer about possible dangerous code but it is only emitted as warnings - nothing else.
    // They set the warnings for a given FN|CLASS and if the boolean $this->NoWarningsAllowed is
    // set to TRUE then it would now contain warnings that could stop compiling/run if set TRUE.
    private function cachedKeyFNWarnings(array $FNKeyFromCachedKey, string $exactFilePath = '***No File Path Given***'): void
    {
        if (array_is_list($FNKeyFromCachedKey) && count($FNKeyFromCachedKey) > 0) {
            $err = "[Class C->cachedKeyFNWarnings()]: A Numbered Array passed when expected an Associative Array to validate using its Key-Value pairs.";
            $this->errors[] = $err;
        }
        // Validate OR add warnings if FN is safe by checking certain Key Values
        else {
            $dangerousCalls = ['shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec'];
            if (
                isset($FNKeyFromCachedKey['args_raw'])
                && !str_starts_with($FNKeyFromCachedKey['args_raw'], '&$c')
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` does NOT start with `&\$c` as its first Parameter.";
            }
            if (
                isset($FNKeyFromCachedKey['body_raw'])
                && $FNKeyFromCachedKey['body_raw'] === '{}'
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` seems to have an Empty Body `{}`.";
            }
            if (
                isset($FNKeyFromCachedKey['only_whitespace_and_or_comments'])
                && $FNKeyFromCachedKey['only_whitespace_and_or_comments'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` seems to have a Body only filled with Whitespace and/or Comments.";
            }
            if (
                isset($FNKeyFromCachedKey['has_inner_functions'])
                && $FNKeyFromCachedKey['has_inner_functions'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` has `Inner Function Declarations` on lines:`" . join(', ', $FNKeyFromCachedKey['nested_function_lines']) . '`.';
            }
            if (
                isset($FNKeyFromCachedKey['has_exit'])
                && $FNKeyFromCachedKey['has_exit'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` has early `exit()` on lines:`" . join(', ', $FNKeyFromCachedKey['exit_lines']) . '`.';
            }
            if (
                isset($FNKeyFromCachedKey['has_raw_output'])
                && $FNKeyFromCachedKey['has_raw_output'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` has `echo` OR similar raw output calls on lines:`" . join(', ', $FNKeyFromCachedKey['raw_output_lines']) . '`.';
            }
            if (
                isset($FNKeyFromCachedKey['has_eval'])
                && $FNKeyFromCachedKey['has_eval'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` has `eval()` on lines:`" . join(', ', $FNKeyFromCachedKey['eval_lines']) . '`.';
            }
            if (
                isset($FNKeyFromCachedKey['has_globals'])
                && $FNKeyFromCachedKey['has_globals'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` has `global` keyword usage on following variables:`" . join(', ', $FNKeyFromCachedKey['global_vars']) . '`.';
            }
            if (
                isset($FNKeyFromCachedKey['has_dangerous_calls'])
                && $FNKeyFromCachedKey['has_dangerous_calls'] === true
            ) {
                $this->WARNINGS[] = "Function `{$FNKeyFromCachedKey['fn_exact_name']}` in `{$exactFilePath}` might have one or more `Dangerous Function Calls` from this list:`" . join(', ', $dangerousCalls) . '`.';
            }
        }
    }
    private function cachedKeyCLASSWarnings(array $CLASSKeyFromCachedKey, string $exactFilePath = ''): void
    {
        if (array_is_list($CLASSKeyFromCachedKey) && count($CLASSKeyFromCachedKey) > 0) {
            $err = "[Class C->cachedKeyCLASSWarnings()]: A Numbered Array passed when expected an Associative Array to validate using its Key-Value pairs.";
            $this->errors[] = ['type' => 'internal', 'err' => $err];
        }
        // Validate OR add warnings if CLASS is safe by checking certain Key Values
        else {
        }
    }
    /*** !!! PRIVATE HELPER FUNCTIONS FOR MANY batch<VARIANTS> ABOVE !!! */
    // Also used by compile() & run() below!
    private function file_status(string $folder, string $file, bool $useExactFilePathInstead = false, bool $deeperAnalysis = false)
    {
        if (!$useExactFilePathInstead) {
            if (is_string($folder) && str_starts_with(trim($folder), "/")) {
                $folder = substr(trim($folder), 1);
            }
        }
        $folder = trim($folder);
        $providedFolder = $folder;
        $file = trim($file);
        if (str_ends_with($folder, '/')) {
            $folder = rtrim($folder, '/');
        }
        if (!str_ends_with($file, '.php')) {
            $file .= '.php';
        }
        if (str_starts_with($file, '/')) {
            $file = ltrim($file, '/');
        }
        $folder = ($useExactFilePathInstead === false) ? (ROOT_FOLDER . '/' . $folder) : $folder;
        $singleFolder = basename($folder);
        $filename = $file;
        $file = $folder . '/' . $file;
        $fileRaw = null;
        $namespace = null;
        $namespaceParts = null;
        $fileUse = [];
        $fns = [];
        $fnames_only = [];
        $fnames_duplicates = [];
        $classes = [];
        $clnames_only = [];
        $clnames_duplicates = [];
        $NO_FN_START_CLI = true;
        $NO_FN_START_FUNK = true;
        if (is_file($file) && is_readable($file)) {
            $fileCnt = file_get_contents($file);
            if ($fileCnt !== false) {
                $fileRaw = $fileCnt;
                $syntaxValid = true;
                $syntaxError = null;
                try {
                    \PhpToken::tokenize($fileRaw, TOKEN_PARSE);
                } catch (\ParseError | \CompileError $e) {
                    $syntaxValid = false;
                    $syntaxError = $e->getMessage() . " on line " . $e->getLine();
                }
                if ($syntaxValid) {
                    $nsAndUses = $this->file_harvest_namespace_and_uses_from_code($fileRaw);
                    $namespace = $nsAndUses['namespace'];
                    $namespaceParts = $nsAndUses['namespace_parts'];
                    $fileUse = $nsAndUses['file_use'];
                    $tokenizedFns = $this->file_harvest_all_functions_from_code($fileRaw);
                    foreach ($tokenizedFns as $fnName => $fnData) {
                        $fns[$fnName] = array_merge($fnData, [
                            'VALID_FN_FOR_FUNKPHP'          => (!$fnData['has_inner_functions']
                                && !$fnData['only_whitespace_and_or_comments']
                                && !str_starts_with(strtolower(trim($fnName)), 'cli_')
                                && !str_starts_with(strtolower(trim($fnName)), 'funk_')
                                && (strtolower(trim($fnName)) !== 'dd')),
                            'fn_name_same_as_lowercased'  => ($fnName === strtolower($fnName)),
                            'fn_uppercased'               => strtoupper($fnName),
                            'fn_starts_with_cli'          => str_starts_with(strtolower($fnName), 'cli_'),
                            'fn_starts_with_funk'         => str_starts_with(strtolower($fnName), 'funk_'),
                        ]);
                        if (in_array(strtolower($fnName), $fnames_only, true)) {
                            $fnames_duplicates[$fnName] = true;
                        }
                        $fnames_only[] = $fnName;
                        if ($fns[$fnName]['fn_starts_with_cli']) $NO_FN_START_CLI = false;
                        if ($fns[$fnName]['fn_starts_with_funk']) $NO_FN_START_FUNK = false;
                    }
                    $tokenizedClasses = $this->file_harvest_all_classes_from_code($fileRaw);
                    foreach ($tokenizedClasses as $className => $classData) {
                        $classes[$className] = $classData;
                        if (in_array(strtolower($className), $clnames_only, true)) {
                            $clnames_duplicates[$className] = true;
                        }
                        $clnames_only[] = $className;
                    }
                }
            } else {
                $this->errors[] = ['type' => 'internal', 'err' => "Class C->file_status()] - FAILED to read Folder+File Path:`{$folder}{$file}` when it should have been possible. Verify Folder/File Permissions in Your Project."];
                return ['INTERNAL_FUNKPHP_ERROR' => "[INTERNAL FUNKPHP ERROR - file_status()] - FAILED to read Folder+File Path:`{$folder}{$file}` when it should have been possible. Verify Folder/File Permissions in Your Project."];
            }
        }
        return [
            'syntax_valid'          => $syntaxValid ?? false,
            'syntax_error'          => $syntaxError ?? null,
            'namespace'             => $namespace,
            'namespace_parts'       => $namespaceParts,
            'file_use'              => $fileUse,
            'functions'             => $fns,
            'classes'               => $classes,
            'file_raw'              => $fileRaw,
            'functions_exist'       => count($fns) > 0,
            'classes_exist'         => count($classes) > 0,
            'file_readable'         => is_readable($file),
            'file_exists'           => is_file($file),
            'folder_provided_path'  => $providedFolder,
            'folder_name'           => $singleFolder,
            'folder_path'           => (is_dir($folder) && is_readable($folder)) ? $folder : null,
            'folder_exists'         => is_dir($folder),
            'file_name'             => $filename,
            'file_path'             => (is_file($file) && is_readable($file)) ? $file : null,
            'fn_names_only'         => $fnames_only,
            'fn_names_duplicates'   => $fnames_duplicates,
            'class_names_only'      => $clnames_only,
            'class_names_duplicates' => $clnames_duplicates,
            'no_fn_starts_with_cli' => $NO_FN_START_CLI,
            'no_fn_starts_with_funk' => $NO_FN_START_FUNK,
        ];
    }
    // Helper function to `file_status` but can also be used
    // without using that one to get an array of regular function declarations!
    // like "function name1(){}, function name2(){}" and so on within same file!
    private function file_harvest_all_functions_from_code(string $code): array
    {
        $tokens = PhpToken::tokenize($code);
        $count = count($tokens);
        $harvested = [];
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->id !== T_FUNCTION) {
                continue;
            }
            $curr = $i + 1;
            $isByRef = false;
            while ($curr < $count && ($tokens[$curr]->id === T_WHITESPACE || $tokens[$curr]->text === '&')) {
                if ($tokens[$curr]->text === '&') {
                    $isByRef = true;
                }
                $curr++;
            }
            if ($curr >= $count || $tokens[$curr]->id !== T_STRING) {
                continue;
            }
            $fnName = $tokens[$curr]->text;
            $fnTokenPos = $tokens[$i]->pos;
            $startLine = $tokens[$i]->line;
            // DocComments
            $commentStartPos = $fnTokenPos;
            $collectedComments = [];
            $back = $i - 1;
            while ($back >= 0) {
                $tok = $tokens[$back];
                if ($tok->id === T_WHITESPACE) {
                    $back--;
                    continue;
                }
                if ($tok->id === T_DOC_COMMENT || $tok->id === T_COMMENT) {
                    array_unshift($collectedComments, $tok->text);
                    $commentStartPos = $tok->pos;
                    $back--;
                    continue;
                }
                break;
            }
            $docComment = !empty($collectedComments) ? implode("\n", $collectedComments) : null;
            // Extract Arguments
            $argStartTok = $curr + 1;
            while ($argStartTok < $count && $tokens[$argStartTok]->text !== '(' && $tokens[$argStartTok]->text !== '{' && $tokens[$argStartTok]->text !== ';') {
                $argStartTok++;
            }
            $argsRaw = '';
            $bodySearchTokIdx = $argStartTok;
            if ($argStartTok < $count && $tokens[$argStartTok]->text === '(') {
                $parenDepth = 1;
                $argTokens = [];
                for ($j = $argStartTok + 1; $j < $count; $j++) {
                    if ($tokens[$j]->text === '(') $parenDepth++;
                    elseif ($tokens[$j]->text === ')') $parenDepth--;
                    if ($parenDepth === 0) {
                        $bodySearchTokIdx = $j + 1;
                        break;
                    }
                    $argTokens[] = $tokens[$j]->text;
                }
                $argsRaw = trim(implode('', $argTokens));
            }
            // Body '{' lookup
            while ($bodySearchTokIdx < $count && $tokens[$bodySearchTokIdx]->text !== '{' && $tokens[$bodySearchTokIdx]->text !== ';') {
                $bodySearchTokIdx++;
            }
            if ($bodySearchTokIdx >= $count || $tokens[$bodySearchTokIdx]->text === ';') {
                continue;
            }
            $bodyStartPos = $tokens[$bodySearchTokIdx]->pos;
            $braceDepth = 0;
            $hasStartedBody = false;
            $bodyEndPos = -1;
            $lastTokenIdx = $i;
            for ($j = $bodySearchTokIdx; $j < $count; $j++) {
                $tok = $tokens[$j];
                if ($tok->text === '{') {
                    $braceDepth++;
                    $hasStartedBody = true;
                } elseif ($tok->text === '}') {
                    $braceDepth--;
                }
                if ($hasStartedBody && $braceDepth === 0) {
                    $bodyEndPos = $tok->pos + strlen($tok->text);
                    $lastTokenIdx = $j;
                    break;
                }
            }
            if ($bodyEndPos === -1) {
                continue;
            }
            $fnRawWithDoc = substr($code, $commentStartPos, $bodyEndPos - $commentStartPos);
            $fnRaw        = substr($code, $fnTokenPos, $bodyEndPos - $fnTokenPos);
            $bodyRaw      = substr($code, $bodyStartPos, $bodyEndPos - $bodyStartPos);
            // Run analysis on the body
            $analysis = $this->file_analyze_body_tokens($bodyRaw, $startLine);
            $harvested[$fnName] = array_merge([
                'fn_exact_name'   => $fnName,
                'fn_lowercased'   => strtolower($fnName),
                'doc_comment'     => $docComment,
                'args_raw'        => $argsRaw,
                'body_raw'        => $bodyRaw,
                'fn_raw'          => $fnRaw,
                'fn_raw_with_doc' => $fnRawWithDoc,
                'is_by_ref'       => $isByRef,
                'line_start'      => $startLine,
            ], $analysis);
            $i = $lastTokenIdx;
        }
        return $harvested;
    }
    // Helper function to `file_status` but can also be used
    // without using that one to get an array of regular class declarations!
    // like "class name1(){}, class name2(){}" and so on within same file!
    private function file_harvest_all_classes_from_code(string $code): array
    {
        $tokens = PhpToken::tokenize($code);
        $count = count($tokens);
        $harvested = [];
        $dangerousFuncs = ['shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec'];
        $dangerousCalls = [];
        $braceDepth = 0;
        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];
            if ($tok->text === '{') {
                $braceDepth++;
            } elseif ($tok->text === '}') {
                $braceDepth--;
            }
            if ($tok->id === T_CLASS) {
                // Skip anonymous classes: "new class {}"
                $prevIdx = $i - 1;
                while ($prevIdx >= 0 && $tokens[$prevIdx]->id === T_WHITESPACE) {
                    $prevIdx--;
                }
                if ($prevIdx >= 0 && $tokens[$prevIdx]->id === T_NEW) {
                    continue;
                }
                // Capture class name
                $nameIndex = $i + 1;
                while ($nameIndex < $count && $tokens[$nameIndex]->id === T_WHITESPACE) {
                    $nameIndex++;
                }
                if ($nameIndex >= $count || $tokens[$nameIndex]->id !== T_STRING) {
                    continue;
                }
                $className = $tokens[$nameIndex]->text;
                $classTokenPos = $tok->pos;
                $startLine = $tok->line;
                // Capture DocComments backward
                $commentStartPos = $classTokenPos;
                $collectedComments = [];
                $back = $i - 1;
                while ($back >= 0) {
                    $btok = $tokens[$back];
                    if ($btok->id === T_WHITESPACE) {
                        $back--;
                        continue;
                    }
                    if ($btok->id === T_DOC_COMMENT || $btok->id === T_COMMENT) {
                        array_unshift($collectedComments, $btok->text);
                        $commentStartPos = $btok->pos;
                        $back--;
                        continue;
                    }
                    break;
                }
                $docComment = !empty($collectedComments) ? implode("\n", $collectedComments) : null;
                // Parse inheritance (extends & implements) until '{'
                $bodySearchIdx = $nameIndex + 1;
                $extends = null;
                $implements = [];
                while ($bodySearchIdx < $count && $tokens[$bodySearchIdx]->text !== '{') {
                    if ($tokens[$bodySearchIdx]->id === T_EXTENDS) {
                        $eIdx = $bodySearchIdx + 1;
                        while ($eIdx < $count && $tokens[$eIdx]->id === T_WHITESPACE) {
                            $eIdx++;
                        }
                        if ($eIdx < $count) {
                            $extends = $tokens[$eIdx]->text;
                        }
                    }
                    if ($tokens[$bodySearchIdx]->id === T_IMPLEMENTS) {
                        for ($impIdx = $bodySearchIdx + 1; $impIdx < $count; $impIdx++) {
                            if ($tokens[$impIdx]->text === '{') break;
                            if ($tokens[$impIdx]->id === T_STRING || $tokens[$impIdx]->id === T_NAME_QUALIFIED) {
                                $implements[] = $tokens[$impIdx]->text;
                            }
                        }
                    }
                    $bodySearchIdx++;
                }
                if ($bodySearchIdx >= $count) {
                    continue;
                }
                $bodyStartPos = $tokens[$bodySearchIdx]->pos;
                $classBraceDepth = 0;
                $hasStartedBody = false;
                $hasEval = false;
                $hasDangerousCalls = false;
                $hasExit = false;
                $bodyEndPos = -1;
                $lastTokenIdx = $i;
                // Walk body for top-level metrics and boundaries
                for ($j = $bodySearchIdx; $j < $count; $j++) {
                    $ctok = $tokens[$j];
                    if ($ctok->text === '{') {
                        $classBraceDepth++;
                        $hasStartedBody = true;
                    } elseif ($ctok->text === '}') {
                        $classBraceDepth--;
                    }
                    if ($hasStartedBody && $classBraceDepth >= 1) {
                        if ($ctok->id === T_EVAL) {
                            $hasEval = true;
                        }
                        if ($ctok->id === T_EXIT) {
                            $hasExit = true;
                        }
                        if ($ctok->id === T_STRING && in_array(strtolower($ctok->text), $dangerousFuncs, true)) {
                            $hasDangerousCalls = true;
                            $dagnerousCalls[] = ['call' => $ctok->text, 'line' => $ctok->line];
                        }
                    }
                    if ($hasStartedBody && $classBraceDepth === 0) {
                        $bodyEndPos = $ctok->pos + strlen($ctok->text);
                        $lastTokenIdx = $j;
                        break;
                    }
                }
                if ($bodyEndPos === -1) {
                    continue;
                }
                $classRawWithDoc = substr($code, $commentStartPos, $bodyEndPos - $commentStartPos);
                $classRaw        = substr($code, $classTokenPos, $bodyEndPos - $classTokenPos);
                $bodyRaw         = substr($code, $bodyStartPos, $bodyEndPos - $bodyStartPos);
                // Deep-analyze class members via file_analyze_class_tokens
                $classStructureAnalysis = $this->file_analyze_class_tokens($bodyRaw, $startLine);
                $harvested[$className] = [
                    'class_name'              => $className,
                    'class_name_ucfirst'      => (ucfirst($className) === $className),
                    'doc_comment'             => $docComment,
                    'extends'                 => $extends,
                    'implements'              => $implements,
                    'traits_used'             => $classStructureAnalysis['traits_used'],
                    'constants'               => $classStructureAnalysis['constants'],
                    'properties'              => $classStructureAnalysis['properties'],
                    'methods'                 => $classStructureAnalysis['methods'],
                    'body_raw'                => $bodyRaw,
                    'class_raw'               => $classRaw,
                    'class_raw_with_doc'      => $classRawWithDoc,
                    'line_start'              => $startLine,
                    'has_eval'                => $hasEval,
                    'has_dangerous_calls'     => $hasDangerousCalls,
                    'dangerous_calls'         => $dangerousCalls,
                    'has_exit'                => $hasExit,
                    'class_starts_with_cli'   => str_starts_with(strtolower($className), 'cli_'),
                    'class_starts_with_funk'  => str_starts_with(strtolower($className), 'funk_'),
                ];
                $i = $lastTokenIdx;
            }
        }
        return $harvested;
    }
    // Helper function gets the namespace and use from raw code string
    private function file_harvest_namespace_and_uses_from_code(string $code): array
    {
        $tokens = PhpToken::tokenize($code);
        $count = count($tokens);
        $namespace = null;
        $namespaceParts = null;
        $fileUse = [];
        $braceDepth = 0;
        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];
            if ($tok->text === '{') {
                $braceDepth++;
            } elseif ($tok->text === '}') {
                $braceDepth--;
            }
            // Only process file-level declarations (outside any function/class body)
            if ($braceDepth === 0) {
                // 1. Namespace Parser
                if ($tok->id === T_NAMESPACE) {
                    $nsTokens = [];
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($tokens[$j]->text === ';' || $tokens[$j]->text === '{') {
                            break;
                        }
                        if ($tokens[$j]->id !== T_WHITESPACE) {
                            $nsTokens[] = $tokens[$j]->text;
                        }
                    }
                    $nsString = trim(implode('', $nsTokens));
                    if ($nsString !== '') {
                        $namespace = $nsString;
                        $namespaceParts = explode('\\', $nsString);
                    }
                }
                // 2. Use Statements Parser ('file_use')
                if ($tok->id === T_USE) {
                    $useStartPos = $tok->pos;
                    $useEndPos = -1;
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($tokens[$j]->text === ';') {
                            $useEndPos = $tokens[$j]->pos + 1;
                            $i = $j; // Fast-forward outer loop past ';'
                            break;
                        }
                    }
                    if ($useEndPos !== -1) {
                        $rawUse = trim(substr($code, $useStartPos, $useEndPos - $useStartPos));
                        // Clean statement removing 'use ' prefix and ';' suffix
                        $cleanUse = preg_replace('/^use\s+/i', '', rtrim($rawUse, ';'));

                        $fileUse[] = [
                            'raw'   => $rawUse,
                            'clean' => trim($cleanUse),
                        ];
                    }
                }
            }
        }
        return [
            'namespace'       => $namespace,
            'namespace_parts' => $namespaceParts,
            'file_use'        => $fileUse,
        ];
    }
    // Helper function (must get code as string) that can analyze already
    // loaded PHP code for safety by providing any functions a function
    // and/or class is using to compare against (dis)allowed functions and so on!
    private function file_analyze_body_tokens(string $bodyCode, int $startLine = 1, array $dangerousFNsDeclared = ['shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'base64_decode']): array
    {
        $tokens = PhpToken::tokenize("<?php " . $bodyCode);
        $count = count($tokens);
        $dangerousFuncs = (!empty($dangerousFNsDeclared) ? $dangerousFNsDeclared :  ['shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'base64_decode']);
        $invalidFunkCallsInFNs = $this->FORBIDDEN['functions_in_regular_functions'];
        $hasSetExceptionHandler = false;
        $hasSetErrorHandler = false;
        $hasSetRegisterShutdownFunction = false;
        $firstSignificantTokenId = null;
        $firstSignificantTokenText = null;
        $startsWithReturn = false;
        $hasReturn = false;
        $returns = [];
        $hasExit = false;
        $exitLines = [];
        $hasInlineHtml = false;
        $inlineHtmlLines = [];
        $hasRawOutput = false;
        $rawOutputLines = [];
        $hasEval = false;
        $evalLines = [];
        $evalValues = [];
        $hasInnerFunctions = false;
        $innerFunctionLines = [];
        $hasInnerClasses = false;
        $innerClassLines = [];
        $hasGlobals = false;
        $globalVars = [];
        $hasDangerousCalls = false;
        $dangerousCalls = [];
        $hasOnlyCommentsOrWhiteSpace = true;
        $hasVariableVars = false;
        $calls = [];
        $funkCalls = [];
        $hasInvalidFunkCalls = false;
        $invalidFunkCalls = [];
        $lineOffset = $startLine;
        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];
            $line = $tok->line + $lineOffset;
            // Find first significant token and store info if "return" statement is first.
            $isIgnoredToken = (
                $tok->text === '{' ||
                $tok->text === '}' ||
                $tok->id === T_OPEN_TAG ||
                $tok->id === T_CLOSE_TAG ||
                $tok->id === T_COMMENT ||
                $tok->id === T_DOC_COMMENT ||
                $tok->id === T_WHITESPACE
            );
            if (!$isIgnoredToken) {
                $hasOnlyCommentsOrWhiteSpace = false;
                if ($firstSignificantTokenId === null) {
                    $firstSignificantTokenId   = $tok->id;
                    $firstSignificantTokenText = $tok->text;
                    $startsWithReturn          = ($tok->id === T_RETURN);
                }
            }
            // Only whitespace?
            if (
                $tok->text !== '{' &&
                $tok->text !== '}' &&
                $tok->id !== T_OPEN_TAG &&
                $tok->id !== T_CLOSE_TAG &&
                $tok->id !== T_COMMENT &&
                $tok->id !== T_DOC_COMMENT &&
                $tok->id !== T_WHITESPACE
            ) {
                $hasOnlyCommentsOrWhiteSpace = false;
            }
            // 1. Early Exits (exit / die)
            if ($tok->id === T_EXIT) {
                $hasExit = true;
                $exitLines[] = $line;
                continue;
            }
            // 2. Raw Output Dumps
            if ($tok->id === T_ECHO || $tok->id === T_PRINT) {
                $hasRawOutput = true;
                $rawOutputLines[] = $line;
                continue;
            }
            if ($tok->id === T_INLINE_HTML) {
                if (trim($tok->text) !== '') {
                    $hasInlineHtml = true;
                    $inlineHtmlLines[] = $line;
                }
                continue;
            }
            // 3. Eval / Dynamic Code
            if ($tok->id === T_EVAL) {
                $hasEval = true;
                $evalLines[] = $line;
                $evalTokens = [];
                $parenDepth = 0;
                while (++$i < $count) {
                    $subTok = $tokens[$i];
                    if ($subTok->text === '(') $parenDepth++;
                    if ($subTok->text === ')') {
                        $parenDepth--;
                        if ($parenDepth === 0) break;
                    }
                    $evalTokens[] = $subTok;
                }
                $evalPayload = trim(implode('', array_column($evalTokens, 'text')));
                $evalValues[] = [
                    'line' => $line,
                    'payload' => $evalPayload,
                    'has_variable' => str_contains($evalPayload, '$')
                ];
                continue;
            }
            // 4. Nested Functions (Named vs Anonymous/Closures)
            if ($tok->id === T_FUNCTION || (defined('T_FN') && $tok->id === T_FN)) {
                $nextIdx = $i + 1;
                while ($nextIdx < $count && (
                    $tokens[$nextIdx]->id === T_WHITESPACE ||
                    $tokens[$nextIdx]->id === T_COMMENT ||
                    $tokens[$nextIdx]->id === T_DOC_COMMENT ||
                    $tokens[$nextIdx]->text === '&'
                )) {
                    $nextIdx++;
                }
                if ($nextIdx < $count && $tokens[$nextIdx]->id === T_STRING) {
                    $hasInnerFunctions = true;
                    $innerFunctionLines[] = $line;
                    continue;
                }
                $hasClosures = true;
                $closureLines[] = $line;
                continue;
            }
            // 5. Nested Classes
            if ($tok->id === T_CLASS) {
                $hasInnerClasses = true;
                $innerClassLines[] = $line;
                continue;
            }
            // 6. Global State Inspection ($GLOBALS or global $a)
            if ($tok->id === T_GLOBAL) {
                $hasGlobals = true;
                for ($g = $i + 1; $g < $count; $g++) {
                    if ($tokens[$g]->text === ';') break;
                    if ($tokens[$g]->id === T_VARIABLE) {
                        $globalVars[] = $tokens[$g]->text;
                    }
                }
            }
            // 7. Variable Variables ($$foo)
            if ($tok->text === '$' && isset($tokens[$i + 1]) && ($tokens[$i + 1]->id === T_VARIABLE || $tokens[$i + 1]->text === '{')) {
                $hasVariableVars = true;
            }
            // 8. Function Calls (T_STRING, T_EVAL, or fully qualified \foo\bar)
            if ($tok->id === T_STRING || $tok->id === T_NAME_QUALIFIED || $tok->id === T_NAME_FULLY_QUALIFIED) {
                $prevIdx = $i - 1;
                while ($prevIdx >= 0 && $tokens[$prevIdx]->id === T_WHITESPACE) {
                    $prevIdx--;
                }
                if ($prevIdx >= 0) {
                    $pId = $tokens[$prevIdx]->id;
                    if (
                        $pId === T_OBJECT_OPERATOR ||
                        $pId === T_DOUBLE_COLON ||
                        $pId === T_FUNCTION ||
                        $pId === T_CLASS ||
                        $pId === T_NEW ||
                        (defined('T_NULLSAFE_OBJECT_OPERATOR') && $pId === T_NULLSAFE_OBJECT_OPERATOR)
                    ) {
                        continue;
                    }
                }
                $nextIdx = $i + 1;
                while ($nextIdx < $count && $tokens[$nextIdx]->id === T_WHITESPACE) {
                    $nextIdx++;
                }
                if ($nextIdx < $count && $tokens[$nextIdx]->text === '(') {
                    $calledName = $tok->text;
                    $lineNo = $line;
                    $argsString = '';
                    $parenDepth = 1;
                    $argRunner = $nextIdx + 1;
                    while ($argRunner < $count) {
                        $argToken = $tokens[$argRunner];
                        if ($argToken->text === '(') {
                            $parenDepth++;
                        } elseif ($argToken->text === ')') {
                            $parenDepth--;
                        }
                        if ($parenDepth === 0) {
                            break;
                        }
                        $argsString .= $argToken->text;
                        $argRunner++;
                    }
                    $loweredName = strtolower(ltrim($calledName, '\\'));
                    if (in_array($loweredName, $dangerousFuncs, true)) {
                        $hasDangerousCalls = true;
                        $dangerousCalls[] = [
                            'name' => $calledName,
                            'line' => $lineNo,
                            'args' => trim($argsString)
                        ];
                    }
                    $calls[] = [
                        'name' => $calledName,
                        'line' => $lineNo,
                        'args' => trim($argsString)
                    ];
                    if (str_starts_with($loweredName, 'funk_')) {
                        $funkCalls[] = [
                            'name' => $calledName,
                            'line' => $lineNo,
                            'args' => trim($argsString)
                        ];
                    }
                    if (in_array($loweredName, $invalidFunkCallsInFNs)) {
                        $hasInvalidFunkCalls = true;
                        $invalidFunkCalls[] = $calledName;
                    }
                    if ($loweredName === 'set_exception_handler') {
                        $hasSetExceptionHandler = true;
                    }
                    if ($loweredName === 'set_error_handler') {
                        $hasSetErrorHandler = true;
                    }
                    if ($loweredName === 'register_shutdown_function') {
                        $hasSetRegisterShutdownFunction = true;
                    }
                }
            }
            // 9. Return statement parsing & context extraction
            if ($tok->id === T_RETURN) {
                $hasReturn = true;
                $returnLine = $line;
                $returnExprTokens = [];
                $returnExprString = '';
                $exprRunner = $i + 1;
                $nestedParenDepth = 0;
                $nestedBracketDepth = 0;
                $nestedBraceDepth = 0;
                while ($exprRunner < $count) {
                    $exprTok = $tokens[$exprRunner];
                    if ($nestedParenDepth === 0 && $nestedBracketDepth === 0 && $nestedBraceDepth === 0) {
                        if ($exprTok->text === ';' || $exprTok->id === T_CLOSE_TAG) {
                            break;
                        }
                    }
                    if ($exprTok->text === '(') $nestedParenDepth++;
                    elseif ($exprTok->text === ')') $nestedParenDepth--;
                    elseif ($exprTok->text === '[') $nestedBracketDepth++;
                    elseif ($exprTok->text === ']') $nestedBracketDepth--;
                    elseif ($exprTok->text === '{') $nestedBraceDepth++;
                    elseif ($exprTok->text === '}') $nestedBraceDepth--;
                    $returnExprTokens[] = $exprTok;
                    $returnExprString .= $exprTok->text;
                    $exprRunner++;
                }
                $rawExpr = trim($returnExprString);
                $exprType = 'void';
                $literalValue = null;
                $isStaticLiteral = false;
                if ($rawExpr !== '') {
                    $firstExprTok = null;
                    foreach ($returnExprTokens as $rTok) {
                        if ($rTok->id !== T_WHITESPACE && $rTok->id !== T_COMMENT && $rTok->id !== T_DOC_COMMENT) {
                            $firstExprTok = $rTok;
                            break;
                        }
                    }
                    if ($firstExprTok !== null) {
                        switch ($firstExprTok->id) {
                            case T_LNUMBER:
                                $exprType = 'integer';
                                $literalValue = (int)$rawExpr;
                                $isStaticLiteral = true;
                                break;
                            case T_DNUMBER:
                                $exprType = 'float';
                                $literalValue = (float)$rawExpr;
                                $isStaticLiteral = true;
                                break;
                            case T_CONSTANT_ENCAPSED_STRING:
                                $exprType = 'string';
                                $literalValue = substr($rawExpr, 1, -1);
                                $isStaticLiteral = true;
                                break;
                            case T_STRING:
                                $lowerFirst = strtolower($firstExprTok->text);
                                if ($lowerFirst === 'true' || $lowerFirst === 'false') {
                                    $exprType = 'boolean';
                                    $literalValue = $lowerFirst === 'true';
                                    $isStaticLiteral = true;
                                } elseif ($lowerFirst === 'null') {
                                    $exprType = 'null';
                                    $literalValue = null;
                                    $isStaticLiteral = true;
                                } else {
                                    $exprType = 'constant_or_function';
                                }
                                break;
                            case T_ARRAY:
                            case $firstExprTok->text === '[':
                                $exprType = 'array';
                                break;
                            case T_VARIABLE:
                                $exprType = 'variable';
                                break;
                            case T_NEW:
                                $exprType = 'object_instantiation';
                                break;
                            default:
                                $exprType = 'expression';
                                break;
                        }
                    }
                }
                $returns[] = [
                    'line'              => $returnLine,
                    'raw_expression'    => $rawExpr,
                    'type_hint'         => $exprType,
                    'is_static_literal' => $isStaticLiteral,
                    'literal_value'     => $literalValue,
                    'has_variable'      => str_contains($rawExpr, '$'),
                    'is_funk_call'      => str_contains(strtolower($rawExpr), 'funk_'),
                ];
            }
        }
        return [
            'first_significant_token_id' => $firstSignificantTokenId,
            'first_significant_token_text' => $firstSignificantTokenText,
            'starts_with_return' => $startsWithReturn,
            'has_return_statement' => $hasReturn,
            'returns' =>        $returns,
            'has_exit'             => $hasExit,
            'exit_lines'           => array_unique($exitLines),
            'has_raw_output'       => $hasRawOutput,
            'raw_output_lines'     => array_unique($rawOutputLines),
            'has_inline_html_output' =>    $hasInlineHtml,
            'inline_html_lines' => $inlineHtmlLines,
            'has_eval'             => $hasEval,
            'eval_lines'           => array_unique($evalLines),
            'eval_values' => $evalValues,
            'has_inner_functions'  => $hasInnerFunctions,
            'nested_function_lines' => array_unique($innerFunctionLines),
            'has_closures'           => $hasClosures ?? false,
            'closure_lines'          => array_unique($closureLines ?? []),
            'has_inner_classes'    => $hasInnerClasses,
            'inner_class_lines'    => array_unique($innerClassLines),
            'has_globals'          => $hasGlobals,
            'global_vars'          => array_unique($globalVars),
            'has_dangerous_calls'  => $hasDangerousCalls,
            'dangerous_calls' => $dangerousCalls,
            'only_whitespace_and_or_comments' => $hasOnlyCommentsOrWhiteSpace,
            'has_variable_vars'    => $hasVariableVars,
            'has_set_error_hankder' => $hasSetErrorHandler,
            'has_set_exception_handler' => $hasSetExceptionHandler,
            'has_register_shutdown_function' => $hasSetRegisterShutdownFunction,
            'calls'                => $calls,
            'funk_calls' => $funkCalls,
            'invalid_funk_calls' => $invalidFunkCalls,
            'has_invalid_funk_calls' => $hasInvalidFunkCalls
        ];
    }
    private function file_analyze_class_tokens(string $classBodyCode, int $startLine = 1): array
    {
        $tokens = PhpToken::tokenize("<?php " . $classBodyCode);
        $count = count($tokens);
        $lineOffset = $startLine;
        $traitsUsed = [];
        $constants = [];
        $properties = [];
        $methods = [];
        $braceDepth = 0;
        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];
            if ($tok->text === '{') {
                $braceDepth++;
                continue;
            } elseif ($tok->text === '}') {
                $braceDepth--;
                continue;
            }
            if ($braceDepth !== 1) {
                continue;
            }
            if ($tok->id === T_USE) {
                $traitTokens = [];
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j]->text === ';' || $tokens[$j]->text === '{') { // '{' handles adapt/insteadof blocks
                        $i = $j;
                        break;
                    }
                    if ($tokens[$j]->id !== T_WHITESPACE) {
                        $traitTokens[] = $tokens[$j]->text;
                    }
                }
                $traitStr = implode('', $traitTokens);
                foreach (explode(',', $traitStr) as $t) {
                    $trimmed = trim($t);
                    if ($trimmed !== '') {
                        $traitsUsed[] = $trimmed;
                    }
                }
                continue;
            }
            if ($tok->id === T_CONST) {
                $visibility = 'public';
                // Look backward for visibility
                for ($b = $i - 1; $b >= 0; $b--) {
                    if ($tokens[$b]->id === T_PRIVATE) {
                        $visibility = 'private';
                        break;
                    }
                    if ($tokens[$b]->id === T_PROTECTED) {
                        $visibility = 'protected';
                        break;
                    }
                    if ($tokens[$b]->id === T_PUBLIC) {
                        $visibility = 'public';
                        break;
                    }
                    if ($tokens[$b]->text === ';' || $tokens[$b]->text === '}') break;
                }
                $constName = null;
                $valueTokens = [];
                $hasEquals = false;
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j]->text === ';') {
                        $i = $j;
                        break;
                    }
                    if (!$hasEquals && $tokens[$j]->id === T_STRING) {
                        $constName = $tokens[$j]->text;
                    } elseif ($tokens[$j]->text === '=') {
                        $hasEquals = true;
                    } elseif ($hasEquals && $tokens[$j]->id !== T_WHITESPACE) {
                        $valueTokens[] = $tokens[$j]->text;
                    }
                }
                if ($constName !== null) {
                    $constants[$constName] = [
                        'name'       => $constName,
                        'visibility' => $visibility,
                        'value_raw'  => trim(implode(' ', $valueTokens)),
                        'line'       => $tok->line + $lineOffset,
                    ];
                }
                continue;
            }
            if ($tok->id === T_FUNCTION) {
                $visibility = 'public';
                $isStatic   = false;
                $isAbstract = false;
                $isFinal    = false;
                for ($b = $i - 1; $b >= 0; $b--) {
                    $bId = $tokens[$b]->id;
                    if ($bId === T_PRIVATE) {
                        $visibility = 'private';
                    }
                    if ($bId === T_PROTECTED) {
                        $visibility = 'protected';
                    }
                    if ($bId === T_PUBLIC) {
                        $visibility = 'public';
                    }
                    if ($bId === T_STATIC) {
                        $isStatic = true;
                    }
                    if ($bId === T_ABSTRACT) {
                        $isAbstract = true;
                    }
                    if ($bId === T_FINAL) {
                        $isFinal = true;
                    }
                    if ($tokens[$b]->text === ';' || $tokens[$b]->text === '}' || $tokens[$b]->text === '{') {
                        break;
                    }
                }
                $nameIdx = $i + 1;
                while ($nameIdx < $count && ($tokens[$nameIdx]->id === T_WHITESPACE || $tokens[$nameIdx]->text === '&')) {
                    $nameIdx++;
                }
                if ($nameIdx >= $count || $tokens[$nameIdx]->id !== T_STRING) {
                    continue;
                }
                $methodName = $tokens[$nameIdx]->text;
                $methodLine = $tok->line + $lineOffset;
                $argStart = $nameIdx + 1;
                while ($argStart < $count && $tokens[$argStart]->text !== '(' && $tokens[$argStart]->text !== ';' && $tokens[$argStart]->text !== '{') {
                    $argStart++;
                }
                $argsRaw = '';
                $bodySearchIdx = $argStart;
                if ($argStart < $count && $tokens[$argStart]->text === '(') {
                    $pDepth = 1;
                    $aTokens = [];
                    for ($j = $argStart + 1; $j < $count; $j++) {
                        if ($tokens[$j]->text === '(') $pDepth++;
                        elseif ($tokens[$j]->text === ')') $pDepth--;
                        if ($pDepth === 0) {
                            $bodySearchIdx = $j + 1;
                            break;
                        }
                        $aTokens[] = $tokens[$j]->text;
                    }
                    $argsRaw = trim(implode('', $aTokens));
                }
                while ($bodySearchIdx < $count && $tokens[$bodySearchIdx]->text !== '{' && $tokens[$bodySearchIdx]->text !== ';') {
                    $bodySearchIdx++;
                }
                if ($bodySearchIdx >= $count) {
                    continue;
                }
                if ($tokens[$bodySearchIdx]->text === ';') {
                    $methods[$methodName] = [
                        'name'        => $methodName,
                        'visibility'  => $visibility,
                        'is_static'   => $isStatic,
                        'is_abstract' => true,
                        'is_final'    => $isFinal,
                        'args_raw'    => $argsRaw,
                        'body_raw'    => null,
                        'line'        => $methodLine,
                        'analysis'    => null,
                    ];
                    $i = $bodySearchIdx;
                    continue;
                }
                $mBodyStartPos = $tokens[$bodySearchIdx]->pos;
                $mBraceDepth = 0;
                $mHasStarted = false;
                $mBodyEndPos = -1;
                $lastIdx = $i;
                for ($j = $bodySearchIdx; $j < $count; $j++) {
                    if ($tokens[$j]->text === '{') {
                        $mBraceDepth++;
                        $mHasStarted = true;
                    } elseif ($tokens[$j]->text === '}') {
                        $mBraceDepth--;
                    }
                    if ($mHasStarted && $mBraceDepth === 0) {
                        $mBodyEndPos = $tokens[$j]->pos + strlen($tokens[$j]->text);
                        $lastIdx = $j;
                        break;
                    }
                }
                if ($mBodyEndPos !== -1) {
                    $methodBodyRaw = substr($classBodyCode, $mBodyStartPos - 5, $mBodyEndPos - $mBodyStartPos); // adjust for <?php token prefix
                    $methodAnalysis =  $this->file_analyze_body_tokens($methodBodyRaw, $methodLine);
                    $methods[$methodName] = [
                        'name'        => $methodName,
                        'visibility'  => $visibility,
                        'is_static'   => $isStatic,
                        'is_abstract' => $isAbstract,
                        'is_final'    => $isFinal,
                        'args_raw'    => $argsRaw,
                        'body_raw'    => $methodBodyRaw,
                        'line'        => $methodLine,
                        'analysis'    => $methodAnalysis,
                    ];
                    $i = $lastIdx;
                }
                continue;
            }
            if ($tok->id === T_VARIABLE) {
                $propName = ltrim($tok->text, '$');
                $visibility = 'public';
                $isStatic   = false;
                $isReadonly = false;
                $typeHint   = null;
                $modifierTokens = [];
                for ($b = $i - 1; $b >= 0; $b--) {
                    $bTok = $tokens[$b];
                    if ($bTok->text === ';' || $bTok->text === '}' || $bTok->text === '{' || $bTok->id === T_DOC_COMMENT) {
                        break;
                    }
                    if ($bTok->id !== T_WHITESPACE) {
                        array_unshift($modifierTokens, $bTok);
                    }
                }
                foreach ($modifierTokens as $mTok) {
                    if ($mTok->id === T_PRIVATE) {
                        $visibility = 'private';
                    } elseif ($mTok->id === T_PROTECTED) {
                        $visibility = 'protected';
                    } elseif ($mTok->id === T_PUBLIC) {
                        $visibility = 'public';
                    } elseif ($mTok->id === T_STATIC) {
                        $isStatic = true;
                    } elseif (defined('T_READONLY') && $mTok->id === T_READONLY) {
                        $isReadonly = true;
                    } elseif ($mTok->id === T_STRING || $mTok->id === T_NAME_QUALIFIED || $mTok->text === '?') {
                        $typeHint .= $mTok->text;
                    }
                }
                $hasDefault = false;
                $defaultValueTokens = [];
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j]->text === ';' || $tokens[$j]->text === ',') {
                        $i = $j;
                        break;
                    }
                    if ($tokens[$j]->text === '=') {
                        $hasDefault = true;
                    } elseif ($hasDefault && $tokens[$j]->id !== T_WHITESPACE) {
                        $defaultValueTokens[] = $tokens[$j]->text;
                    }
                }
                $properties[$propName] = [
                    'name'          => $propName,
                    'visibility'    => $visibility,
                    'is_static'     => $isStatic,
                    'is_readonly'   => $isReadonly,
                    'type'          => $typeHint,
                    'has_default'   => $hasDefault,
                    'default_raw'   => $hasDefault ? implode(' ', $defaultValueTokens) : null,
                    'line'          => $tok->line + $lineOffset,
                ];
            }
        }
        return [
            'traits_used' => array_unique($traitsUsed),
            'constants'   => $constants,
            'properties'  => $properties,
            'methods'     => $methods,
        ];
    }
    // Validate a Single Function in a Single File AND with optional boolean to Validate SingleFileFunctions
    // where it can only be one function in the file (middleware, request, post_response - while routes,
    // query, sql, and validation files can have more than one function per file. This might change!)
    private function validateFNFile(array $fileData, string $expectedFNName, string $contextLabel, string $expectedNSName = '', bool $singleFNExpected = false): ?string
    {
        $relativePath = '/src/funkphp/' . $fileData['folder_provided_path'] . '/' . $fileData['file_name'];
        if (empty($fileData) || array_is_list($fileData)) {
            return "File Function Error in {$contextLabel}: Parsed File Data `$relativePath` as an Array is EITHER A Numbered Array when it should be an Associative Array OR it is Completely Empty. (This is possibly an Internal FunkPHP Error - try regenerate default files in `/src/funkphp/config/` and try again)";
        }
        if (empty($fileData['file_exists'])) {
            return "File Function Error in {$contextLabel}: Expected File `$relativePath` does NOT exist.";
        }
        if (empty($fileData['file_readable'])) {
            return "File Function Error in {$contextLabel}: Expected File `$relativePath` is NOT Readable.";
        }
        if (!$fileData['syntax_valid']) {
            return "File Function Error in {$contextLabel}: File `$relativePath` contains `Invalid PHP Code` as parsed by `\PhpToken::tokenize with TOKEN_PARSE Flag`. Review the PHP Syntax in the File:'`{$fileData['syntax_error']}`'.";
        }
        if (!empty($fileData['classes_exist'])) {
            return "File Function Error in {$contextLabel}: File `$relativePath` contains `Class Definitions` which is forbidden for this type of `File Function`.";
        }
        $fnCount = count($fileData['functions'] ?? []);
        if ($singleFNExpected) {
            if ($fnCount !== 1) {
                return "File Function Error in {$contextLabel}: File `$relativePath` must contain EXACTLY 1 Function (found {$fnCount}).";
            }
        }
        $FN = $fileData['functions'][$expectedFNName] ?? null;
        if (!$FN) {
            return "File Function Error in {$contextLabel}: Expected Function `{$expectedFNName}` in File `$relativePath` does NOT exist.";
        }
        if (strtolower($FN['fn_exact_name']) !== $FN['fn_lowercased']) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `Function Name` that is `all lowercased` and following this Naming Convention: `[a-z_][a-z0-9_]*`.";
        }
        if (str_starts_with(strtolower($FN['fn_exact_name']), 'funk_') || str_starts_with(strtolower($FN['fn_exact_name']), 'cli_')) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `Function Name` that does NOT start with `funk_` OR `cli_` as it will be in the Global Namespace and could clash with Internal FunkPHP Functions.";
        }
        if ($expectedNSName !== '') {
            if (!isset($fileData['namespace']) || $fileData['namespace'] !== $expectedNSName) {
                return "File Function Error in {$contextLabel}: Function `{$expectedFNName}` in File `$relativePath` must have the following namespace: `{$expectedNSName}` (Found: `" . ($fileData['namespace'] ?? '<NO NAMESPACE>') . "`).";
            }
        }
        if ($FN['body_raw'] === '{}' || $FN['only_whitespace_and_or_comments'] === true) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `Code in its Function Body` and cannot just contain `whitespace` and/or `comments`.";
        }
        $argsRaw = trim($FN['args_raw'] ?? '');
        if (!str_starts_with($argsRaw, '&$c')) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `&\$c` as its First Parameter (found `({$argsRaw})`).";
        }
        if ($FN['has_inner_functions'] === true) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` cannot have Inner Function Declarations (e.g. `function name(&\$c){ function inner(&\$c){} }`). See line(s): `" . join(', ', $FN['nested_function_lines']) . "` in the File.";
        }
        if ($FN['has_set_error_hankder']) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function 'set_error_handler'` which must instead be set with `->setDefaultErrorHandler('FN_From_/src/funkphp/config/functions.php>')` under `->CONFIG()` in `/src/funkphp/app/CONFIG.php`.";
        }
        if ($FN['has_set_exception_handler']) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function 'set_exception_handler'` which must instead be set with `->setDefaultExceptionHandler('FN_From_/src/funkphp/config/functions.php>')` under `->CONFIG()` in `/src/funkphp/app/CONFIG.php`.";
        }
        if ($FN['has_register_shutdown_function']) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function 'register_shutdown_function'` which must instead be set with `->pipePostResponseFunction('<FN_From_/src/funkphp/pipes/post_response/FileName.php>')` under `->CONFIG()` in `/src/funkphp/app/CONFIG.php`. They are added using the in-built `register_shutdown_function()` and are executed in such order. `IMPORTANT:` Remember that any use of `exit()` in those `Piped Post-Response Function(s)` will make the remaining (if any) to NOT run Post-Response!";
        }
        if ($FN['has_invalid_funk_calls']) {
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function(s): `" . $this->joinArray($FN['invalid_funk_calls']) . ". Some of these Functions are set under `->CONFIG()` in `/src/funkphp/app/CONFIG.php` while others are not meant to be called inside the `pipes` of a given Matched Route but are meant instead to be used internally by FunkPHP.";
        }
        return null; // Function File for FunkPHP use is all OK here! - Warnings are emitted by another function
    }
    private function validateCLASSFile(array $fileData, string $expectedFNName, string $contextLabel, string $expectedNSName = '', bool $singleFNExpected = false): ?string
    {
        $relativePath = '/src/funkphp/' . $fileData['folder_provided_path'] . '/' . $fileData['file_name'];
        if (empty($fileData) || array_is_list($fileData)) {
            return "File Class Error in {$contextLabel}: Parsed File Data `$relativePath` as an Array is EITHER A Numbered Array when it should be an Associative Array OR it is Completely Empty. (This is possibly an Internal FunkPHP Error - try regenerate default files in `/src/funkphp/config/` and try again)";
        }
        if (empty($fileData['file_exists'])) {
            return "File Class Error in {$contextLabel}: Expected File `$relativePath` does NOT exist.";
        }
        if (empty($fileData['file_readable'])) {
            return "File Class Error in {$contextLabel}: Expected File `$relativePath` is NOT Readable.";
        }
        if (!$fileData['syntax_valid']) {
            return "File Class Error in {$contextLabel}: File `$relativePath` contains `Invalid PHP Code` as parsed by `\PhpToken::tokenize with TOKEN_PARSE Flag`. Review the PHP Syntax in the File:'`{$fileData['syntax_error']}`'.";
        }
        $fnCount = count($fileData['classes'] ?? []);
        if ($singleFNExpected) {
            if ($fnCount !== 1) {
                return "File Class Error in {$contextLabel}: File `$relativePath` must contain EXACTLY 1 Class (found {$fnCount}).";
            }
        }
        $FN = $fileData['classes'][$expectedFNName] ?? null;
        if (!$FN) {
            return "File Class Error in {$contextLabel}: Expected Class `{$expectedFNName}` in File `$relativePath` does NOT exist.";
        }
        if (str_starts_with(strtolower(trim($FN['class_name'])), 'funk')) {
            return "File Class Error in {$contextLabel}: Class `{$expectedFNName}()` in File `$relativePath` cannot start with `Funk` as it is reserved despite being in the shared namespace `funkphp\\classes`.";
        }
        if ($expectedNSName !== '') {
            if (!isset($fileData['namespace']) || $fileData['namespace'] !== $expectedNSName) {
                return "File Class Error in {$contextLabel}: Class `{$expectedFNName}` in File `$relativePath` must have the following namespace: `{$expectedNSName}` (Found: `" . ($fileData['namespace'] ?? '<NO NAMESPACE>') . "`).";
            }
        }
        if ($FN['body_raw'] === '{}' || $FN['only_whitespace_and_or_comments'] === true) {
            return "File Class Error in {$contextLabel}: Class `{$expectedFNName}()` in File `$relativePath` must have `Code in its Class Body` and cannot just contain `whitespace` and/or `comments`.";
        }
        if ($FN['has_inner_functions'] === true) {
            return "File Class Error in {$contextLabel}: Class `{$expectedFNName}()` in File `$relativePath` cannot have Inner Function Declarations (e.g. `function name(&\$c){ function inner(&\$c){} }`). See line(s): `" . join(', ', $FN['nested_function_lines']) . "` in the File.";
        }
        // Now we iterate through each method in the current Class in the classes.php
        // file since those are what could be considered functions in classes.
        foreach ($FN['methods'] as $method => $methodDetails) {
            if ($method !== '__construct') { // Constructor CAN have empty body
                if ($methodDetails['analysis']['only_whitespace_and_or_comments']) {
                    return "File Class Error in {$contextLabel}: Class Method `{$expectedFNName}->{$method}` in File `$relativePath` has `Only Whitespace and/or Comments` in its `Code Body` while NOT being the `__construct` Method. Add some Code to the Class Method OR comment it out for later use.";
                }
            }
            if ($methodDetails['analysis']['has_inner_functions']) {
                return "File Class Error in {$contextLabel}: Class Method `{$expectedFNName}->{$method}` in File `$relativePath` has `Inner Function Declarations` on lines(s) " . $this->joinArray($methodDetails['analysis']['nested_function_lines']) . " which could Conflict with other Globally Namespaced Functions. `Convert it to a Valid Class-based Method` instead.";
            }
            if ($methodDetails['analysis']['has_invalid_funk_calls']) {
                return "File Class Error in {$contextLabel}: Class Method `{$expectedFNName}->{$method}` in File `$relativePath` has calls to the following `Disallowed FunkPHP Functions` " . $this->joinArray($methodDetails['analysis']['invalid_funk_calls']) . " that are meant to be called by other Internal FunkPHP Functions directly and not inside of non-FunkPHP-based Classes.";
            }
        }
        return null; // Class in File for FunkPHP use is all OK here! - Warnings are emitted by another function
    }
    // Validate Response Code is between 100-599
    private function validateStatusCode($status): bool
    {
        if (
            !isset($status) || !is_int($status)
            || ($status < 100 || $status > 599)
        ) {
            return false;
        }
        return true;
    }
    // Validate AND return a JSON String OR just Null when failure
    private function encodeJSONorReturnNull($json): string|null
    {
        try {
            $json = json_encode($json, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return null;
        }
        return $json;
    }
    // Set context to not having to repeat so much for each batchFUNCTION
    // It also first appends to the FunkPHPFluentAPI // THE NEW VERSION
    private function setCtx(string $config_or_method, ?string $route = null, string $batchFN, string $under, mixed ...$vals)
    {
        $this->FunkPHPFluentAPI['ALL'][count($this->FunkPHPFluentAPI['ALL']) + 1] = $this->appendFunkPHPFluentAPI($batchFN, ...$vals);
        if ($config_or_method === 'CONFIG') {
            $this->FunkPHPFluentAPI['CONFIG'][count($this->FunkPHPFluentAPI['CONFIG']) + 1] = $this->appendFunkPHPFluentAPI($batchFN, ...$vals);
        } else {
            if ($route) {
                if (!isset($this->FunkPHPFluentAPI['METHODS'][$config_or_method]['ROUTES'][$route])) {
                    $this->FunkPHPFluentAPI['METHODS'][$config_or_method]['ROUTES'][$route] = [];
                }
                $this->FunkPHPFluentAPI['METHODS'][$config_or_method]['ROUTES'][$route][count($this->FunkPHPFluentAPI['METHODS'][$config_or_method]['ROUTES'][$route]) + 1] = $this->appendFunkPHPFluentAPI($batchFN, ...$vals);
            } else {
                if (!isset($this->FunkPHPFluentAPI['METHODS'][$config_or_method]['CONFIG'])) {
                    $this->FunkPHPFluentAPI['METHODS'][$config_or_method]['CONFIG'] = [];
                }
                $this->FunkPHPFluentAPI['METHODS'][$config_or_method]['CONFIG'][count($this->FunkPHPFluentAPI['METHODS'][$config_or_method]['CONFIG']) + 1] = $this->appendFunkPHPFluentAPI($batchFN, ...$vals);
            }
        }
        //$this->FunkPHPFluentAPI2[count($this->FunkPHPFluentAPI) + 1] = $this->appendFunkPHPFluentAPI($batchFN, ...$vals);
        $exportedVals = array_map(fn($v) => $this->exportShortSyntax($v), $vals);
        $argString = implode(', ', $exportedVals);
        return [
            "`->$batchFN()` under `->{$under}`",
            "`->$batchFN({$argString})` under `->{$under}`"
        ];
    }
    /**
     * Resolves a standardized validation error message template.
     *
     * @param 'InvalidGroupName'|'InvalidArrayMustBeASSOCIATIVE'|'InvalidArrayMustBeNUMBERED'|'InvalidFileCustomErrAfterColon'|'NonEmptyStringNoTrailing'|'NonEmptyAllLowercasedStringNotStartCLIorFUNK'|'InvalidFunctionName'|'InvalidRegex'|'InvalidArrayFormat'|'DuplicateCallInvalid'|'DuplicateCallValid'|'ConflictingConfiguration'|'InvalidHttpStatusCode'|'JsonEncodingFailed'|'DuplicateFunctionNameInBatch'|'UserDefinedFUNCTIONNotFound'|'UserDefinedCLASSNotFound'|'UserDefinedFUNCTIONAlreadyUsedBy'|'UserDefinedCLASSAlreadyUsedBy'|'UserDefinedFUNCTIONAlreadyInArray'|'UserDefinedCLASSAlreadyInArray'|'UserDefinedFUNCTIONHasWrongArgs'|'NotBoolean'|'NonEmptyAllLowercasedStringSTARTWithHTTP'|'NonEmptyAllLowercasedStringSTARTWithHTTPS'|'NotInteger'|'NotIntegerNotNegative'|'NotIntegerNotPositive'|'NotFloat'|'NotFloatNotNegative'|'NotFloatNotPositive'|'NotNumeric'|'NotFloatNotPositive'|'NotFloatNotNegative'|'InvalidStringCustomErrAfterColon'|'InvalidIntegerCustomErrAfterColon'|'InvalidFloatCustomErrAfterColon'|'InvalidNumericCustomErrAfterColon'|'InvalidBooleanCustomErrAfterColon'|'InvalidArrayCustomErrAfterColon'|'InvalidNullCustomErrAfterColon'|'InvalidFileNameCustomErrAfterColon'|'InvalidFunctionNameCustomErrAfterColon'|'InvalidFunctionStructureCustomErrAfterColon' $errType
     * @param string|null $optionalCtx Extra Context injected somewhere in the string. It is always used or internal error is issued instead.
     * @return string The Formatted Error Message Segment.
     */
    private function getErr(string $errType, ?string $optionalCtx = ''): string
    {
        $errors = [
            // Basic Syntax & Data Validation Errors
            'InvalidRegexCustomErrAfterColon' => "Invalid Regex Value in {$optionalCtx}:",
            'InvalidStringCustomErrAfterColon' => "Invalid String Value in {$optionalCtx}:",
            'InvalidIntegerCustomErrAfterColon' => "Invalid Integer Value in {$optionalCtx}:",
            'InvalidFloatCustomErrAfterColon' => "Invalid Float Value in {$optionalCtx}:",
            'InvalidNumericCustomErrAfterColon' => "Invalid Numeric Value in {$optionalCtx}:",
            'InvalidBooleanCustomErrAfterColon' => "Invalid Boolean Value in {$optionalCtx}:",
            'InvalidArrayCustomErrAfterColon' => "Invalid Array Value in {$optionalCtx}:",
            'InvalidNullCustomErrAfterColon' => "Invalid Null Value in {$optionalCtx}:",
            'InvalidFileCustomErrAfterColon' => "Invalid regarding File in {$optionalCtx}:",
            'InvalidFileNameCustomErrAfterColon' => "Invalid Function Filename Value in {$optionalCtx}:",
            'InvalidFunctionNameCustomErrAfterColon' => "Invalid Function Name Value in {$optionalCtx}:",
            'InvalidFunctionStructureCustomErrAfterColon' => "Invalid Function Structure in {$optionalCtx}:",
            'InvalidParamName' => "Invalid Param Rule Name in {$optionalCtx}: Param Rule Name must be a Non-Empty String (no trailing spaces) all lowercased containing only `[a-z0-9_-]` characters without the colon (`:`).",
            'InvalidParamFlexibleStringArray' => "Invalid Param Rule Collection in {$optionalCtx}: must be an Array of Strings where each first element is the Name of the Regex Rule and each second element is the Regex Rule itself. Any matched Flexible Regex Rule is then set to `\$c['req']['matched_params_flexible']['{paramIdentifier}'] = '{name}|null';` when matched OR it is set to null if no Param Flexible Rule match.",
            'NonEmptyStringNoTrailing' => "Invalid String Value in {$optionalCtx}: must be a Non-Empty String (no trailing spaces).",
            'NonEmptyAllLowercasedStringNotStartCLIorFUNK' => "Invalid String Value in {$optionalCtx}: must be a Non-Empty String (no trailing spaces) all lowercased that does NOT start with `cli_` OR `funk_`.",
            'NotBoolean' => "Invalid Boolean Value in {$optionalCtx}: must a Boolean that is set to TRUE or FALSE.",
            'NotInteger' => "Invalid Integer Value in {$optionalCtx}: must an Integer Value.",
            'NotIntegerNotNegative' => "Invalid Integer Value in {$optionalCtx}: must an Integer Value that is also not Negative.",
            'NotIntegerNotPositive' => "Invalid Integer Value in {$optionalCtx}: must an Integer Value that is also not Positive.",
            'NotFloat' => "Invalid Float Value in {$optionalCtx}: must a Float that is set to TRUE or FALSE.",
            'NotFloatNotNegative' => "Invalid Float Value in {$optionalCtx}: must an Float Value that is also not Negative.",
            'NotFloatNotPositive' => "Invalid Float Value in {$optionalCtx}: must an Float Value that is also not Positive.",
            'NotNumeric' => "Invalid Numeric Value in {$optionalCtx}: must a Numeric Value (integer or float).",
            'NotNumericNotNegative' => "Invalid Numeric Value in {$optionalCtx}: must an Numeric Value that is also not Negative.",
            'NotNumericNotPositive' => "Invalid Numeric Value in {$optionalCtx}: must an Numeric Value that is also not Positive.",
            'InvalidGroupORFunctionName'                            => "Invalid Group|Function Name in {$optionalCtx}: must EITHER start with `group:` and then follow with these Valid `[a-z_][a-z0-9_]*` characters, OR it must a `Non-Empty String (no trailing spaces)` all `lowercased` starting with `[_a-z]` and then only use the following characters: `[_a-z0-9]` while it also does NOT start with `funk_` OR `cli_`.",
            'InvalidGroupORFileFunctionNames' => "Invalid Group|File+Function Name(s) in {$optionalCtx}: must EITHER start with `group:` and then follow with these Valid `[a-z_][a-z0-9_]*` characters, OR it must be a Valid `FileName.FunctionName` using `[a-z_][a-z0-9_]*` characters only for `Filename`, then a Single Dot (`.`), followed by these `[a-z_][a-z0-9_]*` characters again for `Function Name` (what PHP considers a `Valid Declared Function Name`). VALID: `users.by_id`, `_users._by_id`, OR `users.all`. NOT VALID: `1users.by_id`, `us-ers.by_id`, `users.by-id`, OR `users.1by_id`.",
            'InvalidFunctionName'                         => "Invalid Function Name in {$optionalCtx}: must be a `Non-Empty String (no trailing spaces)` all `lowercased` starting with `[_a-z]` and then only use the following characters: `[_a-z0-9]` while it also does NOT start with `funk_` OR `cli_`.",
            'InvalidFileAndFunctionName'                         => "Invalid File & Function Name in {$optionalCtx}: must be a `Non-Empty String (no trailing spaces) all lowercased` with a Single Dot (`.`) between the `Filename` and `Function Name`. Both must start with `[a-z_]` and then only use `[a-z0-9_]` characters while NOT starting with `funk_` OR `cli_`.",
            'InvalidMiddlewareFunctionName' => "Invalid Middleware Function Name in {$optionalCtx}: must be a `Non-Empty All Lowercased String (no trailing spaces)` that only uses `[a-z_][a-z0-9_]+` characters in that order while it does NOT start with `cli_` OR `funk_`.",
            'InvalidGroupName'                                => "Invalid Group Name Value in {$optionalCtx}: must be a `Non-Empty String (no trailing spaces)` all `lowercased` that does NOT start with `cli_` OR `funk_`.",
            'InvalidResponseType' => "Invalid Response Type in {$optionalCtx}: Choose between: `page:`, `json:`, `callback:`, OR `text:` and then follow up with the `pageFileName` (for page:), OR `SingleArrayKeyDepth` - only use `[a-zA-Z-_.]` characters - to get `\$c['d']['SingleArrayKeyDepth']` (if 'json:SingleArrayKeyDepth') for where `Stored JSON Data` should be returned from (for json:), OR `userDefinedFunctionName in /src/funkphp/config/functions.php` that you have defined to use as a callback (for callback:), OR the plain text message (for text:). `pipeResponse() automatically completes it with exit()` and then run any optionally configured `Post-Response`.",
            'InvalidResponseContext' => "Invalid Response Context in {$optionalCtx}: Valid choice between `page:|json:|callback:|text:` found, but the Context after the Single Colon (`:`) is Empty or Invalid. ",
            'InvalidAddHeaderFormat' => "Invalid Header Format in {$optionalCtx}: Header Name and Header Value cannot contain any kind of newline characters (`CRLF Injections` risks) OR the Single Colon (`:`) as that is added automatically. Valid Examples: `'Header-Name','HeaderValue'` OR `'Content-type','application/json'`.",
            'InvalidHeaderName' => "Invalid Header Name Value in {$optionalCtx}: Must be a `Non-Empty String` with Header Name Only (e.g. `server`, `x-powered-by`), with `Only Alphanumerics` and `single dashes between the words.`",
            'InvalidCSPSourceArray' => "Invalid CSP Source Array in {$optionalCtx}: Ensure Sources are Valid Non-Empty Strings with no spaces, semicolons, or CRLF Injections.",
            'InvalidCSPDirective' => "Invalid CSP Directive Name Value in {$optionalCtx}. Must be one of the following: ",
            'InvalidCSPWildcardUse' => "Invalid Wildcard Domain CSP Source Value in {$optionalCtx}. Wildcards must appear as `*.domain.com` OR `https://*.domain.com`.",
            'InvalidNonceKeyName' => "Invalid Nonce Key Value in {$optionalCtx}: Nonce Keys must be Non-Empty Strings containing only `[a-zA-Z0-9-_\.]` characters (e.g., `test`, `main-script`).",
            'InvalidPageName' => "Invalid Page Name Value in {$optionalCtx}: must be a `Non-Empty String` containing only `[a-zA-Z0-9-_]` characters (no trailing spaces) and without the File Extension.",
            'InvalidNoRouteMatchTextValue' => "Invalid Text Value in {$optionalCtx}: must be a `Non-Empty String` after `trim()` have been applied to it.",
            'InvalidRegex'                                => "Invalid Regex Value in {$optionalCtx}: must be a `Non-Empty String` that is also a `Valid Regex Pattern` when parsed by `preg_match()`. It cannot be an Empty Expression with optional modifiers (e.g. `//` OR `//i`).",
            'InvalidRouteFormat' => "Invalid Route Value in {$optionalCtx}: A Valid Route must: 1) Start with or just be `/` as root (`never end with -, _ OR /`), 2) Be all `lowercased`, 3) Have all `Uniquely Named /:params` URI segments (if any used), 4) Never use `-` and/or `_ consecutively`, after each other (e.g. `-_` or `_-`) OR as start in static/dynamic segments (e.g. `/:-`, `/:_`, `/_`, OR `/-`), 5) Only use `[a-z0-9_-]` characters.",
            'InvalidRouteFormatDuplicateParams' => "Invalid Route Value in {$optionalCtx}: `Check for Duplicate Params`. A Valid Route must: 1) Start with or just be `/` as root (`never end with -, _ OR /`), 2) Be all `lowercased`, 3) Have all `Uniquely Named /:params` URI segments (if any used), 4) Never use `-` and/or `_ consecutively`, after each other (e.g. `-_` or `_-`) OR as start in static/dynamic segments (e.g. `/:-`, `/:_`, `/_`, OR `/-`), 5) Only use `[a-z0-9_-]` characters.",
            'InvalidRouteAliasName'                     => "Invalid Route Alias Name in {$optionalCtx}: Aliases must only contain `[a-zA-Z0-9_.-]` characters (e.g., `users.all` OR `Users.All`).",
            'NonEmptyAllLowercasedStringSTARTWithHTTP'  => "Invalid String Value in {$optionalCtx}: must be a Non-Empty String (no trailing spaces) all lowercased that starts with `http://`.",
            'NonEmptyAllLowercasedStringSTARTWithHTTPS'  => "Invalid String Value in {$optionalCtx}: must be a Non-Empty String (no trailing spaces) all lowercased that starts with `https://`.",
            'InvalidArrayMustBeNUMBERED'                  => "Invalid Array in {$optionalCtx}: must be Numbered Array.",
            'InvalidArrayMustBeASSOCIATIVE'                  => "Invalid Array in {$optionalCtx}: must be an Associative Array.",
            'InvalidHttpStatusCode'                     => "Invalid Integer Value in {$optionalCtx} must be a `Valid Integer HTTP(S) Status Code` between `100-599`.",
            'JsonEncodingFailedNoData'                        => "Data Serialization to JSON Failed in {$optionalCtx} because no Input/Data were passed to it.",
            'JsonEncodingFailed'                        => "Data Serialization to JSON Failed in {$optionalCtx}. Review the passed Input to it.",
            'RouteIsInvalidMustBecomeValidBeforeWhat' => "Invalid Route being applied with {$optionalCtx}. Route must first become Valid.",
            'InvalidCompilerFlag' => "Invalid Compiler Flag in {$optionalCtx}: must be one of the following: ",
            'InvalidJSONSourceForResponseCtx' => "Invalid JSON Data Source Syntax in {$optionalCtx}: use only `[a-zA-Z0-9-_.]` characters. 'YourKey' after `json:` will then be used in `\$c['d']['YourKey']` as the Final Data Source ",

            // Forbidden via $this->FORBIDDEN Variable
            'ForbiddenResponseHeaders' => "Forbidden Response Header Name in {$optionalCtx}: ",

            // Scope & Existence for FUNCTIONS Validation Errors
            'UserDefinedFUNCTIONHasWrongArgs'                       => "Provided User-defined Function in {$optionalCtx} from `/src/funkphp/config/functions.php` must besides the starting Function Parameter `&\$c` also have the following Function Parameters:",
            'UserDefinedFUNCTIONAlreadyInArray'                       => "Provided User-defined Function in {$optionalCtx} from `/src/funkphp/config/functions.php` is already in a must-be-unique array:",
            'UserDefinedCLASSAlreadyInArray'                       => "Provided User-defined Class in {$optionalCtx} from `/src/funkphp/config/classes.php` is already in a must-be-unique array:",
            'UserDefinedFUNCTIONAlreadyUsedBy'                       => "Provided User-defined Function in {$optionalCtx} from `/src/funkphp/config/functions.php` is already being used by:",
            'UserDefinedCLASSAlreadyUsedBy'                       => "Provided User-defined Class in {$optionalCtx} from `/src/funkphp/config/classes.php` is already being used by:",
            'UserDefinedFUNCTIONNotFound'                       => "Provided User-defined Function in {$optionalCtx} NOT Found in `/src/funkphp/config/functions.php`. Review Function Name OR add it to the File.",
            'UserDefinedFUNCTIONNotFoundForResponseCtx'                       => "Provided User-defined Function in {$optionalCtx} NOT Found in `/src/funkphp/config/functions.php` ",
            'UserDefinedCLASSNotFound'                          => "Provided User-defined Class in {$optionalCtx} NOT Found in `/src/funkphp/config/classes.php`. Review Class Name OR add it to the File.",
            'UserDefinedFNSetAsEngineFN'                         => "Provided User-defined Function in {$optionalCtx} from `/src/funkphp/config/functions.php` is already set as Global Handler.",
            'NoCompiledPageNotFound' => "Provided Page Filename in {$optionalCtx} was NOT found in `/src/funkphp/pages/compiled/`",
            'NoNonCompiledPageNotFound' => "Provided Page Filename in {$optionalCtx} was NOT found in `/src/funkphp/pages/`",
            'NoPageAtAllFound' => "Provided Page Filename in {$optionalCtx} was NOT found in `/src/funkphp/pages/` and also NOT found in `/src/funkphp/pages/compiled/`",
            'GroupPipeResponseNotSupported' => "Unsupported `'group:' Syntax` in {$optionalCtx}: cannot use `group:` in `->pipeResponse()` as you are meant to only use `->pipeResponse()` once for each Route.",
            'RouteHasNoParams' => "No Params for Route in {$optionalCtx} so `->setParamRule()` cannot be used. Add Valid Params to the Route first via `/:param-segment` parts.",
            'RouteHasNotChosenParam' => "Provided Param for Route in {$optionalCtx} does NOT exist so it cannot be used in `->setParamRule()`.",

            // Call Order & Duplicate|Conflict Validation Errors
            'DuplicateFlexibleRegexPairName' => "`Duplicate Regex Pair Name` in {$optionalCtx}: ",
            'DuplicateNonceDirectiveUse' => "`Duplicate Nonce CSP Directive Use` in {$optionalCtx}: ",
            'DuplicateNonceName'           => "`Duplicate Nonce Name` in {$optionalCtx}. Review/change the already `Valid` Nonce Key Name ",
            'DuplicateRouteAliasName'           => "Duplicate Route Alias Name` in {$optionalCtx}. Review/change the already `Valid` Configuration first defined in ",
            'DuplicateCallSessionCookieDueToValidOptionsVersion' => "`Duplicate Setting Session Cookie Call` to {$optionalCtx} due to already being set and `Valid` OR because `->setSessionCookieOptions()` has been used already which sets all Session Cookie Values at once.",
            'DuplicateRouteConflict' => "`Duplicate Route Conflict` in Valid Formatted Route in {$optionalCtx} ",
            'DuplicateCallInvalid'              => "`Duplicate Call` to {$optionalCtx} with either `Exact Values` OR it can Only be Called Once. Review the already `Invalid` Configuration which is before this Error in the `API Array`.",
            'DuplicateCallValid'                => "`Duplicate Call` to {$optionalCtx} with either `Exact Values` OR it can Only be Called Once. Review/change the already `Valid` Configuration which is before this Error in the `API Array`.",
            'DuplicateCallValidCanOnlyBeSetOnce' => "`Duplicate Valid Call` to {$optionalCtx}: this can only be set once. Review the already `Valid` Configuration which is before this Error in the `API Array`.",
            'DuplicateCallinValidCanOnlyBeSetOnce' => "`Duplicate Invalid Call` to {$optionalCtx}: this can only be set once. Review the already `Invalid` Configuration which is before this Error in the `API Array`.",
            'DuplicateCallValidMustBeSetWithDifferentValues' => "`Duplicate Valid Call` to {$optionalCtx}: one or more values must be different in order to use this more than once. Review the already `Valid` Configuration which is before this Error in the `API Array`.",
            'DuplicateCallInvalidMustBeSetWithDifferentValues' => "`Duplicate Invalid Call` to {$optionalCtx}: one or more values must be different in order to use this more than once. Review the already `Invalid` Configuration which is before this Error in the `API Array`.",
            'DuplicateParamGlobal' => "`Duplicate Global Param Rule` in {$optionalCtx}. Review/change the already `Valid` Configuration which is before this Error in the `API Array`.",
            'DuplicateParamMethod' => "`Duplicate Method Param Rule` in {$optionalCtx}. Review/change the already `Valid` Configuration which is before this Error in the `API Array`.",
            'DuplicateParamRoute' => "`Duplicate Route Param Rule` in {$optionalCtx}. Review/change the already `Valid` Configuration which is before this Error in the `API Array`.",
            'ConflictNoneSourceInCSP' => "`Invalid` CSP Configuration in {$optionalCtx}: Source `'none'` must always be used isolated for a given CSP Directive. More than one Source is used.",
            'ConflictRouteParam' => "`Route Parameter in Conflict` in {$optionalCtx}:",
            'ConflictRemovePipedHeader' => "`Conflicting Calls` in {$optionalCtx}: cannot set `Remove a Header` that was first configured as `Pipe a Header`.",
            'ConflictPipeRemovedHeader' => "`Conflicting Calls` in {$optionalCtx}: cannot set `Pipe a Header` that was first configured as `Remove a Header` .",
            'ConflictingExcludeHeadersWithAlreadyPipedHeader' => "Conflicting Calls in {$optionalCtx}: cannot reference the same Header(s) in `->setExcludeHeaders()` and `->pipeHeader()` in the same Route. Headers to Exclude should target Piped Headers in the same `<METHOD>()` and/or `CONFIG()`.",
            'ConflictingPipeHeaderWithAlreadyExcludeHeaders' => "Conflicting Calls in {$optionalCtx}: cannot reference the same Header(s) in `->pipeHeader()` and `->setExcludeHeaders()` in the same Route. Headers to Exclude should target Piped Headers in the same `<METHOD>()` and/or `CONFIG()`.",
            'ConflictingConfiguration'           => "Valid Configuration (`{$optionalCtx}`) is already set and CANNOT be overridden, only changed manually.",
            'ConflictingExcludeMWWithAlreadyPipedMW' => "Conflicting Calls in {$optionalCtx}: cannot reference the same Middleware(s) in `->setExcludeMiddleware()` and `->pipeMiddleware` in the same Route. Middlewares to Exclude should target Piped Middlewares in the same `<METHOD>()` and/or `CONFIG()`.",
            'ConflictingPipeMiddlewareWithAlreadyExcludeMW' => "Conflicting Calls in {$optionalCtx}: cannot reference the same Middleware(s) in `->pipeMiddleware()` and `->setExcludeMiddleware()` in the same Route. Middlewares to Exclude should target Piped Middlewares in the same `<METHOD>()` and/or `CONFIG()`.",
            // When Response Already exists
            'ConflictResponseAlreadyAdded' => "Conflicting Calls in {$optionalCtx}: A `->pipeResponse()` has already been piped. Cannot use `->pipe<Function|SQL|Query|Validation>` after that. If you need `Different Possible Responses` in the same Matched Route, use `funk_return_response()` inside your Piped Functions and one final `->pipeResponse()`.",
        ];
        if (isset($errors[$errType])) {
            return $errors[$errType];
        } else {
            $this->errors[] = ['type' => 'internal', 'err' => "[Class C->getErr()]: Unknown Internal Error Type:`{$errType}`. Report this as a Bug/Issue to the `Official FunkPHP Respositories`."];
            return "UNKNOWN ERROR TYPE CHOSEN: SEE 'internal' Error in `\$this->errors`!";
        }
    }
    /**
     * Set Error Message with specific type ($type) so it can be grouped if needed.
     *
     * Choose Error Type based on scope (global, method, route) and optional method and route when applicable.
     *
     * @param string $errMsg
     * @param 'Route-setParamRulePolymorphic'|'Method-setParamRulePolymorphic'|'Global-setParamRulePolymorphic'|'Global-setDebug'|'Route-pipeCompiledQuery'|'Route-pipeCompiledSQL'|'Route-pipeCompiledValidation'|'Global-setCompileFlag'|'Global-setGroupPipeUserdefined'|'Global-setGroupPipeRequest'|'Global-setGroupPipePostResponse'|'Global-setGroupPipeRoute'|'Global-setGroupPipeMiddlewares'|'Global-setINI_SET'|'Global-setNonces'|'Global-setCSP'|'Global-setSRIInternal'|'Global-setSRIExternal'|'Global-setNoRouteMatchPage'|'Global-setNoRouteMatchJSON'|'Global-setNoRouteMatchText'|'Global-setNoRouteMatchCallback'|'Global-setDefaultRegisteredShutdownHandler'|'Global-setDefaultExceptionHandler'|'Global-setDefaultErrorHandler'|'Global-setDefaultURI_NormalizerHandler'|'Global-setDefaultKernelHandler'|'Global-setBaseURLLocal'|'Global-setBaseURLOnline'|'Global-setBaseURLHost'|'Global-setBaseURLUri'|'Global-setSessionDriver'|'Global-setSessionCookieOptions'|'Global-setSessionCookieName'|'Global-setSessionCookieLifetime'|'Global-setSessionCookiePath'|'Global-setSessionCookieDomain'|'Global-setSessionCookieSecure'|'Global-setSessionCookieHTTPOnly'|'Global-setSessionCookieSameSite'|'Global-setUseFunkPHPOnline'|'Global-setUseHTTPS'|'Global-setUseVendor'|'Global-setParamRule'|'Global-setHeader'|'Global-removeHeader'|'Global-pipeMiddleware'|'Global-pipeRequestFunction'|'Global-pipePostResponseFunction'|'Method-setNoRouteMatch'|'Method-setNoRouteMatchPage'|'Method-setNoRouteMatchJson'|'Method-setNoRouteMatchText'|'Method-setNoRouteMatchCallback'|'Method-setNonces'|'Method-setCSP'|'Method-setRateLimiting'|'Method-pipeMiddleware'|'Method-setHeader'|'Method-removeHeader'|'Method-setParamRule'|'Method-route'|'Route-setAlias'|'Route-setRateLimiting'|'Route-setCache'|'Route-setNonces'|'Route-pipeMiddleware'|'Route-pipeFunction'|'Route-pipeResponse'|'Route-pipeSQL'|'Route-pipeQuery'|'Route-pipeValidation'|'Route-setExcludeMiddlewares'|'Route-setExcludeHeaders'|'Route-setParamRule'|'Route-setCSP'|'Route-setHeader'|'Route-removeHeader'|'Route-route' $errType
     * @param 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'HEAD'|null $method
     * @param string|null $route
     *
     */
    private function setErr(string $errMsg, string $errType = '', string $method = 'CONFIG', ?string $route = null)
    {
        $validErrTypes = [
            'Global-setDebug',
            'Global-setGroupPipeUserdefined',
            'Global-setGroupPipeRequest',
            'Global-setGroupPipePostResponse',
            'Global-setGroupPipeRoute',
            'Global-setGroupPipeMiddlewares',
            'Global-setCompileFlag',
            'Global-setINI_SET',
            'Global-setNonces',
            'Global-setCSP',
            'Global-setSRIInternal',
            'Global-setSRIExternal',
            'Global-setNoRouteMatchPage',
            'Global-setNoRouteMatchJSON',
            'Global-setNoRouteMatchText',
            'Global-setNoRouteMatchCallback',
            'Global-setDefaultExceptionHandler',
            'Global-setDefaultErrorHandler',
            'Global-setDefaultURI_NormalizerHandler',
            'Global-setDefaultKernelHandler',
            'Global-setBaseURLLocal',
            'Global-setBaseURLOnline',
            'Global-setBaseURLHost',
            'Global-setBaseURLUri',
            'Global-setSessionDriver',
            'Global-setSessionCookieOptions',
            'Global-setSessionCookieName',
            'Global-setSessionCookieLifetime',
            'Global-setSessionCookiePath',
            'Global-setSessionCookieDomain',
            'Global-setSessionCookieSecure',
            'Global-setSessionCookieHTTPOnly',
            'Global-setSessionCookieSameSite',
            'Global-setUseFunkPHPOnline',
            'Global-setUseHTTPS',
            'Global-setUseVendor',
            'Global-setParamRule',
            'Global-setParamRulePolymorphic',
            'Global-setHeader',
            'Global-removeHeader',
            'Global-pipeMiddleware',
            'Global-pipeRequestFunction',
            'Global-pipePostResponseFunction',
            'Method-setNoRouteMatch',
            'Method-setNoRouteMatchPage',
            'Method-setNoRouteMatchJson',
            'Method-setNoRouteMatchText',
            'Method-setNoRouteMatchCallback',
            'Method-setNonces',
            'Method-setCSP',
            'Method-setRateLimiting',
            'Method-pipeMiddleware',
            'Method-setHeader',
            'Method-removeHeader',
            'Method-setParamRule',
            'Method-setParamRulePolymorphic',
            'Method-route',
            'Route-setAlias',
            'Route-setRateLimiting',
            'Route-setCache',
            'Route-setNonces',
            'Route-pipeMiddleware',
            'Route-pipeFunction',
            'Route-pipeResponse',
            'Route-pipeSQL',
            'Route-pipeQuery',
            'Route-pipeValidation',
            'Route-pipeCompiledSQL',
            'Route-pipeCompiledQuery',
            'Route-pipeCompiledValidation',
            'Route-setExcludeMiddlewares',
            'Route-setExcludeHeaders',
            'Route-setParamRule',
            'Route-setParamRulePolymorphic',
            'Route-setCSP',
            'Route-setHeader',
            'Route-removeHeader',
            'Route-route',
        ];
        $validMethodTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'CONFIG'];
        // No error (or valid) type
        if (!is_string($errType) || trim($errType) === '' || !in_array($errType, $validErrTypes)) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['type' => 'internal', 'err' => 'Invalid `\$type` (Error Type) Value (OR it is missing) in `class C->setErr()` when setting Error:\'`' . $errMsg . '`\' Report this found bug/issue to the Official FunkPHP Repositories. Choose a `Valid Error Type` from: ' . $this->joinArray($validErrTypes), 'method' => $method, 'route' => $route];
            return;
        }
        // No method (or valid) provided for Method- & Route-related errors (since Route always needs Method)
        if (
            (str_starts_with($errType, 'Method-') || str_starts_with($errType, 'Route-'))
            && (!is_string($method)
                || trim($method) === '' || !in_array($method, $validMethodTypes))
        ) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['type' => 'internal', 'err' => 'Invalid `\$method` (Method Type) Value (OR it is missing) in `class C->setErr()`: must be provided when Error Type starts with `Method-` OR `Route-`. Report this found bug/issue to the Official FunkPHP Repositories. Choose a `Valid Error Type` from: ' . $this->joinArray($validMethodTypes), 'method' => $method, 'route' => $route];
            return;
        }
        if (
            str_starts_with($errType, 'Route-')
            && (!is_string($route)
                || trim($route) === '')
        ) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['type' => 'internal', 'err' => 'Invalid `\$route` Value (OR it is missing) in `class C->setErr()`: must be provided when Error Type starts with `Route-`. Report this found bug/issue to the Official FunkPHP Repositories.', 'method' => $method, 'route' => $route];
            return;
        }
        // = get next error index depending on CONFIG, a METHODS CONFIG, or a METHODS ROUTES
        $nextErrIndex = null;
        if ($method === 'CONFIG') {
            $nextErrIndex = (count($this->errors['CONFIG']) + 1);
        } else {
            if ($route) {
                if (!isset($this->errors['METHODS'][$method]['ROUTES'][$route])) {
                    $this->errors['METHODS'][$method]['ROUTES'][$route] = [];
                }
                $nextErrIndex  = (count($this->errors['METHODS'][$method]['ROUTES'][$route]) + 1);
            } else {
                if (!isset($this->errors['METHODS'][$method]['CONFIG'])) {
                    $this->errors['METHODS'][$method]['CONFIG'] = [];
                }
                $nextErrIndex  = (count($this->errors['METHODS'][$method]['CONFIG']) + 1);
            }
        }
        // append to last API depending on CONFIG, a METHODS CONFIG, or a METHODS ROUTES
        // = $this->FunkPHPFluentAPI[count($this->FunkPHPFluentAPI)] .= ' - (`See Error #' . $nextErrIndex . '`)';
        if ($method === 'CONFIG') {
            $this->FunkPHPFluentAPI['CONFIG'][count($this->FunkPHPFluentAPI['CONFIG'])] .= ' - (`See Error #' . $nextErrIndex . '`)';
        } else {
            if ($route) {
                if (!isset($this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route])) {
                    $this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route] = [];
                }
                $this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route][count($this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route])] .= ' - (`See Error #' . $nextErrIndex . '`)';
            } else {
                if (!isset($this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'])) {
                    $this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'] = [];
                }
                $this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'][count($this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'])] .= ' - (`See Error #' . $nextErrIndex . '`)';
            }
        }
        // add the latest error depending on CONFIG, a METHODS CONFIG, or a METHODS ROUTES
        // = $this->errors[$nextErrIndex] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
        if ($method === 'CONFIG') {
            $this->errors['ERRORS']++;
            $this->errors['CONFIG'][$nextErrIndex] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
        } else {
            if ($route) {
                if (!isset($this->errors['METHODS'][$method]['ROUTES'][$route])) {
                    $this->errors['METHODS'][$method]['ROUTES'][$route] = [];
                }
                $this->errors['ERRORS']++;
                $this->errors['METHODS'][$method]['ROUTES'][$route][$nextErrIndex] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
            } else {
                $this->errors['ERRORS']++;
                if (!isset($this->errors['METHODS'][$method]['CONFIG'])) {
                    $this->errors['METHODS'][$method]['CONFIG'] = [];
                }
                $this->errors['METHODS'][$method]['CONFIG'][$nextErrIndex] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
            }
        }
    }
    /**
     * Set Error Message with specific type ($type) so it can be grouped if needed.
     *
     * Choose Error Type based on scope (global, method, route) and optional method and route when applicable.
     *
     * @param string $errMsg
     * @param 'Internal'|'Global-setDebug'|'Route-pipeCompiledQuery'|'Route-pipeCompiledSQL'|'Route-pipeCompiledValidation'|'Global-setCompileFlag'|'Global-setGroupPipeUserdefined'|'Global-setGroupPipeRequest'|'Global-setGroupPipePostResponse'|'Global-setGroupPipeRoute'|'Global-setGroupPipeMiddlewares'|'Global-setINI_SET'|'Global-setNonces'|'Global-setCSP'|'Global-setSRIInternal'|'Global-setSRIExternal'|'Global-setNoRouteMatchPage'|'Global-setNoRouteMatchJSON'|'Global-setNoRouteMatchText'|'Global-setNoRouteMatchCallback'|'Global-setDefaultExceptionHandler'|'Global-setDefaultErrorHandler'|'Global-setDefaultURI_NormalizerHandler'|'Global-setDefaultKernelHandler'|'Global-setBaseURLLocal'|'Global-setBaseURLOnline'|'Global-setBaseURLHost'|'Global-setBaseURLUri'|'Global-setSessionDriver'|'Global-setSessionCookieOptions'|'Global-setSessionCookieName'|'Global-setSessionCookieLifetime'|'Global-setSessionCookiePath'|'Global-setSessionCookieDomain'|'Global-setSessionCookieSecure'|'Global-setSessionCookieHTTPOnly'|'Global-setSessionCookieSameSite'|'Global-setUseFunkPHPOnline'|'Global-setUseHTTPS'|'Global-setUseVendor'|'Global-setParamRule'|'Global-setHeader'|'Global-removeHeader'|'Global-pipeMiddleware'|'Global-pipeRequestFunction'|'Global-pipePostResponseFunction'|'Method-setNoRouteMatch'|'Method-setNoRouteMatchPage'|'Method-setNoRouteMatchJson'|'Method-setNoRouteMatchText'|'Method-setNoRouteMatchCallback'|'Method-setNonces'|'Method-setCSP'|'Method-setRateLimiting'|'Method-pipeMiddleware'|'Method-setHeader'|'Method-removeHeader'|'Method-setParamRule'|'Method-route'|'Route-setAlias'|'Route-setRateLimiting'|'Route-setCache'|'Route-setNonces'|'Route-pipeMiddleware'|'Route-pipeFunction'|'Route-pipeResponse'|'Route-pipeSQL'|'Route-pipeQuery'|'Route-pipeValidation'|'Route-setExcludeMiddlewares'|'Route-setExcludeHeaders'|'Route-setParamRule'|'Route-setCSP'|'Route-setHeader'|'Route-removeHeader'|'Route-route' $errType
     * @param 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'HEAD'|null $method
     * @param string|null $route
     *
     */
    private function setErrOLD_BUT_WORKED(string $errMsg, string $errType = '', ?string $method = null, ?string $route = null)
    {
        $validErrTypes = [
            'Global-setDebug',
            'Global-setGroupPipeUserdefined',
            'Global-setGroupPipeRequest',
            'Global-setGroupPipePostResponse',
            'Global-setGroupPipeRoute',
            'Global-setGroupPipeMiddlewares',
            'Global-setCompileFlag',
            'Global-setINI_SET',
            'Global-setNonces',
            'Global-setCSP',
            'Global-setSRIInternal',
            'Global-setSRIExternal',
            'Global-setNoRouteMatchPage',
            'Global-setNoRouteMatchJSON',
            'Global-setNoRouteMatchText',
            'Global-setNoRouteMatchCallback',
            'Global-setDefaultExceptionHandler',
            'Global-setDefaultErrorHandler',
            'Global-setDefaultURI_NormalizerHandler',
            'Global-setDefaultKernelHandler',
            'Global-setBaseURLLocal',
            'Global-setBaseURLOnline',
            'Global-setBaseURLHost',
            'Global-setBaseURLUri',
            'Global-setSessionDriver',
            'Global-setSessionCookieOptions',
            'Global-setSessionCookieName',
            'Global-setSessionCookieLifetime',
            'Global-setSessionCookiePath',
            'Global-setSessionCookieDomain',
            'Global-setSessionCookieSecure',
            'Global-setSessionCookieHTTPOnly',
            'Global-setSessionCookieSameSite',
            'Global-setUseFunkPHPOnline',
            'Global-setUseHTTPS',
            'Global-setUseVendor',
            'Global-setParamRule',
            'Global-setHeader',
            'Global-removeHeader',
            'Global-pipeMiddleware',
            'Global-pipeRequestFunction',
            'Global-pipePostResponseFunction',
            'Method-setNoRouteMatch',
            'Method-setNoRouteMatchPage',
            'Method-setNoRouteMatchJson',
            'Method-setNoRouteMatchText',
            'Method-setNoRouteMatchCallback',
            'Method-setNonces',
            'Method-setCSP',
            'Method-setRateLimiting',
            'Method-pipeMiddleware',
            'Method-setHeader',
            'Method-removeHeader',
            'Method-setParamRule',
            'Method-route',
            'Route-setAlias',
            'Route-setRateLimiting',
            'Route-setCache',
            'Route-setNonces',
            'Route-pipeMiddleware',
            'Route-pipeFunction',
            'Route-pipeResponse',
            'Route-pipeSQL',
            'Route-pipeQuery',
            'Route-pipeValidation',
            'Route-pipeCompiledSQL',
            'Route-pipeCompiledQuery',
            'Route-pipeCompiledValidation',
            'Route-setExcludeMiddlewares',
            'Route-setExcludeHeaders',
            'Route-setParamRule',
            'Route-setCSP',
            'Route-setHeader',
            'Route-removeHeader',
            'Route-route',
        ];
        $validMethodTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];
        // No error (or valid) type
        $nextErrIndex = (count($this->errors) + 1);
        if (!is_string($errType) || trim($errType) === '' || !in_array($errType, $validErrTypes)) {
            $this->errors[$nextErrIndex] = ['type' => 'internal', 'err' => 'Invalid \$type (Error Type) Value in `class C->setErr()` when setting Error:\'`' . $errMsg . '`\' Report this found bug/issue to the Official FunkPHP Repositories. Choose a `Valid Error Type` from: ' . $this->joinArray($validErrTypes), 'method' => $method, 'route' => $route];
            return;
        }
        // No method (or valid) provided for Method- & Route-related errors (since Route always needs Method)
        if (
            (str_starts_with($errType, 'Method-') || str_starts_with($errType, 'Route-'))
            && (!is_string($method)
                || trim($method) === '' || !in_array($method, $validMethodTypes))
        ) {
            $this->errors[] = ['type' => 'internal', 'err' => 'Invalid \$method (Method Type) Value in `class C->setErr()`: must be provided when Error Type starts with `Method-` OR `Route-`. Report this found bug/issue to the Official FunkPHP Repositories. Choose a `Valid Error Type` from: ' . $this->joinArray($validMethodTypes), 'method' => $method, 'route' => $route];
            return;
        }
        if (
            str_starts_with($errType, 'Route-')
            && (!is_string($route)
                || trim($route) === '')
        ) {
            $this->errors[$nextErrIndex] = ['type' => 'internal', 'err' => 'Invalid \$route Value in `class C->setErr()`: must be provided when Error Type starts with `Route-`. Report this found bug/issue to the Official FunkPHP Repositories.', 'method' => $method, 'route' => $route];
            return;
        }
        $this->FunkPHPFluentAPI[count($this->FunkPHPFluentAPI)] .= ' - (`See Error #' . $nextErrIndex . '`)';
        $this->errors[$nextErrIndex] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
    }

    // Join array with wrapped `` and comma
    private function joinArray(array $array = [], bool $USE_ARRAY_KEYS = false)
    {
        if ($USE_ARRAY_KEYS) {
            return '`' . join('`, `', array_keys($array)) . '`';
        } else {
            return '`' . join('`, `', $array) . '`';
        }
    }

    // ->config()
    // and can jump to->pipesRequest(),->pipesPostResponse() or ->routes()
    public function CONFIG(): FunkConfig
    {
        $this->setCtx('CONFIG', null, "CONFIG", '');
        return $this->configScope ??= new FunkConfig($this);
    }
    // ->routes() | gives access to:->GET(),->POST(),->PATCH(),->PUT(),->DELETE()
    // and can jump back to ->config()
    public function ROUTES(): FunkRoutes
    {
        $this->setCtx('CONFIG', null, "ROUTES", '');
        return $this->routesScope ??= new FunkRoutes($this);
    }
    // batchFunctions that attempt batching something in $batches that would be validated later unless
    // placed in $invalidBatches based upon initial valid string value like empty string or invalid
    // formatting for a regex or route, and so on! It is structured on "batch<New|Set><LEVEL><WHAT>"
    // Where "New" means you can add several as long as they are not duplicates OR conflict in certain
    // order like "pipeResponse" means you have completed the request cycle for that route and now
    // any piped ->requestPostResponse() should run as a result! Different levels (global, method, route)
    // have different amount of settings/piping they can do (thus what can be batched and not!)

    // Set & New Batches for GLOBAL/CONFIG()! (so ->config()->set|pipe<What>)
    public function batch(string $fn, mixed ...$payload)
    {
        if ($fn === '' || !method_exists($this, $fn)) {
            $this->errors[] = ['type' => 'internal', 'err' => '[Class C->batch()]: Tried calling to a Non-existing Private Function `' . $fn  . '` in Class `C` in `/src/funkphp/core/functions.php`. Please report this Bug/Issue to the `Official FunkPHP Repositories`.'];
            return;
        }
        $this->$fn(...$payload);
    }

    private function batchSetMETHOD(string $method)
    {
        if (!in_array(strtoupper(trim($method)), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'])) {
            $this->errors[] = ['type' => 'internal', 'err' => '[Class C->batchSetMETHOD()]: Invalid Method Choice to set as Context in Class `C` in `/src/funkphp/core/functions.php`. Please report this Bug/Issue to the `Official FunkPHP Repositories`.'];
            return;
        }
        $this->setCtx('CONFIG', null, $method, '');
    }

    /* !!! GLOBAL/CONFIG() BATCHES FUNCTIONS !!! */
    /* setCompileFlag & setDebug */
    private function batchSetCompileFlag(string $flag)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setCompileFlag', "CONFIG()",  $flag);
        $validFlags = [
            'NO_WARNINGS_ALLOWED', // $this->WARNINGS must be 0 after compile() is done or it is considered a failure and discarded
            'COMPILE_ROUTES_SORTED_ASC',
            'COMPILE_ROUTES_SORTED_DESC',
            'ONLY_RETURN_COMPILED_PAGES', // pipeResponse() config will ONLY look for compiled pages and error out if not found during config
            'ONLY_RETURN_NONCOMPILED_PAGES' // pipeResponse() config wil ONLY look for non-compiled pages and error out if not found during config
        ];
        if (isset($this->invalidBatches['config']['compileFlags'][$flag])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setCompileFlag');
            return;
        }
        if (isset($this->validBatches['config']['compileFlags'][$flag])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setCompileFlag');
            return;
        }
        if (!is_string($flag) || trim($flag) === '' || !in_array($flag, $validFlags)) {
            $this->setErr($this->getErr('InvalidCompilerFlag', $ctxVals) . $this->joinArray($validFlags), 'Global-setCompileFlag');
            $this->invalidBatches['config']['compileFlags'][$flag] = true;
            return;
        }
        $this->validBatches['config']['compileFlags'][$flag] = true;
        $this->compileFlags[$flag] = true;
    }

    /**
     * FunkPHP Debug Mode (default is to enable it and always show it, even if zero errors)
     *
     * Debug Internal FunkPHP Configuration State during development|testing. This feature is automatically
     * disabled during compilation. Debug to show Fluent API trail, Errors, Warnings, and in-built variables:
     * `$validBatches`, `$invalidBatches`, `$cached`, and `$compiled`.
     *
     * @param bool $ON_OR_OFF            Enable|disable debugging globally (default: true).
     * @param bool $ALWAYS_SHOW          Enable|disable show debug even if zero errors (default: true).
     * @param bool $SHOW_ALL             Dump all diagnostic targets (`validBatches`, `invalidBatches`, `cached`, `compiled`).
     * @param bool $SHOW_MAIN_CONFIG     Dump `API => CONFIG` or not. Default is `true`. Might get annoying when it is all configured.
     * @param bool $SHOW_VALID_BATCHES   Dump `$validBatches` (staged routes, methods, and config options).
     * @param bool $SHOW_INVALID_BATCHES Dump `$invalidBatches` (rejected configuration calls).
     * @param bool $SHOW_CACHED          Dump `$cached` (parsed files, metadata, placeholders, etc.,).
     * @param bool $SHOW_COMPILED        Dump the final compiled execution matrix generated by `compile()`.
     */
    private function batchSetDebug(bool $ON_OR_OFF = true, bool $ALWAYS_SHOW = true, bool $SHOW_ALL = false, bool $SHOW_MAIN_CONFIG = true, bool $SHOW_VALID_BATCHES = false, bool $SHOW_INVALID_BATCHES = false, bool $SHOW_CACHED = false, bool $SHOW_COMPILED = false)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setDebug', "CONFIG()->setDebug", $ON_OR_OFF, $ALWAYS_SHOW, $SHOW_ALL, $SHOW_MAIN_CONFIG, $SHOW_VALID_BATCHES, $SHOW_INVALID_BATCHES, $SHOW_CACHED, $SHOW_COMPILED);
        if (isset($this->invalidBatches['config']['DEBUG'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setDebug');
            return;
        }
        if (isset($this->validBatches['config']['DEBUG'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setDebug');
            return;
        }
        $this->validBatches['config']['DEBUG'] = [
            'ON_OR_OFF'            => $ON_OR_OFF,
            'ALWAYS_SHOW' => $ALWAYS_SHOW,
            'SHOW_MAIN_CONFIG' => $SHOW_MAIN_CONFIG,
            'SHOW_VALID_BATCHES'   => $SHOW_ALL || $SHOW_VALID_BATCHES,
            'SHOW_INVALID_BATCHES' => $SHOW_ALL || $SHOW_INVALID_BATCHES,
            'SHOW_CACHED'          => $SHOW_ALL || $SHOW_CACHED,
            'SHOW_COMPILED'        => $SHOW_ALL || $SHOW_COMPILED,
            'SHOW_ALL'             => $SHOW_ALL
        ];
        $this->debug = [
            'ON_OR_OFF'            => $ON_OR_OFF,
            'ALWAYS_SHOW' => $ALWAYS_SHOW,
            'SHOW_MAIN_CONFIG' => $SHOW_ALL || $SHOW_MAIN_CONFIG,
            'SHOW_VALID_BATCHES'   => $SHOW_ALL || $SHOW_VALID_BATCHES,
            'SHOW_INVALID_BATCHES' => $SHOW_ALL || $SHOW_INVALID_BATCHES,
            'SHOW_CACHED'          => $SHOW_ALL || $SHOW_CACHED,
            'SHOW_COMPILED'        => $SHOW_ALL || $SHOW_COMPILED,
            'SHOW_ALL'             => $SHOW_ALL
        ];
    }

    /* set<BOOLEAN_VARIANTS_OPTIONS-FunkPHPOnline,UseHTTPS,UseVendor> Global */
    private function batchSetFunkPHPOnlineGlobal(bool $trueOrFalse)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setUseFunkPHPOnline', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['FUNKPHP_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setUseFunkPHPOnline');
            return;
        }
        if (isset($this->validBatches['config']['FUNKPHP_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setUseFunkPHPOnline');
            return;
        }
        if (
            !is_bool($trueOrFalse) || ($trueOrFalse !== FALSE && $trueOrFalse !== TRUE)
        ) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Global-setUseFunkPHPOnline');
            $this->invalidBatches['config']['FUNKPHP_ONLINE'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['FUNKPHP_ONLINE'] = $trueOrFalse;
    }
    private function batchSetUseHTTPSGlobal(bool $trueOrFalse)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setUseHTTPS', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['USE_HTTPS'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setUseHTTPS');
            return;
        }
        if (isset($this->validBatches['config']['USE_HTTPS'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setUseHTTPS');
            return;
        }
        if (
            !is_bool($trueOrFalse) || ($trueOrFalse !== FALSE && $trueOrFalse !== TRUE)
        ) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Global-setUseHTTPS');
            $this->invalidBatches['config']['USE_HTTPS'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['USE_HTTPS'] = $trueOrFalse;
    }
    private function batchSetUseVendorGlobal(bool $trueOrFalse)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setUseVendor', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['USE_VENDOR'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setUseVendor');
            return;
        }
        if (isset($this->validBatches['config']['USE_VENDOR'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setUseVendor');
            return;
        }
        if (
            !is_bool($trueOrFalse) || ($trueOrFalse !== FALSE && $trueOrFalse !== TRUE)
        ) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Global-setUseVendor');
            $this->invalidBatches['config']['USE_VENDOR'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['USE_VENDOR'] = $trueOrFalse;
    }

    /* setUseDefault<Exception,Error,UriNormalizer,In-builtKernel-UserDefinedFunctions> Global */
    private function batchSetDefaultExceptionHandlerGlobal(string $userDefinedFunction) // DEFAULT EXCEPTION HANDLER
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setDefaultExceptionHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setDefaultExceptionHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setDefaultExceptionHandler');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Global-setDefaultExceptionHandler');
            $this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result.";
            $this->setErr($err, 'Global-setDefaultExceptionHandler');
            $this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Prepare Config Functions.php File I/O if needed
        // assuming ROOT_FOLDER constant exists first!
        if (!$this->rootFolderExistOrSetError()) return;
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunction, $ctxVals, '', false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-setDefaultExceptionHandler');
            $this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Unique Function checks for SetExceptionHandler: it must contain "\throwable $<varName>"
        // and this is checked AFTER it starts with &$c so no issues there!
        if (
            !preg_match('/\\\\Throwable\s+\$[_a-z][_a-z0-9]*/i', $fileData['functions'][$userDefinedFunction]['args_raw'])
        ) {
            $err = $this->getErr('UserDefinedFUNCTIONHasWrongArgs', $ctxVals) . ' `\Throwable \$e` (e.g. `function userDefined(&\$c, \Throwable \$e){}`) in order to use it as a User-defined Exception Handler. The variable `$e` can be named something else as well.' . " Found instead:`{$fileData['functions'][$userDefinedFunction]['args_raw']}`.";
            $this->setErr($err, 'Global-setDefaultExceptionHandler');
            $this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultExceptionHandler('{$userDefinedFunction}')";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultExceptionHandler('{$userDefinedFunction}')";
    }
    private function batchSetDefaultErrorHandlerGlobal(string $userDefinedFunction) // DEFAULT GLOBAL ERROR HANDLER
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setDefaultErrorHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setDefaultErrorHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setDefaultErrorHandler');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Global-setDefaultErrorHandler');
            $this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result.";
            $this->setErr($err, 'Global-setDefaultErrorHandler');
            $this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Prepare Config Functions.php File I/O if needed
        // assuming ROOT_FOLDER constant exists first!
        if (!$this->rootFolderExistOrSetError()) return;
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunction, $ctxVals, '', false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-setDefaultErrorHandler');
            $this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Unique Function checks for SetErrorHandler: it must contain "$errNo, $errStr, $errFile, $errLine"
        // and this is checked AFTER it starts with &$c so no issues there! The variables can be typed or not.
        if (
            !preg_match('/^&\$c\s*,\s*(?:int\s+)?\$[_a-z0-9]+\s*,\s*(?:string\s+)?\$[_a-z0-9]+\s*,\s*(?:string\s+)?\$[_a-z0-9]+\s*,\s*(?:int\s+)?\$[_a-z0-9]+$/i', $fileData['functions'][$userDefinedFunction]['args_raw'])
        ) {
            $err = $this->getErr('UserDefinedFUNCTIONHasWrongArgs', $ctxVals) . '` $errNo, $errStr, $errFile, $errLine` (e.g. `function userDefined(&\$c, $errNo, $errStr, $errFile, $errLine){}`) in order to use it as a User-defined Error Handler. The `$errNo,$errStr,$errFile,$errLine` can be named something else as well.' . " Found instead:`{$fileData['functions'][$userDefinedFunction]['args_raw']}`.";
            $this->setErr($err, 'Global-setDefaultErrorHandler');
            $this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultErrorHandler('{$userDefinedFunction}')";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultErrorHandler('{$userDefinedFunction}')";
    }
    private function batchSetDefaultURINormalizerGlobal(string $userDefinedFunction) // URI NORMALIZER GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setDefaultURI_NormalizerHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setDefaultURI_NormalizerHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals),  'Global-setDefaultURI_NormalizerHandler');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals),  'Global-setDefaultURI_NormalizerHandler');
            $this->invalidBatches['config']['DEFAULT_URI_NORMALIZER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result.";
            $this->setErr($err,  'Global-setDefaultURI_NormalizerHandler');
            $this->invalidBatches['config']['DEFAULT_URI_NORMALIZER'] = $userDefinedFunction;
            return;
        }
        // Prepare Config Functions.php File I/O if needed
        // assuming ROOT_FOLDER constant exists first!
        if (!$this->rootFolderExistOrSetError()) return;
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunction, $ctxVals, '', false);
        if ($fatalError !== null) {
            $this->setErr($fatalError,  'Global-setDefaultURI_NormalizerHandler');
            $this->invalidBatches['config']['DEFAULT_URI_NORMALIZER'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_URI_NORMALIZER'] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultURI_NormalizerHandler('{$userDefinedFunction}')";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultURI_NormalizerHandler('{$userDefinedFunction}')";
    }
    private function batchSetDefaultHTTPSKernelDispatchHandlerGlobal(string $userDefinedFunction) // DEFAULT HTTSP KERNEL/ROUTING
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setDefaultKernelHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setDefaultKernelHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setDefaultKernelHandler');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Global-setDefaultKernelHandler');
            $this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result.";
            $this->setErr($err, 'Global-setDefaultKernelHandler');
            $this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
            return;
        }
        // Prepare Config Functions.php File I/O if needed
        // assuming ROOT_FOLDER constant exists first!
        if (!$this->rootFolderExistOrSetError()) return;
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunction, $ctxVals, '', false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-setDefaultKernelHandler');
            $this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultKernelHandler('{$userDefinedFunction}')";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultKernelHandler('{$userDefinedFunction}')";
    }

    /* setNoRouteMatch<VARIANTS> Global - These are all catches when no catches for specific <method(s)> are defined/applied */
    private function batchSetNoRouteMatchPageGlobal(string $PageFileName, int $statusCode = 404) // NO MATCH: PAGE - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchPage', "CONFIG()", $PageFileName);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setNoRouteMatchPage');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setNoRouteMatchPage');
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Global-setNoRouteMatchPage');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        if (!is_string($PageFileName) || (trim($PageFileName) === '') || !preg_match('/[a-zA-Z0-9-_]+/i', $PageFileName)) {
            $this->setErr($this->getErr('InvalidPageName', $ctx), 'Global-setNoRouteMatchPage');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        // Now check if Page File exists either in "/src/funkphp/pages/$PageFileName.php"
        // or in "/src/funkphp/pages/compiled/$PageFileName.php". First hydrate if not yet.
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages', $PageFileName);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages_compiled', $PageFileName);
        // Prioritize Compiled Pages, then possibly non-compiled pages (they could still contain no template engine)
        $pageFound = false;
        if (
            isset($this->cached['files_pages_compiled'][$PageFileName]['file_exists'])
            && $this->cached['files_pages_compiled'][$PageFileName]['file_exists'] === true
        ) {
            $pageFound = true;
        } else if (
            isset($this->cached['files_pages'][$PageFileName]['file_exists'])
            && $this->cached['files_pages'][$PageFileName]['file_exists'] === true
        ) {
            $pageFound = true;
        }
        // No Page at all found?
        if (!$pageFound) {
            $this->setErr($this->getErr('NoPageAtAllFound', $ctxVals) . " using Filename `{$PageFileName}.php`.", 'Global-setNoRouteMatchPage');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        $this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'] = ['page' => $PageFileName, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchJsonGlobal(array|object $data, int $statusCode = 404)  // NO MATCH: JSON - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchJSON', "CONFIG()", $data, $statusCode);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setNoRouteMatchJSON');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setNoRouteMatchJSON');
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Global-setNoRouteMatchJSON');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        // Really check it is an empty object
        $isEmptyObject = is_object($data) && (
            ($data instanceof \Countable && count($data) === 0) ||
            (get_object_vars($data) === [] && !($data instanceof \JsonSerializable))
        );
        if (is_array($data) && empty($data) || $isEmptyObject) {
            $this->setErr($this->getErr('JsonEncodingFailedNoData', $ctx), 'Global-setNoRouteMatchJSON');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $JSON = null;
        try {
            $JSON = json_encode($data, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            $this->setErr($this->getErr('JsonEncodingFailed', $ctx), 'Global-setNoRouteMatchJSON');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $this->validBatches['config']['NO_ROUTE_MATCH']['JSON'] = ['JSON' => $JSON, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchTextGlobal(string $message, int $statusCode = 404)  // NO MATCH: TEXT - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchText', "CONFIG()", $message, $statusCode);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setNoRouteMatchText');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setNoRouteMatchText');
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Global-setNoRouteMatchText');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        if (!is_string($message) || (trim($message) === '')) {
            $this->setErr($this->getErr('InvalidNoRouteMatchTextValue', $ctx), 'Global-setNoRouteMatchText');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        $this->validBatches['config']['NO_ROUTE_MATCH']['TEXT'] = ['text' => $message, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchCallbackGlobal(string $userDefinedFunctionName)  // NO MATCH: CALLBACK - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchCallback', "CONFIG()", $userDefinedFunctionName);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setNoRouteMatchCallback');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setNoRouteMatchCallback');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunctionName)) {
            $this->setErr($this->getErr('InvalidFunctionName', $ctx), 'Global-setNoRouteMatchCallback');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // Hydrate user defined functions if not already
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions', $userDefinedFunctionName);
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunctionName, $ctxVals);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-setNoRouteMatchCallback');
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // After initial check, check that it is not already used by Global Handlers (->CONFIG()->setDefault<Handler>)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName])) {
            $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' See: `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName] . '`', $this->errors['config']);
            return;
        }
        // Finally add it
        $this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
    }

    /* setBASEURL<VARIANTS> Global */
    private function batchSetDefaultBaseURLLocalGlobal(string $httpPath)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLLocal', "CONFIG()", $httpPath);
        if (isset($this->invalidBatches['config']['BASEURL_LOCAL'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setBaseURLLocal');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_LOCAL'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setBaseURLLocal');
            return;
        }
        if (
            !is_string($httpPath) || trim($httpPath) === ''
            || !preg_match('/^http:\/\//', $httpPath)
        ) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringSTARTWithHTTP', $ctxVals), 'Global-setBaseURLLocal');
            $this->invalidBatches['config']['BASEURL_LOCAL'] = $httpPath;
            return;
        }
        $this->validBatches['config']['BASEURL_LOCAL'] = $httpPath;
    }
    private function batchSetDefaultBaseURLOnlineGlobal(string $httpsPath)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLLocal', "CONFIG()", $httpsPath);
        if (isset($this->invalidBatches['config']['BASEURL_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setBaseURLOnline');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setBaseURLOnline');
            return;
        }
        if (
            !is_string($httpsPath) || trim($httpsPath) === ''
            || !preg_match('/^https:\/\//', $httpsPath)
        ) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringSTARTWithHTTPS', $ctxVals), 'Global-setBaseURLOnline');
            $this->invalidBatches['config']['BASEURL_ONLINE'] = $httpsPath;
            return;
        }
        $this->validBatches['config']['BASEURL_ONLINE'] = $httpsPath;
    }
    private function batchSetDefaultBaseURLHostGlobal(string $hostNameLocally)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLHost', "CONFIG()", $hostNameLocally);
        if (isset($this->invalidBatches['config']['BASEURL_HOST'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setBaseURLHost');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_HOST'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setBaseURLHost');
            return;
        }
        if (!is_string($hostNameLocally) || trim($hostNameLocally) === '') {
            $this->setErr($this->getErr('NonEmptyStringNoTrailing', $ctxVals), 'Global-setBaseURLHost');
            $this->invalidBatches['config']['BASEURL_HOST'] = $hostNameLocally;
            return;
        }
        $this->validBatches['config']['BASEURL_HOST'] = $hostNameLocally;
    }
    private function batchSetDefaultBaseURLUriGlobal(string $localURI)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLHost', "CONFIG()", $localURI);
        if (isset($this->invalidBatches['config']['BASEURL_URI'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setBaseURLUri');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_URI'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setBaseURLUri');
            return;
        }
        if (!is_string($localURI) || trim($localURI) === '') {
            $this->setErr($this->getErr('NonEmptyStringNoTrailing', $ctxVals), 'Global-setBaseURLUri');
            $this->invalidBatches['config']['BASEURL_URI'] = $localURI;
            return;
        }
        $this->validBatches['config']['BASEURL_URI'] = $localURI;
    }
    private function batchSetDefaultSessionCookieOptionsGlobal(array $SessionCookieOptions)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieOptions', "CONFIG()", $SessionCookieOptions);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieOptions');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setSessionCookieOptions');
            return;
        }
        $allowedKeys = [
            'SESSION_DRIVER',
            'SESSION_NAME',
            'SESSION_LIFETIME',
            'SESSION_PATH',
            'SESSION_DOMAIN',
            'SESSION_SECURE',
            'SESSION_HTTPONLY',
            'SESSION_SAMESITE',
        ];
        if (empty($SessionCookieOptions) || array_is_list($SessionCookieOptions)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array with these Session Cookie Options: `" . implode('`, `', $allowedKeys) . "`.", 'Global-setSessionCookieOptions');
            $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
            return;
        }
        foreach ($allowedKeys as $k) {
            if (!isset($SessionCookieOptions[$k])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array with these Session Cookie Options: `" . implode('`, `', $allowedKeys) . "`. Missing Key: `'" . "{$k}'`", 'Global-setSessionCookieOptions');
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
        }
        // Validate Session Cookie Options are just Assoc_key => Scalar_Value
        foreach ($SessionCookieOptions as $key => $val) {
            if (!is_scalar($val)) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Invalid Value for Session Cookie Option `{$key}`. It must be a Scalar Value (Non-Empty String, Non-Negative Integer|Float, or Boolean) using these Session Cookie Keys:`" . implode('`, `', $allowedKeys) . "`.", 'Global-setSessionCookieOptions');
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
            if (!in_array($key, $allowedKeys, true)) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Invalid Value for Session Cookie Option `{$key}`. Use these Session Cookie Keys:`" . implode('`, `', $allowedKeys) . "`.", 'Global-setSessionCookieOptions');
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
            if ($key === 'SESSION_DRIVER' && isset($this->validBatches['config']['SESSION']['driver'])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " The Session Cookie Option `{$key}` already exists as a Valid Session Cookie Value under `->CONFIG()`.", 'Global-setSessionCookieOptions');
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
            if (isset($this->validBatches['config']['SESSION']['COOKIES'][$key])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " The Session Cookie Option `{$key}` already exists as a Valid Session Cookie Value under `->CONFIG()`.", 'Global-setSessionCookieOptions');
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            } else if (isset($this->invalidBatches['config']['SESSION']['COOKIES'][$key])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " The Session Cookie Option `{$key}` already exists as a Invalid Session Cookie Value under `->CONFIG()`.", 'Global-setSessionCookieOptions');
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
        }
        // Then validate each individual session cookie option
        $validated = [];
        foreach ($SessionCookieOptions as $key => $val) {
            switch ($key) {
                case 'SESSION_DRIVER':
                    if (!is_string($val) || trim($val) === '' || !in_array(strtolower(trim($val)), ['files', 'redis', 'memcached', 'database', 'array'])) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_DRIVER` Value. Must be a Non-Empty String without trailing spaces that is one of the following values: " . $this->joinArray(['files', 'redis', 'memcached', 'database', 'array']), 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_NAME':
                    if (!is_string($val) || trim($val) === '') {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_NAME` Value. Must be a Non-Empty String without trailing spaces.", 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = trim($val);
                    break;
                case 'SESSION_LIFETIME':
                    if (!is_int($val) || $val < 0) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_LIFETIME` Value. Must be a Non-Negative Integer.", 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_PATH':
                    if (
                        !is_string($val) || !str_starts_with($val, '/')
                        || !preg_match('/^\/([a-zA-Z0-9-_]+\/?)*$/i', $val)
                    ) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_PATH` Value. Must be a Non-Empty String starting with or only being:`/` and then use [a-zA-Z0-9_-#] characters only in each `/segment`. You may include a single trailing slash at the end if technically needed.", 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_DOMAIN':
                    if (
                        !is_string($val) || trim($val) === ''
                        || str_contains($val, '://')
                        || str_contains($val, ':')
                        || str_contains($val, '/')
                        || preg_match('/[\s;]/', $val)
                    ) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_DOMAIN` Value. Must be a Non-Empty String without schemes and ports:`://`, `:`, `/`.", 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_SECURE':
                case 'SESSION_HTTPONLY':
                    if (!is_bool($val)) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `{$key}` Value. Must be a Boolean as either `TRUE` or `FALSE`.", 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_SAMESITE':
                    if (!is_string($val) || trim($val) === '' || !in_array((ucfirst(strtolower(trim($val)))), ['Lax', 'Strict', 'None'], true)) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_SAMESITE` Value. Must be one of these Non-Empty String Values:`Lax, Strict, None`.", 'Global-setSessionCookieOptions');
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = ucfirst(strtolower(trim($val)));
                    break;
            }
        }
        // Finally add all to the specific Session Cookie Variables and assign as valid batch
        foreach ($validated as $k => $v) {
            if ($k === 'SESSION_DRIVER') {
                $this->validBatches['config']['SESSION']['driver'] = $v;
                continue;
            }
            $this->validBatches['config']['SESSION']['COOKIES'][$k] = $v;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = true;
    }

    /* setSESSIONDriver Global & then setSESSION_COOKIE<VARIANTS> Global */
    private function batchSetDefaultSessionDriverGlobal(string $filesOrRedisOrSomethingElse = 'files')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionDriver', "CONFIG()", $filesOrRedisOrSomethingElse);
        if (isset($this->invalidBatches['config']['SESSION']['driver'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionDriver');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['driver'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setSessionDriver');
            return;
        }
        if (
            !is_string($filesOrRedisOrSomethingElse) || trim($filesOrRedisOrSomethingElse) === ''
            || !in_array(strtolower($filesOrRedisOrSomethingElse), ['files', 'redis', 'memcached', 'database', 'array'], true)
        ) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctxVals) . " must be one of these Non-Empty String Values:`files, redis, memcached, database, array`.", 'Global-setSessionDriver');
            $this->invalidBatches['config']['SESSION']['driver'] = $filesOrRedisOrSomethingElse;
            return;
        }
        $this->validBatches['config']['SESSION']['driver'] = $filesOrRedisOrSomethingElse;
    }
    private function batchSetDefaultSessionCookieNameGlobal(string $sessionCookieName = 'fphp_id')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieName', "CONFIG()", $sessionCookieName);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieName');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookieName');
            return;
        }
        if (!is_string($sessionCookieName) || trim($sessionCookieName) === '') {
            $this->setErr($this->getErr('NonEmptyStringNoTrailing', $ctxVals), 'Global-setSessionCookieName');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_NAME'] = $sessionCookieName;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'] = $sessionCookieName;
    }
    private function batchSetDefaultSessionCookieLifetimeGlobal(int $sessionCookieLifetime = 28800)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieLifetime', "CONFIG()", $sessionCookieLifetime);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieLifetime');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookieLifetime');
            return;
        }
        if (!is_int($sessionCookieLifetime) || $sessionCookieLifetime < 0) {
            $this->setErr($this->getErr('NotIntegerNotNegative', $ctxVals), 'Global-setSessionCookieLifetime');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'] = $sessionCookieLifetime;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'] = $sessionCookieLifetime;
    }
    private function batchSetDefaultSessionCookiePathGlobal(string $sessionCookiePath = '/')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookiePath', "CONFIG()", $sessionCookiePath);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookiePath');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookiePath');
            return;
        }
        if (
            !is_string($sessionCookiePath) || trim($sessionCookiePath) === ''
            || !str_starts_with($sessionCookiePath, '/') || !preg_match('/^\/([a-zA-Z0-9-_]+\/?)*$/i', $sessionCookiePath)
        ) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctxVals) . " must be a Non-Empty String starting with or only being:`/` and then use `[a-zA-Z0-9_-#]` characters only in each `/segment`. You may include a single trailing slash at the end if technically needed.", 'Global-setSessionCookiePath');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_PATH'] = $sessionCookiePath;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'] = $sessionCookiePath;
    }
    private function batchSetDefaultSessionCookieDomainGlobal(string $sessionCookieDomain = 'webdev.local')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieDomain', "CONFIG()", $sessionCookieDomain);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieDomain');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookieDomain');
            return;
        }
        if (
            !is_string($sessionCookieDomain) || trim($sessionCookieDomain) === ''
            || str_contains($sessionCookieDomain, '://')
            || str_contains($sessionCookieDomain, ':')
            || str_contains($sessionCookieDomain, '/')
            || preg_match('/[\s;]/', $sessionCookieDomain)
        ) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctxVals) . " Must be a Non-Empty String without schemes and ports:`://`, `:`, `/`.", 'Global-setSessionCookieDomain');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'] = $sessionCookieDomain;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'] = $sessionCookieDomain;
    }
    private function batchSetDefaultSessionCookieSecureGlobal(bool $trueOrFalse = false)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieSecure', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieSecure');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookieSecure');
            return;
        }
        if (!is_bool($trueOrFalse)) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Global-setSessionCookieSecure');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'] = $trueOrFalse;
    }
    private function batchSetDefaultSessionCookieHTTPOnlyGlobal(bool $trueOrFalse = true)
    {

        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieHTTPOnly', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieHTTPOnly');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookieHTTPOnly');
            return;
        }
        if (!is_bool($trueOrFalse)) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Global-setSessionCookieHTTPOnly');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = $trueOrFalse;
    }
    private function batchSetDefaultSessionCookieSameSiteGlobal(string $LaxOrStrict = 'Lax')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieSameSite', "CONFIG()", $LaxOrStrict);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSessionCookieSameSite');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Global-setSessionCookieSameSite');
            return;
        }
        if (!is_string($LaxOrStrict) || trim($LaxOrStrict) === '' || !in_array($LaxOrStrict, ['Lax', 'Strict', 'None'], true)) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctx) . " must be one of these Non-Empty String Values:`Lax, Strict, None`.", 'Global-setSessionCookieSameSite');
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'] = $LaxOrStrict;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'] = $LaxOrStrict;
    }

    /* setINI_SET for "ini_set()" calls Global */
    private function batchSetINI_SETGlobal(array $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setINI_SET', "CONFIG()", $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue);
        if (isset($this->invalidBatches['config']['setINI_SET'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setINI_SET');
            return;
        }
        if (isset($this->validBatches['config']['setINI_SET'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setINI_SET');
            return;
        }
        if (empty($iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue) || array_is_list($iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a non-empty associative array (e.g., `['setting' => 'value']`).", 'Global-setINI_SET');
            $this->invalidBatches['config']['setINI_SET'] = $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue;
            return;
        }
        foreach ($iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue as $k => $v) {
            $isValidKey   = is_string($k) && trim($k) !== '';
            $isValidValue = is_scalar($v) && (!is_string($v) || trim($v) !== '');
            if (!$isValidKey || !$isValidValue) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Keys must be Non-Empty Strings and values must be Non-Empty Scalar Types (string, int, float, bool).", 'Global-setINI_SET');
                $this->invalidBatches['config']['setINI_SET'] = $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue;
                return;
            }
        }
        $this->validBatches['config']['setINI_SET'] = $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue;
    }
    private function batchSetRateLimitingGlobal(int $maxRequestsPerWindowSize = 60, int $windowSizeInSeconds = 60, $by = 'ip', $driver = 'redis') {}

    /* setGrouped<VARIANTS> Global */
    private function batchSetGroupedPipeUserDefined(string $groupName, string ...$userDefFNS)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setGroupPipeUserdefined', "CONFIG()", $groupName, ...$userDefFNS);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Global-setGroupPipeUserdefined');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Global-setGroupPipeUserdefined');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Global-setGroupPipeUserdefined');
            $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
            return;
        }
        if (count($userDefFNS) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Request Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($userDefFNS), 'Global-setGroupPipeUserdefined');
            $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
            return;
        }
        foreach ($userDefFNS as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Global-setGroupPipeUserdefined');
                $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($userDefFNS) !== count(array_unique($userDefFNS))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($userDefFNS), 'Global-setGroupPipeUserdefined');
            $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
            return;
        }
        // Now we check each Function File using $this->cached which will store it in
        // $this->cached['files_pipes_request][$FN_FILE] if it does not already exist.
        // Then we can attempt validation that it is a valid structured file+function:
        // 1. Only one function per file, 2. Function body cannot be empty or just comments,
        // 3. Function body must start with "&$c" in its function parameters.
        foreach ($userDefFNS as $FN_FILE) {
            $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions', $FN_FILE);
            $fileData = $this->cached['file_user_defined_functions'] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "");
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Global-setGroupPipeUserdefined');
                $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
                return;
            }
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$FN_FILE])) {
                $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' See: `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$FN_FILE] . '`', 'Global-setGroupPipeUserdefined');
                return;
            }
        }
        // Set when all OK!
        $this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
    }
    private function batchSetGroupedPipeRequest(string $groupName, string ...$RequestFNs)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setGroupPipeRequest', "CONFIG()", $groupName, ...$RequestFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Global-setGroupPipeRequest');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_REQUEST'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Global-setGroupPipeRequest');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Global-setGroupPipeRequest');
            $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
            return;
        }
        if (count($RequestFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Request Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($RequestFNs), 'Global-setGroupPipeRequest');
            $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
            return;
        }
        foreach ($RequestFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Global-setGroupPipeRequest');
                $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($RequestFNs) !== count(array_unique($RequestFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($RequestFNs), 'Global-setGroupPipeRequest');
            $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
            return;
        }
        // Now we check each Function File using $this->cached which will store it in
        // $this->cached['files_pipes_request][$FN_FILE] if it does not already exist.
        // Then we can attempt validation that it is a valid structured file+function:
        // 1. Only one function per file, 2. Function body cannot be empty or just comments,
        // 3. Function body must start with "&$c" in its function parameters.
        foreach ($RequestFNs as $FN_FILE) {
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_request', $FN_FILE);
            $fileData = $this->cached['files_pipes_request'][$FN_FILE] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "funkphp\\pipes\\request\\{$FN_FILE}", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Global-setGroupPipeRequest');
                $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
                return;
            }
        }
        // Set when all OK!
        $this->validBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
    }
    private function batchSetGroupedPipePostResponse(string $groupName, string ...$PostResponseFNs)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setGroupPipePostResponse', "CONFIG()", $groupName, ...$PostResponseFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Global-setGroupPipePostResponse');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Global-setGroupPipePostResponse');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Global-setGroupPipePostResponse');
            $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
            return;
        }
        if (count($PostResponseFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Post-Response Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($PostResponseFNs), 'Global-setGroupPipePostResponse');
            $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
            return;
        }
        foreach ($PostResponseFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Global-setGroupPipePostResponse');
                $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($PostResponseFNs) !== count(array_unique($PostResponseFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($PostResponseFNs), 'Global-setGroupPipePostResponse');
            $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
            return;
        }
        // Now we check each File using $this->cached
        // Now we check each Function File using $this->cached which will store it in
        // $this->cached['files_pipes_post_response][$FN_FILE] if it does not already exist.
        // Then we can attempt validation that it is a valid structured file+function:
        // 1. Only one function per file, 2. Function body cannot be empty or just comments,
        // 3. Function body must start with "&$c" in its function parameters.
        foreach ($PostResponseFNs as $FN_FILE) {
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_post_response', $FN_FILE);
            $fileData = $this->cached['files_pipes_post_response'][$FN_FILE] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "funkphp\\pipes\\post_response\\{$FN_FILE}", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Global-setGroupPipePostResponse');
                $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
                return;
            }
        }
        // Set when all OK!
        $this->validBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
    }
    private function batchSetGroupedPipeRoute(string $groupName, string ...$RoutePipeFNs)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setGroupPipeRoute', "CONFIG()", $groupName, ...$RoutePipeFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Global-setGroupPipeRoute');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_ROUTES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Global-setGroupPipeRoute');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Global-setGroupPipeRoute');
            $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
            return;
        }
        if (count($RoutePipeFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Route Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($RoutePipeFNs), 'Global-setGroupPipeRoute');
            $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
            return;
        }
        foreach ($RoutePipeFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrThatIsFileAndFunctionWithDot($FN)) {
                $this->setErr($this->getErr('InvalidFileAndFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Global-setGroupPipeRoute');
                $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($RoutePipeFNs) !== count(array_unique($RoutePipeFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($RoutePipeFNs), 'Global-setGroupPipeRoute');
            $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
            return;
        }
        // Now we check each File using $this->cached
        foreach ($RoutePipeFNs as $FN_FILE) {
            [$file, $fn] = explode('.', $FN_FILE);
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_routes', $file);
            $fileData = $this->cached['files_routes'][$file] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $fn, $ctxVals, "funkphp\\pipes\\routes\\{$file}", false);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Global-setGroupPipeRoute');
                $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
                return;
            }
        }
        // ALL OK!
        $this->validBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
    }
    private function batchSetGroupedPipeMiddlewares(string $groupName, string ...$middlewareFNs)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setGroupPipeMiddlewares', "CONFIG()", $groupName, ...$middlewareFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Global-setGroupPipeMiddlewares');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Global-setGroupPipeMiddlewares');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Global-setGroupPipeMiddlewares');
            $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
            return;
        }
        if (count($middlewareFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Middleware Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($middlewareFNs), 'Global-setGroupPipeMiddlewares');
            $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
            return;
        }
        foreach ($middlewareFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Global-setGroupPipeMiddlewares');
                $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($middlewareFNs) !== count(array_unique($middlewareFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($middlewareFNs), 'Global-setGroupPipeMiddlewares');
            $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
            return;
        }
        // Now we check each File using $this->cached
        // Now we check each Function File using $this->cached which will store it in
        // $this->cached['files_pipes_middlewares][$FN_FILE] if it does not already exist.
        // Then we can attempt validation that it is a valid structured file+function:
        // 1. Only one function per file, 2. Function body cannot be empty or just comments,
        // 3. Function body must start with "&$c" in its function parameters.
        foreach ($middlewareFNs as $FN_FILE) {
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $FN_FILE);
            $fileData = $this->cached['files_pipes_middlewares'][$FN_FILE] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "funkphp\\pipes\\middlewares\\{$FN_FILE}", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Global-setGroupPipeMiddlewares');
                $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
                return;
            }
        }
        // Set when all OK!
        $this->validBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
    }

    /* setParamRule GLOBAL */
    private function batchSetParamRuleGlobal(string $param, string $regex, $defaultParamValueOnRegexMismatch = null)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setParamRule', "CONFIG()", $param, $regex, $defaultParamValueOnRegexMismatch);
        if (isset($this->invalidBatches['paramRules']['config'][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Param Identifier must be unique.", 'Global-setParamRule');
            return;
        }
        if (isset($this->validBatches['config']['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Param Identifier must be unique.", 'Global-setParamRule');
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Global-setParamRule');
            $this->invalidBatches['paramRules']['config'][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Validate valid $regex pattern
        $regexValid = true;
        try {
            if (@preg_match($regex, '') === false) {
                $regexValid = false;
            }
        } catch (\Throwable $e) {
            $regexValid = false;
        }
        if (!$regexValid || preg_match('#\/\/[gimsuy]*#', $regex)) {
            $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Global-setParamRule');
            $this->invalidBatches['paramRules']['config'][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Check for duplicate valid rule at global level
        if (isset($this->validBatches['config']['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateParamGlobal', $ctxVals), 'Global-setParamRule');
            return;
        }
        // Finally store valid global param rule
        $this->validBatches['config']['paramRules'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch
        ];
        $this->cached['placeholderUNSUEDParams']['GLOBAL'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch
        ];
    }

    /* setCSP Global */
    private function batchSetCSPGlobal(string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setCSP', "CONFIG()", $directive, ...$sources);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['config'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `\$directive` can only be used/set once.", 'Global-setCSP');
            return;
        }
        if (isset($this->validBatches['config']['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `\$directive` can only be used/set once.", 'Global-setCSP');
            return;
        }
        $allowedDirectives = $this->ALLOWED['csp-directives'];
        if ($directive === '' || !in_array($directive, $allowedDirectives, true)) {
            $this->setErr($this->getErr('InvalidCSPDirective', $ctxVals) . $this->joinArray($allowedDirectives), 'Global-setCSP');
            return;
        }
        if (empty($sources)) {
            $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Global-setCSP');
            $this->invalidBatches['csp']['config'][$directive] = $sources;
            return;
        }
        $formattedSources = $this->formatCSPSources($sources);
        if (in_array("'none'", $formattedSources, true) && count($formattedSources) > 1) {
            $this->setErr($this->getErr('ConflictNoneSourceInCSP', $ctxVals), 'Global-setCSP');
            $this->invalidBatches['csp']['config'][$directive] = $sources;
            return;
        }
        $nonces = [];
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Global-setCSP');
                $this->invalidBatches['csp']['config'][$directive] = $sources;
                return;
            }
            // Is it a nonce that is supposed to be in the 'nonces' array instead?
            $trimmed = trim($source);
            if (str_starts_with(strtolower($trimmed), 'nonce:')) {
                if (in_array(strtolower($trimmed), $nonces)) {
                    $this->setErr($this->getErr('DuplicateNonceName', $ctxVals) . "`{$trimmed}`. You can only use each Unique Nonce Name once per CSP Directive.", 'Global-setCSP');
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
                if (!preg_match('/^nonce:[a-zA-Z0-9-_\.]+$/', strtolower($trimmed))) {
                    $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Global-setCSP');
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
                if (isset($this->validBatches['config']['nonces'][strtolower($trimmed)])) {
                    $this->setErr($this->getErr('DuplicateNonceDirectiveUse', $ctxVals) . "`Nonce Name {$trimmed} ` is already being used by CSP Directive: `{$this->validBatches['config']['nonces'][strtolower($trimmed)]}`.", 'Global-setCSP');
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
                $nonces[] = strtolower($trimmed);
                $this->validBatches['config']['nonces'][strtolower($trimmed)] = $directive;
                continue;
            }
            if (
                $trimmed === ''
                || str_contains($trimmed, ';')
                || str_contains($trimmed, "\r")
                || str_contains($trimmed, "\n")
                || preg_match('/\s/', $trimmed)
            ) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Global-setCSP');
                $this->invalidBatches['csp']['config'][$directive] = $sources;
                return;
            }
            if (str_contains($trimmed, '*') && $trimmed !== '*') {
                if (!preg_match('/^(https?:\/\/)?\*\.[a-zA-Z0-9\.-]+(:\d+)?$/', $trimmed)) {
                    $this->setErr($this->getErr('InvalidCSPWildcardUse', $ctxVals), 'Global-setCSP');
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
            }
        }
        $this->validBatches['config']['csp'][$directive] = array_filter($formattedSources, function ($src) {
            return str_starts_with('nonce:', $src);
        });
    }

    /* setSRI Internal&External Global */

    /* setSRIInternal & setSRIExternal - GLOBAL */
    private function batchSetSRIInternalGlobal(array $internalSRI)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSRIInternal', "CONFIG()", $internalSRI);
        if (isset($this->invalidBatches['global_sris']['internal']['config'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " Set everything once in a Single Array.", 'Global-setSRIInternal');
            return;
        }
        if (isset($this->validBatches['config']['global_sris']['internal'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals) . " Set everything once in a Single Array.", 'Global-setSRIInternal');
            return;
        }
        if (!isset($internalSRI) || empty($internalSRI) || array_is_list($internalSRI)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array whose Keys are Non-Empty Strings and where each Key contains `Single Unique Non-Empty String Values` like:`['app_js' => 'sha384-...']` where the Key is the File Name without File Extension and the Value is the Hash Value of that File.", 'Global-setSRIInternal');
            $this->invalidBatches['global_sris']['internal']['config'] = $internalSRI;
            return;
        }
        $duplicateHashes = [];
        $valid = [];
        foreach ($internalSRI as $key => $sriHash) {
            if (isset($duplicateHashes[$sriHash])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Internal SRI Hash `{$sriHash}` is already used by Key:`{$duplicateHashes[$sriHash]}`. Each Internal SRI Key must contain `Single Unique Non-Empty String Values` like:`['app_js' => 'sha384-...']` where the Key is the File Name without File Extension and the Value is the Hash Value of that File.", 'Global-setSRIInternal');
                $this->invalidBatches['global_sris']['internal']['config'] = $internalSRI;
                return;
            }
            if (
                !is_string($key) || trim($key) === ''
                || !is_string($sriHash) || (trim($sriHash) === '')
                || (!str_contains($sriHash, 'sha'))
            ) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Internal SRI Hash `{$sriHash}` must be a `Non-Empty String` starting with `sha-`. Each Internal SRI Key must contain `Single Unique Non-Empty String Values` like:`['app_js' => 'sha384-...']` where the Key is the File Name without File Extension and the Value is the Hash Value of that File.", 'Global-setSRIInternal');
                $this->invalidBatches['global_sris']['internal']['config'] = $internalSRI;
                return;
            }
            $valid[$key] = $sriHash;
            $duplicateHashes[$sriHash] = $key;
        }
        $this->validBatches['config']['global_sris']['internal'] = $valid;
    }
    private function batchSetSRIExternalGlobal(array $externalSRI)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSRIExternal', "CONFIG()", $externalSRI);
        if (isset($this->invalidBatches['global_sris']['external']['config'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Global-setSRIExternal');
            return;
        }
        if (isset($this->validBatches['config']['global_sris']['external'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Global-setSRIExternal');
            return;
        }
        if (empty($externalSRI) || array_is_list($externalSRI)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a `Non-Empty Associative Array` where each Key is a `Non-Empty String Reference` (e.g. `cdn.tailwind`) and its Value is an Associative Array containing `Exactly Two Keys`: `url` (must start with `https://`) and `hash` (`Non-Empty String` containing `sha-`), for example:`['cdn.tailwind' => ['url' => 'https://cdn.tailwindcss.com', 'hash' => 'sha384-...']]`.", 'Global-setSRIExternal');
            $this->invalidBatches['global_sris']['external']['config'] = $externalSRI;
            return;
        }
        $duplicateHashes = [];
        $valid = [];
        foreach ($externalSRI as $key => $details) {
            if (
                !is_string($key) || trim($key) === ''
                || !is_array($details)
                || count($details) !== 2
                || array_is_list($details)
                || !isset($details['url'], $details['hash'])
                || !is_string($details['url']) || trim($details['url']) === '' || !str_starts_with(trim($details['url']), 'https://')
                || !is_string($details['hash']) || trim($details['hash']) === '' || !str_contains($details['hash'], 'sha')
            ) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a `Non-Empty Associative Array` where each Key is a `Non-Empty String Reference` (e.g. `cdn.tailwind`) and its Value is an Associative Array containing `Exactly Two Keys`: `url` (must start with `https://`) and `hash` (`Non-Empty String` containing `sha-`), for example:`['cdn.tailwind' => ['url' => 'https://cdn.tailwindcss.com', 'hash' => 'sha384-...']]`.", 'Global-setSRIExternal');
                $this->invalidBatches['global_sris']['external']['config'] = $externalSRI;
                return;
            }
            if (isset($duplicateHashes[$details['hash']])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " External SRI Hash `{$details['hash']}` is already used by `Key=>URL {$duplicateHashes[$details['hash']]}`. Each `External SRI Key` must contain `Single Non-Empty String-based Unique Hash Values` in their `hash` Key.", $this->errors['config']);
                $this->invalidBatches['global_sris']['external']['config'] = $externalSRI;
                return;
            }
            $valid[trim($key)] = [
                'url'  => trim($details['url']),
                'hash' => trim($details['hash'])
            ];
            $duplicateHashes[$details['hash']] = "{$key}=>{$details['url']}";
        }
        $this->validBatches['config']['global_sris']['external'] = $valid;
    }

    /* remove|pipeHeader - Global */
    private function batchRemoveHeaderGlobal(string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'removeHeader', "CONFIG()", $header_to_remove);
        if (isset($this->invalidBatches['headers']['config']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Header Name must be unique (case-insensitive).", 'Global-removeHeader');
            return;
        }
        if (isset($this->validBatches['config']['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Header Name must be unique (case-insensitive).", 'Global-removeHeader');
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Global-removeHeader');
            return;
        }
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/', $headerName)) {
            $this->setErr($this->getErr('InvalidHeaderName', $ctxVals), 'Global-removeHeader');
            $this->invalidBatches['headers']['config']['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Header cannot be removed if it was first configured to be added
        if (isset($this->validBatches['config']['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictRemovePipedHeader', $ctxVals), 'Global-removeHeader');
            $this->invalidBatches['headers']['config']['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Store header to be removed from Global level (->config())
        $this->validBatches['config']['headers']['remove'][$lowerHeader] = $headerName;
    }
    private function batchSetHeaderGlobal(string $header, string $value)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setHeader', "CONFIG()", $header, $value);
        $headerName  = trim($header);
        $headerValue = trim($value);
        $lowerHeader = strtolower($headerName);
        if (isset($this->invalidBatches['headers']['config']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `Header Name` must be unique (case-insensitive).", 'Global-setHeader');
            return;
        }
        // Forbid possible CRLF injection
        if (
            $headerName === '' || $headerValue === ''
            || str_contains($headerName, ":") || str_contains($headerValue, ":")
            || str_contains($headerName, "\r") || str_contains($headerName, "\n")
            || str_contains($headerValue, "\r") || str_contains($headerValue, "\n")
        ) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Global-setHeader');
            $this->invalidBatches['headers']['config']['add'][$lowerHeader] = true;
            return;
        }
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Global-setHeader');
            return;
        }
        if (isset($this->validBatches['config']['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `Header Name` must be unique (case-insensitive).", 'Global-setHeader');
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['config']['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Global-setHeader');
            $this->invalidBatches['headers']['config']['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Global level (->config())
        $this->validBatches['config']['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }

    /* pipeMiddleware|requestFunction|postResponseFunction - Global - NEXT UP TO FIX:
    // REMEMBER: when using "group:" to pipe you do not know whether pipe group has been
       added yet due to chaining so just then check that the middlewares|FNs wanna be used
       actually exist and then let compile() resolve if setGroup<Type> actually existed! */
    private function batchPipeMiddlewareGlobal(string $middleware)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'pipeMiddleware', "CONFIG()", $middleware);
        if (isset($this->invalidBatches['middlewares']['config'][$middleware])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-pipeMiddleware');
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Global-pipeMiddleware');
            $this->invalidBatches['middlewares']['config'][$middleware] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        if (str_starts_with($middleware, 'group:')) {
            $this->validBatches['config']['middlewares'][] = $middleware;
            return;
        }
        // Check if FN actually exists as a valid file + function
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
        $fileData = $this->cached['files_pipes_middlewares'][$middleware] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares\\{$middleware}", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-pipeMiddleware');
            $this->invalidBatches['middlewares']['config'][$middleware] = true;
            return;
        }
        // Pipe Global MW when all OK!
        $this->validBatches['config']['middlewares'][] = $middleware;

        // Add middleware (unless group: named) to what middlewares are used by what routes
        // where "GLOBAL" is for CONFIG(), and "<METHOD_NAME>" are CONFIG in each Method
        // but otherwise, it is added with each route.
        if (!str_starts_with($middleware, 'group:')) {
            if (!isset($this->cached['placeholderMiddlewareInvertIindex'][$middleware])) {
                $this->cached['placeholderMiddlewareInvertIindex'][$middleware][] = 'GLOBAL';
            } else {
                if (!in_array('GLOBAL', $this->cached['placeholderMiddlewareInvertIindex'][$middleware])) {
                    $this->cached['placeholderMiddlewareInvertIindex'][$middleware][] = 'GLOBAL';
                }
            }
        }
    }
    private function batchPipeRequestFunctionGlobal(string $fileFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'pipeRequestFunction', "CONFIG()", $fileFunctionName);
        if (isset($this->invalidBatches['config']['request'][$fileFunctionName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-pipeRequestFunction');
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($fileFunctionName)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Global-pipeRequestFunction');
            $this->invalidBatches['config']['request'][$fileFunctionName] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        if (str_starts_with($fileFunctionName, 'group:')) {
            $this->validBatches['config']['request'][] = $fileFunctionName;
            return;
        }
        // Check if FN actually exists as a valid file + function
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_request', $fileFunctionName);
        $fileData = $this->cached['files_pipes_request'][$fileFunctionName] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $fileFunctionName, $ctxVals, "funkphp\\pipes\\request\\{$fileFunctionName}", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-pipeRequestFunction');
            $this->invalidBatches['config']['request'][$fileFunctionName] = true;
            return;
        }
        // Pipe Global MW when all OK!
        $this->validBatches['config']['request'][] = $fileFunctionName;
    }
    private function batchPipePostResponseFunctionGlobal(string $fileFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'pipePostResponseFunction', "CONFIG()", $fileFunctionName);
        if (isset($this->invalidBatches['config']['post_response'][$fileFunctionName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-pipePostResponseFunction');
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($fileFunctionName)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Global-pipePostResponseFunction');
            $this->invalidBatches['config']['post_response'][$fileFunctionName] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        if (str_starts_with($fileFunctionName, 'group:')) {
            $this->validBatches['config']['post_response'][] = $fileFunctionName;
            return;
        }
        // Check if FN actually exists as a valid file + function
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_post_response', $fileFunctionName);
        $fileData = $this->cached['files_pipes_post_response'][$fileFunctionName] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $fileFunctionName, $ctxVals, "funkphp\\pipes\\post_response\\{$fileFunctionName}", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-pipePostResponseFunction');
            $this->invalidBatches['config']['post_response'][$fileFunctionName] = true;
            return;
        }
        // Pipe Global MW when all OK!
        $this->validBatches['config']['post_response'][] = $fileFunctionName;
    }

    /* !!! METHOD BATCHES/ROUTES()->GET|POST|PATCH|PUT|DELETE() FUNCTIONS !!! */
    //METHOD:Set & New Batches for SPECIFIC_METHOD! (so ->routes()-><Method>->set|pipe<What>)
    private function batchSetRateLimitingMethod(string $method, int $maxRequestsPerWindowSize = 60, int $windowSizeInSeconds = 60, $by = 'ip', $driver = 'redis')
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setRateLimit', "CONFIG()->{$method}()", $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver);
    }

    //METHOD: No Match for this https method, if none is set, it falls back to the global versions.
    private function batchSetNoRouteMatchPageMethod(string $method, string $PageFileName, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchPage', "CONFIG()->{$method}()", $PageFileName);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Method-setNoRouteMatchPage', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Method-setNoRouteMatchPage', $method);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Method-setNoRouteMatchPage', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        if (!is_string($PageFileName) || (trim($PageFileName) === '') || !preg_match('/[a-zA-Z0-9-_]+/i', $PageFileName)) {
            $this->setErr($this->getErr('InvalidPageName', $ctx), 'Method-setNoRouteMatchPage', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        // Now check if Page File exists either in "/src/funkphp/pages/$PageFileName.php"
        // or in "/src/funkphp/pages/compiled/$PageFileName.php". First hydrate if not yet.
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages', $PageFileName);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pages_compiled', $PageFileName);
        // Prioritize Compiled Pages, then possibly non-compiled pages (they could still contain no template engine)
        $pageFound = false;
        if (
            isset($this->cached['files_pages_compiled'][$PageFileName]['file_exists'])
            && $this->cached['files_pages_compiled'][$PageFileName]['file_exists'] === true
        ) {
            $pageFound = true;
        } else if (
            isset($this->cached['files_pages'][$PageFileName]['file_exists'])
            && $this->cached['files_pages'][$PageFileName]['file_exists'] === true
        ) {
            $pageFound = true;
        }
        // No Page at all found?
        if (!$pageFound) {
            $this->setErr($this->getErr('NoPageAtAllFound', $ctxVals) . " using Filename `{$PageFileName}.php`.", 'Method-setNoRouteMatchPage', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = ['page' => $PageFileName, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchJsonMethod(string $method, array|object $data, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchJSON', "CONFIG()->{$method}()", $data, $statusCode);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Method-setNoRouteMatchJson', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Method-setNoRouteMatchJson', $method);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Method-setNoRouteMatchJson', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        // Really check it is an empty object
        $isEmptyObject = is_object($data) && (
            ($data instanceof \Countable && count($data) === 0) ||
            (get_object_vars($data) === [] && !($data instanceof \JsonSerializable))
        );
        if (is_array($data) && empty($data) || $isEmptyObject) {
            $this->setErr($this->getErr('JsonEncodingFailedNoData', $ctx), 'Method-setNoRouteMatchJson', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $JSON = null;
        try {
            $JSON = json_encode($data, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            $this->setErr($this->getErr('JsonEncodingFailed', $ctx), 'Method-setNoRouteMatchJson', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = ['JSON' => $JSON, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchTextMethod(string $method, string $message, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchText', "CONFIG()->{$method}()", $message, $statusCode);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Method-setNoRouteMatchText', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Method-setNoRouteMatchText', $method);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Method-setNoRouteMatchText', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        if (!is_string($message) || (trim($message) === '')) {
            $this->setErr($this->getErr('InvalidNoRouteMatchTextValue', $ctx), 'Method-setNoRouteMatchText', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'] = ['text' => $message, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchCallbackMethod(string $method, string $userDefinedFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchCallback', "CONFIG()->{$method}()", $userDefinedFunctionName);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Method-setNoRouteMatchCallback', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Method-setNoRouteMatchCallback', $method);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunctionName)) {
            $this->setErr($this->getErr('InvalidFunctionName', $ctx), 'Method-setNoRouteMatchCallback', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // Hydrate user defined functions if not already
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions', $userDefinedFunctionName);
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunctionName, $ctxVals);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Method-setNoRouteMatchCallback', $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // After initial check, check that it is not already used by Global Handlers (->CONFIG()->setDefault<Handler>)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName])) {
            $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' See: `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName] . '`', 'Method-setNoRouteMatchCallback', $method);
            return;
        }
        // Finally add it
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
    }

    //METHOD: setParamRule Method
    private function batchSetParamRuleMethod(string $method, string $param, string $regex, $defaultParamValueOnRegexMismatch = null)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setParamRule', "ROUTES()->{$method}()", $param, $regex, $defaultParamValueOnRegexMismatch);
        if (isset($this->invalidBatches['paramRules']['methods'][$method][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Param Identifier must be unique (case-insensitive).", 'Method-setParamRule', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Param Identifier must be unique (case-insensitive).", 'Method-setParamRule', $method);
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Method-setParamRule', $method);
            $this->invalidBatches['paramRules']['methods'][$method][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Validate valid $regex pattern
        $regexValid = true;
        try {
            if (@preg_match($regex, '') === false) {
                $regexValid = false;
            }
        } catch (\Throwable $e) {
            $regexValid = false;
        }
        if (!$regexValid || preg_match('#\/\/[gimsuy]*#', $regex)) {
            $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Method-setParamRule', $method);
            $this->invalidBatches['paramRules']['methods'][$method][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Check for duplicate valid rule at method level
        if (isset($this->validBatches['methods'][$method]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateParamMethod', $ctxVals), 'Method-setParamRule', $method);
            $this->invalidBatches['paramRules']['methods'][$method][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Finally store valid method param rule
        $this->validBatches['methods'][$method]['paramRules'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch
        ];
        $this->cached['placeholderUNSUEDParams'][$method][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch
        ];
    }

    //METHOD: setCSP
    private function batchSetCSPMethod(string $method, string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setCSP', "ROUTES()->{$method}()", $directive, ...$sources);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['methods'][$method][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `\$directive` can only be used/set once.", 'Method-setCSP', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `\$directive` can only be used/set once.", 'Method-setCSP', $method);
            return;
        }
        $allowedDirectives = $this->ALLOWED['csp-directives'];
        if ($directive === '' || !in_array($directive, $allowedDirectives, true)) {
            $this->setErr($this->getErr('InvalidCSPDirective', $ctxVals) . $this->joinArray($allowedDirectives), 'Method-setCSP', $method);
            $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
            return;
        }
        if (empty($sources)) {
            $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Method-setCSP', $method);
            $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
            return;
        }
        $formattedSources = $this->formatCSPSources($sources);
        if (in_array("'none'", $formattedSources, true) && count($formattedSources) > 1) {
            $this->setErr($this->getErr('ConflictNoneSourceInCSP', $ctxVals), 'Method-setCSP', $method);
            $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
            return;
        }
        $nonces = [];
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Method-setCSP', $method);
                $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                return;
            }
            // Is it a nonce that is supposed to be in the 'nonces' array instead?
            $trimmed = trim($source);
            if (str_starts_with(strtolower($trimmed), 'nonce:')) {
                if (in_array(strtolower($trimmed), $nonces)) {
                    $this->setErr($this->getErr('DuplicateNonceName', $ctxVals) . "`{$trimmed}`. You can only use each Unique Nonce Name once per CSP Directive.", 'Method-setCSP', $method);
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
                if (!preg_match('/^nonce:[a-zA-Z0-9-_\.]+$/', strtolower($trimmed))) {
                    $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Method-setCSP');
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
                if (isset($this->validBatches['methods'][$method]['nonces'][strtolower($trimmed)])) {
                    $this->setErr($this->getErr('DuplicateNonceDirectiveUse', $ctxVals) . "`Nonce Name {$trimmed} ` is already being used by CSP Directive: `{$this->validBatches['methods'][$method]['nonces'][strtolower($trimmed)]}`.", 'Method-setCSP', $method);
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
                $nonces[] = strtolower($trimmed);
                $this->validBatches['methods'][$method]['nonces'][strtolower($trimmed)] = $directive;
                continue;
            }
            if (
                $trimmed === ''
                || str_contains($trimmed, ';')
                || str_contains($trimmed, "\r")
                || str_contains($trimmed, "\n")
                || preg_match('/\s/', $trimmed)
            ) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Method-setCSP', $method);
                $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                return;
            }
            if (str_contains($trimmed, '*') && $trimmed !== '*') {
                if (!preg_match('/^(https?:\/\/)?\*\.[a-zA-Z0-9\.-]+(:\d+)?$/', $trimmed)) {
                    $this->setErr($this->getErr('InvalidCSPWildcardUse', $ctxVals), 'Method-setCSP', $method);
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
            }
        }
        $this->validBatches['methods'][$method]['csp'][$directive] = array_filter($formattedSources, function ($src) {
            return str_starts_with('nonce:', $src);
        });
    }

    /*METHOD: removeHeader & pipeHeader */
    private function batchSetHeaderMethod(string $method, string $header, $value)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setHeader', "ROUTES()->{$method}()", $header, $value);
        $headerName  = $header;
        $headerValue = $value;
        $lowerHeader = strtolower($headerName);
        if (isset($this->invalidBatches['headers']['methods'][$method]['add'][$header])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `Header Name` must be unique (case-insensitive).", 'Method-setHeader', $method);
            return;
        }
        // Forbid possible CRLF injection
        if (
            $headerName === '' || $headerValue === ''
            || str_contains($headerName, ":") || str_contains($headerValue, ":")
            || str_contains($headerName, "\r") || str_contains($headerName, "\n")
            || str_contains($headerValue, "\r") || str_contains($headerValue, "\n")
        ) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Method-setHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Method-setHeader', $method);
            return;
        }
        // Check if it already exists
        if (isset($this->validBatches['methods'][$method]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `Header Name` must be unique (case-insensitive).", 'Method-setHeader', $method);
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['methods'][$method]['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Method-setHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Method level (->config()->ROUTES()-><METHOD>)
        $this->validBatches['methods'][$method]['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }
    private function batchRemoveHeaderMethod(string $method, string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'removeHeader', "ROUTES()->{$method}()", $header_to_remove);
        if (isset($this->invalidBatches['headers']['methods'][$method]['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Header Name must be unique (case-insensitive).", 'Method-removeHeader', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Header Name must be unique (case-insensitive).", 'Method-removeHeader', $method);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Method-removeHeader', $method);
            return;
        }
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/', $headerName)) {
            $this->setErr($this->getErr('InvalidHeaderName', $ctxVals), 'Method-removeHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Header cannot be removed if it was first configured to be added
        if (isset($this->validBatches['methods'][$method]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictRemovePipedHeader', $ctxVals), 'Method-removeHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['remove'][$header_to_remove] = $header_to_remove;
            return;
        }
        // Store header to be removed from Method level (->config()->ROUTES()-><METHOD>)
        $this->validBatches['methods'][$method]['headers']['remove'][$lowerHeader] = $headerName;
    }
    private function batchPipeMiddlewareMethod(string $method, string $middleware)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'pipeMiddleware', "ROUTES()->$method()", $middleware);
        if (isset($this->invalidBatches['middlewares']['methods'][$method][$middleware])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-pipeMiddleware', $method);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Method-pipeMiddleware', $method);
            $this->invalidBatches['middlewares']['methods'][$method][$middleware] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        if (str_starts_with($middleware, 'group:')) {
            $this->validBatches['methods'][$method]['middlewares'][] = $middleware;
            return;
        }
        // Check if FN actually exists as a valid file + function
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
        $fileData = $this->cached['files_pipes_middlewares'][$middleware] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares\\{$middleware}", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Method-pipeMiddleware', $method);
            $this->invalidBatches['middlewares']['methods'][$method][$middleware] = true;
            return;
        }
        // Pipe Method MW when all OK!
        $this->validBatches['methods'][$method]['middlewares'][] = $middleware;
        // Add middleware (unless group: named) to what middlewares are used by what routes
        // where "GLOBAL" is for CONFIG(), and "<METHOD_NAME>" are CONFIG in each Method
        // but otherwise, it is added with each route.
        if (!str_starts_with($middleware, 'group:')) {
            if (!isset($this->cached['placeholderMiddlewareInvertIindex'][$middleware])) {
                $this->cached['placeholderMiddlewareInvertIindex'][$middleware][] = $method;
            } else {
                if (!in_array($method, $this->cached['placeholderMiddlewareInvertIindex'][$middleware])) {
                    $this->cached['placeholderMiddlewareInvertIindex'][$middleware][] = $method;
                }
            }
        }
    }

    /* !!! ROUTE/ROUTES()-><METHOD>()->route()-> BATCHES FUNCTIONS !!! */
    //ROUTE:Batching New Route `->route("/route", $optionalParamRules as an array)`
    private function batchNewRoute(string $method, string $route)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'ROUTE', "ROUTES()->{$method}()->ROUTE('{$route}')", $route);
        // Check if the associated $method$route is in the InvalidBatches first
        // OR if it is already as an invalid alias OR a valid alias already exists
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-route', $method, $route);
            return;
        }
        // Does $route already exist as a valid one? (meaning it was formatted correctly but duplicate)
        if (isset($this->validBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Route-route', $method, $route);
            return;
        }
        // Check initial string formatting: all non-empty string that is all lowercased,
        // starting with / or just is /, does not end with /, have no consecutive -,_
        // or them after one another like -_ or _- and that all dynamic
        // params begin with "/:[a-z0-9]"
        if (
            !is_string($route) || trim($route) === ''
            || (strtolower($route) !== $route)
            || !str_starts_with($route, '/')
            || (str_ends_with($route, '/') && $route !== '/')
            || !preg_match('/^(?!.*[-_]{2,})(?:\/|(?:\/[:]?[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?)+)$/', $route)
        ) {
            if (isset($this->invalidBatches['routes'][$method][$route])) {
                $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Route-route', $method, $route);
            } else {
                $this->setErr($this->getErr('InvalidRouteFormat', $ctxVals), 'Route-route', $method, $route);
                $this->invalidBatches['routes'][$method][$route] = true;
            }
            return;
        }
        // Check for duplicates if dynamic params are used (indicated by existence of ":")
        // If it is still OK then we check against dynamic structural conflicts like
        // "/:users/" and "/:id" where both use dynamic params on same URI segment level
        $routeHasParams = null;
        if (str_contains($route, ":")) {
            preg_match_all('/:([a-z0-9_-]+)/i', $route, $paramMatches);
            if (count($paramMatches[1]) !== count(array_unique($paramMatches[1]))) {
                if (isset($this->invalidBatches['routes'][$method][$route])) {
                    $this->setErr($this->getErr('InvalidRouteFormatDuplicateParams', $ctxVals), 'Route-route', $method, $route);
                } else {
                    $this->setErr($this->getErr('InvalidRouteFormatDuplicateParams', $ctxVals), 'Route-route', $method, $route);
                    $this->invalidBatches['routes'][$method][$route] = true;
                }
                return;
            }
            // Check if this parent path context ALREADY locked a parameter name meaning
            // if GET()->route('/users/:id') came first then <METHOD>()->route('/users/:id2)
            // cannot follow NOR any depth that starts with "/users/:PARAM" where :PARAM is
            // not "id" since that :PARAM laid out the convention to follow from that parent static.
            $segments = explode('/', ltrim($route, '/'));
            $currentParentContext = '';
            foreach ($segments as $segment) {
                if (str_starts_with($segment, ':')) {
                    $paramName = substr($segment, 1);
                    $contextKey = $currentParentContext === '' ? '/' : $currentParentContext;
                    if (isset($this->cached['placeholderParamContexts'][$contextKey])) {
                        $lockedParamName = $this->cached['placeholderParamContexts'][$contextKey]['param'];
                        if ($lockedParamName !== $paramName) {
                            $this->setErr($this->getErr('ConflictRouteParam', $ctxVals) . " Parameter `:{$paramName}` conflicts with Locked Parameter `:{$lockedParamName}` first defined in `{$this->cached['placeholderParamContexts'][$contextKey]['first']}`. Either Standardize `{$paramName}` across both routes OR if you want the OTHER Route to be considered the `First defined with {$paramName}`, you will need to swap their File Inclusions in `/src/funkphp/core/app.php` (`\$routeFiles`). Default order: `GET => POST => PUT => PATCH => DELETE`. FunkPHP treats `URI Segments` as `Dynamic Folder Levels`, so a given folder depth can only have one dynamic parameter name (e.g. `[id]` but not both `[id]` and `[name]`). Use `ROUTE()->setParamRulePolymorphic()` to match Multiple Data Types (e.g. `numeric IDs` AND `string Usernames`).", 'Route-route', $method, $route);
                            $this->invalidBatches['routes'][$method][$route] = true;
                            return;
                        }
                    } else {
                        // Lock this parameter name globally for this parent path context
                        $this->cached['placeholderParamContexts'][$contextKey] = [
                            'param' => $paramName,
                            'first' => "/src/funkphp/app/{$method}.php => ROUTES()->{$method}()->ROUTE('{$route}')"
                        ];
                    }
                    $currentParentContext .= '/:PARAM';
                } else {
                    $currentParentContext .= '/' . $segment;
                }
            }
            $routeHasParams = $paramMatches[1]; // Store any params used

        }
        // Prepare all subroutes for fast lookup of what middlewares can be excluded and not
        $subRoutes = [];
        $splittedRoute = array_filter(explode('/', $route));
        $currentSubRoute = "{$method}/";
        $subRoutes[] = $currentSubRoute;
        foreach ($splittedRoute as $splitRoute) {
            $currentSubRoute .= "{$splitRoute}/";
            $subRoutes[] = $currentSubRoute;
        }
        $subRoutes[count($subRoutes) - 1] = substr($subRoutes[count($subRoutes) - 1], 0, strlen($subRoutes[count($subRoutes) - 1]) - 1);
        // Add Valid String Formatted METHOD/Route now; in compilation it will be checked for
        // conflicting URI segments with other routes as we do not know which order they are added!
        $this->validBatches['routes'][$method][$route] = [
            'hasParams' => $routeHasParams,
            'paramRules' => null,
            'response' => null,
            'pipes' => [],
            'middlewares' => [],
            'excludeMiddleware' => null,
            'routeSplits' => $splittedRoute,
            'subRoutes' => $subRoutes,
            'headers' => ['add' => null, 'remove' => null],
            'csp' => null,
            'nonces' => null,
            'excludeHeaders' => null,
            'all' => [
                'all_headers' => [
                    'all_add' => [],
                    'all_remove' => []
                ],
                'all_middlewares' => [],
                'all_middlewares_and_pipes' => [],
                'all_csp' => [],
                'all_nonces' => []
            ]
        ];
    }

    //ROUTE: Set & New Batches for ROUTES! (so ->routes()-><Method>()->route()->set|pipe<What>)
    private function batchSetAliasRoute(string $method, string $route, string $alias)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setAlias', "ROUTES()->{$method}()->ROUTE('{$route}')", $alias);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setAlias', $method, $route);
            return;
        }
        // Check if it exists already in invalid or valid batch
        if (isset($this->invalidBatches['aliases'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Route-setAlias', $method, $route);
            return;
        }
        // Alias formatting with typical alphanumerals plus dot-notation support
        if ($alias === '' || !preg_match('/^[a-zA-Z0-9_.-]+$/', $alias)) {
            $this->setErr($this->getErr('InvalidRouteAliasName', $ctxVals), 'Route-setAlias', $method, $route);
            $this->invalidBatches['aliases'][$method][$route] = $alias;
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['alias'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Route-setAlias', $method, $route);
            return;
        }
        // Global Uniqueness Check: Aliases CANNOT be duplicated across ANY method
        if (isset($this->cached['routeAliases'][$alias])) {
            $firstDefined = $this->cached['routeAliases'][$alias];
            $this->setErr($this->getErr('DuplicateRouteAliasName', $ctxVals) . "`{$firstDefined}`", 'Route-setAlias', $method, $route);
            $this->invalidBatches['aliases'][$method][$route] = $alias;
            return;
        }
        // Register valid alias in reverse lookup map
        $this->cached['routeAliases'][$alias] = "->ROUTES()->{$method}()->ROUTE('{$route}')";
        $this->validBatches['routes'][$method][$route]['alias'] = $alias;
    }

    //ROUTE: setParamRulePolymorphic
    private function batchSetParamRulePolymorphicRoute(string $method, string $route, string $paramIdentifier, string ...$keyAndRegexPairs)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setParamRulePolymorphic', "ROUTES()->{$method}()->ROUTE('{$route}')", $paramIdentifier, ...$keyAndRegexPairs);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setParamRule', $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (
            isset($this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier]) ||
            isset($this->invalidBatches['paramRules']['routes'][$method][$route][$paramIdentifier])
        ) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Param Identifier must be unique (case-insensitive) and this applies both to regular ParamRules as well as Flexible ParamRules.", 'Route-setParamRulePolymorphic', $method, $route);
            return;
        }
        if (
            isset($this->validBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier])
            || isset($this->validBatches['paramRules']['routes'][$method][$route][$paramIdentifier])
        ) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Param Identifier must be unique (case-insensitive) and this applies both to regular ParamRules as well as Flexible ParamRules.", 'Route-setParamRulePolymorphic', $method, $route);
            return;
        }
        // Does the valid route even have params?
        if (!isset($this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNoParams', $ctxVals), 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = "$paramIdentifier";
        }
        // Validate valid $param identifier formatting
        if (!is_string($paramIdentifier) || !preg_match('/^[a-z0-9_-]+$/', $paramIdentifier)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = "$paramIdentifier";
            return;
        }
        // $param identifier formatting is valid, but does it exist in the array of hasParams?
        if (!in_array($paramIdentifier, $this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNotChosenParam', $ctxVals) . " The available Params in the Route: " . $this->joinArray($this->validBatches['routes'][$method][$route]['hasParams']) . '.', 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = "$paramIdentifier";
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['paramRules'][$paramIdentifier])) {
            $this->setErr($this->getErr('DuplicateParamRoute', $ctxVals), 'Route-setParamRule', $method, $route);
            return;
        }
        // Check count on $firstKeyNameSecondKeyRegexThirdKeyDefaultValueAndSoOn and make sure it is an equal count since it needs first each element
        // to be the name identifier of the regex rule that then follows. For example: 'number','/[\d]+/' so that can be stored
        // in the $c['req']['matched_params_flexible']['name'] and so on.
        $pairCount = count($keyAndRegexPairs);
        if ($pairCount === 0 || ($pairCount % 2) !== 0) {
            $this->setErr($this->getErr('InvalidParamFlexibleStringArray', $ctxVals) . " Flexible rules require matching [RuleName, RegexPattern] pairs.", 'Route-setParamRulePolymorphic', $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = $paramIdentifier;
            return;
        }
        $compiledPairs = [];
        for ($i = 0; $i < $pairCount; $i += 2) {
            $ruleName = strtolower(trim($keyAndRegexPairs[$i]));
            $regex    = trim($keyAndRegexPairs[$i + 1]);
            if ($ruleName === '' || !preg_match('/^[a-z0-9_-]+$/', $ruleName)) {
                $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Route-setParamRulePolymorphic', $method, $route);
                $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = $paramIdentifier;
                return;
            }
            if (isset($compiledPairs[$ruleName])) {
                $this->setErr($this->getErr('DuplicateFlexibleRegexPairName', $ctxVals) . " `{$ruleName}` is already used for `{$paramIdentifier}`.", 'Route-setParamRulePolymorphic', $method, $route);
                $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = $paramIdentifier;
                return;
            }
            $regexValid = true;
            try {
                if (@preg_match($regex, '') === false) {
                    $regexValid = false;
                }
            } catch (\Throwable $e) {
                $regexValid = false;
            }
            if (!$regexValid || preg_match('#\/\/[gimsuy]*#', $regex)) {
                $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Route-setParamRulePolymorphic', $method, $route);
                $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = $paramIdentifier;
                return;
            }
            $compiledPairs[$ruleName] = $regex;
        }
        $this->validBatches['routes'][$method][$route]['paramRules'][$paramIdentifier] = ['pairs' => $compiledPairs];
    }

    //ROUTE: SetParamRule
    private function batchSetParamRuleRoute(string $method, string $route, string $param, string $regex, $defaultParamValueOnRegexMismatch = null)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setParamRule', "ROUTES()->{$method}()->ROUTE('{$route}')", $param, $regex, $defaultParamValueOnRegexMismatch);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setParamRule', $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['paramRules']['routes'][$method][$route][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Param Identifier must be unique (case-insensitive).", 'Route-setParamRule', $method, $route);
            return;
        }
        if (isset($this->validBatches['paramRules']['routes'][$method][$route][$param])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Param Identifier must be unique (case-insensitive).", 'Route-setParamRule', $method, $route);
            return;
        }
        // Does the valid route even have params?
        if (!isset($this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNoParams', $ctxVals), 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // $param identifier formatting is valid, but does it exist in the array of hasParams?
        if (!in_array($param, $this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNotChosenParam', $ctxVals) . " The available Params in the Route: " . $this->joinArray($this->validBatches['routes'][$method][$route]['hasParams']) . '.', 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Validate valid $regex pattern
        $regexValid = true;
        try {
            if (@preg_match($regex, '') === false) {
                $regexValid = false;
            }
        } catch (\Throwable $e) {
            $regexValid = false;
        }
        if (!$regexValid || preg_match('#\/\/[gimsuy]*#', $regex)) {
            $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Route-setParamRule', $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = "{$param},{$regex},{$defaultParamValueOnRegexMismatch}";
            return;
        }
        // Check for duplicate valid rule at route level
        if (isset($this->validBatches['routes'][$method][$route]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateParamRoute', $ctxVals), 'Route-setParamRule', $method, $route);
            return;
        }
        // Finally add it as valid for that route in
        // $validBatches->paramRules->routes->method->route-><$param>
        // Method-leveled paramRules uses 'paramRules'->'methods',
        // while config() uses 'paramRules'->'global'
        $this->validBatches['routes'][$method][$route]['paramRules'][$param] = ['pattern' => $regex, 'default' => $defaultParamValueOnRegexMismatch];
    }
    /*ROUTE: RateLimiting & setCache */
    private function batchSetRateLimitingRoute(string $method, string $route, int $maxRequestsPerWindowSize = 60, int $windowSizeInSeconds = 60, $by = 'ip', $driver = 'redis')
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setRateLimit', "CONFIG()->{$method}()->ROUTE('{$route}')", $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver);
    }
    private function batchSetCacheRoute(string $method, string $route, int $ttl = 3600, string $driver = 'redis', mixed $varyBy = null, bool $private = false) {}

    /*ROUTE: setCSPRoute */
    private function batchSetCSPRoute(string $method, string $route, string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setCSP', "ROUTES()->{$method}()->ROUTE('{$route}')", $directive, ...$sources);
        // Route must be valid first
        if (isset($this->invalidBatches['csp']['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setCSP', $method, $route);
            return;
        }
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['routes'][$method][$route][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `\$directive` can only be used/set once.", 'Route-setCSP', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `\$directive` can only be used/set once.", 'Route-setCSP', $method, $route);
            return;
        }
        $allowedDirectives = $this->ALLOWED['csp-directives'];
        if ($directive === '' || !in_array($directive, $allowedDirectives, true)) {
            $this->setErr($this->getErr('InvalidCSPDirective', $ctxVals) . $this->joinArray($allowedDirectives), 'Route-setCSP', $method, $route);
            return;
        }
        if (empty($sources)) {
            $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Route-setCSP', $method, $route);
            $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
            return;
        }
        $formattedSources = $this->formatCSPSources($sources);
        if (in_array("'none'", $formattedSources, true) && count($formattedSources) > 1) {
            $this->setErr($this->getErr('ConflictNoneSourceInCSP', $ctxVals), 'Route-setCSP', $method, $route);
            $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
            return;
        }
        $nonces = []; // cannot use same nonce:<name> twice for same directory
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Route-setCSP', $method, $route);
                $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                return;
            }
            $trimmed = trim($source);
            // Is it a nonce that is supposed to be in the 'nonces' array instead?
            if (str_starts_with(strtolower($trimmed), 'nonce:')) {
                if (in_array(strtolower($trimmed), $nonces)) {
                    $this->setErr($this->getErr('DuplicateNonceName', $ctxVals) . "`{$trimmed}`. You can only use each Unique Nonce Name once per CSP Directive.", 'Route-setCSP', $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
                if (!preg_match('/^nonce:[a-zA-Z0-9-_\.]+$/', strtolower($trimmed))) {
                    $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Route-setCSP', $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
                if (isset($this->validBatches['routes'][$method][$route]['nonces'][strtolower($trimmed)])) {
                    $this->setErr($this->getErr('DuplicateNonceDirectiveUse', $ctxVals) . "`Nonce Name {$trimmed} ` is already being used by CSP Directive: `{$this->validBatches['routes'][$method][$route]['nonces'][strtolower($trimmed)]}`.", 'Route-setCSP', $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
                $nonces[] = strtolower($trimmed);
                $this->validBatches['routes'][$method][$route]['nonces'][strtolower($trimmed)] = $directive;
                continue;
            }
            // Not nonce: special-case so check for other stuff
            if (
                $trimmed === ''
                || str_contains($trimmed, ';')
                || str_contains($trimmed, "\r")
                || str_contains($trimmed, "\n")
                || preg_match('/\s/', $trimmed)
            ) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Route-setCSP', $method, $route);
                $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                return;
            }
            if (str_contains($trimmed, '*') && $trimmed !== '*') {
                if (!preg_match('/^(https?:\/\/)?\*\.[a-zA-Z0-9\.-]+(:\d+)?$/', $trimmed)) {
                    $this->setErr($this->getErr('InvalidCSPWildcardUse', $ctxVals), 'Route-setCSP', $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
            }
        }
        $this->validBatches['routes'][$method][$route]['csp'][$directive] = array_filter($formattedSources, function ($src) {
            return str_starts_with('nonce:', $src);
        });
    }

    /*ROUTE: pipeMiddleware, pipeFunction, pipeResponse, pipeSQL, pipeQuery & pipeValidation */
    private function batchPipeMiddlewareRoute(string $method, string $route, string $middleware)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeMiddleware', "ROUTES()->{$method}()->ROUTE('{$route}')", $middleware);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeMiddleware', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['middlewares']['routes'][$method][$route][$middleware])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-pipeMiddleware', $method, $route);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Route-pipeMiddleware', $method, $route);
            $this->invalidBatches['middlewares']['routes'][$method][$route][$middleware] = true;
            return;
        }
        if (in_array($middleware, ($this->validBatches['routes'][$method][$route]['excludeMiddleware'] ?? []), true)) {
            $this->setErr($this->getErr('ConflictingPipeMiddlewareWithAlreadyExcludeMW', $ctxVals) . " Conflict: `->setExcludeMiddleware('{$middleware}')`.", 'Route-setExcludeMiddleware', $method, $route);
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        if (str_starts_with($middleware, 'group:')) {
            $this->validBatches['routes'][$method][$route]['middlewares'][] = $middleware;
            return;
        }
        // Check if FN actually exists as a valid file + function
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
        $fileData = $this->cached['files_pipes_middlewares'][$middleware] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares\\{$middleware}", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Route-pipeMiddleware', $method, $route);
            $this->invalidBatches['middlewares']['routes'][$method][$route][$middleware] = true;
            return;
        }
        // Pipe Route MW when all OK!
        $this->validBatches['routes'][$method][$route]['middlewares'][] = $middleware;
        // Add middleware (unless group: named) to what middlewares are used by what routes
        // where "GLOBAL" is for CONFIG(), and "<METHOD_NAME>" are CONFIG in each Method
        // but otherwise, it is added with each route.
        if (!str_starts_with($middleware, 'group:')) {
            if (!isset($this->cached['placeholderMiddlewareInvertIindex'][$middleware])) {
                $this->cached['placeholderMiddlewareInvertIindex'][$middleware][] = "$method$route";
            } else {
                if (!in_array("$method$route", $this->cached['placeholderMiddlewareInvertIindex'][$middleware])) {
                    $this->cached['placeholderMiddlewareInvertIindex'][$middleware][] = "$method$route";
                }
            }
        }
    }
    private function batchPipeFunctionRoute(string $method, string $route, string $fileFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeFunction', "ROUTES()->{$method}()->ROUTE('{$route}')", $fileFunctionName);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeFunction', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Route-pipeFunction', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Route-pipeFunction', $method, $route);
            $this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($fileFunctionName)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Route-pipeFunction', $method, $route);
            $this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        if (str_starts_with($fileFunctionName, 'group:')) {
            $this->validBatches['routes'][$method][$route]['pipes'][] = $fileFunctionName;
            return;
        }
        // Otherwise we know it is a valid string formatted "filename.functionname"
        [$file, $fn] = explode('.', $fileFunctionName);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_routes', $file);
        $fileData = $this->cached['files_routes'][$file] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $fn, $ctxVals, "funkphp\\pipes\\routes\\{$file}", false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Route-pipeFunction', $method, $route);
            $this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName] = true;
            return;
        }
        $this->validBatches['routes'][$method][$route]['pipes'][] = $fileFunctionName;
    }
    private function batchPipeResponseRoute(string $method, string $route, string $typeOfResponse)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeResponse', "ROUTES()->{$method}()->ROUTE('{$route}')", $typeOfResponse);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeResponse', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Route-pipeResponse', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctx), 'Route-pipeResponse', $method, $route);
            return;
        }
        if (str_starts_with(strtolower(trim($typeOfResponse)), 'group:')) {
            $this->setErr($this->getErr('GroupPipeResponseNotSupported', $ctxVals), 'Route-pipeResponse', $method, $route);
            $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
            return;
        }
        // The valid Response Types
        if (!preg_match('/^(json:|page:|callback:|text:)/i', $typeOfResponse)) {
            $this->setErr($this->getErr('InvalidResponseType', $ctxVals), 'Route-pipeResponse', $method, $route);
            $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
            return;
        }
        // Handle each response type, error out if not possible, and if all OK, just set it to validBatches
        [$type, $ctx] = explode(':', $typeOfResponse, 2);
        $typeErr = '';
        $type = strtolower(trim($type));
        if ($type === 'json') {
            $typeErr = 'Choose a Array Path for where to return the `Stored Valid JSON Data` from. For exampel: `d.subKey.optionalSubkey` will return `Stored JSON Data` from `\$c["d"]["subKey"]["optionalSubkey"]`. Make sure that `JSON Data` is stored in that variable `before pipeResponse() executes`. Invalid JSON Data when it is being returned will make it instead return `500 HTTP(S) Status Code` and `[\'code\':500, \'error\':\'Internal Server Error\']`';
        } else if ($type === 'page') {
            $typeErr = 'Choose a `Page Filename` (e.g. `login`). It will then first check for `/src/funkphp/pages/compiled/login.php` and then for `/src/funkphp/pages/login.php` attempting to Compile it On-the-Fly and then return it. `In-built Page Not Found` is returned instead if both Page Files are not found during runtime (or Page On-the-Fly-Compilation fails).';
        } else if ($type === 'callback') {
            $typeErr = 'Choose a `User-defined Function in /src/funkphp/config/functions.php` that is also NOT already used as a `Global Handler`. For example, if you have set `->setDefaultKernelHandler(\'test\')`, then you cannot use the User-Defined Function `function test(&$c){}` in `/src/funkphp/config/functions.php`.';
        } else if ($type === 'text') {
            $typeErr = 'Write any length (except 0) of Plain-Text after the Single Colon (`:`) that is Valid UTF-8. If you need to return `Non-UTF-8 Plain-Text use a Callback instead` to achieve that kind of Response Type as `pipeResponse() assumes UTF-8` during Configuration.';
        } else {
            $typeErr = "The Response Type `{$type}` does NOT exist but somehow got through the Configuration Checks. Report this FunkPHP Internal Bug/Issue to the `Official FunkPHP Repositories`.";
        }
        if (!isset($ctx) || trim($ctx) === '') {
            $this->setErr($this->getErr('InvalidResponseContext', $ctxVals) . $typeErr, 'Route-pipeResponse', $method, $route);
            $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
            return;
        }
        // Check that Page exists in compiled OR non-compiled for page:
        if ($type === 'page') {
            $ctx = trim($ctx);
            if (!$this->cachedPageFileEITHER_TYPEExists($ctx)) {
                $this->setErr($this->getErr('NoPageAtAllFound', $ctxVals) . ' to be used as the `returned Page in pipeResponse()`.', 'Route-pipeResponse', $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
        }
        // Check that a Single Simple Array Depth String is used for json:
        else if ($type === 'json') {
            $ctx = trim($ctx);
            if (!preg_match('/^[a-zA-Z0-9-_\.]+$/', $ctx)) {
                $this->setErr($this->getErr('InvalidJSONSourceForResponseCtx', $ctxVals) . ' to be used as the `returned JSON Data in pipeResponse()`.', 'Route-pipeResponse', $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
            [$root, $rest] = explode('.', $ctx, 2);
        }
        // Check that User-defined function exists for callback:
        // and that it is not already is set as a Global Handler.
        else if ($type === 'callback') {
            $ctx = trim($ctx);
            if (!$this->cachedUserDefinedFNExists($ctx)) {
                $this->setErr($this->getErr('UserDefinedFUNCTIONNotFoundForResponseCtx', $ctxVals) . ' to be used as the `returned User-defined Callback Function in pipeResponse()`.', 'Route-pipeResponse', $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$ctx])) {
                $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' It cannot be as the `returned User-defined Callback Function in pipeResponse()`. See `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$ctx] . '`', 'Route-pipeResponse', $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
        } else if ($type === 'text') {
            // Nothing really needs to be done here.
        }
        // All good by here so add!
        $this->validBatches['routes'][$method][$route]['response'] = ['type' => $type, 'context' => $ctx];
    }
    private function batchPipeSQLRoute(string $method, string $route, string $sqlFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeSQL', "ROUTES()->{$method}()->ROUTE('{$route}')", $sqlFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeSQL', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['sql']['routes'][$method][$route][$sqlFileFunction])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-pipeSQL', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Route-pipeSQL', $method, $route);
            $this->invalidBatches['sql']['routes'][$method][$route][$sqlFileFunction] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($sqlFileFunction)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Route-pipeSQL', $method, $route);
            $this->invalidBatches['sql']['routes'][$method][$route][$sqlFileFunction] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        // and it will see "sql:" meaning it is a specialized pipe to consider so it does NOT
        // confuse it with regular pipes like function
        if (str_starts_with($sqlFileFunction, 'group:')) {
            $this->validBatches['routes'][$method][$route]['pipes'][] = "sql:$sqlFileFunction";
            return;
        }
        // Parse "filename.fnname" and check just like for pipeFunction()
        // Otherwise we know it is a valid string formatted "filename.functionname"
        [$file, $fn] = explode('.', $sqlFileFunction);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_data_sql', $file);
        $fileData = $this->cached['files_data_sql'][$file] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $fn, $ctxVals, "funkphp\\data\\sql\\{$file}", false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Route-pipeSQL', $method, $route);
            $this->invalidBatches['sql']['routes'][$method][$route][$sqlFileFunction] = true;
            return;
        }
        // When all OK!
        $this->validBatches['routes'][$method][$route]['pipes'][] = "sql:$sqlFileFunction";
    }
    private function batchPipeQueryRoute(string $method, string $route, string $queryFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeQuery', "ROUTES()->{$method}()->ROUTE('{$route}')", $queryFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeQuery', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['query']['routes'][$method][$route][$queryFileFunction])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-pipeQuery', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Route-pipeQuery', $method, $route);
            $this->invalidBatches['query']['routes'][$method][$route][$queryFileFunction] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($queryFileFunction)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Route-pipeQuery', $method, $route);
            $this->invalidBatches['query']['routes'][$method][$route][$queryFileFunction] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        // and it will see "query:" meaning it is a specialized pipe to consider so it does NOT
        // confuse it with regular pipes like function
        if (str_starts_with($queryFileFunction, 'group:')) {
            $this->validBatches['routes'][$method][$route]['pipes'][] = "query:$queryFileFunction";
            return;
        }
        // Parse "filename.fnname" and check just like for pipeFunction()
        [$file, $fn] = explode('.', $queryFileFunction);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_data_query', $file);
        $fileData = $this->cached['files_data_query'][$file] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $fn, $ctxVals, "funkphp\\data\\query\\{$file}", false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Route-pipeQuery', $method, $route);
            $this->invalidBatches['query']['routes'][$method][$route][$queryFileFunction] = true;
            return;
        }
        // When all OK!
        $this->validBatches['routes'][$method][$route]['pipes'][] = "query:$queryFileFunction";
    }
    private function batchPipeValidationRoute(string $method, string $route, string $validationFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeValidation', "ROUTES()->{$method}()->ROUTE('{$route}')", $validationFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeValidation', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['validation']['routes'][$method][$route][$validationFileFunction])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-pipeValidation', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Route-pipeValidation', $method, $route);
            $this->invalidBatches['validation']['routes'][$method][$route][$validationFileFunction] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($validationFileFunction)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Route-pipeValidation', $method, $route);
            $this->invalidBatches['validation']['routes'][$method][$route][$validationFileFunction] = true;
            return;
        }
        // Just add if it starts with "group:" since that is validated by compile()
        // and it will see "validation:" meaning it is a specialized pipe to consider so it does NOT
        // confuse it with regular pipes like function
        if (str_starts_with($validationFileFunction, 'group:')) {
            $this->validBatches['routes'][$method][$route]['pipes'][] = "validation:$validationFileFunction";
            return;
        }
        // Parse "filename.fnname" and check just like for pipeFunction()
        [$file, $fn] = explode('.', $validationFileFunction);
        $this->cachedCreateKeyIfNullAndOptionalFileName('files_data_validation', $file);
        $fileData = $this->cached['files_data_validation'][$file] ?? [];
        // Fatal check: Bails on the first structural error
        $fatalError = $this->validateFNFile($fileData, $fn, $ctxVals, "funkphp\\data\\validation\\{$file}", false);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Route-pipeValidation', $method, $route);
            $this->invalidBatches['validation']['routes'][$method][$route][$validationFileFunction] = true;
            return;
        }
        // When all OK!
        $this->validBatches['routes'][$method][$route]['pipes'][] = "validation:$validationFileFunction";
    }

    /*ROUTE: Compiled versions of above "pipe<Query|SQL|Validation>" Methods!!! Checks in /data/compiled/ Folder! */
    private function batchPipeCompiledSQLRoute(string $method, string $route, string $compiledSQLFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeCompiledSQL', "ROUTES()->{$method}()->ROUTE('{$route}')", $compiledSQLFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeCompiledSQL', $method, $route);
            return;
        }
    }
    private function batchPipeCompiledQueryRoute(string $method, string $route, string $compiledQueryFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeCompiledQuery', "ROUTES()->{$method}()->ROUTE('{$route}')", $compiledQueryFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeCompiledQuery', $method, $route);
            return;
        }
    }
    private function batchPipeCompiledValidationRoute(string $method, string $route, string $compiledValidationFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeCompiledValidation', "ROUTES()->{$method}()->ROUTE('{$route}')", $compiledValidationFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeCompiledValidation', $method, $route);
            return;
        }
    }

    /*ROUTE: excludeMiddleware & excludeHeaders */
    private function batchExcludeMiddlewaresRoute(string $method, string $route, string ...$middlewareToExclude)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setExcludeMiddlewares', "ROUTES()->{$method}()->ROUTE('{$route}')", ...$middlewareToExclude);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setExcludeMiddlewares', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['excludeMiddlewares']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " Set all Middlewares to exclude (this applies both on Method and Global Config) once for this Route.", 'Route-setExcludeMiddlewares', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['excludeMiddlewares'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctx) . " Set all Middlewares to exclude (this applies both on Method and Global Config) once for this Route.", 'Route-setExcludeMiddlewares', $method, $route);
            return;
        }
        // Check that all Middlewares exist; later compile() will also
        // check they exist on correct associated sub-route-depth!
        // and that they do not clash with piped middlewares on the same route as that is conflicting
        $validMWs = [];
        foreach ($middlewareToExclude as $middleware) {
            $middleware = strtolower(trim($middleware));
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($middleware)) {
                $this->setErr($this->getErr('InvalidMiddlewareFunctionName', $ctx) . " Review the Invalid `{$middleware}`.", 'Route-setExcludeMiddlewares', $method, $route);
                $this->invalidBatches['excludeMiddlewares']['routes'][$method][$route] = true;
                return;
            }
            if (in_array($middleware, ($this->validBatches['routes'][$method][$route]['middlewares'] ?? []), true)) {
                $this->setErr($this->getErr('ConflictingExcludeMWWithAlreadyPipedMW', $ctxVals) . " Conflict: `->pipeMiddleware('{$middleware}')`.", 'Route-setExcludeMiddlewares', $method, $route);
                $this->invalidBatches['excludeMiddlewares']['routes'][$method][$route] = true;
                return;
            }
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
            $fileData = $this->cached['files_pipes_middlewares'][$middleware] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares\\{$middleware}", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Route-setExcludeMiddlewares', $method, $route);
                $this->invalidBatches['excludeMiddlewares']['routes'][$method][$route] = true;
                return;
            }
            $validMWs[] = $middleware;
        }
        // Add to excludeMiddleware when all OK!
        $this->validBatches['routes'][$method][$route]['excludeMiddlewares'] = $$validMWs;
    }
    private function batchExcludeHeadersRoute(string $method, string $route, string ...$headersToExclude)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setExcludeHeaders', "ROUTES()->{$method}()->ROUTE('{$route}')", ...$headersToExclude);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setExcludeHeaders', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['excludeHeaders']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " Set all Headers to exclude (this applies both on Method and Global Config) once for this Route.", 'Route-setExcludeHeaders', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['excludeHeaders'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctx) . " Set all Headers to exclude (this applies both on Method and Global Config) once for this Route.", 'Route-setExcludeHeaders', $method, $route);
            return;
        }
        $validHeaders = [];
        foreach ($headersToExclude as $header) {
            $header = strtolower(trim($header));
            if ($header === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $header)) {
                $this->setErr($this->getErr('InvalidHeaderName', $ctxVals) . " Review the Invalid `{$header}`.", 'Route-setExcludeHeaders', $method, $route);
                $this->invalidBatches['excludeHeaders']['routes'][$method][$route] = $headersToExclude;
                return;
            }
            if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$header])) {
                $this->setErr($this->getErr('ConflictingExcludeHeadersWithAlreadyPipedHeader', $ctxVals) . " Conflict: `->pipeHeader('{$header}')`.", 'Route-setExcludeHeaders', $method, $route);
                $this->invalidBatches['excludeHeaders']['routes'][$method][$route] = $headersToExclude;
                return;
            }
            $validHeaders[] = $header;
        }
        $this->validBatches['routes'][$method][$route]['excludeHeaders'] = $validHeaders;
    }

    /*ROUTE: pipeHeader & removeHeader */
    /*ROUTE: setpipeHeaderRoute*/
    private function batchSetHeaderRoute(string $method, string $route, string $header, $value)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setHeader', "ROUTES()->{$method}()->ROUTE('{$route}')", $header);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setHeader', $method, $route);
            return;
        }
        $headerName  = $header;
        $headerValue = $value;
        $lowerHeader = strtolower($headerName);
        // Then check against valid/invalid batches
        if (isset($this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `Header Name` must be unique (case-insensitive).", 'Route-setHeader', $method, $route);
            return;
        }
        // Forbid possible CRLF injection
        if (
            $headerName === '' || $headerValue === ''
            || str_contains($headerName, ":") || str_contains($headerValue, ":")
            || str_contains($headerName, "\r") || str_contains($headerName, "\n")
            || str_contains($headerValue, "\r") || str_contains($headerValue, "\n")
        ) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Route-setHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Route-setHeader', $method, $route);
            return;
        }
        // First check if it already exists
        if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `Header Name` must be unique (case-insensitive).", 'Route-setHeader', $method, $route);
            return;
        }
        if (in_array($lowerHeader, ($this->validBatches['routes'][$method][$route]['excludeHeaders'] ?? []), true)) {
            $this->setErr($this->getErr('ConflictingPipeHeaderWithAlreadyExcludeHeaders', $ctxVals) . " Conflict: `->setExcludeHeaders('{$lowerHeader}')`.", 'Route-setHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['routes'][$method][$route]['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Route-setHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Route level (->config()->ROUTES()-><METHOD>-><ROUTE>)
        $this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }
    /*ROUTE: setRemoveHeaderRoute*/
    private function batchRemoveHeaderRoute(string $method, string $route, string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'removeHeader', "ROUTES()->{$method}()->ROUTE('{$route}')", $header_to_remove);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-removeHeader', $method, $route);
            return;
        }
        // Then check against invalid/valid batches
        if (isset($this->invalidBatches['headers']['routes'][$method][$route]['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Header Name must be unique (case-insensitive).", 'Route-removeHeader', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Header Name must be unique (case-insensitive).", 'Route-removeHeader', $method, $route);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Route-removeHeader', $method, $route);
            return;
        }
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/i', $headerName)) {
            $this->setErr($this->getErr('InvalidHeaderName', $ctxVals), 'Route-removeHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Header cannot be removed if it was first configured to be added
        if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictRemovePipedHeader', $ctxVals), 'Route-removeHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['remove'][] = $header_to_remove;
            return;
        }
        // Store header to be removed from Route level (->config()->ROUTES()-><METHOD>-><ROUTE>)
        $this->validBatches['routes'][$method][$route]['headers']['remove'][$lowerHeader] = $headerName;
    }

    /*PAGE-related Functions like Compiling a Page */
    private function compilePage(string $exactPageFilePath): string
    {
        return '';
    }

    // Two private functions that are ONLY used via Reflection classes so you do not see
    // them while configuring `/src/funkphp/FunkPHP.php` and runs it unless `FunkPHPDeployment.php`
    // is set in `/src/public_html/index.php` to run instead!
    private function compile_setErr(string $err, array &$compileErrors)
    {
        $compileErrors[count($compileErrors) + 1] = $err;
    }
    private function compile_setWarn(string $err, array &$compileWarnings)
    {
        $compileWarnings[count($compileWarnings) + 1] = $err;
    }
    private function compile_add_to_route_trie(string $method, $route) {}

    // Function that generates a Welcome HTML screen when there is nothing in $this->validBatches
    // OR there are zero routes in $this->validBatches['routes]. This should then show a soft success
    // screen and showing how to add some routes and configuration, maybe a link to the Official Docs?
    private function compile_welcome_splash()
    {
        header("content-type: text/html");
        http_response_code(200);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443 ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'loclhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
        $baseUrl = "{$scheme}://{$host}{$basePath}";
        $imgDiskPath = ROOT_PUBLIC_HTML . '/images/favicon.ico';
        $fontDiskPath = ROOT_PUBLIC_HTML . '/fonts/Fredoka-Bold.ttf';
        $fontLightDiskPath = ROOT_PUBLIC_HTML . '/fonts/Fredoka-Light.ttf';
        $PHTML_IMG_SRC = file_exists($imgDiskPath) ? "{$baseUrl}/images/favicon.ico" : "";
        $PHTML_FONT_SRC = file_exists($fontDiskPath) ? "{$baseUrl}/fonts/Fredoka-Bold.ttf" : "";
        $PHTML_FONT2_SRC = file_exists($fontLightDiskPath) ? "{$baseUrl}/fonts/Fredoka-Light.ttf" : "";
    ?>
        <!doctype html>
        <html lang="en">

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>FunkPHP</title>
            <?php if ($PHTML_IMG_SRC): ?>
                <link rel="shortcut icon" href="<?= htmlspecialchars($PHTML_IMG_SRC, ENT_QUOTES, 'UTF-8') ?>" />
            <?php endif; ?>
            <style>
                <?php if ($PHTML_FONT_SRC): ?>@font-face {
                    font-family: 'Fredoka-Bold';
                    src: url("<?= htmlspecialchars($PHTML_FONT_SRC, ENT_QUOTES, 'UTF-8') ?>");
                }

                <?php endif; ?><?php if ($PHTML_FONT2_SRC): ?>@font-face {
                    font-family: 'Fredoka-Light';
                    src: url("<?= htmlspecialchars($PHTML_FONT2_SRC, ENT_QUOTES, 'UTF-8') ?>");
                }

                <?php endif; ?>* {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }

                html {
                    font-size: 14px;
                    font-family: <?php echo $PHTML_FONT_SRC ? "'Fredoka-Bold', " : ""; ?>system-ui, -apple-system, sans-serif;
                    color: #1d2a3b;
                    background-color: #f7f9fc;
                }

                .container {
                    max-width: 900px;
                    margin: 0 auto;
                    padding: 2rem 1rem;
                }

                .card {
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                    padding: 3rem;
                    text-align: left;
                }

                .header {
                    text-align: center;
                    margin-bottom: 2.5rem;
                }

                .title-main {
                    font-size: 2.8rem;
                    color: rgb(28, 9, 48);
                    margin-bottom: 0.5rem;
                    letter-spacing: 0.05rem;
                }

                .badge-success {
                    display: inline-block;
                    background: #e6f4ea;
                    color: #137333;
                    padding: 0.35rem 0.8rem;
                    border-radius: 50px;
                    font-size: 0.9rem;
                    margin-top: 0.5rem;
                }

                .subheading {
                    font-size: 1.1rem;
                    color: #5f6368;
                    line-height: 1.6;
                    font-family: 'Fredroka-Light';
                    font-weight: bold;
                }

                .section-title {
                    font-size: 1.3rem;
                    color: rgb(28, 9, 48);
                    margin: 2rem 0 1rem 0;
                    border-bottom: 2px solid #f1f3f4;
                    padding-bottom: 0.5rem;
                }

                .code-block {
                    background: #1e1e1e;
                    color: #d4d4d4;
                    padding: 1.2rem;
                    border-radius: 8px;
                    font-family: 'Consolas', 'Courier New', monospace;
                    font-size: 0.95rem;
                    line-height: 1.5;
                    overflow-x: auto;
                    margin-bottom: 1rem;
                }

                .code-keyword {
                    color: #569cd6;
                }

                .code-string {
                    color: #ce9178;
                }

                .code-comment {
                    color: #6a9955;
                }

                .code-variable {
                    color: #9cdcfe;
                }

                .btn-docs {
                    display: inline-block;
                    background: rgb(28, 9, 48);
                    color: #ffffff;
                    text-decoration: none;
                    padding: 0.8rem 1.6rem;
                    border-radius: 6px;
                    font-size: 1rem;
                    transition: background 0.2s;
                    margin-top: 1rem;
                }

                .btn-docs:hover {
                    background: #321650;
                }

                footer {
                    text-align: center;
                    color: #b1b1b1;
                    font-size: 0.85rem;
                    margin-top: 2rem;
                    letter-spacing: 0.1rem;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <div class="card">
                    <div class="header">
                        <h1 class="title-main">FunkPHP App Ready</h1>
                        <span class="badge-success">✓ Zero-Config Soft Success</span>
                    </div>

                    <p class="subheading">Your framework core is successfully compiled. To start building endpoints, configure your global settings and routes below.</p>

                    <h2 class="section-title">1. Global Configuration</h2>
                    <p style="margin-bottom: 0.5rem; color: #5f6368;">Configure global security, database instances, and global middleware in <code>/src/funkphp/app/CONFIG.php</code>:</p>
                    <div class="code-block">
                        <span class="code-comment">// src/funkphp/app/CONFIG.php</span><br />
                        <span class="code-comment">/** @var FunkPHP $APP */</span><br />
                        <span class="code-variable">$APP</span>-><span class="code-keyword">CONFIG</span>()<br />
                        -><span class="code-keyword">setDebug</span>(<span class="code-keyword">true</span>)<br />
                        -><span class="code-keyword">pipeMiddleware</span>(<span class="code-string">'cors'</span>)<br />
                        -><span class="code-keyword">pipeMiddleware</span>(<span class="code-string">'rateLimiter'</span>)<br />
                        -><span class="code-keyword">setCSP</span>(<span class="code-string">'default-src'</span>, <span class="code-string">'self'</span>)<br />
                        -><span class="code-keyword">setCSP</span>(<span class="code-string">'script-src'</span>, <span class="code-string">'self'</span>, <span class="code-string">'https://cdn.jsdelivr.net'</span>);<br />
                    </div>

                    <h2 class="section-title">2. Define Route Pipelines</h2>
                    <p style="margin-bottom: 0.5rem; color: #5f6368;">Register RESTful routes with auth guards and handlers in <code>/src/funkphp/app/GET.php</code>:</p>
                    <div class="code-block">
                        <span class="code-comment">// src/funkphp/app/GET.php</span><br />
                        <span class="code-comment">/** @var FunkPHP $APP */</span><br />
                        <span class="code-comment">// Public Healthcheck Endpoint</span><br />
                        <span class="code-variable">$APP</span>-><span class="code-keyword">ROUTES</span>()-><span class="code-keyword">GET</span>()<br />
                        -><span class="code-keyword">ROUTE</span>(<span class="code-string">"/api/v1/health"</span>)<br />
                        -><span class="code-keyword">pipeFunction</span>(<span class="code-string">"system.healthCheck"</span>);<br />

                        <span class="code-comment">// Authenticated User Resource Route</span><br />
                        <span class="code-variable">$APP</span>-><span class="code-keyword">ROUTES</span>()-><span class="code-keyword">GET</span>()<br />
                        -><span class="code-keyword">pipeMiddleware</span>(<span class="code-string">'authGuard'</span>)<br />
                        -><span class="code-keyword">pipeMiddleware</span>(<span class="code-string">'verifyCsrfToken'</span>)<br />
                        -><span class="code-keyword">ROUTE</span>(<span class="code-string">"/api/v1/users/:id"</span>)<br />
                        -><span class="code-keyword">ROUTE</span>(<span class="code-string">"/api/v1/users/:id/profile"</span>)<br />
                        -><span class="code-keyword">pipeFunction</span>(<span class="code-string">"users.getProfile"</span>);<br />
                    </div>

                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="https://www.funkphp.com" target="_blank" class="btn-docs">FunkPHP Official DOCS →</a>
                    </div>
                </div>
                <footer>©2025-2026 FunkPHP.com — Funky Functional Programming</footer>
            </div>
        </body>

        </html>
    <?php
        exit;
    }

    // Function that generates HTML to then output an easier
    // visualized version of current errors and/or warnings.
    private function output_errors(array $internalErrors = [], ?array $compileErrors = [], array $compileWarnings = [])
    {
        header("content-type: text/html; charset=utf-8");
        http_response_code(500);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443 ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
        $baseUrl = "{$scheme}://{$host}{$basePath}";
        $imgDiskPath = ROOT_PUBLIC_HTML . '/images/favicon.ico';
        $fontDiskPath = ROOT_PUBLIC_HTML . '/fonts/Fredoka-Bold.ttf';
        $fontLightDiskPath = ROOT_PUBLIC_HTML . '/fonts/Fredoka-Light.ttf';
        $PHTML_IMG_SRC = file_exists($imgDiskPath) ? "{$baseUrl}/images/favicon.ico" : "";
        $PHTML_FONT_SRC = file_exists($fontDiskPath) ? "{$baseUrl}/fonts/Fredoka-Bold.ttf" : "";
        $PHTML_FONT2_SRC = file_exists($fontLightDiskPath) ? "{$baseUrl}/fonts/Fredoka-Light.ttf" : "";
        // Get the `` highlighted version instead
        $formatMsg = function ($msg) {
            if (!is_string($msg)) {
                $msg = str_replace(['\/', '\\/'], '/', json_encode($msg, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES));
            }
            $escaped = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
            return preg_replace('/`([^`]+)`/', '<span class="code-badge">$1</span>', $escaped);
        };
        // Prepare and count all errors (if any)
        $tabs = ['API', 'CONFIG', 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'COMPILATION', 'INTERNAL'];
        $bucketed = [];
        foreach ($tabs as $t) {
            $bucketed[$t] = [
                'errors' => 0,
                'warnings' => 0
            ];
        }
        $allErrors = $internalErrors['ERRORS'];
        if (!empty($allErrors['CONFIG'])) {
            $bucketed['CONFIG']['errors'] = count($allErrors['CONFIG']);
        }
        if (!empty($compileErrors)) {
            $bucketed['COMPILATION']['errors'] = count($compileErrors);
        }
        if (!empty($compileWarnings)) {
            $bucketed['COMPILATION']['warnings'] = count($compileWarnings);
        }
        if (!empty($allErrors['METHODS'])) {
            foreach ($allErrors['METHODS'] as $method => $methodData) {
                $methodUpper = strtoupper($method);
                if (isset($bucketed[$methodUpper])) {
                    if (!empty($methodData['CONFIG']) && is_array($methodData['CONFIG'])) {
                        $bucketed[$methodUpper]['errors'] += count($methodData['CONFIG']);
                    }
                    if (!empty($methodData['ROUTES']) && is_array($methodData['ROUTES'])) {
                        foreach ($methodData['ROUTES'] as $routePath => $routeErrors) {
                            if (is_array($routeErrors)) {
                                $bucketed[$methodUpper]['errors'] += count($routeErrors);
                            }
                        }
                    }
                }
            }
        }
        $totalErrors = 0;
        $totalWarnings = count($compileWarnings);
        foreach ($tabs as $t) {
            if (isset($bucketed[$t]['errors'])) {
                $totalErrors += $bucketed[$t]['errors'];
            }
        }
        // Show first tab with errors OR API as default since it never has errors.
        // There is also a <script> tag part that remembers last chosen tab.
        $activeTab = 'API';
        foreach ($tabs as $t) {
            if (!empty($bucketed[$t]['errors']) || !empty($bucketed[$t]['warnings'])) {
                $activeTab = $t;
                break;
            }
        }
    ?>
        <!doctype html>
        <html lang="en">

        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>FunkPHP Fluent API</title>
            <?php if ($PHTML_IMG_SRC): ?>
                <link rel="shortcut icon" href="<?= htmlspecialchars($PHTML_IMG_SRC, ENT_QUOTES, 'UTF-8') ?>" />
            <?php endif; ?>
            <style>
                <?php if ($PHTML_FONT_SRC): ?>@font-face {
                    font-family: 'Fredoka-Bold';
                    src: url("<?= htmlspecialchars($PHTML_FONT_SRC, ENT_QUOTES, 'UTF-8') ?>");
                }

                <?php endif; ?><?php if ($PHTML_FONT2_SRC): ?>@font-face {
                    font-family: 'Fredoka-Light';
                    src: url("<?= htmlspecialchars($PHTML_FONT2_SRC, ENT_QUOTES, 'UTF-8') ?>");
                }

                <?php endif; ?>* {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }

                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    background: #0d1117;
                    color: #c9d1d9;
                    padding: 2rem 1rem;
                }

                .container {
                    max-width: 1100px;
                    margin: 0 auto;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 1.5rem;
                    padding-bottom: 1rem;
                    border-bottom: 1px solid #21262d;
                }

                .title {
                    font-family: <?php echo $PHTML_FONT_SRC ? "'Fredoka-Bold', " : ""; ?>sans-serif;
                    font-size: 2rem;
                    color: rgb(141, 85, 201);
                }

                .summary-badges {
                    display: flex;
                    gap: 0.6rem;
                }

                .badge {
                    padding: 0.35rem 0.8rem;
                    border-radius: 20px;
                    font-size: 0.85rem;
                    font-weight: 600;
                    font-family: monospace;
                }

                .badge-danger {
                    background: rgba(248, 81, 73, 0.15);
                    color: #ff7b72;
                    border: 1px solid rgba(248, 81, 73, 0.4);
                }

                .badge-warning {
                    background: rgba(210, 153, 34, 0.15);
                    color: #d29922;
                    border: 1px solid rgba(210, 153, 34, 0.4);
                }

                .tabs-header {
                    display: flex;
                    background: #161b22;
                    border-radius: 8px 8px 0 0;
                    border: 1px solid #30363d;
                    border-bottom: none;
                    overflow-x: auto;
                }

                .tab-btn {
                    padding: 0.85rem 1.4rem;
                    background: none;
                    border: none;
                    color: #8b949e;
                    font-size: 0.95rem;
                    font-weight: 700;
                    cursor: pointer;
                    border-bottom: 3px solid transparent;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    transition: all 0.15s ease;
                }

                .tab-btn:hover {
                    color: #c9d1d9;
                    background: rgba(255, 255, 255, 0.02);
                }

                .tab-btn.active {
                    color: #58a6ff;
                    border-bottom: 3px solid #58a6ff;
                    background: #0d1117;
                }

                .tab-count {
                    background: #21262d;
                    color: #8b949e;
                    border-radius: 10px;
                    padding: 0.1rem 0.45rem;
                    font-size: 0.75rem;
                }

                .tab-btn.active .tab-count {
                    background: rgba(56, 139, 253, 0.15);
                    color: #58a6ff;
                }

                .tab-btn.has-errors .tab-count {
                    background: rgba(248, 81, 73, 0.2);
                    color: #ff7b72;
                }

                .tab-content {
                    background: #0d1117;
                    border: 1px solid #30363d;
                    border-radius: 0 0 8px 8px;
                    padding: 1.5rem;
                    min-height: 420px;
                }

                .tab-panel {
                    display: none;
                }

                .tab-panel.active {
                    display: block;
                }

                .route-group {
                    margin-bottom: 2rem;
                }

                .route-header {
                    font-family: monospace;
                    font-size: 1.00rem;
                    font-weight: 600;
                    color: #79c0ff;
                    background: #161b22;
                    padding: 0.5rem 0.8rem;
                    border-radius: 6px;
                    border: 1px solid #21262d;
                    margin-bottom: 0.75rem;
                    display: inline-block;
                }

                .issue-card {
                    background: #161b22;
                    border-left: 4px solid #ff7b72;
                    padding: 1rem 1.2rem;
                    margin-bottom: 0.8rem;
                    border-radius: 0 6px 6px 0;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                }

                .api-card-consolidated {
                    background: #161b22;
                    border-left: 4px solid #8d55c9;
                    padding: 1.25rem 1rem;
                    border-radius: 0 6px 6px 0;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                    font-family: 'Consolas', 'Courier New', monospace;
                }

                .api-tree-line {
                    margin-bottom: 0.5rem;
                    position: relative;
                    transition: padding 0.1s ease;
                }

                .code-badge.badge-root {
                    background: #232d3f;
                    border-color: #388bfd66;
                    font-weight: 700;
                }

                .issue-card.warn {
                    border-left-color: #d29922;
                }

                .issue-type {
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    font-weight: 700;
                    letter-spacing: 0.05rem;
                    margin-bottom: 0.3rem;
                }

                .issue-card .issue-type {
                    color: #ff7b72;
                }

                .issue-card.warn .issue-type {
                    color: #d29922;
                }

                .issue-body {
                    font-family: 'Consolas', 'Courier New', monospace;
                    font-size: 0.92rem;
                    color: #e6edf3;
                    line-height: 2;
                }

                .code-badge {
                    background: #21262d;
                    color: #79c0ff;
                    padding: 0.05rem 0.15rem;
                    border-radius: 4px;
                    border: 1px solid #363b42;
                    font-family: monospace;
                    font-size: 0.75rem;
                }

                .empty-state {
                    text-align: center;
                    color: #8b949e;
                    padding: 4rem 1rem;
                    font-size: 1rem;
                }

                .alert-warning {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    background: rgba(210, 153, 34, 0.12);
                    border: 1px solid rgba(210, 153, 34, 0.4);
                    border-left: 4px solid #d29922;
                    color: #e3b341;
                    padding: 12px 16px;
                    border-radius: 6px;
                    margin-bottom: 1rem;
                    font-size: 0.88rem;
                    line-height: 1.4;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                }

                .alert-warning .alert-icon {
                    font-size: 1.25rem;
                    flex-shrink: 0;
                }

                .alert-warning .alert-content {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                }

                .alert-warning .alert-content strong {
                    color: #f0b72f;
                    font-size: 0.82rem;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <div class="header">
                    <div class="title">FunkPHP Fluent API</div>
                    <div class="summary-badges">
                        <span class="badge badge-danger"><?= $totalErrors ?> Error<?= $totalErrors === 1 ? '' : 's' ?></span>
                        <span class="badge badge-warning"><?= $totalWarnings ?> Warning<?= $totalWarnings === 1 ? '' : 's' ?></span>
                    </div>
                </div>
                <?php if (!empty($this->debug['ALWAYS_SHOW'])): ?>
                    <div class="alert-warning">
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-content">
                            <span><code>->CONFIG()->setDebug()</code> 2nd argument is <code>TRUE</code> (always show). Set it to <code>FALSE</code> to Allow Compiled Execution.</span>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="tabs-header">
                    <?php foreach ($tabs as $tab):
                        $errCnt = $bucketed[$tab]['errors'] ?? 0;
                        $warnCnt = $bucketed[$tab]['warnings'] ?? 0;
                        $totalTabItems = $errCnt + $warnCnt;
                        $hasClass = $errCnt > 0 ? 'has-errors' : '';
                    ?>
                        <button id="btn-tab-<?= $tab ?>" class="tab-btn <?= $tab === $activeTab ? 'active' : '' ?> <?= $hasClass ?>" onclick="switchTab(event, 'tab-<?= $tab ?>')">
                            <?= $tab ?>
                            <?php if ($totalTabItems > 0): ?>
                                <span class="tab-count"><?= $totalTabItems ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="tab-content">
                    <?php foreach ($tabs as $tab):
                        $hasContent = false;
                        if ($tab === 'CONFIG') {
                            if (count(($allErrors['CONFIG'] ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'API') {
                            if (count(($this->FunkPHPFluentAPI ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'COMPILATION') {
                            if (count(($compileErrors ?? [])) > 0 || count($compileWarnings) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'INTERNAL') {
                            if (count(($allErrors['INTERNAL'] ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else {
                            if (
                                count(($allErrors['METHODS'][$tab]['CONFIG'] ?? [])) > 0
                                || count(($allErrors['METHODS'][$tab]['ROUTES'] ?? [])) > 0
                            ) {
                                $hasContent = true;
                            }
                        }
                    ?>
                        <div id="tab-<?= $tab ?>" class="tab-panel <?= $tab === $activeTab ? 'active' : '' ?>">
                            <?php if (!$hasContent): ?>
                                <?php if ($tab === 'INTERNAL'): ?>
                                    <div class="empty-state">
                                        ✓ No Internal FunkPHP Errors
                                    </div>
                                <?php elseif ($tab === 'COMPILATION'): ?>
                                    <div class="empty-state">
                                        ✓ No FunkPHP Compilation Errors
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        ✓ No Configuration Errors/Warnings in <code><?= $tab ?></code> - <?= $formatMsg("`/src/funkphp/app/{$tab}.php`");  ?>
                                    </div>
                                <?php endif ?>
                            <?php else: ?>
                                <?php
                                // TAB IS "CONFIG"?
                                if ($tab === 'CONFIG') {
                                    $CONFIG_ERRS = $allErrors['CONFIG'] ?? [];
                                ?> <div class="route-group">
                                        <div class="route-header">GLOBAL CONFIG | $APP->CONFIG() in /src/funkphp/app/CONFIG.php</div>
                                        <?php foreach ($CONFIG_ERRS as $idx => $C_ERR) {
                                        ?> <div class="issue-card">
                                                <div class="issue-type">ERROR #<?= $idx ?></div>
                                                <div class="issue-body"><?= $formatMsg($C_ERR['err']) ?></div>
                                            </div> <?php
                                                } ?>
                                    </div>
                                <?php
                                }
                                // TAB IS "INTERNAL"?
                                else if ($tab === 'INTERNAL') {
                                    $INTERNAL_ERRS = $allErrors['INTERNAL'] ?? [];
                                ?> <div class="route-group">
                                        <div class="route-header">Internal FunkPHP Errors (applies to all files in /src/funkphp/app)</div>
                                        <?php foreach ($INTERNAL_ERRS as $idx => $I_ERR) {
                                        ?> <div class="issue-card.warn">
                                                <div class="issue-type">ERROR #<?= $idx ?></div>
                                                <div class="issue-body"><?= $formatMsg($I_ERR['err']) ?></div>
                                            </div> <?php
                                                } ?>
                                    </div>
                                <?php
                                }
                                // TAB IS "API"?
                                else if ($tab === 'API') {
                                    $API_TREE = $this->FunkPHPFluentAPI['ALL'] ?? [];
                                    $currentDepth = 0;
                                ?>
                                    <div class="route-group">
                                        <div class="route-header">FunkPHP Fluent API Tree (all files in /src/funkphp/app)</div>
                                        <div class="api-card api-card-consolidated">
                                            <?php foreach ($API_TREE as $idx => $apiStr):
                                                $trimmed = trim($apiStr);
                                                $upper = strtoupper($trimmed);
                                                if ($upper === '->CONFIG()' || $upper === '->ROUTES()') {
                                                    $currentDepth = 0;
                                                    $lineDepth = 0;
                                                } elseif (preg_match('/^->(GET|POST|PUT|DELETE|PATCH)\(\)$/', $upper)) {
                                                    $currentDepth = 1;
                                                    $lineDepth = 1;
                                                } elseif (str_starts_with($upper, '->ROUTE(')) {
                                                    $currentDepth = 2;
                                                    $lineDepth = 2;
                                                } else {
                                                    $lineDepth = $currentDepth + 1;
                                                }
                                                $paddingPx = $lineDepth * 24;
                                            ?>
                                                <div class="api-tree-line" style="padding-left: <?= $paddingPx ?>px;">
                                                    <span class="code-badge <?= ($upper === '->ROUTES()' || $upper === '->CONFIG()') ? '' : (preg_match('/^->(GET|POST|PUT|DELETE|PATCH)\(\)$/', $upper) ? 'badge-method' : '') ?>">
                                                        <?= $formatMsg($trimmed); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php
                                }
                                // TAB IS "COMPILATION"?
                                else if ($tab === 'COMPILATION') {
                                    $COMPILE_ERRS = $compileErrors ?? [];
                                    $COMPLE_WARNS = $compileWarnings ?? [];
                                ?> <?php if (count($COMPILE_ERRS) > 0): ?>
                                        <div class="route-group">
                                            <div class="route-header">FunkPHP Compilation Errors (happens only if Zero Errors otherwise in all files in /src/funkphp/app)</div>
                                            <?php foreach ($COMPILE_ERRS as $idx => $COMP_ERR) {
                                            ?> <div class="issue-card">
                                                    <div class="issue-type">ERROR #<?= $idx ?></div>
                                                    <div class="issue-body"><?= $formatMsg($COMP_ERR) ?></div>
                                                </div> <?php
                                                    } ?>
                                        </div>
                                    <?php endif ?>
                                    <?php if (count($COMPLE_WARNS) > 0): ?>
                                        <div class="route-group">
                                            <div class="route-header">FunkPHP Compilation Warnings (happens only if Zero Errors otherwise in all files in /src/funkphp/app)</div>
                                            <?php foreach ($COMPLE_WARNS as $idx2 => $COMP_WARN) {
                                            ?> <div class="issue-card warn">
                                                    <div class="issue-type">WARNING #<?= $idx2 ?></div>
                                                    <div class="issue-body"><?= $formatMsg($COMP_WARN) ?></div>
                                                </div> <?php
                                                    } ?>
                                        </div>
                                    <?php endif ?>
                                    <?php
                                }
                                // TAB IS <METHOD>?
                                else {
                                    $R_CONFIG_ERRS = $allErrors['METHODS'][$tab]['CONFIG'] ?? [];
                                    $R_ROUTES_ERRS = $allErrors['METHODS'][$tab]['ROUTES'] ?? [];
                                    ?><?php if (count($R_CONFIG_ERRS) > 0) {  ?>
                                    <div class="route-group">
                                        <div class="route-header"><?= $tab ?> CONFIG | $APP->ROUTES()-><?= $tab ?>() in /src/funkphp/app/<?= $tab ?>.php</div>
                                        <?php foreach ($R_CONFIG_ERRS as $idx => $RC_ERR) {
                                        ?> <div class="issue-card">
                                                <div class="issue-type">ERROR #<?= $idx ?></div>
                                                <div class="issue-body"><?= $formatMsg($RC_ERR['err']) ?></div>
                                            </div> <?php
                                                }
                                                    ?>
                                    </div>
                                <?php } ?>
                                <div class="route-group">
                                    <?php foreach ($R_ROUTES_ERRS as $singleMethodRoute => $singleMethodRouteDetails) {
                                    ?>
                                        <div class="route-header">'<?= "$tab$singleMethodRoute"; ?>' | $APP->ROUTES()-><?= $tab ?>()->ROUTE('<?= $singleMethodRoute; ?>') in /src/funkphp/app/<?= $tab ?>.php</div>
                                        <?php foreach ($singleMethodRouteDetails as $rErrIdx => $rErr) {
                                        ?> <div class="issue-card">
                                                <div class="issue-type">ERROR #<?= $rErrIdx ?></div>
                                                <div class="issue-body"><?= $formatMsg($rErr['err']) ?></div>
                                            </div> <?php
                                                }
                                            } ?>
                                </div>
                            <?php
                                } ?>
                        <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <script>
                function switchTab(evt, tabId) {
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
                    evt.currentTarget.classList.add('active');
                    localStorage.setItem('lastTab', tabId);
                    document.getElementById(tabId).classList.add('active');
                }
                if (localStorage.getItem('lastTab') !== null) {
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'))
                    document.getElementById(localStorage.getItem('lastTab')).classList.add('active');
                    document.getElementById("btn-" + localStorage.getItem('lastTab')).classList.add('active');
                }
            </script>
        </body>

        </html>
<?php
        exit;
    }
    /**
     * Checks whether a middleware is active globally, in method,
     * OR on any parent route prefix leading up to the current route.
     */
    private function isMiddlewareActiveInScope(string $middleware, string $method, string $route): bool
    {
        // Even registered?
        if (!isset($this->cached['placeholderMiddlewaresInWhatRoutes'][$middleware])) {
            return false;
        }
        $activeScopes = $this->cached['placeholderMiddlewaresInWhatRoutes'][$middleware];
        // In global OR method scope?
        if (
            in_array('GLOBAL', $activeScopes, true)
            || in_array($method, $activeScopes, true)
        ) {
            return true;
        }
        // Check Direct Route Path & Parent Ancestor Routes
        // Construct target key format: "GET/:id/users"
        $fullPathKey = $method . ($route === '/' ? '/' : $route);
        foreach ($activeScopes as $scope) {
            if ($scope === 'GLOBAL' || $scope === $method) {
                continue;
            }
            // Exact Route Match
            if ($scope === $fullPathKey) {
                return true;
            }
            // Prefix/Ancestor Match: Ensures "GET/:id" matches a descendant "GET/:id/users"
            // append trailing slash to prevent false positives like "GET/:id" matching "GET/:identifier"
            $scopePrefix = rtrim($scope, '/') . '/';
            $fullPathPrefix = rtrim($fullPathKey, '/') . '/';
            if (str_starts_with($fullPathPrefix, $scopePrefix)) {
                return true;
            }
        }
        return false;
    }
    // Actual compile() function that can EITHER compile and run it as is
    // OR compile and output it to the FunkPHPDeployment.php File instead.
    private function compile(bool $CompileAndRunLocally = true)
    {
        //REFER TO THESE TO
        //$this->FUNKPHP_COMPILED = false;
        //$this->FUNKPHP_COMPILED_SUCCESS = false;

        // Initialize global $c already in `/src/funkphp/FunkPHP.php` to populate it
        // for runtime either in compiled `/src/funkphp/FunkPHPDeployment.php` or
        // just after calling $this->run() after, only, a valid compilation.
        $PATH_USER_DEFINED_FNS = '/src/funkphp/config/functions.php';
        $PATH_CLASSES = '/src/funkphp/config/classes.php';
        // Contains User-defined functions that are assigned Global Handlers meaning
        // they are prioritized even if they are configured at the end of the app.
        $GLOBAL_HANDLERS = [];
        // Contains group:<Name,FN,FN2> (and soon)
        $GLOBAL_GROUPED = [
            'MIDDLEWARES' => [],
            'REQUEST' => [],
            'POST_RESPONSE' => [],
            'USER_DEFINED' => [],
            'ROUTES_FILE_FUNCTIONS' => [],
            'DATA_QUERY_FNS' => [],
            'DATA_SQL_FNS' => [],
            'DATA_VALIDATION_FNS' => [],
            'DATA_QUERY_COMPILED_FNS' => [],
            'DATA_SQL_COMPILED_FNS' => [],
            'DATA_VALIDATION_COMPILED_FNS' => [],
        ];
        // ------------------------------------------------------------------------------------------
        // Attempt compiling FunkPHP and create the code
        // STEP 1: Check there are zero Invalid Batches and zero errors so far.
        // Otherwise, we dump API + Errors and exist early (default in dd()).
        // ------------------------------------------------------------------------------------------
        if ($this->errors['ERRORS'] > 0 || count($this->invalidBatches) > 0) {
            $this->output_errors($this->errors, $this->compileErrors, $this->compileWarnings);
        }

        // ------------------------------------------------------------------------------------------
        // STEP 1.1 EDGE-CASE: Nothing Configured but CONFIG() and ROUTES() are up but nothing used?
        // ------------------------------------------------------------------------------------------
        if (count($this->validBatches) === 0) {
            $this->compile_welcome_splash();
        }

        // ------------------------------------------------------------------------------------------
        // STEP 2.5: Validate User-defined Functions & Classes that were used and that might exist
        // ------------------------------------------------------------------------------------------
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_classes');
        // If files are invalid PHP code already
        if (!$this->cached['file_user_defined_functions']['syntax_valid']) {
            $this->compile_setErr("File Function Error in `{$PATH_USER_DEFINED_FNS} while Compiling FunkPHP Configuration`: File contains Invalid PHP Syntax: '`{$this->cached['file_user_defined_functions']['syntax_error']}`' that needs to be resolved.", $this->compileErrors);
        } else if (
            isset($this->cached['file_user_defined_functions']['functions'])
            && count($this->cached['file_user_defined_functions']['functions']) > 0
        ) {
            foreach ($this->cached['file_user_defined_functions']['functions'] as $userFN => $_) {
                $fatalError = $this->validateFNFile($this->cached['file_user_defined_functions'], $userFN, " `while Compiling FunkPHP Configuration`", "");
                if ($fatalError !== null) {
                    $this->compile_setErr($fatalError . " If you wanna keep the Function but not use it for this Compilation, comment it out inside of the `{$PATH_USER_DEFINED_FNS}` File and retry.", $this->compileErrors);
                }
            }
        }
        if (!$this->cached['file_user_defined_classes']['syntax_valid']) {
            $this->compile_setErr("File Class Error in `{$PATH_USER_DEFINED_FNS} while Compiling FunkPHP Configuration`: File contains Invalid PHP Syntax: '`{$this->cached['file_user_defined_classes']['syntax_error']}`' that needs to be resolved.", $this->compileErrors);
        } else if (
            isset($this->cached['file_user_defined_classes']['classes'])
            && count($this->cached['file_user_defined_classes']['classes']) > 0
        ) {
            foreach ($this->cached['file_user_defined_classes']['classes'] as $userClass => $_) {
                $fatalError = $this->validateCLASSFile($this->cached['file_user_defined_classes'], $userClass, " `while Compiling FunkPHP Configuration`", "");
                if ($fatalError !== null) {
                    echo "ERROR?";
                    $this->compile_setErr($fatalError . " If you wanna keep the Class but not use it for this Compilation, comment it out inside of the `{$PATH_CLASSES}` File and retry.", $this->compileErrors);
                }
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 2: First add to the $compiled->c variable that can be added right away
        // ------------------------------------------------------------------------------------------
        // 3 BOOLEANS
        if (isset($this->validBatches['config']['FUNKPHP_ONLINE'])) {
            $this->compiled['c']['FUNKPHP_ONLINE'] = $this->validBatches['config']['FUNKPHP_ONLINE'];
        }
        if (isset($this->validBatches['config']['USE_VENDOR'])) {
            $this->compiled['c']['FUNKPHP_USE_VENDOR'] = $this->validBatches['config']['USE_VENDOR'];
        }
        if (isset($this->validBatches['config']['USE_HTTPS'])) {
            $this->compiled['c']['FUNKPHP_USE_HTTPS'] = $this->validBatches['config']['USE_HTTPS'];
        }
        // 4 STRINGS
        if (isset($this->validBatches['config']['BASEURL_HOST'])) {
            $this->compiled['c']['BASEURLS']['HOST'] = $this->validBatches['config']['BASEURL_HOST'];
        }
        if (isset($this->validBatches['config']['BASEURL_LOCAL'])) {
            $this->compiled['c']['BASEURLS']['LOCAL'] = $this->validBatches['config']['BASEURL_LOCAL'];
        }
        if (isset($this->validBatches['config']['BASEURL_ONLINE'])) {
            $this->compiled['c']['BASEURLS']['ONLINE'] = $this->validBatches['config']['BASEURL_ONLINE'];
        }
        if (isset($this->validBatches['config']['BASEURL_URI'])) {
            $this->compiled['c']['BASEURLS']['BASEURL_URI'] = $this->validBatches['config']['BASEURL_URI'];
        }
        // 1 ARRAY (ini_set(s))
        if (isset($this->validBatches['config']['setINI_SET'])) {
            $this->compiled['config']['INI_SETS'] = $this->validBatches['config']['setINI_SET'];
        }
        // ------------------------------------------------------------------------------------------
        // STEP 3: Check Global Handlers set and then all setGroup<VARIANTS> since they can refer to
        // either non-existing function+files AND/OR to set Global Handlers which would conflict.
        // STEP 3.1 - Global Handlers
        // ------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->compiled['c']['FUNKPHP_CUSTOM_HTTPS_KERNEL'] = $this->validBatches['config']['DEFAULT_HTTPS_KERNEL'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_HTTPS_KERNEL']] = "User-defined Default HTTPS Kernel Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->compiled['c']['FUNKPHP_CUSTOM_EXCEPTION_HANDLER'] = $this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER']] = "User-defined Default Exception Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->compiled['c']['FUNKPHP_CUSTOM_ERROR_HANDLER'] = $this->validBatches['config']['DEFAULT_ERROR_HANDLER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_ERROR_HANDLER']] = "User-defined Default Error Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->compiled['c']['FUNKPHP_CUSTOM_URI_NORMALIZER'] = $this->validBatches['config']['DEFAULT_URI_NORMALIZER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_URI_NORMALIZER']] = "User-defined Default URI Normalizer Handler";
        }
        // -----------------------------------------------------------------------------
        // STEP 3.2 - Grouped<VARIANTS>
        // -----------------------------------------------------------------------------
        if (isset($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'])) {
            foreach ($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'] as $GROUPED_UD_NAME => $GROUPED_UD_VALS) {
                $validGroup = true;
                foreach ($GROUPED_UD_VALS as $UD_FN) {
                    if (isset($GLOBAL_HANDLERS[$UD_FN])) {
                        $this->compile_setErr("Grouped-configured User-defined Function `{$UD_FN}` in `{$PATH_USER_DEFINED_FNS}` in `->setGroupPipeUserdefined('{$GROUPED_UD_NAME}')` conflicts with already defined Global Handler Role `{$GLOBAL_HANDLERS[$UD_FN]}.` Remove `{$UD_FN}` from the `->setGroupPipeUserdefined()` OR from the `Global Handler Role`.", $this->compileErrors);
                        //$compileErrors[count($compileErrors) + 1] = "Grouped-configured User-defined Function `{$UD_FN}` in `{$PATH_USER_DEFINED_FNS}` in `->setGroupPipeUserdefined('{$GROUPED_UD_NAME}')` conflicts with already defined Global Handler Role `{$GLOBAL_HANDLERS[$UD_FN]}.` Remove `{$UD_FN}` from the `->setGroupPipeUserdefined()` OR from the `Global Handler Role`.";
                        $validGroup = false;
                    }
                }
                if ($validGroup) {
                    $GLOBAL_GROUPED['USER_DEFINED']["group:$GROUPED_UD_NAME"] = $GROUPED_UD_VALS;
                }
            }
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_MIDDLEWARES'])) {
            foreach ($this->validBatches['config']['GROUPED_PIPE_MIDDLEWARES'] as $GROUPED => $_) {
                $GLOBAL_GROUPED['MIDDLEWARES']["group:$GROUPED"] = $_;
            }
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_REQUEST'])) {
            foreach ($this->validBatches['config']['GROUPED_PIPE_REQUEST']  as $GROUPED => $_) {
                $GLOBAL_GROUPED['REQUEST']["group:$GROUPED"] = $_;
            }
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_ROUTES'])) {
            foreach ($this->validBatches['config']['GROUPED_PIPE_ROUTES'] as $GROUPED => $_) {
                $GLOBAL_GROUPED['ROUTES_FILE_FUNCTIONS']["group:$GROUPED"] = $_;
            }
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_POST_RESPONSE'])) {
            foreach ($this->validBatches['config']['GROUPED_PIPE_POST_RESPONSE'] as $GROUPED => $_) {
                $GLOBAL_GROUPED['POST_RESPONSE']["group:$GROUPED"] = $_;
            }
        }
        // -----------------------------------------------------------------------------
        // STEP 4: Check SESSION (driver + COOKIES) - either AS_OPTIONS or single values
        // -----------------------------------------------------------------------------
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->compiled['c']['SESSION'] = $this->validBatches['config']['SESSION'];
        } else {
            // Use user-defined (UD) OR default Session Driver? (files)
            if (!isset($this->validBatches['config']['SESSION']['driver'])) {
                $this->compiled['c']['SESSION']['driver'] = "files";
                $this->compile_setWarn("No Default `Session Cookie Driver` set (with `'->CONFIG()->setSessionDriver()'`) - using default: `'files'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['driver'] = $this->validBatches['config']['SESSION']['driver'];
            }
            // UD or default Session Name?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_NAME'] = "fphp_id";
                $this->compile_setWarn("No Default `Session Cookie Name` set (with `'->CONFIG()->setSessionCookieName()'`) - using default: `'fphp_id'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_NAME'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'];
            }
            // UD or default Session Domain?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_DOMAIN'] = "funkphp";
                $this->compile_setWarn("No Default `Session Cookie Domain` set (with `'->CONFIG()->setSessionCookieDomain()'`) - using default: `'funkphp'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_DOMAIN'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'];
            }
            // UD or default Session Path?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_PATH'] = "/";
                $this->compile_setWarn("No Default `Session Cookie Domain` set (with `'->CONFIG()->setSessionCookiePath()'`) - using default: `'/'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_PATH'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'];
            }
            // UD or default Session Lifetime?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_LIFETIME'] = 28800;
                $this->compile_setWarn("No Default `Session Cookie Lifetime` set (with `'->CONFIG()->setSessionCookieLifetime()'`) - using default: `'28800'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_LIFETIME'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'];
            }
            // UD or default Session Lifetime?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SAMESITE'] = 'Lax';
                $this->compile_setWarn("No Default `Session Cookie Samesite` set (with `'->CONFIG()->setSessionCookieSameSite()'`) - using default: `'Lax'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SAMESITE'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'];
            }
            // UD or default Session SECURE?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SECURE'] = false;
                $this->compile_setWarn("No Default `Session Cookie Secure` set (with `'->CONFIG()->setSessionCookieSecure()'`) - using default: `'false'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SECURE'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'];
            }
            // UD or default Session HTTP?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = true;
                $this->compile_setWarn("No Default `Session Cookie HttpOnly` set (with `'->CONFIG()->setSessionCookieHTTPOnly()'`) - using default: `'true'`.", $this->compileWarnings);
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'];
            }
        }
        // ------------------------------------------------------------------------------------------------
        // STEP 5: NoRouteMatch on Global/CONFIG+Every Methods Level (callback cannot be Global Handler FN!)
        // Which starts with NoRouteMatch CALLBACK then JSON & PAGE Globally, then same again but <METHODS>
        // ------------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH'])) {
            if (
                isset($this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])
                && isset($GLOBAL_HANDLERS[$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']])
            ) {
                $this->compile_setErr("User-defined Function `{$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']}` in `{$PATH_USER_DEFINED_FNS}` in `->CONFIG()->setNoRouteMatchCallback('{$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']}')` conflicts with already defined `Global Handler Role {$GLOBAL_HANDLERS[$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']]}`. Remove `{$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']}` from `->CONFIG()->setNoRouteMatchCallback()` OR from the `Global Handler Role`.", $this->compileErrors);
            } else {
                $this->compiled['config']['NO_ROUTE_MATCH']['CALLBACK'] =  $this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK'];
            }
            if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
                $this->compiled['config']['NO_ROUTE_MATCH']['PAGE'] =  $this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'];
            }
            if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
                $this->compiled['config']['NO_ROUTE_MATCH']['JSON'] =  $this->validBatches['config']['NO_ROUTE_MATCH']['JSON'];
            }
        }
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'] as $method) {
            if (
                isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])
            ) {
                if (isset($GLOBAL_HANDLERS[$this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK']])) {
                    $callbackFn = $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'];
                    $role = $GLOBAL_HANDLERS[$callbackFn];
                    $this->compile_setErr(
                        "User-defined Function `{$callbackFn}` in `{$PATH_USER_DEFINED_FNS}` in `->ROUTES()->{$method}()->setNoRouteMatchCallback('{$callbackFn}')` conflicts with already defined `Global Handler Role {$role}`. Remove `{$callbackFn}` from `->ROUTES()->{$method}()->setNoRouteMatchCallback()` OR from the `Global Handler Role`.",
                        $compileErrors
                    );
                } else {
                    $this->compiled['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'] =   $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'];
                }
            }
            if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
                $this->compiled['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] =  $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'];
            }
            if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
                $this->compiled['methods'][$method]['NO_ROUTE_MATCH']['JSON'] =  $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'];
            }
        }
        // ------------------------------------------------------------------------------------------------
        // STEP 6: HEADERS (add+remove) on Global/CONFIG+Every Methods Level
        // ------------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['headers'])) {
            $this->compiled['config']['headers'] =  $this->validBatches['config']['headers'];
        }
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'] as $method) {
            if (isset($this->validBatches['methods'][$method]['headers'])) {
                $this->compiled['methods'][$method]['headers'] = $this->validBatches['methods'][$method]['headers'];
            }
        }
        // ------------------------------------------------------------------------------------------------
        // STEP 7: SetCSP & setNonces on Global/CONFIG+Every Methods Level
        // ------------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['csp'])) {
            $this->compiled['config']['csp'] =  $this->validBatches['config']['csp'];
        }
        if (isset($this->validBatches['config']['nonces'])) {
            $this->compiled['config']['nonces'] =  $this->validBatches['config']['nonces'];
        }
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'] as $method) {
            if (isset($this->validBatches['methods'][$method]['csp'])) {
                $this->compiled['methods'][$method]['csp'] = $this->validBatches['methods'][$method]['csp'];
            }
            if (isset($this->validBatches['methods'][$method]['nonces'])) {
                $this->compiled['methods'][$method]['nonces'] = $this->validBatches['methods'][$method]['nonces'];
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 8: Build pipes() for `request` & `post_response` and also check if request is empty
        // and the same with `post_response` and/or if they conflict with DEFAULT_HTTPS_KERNEL and/or
        // any registered DEFAULT_REGISTER_SHUTDOWN_HANDLER which conflicts with post_response pipes.
        // 8.1 Request Pipes
        // ------------------------------------------------------------------------------------------
        if (!isset($this->validBatches['config']['request'])) {
            if (!isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
                $this->compile_setWarn("No Request Pipes (via `->pipeRequestFunction() in ->CONFIG()` detected. If intended to use No Request Pipes, just ignore this warning. This means that only Global-based Middlewares, then Route-matching, then Method-based Middleware and finally Route-based Middleware and its remaining pipes will run.", $this->compileWarnings);
            } else {
                $this->compile_setWarn("No Request Pipes (via `->pipeRequestFunction() in ->CONFIG()` detected. If intended to use No Request Pipes, just ignore this warning. The `User-defined Custom Default HTTPS Kernel Handler` is configured for use meaning that after Successful Compilation it will have access to Trie-based Routes with Metadata and then it is `all up to that User-defined Function to handle everything` from Route-matching to executing each Route-associated Pipe Function(s).", $this->compileWarnings);
            }
        }
        // request pipes exist
        else {
            if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
                $this->compile_setWarn("Request Pipes (via `->pipeRequestFunction() in ->CONFIG()` and the `User-defined Custom Default HTTPS Kernel Handler` detected. This means that that after Successful Compilation it will have access to Trie-based Routes with Metadata and then it is `all up to that User-defined Function to handle everything AFTER Request Pipe Functions first have ran`; everything from Route-matching to executing each Route-associated Pipe Function(s).", $this->compileWarnings);
            }
            // VALIDATE "group:" Variants and then ADD REQUEST PIPES
            $allPipes = [];
            foreach ($this->validBatches['config']['request'] as $pipe) {
                if (!str_starts_with($pipe, 'group:')) {
                    if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $pipe) {
                        $this->compile_setWarn("`Consecutive GLOBAL Pipe Request Function '{$pipe}' found`. Ignore this warning if it is intentional or Review `->CONFIG()->pipeRequestFunction()` in `/src/funkphp/app/CONFIG.php`.", $this->compileWarnings);
                    }
                    $allPipes[] = $pipe;
                    continue;
                }
                if (!isset($GLOBAL_GROUPED['REQUEST'][$pipe])) {
                    $this->compile_setErr("Grouped GLOBAL Request Pipe Functions with the name `{$pipe}` does not exist but was still part of the `->CONFIG()->pipeRequestFunction('{$pipe}')` in `/src/funkphp/app/CONFIG.php`. Use `->setGroupPipeRequest('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.", $this->compileErrors);
                } else {
                    foreach ($GLOBAL_GROUPED['REQUEST'][$pipe] as $groupPipe) {
                        if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                            $this->compile_setWarn("`Consecutive GLOBAL Pipe Request Function '{$groupPipe}' found`. Ignore this warning if it is intentional or Review `->CONFIG()->pipeRequestFunction()` in `/src/funkphp/app/CONFIG.php`.", $this->compileWarnings);
                        }
                        $allPipes[] = $groupPipe;
                    }
                }
            }
            $this->compiled['config']['pipes']['request'] =  $allPipes;
        }
        // ------------------------------------------------------------------------------------------
        // 8.2 Middlewares Globally (runs AFTER a matched method+route only?)
        // ------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['middlewares'])) {
            // VALIDATE "group:" Variants and then ADD MIDDLEWARES PIPES (global first then method level)
            $allPipes = [];
            foreach ($this->validBatches['config']['middlewares'] as $pipe) {
                if (!str_starts_with($pipe, 'group:')) {
                    if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $pipe) {
                        $this->compile_setWarn("`Consecutive GLOBAL Pipe Middleware Function '{$pipe}' found`. Ignore this warning if it is intentional or Review `->CONFIG()->pipeMiddleware()` in `/src/funkphp/app/CONFIG.php`.", $this->compileWarnings);
                    }
                    $allPipes[] = $pipe;
                    continue;
                }
                if (!isset($GLOBAL_GROUPED['MIDDLEWARES'][$pipe])) {
                    $this->compile_setErr("Grouped GLOBAL Middleware Pipe Functions with the name `{$pipe}` does not exist but was still part of the `->CONFIG()->pipeMiddleware('{$pipe}')` in `/src/funkphp/app/CONFIG.php`. Use `->setGroupPipeMiddlewares('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.", $this->compileErrors);
                } else {
                    foreach ($GLOBAL_GROUPED['MIDDLEWARES'][$pipe] as $groupPipe) {
                        if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                            $this->compile_setWarn("`Consecutive GLOBAL Pipe Middleware Function '{$groupPipe}' found`. Ignore this warning if it is intentional or Review `->CONFIG()->pipeMiddleware()` in `/src/funkphp/app/CONFIG.php`.", $this->compileWarnings);
                        }
                        $allPipes[] = $groupPipe;
                        // As MWs are unpacked, add then them to global-based MW Invert Index
                        if (!isset($this->cached['placeholderMiddlewareInvertIindex'][$groupPipe])) {
                            $this->cached['placeholderMiddlewareInvertIindex'][$groupPipe][] = 'GLOBAL';
                        } else {
                            if (!in_array('GLOBAL', $this->cached['placeholderMiddlewareInvertIindex'][$groupPipe])) {
                                $this->cached['placeholderMiddlewareInvertIindex'][$groupPipe][] = 'GLOBAL';
                            }
                        }
                    }
                }
            }
            $this->compiled['config']['pipes']['middlewares'] =  $allPipes;
        }
        // ------------------------------------------------------------------------------------------
        // 8.3 Post-Response Pipes
        // ------------------------------------------------------------------------------------------
        if (!isset($this->validBatches['config']['post_response'])) {
            $this->compile_setWarn("No Post-Response Pipes (via `->pipePostResponseFunction() in ->CONFIG()` detected. If intended to use No Post-Response Pipes, just ignore this warning. This means that after each HTTP(S) Request that completes (or via `exit()`), nothing else happens. `Piped Post-Response Functions` are otherwise executed via the in-built PHP Function `register_shutdown_function()` in the ordered they have been added/piped. This is also why you will get a Fatal Compiling Error if you try to use the `register_shutdown_function()` inside any of your Function Files.", $this->compileWarnings);
        }
        // post_response pipes exist
        else {
            // VALIDATE "group:" Variants and then ADD POST-RESPONSE PIPES
            // VALIDATE "group:" Variants and then ADD REQUEST PIPES
            $allPipes = [];
            foreach ($this->validBatches['config']['post_response'] as $pipe) {
                if (!str_starts_with($pipe, 'group:')) {
                    if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $pipe) {
                        $this->compile_setWarn("`Consecutive GLOBAL Pipe Post-Response Function '{$pipe}' found`. Ignore this warning if it is intentional or Review `->CONFIG()->pipePostResponseFunction()` in `/src/funkphp/app/CONFIG.php`.", $this->compileWarnings);
                    }
                    $allPipes[] = $pipe;
                    continue;
                }
                if (!isset($GLOBAL_GROUPED['POST_RESPONSE'][$pipe])) {
                    $this->compile_setErr("Grouped GLOBAL Post-Response Pipe Functions with the name `{$pipe}` does not exist but was still part of the `->CONFIG()->pipePostResponseFunction('{$pipe}')` in `/src/funkphp/app/CONFIG.php`. Use `->setGroupPipePostResponse('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.", $this->compileErrors);
                } else {
                    foreach ($GLOBAL_GROUPED['POST_RESPONSE'][$pipe] as $groupPipe) {
                        if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                            $this->compile_setWarn("`Consecutive GLOBAL Pipe Post-Response Function '{$groupPipe}' found`. Ignore this warning if it is intentional or Review `->CONFIG()->pipePostResponseFunction()` in `/src/funkphp/app/CONFIG.php`.", $this->compileWarnings);
                        }
                        $allPipes[] = $groupPipe;
                    }
                }
            }
            $this->compiled['config']['pipes']['post_response'] =  $allPipes;
        }
        // ------------------------------------------------------------------------------------------
        // STEP 9: Build `middlewares` for all <METHODS> - same checks as global config()
        // ------------------------------------------------------------------------------------------
        if (isset($this->validBatches['methods'])) {
            foreach ($this->validBatches['methods'] as $method => $methodConfig) {
                if (isset($methodConfig['middlewares'])) {
                    // VALIDATE "group:" Variants and then ADD MIDDLEWARES PIPES (global first then method level)
                    $allPipes = [];
                    foreach ($methodConfig['middlewares'] as $pipe) {
                        if (!str_starts_with($pipe, 'group:')) {
                            if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $pipe) {
                                $this->compile_setWarn("`Consecutive {$method} Pipe Middleware Function '{$pipe}' found`. Ignore this warning if it is intentional or Review `->ROUTES()->{$method}()->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.", $this->compileWarnings);
                            }
                            $allPipes[] = $pipe;
                            continue;
                        }
                        if (!isset($GLOBAL_GROUPED['MIDDLEWARES'][$pipe])) {
                            $this->compile_setErr("Grouped Middleware {$method} Pipe Functions with the name `{$pipe}` does not exist but was still part of the `->ROUTES()->{$method}()->pipeMiddleware('{$pipe}')` in `/src/funkphp/app/{$method}.php`. Use `->setGroupPipeMiddlewares('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.", $this->compileErrors);
                        } else {
                            foreach ($GLOBAL_GROUPED['MIDDLEWARES'][$pipe] as $groupPipe) {
                                if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                                    $this->compile_setWarn("`Consecutive {$method} Pipe Middleware Function '{$groupPipe}' found`. Ignore this warning if it is intentional or Review `->ROUTES()->{$method}()->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.", $this->compileWarnings);
                                }
                                $allPipes[] = $groupPipe;
                                // As MWs are unpacked, add then them to method-based MW Invert Index
                                if (!isset($this->cached['placeholderMiddlewareInvertIindex'][$groupPipe])) {
                                    $this->cached['placeholderMiddlewareInvertIindex'][$groupPipe][] = $method;
                                } else {
                                    if (!in_array($method, $this->cached['placeholderMiddlewareInvertIindex'][$groupPipe])) {
                                        $this->cached['placeholderMiddlewareInvertIindex'][$groupPipe][] = $method;
                                    }
                                }
                            }
                        }
                    }
                    // Last MW Globally same as First MW in Method? That is another Consecutive version!
                    if (
                        count($allPipes) > 0 &&
                        isset($this->compiled['config']['pipes']['middlewares'])
                        && count($this->compiled['config']['pipes']['middlewares']) > 0
                    ) {
                        $lastConfigMW = $this->compiled['config']['pipes']['middlewares'][count($this->compiled['config']['pipes']['middlewares']) - 1];
                        if ($allPipes[0] === $lastConfigMW) {
                            $this->compile_setWarn("`Consecutive Pipe Middleware Function '{$allPipes[0]}' found`. It runs as `Last Middleware Globally` and then it runs as the `First {$method} Middleware` for any Matched Route in `{$method}`. Ignore this warning if it is intentional or Review: `->CONFIG()->pipeMiddleware('{$lastConfigMW}')` in `/src/funkphp/app/CONFIG.php` AND `->ROUTES()->{$method}()->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.", $this->compileWarnings);
                        }
                    }
                    $this->compiled['methods'][$method]['middlewares'] = $allPipes;
                }
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 10: Build `params` for GLOBAL & for all <METHODS>
        // ------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['paramRules'])) {
            $this->compiled['config']['params'] = $this->validBatches['config']['paramRules'];
        }
        if (isset($this->validBatches['methods'])) {
            foreach ($this->validBatches['methods'] as $method => $methodConfig) {
                if (isset($methodConfig['paramRules'])) {
                    $this->compiled['methods'][$method]['params'] = $methodConfig['paramRules'];
                }
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 11: Build `routes` - (unpacking) middlewares, headers|csp|nonces|exclusions and pipes
        // ------------------------------------------------------------------------------------------
        // STEP 11.1: Build `routes` - Check if routes exist or not and output error if not?
        // or should it be allowed to NOT have any routes just as a "soft success"?
        // No Routes?
        if (!isset($this->validBatches['routes']) || count($this->validBatches['routes']) === 0) {
            //$this->compile_setWarn("`No Routes Configured`. This means ", $this->compileWarnings);
            $this->compile_welcome_splash();
        }
        // Routes exist!
        else {
            // STEP 11.2: Build `routes` - Iterate through each Method and their Single Routes
            foreach ($this->validBatches['routes'] as $method => $methodRoutes) {
                foreach ($methodRoutes as $route => $routeDetails) {
                    // STEP 11.3.0: Add the current Route to the Trie - this is only
                    // when compiling and running it as the deployed build would
                    // have flattened route matching instead.
                    $this->compile_add_to_route_trie($method, $route);

                    // STEP 11.3: Build `routes` - unpack all "group:" in Middlewares & Pipes first
                    // and add them to the $GLOBAL_GROUPED Array
                    $CURRENT_ROUTE_STR = "`'{$method}{$route}`";
                    // FORBIDDEN EDGE-CASE: 0 Pipes but at least 1 MW for the Route.
                    if (
                        isset($routeDetails['pipes'])
                        && count($routeDetails['pipes']) === 0
                        && isset($routeDetails['middlewares'])
                        && count($routeDetails['middlewares']) > 0
                    ) {
                        $this->compile_setErr("`Only Middlewares` in Route {$CURRENT_ROUTE_STR} but `No Pipes`. You need `at least one Pipe Function` for the Route {$CURRENT_ROUTE_STR}.", $this->compileErrors);
                        continue;
                    }
                    if (!isset($routeDetails['pipes']) || count($routeDetails['pipes']) === 0) {
                        $this->compile_setWarn("No Pipes for Route {$CURRENT_ROUTE_STR}'.", $this->compileWarnings);
                    }
                    if (!isset($routeDetails['middlewares']) || count($routeDetails['middlewares']) === 0) {
                        $this->compile_setWarn("No Middlewares for Route {$CURRENT_ROUTE_STR}'.", $this->compileWarnings);
                    }

                    // STEP 11.4: Iterate through Route Middlewares and warn about consecutive runs that
                    // could stem from inherting last MW from Method that is same as first MW in
                    // route. Also, then check setMiddlewaresToExclude() that all those exist down
                    // the Method+Globally or set Error if not. Do same for setHeadersToExclude()

                    // STEP 11.5: Iterate through Route Pipes and check for consecutive FN piping

                    // STEP 11.6 Add to the $this->COMPILED['routes'][$method][$route] with the 'all' part!
                }
            }

            // STEP 11.4: Build `routes` -

            // STEP 11.5: Build `routes` -

            // STEP 11.6: Build `routes` -
        }



        /////////////////////////////////////// END /////////////////////////////
        //$this->compile_setErr("", $this->compileErrors);
        if (
            isset($this->debug['SHOW_MAIN_CONFIG'])
            && !$this->debug['SHOW_MAIN_CONFIG']
        ) {
            $this->FunkPHPFluentAPI['CONFIG'] = '(' . (count($this->FunkPHPFluentAPI['CONFIG'])) . ' Configuration' . (count($this->FunkPHPFluentAPI['CONFIG']) > 1 ? 's' : '') . ') - Show all Configuration by setting third Boolean in `CONFIG()->setDebug()` in `/src/funkphp/app/CONFIG.php` to `true`.';
        }
        if (count($this->compileErrors) > 0 || $this->debug['ALWAYS_SHOW']) {
            $this->output_errors($this->errors, $this->compileErrors, $this->compileWarnings);
        }

        dd(['COMPILE FLAGS' => $this->compileFlags, 'API' => $this->FunkPHPFluentAPI, 'COMPILE_ERRORS' => $compileErrors, 'COMPILE_WARNINGS' => $this->compileWarnings, 'COMPILED' => $this->compiled, 'VALID' => $this->validBatches,  'CACHED' => $this->cached], "COMPILATION - DEBUG", true);
    }
    private function run()
    {
        // Run the valid compiled FunkPHP
    }
}
/**
 * Class FunkPHP
 *
 * The Entry Point for FunkPHP Fluent API.
 */
class FunkPHP
{
    public function __construct(private C $c) {}
    /**
     * Access global framework configuration settings.
     *
     * @return FunkConfig
     */
    public function CONFIG(): FunkConfig
    {
        return $this->c->config();
    }
    /**
     * Access HTTP route definition builders.
     *
     * @return FunkRoutes
     */
    public function ROUTES(): FunkRoutes
    {
        return $this->c->routes();
    }
}
/*
 * Class FunkConfig() - accessed via FunkPHP()->config() - contains
 * Can jump to ->routes() | This is also known as "global"
*/
class FunkConfig
{
    public function __construct(private C $c) {}
    /**
     * FLUENT METHOD VISUAL COMMENT DIVIDER (HAS NO LOGICAL, BUT MAYBE PRACTICAL EFFECT)
     *
     * USE: `_('GLOBAL HANDLERS')` or `_('ROUTES FOR BLABLA')`
     *
     * @param string ...$comment Optional Visual Label
     *
     * IMPORTANT: it is IGNORED during Compilation & Runtime.
     * @return $this
     */
    public function _(string ...$comment): self
    {
        return $this;
    }
    /**
     * ARBITRARY SPACE BETWEEN CHAINED METHODS (HAS NO LOGICAL, BUT MAYBE PRACTICAL EFFECT)
     *
     * @return $this
     */
    public function ______________________________________________(): self
    {
        return $this;
    }

    /**
     * Set a compilation engine flag to control code generation rules.
     *
     * @param string $flag Compiler flag (e.g., "NO_WARNINGS_ALLOWED")
     * @return $this
     */
    public function setCompileFlag(string $flag): self
    {
        $flag = strtoupper(trim($flag));
        $this->c->batch('batchSetCompileFlag', $flag);
        return $this;
    }
    /**
     * FunkPHP Debug Mode (default is to enable it and always show it, even if zero errors)
     *
     * Debug Internal FunkPHP Configuration State during development|testing. This feature is automatically
     * disabled during compilation. Debug to show Fluent API trail, Errors, Warnings, and in-built variables:
     * `$validBatches`, `$invalidBatches`, `$cached`, and `$compiled`.
     *
     * @param bool $ON_OR_OFF            Enable|disable debugging globally (default: true).
     * @param bool $ALWAYS_SHOW          Enable|disable show debug even if zero errors (default: true).
     * @param bool $SHOW_ALL             Dump all diagnostic targets (`validBatches`, `invalidBatches`, `cached`, `compiled`).
     * @param bool $SHOW_MAIN_CONFIG     Dump `API => CONFIG` or not. Default is `true`. Might get annoying when it is all configured.
     * @param bool $SHOW_VALID_BATCHES   Dump `$validBatches` (staged routes, methods, and config options).
     * @param bool $SHOW_INVALID_BATCHES Dump `$invalidBatches` (rejected configuration calls).
     * @param bool $SHOW_CACHED          Dump `$cached` (parsed files, metadata, placeholders, etc.,).
     * @param bool $SHOW_COMPILED        Dump the final compiled execution matrix generated by `compile()`.
     */
    public function setDebug(bool $ON_OR_OFF = true, bool $ALWAYS_SHOW = true, bool $SHOW_ALL = false, bool $SHOW_MAIN_CONFIG = true, bool $SHOW_VALID_BATCHES = false, bool $SHOW_INVALID_BATCHES = false, bool $SHOW_CACHED = false, bool $SHOW_COMPILED = false): self
    {
        $this->c->batch('batchSetDebug', $ON_OR_OFF, $ALWAYS_SHOW, $SHOW_ALL, $SHOW_VALID_BATCHES, $SHOW_INVALID_BATCHES, $SHOW_CACHED, $SHOW_COMPILED);
        return $this;
    }

    /* setGroup<VARIANTS> - use the prefix "group:<$groupName>"
    // to faster more pipes at the same time! - GLOBAL */
    /**
     * Set a Group of User-defined Functions (in `/src/funkphp/config/functions.php`)
     *
     * After you have set this Group of User-defined Functions, you can refer to them via `group:<groupName>`
     * when using `pipeFunction()` OR `pipeMiddleware()` where applicable: 1) Global Middlewares, 2) Method Middlewares,
     * 3) piped Function(s) in Single Route(s). User-defined Functions already set as Default Global Handlers are not allowed.
     *
     * @param string $groupName The name of the Grouped User-defined Functions
     * @param string ...$pipeUserDefinedFNs Name of each Single User-defined Function in `/src/funkphp/config/functions.php`
     */
    public function setGroupPipeUserdefined(string $groupName, string ...$pipeUserDefinedFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeUserDefined', $groupName, ...$pipeUserDefinedFNs);
        return $this;
    }

    /**
     * Group multiple request pipeline function names under a single reference key.
     *
     * @param string $groupName Group identifier key
     * @param string ...$pipeRequestFNs Function names to assign to this group
     * @return $this
     */
    public function setGroupPipeRequest(string $groupName, string ...$pipeRequestFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeRequest', $groupName, ...$pipeRequestFNs);
        return $this;
    }

    /**
     * Group multiple post-response pipeline function names under a single reference key.
     *
     * @param string $groupName Group identifier key
     * @param string ...$pipePostReponseFNs Function names to assign to this group
     * @return $this
     */
    public function setGroupPipePostResponse(string $groupName, string ...$pipePostReponseFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipePostResponse', $groupName, ...$pipePostReponseFNs);
        return $this;
    }

    /**
     * Group multiple route handler function names under a single reference key.
     *
     * @param string $groupName Group identifier key
     * @param string ...$pipeRouteFNs Function names to assign to this group
     * @return $this
     */
    public function setGroupPipeRoute(string $groupName, string ...$pipeRouteFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeRoute', $groupName, ...$pipeRouteFNs);
        return $this;
    }

    /**
     * Group multiple middleware function names under a single reference key.
     *
     * @param string $groupName Group identifier key
     * @param string ...$pipeMiddlewareFNs Function names to assign to this group
     * @return $this
     */
    public function setGroupPipeMiddlewares(string $groupName, string ...$pipeMiddlewareFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeMiddlewares', $groupName, ...$pipeMiddlewareFNs);
        return $this;
    }

    /**
     * Apply runtime php.ini configuration settings globally.
     *
     * @param array<string, scalar> $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue Key-value pairs of ini_set() calls
     * @return $this
     */
    public function setINI_SET(array $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue): self
    {
        $this->c->batch('batchSetINI_SETGlobal', $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue);
        return $this;
    }

    /**
     * Configures Content-Security-Policy (CSP) directives Globally (in `/src/funkphp/app/CONFIG.php`).
     *
     * Automatically wraps standard CSP keywords (e.g. 'self', 'none', 'unsafe-inline') in single quotes,
     * while preserving casing for hashes, nonces, and domains.
     *
     * @param 'require-trusted-types-for'|'trusted-types'|'default-src'|'script-src'|'script-src-elem'|'script-src-attr'|'style-src'|'style-src-elem'|'style-src-attr'|'img-src'|'font-src'|'connect-src'|'media-src'|'object-src'|'child-src'|'frame-src'|'worker-src'|'manifest-src'|'prefetch-src'|'base-uri'|'form-action'|'frame-ancestors'|'sandbox'|'plugin-types'|'navigate-to'|'report-uri'|'report-to' $sourceType
     * The CSP directive name. Supported values:
     * - `default-src`      : Fallback for other fetch directives.
     * - `script-src`       : JavaScript execution sources.
     * - `script-src-elem`  : Valid sources for `<script>` elements.
     * - `script-src-attr`  : Valid sources for inline event handlers (e.g. onclick).
     * - `style-src`        : Stylesheet and CSS sources.
     * - `style-src-elem`   : Valid sources for `<style>` and `<link rel="stylesheet">`.
     * - `style-src-attr`   : Valid sources for inline `style="..."` attributes.
     * - `img-src`          : Images and favicons.
     * - `font-src`         : Web fonts.
     * - `connect-src`      : Fetch, XMLHttpRequest, WebSocket, and EventSource targets.
     * - `media-src`        : Audio and video `<audio>`, `<video>`.
     * - `object-src`       : Plugins like Flash or PDF viewers (`<object>`, `<embed>`).
     * - `child-src`        : Web workers and nested frame contexts.
     * - `frame-src`        : Valid sources for `<iframe>` and `<frame>`.
     * - `worker-src`       : Valid sources for Worker, SharedWorker, or ServiceWorker.
     * - `manifest-src`     : Web App Manifest files.
     * - `prefetch-src`     : Resources to be prefetched or prerendered.
     * - `base-uri`         : Restricted URLs for the `<base>` element.
     * - `form-action`      : Valid target URLs for `<form>` submissions.
     * - `frame-ancestors`  : Valid parents that may embed this page in an `<iframe>`.
     * - `sandbox`         : Enables sandbox restrictions for the requested resource. Available flags:`allow-forms allow-same-origin allow-scripts allow-popups, allow-modals, allow-orientation-lock, allow-pointer-lock, allow-presentation, allow-popups-to-escape-sandbox, and allow-top-navigation`
     * - `report-uri`      : Endpoint URL where CSP violation reports are sent (Deprecated).
     * - `report-to`       : Reporting API group name for CSP violations.
     *
     * @param string ...$sources One or more sources (e.g. `'self'`, `'https://cdn.example.com'`, `'sha256-...'`).
     *
     * @example
     * FunkPHP()->config()->setCSP('script-src', 'self', 'https://cdn.jsdelivr.net');
     * FunkPHP()->config()->setCSP('object-src', 'none');
     *
     * @return $this
     */
    public function setCSP(string $sourceType, string ...$sources): self
    {
        $sourceType = strtolower(trim($sourceType));
        $this->c->batch('batchSetCSPGlobal', $sourceType, ...$sources);
        return $this;
    }

    /**
     * Define Subresource Integrity (SRI) hashes for internal assets.
     *
     * @param array<string, string> $internalSRI Asset paths mapped to SRI hashes
     * @return $this
     */
    public function setSRIInternal(array $internalSRI): self
    {
        $this->c->batch('batchSetSRIInternalGlobal', $internalSRI);
        return $this;
    }

    /**
     * Define Subresource Integrity (SRI) hashes and options for external scripts/styles.
     *
     * @param array<string, mixed> $options SRI configuration and hash map
     * @return $this
     */
    public function setSRIExternal(array $options): self
    {
        $this->c->batch('batchSetSRIExternalGlobal', $options);
        return $this;
    }

    /**
     * Render a template page as the global fallback when no route matches.
     *
     * @param string $PageFileName Template filename or path
     * @param int $statusCode HTTP status code (default: 404)
     * @return $this
     */
    public function setNoRouteMatchPage(string $PageFileName, int $statusCode = 404): self
    {
        $PageFileName = strtolower(trim($PageFileName));
        $this->c->batch('batchSetNoRouteMatchPageGlobal', $PageFileName, $statusCode);
        return $this;
    }

    /**
     * Return a JSON payload as the global fallback when no route matches.
     *
     * @param array<mixed>|object $data JSON payload structure
     * @param int $statusCode HTTP status code (default: 404)
     * @return $this
     */
    public function setNoRouteMatchJSON(array|object $data, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchJsonGlobal', $data, $statusCode);
        return $this;
    }

    /**
     * Return plain text as the global fallback when no route matches.
     *
     * @param string $message Response message text
     * @param int $statusCode HTTP status code (default: 404)
     * @return $this
     */
    public function setNoRouteMatchText(string $message, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchTextGlobal', $message, $statusCode);
        return $this;
    }

    /**
     * Register a callback function as the global fallback handler when no route matches.
     *
     * @param callable|string $userDefinedFunctionName Callback function name or callable
     * @return $this
     */
    public function setNoRouteMatchCallback(callable|string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetNoRouteMatchCallbackGlobal', $userDefinedFunctionName);
        return $this;
    }

    /**
     * Set the globaluncaught exception handler callback function.
     *
     * @param string $userDefinedFunctionName Name of the user-defined exception handler function
     * @return $this
     */
    public function setDefaultExceptionHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultExceptionHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }

    /**
     * Set the global PHP error handler callback function.
     *
     * @param string $userDefinedFunctionName Name of the user-defined error handler function
     * @return $this
     */
    public function setDefaultErrorHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultErrorHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }

    /**
     * Set the global URI normalizer handler callback function.
     *
     * @param string $userDefinedFunctionName Name of the URI normalization function
     * @return $this
     */
    public function setDefaultURI_NormalizerHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultURINormalizerGlobal', $userDefinedFunctionName);
        return $this;
    }

    /**
     * Set the primary HTTP kernel dispatch handler callback function.
     *
     * @param string $userDefinedFunctionName Name of the kernel dispatch handler function
     * @return $this
     */
    public function setDefaultKernelHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultHTTPSKernelDispatchHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }

    /**
     * Set the base URL for local development environments.
     *
     * @param string $httpsPath Full local URL (e.g., "http://WKF.com")
     * @return $this
     */
    public function setBaseURLLocal(string $httpsPath): self
    {
        $this->c->batch('batchSetDefaultBaseURLLocalGlobal', $httpsPath);
        return $this;
    }

    /**
     * Set the base URL for production/online deployment.
     *
     * @param string $httpsPath Full production URL (e.g., "https://www.FunkPHP.com")
     * @return $this
     */
    public function setBaseURLOnline(string $httpsPath): self
    {
        $this->c->batch('batchSetDefaultBaseURLOnlineGlobal', $httpsPath);
        return $this;
    }

    /**
     * Set the target host name string used to detect local development environment.
     *
     * @param string $hostNameLocally Local hostname indicator (e.g., "wkf" or "localhost")
     * @return $this
     */
    public function setBaseURLHost(string $hostNameLocally): self
    {
        $this->c->batch('batchSetDefaultBaseURLHostGlobal', $hostNameLocally);
        return $this;
    }

    /**
     * Set the base sub-folder or sub-path prefix for local development URLs.
     *
     * @param string $localURI Sub-path URI prefix (e.g., "/funkphp")
     * @return $this
     */
    public function setBaseURLUri(string $localURI): self
    {
        $this->c->batch('batchSetDefaultBaseURLUriGlobal', $localURI);
        return $this;
    }

    /**
     * Set Default Session Cookie Driver
     *
     * Choose between 'files', 'redis', 'memcached', 'database', 'array'
     *
     * @param 'files'|'redis'|'memcached'|'database'|'array' $filesOrRedisOrSomethingElse
     */
    public function setSessionDriver(string $filesOrRedisOrSomethingElse = 'files'): self
    {
        $filesOrRedisOrSomethingElse = strtolower(trim($filesOrRedisOrSomethingElse));
        $this->c->batch('batchSetDefaultSessionDriverGlobal', $filesOrRedisOrSomethingElse);
        return $this;
    }

    /**
     * Set session cookie options in bulk using an associative array.
     *
     * @param array{
     *     SESSION_DRIVER?: string,
     *     SESSION_NAME?: string,
     *     SESSION_LIFETIME?: int,
     *     SESSION_PATH?: string,
     *     SESSION_DOMAIN?: string,
     *     SESSION_SECURE?: bool,
     *     SESSION_HTTPONLY?: bool,
     *     SESSION_SAMESITE?: string
     * } $sessionCookieOptions
     * @return $this
     */
    public function setSessionCookieOptions(array $sessionCookieOptions): self
    {
        $this->c->batch('batchSetDefaultSessionCookieOptionsGlobal', $sessionCookieOptions);
        return $this;
    }

    /**
     * Set the global session cookie name.
     *
     * @param string $sessionCookieName Default is 'fphp_id'
     * @return $this
     */
    public function setSessionCookieName(string $sessionCookieName = 'fphp_id'): self
    {
        $this->c->batch('batchSetDefaultSessionCookieNameGlobal', $sessionCookieName);
        return $this;
    }

    /**
     * Set the global session cookie lifetime in seconds.
     *
     * @param int $sessionCookieLifetime Lifetime in seconds (default: 28800 = 8 hours)
     * @return $this
     */
    public function setSessionCookieLifetime(int $sessionCookieLifetime = 28800): self
    {
        $this->c->batch('batchSetDefaultSessionCookieLifetimeGlobal', $sessionCookieLifetime);
        return $this;
    }

    /**
     * Set the session cookie path scope.
     *
     * @param string $sessionCookiePath Default is '/'
     * @return $this
     */
    public function setSessionCookiePath(string $sessionCookiePath = '/'): self
    {
        $this->c->batch('batchSetDefaultSessionCookiePathGlobal', $sessionCookiePath);
        return $this;
    }

    /**
     * Set the domain scope for session cookies.
     *
     * @param string $sessionCookieDomain Target domain (default: 'webdev.local')
     * @return $this
     */
    public function setSessionCookieDomain(string $sessionCookieDomain = 'webdev.local'): self
    {
        $sessionCookieDomain = strtolower(trim($sessionCookieDomain));
        $this->c->batch('batchSetDefaultSessionCookieDomainGlobal', $sessionCookieDomain);
        return $this;
    }

    /**
     * Set Default Session Cookie HTTP Secure Boolean
     *
     * Choose between 'true' OR 'false'
     *
     * @param bool $trueOrFalse
     */
    public function setSessionCookieSecure(bool $trueOrFalse = false): self
    {
        $this->c->batch('batchSetDefaultSessionCookieSecureGlobal', $trueOrFalse);
        return $this;
    }
    /**
     * Set Default Session Cookie HTTPOnly Boolean
     *
     * Choose between 'true' OR 'false'
     *
     * @param bool $trueOrFalse
     */
    public function setSessionCookieHTTPOnly(bool $trueOrFalse = true): self
    {
        $this->c->batch('batchSetDefaultSessionCookieHTTPOnlyGlobal', $trueOrFalse);
        return $this;
    }
    /**
     * Set Default SameSite Value for Session Cookie
     *
     * Choose between 'Lax','Strict' OR 'None'.
     *
     * @param 'Lax'|'Strict'|'None' $LaxOrStrict
     */
    public function setSessionCookieSameSite(string $LaxOrStrict = 'Lax'): self
    {
        $LaxOrStrict = ucfirst(strtolower(trim($LaxOrStrict)));
        $this->c->batch('batchSetDefaultSessionCookieSameSiteGlobal', $LaxOrStrict);
        return $this;
    }

    /* set<VARIANTS> that are ONLY Boolean - GLOBAL */
    /**
     * Set if FunkPHP should act as if online. `IMPORTANT: This one
     * might not be needed so might be removed in the future. It is
     * right now just a placeholder here due to legacy purposes`.
     *
     * Choose between 'true' OR 'false'
     *
     * @param bool $trueOrFalse
     */
    public function setUseFunkPHPOnline(bool $trueOrFalse): self
    {
        $this->c->batch('batchSetFunkPHPOnlineGlobal', $trueOrFalse);
        return $this;
    }
    /**
     * Set if FunkPHP should use HTTPS meaning it will also check
     * that HTTPS Secure Cookies are being used and it will also
     * upgrade Non-Http visits to HTTPS versions via `header("Location: ")`.
     *
     * Choose between 'true' OR 'false'
     *
     * @param bool $trueOrFalse
     */
    public function setUseHTTPS(bool $trueOrFalse): self
    {
        $this->c->batch('batchSetUseHTTPSGlobal', $trueOrFalse);
        return $this;
    }
    /**
     * Set if FunkPHP should use Vendor/Composer Files (in `/src/funkphp/vendor`) or not. If set to TRUE then it will include the autoloading part of vendors which
     * happens AFTER the FunkPHP.php|FunkPHPDeployment.php file have been loaded.
     *
     * Choose between 'true' OR 'false'
     *
     * @param bool $trueOrFalse
     */
    public function setUseVendor(bool $trueOrFalse): self
    {
        $this->c->batch('batchSetUseVendorGlobal', $trueOrFalse);
        return $this;
    }

    /**
     * Define a global parameter validation regex rule applied across all routes.
     *
     * @param string $param Parameter name without leading colon (e.g., "id")
     * @param string $regex Regex pattern (e.g., "/[\d]+/")
     * @param string|null $defaultParamValueOnRegexMismatch Fallback value if validation fails
     * @return $this
     */
    public function setParamRule(string $param, string $regex, $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleGlobal', $param, $regex, $defaultParamValueOnRegexMismatch);
        return $this;
    }

    /**
     * Set a response Header that is applied globally (can be overwritten with a setHeader on a given Method AND on a given Route).
     *
     * @param 'Accept-Ranges'|'Access-Control-Allow-Credentials'|'Access-Control-Allow-Headers'|'Access-Control-Allow-Methods'|'Access-Control-Allow-Origin'|'Access-Control-Expose-Headers'|'Access-Control-Max-Age'|'Age'|'Allow'|'Alt-Svc'|'Cache-Control'|'Clear-Site-Data'|'Content-Disposition'|'Content-Encoding'|'Content-Language'|'Content-Location'|'Content-Range'|'Content-Security-Policy'|'Content-Security-Policy-Report-Only'|'Content-Type'|'Cross-Origin-Embedder-Policy'|'Cross-Origin-Opener-Policy'|'Cross-Origin-Resource-Policy'|'ETag'|'Expires'|'Last-Modified'|'Location'|'Origin-Trial'|'Permissions-Policy'|'Pragma'|'Referrer-Policy'|'Retry-After'|'Server-Timing'|'Strict-Transport-Security'|'Timing-Allow-Origin'|'Vary'|'WWW-Authenticate'|'X-Content-Type-Options'|'X-Frame-Options'|'X-RateLimit-Limit'|'X-RateLimit-Remaining'|'X-RateLimit-Reset'|'X-Request-ID'|'X-XSS-Protection'|string $header Header Name
     * @param string $value Header Value (e.g., "nosniff")
     * @return $this
     */
    public function setHeader(string $header, string $value): self
    {
        $this->c->batch('batchSetHeaderGlobal', trim($header), trim($value));
        return $this;
    }

    /**
     * Remove a previously queued global response header.
     *
     * @param string $header_to_remove Case-insensitive header key to remove
     * @return $this
     */
    public function removeHeader(string $header_to_remove): self
    {
        $header_to_remove = strtolower(trim($header_to_remove));
        $this->c->batch('batchRemoveHeaderGlobal', $header_to_remove);
        return $this;
    }

    /**
     * Attach a middleware globally to run across all incoming requests.
     *
     * @param string $middleware Middleware name or reference key
     * @return $this
     */
    public function pipeMiddleware(string $middleware): self
    {
        $middleware = strtolower(trim($middleware));
        $this->c->batch('batchPipeMiddlewareGlobal', $middleware);
        return $this;
    }

    /**
     * Register a global request pipeline function to execute before route handling.
     *
     * @param string $requestFunction Function name or "group:name"
     * @return $this
     */
    public function pipeRequestFunction(string $requestFunction): self
    {
        $requestFunction = strtolower(trim($requestFunction));
        $this->c->batch('batchPipeRequestFunctionGlobal', $requestFunction);
        return $this;
    }

    /**
     * Register a global post-response function to execute after the response is sent.
     *
     * @param string $postResponseFunction Function name or "group:name"
     * @return $this
     */
    public function pipePostResponseFunction(string $postResponseFunction): self
    {
        $postResponseFunction = strtolower(trim($postResponseFunction));
        $this->c->batch('batchPipePostResponseFunctionGlobal', $postResponseFunction);
        return $this;
    }

    /**
     * Switch context directly from configuration to the route definition builder.
     *
     * @return FunkRoutes
     */
    public function ROUTES(): FunkRoutes
    {
        return $this->c->routes();
    }
}
/**
 * Class FunkRoutes
 *
 * @method FunkMethod GET()
 * @method FunkMethod POST()
 * @method FunkMethod PUT()
 * @method FunkMethod PATCH()
 * @method FunkMethod DELETE()
 * @method FunkMethod CONFIG()
 */
class FunkRoutes
{
    private array $methodInstances = [];
    public function __construct(private C $c) {}
    /**
     * Switch or initialize routing context for HEAD requests.
     *
     * @return FunkMethod
     */
    public function HEAD(): FunkMethod
    {
        $this->c->batch("batchSetMETHOD", "HEAD");
        return $this->methodInstances['HEAD'] ??= new FunkMethod($this->c, $this, 'HEAD');
    }
    /**
     * Switch or initialize routing context for GET requests.
     *
     * @return FunkMethod
     */
    public function GET(): FunkMethod
    {
        $this->c->batch("batchSetMETHOD", "GET");
        return $this->methodInstances['GET'] ??= new FunkMethod($this->c, $this, 'GET');
    }
    /**
     * Switch or initialize routing context for POST requests.
     *
     * @return FunkMethod
     */
    public function POST(): FunkMethod
    {
        $this->c->batch("batchSetMETHOD", "POST");
        return $this->methodInstances['POST'] ??= new FunkMethod($this->c, $this, 'POST');
    }
    /**
     * Switch or initialize routing context for PUT requests.
     *
     * @return FunkMethod
     */
    public function PUT(): FunkMethod
    {
        $this->c->batch("batchSetMETHOD", "PUT");
        return $this->methodInstances['PUT'] ??= new FunkMethod($this->c, $this, 'PUT');
    }
    /**
     * Switch or initialize routing context for PATCH requests.
     *
     * @return FunkMethod
     */
    public function PATCH(): FunkMethod
    {
        $this->c->batch("batchSetMETHOD", "PATCH");
        return $this->methodInstances['PATCH'] ??= new FunkMethod($this->c, $this, 'PATCH');
    }
    /**
     * Switch or initialize routing context for DELETE requests.
     *
     * @return FunkMethod
     */
    public function DELETE(): FunkMethod
    {
        $this->c->batch("batchSetMETHOD", "DELETE");
        return $this->methodInstances['DELETE'] ??= new FunkMethod($this->c, $this, 'DELETE');
    }
    /**
     * Jump directly back to the global application configuration context.
     *
     * @return FunkConfig
     */
    public function CONFIG(): FunkConfig
    {
        return $this->c->config();
    }
}
/**
 * Class FunkMethod
 *
 * Manages HTTP method-level routing defaults, nonces, and fallback handlers.
 *
 * @method FunkMethod HEAD()
 * @method FunkMethod GET()
 * @method FunkMethod POST()
 * @method FunkMethod PUT()
 * @method FunkMethod PATCH()
 * @method FunkMethod DELETE()
 */
class FunkMethod
{
    public function __construct(
        private C $c,
        private FunkRoutes $parent,
        private string $method
    ) {}
    /**
     * FLUENT METHOD VISUAL COMMENT DIVIDER (HAS NO LOGICAL, BUT MAYBE PRACTICAL EFFECT)
     *
     * USE: `_('GLOBAL HANDLERS')` or `_('ROUTES FOR BLABLA')`
     *
     * @param string ...$comment Optional Visual Label
     *
     * IMPORTANT: it is IGNORED during Compilation & Runtime.
     * @return $this
     */
    public function _(string ...$comment): self
    {
        return $this;
    }
    /**
     * ARBITRARY SPACE BETWEEN CHAINED METHODS (HAS NO LOGICAL, BUT MAYBE PRACTICAL EFFECT)
     *
     * @return $this
     */
    public function ______________________________________________(): self
    {
        return $this;
    }
    /**
     * Set raw route fallback options for this HTTP method.
     *
     * @param array<string, mixed> $options
     * @return $this
     */
    public function setNoRouteMatch(array $options): self
    {
        $this->c->batch('batchSetNoRouteMatchMethod', $this->method, $options);
        return $this;
    }
    /**
     * Render a template page when no route matches this HTTP method.
     *
     * @param string $PageFileName
     * @param int $statusCode Default HTTP status code (404)
     * @return $this
     */
    public function setNoRouteMatchPage(string $PageFileName, int $statusCode = 404): self
    {
        $PageFileName = strtolower(trim($PageFileName));
        $this->c->batch('batchSetNoRouteMatchPageMethod', $this->method, $PageFileName, $statusCode);
        return $this;
    }
    /**
     * Return a JSON payload when no route matches this HTTP method.
     *
     * @param array<mixed>|object $data
     * @param int $statusCode Default HTTP status code (404)
     * @return $this
     */
    public function setNoRouteMatchJson(array|object $data, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchJsonMethod', $this->method, $data, $statusCode);
        return $this;
    }
    /**
     * Return plain text when no route matches this HTTP method.
     *
     * @param string $message
     * @param int $statusCode Default HTTP status code (404)
     * @return $this
     */
    public function setNoRouteMatchText(string $message, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchTextMethod', $this->method, $message, $statusCode);
        return $this;
    }
    /**
     * Register a callback function when no route matches this HTTP method.
     *
     * @param string $functionName
     * @return $this
     */
    public function setNoRouteMatchCallback(string $functionName): self
    {
        $functionName = strtolower(trim($functionName));
        $this->c->batch('batchSetNoRouteMatchCallbackMethod', $this->method, $functionName);
        return $this;
    }
    /**
     * Configures Content-Security-Policy (CSP) directives for a given Method (in `/src/funkphp/app/<METHOD>.php`).
     *
     * Automatically wraps standard CSP keywords (e.g. 'self', 'none', 'unsafe-inline') in single quotes,
     * while preserving casing for hashes, nonces, and domains.
     *
     * @param 'require-trusted-types-for'|'trusted-types'|'default-src'|'script-src'|'script-src-elem'|'script-src-attr'|'style-src'|'style-src-elem'|'style-src-attr'|'img-src'|'font-src'|'connect-src'|'media-src'|'object-src'|'child-src'|'frame-src'|'worker-src'|'manifest-src'|'prefetch-src'|'base-uri'|'form-action'|'frame-ancestors'|'sandbox'|'plugin-types'|'navigate-to'|'report-uri'|'report-to' $sourceType
     * The CSP directive name. Supported values:
     * - `default-src`      : Fallback for other fetch directives.
     * - `script-src`       : JavaScript execution sources.
     * - `script-src-elem`  : Valid sources for `<script>` elements.
     * - `script-src-attr`  : Valid sources for inline event handlers (e.g. onclick).
     * - `style-src`        : Stylesheet and CSS sources.
     * - `style-src-elem`   : Valid sources for `<style>` and `<link rel="stylesheet">`.
     * - `style-src-attr`   : Valid sources for inline `style="..."` attributes.
     * - `img-src`          : Images and favicons.
     * - `font-src`         : Web fonts.
     * - `connect-src`      : Fetch, XMLHttpRequest, WebSocket, and EventSource targets.
     * - `media-src`        : Audio and video `<audio>`, `<video>`.
     * - `object-src`       : Plugins like Flash or PDF viewers (`<object>`, `<embed>`).
     * - `child-src`        : Web workers and nested frame contexts.
     * - `frame-src`        : Valid sources for `<iframe>` and `<frame>`.
     * - `worker-src`       : Valid sources for Worker, SharedWorker, or ServiceWorker.
     * - `manifest-src`     : Web App Manifest files.
     * - `prefetch-src`     : Resources to be prefetched or prerendered.
     * - `base-uri`         : Restricted URLs for the `<base>` element.
     * - `form-action`      : Valid target URLs for `<form>` submissions.
     * - `frame-ancestors`  : Valid parents that may embed this page in an `<iframe>`.
     * - `sandbox`         : Enables sandbox restrictions for the requested resource. Available flags:`allow-forms allow-same-origin allow-scripts allow-popups, allow-modals, allow-orientation-lock, allow-pointer-lock, allow-presentation, allow-popups-to-escape-sandbox, and allow-top-navigation`
     * - `report-uri`      : Endpoint URL where CSP violation reports are sent (Deprecated).
     * - `report-to`       : Reporting API group name for CSP violations.
     *
     * @param string ...$sources One or more sources (e.g. `'self'`, `'https://cdn.example.com'`, `'sha256-...'`).
     *
     * @example
     * FunkPHP()->config()->routes()-><METHOD>()->setCSP('script-src', 'self', 'https://cdn.jsdelivr.net');
     * FunkPHP()->config()->routes()-><METHOD>()->setCSP('object-src', 'none');
     *
     * @return $this
     */
    public function setCSP(string $sourceType, string ...$sources): self
    {
        $sourceType = strtolower(trim($sourceType));
        $this->c->batch('batchSetCSPMethod', $this->method, $sourceType, ...$sources);
        return $this;
    }
    /**
     * Configure rate limiting options for this HTTP method.
     *
     * @param array<string, mixed> $rateLimitingOptions
     * @return $this
     */
    public function setRateLimit(array $rateLimitingOptions): self
    {
        $this->c->batch('batchSetRateLimitingRoute', $this->method, $rateLimitingOptions);
        return $this;
    }
    /**
     * Attach a middleware to all routes under this HTTP method.
     *
     * @param string $middleware Middleware name or reference key
     * @return $this
     */
    public function pipeMiddleware(string $middleware = ''): self
    {
        $middleware = strtolower(trim($middleware));
        $this->c->batch('batchPipeMiddlewareMethod', $this->method, $middleware);
        return $this;
    }
    /**
     * Set a response Header that is applied on current HTTP Method (can be overwritten by a given matched Route AND/OR via `->setExcludeHeaders`).
     *
     * @param 'Accept-Ranges'|'Access-Control-Allow-Credentials'|'Access-Control-Allow-Headers'|'Access-Control-Allow-Methods'|'Access-Control-Allow-Origin'|'Access-Control-Expose-Headers'|'Access-Control-Max-Age'|'Age'|'Allow'|'Alt-Svc'|'Cache-Control'|'Clear-Site-Data'|'Content-Disposition'|'Content-Encoding'|'Content-Language'|'Content-Location'|'Content-Range'|'Content-Security-Policy'|'Content-Security-Policy-Report-Only'|'Content-Type'|'Cross-Origin-Embedder-Policy'|'Cross-Origin-Opener-Policy'|'Cross-Origin-Resource-Policy'|'ETag'|'Expires'|'Last-Modified'|'Location'|'Origin-Trial'|'Permissions-Policy'|'Pragma'|'Referrer-Policy'|'Retry-After'|'Server-Timing'|'Strict-Transport-Security'|'Timing-Allow-Origin'|'Vary'|'WWW-Authenticate'|'X-Content-Type-Options'|'X-Frame-Options'|'X-RateLimit-Limit'|'X-RateLimit-Remaining'|'X-RateLimit-Reset'|'X-Request-ID'|'X-XSS-Protection'|string $header Header Name
     * @param string $value Header Value (e.g., "nosniff")
     * @return $this
     */
    public function setHeader(string $header, string $value): self
    {
        $this->c->batch('batchSetHeaderMethod', $this->method, trim($header), trim($value));
        return $this;
    }
    /**
     * Remove a previously queued response header for this HTTP method.
     *
     * @param string $header_to_remove Case-insensitive header key to remove
     * @return $this
     */
    public function removeHeader(string $header_to_remove): self
    {
        $header_to_remove = strtolower(trim($header_to_remove));
        $this->c->batch('batchRemoveHeaderMethod', $this->method, $header_to_remove);
        return $this;
    }
    /**
     * Define a parameter validation regex rule scoped to this HTTP method.
     *
     * @param string $param Parameter name without leading colon (e.g., "id")
     * @param string $regex Regex pattern (e.g., "/[\d]+/")
     * @param string|null $defaultParamValueOnRegexMismatch Fallback value if validation fails
     * @return $this
     */
    public function setParamRule(string $param, string $regex, string|null $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleMethod', $this->method, $param, $regex, $defaultParamValueOnRegexMismatch);
        return $this;
    }
    /**
     * Initialize a new route definition for the current HTTP method.
     *
     * @param string $path Route path pattern (e.g., "/users/:id")
     * @return FunkRoute
     */
    public function ROUTE(string $path): FunkRoute
    {
        $this->c->batch('batchNewRoute', $this->method, strtolower(trim($path)));
        return new FunkRoute($this->c, $this, $this->method, strtolower(trim($path)));
    }
    /**
     * Switch context back to HEAD method builder.
     *
     * @return FunkMethod
     */
    public function HEAD(): FunkMethod
    {
        return $this->parent->HEAD();
    }
    /**
     * Switch context back to GET method builder.
     *
     * @return FunkMethod
     */
    public function GET(): FunkMethod
    {
        return $this->parent->GET();
    }
    /**
     * Switch context back to POST method builder.
     *
     * @return FunkMethod
     */
    public function POST(): FunkMethod
    {
        return $this->parent->POST();
    }
    /**
     * Switch context back to PUT method builder.
     *
     * @return FunkMethod
     */
    public function PUT(): FunkMethod
    {
        return $this->parent->PUT();
    }
    /**
     * Switch context back to PATCH method builder.
     *
     * @return FunkMethod
     */
    public function PATCH(): FunkMethod
    {
        return $this->parent->PATCH();
    }
    /**
     * Switch context back to DELETE method builder.
     *
     * @return FunkMethod
     */
    public function DELETE(): FunkMethod
    {
        return $this->parent->DELETE();
    }
}
/*
 * Class FunkRoute() - accessed via FunkPHP()->routes()-><METHOD>()->route("/URI-path")
*/
class FunkRoute
{
    public function __construct(
        private C $c,
        private FunkMethod $parentMethod,
        private string $method,
        private string $routePath,
    ) {}
    /**
     * FLUENT METHOD VISUAL COMMENT DIVIDER (HAS NO LOGICAL, BUT MAYBE PRACTICAL EFFECT)
     *
     * USE: `_('GLOBAL HANDLERS')` or `_('ROUTES FOR BLABLA')`
     *
     * @param string ...$comment Optional Visual Label
     *
     * IMPORTANT: it is IGNORED during Compilation & Runtime.
     * @return $this
     */
    public function _(string ...$comment): self
    {
        return $this;
    }
    /**
     * ARBITRARY SPACE BETWEEN CHAINED METHODS (HAS NO LOGICAL, BUT MAYBE PRACTICAL EFFECT)
     *
     * @return $this
     */
    public function ______________________________________________(): self
    {
        return $this;
    }
    /**
     * Set a named alias for this specific route.
     *
     * @param string $aliasName Unique alias identifier
     * @return $this
     */
    public function setAlias(string $aliasName = ''): self
    {
        $aliasName = trim($aliasName);
        $this->c->batch('batchSetAliasRoute', $this->method, $this->routePath, $aliasName);
        return $this;
    }
    /**
     * Configure rate limiting options specific to this route.
     *
     * @param array<string, mixed> $rateLimitingOptions Rate limiting parameters
     * @return $this
     */
    public function setRateLimit(array $rateLimitingOptions): self
    {
        $this->c->batch('batchSetRateLimitingRoute', $this->method, $this->routePath, $rateLimitingOptions);
        return $this;
    }
    /**
     * Configure response caching options for this route.
     *
     * @param array<string, mixed> $cacheOptions Cache strategy configuration
     * @return $this
     */
    public function setCache(array $cacheOptions): self
    {
        $this->c->batch('batchSetCacheRoute', $this->method, $this->routePath, $cacheOptions);
        return $this;
    }
    /**
     * Attach a middleware specific to this route. They all run in FIFO.
     *
     * @param string $middleware Middleware function or group name
     * @return $this
     */
    public function pipeMiddleware(string $middleware = ''): self
    {
        $middleware = strtolower(trim($middleware));
        $this->c->batch('batchPipeMiddlewareRoute', $this->method, $this->routePath, $middleware);
        return $this;
    }
    /**
     * Pipe a handler function for this route. They all run in FIFO.
     *
     * @param string $fileNameAndFunctionName Function or file reference key
     * @return $this
     */
    public function pipeFunction(string $fileNameAndFunctionName = ''): self
    {
        $fileNameAndFunctionName = strtolower(trim($fileNameAndFunctionName));
        $this->c->batch('batchPipeFunctionRoute', $this->method, $this->routePath, $fileNameAndFunctionName);
        return $this;
    }
    /**
     * Specify ONE Response transformation or content type format for this route.
     *
     * @param string $typeOfResponse Response format identifier (e.g., "json", "html")
     * @return $this
     */
    public function pipeResponse(string $typeOfResponse): self
    {
        $typeOfResponse = trim($typeOfResponse);
        $this->c->batch('batchPipeResponseRoute', $this->method, $this->routePath, $typeOfResponse);
        return $this;
    }
    /**
     * Pipe an raw SQL execution handler to this route.
     *
     * @param string $sqlFileFunction SQL execution handler function
     * @return $this
     */
    public function pipeSQL(string $sqlFileFunction): self
    {
        $sqlFileFunction = strtolower(trim($sqlFileFunction));
        $this->c->batch('batchPipeSQLRoute', $this->method, $this->routePath, $sqlFileFunction);
        return $this;
    }
    /**
     * Pipe a database query handler function to this route.
     *
     * @param string $queryFileFunction Query handler function
     * @return $this
     */
    public function pipeQuery(string $queryFileFunction): self
    {
        $queryFileFunction = strtolower(trim($queryFileFunction));
        $this->c->batch('batchPipeQueryRoute', $this->method, $this->routePath, $queryFileFunction);
        return $this;
    }
    /**
     * Pipe a request validation handler function to this route.
     *
     * @param string $validationFileFunction Validation handler function
     * @return $this
     */
    public function pipeValidation(string $validationFileFunction): self
    {
        $validationFileFunction = strtolower(trim($validationFileFunction));
        $this->c->batch('batchPipeValidationRoute', $this->method, $this->routePath, $validationFileFunction);
        return $this;
    }
    /**
     * Pipe a compiled pre-optimized SQL handler to this route.
     *
     * @param string $compiledSQLFileFunction Compiled SQL handler function
     * @return $this
     */
    public function pipeCompiledSQL(string $compiledSQLFileFunction)
    {
        $compiledSQLFileFunction = strtolower(trim($compiledSQLFileFunction));
        $this->c->batch('batchPipeCompiledSQLRoute', $this->method, $this->routePath, $compiledSQLFileFunction);
        return $this;
    }
    /**
     * Pipe a compiled pre-optimized database query handler to this route.
     *
     * @param string $compiledQueryFileFunction Compiled query handler function
     * @return $this
     */
    public function pipeCompiledQuery(string $compiledQueryFileFunction)
    {
        $compiledQueryFileFunction = strtolower(trim($compiledQueryFileFunction));
        $this->c->batch('batchPipeCompiledQueryRoute', $this->method, $this->routePath, $compiledQueryFileFunction);
        return $this;
    }
    /**
     * Pipe a compiled pre-optimized validation handler to this route.
     *
     * @param string $compiledValidationFileFunction Compiled validation handler function
     * @return $this
     */
    public function pipeCompiledValidation(string $compiledValidationFileFunction)
    {
        $compiledValidationFileFunction = strtolower(trim($compiledValidationFileFunction));
        $this->c->batch('batchPipeCompiledValidationRoute', $this->method, $this->routePath, $compiledValidationFileFunction);
        return $this;
    }
    /**
     * Exclude specific global/method middlewares from running on this route.
     *
     * @param string ...$middlewareToExclude Middleware names or keys to bypass
     * @return $this
     */
    public function setExcludeMiddlewares(string ...$middlewareToExclude): self
    {
        $this->c->batch('batchExcludeMiddlewaresRoute', $this->method, $this->routePath, ...$middlewareToExclude);
        return $this;
    }
    /**
     * Exclude specific global/method response headers from being sent on this route.
     *
     * @param string ...$headersToExclude Header keys to bypass
     * @return $this
     */
    public function setExcludeHeaders(string ...$headersToExclude): self
    {
        $this->c->batch('batchExcludeHeadersRoute', $this->method, $this->routePath, ...$headersToExclude);
        return $this;
    }
    /**
     * Define a Single Parameter Regex Rule scoped exclusively to this Route.
     *
     * @param string $param Parameter name without leading colon (e.g., "id")
     * @param string $regex Regex pattern (e.g., "/[\d]+/")
     * @param string|null $defaultParamValueOnRegexMismatch Fallback value if validation fails
     * @return $this
     */
    public function setParamRule(string $param, string $regex, string|null $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleRoute', $this->method, $this->routePath, $param, $regex, $defaultParamValueOnRegexMismatch);
        return $this;
    }
    /**
     * Define Multiple Alternative Regex Rules for a Single Route Parameter (so called `Polymorphic Parameter`) scoped exclusively to this Route.
     *
     * Allows a parameter (e.g., ":identifier") to match against different input forms
     * (e.g., "numeric_id", "/\d+/", "slug", "/[a-z0-9-]+/").
     *
     * @param string $paramIdentifier Parameter name without leading colon (e.g., "id" or "identifier")
     * @param string ...$keyAndRegexPairs Sequential pairs of [RuleName, RegexPattern] (e.g., "num", "/\d+/", "slug", "/[a-z]+/")
     * @return $this
     */
    public function setParamRulePolymorphic(string $paramIdentifier, string ...$keyAndRegexPairs): self
    {
        $paramIdentifier = strtolower(trim($paramIdentifier));
        $this->c->batch('batchSetParamRulePolymorphicRoute', $this->method, $this->routePath, $paramIdentifier, ...$keyAndRegexPairs);
        return $this;
    }
    /**
     * Configures Content-Security-Policy (CSP) directives for a given Route in a Method (in `/src/funkphp/app/<METHOD>.php`).
     *
     * Automatically wraps standard CSP keywords (e.g. 'self', 'none', 'unsafe-inline') in single quotes,
     * while preserving casing for hashes, nonces, and domains.
     *
     * @param 'require-trusted-types-for'|'trusted-types'|'default-src'|'script-src'|'script-src-elem'|'script-src-attr'|'style-src'|'style-src-elem'|'style-src-attr'|'img-src'|'font-src'|'connect-src'|'media-src'|'object-src'|'child-src'|'frame-src'|'worker-src'|'manifest-src'|'prefetch-src'|'base-uri'|'form-action'|'frame-ancestors'|'sandbox'|'plugin-types'|'navigate-to'|'report-uri'|'report-to' $sourceType
     * The CSP directive name. Supported values:
     * - `default-src`      : Fallback for other fetch directives.
     * - `script-src`       : JavaScript execution sources.
     * - `script-src-elem`  : Valid sources for `<script>` elements.
     * - `script-src-attr`  : Valid sources for inline event handlers (e.g. onclick).
     * - `style-src`        : Stylesheet and CSS sources.
     * - `style-src-elem`   : Valid sources for `<style>` and `<link rel="stylesheet">`.
     * - `style-src-attr`   : Valid sources for inline `style="..."` attributes.
     * - `img-src`          : Images and favicons.
     * - `font-src`         : Web fonts.
     * - `connect-src`      : Fetch, XMLHttpRequest, WebSocket, and EventSource targets.
     * - `media-src`        : Audio and video `<audio>`, `<video>`.
     * - `object-src`       : Plugins like Flash or PDF viewers (`<object>`, `<embed>`).
     * - `child-src`        : Web workers and nested frame contexts.
     * - `frame-src`        : Valid sources for `<iframe>` and `<frame>`.
     * - `worker-src`       : Valid sources for Worker, SharedWorker, or ServiceWorker.
     * - `manifest-src`     : Web App Manifest files.
     * - `prefetch-src`     : Resources to be prefetched or prerendered.
     * - `base-uri`         : Restricted URLs for the `<base>` element.
     * - `form-action`      : Valid target URLs for `<form>` submissions.
     * - `frame-ancestors`  : Valid parents that may embed this page in an `<iframe>`.
     * - `sandbox`         : Enables sandbox restrictions for the requested resource. Available flags:`allow-forms allow-same-origin allow-scripts allow-popups, allow-modals, allow-orientation-lock, allow-pointer-lock, allow-presentation, allow-popups-to-escape-sandbox, and allow-top-navigation`
     * - `report-uri`      : Endpoint URL where CSP violation reports are sent (Deprecated).
     * - `report-to`       : Reporting API group name for CSP violations.
     *
     * @param string ...$sources One or more sources (e.g. `'self'`, `'https://cdn.example.com'`, `'sha256-...'`).
     *
     * @example
     * FunkPHP()->config()->routes()-><METHOD>->route()->setCSP('script-src', 'self', 'https://cdn.jsdelivr.net');
     * FunkPHP()->config()->routes()-><METHOD>->route()->setCSP('object-src', 'none');
     *
     * @return $this
     */
    public function setCSP(string $sourceType, string ...$sources): self
    {
        $sourceType = strtolower(trim($sourceType));
        $this->c->batch('batchSetCSPRoute', $this->method, $this->routePath, $sourceType, ...$sources);
        return $this;
    }
    /**
     * Set a response header to be sent exclusively for this Route.
     *
     * @param 'Accept-Ranges'|'Access-Control-Allow-Credentials'|'Access-Control-Allow-Headers'|'Access-Control-Allow-Methods'|'Access-Control-Allow-Origin'|'Access-Control-Expose-Headers'|'Access-Control-Max-Age'|'Age'|'Allow'|'Alt-Svc'|'Cache-Control'|'Clear-Site-Data'|'Content-Disposition'|'Content-Encoding'|'Content-Language'|'Content-Location'|'Content-Range'|'Content-Security-Policy'|'Content-Security-Policy-Report-Only'|'Content-Type'|'Cross-Origin-Embedder-Policy'|'Cross-Origin-Opener-Policy'|'Cross-Origin-Resource-Policy'|'ETag'|'Expires'|'Last-Modified'|'Location'|'Origin-Trial'|'Permissions-Policy'|'Pragma'|'Referrer-Policy'|'Retry-After'|'Server-Timing'|'Strict-Transport-Security'|'Timing-Allow-Origin'|'Vary'|'WWW-Authenticate'|'X-Content-Type-Options'|'X-Frame-Options'|'X-RateLimit-Limit'|'X-RateLimit-Remaining'|'X-RateLimit-Reset'|'X-Request-ID'|'X-XSS-Protection'|string $header Header Name
     * @param string $value Header Value (e.g., "nosniff")
     *
     * @return $this
     */
    public function setHeader(string $header, string $value): self
    {
        $this->c->batch('batchSetHeaderRoute', $this->method, $this->routePath, trim($header), trim($value));
        return $this;
    }
    /**
     * Remove a previously queued header for this specific route.
     *
     * @param string $header_to_remove Case-insensitive header key to remove
     * @return $this
     */
    public function removeHeader(string $header_to_remove): self
    {
        $this->c->batch('batchRemoveHeaderRoute', $this->method, $this->routePath, $header_to_remove);
        return $this;
    }
    /**
     * Initialize another route under the current HTTP method context.
     *
     * @param string $path Route path pattern (e.g., "/posts/:slug")
     * @return FunkRoute
     */
    public function ROUTE(string $path): FunkRoute
    {
        return $this->parentMethod->ROUTE($path);
    }
    /**
     * Switch context back to HEAD method builder.
     *
     * @return FunkMethod
     */
    public function HEAD(): FunkMethod
    {
        return $this->parentMethod->HEAD();
    }
    /**
     * Switch context back to GET method builder.
     *
     * @return FunkMethod
     */
    public function GET(): FunkMethod
    {
        return $this->parentMethod->GET();
    }
    /**
     * Switch context back to POST method builder.
     *
     * @return FunkMethod
     */
    public function POST(): FunkMethod
    {
        return $this->parentMethod->POST();
    }
    /**
     * Switch context back to PUT method builder.
     *
     * @return FunkMethod
     */
    public function PUT(): FunkMethod
    {
        return $this->parentMethod->PUT();
    }
    /**
     * Switch context back to PATCH method builder.
     *
     * @return FunkMethod
     */
    public function PATCH(): FunkMethod
    {
        return $this->parentMethod->PATCH();
    }
    /**
     * Switch context back to DELETE method builder.
     *
     * @return FunkMethod
     */
    public function DELETE(): FunkMethod
    {
        return $this->parentMethod->DELETE();
    }
}
/* Global entry point for initializing FunkPHP in `/src/funkphp/config/app.php` */
function FunkPHP()
{
    return new FunkPHP(new C);
}
