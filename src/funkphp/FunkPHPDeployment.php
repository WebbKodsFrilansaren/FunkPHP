<?php

namespace {
    define('FUNKPHP_DEPLOYED', true);
    define('FUNKPHP_NO_VALUE', new stdClass());
    define('FUNKPHP_ALLOW_INSTANCE_OVERWRITE', 1);
    $c = array(
        'FUNKPHP_ONLINE' => false,
        'FUNKPHP_USE_HTTPS' => false,
        'FUNKPHP_USE_PREPARE_URI' => true,
        'FUNKPHP_USE_VENDOR' => true,
        'FUNKPHP_CUSTOM_URI_NORMALIZER' => NULL,
        'FUNKPHP_CUSTOM_EXCEPTION_HANDLER' => NULL,
        'FUNKPHP_CUSTOM_REGISTER_SHUTDOWN_FUNCTION' => NULL,
        'INI_SETS' => array('session.cache_limiter' => 'public', 'session.use_strict_mode' => 8, 'session.use_only_cookies' => 1, 'session.cache_expire' => 30, 'session.cookie_lifetime' => 0, 'session.name' => 'fphp_id', 'session.sid_length' => 192, 'session.sid_bits_per_character' => 6, 'display_errors' => 1, 'display_startup_errors' => 1, 'error_reporting' => 1,),
        'BASEURLS' => array('LOCAL' => 'http://webdev.local:81/funkphp', 'ONLINE' => 'https://www.funkphp.com', 'BASEURL' => 'localhost', 'BASEURL_URI' => '/funkphp/src/public_html/',),
        'SESSION' => array('driver' => 'files', 'COOKIES' => array('SESSION_NAME' => 'fphp_id', 'SESSION_LIFETIME' => 28800, 'SESSION_PATH' => '/', 'SESSION_DOMAIN' => 'webdev.local', 'SESSION_SECURE' => false, 'SESSION_HTTPONLY' => true, 'SESSION_SAMESITE' => 'Lax',),),
        'shared' => array(),
        'custom' => NULL,
        'classes' => array('vendor' => array(), 'user' => array(),),
        'credentials' => array('mysql_native' => array('driver' => 'mysqli', 'host' => '127.0.0.1', 'user' => 'root', 'password' => 'secret', 'database' => 'funk_db', 'port' => 3306, 'charset' => 'utf8mb4',), 'mysql_pdo' => array('driver' => 'pdo_mysql', 'host' => '127.0.0.1', 'user' => 'root', 'password' => 'secret', 'database' => 'funk_db', 'port' => 3306, 'charset' => 'utf8mb4',), 'postgres_pdo' => array('driver' => 'pdo_pgsql', 'host' => 'localhost', 'user' => 'postgres', 'password' => 'secret_pg_pass', 'database' => 'funk_postgres', 'port' => 5432, 'sslmode' => 'prefer',), 'redis_main' => array('driver' => 'redis', 'host' => '127.0.0.1', 'port' => 6379, 'password' => 'redis_auth_token', 'database' => 0, 'timeout' => 0,), 'memcached_cluster' => array('driver' => 'memcached', 'servers' => array(0 => array(0 => '127.0.0.1', 1 => 11211, 2 => 100,),),), 'mongo_docs' => array('driver' => 'mongodb', 'dsn' => 'mongodb://root:secret@127.0.0.1:27017', 'database' => 'funk_nosql', 'options' => array(),), 'aws_dynamo' => array('driver' => 'dynamodb', 'region' => 'us-east-1', 'version' => 'latest', 'key' => 'AKIAIOSFODNN7EXAMPLE', 'secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY', 'endpoint' => 'http://localhost:8000',),),
        'connections' => array(),
        'req' => array('method' => $_SERVER['REQUEST_METHOD'] ?? 'GET', 'ip' => $_SERVER['REMOTE_ADDR'] ?? null, 'time' => $_SERVER['REQUEST_TIME'] ?? time(), 'uri' => NULL, 'query' => $_SERVER['QUERY_STRING'] ?? null, 'base_url_absolute' => NULL, 'base_url_relative' => NULL, 'matched_in' => NULL, 'route' => NULL, 'params' => NULL, 'segments' => NULL, 'auth' => NULL, 'matched_config' => NULL, 'matched_pipeline' => array(), 'matched_middlewares' => NULL, 'skip_post_response' => false, 'current_pipeline' => NULL, 'next_pipeline' => NULL, 'current_middleware' => NULL, 'next_middleware' => NULL, 'keep_running_pipeline' => NULL, 'keep_running_middlewares' => NULL, 'keep_running_exit' => NULL, 'code' => 418, 'log' => array(), 'ua' => NULL, 'content_type' => NULL, 'accept' => NULL, 'protocol' => NULL,),
        'd' => NULL,
        'v' => NULL,
        'v_ok' => NULL,
        'v_ok_files' => NULL,
        'v_config' => array(),
        'v_data' => NULL,
        'p' => NULL,
        'files' => NULL,
        'err' => array('MAYBE' => array(), 'FUNCTIONS' => array(), 'CLASSES' => array(), 'CONNECTIONS' => array(), 'PIPELINE' => array(), 'MIDDLEWARES' => array(), 'PAGE' => array(), 'VALIDATION' => array(), 'SQL' => array(), 'QUERY' => array(),),
        'pipeline' => array(
            '<CONFIG_GLOBAL>' => array(
                'global_headers' => array('add' => array(
                    0 => 'Content-Security-Policy: default-src \'none\'; img-src \'self\'; script-src \'self\'; connect-src \'none\'; style-src \'self\'; object-src \'none\'; frame-ancestors \'none\'; form-action \'self\'; font-src \'self\'; base-uri \'self\';',
                    1 => 'x-frame-options: DENY',
                    2 => 'x-content-type-options: nosniff',
                    3 => 'x-xss-protection: 1; mode=block',
                    4 => 'x-permitted-cross-domain-policies: none',
                    5 => 'referrer-policy: strict-origin-when-cross-origin',
                    6 => 'Access-Control-Allow-Origin: \'self\'',
                    7 => 'cross-origin-resource-policy: same-origin',
                    8 => 'Cross-Origin-Embedder-Policy: require-corp',
                    9 => 'Cross-Origin-Opener-Policy: same-origin',
                    10 => 'Expect-CT: enforce, max-age=86400',
                    11 => 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload',
                ), 'remove' => array(
                    0 => 'X-Powered-By',
                    1 => 'Server',
                    2 => 'X-AspNet-Version',
                    3 => 'X-AspNetMvc-Version',
                ),),
                'global_sris' => array(),
                'global_nonces' => array(),
                'global_csp' => array(),
                'global_rate_limiting' => NULL,
                'global_param_rules' => array(),
                'global_default_no_route_match_response' => array(
                    'page' => NULL,
                    'json' => NULL,
                    'xml' => NULL,
                    'text' => NULL,
                    'callback' => NULL,
                ),
            ),
            'request' => array(
                0 => 'pl_run_ini_sets',
                1 => 'pl_match_denied_exact_ips',
                2 => 'pl_match_denied_methods',
                3 => 'pl_match_denied_uas',
                4 => 'pl_https_kernel_dispatch',
            ),
            'post_response' => array(0 => 'pl_debug',),
        ),
    );
    require_once __DIR__ . '/vendor/autoload.php';
    set_exception_handler(function (\Throwable $e) use (&$c) {
        \funk_default_exception_handler($c, $e);
    });
    register_shutdown_function(function () use (&$c) {
        \funk_default_register_shutdown_function($c);
    });
    function funak_handle_uncaught_exception(&$c, $e)
    {
        $testing = "tset";
    }
    function funak_set_register_shutdown_function(&$c)
    {
        $testing = "tset2";
    }
    function funk_validate_test(&$c)
    {
        if (empty($c['email'])) {
            return false;
        }
        return filter_var($c['email'], FILTER_VALIDATE_EMAIL);
    }
    function funk_use_class(&$c, $objClassFolder, $newObjectOrExistingObject, $instanceKey = null)
    {
        if (!in_array($objClassFolder, ['vendor', 'classes'])) {
            $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` received invalid $objClassFolder Value. Must be STRING (either "vendor" or "classes").';
            return null;
        }
        if (!is_object($newObjectOrExistingObject) && !is_string($newObjectOrExistingObject)) {
            $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` received invalid $newObjectOrExistingObject Value. Must be STRING (object to GET) or OBJECT (to SET).';
            return null;
        }
        if (is_string($newObjectOrExistingObject)) {
            if (isset($c['INSTANCES'][$objClassFolder][$newObjectOrExistingObject])) {
                return $c['INSTANCES'][$objClassFolder][$newObjectOrExistingObject];
            } else {
                $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` did not find the requested instance `' . $newObjectOrExistingObject . '` in the `' . $objClassFolder . '` Folder. Typo and/or not set first?';
                return null;
            }
        } else if (is_object($newObjectOrExistingObject)) {
            if (!is_string($instanceKey) || empty($instanceKey)) {
                $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` received invalid $instanceKey Value. Must be NON-EMPTY STRING when setting a new object instance.';
                return null;
            }
            if (isset($c['INSTANCES'][$objClassFolder][$instanceKey])) {
                if (!FUNKPHP_ALLOW_INSTANCE_OVERWRITE) {
                    $c['err']['CLASSES']['funk_use_class()'][] = 'The `funk_use_class()` cannot set the instance for key `' . $instanceKey . '` in the `' . $objClassFolder . '` Folder as it already exists! Overwriting existing instances is not allowed.';
                    $err = 'The `funk_use_class()` cannot set the instance for key `' . $instanceKey . '` in the `' . $objClassFolder . '` Folder as it already exists! Overwriting existing instances is not allowed. Change to: `define("FUNKPHP_ALLOW_INSTANCE_OVERWRITE",true)` in `config/_all.php` (below $c["INSTANCES"] to `true` if you want to allow overwriting existing instances!';
                    \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
                } else {
                    $c['INSTANCES'][$objClassFolder][$instanceKey] = $newObjectOrExistingObject;
                    return $c['INSTANCES'][$objClassFolder][$instanceKey];
                }
            } else {
                $c['INSTANCES'][$objClassFolder][$instanceKey] = $newObjectOrExistingObject;
                return $c['INSTANCES'][$objClassFolder][$instanceKey];
            }
        }
        return null;
    }
    function funk_session_started_or_start_it(&$c)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        if (($c['SESSION']['driver'] ?? 'files') === 'redis') {
            \funk_connect_redis_infrastructure($c);
        }
        session_set_cookie_params(['lifetime' => 28800, 'path' => '/', 'domain' => 'webdev.local', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax',]);
        if (!session_start()) {
            $err = 'Tell The Developer: FAILED to Start Session-based Cookie Session. Please check $c[\'INI_SETS\'] and/or $c[\'COOKIES\'] in the Global Configuration `funkphp/config/_all.php` File and adjust the values accordingly if needed!';
            \funk_use_error_json_or_page($c, 500, ['internal_error' => $err], '500', $err);
        }
    }
    function funk_session_get(&$c, string $key, $default = null)
    {
        \funk_session_started_or_start_it($c);
        return $_SESSION[$key] ?? $default;
    }
    function funk_session_set(&$c, string $key, $value): void
    {
        \funk_session_started_or_start_it($c);
        $_SESSION[$key] = $value;
    }
    function funk_session_destroy(&$c, $set_other_cookies_with_h_setcookie_as_array = [], $redirect = null)
    {
        if (session_id() || session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_unset();
            session_destroy();
            \funk_session_cookie_set(session_name(), '', time() - 3600);
            \funk_session_cookie_set("csrf", '', time() - 3600);
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
        if (\funk_session_get($c, '_funk_csrf') === null) {
            $_SESSION['_funk_csrf'] = [];
        }
        $token = hash('sha256', random_bytes(32));
        $_SESSION['_funk_csrf'][$token] = ['uri' => $currentUri, 'expires' => ($lifetimeSeconds === null) ? null : (time() + $lifetimeSeconds)];
        if (count($_SESSION['_funk_csrf']) > 99) {
            array_shift($_SESSION['_funk_csrf']);
        }
        return $token;
    }
    function funk_generate_random_password(&$c, $length = 20, $returnHashed = false)
    {
        $randomizer = new Random\Randomizer();
        $lowers = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',];
        $uppers = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',];
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',];
        $special = ['!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~',];
        $all = array_merge($lowers, $uppers, $numbers, $special);
        $total = count($all) - 1;
        $password = '';
        while (strlen($password) < $length) {
            $randomCharIndex = $randomizer->getInt(0, $total);
            $password .= $all[$randomCharIndex];
        }
        $password = $randomizer->shuffleArray(str_split($password));
        $password = implode('', $password);
        if ($returnHashed) {
            return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }
        return $password;
    }
    function funk_generate_random_number(&$c, $length = 10)
    {
        $randomizer = new Random\Randomizer();
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',];
        $total = count($numbers) - 1;
        $number = '';
        $randomCharIndex = $randomizer->getInt(1, $total);
        $number .= $numbers[$randomCharIndex];
        while (strlen($number) < $length) {
            $randomCharIndex = $randomizer->getInt(0, $total);
            $number .= $numbers[$randomCharIndex];
        }
        return (int)$number;
    }
    function funk_generate_random_user_id(&$c, $length = 96)
    {
        $randomizer = new Random\Randomizer();
        $lowers = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',];
        $uppers = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',];
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',];
        $all = array_merge($lowers, $uppers, $numbers);
        $total = count($all) - 1;
        $user_id = '';
        while (strlen($user_id) < $length) {
            if (strlen($user_id) % 24 == 0 && strlen($user_id) != 0) {
                $user_id .= '-';
                continue;
            }
            $randomCharIndex = $randomizer->getInt(0, $total);
            $user_id .= $all[$randomCharIndex];
        }
        return $user_id;
    }
    function funk_generate_random_csrf(&$c, $length = 384)
    {
        $randomizer = new Random\Randomizer();
        $lowers = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',];
        $uppers = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',];
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9',];
        $all = array_merge($lowers, $uppers, $numbers);
        $total = count($all) - 1;
        $csrf = '';
        while (strlen($csrf) < $length) {
            $randomCharIndex = $randomizer->getInt(0, $total);
            $csrf .= $all[$randomCharIndex];
        }
        return $csrf;
    }
    function funk_use_error_raw_html(&$c, int $errCode, string $errMsg)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($erCode) || !is_int($erCode) || $erCode < 100 || $erCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_html_string()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($errMsg) || !is_string($errMsg) || empty($errMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_html_string()` Function. This should be a non-empty string!');
        }
        http_response_code($errCode);
        header('Content-Type: text/html; charset=utf-8');
        echo $errMsg;
        exit();
    }
    function funk_use_error_raw_plain(&$c, int $errCode, string $errMsg)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_plain_text()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($errMsg) || !is_string($errMsg) || empty($errMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_plain_text()` Function. This should be a non-empty string!');
        }
        http_response_code($errCode);
        header('Content-Type: text/plain; charset=utf-8');
        echo $errMsg;
        exit();
    }
    function funk_use_error_xml(&$c, int $errCode, string $errMsg)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_xml()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($errMsg) || !is_string($errMsg) || empty($errMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_xml()` Function. This should be a non-empty string!');
        }
        http_response_code($errCode);
        header('Content-Type: application/xml; charset=utf-8');
        echo $errMsg;
        exit();
    }
    function funk_use_error_page(&$c, int $errCode, string $errMsg, string $pageName)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_page()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($errMsg) || !is_string($errMsg) || empty($errMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_page()` Function. This should be a non-empty string!');
        }
        if (!isset($pageName) || !is_string($pageName) || empty($pageName) || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_handle_error_page()` Function. This should be a non-empty string that is also a readable file inside `/pages/compiled/[errors]/` directory!');
        }
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
    function funk_use_error_callback(&$c, int $errCode, string $errMsg, string $callbackName, $optionalCallbackData = null)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_callback()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($errMsg) || !is_string($errMsg) || empty($errMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_callback()` Function. This should be a non-empty string!');
        }
        if (!isset($callbackName) || !is_string($callbackName) || empty($callbackName) || !is_callable($callbackName)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_handle_error_callback()` Function. This should be a non-empty string that is also callable!');
        }
        http_response_code($errCode);
        try {
            $callbackName($c, $errMsg, $optionalCallbackData);
        } catch (\Throwable $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_callback()` Function with the following Error Message:`' . $e->getMessage() . '`.');
        }
        exit();
    }
    function funk_use_error_throw(&$c, string $exceptionErrMsg)
    {
        if (!isset($exceptionErrMsg) || !is_string($exceptionErrMsg) || empty($exceptionErrMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_handle_error_throw()` Function. This should be a non-empty string!');
        }
        throw new Exception($exceptionErrMsg);
    }
    function funk_use_error_json(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_handle_error_json()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($jsonObjectOrStringThatReturnsJSON) || (!is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON) && (!is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)))) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_handle_error_json()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
        }
        http_response_code($errCode);
        $jsonData = $jsonObjectOrStringThatReturnsJSON;
        if (is_string($jsonData) && is_callable($jsonData)) {
            try {
                $jsonData = $jsonData($c);
            } catch (\Throwable $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
            }
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_handle_error_json()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
        }
        exit();
    }
    function funk_use_error_json_or_page(&$c, int $errCode, $jsonObjectOrStringThatReturnsJSON, string $pageName, string $pageErrMsg)
    {
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page()` Function. This should be an Integer between 100 and 599!');
        }
        if (!isset($pageErrMsg) || !is_string($pageErrMsg) || empty($pageErrMsg)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String!');
        }
        if (!isset($jsonObjectOrStringThatReturnsJSON) || (!is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON) && (!is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)))) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
        }
        if (!isset($pageName) || !is_string($pageName) || empty($pageName) || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page()` Function. This should be a Non-Empty String and it must exist as a file in the `src/funkphp/pages/compiled/[errors]` directory!');
        }
        http_response_code($errCode);
        if (isset($c['req']['accept']) && is_string($c['req']['accept']) && !empty($c['req']['accept']) && (str_contains($c['req']['accept'], 'application/json') || str_contains($c['req']['accept'], 'text/json'))) {
            $jsonData = $jsonObjectOrStringThatReturnsJSON;
            if (is_string($jsonData) && is_callable($jsonData)) {
                try {
                    $jsonData = $jsonData($c);
                } catch (\Throwable $e) {
                    \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
                }
            }
            header('Content-Type: application/json; charset=utf-8');
            try {
                echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\JsonException $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
            }
        } else {
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
    function funk_use_error_json_or_page_or_callback(&$c, int $errCode, string $errMsgForPageAndCallback, $jsonObjectOrStringThatReturnsJSON, string $pageName, string $callableName, $optionalCallbackData = null)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        if (!isset($errCode) || !is_int($errCode) || $errCode < 100 || $errCode > 599) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Code Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be an integer between 100 and 599!');
        }
        if (!isset($errMsgForPageAndCallback) || !is_string($errMsgForPageAndCallback) || empty($errMsgForPageAndCallback)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Error Message Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String!');
        }
        if (!isset($pageName) || !is_string($pageName) || empty($pageName) || !is_readable(ROOT_PAGES_ERRORS . '/' . $pageName . '.php')) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Page Filename Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String and it must exist as a file in the `src/funkphp/pages/compiled/[errors]` directory!');
        }
        if (!isset($callableName) || !is_string($callableName) || empty($callableName) || !is_callable($callableName)) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid Callback Name Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be a Non-Empty String that is also Callable!');
        }
        if (!isset($jsonObjectOrStringThatReturnsJSON) || (!is_array($jsonObjectOrStringThatReturnsJSON) && !is_object($jsonObjectOrStringThatReturnsJSON) && (!is_string($jsonObjectOrStringThatReturnsJSON) || !is_callable($jsonObjectOrStringThatReturnsJSON)))) {
            \critical_err_json_or_html(500, 'Tell the Developer: No Valid JSON Data or Callable Provided to `funk_use_error_json_or_page_or_callback()` Function. This should be either a Non-Empty Array/Object OR a Non-Empty String that is also Callable which returns a Valid JSON Payload!');
        }
        http_response_code($errCode);
        if (isset($c['req']['accept']) && is_string($c['req']['accept']) && !empty($c['req']['accept']) && str_contains($c['req']['accept'], 'text/html')) {
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; img-src 'self'; script-src 'self'; connect-src 'none'; style-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; font-src 'self'; base-uri 'self';");
            try {
                $custom_error_message = $errMsgForPageAndCallback;
                include_once ROOT_PAGES_ERRORS . '/' . $pageName . '.php';
            } catch (\Throwable $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` Function while trying to return a Custom Error Page. Yes, an error to show an error occured:`' . $e->getMessage() . '`.');
            }
        } else if (isset($c['req']['accept']) && is_string($c['req']['accept']) && !empty($c['req']['accept']) && (str_contains($c['req']['accept'], 'application/json') || str_contains($c['req']['accept'], 'text/json'))) {
            $jsonData = $jsonObjectOrStringThatReturnsJSON;
            if (is_string($jsonData) && is_callable($jsonData)) {
                try {
                    $jsonData = $jsonData($c);
                } catch (\Throwable $e) {
                    \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the JSON Callable:`' . $e->getMessage() . '` that was called using the `funk_use_error_json_or_page_or_callback()` Function!');
                }
            }
            header('Content-Type: application/json; charset=utf-8');
            try {
                echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\JsonException $e) {
                \critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` While Encoding the Provided Data to JSON:`' . $e->getMessage() . '`');
            }
        } else {
            try {
                $callableName($c, $errMsgForPageAndCallback, $optionalCallbackData);
            } catch (\Throwable $e) {
                critical_err_json_or_html(500, 'Tell the Developer: An Exception Occurred Inside the `funk_use_error_json_or_page_or_callback()` Function with the following Error Message:`' . $e->getMessage() . '`');
            }
        }
        exit();
    }
    function funk_collect_output_message(&$c, $level, $key, $message)
    {
        if (!isset($level) || !is_string($level) || empty($level) || !in_array(strtolower($level), ['info', 'warning', 'error', 'debug', 'critical', 'notice', 'alert', 'emergency', 'success', 'failure',]) || !isset($key) || !is_string($key) || empty($key) || !isset($message) || !is_string($message) || empty($message)) {
            error_log('FunkPHP: Invalid Parameters Passed to funk_collect_output_message() Function. Expected 3 Non-Empty String:s: [Level, Key, Message]!');
            return;
        }
        $c['req']['user_messages'][] = ['level' => strtolower($level), 'key' => mb_strtoupper($key), 'message' => $message,];
    }
    function funk_use_log(&$c, string $logMessage, string $logType = 'WARN'): void
    {
        if (!isset($c['req']['log']) || !is_array($c['req']['log'])) {
            $c['req']['log'] = [];
            \funk_use_log($c, 'The Log Array Did Not Exist, so it was Created Automatically!', 'INFO');
            return;
        }
        $c['req']['log'][] = ['timestamp' => time(), 'type' => strtoupper($logType), 'message' => $logMessage];
        return;
    }
    function funk_save_log(&$c): void
    {
        if (!empty($c['req']['log'])) {
            error_log("--- FUNKPHP POST-RESPONSE LOGS ---");
            error_log(print_r($c['req']['log'], true));
            error_log("--- END LOGS ---");
        }
        return;
    }
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
    function funk_skip_post_response(&$c)
    {
        $c['req']['skip_post_response'] = true;
        ob_end_clean();
        return;
    }
    function funk_abort_pipeline_request(&$c)
    {
        $c['req']['current_pipeline'] = null;
        $c['req']['keep_running_pipeline'] = false;
        $c['<ENTRY>']['pipeline']['request'] = null;
        return;
    }
    function funk_abort_pipeline_post_response(&$c)
    {
        $c['req']['current_pipeline'] = null;
        $c['req']['keep_running_pipeline'] = false;
        $c['<ENTRY>']['pipeline']['post_response'] = null;
        return;
    }
    function funk_abort_middlewares(&$c)
    {
        $c['req']['current_middleware'] = null;
        $c['req']['keep_running_middlewares'] = false;
        $c['req']['matched_middlewares'] = null;
        return;
    }
    function funk_params_are(&$c, $args)
    {
        if (!isset($args) || !is_array($args)) {
            $c['err']['ROUTES']['funk_param_are'][] = 'No Parameters provided (by the Developer) to Validate for Current Route!';
            return null;
        }
        if (!isset($c['req']['params']) || !is_array($c['req']['params'])) {
            $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters (from the Visitor) to Validate for Current Route!';
            return null;
        }
        $params = $c['req']['params'];
        return true;
    }
    function funk_param_is(&$c, $param_key, $validation)
    {
        if (!isset($c['req']['params']) || !is_array($c['req']['params'])) {
            $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameters to Validate for Current Route!';
            return null;
        }
        $param = $c['req']['params'][$param_key] ?? null;
        if ($param === null) {
            $c['err']['ROUTES']['funk_param_is'][] = 'No matched Dynamic Parameter with Key `' . $param_key . '` to Validate for Current Route!';
            return null;
        }
        return true;
    }
    function funk_param_is_string(&$c, $param_key)
    {
        if (!isset($param_key)) {
            $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
            return null;
        }
        $param = $c['req']['params'][$param_key] ?? null;
        return is_string($param) && !empty($param);
    }
    function funk_param_is_number(&$c, $param_key)
    {
        if (!isset($param_key)) {
            $c['err']['ROUTES']['funk_param_is_string'][] = 'No Parameter Key provided to Validate for Current Route!';
            return null;
        }
        $param = $c['req']['params'][$param_key] ?? null;
        return is_numeric($param);
    }
    function funk_param_is_integer(&$c, $param_key)
    {
        if (!isset($param_key)) {
            $c['err']['ROUTES']['funk_param_is_integer'][] = 'No Parameter Key provided to Validate for Current Route!';
            return null;
        }
        $param = $c['req']['params'][$param_key] ?? null;
        return is_int($param) || intval($param) == $param;
    }
    function funk_param_is_float(&$c, $param_key)
    {
        if (!isset($param_key)) {
            $c['err']['ROUTES']['funk_param_is_float'][] = 'No Parameter Key provided to Validate for Current Route!';
            return null;
        }
        $param = $c['req']['params'][$param_key] ?? null;
        return is_float($param) || (is_numeric($param) && strpos($param, '.') !== false && floatval($param) == $param);
    }
    function funk_param_is_regex(&$c, $param_key, $regexStr)
    {
        if (!isset($param_key)) {
            $c['err']['ROUTES']['funk_param_is_regex'][] = 'No Parameter Key provided to Validate for Current Route!';
            return null;
        }
        if (!isset($regexStr) || !is_string($regexStr) || empty($regexStr)) {
            $c['err']['ROUTES']['funk_param_is_regex'][] = 'No Regex String provided to Validate for Current Route!';
            return null;
        }
        $param = $c['req']['params'][$param_key] ?? "";
        return preg_match($regexStr, $param) === 1;
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
                    $c['err']['DATABASES']['funk_db_conn'][] = 'Connection failed for ' . $dbKey . ': ' . pg_last_error();
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
    function funk_validation_validate_rules(&$c, $inputValue, $fullFieldName, array $rules, array &$currentErrPath): void
    {
        $stop = array_key_exists('stop', $rules);
        $nullable = array_key_exists('nullable', $rules);
        $required = array_key_exists('required', $rules);
        $field = array_key_exists('field', $rules);
        if ($field) {
            $fullFieldName = $rules['field']['value'] ?? $fullFieldName;
            unset($rules['field']);
        }
        if ($required) {
            $required = $rules['required'];
            unset($rules['required']);
        }
        if ($stop) {
            unset($rules['stop']);
        }
        if ($nullable && $inputValue === null) {
            return;
        }
        if ($required) {
            $ruleValue = $required['value'] ?? null;
            $customErr = $required['err_msg'] ?? null;
            $error = \funk_validate_required($fullFieldName, $inputValue, $ruleValue, $customErr);
            if ($error !== null) {
                $currentErrPath['required'] = $error;
                $c['v_ok'] = false;
                if (isset($c['v_config']['stop_all_on_first_error'])) {
                    $c['v_config']['stop_all_on_first_error'] = true;
                    return;
                }
                if ($stop) {
                    return;
                }
            }
        }
        $categorizedDataTypeRules = ['string_types' => ['string' => true, 'char' => true, 'email' => true, 'email_custom' => true, 'password' => true, 'password_custom' => true, 'password_confirm' => true, 'json' => true, 'url' => true, 'ip' => true, 'ip4' => true, 'ip6' => true, 'uuid' => true, 'phone' => true, 'date' => true,],   'number_types' => ['digit' => true, 'integer' => true, 'float' => true, 'number' => true,],    'array_types' => ['array' => true, 'list' => true, 'set' => true, 'enum' => true,], 'file_types' => ['file' => true, 'image' => true, 'video' => true, 'audio' => true,],      'complex_types' => ['null' => true, 'object' => true, 'unchecked' => true, 'checked' => true, 'boolean' => true,],];
        $foundTypeRule = null;
        $foundTypeCat = null;
        foreach ($rules as $ruleName => $ruleConfig) {
            if (isset($categorizedDataTypeRules['string_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'string_types';
                break;
            } elseif (isset($categorizedDataTypeRules['number_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'number_types';
                break;
            } elseif (isset($categorizedDataTypeRules['array_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'array_types';
                break;
            } elseif (isset($categorizedDataTypeRules['complex_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'complex_types';
                break;
            } elseif (isset($categorizedDataTypeRules['file_types'][$ruleName])) {
                $foundTypeRule = $ruleName;
                $foundTypeCat = 'file_types';
                break;
            }
        }
        if ($foundTypeRule) {
            $validatorFn = 'funk_validate_' . $foundTypeRule;
            $ruleConfig = $rules[$foundTypeRule];
            $ruleValue = $ruleConfig['value'] ?? null;
            $customErr = $ruleConfig['err_msg'] ?? null;
            if ($foundTypeRule === 'password' || 'password_custom' === $foundTypeRule) {
                $c['v_config']['passwords_to_match'][$fullFieldName] = is_string($inputValue) ? (string)$inputValue : null;
            } elseif ($foundTypeRule === 'password_confirm') {
                $ruleValue = $c['v_config']['passwords_to_match'][$ruleValue] ?? null;
            }
            $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);
            if ($error !== null) {
                $currentErrPath[$foundTypeRule] = $error;
                $c['v_ok'] = false;
                if (isset($c['v_config']['stop_all_on_first_error'])) {
                    $c['v_config']['stop_all_on_first_error'] = true;
                    return;
                }
                if (isset($rules['stop'])) {
                    return;
                }
            }
            unset($rules[$foundTypeRule]);
        } else {
            $currentErrPath[$foundTypeRule] = "This is unknown data type: '{$foundTypeRule}' in field '{$fullFieldName}'. Please tell the Developer about it since validation cannot continue without a valid data type provided!";
            $c['err']['VALIDATIONS']['funk_validation_validate_rules'][] = "Unknown Data Type Validation Rule: '{$foundTypeRule}' for field '{$fullFieldName}'.";
            $c['v_ok'] = false;
            if (isset($c['v_config']['stop_all_on_first_error'])) {
                $c['v_config']['stop_all_on_first_error'] = true;
                return;
            }
            return;
        }
        $mappedRulesBasedTypeCategory = ['string_types' => ['min' => 'minlen', 'max' => 'maxlen', 'exact' => 'exactlen', 'between' => 'betweenlen', 'size' => 'sizelen',], 'number_types' => ['min' => 'minval', 'max' => 'maxval', 'exact' => 'exactval', 'between' => 'betweenval', 'size' => 'sizeval',], 'array_types' => ['count' => 'arraycount', 'min' => 'mincount', 'max' => 'maxcount', 'exact' => 'exactcount', 'between' => 'betweencount', 'size' => 'sizecount',], 'complex_types' => [], 'file_types' => ['min' => 'min_filesize', 'max' => 'max_filesize', 'exact' => 'exact_filesize', 'between' => 'between_filesize', 'size' => 'size_filesize',],];
        if (isset($c['v_config']['source']) && $c['v_config']['source'] === 'GET' && $foundTypeCat === 'number_types') {
            if (is_numeric($inputValue)) {
                if ($foundTypeRule === 'digit') {
                    $inputValue = (int)$inputValue ?? null;
                } elseif ($foundTypeRule === 'integer') {
                    $inputValue = intval($inputValue) ?? null;
                } elseif ($foundTypeRule === 'float') {
                    $inputValue = floatval($inputValue) ?? null;
                } elseif ($foundTypeRule === 'number') {
                    $inputValue = floatval($inputValue) ?? null;
                }
            }
        }
        foreach ($rules as $rule => $ruleConfig) {
            $ruleValue = $ruleConfig['value'];
            $customErr = $ruleConfig['err_msg'];
            $errorKey = $rule;
            if (isset($mappedRulesBasedTypeCategory[$foundTypeCat][$rule])) {
                $rule = $mappedRulesBasedTypeCategory[$foundTypeCat][$rule];
            }
            $validatorFn = 'funk_validate_' . $rule;
            if (function_exists($validatorFn)) {
                $error = $validatorFn($fullFieldName, $inputValue, $ruleValue, $customErr);
                if ($error !== null) {
                    $currentErrPath[$errorKey] = $error;
                    $c['v_ok'] = false;
                    if (isset($c['v_config']['stop_all_on_first_error'])) {
                        $c['v_config']['stop_all_on_first_error'] = true;
                        return;
                    }
                    if ($stop) {
                        return;
                    }
                }
            } else {
                $currentErrPath[$foundTypeRule] = "This is unknown data type: '{$foundTypeRule}' in field '{$fullFieldName}'. Please tell the Developer about it. Validation will continue though!";
                $c['err']["VALIDATIONS"]['funk_validation_validate_rules'][] = "Unknown Data Validation Rule: '{$foundTypeRule}' for field '{$fullFieldName}'.";
                $c['v_ok'] = false;
                if (isset($c['v_config']['stop_all_on_first_error'])) {
                    $c['v_config']['stop_all_on_first_error'] = true;
                    return;
                }
            }
        }
    };
    function funk_load_sql(&$c, $sqlHandler, $sqlFunction)
    {
        if (!is_string($sqlHandler) || !is_string($sqlFunction)) {
            $c['err']['SQL']['funk_use_sql'][] = 'funk_use_sql() needs Valid Strings for `\$sqlHandler` and `\$sqlFunction`. First is the SQL Handler File Name `s_FileName` without extension and second is the SQL Function Name `s_FunctionName`!';
            return false;
        }
        $sqlFunk = null;
        if (isset($c['dispatchers']['sql'][$sqlHandler])) {
            if (!is_callable($c['dispatchers']['sql'][$sqlHandler])) {
                $c['err']['SQL']['funk_use_sql'][] = 'Already Loaded SQL Handler `' . $sqlHandler . '` is not callable. Has it been mutated after first loading/use?';
                return false;
            }
            $sqlFunk = $c['dispatchers']['sql'][$sqlHandler]($c, $sqlFunction) ?? null;
            if ($sqlFunk === null) {
                $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` did not return the SQL Handler Function `' . $sqlFunction . '`. Does it exist in the File as a callable function with the correct name?';
                return false;
            } else {
                return $sqlFunk;
            }
        } else {
            if (!is_readable(ROOT_FOLDER . '/data/sql/' . $sqlHandler . '.php')) {
                $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` not found or not readable. Does the file exist in the `sql` directory and/or is it forbidden to read/access?';
                return false;
            }
            $sqlFile = include_once ROOT_FOLDER . '/data/sql/' . $sqlHandler . '.php';
            if (!is_callable($sqlFile)) {
                $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` was loaded but did not return a callable function. It should return a function that accepts `$c` and `$sqlFunction` as parameters which it then checks if it exists in current scope and then calls and returns its return value!';
                return false;
            }
            $c['dispatchers']['sql'][$sqlHandler] = $sqlFile;
            $sqlFunk = $c['dispatchers']['sql'][$sqlHandler]($c, $sqlFunction) ?? null;
            if ($sqlFunk === null) {
                $c['err']['SQL']['funk_use_sql'][] = 'SQL Handler File `' . $sqlHandler . '.php` was loaded and is callable but did not return the SQL Handler Function `' . $sqlFunction . '`. Does it exist in the File as a callable function with the correct name?';
                return false;
            }
            return $sqlFunk;
        }
    }
    function funk_use_sql(&$c, $sqlArrayKey, $inputData = null, $hydrateDataAfter = false)
    {
        $longDefaultErr = 'The `\$sqlArrayKey` must be a Valid Array containing the following keys: `qtype`, `sql`, `hydrate`, `bparam` and `fields`. `qtype` is the SQL Query Type (e.g., SELECT, INSERT, UPDATE, DELETE), `sql` is the SQL Query String, `hydrate` is the Hydration Array Key, `bparam` is the Bind Parameters Array Key and `fields` is the Matching Validated Data Input Fields Array Key. Only `qtype` and `sql` must contain actual values that would be used whereas the rest are optional meaning they must exist as array keys but can be empty or null!';
        if (!is_array($sqlArrayKey)) {
            $c['err']['SQL']['funk_use_sql'][] = $longDefaultErr;
            return false;
        }
        if (!array_key_exists('qtype', $sqlArrayKey) || !array_key_exists('sql', $sqlArrayKey) || !array_key_exists('hydrate', $sqlArrayKey) || !array_key_exists('bparam', $sqlArrayKey) || !array_key_exists('fields', $sqlArrayKey)) {
            $c['err']['SQL']['funk_use_sql'][] = $longDefaultErr;
            return false;
        }
        if (!isset($c['db']) || $c['db'] === null || !($c['db'] instanceof mysqli)) {
            $c['err']['SQL']['funk_use_sql'][] = 'Database Connection `$c[\'db\']` is NOT Set, IS NULL or NOT a Valid MySQLi Object Instance. Connect to the Database before calling this Function!';
            return false;
        }
        $validQueryTypes = ['SELECT' => [], 'INSERT' => [], 'UPDATE' => [], 'DELETE' => [],];
        if (!isset($validQueryTypes[$sqlArrayKey['qtype']])) {
            $c['err']['SQL']['funk_use_sql'][] = 'Invalid SQL Query Type Provided. Valid Query Types are: `SELECT`,`UPDATE`,`INSERT` & `DELETE` in current version of FunkPHP!';
            return false;
        }
        return true;
    }
    function funk_use_hydrate_sql(&$c, $hydrateKey, $fetchedData) {}
    function funk_use_validation(&$c, $validationHandler, $validationFunction, $source)
    {
        if (!is_string($validationHandler) || !is_string($validationFunction)) {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid string for `\$validationHandler` and `\$validationFunction`. First is the Validation Handler File Name `v_FileName` without extension and second is the Validation Function Name `v_FunctionName`!';
            return false;
        }
        $optimizedValidationArray = null;
        if (isset($c['dispatchers']['validation'][$validationHandler])) {
            $optimizedValidationArray = $c['dispatchers']['validation'][$validationHandler]($c, $validationFunction) ?? null;
        } else {
            $validationFile = ROOT_FOLDER . '/data/validations/' . $validationHandler . '.php';
            if (is_readable($validationFile)) {
                $validationDataFromFile = include_once $validationFile;
                if (is_callable($validationDataFromFile)) {
                    $c['dispatchers']['validation'][$validationHandler] = $validationDataFromFile;
                    $optimizedValidationArray = $c['dispatchers']['validation'][$validationHandler]($c, $validationFunction) ?? null;
                } else {
                    $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Handler File ``' . $validationHandler . '.php` did not return a callable function.';
                    return false;
                }
            } else {
                $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Handler File `' . $validationHandler . '.php` not found or not readable! (Reminder: a single string is parsed as `v_file=>v_function`!)';
                return false;
            }
        }
        if ($source === "FILES") {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Use Validation Function `funk_use_validation_files(&\$c, \$optimizedValidationArray)` instead to validate `\$_FILES`!';
            return false;
        }
        if (!is_array($optimizedValidationArray) || empty($optimizedValidationArray)) {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a non-empty array for `\$optimizedValidationArray`!';
            return false;
        }
        $allowedSources = ['GET' => [], 'POST' => [], 'JSON' => []];
        if (!is_string($source) || !isset($allowedSources[$source])) {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid string for `\$source` (\"GET\", \"POST\" or \"JSON\" - uppercase only)!';
            return false;
        }
        $inputData = null;
        if ($source === 'GET') {
            $inputData = $_GET ?? null;
            $c['v_config']['source'] = "GET";
        } elseif ($source === 'POST') {
            $inputData = $_POST ?? null;
            $c['v_config']['source'] = "POST";
        } elseif ($source === 'JSON') {
            $inputData = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid decoded JSON string for `\$source`!';
                return false;
            }
            $c['v_config']['source'] = "JSON";
        }
        if (!is_array($inputData) || empty($inputData)) {
            $c['err']['VALIDATIONS']['funk_use_validation'][] = 'Validation Function needs a valid non-empty array for `\$inputData`!';
            return false;
        }
        var_dump('TEST DATA(GET/POST/JSON):', $inputData);
        $c['v_ok'] = true;
        $c['v'] = [];
        $c['v_data'] = [];
        \funk_validation_recursively_improved($c, $inputData, $optimizedValidationArray, $c['v'], $c['v_data'],);
        if ($c['v_ok']) {
            $c['v'] = null;
            return true;
        }
        if (isset($c['v_config']['show_v_data_only_if_all_valid']) && $c['v_config']['show_v_data_only_if_all_valid'] === true) {
            $c['v_data'] = null;
        }
        return false;
    }
    function funk_use_validation_files(&$c, $optimizedValidationArray)
    {
        if (!is_array($optimizedValidationArray) || empty($optimizedValidationArray)) {
            $c['err']['VALIDATIONS']['funk_use_validation_files'][] = "Files Validation Function must receive a non-empty array for `\$optimizedValidationArray`!";
            return false;
        }
        if (!is_array($_FILES) || empty($_FILES)) {
            $c['err']['VALIDATIONS']['funk_use_validation_files'][] = "Files Validation Function must receive a non-empty array for `\$_FILES`!";
            return false;
        }
        if ($c['v_ok']) {
            return true;
        }
        return false;
    }
}
