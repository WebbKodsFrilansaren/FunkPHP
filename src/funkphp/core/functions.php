<?php

/**
 * -----------------
 * FUNKPHP FUNCTIONS
 * -----------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
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
// This function uses the "The Random\Randomizer class" to generate a unique number
function funk_generate_random_number(&$c, $length = 10)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();
    // Prepare numbers that can be used
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
    // Prepare empty number string and total count of numbers array minus 1
    // and add random numbers to the number until it reaches the desired length
    $total = count($numbers) - 1;
    $number = '';
    // First number cannot be 0
    $randomCharIndex = $randomizer->getInt(1, $total);
    $number .= $numbers[$randomCharIndex];
    while (strlen($number) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total);
        $number .= $numbers[$randomCharIndex];
    }
    // Return the generated number as an integer
    return (int)$number;
}
// This function uses the "The Random\Randomizer class" to generate a unique user_id
function funk_generate_random_user_id(&$c, $length = 96)
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

    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers);
    $total = count($all) - 1;

    // Prepare empty user_id string and add random characters to the user_id until it reaches the desired length
    $user_id = '';
    while (strlen($user_id) < $length) {
        // Insert a "-" after every 24 characters except for the last one
        if (strlen($user_id) % 24 == 0 && strlen($user_id) != 0) {
            $user_id .= '-';
            continue;
        }
        $randomCharIndex = $randomizer->getInt(0, $total);
        $user_id .= $all[$randomCharIndex];
    }

    // Return the generated user_id
    return $user_id;
}
// This function uses the "The Random\Randomizer class" to generate a unique CSRF
function funk_generate_random_csrf(&$c, $length = 384)
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

    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers);
    $total = count($all) - 1;

    // Prepare empty CSRF string and add random characters to the CSRF until it reaches the desired length
    $csrf = '';
    while (strlen($csrf) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total);
        $csrf .= $all[$randomCharIndex];
    }

    // Return the generated CSRF
    return $csrf;
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
        isset($c['<ENTRY>']['pipeline']['post_response'])
        && is_array($c['<ENTRY>']['pipeline']['post_response'])
        && array_is_list($c['<ENTRY>']['pipeline']['post_response'])
        && !empty($c['<ENTRY>']['pipeline']['post_response'])
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

// Function to skip the post_response pipeline
function funk_skip_post_response(&$c)
{
    $c['req']['skip_post_response'] = true;
    ob_end_clean();
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

// Same as above but used for the post response functions
// IMPORTANT: As you can see, it will remove all remaining
// pipeline functions, so use with care!
function funk_abort_pipeline_post_response(&$c)
{
    $c['req']['keep_running_pipeline'] = false;
    return;
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

/*** PAGE-RELATED Functions For FunkPHP  ***/






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
    // The actual written config line by line starting with FunkPHP()
    public array $FunkPHPTextArray = ["FunkPHP()"];
    // $errors contain all errors + categorized errors
    // $WARNINGS contain warnings meaning compiling/running will happen
    // but developer will be known about possible issues such as dangerous
    // function calls, early exists, evals(), and so on. But they are never stopped
    // unless configured so (if $this->NoWarningsAllowed is set to TRUE).
    private array $errors = [];
    private array $WARNINGS = [];
    private array $compileFlags = [];
    // Valid + Invalid batches, compile() only starts if $invalidBatches is empty!
    private array $validBatches = [];
    private array $invalidBatches = [];
    // $cached = (Attempted) Access to any file/function and/or file=>function in a DRY fashion!
    private array $cached = [
        'placeholderRoutes' => [],
        'placeholderParamContexts' => [],
        'placeHolderUsedUserDefinedEngineFNS' => [], // defaultRegisterShutDown,Error|ExceptionHandler&HTTPSKernel
        'placeholderUsedUserDefinedFunctions' => [],
        'placeholderUsedUserDefinedClasses' => [],
        'placeholderMiddlewaresInWhatRoutes' => [],
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
        'file_core_functions' => null,
        'file_manifest' => null,

    ];
    // $compiled = The entire compiled code that can either be executed as is OR
    // be exported to the `/src/funkphp/FunkPHPDeployment.php` File!
    private array $compiled = [
        'config' => [],
        'methods' => [],
        'routes' => [],
        'middlewares' => [],
        'pages' => ['compiled' => [], 'layouts' => [], 'components' => [], 'partials' => [], 'errors' => []],
        'data' => ['query' => [], 'sql' => [], 'validation' => []],
        // This is the $c Variable that is then assigned automatically globally.
        'c' => [
            'FUNKPHP_ONLINE' => false,
            'FUNKPHP_USE_HTTPS' => false,
            'FUNKPHP_USE_PREPARE_URI' => true,
            "FUNKPHP_USE_VENDOR" => true,
            "FUNKPHP_CUSTOM_EXCEPTION_HANDLER" => null,
            "FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION" => null,
            "FUNKPHP_CUSTOM_ERROR_HANDLER" => null,
            "FUNKPHP_CUSTOM_URI_NORMALIZER" => null,
            "FUNKPHP_CUSTOM_HTTPS_KERNEL_DISPATCH_PIPELINE_REQUEST_FUNCTION" => null,
            'INI_SETS' => [
                'session.cache_limiter' => 'public',
                'session.use_strict_mode' => 8,
                'session.use_only_cookies' => 1,
                'session.cache_expire' => 30,
                'session.cookie_lifetime' => 0,
                'session.name' => 'fphp_id',
                'session.sid_length' => 192,
                'session.sid_bits_per_character' => 6,
                'display_errors'          => 1,
                'display_startup_errors'  => 1,
                'error_reporting'         => 1,
            ],
            'BASEURLS' => [
                'LOCAL' => "http://webdev.local:81/funkphp",
                'ONLINE' => "https://www.funkphp.com",
                'BASEURL' =>  'localhost',
                'BASEURL_URI' => '/funkphp/src/public_html/',
            ],
            'SESSION' => [
                'driver' => 'files',
                'COOKIES' => [
                    'SESSION_NAME' => 'fphp_id',
                    'SESSION_LIFETIME' => 28800,
                    'SESSION_PATH' => '/',
                    'SESSION_DOMAIN' => "webdev.local",
                    'SESSION_SECURE' => false,
                    'SESSION_HTTPONLY' => true,
                    'SESSION_SAMESITE' => 'Lax',
                ]
            ],
            '<ENTRY>' => [],
            'pipeline' => [
                'request' => [],
                'post_response' => []
            ],
            'ROUTES' => [],
            'shared' => [],
            'custom' => null,
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
                'matched_pipeline' => [],
                'matched_middlewares' => null,
                'skip_post_response' => false,
                'current_pipeline' => null,
                'next_pipeline' => null,
                'current_middleware' => null,
                'next_middleware' => null,
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
            'err' => [
                'MAYBE' => [],
                'FUNCTIONS' => [],
                'CLASSES' => [],
                'CONNECTIONS' => [],
                'PIPELINE' => [],
                'MIDDLEWARES' => [],
                'PAGE' => [],
                'VALIDATION' => [],
                'SQL' => [],
                'QUERY' => [],
            ],
        ],
    ];

    // NAVIGATION VARIABLES+METHODS IN IDE ->config()
    private ?FunkConfig $configScope = null;
    private ?FunkRoutes $routesScope = null;
    // Default booleans for compile(), run()
    private bool $FunkPHPcompiled = false;
    private bool $FunkPHPbooted = false;

    // Helper function to build the $FunkPHPTextArray
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

    private function appendFunkPHPTextArray(string $methodName, mixed ...$vars): string
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
    private function getLastFunkPHPTextArray(): string
    {
        return ($this->FunkPHPTextArray[count($this->FunkPHPTextArray) - 1]);
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
            $err = "[Class C]: Expected `ROOT_FOLDER` Constant Not Defined or is not ending with `src/funkphp` as a Non-Empty String. It is supposed to be defined in `/src/funkphp/core/CONSTANTS.php`. Verify the integrity of that File.";
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
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/data/sql', $optionalFileName);
            }
        } elseif ($key === 'files_data_query') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/data/query', $optionalFileName);
            }
        } elseif ($key === 'files_data_validation') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/data/validation', $optionalFileName);
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
                global $reserved_functions;
                $reserved = $reserved_functions ?? [];
                // 1. Tokenized Namespace & Use statements
                $nsAndUses = $this->file_harvest_namespace_and_uses_from_code($fileRaw);
                $namespace = $nsAndUses['namespace'];
                $namespaceParts = $nsAndUses['namespace_parts'];
                $fileUse = $nsAndUses['file_use'];
                // 2. Tokenized Functions
                $tokenizedFns = $this->file_harvest_all_functions_from_code($fileRaw);
                foreach ($tokenizedFns as $fnName => $fnData) {
                    $isReserved = in_array($fnName, $reserved, true);
                    $fns[$fnName] = array_merge($fnData, [
                        'valid_fn_structure'          => !$isReserved && !$fnData['has_inner_functions'],
                        'fn_name_reserved'            => $isReserved,
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
                // 3. Tokenized Classes
                $tokenizedClasses = $this->file_harvest_all_classes_from_code($fileRaw);
                foreach ($tokenizedClasses as $className => $classData) {
                    $classes[$className] = $classData;
                    if (in_array(strtolower($className), $clnames_only, true)) {
                        $clnames_duplicates[$className] = true;
                    }
                    $clnames_only[] = $className;
                }
            } else {
                $this->errors[] = ['type' => 'internal', 'err' => "Class C->file_status()] - FAILED to read Folder+File Path:`{$folder}{$file}` when it should ahve been possible. Verify Folder/File Permissions in Your Project."];
                return ['INTERNAL_FUNKPHP_ERROR' => "[INTERNAL FUNKPHP ERROR - file_status()] - FAILED to read Folder+File Path:`{$folder}{$file}` when it should ahve been possible. Verify Folder/File Permissions in Your Project."];
            }
        }
        return [
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
    private function file_analyze_body_tokens(string $bodyCode, int $startLine = 1): array
    {
        $tokens = PhpToken::tokenize("<?php " . $bodyCode);
        $count = count($tokens);
        $dangerousFuncs = ['shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'base64_decode'];
        $hasExit = false;
        $exitLines = [];
        $hasRawOutput = false;
        $rawOutputLines = [];
        $hasEval = false;
        $evalLines = [];
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
        // Account for added '<?php ' offset in line mapping if needed
        $lineOffset = $startLine;
        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];
            $line = $tok->line + $lineOffset;
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
            // 3. Eval / Dynamic Code
            if ($tok->id === T_EVAL) {
                $hasEval = true;
                $evalLines[] = $line;
                continue;
            }
            // 4. Nested Functions (Named vs Anonymous/Closures)
            if ($tok->id === T_FUNCTION || (defined('T_FN') && $tok->id === T_FN)) {
                $nextIdx = $i + 1;
                // Fast-forward past whitespace, comments, and reference operators ('&')
                while ($nextIdx < $count && (
                    $tokens[$nextIdx]->id === T_WHITESPACE ||
                    $tokens[$nextIdx]->id === T_COMMENT ||
                    $tokens[$nextIdx]->id === T_DOC_COMMENT ||
                    $tokens[$nextIdx]->text === '&'
                )) {
                    $nextIdx++;
                }
                // If a T_STRING immediately follows, it's a NAMED inner function (e.g., function test() {})
                if ($nextIdx < $count && $tokens[$nextIdx]->id === T_STRING) {
                    $hasInnerFunctions = true;
                    $innerFunctionLines[] = $line;
                    continue;
                }
                // Otherwise, it's an anonymous closure ($var = function() {}) or arrow function
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
                // Exclude method calls ($obj->method), static calls (Class::method), definitions, or instantiations
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
                // Confirm call via opening parenthesis '('
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
                    if (str_starts_with(strtolower($calledName), 'funk_')) {
                        $funkCalls[] = [
                            'name' => $calledName,
                            'line' => $lineNo,
                            'args' => trim($argsString)
                        ];
                    }
                }
            }
        }
        return [
            'has_exit'             => $hasExit,
            'exit_lines'           => array_unique($exitLines),
            'has_raw_output'       => $hasRawOutput,
            'raw_output_lines'     => array_unique($rawOutputLines),
            'has_eval'             => $hasEval,
            'eval_lines'           => array_unique($evalLines),
            'has_inner_functions'  => $hasInnerFunctions,
            'nested_function_lines' => array_unique($innerFunctionLines),
            'has_closures'           => $hasClosures ?? false,   // Safe anonymous closures
            'closure_lines'          => array_unique($closureLines ?? []),
            'has_inner_classes'    => $hasInnerClasses,
            'inner_class_lines'    => array_unique($innerClassLines),
            'has_globals'          => $hasGlobals,
            'global_vars'          => array_unique($globalVars),
            'has_dangerous_calls'  => $hasDangerousCalls,
            'dangerous_calls' => $dangerousCalls,
            'only_whitespace_and_or_comments' => $hasOnlyCommentsOrWhiteSpace,
            'has_variable_vars'    => $hasVariableVars,
            'calls'                => $calls,
            'funk_calls' => $funkCalls,
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
            // We only parse class members at the top level of the class body (depth === 1 inside "class { ... }")
            if ($braceDepth !== 1) {
                continue;
            }
            // 1. TRAIT INCLUSION: "use TraitA, TraitB;"
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
            // 2. CONSTANTS: "public const FOO = 'bar';"
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
                // Look forward for CONST_NAME = value
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
            // 3. METHODS: "public static function myMethod($a) { ... }"
            if ($tok->id === T_FUNCTION) {
                $visibility = 'public'; // PHP default
                $isStatic   = false;
                $isAbstract = false;
                $isFinal    = false;
                // Look backward for modifiers
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
                // Find method name
                $nameIdx = $i + 1;
                while ($nameIdx < $count && ($tokens[$nameIdx]->id === T_WHITESPACE || $tokens[$nameIdx]->text === '&')) {
                    $nameIdx++;
                }
                if ($nameIdx >= $count || $tokens[$nameIdx]->id !== T_STRING) {
                    continue;
                }
                $methodName = $tokens[$nameIdx]->text;
                $methodLine = $tok->line + $lineOffset;
                // Harvest arguments string inside (...)
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
                // Find method body opening '{' or abstract semicolon ';'
                while ($bodySearchIdx < $count && $tokens[$bodySearchIdx]->text !== '{' && $tokens[$bodySearchIdx]->text !== ';') {
                    $bodySearchIdx++;
                }
                if ($bodySearchIdx >= $count) {
                    continue;
                }
                // Abstract or interface method with no body
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
                // Extract body using brace depth
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
            // 4. PROPERTIES: "private static ?string $name = 'default';"
            if ($tok->id === T_VARIABLE) {
                $propName = ltrim($tok->text, '$');
                $visibility = 'public'; // Default if unassigned
                $isStatic   = false;
                $isReadonly = false;
                $typeHint   = null;
                // Scan backwards to semicolon / brace / docblock for modifiers and type hint
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
                // Scan forward for default value until ';'
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
            return "File Function Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` cannot have Inner Function Declarations (e.g. `function name(&\$c){ function inner(&\$c){} }`). See line(s):`" . join(', ', $FN['nested_function_lines']) . "` in the File.";
        }
        return null; // Function File for FunkPHP use is all OK here! - Warnings are emitted by another function
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
    // It also first appends to the FunkPHPTextArray
    private function setCtx(string $batchFN, string $under, mixed ...$vals)
    {
        $this->FunkPHPTextArray[] = $this->appendFunkPHPTextArray($batchFN, ...$vals);
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
            'InvalidMiddlewareFunctionName' => "Invalid Middleware Function Name in {$optionalCtx}: must be a `Non-Empty All Lowercased String (no trailing spaces)` that only uses `[a-z_][a-z0-9_]+` characters in that order while it does NOT start with `cli_` OR `funk_`.",
            'InvalidGroupName'                                => "Invalid Group Name Value in {$optionalCtx}: must be a `Non-Empty String (no trailing spaces)` all `lowercased` that does NOT start with `cli_` OR `funk_`.",
            'InvalidResponseType' => "Invalid Response Type in {$optionalCtx}: Choose between: `page:`, `json:`, `callback:`, OR `text:` and then follow up with the `pageFileName` (for page:), OR `SingleArrayKeyDepth` - only use `[a-zA-Z-_.]` characters - to get `\$c['d']['SingleArrayKeyDepth']` (if 'json:SingleArrayKeyDepth') for where `Stored JSON Data` should be returned from (for json:), OR `userDefinedFunctionName in /src/funkphp/config/functions.php` that you have defined to use as a callback (for callback:), OR the plain text message (for text:). `pipeResponse() automatically completes it with exit()` and then run any optionally configured `Post-Response`.",
            'InvalidResponseContext' => "Invalid Response Context in {$optionalCtx}: Valid choice between `page:|json:|callback:|text:` found, but the Context after the Single Colon (`:`) is Empty or Invalid. ",
            'InvalidAddHeaderFormat' => "Invalid Header Value Format in {$optionalCtx}: Header must not contain any kind of newline characters (`CRLF Injections` risks) and must follow `Header-Name: Header-Value` syntax (e.g. `X-Frame-Options: DENY`) where the Single Semi-colon (`:`) is the `divider` between `Key` and `Value`.",
            'InvalidHeaderName' => "Invalid Header Name Value in {$optionalCtx}: Must be a `Non-Empty String` with Header Name Only (e.g. `server`, `x-powered-by`), with `Only Alphanumerics` and `single dashes between the words.`",
            'InvalidCSPSourceArray' => "Invalid CSP Source Array in {$optionalCtx}: Ensure Sources are Valid Non-Empty Strings with no spaces, semicolons, or CRLF Injections.",
            'InvalidCSPDirective' => "Invalid CSP Directive Name Value in {$optionalCtx}. Must be one of the following: ",
            'InvalidCSPWildcardUse' => "Invalid Wildcard Domain CSP Source Value in {$optionalCtx}. Wildcards must appear as `*.domain.com` OR `https://*.domain.com`.",
            'InvalidNonceKeys' => "Invalid Nonce Array Value in {$optionalCtx}: Nonce Keys must be Non-Empty Strings containing only `[a-zA-Z0-9_-]` characters (e.g., `test`, `main_script`, `inline-css`). They are then referenced with `SetCSP` as `->setCSP('script-src','nonce:main_script')` OR in Templated Pages:`{{nonce:main_script}}`.",
            'InvalidNonceKeyName' => "Invalid Nonce Key Value in {$optionalCtx}: Nonce Keys must be Non-Empty Strings containing only `[a-zA-Z0-9_-]` characters (e.g., `test`, `main-script`).",
            'InvalidPageName' => "Invalid Page Name Value in {$optionalCtx}: must be a `Non-Empty String` containing only `[a-zA-Z0-9-_]` characters (no trailing spaces) and without the File Extension.",
            'InvalidNoRouteMatchTextValue' => "Invalid Text Value in {$optionalCtx}: must be a `Non-Empty String` after `trim()` have been applied to it.",
            'InvalidRegex'                                => "Invalid Regex Value in {$optionalCtx}: must be a Non-Empty String that is also a Valid Regex Pattern when parsed by `preg_match()`. It cannot be an Empty Expression with optional modifiers (e.g. `//` OR `//i`).",
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

            // Call Order & Duplicate|Conflict Validation Errors
            'DuplicateNonceKeyName'           => "Duplicate Nonce Key Name in {$optionalCtx}. Review/change the already Valid Nonce Key Name ",
            'DuplicateRouteAliasName'           => "Duplicate Route Alias Name in {$optionalCtx}. Review/change the already Valid Configuration first defined in ",
            'DuplicateRouteConflict' => "Duplicate Route Conflict in Valid Formatted Route in {$optionalCtx} ",
            'DuplicateCallInvalid'              => "Duplicate Call to {$optionalCtx}. Review the already Invalid Configuration.",
            'DuplicateCallValid'                => "Duplicate Call to {$optionalCtx}. Review/change the already Valid Configuration.",
            'DuplicateParamGlobal' => "Duplicate Global Param Rule in {$optionalCtx}. Review/change the already Valid Configuration.",
            'DuplicateParamMethod' => "Duplicate Method Param Rule in {$optionalCtx}. Review/change the already Valid Configuration.",
            'DuplicateParamRoute' => "Duplicate Route Param Rule in {$optionalCtx}. Review/change the already Valid Configuration.",
            'ConflictNoneSourceInCSP' => "Invalid CSP Configuration in {$optionalCtx}: Source `'none'` must always be used isolated for a given CSP Directive. More than one Source is used.",
            'ConflictRouteParam' => "Route Parameter in Conflict in {$optionalCtx}:",
            'ConflictRemovePipedHeader' => "Conflicting Calls in {$optionalCtx}: cannot set `Remove a Header` that was first configured as `Pipe a Header`.",
            'ConflictPipeRemovedHeader' => "Conflicting Calls in {$optionalCtx}: cannot set `Pipe a Header` that was first configured as `Remove a Header` .",
            'ConflictingConfiguration'           => "Valid Configuration (`{$optionalCtx}`) is already set and CANNOT be overridden, only changed manually.",
            'ConflictingExcludeMWWithAlreadyPipedMW' => "Conflicting Middlewares in {$optionalCtx}: cannot reference the same Middleware(s) in `setExcludeMiddleware()` and `pipeMiddleware` in the same Route. Choose Middlewares piped in a given `<METHOD>()` and/or `CONFIG()`.",
            'ConflictingPipeMiddlewareWithAlreadyExcludeMW' => "Conflicting Middlewares in {$optionalCtx}: cannot reference the same Middleware(s) in `pipeMiddleware()` and `setExcludeMiddleware()` in the same Route. Choose Middlewares piped in a given `<METHOD>()` and/or `CONFIG()`.",
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
     * @param 'Global-setCompileFlag'|'Global-setGroupPipeUserdefined'|'Global-setGroupPipeRequest'|'Global-setGroupPipePostResponse'|'Global-setGroupPipeRoute'|'Global-setGroupPipeMiddlewares'|'Global-setINI_SET'|'Global-setNonces'|'Global-setCSP'|'Global-setSRIInternal'|'Global-setSRIExternal'|'Global-setNoRouteMatchPage'|'Global-setNoRouteMatchJSON'|'Global-setNoRouteMatchText'|'Global-setNoRouteMatchCallback'|'Global-setDefaultRegisteredShutdownHandler'|'Global-setDefaultExceptionHandler'|'Global-setDefaultErrorHandler'|'Global-setDefaultURI_NormalizerHandler'|'Global-setDefaultKernelHandler'|'Global-setBaseURLLocal'|'Global-setBaseURLOnline'|'Global-setBaseURLHost'|'Global-setBaseURLUri'|'Global-setSessionDriver'|'Global-setSessionCookieOptions'|'Global-setSessionCookieName'|'Global-setSessionCookieLifetime'|'Global-setSessionCookiePath'|'Global-setSessionCookieDomain'|'Global-setSessionCookieSecure'|'Global-setSessionCookieHTTPOnly'|'Global-setSessionCookieSameSite'|'Global-setUseFunkPHPOnline'|'Global-setUseHTTPS'|'Global-setUseVendor'|'Global-setParamRule'|'Global-pipeHeader'|'Global-removeHeader'|'Global-pipeMiddleware'|'Global-pipeRequestFunction'|'Global-pipePostResponseFunction'|'Method-setNoRouteMatch'|'Method-setNoRouteMatchPage'|'Method-setNoRouteMatchJson'|'Method-setNoRouteMatchText'|'Method-setNoRouteMatchCallback'|'Method-setNonces'|'Method-setCSP'|'Method-setRateLimiting'|'Method-pipeMiddleware'|'Method-pipeHeader'|'Method-removeHeader'|'Method-setParamRule'|'Method-route'|'Route-setAlias'|'Route-setRateLimiting'|'Route-setCache'|'Route-setNonces'|'Route-pipeMiddleware'|'Route-pipeFunction'|'Route-pipeResponse'|'Route-pipeSQL'|'Route-pipeQuery'|'Route-pipeValidation'|'Route-setExcludeMiddleware'|'Route-setExcludeHeaders'|'Route-setParamRule'|'Route-setCSP'|'Route-pipeHeader'|'Route-removeHeader'|'Route-route' $errType
     * @param 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'HEAD'|null $method
     * @param string|null $route
     *
     */
    private function setErr(string $errMsg, string $errType = '', ?string $method = null, ?string $route = null)
    {
        $validErrTypes = [
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
            'Global-setDefaultRegisteredShutdownHandler',
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
            'Global-pipeHeader',
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
            'Method-pipeHeader',
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
            'Route-setExcludeMiddleware',
            'Route-setExcludeHeaders',
            'Route-setParamRule',
            'Route-setCSP',
            'Route-pipeHeader',
            'Route-removeHeader',
            'Route-route',
        ];
        $validMethodTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];
        // No error (or valid) type
        if (!is_string($errType) || trim($errType) === '' || !in_array($errType, $validErrTypes)) {
            $this->errors[] = ['type' => 'internal', 'err' => 'Invalid \$type (Error Type) Value in `class C->setErr()` when setting Error:\'`' . $errMsg . '`\' Report this found bug/issue to the Official FunkPHP Repositories. Choose a `Valid Error Type` from: ' . $this->joinArray($validErrTypes), 'method' => $method, 'route' => $route];
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
            $this->errors[] = ['type' => 'internal', 'err' => 'Invalid \$route Value in `class C->setErr()`: must be provided when Error Type starts with `Route-`. Report this found bug/issue to the Official FunkPHP Repositories.', 'method' => $method, 'route' => $route];
            return;
        }
        $this->errors[] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
    }
    // Join array with wrapped `` and comma
    private function joinArray(array $array = [])
    {
        return '`' . join('`, `', $array) . '`';
    }

    // ->config()
    // and can jump to->pipesRequest(),->pipesPostResponse() or ->routes()
    public function CONFIG(): FunkConfig
    {
        $this->FunkPHPTextArray[] = "->CONFIG()";
        return $this->configScope ??= new FunkConfig($this);
    }
    // ->routes() | gives access to:->GET(),->POST(),->PATCH(),->PUT(),->DELETE()
    // and can jump back to ->config()
    public function ROUTES(): FunkRoutes
    {
        $this->FunkPHPTextArray[] = "->ROUTES()";
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

    /* !!! GLOBAL/CONFIG() BATCHES FUNCTIONS !!! */
    /* set<BOOLEAN_VARIANTS_OPTIONS-FunkPHPOnline,UseHTTPS,UseVendor> Global */
    private function batchSetCompileFlag(string $flag)
    {
        [$ctx, $ctxVals] = $this->setCtx('setCompileFlag', "CONFIG()", $flag);
        $validFlags = [
            'NO_WARNINGS_ALLOWED',
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
            $this->invalidBatches['config']['compileFlags'] = $flag;
            return;
        }
        $this->compileFlags[$flag] = true;
    }

    private function batchSetFunkPHPOnlineGlobal(bool $trueOrFalse)
    {
        [$ctx, $ctxVals] = $this->setCtx('setUseFunkPHPOnline', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['FUNKPHP_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setUseFunkPHPOnline');
            return;
        }
        if (isset($this->validBatches['config']['FUNKPHP_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setUseFunkPHPOnline');
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
        [$ctx, $ctxVals] = $this->setCtx('setUseHTTPS', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['USE_HTTPS'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setUseHTTPS');
            return;
        }
        if (isset($this->validBatches['config']['USE_HTTPS'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setUseHTTPS');
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
        [$ctx, $ctxVals] = $this->setCtx('setUseVendor', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['USE_VENDOR'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setUseVendor');
            return;
        }
        if (isset($this->validBatches['config']['USE_VENDOR'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setUseVendor');
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

    /* setUseDefault<Register,Exception,Error,UriNormalizer,In-builtKernel-UserDefinedFunctions> Global */
    private function batchSetDefaultRegisteredShutdownFunctionGlobal(string $userDefinedFunction)  // DEFAULT REGISTER SHUTDOWN HANDLER
    {
        [$ctx, $ctxVals] = $this->setCtx('setDefaultRegisteredShutdownHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_REGISTER_SHUTDOWN_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setDefaultRegisteredShutdownHandler');
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Global-setDefaultRegisteredShutdownHandler');
            $this->invalidBatches['config']['DEFAULT_REGISTER_SHUTDOWN_HANDLER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result.";
            $this->setErr($err, 'Global-setDefaultRegisteredShutdownHandler');
            $this->invalidBatches['config']['DEFAULT_REGISTER_SHUTDOWN_HANDLER'] = $userDefinedFunction;
            return;
        }
        // FN already in array of chained Register Shutdown FNs?
        if (in_array($userDefinedFunction, $this->validBatches['config']['REGISTERED_SHUTDOWN_HANDLERS'] ?? [], true)) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyInArray', $ctxVals) . $this->joinArray($this->validBatches['config']['REGISTERED_SHUTDOWN_HANDLERS'] ?? ['***EMPTY***']);
            $this->setErr($err, 'Global-setDefaultRegisteredShutdownHandler');
            return;
        }
        // Prepare Config Functions.php File I/O if needed
        // assuming ROOT_FOLDER constant exists first!
        if (!$this->rootFolderExistOrSetError()) return;
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions');
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunction, $ctxVals);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Global-setDefaultRegisteredShutdownHandler');
            $this->invalidBatches['config']['DEFAULT_REGISTER_SHUTDOWN_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_REGISTER_SHUTDOWN_HANDLER'][] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultRegisteredShutdownHandler()";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultRegisteredShutdownHandler()";
    }
    private function batchSetDefaultExceptionHandlerGlobal(string $userDefinedFunction) // DEFAULT EXCEPTION HANDLER
    {
        [$ctx, $ctxVals] = $this->setCtx('setDefaultExceptionHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setDefaultExceptionHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setDefaultExceptionHandler');
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
        [$ctx, $ctxVals] = $this->setCtx('setDefaultErrorHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setDefaultErrorHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setDefaultErrorHandler');
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
        [$ctx, $ctxVals] = $this->setCtx('setDefaultURI_NormalizerHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setDefaultURI_NormalizerHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals),  'Global-setDefaultURI_NormalizerHandler');
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
        [$ctx, $ctxVals] = $this->setCtx('setDefaultKernelHandler', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setDefaultKernelHandler');
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setDefaultKernelHandler');
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchPage', "CONFIG()", $PageFileName);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setNoRouteMatchPage');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setNoRouteMatchPage');
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchJSON', "CONFIG()", $data, $statusCode);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setNoRouteMatchJSON');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setNoRouteMatchJSON');
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchText', "CONFIG()", $message, $statusCode);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setNoRouteMatchText');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setNoRouteMatchText');
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchCallback', "CONFIG()", $userDefinedFunctionName);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setNoRouteMatchCallback');
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setNoRouteMatchCallback');
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
    private function batchSetDefaultBaseURLLocalGlobal(string $httpsPath)
    {
        [$ctx, $ctxVals] = $this->setCtx('setBaseURLLocal', "CONFIG()", $httpsPath);
        if (isset($this->invalidBatches['config']['BASEURL_LOCAL'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setBaseURLLocal');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_LOCAL'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setBaseURLLocal');
            return;
        }
        if (
            !is_string($httpsPath) || trim($httpsPath) === ''
            || !preg_match('/^http:\/\//', $httpsPath)
        ) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringSTARTWithHTTP', $ctxVals), 'Global-setBaseURLLocal');
            $this->invalidBatches['config']['BASEURL_LOCAL'] = $httpsPath;
            return;
        }
        $this->validBatches['config']['BASEURL_LOCAL'] = $httpsPath;
    }
    private function batchSetDefaultBaseURLOnlineGlobal(string $httpsPath)
    {
        [$ctx, $ctxVals] = $this->setCtx('setBaseURLLocal', "CONFIG()", $httpsPath);
        if (isset($this->invalidBatches['config']['BASEURL_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setBaseURLOnline');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setBaseURLOnline');
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
        [$ctx, $ctxVals] = $this->setCtx('setBaseURLHost', "CONFIG()", $hostNameLocally);
        if (isset($this->invalidBatches['config']['BASEURL_HOST'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setBaseURLHost');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_HOST'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setBaseURLHost');
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
        [$ctx, $ctxVals] = $this->setCtx('setBaseURLHost', "CONFIG()", $localURI);
        if (isset($this->invalidBatches['config']['BASEURL_URI'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setBaseURLUri');
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_URI'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setBaseURLUri');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieOptions', "CONFIG()", $SessionCookieOptions);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieOptions');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieOptions');
            return;
        }
        $allowedKeys = [
            'SESSION_NAME',
            'SESSION_LIFETIME',
            'SESSION_PATH',
            'SESSION_DOMAIN',
            'SESSION_SECURE',
            'SESSION_HTTPONLY',
            'SESSION_SAMESITE',
        ];
        if (empty($SessionCookieOptions) || array_is_list($SessionCookieOptions)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array with these Session Cookie Options:`" . implode('`, `', $allowedKeys) . "`.", 'Global-setSessionCookieOptions');
            $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
            return;
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
            $this->validBatches['config']['SESSION']['COOKIES'][$k] = $v;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = true;
    }

    /* setSESSIONDriver Global & then setSESSION_COOKIE<VARIANTS> Global */
    private function batchSetDefaultSessionDriverGlobal(string $filesOrRedisOrSomethingElse = 'files')
    {
        [$ctx, $ctxVals] = $this->setCtx('setSessionDriver', "CONFIG()", $filesOrRedisOrSomethingElse);
        if (isset($this->invalidBatches['config']['SESSION']['driver'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionDriver');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['driver'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionDriver');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieName', "CONFIG()", $sessionCookieName);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieName');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieName');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieLifetime', "CONFIG()", $sessionCookieLifetime);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieLifetime');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieLifetime');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookiePath', "CONFIG()", $sessionCookiePath);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookiePath');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookiePath');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieDomain', "CONFIG()", $sessionCookieDomain);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieDomain');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieDomain');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieSecure', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieSecure');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieSecure');
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

        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieHTTPOnly', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieHTTPOnly');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieHTTPOnly');
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
        [$ctx, $ctxVals] = $this->setCtx('setSessionCookieSameSite', "CONFIG()", $LaxOrStrict);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSessionCookieSameSite');
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSessionCookieSameSite');
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
        [$ctx, $ctxVals] = $this->setCtx('setINI_SET', "CONFIG()", $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue);
        if (isset($this->invalidBatches['config']['setINI_SET'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setINI_SET');
            return;
        }
        if (isset($this->validBatches['config']['setINI_SET'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setINI_SET');
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

    /* setGrouped<VARIANTS> Global */
    private function batchSetGroupedPipeUserDefined(string $groupName, string ...$userDefFNS)
    {
        [$ctx, $ctxVals] = $this->setCtx('setGroupPipeUserdefined', "CONFIG()", $groupName, ...$userDefFNS);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setGroupPipeUserdefined');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setGroupPipeUserdefined');
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
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals), 'Global-setGroupPipeUserdefined');
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
        [$ctx, $ctxVals] = $this->setCtx('setGroupPipeRequest', "CONFIG()", $groupName, ...$RequestFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setGroupPipeRequest');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_REQUEST'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setGroupPipeRequest');
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
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals), 'Global-setGroupPipeRequest');
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
        [$ctx, $ctxVals] = $this->setCtx('setGroupPipePostResponse', "CONFIG()", $groupName, ...$PostResponseFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setGroupPipePostResponse');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setGroupPipePostResponse');
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
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals), 'Global-setGroupPipePostResponse');
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
        [$ctx, $ctxVals] = $this->setCtx('setGroupPipeRoute', "CONFIG()", $groupName, ...$RoutePipeFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setGroupPipeRoute');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_ROUTES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setGroupPipeRoute');
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
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals), 'Global-setGroupPipeRoute');
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
        [$ctx, $ctxVals] = $this->setCtx('setGroupPipeMiddlewares', "CONFIG()", $groupName, ...$middlewareFNs);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setGroupPipeMiddlewares');
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setGroupPipeMiddlewares');
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
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals), 'Global-setGroupPipeMiddlewares');
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
        [$ctx, $ctxVals] = $this->setCtx('setParamRule', "CONFIG()", $param, $regex, $defaultParamValueOnRegexMismatch);
        if (isset($this->invalidBatches['paramRules']['config'][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setParamRule');
            return;
        }
        if (isset($this->validBatches['config']['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setParamRule');
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
    }

    /* setCSP<VARIANTS> & setNonces Global */
    private function batchSetNoncesGlobal(string ...$noncesReferenceKeys)
    {
        [$ctx, $ctxVals] = $this->setCtx('setNonces', "CONFIG()", ...$noncesReferenceKeys);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['nonces']['config'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setNonces');
            return;
        }
        if (isset($this->validBatches['config']['nonces'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setNonces');
            return;
        }
        if (empty($noncesReferenceKeys)) {
            $this->setErr($this->getErr('InvalidNonceKeys', $ctxVals), 'Global-setNonces');
            $this->invalidBatches['nonces']['config'] = $noncesReferenceKeys;
            return;
        }
        $cleanedKeys = [];
        foreach ($noncesReferenceKeys as $key) {
            if (!is_string($key)) {
                $this->setErr($this->getErr('InvalidNonceKeys', $ctxVals), 'Global-setNonces');
                $this->invalidBatches['nonces']['config'] = $noncesReferenceKeys;
                return;
            }
            $trimmed = trim($key);
            if ($trimmed === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $trimmed)) {
                $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Global-setNonces');
                $this->invalidBatches['nonces']['config'] = $noncesReferenceKeys;
                return;
            }
            if (in_array($trimmed, $cleanedKeys)) {
                $this->setErr($this->getErr('DuplicateNonceKeyName', $ctxVals) . "`{$key}`", 'Global-setNonces');
                $this->invalidBatches['nonces']['config'] = $noncesReferenceKeys;
            }
            $cleanedKeys[] = $trimmed;
        }
        $this->validBatches['config']['nonces'] = $cleanedKeys;
    }
    private function batchSetCSPGlobal(string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx('setCSP', "CONFIG()", $directive, ...$sources);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['config'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setCSP');
            return;
        }
        if (isset($this->validBatches['config']['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setCSP');
            return;
        }
        $allowedDirectives = [
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
            'report-uri',
            'report-to'
        ];
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
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Global-setCSP');
                $this->invalidBatches['csp']['config'][$directive] = $sources;
                return;
            }
            $trimmed = trim($source);
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
        $this->validBatches['config']['csp'][$directive] = $formattedSources;
    }

    /* setSRI Internal&External Global */

    /* setSRIInternal & setSRIExternal - GLOBAL */
    private function batchSetSRIInternalGlobal(array $internalSRI)
    {
        [$ctx, $ctxVals] = $this->setCtx('setSRIInternal', "CONFIG()", $internalSRI);
        if (isset($this->invalidBatches['global_sris']['internal']['config'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSRIInternal');
            return;
        }
        if (isset($this->validBatches['config']['global_sris']['internal'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSRIInternal');
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
        [$ctx, $ctxVals] = $this->setCtx('setSRIExternal', "CONFIG()", $externalSRI);
        if (isset($this->invalidBatches['global_sris']['external']['config'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-setSRIExternal');
            return;
        }
        if (isset($this->validBatches['config']['global_sris']['external'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-setSRIExternal');
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
        [$ctx, $ctxVals] = $this->setCtx('removeHeader', "CONFIG()", $header_to_remove);
        if (isset($this->invalidBatches['headers']['config']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-removeHeader');
            return;
        }
        if (isset($this->validBatches['config']['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-removeHeader');
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
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
    private function batchPipeHeaderGlobal(string $header)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeHeader', "CONFIG()", $header);
        if (isset($this->invalidBatches['headers']['config']['add'][strtolower($header)])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Global-pipeHeader');
            return;
        }
        // Forbid possible CRLF injection
        if (str_contains($header, "\r") || str_contains($header, "\n")) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Global-pipeHeader');
            $this->invalidBatches['headers']['config']['add'][strtolower($header)] = true;
            return;
        }
        // Must be two parts after splitted on ":"
        $parts = explode(':', $header, 2);
        if (count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '') {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Global-pipeHeader');
            $this->invalidBatches['headers']['config']['add'][strtolower($header)] = true;
            return;
        }
        // Now prepare header to store but first check if it already exists
        $headerName  = trim($parts[0]);
        $headerValue = trim($parts[1]);
        $lowerHeader = strtolower($headerName);
        if (isset($this->validBatches['config']['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Global-pipeHeader');
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['config']['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Global-pipeHeader');
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
        [$ctx, $ctxVals] = $this->setCtx('pipeMiddleware', "CONFIG()", $middleware);
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
    }
    private function batchPipeRequestFunctionGlobal(string $fileFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeRequestFunction', "CONFIG()", $fileFunctionName);
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
        [$ctx, $ctxVals] = $this->setCtx('pipePostResponseFunction', "CONFIG()", $fileFunctionName);
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
    private function batchSetRateLimitingMethod(string $method, array $rateLimitingOptions) {}

    //METHOD: No Match for this https method, if none is set, it falls back to the global versions.
    private function batchSetNoRouteMatchPageMethod(string $method, string $PageFileName, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchPage', "CONFIG()->{$method}()", $PageFileName);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setNoRouteMatchPage', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setNoRouteMatchPage', $method);
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchJSON', "CONFIG()->{$method}()", $data, $statusCode);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setNoRouteMatchJson', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setNoRouteMatchJson', $method);
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchText', "CONFIG()->{$method}()", $message, $statusCode);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setNoRouteMatchText', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setNoRouteMatchText', $method);
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
        [$ctx, $ctxVals] = $this->setCtx('setNoRouteMatchCallback', "CONFIG()->{$method}()", $userDefinedFunctionName);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setNoRouteMatchCallback', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setNoRouteMatchCallback', $method);
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
        [$ctx, $ctxVals] = $this->setCtx('setParamRule', "ROUTES()->{$method}()", $param, $regex, $defaultParamValueOnRegexMismatch);
        if (isset($this->invalidBatches['paramRules']['methods'][$method][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setParamRule', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setParamRule', $method);
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
    }

    //METHOD: setNonces & setCSP
    private function batchSetNoncesMethod(string $method, string ...$noncesReferenceKeys)
    {
        [$ctx, $ctxVals] = $this->setCtx('setNonces', "ROUTES()->{$method}()", ...$noncesReferenceKeys);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['nonces']['methods'][$method])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setNonces', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['nonces'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setNonces', $method);
            return;
        }
        if (empty($noncesReferenceKeys)) {
            $this->setErr($this->getErr('InvalidNonceKeys', $ctxVals), 'Method-setNonces', $method);
            $this->invalidBatches['nonces']['methods'][$method] = $noncesReferenceKeys;
            return;
        }
        $cleanedKeys = [];
        foreach ($noncesReferenceKeys as $key) {
            if (!is_string($key)) {
                $this->setErr($this->getErr('InvalidNonceKeys', $ctxVals), 'Method-setNonces', $method);
                $this->invalidBatches['nonces']['methods'][$method] = $noncesReferenceKeys;
                return;
            }
            $trimmed = trim($key);
            if ($trimmed === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $trimmed)) {
                $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Method-setNonces', $method);
                $this->invalidBatches['nonces']['methods'][$method] = $noncesReferenceKeys;
                return;
            }
            if (in_array($trimmed, $cleanedKeys)) {
                $this->setErr($this->getErr('DuplicateNonceKeyName', $ctxVals) . "`{$key}`", 'Method-setNonces', $method);
                $this->invalidBatches['nonces']['methods'][$method] = $noncesReferenceKeys;
            }
            $cleanedKeys[] = $trimmed;
        }
        $this->validBatches['methods'][$method]['nonces'] = $cleanedKeys;
    }
    private function batchSetCSPMethod(string $method, string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx('setCSP', "ROUTES()->{$method}()", $directive, ...$sources);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['methods'][$method][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-setCSP', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-setCSP', $method);
            return;
        }
        $allowedDirectives = [
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
            'report-uri',
            'report-to'
        ];
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
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Method-setCSP', $method);
                $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                return;
            }
            $trimmed = trim($source);
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
        $this->validBatches['methods'][$method]['csp'][$directive] = $formattedSources;
    }

    /*METHOD: removeHeader & pipeHeader */
    private function batchPipeHeaderMethod(string $method, string $header)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeHeader', "ROUTES()->{$method}()", $header);
        if (isset($this->invalidBatches['headers']['methods'][$method]['add'][$header])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-pipeHeader', $method);
            return;
        }
        // Forbid possible CRLF injection
        if (str_contains($header, "\r") || str_contains($header, "\n")) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Method-pipeHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$header] = true;
            return;
        }
        // Must be two parts after splitted on ":"
        $parts = explode(':', $header, 2);
        if (count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '') {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Method-pipeHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$header] = true;
            return;
        }
        // Now prepare header to store but first check if it already exists
        $headerName  = trim($parts[0]);
        $headerValue = trim($parts[1]);
        $lowerHeader = strtolower($headerName);
        if (isset($this->validBatches['methods'][$method]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-pipeHeader', $method);
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['methods'][$method]['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Method-pipeHeader', $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Method level (->config()->ROUTES()-><METHOD>)
        $this->validBatches['methods'][$method]['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }
    private function batchRemoveHeaderMethod(string $method, string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx('removeHeader', "ROUTES()->{$method}()", $header_to_remove);
        if (isset($this->invalidBatches['headers']['methods'][$method]['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-removeHeader', $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Method-removeHeader', $method);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
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
        [$ctx, $ctxVals] = $this->setCtx('pipeMiddleware', "ROUTES()->$method()", $middleware);
        if (isset($this->invalidBatches['middlewares']['methods'][$method][$middleware])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Method-pipeMiddleware');
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Method-pipeMiddleware');
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
            $this->setErr($fatalError, 'Method-pipeMiddleware');
            $this->invalidBatches['middlewares']['methods'][$method][$middleware] = true;
            return;
        }
        // Pipe Global MW when all OK!
        $this->validBatches['methods'][$method]['middlewares'][] = $middleware;
    }

    /* !!! ROUTE/ROUTES()-><METHOD>()->route()-> BATCHES FUNCTIONS !!! */
    //ROUTE:Batching New Route `->route("/route", $optionalParamRules as an array)`
    private function batchNewRoute(string $method, string $route)
    {
        [$ctx, $ctxVals] = $this->setCtx('route', "ROUTES()->{$method}()->route('{$route}')", $route);
        // Check if the associated $method$route is in the InvalidBatches first
        // OR if it is already as an invalid alias OR a valid alias already exists
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-route', $method, $route);
            return;
        }
        // Does $route already exist as a valid one? (meaning it was formatted correctly but duplicate)
        if (isset($this->validBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-route', $method, $route);
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
        $placeHolderRoute = '';
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
                            $this->setErr($this->getErr('ConflictRouteParam', $ctxVals) . " Parameter `:{$paramName}` conflicts with Locked Parameter `:{$lockedParamName}` first defined in `{$this->cached['placeholderParamContexts'][$contextKey]['first']}`", 'Route-route', $method, $route);
                            $this->invalidBatches['routes'][$method][$route] = true;
                            return;
                        }
                    } else {
                        // Lock this parameter name globally for this parent path context
                        $this->cached['placeholderParamContexts'][$contextKey] = ['param' => $paramName, 'first' => "->ROUTES()->{$method}()->route('{$route}')"];
                    }
                    $currentParentContext .= '/:PARAM';
                } else {
                    $currentParentContext .= '/' . $segment;
                }
            }
            $routeHasParams = $paramMatches[1]; // Store any params used
            $placeHolderRoute = preg_replace('/:([a-z0-9_-]+)/', ':PARAM', $route);
        }
        // If it is a dynamic param route we check if it already exists given same
        // URI segment levels for <METHOD>/:PARAM1|STATIC1/:PARAM2|STATIC and so on
        // POSSIBLE DEAD CODE SO OUTCOMMENTED FOR NOW (UNTIL IT NO LONGER IS SO SAY !:P):
        // if ($placeHolderRoute !== '') {
        //     if (isset($this->cached['placeholderRoutes'][$method][$placeHolderRoute])) {
        //         $this->setErr($this->getErr('ConflictRouteParam', $ctxVals) . ' with (defined first):`' . $this->cached['placeholderRoutes'][$method][$placeHolderRoute] . '`.', $this->errors['routes'][$method]);
        //         return;
        //     } else {
        //         $this->cached['placeholderRoutes'][$method][$placeHolderRoute] = "->ROUTES()->{$method}()->route('{$route}')";
        //     }
        // }
        // Add Valid String Formatted METHOD/Route now; in compilation it will be checked for
        // conflicting URI segments with other routes as we do not know which order they are added!
        $this->validBatches['routes'][$method][$route] = ['hasParams' => $routeHasParams, 'response' => null, 'pipes' => [], 'middlewares' => [], 'excludeMiddleware' => null, 'headers' => ['add' => null, 'remove' => null], 'excludeHeaders' => null];
    }

    //ROUTE: Set & New Batches for ROUTES! (so ->routes()-><Method>()->route()->set|pipe<What>)
    private function batchSetAliasRoute(string $method, string $route, string $alias)
    {
        [$ctx, $ctxVals] = $this->setCtx('setAlias', "ROUTES()->{$method}()->route('{$route}')", $alias);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setAlias', $method, $route);
            return;
        }
        // Check if it exists already in invalid or valid batch
        if (isset($this->invalidBatches['aliases'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-setAlias', $method, $route);
            return;
        }
        // Alias formatting with typical alphanumerals plus dot-notation support
        if ($alias === '' || !preg_match('/^[a-zA-Z0-9_.-]+$/', $alias)) {
            $this->setErr($this->getErr('InvalidRouteAliasName', $ctxVals), 'Route-setAlias', $method, $route);
            $this->invalidBatches['aliases'][$method][$route] = $alias;
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['alias'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-setAlias', $method, $route);
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
        $this->cached['routeAliases'][$alias] = "->ROUTES()->{$method}()->route('{$route}')";
        $this->validBatches['routes'][$method][$route]['alias'] = $alias;
    }

    //ROUTE: SetParamRule
    private function batchSetParamRuleRoute(string $method, string $route, string $param, string $regex, $defaultParamValueOnRegexMismatch = null)
    {
        [$ctx, $ctxVals] = $this->setCtx('setParamRule', "ROUTES()->{$method}()->route('{$route}')", $param, $regex, $defaultParamValueOnRegexMismatch);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setParamRule', $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['paramRules']['routes'][$method][$route][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-setParamRule', $method, $route);
            return;
        }
        if (isset($this->validBatches['paramRules']['routes'][$method][$route][$param])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-setParamRule', $method, $route);
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Route-setParamRule', $method, $route);
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
    private function batchSetRateLimitingRoute(string $method, string $route, array $rateLimitingOptions) {}
    private function batchSetCacheRoute(string $method, string $route, array $cacheOptions) {}

    /*ROUTE: setNoncesRoute & setCSP<VARIANTS> */
    private function batchSetNoncesRoute(string $method, $route, string ...$noncesReferenceKeys)
    {
        [$ctx, $ctxVals] = $this->setCtx('setNonces', "ROUTES()->{$method}()->route('{$route}')", ...$noncesReferenceKeys);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setNonces', $method, $route);
            return;
        }
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['nonces']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-setNonces', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['nonces'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-setNonces', $method, $route);
            return;
        }
        if (empty($noncesReferenceKeys)) {
            $this->setErr($this->getErr('InvalidNonceKeys', $ctxVals), 'Route-setNonces', $method, $route);
            $this->invalidBatches['nonces']['routes'][$method][$route] = $noncesReferenceKeys;
            return;
        }
        $cleanedKeys = [];
        foreach ($noncesReferenceKeys as $key) {
            if (!is_string($key)) {
                $this->setErr($this->getErr('InvalidNonceKeys', $ctxVals), 'Route-setNonces', $method, $route);
                $this->invalidBatches['nonces']['routes'][$method][$route] = $noncesReferenceKeys;
                return;
            }
            $trimmed = trim($key);
            if ($trimmed === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $trimmed)) {
                $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Route-setNonces', $method, $route);
                $this->invalidBatches['nonces']['routes'][$method][$route] = $noncesReferenceKeys;
                return;
            }
            if (in_array($trimmed, $cleanedKeys)) {
                $this->setErr($this->getErr('DuplicateNonceKeyName', $ctxVals) . "`{$key}`", 'Route-setNonces', $method, $route);
                $this->invalidBatches['nonces']['routes'][$method][$route] = $noncesReferenceKeys;
            }
            $cleanedKeys[] = $trimmed;
        }
        $this->validBatches['routes'][$method][$route]['nonces'] = $cleanedKeys;
    }
    /*ROUTE: setCSPRoute */
    private function batchSetCSPRoute(string $method, string $route, string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx('setCSP', "ROUTES()->{$method}()->route('{$route}')", $directive, ...$sources);
        // Route must be valid first
        if (isset($this->invalidBatches['csp']['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setCSP', $method, $route);
            return;
        }
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['routes'][$method][$route][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-setCSP', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-setCSP', $method, $route);
            return;
        }
        $allowedDirectives = [
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
            'report-uri',
            'report-to'
        ];
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
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Route-setCSP', $method, $route);
                $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                return;
            }
            $trimmed = trim($source);
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
        $this->validBatches['routes'][$method][$route]['csp'][$directive] = $formattedSources;
    }

    /*ROUTE: pipeMiddleware, pipeFunction, pipeResponse, pipeSQL, pipeQuery & pipeValidation */
    private function batchPipeMiddlewareRoute(string $method, string $route, string $middleware)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeMiddleware', "ROUTES()->{$method}()->route('{$route}')", $middleware);
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
        // Pipe Global MW when all OK!
        $this->validBatches['routes'][$method][$route]['middlewares'][] = $middleware;
    }

    private function batchPipeFunctionRoute(string $method, string $route, string $fileFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeFunction', "ROUTES()->{$method}()->route('{$route}')", $fileFunctionName);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeFunction', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Route-pipeFunction', $method, $route);
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
        [$ctx, $ctxVals] = $this->setCtx('pipeResponse', "ROUTES()->{$method}()->route('{$route}')", $typeOfResponse);
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
        [$ctx, $ctxVals] = $this->setCtx('pipeSQL', "ROUTES()->{$method}()->route('{$route}')", $sqlFileFunction);
    }
    private function batchPipeQueryRoute(string $method, string $route, string $queryFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeQuery', "ROUTES()->{$method}()->route('{$route}')", $queryFileFunction);
    }
    private function batchPipeValidationRoute(string $method, string $route, string $validationFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeValidation', "ROUTES()->{$method}()->route('{$route}')", $validationFileFunction);
    }

    /*ROUTE: excludeMiddleware & excludeHeaders */
    private function batchExcludeMiddlewareRoute(string $method, string $route, string ...$middlewareToExclude)
    {
        [$ctx, $ctxVals] = $this->setCtx('setExcludeMiddleware', "ROUTES()->{$method}()->route('{$route}')", ...$middlewareToExclude);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-setExcludeMiddleware', $method, $route);
            return;
        }
        if (isset($this->invalidBatches['excludeMiddleware']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-setExcludeMiddleware', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['excludeMiddleware'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctx), 'Route-setExcludeMiddleware', $method, $route);
            return;
        }
        // Check that all Middlewares exist; later compile() will also
        // check they exist on correct associated sub-route-depth!
        // and that they do not clash with piped middlewares on the same route as that is conflicting
        foreach ($middlewareToExclude as $middleware) {
            $middleware = strtolower(trim($middleware));
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($middleware)) {
                $this->setErr($this->getErr('InvalidMiddlewareFunctionName', $ctx), 'Route-setExcludeMiddleware', $method, $route);
                return;
            }
            if (in_array($middleware, ($this->validBatches['routes'][$method][$route]['middlewares'] ?? []), true)) {
                $this->setErr($this->getErr('ConflictingExcludeMWWithAlreadyPipedMW', $ctxVals) . " Conflict: `->pipeMiddleware('{$middleware}')`.", 'Route-setExcludeMiddleware', $method, $route);
                return;
            }
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
            $fileData = $this->cached['files_pipes_middlewares'][$middleware] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares\\{$middleware}", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Route-setExcludeMiddleware', $method, $route);
                $this->invalidBatches['excludeMiddleware']['routes'][$method][$route] = true;
                return;
            }
        }
        // Add to excludeMiddleware when all OK!
        $this->validBatches['routes'][$method][$route]['excludeMiddleware'] = $middlewareToExclude;
    }
    private function batchExcludeHeadersRoute(string $method, string $route, string ...$headersToExclude)
    {
        [$ctx, $ctxVals] = $this->setCtx('excludeHeaders', "ROUTES()->{$method}()->route('{$route}')", ...$headersToExclude);
    }

    /*ROUTE: pipeHeader & removeHeader */
    /*ROUTE: setpipeHeaderRoute*/
    private function batchPipeHeaderRoute(string $method, string $route, string $header)
    {
        [$ctx, $ctxVals] = $this->setCtx('pipeHeader', "ROUTES()->{$method}()->route('{$route}')", $header);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-pipeHeader', $method, $route);
            return;
        }
        // Then check against valid/invalid batches
        if (isset($this->invalidBatches['headers']['routes'][$method][$route]['add'][$header])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-pipeHeader', $method, $route);
            return;
        }
        // Forbid possible CRLF injection
        if (str_contains($header, "\r") || str_contains($header, "\n")) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Route-pipeHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$header] = true;
            return;
        }
        // Must be two parts after splitted on ":"
        $parts = explode(':', $header, 2);
        if (count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '') {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Route-pipeHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$header] = true;
            return;
        }
        // Now prepare header to store but first check if it already exists
        $headerName  = trim($parts[0]);
        $headerValue = trim($parts[1]);
        $lowerHeader = strtolower($headerName);
        if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-pipeHeader', $method, $route);
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['routes'][$method][$route]['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Route-pipeHeader', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Route level (->config()->ROUTES()-><METHOD>-><ROUTE>)
        $this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }
    /*ROUTE: setRemoveHeaderRoute*/
    private function batchRemoveHeaderRoute(string $method, string $route, string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx('removeHeader', "ROUTES()->{$method}()->route('{$route}')", $header_to_remove);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route-removeHeader', $method, $route);
            return;
        }
        // Then check against invalid/valid batches
        if (isset($this->invalidBatches['headers']['routes'][$method][$route]['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Route-removeHeader', $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Route-removeHeader', $method, $route);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $headerName)) {
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
    private function compile()
    {
        // Attempt compiling FunkPHP and create the code
        // STEP 1: Check there are zero Invalid Batches and zero errors so far.
    }
    private function run()
    {
        // Run the valid compiled FunkPHP
        echo "TEST";
    }
}
/*
 * Class FunkPHP is the top level navigation in the IDE that "jumps" via method-chaining
 * between `->CONFIG()`,`->pipesRequest()`,`->pipesPostResponse()`,`->routes()`. It is
 * accessed via function FunkPHP() which then returns FunKPHP instance with class C as
 * private variable. Should be used as:`return FunkPH()-><config()|routes()>`
*/
class FunkPHP
{
    public function __construct(private C $c) {}
    // TOP LEVEL METHOD-CHAINED-BASED NAVIGATION
    public function CONFIG(): FunkConfig
    {
        return $this->c->config();
    }
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


    /* setCompileFlag - to set specific flags for compile() when it runs/executes */
    // "setAllowNoWarnings" is set here as valid flag!!! - remove that batchSetAllowNoWarnings FN !!!
    public function setCompileFlag(string $flag): self
    {
        $flag = strtoupper(trim($flag));
        $this->c->batch('batchSetCompileFlag', $flag);
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
    public function setGroupPipeRequest(string $groupName, string ...$pipeRequestFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeRequest', $groupName, ...$pipeRequestFNs);
        return $this;
    }
    public function setGroupPipePostResponse(string $groupName, string ...$pipePostReponseFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipePostResponse', $groupName, ...$pipePostReponseFNs);
        return $this;
    }
    public function setGroupPipeRoute(string $groupName, string ...$pipeRouteFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeRoute', $groupName, ...$pipeRouteFNs);
        return $this;
    }
    public function setGroupPipeMiddlewares(string $groupName, string ...$pipeMiddlewareFNs): self
    {
        $groupName = strtolower(trim($groupName));
        $this->c->batch('batchSetGroupedPipeMiddlewares', $groupName, ...$pipeMiddlewareFNs);
        return $this;
    }

    /* INI_SET() Setter - GLOBAL */
    public function setINI_SET(array $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue): self
    {
        $this->c->batch('batchSetINI_SETGlobal', $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue);
        return $this;
    }

    /* setNonces and setCSP<&Variants> - GLOBAL */
    public function setNonces(string ...$noncesReferenceKeys)
    {
        $this->c->batch('batchSetNoncesGlobal', ...$noncesReferenceKeys);
        return $this;
    }
    /**
     * Configures Content-Security-Policy (CSP) directives globally.
     *
     * Automatically wraps standard CSP keywords (e.g. 'self', 'none', 'unsafe-inline') in single quotes,
     * while preserving casing for hashes, nonces, and domains.
     *
     * @param 'default-src'|'script-src'|'script-src-elem'|'script-src-attr'|'style-src'|'style-src-elem'|'style-src-attr'|'img-src'|'font-src'|'connect-src'|'media-src'|'object-src'|'child-src'|'frame-src'|'worker-src'|'manifest-src'|'prefetch-src'|'base-uri'|'form-action'|'frame-ancestors'|'sandbox'|'plugin-types'|'navigate-to'|'report-uri'|'report-to' $sourceType
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

    /* setSRI<VARIANTS> - GLOBAL */
    public function setSRIInternal(array $internalSRI): self
    {
        $this->c->batch('batchSetSRIInternalGlobal', $internalSRI);
        return $this;
    }
    public function setSRIExternal(array $options): self
    {
        $this->c->batch('batchSetSRIExternalGlobal', $options);
        return $this;
    }

    /* setNoRouteMatch<Variants> - GLOBAL */
    public function setNoRouteMatchPage(string $PageFileName, int $statusCode = 404): self
    {
        $PageFileName = strtolower(trim($PageFileName));
        $this->c->batch('batchSetNoRouteMatchPageGlobal', $PageFileName, $statusCode);
        return $this;
    }
    public function setNoRouteMatchJSON(array|object $data, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchJsonGlobal', $data, $statusCode);
        return $this;
    }
    public function setNoRouteMatchText(string $message, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchTextGlobal', $message, $statusCode);
        return $this;
    }
    public function setNoRouteMatchCallback(callable|string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetNoRouteMatchCallbackGlobal', $userDefinedFunctionName);
        return $this;
    }

    /* setDefault<Variants_from_User_defined_Functions> */
    public function setDefaultRegisteredShutdownHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultRegisteredShutdownFunctionGlobal', $userDefinedFunctionName);
        return $this;
    }
    public function setDefaultExceptionHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultExceptionHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }
    public function setDefaultErrorHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultErrorHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }
    public function setDefaultURI_NormalizerHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultURINormalizerGlobal', $userDefinedFunctionName);
        return $this;
    }
    public function setDefaultKernelHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultHTTPSKernelDispatchHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }

    /* setBASEURL<Variants> - GLOBAL */
    public function setBaseURLLocal(string $httpsPath): self
    {
        $this->c->batch('batchSetDefaultBaseURLLocalGlobal', $httpsPath);
        return $this;
    }
    public function setBaseURLOnline(string $httpsPath): self
    {
        $this->c->batch('batchSetDefaultBaseURLOnlineGlobal', $httpsPath);
        return $this;
    }
    public function setBaseURLHost(string $hostNameLocally): self
    {
        $this->c->batch('batchSetDefaultBaseURLHostGlobal', $hostNameLocally);
        return $this;
    }
    public function setBaseURLUri(string $localURI): self
    {
        $this->c->batch('batchSetDefaultBaseURLUriGlobal', $localURI);
        return $this;
    }

    /* setSession<Driver&Cookie_Configs_For_it> - GLOBAL */

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
    /* This one is just to set all values immediately as an array instead of single ones */
    public function setSessionCookieOptions(array $sessionCookieOptions): self
    {
        $this->c->batch('batchSetDefaultSessionCookieOptionsGlobal', $sessionCookieOptions);
        return $this;
    }
    public function setSessionCookieName(string $sessionCookieName = 'fphp_id'): self
    {
        $this->c->batch('batchSetDefaultSessionCookieNameGlobal', $sessionCookieName);
        return $this;
    }
    public function setSessionCookieLifetime(int $sessionCookieLifetime = 28800): self
    {
        $this->c->batch('batchSetDefaultSessionCookieLifetimeGlobal', $sessionCookieLifetime);
        return $this;
    }
    public function setSessionCookiePath(string $sessionCookiePath = '/'): self
    {
        $this->c->batch('batchSetDefaultSessionCookiePathGlobal', $sessionCookiePath);
        return $this;
    }
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
    public function setUseFunKPHPOnline(bool $trueOrFalse): self
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

    /* setParamRule Globally/config() - GLOBAL */
    public function setParamRule(string $param, string $regex, $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleGlobal', $param, $regex, $defaultParamValueOnRegexMismatch);
        return $this;
    }

    /* pipeHeader Globally/config() - GLOBAL */
    public function pipeHeader(string $header): self
    {
        $this->c->batch('batchPipeHeaderGlobal', $header);
        return $this;
    }
    /* removeHeader Globally/config() - GLOBAL */
    public function removeHeader(string $header_to_remove): self
    {
        $header_to_remove = strtolower(trim($header_to_remove));
        $this->c->batch('batchRemoveHeaderGlobal', $header_to_remove);
        return $this;
    }
    /* pipeMiddleware Globally/config() - GLOBAL */
    public function pipeMiddleware(string $middleware): self
    {
        $middleware = strtolower(trim($middleware));
        $this->c->batch('batchPipeMiddlewareGlobal', $middleware);
        return $this;
    }
    /* pipeRequestFunction Globally/config() - GLOBAL */
    public function pipeRequestFunction(string $requestFunction): self
    {
        $requestFunction = strtolower(trim($requestFunction));
        $this->c->batch('batchPipeRequestFunctionGlobal', $requestFunction);
        return $this;
    }
    /* pipePostResponseFunction Globally/config() - GLOBAL */
    public function pipePostResponseFunction(string $postResponseFunction): self
    {
        $postResponseFunction = strtolower(trim($postResponseFunction));
        $this->c->batch('batchPipePostResponseFunctionGlobal', $postResponseFunction);
        return $this;
    }
    // Jump to ->routes() from FunkPHP->config()! - from GLOBAL
    public function ROUTES(): FunkRoutes
    {
        return $this->c->routes();
    }
}
/*
 * Class FunkRoutes() - accessed via FunkPHP()->routes() - contains references to all
 * typical method-based routes such as GET,POST,PUT,DELETE, and PATCH+HEAD!
 * Can also jump back to ->config()
*/
class FunkRoutes
{
    private array $methodInstances = [];
    public function __construct(private C $c) {}
    public function HEAD(): FunkMethod
    {
        $this->c->FunkPHPTextArray[] = "->HEAD()";
        return $this->methodInstances['HEAD'] ??= new FunkMethod($this->c, $this, 'HEAD');
    }
    public function GET(): FunkMethod
    {
        $this->c->FunkPHPTextArray[] = "->GET()";
        return $this->methodInstances['GET'] ??= new FunkMethod($this->c, $this, 'GET');
    }
    public function POST(): FunkMethod
    {
        $this->c->FunkPHPTextArray[] = "->POST()";
        return $this->methodInstances['POST'] ??= new FunkMethod($this->c, $this, 'POST');
    }
    public function PUT(): FunkMethod
    {
        $this->c->FunkPHPTextArray[] = "->PUT()";
        return $this->methodInstances['PUT'] ??= new FunkMethod($this->c, $this, 'PUT');
    }
    public function PATCH(): FunkMethod
    {
        $this->c->FunkPHPTextArray[] = "->PATCH()";
        return $this->methodInstances['PATCH'] ??= new FunkMethod($this->c, $this, 'PATCH');
    }
    public function DELETE(): FunkMethod
    {
        $this->c->FunkPHPTextArray[] = "->DELETE()";
        return $this->methodInstances['DELETE'] ??= new FunkMethod($this->c, $this, 'DELETE');
    }
    public function CONFIG(): FunkConfig
    {
        $this->c->FunkPHPTextArray[] = "->CONFIG()";
        return $this->c->config();
    }
}
/*
 * Class FunkMethod() - accessed via FunkPHP()->routes()-><METHOD>()
*/
class FunkMethod
{
    public function __construct(
        private C $c,
        private FunkRoutes $parent,
        private string $method
    ) {}
    public function setNoRouteMatch(array $options): self
    {
        $this->c->batch('batchSetNoRouteMatchMethod', $this->method, $options);
        return $this;
    }
    public function setNoRouteMatchPage(string $PageFileName, int $statusCode = 404): self
    {
        $PageFileName = strtolower(trim($PageFileName));
        $this->c->batch('batchSetNoRouteMatchPageMethod', $this->method, $PageFileName, $statusCode);
        return $this;
    }
    public function setNoRouteMatchJson(array|object $data, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchJsonMethod', $this->method, $data, $statusCode);
        return $this;
    }
    public function setNoRouteMatchText(string $message, int $statusCode = 404): self
    {
        $this->c->batch('batchSetNoRouteMatchTextMethod', $this->method, $message, $statusCode);
        return $this;
    }
    public function setNoRouteMatchCallback(string $functionName): self
    {
        $functionName = strtolower(trim($functionName));
        $this->c->batch('batchSetNoRouteMatchCallbackMethod', $this->method, $functionName);
        return $this;
    }
    public function setNonces(...$noncesReferenceKeys)
    {
        $this->c->batch('batchSetNoncesMethod', $this->method, ...$noncesReferenceKeys);
        return $this;
    }
    /**
     * Configures Content-Security-Policy (CSP) directives globally.
     *
     * Automatically wraps standard CSP keywords (e.g. 'self', 'none', 'unsafe-inline') in single quotes,
     * while preserving casing for hashes, nonces, and domains.
     *
     * @param 'default-src'|'script-src'|'script-src-elem'|'script-src-attr'|'style-src'|'style-src-elem'|'style-src-attr'|'img-src'|'font-src'|'connect-src'|'media-src'|'object-src'|'child-src'|'frame-src'|'worker-src'|'manifest-src'|'prefetch-src'|'base-uri'|'form-action'|'frame-ancestors'|'sandbox'|'plugin-types'|'navigate-to'|'report-uri'|'report-to' $sourceType
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
    public function setRateLimiting(array $rateLimitingOptions): self
    {
        $this->c->batch('batchSetRateLimitingRoute', $this->method, $rateLimitingOptions);
        return $this;
    }

    public function pipeMiddleware(string $middleware = ''): self
    {
        $middleware = strtolower(trim($middleware));
        $this->c->batch('batchPipeMiddlewareMethod', $this->method, $middleware);
        return $this;
    }
    /*METHOD: pipeHeader & removeHeader & setParamRule */
    public function pipeHeader(string $header): self
    {
        $this->c->batch('batchPipeHeaderMethod', $this->method, $header);
        return $this;
    }
    public function removeHeader(string $header_to_remove): self
    {
        $header_to_remove = strtolower(trim($header_to_remove));
        $this->c->batch('batchRemoveHeaderMethod', $this->method, $header_to_remove);
        return $this;
    }
    public function setParamRule(string $param, string $regex, string|null $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleMethod', $this->method, $param, $regex, $defaultParamValueOnRegexMismatch);
        return $this;
    }
    // Create a new route for the current FunkMethod() and/or
    // jump back/initialize to HEAD,GET,POST,PUT,PATCH,DELETE
    // that is under ->routes() | This allows for group and such!
    public function route(string $path): FunkRoute
    {
        $this->c->batch('batchNewRoute', $this->method, strtolower(trim($path)));
        return new FunkRoute($this->c, $this, $this->method, strtolower(trim($path)));
    }
    public function HEAD(): FunkMethod
    {
        return $this->parent->HEAD();
    }
    public function GET(): FunkMethod
    {
        return $this->parent->GET();
    }
    public function POST(): FunkMethod
    {
        return $this->parent->POST();
    }
    public function PUT(): FunkMethod
    {
        return $this->parent->PUT();
    }
    public function PATCH(): FunkMethod
    {
        return $this->parent->PATCH();
    }
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
    /*ROUTE: set<VARIANTS> */
    public function setAlias(string $aliasName = ''): self
    {
        $aliasName = trim($aliasName);
        $this->c->batch('batchSetAliasRoute', $this->method, $this->routePath, $aliasName);
        return $this;
    }
    public function setRateLimiting(array $rateLimitingOptions): self
    {
        $this->c->batch('batchSetRateLimitingRoute', $this->method, $this->routePath, $rateLimitingOptions);
        return $this;
    }
    public function setCache(array $cacheOptions): self
    {
        $this->c->batch('batchSetCacheRoute', $this->method, $this->routePath, $cacheOptions);
        return $this;
    }
    public function setNonces(...$noncesReferenceKeys)
    {
        $this->c->batch('batchSetNoncesRoute', $this->method, $this->routePath, ...$noncesReferenceKeys);
        return $this;
    }

    public function pipeMiddleware(string $middleware = ''): self
    {
        $middleware = strtolower(trim($middleware));
        $this->c->batch('batchPipeMiddlewareRoute', $this->method, $this->routePath, $middleware);
        return $this;
    }
    public function pipeFunction(string $fileNameAndFunctionName = ''): self
    {
        $fileNameAndFunctionName = strtolower(trim($fileNameAndFunctionName));
        $this->c->batch('batchPipeFunctionRoute', $this->method, $this->routePath, $fileNameAndFunctionName);
        return $this;
    }
    public function pipeResponse(string $typeOfResponse): self
    {
        $typeOfResponse = trim($typeOfResponse);
        $this->c->batch('batchPipeResponseRoute', $this->method, $this->routePath, $typeOfResponse);
        return $this;
    }
    public function pipeSQL(string $sqlFileFunction): self
    {
        $sqlFileFunction = strtolower(trim($sqlFileFunction));
        $this->c->batch('batchPipeSQLRoute', $this->method, $this->routePath, $sqlFileFunction);
        return $this;
    }
    public function pipeQuery(string $queryFileFunction): self
    {
        $queryFileFunction = strtolower(trim($queryFileFunction));
        $this->c->batch('batchPipeQueryRoute', $this->method, $this->routePath, $queryFileFunction);
        return $this;
    }
    public function pipeValidation(string $validationFileFunction): self
    {
        $validationFileFunction = strtolower(trim($validationFileFunction));
        $this->c->batch('batchPipeValidationRoute', $this->method, $this->routePath, $validationFileFunction);
        return $this;
    }
    public function setExcludeMiddleware(string ...$middlewareToExclude): self
    {
        $this->c->batch('batchExcludeMiddlewareRoute', $this->method, $this->routePath, ...$middlewareToExclude);
        return $this;
    }
    public function setExcludeHeaders(string ...$headersToExclude): self
    {
        $this->c->batch('batchExcludeHeadersRoute', $this->method, $this->routePath, ...$headersToExclude);
        return $this;
    }
    public function setParamRule(string $param, string $regex, string|null $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleRoute', $this->method, $this->routePath, $param, $regex, $defaultParamValueOnRegexMismatch);
        return $this;
    }

    /**
     * Configures Content-Security-Policy (CSP) directives globally.
     *
     * Automatically wraps standard CSP keywords (e.g. 'self', 'none', 'unsafe-inline') in single quotes,
     * while preserving casing for hashes, nonces, and domains.
     *
     * @param 'default-src'|'script-src'|'script-src-elem'|'script-src-attr'|'style-src'|'style-src-elem'|'style-src-attr'|'img-src'|'font-src'|'connect-src'|'media-src'|'object-src'|'child-src'|'frame-src'|'worker-src'|'manifest-src'|'prefetch-src'|'base-uri'|'form-action'|'frame-ancestors'|'sandbox'|'plugin-types'|'navigate-to'|'report-uri'|'report-to' $sourceType
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

    /*ROUTE: pipeHeader & removeHeader */
    public function pipeHeader(string $header): self
    {
        $this->c->batch('batchPipeHeaderRoute', $this->method, $this->routePath, $header);
        return $this;
    }
    public function removeHeader(string $header_to_remove): self
    {
        $this->c->batch('batchRemoveHeaderRoute', $this->method, $this->routePath, $header_to_remove);
        return $this;
    }

    // Create a new Route under currently navigated <METHOD>() and/or
    // jump to any other available <METHOD>() as seen below!
    public function route(string $path): FunkRoute
    {
        return $this->parentMethod->route($path);
    }
    public function HEAD(): FunkMethod
    {
        return $this->parentMethod->HEAD();
    }
    public function GET(): FunkMethod
    {
        return $this->parentMethod->GET();
    }
    public function POST(): FunkMethod
    {
        return $this->parentMethod->POST();
    }
    public function PUT(): FunkMethod
    {
        return $this->parentMethod->PUT();
    }
    public function PATCH(): FunkMethod
    {
        return $this->parentMethod->PATCH();
    }
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
