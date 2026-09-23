<?php

/**
 * FunkPHPDeployment File
 * Built: 2026-09-23 10:27:29
 * Compiler Flags: `ALLOW_GHOST_ROUTES`, `OUTPUT_OVERRIDE_DEBUG`
 * DO NOT EDIT DIRECTLY - CHANGES ARE OVERWRITTEN WHEN (RE)BUILDING
 */

namespace {
    define('FUNKPHP_NO_VALUE', new \stdClass());
    define('FUNKPHP_ONLINE', true);
    define('ROOT_FOLDER', __DIR__);
    define('ROOT_PAGES', __DIR__ . '/pages');
    $c = ['BASEURLS' => ['LOCAL' => null, 'ONLINE' => null, 'BASEURL_URI' => null, 'HOST' => null], 'SESSION' => ['driver' => 'files', 'COOKIES' => ['SESSION_NAME' => 'fphp_id', 'SESSION_DOMAIN' => 'funkphp', 'SESSION_PATH' => '/', 'SESSION_LIFETIME' => 28800, 'SESSION_SAMESITE' => 'Lax', 'SESSION_SECURE' => false, 'SESSION_HTTPONLY' => true]], 'shared' => [], 'classes' => ['vendor' => [], 'user' => []], 'credentials' => null, 'connections' => [], 'req' => ['ip' => null, 'method' => null, 'prefers' => null, 'uri' => null, 'route' => null, 'route_matched' => false, 'segments' => null, 'params' => null, 'param_valid' => null, 'params_valid' => null, 'params_details' => null, 'accept_order' => null, 'accepts' => null, 'query' => null, 'base_url_absolute' => null, 'base_url_relative' => null, 'time' => null, 'log' => [], 'ua' => null], 'd' => null, 'v' => null, 'v_ok' => null, 'v_ok_files' => null, 'v_config' => [], 'v_data' => null, 'p' => null, 'files' => null, 'err' => [], 'runtime' => ['request_accepts' => [], 'request_ip_sources' => [], 'request_form_spoof_methods' => ['PUT', 'PATCH', 'DELETE'], 'trusted_ip_proxies' => ['ip4' => ['173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22'], 'ip6' => ['2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32']], 'trusted_ip_headers' => ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'], 'state' => 'global', 'global_headers' => null, 'method_headers' => null, 'SKIP_POST_RESPONSE_ON_NO_MATCH' => false]];
    $c['req']['time'] = $_SERVER['REQUEST_TIME'] ?? time();
    $c['req']['query'] = $_SERVER['QUERY_STRING'] ?? null;
    $c['req']['ua'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $c['runtime']['state'] = 'global';
    function test(&$c): void
    {
        echo "YAS from test() user defined fn! This is callback on NO_ROUTE_MATCH";
    }
    function testar0(&$c)
    {
        echo "HTTPS KERNEL CUSTOM TEST";
    }
    function dd(mixed $data, string $headerOptionalMsg = '', bool $exit = true, bool $ignoreC = true, bool $colorizeAccentGravedText = true): void
    {
        if (php_sapi_name() === 'cli' && function_exists('cli_dump')) {
            cli_dd($data, $exit);
            return;
        }
        global $c;
        $metrics = ['nulls' => 0, 'strings' => 0, 'strings-empty' => 0, 'booleans' => 0, 'booleans-true' => 0, 'booleans-false' => 0, 'integers' => 0, 'floats' => 0, 'arrays' => 0, 'arrays-empty' => 0, 'arrays-lists' => 0, 'arrays-assocs' => 0, 'objects' => 0, 'others' => 0,];
        $render = function ($data, $key = null, $isList = false, array $seenObjects = [], int $depth = 0) use (&$render, &$metrics, $colorizeAccentGravedText): string {
            if ($depth > 25) {
                return "<div class=\"fd-row\"><span class=\"fd-null\">*MAX DEPTH EXCEEDED:{$depth}*</span></div>";
            }
            $prefix = '';
            if ($key !== null) {
                $safeKey = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
                $prefix = $isList ? "<span class=\"fd-idx\">[{$safeKey}]</span> " : "<span class=\"fd-key\">'{$safeKey}'</span> <span class=\"fd-type\">=&gt;</span> ";
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
        } ?>
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
    function funk_session_get_key(&$c, string $key, $default = null)
    {
        \funk_internal_session_started_or_start_it($c);
        return $_SESSION[$key] ?? $default;
    }
    function funk_session_set_key(&$c, string $key, $value): void
    {
        \funk_internal_session_started_or_start_it($c);
        $_SESSION[$key] = $value;
    }
    function funk_session_key_exist(&$c, string $key): bool
    {
        \funk_internal_session_started_or_start_it($c);
        if (isset($_SESSION[$key]) || array_key_exists($key, $_SESSION)) {
            return true;
        } else {
            return false;
        }
    }
    function funk_session_destroy(&$c, $set_other_cookies_with_h_setcookie_as_array = [], $redirect = null)
    {
        if (session_id() || session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_unset();
            session_destroy();
            \funk_session_cookie_set($c, session_name(), '', time() - 3600);
            \funk_session_cookie_set($c, "csrf", '', time() - 3600);
            if (!empty($set_other_cookies_with_h_setcookie_as_array)) {
                foreach ($set_other_cookies_with_h_setcookie_as_array as $cookie) {
                    \funk_session_cookie_set(...$cookie);
                }
            }
        }
        if ($redirect) {
            header("Location: $redirect");
            exit;
        }
    }
    function funk_session_cookie_set(&$c, $name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true, $samesite = 'strict')
    {
        setcookie($name, $value, ['expires' => $expire, 'path' => $path, 'domain' => $domain, 'secure' => $secure, 'httponly' => $httponly, 'samesite' => $samesite]);
    }
    function funk_generate_csrf(&$c, string $currentUri, ?int $lifetimeSeconds = null): string
    {
        if (\funk_session_get_key($c, '_funk_csrf') === null) {
            $_SESSION['_funk_csrf'] = [];
        }
        $token = hash('sha256', random_bytes(32));
        $_SESSION['_funk_csrf'][$token] = ['uri' => $currentUri, 'expires' => ($lifetimeSeconds === null) ? null : (time() + $lifetimeSeconds)];
        if (count($_SESSION['_funk_csrf']) > 99) {
            array_shift($_SESSION['_funk_csrf']);
        }
        return $token;
    }
    function funk_return_response_file($filePath, $fileName = null, $statusCode = 200)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            \funk_return_error_json_or_page($c, 404, \funk_internal_critical_error_json($c, 404, 'Internal Server Error: File `' . $fileName . '` Not Found. Do this check before Calling this Function.'), '404', 'Internal Server Error: File `' . $fileName . '` Not Found. Do this check before Calling this Function.');
        }
        header_remove('content-type');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        \funk_internal_send_headers($c);
        http_response_code($statusCode);
        $downloadName = $fileName ?? basename($filePath);
        $safeFileName = str_replace(['"', "\r", "\n"], '', $downloadName);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safeFileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    function funk_return_error_raw(&$c, int $errCode, string $errMsg, string $contentType = 'text/plain; charset=utf-8'): void
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header_remove('content-type');
        http_response_code($errCode);
        \funk_set_header($c, 'content-type', $contentType);
        if ($contentType === 'text/html; charset=utf-8') {
            \funk_set_header($c, 'content-security-policy', "default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            \funk_internal_send_headers($c, true);
        } else {
            \funk_internal_send_headers($c);
        }
        echo $errMsg;
        exit();
    }
    function funk_return_error_raw_plain(&$c, int $errCode, string $errMsg): void
    {
        \funk_return_error_raw($c, $errCode, $errMsg, 'text/plain; charset=utf-8');
    }
    function funk_return_error_raw_html(&$c, int $errCode, string $errMsg): void
    {
        \funk_return_error_raw($c, $errCode, $errMsg, 'text/html; charset=utf-8');
    }
    function funk_return_error_xml(&$c, int $errCode, string $errMsg): void
    {
        \funk_return_error_raw($c, $errCode, $errMsg, 'application/xml; charset=utf-8');
    }
    function funk_return_error_page(&$c, int $errCode, string $errMsg, string $pageName)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header_remove('content-type');
        http_response_code($errCode);
        \funk_set_header($c, 'content-type', 'text/html');
        \funk_set_header($c, 'content-security-policy', "default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        \funk_internal_send_headers($c, true);
        try {
            $custom_error_message = $errMsg;
            $pagePath = defined('FUNKPHP_ONLINE') ? ROOT_FOLDER . '/pages/' . $pageName . '.php' : ROOT_FOLDER . '/pages/compiled/' . $pageName . '.php';
            include_once $pagePath;
        } catch (\Throwable $e) {
            echo \funk_internal_critical_error_page($c, 404, 'Internal Error Page Rendering Failure: ' . $e->getMessage() . ' | Error Message meant to show: ' . $errMsg);
            exit;
        }
        exit();
    }
    function funk_throw_exception(&$c, string $exceptionErrMsg)
    {
        if (!isset($exceptionErrMsg) || !is_string($exceptionErrMsg) || empty($exceptionErrMsg)) {
            \funk_return_error_json_or_page($c, 500, \funk_internal_critical_error_json($c, 500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_throw()` Function. This should be a non-empty string!'), '500', 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_throw()` Function. This should be a non-empty string!');
        }
        throw new Exception($exceptionErrMsg);
    }
    function funk_return_error_json(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header_remove('content-type');
        http_response_code($errCode);
        \funk_set_header($c, 'content-type', 'application/json; charset=utf-8');
        \funk_internal_send_headers($c);
        $jsonData = $jsonObjectOrStringThatReturnsJSON;
        if (is_string($jsonData) && is_callable($jsonData)) {
            try {
                $jsonData = $jsonData($c);
            } catch (\Throwable $e) {
                echo json_encode(\funk_internal_critical_error_json($c, 500, 'INTERNAL SERVER ERROR: JSON Callable Error: ' . $e->getMessage()));
            }
        }
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            echo json_encode(\funk_internal_critical_error_json($c, 500, 'INTERNAL SERVER ERROR: JSON Encoding Failure: ' . $e->getMessage()));
        }
        exit();
    }
    function funk_return_error_json_or_page(&$c, int $errCode, mixed $jsonObjectOrStringThatReturnsJSON, string $pageName, string $pageErrMsg): void
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header_remove('content-type');
        http_response_code($errCode);
        $prefers = $c['req']['prefers'] ?? null;
        if ($prefers === null) {
            $negotiated = \funk_internal_negotiate_content($c);
            $c['req']['accept_order'] = $negotiated[0];
            $c['req']['prefers'] = $negotiated[1];
            $prefers = $c['req']['prefers'];
        }
        if ($prefers === 'json') {
            \funk_set_header($c, 'content-type', 'application/json; charset=utf-8');
            \funk_internal_send_headers($c);
            $jsonData = $jsonObjectOrStringThatReturnsJSON;
            if (is_string($jsonData) && is_callable($jsonData)) {
                try {
                    $jsonData = $jsonData($c);
                } catch (\Throwable $e) {
                    echo json_encode(\funk_internal_critical_error_json($c, 500, 'INTERNAL SERVER ERROR: JSON Callable Error: ' . $e->getMessage()));
                }
            }
            try {
                echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\JsonException $e) {
                echo json_encode(\funk_internal_critical_error_json($c, 500, 'INTERNAL SERVER ERROR: JSON Encoding Failure: ' . $e->getMessage()));
            }
            exit();
        }
        \funk_set_header($c, 'content-type', 'text/html; charset=utf-8');
        \funk_set_header($c, 'content-security-policy', "default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
        \funk_internal_send_headers($c, true);
        try {
            $custom_error_message = $pageErrMsg;
            $pagePath = defined('FUNKPHP_ONLINE') ? ROOT_FOLDER . '/pages/' . $pageName . '.php' : ROOT_FOLDER . '/pages/compiled/' . $pageName . '.php';
            include_once $pagePath;
        } catch (\Throwable $e) {
            echo \funk_internal_critical_error_page($c, 404, 'Internal Error Page Rendering Failure: ' . $e->getMessage() . ' | Error Message meant to show: ' . $pageErrMsg);
        }
        exit();
    }
    function funk_req_accepts(&$c, $contentType): bool
    {
        if (isset($c['req']['accepts'][$contentType])) {
            return true;
        }
        return false;
    }
    function funk_req_prefers(&$c, $contentType): bool
    {
        if (isset($c['req']['prefers']) && $c['req']['prefers'] === $contentType) {
            return true;
        }
        return false;
    }
    function funk_set_header(&$c, $headerName, $value)
    {
        $key = strtolower(trim($headerName));
        $c['runtime']['route']['headers']['add'][$key] = $headerName . ': ' . $value;
    }
    function funk_remove_header(&$c, string $headerName)
    {
        if (!headers_sent()) {
            header_remove($headerName);
        }
    }
    function funk_return_response_page(&$c, string $pageNameWithoutExtension, int $code = 200)
    {
        header_remove('content-type');
        http_response_code($code);
        \funk_set_header($c, 'content-type', 'text/html');
        \funk_internal_send_headers($c);
        $pagePath = defined('FUNKPHP_ONLINE') ? ROOT_FOLDER . '/pages/' . $pageNameWithoutExtension . '.php' : ROOT_FOLDER . '/pages/compiled/' . $pageNameWithoutExtension . '.php';
        if (file_exists($pagePath)) {
            require_once $pagePath;
        } else {
            \funk_return_error_json_or_page($c, 500, \funk_internal_critical_error_json($c, 500, 'Failed to load a `User-defined Page` to Return a Response. This means the `Page` does NOT exist in the Expected Folder `/pages/`.'), '500', 'Failed to use a `User-defined Function` to Return a Response. This means the Function-name does NOT exist.');
        }
        exit();
    }
    function funk_return_response_json(&$c, string $c_data_key_with_JSON_encoded_Data, int $code = 200)
    {
        header_remove('content-type');
        http_response_code($code);
        \funk_set_header($c, 'content-type', 'application/json');
        \funk_internal_send_headers($c);
        if (isset($c['d'][$c_data_key_with_JSON_encoded_Data])) {
            echo is_string($c['d'][$c_data_key_with_JSON_encoded_Data]) ? $c['d'][$c_data_key_with_JSON_encoded_Data] : json_encode($c['d'][$c_data_key_with_JSON_encoded_Data]);
        }
        exit();
    }
    function funk_return_response_text(&$c, string $rawTextString, int $code = 200)
    {
        header_remove('content-type');
        http_response_code($code);
        \funk_set_header($c, 'content-type', 'text/plain');
        \funk_internal_send_headers($c);
        echo $rawTextString;
        exit();
    }
    function funk_return_response_callback(&$c, string $userDefinedFunctionName)
    {
        if (function_exists($userDefinedFunctionName)) {
            $userDefinedFunctionName($c);
            exit();
        }
        \funk_return_error_json_or_page($c, 500, \funk_internal_critical_error_json($c, 500, 'Failed to use a `User-defined Function` to Return a Response. This means the Function-name does NOT exist.'), '500', 'Failed to use a `User-defined Function` to Return a Response. This means the Function-name does NOT exist.');
    }
    function funk_req_param_valid(&$c, string $param): bool
    {
        if (isset($c['req']['param_valid'][$param]) && $c['req']['param_valid'][$param] === true) {
            return true;
        }
        return false;
    }
    function funk_req_params_valid(&$c): bool
    {
        if (isset($c['req']['params_valid']) && $c['req']['params_valid'] === true) {
            return true;
        }
        return false;
    }
    function funk_db_conn(&$c, $dbKey)
    {
        if (!isset($dbKey) || !is_string($dbKey)) {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Invalid or missing $dbKey passed to funk_db_conn().';
            return null;
        }
        if (isset($c['DATABASES'][$dbKey])) {
            return $c['DATABASES'][$dbKey];
        }
        $credentials = null;
        if ($credentials === null) {
            $c['err']['DATABASES']['funk_db_conn'][] = "No database configuration found for key '$dbKey'.";
            return null;
        }
        if ($credentials['driver'] === 'mysqli') {
            $host = $credentials['host'] ?? 'localhost';
            $user = $credentials['user'] ?? 'root';
            $password = $credentials['password'] ?? '';
            $database = $credentials['database'] ?? '';
            $port = $credentials['port'] ?? 3306;
            $charset = $credentials['charset'] ?? 'utf8mb4';
            try {
                $mysqli = new mysqli($host, $user, $password, $database, $port);
                if ($mysqli->connect_error) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . $mysqli->connect_error;
                    return null;
                }
                if (!$mysqli->set_charset($charset)) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Error loading character set ' . $charset . ' for ' . $dbKey . ': ' . $mysqli->error;
                }
                $c['DATABASES'][$dbKey] = $mysqli;
                return $c['DATABASES'][$dbKey];
            } catch (Exception $ex) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
                return null;
            }
        } else if ($credentials['driver'] === 'pgsql') {
            $host = $credentials['host'] ?? 'localhost';
            $user = $credentials['user'] ?? 'postgres';
            $password = $credentials['password'] ?? '';
            $database = $credentials['database'] ?? '';
            $port = $credentials['port'] ?? 5432;
            $charset = $credentials['charset'] ?? 'utf8';
            $connString = "host=$host port=$port dbname=$database user=$user password=$password options='--client_encoding=$charset'";
            try {
                $pgsql = pg_connect($connString);
                if ($pgsql === false) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . pg_last_error(null);
                    return null;
                }
                $c['DATABASES'][$dbKey] = $pgsql;
                return $c['DATABASES'][$dbKey];
            } catch (Exception $ex) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
                return null;
            }
        } elseif ($credentials['driver'] === 'mongodb') {
            $host = $credentials['host'] ?? 'localhost';
            $user = $credentials['user'] ?? '';
            $password = $credentials['password'] ?? '';
            $database = $credentials['database'] ?? '';
            $port = $credentials['port'] ?? 27017;
            $charset = $credentials['charset'] ?? 'utf8';
            $authPart = ($user && $password) ? $user . ':' . $password . '@' : '';
            $uri = 'mongodb://' . $authPart . $host . ':' . $port;
            try {
                if (!class_exists('MongoDB\Client')) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'MongoDB extension is not installed or enabled.';
                    return null;
                }
                $mongoClient = new \MongoDB\Client($uri);
                $mongoDB = $mongoClient->selectDatabase($database);
                $c['DATABASES'][$dbKey] = $mongoDB;
                return $c['DATABASES'][$dbKey];
            } catch (Exception $ex) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
                return null;
            }
        } elseif ($credentials['driver'] === 'redis') {
            $host = $credentials['host'] ?? '127.0.0.1';
            $port = $credentials['port'] ?? 6379;
            $password = $credentials['password'] ?? null;
            $database = $credentials['database'] ?? 0;
            try {
                if (!class_exists('\Redis')) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Redis extension is not installed or enabled.';
                    return null;
                }
                $redis = new \Redis();
                if (!$redis->connect($host, $port)) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Redis connection failed for ' . $dbKey;
                    return null;
                }
                if ($password !== null && !$redis->auth($password)) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Redis authentication failed for ' . $dbKey;
                    $redis->close();
                    return null;
                }
                if (!$redis->select($database)) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Redis database selection failed for ' . $dbKey . ' (DB: ' . $database . ')';
                    $redis->close();
                    return null;
                }
                $c['DATABASES'][$dbKey] = $redis;
                return $c['DATABASES'][$dbKey];
            } catch (\Exception $ex) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
                return null;
            }
        } elseif ($credentials['driver'] === 'memcached') {
            $host = $credentials['host'] ?? '127.0.0.1';
            $port = $credentials['port'] ?? 11211;
            try {
                if (!class_exists('\Memcached')) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached extension is not installed or enabled.';
                    return null;
                }
                $memcached = new \Memcached();
                if (!$memcached->addServer($host, $port)) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached failed to add server for ' . $dbKey . '.';
                    return null;
                }
                $stats = $memcached->getStats();
                if (empty($stats) || !isset($stats["$host:$port"]) || $stats["$host:$port"]['pid'] === -1) {
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Memcached server ' . $host . ':' . $port . ' is unavailable.';
                    return null;
                }
                $c['DATABASES'][$dbKey] = $memcached;
                return $c['DATABASES'][$dbKey];
            } catch (\Exception $ex) {
                $c['err']['DATABASES']['funk_db_conn'][] = 'Exception occurred while connecting to ' . $dbKey . ': `' . $ex->getMessage() . '`';
                return null;
            }
        } else {
            $c['err']['DATABASES']['funk_db_conn'][] = 'Database driver "' . $credentials['driver'] . '" for key `' . $dbKey . '` is not supported in current version of FunkPHP.';
            return null;
        }
    }
    function funk_internal_session_started_or_start_it(&$c)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        if (($c['SESSION']['driver'] ?? 'files') === 'redis') {
        }
        session_set_cookie_params(['lifetime' => $c['SESSION']['COOKIES']['SESSION_LIFETIME'] ?? 0, 'path' => $c['SESSION']['COOKIES']['SESSION_PATH'] ?? '/', 'domain' => $c['SESSION']['COOKIES']['SESSION_DOMAIN'] ?? '', 'secure' => $c['SESSION']['COOKIES']['SESSION_SECURE'] ?? true, 'httponly' => true, 'samesite' => $c['SESSION']['COOKIES']['SESSION_SAMESITE'] ?? 'Lax',]);
        if (!session_start()) {
            $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
            \funk_return_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
    function funk_internal_rate_limiter(&$c, int $maxRequestsPerWindowSize, int $windowSizeSecs, string|array $by = 'ip', $driver = 'redis') {}
    function funk_internal_route_cache(&$c, int $ttl, string $driver = 'redis', string|array|null $varyBy = null, bool $private = false, string $getOrSet = 'GET')
    {
        if ($getOrSet === 'GET') {
        } else {
        }
    }
    function funk_internal_resolve_ip(&$c): string
    {
        $RESOLVE_IP = function ($ip, $trustedList) use ($c) {
            if (empty($ip)) {
                return false;
            }
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
        };
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $trustedProxies = $c['runtime']['trusted_ip_proxies'] ?? [];
        $ipHeaders = $c['runtime']['trusted_ip_headers'] ?? ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'];
        if (empty($trustedProxies) || !$RESOLVE_IP($remoteAddr, $trustedProxies)) {
            return $remoteAddr;
        }
        foreach ($ipHeaders as $headerKey) {
            if (!empty($_SERVER[$headerKey])) {
                $rawHeader = $_SERVER[$headerKey];
                $ipList = array_map('trim', explode(',', $rawHeader));
                for ($i = count($ipList) - 1; $i >= 0; $i--) {
                    $candidateIp = $ipList[$i];
                    if (filter_var($candidateIp, FILTER_VALIDATE_IP)) {
                        if (!$RESOLVE_IP($candidateIp, $trustedProxies)) {
                            return $candidateIp;
                        }
                    }
                }
            }
        }
        return $remoteAddr;
    }
    function funk_internal_resolve_uri(&$c): array
    {
        $URI = null;
        $URL_REL = null;
        $URL_ABS = null;
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $cleanPath = explode('?', $rawUri, 2)[0];
        $cleanPath = explode('#', $cleanPath, 2)[0];
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseUrl = dirname($scriptName);
        if ($baseUrl !== '/' && str_starts_with($cleanPath, $baseUrl)) {
            $cleanPath = substr($cleanPath, strlen($baseUrl));
        }
        $cleanPath = preg_replace('#/{2,#', '/', $cleanPath);
        $cleanPath = trim($cleanPath, '/');
        $URI = ($cleanPath === '') ? '/' : '/' . $cleanPath;
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $scriptName ?: $_SERVER['SCRIPT_NAME'] ?: '';
        $baseUrl = $baseUrl ? $baseUrl : dirname($scriptName);
        $URL_ABS = rtrim($protocol . $host . $baseUrl, '/');
        $URL_REL = ($baseUrl === '/') ? '' : $baseUrl;
        return [$URI, $URL_ABS, $URL_REL];
    }
    function funk_internal_exception_handler(&$c, \Throwable $e)
    {
        $c['err']['INTERNAL'][] = "UNCAUGHT EXCEPTION: " . $e->getMessage();
        $c['req']['log'][] = 'UNCAUGHT EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        $isDebug = ((defined('FUNKPHP_ONLINE') && FUNKPHP_ONLINE === false && isset($c['debug']['show_errors'])) ? true : false);
        if ($isDebug) {
            $file = $e->getFile();
            $line = $e->getLine();
            $msg = htmlspecialchars($e->getMessage());
            $type = get_class($e);
            $snippet = \funk_internal_render_code_snippet($file, $line);
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
        \funk_return_error_json_or_page($c, 500, \funk_internal_critical_error_json($c, 500, $err), '500', $err);
    }
    function funk_internal_error_handler(&$c, int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
    function funk_internal_render_code_snippet(string $filePath, int $errorLine, int $padding = 5): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return '<div style="padding:10px; background:#222; color:#888;">Source File Unavailable</div>';
        }
        $lines = file($filePath);
        $start = max(0, $errorLine - $padding - 1);
        $end = min(count($lines), $errorLine + $padding);
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
    function funk_internal_negotiate_content(mixed &$c): array
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (empty(trim($acceptHeader))) {
            return [[], null];
        }
        $mimeTypeToShortHand = ['text/html' => 'html', 'application/xhtml+xml' => 'xhtml', 'application/json' => 'json', 'text/json' => 'json', 'application/vnd.api+json' => 'jsonapi', 'application/problem+json' => 'jsonproblem', 'application/ld+json' => 'jsonld', 'application/hal+json' => 'jsonhal', 'application/wasm' => 'wasm', 'application/xml' => 'xml', 'application/octet-stream' => 'stream', 'text/xml' => 'xml', 'text/plain' => 'text', 'text/csv' => 'csv', 'text/calendar' => 'calendar', 'text/vcard' => 'vcard', 'text/markdown' => 'markdown', 'image/webp' => 'webp', 'image/avif' => 'avif', 'image/x-icon' => 'ico', 'image/bmp' => 'bmp', 'image/apng' => 'apng', 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/gif' => 'gif', 'image/tiff' => 'tiff', 'image/heic' => 'heic', 'image/svg+xml' => 'svg', 'audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/flac' => 'flac', 'video/mp4' => 'mp4',];
        $userCustomAccepts = $c['runtime']['request_accepts'] ?? [];
        $types = [];
        $c['req']['accepts']['json'] = false;
        $c['req']['accepts']['html'] = false;
        $c['req']['accepts']['xml'] = false;
        $c['req']['accepts']['text'] = false;
        $c['req']['accepts']['image'] = false;
        $c['req']['accepts']['media'] = false;
        foreach (explode(',', $acceptHeader) as $part) {
            $segments = explode(';', trim($part));
            $mediaType = trim($segments[0]);
            if (empty($mediaType)) {
                continue;
            }
            $q = 1.0;
            foreach (array_slice($segments, 1) as $param) {
                $param = trim($param);
                if (str_starts_with($param, 'q=')) {
                    $q = (float) substr($param, 2);
                    break;
                }
            }
            if ($q <= 0) {
                continue;
            }
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
                $c['req']['accepts']['json'] = true;
                $c['req']['accepts']['html'] = true;
                $c['req']['accepts']['xml'] = true;
                $c['req']['accepts']['text'] = true;
                $c['req']['accepts']['media'] = true;
                $c['req']['accepts']['audio'] = true;
                $c['req']['accepts']['video'] = true;
                $c['req']['accepts']['image'] = true;
            }
            $types[] = ['type' => $mediaType, 'q' => $q];
        }
        usort($types, fn($a, $b) => $b['q'] <=> $a['q']);
        $sortedList = array_column($types, 'type');
        $topMime = $sortedList[0] ?? null;
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
    function funk_internal_send_headers(&$c, bool $ignoreCSP = false): void
    {
        if (headers_sent()) {
            $c['err']['INTERNAL'][] = "Headers already sent prior to funk_internal_send_headers(). Check for Unhandled Output or manual `header()` calls.";
            return;
        }
        $state = $c['runtime']['state'] ?? 'global';
        if ($state === 'route' && isset($c['runtime']['route']['headers'])) {
            if (isset($c['runtime']['route']['headers']['remove'])) {
                foreach ($c['runtime']['route']['headers']['remove'] as $headerToRemove) {
                    header_remove($headerToRemove);
                }
            }
            if (isset($c['runtime']['route']['headers']['add'])) {
                foreach ($c['runtime']['route']['headers']['add'] as $headerLine) {
                    header($headerLine);
                }
            }
            if (!$ignoreCSP) {
                $cspParts = [];
                $cspDirectives = ['placeholder' => ['placeholder2']];
                foreach ($cspDirectives as $directive => $sources) {
                    if (empty($sources)) {
                        continue;
                    }
                    $cspParts[] = $directive . ' ' . implode(' ', $sources);
                }
                if (!empty($cspParts)) {
                    header('Content-Security-Policy: ' . implode('; ', $cspParts));
                }
            }
            return;
        }
        $staticHeaders = match ($state) {
            'method' => $c['runtime']['method_headers'][$c['req']['method']] ?? [],
            default => $c['runtime']['global_headers'] ?? [],
        };
        if (isset($staticHeaders['remove'])) {
            foreach ($staticHeaders['remove'] as $headerToRemove) {
                header_remove($headerToRemove);
            }
        }
        if (isset($staticHeaders['add'])) {
            foreach ($staticHeaders['add'] as $headerLine) {
                header($headerLine);
            }
        }
        if (isset($c['runtime']['route']['headers']['add'])) {
            foreach ($c['runtime']['route']['headers']['add'] as $dynamicHeaderLine) {
                header($dynamicHeaderLine);
            }
        }
        if (isset($c['runtime']['route']['headers']['remove'])) {
            foreach ($c['runtime']['route']['headers']['remove'] as $dynamicHeaderLineRemove) {
                header_remove($dynamicHeaderLineRemove);
            }
        }
    }
    function funk_internal_critical_error_page(&$c, $code = 500, $message = 'No Specific Error Message Provided.', $configuredThisYet = '->setNoRouteMatch', $textAfterCode = '')
    {
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $message = preg_replace('/&lt;br\s*\/?&gt;/i', '<br/>', $message);
        $message = preg_replace_callback('/`([^`]+)`/', function ($matches) {
            return '<span style="background-color: #313244; color: #f5c2e7; padding: 0.2rem 0.1rem; border-radius: 4px; font-family: monospace; font-size: 0.7em; border: 1px solid #45475a;">' . $matches[1] . '</span>';
        }, $message);
        $html = '';
        $html .= '<!DOCTYPE html>';
        $html .= '<html lang="en">';
        $html .= '<head>';
        $html .= '    <meta charset="UTF-8">';
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
        if (is_string($textAfterCode) && trim($textAfterCode) !== '') {
            $html .= '    <title>' . $code . ' - ' . htmlspecialchars($textAfterCode, ENT_QUOTES, 'UTF-8') . '</title>';
        } else {
            $html .= '    <title>' . $code . ' - No Content or Page Found | Have You Configured `' . htmlspecialchars($configuredThisYet, ENT_QUOTES, 'UTF-8') . '` Yet?</title>';
        }
        $html .= '    <style>';
        $html .= '        * { box-sizing: border-box; margin: 0; padding: 0; }';
        $html .= '        body {';
        $html .= '            background-color: #181825;';
        $html .= '            color: #cdd6f4;';
        $html .= '            font-family: system-ui, -apple-system, sans-serif;';
        $html .= '            display: grid;';
        $html .= '            place-items: center;';
        $html .= '            min-height: 100vh; padding:3rem;';
        $html .= '        }';
        $html .= '        .container { text-align: center; padding: 2rem; }';
        $html .= '        h1 {';
        $html .= '            font-size: 5rem;';
        $html .= '            font-weight: 800;';
        $html .= '            color: rgb(162, 74, 255);';
        $html .= '            line-height: 1;';
        $html .= '            margin-bottom: 0.5rem;';
        $html .= '        }';
        $html .= '        p {';
        $html .= '            font-size: 1.25rem; line-height:1.7;';
        $html .= '            color: #a6adc8;';
        $html .= '        }';
        $html .= '    </style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '    <div class="container">';
        if (is_string($textAfterCode) && trim($textAfterCode) !== '') {
            $html .= '<h1>' . $code . ' - ' . htmlspecialchars($textAfterCode, ENT_QUOTES, 'UTF-8') . '</h1>';
        } else {
            $html .= '<h1>' . $code . '</h1>';
        }
        $html .= '<p>' . $message . '</p>';
        $html .= '</div>';
        $html .= '</body>';
        $html .= '</html>';
        return $html;
    }
    function funk_internal_critical_error_json(&$c, $code = 500, $message = 'No Specific Error Message Provided.')
    {
        $json = ['code' => $code, 'error' => $message];
        return $json;
    }
    function FunkValidate()
    {
        return new FunkPHPValidate(new FunkPHPValidateC);
    }
    function FunkSQL()
    {
        return new FunkPHPSQL(new FunkPHPSQLC);
    }
}

namespace funkphp\classes {
    class UserDTO
    {
        public function __construct(public readonly int $id, public string $username, public string $email, public array $roles = ['user'], public bool $isActive = true) {}
        public function hasRole(string $role): bool
        {
            return in_array(strtolower($role), array_map('strtolower', $this->roles), true);
        }
        public function toArray(): array
        {
            return ['id' => $this->id, 'username' => $this->username, 'email' => $this->email, 'roles' => $this->roles, 'is_active' => $this->isActive,];
        }
    }
    class SecurityUtils
    {
        private static string $algo = 'sha256';
        private const PEPPER = 'fphp_secret_key_2026';
        public static function hashPassword(string $password): string
        {
            $salted = $password . self::PEPPER;
            return password_hash($salted, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 1,]);
        }
        public static function generateNonce(int $length = 32): string
        {
            if ($length < 16) {
                $length = 16;
            }
            return bin2hex(random_bytes((int) ($length / 2)));
        }
        public function verifyToken(?string $token, string $hash): bool
        {
            if (null === $token || '' === trim($token)) {
                return false;
            }
            return hash_equals(hash(self::$algo, $token . self::PEPPER), $hash);
        }
    }
    class ResponsePipeline
    {
        protected array $headers = [];
        protected array $payload = [];
        protected int $statusCode = 200;
        public function setStatus(int $code): self
        {
            $this->statusCode = $code;
            return $this;
        }
        public function withHeaders(array ...$headerPairs): self
        {
            foreach ($headerPairs as $pair) {
                if (isset($pair['key'], $pair['value'])) {
                    $this->headers[strtolower($pair['key'])] = $pair['value'];
                }
            }
            return $this;
        }
        public function buildResponse(string $format = 'json'): array
        {
            $formatted = ['status' => $this->statusCode, 'headers' => $this->headers, 'timestamp' => time(),];
            return match (strtolower($format)) {
                'json' => array_merge($formatted, ['data' => $this->payload]),
                'xml' => array_merge($formatted, ['xml_data' => $this->payload]),
                default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
            };
        }
    }
}

