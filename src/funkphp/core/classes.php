<?php

/**
 * -----------------------------
 * FUNKPHP INTERNAL CLASSES
 * -----------------------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/
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
            'funk_internal_session_started_or_start_it',
            'funk_session_cookie_set',
            'funk_default_exception_handler',
            'register_shutdown_function',
            'set_exception_handler',
            'set_error_handler',
            'funk_internal_handle_no_route_match',
            'funk_internal_send_headers',
            'funk_internal_return_response',
            'funk_internal_exception_handler',
            'funk_internal_error_handler',
            'funk_internal_rate_limiter',
            'funk_internal_is_ip_trusted',
        ],
        'reserved_group_names' => ['sql', 'query', 'validation'],
        'reserved_fn_names' => ['cli_dump', 'cli_dd', 'dd'],
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
        ],
        'drivers' => [
            'cache' => ['redis', 'memcached', 'file', 'apcu', 'array'],
            'ratelimit' => ['redis', 'memcached', 'file', 'apcu', 'array']
        ],
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
        'WARNINGS' => 0,
        'CONFIG' => [],
        'METHODS' => [],
        'COMPILATION' => ['errors' => [], 'warnings' => []],
        'FILES' => [],
        'INTERNAL' => [],
    ];
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
    // $compiled = The entire compiled code that can either be executed as is OR
    // be exported to the `/src/funkphp/FunkPHPDeployment.php` File!
    private array $routePrefixes = [
        'GET' => null,
        'POST' => null,
        'PUT' => null,
        'DELETE' => null,
        'PATCH' => null,
    ];
    private array $compiled = [
        'config' => [
            'runtime' => [
                'trusted_ip_proxies' => [
                    'ip4' => [
                        "173.245.48.0/20",
                        "103.21.244.0/22",
                        "103.22.200.0/22",
                        "103.31.4.0/22",
                        "141.101.64.0/18",
                        "108.162.192.0/18",
                        "190.93.240.0/20",
                        "188.114.96.0/20",
                        "197.234.240.0/22",
                        "198.41.128.0/17",
                        "162.158.0.0/15",
                        "104.16.0.0/13",
                        "104.24.0.0/14",
                        "172.64.0.0/13",
                        "131.0.72.0/22",
                    ],
                    'ip6' => [
                        "2400:cb00::/32",
                        "2606:4700::/32",
                        "2803:f800::/32",
                        "2405:b500::/32",
                        "2405:8100::/32",
                        "2a06:98c0::/29",
                        "2c0f:f248::/32"
                    ],
                ],
                'trusted_ip_headers' => [
                    'HTTP_CF_CONNECTING_IP',
                    'HTTP_X_FORWARDED_FOR',
                    'HTTP_X_REAL_IP'
                ],
                'debug' => [],
                'online' => false,
                'use_https' => false,
                'use_vendor' => true,
                'custom_exception_handler' => null,
                'custom_error_handler' => null,
                'custom_ip_resolver' => null,
                'custom_uri_normalizer' => null,
                'custom_https_kernel' => null,
                'request_ip_sources' => [],
                'request_form_spoof_methods' => ['PUT', 'PATCH', 'DELETE'],
                'ini_sets' => [],
            ],
            'NO_ROUTE_MATCH' => [],
            'pipes' => [
                'request' => [],
                'request-resolved' => [],
                'middlewares' => [],
                'middlewares-resolved' => [],
                'post_response' => [],
                'post_response-resolved' => [],
            ],
        ],
        'methods' => [],
        'routes' => ['trie' => [], 'trie_metadata' => []],
        'pages' => [],
        'data' => [],
        // This is the $c Variable that is then assigned automatically globally.
        'c' => [
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
                'matched_config' => null,
                'matched_params' => null,
                'matched_pipes' => [],
                'matched_middlewares' => null,
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
        ],
    ];

    // NAVIGATION VARIABLES+METHODS IN IDE ->config()
    private ?FunkConfig $configScope = null;
    private ?FunkRoutes $routesScope = null;
    // Default booleans for compile(), run()
    private bool $FUNKPHP_COMPILED = false;
    private bool $FUNKPHP_COMPILED_SUCCESS = false;
    private bool $FUNKPHP_RAN = false;
    private array $debug = [
        'ON_OR_OFF' => false,
        'SHOW_VALID_BATCHES' => false,
        'SHOW_INVALID_BATCHES' => false,
        'SHOW_CACHED' => false,
        'SHOW_COMPILED' => false,
        'SHOW_ALL' => false,
    ];

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
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Internal FunKPHP Constant Missing', 'err' => $err];
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
            || in_array($str, $this->FORBIDDEN['reserved_fn_names'])
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
            || (str_starts_with($str, 'group:cli_'))
            || (str_starts_with($str, 'group:funk_'))
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
            || (str_starts_with($str, 'group:cli_'))
            || (str_starts_with($str, 'group:funk_'))
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
            if (!isset($this->cached[$key])) {
                $this->cached[$key] = $this->file_status('/config', 'functions');
                $this->cached[$key]['?file_type'] = 'user-functions';
            }
        } elseif ($key === 'file_user_defined_classes') {
            if (!isset($this->cached[$key])) {
                $this->cached[$key] = $this->file_status('/config', 'classes', false, true);
                $this->cached[$key]['?file_type'] = 'user-classes';
            }
        } elseif ($key === 'file_user_defined_tables') {
            if (!isset($this->cached[$key])) {
                $this->cached[$key] = $this->file_status('/config', 'tables');
                $this->cached[$key][$optionalFileName]['?file_type'] = 'user-tables';
            }
        } elseif ($key === 'files_pipes_request') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/request', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'request';
            }
        } elseif ($key === 'files_pipes_post_response') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/post_response', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'post-response';
            }
        } elseif ($key === 'files_pipes_middlewares') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/middlewares', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'middleware';
            }
        } elseif ($key === 'files_routes') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pipes/routes', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'route';
            }
        } elseif ($key === 'files_pages') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'pages-uncompiled';
            }
        } elseif ($key === 'files_pages_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages/compiled', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'pages-compiled';
            }
        } elseif ($key === 'files_pages_components') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages/components', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'pages-components';
            }
        } elseif ($key === 'files_pages_layouts') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages/layouts', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'pages-layouts';
            }
        } elseif ($key === 'files_pages_partials') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/pages/partials', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'pages-partials';
            }
        } elseif ($key === 'files_data_sql') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/sql', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'data-sql-uncompiled';
            }
        } elseif ($key === 'files_data_query') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/query', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'data-query-uncompiled';
            }
        } elseif ($key === 'files_data_validation') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/validation', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'data-validation-uncompiled';
            }
        } elseif ($key === 'files_data_sql_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/compiled/sql', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'data-sql-compiled';
            }
        } elseif ($key === 'files_data_query_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/compiled/query', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'data-query-compiled';
            }
        } elseif ($key === 'files_data_validation_compiled') {
            if (!isset($this->cached[$key][$optionalFileName])) {
                $this->cached[$key][$optionalFileName] = $this->file_status('/data/compiled/validation', $optionalFileName);
                $this->cached[$key][$optionalFileName]['?file_type'] = 'data-validation-compiled';
            }
        } elseif ($key === 'file_core_functions') {
            if (!isset($this->cached[$key])) {
                $this->cached[$key] = $this->file_status('/core', 'functions');
                $this->cached[$key]['?file_type'] = 'core-functions';
            }
        } elseif ($key === 'file_manifest') {
            if (!isset($this->cached[$key])) {
                $this->cached[$key] = $this->file_status('/core', 'manifest');
                $this->cached[$key]['?file_type'] = 'core-manifest';
            }
        } else {
            $err = "[Class C->\$this->cachedCreateKeyIfNull()]: Unknown `{$key}` Value passed when it expected one of those defined in \$this->cached in Class C. Report this Internal Error to the Official FunkPHP Repositories.";
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Unknown Passed Value', 'err' => $err];
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
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid Array Value `$FNKeyFromCachedKey`', 'err' => $err];
            $this->errors['ERRORS']++;
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
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Expected a Non-Empty Associative Array', 'err' => $err];
        }
        // Validate OR add warnings if CLASS is safe by checking certain Key Values
        else {
        }
    }
    /*** !!! PRIVATE HELPER FUNCTIONS FOR MANY batch<VARIANTS> ABOVE !!! */
    // Also used by compile() & run() below!
    private function file_status(string $folder, string $file, bool $useExactFilePathInstead = false, bool $OnlyGetClasses = false)
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
                    if (!$OnlyGetClasses) {
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
                $this->errors['ERRORS']++;
                $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Local File Reading Failed', 'err' => "Class C->file_status()] - FAILED to read Folder+File Path:`{$folder}{$file}` when it should have been possible. Verify Folder/File Permissions in Your Project."];
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
        $braceDepth = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->id !== T_FUNCTION) {
                continue;
            }
            if ($tok->text === '{') {
                $braceDepth++;
            } elseif ($tok->text === '}') {
                $braceDepth--;
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
            $fatalErr = "Function File Error in {$contextLabel}: Parsed File Data `$relativePath` as an Array is EITHER A Numbered Array when it should be an Associative Array OR it is Completely Empty. (This is possibly an Internal FunkPHP Error - try regenerate default files in `/src/funkphp/config/` and try again)";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Cannot Parse Expected Function File Data', $fatalErr);
            return $fatalErr;
        }
        if (empty($fileData['file_exists'])) {
            $fatalErr = "Function File Error in {$contextLabel}: Expected File `$relativePath` does NOT exist. Validate/Review File Reading Permissions for the Path mentioned here in the Error in order to attempt looking up the File in the first place.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Function File Not Found', $fatalErr);
            return $fatalErr;
        }
        if (empty($fileData['file_readable'])) {
            $fatalErr = "Function File Error in {$contextLabel}: Expected File `$relativePath` is NOT Readable. Validate/Review File Reading Permissions for the Path mentioned here in the Error in order to attempt reading the File in the first place.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Function File Not Readable', $fatalErr);
            return $fatalErr;
        }
        if (!$fileData['syntax_valid']) {
            $fatalErr = "Function File Error in {$contextLabel}: File `$relativePath` contains `Invalid PHP Code` as parsed by `\PhpToken::tokenize with TOKEN_PARSE Flag`. Review the PHP Syntax in the File:'`{$fileData['syntax_error']}`'.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Invalid PHP Code in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($fileData['classes_exist']) {
            $fatalErr = "Function File Error in {$contextLabel}: File `$relativePath` contains `Class Definitions` (" . count($fileData['classes']) . ':' . $this->joinArray(array_keys($fileData['classes'])) . ") which is forbidden for this type of `File Function`. Write `User-defined Classes` in `/src/funkphp/config/classes.php` instead.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Function File Contains Classes while Not Allowed', $fatalErr);
            return $fatalErr;
        }
        $fnCount = count($fileData['functions'] ?? []);
        if ($singleFNExpected) {
            if ($fnCount !== 1) {
                $fatalErr = "Function File Error in {$contextLabel}: File `$relativePath` must contain EXACTLY 1 Function (found {$fnCount}). This means you should ONLY have 1 Function Declaration in the Global Namespace inside of the Function File.";
                $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Function File Contains Too Many Functions', $fatalErr);
                return $fatalErr;
            }
        }
        $FN = $fileData['functions'][$expectedFNName] ?? null;
        if (!$FN) {
            $fnThatExist = "<No Functions>";
            if (isset($fileData['functions']) && count($fileData['functions']) > 0) {
                $fnThatExist = $this->joinArray($fileData['functions'], true);
            }
            $fatalErr = "Function File Error in {$contextLabel}: Expected Function `{$expectedFNName}` in File `$relativePath` does NOT exist. It should exist as a Function Declaration `function {$expectedFNName}(&\$c){}` within the Global Namespace inside of the Function File. Available Functions in `$relativePath`: {$fnThatExist}.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Expected Function in Function File Not Found', $fatalErr);
            return $fatalErr;
        }
        if (strtolower($FN['fn_exact_name']) !== $FN['fn_lowercased']) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `Function Name` that is `all lowercased` and following this Naming Convention: `[a-z_][a-z0-9_]*`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Invalid Function Name in Function File', $fatalErr);
            return $fatalErr;
        }
        if (str_starts_with(strtolower($FN['fn_exact_name']), 'funk_') || str_starts_with(strtolower($FN['fn_exact_name']), 'cli_')) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `Function Name` that does NOT start with `funk_` OR `cli_` as it will be in the Global Namespace and could clash with Internal FunkPHP Functions.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden Function Name in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($expectedNSName !== '') {
            if (!isset($fileData['namespace']) || $fileData['namespace'] !== $expectedNSName) {
                $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}` in File `$relativePath` must have the following namespace: `{$expectedNSName}` (Found: `" . ($fileData['namespace'] ?? '<NO NAMESPACE>') . "`).";
                $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Function File Missing Required Namespace', $fatalErr);
                return $fatalErr;
            }
        }
        if ($FN['body_raw'] === '{}' || $FN['only_whitespace_and_or_comments'] === true) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `Code in its Function Body` and cannot just contain `whitespace` and/or `comments`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Function Body for Function Missing in Function File', $fatalErr);
            return $fatalErr;
        }
        $argsRaw = trim($FN['args_raw'] ?? '');
        if (!str_starts_with($argsRaw, '&$c')) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` must have `&\$c` as its First Parameter (found `({$argsRaw})`).";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Missing Function Argument \'&$c\' as First Function Argument in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($FN['has_inner_functions'] === true) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` cannot have Inner Function Declarations (e.g. `function name(&\$c){ function inner(&\$c){} }`). See line(s): `" . join(', ', $FN['nested_function_lines']) . "` in the File. Use `Anonymous Function Declarations` instead such as: `\$innerFN = function(\$arg) use (\$otherArgs) { return \$arg; };` OR `\$innerFN = fn(\$arg) => \$arg + \$otherArgs;`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden Inner Functions in Function in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($FN['has_set_error_hankder']) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function 'set_error_handler'` which must instead be set with `->setDefaultErrorHandler('FN_From_/src/funkphp/config/functions.php>')` under `->CONFIG()` in `/src/funkphp/app/CONFIG.php`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden use of In-built PHP Function in Function in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($FN['has_set_exception_handler']) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function 'set_exception_handler'` which must instead be set with `->setDefaultExceptionHandler('FN_From_/src/funkphp/config/functions.php>')` under `->CONFIG()` in `/src/funkphp/app/CONFIG.php`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden use of In-built PHP Function in Function in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($FN['has_register_shutdown_function']) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function 'register_shutdown_function'` which must instead be set with `->pipePostResponseFunction('<FN_From_/src/funkphp/pipes/post_response/FileName.php>')` under `->CONFIG()` in `/src/funkphp/app/CONFIG.php`. They are added using the in-built `register_shutdown_function()` and are executed in such order. `IMPORTANT:` Remember that any use of `exit()` in those `Piped Post-Response Function(s)` will make the remaining (if any) to NOT run Post-Response!";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden use of In-built PHP Function in Function in Function File', $fatalErr);
            return $fatalErr;
        }
        if ($FN['has_invalid_funk_calls']) {
            $fatalErr = "Function File Error in {$contextLabel}: Function `{$expectedFNName}()` in File `$relativePath` has `Forbidden Function(s): `" . $this->joinArray($FN['invalid_funk_calls']) . ". Some of these Functions are set under `->CONFIG()` in `/src/funkphp/app/CONFIG.php` while others are not meant to be called inside the `pipes` of a given Matched Route but are meant instead to be used internally by FunkPHP.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden use of Internal FunkPHP Functions in Function in Function File', $fatalErr);
            return $fatalErr;
        }
        return null; // Function File for FunkPHP use is all OK here! - Warnings are emitted by another function
    }
    private function validateCLASSFile(array $fileData, string $expectedFNName, string $contextLabel, string $expectedNSName = '', bool $singleFNExpected = false): ?string
    {
        $relativePath = '/src/funkphp/' . $fileData['folder_provided_path'] . '/' . $fileData['file_name'];
        if (empty($fileData) || array_is_list($fileData)) {
            $fatalErr = "File Class Error in {$contextLabel}: Parsed File Data `$relativePath` as an Array is EITHER A Numbered Array when it should be an Associative Array OR it is Completely Empty. (This is possibly an Internal FunkPHP Error - try regenerate default files in `/src/funkphp/config/` and try again)";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Cannot Parse Expected Class File Data', $fatalErr);
            return $fatalErr;
        }
        if (empty($fileData['file_exists'])) {
            $fatalErr = "File Class Error in {$contextLabel}: Expected File `$relativePath` does NOT exist.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Class File Not Found', $fatalErr);
            return $fatalErr;
        }
        if (empty($fileData['file_readable'])) {
            $fatalErr = "File Class Error in {$contextLabel}: Expected File `$relativePath` is NOT Readable.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Class File Not Readable ', $fatalErr);
            return $fatalErr;
        }
        if (!$fileData['syntax_valid']) {
            $fatalErr = "File Class Error in {$contextLabel}: File `$relativePath` contains `Invalid PHP Code` as parsed by `\PhpToken::tokenize with TOKEN_PARSE Flag`. Review the PHP Syntax in the File:'`{$fileData['syntax_error']}`'.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Invalid PHP Code in Class File', $fatalErr);
            return $fatalErr;
        }
        // Class count that must be fulfilled
        $fnCount = count($fileData['classes'] ?? []);
        if ($singleFNExpected) {
            if ($fnCount !== 1) {
                $fatalErr = "File Class Error in {$contextLabel}: File `$relativePath` must contain EXACTLY 1 Class (found {$fnCount}).";
                $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Class File Contains Too Many Classes', $fatalErr);
                return $fatalErr;
            }
        }
        $FN = $fileData['classes'][$expectedFNName] ?? null;
        if (!$FN) {
            $fatalErr = "File Class Error in {$contextLabel}: Expected Class `{$expectedFNName}` in File `$relativePath` does NOT exist.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Expected Class Missing in Class File', $fatalErr);
            return $fatalErr;
        }
        $CLASS_EXACT_NAME = $FN['class_name'] ?? null;
        if (str_starts_with(strtolower(trim($FN['class_name'])), 'funk')) {
            $fatalErr = "File Class Error in {$contextLabel}: Class `{$expectedFNName}()` in File `$relativePath` cannot start with `Funk` as it is reserved despite being in the shared namespace `funkphp\\classes`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden Class Name in Class File', $fatalErr, $CLASS_EXACT_NAME);
            return $fatalErr;
        }
        if ($expectedNSName !== '') {
            if (!isset($fileData['namespace']) || $fileData['namespace'] !== $expectedNSName) {
                $fatalErr = "File Class Error in {$contextLabel}: Class `{$expectedFNName}` in File `$relativePath` must have the following namespace: `{$expectedNSName}` (Found: `" . ($fileData['namespace'] ?? '<NO NAMESPACE>') . "`).";
                $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Class File Missing Required Namespace', $fatalErr, $CLASS_EXACT_NAME);
                return $fatalErr;
            }
        }
        if ($FN['body_raw'] === '{}' || $FN['only_whitespace_and_or_comments'] === true) {
            $fatalErr = "File Class Error in {$contextLabel}: Class `{$expectedFNName}()` in File `$relativePath` must have `Code in its Class Body` and cannot just contain `whitespace` and/or `comments`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Class File Missing Code In Main Body', $fatalErr, $CLASS_EXACT_NAME);
            return $fatalErr;
        }
        if ($FN['has_inner_functions'] === true) {
            $fatalErr = "File Class Error in {$contextLabel}: Class `{$expectedFNName}()` in File `$relativePath` cannot have Inner Function Declarations (e.g. `function name(&\$c){ function inner(&\$c){} }`). See line(s): `" . join(', ', $FN['nested_function_lines']) . "` in the File. Use `Anonymous Function Declarations` instead such as: `\$innerFN = function(\$arg) use (\$otherArgs) { return \$arg; };` OR `\$innerFN = fn(\$arg) => \$arg + \$otherArgs;`.";
            $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden Inner Functions in Class Method in Class File', $fatalErr, $CLASS_EXACT_NAME);
            return $fatalErr;
        }
        // Now we iterate through each method in the current Class in the classes.php
        // file since those are what could be considered functions in classes.
        foreach ($FN['methods'] as $method => $methodDetails) {
            if ($method !== '__construct') { // Constructor CAN have empty body
                if ($methodDetails['analysis']['only_whitespace_and_or_comments']) {
                    $fatalErr = "File Class Error in {$contextLabel}: Class Method `{$expectedFNName}->{$method}` in File `$relativePath` has `Only Whitespace and/or Comments` in its `Code Body` while NOT being the `__construct` Method. Add some Code to the Class Method OR comment it out for later use.";
                    $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'No Function Body in Class Method in Class File', $fatalErr, $CLASS_EXACT_NAME);
                    return $fatalErr;
                }
            }
            if ($methodDetails['analysis']['has_inner_functions']) {
                $fatalErr = "File Class Error in {$contextLabel}: Class Method `{$expectedFNName}->{$method}` in File `$relativePath` has `Inner Function Declarations` on lines(s) " . $this->joinArray($methodDetails['analysis']['nested_function_lines']) . " which could Conflict with other Globally Namespaced Functions. `Convert it to a Valid Class-based Method` instead. Also, regarding using Inner Functions in general in FunkPHP; Use `Anonymous Function Declarations` instead such as: `\$innerFN = function(\$arg) use (\$otherArgs) { return \$arg; };` OR `\$innerFN = fn(\$arg) => \$arg + \$otherArgs;`.";
                $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden Inner Functions in Class Method in Class File', $fatalErr, $CLASS_EXACT_NAME);
                return $fatalErr;
            }
            if ($methodDetails['analysis']['has_invalid_funk_calls']) {
                $fatalErr = "File Class Error in {$contextLabel}: Class Method `{$expectedFNName}->{$method}` in File `$relativePath` has calls to the following `Disallowed FunkPHP Functions` " . $this->joinArray($methodDetails['analysis']['invalid_funk_calls']) . " that are meant to be called by other Internal FunkPHP Functions directly and not inside of non-FunkPHP-based Classes.";
                $this->setFileErr($fileData['?file_type'], $fileData['file_name'], $expectedFNName, 'Forbidden use of Internal FunkPHP Functions in Class Method in Class File', $fatalErr, $CLASS_EXACT_NAME);
                return $fatalErr;
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
            "`->$batchFN()`",
            "`->$batchFN({$argString})`"
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
            'InvalidParamName' => "Invalid Param Rule Name in {$optionalCtx}: Param Rule Name must be a Non-Empty String (no trailing spaces) all lowercased containing only `[a-z0-9_-]` characters without the colon (`:`). If used for a specific `METHOD/route`, it MUST match at least one of the Params of the Route meaning that `METHOD/route` must have at least one Param in its Route Segments.",
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
            'InvalidHeaderNameChoiceCSP' => "Invalid Header Name Value in {$optionalCtx}: Header `Content-Security-Policy` must be set using `->setCSP()` instead where each call then sets one directive for it. Here You can also use `nonces:<name>` as a source in order to cause it to generate a nonce value when the CSP Header is sent. This nonce value would then be used in `{{nonce:<name>}}` in any HTML Element where applicable. `->setCSP()` inherits CSP Directives from Method and CONFIG. So, if you set one CSP Directive for Global CONFIG but not for a given Route, then that Route inherits that CSP Directive, and same for inheriting from the Method the Route is attached to.",
            'InvalidCSPSourceArray' => "Invalid CSP Source Array in {$optionalCtx}: Ensure Sources are Valid Non-Empty Strings with no spaces, semicolons, or CRLF Injections.",
            'InvalidCSPDirective' => "Invalid CSP Directive Name Value in {$optionalCtx}. Must be one of the following: ",
            'InvalidCSPWildcardUse' => "Invalid Wildcard Domain CSP Source Value in {$optionalCtx}. Wildcards must appear as `*.domain.com` OR `https://*.domain.com`.",
            'InvalidNonceKeyName' => "Invalid Nonce Key Value in {$optionalCtx}: Nonce Keys must be Non-Empty Strings containing only `[a-zA-Z0-9-_\.]` characters (e.g., `test`, `main-script`).",
            'InvalidPageName' => "Invalid Page Name Value in {$optionalCtx}: must be a `Non-Empty String` containing only `[a-zA-Z0-9-_]` characters (no trailing spaces) and without the File Extension.",
            'InvalidNoRouteMatchTextValue' => "Invalid Text Value in {$optionalCtx}: must be a `Non-Empty String` after `trim()` have been applied to it.",
            'InvalidParamCBFN' => "Invalid Function Name as Callback for Param Rule in {$optionalCtx}: must be a `Non-Empty String` after `trim()` all lowercased that start with `[a-z_]` and then only contain `[a-z0-9_]` characters. In other words; a valid Function Declaration in PHP.",
            'InvalidRegex'                                => "Invalid Regex Value in {$optionalCtx}: must be a `Non-Empty String` that is also a `Valid Regex Pattern` when parsed by `preg_match()`. It cannot be an Empty Expression with optional modifiers (e.g. `//` OR `//i`).",
            'InvalidRouteFormat' => "Invalid Route Value in {$optionalCtx}: A Valid Route must: 1) Start with or just be `/` as root (`never end with -, _ OR /`), 2) Be all `lowercased`, 3) Have all `Uniquely Named /:params` URI segments (if any used), 4) Never use `-` and/or `_ consecutively`, after each other (e.g. `-_` or `_-`) OR as start in static/dynamic segments (e.g. `/:-`, `/:_`, `/_`, OR `/-`), 5) Only use `[a-z0-9_-]` characters.",
            'InvalidRoutePrefixFormat' => "Invalid Route Prefix Value in {$optionalCtx}: A Valid Route Prefix must: 1) Start with `/` (it can NEVER just be only `/` for Route Prefixes!) as root (`never end with -, _ OR /`), 2) Be all `lowercased`, 3) Have all `Uniquely Named /:params` URI segments (if any used), 4) Never use `-` and/or `_ consecutively`, after each other (e.g. `-_` or `_-`) OR as start in static/dynamic segments (e.g. `/:-`, `/:_`, `/_`, OR `/-`), 5) Only use `[a-z0-9_-]` characters.",
            'InvalidRoutePrefixResetFirst' => "Route Prefix Value in {$optionalCtx}: Reset the current Method Route Prefix Value with the help of `->ROUTEPrefixReset()` before setting a New Route Prefix for the current Method!",
            'InvalidRouteFormatDuplicateParams' => "Invalid Route Value in {$optionalCtx}: `Check for Duplicate Params`. A Valid Route must: 1) Start with or just be `/` as root (`never end with -, _ OR /`), 2) Be all `lowercased`, 3) Have all `Uniquely Named /:params` URI segments (if any used), 4) Never use `-` and/or `_ consecutively`, after each other (e.g. `-_` or `_-`) OR as start in static/dynamic segments (e.g. `/:-`, `/:_`, `/_`, OR `/-`), 5) Only use `[a-z0-9_-]` characters.",
            'InvalidRouteFormatDuplicateParamsPrefix' => "Invalid Route Prefix Value in {$optionalCtx}: `Check for Duplicate Params`. A Valid Route Prefix must: 1) Start with `/` (it can NEVER just be only `/` for Route Prefixes!) as root (`never end with -, _ OR /`), 2) Be all `lowercased`, 3) Have all `Uniquely Named /:params` URI segments (if any used), 4) Never use `-` and/or `_ consecutively`, after each other (e.g. `-_` or `_-`) OR as start in static/dynamic segments (e.g. `/:-`, `/:_`, `/_`, OR `/-`), 5) Only use `[a-z0-9_-]` characters.",
            'InvalidRouteAliasName'                     => "Invalid Route Alias Name in {$optionalCtx}: Aliases must only contain `[a-zA-Z0-9_.-]` characters (e.g., `users.all` OR `Users.All`).",
            'InvalidTTL_Cache' => "Invalid TTL (time-to-live) Value in {$optionalCtx}: It must be an `Integer` - meaning the `number of seconds` - between `0` and `31536000` (1 year).",
            'InvalidDriver_Cache' => "Invalid Driver Value in {$optionalCtx}: It must be one of the following Valid ones: ",
            'InvalidDriver_RateLimit' => "Invalid Driver Value in {$optionalCtx}: It must be one of the following Valid ones: ",
            'InvalidVaryBy_Cache' => "Invalid \$varyBy Value in {$optionalCtx}:",
            'InvalidPrivate_Cache' => 'Invalid Boolean Value in {$optionalCtx}: It must be a Valid Boolean Value that is either `TRUE` or `FALSE`.',
            'InvalidMaxRequests_RateLimit' => "Invalid Max Request Per Window Size Value in {$optionalCtx}: Must be between - in the `number of seconds` - `1` and `1 000 000` (1 million).",
            'InvalidWindowSize_RateLimit' => "Invalid Window Size in Seconds Value in {$optionalCtx}: Must be between - in the `number of seconds` - `1` and `86 400` (24 hours).",
            'InvalidBy_RateLimit' => "Invalid \$by Value in {$optionalCtx}:",
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
            'DuplicateCacheOption' => "`Duplicate Cache Option` in {$optionalCtx}:",
            'DuplicateRateLimitOption' => "`Duplicate Rate Limit Option` in {$optionalCtx}:",
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
            'ConflictingConfiguration'           => "Valid Configuration {$optionalCtx} is already set and CANNOT be overridden, only changed manually.",
            'ConflictingHTTPSWithInsecureSessionCookie' => "Conflicting Values in {$optionalCtx} with `Session Cookie Secure` that is set to `false` while `->setUseHTTPS()` is set to `true`. This would mean using `HTTP Insecure Cookies` with otherwise `HTTP Secure Connections` which is not allowed. Choose EITHER both Insecure (`false`) OR both Secure (`true`). This is automated if you omit both.",
            'ConflictingInsecureSessionCookieAndUseHTTPS' => "Conflicting Values in {$optionalCtx} with `useHTTPS` that are NOT the same value which they need to be otherwise it would mean to use EITHER `Insecure Connection with Secure Session Cookies` OR `Secure Connection with Insecure Session Cookies`. Choose EITHER both Insecure (`false`) OR both Secure (`true`). This is automated if you omit both.",
            'ConflictingExcludeMWWithAlreadyPipedMW' => "Conflicting Calls in {$optionalCtx}: cannot reference the same Middleware(s) in `->setExcludeMiddleware()` and `->pipeMiddleware` in the same Route. Middlewares to Exclude should target Piped Middlewares in the same `<METHOD>()` and/or `CONFIG()`.",
            'ConflictingPipeMiddlewareWithAlreadyExcludeMW' => "Conflicting Calls in {$optionalCtx}: cannot reference the same Middleware(s) in `->pipeMiddleware()` and `->setExcludeMiddleware()` in the same Route. Middlewares to Exclude should target Piped Middlewares in the same `<METHOD>()` and/or `CONFIG()`.",
            'ConflictingURINormalizerWithCustomHTTPSKernel' => "Conflicting User-defined Defaults in {$optionalCtx}: Cannot use `->setDefaultKernelHandler()` together with `->setDefaultURI_NormalizerHandler()` as latter one is meant to be used internally during Route Matching, and Route Matching is meant to be handled by the User-defined Function in `->setDefaultKernelHandler()` if used. Choose between using `->setDefaultKernelHandler()` OR `->setDefaultURI_NormalizerHandler()`, but not both.",
            // When Response Already exists
            'ConflictResponseAlreadyAdded' => "Conflicting Calls in {$optionalCtx}: A `->pipeResponse()` has already been piped. Cannot use `->pipe<Function|SQL|Query|Validation>` after that. If you need `Different Possible Responses` in the same Matched Route, use `funk_return_response()` inside your Piped Functions and one final `->pipeResponse()`.",
        ];
        if (isset($errors[$errType])) {
            return $errors[$errType];
        } else {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['type' => 'internal', 'err' => "[Class C->getErr()]: Unknown Internal Error Type:`{$errType}`. Report this as a Bug/Issue to the `Official FunkPHP Respositories`."];
            return "UNKNOWN ERROR TYPE CHOSEN: SEE 'internal' Error in `\$this->errors`!";
        }
    }
    /**
     * Set Error Message with specific Error Title.
     *
     * Choose Error Type based on scope (global, method, route) and optional method and route when applicable.
     *
     * @param string $errMsg The detailed Error Message that is shown when pressing "Details ->" button
     * @param string $errShort The short version of the Error Message that is shown after the Error Number
     * @param 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'HEAD'|null $method
     * @param string|null $route
     *
     */
    private function setErr(string $errMsg, string $errShort = '', string $method = 'CONFIG', ?string $route = null)
    {
        $validMethodTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'CONFIG'];
        $validRouteMethodTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'];
        // No short error
        if (!is_string($errShort) || trim($errShort) === '') {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid Value in (\$type)', 'err' => 'Invalid `\$type` (Error Type) Value (OR it is missing) in `class C->setErr()` when setting Error:\'`' . $errMsg . '`\' Report this found bug/issue to the Official FunkPHP Repositories.', 'method' => $method, 'route' => $route];
            $this->FunkPHPFluentAPI['ALL'][count($this->FunkPHPFluentAPI)['ALL']] .= "`(See Error #" . count($this->errors['INTERNAL']) . " in 'INTERNALS')`";
            return;
        }
        // No method (or valid) provided
        if (
            !is_string($method) || trim($method) === '' || !in_array($method, $validMethodTypes)
        ) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid Value in (\$method)', 'err' => 'Invalid `\$method` (Method Type) Value (OR it is missing) in `class C->setErr()`: must be provided where `\'CONFIG\'` is default. Report this found bug/issue to the Official FunkPHP Repositories. Choose a `Valid Method Type` from: ' . $this->joinArray($validMethodTypes), 'method' => $method, 'route' => $route];
            $this->FunkPHPFluentAPI['ALL'][count($this->FunkPHPFluentAPI)['ALL']] .= "`(See Error #" . count($this->errors['INTERNAL']) . " in 'INTERNALS')`";
            return;
        }
        if (
            isset($route) && (!is_string($route) || trim($route) === '' || !in_array($method, $validRouteMethodTypes))
        ) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid Value in (\$route)', 'err' => 'Invalid `\$route` Value (OR it is missing) in `class C->setErr()`: must be provided when Error Type starts with `Route-`. Report this found bug/issue to the Official FunkPHP Repositories.', 'method' => $method, 'route' => $route];
            $this->FunkPHPFluentAPI['ALL'][count($this->FunkPHPFluentAPI)['ALL']] .= "`(See Error #" . count($this->errors['INTERNAL']) . " in 'INTERNALS')`";
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
        // append to last API depending on CONFIG, a METHODS CONFIG, or a METHODS ROUTESs
        // = $this->FunkPHPFluentAPI[count($this->FunkPHPFluentAPI)] .= ' - (`See Error #' . $nextErrIndex . '`)';
        if ($method === 'CONFIG') {
            $this->FunkPHPFluentAPI['CONFIG'][count($this->FunkPHPFluentAPI['CONFIG'])] .= ' - (`See Error #' . $nextErrIndex . '`)';
            $this->FunkPHPFluentAPI['ALL'][array_key_last($this->FunkPHPFluentAPI['ALL'])] .= " (See Error #" . $nextErrIndex . " in 'CONFIG')";
        } else {
            if ($route) {
                if (!isset($this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route])) {
                    $this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route] = [];
                }
                $this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route][count($this->FunkPHPFluentAPI['METHODS'][$method]['ROUTES'][$route])] .= ' - (`See Error #' . $nextErrIndex . '`)';
                $this->FunkPHPFluentAPI['ALL'][array_key_last($this->FunkPHPFluentAPI['ALL'])] .= " (See Error #" . $nextErrIndex . " in '$method' => '$method$route')";
            } else {
                if (!isset($this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'])) {
                    $this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'] = [];
                }
                $this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'][count($this->FunkPHPFluentAPI['METHODS'][$method]['CONFIG'])] .= ' - (`See Error #' . $nextErrIndex . '`)';
                $this->FunkPHPFluentAPI['ALL'][array_key_last($this->FunkPHPFluentAPI['ALL'])] .= " (See Error #" . $nextErrIndex . " in 'GET' => '$method CONFIG')";
            }
        }
        // add the latest error depending on CONFIG, a METHODS CONFIG, or a METHODS ROUTES
        // = $this->errors[$nextErrIndex] = ['err' => $errMsg, 'type' => $errType, 'method' => $method, 'route' => $route];
        if ($method === 'CONFIG') {
            $this->errors['ERRORS']++;
            $this->errors['CONFIG'][$nextErrIndex] = ['err' => $errMsg, 'errShort' => $errShort, 'method' => $method, 'route' => $route];
        } else {
            if ($route) {
                if (!isset($this->errors['METHODS'][$method]['ROUTES'][$route])) {
                    $this->errors['METHODS'][$method]['ROUTES'][$route] = [];
                }
                $this->errors['ERRORS']++;
                $this->errors['METHODS'][$method]['ROUTES'][$route][$nextErrIndex] = ['err' => $errMsg, 'errShort' => $errShort, 'method' => $method, 'route' => $route];
            } else {
                $this->errors['ERRORS']++;
                if (!isset($this->errors['METHODS'][$method]['CONFIG'])) {
                    $this->errors['METHODS'][$method]['CONFIG'] = [];
                }
                $this->errors['METHODS'][$method]['CONFIG'][$nextErrIndex] = ['err' => $errMsg, 'errShort' => $errShort, 'method' => $method, 'route' => $route];
            }
        }
    }

    /**
     * Set File Error Message with specific Error Title.
     *
     * Choose Error Type based on scope (global, method, route) and optional method and route when applicable.
     *
     * @param string 'user-functions'|'user-classes'|'pages-layouts'|'pages-partials'|'pages-components'|'pages-uncompiled'|'pages-compiled'|'user-tables'|'response'|'post-response'|'middleware'|'route'|'data-query-compiled'|'data-sql-compiled'|'data-validation-compiled'|'data-query-uncompiled'|'data-sql-uncompiled'|'data-validation-uncompiled' $fileType
     * @param string $file File Name to the file that is within that $fileType Category
     * @param string $fn Function Name inside that $file within that $fileType Category
     * @param string $errMsg The detailed Error Message that is shown when pressing "Details ->" button
     * @param string $errShort The short version of the Error Message that is shown after the Error Number
     * @param string|null $route
     *
     */
    private function setFileErr(string $fileType, string $file, string $fn, string $errShort, string $err, string|null $fnOrClassNameExact = null)
    {
        $validFileTypes = [
            'user-functions',
            'user-classes',
            'pages-layouts',
            'pages-partials',
            'pages-components',
            'pages-uncompiled',
            'pages-compiled',
            'user-tables',
            'request',
            'post-response',
            'middleware',
            'route',
            'data-query-compiled',
            'data-sql-compiled',
            'data-validation-compiled',
            'data-query-uncompiled',
            'data-sql-uncompiled',
            'data-validation-uncompiled'
        ];
        if (
            !isset($fileType) || !is_string($fileType) || trim($fileType) === ''
            || !in_array(strtolower(trim($fileType)), $validFileTypes)
        ) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid Value in (\$fileType)', 'err' => 'Invalid `\$type` (Error Type) Value (OR it is missing) in `class C->setFileErr()` when trying to Set a File/Function Error. Must be one of these: ' . $this->joinArray($validFileTypes) . '. Report this found bug/issue to the Official FunkPHP Repositories.', 'file' => $file, 'fn' => $fn];
            return;
        }
        if (
            !isset($file) || !isset($fn) || !is_string($file) || !is_string($fn)
            || !isset($errShort) || !isset($err) || !is_string($errShort) || !is_string($err)
            || strtolower(trim($file)) === '' || strtolower(trim($fn)) === ''
            || trim($err) === '' || trim($errShort) === ''
        ) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid Value(s) in `\$file, \$fn, \$errShort and/or \$err`', 'err' => 'Invalid Data Types (all must be Non-Empty Strings) for `\$file, \$fn, \$errShort and/or \$err` in `class C->setFileErr()` when trying to Set a File/Function Error. Report this found bug/issue to the Official FunkPHP Repositories.', 'file' => $file, 'fn' => $fn];
            return;
        }
        $fileType = strtolower(trim($fileType));
        $fn =  strtolower(trim($fn));
        $file =  strtolower(trim($file));
        $err =  trim($err);
        $errShort =  trim($errShort);
        if (!isset($this->errors['FILES'][$fileType])) {
            $this->errors['FILES'][$fileType] = [];
        }
        if (!isset($this->errors['FILES'][$fileType][$file])) {
            $this->errors['FILES'][$fileType][$file] = [];
        }
        if (!isset($this->errors['FILES'][$fileType][$file][$fn])) {
            $this->errors['FILES'][$fileType][$file][$fn] = [];
        }
        $this->errors['FILES'][$fileType][$file][$fn][count($this->errors['FILES'][$fileType][$file][$fn]) + 1] = ['errShort' => $errShort, 'err' => $err, 'file' => $file, 'fn' => $fn, 'exact_name' => $fnOrClassNameExact];
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
    // Where "New" means you can add sevseral as long as they are not duplicates OR conflict in certain
    // order like "pipeResponse" means you have completed the request cycle for that route and now
    // any piped ->requestPostResponse() should run as a result! Different levels (global, method, route)
    // have different amount of settings/piping they can do (thus what can be batched and not!)

    // Set & New Batches for GLOBAL/CONFIG()! (so ->config()->set|pipe<What>)
    public function batch(string $fn, mixed ...$payload)
    {
        if ($fn === '' || !method_exists($this, $fn)) {
            $this->errors['ERRORS']++;
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Failed to call Internal batch<METHOD>', 'err' => '[Class C->batch()]: Tried calling to a Non-existing Private Function `' . $fn  . '` in Class `C` in `/src/funkphp/core/functions.php`. Any Value(s) `Class C->batch()` were supposed to return to some other function will now have been converted to `null`. Please report this Bug/Issue to the `Official FunkPHP Repositories`.'];
            return null;
        }
        return $this->$fn(...$payload);
    }

    // batchSetMETHOD is for setting -><METHOD>() so it shows up correctly in "API" Tab
    private function batchSetMETHOD(string $method)
    {
        $this->FunkPHPFluentAPI['ALL'][count($this->FunkPHPFluentAPI['ALL']) + 1] = "->$method()";
    }


    /* !!! GLOBAL/CONFIG() BATCHES FUNCTIONS !!! */
    /* setCompileFlag & setDebug */
    private function batchSetCompileFlag(string $flag)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setCompileFlag', "CONFIG()",  $flag);
        $validFlags = [
            'ALLOW_GHOST_ROUTES', // no error issued when
            'ALL_ROUTES_MUST_HAVE_PIPE_RESPONSE', // pipeResponse() must be applied to every route or hard compilation error.
            'HIDE_NO_ROUTE_RESPONSE_WARNING', // No warning issued when a Route has no 'response' (no pipeResponse())
            'NO_WARNINGS_ALLOWED', // $this->errors['COMPILATION']['warnings'] must be 0 after compile() is done or compilation fails
            'ONLY_RETURN_COMPILED_PAGES', // pipeResponse() config will ONLY look for compiled pages and error out if not found during config
            'ONLY_RETURN_NONCOMPILED_PAGES' // pipeResponse() config wil ONLY look for non-compiled pages and error out if not found during config
        ];
        if (isset($this->invalidBatches['config']['compileFlags'][$flag])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['compileFlags'][$flag])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_string($flag) || trim($flag) === '' || !in_array($flag, $validFlags)) {
            $this->setErr($this->getErr('InvalidCompilerFlag', $ctxVals) . $this->joinArray($validFlags), 'Duplicate Compiler Flag ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEBUG'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctxVals), 'Duplicate Call ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['FUNKPHP_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_bool($trueOrFalse) || ($trueOrFalse !== FALSE && $trueOrFalse !== TRUE)
        ) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Invalid Boolean Value ' . $ctxVals);
            $this->invalidBatches['config']['FUNKPHP_ONLINE'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['FUNKPHP_ONLINE'] = $trueOrFalse;
    }
    private function batchSetUseHTTPSGlobal(bool $trueOrFalse)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setUseHTTPS', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['USE_HTTPS'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['USE_HTTPS'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_bool($trueOrFalse) || ($trueOrFalse !== FALSE && $trueOrFalse !== TRUE)
        ) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Invalid Boolean Value ' . $ctxVals);
            $this->invalidBatches['config']['USE_HTTPS'] = $trueOrFalse;
            return;
        }
        if (
            $trueOrFalse === true
            && isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])
            && $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'] !== true
        ) {
            $this->setErr($this->getErr('ConflictingHTTPSWithInsecureSessionCookie', $ctxVals), 'Conflicting Use HTTPS with Insecure Session Cookie ' . $ctxVals);
            $this->invalidBatches['config']['USE_HTTPS'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['USE_HTTPS'] = $trueOrFalse;
    }
    private function batchSetUseVendorGlobal(bool $trueOrFalse)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setUseVendor', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['USE_VENDOR'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['USE_VENDOR'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_bool($trueOrFalse) || ($trueOrFalse !== FALSE && $trueOrFalse !== TRUE)
        ) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Invalid Boolean Value ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
            $this->setErr($err, 'User-defined Function Already In Use ' . $ctxVals);
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
            $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_EXCEPTION_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Unique Function checks for SetExceptionHandler: it must contain "\throwable $<varName>"
        // and this is checked AFTER it starts with &$c so no issues there!
        if (
            !preg_match('/\\\\Throwable\s+\$[_a-z][_a-z0-9]*/i', $fileData['functions'][$userDefinedFunction]['args_raw'])
        ) {
            $err = $this->getErr('UserDefinedFUNCTIONHasWrongArgs', $ctxVals) . ' `\Throwable \$e` (e.g. `function userDefined(&\$c, \Throwable \$e){}`) in order to use it as a User-defined Exception Handler. The variable `$e` can be named something else as well.' . " Found instead:`{$fileData['functions'][$userDefinedFunction]['args_raw']}`.";
            $this->setErr($err, 'Invalid Function Arguments in User-defined Function File ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
            $this->setErr($err, 'User-defined Function Already In Use ' . $ctxVals);
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
            $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_ERROR_HANDLER'] = $userDefinedFunction;
            return;
        }
        // Unique Function checks for SetErrorHandler: it must contain "$errNo, $errStr, $errFile, $errLine"
        // and this is checked AFTER it starts with &$c so no issues there! The variables can be typed or not.
        if (
            !preg_match('/^&\$c\s*,\s*(?:int\s+)?\$[_a-z0-9]+\s*,\s*(?:string\s+)?\$[_a-z0-9]+\s*,\s*(?:string\s+)?\$[_a-z0-9]+\s*,\s*(?:int\s+)?\$[_a-z0-9]+$/i', $fileData['functions'][$userDefinedFunction]['args_raw'])
        ) {
            $err = $this->getErr('UserDefinedFUNCTIONHasWrongArgs', $ctxVals) . '` $errNo, $errStr, $errFile, $errLine` (e.g. `function userDefined(&\$c, $errNo, $errStr, $errFile, $errLine){}`) in order to use it as a User-defined Error Handler. The `$errNo,$errStr,$errFile,$errLine` can be named something else as well.' . " Found instead:`{$fileData['functions'][$userDefinedFunction]['args_raw']}`.";
            $this->setErr($err, 'Invalid Function Arguments in User-defined File Function ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals),  'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->setErr($this->getErr('ConflictingURINormalizerWithCustomHTTPSKernel', $ctxVals), 'Conflicting User-defined Defaults ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals),  'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_URI_NORMALIZER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
            $this->setErr($err,  'User-defined Function Already In Use ' . $ctxVals);
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
            $this->setErr($fatalError,  'Function File Error (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->setErr($this->getErr('ConflictingURINormalizerWithCustomHTTPSKernel', $ctxVals), 'Conflicting User-defined Defaults ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
            $this->setErr($err, 'User-defined Function Already In Use ' . $ctxVals);
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
            $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_HTTPS_KERNEL'] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultKernelHandler('{$userDefinedFunction}')";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultKernelHandler('{$userDefinedFunction}')";
    }
    private function batchSetDefaultIPResolverGlobal(string $userDefinedFunction) // URI NORMALIZER GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setDefaultIPResolver', "CONFIG()", $userDefinedFunction);
        if (isset($this->invalidBatches['config']['DEFAULT_IP_RESOLVER'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['DEFAULT_IP_RESOLVER'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals),  'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunction)) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringNotStartCLIorFUNK', $ctxVals),  'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_IP_RESOLVER'] = $userDefinedFunction;
            return;
        }
        // FN already used by some other Global Engine Function? (exception, error, uri normalizer, https kernel?)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction])) {
            $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer`, `IP Resolver`, and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
            $this->setErr($err,  'User-defined Function Already In Use ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_IP_RESOLVER'] = $userDefinedFunction;
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
            $this->setErr($fatalError,  'Function File Error (also see FILES tab) ' . $ctxVals);
            $this->invalidBatches['config']['DEFAULT_IP_RESOLVER'] = $userDefinedFunction;
            return;
        }
        // Add to ValidBatches, UserDefinedFNs and also UserDefinedEngineFNs which means any User-defined function
        // that is added there cannot be used for multiple purposes as they are meant to be very specifically used.
        $this->validBatches['config']['DEFAULT_IP_RESOLVER'] = $userDefinedFunction;
        $this->cached['placeholderUsedUserDefinedFunctions'][$userDefinedFunction] = "->CONFIG()->setDefaultIPResolver('{$userDefinedFunction}')";
        $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunction] = "->CONFIG()->setDefaultIPResolver('{$userDefinedFunction}')";
    }

    /* setNoRouteMatch<VARIANTS> Global - These are all catches when no catches for specific <method(s)> are defined/applied */
    private function batchSetNoRouteMatchPageGlobal(string $PageFileName, int $statusCode = 404) // NO MATCH: PAGE - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchPage', "CONFIG()", $PageFileName);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Cal ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Invalid HTTP Response Code ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        if (!is_string($PageFileName) || (trim($PageFileName) === '') || !preg_match('/[a-zA-Z0-9-_]+/i', $PageFileName)) {
            $this->setErr($this->getErr('InvalidPageName', $ctx), 'Invalid Page Name ' . $ctxVals);
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
            $this->setErr($this->getErr('NoPageAtAllFound', $ctxVals) . " using Filename `{$PageFileName}.php`.", 'No Page File Found ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        $this->validBatches['config']['NO_ROUTE_MATCH']['PAGE'] = ['page' => $PageFileName, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchJsonGlobal(array|object $data, int $statusCode = 404)  // NO MATCH: JSON - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchJSON', "CONFIG()", $data, $statusCode);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Invalid HTTP Response Code ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        // Really check it is an empty object
        $isEmptyObject = is_object($data) && (
            ($data instanceof \Countable && count($data) === 0) ||
            (get_object_vars($data) === [] && !($data instanceof \JsonSerializable))
        );
        if (is_array($data) && empty($data) || $isEmptyObject) {
            $this->setErr($this->getErr('JsonEncodingFailedNoData', $ctx), 'Invalid Data for JSON Encoding ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $JSON = null;
        try {
            $JSON = json_encode($data, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            $this->setErr($this->getErr('JsonEncodingFailed', $ctx), 'JSON Encoding Failed ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $this->validBatches['config']['NO_ROUTE_MATCH']['JSON'] = ['JSON' => $JSON, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchTextGlobal(string $message, int $statusCode = 404)  // NO MATCH: TEXT - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchText', "CONFIG()", $message, $statusCode);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Invalid HTTP Response Code ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        if (!is_string($message) || (trim($message) === '')) {
            $this->setErr($this->getErr('InvalidNoRouteMatchTextValue', $ctx), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        $this->validBatches['config']['NO_ROUTE_MATCH']['TEXT'] = ['text' => $message, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchCallbackGlobal(string $userDefinedFunctionName)  // NO MATCH: CALLBACK - GLOBAL
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setNoRouteMatchCallback', "CONFIG()", $userDefinedFunctionName);
        if (isset($this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunctionName)) {
            $this->setErr($this->getErr('InvalidFunctionName', $ctx), 'Invalid Function Name ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // Hydrate user defined functions if not already
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions', $userDefinedFunctionName);
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunctionName, $ctxVals);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
            $this->invalidBatches['config']['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // After initial check, check that it is not already used by Global Handlers (->CONFIG()->setDefault<Handler>)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName])) {
            $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' See: `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName] . '`', 'User-defined File Function Already in Use ');
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_LOCAL'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_string($httpPath) || trim($httpPath) === ''
            || !preg_match('/^http:\/\//', $httpPath)
        ) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringSTARTWithHTTP', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['BASEURL_LOCAL'] = $httpPath;
            return;
        }
        $this->validBatches['config']['BASEURL_LOCAL'] = $httpPath;
    }
    private function batchSetDefaultBaseURLOnlineGlobal(string $httpsPath)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLLocal', "CONFIG()", $httpsPath);
        if (isset($this->invalidBatches['config']['BASEURL_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_ONLINE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_string($httpsPath) || trim($httpsPath) === ''
            || !preg_match('/^https:\/\//', $httpsPath)
        ) {
            $this->setErr($this->getErr('NonEmptyAllLowercasedStringSTARTWithHTTPS', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['BASEURL_ONLINE'] = $httpsPath;
            return;
        }
        $this->validBatches['config']['BASEURL_ONLINE'] = $httpsPath;
    }
    private function batchSetDefaultBaseURLHostGlobal(string $hostNameLocally)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLHost', "CONFIG()", $hostNameLocally);
        if (isset($this->invalidBatches['config']['BASEURL_HOST'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_HOST'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_string($hostNameLocally) || trim($hostNameLocally) === '') {
            $this->setErr($this->getErr('NonEmptyStringNoTrailing', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['BASEURL_HOST'] = $hostNameLocally;
            return;
        }
        $this->validBatches['config']['BASEURL_HOST'] = $hostNameLocally;
    }
    private function batchSetDefaultBaseURLUriGlobal(string $localURI)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setBaseURLHost', "CONFIG()", $localURI);
        if (isset($this->invalidBatches['config']['BASEURL_URI'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['BASEURL_URI'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_string($localURI) || trim($localURI) === '') {
            $this->setErr($this->getErr('NonEmptyStringNoTrailing', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['BASEURL_URI'] = $localURI;
            return;
        }
        $this->validBatches['config']['BASEURL_URI'] = $localURI;
    }
    private function batchSetDefaultSessionCookieOptionsGlobal(array $SessionCookieOptions)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieOptions', "CONFIG()", $SessionCookieOptions);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
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
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array with these Session Cookie Options: `" . implode('`, `', $allowedKeys) . "`.", 'Invalid Session Cookie Options Formatting ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
            return;
        }
        foreach ($allowedKeys as $k) {
            if (!isset($SessionCookieOptions[$k])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array with these Session Cookie Options: `" . implode('`, `', $allowedKeys) . "`. Missing Key: `'" . "{$k}'`", 'Invalid Session Cookie Options Key ' . $ctxVals);
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
        }
        // Validate Session Cookie Options are just Assoc_key => Scalar_Value
        foreach ($SessionCookieOptions as $key => $val) {
            if (!is_scalar($val)) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Invalid Value for Session Cookie Option `{$key}`. It must be a Scalar Value (Non-Empty String, Non-Negative Integer|Float, or Boolean) using these Session Cookie Keys:`" . implode('`, `', $allowedKeys) . "`.", 'Invalid Session Cookie Key Value ' . $ctxVals);
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
            if (!in_array($key, $allowedKeys, true)) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Invalid Value for Session Cookie Option `{$key}`. Use these Session Cookie Keys:`" . implode('`, `', $allowedKeys) . "`.", 'Invalid Session Cookie Key Value ' . $ctxVals);
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
            if ($key === 'SESSION_DRIVER' && isset($this->validBatches['config']['SESSION']['driver'])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " The Session Cookie Option `{$key}` already exists as a Valid Session Cookie Value under `->CONFIG()`.", 'Duplicate Session Cookie Key ' . $ctxVals);
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            }
            if (isset($this->validBatches['config']['SESSION']['COOKIES'][$key])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " The Session Cookie Option `{$key}` already exists as a Valid Session Cookie Value under `->CONFIG()`.", 'Duplicate Session Cookie Key ' . $ctxVals);
                $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                return;
            } else if (isset($this->invalidBatches['config']['SESSION']['COOKIES'][$key])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " The Session Cookie Option `{$key}` already exists as a Invalid Session Cookie Value under `->CONFIG()`.", 'Duplicate Session Cookie Key ' . $ctxVals);
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
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_DRIVER` Value. Must be a Non-Empty String without trailing spaces that is one of the following values: " . $this->joinArray(['files', 'redis', 'memcached', 'database', 'array']), 'Invalid String Value ' . $ctxVals);
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_NAME':
                    if (!is_string($val) || trim($val) === '') {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_NAME` Value. Must be a Non-Empty String without trailing spaces.", 'Invalid String Value ' . $ctxVals);
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = trim($val);
                    break;
                case 'SESSION_LIFETIME':
                    if (!is_int($val) || $val < 0) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_LIFETIME` Value. Must be a Non-Negative Integer.", 'Invalid Integer Value ' . $ctxVals);
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
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_PATH` Value. Must be a Non-Empty String starting with or only being:`/` and then use [a-zA-Z0-9_-#] characters only in each `/segment`. You may include a single trailing slash at the end if technically needed.", 'Invalid String Value ' . $ctxVals);
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
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_DOMAIN` Value. Must be a Non-Empty String without schemes and ports:`://`, `:`, `/`.", 'Invalid String Value ' . $ctxVals);
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_SECURE':
                    if (!is_bool($val)) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `{$key}` Value. Must be a Boolean as either `TRUE` or `FALSE`.", 'Invalid Boolean Value ' . $ctxVals);
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    if (isset($this->validBatches['config']['USE_HTTPS']) && $this->validBatches['config']['USE_HTTPS'] !== $val) {
                        $this->setErr($this->getErr('ConflictingInsecureSessionCookieAndUseHTTPS', $ctx), 'Conflicting Use HTTPS with Session Cookie Option ' . $ctxVals);
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_HTTPONLY':
                    if (!is_bool($val)) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `{$key}` Value. Must be a Boolean as either `TRUE` or `FALSE`.", 'Invalid Boolean Value ' . $ctxVals);
                        $this->invalidBatches['config']['SESSION']['COOKIES']['AS_OPTIONS'] = $SessionCookieOptions;
                        return;
                    }
                    $validated[$key] = $val;
                    break;
                case 'SESSION_SAMESITE':
                    if (!is_string($val) || trim($val) === '' || !in_array((ucfirst(strtolower(trim($val)))), ['Lax', 'Strict', 'None'], true)) {
                        $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctx) . " Invalid `SESSION_SAMESITE` Value. Must be one of these Non-Empty String Values:`Lax, Strict, None`.", 'Invalid String Value ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['driver'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_string($filesOrRedisOrSomethingElse) || trim($filesOrRedisOrSomethingElse) === ''
            || !in_array(strtolower($filesOrRedisOrSomethingElse), ['files', 'redis', 'memcached', 'database', 'array'], true)
        ) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctxVals) . " must be one of these Non-Empty String Values:`files, redis, memcached, database, array`.", 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['driver'] = $filesOrRedisOrSomethingElse;
            return;
        }
        $this->validBatches['config']['SESSION']['driver'] = $filesOrRedisOrSomethingElse;
    }
    private function batchSetDefaultSessionCookieNameGlobal(string $sessionCookieName = 'fphp_id')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieName', "CONFIG()", $sessionCookieName);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_string($sessionCookieName) || trim($sessionCookieName) === '') {
            $this->setErr($this->getErr('NonEmptyStringNoTrailing', $ctxVals), 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_NAME'] = $sessionCookieName;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'] = $sessionCookieName;
    }
    private function batchSetDefaultSessionCookieLifetimeGlobal(int $sessionCookieLifetime = 28800)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieLifetime', "CONFIG()", $sessionCookieLifetime);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_int($sessionCookieLifetime) || $sessionCookieLifetime < 0) {
            $this->setErr($this->getErr('NotIntegerNotNegative', $ctxVals), 'Invalid Integer Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'] = $sessionCookieLifetime;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'] = $sessionCookieLifetime;
    }
    private function batchSetDefaultSessionCookiePathGlobal(string $sessionCookiePath = '/')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookiePath', "CONFIG()", $sessionCookiePath);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_string($sessionCookiePath) || trim($sessionCookiePath) === ''
            || !str_starts_with($sessionCookiePath, '/') || !preg_match('/^\/([a-zA-Z0-9-_]+\/?)*$/i', $sessionCookiePath)
        ) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctxVals) . " must be a Non-Empty String starting with or only being:`/` and then use `[a-zA-Z0-9_-#]` characters only in each `/segment`. You may include a single trailing slash at the end if technically needed.", 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_PATH'] = $sessionCookiePath;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'] = $sessionCookiePath;
    }
    private function batchSetDefaultSessionCookieDomainGlobal(string $sessionCookieDomain = 'webdev.local')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieDomain', "CONFIG()", $sessionCookieDomain);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (
            !is_string($sessionCookieDomain) || trim($sessionCookieDomain) === ''
            || str_contains($sessionCookieDomain, '://')
            || str_contains($sessionCookieDomain, ':')
            || str_contains($sessionCookieDomain, '/')
            || preg_match('/[\s;]/', $sessionCookieDomain)
        ) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctxVals) . " Must be a Non-Empty String without schemes and ports:`://`, `:`, `/`.", 'Invalid String Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'] = $sessionCookieDomain;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'] = $sessionCookieDomain;
    }
    private function batchSetDefaultSessionCookieSecureGlobal(bool $trueOrFalse = false)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieSecure', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_bool($trueOrFalse)) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Invalid Boolean Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'] = $trueOrFalse;
            return;
        }
        if (isset($this->validBatches['config']['USE_HTTPS']) && $this->validBatches['config']['USE_HTTPS'] !== $trueOrFalse) {
            $this->setErr($this->getErr('ConflictingInsecureSessionCookieAndUseHTTPS', $ctx), 'Conflicting Use HTTPS with Session Cookie Option ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'] = $trueOrFalse;
    }
    private function batchSetDefaultSessionCookieHTTPOnlyGlobal(bool $trueOrFalse = true)
    {

        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieHTTPOnly', "CONFIG()", $trueOrFalse);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_bool($trueOrFalse)) {
            $this->setErr($this->getErr('NotBoolean', $ctxVals), 'Invalid Boolean Value ' . $ctxVals);
            $this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = $trueOrFalse;
            return;
        }
        $this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = $trueOrFalse;
    }
    private function batchSetDefaultSessionCookieSameSiteGlobal(string $LaxOrStrict = 'Lax')
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSessionCookieSameSite', "CONFIG()", $LaxOrStrict);
        if (isset($this->invalidBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
            $this->setErr($this->getErr('DuplicateCallSessionCookieDueToValidOptionsVersion', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!is_string($LaxOrStrict) || trim($LaxOrStrict) === '' || !in_array($LaxOrStrict, ['Lax', 'Strict', 'None'], true)) {
            $this->setErr($this->getErr('InvalidStringCustomErrAfterColon', $ctx) . " must be one of these Non-Empty String Values:`Lax, Strict, None`.", 'Invalid String Value ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['setINI_SET'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (empty($iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue) || array_is_list($iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a non-empty associative array (e.g., `['setting' => 'value']`).", 'Invalid Array Value ' . $ctxVals);
            $this->invalidBatches['config']['setINI_SET'] = $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue;
            return;
        }
        foreach ($iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue as $k => $v) {
            $isValidKey   = is_string($k) && trim($k) !== '';
            $isValidValue = is_scalar($v) && (!is_string($v) || trim($v) !== '');
            if (!$isValidKey || !$isValidValue) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Keys must be Non-Empty Strings and values must be Non-Empty Scalar Types (string, int, float, bool).", 'Invalid Key=>Value Values in Array ' . $ctxVals);
                $this->invalidBatches['config']['setINI_SET'] = $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue;
                return;
            }
        }
        $this->validBatches['config']['setINI_SET'] = $iniSetArrayWithKeyNamesAsSettingTypeWithSingleScalarValue;
    }

    /* setRateLimit Global */
    private function batchSetRateLimitingGlobal(
        int $maxRequestsPerWindowSize = 60,
        int $windowSizeInSeconds = 60,
        $by = 'ip',
        $driver = 'redis'
    ) {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setRateLimit', "", $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver);
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['ratelimit']['config'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " You can only set Rate Limit for a Global CONFIG once.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['ratelimit']['config'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals) . " You can only set Rate Limit for a Global CONFIG once.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        // Max Requests per window size (between 1-1000000)
        if ($maxRequestsPerWindowSize < 1 || $maxRequestsPerWindowSize > 1000000) {
            $this->setErr($this->getErr('InvalidMaxRequests_RateLimit', $ctxVals), 'Invalid $maxRequestsPerWindowSize for Global CONFIG Rate Limit. Must be between 1 and 1,000,000 ' . $ctxVals);
            $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Window Size in Seconds (between 1-86400 seconds OR 1 second-24 hours)
        if ($windowSizeInSeconds < 1 || $windowSizeInSeconds > 86400) {
            $this->setErr($this->getErr('InvalidWindowSize_RateLimit', $ctxVals), 'Invalid $windowSizeInSeconds for Global CONFIG Rate Limit. Must be between 1 and 86,400 seconds (24h) ' . $ctxVals);
            $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Driver validation
        $cleanDriver = strtolower(trim($driver));
        if (!in_array($cleanDriver, $this->ALLOWED['drivers']['ratelimit'], true)) {
            $this->setErr($this->getErr('InvalidDriver_RateLimit', $ctxVals) . $this->joinArray($this->ALLOWED['drivers']['ratelimit']) . '.', 'Invalid Driver for Global CONFIG Rate Limit ' . $ctxVals);
            $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Validate `$by` (Identifier strategy)
        $normalizedBy = [];
        if (!is_string($by) && !is_array($by)) {
            $this->setErr(
                $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` must be a `String` or an `Array of Strings`.",
                'Invalid $by Value for Global CONFIG Rate Limit ' . $ctxVals,
            );
            $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        $items = is_string($by) ? [$by] : $by;
        if (empty($items)) {
            $this->setErr(
                $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` cannot be empty.",
                'Empty $by for Global CONFIG Rate Limit ' . $ctxVals,
            );
            $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        foreach ($items as $item) {
            if (!is_string($item)) {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " Array items in `$by` must all be `Non-Empty Strings`.",
                    'Invalid $by Array Item for Global CONFIG Rate Limit ' . $ctxVals,
                );
                $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            $trimmedItem = strtolower(trim($item));
            if ($trimmedItem === '') {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` items cannot be `Empty Strings`.",
                    'Empty $by Item for Global CONFIG Rate Limit ' . $ctxVals,
                );
                $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            // Valid identifier formats: 'ip', 'user_id', 'session', 'api_key', 'header:<name>', 'query:<name>'
            if (
                !preg_match('/^(header|query):[a-z0-9_-]+$/i', $trimmedItem)
                && !in_array($trimmedItem, ['ip', 'user_id', 'session', 'api_key'], true)
            ) {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " Item `{$item}` is Invalid. Use formats like `'header:X-Api-Key'`, `'query:token'`, or a direct token from: " . $this->joinArray(['ip', 'user_id', 'session', 'api_key']) . '.',
                    'Invalid $by Format for Global CONFIG Rate Limit ' . $ctxVals,
                );
                $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            // Duplicate identifier check
            if (!in_array($trimmedItem, $normalizedBy, true)) {
                $normalizedBy[] = $trimmedItem;
            } else {
                $this->setErr($this->getErr('DuplicateRateLimitOption', $ctxVals) . " Rate Limit Identifier `{$trimmedItem}` has already been added.", 'Duplicate Rate Limit Identifier ' . $ctxVals);
                $this->invalidBatches['ratelimit']['config'] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
        }
        // Add to valid batches when all OK
        $this->validBatches['ratelimit']['config'] = [
            'max_requests' => $maxRequestsPerWindowSize,
            'window_seconds' => $windowSizeInSeconds,
            'by' => $normalizedBy,
            'driver' => $cleanDriver,
        ];
    }
    /* setGrouped<VARIANTS> Global */
    private function batchSetGroupedPipeUserDefined(string $groupName, string ...$userDefFNS)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setGroupPipeUserdefined', "CONFIG()", $groupName, ...$userDefFNS);
        // Initial checks: invalidBathced already? ValidBatched already? Invalid $groupName string?
        // Any of the FNs invalid in their naming? Only after that, do we start checking each FN file.
        if (isset($this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Invalid Group Name ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
            return;
        }
        if (count($userDefFNS) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Request Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($userDefFNS), 'Provide at least 2 Functions ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
            return;
        }
        foreach ($userDefFNS as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Invalid Function Name ' . $ctxVals);
                $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($userDefFNS) !== count(array_unique($userDefFNS))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($userDefFNS), 'Duplicate Function Name ' . $ctxVals);
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
                $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
                $this->invalidBatches['config']['GROUPED_PIPE_USER_DEFINED'][$groupName] = [...$userDefFNS];
                return;
            }
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$FN_FILE])) {
                $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' See: `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$FN_FILE] . '`', 'User-defined Function Already in Use ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_REQUEST'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Invalid Group Name ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
            return;
        }
        if (count($RequestFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Request Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($RequestFNs), 'Provide at least 2 Functions ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
            return;
        }
        foreach ($RequestFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Invalid Function Name ' . $ctxVals);
                $this->invalidBatches['config']['GROUPED_PIPE_REQUEST'][$groupName] = [...$RequestFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($RequestFNs) !== count(array_unique($RequestFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($RequestFNs), 'Duplicate Function Name ' . $ctxVals);
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
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "funkphp\\pipes\\request", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Invalid Group Name ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
            return;
        }
        if (count($PostResponseFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Post-Response Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($PostResponseFNs), 'Provide at least 2 Functions ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
            return;
        }
        foreach ($PostResponseFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Invalid Function Name ' . $ctxVals);
                $this->invalidBatches['config']['GROUPED_PIPE_POST_RESPONSE'][$groupName] = [...$PostResponseFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($PostResponseFNs) !== count(array_unique($PostResponseFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($PostResponseFNs), 'Duplicate Function Name ' . $ctxVals);
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
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "funkphp\\pipes\\post_response", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_ROUTES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Invalid Group Name ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
            return;
        }
        if (count($RoutePipeFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Route Pipe Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($RoutePipeFNs), 'Provide at least 2 Functions ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
            return;
        }
        foreach ($RoutePipeFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrThatIsFileAndFunctionWithDot($FN)) {
                $this->setErr($this->getErr('InvalidFileAndFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Invalid Function Name ' . $ctxVals);
                $this->invalidBatches['config']['GROUPED_PIPE_ROUTES'][$groupName] = [...$RoutePipeFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($RoutePipeFNs) !== count(array_unique($RoutePipeFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($RoutePipeFNs), 'Duplicate Function Name ' . $ctxVals);
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
                $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Group Name must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($groupName)) {
            $this->setErr($this->getErr('InvalidGroupName', $ctxVals), 'Invalid Group Name ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
            return;
        }
        if (count($middlewareFNs) < 2) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . ' Count of Middleware Functions must be at least two(2) Functions.' . ' Provided: ' . $this->joinArray($middlewareFNs), 'Provide at least 2 Functions ' . $ctxVals);
            $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
            return;
        }
        foreach ($middlewareFNs as $FN) {
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($FN)) {
                $this->setErr($this->getErr('InvalidFunctionName', $ctxVals) . " Review the Invalid `{$FN}`.", 'Invalid Function Name ' . $ctxVals);
                $this->invalidBatches['config']['GROUPED_PIPE_MIDDLEWARES'][$groupName] = [...$middlewareFNs];
                return;
            }
        }
        // Find and disallow duplicates
        if (count($middlewareFNs) !== count(array_unique($middlewareFNs))) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Duplicate Function Names Found." . ' Provided: ' . $this->joinArray($middlewareFNs), 'Duplicate Function Name ' . $ctxVals);
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
            $fatalError = $this->validateFNFile($fileData, $FN_FILE, $ctxVals, "funkphp\\pipes\\middlewares", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Function File Error (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctxVals) . " Param Identifier must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Param Identifier must be unique.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Invalid Param Name ' .  $ctxVals);
            $this->invalidBatches['paramRules']['config'][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => null,
            ];
            return;
        }
        // callback|cb: OR a regex pattern for the param?
        $callback = null;
        $cbFN = null;
        // if=callback
        if (
            str_starts_with(strtolower(trim($regex)), 'callback:')
            || str_starts_with(strtolower(trim($regex)), 'cb:')
        ) {
            $regex = strtolower(trim($regex));
            // must be valid fn name and NOT set as global handler
            if (!preg_match('/^(callback|cb){1}:([a-z_][a-z0-9_]*)$/', $regex, $cbFN)) {
                $this->setErr($this->getErr('InvalidParamCBFN', $ctxVals), 'Invalid User-Defined Function Name for Param Rule ' .  $ctxVals);
                $this->invalidBatches['paramRules']['config'][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }

            // User-defined FN already used by Global handlers?
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$cbFN[2]])) {
                $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$cbFN[2]]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
                $this->setErr($err, 'User-defined Function Already In Use ' . $ctxVals);
                $this->invalidBatches['paramRules']['config'][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // User-defined FN even exist?
            if (!$this->cachedUserDefinedFNExists($cbFN[2])) {
                $this->setErr($this->getErr('UserDefinedFUNCTIONNotFound', $ctxVals) . " User-Defined Function `{$cbFN[2]}` as a Callback for a Param Rule was NOT found in other words.", 'User-Defined Function for Param Rule Not Found' .  $ctxVals);
                $this->invalidBatches['paramRules']['config'][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // Swap places since callback will be used and not a pattern!
            $callback = $cbFN[2];
            $regex = null;
        } // else=$regex pattern to use instead of callback
        else {
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
                $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Invalid Regex Pattern ' .  $ctxVals);
                $this->invalidBatches['paramRules']['config'][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => $callback,
                ];
                return;
            }
        }
        // Check for duplicate valid rule at global level
        if (isset($this->validBatches['config']['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateParamGlobal', $ctxVals), 'Duplicate Param Rule ' . $ctxVals);
            $this->invalidBatches['paramRules']['config'][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => $callback,
            ];
            return;
        }
        // Finally store valid global param rule
        $this->validBatches['config']['paramRules'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch,
            'callback' => $callback,
        ];
        $this->cached['placeholderUNSUEDParams']['GLOBAL'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch,
            'callback' => $callback,
        ];
    }

    /* setCSP Global */
    private function batchSetCSPGlobal(string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setCSP', "CONFIG()", $directive, ...$sources);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['config'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `\$directive` can only be used/set once.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `\$directive` can only be used/set once.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        $allowedDirectives = $this->ALLOWED['csp-directives'];
        if ($directive === '' || !in_array($directive, $allowedDirectives, true)) {
            $this->setErr($this->getErr('InvalidCSPDirective', $ctxVals) . $this->joinArray($allowedDirectives), 'Invalid CSP Directive ' . $ctxVals);
            return;
        }
        if (empty($sources)) {
            $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Sources for CSP Directive Missing ' . $ctxVals);
            $this->invalidBatches['csp']['config'][$directive] = $sources;
            return;
        }
        $formattedSources = $this->formatCSPSources($sources);
        if (in_array("'none'", $formattedSources, true) && count($formattedSources) > 1) {
            $this->setErr($this->getErr('ConflictNoneSourceInCSP', $ctxVals), 'CSP Source \'none\' must be used exclusively ' . $ctxVals);
            $this->invalidBatches['csp']['config'][$directive] = $sources;
            return;
        }
        $nonces = [];
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Invalid CSP Source Value ' . $ctxVals);
                $this->invalidBatches['csp']['config'][$directive] = $sources;
                return;
            }
            // Is it a nonce that is supposed to be in the 'nonces' array instead?
            $trimmed = trim($source);
            if (str_starts_with(strtolower($trimmed), 'nonce:')) {
                if (in_array(strtolower($trimmed), $nonces)) {
                    $this->setErr($this->getErr('DuplicateNonceName', $ctxVals) . "`{$trimmed}`. You can only use each Unique Nonce Name once per CSP Directive.", 'Duplicate CSP Nonce Name ' . $ctxVals);
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
                if (!preg_match('/^nonce:[a-zA-Z0-9-_\.]+$/', strtolower($trimmed))) {
                    $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Invalid Nonce Key Name ' . $ctxVals);
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
                if (isset($this->validBatches['config']['nonces'][strtolower($trimmed)])) {
                    $this->setErr($this->getErr('DuplicateNonceDirectiveUse', $ctxVals) . "`Nonce Name {$trimmed} ` is already being used by CSP Directive: `{$this->validBatches['config']['nonces'][strtolower($trimmed)]}`.", 'Duplicate CSP Nonce Name ' . $ctxVals);
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
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Invalid CSP Source Array Formatting ' . $ctxVals);
                $this->invalidBatches['csp']['config'][$directive] = $sources;
                return;
            }
            if (str_contains($trimmed, '*') && $trimmed !== '*') {
                if (!preg_match('/^(https?:\/\/)?\*\.[a-zA-Z0-9\.-]+(:\d+)?$/', $trimmed)) {
                    $this->setErr($this->getErr('InvalidCSPWildcardUse', $ctxVals), 'Invalid CSP Source Wildcard Use ' . $ctxVals);
                    $this->invalidBatches['csp']['config'][$directive] = $sources;
                    return;
                }
            }
        }
        $this->validBatches['config']['csp'][$directive] = array_filter($formattedSources, function ($src) {
            return !str_starts_with($src, 'nonce:');
        });
    }

    /* setSRI Internal&External Global */

    /* setSRIInternal & setSRIExternal - GLOBAL */
    private function batchSetSRIInternalGlobal(array $internalSRI)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setSRIInternal', "CONFIG()", $internalSRI);
        if (isset($this->invalidBatches['global_sris']['internal']['config'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " Set everything once in a Single Array.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['global_sris']['internal'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals) . " Set everything once in a Single Array.", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!isset($internalSRI) || empty($internalSRI) || array_is_list($internalSRI)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a Non-Empty Associative Array whose Keys are Non-Empty Strings and where each Key contains `Single Unique Non-Empty String Values` like:`['app_js' => 'sha384-...']` where the Key is the File Name without File Extension and the Value is the Hash Value of that File.", 'Invalid Internal SRI Array Formatting ' . $ctxVals);
            $this->invalidBatches['global_sris']['internal']['config'] = $internalSRI;
            return;
        }
        $duplicateHashes = [];
        $valid = [];
        foreach ($internalSRI as $key => $sriHash) {
            if (isset($duplicateHashes[$sriHash])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Internal SRI Hash `{$sriHash}` is already used by Key:`{$duplicateHashes[$sriHash]}`. Each Internal SRI Key must contain `Single Unique Non-Empty String Values` like:`['app_js' => 'sha384-...']` where the Key is the File Name without File Extension and the Value is the Hash Value of that File.", 'Duplicate Internal SRI Hash ' . $ctxVals);
                $this->invalidBatches['global_sris']['internal']['config'] = $internalSRI;
                return;
            }
            if (
                !is_string($key) || trim($key) === ''
                || !is_string($sriHash) || (trim($sriHash) === '')
                || (!str_contains($sriHash, 'sha'))
            ) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " Internal SRI Hash `{$sriHash}` must be a `Non-Empty String` starting with `sha-`. Each Internal SRI Key must contain `Single Unique Non-Empty String Values` like:`['app_js' => 'sha384-...']` where the Key is the File Name without File Extension and the Value is the Hash Value of that File.", 'Invalid Internal SRI Key->Value Formatting ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['global_sris']['external'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (empty($externalSRI) || array_is_list($externalSRI)) {
            $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a `Non-Empty Associative Array` where each Key is a `Non-Empty String Reference` (e.g. `cdn.tailwind`) and its Value is an Associative Array containing `Exactly Two Keys`: `url` (must start with `https://`) and `hash` (`Non-Empty String` containing `sha-`), for example:`['cdn.tailwind' => ['url' => 'https://cdn.tailwindcss.com', 'hash' => 'sha384-...']]`.", 'Invalid External SRI Array Formatting  ' . $ctxVals);
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
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " must be a `Non-Empty Associative Array` where each Key is a `Non-Empty String Reference` (e.g. `cdn.tailwind`) and its Value is an Associative Array containing `Exactly Two Keys`: `url` (must start with `https://`) and `hash` (`Non-Empty String` containing `sha-`), for example:`['cdn.tailwind' => ['url' => 'https://cdn.tailwindcss.com', 'hash' => 'sha384-...']]`.", 'Invalid External SRI Key->Value Formatting  ' . $ctxVals);
                $this->invalidBatches['global_sris']['external']['config'] = $externalSRI;
                return;
            }
            if (isset($duplicateHashes[$details['hash']])) {
                $this->setErr($this->getErr('InvalidArrayCustomErrAfterColon', $ctxVals) . " External SRI Hash `{$details['hash']}` is already used by `Key=>URL {$duplicateHashes[$details['hash']]}`. Each `External SRI Key` must contain `Single Non-Empty String-based Unique Hash Values` in their `hash` Key.", 'Duplicate External SRI Hash ' . $ctxVals);
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
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setHeaderRemove', "CONFIG()", $header_to_remove);
        if (isset($this->invalidBatches['headers']['config']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Header Name must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (isset($this->validBatches['config']['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Header Name must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Duplicate Header ' . $ctxVals);
            return;
        }
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/', $headerName)) {
            $this->setErr($this->getErr('InvalidHeaderName', $ctxVals), 'Invalid Header Name ' . $ctxVals);
            $this->invalidBatches['headers']['config']['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Header cannot be removed if it was first configured to be added
        if (isset($this->validBatches['config']['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictRemovePipedHeader', $ctxVals), 'Conflicting Headers ' . $ctxVals);
            $this->invalidBatches['headers']['config']['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Store header to be removed from Global level (->config())
        $this->validBatches['config']['headers']['remove'][$lowerHeader] = $headerName;
    }
    private function batchSetHeaderGlobal(string $header, string $value)
    {
        [$ctx, $ctxVals] = $this->setCtx('CONFIG', null, 'setHeaderAdd', "CONFIG()", $header, $value);
        $headerName  = trim($header);
        $headerValue = trim($value);
        $lowerHeader = strtolower($headerName);
        if (isset($this->invalidBatches['headers']['config']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `Header Name` must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals);
            return;
        }
        // Check for special case: cannot use content-security-policy (use setCSP instead)
        if ($lowerHeader === 'content-security-policy') {
            $this->setErr($this->getErr('InvalidHeaderNameChoiceCSP', $ctxVals), 'Cannot use Header Name `Content-Security-Policy`, use `->setCSP()` instead');
            $this->invalidBatches['headers']['config']['add'][$lowerHeader] = true;
            return;
        }
        // Forbid possible CRLF injection
        if (
            $headerName === '' || $headerValue === ''
            || str_contains($headerName, ":") || str_contains($headerValue, ":")
            || str_contains($headerName, "\r") || str_contains($headerName, "\n")
            || str_contains($headerValue, "\r") || str_contains($headerValue, "\n")
        ) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Invalid Header Name ' . $ctxVals);
            $this->invalidBatches['headers']['config']['add'][$lowerHeader] = true;
            return;
        }
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Forbidden Response Header Name ' . $ctxVals);
            $this->invalidBatches['headers']['config']['add'][$lowerHeader] = true;
            return;
        }
        if (isset($this->validBatches['config']['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `Header Name` must be unique (case-insensitive).", 'Duplicate Header ' . $ctxVals);
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['config']['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Conflicting Headers ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Duplicate call ' . $ctxVals);
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
        $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Invalid Middleware File Function (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($fileFunctionName)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Duplicate Call ' . $ctxVals);
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
        $fatalError = $this->validateFNFile($fileData, $fileFunctionName, $ctxVals, "funkphp\\pipes\\request", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Invalid Request Pipe File Function (also see FILES tab) ' . $ctxVals);
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
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($fileFunctionName)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Duplicate Call ' . $ctxVals);
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
        $fatalError = $this->validateFNFile($fileData, $fileFunctionName, $ctxVals, "funkphp\\pipes\\post_response", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Invalid Post-Response File Function (also see FILES tab) ' . $ctxVals);
            $this->invalidBatches['config']['post_response'][$fileFunctionName] = true;
            return;
        }
        // Pipe Global MW when all OK!
        $this->validBatches['config']['post_response'][] = $fileFunctionName;
    }

    /* !!! METHOD BATCHES/ROUTES()->GET|POST|PATCH|PUT|DELETE() FUNCTIONS !!! */
    //METHOD:Set & New Batches for SPECIFIC_METHOD! (so ->routes()-><Method>->set|pipe<What>)
    //METHOD: setRateLimit
    private function batchSetRateLimitingMethod(
        string $method,
        int $maxRequestsPerWindowSize = 60,
        int $windowSizeInSeconds = 60,
        $by = 'ip',
        $driver = 'redis'
    ) {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setRateLimit', "", $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver);
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['ratelimit']['methods'][$method])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " You can only set Rate Limit for a Method once.", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['ratelimit']['methods'][$method])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals) . " You can only set Rate Limit for a Method once.", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        // Max Requests per window size (between 1-1000000)
        if ($maxRequestsPerWindowSize < 1 || $maxRequestsPerWindowSize > 1000000) {
            $this->setErr($this->getErr('InvalidMaxRequests_RateLimit', $ctxVals), 'Invalid $maxRequestsPerWindowSize for Method Rate Limit. Must be between 1 and 1,000,000 ' . $ctxVals, $method);
            $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Window Size in Seconds (between 1-86400 seconds OR 1 second-24 hours)
        if ($windowSizeInSeconds < 1 || $windowSizeInSeconds > 86400) {
            $this->setErr($this->getErr('InvalidWindowSize_RateLimit', $ctxVals), 'Invalid $windowSizeInSeconds for Method Rate Limit. Must be between 1 and 86,400 seconds (24h) ' . $ctxVals, $method);
            $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Driver validation
        $cleanDriver = strtolower(trim($driver));
        if (!in_array($cleanDriver, $this->ALLOWED['drivers']['ratelimit'], true)) {
            $this->setErr($this->getErr('InvalidDriver_RateLimit', $ctxVals) . $this->joinArray($this->ALLOWED['drivers']['ratelimit']) . '.', 'Invalid Driver for Method Rate Limit ' . $ctxVals, $method);
            $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Validate `$by` (Identifier strategy)
        $normalizedBy = [];
        if (!is_string($by) && !is_array($by)) {
            $this->setErr(
                $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` must be a `String` or an `Array of Strings`.",
                'Invalid $by Value for Method Rate Limit ' . $ctxVals,
                $method,
            );
            $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        $items = is_string($by) ? [$by] : $by;
        if (empty($items)) {
            $this->setErr(
                $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` cannot be empty.",
                'Empty $by for Method Rate Limit ' . $ctxVals,
                $method,
            );
            $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        foreach ($items as $item) {
            if (!is_string($item)) {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " Array items in `$by` must all be `Non-Empty Strings`.",
                    'Invalid $by Array Item for Method Rate Limit ' . $ctxVals,
                    $method,
                );
                $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            $trimmedItem = strtolower(trim($item));
            if ($trimmedItem === '') {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` items cannot be `Empty Strings`.",
                    'Empty $by Item for Method Rate Limit ' . $ctxVals,
                    $method,
                );
                $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            // Valid identifier formats: 'ip', 'user_id', 'session', 'api_key', 'header:<name>', 'query:<name>'
            if (
                !preg_match('/^(header|query):[a-z0-9_-]+$/i', $trimmedItem)
                && !in_array($trimmedItem, ['ip', 'user_id', 'session', 'api_key'], true)
            ) {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " Item `{$item}` is Invalid. Use formats like `'header:X-Api-Key'`, `'query:token'`, or a direct token from: " . $this->joinArray(['ip', 'user_id', 'session', 'api_key']) . '.',
                    'Invalid $by Format for Method Rate Limit ' . $ctxVals,
                    $method,
                );
                $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            // Duplicate identifier check
            if (!in_array($trimmedItem, $normalizedBy, true)) {
                $normalizedBy[] = $trimmedItem;
            } else {
                $this->setErr($this->getErr('DuplicateRateLimitOption', $ctxVals) . " Rate Limit Identifier `{$trimmedItem}` has already been added.", 'Duplicate Rate Limit Identifier ' . $ctxVals, $method);
                $this->invalidBatches['ratelimit']['methods'][$method] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
        }
        // Add to valid batches when all OK
        $this->validBatches['ratelimit']['methods'][$method] = [
            'max_requests' => $maxRequestsPerWindowSize,
            'window_seconds' => $windowSizeInSeconds,
            'by' => $normalizedBy,
            'driver' => $cleanDriver,
        ];
    }

    //METHOD: No Match for this https method, if none is set, it falls back to the global versions.
    private function batchSetNoRouteMatchPageMethod(string $method, string $PageFileName, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchPage', "CONFIG()->{$method}()", $PageFileName);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Invalid HTTP Response Code ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        if (!is_string($PageFileName) || (trim($PageFileName) === '') || !preg_match('/[a-zA-Z0-9-_]+/i', $PageFileName)) {
            $this->setErr($this->getErr('InvalidPageName', $ctx), 'Invalid String Value ' . $ctxVals, $method);
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
            $this->setErr($this->getErr('NoPageAtAllFound', $ctxVals) . " using Filename `{$PageFileName}.php`.", 'No Page File Found ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = $PageFileName;
            return;
        }
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['PAGE'] = ['page' => $PageFileName, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchJsonMethod(string $method, array|object $data, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchJSON', "CONFIG()->{$method}()", $data, $statusCode);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Invalid HTTP Response Code ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        // Really check it is an empty object
        $isEmptyObject = is_object($data) && (
            ($data instanceof \Countable && count($data) === 0) ||
            (get_object_vars($data) === [] && !($data instanceof \JsonSerializable))
        );
        if (is_array($data) && empty($data) || $isEmptyObject) {
            $this->setErr($this->getErr('JsonEncodingFailedNoData', $ctx), 'Invalid Data for JSON Encoding ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $JSON = null;
        try {
            $JSON = json_encode($data, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            $this->setErr($this->getErr('JsonEncodingFailed', $ctx), 'JSON Encoding Failed ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = true;
            return;
        }
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['JSON'] = ['JSON' => $JSON, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchTextMethod(string $method, string $message, int $statusCode = 404)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchText', "CONFIG()->{$method}()", $message, $statusCode);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (!$this->validateStatusCode($statusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctx), 'Invalid HTTP Response Code ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        if (!is_string($message) || (trim($message) === '')) {
            $this->setErr($this->getErr('InvalidNoRouteMatchTextValue', $ctx), 'Invalid String Value ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'] = true;
            return;
        }
        $this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['TEXT'] = ['text' => $message, 'code' => $statusCode];
    }
    private function batchSetNoRouteMatchCallbackMethod(string $method, string $userDefinedFunctionName)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setNoRouteMatchCallback', "CONFIG()->{$method}()", $userDefinedFunctionName);
        if (isset($this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($userDefinedFunctionName)) {
            $this->setErr($this->getErr('InvalidFunctionName', $ctx), 'Invalid Function Name ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // Hydrate user defined functions if not already
        $this->cachedCreateKeyIfNullAndOptionalFileName('file_user_defined_functions', $userDefinedFunctionName);
        $fileData = $this->cached['file_user_defined_functions'] ?? [];
        // Bails on the first structural error regarding a typical user-defined function
        $fatalError = $this->validateFNFile($fileData, $userDefinedFunctionName, $ctxVals);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Invalid Function File (also see FILES tab) ' . $ctxVals, $method);
            $this->invalidBatches['methods'][$method]['NO_ROUTE_MATCH']['CALLBACK'] = $userDefinedFunctionName;
            return;
        }
        // After initial check, check that it is not already used by Global Handlers (->CONFIG()->setDefault<Handler>)
        if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName])) {
            $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' See: `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$userDefinedFunctionName] . '`', 'User-defined File Function Already in Use ' . $ctxVals, $method);
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
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Param Identifier must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Param Identifier must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Invalid Param Name ' . $ctxVals, $method);
            $this->invalidBatches['paramRules']['methods'][$method][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => null,
            ];
            return;
        }
        // callback|cb: OR a regex pattern for the param?
        $callback = null;
        $cbFN = null;
        // if=callback
        if (
            str_starts_with(strtolower(trim($regex)), 'callback:')
            || str_starts_with(strtolower(trim($regex)), 'cb:')
        ) {
            $regex = strtolower(trim($regex));
            // must be valid fn name and NOT set as global handler
            if (!preg_match('/^(callback|cb){1}:([a-z_][a-z0-9_]*)$/', $regex, $cbFN)) {
                $this->setErr($this->getErr('InvalidParamCBFN', $ctxVals), 'Invalid User-Defined Function Name for Param Rule ' .  $ctxVals, $method);
                $this->invalidBatches['paramRules']['methods'][$method][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // User-defined FN already used by Global handlers?
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$cbFN[2]])) {
                $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$cbFN[2]]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
                $this->setErr($err, 'User-defined Function Already In Use ' . $ctxVals, $method);
                $this->invalidBatches['paramRules']['methods'][$method][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // User-defined FN even exist?
            if (!$this->cachedUserDefinedFNExists($cbFN[2])) {
                $this->setErr($this->getErr('UserDefinedFUNCTIONNotFound', $ctxVals) . " User-Defined Function `{$cbFN[2]}` as a Callback for a Param Rule was NOT found in other words.", 'User-Defined Function for Param Rule Not Found' .  $ctxVals, $method);
                $this->invalidBatches['paramRules']['methods'][$method][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // Swap places since callback will be used and not a pattern!
            $callback = $cbFN[2];
            $regex = null;
        }
        // else=$regex pattern to use instead of callback
        else {
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
                $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Invalid Regex Pattern ' . $ctxVals, $method);
                $this->invalidBatches['paramRules']['methods'][$method][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => $callback,
                ];
                return;
            }
        }
        // Check for duplicate valid rule at method level
        if (isset($this->validBatches['methods'][$method]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateParamMethod', $ctxVals), 'Duplicate Param ' . $ctxVals, $method);
            $this->invalidBatches['paramRules']['methods'][$method][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => $callback,
            ];
            return;
        }
        // Finally store valid method param rule
        $this->validBatches['methods'][$method]['paramRules'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch,
            'callback' => $callback,
        ];
        $this->cached['placeholderUNSUEDParams'][$method][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch,
            'callback' => $callback,
        ];
    }

    //METHOD: setCSP
    private function batchSetCSPMethod(string $method, string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setCSP', "ROUTES()->{$method}()", $directive, ...$sources);
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['methods'][$method][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `\$directive` can only be used/set once.", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `\$directive` can only be used/set once.", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        $allowedDirectives = $this->ALLOWED['csp-directives'];
        if ($directive === '' || !in_array($directive, $allowedDirectives, true)) {
            $this->setErr($this->getErr('InvalidCSPDirective', $ctxVals) . $this->joinArray($allowedDirectives), 'Invalid CSP Directive ' . $ctxVals, $method);
            $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
            return;
        }
        if (empty($sources)) {
            $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Sources for CSP Directive Missing ' . $ctxVals, $method);
            $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
            return;
        }
        $formattedSources = $this->formatCSPSources($sources);
        if (in_array("'none'", $formattedSources, true) && count($formattedSources) > 1) {
            $this->setErr($this->getErr('ConflictNoneSourceInCSP', $ctxVals), 'CSP Source \'none\' must be used exclusively ' . $ctxVals, $method);
            $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
            return;
        }
        $nonces = [];
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Invalid CSP Source Value ' . $ctxVals, $method);
                $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                return;
            }
            // Is it a nonce that is supposed to be in the 'nonces' array instead?
            $trimmed = trim($source);
            if (str_starts_with(strtolower($trimmed), 'nonce:')) {
                if (in_array(strtolower($trimmed), $nonces)) {
                    $this->setErr($this->getErr('DuplicateNonceName', $ctxVals) . "`{$trimmed}`. You can only use each Unique Nonce Name once per CSP Directive.", 'Duplicate CSP Nonce Name ' . $ctxVals, $method);
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
                if (!preg_match('/^nonce:[a-zA-Z0-9-_\.]+$/', strtolower($trimmed))) {
                    $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Invalid Nonce Key Name ' . $ctxVals, $method);
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
                if (isset($this->validBatches['methods'][$method]['nonces'][strtolower($trimmed)])) {
                    $this->setErr($this->getErr('DuplicateNonceDirectiveUse', $ctxVals) . "`Nonce Name {$trimmed} ` is already being used by CSP Directive: `{$this->validBatches['methods'][$method]['nonces'][strtolower($trimmed)]}`.", 'Duplicate Nonce Key Name ' . $ctxVals, $method);
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
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Invalid CSP Source Array Formatting ' . $ctxVals, $method);
                $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                return;
            }
            if (str_contains($trimmed, '*') && $trimmed !== '*') {
                if (!preg_match('/^(https?:\/\/)?\*\.[a-zA-Z0-9\.-]+(:\d+)?$/', $trimmed)) {
                    $this->setErr($this->getErr('InvalidCSPWildcardUse', $ctxVals), 'Invalid CSP Source Wildcard Use ' . $ctxVals, $method);
                    $this->invalidBatches['csp']['methods'][$method][$directive] = $sources;
                    return;
                }
            }
        }
        $this->validBatches['methods'][$method]['csp'][$directive] = array_filter($formattedSources, function ($src) {
            return !str_starts_with($src, 'nonce:');
        });
    }

    /*METHOD: removeHeader & pipeHeader */
    private function batchSetHeaderMethod(string $method, string $header, $value)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setHeaderAdd', "ROUTES()->{$method}()", $header, $value);
        $headerName  = $header;
        $headerValue = $value;
        $lowerHeader = strtolower($headerName);
        if (isset($this->invalidBatches['headers']['methods'][$method]['add'][$header])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `Header Name` must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        // Check for special case: cannot use content-security-policy (use setCSP instead)
        if ($lowerHeader === 'content-security-policy') {
            $this->setErr($this->getErr('InvalidHeaderNameChoiceCSP', $ctxVals), 'Cannot use Header Name `Content-Security-Policy`, use `->setCSP()` instead', $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        // Forbid possible CRLF injection
        if (
            $headerName === '' || $headerValue === ''
            || str_contains($headerName, ":") || str_contains($headerValue, ":")
            || str_contains($headerName, "\r") || str_contains($headerName, "\n")
            || str_contains($headerValue, "\r") || str_contains($headerValue, "\n")
        ) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Invalid Header Name ' . $ctxVals, $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Forbidden Response Header Name ' . $ctxVals, $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        // Check if it already exists
        if (isset($this->validBatches['methods'][$method]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `Header Name` must be unique (case-insensitive).", 'Duplicate Header ' . $ctxVals, $method);
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['methods'][$method]['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Conflicting Headers ' . $ctxVals, $method);
            $this->invalidBatches['headers']['methods'][$method]['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Method level (->config()->ROUTES()-><METHOD>)
        $this->validBatches['methods'][$method]['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }
    private function batchRemoveHeaderMethod(string $method, string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'setHeaderRemove', "ROUTES()->{$method}()", $header_to_remove);
        if (isset($this->invalidBatches['headers']['methods'][$method]['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Header Name must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (isset($this->validBatches['methods'][$method]['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Header Name must be unique (case-insensitive).",  'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Forbidden Response Header Name ' . $ctxVals, $method);
            return;
        }
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/', $headerName)) {
            $this->setErr($this->getErr('InvalidHeaderName', $ctxVals), 'Invalid Header Name ' . $ctxVals, $method);
            $this->invalidBatches['headers']['methods'][$method]['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Header cannot be removed if it was first configured to be added
        if (isset($this->validBatches['methods'][$method]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictRemovePipedHeader', $ctxVals), 'Conflicting Headers ' . $ctxVals, $method);
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
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Invalid Function Name ' . $ctxVals, $method);
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
        $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Invalid Middleware File Function (also see FILES tab) ' . $ctxVals, $method);
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

    //METHOD+ROUTE: ROUTEPrefixSet()
    private function batchNewRoutePrefixSet(string $method, string $prefix)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'ROUTEPrefixSet', "", $prefix);
        if (isset($this->invalidBatches['methods']['prefix'][$method])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals) . " (Current Invalid Route Prefix: `{$this->invalidBatches['methods']['prefix'][$method]}`)", "Invalid Route Prefix Already Set (`{$this->invalidBatches['methods']['prefix'][$method]}`) " . $ctxVals, $method, null);
            return;
        }
        if (isset($this->validBatches['methods']['prefix'][$method])) {
            $this->setErr($this->getErr('InvalidRoutePrefixResetFirst', $ctxVals) . " (Current Valid Route Prefix: `{$this->validBatches['methods']['prefix'][$method]}`)", "Reset Route Prefix First " . $ctxVals, $method, null);
            return;
        }
        // Route Prefix must be valid just like a regular Route...
        if (
            !is_string($prefix) || (trim($prefix) === '' || trim($prefix) === '/')
            || (strtolower($prefix) !== $prefix)
            || !str_starts_with($prefix, '/')
            || (str_ends_with($prefix, '/') && $prefix !== '/')
            || !preg_match('/^(?!.*[-_]{2,})(?:\/|(?:\/[:]?[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?)+)$/', $prefix)
        ) {
            $this->setErr($this->getErr('InvalidRoutePrefixFormat', $ctxVals), 'Invalid Route Prefix Formatting ' . $ctxVals, $method, null);
            $this->invalidBatches['methods']['prefix'][$method] = $prefix;
            return;
        }
        // ...which includes its Params parts!
        if (str_contains($prefix, ":")) {
            preg_match_all('/:([a-z0-9_-]+)/i', $prefix, $paramMatches);
            if (count($paramMatches[1]) !== count(array_unique($paramMatches[1]))) {
                $this->setErr($this->getErr('InvalidRouteFormatDuplicateParamsPrefix', $ctxVals), 'Duplicate Route Prefix Params ' . $ctxVals, $method, null);
                $this->invalidBatches['methods']['prefix'][$method] = $prefix;
                return;
            }
        }
        // Valid Route Prefix
        $this->routePrefixes[$method] = $prefix;
        $this->validBatches['methods']['prefix'][$method] = $prefix;
    }

    //METHOD+ROUTE: ROUTEPrefixReset()
    private function batchNewRoutePrefixReset(string $method)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, null, 'ROUTEPrefixReset', "");
        if (isset($this->invalidBatches['methods']['prefix'][$method])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals) . " (Current Invalid Route Prefix: `{$this->invalidBatches['methods']['prefix'][$method]}`)", "Invalid Route Prefix Already Set (`{$this->invalidBatches['methods']['prefix'][$method]}`) " . $ctxVals, $method, null);
            return;
        }
        // Reset Current Method Valid Route Prefix
        $this->routePrefixes[$method] = null;
        unset($this->validBatches['methods']['prefix'][$method]);
    }
    // Get current HTTP Method Prefix
    private function batchMethodPrefix(string $method)
    {
        return (isset($this->routePrefixes[$method]) ? $this->routePrefixes[$method] : '');
    }

    /* !!! ROUTE/ROUTES()-><METHOD>()->route()-> BATCHES FUNCTIONS !!! */
    //ROUTE:Batching New Route `->route("/route", $optionalParamRules as an array)`
    private function batchNewRoute(string $method, string $route)
    {
        $rawRoute = $route;
        if (isset($this->routePrefixes[$method])) {
            $rawRoute = substr($route, strlen($this->routePrefixes[$method]));
        }
        [$ctx, $ctxVals] = $this->setCtx($method, $rawRoute, 'ROUTE', "", $rawRoute);

        // Check if the associated $method$route is in the InvalidBatches first
        // OR if it is already as an invalid alias OR a valid alias already exists
        if (isset($this->invalidBatches['methods'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Duplicate Call ' . $ctxVals, $method);
            return;
        }
        // Does $route already exist as a valid one? (meaning it was formatted correctly but duplicate)
        if (isset($this->validBatches['methods'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Duplicate Call ' . $ctxVals, $method, $route);
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
                $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Duplicate Route ' . $ctxVals, $method, $route);
            } else {
                $this->setErr($this->getErr('InvalidRouteFormat', $ctxVals), 'Invalid Route Formatting ' . $ctxVals, $method, $route);
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
                    $this->setErr($this->getErr('InvalidRouteFormatDuplicateParams', $ctxVals), 'Duplicate Params in Route ' . $ctxVals, $method, $route);
                } else {
                    $this->setErr($this->getErr('InvalidRouteFormatDuplicateParams', $ctxVals), 'Duplicate Params in Route ' . $ctxVals, $method, $route);
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
                            $this->setErr($this->getErr('ConflictRouteParam', $ctxVals) . " Parameter `:{$paramName}` conflicts with Locked Parameter `:{$lockedParamName}` first defined in `{$this->cached['placeholderParamContexts'][$contextKey]['first']}`. Either Standardize `{$paramName}` across both routes OR if you want the OTHER Route to be considered the `First defined with {$paramName}`, you will need to swap their File Inclusions in `/src/funkphp/core/app.php` (`\$routeFiles`). Default order: `GET => POST => PUT => PATCH => DELETE`. FunkPHP treats `URI Segments` as `Dynamic Folder Levels`, so a given folder depth can only have one dynamic parameter name (e.g. `[id]` but not both `[id]` and `[name]`). Use `ROUTE()->setParamRulePolymorphic()` to match Multiple Data Types (e.g. `numeric IDs` AND `string Usernames`).", 'Conflicting Params between Routes ' . $ctxVals, $method, $route);
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
        $currentSubRoute = "";
        $subRoutes[] = $currentSubRoute;
        foreach ($splittedRoute as $splitRoute) {
            $currentSubRoute .= "/{$splitRoute}";
            $subRoutes[] = $currentSubRoute;
        }
        // Remove first and last item since that is the root (whose MWs are retrieved other way)
        // and last item is the same as current route which would not be considered a subroute
        if (count($subRoutes) > 0) {
            array_pop($subRoutes);
            array_shift($subRoutes);
        } else {
            $subRoutes = [];
        }
        // Add Valid String Formatted METHOD/Route now; in compilation it will be checked for
        // conflicting URI segments with other routes as we do not know which order they are added!
        $this->validBatches['routes'][$method][$route] = [
            'hasParams' => $routeHasParams,
            'paramRules' => null,
            'response' => null,
            'pipes' => [],
            'pipes-resolved' => [],
            'middlewares' => [],
            'pipes_and_middlewares' => [],
            'middlewares_to_inherit' => [],
            'excludeMiddlewares' => null,
            'routeSplits' => $splittedRoute,
            'subRoutes' => $subRoutes,
            'headers' => ['add' => null, 'remove' => null],
            'csp' => null,
            'nonces' => null,
            'excludeHeaders' => null,
        ];
    }

    //ROUTE: Set & New Batches for ROUTES! (so ->routes()-><Method>()->route()->set|pipe<What>)
    private function batchSetAliasRoute(string $method, string $route, string $alias)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setAlias', "ROUTES()->{$method}()->ROUTE('{$route}')", $alias);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Check if it exists already in invalid or valid batch
        if (isset($this->invalidBatches['aliases'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // Alias formatting with typical alphanumerals plus dot-notation support
        if ($alias === '' || !preg_match('/^[a-zA-Z0-9_.-]+$/', $alias)) {
            $this->setErr($this->getErr('InvalidRouteAliasName', $ctxVals), 'Invalid Route Alias ' . $ctxVals, $method, $route);
            $this->invalidBatches['aliases'][$method][$route] = $alias;
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['alias'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals), 'Route Already has an Alias' . $ctxVals, $method, $route);
            return;
        }
        // Global Uniqueness Check: Aliases CANNOT be duplicated across ANY method
        if (isset($this->cached['routeAliases'][$alias])) {
            $firstDefined = $this->cached['routeAliases'][$alias];
            $this->setErr($this->getErr('DuplicateRouteAliasName', $ctxVals) . "`{$firstDefined}`", 'Route Alias Already in Use ' . $ctxVals, $method, $route);
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
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (
            isset($this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier]) ||
            isset($this->invalidBatches['paramRules']['routes'][$method][$route][$paramIdentifier])
        ) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Param Identifier must be unique (case-insensitive) and this applies both to regular ParamRules as well as Flexible ParamRules.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (
            isset($this->validBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier])
            || isset($this->validBatches['paramRules']['routes'][$method][$route][$paramIdentifier])
        ) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Param Identifier must be unique (case-insensitive) and this applies both to regular ParamRules as well as Flexible ParamRules.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // Does the valid route even have params?
        if (!isset($this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNoParams', $ctxVals), 'Route uses No Params ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = "$paramIdentifier";
        }
        // Validate valid $param identifier formatting
        if (!is_string($paramIdentifier) || !preg_match('/^[a-z0-9_-]+$/', $paramIdentifier)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Invalid Param Name ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = "$paramIdentifier";
            return;
        }
        // $param identifier formatting is valid, but does it exist in the array of hasParams?
        if (!in_array($paramIdentifier, $this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNotChosenParam', $ctxVals) . " The available Params in the Route: " . $this->joinArray($this->validBatches['routes'][$method][$route]['hasParams']) . '.', 'Param Name not in Available Params of Route ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = "$paramIdentifier";
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['paramRules'][$paramIdentifier])) {
            $this->setErr($this->getErr('DuplicateParamRoute', $ctxVals), 'Duplicate Route Param ' . $ctxVals, $method, $route);
            return;
        }
        // Check count on $firstKeyNameSecondKeyRegexThirdKeyDefaultValueAndSoOn and make sure it is an equal count since it needs first each element
        // to be the name identifier of the regex rule that then follows. For example: 'number','/[\d]+/' so that can be stored
        // in the $c['req']['matched_params_flexible']['name'] and so on.
        $pairCount = count($keyAndRegexPairs);
        if ($pairCount === 0 || ($pairCount % 2) !== 0) {
            $this->setErr($this->getErr('InvalidParamFlexibleStringArray', $ctxVals) . " Flexible rules require matching [RuleName, RegexPattern] pairs.", '"RuleName, RegexPattern" Pairs Required ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = $paramIdentifier;
            return;
        }
        $compiledPairs = [];
        for ($i = 0; $i < $pairCount; $i += 2) {
            $ruleName = strtolower(trim($keyAndRegexPairs[$i]));
            $regex    = trim($keyAndRegexPairs[$i + 1]);
            if ($ruleName === '' || !preg_match('/^[a-z0-9_-]+$/', $ruleName)) {
                $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Invalid Param Name ' . $ctxVals, $method, $route);
                $this->invalidBatches['paramRulesFlexible']['routes'][$method][$route][$paramIdentifier] = $paramIdentifier;
                return;
            }
            if (isset($compiledPairs[$ruleName])) {
                $this->setErr($this->getErr('DuplicateFlexibleRegexPairName', $ctxVals) . " `{$ruleName}` is already used for `{$paramIdentifier}`.", 'Duplicate "RuleName, RegexPattern" Pair ' . $ctxVals, $method, $route);
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
                $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Invalid Regex Pattern ' . $ctxVals, $method, $route);
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
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['paramRules']['routes'][$method][$route][$param])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each Param Identifier must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['paramRules']['routes'][$method][$route][$param])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each Param Identifier must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // Does the valid route even have params?
        if (!isset($this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNoParams', $ctxVals), 'Route uses No Params ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => null,
            ];
            return;
        }
        // Validate valid $param identifier formatting
        if (!is_string($param) || !preg_match('/^[a-z0-9_-]+$/', $param)) {
            $this->setErr($this->getErr('InvalidParamName', $ctxVals), 'Invalid Param Name ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => null,
            ];
            return;
        }
        // $param identifier formatting is valid, but does it exist in the array of hasParams?
        if (!in_array($param, $this->validBatches['routes'][$method][$route]['hasParams'])) {
            $this->setErr($this->getErr('RouteHasNotChosenParam', $ctxVals) . " The available Params in the Route: " . $this->joinArray($this->validBatches['routes'][$method][$route]['hasParams']) . '.', 'Param Name not in Available Params of Route ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => null,
            ];
            return;
        }
        $callback = null;
        $cbFN = null;
        // if=callback
        if (
            str_starts_with(strtolower(trim($regex)), 'callback:')
            || str_starts_with(strtolower(trim($regex)), 'cb:')
        ) {
            $regex = strtolower(trim($regex));
            // must be valid fn name and NOT set as global handler
            if (!preg_match('/^(callback|cb){1}:([a-z_][a-z0-9_]*)$/', $regex, $cbFN)) {
                $this->setErr($this->getErr('InvalidParamCBFN', $ctxVals), 'Invalid User-Defined Function Name for Param Rule ' .  $ctxVals, $method, $route);
                $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // User-defined FN already used by Global handlers?
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$cbFN[2]])) {
                $err = $this->getErr('UserDefinedFUNCTIONAlreadyUsedBy', $ctxVals) . "`{$this->cached['placeholderUsedUserDefinedFunctions'][$cbFN[2]]}` and cannot be used for multiple purposes as a result. The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.";
                $this->setErr($err, 'User-defined Function Already In Use ' . $ctxVals, $method, $route);
                $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // User-defined FN even exist?
            if (!$this->cachedUserDefinedFNExists($cbFN[2])) {
                $this->setErr($this->getErr('UserDefinedFUNCTIONNotFound', $ctxVals) . " User-Defined Function `{$cbFN[2]}` as a Callback for a Param Rule was NOT found in other words.", 'User-Defined Function for Param Rule Not Found' .  $ctxVals, $method, $route);
                $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => null,
                ];
                return;
            }
            // Swap places since callback will be used and not a pattern!
            $callback = $cbFN[2];
            $regex = null;
        }
        // else=$regex pattern to use instead of callback
        else {
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
                $this->setErr($this->getErr('InvalidRegex', $ctxVals), 'Invalid Regex Pattern ' . $ctxVals, $method, $route);
                $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                    'pattern' => $regex,
                    'default' => $defaultParamValueOnRegexMismatch,
                    'callback' => $callback,
                ];
                return;
            }
        }
        // Check for duplicate valid rule at route level
        if (isset($this->validBatches['routes'][$method][$route]['paramRules'][$param])) {
            $this->setErr($this->getErr('DuplicateParamRoute', $ctxVals), 'Duplicate Route Param ' . $ctxVals, $method, $route);
            $this->invalidBatches['paramRules']['routes'][$method][$route][$param] = [
                'pattern' => $regex,
                'default' => $defaultParamValueOnRegexMismatch,
                'callback' => $callback,
            ];
            return;
        }
        // Finally add it as valid for that route in
        // $validBatches->paramRules->routes->method->route-><$param>
        // Method-leveled paramRules uses 'paramRules'->'methods',
        // while config() uses 'paramRules'->'global'
        $this->validBatches['routes'][$method][$route]['paramRules'][$param] = [
            'pattern' => $regex,
            'default' => $defaultParamValueOnRegexMismatch,
            'callback' => $callback,
        ];
    }
    /*ROUTE: RateLimiting & setCache */
    private function batchSetRateLimitingRoute(
        string $method,
        string $route,
        int $maxRequestsPerWindowSize = 60,
        int $windowSizeInSeconds = 60,
        $by = 'ip',
        $driver = 'redis'
    ) {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setRateLimit', "", $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['ratelimit']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " You can only set Rate Limit for a Route once.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['ratelimit']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals) . " You can only set Rate Limit for a Route once.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // Max Requests per window size (between 1-1000000)
        if ($maxRequestsPerWindowSize < 1 || $maxRequestsPerWindowSize > 1000000) {
            $this->setErr($this->getErr('InvalidMaxRequests_RateLimit', $ctxVals), 'Invalid $maxRequestsPerWindowSize for Route Rate Limit. Must be between 1 and 1,000,000 ' . $ctxVals, $method, $route);
            $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Window Size in Seconds (between 1-86400 seconds OR 1 second-24 hours)
        if ($windowSizeInSeconds < 1 || $windowSizeInSeconds > 86400) {
            $this->setErr($this->getErr('InvalidWindowSize_RateLimit', $ctxVals), 'Invalid $windowSizeInSeconds for Route Rate Limit. Must be between 1 and 86,400 seconds (24h) ' . $ctxVals, $method, $route);
            $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Driver validation
        $cleanDriver = strtolower(trim($driver));
        if (!in_array($cleanDriver, $this->ALLOWED['drivers']['ratelimit'], true)) {
            $this->setErr($this->getErr('InvalidDriver_RateLimit', $ctxVals) . $this->joinArray($this->ALLOWED['drivers']['ratelimit']) . '.', 'Invalid Driver for Route Rate Limit ' . $ctxVals, $method, $route);
            $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        // Validate `$by` (Identifier strategy)
        $normalizedBy = [];
        if (!is_string($by) && !is_array($by)) {
            $this->setErr(
                $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` must be a `String` or an `Array of Strings`.",
                'Invalid $by Value for Route Rate Limit ' . $ctxVals,
                $method,
                $route
            );
            $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        $items = is_string($by) ? [$by] : $by;
        if (empty($items)) {
            $this->setErr(
                $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` cannot be empty.",
                'Empty $by for Route Rate Limit ' . $ctxVals,
                $method,
                $route
            );
            $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
            return;
        }
        foreach ($items as $item) {
            if (!is_string($item)) {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " Array items in `$by` must all be `Non-Empty Strings`.",
                    'Invalid $by Array Item for Route Rate Limit ' . $ctxVals,
                    $method,
                    $route
                );
                $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            $trimmedItem = strtolower(trim($item));
            if ($trimmedItem === '') {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " `$by` items cannot be `Empty Strings`.",
                    'Empty $by Item for Route Rate Limit ' . $ctxVals,
                    $method,
                    $route
                );
                $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            // Valid identifier formats: 'ip', 'user_id', 'session', 'api_key', 'header:<name>', 'query:<name>'
            if (
                !preg_match('/^(header|query):[a-z0-9_-]+$/i', $trimmedItem)
                && !in_array($trimmedItem, ['ip', 'user_id', 'session', 'api_key'], true)
            ) {
                $this->setErr(
                    $this->getErr('InvalidBy_RateLimit', $ctxVals) . " Item `{$item}` is Invalid. Use formats like `'header:X-Api-Key'`, `'query:token'`, or a direct token from: " . $this->joinArray(['ip', 'user_id', 'session', 'api_key']) . '.',
                    'Invalid $by Format for Route Rate Limit ' . $ctxVals,
                    $method,
                    $route
                );
                $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
            // Duplicate identifier check
            if (!in_array($trimmedItem, $normalizedBy, true)) {
                $normalizedBy[] = $trimmedItem;
            } else {
                $this->setErr($this->getErr('DuplicateRateLimitOption', $ctxVals) . " Rate Limit Identifier `{$trimmedItem}` has already been added.", 'Duplicate Rate Limit Identifier ' . $ctxVals, $method, $route);
                $this->invalidBatches['ratelimit']['routes'][$method][$route] = [$maxRequestsPerWindowSize, $windowSizeInSeconds, $by, $driver];
                return;
            }
        }
        // Add to valid batches when all OK
        $this->validBatches['ratelimit']['routes'][$method][$route] = [
            'max_requests' => $maxRequestsPerWindowSize,
            'window_seconds' => $windowSizeInSeconds,
            'by' => $normalizedBy,
            'driver' => $cleanDriver,
        ];
    }
    private function batchSetCacheRoute(
        string $method,
        string $route,
        int $ttl = 3600,
        string $driver = 'redis',
        string|array|null $varyBy = null,
        bool $private = false
    ) {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setCache', "", $ttl, $driver, $varyBy, $private);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Now validate inValidBatches|validBatches
        if (isset($this->invalidBatches['cache']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " You can only set Cache for a Route once.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['cache']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctxVals) . " You can only set Cache for a Route once.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // TTL (time-to-live) must be 0 OR at most 1 year (31536000)
        if ($ttl < 0 || $ttl > 31536000) {
            $this->setErr($this->getErr('InvalidTTL_Cache', $ctxVals), 'Invalid TTL (time-to-live) for Route Cache ' . $ctxVals, $method, $route);
            $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
            return;
        }
        // Driver (based upon $this->FORBIDDEN['drivers']['cache'])
        if (!in_array($driver, $this->ALLOWED['drivers']['cache'])) {
            $this->setErr($this->getErr('InvalidDriver_Cache', $ctxVals) . $this->joinArray($this->ALLOWED['drivers']['cache']) . '.', 'Invalid Driver for Route Cache ' . $ctxVals, $method, $route);
            $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
            return;
        }
        if (!is_bool($private)) {
            $this->setErr($this->getErr('InvalidPrivate_Cache', $ctxVals), 'Invalid $private Value for Route Cache ' . $ctxVals, $method, $route);
            $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
            return;
        }
        // Validate $varyBy if provided (not null)
        $normalizedVaryBy = [];
        if ($varyBy !== null) {
            if (!is_string($varyBy) && !is_array($varyBy)) {
                $this->setErr(
                    $this->getErr('InvalidVaryBy_Cache', $ctxVals) . " `$varyBy` must be a `String`, an `Array of Strings`, or `null`.",
                    'Invalid $varyBy Value for Route Cache ' . $ctxVals,
                    $method,
                    $route
                );
                $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
                return;
            }
            $items = is_string($varyBy) ? [$varyBy] : $varyBy;
            foreach ($items as $item) {
                if (!is_string($item)) {
                    $this->setErr(
                        $this->getErr('InvalidVaryBy_Cache', $ctxVals) . " Array items in `$varyBy` must all be `Non-Empty Strings`.",
                        'Invalid $varyBy Array Item for Route Cache ' . $ctxVals,
                        $method,
                        $route
                    );
                    $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
                    return;
                }
                $trimmedItem = strtolower(trim($item));
                if ($trimmedItem === '') {
                    $this->setErr(
                        $this->getErr('InvalidVaryBy_Cache', $ctxVals) . " `$varyBy` items cannot be `Empty Strings`.",
                        'Empty $varyBy Item for Route Cache ' . $ctxVals,
                        $method,
                        $route
                    );
                    $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
                    return;
                }
                // Valid prefixes such as: header:x, query:x, cookie:x, param:x, or non-keys like ip/user_id
                if (
                    !preg_match('/^(header|query|cookie|param):[a-z0-9_-]+$/i', $trimmedItem)
                    && !in_array($trimmedItem, ['ip', 'user_id', 'session'], true)
                ) {
                    $this->setErr(
                        $this->getErr('InvalidVaryBy_Cache', $ctxVals) . " Item `{$item}` is Invalid. Use formats like `'header:Accept'`, `'query:page'`, `'cookie:session'`, `'param:id'`, or a single direct one of the following: " . $this->joinArray(['ip', 'user_id', 'session']) . '.',
                        'Invalid $varyBy Format for Route Cache ' . $ctxVals,
                        $method,
                        $route
                    );
                    $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
                    return;
                }
                // error out if duplicate
                if (!in_array($trimmedItem, $normalizedVaryBy, true)) {
                    $normalizedVaryBy[] = $trimmedItem;
                } else {
                    $this->setErr($this->getErr('DuplicateCacheOption', $ctxVals) . " Route Cache Option `{$trimmedItem}` has already been added.", 'Duplicate Route Cache Option ' . $ctxVals, $method, $route);
                    $this->invalidBatches['cache']['routes'][$method][$route] = [$ttl, $driver, $varyBy, $private];
                    return;
                }
            }
        }
        // Add to valid batches when all ok
        $this->validBatches['cache']['routes'][$method][$route] = [
            'ttl' => $ttl,
            'driver' => $driver,
            'varyBy' => !empty($normalizedVaryBy) ? $normalizedVaryBy : null,
            'private' => $private,
        ];
    }

    /*ROUTE: setCSPRoute */
    private function batchSetCSPRoute(string $method, string $route, string $directive, string ...$sources)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setCSP', "ROUTES()->{$method}()->ROUTE('{$route}')", $directive, ...$sources);
        // Route must be valid first
        if (isset($this->invalidBatches['csp']['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Check if already in inValidBatches OR validBatches!
        if (isset($this->invalidBatches['csp']['routes'][$method][$route][$directive])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `\$directive` can only be used/set once.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['csp'][$directive])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `\$directive` can only be used/set once.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        $allowedDirectives = $this->ALLOWED['csp-directives'];
        if ($directive === '' || !in_array($directive, $allowedDirectives, true)) {
            $this->setErr($this->getErr('InvalidCSPDirective', $ctxVals) . $this->joinArray($allowedDirectives), 'Invalid CSP Directive ' . $ctxVals, $method, $route);
            return;
        }
        if (empty($sources)) {
            $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Sources for CSP Directive Missing ' . $ctxVals, $method, $route);
            $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
            return;
        }
        $formattedSources = $this->formatCSPSources($sources);
        if (in_array("'none'", $formattedSources, true) && count($formattedSources) > 1) {
            $this->setErr($this->getErr('ConflictNoneSourceInCSP', $ctxVals), 'CSP Source \'none\' must be used exclusively ' . $ctxVals, $method, $route);
            $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
            return;
        }
        $nonces = []; // cannot use same nonce:<name> twice for same directory
        foreach ($sources as $source) {
            if (!is_string($source)) {
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Invalid CSP Source Value ' . $ctxVals, $method, $route);
                $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                return;
            }
            $trimmed = trim($source);
            // Is it a nonce that is supposed to be in the 'nonces' array instead?
            if (str_starts_with(strtolower($trimmed), 'nonce:')) {
                if (in_array(strtolower($trimmed), $nonces)) {
                    $this->setErr($this->getErr('DuplicateNonceName', $ctxVals) . "`{$trimmed}`. You can only use each Unique Nonce Name once per CSP Directive.", 'Duplicate CSP Nonce Name ' . $ctxVals, $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
                if (!preg_match('/^nonce:[a-zA-Z0-9-_\.]+$/', strtolower($trimmed))) {
                    $this->setErr($this->getErr('InvalidNonceKeyName', $ctxVals), 'Invalid Nonce Key Name ' . $ctxVals, $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
                if (isset($this->validBatches['routes'][$method][$route]['nonces'][strtolower($trimmed)])) {
                    $this->setErr($this->getErr('DuplicateNonceDirectiveUse', $ctxVals) . "`Nonce Name {$trimmed} ` is already being used by CSP Directive: `{$this->validBatches['routes'][$method][$route]['nonces'][strtolower($trimmed)]}`.", 'Duplicate CSP Nonce Key Name ' . $ctxVals, $method, $route);
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
                $this->setErr($this->getErr('InvalidCSPSourceArray', $ctxVals), 'Invalid CSP Source Array Formatting ' . $ctxVals, $method, $route);
                $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                return;
            }
            if (str_contains($trimmed, '*') && $trimmed !== '*') {
                if (!preg_match('/^(https?:\/\/)?\*\.[a-zA-Z0-9\.-]+(:\d+)?$/', $trimmed)) {
                    $this->setErr($this->getErr('InvalidCSPWildcardUse', $ctxVals), 'Invalid CSP Source Wildcard Use ' . $ctxVals, $method, $route);
                    $this->invalidBatches['csp']['routes'][$method][$route][$directive] = $sources;
                    return;
                }
            }
        }
        $this->validBatches['routes'][$method][$route]['csp'][$directive] = array_filter($formattedSources, function ($src) {
            return !str_starts_with($src, 'nonce:');
        });
    }

    /*ROUTE: pipeMiddleware, pipeFunction, pipeResponse, pipeSQL, pipeQuery & pipeValidation */
    private function batchPipeMiddlewareRoute(string $method, string $route, string $middleware)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeMiddleware', "ROUTES()->{$method}()->ROUTE('{$route}')", $middleware);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['middlewares']['routes'][$method][$route][$middleware])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORFNWithoutCLIorFunk($middleware)) {
            $this->setErr($this->getErr('InvalidGroupORFunctionName', $ctxVals), 'Invalid Function Name Formatting ' . $ctxVals, $method, $route);
            $this->invalidBatches['middlewares']['routes'][$method][$route][$middleware] = true;
            return;
        }
        if (in_array($middleware, ($this->validBatches['routes'][$method][$route]['excludeMiddleware'] ?? []), true)) {
            $this->setErr($this->getErr('ConflictingPipeMiddlewareWithAlreadyExcludeMW', $ctxVals) . " Conflict: `->setExcludeMiddleware('{$middleware}')`.", 'Conflicting Middlewares ' . $ctxVals, $method, $route);
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
        $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares", true);
        if ($fatalError !== null) {
            $this->setErr($fatalError, 'Invalid Middleware Function File (also see FILES tab) ' . $ctxVals, $method, $route);
            $this->invalidBatches['middlewares']['routes'][$method][$route][$middleware] = true;
            return;
        }
        // Pipe Route MW when all OK!
        $this->validBatches['routes'][$method][$route]['middlewares'][] = $middleware;
        $this->validBatches['routes'][$method][$route]['middlewares_to_inherit'][] = $middleware;
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
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Route Response Already Added ' . $ctxVals, $method, $route);
            $this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($fileFunctionName)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Invalid Function Name Formatting ' . $ctxVals, $method, $route);
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
            $this->setErr($fatalError, 'Invalid Route Pipe File Function (also see FILES tab) ' . $ctxVals, $method, $route);
            $this->invalidBatches['pipes']['functions']['routes'][$method][$route][$fileFunctionName] = true;
            return;
        }
        $this->validBatches['routes'][$method][$route]['pipes'][] = $fileFunctionName;
    }
    private function batchPipeResponseRoute(string $method, string $route, string $typeOfResponse, int $httpResponseStatusCode = 200)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeResponse', "ROUTES()->{$method}()->ROUTE('{$route}')", $typeOfResponse);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctxVals), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('DuplicateCallValid', $ctx), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (!$this->validateStatusCode($httpResponseStatusCode)) {
            $this->setErr($this->getErr('InvalidHttpStatusCode', $ctxVals), 'Invalid Route Resoponse HTTP(S) Status Code ' . $ctxVals, $method, $route);
            $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
            return;
        }
        if (str_starts_with(strtolower(trim($typeOfResponse)), 'group:')) {
            $this->setErr($this->getErr('GroupPipeResponseNotSupported', $ctxVals), '"group:" Prefix Not Supported ' . $ctxVals, $method, $route);
            $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
            return;
        }
        // The valid Response Types
        if (!preg_match('/^(json:|page:|callback:|text:)/i', $typeOfResponse)) {
            $this->setErr($this->getErr('InvalidResponseType', $ctxVals), 'Invalid Route Response Type ' . $ctxVals, $method, $route);
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
            $this->setErr($this->getErr('InvalidResponseContext', $ctxVals) . $typeErr, 'Invalid Route Response Context' . $ctxVals, $method, $route);
            $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
            return;
        }
        // Check that Page exists in compiled OR non-compiled for page:
        if ($type === 'page') {
            $ctx = trim($ctx);
            if (!$this->cachedPageFileEITHER_TYPEExists($ctx)) {
                $this->setErr($this->getErr('NoPageAtAllFound', $ctxVals) . ' to be used as the `returned Page in pipeResponse()`.', 'Page for Route Response Not Found ' . $ctxVals, $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
        }
        // Check that a Single Simple Array Depth String is used for json:
        else if ($type === 'json') {
            $ctx = trim($ctx);
            if (!preg_match('/^[a-zA-Z0-9-_\.]+$/', $ctx)) {
                $this->setErr($this->getErr('InvalidJSONSourceForResponseCtx', $ctxVals) . ' to be used as the `returned JSON Data in pipeResponse()`.', 'Invalid JSON Data for Route Response ' . $ctxVals, $method, $route);
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
                $this->setErr($this->getErr('UserDefinedFUNCTIONNotFoundForResponseCtx', $ctxVals) . ' to be used as the `returned User-defined Callback Function in pipeResponse()`.', 'User-defined File Function for Route Response Not Found ' . $ctxVals, $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
            if (isset($this->cached['placeHolderUsedUserDefinedEngineFNS'][$ctx])) {
                $this->setErr($this->getErr('UserDefinedFNSetAsEngineFN', $ctxVals) . ' It cannot be as the `returned User-defined Callback Function in pipeResponse()`. See `' . $this->cached['placeHolderUsedUserDefinedEngineFNS'][$ctx] . '`', 'User-defined File Function for Route Response Already in Use ' . $ctxVals, $method, $route);
                $this->invalidBatches['pipes']['responses']['routes'][$method][$route][strtolower(trim($typeOfResponse))] = true;
                return;
            }
        } else if ($type === 'text') {
            // Nothing really needs to be done here.
        }
        // All good by here so add!
        $this->validBatches['routes'][$method][$route]['response'] = ['type' => $type, 'context' => $ctx, 'code' => $httpResponseStatusCode];
    }
    private function batchPipeSQLRoute(string $method, string $route, string $sqlFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeSQL', "ROUTES()->{$method}()->ROUTE('{$route}')", $sqlFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['sql']['routes'][$method][$route][$sqlFileFunction])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Duplicate Call ' . $ctxVals, $method, $route);
            $this->invalidBatches['sql']['routes'][$method][$route][$sqlFileFunction] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($sqlFileFunction)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Invalid Function Name Formatting ' . $ctxVals, $method, $route);
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
            $this->setErr($fatalError, 'Invalid Data SQL File Function (also see FILES tab) ' . $ctxVals, $method, $route);
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
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['query']['routes'][$method][$route][$queryFileFunction])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Duplicate Call ' . $ctxVals, $method, $route);
            $this->invalidBatches['query']['routes'][$method][$route][$queryFileFunction] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($queryFileFunction)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Invalid Function Name Formatting ' . $ctxVals, $method, $route);
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
            $this->setErr($fatalError, 'Invalid Data Query File Function (also see FILES tab) ' . $ctxVals, $method, $route);
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
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['validation']['routes'][$method][$route][$validationFileFunction])) {
            $this->setErr($this->getErr('DuplicateCallInvalid', $ctx), 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['response'])) {
            $this->setErr($this->getErr('ConflictResponseAlreadyAdded', $ctxVals), 'Duplicate Call ' . $ctxVals, $method, $route);
            $this->invalidBatches['validation']['routes'][$method][$route][$validationFileFunction] = true;
            return;
        }
        if (!$this->nonEmptyLC_Str_ThatISGroupORRouteFileFNWithoutCLIorFunk($validationFileFunction)) {
            $this->setErr($this->getErr('InvalidGroupORFileFunctionNames', $ctxVals), 'Invalid Function Name Formatting ' . $ctxVals, $method, $route);
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
            $this->setErr($fatalError, 'Invalid Data Validation File Function (also see FILES tab) ' . $ctxVals, $method, $route);
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
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
    }
    private function batchPipeCompiledQueryRoute(string $method, string $route, string $compiledQueryFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeCompiledQuery', "ROUTES()->{$method}()->ROUTE('{$route}')", $compiledQueryFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
    }
    private function batchPipeCompiledValidationRoute(string $method, string $route, string $compiledValidationFileFunction)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'pipeCompiledValidation', "ROUTES()->{$method}()->ROUTE('{$route}')", $compiledValidationFileFunction);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
    }

    /*ROUTE: excludeMiddleware & excludeHeaders */
    private function batchExcludeMiddlewaresRoute(string $method, string $route, string ...$middlewareToExclude)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setExcludeMiddlewares', "ROUTES()->{$method}()->ROUTE('{$route}')", ...$middlewareToExclude);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['excludeMiddlewares']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " Set all Middlewares to exclude (this applies both on Method and Global Config) once for this Route.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['excludeMiddlewares'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctx) . " Set all Middlewares to exclude (this applies both on Method and Global Config) once for this Route.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // Check that all Middlewares exist; later compile() will also
        // check they exist on correct associated sub-route-depth!
        // and that they do not clash with piped middlewares on the same route as that is conflicting
        $validMWs = [];
        foreach ($middlewareToExclude as $middleware) {
            $middleware = strtolower(trim($middleware));
            if (!$this->nonEmptyLowercaseStrNotStartWithCLIorFunk($middleware)) {
                $this->setErr($this->getErr('InvalidMiddlewareFunctionName', $ctx) . " Review the Invalid `{$middleware}`.", 'Invalid Middleware Name Formatting ' . $ctxVals, $method, $route);
                $this->invalidBatches['excludeMiddlewares']['routes'][$method][$route] = true;
                return;
            }
            if (in_array($middleware, ($this->validBatches['routes'][$method][$route]['middlewares'] ?? []), true)) {
                $this->setErr($this->getErr('ConflictingExcludeMWWithAlreadyPipedMW', $ctxVals) . " Conflict: `->pipeMiddleware('{$middleware}')`.", 'Conflicting Middlewares ' . $ctxVals, $method, $route);
                $this->invalidBatches['excludeMiddlewares']['routes'][$method][$route] = true;
                return;
            }
            $this->cachedCreateKeyIfNullAndOptionalFileName('files_pipes_middlewares', $middleware);
            $fileData = $this->cached['files_pipes_middlewares'][$middleware] ?? [];
            // Fatal check: Bails on the first structural error
            $fatalError = $this->validateFNFile($fileData, $middleware, $ctxVals, "funkphp\\pipes\\middlewares\\{$middleware}", true);
            if ($fatalError !== null) {
                $this->setErr($fatalError, 'Invalid Middleware File Function (also see FILES tab) ' . $ctxVals, $method, $route);
                $this->invalidBatches['excludeMiddlewares']['routes'][$method][$route] = true;
                return;
            }
            $validMWs[] = $middleware;
        }
        // Add to excludeMiddleware when all OK!
        $this->validBatches['routes'][$method][$route]['excludeMiddlewares'] = $validMWs;
    }
    private function batchExcludeHeadersRoute(string $method, string $route, string ...$headersToExclude)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setExcludeHeaders', "ROUTES()->{$method}()->ROUTE('{$route}')", ...$headersToExclude);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->invalidBatches['excludeHeaders']['routes'][$method][$route])) {
            $this->setErr($this->getErr('DuplicateCallinValidCanOnlyBeSetOnce', $ctx) . " Set all Headers to exclude (this applies both on Method and Global Config) once for this Route.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['excludeHeaders'])) {
            $this->setErr($this->getErr('DuplicateCallValidCanOnlyBeSetOnce', $ctx) . " Set all Headers to exclude (this applies both on Method and Global Config) once for this Route.", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        $validHeaders = [];
        foreach ($headersToExclude as $header) {
            $header = strtolower(trim($header));
            if ($header === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $header)) {
                $this->setErr($this->getErr('InvalidHeaderName', $ctxVals) . " Review the Invalid `{$header}`.", 'Invalid Header Name ' . $ctxVals, $method, $route);
                $this->invalidBatches['excludeHeaders']['routes'][$method][$route] = $headersToExclude;
                return;
            }
            if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$header])) {
                $this->setErr($this->getErr('ConflictingExcludeHeadersWithAlreadyPipedHeader', $ctxVals) . " Conflict: `->pipeHeader('{$header}')`.", 'Conflicting Headers ' . $ctxVals, $method, $route);
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
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setHeaderAdd', "ROUTES()->{$method}()->ROUTE('{$route}')", $header);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        $headerName  = $header;
        $headerValue = $value;
        $lowerHeader = strtolower($headerName);
        // Then check against valid/invalid batches
        if (isset($this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Each `Header Name` must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // Check for special case: cannot use content-security-policy (use setCSP instead)
        if ($lowerHeader === 'content-security-policy') {
            $this->setErr($this->getErr('InvalidHeaderNameChoiceCSP', $ctxVals), 'Cannot use Header Name `Content-Security-Policy`, use `->setCSP()` instead', $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // Forbid possible CRLF injection
        if (
            $headerName === '' || $headerValue === ''
            || str_contains($headerName, ":") || str_contains($headerValue, ":")
            || str_contains($headerName, "\r") || str_contains($headerName, "\n")
            || str_contains($headerValue, "\r") || str_contains($headerValue, "\n")
        ) {
            $this->setErr($this->getErr('InvalidAddHeaderFormat', $ctx), 'Invalid Header Name ' . $ctxVals, $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Forbidden Response Header Name ' . $ctxVals, $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // First check if it already exists
        if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Each `Header Name` must be unique (case-insensitive).", 'Duplicate Header ' . $ctxVals, $method, $route);
            return;
        }
        if (in_array($lowerHeader, ($this->validBatches['routes'][$method][$route]['excludeHeaders'] ?? []), true)) {
            $this->setErr($this->getErr('ConflictingPipeHeaderWithAlreadyExcludeHeaders', $ctxVals) . " Conflict: `->setExcludeHeaders('{$lowerHeader}')`.", 'Conflicting Headers ' . $ctxVals, $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // Cannot add a header that was first meant to be removed
        if (isset($this->validBatches['routes'][$method][$route]['headers']['remove'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictPipeRemovedHeader', $ctxVals), 'Conflicting Headers ' . $ctxVals, $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['add'][$lowerHeader] = true;
            return;
        }
        // Store header to be addd from Route level (->config()->ROUTES()-><METHOD>-><ROUTE>)
        $this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader] = ['name' => $headerName, 'value' => $headerValue];
    }
    /*ROUTE: setRemoveHeaderRoute*/
    private function batchRemoveHeaderRoute(string $method, string $route, string $header_to_remove)
    {
        [$ctx, $ctxVals] = $this->setCtx($method, $route, 'setHeaderRemove', "ROUTES()->{$method}()->ROUTE('{$route}')", $header_to_remove);
        // Route must be valid first
        if (isset($this->invalidBatches['routes'][$method][$route])) {
            $this->setErr($this->getErr('RouteIsInvalidMustBecomeValidBeforeWhat', $ctxVals), 'Route is Invalid - must become Valid First ' . $ctxVals, $method, $route);
            return;
        }
        // Then check against invalid/valid batches
        if (isset($this->invalidBatches['headers']['routes'][$method][$route]['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallInvalidMustBeSetWithDifferentValues', $ctx) . " Header Name must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        if (isset($this->validBatches['routes'][$method][$route]['headers']['remove'][$header_to_remove])) {
            $this->setErr($this->getErr('DuplicateCallValidMustBeSetWithDifferentValues', $ctxVals) . " Header Name must be unique (case-insensitive).", 'Duplicate Call ' . $ctxVals, $method, $route);
            return;
        }
        // We will store both the original value and its lowercased version to find future conflicts
        $headerName = trim($header_to_remove);
        $lowerHeader = strtolower($headerName);
        if (in_array($lowerHeader, $this->FORBIDDEN['headers'], true)) {
            $this->setErr($this->getErr('ForbiddenResponseHeaders', $ctxVals) . " Header Name `'{$lowerHeader}'` is a Forbidden Response Header along with: " . $this->joinArray($this->FORBIDDEN['headers']) . '.', 'Forbidden Response Header Name ' . $ctxVals, $method, $route);
            return;
        }
        // Header names cannot contain colons, spaces, or CRLF injections
        if ($headerName === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/i', $headerName)) {
            $this->setErr($this->getErr('InvalidHeaderName', $ctxVals), 'Invalid Header Name ' . $ctxVals, $method, $route);
            $this->invalidBatches['headers']['routes'][$method][$route]['remove'][$lowerHeader] = $header_to_remove;
            return;
        }
        // Header cannot be removed if it was first configured to be added
        if (isset($this->validBatches['routes'][$method][$route]['headers']['add'][$lowerHeader])) {
            $this->setErr($this->getErr('ConflictRemovePipedHeader', $ctxVals), 'Conflicting Headers ' . $ctxVals, $method, $route);
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
    private function compile_setWarn(string $errShort, string $err)
    {
        if (!isset($this->errors['COMPILATION']['warnings'])) {
            $this->errors['COMPILATION']['warnings'] = [];
        }
        $this->errors['WARNINGS']++;
        $this->errors['COMPILATION']['warnings'][count($this->errors['COMPILATION']['warnings']) + 1] = ['warnShort' => $errShort, 'warn' => $err];
    }
    private function compile_setErr(string $errShort, string $err)
    {
        if (!isset($this->errors['COMPILATION']['errors'])) {
            $this->errors['COMPILATION']['errors'] = [];
        }
        $this->errors['ERRORS']++;
        $this->errors['COMPILATION']['errors'][count($this->errors['COMPILATION']['errors']) + 1] = ['errShort' => $errShort, 'err' => $err];
    }

    // Add current compiled route to trie ($this->compiled['routes']['trie'])
    private function compile_add_to_route_trie(string $method, string $route): void
    {
        if ($route === '') {
            return;
        }
        if ($route === '/') {
            $this->compiled['routes']['trie'][$method]['/'] = [];
            return;
        }
        $segments = explode('/', trim($route, '/'));
        $currentNode = &$this->compiled['routes']['trie'][$method];
        foreach ($segments as $segment) {
            if (str_starts_with($segment, ':')) {
                // Dynamic parameter segment
                $paramName = substr($segment, 1);
                if (!isset($currentNode[':'])) {
                    $currentNode[':'] = [];
                }
                if (!isset($currentNode[':'][$paramName])) {
                    $currentNode[':'][$paramName] = [];
                }
                $currentNode = &$currentNode[':'][$paramName];
            } else {
                $segmentStr = (string)$segment;
                if (!isset($currentNode[$segmentStr])) {
                    $currentNode[$segmentStr] = [];
                }
                $currentNode = &$currentNode[$segmentStr];
            }
        }
    }
    // Create metadata after all routes added to the trie
    private function compile_build_trie_metadata(): void
    {
        $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        $metadata = [
            '<ALL>' => [
                'totalAllRoutes'     => 0,
                'totalStaticRoutes'  => 0,
                'totalDynamicRoutes' => 0,
                'minURICountAll'     => 0,
                'maxURICountAll'     => 0,
            ]
        ];
        $allMins = [];
        $allMaxs = [];
        foreach ($validMethods as $method) {
            $methodRoutes = $this->compiled['routes'][$method] ?? [];
            $segmentCountsCollected = [];
            $metadata[$method] = [
                'allRoutes'               => [],
                'staticRoutes'            => [],
                'dynamicRoutes'           => [],
                'minURICount'             => 0,
                'maxURICount'             => 0,
                'URICountExistsForNumber' => [],
                'allRoutesCount'          => 0,
                'staticRoutesCount'       => 0,
                'dynamicRoutesCount'      => 0,
            ];
            foreach ($methodRoutes as $routeStr => $routeConfig) {
                if ($routeStr === '/') {
                    $segmentCount = 0;
                } else {
                    $trimmedRoute = trim($routeStr, '/');
                    $segmentCount = substr_count($trimmedRoute, '/') + 1;
                }
                $segmentCountsCollected[] = $segmentCount;
                $metadata[$method]['URICountExistsForNumber'][$segmentCount] = 1;
                $metadata[$method]['allRoutes'][$routeStr] = 1;
                if (str_contains($routeStr, ':')) {
                    $metadata[$method]['dynamicRoutes'][$routeStr] = 1;
                } else {
                    $metadata[$method]['staticRoutes'][$routeStr] = 1;
                }
            }
            $metadata[$method]['allRoutesCount']     = count($metadata[$method]['allRoutes']);
            $metadata[$method]['staticRoutesCount']  = count($metadata[$method]['staticRoutes']);
            $metadata[$method]['dynamicRoutesCount'] = count($metadata[$method]['dynamicRoutes']);
            if (!empty($segmentCountsCollected)) {
                $min = min($segmentCountsCollected);
                $max = max($segmentCountsCollected);
                $metadata[$method]['minURICount'] = $min;
                $metadata[$method]['maxURICount'] = $max;
                $allMins[] = $min;
                $allMaxs[] = $max;
            }
            $metadata['<ALL>']['totalAllRoutes']     += $metadata[$method]['allRoutesCount'];
            $metadata['<ALL>']['totalStaticRoutes']  += $metadata[$method]['staticRoutesCount'];
            $metadata['<ALL>']['totalDynamicRoutes'] += $metadata[$method]['dynamicRoutesCount'];
        }
        $metadata['<ALL>']['minURICountAll'] = !empty($allMins) ? min($allMins) : 0;
        $metadata['<ALL>']['maxURICountAll'] = !empty($allMaxs) ? max($allMaxs) : 0;
        $this->compiled['routes']['trie_metadata'] = $metadata;
    }
    // Resolve file & namespace running paths for a type of fn or page file
    private function compile_resolve_fn_paths(string $type, string $fn)
    {
        $validTypes = [
            'middleware',
            'request',
            'post_response',
            'pipe',
            'user-defined',
            'page-uncompiled',
            'page-compiled',
            'sql-uncompiled',
            'sql-compiled',
            'query-uncompiled',
            'query-compiled',
            'validation-uncompiled',
            'validation-compiled',
        ];
        if (
            !isset($type) || !is_string($type)
            || strtolower(trim($type)) === '' || !in_array(strtolower(trim($type)), $validTypes)
        ) {
            $err = "[Class C->compile_resolve_fn_paths()]: `\$type` passed to Function must be one of the following (case-insensitive): " . $this->joinArray($validTypes) . ". Report this INTERNAL Error as a Bug/Issue to the Official FunkPHP Repositories.";
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Invalid $type Value Passed: `' . $type . '`', 'err' => $err];
            $this->errors['ERRORS']++;
            return ['<INVALID_$type_PROVIDED>', '<INVALID_$type_PROVIDED>'];
        }
        if (!defined('ROOT_FOLDER')) {
            $err = "[Class C->compile_resolve_fn_paths()]: Constant `ROOT_FOLDER` must exist that is the Root Folder pointing to `/src/funkphp` in order to resolve correct inclusion/running paths. Report this INTERNAL Error as a Bug/Issue to the Official FunkPHP Repositories.";
            $this->errors['INTERNAL'][count($this->errors['INTERNAL']) + 1] = ['errShort' => 'Missing Internal Constant', 'err' => $err];
            $this->errors['ERRORS']++;
            return ['<CONSTANT_ROOT_FOLDER_MISSING>', '<CONSTANT_ROOT_FOLDER_MISSING>'];
        }
        $type = strtolower(trim($type));
        if ($type === 'middleware') {
            return ["\\funkphp\\pipes\\middlewares\\$fn", ROOT_FOLDER . "/pipes/middlewares/$fn.php"];
        }
        if ($type === 'request') {
            return ["\\funkphp\\pipes\\response\\$fn", ROOT_FOLDER . "/pipes/request/$fn.php"];
        }
        if ($type === 'post_response') {
            return ["\\funkphp\\pipes\\post_response\\$fn", ROOT_FOLDER . "/pipes/post_response/$fn.php"];
        }
        if ($type === 'pipe') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\pipes\\routes\\{$parts[0]}\\{$parts[1]}", ROOT_FOLDER . "/pipes/routes/{$parts[0]}.php"];
        }
        if ($type === 'user-defined') {
            return ["\\$fn", ROOT_FOLDER . "/config/functions.php"];
        }
        if ($type === 'sql-uncompiled') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\data\\sql\\{$parts[0]}\\{$parts[1]}", ROOT_FOLDER . "/data/sql/{$parts[0]}.php"];
        }
        if ($type === 'query-uncompiled') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\data\\query\\{$parts[0]}\\{$parts[1]}", ROOT_FOLDER . "/data/query/{$parts[0]}.php"];
        }
        if ($type === 'validation-uncompiled') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\data\\validation\\{$parts[0]}\\{$parts[1]}", ROOT_FOLDER . "/data/validation/{$parts[0]}.php"];
        }
        if ($type === 'sql-compiled') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\data\\compiled\\sql\\{$parts[0]}_{$parts[1]}", ROOT_FOLDER . "/data/compiled/sql.php"];
        }
        if ($type === 'query-compiled') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\data\\compiled\\query\\{$parts[0]}_{$parts[1]}", ROOT_FOLDER . "/data/compiled/query.php"];
        }
        if ($type === 'validation-compiled') {
            $parts = explode('.', $fn, 2);
            return ["\\funkphp\\data\\compiled\\validation\\{$parts[0]}_{$parts[1]}", ROOT_FOLDER . "/data/compiled/validation.php"];
        }
        if ($type === 'page-compiled') {
            return ["\\funkphp\\pages\\compiled\\{$fn}", ROOT_FOLDER . "/pages/compiled/{$fn}.php"];
        }
        if ($type === 'page-uncompiled') {
            return ["\\funkphp\\pages\\uncompiled\\{$fn}", ROOT_FOLDER . "/pages/{$fn}.php"];
        }
    }
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
    private function output_errors(array $internalErrors = [], array $compiled = [])
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
        $fontLightDiskPath = ROOT_PUBLIC_HTML . '/fonts/Fredoka-Regular.ttf';
        $PHTML_IMG_SRC = file_exists($imgDiskPath) ? "{$baseUrl}/images/favicon.ico" : "";
        $PHTML_FONT_SRC = file_exists($fontDiskPath) ? "{$baseUrl}/fonts/Fredoka-Bold.ttf" : "";
        $PHTML_FONT2_SRC = file_exists($fontLightDiskPath) ? "{$baseUrl}/fonts/Fredoka-Regular.ttf" : "";

        // Base64 Images
        $COMPILED_BASE64 = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAB5UlEQVR4nO1WXU7CQBDuAVATTYw+eAY9hpiYkBi5iBp26kMfxZBYOA/BGUCMR+BZgpfg4TNbtqSU7X9540smaXens9/OfDtdxznAQAnuSLAgATJs4QqaTt2gfIsHphi/+yCAiHFnhOvw3Txz1MfyTdympQgoxuRphnNijKLB4mM5CKAUgdcxrkjQswTsmbnM4JUIkICD3W6nfGcsKY5izEqVgWIacBk34bt53tGAhsdoKEbfFdxb4gR+eo4Evvat9RR4jIbeZTAmWGkiFt++ngszkkjCFTR14DyLK8atIe3nJR0xv3BZnATo1EZ2F5DrfOLh5QtH2ojRUoL5Zl6wCkuVCrUtpFRBEWMQLv7MuFCMd8X4I8aSGF0zts4sY5B351NL6idpJPXO9YI7xBldJXis1KRsiC9kUr60EFh6Pziu1KSqEugMcbIPAtPYQi1bCZTgjQTttHJWwkaEgrkWXEBiLcBAhO4Yl5sekyZCVeI+YDuGWnC65qbu7WjM1GNIxtH7xmkiySHOop2wTCNSgo8kAgjFkfRx3K/WVkw51Rn3MyT8rJ+R3nnWzwhFM5B1OgqpnWoikNenFgKUIbiiBBZB2oY4K3AK0n7bxZqNW+I+sHdQXX27KFSB+8BeQDnuAwc4NeEfSbNmHP2EIRUAAAAASUVORK5CYII=" style="height:25px;">';
        $WARNING_BASE64 = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAACXBIWXMAAAsTAAALEwEAmpwYAAADX0lEQVR4nO2ZTUgVURTHb2WLggqKWkUu+ti0iiLCxDnnaWWo9E1tpQ8IIVoEgYtsI1TbWlW0k1AqfOdMFhlIC2lhm8JVJPTueVpktajog8qJqymi7+m8eTNv5sn9wYHh8ebM+f/nzr33zChlsVgsFovFYrFYLCUiQ061MLQK462JgNbhNOxWCx1hB4TwlTB6uUIzvNQu1qiFiHDqvCb8nU/8lAnmPwTn1EIim3YaNeHYfOKnmTCmyTmoFgLv7u9ZJ4Sf/YqfCoJP5lxV7sjERFeY+KmRADdVOZPtrtmmGf4ENoDhb6bb2aHKFSF4FlT8tFHQ73lqkSo3NDsnihU/GRmC46qc0F27lmmCt2EZoAllmBqXq3JBE7SFJX7ao3BJlQPS46zXBN/CNkAYv4+4WKmSjjB0+JvhcVQIH5nQhB/8mQAdKslkXazys+PTDPS6p37l5HnmWBi7fcwFY+YaKol4bW2LhXDAj4iRnuq1M883v/naLhMOmGuppKEJm/0NffiYNwfDR5+rQrNKEqPdVSuEcMTns/8lXx7N+NXnXPB+qLdulUoKQnjV/3KGY16fUzEzx4sX25cW2DFeUUkg04MbheBnIUta9kHtmlxzQGHLIvzKPqzdouJGCNKFFY6eTjubZuXhms2F5jErRzyq/yNpTAUo2svV4WlO7QySSzPsU3Hg9TkVwjAYpGhx8fDMfFk3dSxQLobBXHNK5GiClmAFj9+1zln5GO8FzkfQUlrxj/euNq+sghY8+fwK42mdhpN+doFzBsEnU1PJDBDGG0WKDz8IrpdEfCaNW/283vbx7D4RxsumddaEvcXmMzWZ2iI3QCYKL7bYC7PyunAxDFOjFe9CQ9HiGb/mama8rmNL/G+F5wgXGiIzQDM+Ld6A/M1QCBOrGV29kYgfcbGykL36XJFJw4HZ5qYOhZHb1DhMdRtCN0AotT+MAseD8IcwXMtSqsl8NjPHhfYTc5rgOvWhG6DJORWaARGH2VuEbkCWUk1xC4t1IpRg3VosYVp0FQWa8Xnihz9Bv4oKYedo3ALnDYIjkRlgEIY7sYvMH7dVSb77MXQm787jXVObKhXi4hkhGIpfOLwxbbWK7WMIO5BhPKsJ282b2hJF+8Q1wUnkRxKLxWKxWCwWi8Wiks4/S+qQp9eHxIoAAAAASUVORK5CYII=" style="height:25px;">';
        $ERROR_BASE64 = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAACXBIWXMAAAsTAAALEwEAmpwYAAADHUlEQVR4nO2ZPWgUURDHnxoLBSMosRJTRG1SCWIRRYONSgx+Bm1DNBDChZ3ZxMu9WVwbQdNqpWIXJGARbQRjYxEsUikpFQRBRBMLI36g5i97l3jCfWR3b/d273g/mO7e3Pz/b2/uzT6lDAaDwWAwGAwGg6FOQPgQhDWE7haCNXLWQdXsQOxuaH4FYZQPegmxD6tmBJosaPpVWfxKeJ/RNKKaCTh8EsLLa4ovxjIcPq2aAbj2Dgh9DiB+5UngRW+tanRQaHLBxBdNuKMaGTi8D8K/wxtAf6Ct/apRgabnocUXTZiFUutUo4EcXaxZfDEuqEYCRJug6W1kBmh6B3dws2oUoNmNcPdXD0lXVSMA98pOCH2N3gD+BrHbVdqB8KTPx/oTND/Jh/BHnyZMqjQD4S6fJ77HcDOt/9a5mVYIT/tY5+XuUmkErrsewnO+ROQybSXrc5k2n+bNed+l0gaE+n0+xguVc/CCz4bYr9IExsa2QPi9z+K/VMyjecmniR+QzW5VaQGabgbo5stw3ZaSHIODGwNOjDdUGoCMdkD4R6C/tPHh7RV6gP8cmn5inPcmo/o/oOlRoMLzBtDukjzO2J7AeYSnVZJA7KMhika5CQ+aDoTLxceSEe+6LdA8H6poxz5bkk+4L6QB8+V6SvwGCA+HKrgQU2XyPawh33B9xbu0Lf/KKnzB+d8vhC5DaMDnKbDaU7Do1VQ/AzTdrlF8HHGrPuIdq9PX6+01g55C6Fp+dNY8U3M+rybH6ozfAPEKr3m3RkvyaspGYWq84rXdU/tO8VK5YQZ9fRsCHIWr5Ld74jNA6FkEu195GKq9sXoGz8Qk3m4PeFavUqR1qlQ8nYkkt1djdmRX9AZo+0REBXoN6zuEJ6Ct3pVrs4nA80TVoOMxGMCXoisw7qCBGAywepMXlmAjRLhpLaEY7YjcAA8Iv0j/7tOsigs49vkGMOBcbAZ4QPP99Irne6ou937CU4mLLY0HXm2xG7AKtD0I4TeJC9f02hurVXKXIXY3hIeg6br3prYuUfiuIeToSCovSQwGg8FgMBgMBoNKO38BiP/u0tQ90GMAAAAASUVORK5CYII=" style="height:25px;">';
        $API_TREE_BASE64 = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAACXBIWXMAAAsTAAALEwEAmpwYAAABXklEQVR4nO3asUrDUBTG8UxuOvga9mVEfBTrPSAE3FxUlL6N9X6DFHcrOOsbqKPySelyW6SmwWh6zveHjLec/NIkw01VKaWUUhuTgWxzVF4yAVAAJgD6ArDMQcq8NHBq4PuqwV0B1FNuWeYoZX42HdwNQD0/+fG6g7sBsMxRm8FdAFjmwMCPcsCU+Xx8y4OjO26vXOsBIM0feAsnX0+422StF4DHcrjZlW+61gWAZb6Ww/30t/cHgPbDhQdwkQmA/QewDu81AUAA7P1bwARAAZgAKAD7/2MYGyDzJDYAeBoaIGWebRRA1eK3+zLXQgKAACgACIB9A/izLDpAlwkAAqAA0PO3QJeF3xpLy5uj4H4oAAMvyuES+BRqe3w45t7yBxIGviTwsL7njnuAWQZetxne1UdSCbwJC1AgXH1zO8QAKJ8JCTxP4EMC38IBrJMAIAAKAAJgWACllFLV7/UFRqMuZVg/Fm0AAAAASUVORK5CYII=" style="height:25px;">';
        $CONFIG_BASE64 = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAACXBIWXMAAAsTAAALEwEAmpwYAAABTklEQVR4nO2WsUoDQRCGDwQFfRTfIFiI+Ao+gNiaKuDNYJF0amlhIQTEQgTbgGAhM1dYBCwsfANJFEQQGyv9Ze4ucmzuTGL2rrofBo6Z3f12do+dCYJatcpW+xGLJDgiwZAV8GqCAQsOjTEGtoB3oGOkOMgDDyy4H6Hh+zRDwdoo83Fwuivf0Inrcw32LK7BVYFDwToLTisHF8onuHmNJVa0WPFAik9SvJBgs1Rw6wYrJLhz3upO4QRfYBacTA31Cla8/hYFwUXemDDCRhkZx8UmtV5OvGOlNwt+tsFWSeYEH2cyHrpQTvxvc9djEnyRYHv0Y7EgcuLWWPRIcZnx9d2j8Qr9Y97u/zsPwTdH2JkZqtCtKyzEvVYMnzFzEnQnQs2f3PmTPSL23b7HcuBDJDgvgtqmgrLEgo/KoUGScb9yqGnvFqsp/J0UZ9NCfwBksk4l0OsZrwAAAABJRU5ErkJggg==" style="height:25px;">';
        $ROUTE_BASE64 = "<img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAACXBIWXMAAAsTAAALEwEAmpwYAAACs0lEQVR4nO2av2sUQRTHR2wUIdcZFH+A2Fjqn6BRWwv9B2y0lcHszKU4SwsFCxGxtIzGHHZySd4s0RDsJCcKlilFjEas1K/M4shxbszt7Ozt3M584DV3x7z9fndn9ua9YSwSiUQikUikBpIeWjpYSLRXcFEQFoTCtlRAFoSvUuGpVDjPmkrSQ0sQnv8VvXN0O+uYYk1CLmFaKGyMID6L7LdLmGZNgL/CwSLiB0x4305xiIUoXjbBBF5S/ESbwB2Jn0gTuGPxE2UCr0j8RJjAKxbvtQl8TOK9NIGPWbxXJvCaxHthAq9ZfK0mcE/E12KC1BsbwluHAn60U5xJFE4LwhtrE/Q1Vb2Bku7F69g043cI+wThvpcmJD20pEK/gkd48x+jCZcE4ZPldNiopJ4gRitm7BYfpMJjQbgjCbd1CILMyzeX4qgkpJZ5um7Fp7hQRrhQeJekOFs075V57JWEW3qdsJgOM+4MICxYG0B4XbbwqWuFkvCroOlPXBrwxdKAb7OEI2Xz8xc4IAnfCxrw2eXiB6tHn3CvbP65ZRyXhDWb/E4Ww6SEAXIF5/LGvPkSh8UqTuhIVnFsp9yScFnfSdv8znoOwnIKCMLJnLGu7/YavLGG/YLw0Np4l1NA86dpUfgi8ua/JNz9nwGzCqfK/CMciHnmCkGYqdCALf26y75Lca3oYld0+tmboLDoZAooPMh5XJVUICfCdRCeMdd01jFVdAeYKFwdHEPf6THsIvuVtdZk8c3QR90MZcCerDeo8KhK8d7uCIXCtiD8nHjxhqALIr6ZEHRdUIRcGRY+iDcE3RgxBN0aMwTdHDUE3R43BH1AwhD0ERlD0IekDEEfkzPoTUrBrlK/MQclh4qr3RHu/GLjjsoONzl002Ko0LqlP3NexvKdJMTj8pFIJBKJRJgv/AbJ5iExuMEOugAAAABJRU5ErkJggg==\" style=\"height:25px;\">";
        $OOP_BASE64 = "<img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAYAAAA6/NlyAAAACXBIWXMAAAsTAAALEwEAmpwYAAACzklEQVR4nO2Zz3LTMBDG9RiUP8fSdwE6fRlKtElOMMzwKi0FLky5JJHSwtsUBgKXnj5mEzm1N7YS/6tVxd+MDnU/O/uzbGl3rZRSajTHM7K4IIMFWSCqYbDQFl/GUxypBFYb/Ow8sPbBfzGrWs6sBbTF1+EET1RkemPwVBtcOvBzlTzG/A8VqUarVxba4I9Kpjz5pzb44Y5dlz3WhVJx5I3rxLfmlMBscnfjquyxLpTEkTd0Nt584FhFew9MezLU3gKryEU9cOSiHjhyUQ8cuagH9kh0RDqtjjYqJdnRqAs8Eh2Rrqujwkop6WjUBabAOyIbHY3awCb8jkimo9HADOMhLGo7c1APnFUPnNJghhNtYcninxtmaHGc9lTxFzTmvNdvHZgMPhQV2drgfR3/tiI+9/ptAg9mOHE/fEsG+vQ7Hg/nONAWg+UxCwxneFnVn/ebfE6Rv3VgvXoseW8eSD8fc4nAtKrfF1Oe/z6A/y7v8hwH0v/a4JELaFHV74spz986MJU8p21/qXPIY0wyGLL4XeniLfj5Xc7LqvhvX0ZI24Ad7Df37n0MBZgXPRfTRAB/cjfiMg+afMDpfZArpbHFYdfAcpXWFi8yMV/hufjOnSljvcDpsqtLYM94K68hgWUZS9seaVF2XXQOvCr0J3Jm1+cYfPaVsfRQF60i1V60GtkCWvLvco1GgbVLJHghkX5+nOS2UdYfHDBZmKJUMdk2RKpY1h8W8NDi2M3KLUPwzPmS+7L+4IBZXKIVbRva4p2q4Q8SmKUNXmmDGb+jy/fUYCrLtip+zgPKtoPvBTgk9cBC1ANH0IiXGWElYP5Q5Su7ulamjDU4qw08nuKIP1SltpGwvh7exXXjmxDaFTh1F885/Qvt66GL6Wzb00dlgGMQ9cCRi3rgyEX7C2zCz6QazcR04JlU45nYeDOTinXcrCc0nUkFEFijQ2Zi/wEwSFQGAW+qkwAAAABJRU5ErkJggg==\" style=\"height:25px;\">";
        $PATH_BASE64 = "<img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAABKElEQVR4nO2aPU4DMRCFpwKJy1BRkJyBgoNQx48Uqfir6DgKHZlJT4UoOEBCroBoHjLaFIgFHIjkcZhPetVO4W89bjwWCYKgiMkjd5LyKimfYeRPyXUj47F4A8rLEoEPUb4m44F4AspFXtx4xkFRvfH6fWeMT5N77okX0P3l0vqTW+5C+dDJ3EirIpnRHfdhfFm7Jf8a5SIZL/K5lk2IZE6NR6u2rJDzjYnUAFMOVzvTtMi36906ERiXlfp9nSxLRJqKfCWSjIfinPGMg/9zRqQRECLOQIg4AyHiDISIMxAizkCIOAMh4gyEiDMQIs5AiDgDIeIMhIgzsP0i2k2dphxKI4OeZJx/+phncr+9Ea+VpDzrfzCQZerNA8sFjPMs0TsMDYJA+ngDFYpM7MYn2H0AAAAASUVORK5CYII=\" style=\"height:25px;\">";
        // Get the `` highlighted version instead
        $formatMsg2 = function ($msg) {
            return preg_replace('/\((See Error[^\)]+)\)/', '<span class="code-badge-error">[$1]</span>', $msg);
        };
        $formatMsg = function ($msg) {
            if (!is_string($msg)) {
                $msg = str_replace(['\/', '\\/'], '/', json_encode($msg, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES));
            }
            $escaped = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
            return preg_replace('/`([^`]+)`/', '<span class="code-badge">$1</span>', $escaped);
        };
        // Prepare and count all errors (if any)
        $tabs = ['API', 'CONFIG', 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'FILES', 'COMPILATION', 'INTERNAL'];
        $bucketed = [];
        foreach ($tabs as $t) {
            $bucketed[$t] = [
                'errors' => 0,
                'warnings' => 0
            ];
        }
        $errCount = $internalErrors['ERRORS'];

        if (!empty($internalErrors['CONFIG'])) {
            $bucketed['CONFIG']['errors'] = count($internalErrors['CONFIG']);
        }
        if (!empty($internalErrors['INTERNAL'])) {
            $bucketed['INTERNAL']['errors'] = count($internalErrors['INTERNAL']);
        }
        if (!empty($internalErrors['COMPILATION']['errors'])) {
            $bucketed['COMPILATION']['errors'] = count($internalErrors['COMPILATION']['errors']);
        }
        if (!empty($internalErrors['COMPILATION']['warnings'])) {
            $bucketed['COMPILATION']['warnings'] = count($internalErrors['COMPILATION']['warnings']);
        }
        if (!empty($internalErrors['METHODS'])) {
            foreach ($internalErrors['METHODS'] as $method => $methodData) {
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
        if (!empty($internalErrors['FILES'])) {
            foreach ($internalErrors['FILES'] as $fileType => $singleFile) {
                if (!empty($singleFile)) {
                    $bucketed['FILES']['errors'] += count($singleFile);
                }
            }
        }
        // FILES errors are also in other Tabs so remove them after adding their count to tab
        $totalErrors = 0 - $bucketed['FILES']['errors'];
        $totalWarnings =  $bucketed['COMPILATION']['warnings'] ?? 0;
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
                    font-family: 'Fredoka-Regular';
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
                    max-width: 1200px;
                    margin: 0 auto;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 1.5rem;
                    padding-bottom: 1rem;
                    border-bottom: 1px solid #21262d;
                    padding-left: 0.5rem;
                    padding-right: 0.5rem;
                }

                .title {
                    font-family: <?php echo $PHTML_FONT_SRC ? "'Fredoka-Bold', " : ""; ?>sans-serif;
                    font-size: 2rem;
                    color: rgb(162, 74, 255);
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
                    font-family: 'Fredoka-Bold';
                    font-size: 1rem;
                    letter-spacing: 0.1rem;
                    color: #ff7b72;
                    border: 1px solid rgba(248, 81, 73, 0.4);
                }

                .badge-warning {
                    background: rgba(210, 153, 34, 0.15);
                    font-family: 'Fredoka-Bold';
                    font-size: 1rem;
                    letter-spacing: 0.1rem;
                    color: #d29922;
                    border: 1px solid rgba(210, 153, 34, 0.4);
                }

                .tabs-header {
                    display: flex;
                    flex-wrap: wrap;
                    background: #161b22;
                    border-radius: 8px 8px 0 0;
                    border: 1px solid #30363d;
                    border-bottom: none;
                    overflow-x: auto;
                }

                .tab-btn {
                    padding: 0.69rem 0.9rem;
                    background: none;
                    border: none;
                    color: #8b949e;
                    font-size: 0.95rem;
                    letter-spacing: 0.1rem;
                    font-weight: 700;
                    font-family: 'Fredoka-Bold';
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
                    font-size: 1rem;
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

                .tab-group {
                    margin-bottom: 2rem;
                }

                .tab-header {
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
                    width: 100%;
                }

                .tab-header-category {
                    font-family: monospace;
                    font-size: 1.15rem;
                    font-weight: 600;
                    color: #79c0ff;
                    background: #161b22;
                    padding: 0.5rem 0.8rem;
                    border-radius: 6px;
                    border: 1px solid #21262d;
                    margin-bottom: 0.25rem;
                    display: inline-block;
                    width: 100%;
                }

                .issue-card {
                    background: #161b22;
                    border-left: 4px solid #ff7b72;
                    padding: 1rem 1.2rem;
                    margin-bottom: 0.5rem;
                    border-radius: 0 6px 6px 0;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                }

                .issue-type-with-button {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    align-items: center;
                    width: 100%;
                }

                .view-details-btn {
                    background: #21262d;
                    color: #c9d1d9;
                    border: 1px solid #30363d;
                    border-radius: 6px;
                    padding: 4px 10px;
                    font-size: 1rem;
                    font-family: 'Fredoka-Bold';
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    transition: background-color 0.15s ease, border-color 0.15s ease;
                }

                .view-details-btn:hover {
                    background: #30363d;
                    border-color: #8b949e;
                    color: #f0f6fc;
                }

                .view-details-btn .chevron {
                    display: inline-block;
                    font-size: 0.65rem;
                    transition: transform 0.2s ease;
                }

                .view-details-btn.active .chevron {
                    transform: rotate(90deg);
                }

                .issue-body {
                    margin-top: 10px;
                    padding-top: 10px;
                    border-top: 1px solid #21262d;
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

                .issue-card-warn {
                    border-left-color: #d29922;
                }

                .warn-color {
                    color: #d29922;
                }

                .issue-type {
                    font-size: 0.8rem;
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
                    font-size: 1rem;
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
                    overflow-x: scroll;
                }

                .code-badge-error {
                    color: #ff7b72;
                    padding: 0.05rem 0.15rem;
                    font-family: 'Consolas', 'Courier New', monospace;
                    font-size: 0.9rem;
                    font-weight: 700;
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
                    font-size: 0.9rem;
                    font-family: 'Consolas', 'Courier New', monospace;
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
                    <div class="title">FunkPHP - Set, Pipe, Run!</div>
                    <div class="summary-badges">
                        <span class="badge badge-danger"><?= $totalErrors ?> Error<?= $totalErrors === 1 ? '' : 's' ?></span>
                        <span class="badge badge-warning"><?= $totalWarnings ?> Warning<?= $totalWarnings === 1 ? '' : 's' ?></span>
                    </div>
                </div>
                <?php if (!empty($this->debug['ALWAYS_SHOW'])): ?>
                    <div class="alert-warning" style="display:flex; align-items:center; align-content:center; gap:0.5rem;">
                        <span class="alert-icon"><?= $WARNING_BASE64; ?></span>
                        <div class="alert-content" style="width:100%; display:inline-block; padding-bottom:0.2rem;">
                            <code>->CONFIG()->setDebug()</code> 2nd argument is <code>TRUE</code> (always show). Set it to <code>FALSE</code> to Allow Compiled Execution.
                        </div>
                    </div>
                <?php endif; ?>
                <div class="tabs-header">
                    <?php foreach ($tabs as $tab):
                        $errCnt = $bucketed[$tab]['errors'] ?? 0;
                        $warnCnt = $bucketed[$tab]['warnings'] ?? 0;
                        $totalTabItems = $errCnt + $warnCnt;
                        $hasClass = $errCnt > 0 ? 'has-errors' : '';
                        // Only show "INTERNAL" tab when having actual errors
                        if ($errCnt === 0 && $tab === 'INTERNAL') {
                            continue;
                        }
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
                            if (count(($internalErrors['CONFIG'] ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'API') {
                            if (count(($this->FunkPHPFluentAPI ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'COMPILATION') {
                            if (count($internalErrors['COMPILATION']['errors']) > 0 || count($internalErrors['COMPILATION']['warnings']) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'FILES') {
                            if (count(($internalErrors['FILES'] ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else if ($tab === 'INTERNAL') {
                            if (count(($internalErrors['INTERNAL'] ?? [])) > 0) {
                                $hasContent = true;
                            }
                        } else {
                            if (
                                count(($internalErrors['METHODS'][$tab]['CONFIG'] ?? [])) > 0
                                || count(($internalErrors['METHODS'][$tab]['ROUTES'] ?? [])) > 0
                            ) {
                                $hasContent = true;
                            }
                        }
                    ?>
                        <div id="tab-<?= $tab ?>" class="tab-panel <?= $tab === $activeTab ? 'active' : '' ?>">
                            <?php if (!$hasContent): ?>
                                <?php if ($tab === 'INTERNAL'): ?>
                                    <div class="empty-state">
                                        ✓ No Internal FunkPHP Errors/Warnings<br />Errors/Warnings showing up here strongly suggest Internal Issues with FunkPHP that you are usually recommended to report to the Official Repositories of FunkPHP.
                                    </div>
                                <?php elseif ($tab === 'COMPILATION'): ?>
                                    <div class="empty-state">
                                        ✓ No FunkPHP Compilation Errors/Warnings<br />Errors/Warnings here only first show up after No Errors/Warnings in<br /><?= $formatMsg('`API`, `CONFIG`, `GET`, `POST`, `PUT`, `DELETE`, `PATCH`'); ?>
                                    </div>
                                <?php elseif ($tab === 'FILES'): ?>
                                    <div class="empty-state">
                                        ✓ No FunkPHP File (Function|Class) Errors/Warnings<br />Errors/Warnings here are related to the Files and/or their<br /> Functions/Classes in Directories (including sub-directories):<br /><?= $formatMsg('`/src/funkphp/config/`'); ?><br /><?= $formatMsg('`/src/funkphp/pipes/`'); ?><br /><?= $formatMsg('`/src/funkphp/data/`'); ?><br /><?= $formatMsg('`/src/funkphp/pages/`'); ?>
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
                                    $CONFIG_ERRS = $internalErrors['CONFIG'] ?? [];
                                ?> <div class="tab-group">
                                        <div class="tab-header" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $CONFIG_BASE64; ?> GLOBAL CONFIG | $APP->CONFIG() <?= $PATH_BASE64; ?> /src/funkphp/app/CONFIG.php</div>
                                        <?php foreach ($CONFIG_ERRS as $idx => $C_ERR) {
                                        ?>
                                            <div class="issue-card">
                                                <div class="issue-type-with-button">
                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($C_ERR['errShort'] ?? '<Error Title Missing>') ?>
                                                    </div>
                                                    <button type="button" class="view-details-btn">
                                                        Details <span class="chevron">▶</span>
                                                    </button>
                                                </div>
                                                <div style="display:none;" class="issue-body"><?= $formatMsg($C_ERR['err'] ?? '<Error Missing>') ?></div>
                                            </div>
                                        <?php
                                        } ?>
                                    </div>
                                <?php
                                }
                                // TAB IS "FILES"?
                                else if ($tab === 'FILES') {
                                    $FILE_TYPES_PATHS = [
                                        'user-functions' => 'User-defined Functions | /src/funkphp/config',
                                        'user-classes' => 'User-defined Classes | /src/funkphp/config',
                                        'user-tables' => 'Tables | /src/funkphp/config/tables.php',
                                        'core-manifest' => 'Core Manifest | /src/funkphp/core/manifest.php',
                                        'core-functions' => 'Core Functions | /src/funkphp/core/functions.php',
                                        'pages-layouts' => 'Page Layouts | /src/funkphp/pages/layouts',
                                        'pages-partials' => 'Page Partials | /src/funkphp/pages/partials',
                                        'pages-components' => 'Page Components | /src/funkphp/pages/components',
                                        'pages-uncompiled' => 'Page Uncompiled | /src/funkphp/pages',
                                        'pages-compiled' => 'Page Compiled | /src/funkphp/pages/compiled',
                                        'request' => 'Request Pipe Functions | /src/funkphp/pipes/request',
                                        'post-response' => 'Post-Response Pipe Functions | /src/funkphp/pipes/post_response',
                                        'middleware' => 'Middleware Pipe Functions | /src/funkphp/pipes/middlewares',
                                        'route' => 'Route Pipe Functions | /src/funkphp/pipes/routes',
                                        'data-query-compiled' => 'Data Query Compiled | /src/funkphp/data/compiled/query.php',
                                        'data-sql-compiled' => 'Data SQL Compiled | /src/funkphp/data/compiled/sql.php',
                                        'data-validation-compiled' => 'Data Validation Compiled | /src/funkphp/data/compiled/validation.php',
                                        'data-query-uncompiled' => 'Data Query Uncompiled | /src/funkphp/data/query',
                                        'data-sql-uncompiled' => 'Data SQL Uncompiled | /src/funkphp/data/sql',
                                        'data-validation-uncompiled' => 'Data Validation Uncompiled | /src/funkphp/data/validation'
                                    ];
                                    $FILES_ERRS = $internalErrors['FILES'] ?? [];

                                ?> <div class="tab-group">
                                        <?php foreach ($FILES_ERRS as $fileType => $singleFile) {
                                        ?><div class="tab-header-category"><?= $FILE_TYPES_PATHS[$fileType]; ?></div>
                                            <?php // SHOW for "User Defined Functions"
                                            if ($fileType === 'user-functions') {
                                                foreach ($singleFile['functions.php'] as $fName => $F_ERR) {
                                            ?><div class="tab-header" style="margin-top:0.8rem; display:flex; align-items:center; align-content:center; gap:0.5rem;">&fnof; <?= $fName; ?> | <?= $PATH_BASE64; ?> /src/funkphp/config/functions.php</div>
                                                    <?php foreach ($F_ERR as $idx => $F_ERR2) { ?>
                                                        <div class="issue-card">
                                                            <div class="issue-type-with-button">
                                                                <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($F_ERR2['errShort'] ?? '<Error Title Missing>') ?>
                                                                </div>
                                                                <button type="button" class="view-details-btn">
                                                                    Details <span class="chevron">▶</span>
                                                                </button>
                                                            </div>
                                                            <div style="display:none; font-weight:normal;" class="issue-body"><?= $formatMsg($F_ERR2['err'] ?? '<Error Missing>') ?></div>
                                                        </div> <?php
                                                            }
                                                        }
                                                    }  // SHOW for "User Defined Classes"
                                                    else if ($fileType === 'user-classes') {
                                                        foreach ($singleFile['classes.php'] as $cName => $C_ERR) {
                                                            $cName = "<Unknown Class Name>";
                                                            if (count($C_ERR) > 0) {
                                                                $cName = $C_ERR[1]['exact_name'] ?? "<Failed to Retrieve Class Name>";
                                                            }
                                                                ?><div class="tab-header" style="margin-top:0.8rem; display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $OOP_BASE64; ?> <?= $formatMsg($cName); ?> | <?= $PATH_BASE64; ?> /src/funkphp/config/classes.php</div>
                                                    <?php foreach ($C_ERR as $idx => $C_ERR2) { ?>
                                                        <div class="issue-card">
                                                            <div class="issue-type-with-button">
                                                                <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($C_ERR2['errShort'] ?? '<Error Title Missing>') ?>
                                                                </div>
                                                                <button type="button" class="view-details-btn">
                                                                    Details <span class="chevron">▶</span>
                                                                </button>
                                                            </div>
                                                            <div style="display:none; font-weight:normal;" class="issue-body"><?= $formatMsg($C_ERR2['err'] ?? '<Error Missing>') ?></div>
                                                        </div> <?php
                                                            }
                                                        }
                                                    }
                                                    // SHOW for "Request Pipe Functions" (/src/funphp/pipes/response)
                                                    else if ($fileType === 'request') {
                                                        foreach ($singleFile as $fileName => $functions) {
                                                            foreach ($functions as $fnName => $errors) {
                                                                ?>
                                                        <div class="tab-header" style="margin-top:0.8rem; display:flex; align-items:center; align-content:center; gap:0.5rem;">
                                                            &fnof; <?= $fnName; ?> | <?= $PATH_BASE64; ?> /src/funkphp/pipes/request/<?= $fileName; ?>
                                                        </div>
                                                        <?php foreach ($errors as $idx => $errData) { ?>
                                                            <div class="issue-card">
                                                                <div class="issue-type-with-button">
                                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($errData['errShort'] ?? '<Error Title Missing>') ?>
                                                                    </div>
                                                                    <button type="button" class="view-details-btn">
                                                                        Details <span class="chevron">▶</span>
                                                                    </button>
                                                                </div>
                                                                <div style="display:none; font-weight:normal;" class="issue-body">
                                                                    <?= $formatMsg($errData['err'] ?? '<Error Missing>') ?>
                                                                </div>
                                                            </div>
                                                        <?php }
                                                            }
                                                        }
                                                    }
                                                    // SHOW for "Request Pipe Functions" (/src/funphp/pipes/post_response)
                                                    else if ($fileType === 'post-response') {
                                                        foreach ($singleFile as $fileName => $functions) {
                                                            foreach ($functions as $fnName => $errors) {
                                                        ?>
                                                        <div class="tab-header" style="margin-top:0.8rem; display:flex; align-items:center; align-content:center; gap:0.5rem;">
                                                            &fnof; <?= $fnName; ?> | <?= $PATH_BASE64; ?> /src/funkphp/pipes/post_response/<?= $fileName; ?>
                                                        </div>
                                                        <?php foreach ($errors as $idx => $errData) { ?>
                                                            <div class="issue-card">
                                                                <div class="issue-type-with-button">
                                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($errData['errShort'] ?? '<Error Title Missing>') ?>
                                                                    </div>
                                                                    <button type="button" class="view-details-btn">
                                                                        Details <span class="chevron">▶</span>
                                                                    </button>
                                                                </div>
                                                                <div style="display:none; font-weight:normal;" class="issue-body">
                                                                    <?= $formatMsg($errData['err'] ?? '<Error Missing>') ?>
                                                                </div>
                                                            </div>
                                                        <?php }
                                                            }
                                                        }
                                                    }
                                                    // SHOW for "Middlewares Pipe Functions" (/src/funphp/pipes/middlewares)
                                                    else if ($fileType === 'middleware') {
                                                        foreach ($singleFile as $fileName => $functions) {
                                                            foreach ($functions as $fnName => $errors) {
                                                        ?>
                                                        <div class="tab-header" style="margin-top:0.8rem; display:flex; align-items:center; align-content:center; gap:0.5rem;">
                                                            &fnof; <?= $fnName; ?> | <?= $PATH_BASE64; ?> /src/funkphp/pipes/middlewares/<?= $fileName; ?>
                                                        </div>
                                                        <?php foreach ($errors as $idx => $errData) { ?>
                                                            <div class="issue-card">
                                                                <div class="issue-type-with-button">
                                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($errData['errShort'] ?? '<Error Title Missing>') ?>
                                                                    </div>
                                                                    <button type="button" class="view-details-btn">
                                                                        Details <span class="chevron">▶</span>
                                                                    </button>
                                                                </div>
                                                                <div style="display:none;" class="issue-body">
                                                                    <?= $formatMsg($errData['err'] ?? '<Error Missing>') ?>
                                                                </div>
                                                            </div>
                                                        <?php }
                                                            }
                                                        }
                                                    }
                                                    // SHOW for "Route Pipe Functions" (/src/funphp/pipes/routes)
                                                    else if ($fileType === 'route') {
                                                        foreach ($singleFile as $fileName => $functions) {
                                                            foreach ($functions as $fnName => $errors) {
                                                        ?>
                                                        <div class="tab-header" style="margin-top:0.8rem; display:flex; align-items:center; align-content:center; gap:0.5rem;">
                                                            &fnof; <?= $fnName; ?> | <?= $PATH_BASE64; ?> /src/funkphp/pipes/routes/<?= $fileName; ?>
                                                        </div>
                                                        <?php foreach ($errors as $idx => $errData) { ?>
                                                            <div class="issue-card">
                                                                <div class="issue-type-with-button">
                                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($errData['errShort'] ?? '<Error Title Missing>') ?>
                                                                    </div>
                                                                    <button type="button" class="view-details-btn">
                                                                        Details <span class="chevron">▶</span>
                                                                    </button>
                                                                </div>
                                                                <div style="display:none;" class="issue-body">
                                                                    <?= $formatMsg($errData['err'] ?? '<Error Missing>') ?>
                                                                </div>
                                                            </div>
                                            <?php }
                                                            }
                                                        }
                                                    }
                                            ?>
                                            <hr style="margin-bottom:1.5rem; margin-top:1rem; border-top:1px solid #21262d;" />

                                        <?php
                                        } ?>

                                    </div>
                                <?php
                                }
                                // TAB IS "INTERNAL"?
                                else if ($tab === 'INTERNAL') {
                                    $INTERNAL_ERRS = $internalErrors['INTERNAL'] ?? [];
                                ?> <div class="tab-group">
                                        <div class="tab-header">Internal FunkPHP Errors (applies to all files in /src/funkphp/app)</div>
                                        <?php foreach ($INTERNAL_ERRS as $idx => $I_ERR) {
                                        ?>
                                            <div class="issue-card">
                                                <div class="issue-type-with-button">
                                                    <div class="issue-type">ERROR #<?= $idx ?>: <?= $formatMsg($I_ERR['errShort'] ?? '<Error Title Missing>') ?>
                                                    </div>
                                                    <button type="button" class="view-details-btn">
                                                        Details <span class="chevron">▶</span>
                                                    </button>
                                                </div>
                                                <div style="display:none;" class="issue-body"><?= $formatMsg($I_ERR['err'] ?? '<Error Missing>') ?></div>
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
                                    <div class="tab-group">
                                        <div class="tab-header" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $API_TREE_BASE64; ?>FunkPHP Fluent API (relevant files <?= $PATH_BASE64; ?> /src/funkphp/app)</div>
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
                                                } elseif (str_starts_with($upper, '->ROUTE(') || str_starts_with($upper, '->ROUTEP')) {
                                                    $currentDepth = 2;
                                                    $lineDepth = 2;
                                                } else {
                                                    $lineDepth = $currentDepth + 1;
                                                }
                                                $paddingPx = $lineDepth * 24;
                                            ?>
                                                <div data-api-row="<?= $idx; ?>" class="api-tree-line" style="padding-left: <?= $paddingPx ?>px;">
                                                    <span class="code-badge<?= ($upper === '->ROUTES()' || $upper === '->CONFIG()') ? '' : (preg_match('/^->(GET|POST|PUT|DELETE|PATCH)\(\)$/', $upper) ? ' badge-method' : '') ?>">
                                                        <?= $formatMsg2($formatMsg($trimmed)); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="tab-group">
                                        <div class="tab-header" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $COMPILED_BASE64; ?>FunkPHP Compiled API (relevant files <?= $PATH_BASE64; ?> /src/funkphp/app)</div>
                                        <div class="api-card api-card-consolidated" style="padding:1px;">
                                            <?= dd($compiled, 'Either RUNS/OUTPUTS as FunkPHPDeployment.php OR You Parse with Custom HTTPS Kernel Function!', false); ?>
                                        </div>
                                    </div>
                                <?php
                                }
                                // TAB IS "COMPILATION"?
                                else if ($tab === 'COMPILATION') {
                                    $COMPILE_ERRS = $internalErrors['COMPILATION']['errors'] ?? [];
                                    $COMPILE_WARNS = $internalErrors['COMPILATION']['warnings'] ?? [];
                                ?> <?php if (count($COMPILE_ERRS) > 0): ?>
                                        <div class="tab-group">
                                            <div class="tab-header">FunkPHP Compilation Errors (happens only if Zero Errors otherwise in all files in /src/funkphp/app)</div>
                                            <?php foreach ($COMPILE_ERRS as $idx => $COMP_ERR) {
                                            ?>
                                                <div class="issue-card">
                                                    <div class="issue-type-with-button">
                                                        <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($COMP_ERR['errShort'] ?? '<Error Title Missing>') ?>
                                                        </div>
                                                        <button type="button" class="view-details-btn">
                                                            Details <span class="chevron">▶</span>
                                                        </button>
                                                    </div>
                                                    <div style="display:none;" class="issue-body"><?= $formatMsg($COMP_ERR['err'] ?? '<Error Missing>') ?></div>
                                                </div>
                                            <?php
                                            } ?>
                                        </div>
                                    <?php endif ?>
                                    <?php if (count($COMPILE_WARNS) > 0): ?>
                                        <div class="tab-group">
                                            <div class="tab-header">FunkPHP Compilation Warnings (happens only if Zero Errors otherwise in all files in /src/funkphp/app)</div>
                                            <?php foreach ($COMPILE_WARNS as $idx2 => $COMP_WARN) {
                                            ?>
                                                <div class="issue-card issue-card-warn">
                                                    <div class="issue-type-with-button">
                                                        <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem; color:#e3b341;"><?= $WARNING_BASE64; ?> WARNING #<?= $idx2 ?>: <?= $formatMsg($COMP_WARN['warnShort'] ?? '<Warning Title Missing>') ?>
                                                        </div>
                                                        <button type="button" class="view-details-btn">
                                                            Details <span class="chevron">▶</span>
                                                        </button>
                                                    </div>
                                                    <div style="display:none;" class="issue-body"><?= $formatMsg($COMP_WARN['warn'] ?? '<Warning Missing>') ?></div>
                                                </div>
                                            <?php
                                            } ?>
                                        </div>
                                    <?php endif ?>
                                    <?php
                                }
                                // TAB IS <METHOD>?
                                else {
                                    $R_CONFIG_ERRS = $internalErrors['METHODS'][$tab]['CONFIG'] ?? [];
                                    $R_ROUTES_ERRS = $internalErrors['METHODS'][$tab]['ROUTES'] ?? [];
                                    ?><?php if (count($R_CONFIG_ERRS) > 0) {  ?>
                                    <div class="tab-group">
                                        <div class="tab-header" style="display:flex; align-items:center; align-content:center; gap:0.5rem;">
                                            <?= $CONFIG_BASE64; ?> <?= $tab ?> CONFIG | $APP->ROUTES()-><?= $tab ?>() <?= $PATH_BASE64; ?> /src/funkphp/app/<?= $tab ?>.php</div>
                                        <?php foreach ($R_CONFIG_ERRS as $idx => $RC_ERR) {
                                        ?>
                                            <div class=" issue-card">
                                                <div class="issue-type-with-button">
                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $idx ?>: <?= $formatMsg($RC_ERR['errShort'] ?? '<Error Title Missing>') ?>
                                                    </div>
                                                    <button type="button" class="view-details-btn">
                                                        Details <span class="chevron">▶</span>
                                                    </button>
                                                </div>
                                                <div style="display:none;" class="issue-body"><?= $formatMsg($RC_ERR['err'] ?? '<Error Missing>') ?></div>
                                            </div> <?php
                                                }
                                                    ?>
                                    </div>
                                <?php } ?>
                                <div class="tab-group">
                                    <?php foreach ($R_ROUTES_ERRS as $singleMethodRoute => $singleMethodRouteDetails) {
                                    ?>
                                        <div class="tab-header" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ROUTE_BASE64; ?><?= "$tab$singleMethodRoute"; ?> | $APP->ROUTES()-><?= $tab ?>()->ROUTE('<?= $singleMethodRoute; ?>') <?= $PATH_BASE64; ?> /src/funkphp/app/<?= $tab ?>.php</div>
                                        <?php foreach ($singleMethodRouteDetails as $rErrIdx => $rErr) {
                                        ?> <div class="issue-card">
                                                <div class="issue-type-with-button">
                                                    <div class="issue-type" style="display:flex; align-items:center; align-content:center; gap:0.5rem;"><?= $ERROR_BASE64; ?> ERROR #<?= $rErrIdx ?>: <?= $formatMsg($rErr['errShort'] ?? '<Error Title Missing>') ?>
                                                    </div>
                                                    <button type="button" class="view-details-btn">
                                                        Details <span class="chevron">▶</span>
                                                    </button>
                                                </div>
                                                <div style="display:none;" class="issue-body"><?= $formatMsg($rErr['err'] ?? '<Error Missing>') ?></div>
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
                document.addEventListener('click', viewDetails);

                function viewDetails(evt) {
                    const btn = evt.target.closest('.view-details-btn');
                    if (!btn) return;
                    const issueCard = btn.closest('.issue-card');
                    if (!issueCard) return;
                    const issueBody = issueCard.querySelector('.issue-body');
                    if (!issueBody) return;
                    const isHidden = issueBody.style.display === 'none' || getComputedStyle(issueBody).display === 'none';
                    if (isHidden) {
                        issueBody.style.display = 'block';
                        btn.classList.add('active');
                    } else {
                        issueBody.style.display = 'none';
                        btn.classList.remove('active');
                    }
                }
            </script>
        </body>

        </html>
<?php
        exit;
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
            $this->output_errors($this->errors, $this->compiled);
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
            $this->compile_setErr("Invalid PHP Code Syntax", "File Function Error in `{$PATH_USER_DEFINED_FNS} while Compiling FunkPHP Configuration`: File contains Invalid PHP Syntax: '`{$this->cached['file_user_defined_functions']['syntax_error']}`' that needs to be resolved.");
        } else if (
            isset($this->cached['file_user_defined_functions']['functions'])
            && count($this->cached['file_user_defined_functions']['functions']) > 0
        ) {
            foreach ($this->cached['file_user_defined_functions']['functions'] as $userFN => $_) {
                $fatalError = $this->validateFNFile($this->cached['file_user_defined_functions'], $userFN, " `while Compiling FunkPHP Configuration`", "");
                if ($fatalError !== null) {
                    $this->compile_setErr("Invalid User-defined File Function (also see FILES tab)", $fatalError . " If you wanna keep the Function but not use it for this Compilation, comment it out inside of the `{$PATH_USER_DEFINED_FNS}` File and retry.");
                }
            }
        }
        if (!$this->cached['file_user_defined_classes']['syntax_valid']) {
            $this->compile_setErr("Invalid User-defined Class File", "File Class Error in `{$PATH_USER_DEFINED_FNS} while Compiling FunkPHP Configuration`: File contains Invalid PHP Syntax: '`{$this->cached['file_user_defined_classes']['syntax_error']}`' that needs to be resolved.");
        } else if (
            isset($this->cached['file_user_defined_classes']['classes'])
            && count($this->cached['file_user_defined_classes']['classes']) > 0
        ) {
            foreach ($this->cached['file_user_defined_classes']['classes'] as $userClass => $_) {
                $fatalError = $this->validateCLASSFile($this->cached['file_user_defined_classes'], $userClass, " `while Compiling FunkPHP Configuration`", "");
                if ($fatalError !== null) {
                    $this->compile_setErr("Invalid User-defined Class File (also see FILES tab)", $fatalError . " If you wanna keep the Class but not use it for this Compilation, comment it out inside of the `{$PATH_CLASSES}` File and retry.");
                }
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 2: First add to the $compiled->c variable that can be added right away
        // ------------------------------------------------------------------------------------------
        // 3 BOOLEANS
        if (isset($this->validBatches['config']['FUNKPHP_ONLINE'])) {
            $this->compiled['config']['runtime']['online'] = $this->validBatches['config']['FUNKPHP_ONLINE'];
        }
        if (isset($this->validBatches['config']['USE_VENDOR'])) {
            $this->compiled['config']['runtime']['use_vendor'] = $this->validBatches['config']['USE_VENDOR'];
        }
        if (isset($this->validBatches['config']['USE_HTTPS'])) {
            $this->compiled['config']['runtime']['use_https'] = $this->validBatches['config']['USE_HTTPS'];
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
            $this->compiled['config']['runtime']['ini_sets'] = $this->validBatches['config']['setINI_SET'];
        }
        // ------------------------------------------------------------------------------------------
        // STEP 3: Check Global Handlers set and then all setGroup<VARIANTS> since they can refer to
        // either non-existing function+files AND/OR to set Global Handlers which would conflict.
        // STEP 3.1 - Global Handlers
        // ------------------------------------------------------------------------------------------
        if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
            $this->compiled['config']['runtime']['custom_https_kernel'] = $this->validBatches['config']['DEFAULT_HTTPS_KERNEL'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_HTTPS_KERNEL']] = "User-defined Default HTTPS Kernel Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'])) {
            $this->compiled['config']['runtime']['custom_exception_handler'] = $this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_EXCEPTION_HANDLER']] = "User-defined Default Exception Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_ERROR_HANDLER'])) {
            $this->compiled['config']['runtime']['custom_error_handler'] = $this->validBatches['config']['DEFAULT_ERROR_HANDLER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_ERROR_HANDLER']] = "User-defined Default Error Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_URI_NORMALIZER'])) {
            $this->compiled['config']['runtime']['custom_uri_normalizer'] = $this->validBatches['config']['DEFAULT_URI_NORMALIZER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_URI_NORMALIZER']] = "User-defined Default URI Normalizer Handler";
        }
        if (isset($this->validBatches['config']['DEFAULT_IP_RESOLVER'])) {
            $this->compiled['config']['runtime']['custom_ip_resolver'] = $this->validBatches['config']['DEFAULT_IP_RESOLVER'];
            $GLOBAL_HANDLERS[$this->validBatches['config']['DEFAULT_IP_RESOLVER']] = "User-defined Default IP Resolver Handler";
        }
        // -----------------------------------------------------------------------------
        // STEP 3.2 - Grouped<VARIANTS>
        // -----------------------------------------------------------------------------
        if (isset($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'])) {
            foreach ($this->validBatches['config']['GROUPED_PIPE_USER_DEFINED'] as $GROUPED_UD_NAME => $GROUPED_UD_VALS) {
                $validGroup = true;
                foreach ($GROUPED_UD_VALS as $UD_FN) {
                    if (isset($GLOBAL_HANDLERS[$UD_FN])) {
                        $this->compile_setErr("Conflicting User-defined Functions", "Grouped-configured User-defined Function `{$UD_FN}` in `{$PATH_USER_DEFINED_FNS}` in `->setGroupPipeUserdefined('{$GROUPED_UD_NAME}')` conflicts with already defined Global Handler Role `{$GLOBAL_HANDLERS[$UD_FN]}.` Remove `{$UD_FN}` from the `->setGroupPipeUserdefined()` OR from the `Global Handler Role`.");
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
                $this->compile_setWarn("Missing Default Session Cookie Driver", "No Default `Session Cookie Driver` set (with `'->CONFIG()->setSessionDriver()'`) - using default: `'files'`.");
            } else {
                $this->compiled['c']['SESSION']['driver'] = $this->validBatches['config']['SESSION']['driver'];
            }
            // UD or default Session Name?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_NAME'] = "fphp_id";
                $this->compile_setWarn("Missing Default Session Cookie Name", "No Default `Session Cookie Name` set (with `'->CONFIG()->setSessionCookieName()'`) - using default: `'fphp_id'`.");
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_NAME'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_NAME'];
            }
            // UD or default Session Domain?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_DOMAIN'] = "funkphp";
                $this->compile_setWarn("Missing Default Session Cookie Domain", "No Default `Session Cookie Domain` set (with `'->CONFIG()->setSessionCookieDomain()'`) - using default: `'funkphp'`.");
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_DOMAIN'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_DOMAIN'];
            }
            // UD or default Session Path?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_PATH'] = "/";
                $this->compile_setWarn("Missing Default Session Cookie Path", "No Default `Session Cookie Domain` set (with `'->CONFIG()->setSessionCookiePath()'`) - using default: `'/'`.");
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_PATH'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_PATH'];
            }
            // UD or default Session Lifetime?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_LIFETIME'] = 28800;
                $this->compile_setWarn("Missing Default Session Cookie Lifetime", "No Default `Session Cookie Lifetime` set (with `'->CONFIG()->setSessionCookieLifetime()'`) - using default: `'28800'`.");
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_LIFETIME'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_LIFETIME'];
            }
            // UD or default Session Lifetime?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SAMESITE'] = 'Lax';
                $this->compile_setWarn("Missing Default Session Cookie SameSite", "No Default `Session Cookie Samesite` set (with `'->CONFIG()->setSessionCookieSameSite()'`) - using default: `'Lax'`.");
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SAMESITE'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SAMESITE'];
            }
            // UD or default Session SECURE?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'])) {
                $what = '';
                if (
                    isset($this->compiled['config']['runtime']['use_https']) &&
                    $this->compiled['config']['runtime']['use_https'] === true
                ) {
                    $this->compiled['c']['SESSION']['COOKIES']['SESSION_SECURE'] = true;
                    $what = 'true';
                } else {
                    $this->compiled['c']['SESSION']['COOKIES']['SESSION_SECURE'] = false;
                    $what = 'false';
                }
                $this->compile_setWarn("Missing Default Session Cookie Secure", "No Default `Session Cookie Secure` set (with `'->CONFIG()->setSessionCookieSecure()'`) - using default: `'{$what}'`.");
            } else {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_SECURE'] = $this->validBatches['config']['SESSION']['COOKIES']['SESSION_SECURE'];
            }
            // UD or default Session HTTP?
            if (!isset($this->validBatches['config']['SESSION']['COOKIES']['SESSION_HTTPONLY'])) {
                $this->compiled['c']['SESSION']['COOKIES']['SESSION_HTTPONLY'] = true;
                $this->compile_setWarn("Missing Default Session Cookie HTTPOnly", "No Default `Session Cookie HttpOnly` set (with `'->CONFIG()->setSessionCookieHTTPOnly()'`) - using default: `'true'`.");
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
                $this->compile_setErr("Conflicting User-defined Functions", "User-defined Function `{$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']}` in `{$PATH_USER_DEFINED_FNS}` in `->CONFIG()->setNoRouteMatchCallback('{$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']}')` conflicts with already defined `Global Handler Role {$GLOBAL_HANDLERS[$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']]}`. Remove `{$this->validBatches['config']['NO_ROUTE_MATCH']['CALLBACK']}` from `->CONFIG()->setNoRouteMatchCallback()` OR from the `Global Handler Role`.");
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
                        "Conflicting User-defined Functions",
                        "User-defined Function `{$callbackFn}` in `{$PATH_USER_DEFINED_FNS}` in `->ROUTES()->{$method}()->setNoRouteMatchCallback('{$callbackFn}')` conflicts with already defined `Global Handler Role {$role}`. Remove `{$callbackFn}` from `->ROUTES()->{$method}()->setNoRouteMatchCallback()` OR from the `Global Handler Role`.",
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
                $this->compile_setWarn("No Request Pipes used with FunkPHP HTTPS Kernel", "No Request Pipes (via `->pipeRequestFunction() in ->CONFIG()` found. If intended to use No Request Pipes, just ignore this warning. This means that only Global-based Middlewares, then Route-matching, then Method-based Middleware and finally Route-based Middleware and its remaining Pipe Functions will run.");
            } else {
                $this->compile_setWarn("No request Pipes used without FunkPHP HTTPS Kernel", "No Request Pipes (via `->pipeRequestFunction() in ->CONFIG()` found. If intended to use No Request Pipes, just ignore this warning. The `User-defined Custom Default HTTPS Kernel Handler` is configured for use meaning that after Successful Compilation it will have access to `All Compiled Data`, `Trie-based Routes with Metadata` and then it is `all up to that User-defined Function to handle everything` from Route-matching to executing each Route-associated Pipe Function(s).");
            }
        }
        // request pipes exist
        else {
            if (isset($this->validBatches['config']['DEFAULT_HTTPS_KERNEL'])) {
                $this->compile_setWarn("Request Pipes with User-defined HTTPS Kernel", "Request Pipes (via `->pipeRequestFunction() in ->CONFIG()` and the `User-defined Custom Default HTTPS Kernel Handler` detected. This means that that after Successful Compilation it will have access to Trie-based Routes with Metadata and then it is `all up to that User-defined Function to handle everything AFTER Request Pipe Functions first have ran`; everything from Route-matching to executing each Route-associated Pipe Function(s).");
            }
            // VALIDATE "group:" Variants and then ADD REQUEST PIPES
            $allPipes = [];
            foreach ($this->validBatches['config']['request'] as $pipe) {
                if (!str_starts_with($pipe, 'group:')) {
                    if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $pipe) {
                        $this->compile_setWarn("Consecutive `CONFIG() Request` Function `{$pipe}`", "Consecutive `CONFIG() Request Function {$pipe}` found. Ignore this warning if it is intentional OR Review `->CONFIG()->pipeRequestFunction()` in `/src/funkphp/app/CONFIG.php`.");
                    }
                    $allPipes[] = $pipe;
                    continue;
                }
                if (!isset($GLOBAL_GROUPED['REQUEST'][$pipe])) {
                    $this->compile_setErr("Missing `CONFIG() Request` Function Group", "Grouped `CONFIG()` Request Functions with the name `{$pipe}` does not exist but was still part of the `->CONFIG()->pipeRequestFunction('{$pipe}')` in `/src/funkphp/app/CONFIG.php`. Use `->setGroupPipeRequest('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.");
                } else {
                    foreach ($GLOBAL_GROUPED['REQUEST'][$pipe] as $groupPipe) {
                        if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                            $this->compile_setWarn("Consecutive `CONFIG() Request` Function `{$groupPipe}`", "Consecutive `CONFIG() Request Function {$groupPipe}` found. Ignore this warning if it is intentional OR Review `->CONFIG()->pipeRequestFunction()` in `/src/funkphp/app/CONFIG.php`.");
                        }
                        $allPipes[] = $groupPipe;
                    }
                }
            }
            // Add by resolving their run + file paths
            foreach ($allPipes as $pipe) {
                $resolved = $this->compile_resolve_fn_paths('request', $pipe);
                $this->compiled['config']['pipes']['request-resolved'][] = ['run' => $resolved[0], 'path' => $resolved[1]];
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
                        $this->compile_setWarn("Consecutive `CONFIG() Middleware` Function `{$pipe}`", "Consecutive `CONFIG() Middleware Function {$pipe}` found. Ignore this warning if it is intentional OR Review `->CONFIG()->pipeMiddleware()` in `/src/funkphp/app/CONFIG.php`.");
                    }
                    $allPipes[] = $pipe;
                    continue;
                }
                if (!isset($GLOBAL_GROUPED['MIDDLEWARES'][$pipe])) {
                    $this->compile_setErr("Missing `CONFIG() Middleware` Function Group", "Grouped `CONFIG()` Middleware Functions with the name `{$pipe}` does not exist but was still part of the `->CONFIG()->pipeMiddleware('{$pipe}')` in `/src/funkphp/app/CONFIG.php`. Use `->setGroupPipeMiddlewares('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.");
                } else {
                    foreach ($GLOBAL_GROUPED['MIDDLEWARES'][$pipe] as $groupPipe) {
                        if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                            $this->compile_setWarn("Consecutive `CONFIG() Middleware` Function `{$groupPipe}`", "Consecutive `CONFIG() Middleware Function {$groupPipe}` found. Ignore this warning if it is intentional OR Review `->CONFIG()->pipeMiddleware()` in `/src/funkphp/app/CONFIG.php`.");
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
            foreach ($allPipes as $pipe) {
                $resolved = $this->compile_resolve_fn_paths('middleware', $pipe);
                $this->compiled['config']['pipes']['middlewares-resolved'][] = ['run' => $resolved[0], 'path' => $resolved[1]];
            }
            $this->compiled['config']['pipes']['middlewares'] =  $allPipes;
        }
        // ------------------------------------------------------------------------------------------
        // 8.3 Post-Response Pipes
        // ------------------------------------------------------------------------------------------
        if (!isset($this->validBatches['config']['post_response'])) {
            $this->compile_setWarn("No Post-Response File Functions used", "No Post-Response Pipes (via `->pipePostResponseFunction() in ->CONFIG()` found. If intended to use No Post-Response Pipes, just ignore this warning. This means that after each HTTP(S) Request that completes (or via `exit()`), nothing else happens. `Piped Post-Response Functions` are otherwise executed via the in-built PHP Function `register_shutdown_function()` in the ordered they have been added/piped. This is also why you will get a Fatal Compiling Error if you try to use the `register_shutdown_function()` inside any of your Function Files.");
        }
        // post_response pipes exist
        else {
            // VALIDATE "group:" Variants and then ADD POST-RESPONSE PIPES
            // VALIDATE "group:" Variants and then ADD REQUEST PIPES
            $allPipes = [];
            foreach ($this->validBatches['config']['post_response'] as $pipe) {
                if (!str_starts_with($pipe, 'group:')) {
                    if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $pipe) {
                        $this->compile_setWarn("Consecutive `CONFIG() Post-Response` Function `{$pipe}`", "Consecutive `CONFIG() Post-Response Function {$pipe}` found. Ignore this warning if it is intentional OR Review `->CONFIG()->pipePostResponseFunction()` in `/src/funkphp/app/CONFIG.php`.");
                    }
                    $allPipes[] = $pipe;
                    continue;
                }
                if (!isset($GLOBAL_GROUPED['POST_RESPONSE'][$pipe])) {
                    $this->compile_setErr("Missing `CONFIG() Post-Response` Function Group", "Grouped `CONFIG()` Post-Response Functions with the name `{$pipe}` does not exist but was still part of the `->CONFIG()->pipePostResponseFunction('{$pipe}')` in `/src/funkphp/app/CONFIG.php`. Use `->setGroupPipePostResponse('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.");
                } else {
                    foreach ($GLOBAL_GROUPED['POST_RESPONSE'][$pipe] as $groupPipe) {
                        if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                            $this->compile_setWarn("Consecutive `CONFIG() Post-Response` Function `{$groupPipe}`", "Consecutive `CONFIG() Post-Response Function {$groupPipe}` found. Ignore this warning if it is intentional OR Review `->CONFIG()->pipePostResponseFunction()` in `/src/funkphp/app/CONFIG.php`.");
                        }
                        $allPipes[] = $groupPipe;
                    }
                }
            }
            foreach ($allPipes as $pipe) {
                $resolved = $this->compile_resolve_fn_paths('post_response', $pipe);
                $this->compiled['config']['pipes']['post_response-resolved'][] = ['run' => $resolved[0], 'path' => $resolved[1]];
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
                                $this->compile_setWarn("Consecutive `{$method} Middleware` Function `{$pipe}`", "Consecutive `{$method}` Middleware Function `{$pipe}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                            }
                            $allPipes[] = $pipe;
                            continue;
                        }
                        if (!isset($GLOBAL_GROUPED['MIDDLEWARES'][$pipe])) {
                            $this->compile_setErr("Missing `{$method} Middleware` Function Group", "Grouped Middleware {$method} Functions with the name `{$pipe}` does not exist but was still part of the `->ROUTES()->{$method}()->pipeMiddleware('{$pipe}')` in `/src/funkphp/app/{$method}.php`. Use `->setGroupPipeMiddlewares('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.");
                        } else {
                            foreach ($GLOBAL_GROUPED['MIDDLEWARES'][$pipe] as $groupPipe) {
                                if (count($allPipes) > 0 && $allPipes[count($allPipes) - 1] === $groupPipe) {
                                    $this->compile_setWarn("Consecutive `{$method} Middleware` Function `{$groupPipe}`", "Consecutive `{$method}` Middleware Function `{$groupPipe}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
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
                            $this->compile_setWarn("Consecutive Middleware Function `{$lastConfigMW}` between `CONFIG()`<->`{$method}`", "Consecutive Pipe Middleware Function `{$allPipes[0]}` found. It runs as `Last Middleware Globally/CONFIG()` and then it runs as the `First {$method} Middleware` for any Matched Route in `{$method}`. Ignore this warning if it is intentional or Review: `->CONFIG()->pipeMiddleware('{$lastConfigMW}')` in `/src/funkphp/app/CONFIG.php` AND `->ROUTES()->{$method}()->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                        }
                    }
                    $this->compiled['methods'][$method]['middlewares'] = $allPipes;
                    foreach ($allPipes as $pipe) {
                        $resolved = $this->compile_resolve_fn_paths('middleware', $pipe);
                        $this->compiled['methods'][$method]['middlewares-resolved'][] = ['run' => $resolved[0], 'path' => $resolved[1]];
                    }
                }
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 10: Build `params` for GLOBAL & for all <METHODS> - if they use UserDefined FNs for
        // callback-based param rules they cannot conflict with already set global handlers
        // Also, add any setRateLimit() for 'config' and <METHODS>
        // ------------------------------------------------------------------------------------------
        // This one is used to validate all param rules are used or warning is issued after iterating
        // through all routes who are the ones using param rules via themselves, method or config
        $USED_PARAM_RULES = ['config' => [], 'methods' => []];
        if (isset($this->validBatches['config']['paramRules'])) {
            $validRules = true;
            foreach ($this->validBatches['config']['paramRules'] as $configParamR) {
                if (isset($configParamR['callback']) && isset($GLOBAL_HANDLERS[$configParamR['callback']])) {
                    $this->compile_setErr("Conflicting User-defined Functions", "User-defined Callback for Global Param Rule `{$configParamR['callback']}` is Already being used by Global Handler `{$GLOBAL_HANDLERS[$configParamR['callback']]}`.  The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.");
                    $validRules = false;
                }
            }
            if ($validRules) {
                $this->compiled['config']['params'] = $this->validBatches['config']['paramRules'];
                $USED_PARAM_RULES['config'] = $this->validBatches['config']['paramRules'];
            }
        }
        if (isset($this->validBatches['ratelimit']['config'])) {
            $this->compiled['config']['ratelimit'] = $this->validBatches['ratelimit']['config'];
        }
        if (isset($this->validBatches['methods'])) {
            $validRules = true;
            foreach ($this->validBatches['methods'] as $method => $methodConfig) {
                if (isset($methodConfig['paramRules'])) {
                    foreach ($methodConfig['paramRules'] as $methodConfigParamR) {
                        if (isset($methodConfigParamR['callback']) && isset($GLOBAL_HANDLERS[$methodConfigParamR['callback']])) {
                            $this->compile_setErr("Conflicting User-defined Functions", "User-defined Callback for {$method} Param Rule `{$methodConfigParamR['callback']}` is Already being used by Global Handler `{$GLOBAL_HANDLERS[$methodConfigParamR['callback']]}`.  The Global Handlers such as `Error Handler`, `Exception Handler`, `URI Normalizer` and `Custom HTTPS Kernel` are always prioritized when first set with User-defined Functions.");
                            $validRules = false;
                        }
                    }
                    if ($validRules) {
                        $this->compiled['methods'][$method]['params'] = $methodConfig['paramRules'];
                        $USED_PARAM_RULES['methods'][$method] = $methodConfig['paramRules'];
                    }
                }
                if (isset($this->validBatches['ratelimit']['methods'][$method])) {
                    $this->compiled['methods'][$method]['ratelimit'] =  $this->validBatches['ratelimit']['methods'][$method];
                }
            }
        }
        // ------------------------------------------------------------------------------------------
        // STEP 11: Build `routes` - (unpacking) middlewares, headers|csp|nonces|exclusions and pipes
        // ------------------------------------------------------------------------------------------
        // STEP 11.1: Build `routes` - Check if routes exist or not and output error if not?
        // or should it be allowed to NOT have any routes just as a "soft success"?
        // First set the middleware
        $this->compiled['config']['runtime']['middlewares_inverted'] = $this->cached['placeholderMiddlewareInvertIindex'];

        // No Routes?
        if (!isset($this->validBatches['routes']) || count($this->validBatches['routes']) === 0) {
            //$this->compile_setWarn("`No Routes Configured`. This means ");
            $this->compile_welcome_splash();
        }
        // Routes exist!
        else {
            // STEP 11.2: Build `routes` - Iterate through each Method and their Single Routes
            // It is already GUARANTEED that Routes are not conflicting with one another as in
            // being exactly same or having different /:paramNames on the same URI segment level.
            // They are all following Folder-based Pathing so only one [dynamic] on a given level
            // or "directory depth" is allowed. So: "GET/:userid" & "POST/:user_id" will conflict.
            foreach ($this->validBatches['routes'] as $method => $methodRoutes) {
                ksort($methodRoutes); // Sort routes ascending so we always start with smallest route and build up
                foreach ($methodRoutes as $route => $routeDetails) {
                    // STEP 11.3.0: Add the current Route to the Trie - this is only
                    // when compiling and running it as the deployed build would
                    // have flattened route matching function instead.
                    $this->compile_add_to_route_trie($method, $route);
                    // STEP 11.3: Build `routes` - unpack all "group:" in Middlewares & Pipes first
                    // and add them to the $GLOBAL_GROUPED Array
                    $CURRENT_ROUTE_STR = "{$method}{$route}";
                    $CURRENT_ROUTE_PIPES = [];
                    $CURRENT_ROUTE_MWS = [];
                    $CURRENT_ROUTE_MWS_TO_INHERIT = [];
                    // if not same count as excludeMiddleware|Headers after merging
                    // all MWs|Headers, it means it tried to exlude non-existing ones
                    $CURRENT_ROUTE_EXCLUDED_MWS = [];
                    $CURRENT_ROUTE_EXCLUDED_HEADERS = [];
                    // WHEN NO PIPES & NO MWS!
                    if (
                        isset($routeDetails['pipes'])
                        && count($routeDetails['pipes']) === 0
                        && isset($routeDetails['middlewares'])
                        && count($routeDetails['middlewares']) === 0
                    ) {
                        if (!isset($this->compileFlags['ALLOW_GHOST_ROUTES'])) {
                            $this->compile_setErr("👻 GHOST ROUTE `{$CURRENT_ROUTE_STR}`👻", "You must have `at least 1 Pipe` (when you do not need any Middleware) OR `at least 1 Middleware` (when you only want the Route to act as a Middleware Scope for other Children Routes to inherit Middleware from; this means the Pipe-Empty Route returns `404` when 'matched') for a given Method/Route in order for it to be considered Valid to Compile. Due to this Error, no further Compiling for this current Route `{$method}{$route}` will take place until `at least 1 Route Pipe/Middleware` first has been added. Use `->setCompileFlag('ALLOW_GHOST_ROUTES')` in `/src/funkphp/app/CONFIG.app` if you need to allow for Empty Routes for the moment. This Compiler Flag will be removed when `php funk build` is used to build the `FunkPHPDeployment.php` File as Empty Routes should NOT be used in Production.");
                            $this->compiled['routes'][$method][$route] = $routeDetails;
                            $this->compiled['routes'][$method][$route]['👻GHOST_ROUTE👻'] = true;
                            continue;
                        } else {
                            $this->compiled['routes'][$method][$route]['👻GHOST_ROUTE👻'] = true;
                        }
                    }
                    // When ONLY MWs implying a scoping method/route (like MWs that to be inherited by subroutes)
                    if (
                        isset($routeDetails['pipes'])
                        && count($routeDetails['pipes']) === 0
                        && isset($routeDetails['middlewares'])
                        && count($routeDetails['middlewares']) > 0
                    ) {
                        $this->compile_setWarn("Only Route Middlewares without Route Pipes in `{$CURRENT_ROUTE_STR}`", "`Only Middlewares` in Route `{$CURRENT_ROUTE_STR}`. This means that Route `{$CURRENT_ROUTE_STR}` will return `404` when Routing Matched while its `Middlewares Will Be Inherited` by its Children Routes.");
                    }
                    // Add any Route alias - already guaranteed to be unique globally
                    if (isset($this->validBatches['routes'][$method][$route]['alias'])) {
                        $this->compiled['routes'][$method][$route]['alias'] = $this->validBatches['routes'][$method][$route]['alias'];
                    }
                    // Add hasParams Rule so it can be used to check for params where it first checks if route has needed param rules
                    // based upon param identifier matching (/user/:id must match 'id') and then it checks same but from the current
                    // method and then from global config. If no param rule found for any param from route then warning is issued.
                    $this->compiled['routes'][$method][$route]['hasParams'] = $this->validBatches['routes'][$method][$route]['hasParams'];
                    if (isset($this->compiled['routes'][$method][$route]['hasParams'])) {
                        foreach ($this->compiled['routes'][$method][$route]['hasParams'] as $routeParam) {
                            // Param Rule in Current Route?
                            if (isset($this->validBatches['routes'][$method][$route]['paramRules'][$routeParam])) {
                                $this->compiled['routes'][$method][$route]['params'][$routeParam] = $this->validBatches['routes'][$method][$route]['paramRules'][$routeParam];
                            } // Param Rule in Current Method?
                            else if (isset($this->compiled['methods'][$method]['params'][$routeParam])) {
                                $this->compiled['routes'][$method][$route]['params'][$routeParam] = $this->compiled['methods'][$method]['params'][$routeParam];
                                unset($USED_PARAM_RULES['methods'][$method][$routeParam]);
                            } // Param Rule in Global Config?
                            else if (isset($this->compiled['config']['params'][$routeParam])) {
                                $this->compiled['routes'][$method][$route]['params'][$routeParam] = $this->compiled['config']['params'][$routeParam];
                                unset($USED_PARAM_RULES['config'][$routeParam]);
                            }
                            // Issue warning when no Param Rule found for current Route Param
                            else {
                                $this->compile_setWarn("No Param Rule Available for `{$routeParam}` in `{$CURRENT_ROUTE_STR}`", "The following Param `{$routeParam}` in `{$CURRENT_ROUTE_STR}` has no Available Param Rules in Current Route, not in `{$method}`, and not in Global CONFIG. This means that You need to `Parse the Param Manually` using any of your `Route Pipe Function(s)`. If that is exactly what You are doing for `{$CURRENT_ROUTE_STR}`, just ignore this warning.");
                            }
                        }
                    }
                    // Now unpacking Pipes & MWs (meaning when they start with "group:")
                    // UNPACK PIPES for ROUTE: ???FIX LATER??? add "group:" for sql,query & validation?
                    // in the sense of: "group:sql:name", "group:query:name" & "group:validation:name"?
                    if (
                        isset($routeDetails['pipes'])
                        && count($routeDetails['pipes']) > 0
                    ) {
                        foreach ($routeDetails['pipes'] as $rPipe) {
                            if (!str_starts_with($rPipe, 'group:')) {
                                if (count($CURRENT_ROUTE_PIPES) > 0 && $CURRENT_ROUTE_PIPES[count($CURRENT_ROUTE_PIPES) - 1] === $rPipe) {
                                    $this->compile_setWarn("Consecutive Route Pipe File Functions", "`Consecutive Route Pipe Function '{$rPipe}' in {$CURRENT_ROUTE_STR} found`. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeFunction()` in `/src/funkphp/app/{$method}.php`.");
                                }
                                $CURRENT_ROUTE_PIPES[] = $rPipe;
                                continue;
                            }
                            if (!isset($GLOBAL_GROUPED['ROUTES_FILE_FUNCTIONS'][$rPipe])) {
                                $this->compile_setErr("Missing Route Pipe File Function Group in `{$CURRENT_ROUTE_STR}`", "Grouped Route Pipe File Functions name `{$rPipe}` used in `{$CURRENT_ROUTE_STR}` does not exist but was still part of the `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeFunction('{$pipe}')` in `/src/funkphp/app/{$method}.php`. Use `->setGroupPipeRoute('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.");
                            } else {
                                foreach ($GLOBAL_GROUPED['ROUTES_FILE_FUNCTIONS'][$rPipe] as $groupPipe) {
                                    if (count($CURRENT_ROUTE_PIPES) > 0 && $CURRENT_ROUTE_PIPES[count($CURRENT_ROUTE_PIPES) - 1] === $groupPipe) {
                                        $this->compile_setWarn("Consecutive Route Pipe File Functions in `{$CURRENT_ROUTE_STR}`", "Consecutive Route Pipe Function `{$groupPipe}` in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeFunction()` in `/src/funkphp/app/{$method}.php`.");
                                    }
                                    $CURRENT_ROUTE_PIPES[] = $groupPipe;
                                }
                            }
                        }
                    }
                    // Add ALL Pipes to Compiled Route including resolved versions of them
                    foreach ($CURRENT_ROUTE_PIPES as $CRoutePipe) {
                        $resolved = $this->compile_resolve_fn_paths('pipe', $CRoutePipe);
                        $this->compiled['routes'][$method][$route]['pipes-resolved'][] = $resolved;
                    }
                    $this->compiled['routes'][$method][$route]['pipes'] = $CURRENT_ROUTE_PIPES;
                    // Before we unpack MWs inside Route, we need to check if it has any routes first
                    // from current $method and then if any of its subroutes has any MWs (including MWs to unpack!)
                    // MWs inherited from Global Config (compared with what is in excludeMiddlewares!)
                    if (
                        isset($this->compiled['config']['pipes']['middlewares'])
                        && count($this->compiled['config']['pipes']['middlewares']) > 0
                    ) {
                        foreach ($this->compiled['config']['pipes']['middlewares'] as $configRMWs) {
                            if (isset($routeDetails['excludeMiddlewares'])) {
                                if (!in_array($configRMWs, $routeDetails['excludeMiddlewares'])) {
                                    if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $configRMWs) {
                                        $this->compile_setWarn("Consecutive `CONFIG() Middleware` Function `{$configRMWs}` for `{$CURRENT_ROUTE_STR}`", "Consecutive Middleware Function `{$configRMWs}` (`inherited from ->CONFIG()->pipeMiddleware('{$configRMWs}') in /src/funkphp/app/CONFIG.php`) in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware('{$configRMWs}')` in `/src/funkphp/app/{$method}.php` and/or in `/src/funkphp/app/CONFIG.php`.");
                                    }
                                    $CURRENT_ROUTE_MWS[] = $configRMWs;
                                } else {
                                    if (!in_array($configRMWs, $CURRENT_ROUTE_EXCLUDED_MWS)) {
                                        array_push($CURRENT_ROUTE_EXCLUDED_MWS, $configRMWs);
                                    }
                                    continue;
                                }
                            } else {
                                if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $configRMWs) {
                                    $this->compile_setWarn("Consecutive `CONFIG() Middleware` Function `{$configRMWs}` for `{$CURRENT_ROUTE_STR}`", "Consecutive Middleware Function `{$configRMWs}` (`inherited from ->CONFIG()->pipeMiddleware('{$configRMWs}') in /src/funkphp/app/CONFIG.php`) in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware('{$configRMWs}')` in `/src/funkphp/app/{$method}.php` and/or in `/src/funkphp/app/CONFIG.php`.");
                                }
                                $CURRENT_ROUTE_MWS[] = $configRMWs;
                            }
                        }
                    }
                    // MWs inherited from $method (compared withs what is in excludeMiddlewares!)
                    if (
                        isset($this->compiled['methods'][$method]['middlewares'])
                        && count($this->compiled['methods'][$method]['middlewares']) > 0
                    ) {
                        foreach ($this->compiled['methods'][$method]['middlewares'] as $mRWsIdx => $methodRMWs) {
                            if (isset($routeDetails['excludeMiddlewares'])) {
                                if (!in_array($methodRMWs, $routeDetails['excludeMiddlewares'])) {
                                    if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $methodRMWs) {
                                        if ($mRWsIdx === 0) {
                                            $this->compile_setWarn("Consecutive Middleware Function `{$methodRMWs}` between `CONFIG()`<->`{$method}` for `{$CURRENT_ROUTE_STR}`", "Consecutive Middleware Function `{$methodRMWs}` in `{$CURRENT_ROUTE_STR}` found. It is `inherited from CONFIG() Middlewares` defined in `/src/funkphp/app/CONFIG.php`. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware('{$methodRMWs}')` in `/src/funkphp/app/{$method}.php` and/or in `/src/funkphp/app/CONFIG.php`.");
                                        } else {
                                            $this->compile_setWarn("Consecutive `{$method} Middleware` Function `{$methodRMWs}` for `{$CURRENT_ROUTE_STR}`", "Consecutive `{$method} Middleware` Function `{$methodRMWs}` in /src/funkphp/app/{$method}.php`) in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware('{$methodRMWs}')` and `->ROUTES()->{$method}->pipeMiddleware('{$methodRMWs}')` in `/src/funkphp/app/{$method}.php`.");
                                        }
                                    }
                                    $CURRENT_ROUTE_MWS[] = $methodRMWs;
                                } else {
                                    if (!in_array($methodRMWs, $CURRENT_ROUTE_EXCLUDED_MWS)) {
                                        array_push($CURRENT_ROUTE_EXCLUDED_MWS, $methodRMWs);
                                    }
                                    continue;
                                }
                            } else {
                                if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $methodRMWs) {
                                    if ($mRWsIdx === 0) {
                                        $this->compile_setWarn("Consecutive Middleware Function `{$methodRMWs}` between `CONFIG()`<->`{$method}` for `{$CURRENT_ROUTE_STR}`", "Consecutive Middleware Function `{$methodRMWs}` in `{$CURRENT_ROUTE_STR}` found. It is `inherited from CONFIG() Middlewares` defined in `/src/funkphp/app/CONFIG.php`. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware('{$methodRMWs}')` in `/src/funkphp/app/{$method}.php` and/or in `/src/funkphp/app/CONFIG.php`.");
                                    } else {
                                        $this->compile_setWarn("Consecutive `{$method} Middleware` Function `{$methodRMWs}` for `{$CURRENT_ROUTE_STR}`", "Consecutive `{$method} Middleware` Function `{$methodRMWs}` in /src/funkphp/app/{$method}.php`) in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware('{$methodRMWs}')` and `->ROUTES()->{$method}->pipeMiddleware('{$methodRMWs}')` in `/src/funkphp/app/{$method}.php`.");
                                    }
                                }
                                $CURRENT_ROUTE_MWS[] = $methodRMWs;
                            }
                        }
                    }
                    // MWs inherited from 'subroutes' for current $method$route (against excludeMiddlewares!)
                    // The ROOT (Method) and the ROOT itself is NOT a subroute so any route needs at least
                    // two segments (not counting the initial / root one). Since Routes are sorted ascending
                    // the subroutes should either exist if they are defined and the middlewares should also
                    // already be unpacked so no need to look and parse for "group:" here. Then only add those
                    // that are NOT in the excludeMiddlewares array.
                    $this->compiled['routes'][$method][$route]['subRoutes'] = $routeDetails['subRoutes'];
                    if (isset($routeDetails['subRoutes']) && count($routeDetails['subRoutes']) > 0) {
                        foreach ($routeDetails['subRoutes'] as $subRouteMW) {
                            if (isset($this->validBatches['routes'][$method][$subRouteMW])) {
                                if (
                                    isset($this->validBatches['routes'][$method][$subRouteMW]['middlewares_to_inherit']) &&
                                    count($this->validBatches['routes'][$method][$subRouteMW]['middlewares_to_inherit']) > 0
                                ) {
                                    foreach ($this->validBatches['routes'][$method][$subRouteMW]['middlewares_to_inherit'] as $subRMWidx => $subRMW) {
                                        if (isset($routeDetails['excludeMiddlewares'])) {
                                            if (in_array($subRMW, $routeDetails['excludeMiddlewares'])) {
                                                if (!in_array($subRMW, $CURRENT_ROUTE_EXCLUDED_MWS)) {
                                                    array_push($CURRENT_ROUTE_EXCLUDED_MWS, $subRMW);
                                                }
                                                continue;
                                            }
                                        }
                                        if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $subRMW) {
                                            if ($subRMWidx === 0) {
                                                $this->compile_setWarn("Consecutive Middleware Function `{$subRMW}` between `{$method}{$subRouteMW}`<->`{$method}{$route}`", "Consecutive Pipe Middleware Function `{$subRMW}` in `{$CURRENT_ROUTE_STR}` found. It is `Inherited from SubRoute {$method}{$subRouteMW} Middlewares`. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$subRouteMW}')->pipeMiddleware()` and/or `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                                            } else {
                                                $this->compile_setWarn("Consecutive `{$method}{$route}` Middleware Function `{$subRMW}` from `{$method}{$subRouteMW}`", "Consecutive Pipe Middleware Function `{$subRMW}` in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                                            }
                                        }
                                        $CURRENT_ROUTE_MWS[] = $subRMW;
                                        continue;
                                    }
                                }
                            }
                            // When no SubRoute for current Route exists
                            else {
                                continue;
                            }
                        }
                    }
                    // UNPACK MWs for ROUTE
                    if (
                        isset($routeDetails['middlewares'])
                        && count($routeDetails['middlewares']) > 0
                    ) {
                        foreach ($routeDetails['middlewares'] as $rMWidx => $rMW) {
                            if (!str_starts_with($rMW, 'group:')) {
                                if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $rMW) {
                                    if ($rMWidx === 0) {
                                        $this->compile_setWarn("Consecutive Middleware Function `{$rMW}` between `{$method}`<->`{$method}{$route}`", "Consecutive Pipe Middleware File Function `{$rMW}` in `{$CURRENT_ROUTE_STR}` found. It is `Inherited from {$method} Middlewares`. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                                    } else {
                                        $this->compile_setWarn("Consecutive `{$method}{$route}` Middleware Function `{$rMW}`", "Consecutive Pipe Middleware File Function `{$rMW}` in `{$CURRENT_ROUTE_STR}` found. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                                    }
                                }
                                $CURRENT_ROUTE_MWS[] = $rMW;
                                $CURRENT_ROUTE_MWS_TO_INHERIT[] = $rMW;
                                continue;
                            }
                            if (!isset($GLOBAL_GROUPED['MIDDLEWARES'][$rMW])) {
                                $this->compile_setErr("Missing `Middleware` Function Group in `{$CURRENT_ROUTE_STR}`", "Grouped Middleware Pipe Functions with the name `{$rMW}` used in `{$CURRENT_ROUTE_STR}` does not exist but was still part of the `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeiddleware('{$rMW}')` in `/src/funkphp/app/{$method}.php`. Use `->setGroupPipeMiddlewares('{$pipe}')` in `/src/funkphp/app/CONFIG.php` to first create the Grouping.");
                            } else {
                                foreach ($GLOBAL_GROUPED['MIDDLEWARES'][$rMW] as $rMWPipe) {
                                    // This is Route's MW and here you CANNOT exclude the same one to pipe as exclusion applies to
                                    // subroutes, method and global config, not the route itself. Same applies for excluding headers.
                                    if (isset($routeDetails['excludeMiddlewares']) && in_array($rMWPipe, $routeDetails['excludeMiddlewares'])) {
                                        $this->compile_setErr("Conflicting Middlewares in `{$method}{$route}`", "Middleware `{$rMWPipe}` in Middleware Group `{$rMW}` (first set in `CONFIG()` in `/src/funkphp/app/CONFIG.php`) is also registered as a `Middleware to Exclude` in `->ROUTES()->{$method}()->ROUTE('{$route}')->setExcludeMiddlewares()` in `/src/funkphp/app/{$method}.php`.");
                                        continue;
                                    }
                                    if (count($CURRENT_ROUTE_MWS) > 0 && $CURRENT_ROUTE_MWS[count($CURRENT_ROUTE_MWS) - 1] === $rMWPipe) {
                                        $this->compile_setWarn("Consecutive Middleware Function `{$rMWPipe}` in `{$method}{$route}`", "Consecutive Pipe Middleware File Function `{$rMWPipe}` in `{$CURRENT_ROUTE_STR}` found. This could be from `{$method}` Middlewares OR it is from its own Middlewares. Ignore this warning if it is intentional OR Review `->ROUTES()->{$method}()->ROUTE('{$route}')->pipeMiddleware()` in `/src/funkphp/app/{$method}.php`.");
                                    }
                                    $CURRENT_ROUTE_MWS[] = $rMWPipe;
                                    $CURRENT_ROUTE_MWS_TO_INHERIT[] = $rMWPipe;
                                    // As MWs are unpacked, add then them to method-route-based MW Invert Index
                                    if (!isset($this->cached['placeholderMiddlewareInvertIindex'][$rMWPipe])) {
                                        $this->cached['placeholderMiddlewareInvertIindex'][$rMWPipe][] =  $method . $route;
                                    } else {
                                        if (!in_array("$method$route", $this->cached['placeholderMiddlewareInvertIindex'][$rMWPipe])) {
                                            $this->cached['placeholderMiddlewareInvertIindex'][$rMWPipe][] = "$method$route";
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // Add ALL MWs now to Compiled Route
                    if (
                        isset($routeDetails['excludeMiddlewares'])
                        && count(array_diff($routeDetails['excludeMiddlewares'], $CURRENT_ROUTE_EXCLUDED_MWS)) > 0
                    ) {
                        $this->compile_setErr("Failed to Exclude Middlewares in `{$method}{$route}`", "Failed to Exclude these Middlewares in `{$method}{$route}`: '" . $this->joinArray(array_diff($routeDetails['excludeMiddlewares'], $CURRENT_ROUTE_EXCLUDED_MWS)) . "'. This means that Middleware/those Middlewares in `{$method}{$route}` do NOT exist in the `CONFIG()`, `{$method} CONFIG`, or any of the Subroutes: " . $this->joinArray(($routeDetails['subRoutes'] ?? ['<No Subroutes>'])) . ". Review the Excluded Middlewares in `->ROUTES()->{$method}()->ROUTE('{$route}')->setExcludeMiddlewares()` in `/src/funkphp/config/{$method}.php`.");
                    }
                    foreach ($CURRENT_ROUTE_MWS as $currRmw) {
                        $resolved = $this->compile_resolve_fn_paths('middleware', $currRmw);
                        $this->compiled['routes'][$method][$route]['middlewares-resolved'][] = ['run' => $resolved[0], 'path' => $resolved[1]];
                    }
                    $this->compiled['routes'][$method][$route]['middlewares'] = $CURRENT_ROUTE_MWS;
                    $this->compiled['routes'][$method][$route]['middlewares_to_inherit'] = $CURRENT_ROUTE_MWS_TO_INHERIT;
                    // STEP 11.4: Build `routes` - BUILD HEADERS (COMBINED WITH ExcludedHeaders) for ROUTES
                    // where we begin adding those from the Route itself and then we only add those that are
                    // not already added with the same header name (as Route Headers are prioritized) OR are
                    // in the excludeHeaders array for the Route. Then we do the same for 'remove' Headers.
                    if (isset($routeDetails['headers']['add'])) {
                        foreach ($routeDetails['headers']['add'] as $routeHeader) {
                            $this->compiled['routes'][$method][$route]['headers']['add'][] = strtolower($routeHeader['name']) . ': ' . $routeHeader['value'];
                        }
                    }
                    // Add any Non-Excluded Or Already-Added-With-Same-Name Method Headers
                    if (isset($this->compiled['methods'][$method]['headers']['add'])) {
                        foreach ($this->compiled['methods'][$method]['headers']['add'] as $methodHKey => $methodHeader) {
                            if (
                                isset($routeDetails['headers']['add'][$methodHKey])
                                || (isset($routeDetails['excludeHeaders'])
                                    && in_array($methodHKey, $routeDetails['excludeHeaders']))
                            ) {
                                if (
                                    isset($routeDetails['excludeHeaders'])
                                    && in_array($methodHKey, $routeDetails['excludeHeaders'])
                                ) {
                                    $CURRENT_ROUTE_EXCLUDED_HEADERS[] = $methodHKey;
                                }
                                continue;
                            } else {
                                $this->compiled['routes'][$method][$route]['headers']['add'][] = strtolower($methodHeader['name']) . ': ' . $methodHeader['value'];
                            }
                        }
                    }
                    // Add any Non-Excluded Or Already-Added-With-Same-Name Config/GLOBAL Headers
                    if (isset($this->compiled['config']['headers']['add'])) {
                        foreach ($this->compiled['config']['headers']['add'] as $configHKey => $configHeader) {
                            if (
                                isset($routeDetails['headers']['add'][$configHKey])
                                || (isset($routeDetails['excludeHeaders'])
                                    && in_array($configHKey, $routeDetails['excludeHeaders']))
                            ) {
                                if (
                                    isset($routeDetails['excludeHeaders'])
                                    && in_array($configHKey, $routeDetails['excludeHeaders'])
                                ) {
                                    $CURRENT_ROUTE_EXCLUDED_HEADERS[] = $configHKey;
                                }
                                continue;
                            } else {
                                $this->compiled['routes'][$method][$route]['headers']['add'][] = strtolower($configHeader['name']) . ': ' . $configHeader['value'];
                            }
                        }
                    }
                    // Finally check that all headers to exclude from route actually were that or set compile error
                    if (
                        isset($routeDetails['excludeHeaders'])
                        && count(array_diff($routeDetails['excludeHeaders'], $CURRENT_ROUTE_EXCLUDED_HEADERS)) > 0
                    ) {
                        $this->compile_setErr("Failed to Exclude Headers in `{$method}{$route}`", "Failed to Exclude these Headers in `{$method}{$route}`: '" . $this->joinArray(array_diff($routeDetails['excludeHeaders'], $CURRENT_ROUTE_EXCLUDED_HEADERS)) . "'. This means that Header/those Headers in `{$method}{$route}` do NOT exist in the `CONFIG()` or `{$method} CONFIG`. Review the Excluded Headers in `->ROUTES()->{$method}()->ROUTE('{$route}')->setExcludeHeaders()` in `/src/funkphp/config/{$method}.php`.");
                    }
                    // Quick adding of 'remove' headers for current Route, then from Method and Config unless already added
                    if (isset($routeDetails['headers']['remove'])) {
                        foreach ($routeDetails['headers']['remove'] as $routeHeaderRemove) {
                            $this->compiled['routes'][$method][$route]['headers']['remove'][] = $routeHeaderRemove;
                        }
                    }
                    if (isset($this->compiled['methods'][$method]['headers']['remove'])) {
                        foreach ($this->compiled['methods'][$method]['headers']['remove'] as $methodHeaderRemove) {
                            if (!in_array($methodHeaderRemove,  $this->compiled['routes'][$method][$route]['headers']['remove'])) {
                                $this->compiled['routes'][$method][$route]['headers']['remove'][] = $methodHeaderRemove;
                            }
                        }
                    }
                    if (isset($this->compiled['config']['headers']['remove'])) {
                        foreach ($this->compiled['config']['headers']['remove'] as $configHeaderRemove) {
                            if (!in_array($configHeaderRemove,  $this->compiled['routes'][$method][$route]['headers']['remove'])) {
                                $this->compiled['routes'][$method][$route]['headers']['remove'][] = $configHeaderRemove;
                            }
                        }
                    }
                    // STEP 11.5: Build `routes` - setCSP() and nonces for the Route as CSP Directives
                    // are inherited first from Method and then from CONFIG (those actually defined)
                    // and NOT empty with nonces waiting for them to be used.
                    // First from CONFIG
                    if (!empty($this->compiled['config']['csp'])) {
                        $this->compiled['routes'][$method][$route]['csp'] = $this->compiled['config']['csp'];
                        $this->compiled['routes'][$method][$route]['nonces'] = $this->compiled['config']['nonces'] ?? [];
                    }
                    // Then from current $method
                    if (!empty($this->compiled['methods'][$method]['csp'])) {
                        foreach ($this->compiled['methods'][$method]['csp'] as $directive => $sources) {
                            $this->compiled['routes'][$method][$route]['csp'][$directive] = $sources;
                        }
                        if (!empty($this->compiled['methods'][$method]['nonces'])) {
                            foreach ($this->compiled['methods'][$method]['nonces'] as $nonceName => $dir) {
                                $this->compiled['routes'][$method][$route]['nonces'][$nonceName] = $dir;
                            }
                        }
                    }
                    // Finally from current $route
                    if (!empty($routeDetails['csp'])) {
                        foreach ($routeDetails['csp'] as $directive => $sources) {
                            $this->compiled['routes'][$method][$route]['csp'][$directive] = $sources;
                        }
                        if (!empty($routeDetails['nonces'])) {
                            foreach ($routeDetails['nonces'] as $nonceName => $dir) {
                                $this->compiled['routes'][$method][$route]['nonces'][$nonceName] = $dir;
                            }
                        }
                    }
                    // STEP 11.6: Build `routes` - setCache() and setRateLimit() for Route
                    if (isset($this->validBatches['cache']['routes'][$method][$route])) {
                        $this->compiled['routes'][$method][$route]['cache'] = $this->validBatches['cache']['routes'][$method][$route];
                    }
                    if (isset($this->validBatches['ratelimit']['routes'][$method][$route])) {
                        $this->compiled['routes'][$method][$route]['ratelimit'] = $this->validBatches['ratelimit']['routes'][$method][$route];
                    }
                    // STEP 11.7: Build `routes` - Check for any pipeResponse, it is either something
                    // OR null so just add it anyway but issue a warning when it is null.
                    if (!isset($this->validBatches['routes'][$method][$route]['response'])) {
                        if (isset($this->compileFlags['ALL_ROUTES_MUST_HAVE_PIPE_RESPONSE'])) {
                            $this->compile_setErr("Response is REQUIRED in Route `{$CURRENT_ROUTE_STR}`", "Compiler Flag `ALL_ROUTES_MUST_HAVE_PIPE_RESPONSE` forces `{$CURRENT_ROUTE_STR}` to have a `->pipeResponse()`.");
                        } else if (!isset($this->compileFlags['HIDE_NO_ROUTE_RESPONSE_WARNING'])) {
                            $this->compile_setWarn("No Response in Route `{$CURRENT_ROUTE_STR}`", "The Route `{$CURRENT_ROUTE_STR}` has no `Piped Response` (via `->pipeResponse()`) meaning it must be handled manually inside of Pipe Functions OR the Route `{$CURRENT_ROUTE_STR}` would essentially NOT have a Response to the End-user. Use `funk_return_response_page()`, `funk_return_response_json()`, `funk_return_response_callback()`, or `funk_return_response_file()` inside any of the referenced Files=>Functions in any of the `->pipeFunction()` in order to fulfill the requirement of returning a Response in the Route `{$CURRENT_ROUTE_STR}`. Remember that no other `->pipe<TYPE>()` can be used after the `->pipeResponse()` for the Route as it is meant to complete the HTTP(S) Request.");
                        }
                    } else {
                        if (
                            isset($this->validBatches['routes'][$method][$route]['response']['type'])
                            && $this->validBatches['routes'][$method][$route]['response']['type'] === 'callback'
                        ) {
                            if (isset($GLOBAL_HANDLERS[$this->validBatches['routes'][$method][$route]['response']['context']])) {
                                $this->compile_setErr("User-defined File Function for Route Response Already in Use", "Provided User-defined Function `{$this->validBatches['routes'][$method][$route]['response']['context']}` in `/src/funkphp/config/functions.php` used for `Piped Response` in `{$CURRENT_ROUTE_STR}`  is already set as the following Global Handler: `{$GLOBAL_HANDLERS[$this->validBatches['routes'][$method][$route]['response']['context']]}`.");
                            }
                        }
                    }
                    $this->compiled['routes'][$method][$route]['response'] = $this->validBatches['routes'][$method][$route]['response'];

                    // END OF Current $route Iteration!
                }
                // Any Param Rules for Current $method that were NEVER used by Any of its $route(s)?
                if (isset($USED_PARAM_RULES['methods'][$method]) && count($USED_PARAM_RULES['methods'][$method]) > 0) {
                    $unusedCount = count($USED_PARAM_RULES['methods'][$method]);
                    $rulesList = $this->joinArray($USED_PARAM_RULES['methods'][$method], true);
                    $this->compile_setWarn(
                        "`{$unusedCount} Unused` {$method} Param Rule(s)",
                        "The following `{$method} Param Rule(s)` were NEVER USED by any Route in `{$method}`: {$rulesList}. Routes either Override Them Locally or do not require them. Feel free to remove any deemed unnecessary (see all `->setParamRule()` in `/src/funkphp/app/{$method}.php`) OR ignore this warning."
                    );
                }
                // END OF Current $method Iteration!
            } // ITERATING THROUGH ALL METHODS WITH ROUTES COMPLETE HERE!
            // Any Param Rules for ALL $method(s) and $route(s) that were NEVER used?
            if (isset($USED_PARAM_RULES['config']) && count($USED_PARAM_RULES['config']) > 0) {
                $unusedCount = count($USED_PARAM_RULES['config']);
                $rulesList = $this->joinArray($USED_PARAM_RULES['config'], true);
                $this->compile_setWarn(
                    "`{$unusedCount} Unused` Global Param Rule(s)",
                    "The following `Global Param Rule(s)` were NEVER USED by Any Route: {$rulesList}. Methods/Routes either Override Them Locally or do not require them. Feel free to remove any deemed unnecessary (see all `->setParamRule()` in `/src/funkphp/app/CONFIG.php`) OR ignore this warning."
                );
            }
            // STEP 11.7: Build `routes` - generate final metadata for trie
            // which is very useful when building flattened route matching
            $this->compile_build_trie_metadata();
            // STEP 11.8: Populate the $c Variable (only relevant if it then
            // runs locally though) via global access
            global $c;
            $c = $this->compiled['c'];
        }  // COMPILATION COMPLETE HERE (CAN NOW RUN OR CREATE FunkPHPDeployment.php)
        /////////////////////////////////////// END /////////////////////////////
        // Show in-built FunkPHP GUI if any Compilation Errors OR Warnings if
        // not allowed OR if Debug is set to ALWAYS_SHOW - good for... debugging
        // OTHERWISE: run() it as is (when running locally) OR move on to building
        // the FunkPHPDeployment.php File (usually happens when `php funk build` runs
        // as it calls the same function with CompileAndRunLocally=false)
        if ((isset($this->errors['COMPILATION']['errors'])
                && count($this->errors['COMPILATION']['errors']) > 0)
            || $this->debug['ALWAYS_SHOW'] || (isset($this->compileFlags['NO_WARNINGS_ALLOWED'])
                && (count($this->errors['COMPILATION']['warnings']) > 0))
        ) {
            $this->output_errors($this->errors, $this->compiled);
        }
        if ($CompileAndRunLocally) {
            $this->run();
        }

        ///////////////////////////////////////////////////////
        ////////// START BUILDING FunkPHPDeployment.php ///////
        ///////////////////////////////////////////////////////

        //////////////////////////////////////////////////////
        ////////// DONE BUILDING FunkPHPDeployment.php ///////
        //////////////////////////////////////////////////////
    }
    private function run()
    {
        // Run the valid compiled FunkPHP - which is NOT the same as outputting
        // it to the FunkPHPDeployment.php Monolithic File. This is essentially
        // running locally without the optimized output file which is why the
        // Trie Routes version also exist to run it locally without the file.

        // First load custom functions & classes (core functions already loaded)
        require_once ROOT_FOLDER . '/config/functions.php';
        require_once ROOT_FOLDER . '/config/classes.php';
        // Grab the global $c since that is what is passed around everywhere
        global $c;
        // If Custom HTTPS Kernel wanna deal with all the running, then just pass on the $this->compiled
        // inside of the $c and exit early as Custom HTTPS Kernel gotta deal then with shutdown reigster
        // if desirable or if wanna use the post_response-resolved parts differently. Even ob_start()
        // must be turned on manually if using Custom HTTPS Kernel. GL&HF! ^_^ May the best Kernel Win!
        if (isset($this->compiled['config']['runtime']['custom_https_kernel'])) {
            if (function_exists($this->compiled['config']['runtime']['custom_https_kernel'])) {
                $c['compiled'] = $this->compiled;
                $this->compiled['config']['runtime']['custom_https_kernel']($c);
            } else {
                $c['err']['INTERNAL'][] = "Failed to find expected `Custom User-defined HTTPS Kernel Handler Function`.";
            }
            exit;
        }
        // Run all set ini if any with ini_set()
        if (isset($this->compiled['config']['runtime']['ini_sets'])) {
            foreach ($this->compiled['config']['runtime']['ini_sets'] as $compiledIniSetK => $compiledIniSetV) {
                ini_set($compiledIniSetK, $compiledIniSetV);
            }
        }
        // Output buffering starts
        ob_start();
        // Constant FUNKPHP_ONLINE is always FALSE during run() / local running
        if (isset($this->compiled['config']['runtime']['online'])) {
            define("FUNKPHP_ONLINE", false);
        }
        // Include Composer Vendor stuff is set to true and if file exist
        if (
            isset($this->compiled['config']['runtime']['use_vendor']) &&
            $this->compiled['config']['runtime']['use_vendor'] === true
        ) {
            $vendorPath = ROOT_FOLDER . '/vendor/autoload.php';
            if (file_exists($vendorPath)) {
                require_once $vendorPath;
            } else {
                $c['err']['INTERNAL'][] = "Vendor Autoload Enabled (`use_vendor = true`), but File `{$vendorPath}` was NOT Found.";
            }
        }
        // Set User-defined or default exception handler
        set_exception_handler(function (\Throwable $e) use (&$c) {
            if (isset($this->compiled['config']['runtime']['custom_exception_handler'])) {
                if (function_exists($this->compiled['config']['runtime']['custom_exception_handler'])) {
                    $this->compiled['config']['runtime']['custom_exception_handler']($c, $e);
                } else {
                    $c['err']['INTERNAL'][] = "Failed to find expected `Custom User-defined Exception Handler Function`. Fallbacks to In-built Default.";
                    \funk_internal_exception_handler($c, $e);
                }
            } else {
                \funk_internal_exception_handler($c, $e);
            }
        });
        // Set User-defined or default error handler
        if (isset($this->compiled['config']['runtime']['custom_error_handler'])) {
            if (function_exists($this->compiled['config']['runtime']['custom_error_handler'])) {
                set_error_handler($this->compiled['config']['runtime']['custom_error_handler']);
            } else {
                $c['err']['INTERNAL'][] = "Failed to find expected `Custom User-defined Error Handler Function`.";
            }
        } else {
            set_error_handler('\funk_internal_error_handler');
        }
        // Add any post-response pipes as registered shutdown functions so that is prepared first
        foreach ($this->compiled['config']['pipes']['post_response-resolved'] as $pResponseRegister) {
            $funcName = $pResponseRegister['run'];
            $filePath = $pResponseRegister['path'];
            if (!function_exists($funcName) && file_exists($filePath)) {
                require_once $filePath;
            }
            if (function_exists($funcName)) {
                register_shutdown_function(function () use ($funcName, &$c) {
                    $funcName($c);
                });
            } else {
                $c['err']['post-response'][] = "Post-response Pipe Function `{$funcName}` Failed to be resolved after being loaded from Path `{$pResponseRegister['path']}`.";
                trigger_error("Post-response Pipe Function `{$funcName}` could not be resolved.", E_USER_WARNING);
            }
        }
        // Run any request pipes registered
        foreach ($this->compiled['config']['pipes']['request-resolved'] as $pRequest) {
            $funcName = $pRequest['run'];
            $filePath = $pRequest['path'];
            if (!function_exists($funcName) && file_exists($filePath)) {
                require_once $filePath;
            }
            if (function_exists($funcName)) {
                $funcName($c);
            } else {
                // Fallback or early warning if file/function failed to resolve
                $c['err']['request'][] = "Request Pipe Function `{$funcName}` Failed to be resolved after being loaded from Path `{$pRequest['path']}`.";
                trigger_error("Request Pipe Function `{$funcName}` could not be resolved.", E_USER_WARNING);
            }
        }
        // Run any set funk_internal_rate_limiter() for global/CONFIG() context
        if (isset($this->compiled['config']['ratelimit'])) {
            funk_internal_rate_limiter(
                $c,
                $this->compiled['config']['ratelimit']['max_requests'],
                $this->compiled['config']['ratelimit']['window_seconds'],
                $this->compiled['config']['ratelimit']['by'],
                $this->compiled['config']['ratelimit']['driver']
            );
        }
        // Run any set URI normalizer OR the in-built will run
        // Here we also set the method whether on "_method" is in $_POST meaning form spoofing
        if (isset($this->compiled['config']['runtime']['custom_uri_normalizer'])) {
            $c['req']['uri'] = $this->compiled['config']['runtime']['custom_uri_normalizer']($c);
        } else {
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
            $c['req']['uri'] = ($cleanPath === '') ? '/' : '/' . $cleanPath;
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptName = $scriptName ?: $_SERVER['SCRIPT_NAME'] ?: '';
            $baseUrl = $baseUrl ? $baseUrl : dirname($scriptName);
            $c['req']['base_url_absolute'] = rtrim($protocol . $host . $baseUrl, '/');
            $c['req']['base_url_relative'] = ($baseUrl === '/') ? '' : $baseUrl;
        }
        $c['req']['method'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($c['req']['method'] === 'POST' && !empty($this->compiled['config']['runtime']['request_form_spoof_methods'])) {
            $spoofedMethod = ($_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');
            if (in_array($spoofedMethod, $this->compiled['config']['runtime']['request_form_spoof_methods'], true)) {
                $c['req']['method'] = $spoofedMethod;
            }
        }
        // Resolve IP (parse correct IP from trusted proxy if configured)
        // with either User-defined Function OR with internal default
        $c['runtime']['trusted_ip_proxies'] = $this->compiled['config']['runtime']['trusted_ip_proxies'];
        $c['runtime']['trusted_ip_headers'] = $this->compiled['config']['runtime']['trusted_ip_headers'];
        if (isset($this->compiled['config']['runtime']['custom_ip_resolver'])) {
            $c['req']['ip'] = $this->compiled['config']['runtime']['custom_ip_resolver']($c);
        } else {
            $c['req']['ip'] = funk_internal_resolve_ip($c);
        }
        $c['req']['time'] = $_SERVER['REQUEST_TIME'] ?? time();
        $c['req']['query'] = $_SERVER['QUERY_STRING'] ?? null;

        // The question now is: run global middlewares BEFORE route-matching OR should they
        // only run after a matched route just like middlewares for matched method+route only
        // run when matched method (its middlewares) and then also the matched route's mws?
        // REMOVE LATER: Placeholder echo to know when compiled
        echo "run() started - compilation succeeded!<br/>";
        // A final exit to not be able to jump back to the compile() again
        // This will also trigger registered shutdown functions/any post-response pipes
        exit;
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
     * Set Compiler Flags that are applied when compiling. Most of them are about what is allowed or not, whether to ignore certain warnings and/or errors or not.
     *
     * @param 'ALLOW_GHOST_ROUTES'|'ALL_ROUTES_MUST_HAVE_PIPE_RESPONSE'|'HIDE_NO_ROUTE_RESPONSE_WARNING'|'NO_WARNINGS_ALLOWED'|'ONLY_RETURN_COMPILED_PAGES'|'ONLY_RETURN_NONCOMPILED_PAGES' $flag Compiler flag (e.g., "NO_WARNINGS_ALLOWED")
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
     * Set Rate Limiting Globally. This is always applied first before any Matched Method's Rate Limiting which itself applies before any Matched Route's Rate Limiting.
     *
     * @param int $maxRequestsPerWindowSize Maximum allowed requests within the time window (1 to 1,000,000).
     * @param int $windowSizeInSeconds Time window duration in seconds (1 to 86,400 / 24 hours).
     * @param string|array<int, string> $by Client identifier key or array of keys (e.g. 'ip', 'user_id', 'header:X-Api-Key', 'query:token').
     * @param 'redis'|'memcached'|'file'|'apcu'|'array' $driver Rate limiter storage driver (e.g. 'redis', 'memcached', 'apcu').
     * @return $this
     */
    public function setRateLimit(
        int $maxRequestsPerWindowSize = 60,
        int $windowSizeInSeconds = 60,
        string|array $by = 'ip',
        string $driver = 'redis'
    ): self {
        $this->c->batch('batchSetRateLimitingGlobal', $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, strtolower(trim($driver)));
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
     * Set Custom User-defined Exception Handler to be set in `set_exception_handler()`
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
     * Set Custom User-defined Error Handler to be set in `set_error_handler()`
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
     * Set Custom User-defined URI Normalizer to normalize `$_SERVER['REQUEST_URI']` to be set in `$c['req']['uri']`
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
     * Set Custom User-defined HTTP(S) Kernel that will do everything with the Successful Compiled
     *
     * @param string $userDefinedFunctionName Name of the Custom HTTP(S) Kernel
     * @return $this
     */
    public function setDefaultKernelHandler(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultHTTPSKernelDispatchHandlerGlobal', $userDefinedFunctionName);
        return $this;
    }

    /**
     * Set Custom User-defined IP Resolver instead of using in-built `funk_internal_resolve_ip()`
     *
     * If you set a Custom IP Resolver, you can use the stored `Trusted IPv4 & IPv6 Proxies` in:
     * `$c['runtime']['trusted_ip_proxies']['ip4']` and `$c['runtime']['trusted_ip_proxies']['ip6']`
     *  for `Trusted IP Headers` stored in `$c['runtime']['trusted_ip_headers']` - all 3 List Arrays.
     *
     * @param string $userDefinedFunctionName Name of the IP Resolver Function
     * @return $this
     */
    public function setDefaultIPResolver(string $userDefinedFunctionName): self
    {
        $userDefinedFunctionName = strtolower(trim($userDefinedFunctionName));
        $this->c->batch('batchSetDefaultIPResolverGlobal', $userDefinedFunctionName);
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
     * @param 'callback:user_defined_fn'|'/^regex_pattern$/i' $regexORcb EITHER a User-defined Function defined in `/src/funkphp/config/functions.php` OR a Valid Regex pattern (e.g., "/[\d]+/")
     * @param string|null $defaultParamValueOnRegexMismatch Fallback value if validation fails
     * @return $this
     */
    public function setParamRule(string $param, string $regexORcb, $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleGlobal', $param, $regexORcb, $defaultParamValueOnRegexMismatch);
        return $this;
    }

    /**
     * Set a response Header that is applied globally (can be overwritten with a setHeader on a given Method AND on a given Route).
     *
     * @param 'Accept-Ranges'|'Access-Control-Allow-Credentials'|'Access-Control-Allow-Headers'|'Access-Control-Allow-Methods'|'Access-Control-Allow-Origin'|'Access-Control-Expose-Headers'|'Access-Control-Max-Age'|'Age'|'Allow'|'Alt-Svc'|'Cache-Control'|'Clear-Site-Data'|'Content-Disposition'|'Content-Encoding'|'Content-Language'|'Content-Location'|'Content-Range'|'Content-Security-Policy'|'Content-Security-Policy-Report-Only'|'Content-Type'|'Cross-Origin-Embedder-Policy'|'Cross-Origin-Opener-Policy'|'Cross-Origin-Resource-Policy'|'ETag'|'Expires'|'Last-Modified'|'Location'|'Origin-Trial'|'Permissions-Policy'|'Pragma'|'Referrer-Policy'|'Retry-After'|'Server-Timing'|'Strict-Transport-Security'|'Timing-Allow-Origin'|'Vary'|'WWW-Authenticate'|'X-Content-Type-Options'|'X-Frame-Options'|'X-RateLimit-Limit'|'X-RateLimit-Remaining'|'X-RateLimit-Reset'|'X-Request-ID'|'X-XSS-Protection'|string $header Header Name
     * @param string $value Header Value (e.g., "nosniff")
     * @return $this
     */
    public function setHeaderAdd(string $header, string $value): self
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
    public function setHeaderRemove(string $header_to_remove): self
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
     * Set Rate Limiting for current Method. Remember that any Rate Limiting set on the Global CONFIG is always applied first, and this Method must also be matched before its Rate Limiting kicks into full gear.
     *
     * @param int $maxRequestsPerWindowSize Maximum allowed requests within the time window (1 to 1,000,000).
     * @param int $windowSizeInSeconds Time window duration in seconds (1 to 86,400 / 24 hours).
     * @param string|array<int, string> $by Client identifier key or array of keys (e.g. 'ip', 'user_id', 'header:X-Api-Key', 'query:token').
     * @param 'redis'|'memcached'|'file'|'apcu'|'array' $driver Rate limiter storage driver (e.g. 'redis', 'memcached', 'apcu').
     * @return $this
     */
    public function setRateLimit(
        int $maxRequestsPerWindowSize = 60,
        int $windowSizeInSeconds = 60,
        string|array $by = 'ip',
        string $driver = 'redis'
    ): self {
        $this->c->batch('batchSetRateLimitingMethod', $this->method, $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, strtolower(trim($driver)));
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
    public function setHeaderAdd(string $header, string $value): self
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
    public function setHeaderRemove(string $header_to_remove): self
    {
        $header_to_remove = strtolower(trim($header_to_remove));
        $this->c->batch('batchRemoveHeaderMethod', $this->method, $header_to_remove);
        return $this;
    }
    /**
     * Define a parameter validation regex rule scoped to this HTTP method.
     *
     * @param string $param Parameter name without leading colon (e.g., "id")
     * @param 'callback:user_defined_fn'|'/^regex_pattern$/i' $regexORcb EITHER a User-defined Function defined in `/src/funkphp/config/functions.php` OR a Valid Regex pattern (e.g., "/[\d]+/")
     * @param string|null $defaultParamValueOnRegexMismatch Fallback value if validation fails
     * @return $this
     */
    public function setParamRule(string $param, string $regexORcb, string|null $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleMethod', $this->method, $param, $regexORcb, $defaultParamValueOnRegexMismatch);
        return $this;
    }
    /**
     * Initialize a New Route Prefix for the current HTTP method.
     *
     * @param string $prefix Route Prefix that must be a Valid Route Path Pattern (e.g., "/users" OR "/users/:id" and so on)
     * @return $this
     */
    public function ROUTEPrefixSet(string $prefix): self
    {
        $this->c->batch('batchNewRoutePrefixSet', $this->method, strtolower(trim($prefix)));
        return $this;
    }
    /**
     * Resets last used Route Prefix for the current HTTP method.
     *
     * @return $this
     */
    public function ROUTEPrefixReset(): self
    {
        $this->c->batch('batchNewRoutePrefixReset', $this->method);
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
        $methodPrefix = $this->c->batch("batchMethodPrefix", $this->method);
        $this->c->batch('batchNewRoute', $this->method, strtolower(trim($methodPrefix . $path)));
        return new FunkRoute($this->c, $this, $this->method, strtolower(trim($methodPrefix . $path)));
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
     * Initialize a New Route Prefix for the current HTTP method.
     *
     * @param string $prefix Route Prefix that must be a Valid Route Path Pattern (e.g., "/users" OR "/users/:id" and so on)
     * @return $this
     */
    public function ROUTEPrefixSet(string $prefix): self
    {
        $this->c->batch('batchNewRoutePrefixSet', $this->method, strtolower(trim($prefix)));
        return $this;
    }
    /**
     * Resets last used Route Prefix for the current HTTP method.
     *
     * @return $this
     */
    public function ROUTEPrefixReset(): self
    {
        $this->c->batch('batchNewRoutePrefixReset', $this->method);
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
     * Set Rate Limiting for current Method=>Route. Remember that any Rate Limiting set on the Method as a whole and/or Globally are always applied first, including that this Route must also first be matched on its method before this Rate Limit kicks into full gear.
     *
     * @param int $maxRequestsPerWindowSize Maximum allowed requests within the time window (1 to 1,000,000).
     * @param int $windowSizeInSeconds Time window duration in seconds (1 to 86,400 / 24 hours).
     * @param string|array<int, string> $by Client identifier key or array of keys (e.g. 'ip', 'user_id', 'header:X-Api-Key', 'query:token').
     * @param 'redis'|'memcached'|'file'|'apcu'|'array' $driver Rate limiter storage driver (e.g. 'redis', 'memcached', 'apcu').
     * @return $this
     */
    public function setRateLimit(int $maxRequestsPerWindowSize = 60, int $windowSizeInSeconds = 60, string $by = 'ip', string $driver = 'redis'): self
    {
        $this->c->batch('batchSetRateLimitingRoute', $this->method, $this->routePath, $maxRequestsPerWindowSize, $windowSizeInSeconds, $by, strtolower(trim($driver)));
        return $this;
    }
    /**
     * Set Caching Options for Current Method=>Route. Caching can only be done for each specific Route once.
     *
     * @param int $ttl Time-To-Live in seconds (0 to 31,536,000 / 1 year). 0 disables caching.
     * @param 'redis'|'memcached'|'file'|'apcu'|'array' $driver Cache storage driver (e.g. 'redis', 'memcached', 'file', 'apcu').
     * @param string|array<int, string>|null $varyBy Cache Key to check against (e.g. `'header:Authorization', 'query:page', 'ip', 'session', 'user_id'`).
     * @param bool $private If true, sets HTTP response header 'Cache-Control: private'.
     * @return $this
     */
    public function setCache(int $ttl = 3600, string $driver = 'redis', string|array|null $varyBy = null, bool $private = false): self
    {
        $this->c->batch('batchSetCacheRoute', $this->method, $this->routePath, $ttl, strtolower(trim($driver)), $varyBy, $private);
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
     * @param string $typeOfResponse Response Type as a Key-Value pair
     * `page:file_name` OR
     * `json:key_in_$c['d'][<key>]` OR
     * `text:plain text message` OR
     * `callback:user_defined_fn`.
     *
     * For Page Response: `->pipeResponse('page:about')` will look for the page "about.php" in "/src/funkphp/pages/compiled/about.php" and then in "/src/funkphp/pages/about.php". It also runs exit() after.
     *
     * For JSON Response: `->pipeResponse('json:users')` it will try parse "$c['d']['users']" as JSON and then return it or JSON containing Internal Server Error. It also runs exit() after.
     *
     * For Callback Response: `->pipeResponse('callback:special')` it will look for User-defined Function `test(&$c){}` in "/src/funkphp/config/functions.php" and run it and also run exit() unless that function does it first.
     *
     * For Text Response: `->pipeResponse('text:this is just plain text')` it will just echo that text back as "text/plain" Content-Type and then exit().
     *
     * @param int $httpResponseStatusCode HTTP Response Code where 200 is default if omitted.
     * @return $this
     */
    public function pipeResponse(string $typeOfResponse, int $httpResponseStatusCode = 200): self
    {
        $typeOfResponse = trim($typeOfResponse);
        $this->c->batch('batchPipeResponseRoute', $this->method, $this->routePath, $typeOfResponse, $httpResponseStatusCode);
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
     * @param 'callback:user_defined_fn'|'/^regex_pattern$/i' $regexORcb EITHER a User-defined Function defined in `/src/funkphp/config/functions.php` OR a Valid Regex pattern (e.g., "/[\d]+/")
     * @param string|null $defaultParamValueOnRegexMismatch Fallback value if validation fails
     * @return $this
     */
    public function setParamRule(string $param, string $regexORcb, string|null $defaultParamValueOnRegexMismatch = null): self
    {
        $param = strtolower(trim($param));
        $this->c->batch('batchSetParamRuleRoute', $this->method, $this->routePath, $param, $regexORcb, $defaultParamValueOnRegexMismatch);
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
    public function setHeaderAdd(string $header, string $value): self
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
    public function setHeaderRemove(string $header_to_remove): self
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