namespace funkphp\pipes\routes\test {
    function test(&$c)
    {
        if (\funk_req_prefers($c, 'json')) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "This is a test JSON response from the test function!",]);
            exit;
        }
        $c['req']['yo'] = "YO";
        echo "<h1 style='font-size:12px;'>Testing with HTML tags to see how the cURL Request Test functionality in FunkGUI will react to it!</h1>";
        echo "<div>";
        echo "<p>(from funkphp\pipes\\routes\\test) This is a test paragraph to see how the cURL Request Test functionality in FunkGUI will react to it!</p>";
        echo "</div>";
    }
}

namespace {
    ob_start();
    if (file_exists(ROOT_FOLDER . '/vendor/autoload.php')) {
        require_once ROOT_FOLDER . '/vendor/autoload.php';
    } else {
        $c['err']['INTERNAL'][] = 'Vendor Autoload Enabled (`use_vendor = true`), but File `' . ROOT_FOLDER . '/vendor/autoload.php' . '` was NOT Found.';
    }
    set_exception_handler(function (\Throwable $e) use (&$c) {
        \funk_internal_exception_handler($c, $e);
    });
    set_error_handler(function (int $errno, string $errstr, string $errfile = '', int $errline = 0) use (&$c) {
        return \funk_internal_error_handler($c, $errno, $errstr, $errfile, $errline);
    });
    $c['req']['ip'] = \funk_internal_resolve_ip($c);
    [$c['req']['uri'], $c['req']['base_url_absolute'], $c['req']['base_url_relative']] = \funk_internal_resolve_uri($c);
    $c['req']['method'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($c['req']['method'] === 'POST' && !empty($c['runtime']['request_form_spoof_methods'])) {
        $spoofedMethod = ($_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');
        if (in_array($spoofedMethod, $c['runtime']['request_form_spoof_methods'], true)) {
            $c['req']['method'] = $spoofedMethod;
        }
        unset($spoofedMethod);
    }
    [$c['req']['accept_order'], $c['req']['prefers']] = \funk_internal_negotiate_content($c);
    if (!in_array($c['req']['method'], ['GET'], true)) {
        FUNKPHP_NO_ROUTE_MATCH_GLOBAL_AND_NO_NO_MATCH_GOTO:
        switch (($c['req']['prefers'] ?? 'html')) {
            case 'json':
                \funk_set_header($c, 'content-type', 'application/json');
                header_remove('content-type');
                \funk_internal_send_headers($c);
                http_response_code(404);
                echo '{"err":"nothing found"}';
                exit;
            case 'html':
                \funk_set_header($c, 'content-type', 'text/html');
                header_remove('content-type');
                \funk_internal_send_headers($c);
                if (!file_exists(ROOT_FOLDER . '/pages/test.php')) {
                    http_response_code(404);
                    echo \funk_internal_critical_error_page($c, 404, 'Internal Server Error: Could Not Find Configured \'Not Found\' Page!', '->setNoRouteMatchPage()', 'No Page Found');
                } else {
                    http_response_code(404);
                    include ROOT_FOLDER . '/pages/test.php';
                }
                exit;
            case 'text':
                \funk_set_header($c, 'content-type', 'text/plain');
                header_remove('content-type');
                \funk_internal_send_headers($c);
                http_response_code(404);
                echo 'nothing in GLOBAL!';
                exit;
            default:
                unset($c['runtime']['global_headers']['add']['content-type']);
                unset($c['runtime']['method_headers']['add'][($c['req']['method'] ?? 'GET')]['content-type']);
                header_remove('content-type');
                \test($c);
                exit;
        }
        FUNKPHP_NO_ROUTE_MATCH_GET:
        switch (($c['req']['prefers'] ?? 'html')) {
            case 'html':
                \funk_set_header($c, 'content-type', 'text/html');
                header_remove('content-type');
                \funk_internal_send_headers($c);
                if (!file_exists(ROOT_FOLDER . '/pages/test.php')) {
                    http_response_code(404);
                    echo \funk_internal_critical_error_page($c, 404, 'Internal Server Error: Could Not Find Configured \'Not Found\' Page!', '->setNoRouteMatchPage()', 'No Page Found');
                } else {
                    http_response_code(404);
                    include ROOT_FOLDER . '/pages/test.php';
                }
                exit;
            case 'default':
                goto FUNKPHP_NO_ROUTE_MATCH_GLOBAL_AND_NO_NO_MATCH_GOTO;
        }
    }
    $URI = $c['req']['uri'] ?? '/';
    $SEGS = ($URI === '/') ? [] : explode('/', trim($URI, '/'));
    $SEGS_COUNT = count($SEGS);
    if ($SEGS_COUNT < 2 || $SEGS_COUNT > 3) {
        unset($URI, $SEGS_COUNT);
        goto FUNKPHP_NO_ROUTE_MATCH_GLOBAL_AND_NO_NO_MATCH_GOTO;
    }
    $c['runtime']['state'] = 'method';
    switch (($c['req']['method'] ?? 'GET')) {
        case 'GET':
            \funk_internal_rate_limiter($c, 60, 60, ['ip'], 'redis');
            switch ($URI) {
                case '/test/id2/id3':
                    goto FUNKPHP_ROUTE_GET_TEST_ID2_ID3;
                case '/test/test-2':
                    goto FUNKPHP_ROUTE_GET_TEST_TESTD__2;
            }
            switch ($SEGS_COUNT) {
                case 2:
                    goto FUNKPHP_GET_SEGS_2;
                case 3:
                    goto FUNKPHP_GET_SEGS_3;
                default:
                    goto FUNKPHP_NO_ROUTE_MATCH_GET;
            }
            break;
    }
    FUNKPHP_GET_SEGS_2:
    if (\strcasecmp($SEGS[0], 'test') === 0) {
        if (\strcasecmp($SEGS[1], 'test-2') === 0) {
            goto FUNKPHP_ROUTE_GET_TEST_TESTD__2;
        }
        goto FUNKPHP_ROUTE_GET_TEST_P__ID2;
    }
    goto FUNKPHP_NO_ROUTE_MATCH_GET;
    FUNKPHP_GET_SEGS_3:
    if (\strcasecmp($SEGS[0], 'test') === 0) {
        if (\strcasecmp($SEGS[1], 'id2') === 0) {
            if (\strcasecmp($SEGS[2], 'id3') === 0) {
                goto FUNKPHP_ROUTE_GET_TEST_ID2_ID3;
            }
            goto FUNKPHP_ROUTE_GET_TEST_ID2_P__ID3;
        }
        goto FUNKPHP_ROUTE_GET_TEST_P__ID2_P__ID3;
    }
    goto FUNKPHP_NO_ROUTE_MATCH_GET;
}
