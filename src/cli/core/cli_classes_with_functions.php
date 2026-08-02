<?php
// CLI CLASSES WITH RELATED FUNCTIONS
// This file with classes & funbctions is meant for things such as
// validation schemes, SQL query schemes, and maybe config schemes?
// It uses classes to provide high DX in the IDE when writing them!
// First it has classes that can contain all given rules for either
// all data types (RuleSetAll) or specific ones (RuleSetString, etc.),
// or even specific ones for a given data type (like RuleSetPassword which
// has related rules from just RuleSetString). Then there are "global" functions
// used as entry-points so the IDE can provide autocompletions+strict data typing!

/**
 * --------------------------------------
 * FUNKCLI CLASSES WITH RELATED FUNCTIONS
 * --------------------------------------
 * DO NOT MANUALLY EDIT THIS FILE.
 * If you are currently editing this file to see if FunkCLI will "self-heal",
 * it won't. This is a micro-framework, not your therapist. If you alter this
 * source of truth, your app will most likely crash, and your peer will know
 * you do not understand how caching and/or compiled files work.
 **/


/**
 * Converts exploded dot-notation path segments into a compiled PHP $c array access string.
 * Differentiates between quoted string keys ("'5'" -> ['5']) and unquoted integer keys ("5" -> [5]).
 *
 * @param array $explodedDotNotationPathString Array of path segments from explode('.', $path)
 * @return string Compiled PHP array access code, e.g. "$c['shared']['stuff'][5] ?? null"
 */
function cli_bracketfy(array $explodedDotNotationPathString): string
{
    $bracketed = '';
    foreach ($explodedDotNotationPathString as $segment) {
        $segment = trim((string)$segment);
        if ($segment === '') {
            continue;
        }
        // 1. Explicitly wrapped in single or double quotes -> string key (e.g., '5' => ['5'])
        if (
            (str_starts_with($segment, "'") && str_ends_with($segment, "'") && strlen($segment) >= 2) ||
            (str_starts_with($segment, '"') && str_ends_with($segment, '"') && strlen($segment) >= 2)
        ) {
            $unquoted = substr($segment, 1, -1);
            $bracketed .= "['" . addslashes($unquoted) . "']";
        }
        // 2. Unquoted integer -> literal integer index (e.g., 5 => [5])
        elseif (preg_match('/^-?\d+$/', $segment)) {
            $bracketed .= "[{$segment}]";
        }
        // 3. Unquoted identifier -> standard string key (e.g., shared => ['shared'])
        else {
            $bracketed .= "['" . addslashes($segment) . "']";
        }
    }
    return $bracketed;
}

/**
 * Validates that a given object strictly adheres to the expected RuleSet class structure
 * by verifying all required properties and methods via Reflection.
 *
 * @param mixed  $ruleSetInstance     The object to validate
 * @param array  $validationErrWarns Reference to error/warning collector array
 * @param string $fieldKey           The field key name (for context in error messages)
 * @return bool True if valid structure, false if any property/method is missing
 */
function cli_validate_ruleset_class(mixed $ruleSetInstance, array &$validationErrWarns, string $fieldKey): bool
{
    if (!is_object($ruleSetInstance)) {
        cli_build_warning_err_list(
            $validationErrWarns,
            'cli_err',
            "Validation Key `{$fieldKey}` MUST be an Object Instance of `RuleSet`!"
        );
        return false;
    }
    $ref = new \ReflectionClass($ruleSetInstance);
    $className = $ref->getName();
    $hasErrors = false;
    // 1. Array of required properties (public, private, static, etc.)
    $requiredProperties = [
        'mixedDataType',
        'dimensionalArrayDepth',
        'dataType',
        'dataTypeCategory',
        'maxIntegerValue',
        'minIntegerValue',
        'exactIntegerValue',
        'maxArrayCountValue',
        'minArrayCountValue',
        'maxArrayCountValue',
        'minObjectCountValue',
        'minObjectCountValue',
        'exactObjectCountValue',
        'maxFloatValue',
        'minFloatValue',
        'exactFloatValue',
        'maxFilesizeValue',
        'minFilesizeValue',
        'exactFilesizeValue',
        'maxStringLength',
        'minStringLength',
        'exactStringLength',
        'inputKeyField',
        'useNullable',
        'useRequired',
        'useBail',
        'arrayType',
        'dimensionalArrayCount',
        'rules',
        'configErrors',
        'configWarnings',
        'compiledErrors',
        'mergedErrorsBesdiesDataType',
        'extensionToMimeMap',
        'typeGuardMap',
        'setDataTypeCategory',
    ];
    foreach ($requiredProperties as $propName) {
        if (!$ref->hasProperty($propName)) {
            $hasErrors = true;
            cli_build_warning_err_list(
                $validationErrWarns,
                'cli_err',
                "RuleSet instance for Key `{$fieldKey}` (Class `{$className}`) is MISSING Rquired Property `\${$propName}`!"
            );
        }
    }
    // 2. Array of required methods (public, private, protected, static)
    // Easily add or adjust method names here as you expand RuleSet!
    $requiredMethods = [
        0 => 'setDatatype',
        1 => 'bail',
        2 => 'nullable',
        3 => 'input_key_field',
        4 => 'required',
        5 => 'callback',
        6 => 'keys_in_array_null_allowed',
        7 => 'keys_in_array_null_allowed_exact_count',
        8 => 'keys_in_array_not_null',
        9 => 'keys_in_array_not_null_exact_count',
        10 => 'keys_in_array_list',
        11 => 'keys_in_array_associative',
        12 => 'keys_in_array_depths',
        13 => 'elements_in_array_are_all',
        14 => 'min',
        15 => 'max',
        16 => 'between',
        17 => 'size',
        18 => 'starts_with',
        19 => 'ends_with',
        20 => 'contains',
        21 => 'doesnt_start_with',
        22 => 'doesnt_end_with',
        23 => 'doesnt_contain',
        24 => 'in_allowed',
        25 => 'in_disallowed',
        26 => 'not_in_allowed',
        27 => 'not_in_disallowed',
        28 => 'in',
        29 => 'not_in',
        30 => 'min_mb',
        31 => 'max_mb',
        32 => 'between_mb',
        33 => 'size_mb',
        34 => 'regex',
        35 => 'not_regex',
        36 => 'mac_address',
        37 => 'lowercase',
        38 => 'uppercase',
        39 => 'lowercase_mb',
        40 => 'uppercase_mb',
        41 => 'uid',
        42 => 'slug',
        43 => 'base64',
        44 => 'not_base64',
        45 => 'base32',
        46 => 'base58',
        47 => 'base64url',
        48 => 'hexadecimal',
        49 => 'md5',
        50 => 'sha1',
        51 => 'sha256',
        52 => 'sha384',
        53 => 'sha512',
        54 => 'octal',
        55 => 'binary',
        56 => 'pem',
        57 => 'ip',
        58 => 'ipv4',
        59 => 'ipv6',
        60 => 'json',
        61 => 'ascii',
        62 => 'ascii_printable',
        63 => 'utf8',
        64 => 'color',
        65 => 'single_char',
        66 => 'single_char_mb',
        67 => 'starts_with_mb',
        68 => 'ends_with_mb',
        69 => 'contains_mb',
        70 => 'doesnt_start_with_mb',
        71 => 'doesnt_end_with_mb',
        72 => 'doesnt_contain_mb',
        73 => 'date',
        74 => 'date_after',
        75 => 'date_after_or_equal',
        76 => 'date_before',
        77 => 'date_before_or_equal',
        78 => 'date_equals',
        79 => 'date_format',
        80 => 'date_in',
        81 => 'date_timezone',
        82 => 'encoding',
        83 => 'password',
        84 => 'password_uncompromised',
        85 => 'email_web',
        86 => 'phone',
        87 => 'gte',
        88 => 'gt',
        89 => 'lte',
        90 => 'lt',
        91 => 'same',
        92 => 'different',
        93 => 'multiple_of',
        94 => 'single_digit',
        95 => 'digits',
        96 => 'min_digits',
        97 => 'max_digits',
        98 => 'digits_between',
        99 => 'decimal',
        100 => 'checked',
        101 => 'unchecked',
        102 => 'exists',
        103 => 'unique',
        104 => 'unique_except',
        105 => 'file_min',
        106 => 'file_max',
        107 => 'file_between',
        108 => 'file_size',
        109 => 'file_extensions',
        110 => 'file_mimes',
        111 => 'file_image',
        112 => 'file_dimensions',
        113 => 'file_dpi',
        114 => 'file_encoding',
        115 => 'validateSetDataTypeParameters',
        116 => 'validateStringDataConversion',
        117 => 'validateRuleUsage',
        118 => 'validateRuleMultipleValues',
    ];
    foreach ($requiredMethods as $methodName) {
        if (!$ref->hasMethod($methodName)) {
            $hasErrors = true;
            cli_build_warning_err_list(
                $validationErrWarns,
                'cli_err',
                "RuleSet instance for Key `{$fieldKey}` (Class `{$className}`) is MISSING Required Method `{$methodName}()`!"
            );
        }
    }
    return !$hasErrors;
}

/**
 * Normalizes and converts human-readable file size units to raw bytes.
 *
 * @param int|float $size Size value.
 * @param string $unit Unit string ('B', 'KB', 'MB', 'GB', 'PT').
 * @return int|false Bytes count on success, or false if invalid unit or negative size.
 * Used by file_RULE(s) since they need to output valid max, min, between, size, file sizes!
 */
function cli_parseFileSizeToBytes(int|float $size, string $unit): int|false
{
    if ($size < 0) {
        return false;
    }
    $normalizedUnit = strtoupper(trim($unit));
    $multiplier = match ($normalizedUnit) {
        'B', 'BYTES', 'BYTE' => 1,
        'KB', 'K'            => 1024,
        'MB', 'M'            => 1024 * 1024,
        'GB', 'G'            => 1024 * 1024 * 1024,
        'PB', 'P'            => 1024 * 1024 * 1024 * 1024,
        default              => false
    };
    if ($multiplier === false) {
        return false;
    }
    return (int) round($size * $multiplier);
}

/**
 * Builds a Prefix Trie from validation schema keys.
 * Fully supports multi-dimensional array wildcards (*.*, a.*.b.*) while catching
 * structural conflicts (mixing associative keys with wildcards at the same node level).
 *
 * @param array  $allValidationKeys Array of raw string keys from VALIDATION
 * @param array  $validationErrWarns Reference to error/warning collector
 * @param string $file Validation file name
 * @param string $fn   Validation function name
 * @return array Array containing ['parsedKeys' => map, 'trie' => rootNode]
 */
function cli_validate_key_trie(array $allValidationKeys, array &$validationErrWarns, string $file = '', string $fn = ''): array
{
    $trie = [
        'children'     => [],
        'has_wildcard' => false,
        'has_named'    => false,
        'is_terminal'  => false,
        'keys'         => [],
    ];
    $parsedKeyMap = [];
    $segmentRegex = '/^[^\x00-\x1F\x7F]+$/u';
    $multiplieWildcardRegex = '/[*]{2,}/';
    // INITIAL VALIDATION KEY String(s) Check (not starting ending with ., not containing '\.*')
    foreach ($allValidationKeys as $avK) {
        $fieldKey = (string) $avK;
        if ($fieldKey === '') {
            cli_build_warning_err_list($validationErrWarns, 'cli_err', "`Empty Key ('')` in VALIDATION Array FOUND!");
        }
        if (preg_match_all($multiplieWildcardRegex, $fieldKey)) {
            cli_build_warning_err_list($validationErrWarns, 'cli_err', "Validation Key `{$fieldKey}` contains Consecutive Wildcards (`**` or more) when they should be separated by Single Dots (e.g `*.*` as Root OR `key.*` after Root)!");
        }
        // Split on unescaped dots: 'grid\.*.*.*' -> ['grid\.*', '*', '*']
        // and then for each segment; Unescape dots to get the literal key name for segment checking
        // to validate it contains only valid characters (see $segmentRegex above)
        $rawSegments = preg_split('/(?<!\\\\)\./', $fieldKey);
        foreach ($rawSegments as $segment) {

            $literalSegment = str_replace('\.', '.', $segment);
            if ($literalSegment === '') {
                cli_build_warning_err_list($validationErrWarns, 'cli_err', "Validation Key `{$fieldKey}` contains an Empty Dot Segment (e.g., `key..subKey` or trailing dot)!");
            }
            if (!preg_match($segmentRegex, $literalSegment)) {
                cli_build_warning_err_list($validationErrWarns, 'cli_err', "Validation Key Segment `{$literalSegment}` in `{$fieldKey}` contains `Invalid Control Characters` and/or `Invalid Bytes`. Please ONLY use characters that are JSON-valid characters!");
            }
        }
        if (str_contains($fieldKey, '\.*')) {
            cli_build_warning_err_list($validationErrWarns, 'cli_err', "`Escaped Segment Syntax on Wildcard ('\.*')` - NOT Supported yet - in VALIDATION Array FOUND. No further Validation will be done until this is resolved!");
            return [
                'parsedKeys' => $parsedKeyMap,
                'trie'       => $trie,
            ];
        }
        if (str_starts_with($fieldKey, '.') || (str_ends_with($fieldKey, '.') && !str_ends_with($fieldKey, '\.'))) {
            cli_build_warning_err_list($validationErrWarns, 'cli_err', "`Starting/ending with . (dot) in Key ({$fieldKey})` in VALIDATION Array FOUND. No further Validation will be done until this is resolved!");
            return [
                'parsedKeys' => $parsedKeyMap,
                'trie'       => $trie,
            ];
        }
        // Begin validating edge-case where * is used as root key meaning all other keys
        // must start with *. (e.g. "*" exists so next must be "*.<key_or_another_*_for_nesting>")
        if (str_starts_with($avK, '*')) {
            foreach ($allValidationKeys as $currentKey) {
                if (!str_starts_with($currentKey, "*.") && $currentKey !== "*") {
                    cli_build_warning_err_list($validationErrWarns, 'cli_err', "Validation Key `$currentKey` must start with `*.` in VALIDATION Array! When `*` is used as a Root Key, the entire Data Root is a Numbered Array!");
                }
            }
        }
        // Wildcard * exists, starting or not - then checks for when it ends with and when doesn't end with it
        if (str_contains($fieldKey, '*')) {
            // 1. Parent key check: If key contains '*' but does NOT end with '*' (e.g. 'bigger.names.*.name')
            if (!str_ends_with($fieldKey, '*')) {
                // Find the parent path before the last unescaped dot
                if (preg_match('/^(.*)(?<!\\\\)\.([^.]+)$/', $fieldKey, $matches)) {
                    $parentKey = $matches[1];
                    if (!in_array($parentKey, $allValidationKeys, true)) {
                        cli_build_warning_err_list(
                            $validationErrWarns,
                            'cli_err',
                            "Validation Key `{$fieldKey}` requires Parent Array Key `{$parentKey}` in VALIDATION Array as an `Array Datatype`!"
                        );
                    }
                }
            }
            // 2. Leaf wildcard subkey check: If key ends with '*' (e.g. 'bigger.names.*')
            // meaning it must have subkeys that starts with it (e.g. 'bigger.names.*.subkey')
            // and there CANNOT exist 'bigger.names' as 'bigger.names.*' informs that it is numbered array!
            // But we have edge-case "*" since that is NOT ".*" and we need to check against ".*" of any level
            // (the count of .* that is; .* or .*.* and so on) using $arrConflict so we use preg_replace from
            // the end of a string
            if (str_ends_with($fieldKey, '*')) {
                $hasSubkey = false;
                $prefix = $fieldKey . '.';
                foreach ($allValidationKeys as $k) {
                    if ($k !== $fieldKey && str_starts_with($k, $prefix)) {
                        $hasSubkey = true;
                        break;
                    }
                }
                if (!$hasSubkey) {
                    cli_build_warning_err_list(
                        $validationErrWarns,
                        'cli_err',
                        "Validation Key `{$fieldKey}` requires at least one `Subkey` (e.g., `{$fieldKey}.subKey`) to exist in the VALIDATION Array!"
                    );
                }
                $arrConflict = preg_replace(
                    '/(\.\*)+$/',
                    '',
                    $fieldKey
                );
                //echo "FIELD KEY: {$fieldKey} | CONFLICT?: $arrConflict:\n";
                if (in_array($arrConflict, $allValidationKeys) && $fieldKey !== '*') {
                    cli_build_warning_err_list(
                        $validationErrWarns,
                        'cli_err',
                        "Validation Key `{$fieldKey}` conflicts with `{$arrConflict}` since `{$fieldKey}` indicates a Numbered Array in that VALIDATION Key!"
                    );
                }
            }
            // 3.  No wildcard * at the end so does its supposed parent then exist? There exists *
            // but does not end with it so there must exist a parent which ends then with .*!
            if (!str_ends_with($fieldKey, "*")) {
                $lastSplit = strrpos($fieldKey, ".");
                $firstPart = substr($fieldKey, 0, $lastSplit);
                if (!in_array($firstPart, $allValidationKeys)) {
                    cli_build_warning_err_list($validationErrWarns, 'cli_err', "Validation Key `{$fieldKey}` requires the Key `$firstPart` to exist in the VALIDATION Array!");
                }
            }
        }
    }
    cli_stop_from_warn_err_list($validationErrWarns, "B`Please Review (" . count($validationErrWarns) . ")` R`Errors` above for the Validation Function `{$fn}` in `/src/funkphp/data/validation/$file.php`!");

    // Here the raw segments in array keys in VALIDATION key all having valid formatting
    // so now we will be looking for structural conflicts and/or missing "Parent Keys"/"SubKeys"
    // Regex for each dot-separated segment (rejects control characters & null bytes)
    // This validates that each dot-separated segment is valid, otherwise it will not be used in the built Trie
    $segmentRegex = '/^[^\x00-\x1F\x7F]+$/u';
    foreach ($allValidationKeys as $rawKey) {
        $fieldKey = (string) $rawKey;
        $rawSegments = preg_split('/(?<!\\\\)\./', $fieldKey);
        // Unescape dots for literal segment names: 'grid\.*' -> 'grid.*'
        $segments = array_map(static function ($s) {
            return str_replace('\.', '.', $s);
        }, $rawSegments);
        // Count wildcard depth for compiler scoping ($v_0, $v_1, etc.)
        $wildcardDepth = 0;
        foreach ($segments as $seg) {
            if ($seg === '*') {
                $wildcardDepth++;
            }
        }
        $parsedKeyMap[$fieldKey] = [
            'segments'       => $segments,
            'wildcard_depth' => $wildcardDepth,
        ];
        // 4. Traverse & Build Trie to detect Object vs. Array conflicts at each node level
        $currentNode = &$trie;
        $pathSoFar = [];
        foreach ($segments as $segment) {
            $pathSoFar[] = $segment;
            $isWildcard = ($segment === '*');
            if ($isWildcard) {
                // Conflict check: Parent node level already has named associative children!
                if ($currentNode['has_named']) {
                    $parentPath = implode('.', array_slice($pathSoFar, 0, -1));
                    $parentDisplay = ($parentPath === '') ? 'Root' : "{$parentPath}";
                    cli_build_warning_err_list(
                        $validationErrWarns,
                        'cli_err',
                        "Structural Conflict `{$fieldKey}`: Node `{$parentDisplay}` mixes named Object/Array Keys with an Array Wildcard (`*`). A node cannot be both an Associative Object/Array and a Numbered List!"
                    );
                }
                $currentNode['has_wildcard'] = true;
            } else {
                // Conflict check: Parent node level is already marked as an array wildcard!
                if ($currentNode['has_wildcard']) {
                    $parentPath = implode('.', array_slice($pathSoFar, 0, -1));
                    $parentDisplay = ($parentPath === '') ? 'Root' : "{$parentPath}";
                    $pathSoFarImploded = implode('.', $pathSoFar);
                    cli_build_warning_err_list(
                        $validationErrWarns,
                        'cli_err',
                        "Structural Conflict `{$fieldKey}`: Node `{$parentDisplay}.*` is defined as an Array Wildcard (`*`) and conflicts with `{$pathSoFarImploded}`. Cannot add Named Associative Subkey `{$segment}` directly to an Array Wildcard Level!"
                    );
                }
                $currentNode['has_named'] = true;
            }
            if (!isset($currentNode['children'][$segment])) {
                $currentNode['children'][$segment] = [
                    'children'     => [],
                    'has_wildcard' => false,
                    'has_named'    => false,
                    'is_terminal'  => false,
                    'keys'         => [],
                ];
            }
            $currentNode = &$currentNode['children'][$segment];
        }
        $currentNode['is_terminal'] = true;
        $currentNode['keys'][] = $fieldKey;
    }
    return [
        'parsedKeys' => $parsedKeyMap,
        'trie'       => $trie,
    ];
}

/* CLI Function that compiles a returned array with the following starting point:
        return
        [
        '<CONFIG'> => ['stop_all_on_first_error' => false],
         'VALIDATION'=> [
            'key' => ruleFunctionObject()->with()->rules('RuleValue','CustomErrorMsgOrDefaultIsUsed'),
            'key.subKey' => ruleFunctionObject()->with()->rules()->andMaybeOtherRules(),
                        ]
        ];
        and then returns a single string that contains the optimized validation version!
*/
function cli_compile_validation_schema($validation_schema_array, $file, $fn, $customComment = '', $DEBUG = false): string
{
    if (
        !isset($validation_schema_array)
        || !is_array($validation_schema_array)
        || array_is_list($validation_schema_array)
        || count($validation_schema_array) === 0
        || !array_key_exists('<CONFIG>', $validation_schema_array)
        || !array_key_exists('stop_all_on_first_error', $validation_schema_array['<CONFIG>'])
        || !array_key_exists('VALIDATION', $validation_schema_array)
        || count($validation_schema_array['VALIDATION']) === 0
        || array_is_list($validation_schema_array['VALIDATION'])
    ) {
        cli_err("\$validation_schema_array must be a Non-Empty Associative Array containg the main Keys: `<CONFIG>` & `VALIDATION` which themselves CANNOT be Empty Arrays but must be both Associative Arrays!");
    }
    $validationConfig = $validation_schema_array['<CONFIG>'] ?? null;
    $validationKey = $validation_schema_array['VALIDATION'] ?? null;
    ksort($validationKey);

    $compiledValidationRules = [];
    $compiledFunctionCommentAbove = [];
    $compiledFunctionCommentAbove[] = ($DEBUG ? "/**\n * ## WITH DEBUG ##\n * Compiled Validation $file=>$fn" : "/**\n * Compiled Validation $file=>$fn");
    $compiledValidationSchema = '';
    global $tablesAndRelationshipsFile;
    global $connectionsFile;

    // --- STEP 1: Validate Keys and RuleSet Instance Values ---
    // Extract all array keys beforehand to allow O(1) or O(N) structural checks
    // Regex for each dot-separated segment (rejects control characters & null bytes)
    $segmentRegex = '/^[^\x00-\x1F\x7F]+$/u';
    $validationErrWarns = [];
    $allValidationKeys = array_map('strval', array_keys($validationKey));

    // 1A. Build Prefix Trie and validate structural key conflicts
    $parsedKeySegmentsMap = cli_validate_key_trie($allValidationKeys, $validationErrWarns, $file, $fn);
    cli_stop_from_warn_err_list($validationErrWarns, "B`Please Review (" . count($validationErrWarns) . ")` R`Errors` above for the Validation Function `{$fn}` in `/src/funkphp/data/validation/$file.php`! (Start with R`Structural Conflict Errors` - if any)");

    // Now we iterate through each ['VALIDATION'] => ['key' as => data() <- This should be the case or add err!]
    foreach ($validationKey as $rawKey => $ruleSetInstance) {
        // Handle PHP auto-casting '123' to integer 123
        $fieldKey = (string) $rawKey;
        //Parse dot-notation segments while respecting escaped dots (\.)
        $segments = preg_split('/(?<!\\\\)\./', $fieldKey);

        // Check 1: Must strictly be an instance of RuleSet and that it contains all needed properties & methods!
        if (!($ruleSetInstance instanceof \RuleSet)) {
            $valueType = is_object($ruleSetInstance) ? get_class($ruleSetInstance) : gettype($ruleSetInstance);
            cli_build_warning_err_list($validationErrWarns, 'cli_err', "Validation Key `{$fieldKey}` must be an instance of `RuleSet` initialized via `data()`. Data Type `{$valueType}` was given instead.");
        } elseif (!cli_validate_ruleset_class($ruleSetInstance, $validationErrWarns, $fieldKey)) {
            cli_stop_from_warn_err_list($validationErrWarns, "B`Please Review (" . count($validationErrWarns) . ")` R`Errors` above for the Validation Function `{$fn}` in `/src/funkphp/data/validation/$file.php`! (Start with R`Structural Conflict Errors` - if any)");
        }
        // Edge-case check: Root key * exists meaning it should be dataType 'array' with max(), size() OR between() rule
        // to know limit of array elements. This is required for all array-types for security and performance reasons!
        else if ($fieldKey === '*') {
            // FIX THAT HERE ALSO: fix helper function that validates that each RuleSet Class instance has all properties+methods available!
        }
    }
    cli_stop_from_warn_err_list($validationErrWarns, "B`Please Review (" . count($validationErrWarns) . ")` R`Errors` above for the Validation Function `{$fn}` in `/src/funkphp/data/validation/$file.php`! (Start with R`Structural Conflict Errors` - if any)");

    /* FROM GEMINI LLM; might be true or NOT for exists(), unique(), unique_except() DB-related ONLY rules!!!
    How the Compiler Replaces Placeholders dynamically
    When the FunkPHP compiler processes the rules:
    It reads $this->rules['exists']['databaseConnection'] (e.g. 'mysql_pdo' vs 'mongo_docs').
    It looks up 'mysql_pdo' in conns.php to check the 'driver' key (pdo_mysql, mysqli, mongodb, redis, etc.).
    It swaps out {{##DB_EXISTS_NOT_FOUND##}} with driver-specific PHP:
        pdo_mysql / pdo_pgsql:
        $c->db('mysql_pdo')->prepare("SELECT 1 FROM usersWHEREemail = ? LIMIT 1")->execute([{{##INPUT##}}]) && $stmt->fetchColumn() === false

        mysqli:
        $c->db('mysql_native')->query("SELECT 1 FROM usersWHEREemail = '" . $c->db('mysql_native')->real_escape_string({{##INPUT##}}) . "' LIMIT 1")->num_rows === 0

        mongodb:
        $c->db('mongo_docs')->users->countDocuments(['email' => {{##INPUT##}}]) === 0
    */

    // The final Validation Schema including all the
    // optimized flattened if(){}else{} and goto labels: code!
    return $compiledValidationSchema;
}

/* ACTUAL RULE-RELATED CLASSES (FOR VALIDATION PURPOSES! - Compiler Functions Are Before These!) */
/*
 * List of Country Codes so you can use `CountryCode::<COUNTRY> for phone() rules!`
 */
final class CountryCode
{
    public const UK = '44';
    public const USA = '1';
    public const Algeria = '213';
    public const Andorra = '376';
    public const Angola = '244';
    public const Anguilla = '1264';
    public const Antigua_Barbuda = '1268';
    public const Argentina = '54';
    public const Armenia = '374';
    public const Aruba = '297';
    public const Australia = '61';
    public const Austria = '43';
    public const Azerbaijan = '994';
    public const Bahamas = '1242';
    public const Bahrain = '973';
    public const Bangladesh = '880';
    public const Barbados = '1246';
    public const Belarus = '375';
    public const Belgium = '32';
    public const Belize = '501';
    public const Benin = '229';
    public const Bermuda = '1441';
    public const Bhutan = '975';
    public const Bolivia = '591';
    public const Bosnia_Herzegovina = '387';
    public const Botswana = '267';
    public const Brazil = '55';
    public const Brunei = '673';
    public const Bulgaria = '359';
    public const Burkina_Faso = '226';
    public const Burundi = '257';
    public const Cambodia = '855';
    public const Cameroon = '237';
    public const Canada = '1';
    public const Cape_Verde_Islands = '238';
    public const Cayman_Islands = '1345';
    public const Central_African_Republic = '236';
    public const Chile = '56';
    public const China = '86';
    public const Colombia = '57';
    public const Comoros = '269';
    public const Congo = '242';
    public const Cook_Islands = '682';
    public const Cote_d_Ivoire = '225';
    public const Costa_Rica = '506';
    public const Croatia = '385';
    public const Cuba = '53';
    public const Cyprus_North = '90392';
    public const Cyprus_South = '357';
    public const Czech_Republic = '42';
    public const Denmark = '45';
    public const Djibouti = '253';
    public const Dominica = '1809';
    public const Dominican_Republic = '1809';
    public const Ecuador = '593';
    public const Egypt = '20';
    public const El_Salvador = '503';
    public const Equatorial_Guinea = '240';
    public const Eritrea = '291';
    public const Estonia = '372';
    public const Ethiopia = '251';
    public const Falkland_Islands = '500';
    public const Faroe_Islands = '298';
    public const Fiji = '679';
    public const Finland = '358';
    public const France = '33';
    public const French_Guiana = '594';
    public const French_Polynesia = '689';
    public const Gabon = '241';
    public const Gambia = '220';
    public const Georgia = '995';
    public const Germany = '49';
    public const Ghana = '233';
    public const Gibraltar = '350';
    public const Greece = '30';
    public const Greenland = '299';
    public const Grenada = '1473';
    public const Guadeloupe = '590';
    public const Guam = '671';
    public const Guatemala = '502';
    public const Guinea = '224';
    public const Guinea_Bissau = '245';
    public const Guyana = '592';
    public const Haiti = '509';
    public const Honduras = '504';
    public const Hong_Kong = '852';
    public const Hungary = '36';
    public const Iceland = '354';
    public const India = '91';
    public const Indonesia = '62';
    public const Iran = '98';
    public const Iraq = '964';
    public const Ireland = '353';
    public const Israel = '972';
    public const Italy = '39';
    public const Jamaica = '1876';
    public const Japan = '81';
    public const Jordan = '962';
    public const Kazakhstan = '7';
    public const Kenya = '254';
    public const Kiribati = '686';
    public const North_Korea = '850';
    public const South_Korea = '82';
    public const Korea_North = '850';
    public const Korea_South = '82';
    public const Kuwait = '965';
    public const Kyrgyzstan = '996';
    public const Laos = '856';
    public const Latvia = '371';
    public const Lebanon = '961';
    public const Lesotho = '266';
    public const Liberia = '231';
    public const Libya = '218';
    public const Liechtenstein = '417';
    public const Lithuania = '370';
    public const Luxembourg = '352';
    public const Macao = '853';
    public const Macedonia = '389';
    public const Madagascar = '261';
    public const Malawi = '265';
    public const Malaysia = '60';
    public const Maldives = '960';
    public const Mali = '223';
    public const Malta = '356';
    public const Marshall_Islands = '692';
    public const Martinique = '596';
    public const Mauritania = '222';
    public const Mayotte = '269';
    public const Mexico = '52';
    public const Micronesia = '691';
    public const Moldova = '373';
    public const Monaco = '377';
    public const Mongolia = '976';
    public const Montserrat = '1664';
    public const Morocco = '212';
    public const Mozambique = '258';
    public const Myanmar = '95';
    public const Namibia = '264';
    public const Nauru = '674';
    public const Nepal = '977';
    public const Netherlands = '31';
    public const New_Caledonia = '687';
    public const New_Zealand = '64';
    public const Nicaragua = '505';
    public const Niger = '227';
    public const Nigeria = '234';
    public const Niue = '683';
    public const Norfolk_Islands = '672';
    public const Northern_Marianas = '670';
    public const Norway = '47';
    public const Oman = '968';
    public const Palau = '680';
    public const Panama = '507';
    public const Papua_New_Guinea = '675';
    public const Paraguay = '595';
    public const Peru = '51';
    public const Philippines = '63';
    public const Poland = '48';
    public const Portugal = '351';
    public const Puerto_Rico = '1787';
    public const Qatar = '974';
    public const Reunion = '262';
    public const Romania = '40';
    public const Russia = '7';
    public const Rwanda = '250';
    public const San_Marino = '378';
    public const Sao_Tome_Principe = '239';
    public const Saudi_Arabia = '966';
    public const Senegal = '221';
    public const Serbia = '381';
    public const Seychelles = '248';
    public const Sierra_Leone = '232';
    public const Singapore = '65';
    public const Slovak_Republic = '421';
    public const Slovenia = '386';
    public const Solomon_Islands = '677';
    public const Somalia = '252';
    public const South_Africa = '27';
    public const Spain = '34';
    public const Sri_Lanka = '94';
    public const St_Helena = '290';
    public const Saint_Kitts = '1869';
    public const Saint_Lucia = '1758';
    public const Saint_Helena = '290';
    public const St_Kitts = '1869';
    public const St_Lucia = '1758';
    public const Sudan = '249';
    public const Suriname = '597';
    public const Swaziland = '268';
    public const Sweden = '46';
    public const Switzerland = '41';
    public const Syria = '963';
    public const Taiwan = '886';
    public const Tajikstan = '7';
    public const Thailand = '66';
    public const Togo = '228';
    public const Tonga = '676';
    public const Trinidad_Tobago = '1868';
    public const Tunisia = '216';
    public const Turkey = '90';
    public const Turkmenistan = '993';
    public const Turks_Caicos_Islands = '1649';
    public const Tuvalu = '688';
    public const Uganda = '256';
    public const Ukraine = '380';
    public const United_Arab_Emirates = '971';
    public const Uruguay = '598';
    public const Uzbekistan = '998';
    public const Vanuatu = '678';
    public const Vatican_City = '379';
    public const Venezuela = '58';
    public const Vietnam = '84';
    public const Virgin_Islands_British = '1284';
    public const British_Virgin_Islands = '1284';
    public const Virgin_Islands_US = '1340';
    public const US_Virgin_Islands = '1340';
    public const Wallis_Futuna = '681';
    public const Yemen_North = '969';
    public const Yemen_South = '967';
    public const North_Yemen = '969';
    public const South_Yemen = '967';
    public const Zambia = '260';
    public const Zimbabwe = '263';
}
class RuleSet
{
    /*
     * Comprehensive Extension to MIME Types Mapping Dictionary. - WILL PROBABLY BE MOVED & EXTENDED!!!
     */
    private static array $extensionToMimeMap = [
        // Images
        // Standard & Modern Web Images
        'jpg'     => ['image/jpeg', 'image/pjpeg'],
        'jpeg'    => ['image/jpeg', 'image/pjpeg'],
        'jpe'     => ['image/jpeg', 'image/pjpeg'],
        'png'     => ['image/png', 'image/x-png'],
        'gif'     => ['image/gif'],
        'bmp'     => ['image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-ms-bmp'],
        'webp'    => ['image/webp'],
        'avif'    => ['image/avif', 'image/avif-sequence'],
        'heic'    => ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'],
        'heif'    => ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'],
        'tiff'    => ['image/tiff', 'image/x-tiff'],
        'tif'     => ['image/tiff', 'image/x-tiff'],
        'ico'     => ['image/x-icon', 'image/vnd.microsoft.icon', 'image/icon', 'image/ico', 'application/ico'],
        'svg'     => ['image/svg+xml', 'text/xml', 'text/plain'],
        // Next-Gen & Advanced Formats (JPEG 2000, JPEG XL, etc.)
        'jp2'     => ['image/jp2', 'image/jpx', 'image/jpm'],
        'j2k'     => ['image/jp2', 'image/j2c', 'image/jpc'],
        'jpf'     => ['image/jp2', 'image/jpx'],
        'jpx'     => ['image/jpx', 'image/jp2'],
        'jxl'     => ['image/jxl'],
        // Specialty / Graphics Editing Formats
        'psd'     => ['image/vnd.adobe.photoshop', 'image/x-photoshop', 'application/x-photoshop'],
        'tga'     => ['image/x-tga', 'image/targa'],
        'pnm'     => ['image/x-portable-anymap'],
        'pbm'     => ['image/x-portable-bitmap'],
        'pgm'     => ['image/x-portable-graymap'],
        'ppm'     => ['image/x-portable-pixmap'],
        // Documents & Text
        'pdf'   => ['application/pdf', 'application/x-pdf', 'application/acrobat'],
        'txt'   => ['text/plain'],
        'rtf'   => ['text/rtf', 'application/rtf'],
        'json'  => ['application/json', 'text/json', 'text/plain'],
        'xml'   => ['application/xml', 'text/xml', 'text/plain'],
        'csv'   => ['text/csv', 'text/plain', 'text/x-comma-separated-values', 'text/comma-separated-values', 'application/vnd.ms-excel'],
        'doc'   => ['application/msword', 'application/vnd.ms-word'],
        'docx'  => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls'   => ['application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel'],
        'xlsx'  => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt'   => ['application/vnd.ms-powerpoint', 'application/mspowerpoint'],
        'pptx'  => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'odt'   => ['application/vnd.oasis.opendocument.text'],
        'ods'   => ['application/vnd.oasis.opendocument.spreadsheet'],
        'odp'   => ['application/vnd.oasis.opendocument.presentation'],
        'epub'  => ['application/epub+zip'],
        // Archives & Executables
        'zip'   => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        'rar'   => ['application/x-rar-compressed', 'application/rar', 'application/octet-stream'],
        'tar'   => ['application/x-tar'],
        'gz'    => ['application/x-gzip', 'application/gzip'],
        '7z'    => ['application/x-7z-compressed'],
        'bz2'   => ['application/x-bzip2'],
        'xz'    => ['application/x-xz'],
        // Audio
        'mp3'   => ['audio/mpeg', 'audio/mp3', 'audio/x-mpeg', 'audio/x-mp3'],
        'wav'   => ['audio/wav', 'audio/x-wav', 'audio/wave'],
        'ogg'   => ['audio/ogg', 'video/ogg', 'application/ogg'],
        'm4a'   => ['audio/m4a', 'audio/x-m4a', 'audio/mp4'],
        'flac'  => ['audio/flac', 'audio/x-flac'],
        'aac'   => ['audio/aac', 'audio/x-aac'],
        'wma'   => ['audio/x-ms-wma'],
        'opus'  => ['audio/opus'],
        // Video
        'mp4'   => ['video/mp4', 'video/x-mp4'],
        'webm'  => ['video/webm'],
        'avi'   => ['video/x-msvideo', 'video/avi', 'video/msvideo'],
        'mov'   => ['video/quicktime'],
        'mkv'   => ['video/x-matroska'],
        'wmv'   => ['video/x-ms-wmv'],
        'flv'   => ['video/x-flv'],
        'm4v'   => ['video/x-m4v'],
        '3gp'   => ['video/3gpp', 'audio/3gpp'],
        // Fonts
        'ttf'   => ['font/ttf', 'font/sfnt', 'application/x-font-ttf'],
        'otf'   => ['font/otf', 'font/sfnt', 'application/x-font-opentype'],
        'woff'  => ['font/woff', 'application/font-woff', 'application/x-font-woff'],
        'woff2' => ['font/woff2'],
        'eot'   => ['application/vnd.ms-fontobject'],
    ];
    // Valid "Data Types" (set in $dataType)
    // TODO: test all cuz these might actually be wrong sometimes when evaluated with "if(!{$guardExpression})"???
    private array $typeGuardMap = [
        // File Types
        'file'      => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'string'    => 'is_string({{##INPUT##}})',
        'checkbox' => '(is_scalar({{##INPUT##}}) && {{##INPUT##}} !== null)',
        'integer'   => 'is_int({{##INPUT##}})',
        'float'     => 'is_float({{##INPUT##}})',
        'boolean'   => 'is_bool({{##INPUT##}})',
        'null'   => 'is_null({{##INPUT##}})',
        'array'     => 'is_array({{##INPUT##}})',
        'object'    => 'is_object({{##INPUT##}})',
        'number'    => 'is_numeric({{##INPUT##}})',
        'scalar'     => 'is_scalar({{##INPUT##}})',
    ];
    private array $setDataTypeCategory = [
        'string' => 'string',
        'integer' => 'numeric',
        'number' => 'numeric',
        'float' => 'numeric',
        'boolean' => 'boolean',
        'object' => 'object',
        'array' => 'array',
        'checkbox' => 'checkbox',
        'file' => 'file',
        'null' => 'null'
    ];
    private ?array $mixedDataType = [];
    private ?string $dataType = null;
    private ?string $dataTypeCategory = null;
    private ?string $inputKeyField = null; // This replaces the {{##INPUT_KEY##}} with this set string value instead of the 'VALIDATION' => ['key' =>...]
    private ?int $maxIntegerValue = null;
    private ?int $minIntegerValue = null;
    private ?int $exactIntegerValue = null;
    private ?int $maxArrayCountValue = null;
    private ?int $minArrayCountValue = null;
    private ?int $exactArrayCountValue = null;
    private ?int $maxObjectCountValue = null;
    private ?int $minObjectCountValue = null;
    private ?int $exactObjectCountValue = null;
    private ?float $maxFloatValue = null;
    private ?float $minFloatValue = null;
    private ?float $exactFloatValue = null;
    private ?float $maxFilesizeValue = null;
    private ?float $minFilesizeValue = null;
    private ?float $exactFilesizeValue = null;
    private ?int $maxStringLength = null;
    private ?int $minStringLength = null;
    private ?int $exactStringLength = null;
    private ?bool $useNullable = false;
    private ?bool $useRequired = false;
    private ?bool $useBail = false;
    private ?string $arrayType = null;
    private ?array $dimensionalArrayCount = [];
    private ?int $dimensionalArrayDepth = 0;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Tracks warnings that will still compile but might be crucial to reconsider due to them
    public array $configWarnings = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
    // ENTRY POINT METHOD WHERE YOU SET DATA TYPE with OR without optional parameters
    public function setDatatype(string $dataType, string $customErrorMsg = '', ?string $customErrorMsgForParameters = ''): self
    {
        if (isset($this->dataType)) {
            $this->configErrors[] = 'A Data Type is already set: `' . $this->dataType . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (trim($dataType) === '') {
            $this->configErrors[] = 'No Data Type provided?!: `' . $dataType . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (trim($dataType) === 'arrays') {
            $this->configErrors[] = 'Data Type `arrays` for Input Key `{{##INPUT_KEY##}}` means `Multi-Dimensional Array` (e.g. `$input[0][0]`) where each new array depth is separated by a comma (`,`) after `arrays:` must have between() and/or size() values separated by each comma (e.g. `arrays:10,10-15` means `depth 1` has `size(10)` while `depth 2` has `between(10,15)` in their respective count values)! VALIDATION Key must have matching numbers of `*` separated with a single dot `.` (except for first one if starting from root: `*.*.key` vs `key.*.*`)!';
            return $this;
        }
        // Optional extra parameters when setting data type such as ("datatype:<parameter1&|parameter2>")
        // 1. Check if type has parameters (e.g., "array:1,5", "string:1-10")
        $parsed = $this->validateSetDataTypeParameters($dataType);
        if ($parsed === false) {
            return $this; // Syntax error recorded in configErrors
        }
        // 2. If parameterized, handle shortcuts
        if (is_array($parsed)) {
            [$prefix, $paramString] = $parsed;
            // Parse parameters using your multi-value validator
            $arrVals = $this->validateRuleMultipleValues($dataType, $paramString, ['string'], false, true);
            if ($arrVals === false) {
                return $this;
            }
            // --- ARRAYS: (Dimensional array support like arrays:1-5,5-5) ---
            // --- ARRAYS: (Multi-dimensional array support like arrays:1-5,5 or arrays:5,5-10) ---
            if ($prefix === 'arrays') {
                $guardConditions = [];
                $min = null;
                $max = nulL;
                $exactSize = null;
                foreach ($arrVals as $levelIndex => $arVal) {
                    // Build depth accessor: level 0 = {{##INPUT##}}, level 1 = {{##INPUT##}}[0], level 2 = {{##INPUT##}}[0][0]
                    $accessor = '{{##INPUT##}}' . str_repeat('[0]', $levelIndex);
                    // Level-0 only checks is_array; level-1+ also checks isset() and is_array()
                    if ($levelIndex === 0) {
                        $guardConditions[] = "is_array({$accessor})";
                    } else {
                        $guardConditions[] = "(isset({$accessor}) && is_array({$accessor}))";
                    }
                    // Check if level parameter is a Range (e.g., "1-5")
                    if (str_contains($arVal, '-')) {
                        if (substr_count($arVal, '-') !== 1) {
                            $this->configErrors[] = "Identified Valid Data Type (arrays:) has invalid range format (`{$arVal}`) for Input Key `{{##INPUT_KEY##}}`!";
                            return $this;
                        }
                        [$minStr, $maxStr] = explode('-', $arVal, 2);
                        if (!$this->validateStringDataConversion($minStr, 'integer') || !$this->validateStringDataConversion($maxStr, 'integer')) {
                            $this->configErrors[] = "Identified Valid Data Type (arrays:) could not convert String Range (`{$arVal}`) to Integer for `$dataType` on Input Key `{{##INPUT_KEY##}}`!";
                            return $this;
                        }
                        $min = (int)$minStr;
                        $max = (int)$maxStr;
                        if ($min >= $max) {
                            $this->configErrors[] = "Identified Valid Data Type (arrays:) min boundary (`{$min}`) cannot be greater than or equal to max boundary (`{$max}`) in `{$arVal}` for Input Key `{{##INPUT_KEY##}}`. Use a Single Integer if you want an exact size instead!";
                            return $this;
                        }
                        $guardConditions[] = "(count({$accessor}) >= {$min} && count({$accessor}) <= {$max})";
                    }
                    // Single value exact size (e.g., "5")
                    else {
                        if (!$this->validateStringDataConversion($arVal, 'integer')) {
                            $this->configErrors[] = "Identified Valid Data Type (arrays:) could not convert String Value (`{$arVal}`) to Integer for `$dataType` on Input Key `{{##INPUT_KEY##}}`!";
                            return $this;
                        }
                        $exactSize = (int)$arVal;
                        $guardConditions[] = "count({$accessor}) === {$exactSize}";
                    }
                    $this->dimensionalArrayCount[] = $arVal;
                }
                $this->dimensionalArrayDepth = count($this->dimensionalArrayCount);
                $guardExpression = implode(' && ', $guardConditions);
                $dataType = 'array';
                $this->dataType = $dataType;
                $this->dataTypeCategory = $this->setDataTypeCategory[$dataType];
                $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Must be of data type `multi-dimensional array`!";
                $error = addcslashes($error, "'\\");
                $this->rules[$dataType] = ['error' => $error, 'values' => ['type' => $dataType, 'dimensionalArrayDepth' =>  count($this->dimensionalArrayCount), 'dimensionalArrayCount' =>  $this->dimensionalArrayCount]];
                $this->rules[$dataType]['compiled'] = "if(!({$guardExpression})) {\n" .
                    "    {{##DEBUG##}}{{##ERRORS##}}['{$dataType}'] = '{$error}';\n" .
                    "    {{##GOTO_STOP_ALL##}}\n" .
                    "    {{##GOTO_BAIL##}}\n" .
                    "    {{##GOTO_NEXT_LOOP##}}\n" .
                    "    {{##GOTO_EXIT_LOOP##}}\n" .
                    "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                    "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                    "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                    "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                    "    {{##GOTO_NEXT_RULE##}}\n" .
                    "    {{##GOTO_END_FIELD##}}\n" .
                    "}";
                return $this;
            }
            // --- ALL OTHER PARAMETERIZED TYPES (array:, string:, integer:, float:, file:, number:) ---
            $allowedShortcutTypes = ['array', 'string', 'integer', 'float', 'file', 'number', 'object'];
            if (!in_array($prefix, $allowedShortcutTypes, true)) {
                $this->configErrors[] = "Invalid Parameterized Data Type chosen: `{$dataType}` for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
            // Convert string arguments to appropriate numeric types for bounds checks
            $numericVals = [];
            // Determine whether parameters should be parsed as float or int (files & numbers are floats)
            $targetType = ($prefix === 'float' || $prefix === 'number' || $prefix === 'file') ? 'float' : 'integer';
            foreach ($arrVals as $arVal) {
                if (!$this->validateStringDataConversion($arVal, $targetType)) {
                    $this->configErrors[] = "Identified Valid Data Type (`{$prefix}:`) could not convert parameter (`{$arVal}`) to " . ucfirst($targetType) . " for `$dataType` for Input Key `{{##INPUT_KEY##}}`!";
                    return $this;
                }
                // Cast dynamically: (float)"5.5" => 5.5, (int)"5" => 5
                $numericVals[] = ($targetType === 'float') ? (float)$arVal : (int)$arVal;
            }
            if (count($numericVals) > 2) {
                $this->configErrors[] = "Identified Valid Data Type (`{$prefix}:`) has too many parameters: `{$dataType}`. Provide 1 parameter (exact size) or 2 parameters (min, max via the `between` Rule) for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
            // Register the base data type first & then apply automatic size or range shortcut rules
            $this->setDatatype($prefix, $customErrorMsg);
            if (count($numericVals) === 2) {
                return $this->between($numericVals[0], $numericVals[1], ((!empty($customErrorMsgForParameters)) ? $customErrorMsgForParameters : ('`{{#INPUT_KEY#}}` must be between `' . $numericVals[0] . '` and `' . $numericVals[1] . '`!')));
            }
            return $this->size($numericVals[0], ((!empty($customErrorMsgForParameters)) ? $customErrorMsgForParameters : ('`{{#INPUT_KEY#}}` must have an exact size of `{$numericVals[0]}`!')));
        }
        // ANY OTHER DATA TYPE THAT IS VALID BASED ON $typeGuardMap?
        if (!isset($this->typeGuardMap[$dataType])) {
            $this->configErrors[] = 'Invalid Data Type chosen: `' . $dataType . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // Do not allow Parameters Only Custom Error Message when only setting data type without optional parameters!
        if (trim($customErrorMsgForParameters) !== '') {
            $this->configErrors[] = 'Cannot use Custom Error Message for Parameters (`' .  $customErrorMsgForParameters  . '`) when setting only Data Type and not its Parameters:`' . $dataType . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->dataType = $dataType;
        $this->dataTypeCategory = $this->setDataTypeCategory[$dataType];
        $error = ((!empty($customErrorMsg)) ? $customErrorMsg : "`{{##INPUT_KEY##}}` must be of data type `{$dataType}`.");
        $error = addcslashes($error, "'\\");
        $guardExpression = $this->typeGuardMap[$dataType];
        $this->rules[$dataType] = ['error'    => $error, 'values' => ['type' => $dataType]];
        $this->rules[$dataType]['compiled'] =  "if(!{$guardExpression}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['{$dataType}'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        return $this;
    }
    // SOME HELPER FUNCTIONS ALMOST ALL OTHER METHODS USES
    // PRIVATE HELPER: Parses "datatype:param1,param2" syntax safely
    private function validateSetDataTypeParameters(string $rawDataType): array|false|null
    {
        if (!str_contains($rawDataType, ':')) {
            return null; // Standard type without parameters (e.g. "array", "string")
        }
        if (substr_count($rawDataType, ':') !== 1) {
            $this->configErrors[] = "Identified Valid Data Type but it has too many colons, only use one: `{$rawDataType}`!";
            return false;
        }
        [$prefix, $rawParams] = explode(':', $rawDataType, 2);
        $prefix = strtolower(trim($prefix));
        if ($prefix !== 'arrays') {
            $rawParams = str_replace('-', ',', $rawParams);
        }
        $rawParams = trim($rawParams);
        if ($rawParams === '') {
            $this->configErrors[] = "Identified Valid Data Type (`{$prefix}:`) but no parameters were provided: `{$rawDataType}`!";
            return false;
        }
        return [$prefix, $rawParams];
    }
    private function validateStringDataConversion(string $stringToDataConvert, string $newDataTypeToConvertTo): bool|self
    {
        $targetType = strtolower(trim($newDataTypeToConvertTo));
        if ($targetType === '' || !in_array($targetType, ['integer', 'float', 'boolean', 'string', 'array', 'object'], true)) {
            $this->configErrors[] = "Rule Data Type could not be validated due to incorrect internal use of `\$this->validateStringDataConversion` in `class RuleSet` in `/src/cli/core/cli_classes_with_functions.php` whose first argument must be one of the valid Data Types to convert to:`" . implode(', ', ['integer', 'float', 'boolean']) . "` before setting the Data Type Rule on Input Key: `{{##INPUT_KEY##}}`! Please report this bug to `github.com|codeberg.org/WebbKodsFrilansaren/FunkPHP`!";
            return $this;
        }
        if (trim($stringToDataConvert) === '') {
            return false;
        }
        return match ($targetType) {
            'integer' => filter_var($stringToDataConvert, FILTER_VALIDATE_INT) !== false,
            'float'   => filter_var($stringToDataConvert, FILTER_VALIDATE_FLOAT) !== false,
            'boolean' => filter_var($stringToDataConvert, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
            'string', 'array', 'object' => true,
            default   => false,
        };
    }
    /**
     * Validates Rule Config checking 3 things: data type set first, no duplicate and no conflicts
     *
     * @param string $ruleName The name of the rule being added (e.g., 'starts_with', 'checked')
     * @param array $conflictingRules List of existing rules that cannot coexist with this rule
     * @param array|string $allowedDataTypes Restricted data types (empty array = allowed on all data types)
     * @return bool Returns true if configuration is valid; false if a config error was added
     */
    private function validateRuleUsage(
        string $ruleName,
        array $conflictingRules = [],
        array|string $allowedDataTypes = [],
        array|string $allowedDataTypeCategories = []
    ): bool {
        // 1. Ensure a Data Type Rule has been set first
        // 2. Restrict to specific Data Types (if provided)
        // 3. Prevent Duplicate Rules
        // 4. Prevent Conflicting Rules
        if ($this->dataType === null) {
            $this->configErrors[] = "Cannot add Rule `{$ruleName}` before setting the Data Type Rule on Input Key: `{{##INPUT_KEY##}}`!";
            return false;
        }
        if (!empty($allowedDataTypes)) {
            $allowed = (array) $allowedDataTypes;
            if (!in_array($this->dataType, $allowed, true)) {
                $allowedList = implode(', ', $allowed);
                $this->configErrors[] = "Rule `{$ruleName}` can Only Be Used on Data Types: [{$allowedList}]. Current Data Type: `{$this->dataType}` on Input Key: `{{##INPUT_KEY##}}`.";
                return false;
            }
        }
        if (!empty($allowedDataTypeCategories)) {
            $allowed = (array) $allowedDataTypeCategories;
            if (!in_array($this->dataTypeCategory, $allowed, true)) {
                $allowedList = implode(', ', $allowed);
                $this->configErrors[] = "Rule `$ruleName` is Only Valid for Data Type Categories: [{$allowedList}], but Data Type `{$this->dataType}` was selected for Input Key `{{##INPUT_KEY##}}`!";
                return false;
            }
        }
        if (isset($this->rules[$ruleName])) {
            $this->configErrors[] = "Rule `{$ruleName}` is Already Used for Input Key: `{{##INPUT_KEY##}}`!";
            return false;
        }
        foreach ($conflictingRules as $conflict) {
            if (isset($this->rules[$conflict])) {
                $this->configErrors[] = "Rule `{$ruleName}` conflicts with Existing Rule `{$conflict}` on Input Key: `{{##INPUT_KEY##}}`!";
                return false;
            }
        }
        return true;
    }
    /**
     * Validates and normalizes values supplied to a rule method during builder time.
     *
     * @param string $ruleName Name of the rule being configured (e.g. 'contains')
     * @param mixed $values Raw values passed into the rule method (array, comma-separated string, scalar)
     * @param array $allowedDataTypes Allowed PHP data types for each parameter (empty array = allow all)
     * @return array|false Normalized array of unique values on success, or false on validation failure
     */
    private function validateRuleMultipleValues(
        string $ruleName,
        mixed $values,
        array $allowedDataTypes = [],
        bool $valuesCanBeEmpty = false,
        bool $valuesCanBeSame = false
    ): array|false {
        // 1. Normalize into an array
        if (is_array($values)) {
            $normalized = array_values($values);
        } elseif (is_string($values)) {
            $normalized = array_map('trim', explode(',', $values));
        } else {
            $normalized = [$values];
        }
        // Filter out empty strings resulting from comma splitting
        $normalized = array_values(array_filter($normalized, fn($v) => $v !== ''));
        // 2. Ensure non-empty list of values
        if (empty($normalized)) {
            if ($valuesCanBeEmpty) {
                return [];
            }
            $this->configErrors[] = "Rule `{$ruleName}` Requires at least One Value for Input Key `{{##INPUT_KEY##}}`!";
            return false;
        }
        $uniqueValues = [];
        foreach ($normalized as $val) {
            // Strict duplicate check
            if (!$valuesCanBeSame && in_array($val, $uniqueValues, true)) {
                $valFormatted = is_scalar($val) || $val === null ? var_export($val, true) : json_encode($val);
                $this->configErrors[] = "Rule `{$ruleName}` contains Duplicate ({$valFormatted}) Parameter Values for Input Key `{{##INPUT_KEY##}}`!";
                return false;
            }
            $uniqueValues[] = $val;
        }
        // 3. Validate Data Types of parameters (if restricted)
        if (!empty($allowedDataTypes)) {
            $normalizeType = function (mixed $t): string {
                if ($t === null) {
                    return 'null';
                }
                return match (strtolower((string)$t)) {
                    'integer', 'int'   => 'int',
                    'boolean', 'bool'  => 'bool',
                    'double', 'float'  => 'float',
                    'null'             => 'null',
                    default            => strtolower((string)$t)
                };
            };
            $normalizedAllowedTypes = array_map($normalizeType, (array)$allowedDataTypes);
            foreach ($uniqueValues as $index => $val) {
                $rawType = gettype($val);
                $type = $normalizeType($rawType);
                if (!in_array($type, $normalizedAllowedTypes, true)) {
                    $allowedList = implode(', ', $normalizedAllowedTypes);
                    $valFormatted = is_scalar($val) || $val === null ? var_export($val, true) : json_encode($val);
                    $this->configErrors[] = "Rule `{$ruleName}` value at index [{$index}] must be of type [{$allowedList}], `{$type} => {$valFormatted}` given for Input Key `{{##INPUT_KEY##}}`!";
                    return false;
                }
            }
        }
        return $uniqueValues;
    }

    // ACTUAL RULES (some are data type-restricted like some for only strings, some for only arrays, etc.)
    /* ALL DATA TYPES RULES */
    public function bail(): self
    {
        if (!$this->validateRuleUsage('bail')) {
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'values' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }
    public function nullable(): self
    {
        if (!$this->validateRuleUsage('nullable')) {
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'values' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }
    public function input_key_field(string $fieldToReplaceINPUT_KEYWith): self
    {
        if (!$this->validateRuleUsage('input_key_field')) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('input_key_field', $fieldToReplaceINPUT_KEYWith, ['string']);
        if (!$validated) {
            return $this;
        }
        $this->inputKeyField = strtolower(trim($validated[0]));
        $this->rules['input_key_field'] = ['error' => null, 'values' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }
    public function required(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('required')) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "`{{##INPUT_KEY##}}` is REQUIRED!";
        $error = addcslashes($error, "'\\");
        $this->rules['required'] = ['error' => $error];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['required'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = '" . $error . "';";
        $this->useRequired = true;
        return $this;
    }
    /**
     * Validates field input using a custom user-defined function from `/src/funkphp/config/functions.php` OR Any `Fully Qualified Name (e.g \Name\Name2\Name3)`. You do NOT need to add the starting slash!
     *
     * The callback function receives the Global `$c` variable as its first argument and the input value as its second:
     * `function_name($c, $value)`. Returning `false` triggers a validation error. Any `other returned value than false is ignored`!
     *
     * @param string $functionName Name of the function defined in `/src/funkphp/config/functions.php`.
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function callback(string $functionName, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('callback', [], [], [])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('callback', $functionName, ['string']);
        if (!$validated) {
            return $this;
        }
        $func = trim($validated[0]);
        // Developer config check: Validate function identifier syntax
        if (empty($func) || !preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\\\\-\x7f-\xff]*$/', $func)) {
            $this->configErrors[] = "Rule `callback` Parameter `'{$func}'` is NOT a valid PHP function identifier for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` failed custom validation!";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(\\{$func}(\$c,{{##INPUT##}}) === false) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['callback'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['callback'] = [
            'values' => "$func",
            'callback' => "\\$func",
            'error'        => $error,
            'compiled'     => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['callback'] = '" . $error . "';";
        return $this;
    }

    /* ARRAY-ONLY RULES */
    public function keys_in_array_null_allowed(array|string $keysThatMustExistCanBeNull, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('keys_in_array_null_allowed', [], [], ['array'])) {
            return $this;
        }
        $keys = $this->validateRuleMultipleValues('keys_in_array_null_allowed', $keysThatMustExistCanBeNull, ['string', 'null', 'NULL']);
        if (!$keys) {
            return $this;
        }
        // 4. Normalize & Validate Input Parameters
        if (is_string($keysThatMustExistCanBeNull)) {
            $keys = array_filter(array_map('trim', explode(',', $keysThatMustExistCanBeNull)), fn($k) => $k !== '');
        } else {
            $keys = array_values($keysThatMustExistCanBeNull);
        }
        if (empty($keys)) {
            $this->configErrors[] = 'Rule `keys_in_array_null_allowed` requires at least one required key for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Build Failure Condition (Fails if any required key is missing from array keys)
        $exportedKeys = var_export($keys, true);
        $condition = 'array_diff(' . $exportedKeys . ', array_keys({{##INPUT##}})) !== []';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is missing required keys.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_null_allowed'] = [
            'values' => $keys,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['keys_in_array_null_allowed'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_null_allowed'] = '" . $error . "';";
        return $this;
    }
    public function keys_in_array_null_allowed_exact_count(array|string $keysThatMustExistCanBeNull, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('keys_in_array_null_allowed_exact_count', [], [], ['array'])) {
            return $this;
        }
        $keys = $this->validateRuleMultipleValues('keys_in_array_null_allowed_exact_count', $keysThatMustExistCanBeNull, ['string', 'null', 'NULL']);
        if (!$keys) {
            return $this;
        }
        // 4. Normalize & Validate Input Parameters
        if (is_string($keysThatMustExistCanBeNull)) {
            $keys = array_filter(array_map('trim', explode(',', $keysThatMustExistCanBeNull)), fn($k) => $k !== '');
        } else {
            $keys = array_values($keysThatMustExistCanBeNull);
        }
        if (empty($keys)) {
            $this->configErrors[] = 'Rule `keys_in_array_null_allowed_exact_count` requires at least one required key for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Build Failure Condition
        $expectedCount = count($keys);
        $exportedKeys  = var_export($keys, true);
        // O(1) count check short-circuits array_diff execution on size mismatch
        $condition = 'count({{##INPUT##}}) !== ' . $expectedCount . ' || array_diff(' . $exportedKeys . ', array_keys({{##INPUT##}})) !== []';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain exactly {$expectedCount} specific keys.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_null_allowed_exact_count'] = [
            'values'  => $keys,
            'expected_count' => $expectedCount,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['keys_in_array_null_allowed_exact_count'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_null_allowed_exact_count'] = '" . $error . "';";
        return $this;
    }
    public function keys_in_array_not_null(array|string $keysThatMustExistNotNull, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('keys_in_array_not_null', [], [], ['array'])) {
            return $this;
        }
        $keys = $this->validateRuleMultipleValues('keys_in_array_not_null', $keysThatMustExistNotNull, ['string', 'null', 'NULL']);
        if (!$keys) {
            return $this;
        }
        // 4. Normalize & Validate Input Parameters
        if (is_string($keysThatMustExistNotNull)) {
            $keys = array_filter(array_map('trim', explode(',', $keysThatMustExistNotNull)), fn($k) => $k !== '');
        } else {
            $keys = array_values($keysThatMustExistNotNull);
        }
        if (empty($keys)) {
            $this->configErrors[] = 'Rule `keys_in_array_not_null` requires at least one required key for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Build Failure Condition (Opcode-level isset check)
        $issetArgs = implode(', ', array_map(fn($k) => '{{##INPUT##}}[' . var_export($k, true) . ']', $keys));
        $condition = '!isset(' . $issetArgs . ')';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is missing required non-null keys.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_not_null'] = [
            'values' => $keys,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['keys_in_array_not_null'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_not_null'] = '" . $error . "';";
        return $this;
    }
    public function keys_in_array_not_null_exact_count(array|string $keysThatMustExistNotNull, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('keys_in_array_not_null_exact_count', [], [], ['array'])) {
            return $this;
        }
        $keys = $this->validateRuleMultipleValues('keys_in_array_not_null_exact_count', $keysThatMustExistNotNull, ['string', 'null', 'NULL']);
        if (!$keys) {
            return $this;
        }
        // 4. Normalize & Validate Input Parameters
        if (is_string($keysThatMustExistNotNull)) {
            $keys = array_filter(array_map('trim', explode(',', $keysThatMustExistNotNull)), fn($k) => $k !== '');
        } else {
            $keys = array_values($keysThatMustExistNotNull);
        }
        if (empty($keys)) {
            $this->configErrors[] = 'Rule `keys_in_array_not_null_exact_count` requires at least one required key for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Build Failure Condition ($O(1)$ short-circuit count check followed by isset)
        $expectedCount = count($keys);
        $issetArgs = implode(', ', array_map(fn($k) => '{{##INPUT##}}[' . var_export($k, true) . ']', $keys));
        $condition = 'count({{##INPUT##}}) !== ' . $expectedCount . ' || !isset(' . $issetArgs . ')';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain exactly {$expectedCount} specified non-null keys.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_not_null_exact_count'] = [
            'values'  => $keys,
            'expected_count' => $expectedCount,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['keys_in_array_not_null_exact_count'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_not_null_exact_count'] = '" . $error . "';";
        return $this;
    }
    public function keys_in_array_list(string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'array' data type & conflicts with keys_in_array_associative
        if (!$this->validateRuleUsage('keys_in_array_list', ['keys_in_array_associative'], ['array'], [])) {
            return $this;
        }
        // 2. Classify the array structure on the builder
        $this->arrayType = 'list';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a Numbered Array!";
        $error = addcslashes($error, "'\\");
        $this->rules['array']['compiled'] = str_replace("if(!is_array({{##INPUT##}}))", "if(!is_array({{##INPUT##}}) || !array_is_list({{##INPUT##}}))", $this->rules['array']['compiled']);
        $this->rules['array']['error'] .= ' ' . $error;
        return $this;
    }
    public function keys_in_array_associative(string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'array' data type & conflicts with keys_in_array_list
        if (!$this->validateRuleUsage('keys_in_array_associative', ['keys_in_array_list'], ['array'], [])) {
            return $this;
        }
        // 2. Classify the array structure on the builder
        $this->arrayType = 'associative';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be an Associative Array!";
        $error = addcslashes($error, "'\\");
        // !empty() ensures an empty array [] passes as a valid empty map instead of failing array_is_list()
        $this->rules['array']['compiled'] = str_replace(
            "if(!is_array({{##INPUT##}}))",
            "if(!is_array({{##INPUT##}}) || (!empty({{##INPUT##}}) && array_is_list({{##INPUT##}})))",
            $this->rules['array']['compiled']
        );
        $this->rules['array']['error'] .= ' ' . $error;
        return $this;
    }
    public function keys_in_array_depths(array $pathsWhereEachDotIsNextDepthLevel, string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'array' data type
        if (!$this->validateRuleUsage('keys_in_array_depths', [], ['array'], [])) {
            return $this;
        }
        if (empty($pathsWhereEachDotIsNextDepthLevel)) {
            $this->configErrors[] = '`keys_in_array_depths` requires at least one path string.';
            return $this;
        }
        $compiledPathChecks = [];
        // 2. Build short-circuiting PHP checks for each path
        foreach ($pathsWhereEachDotIsNextDepthLevel as $path) {
            $segments = array_filter(array_map('trim', explode('.', $path)), 'strlen');
            if (empty($segments)) {
                continue;
            }
            $stepChecks = [];
            $currentAccess = '{{##INPUT##}}';
            $totalSegments = count($segments);
            $i = 0;
            foreach ($segments as $segment) {
                $i++;
                $exportedKey = var_export($segment, true);
                // Check if key exists at current level
                $stepChecks[] = "array_key_exists({$exportedKey}, {$currentAccess})";
                // Advance pointer deeper into the array structure
                $currentAccess .= "[{$exportedKey}]";
                // If there are more segments to traverse, ensure current node is an array
                if ($i < $totalSegments) {
                    $stepChecks[] = "is_array({$currentAccess})";
                }
            }
            // Wrap individual path checks in parentheses
            $compiledPathChecks[] = '(' . implode(' && ', $stepChecks) . ')';
        }
        if (empty($compiledPathChecks)) {
            $this->configErrors[] = '`keys_in_array_depths` provided paths contained no valid segments.';
            return $this;
        }
        // Combine all path conditions (all requested paths must exist)
        $fullCondition = implode(' && ', $compiledPathChecks);
        if (count($compiledPathChecks) > 1) {
            $fullCondition = "({$fullCondition})";
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is missing required nested array key paths: " . implode(', ', $pathsWhereEachDotIsNextDepthLevel);
        $error = addcslashes($error, "'\\");
        // 3. Store compiled rule
        $this->rules['keys_in_array_depths'] = [
            'values'    => $pathsWhereEachDotIsNextDepthLevel,
            'paths_compiled' => $compiledPathChecks,
            'error'    => $error,
            'compiled' => "if(!({$fullCondition})) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['keys_in_array_depths'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_depths'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that every element within the target array matches the specified data type.
     *
     * @param 'distinct'|'distinct_strict'|'distinct_ignore_case'|'string'|'string-not-empty'|'int'|'int_only_positive'|'int_only_negative'|'float'|'float_only_positive'|'float_only_negative'|'int_and_float'|'int_and_float_only_positive'|'int_and_float_only_negative'|'numeric'|'numeric_only_positive'|'numeric_only_negative'|'boolean'|'boolean_only_true'|'boolean_only_false'|'scalar'|'scalar_not_empty'|'null'|'object'|'array'|'array-not-empty'|'list'|'list-not-empty'|'associative'|'associative-not-empty'|'callback:'|string $arrayElementSingleDataType Target element data type or a Custom `callback:` user-defined in `/src/funkphp/config/functions.php` OR Any `Fully Qualified Name (e.g \Name\Name2\Name3)` - you do NOT need to add the starting slash! If you use Custom `callback:`, each element will be passed as the SECOND ARGUMENT after the Global $c variable, as such: `callback($c,$element)` and if the returning value is `false` then it is considered to have failed the validation for that Input! (any other returned value than false is ignored when using `callback:`)
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function elements_in_array_are_all(string $arrayElementSingleDataType, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('elements_in_array_are_all', [], [], ['array'])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('elements_in_array_are_all', $arrayElementSingleDataType, ['string']);
        if (!$validated) {
            return $this;
        }
        $targetType = strtolower(trim($validated[0]));
        $useCallback = false;
        $useDistinct = false;
        // Map allowed target types to their compiled negative PHP evaluation condition
        $typeConditions = [
            'string'                  => '!is_string($elem)',
            'string-not-empty'        => '!is_string($elem) || trim($elem) === \'\'',
            'int'                 => '!is_int($elem)',
            'int_only_positive'   => '!is_int($elem) || $elem <= 0',
            'int_only_negative'   => '!is_int($elem) || $elem >= 0',
            'float'                   => '!is_float($elem)',
            'float_only_positive'     => '!is_float($elem) || $elem <= 0',
            'float_only_negative'     => '!is_float($elem) || $elem >= 0',
            'int_and_float'                  => '(!is_int($elem) && !is_float($elem))',
            'int_and_float_only_positive'    => '(!is_int($elem) && !is_float($elem)) || $elem <= 0',
            'int_and_float_only_negative'    => '(!is_int($elem) && !is_float($elem)) || $elem >= 0',
            'numeric'                 => '!is_numeric($elem)',
            'numeric_only_positive'   => '!is_numeric($elem) || $elem <= 0',
            'numeric_only_negative'   => '!is_numeric($elem) || $elem >= 0',
            'boolean'                 => '!is_bool($elem)',
            'boolean_only_true'       => '!is_bool($elem) || $elem !== true',
            'boolean_only_false'      => '!is_bool($elem) || $elem !== false',
            'scalar'           => '!is_scalar($elem)',
            'scalar_not_empty' => '!is_scalar($elem) || (is_string($elem) && trim($elem) === \'\')',
            'null'                    => '$elem !== null',
            'object'                  => '!is_object($elem)',
            'array'                   => '!is_array($elem)',
            'array-not-empty'         => '!is_array($elem) || count($elem) === 0',
            'list'                    => '!is_array($elem) || !array_is_list($elem)',
            'list-not-empty'          => '!is_array($elem) || !array_is_list($elem) || count($elem) === 0',
            'associative'             => '!is_array($elem) || array_is_list($elem)',
            'associative-not-empty'   => '!is_array($elem) || array_is_list($elem) || count($elem) === 0',
        ];
        // Developer config check: Reject unrecognized data types
        $callBackCondition = '';
        $callbackUsed = null;
        if (str_starts_with($targetType, 'callback:')) {
            $useCallback = true;
            $cb = explode("callback:", $targetType)[1];
            if (trim($cb) === '') {
                $this->configErrors[] = "Rule `elements_in_array_are_all` Parameter `'{$targetType}'` is NOT a valid target data type (allowed: EITHER a custom `callback:function_name` stored in `/src/funkphp/config/functions.php` that must return false if it fails validation, OR:`" . join(",", array_keys($typeConditions)) . "`) for Input Key `{{##INPUT_KEY##}}`!";
            }
            $callbackUsed = $cb;
            $callBackCondition = "\\{$cb}(\$c,\$elem) === false";
        } else if (str_starts_with($targetType, 'distinct')) {
            $useDistinct = true;
        } else if (!array_key_exists($targetType, $typeConditions)) {
            $allowedList = implode(", ", array_keys($typeConditions));
            $this->configErrors[] = "Rule `elements_in_array_are_all` Parameter `'{$targetType}'` is NOT a valid target data type (allowed: EITHER a custom `callback:function_name` stored in `/src/funkphp/config/functions.php` that must return false if it fails validation, OR:`{$allowedList}`) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if (!$useDistinct) {
            $condition = ($useCallback ? $callBackCondition : $typeConditions[$targetType]);
            $error = !empty($customErrorMsg)
                ? $customErrorMsg
                : "Field `{{##INPUT_KEY##}}` must contain only `{$targetType}` elements!";
            $error = addcslashes($error, "'\\");
            $compiledCode = "foreach({{##INPUT##}} as \$elem) {\n" .
                "    if({$condition}) {\n" .
                "        {{##ERRORS##}}['elements_in_array_are_all'] = '{$error}';\n" .
                "        {{##GOTO_STOP_ALL##}}\n" .
                "        {{##GOTO_BAIL##}}\n" .
                "        {{##GOTO_NEXT_RULE##}}\n" .
                "        {{##GOTO_END_FIELD##}}\n" .
                "        break;\n" .
                "    }\n" .
                "}";
        }  // Starts with distinct, so now we check if
        // it is, strict, ignore_case or just distinct!
        else {
            $allowedDistinct = ['distinct', 'distinct_strict', 'distinct_ignore_case'];
            // Developer config check: Reject unrecognized distinct parameters
            if (!in_array($targetType, $allowedDistinct, true)) {
                $this->configErrors[] = "Rule `elements_in_array_are_all` Parameter `'{$targetType}'` is NOT a valid distinct mode (allowed: `distinct`, `distinct_strict`, `distinct_ignore_case`) for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
            $error = !empty($customErrorMsg)
                ? $customErrorMsg
                : "Field `{{##INPUT_KEY##}}` contains duplicate elements!";
            $error = addcslashes($error, "'\\");
            if ($targetType === 'distinct_ignore_case') {
                $compiledCode = "\$seen = [];\n" .
                    "foreach({{##INPUT##}} as \$elem) {\n" .
                    "    \$checkVal = is_string(\$elem) ? strtolower(\$elem) : \$elem;\n" .
                    "    if(in_array(\$checkVal, \$seen, false)) {\n" .
                    "        {{##ERRORS##}}['elements_in_array_are_all'] = '{$error}';\n" .
                    "        {{##GOTO_STOP_ALL##}}\n" .
                    "        {{##GOTO_BAIL##}}\n" .
                    "        {{##GOTO_NEXT_RULE##}}\n" .
                    "        {{##GOTO_END_FIELD##}}\n" .
                    "        break;\n" .
                    "    }\n" .
                    "    \$seen[] = \$checkVal;\n" .
                    "}";
            } else {
                $strictFlag = ($targetType === 'distinct_strict') ? 'true' : 'false';
                $compiledCode = "\$seen = [];\n" .
                    "foreach({{##INPUT##}} as \$elem) {\n" .
                    "    if(in_array(\$elem, \$seen, {$strictFlag})) {\n" .
                    "        {{##ERRORS##}}['elements_in_array_are_all'] = '{$error}';\n" .
                    "        {{##GOTO_STOP_ALL##}}\n" .
                    "        {{##GOTO_BAIL##}}\n" .
                    "        {{##GOTO_NEXT_RULE##}}\n" .
                    "        {{##GOTO_END_FIELD##}}\n" .
                    "        break;\n" .
                    "    }\n" .
                    "    \$seen[] = \$elem;\n" .
                    "}";
            }
        }
        $this->rules['elements_in_array_are_all'] = [
            'callback' => $useCallback,
            'callback_used' => $callbackUsed,
            'targetType' => $targetType,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['elements_in_array_are_all'] = '" . $error . "';";
        return $this;
    }

    /* MIXED DATA TYPES RULES when using RuleSetAll! */
    public function min(int|float $minValue, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('min', ['between', 'between_mb', 'size', 'min_mb', 'file_min'])) {
            return $this;
        }
        if (is_float($minValue)) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataType === 'integer'
            ) {
                $this->configErrors[] = 'Rule `min` has a Non-Integer Value `' . $minValue . '` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        if ($minValue < 0) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataType === 'file'
            ) {
                $this->configErrors[] = 'Rule `min` has a Negative Value `' . $minValue . '` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        $error = ((!empty($customErrorMsg)) ? $customErrorMsg : "Value must be at least {$minValue}.");
        $error = addcslashes($error, "'\\");
        // Branch compilation based on the selected Data Type
        switch ($this->dataType) {
            case 'string':
                $condition = "strlen({{##INPUT##}}) < {$minValue}";
                break;
            case 'integer':
            case 'float':
            case 'number':
                $condition = "{{##INPUT##}} < {$minValue}";
                break;
            case 'array':
            case 'arr':
                $condition = "count({{##INPUT##}}) < {$minValue}";
                break;
            case 'object':
                $condition = "(count(get_object_vars({{##INPUT##}})) < {$minValue}";
                break;
            case 'file':
            case 'image':
            case 'video':
            case 'audio':
                $condition = "(isset({{##INPUT##}}['size']) && ({{##INPUT##}}['size'] / 1024) < {$minValue})";
                break;
            default:
                $this->configErrors[] = "Rule `min` is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        $this->rules['min'] = [
            'error'    => $error,
            'values'    => $minValue,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['min'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['min'] = '{$error}';";
        return $this;
    }
    public function max(int|float $maxValue, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('max', ['between', 'between_mb', 'size', 'max_mb', 'file_max'])) {
            return $this;
        }
        if (is_float($maxValue)) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataType === 'integer'
            ) {
                $this->configErrors[] = 'Rule `max` has a Non-Integer Value `' . $maxValue . '` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        if ($maxValue < 0) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataType === 'file'
            ) {
                $this->configErrors[] = 'Rule `max` has a Negative Value `' . $maxValue . '` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        $error = ((!empty($customErrorMsg)) ? $customErrorMsg : "Value must be at most {$maxValue}.");
        $error = addcslashes($error, "'\\");
        // Branch compilation based on the selected Data Type
        switch ($this->dataType) {
            case 'string':
                $condition = "strlen({{##INPUT##}}) > {$maxValue}";
                break;
            case 'integer':
            case 'float':
            case 'number':
                $condition = "{{##INPUT##}} > {$maxValue}";
                break;
            case 'array':
            case 'arr':
                $condition = "count({{##INPUT##}}) > {$maxValue}";
                break;
            case 'object':
                $condition = "(count(get_object_vars({{##INPUT##}})) > {$maxValue}";
                break;
            case 'file':
            case 'image':
            case 'video':
            case 'audio':
                $condition = "(isset({{##INPUT##}}['size']) && ({{##INPUT##}}['size'] / 1024) > {$maxValue})";
                break;
            default:
                $this->configErrors[] = "Rule `max` is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        $this->rules['max'] = [
            'error'    => $error,
            'values'    => $maxValue,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['max'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['max'] = '{$error}';";
        return $this;
    }
    public function between(int|float $minVal, int|float $maxVal, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('between', ['size', 'between_mb', 'min', 'max', 'min_mb', 'max_mb', 'file_between', 'file_min', 'file_max', 'file_size'])) {
            return $this;
        }
        // 3. Range Sanity Guard ($minVal must be strictly less than $maxVal)
        if ($minVal >= $maxVal) {
            $this->configErrors[] = 'Rule `between` has invalid range `[' . $minVal . ', ' . $maxVal . ']` for Input Key `{{##INPUT_KEY##}}` where Min value must be strictly smaller than Max value. Use the Rule `size()` if need an exact value to be validated instead!';
            return $this;
        }
        // 4. Float Guard
        if (is_float($minVal) || is_float($maxVal)) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataType === 'integer'
            ) {
                $this->configErrors[] = 'Rule `between` has a Non-Integer boundary input `[' . $minVal . ', ' . $maxVal . ']` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        // 5. Negative Boundary Guard
        if ($minVal < 0 || $maxVal < 0) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataTypeCategory === 'file'
            ) {
                $this->configErrors[] = 'Rule `between` has a Negative Boundary Value (one or both) for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Value must be between {$minVal} and {$maxVal}.";
        $error = addcslashes($error, "'\\");
        // 6. Branch compilation based on selected Data Type / Category
        switch ($this->dataTypeCategory) {
            case 'string':
                $condition = "(strlen({{##INPUT##}}) < {$minVal} || strlen({{##INPUT##}}) > {$maxVal})";
                break;
            case 'numeric':
                $condition = "({{##INPUT##}} < {$minVal} || {{##INPUT##}} > {$maxVal})";
                break;
            case 'array':
                $condition = "(count({{##INPUT##}}) < {$minVal} || count({{##INPUT##}}) > {$maxVal})";
                break;
            case 'object':
                $condition = "(count(get_object_vars({{##INPUT##}})) < {$minVal} || count(get_object_vars({{##INPUT##}})) > {$maxVal})";
                break;
            case 'file':
                $condition = "(!isset({{##INPUT##}}['size']) || ({{##INPUT##}}['size'] / 1024) < {$minVal} || ({{##INPUT##}}['size'] / 1024) > {$maxVal})";
                break;
            default:
                $this->configErrors[] = "Rule `between` is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        // 7. Store compiled rules
        $this->rules['between'] = [
            'error'    => $error,
            'values'    => ['min' => $minVal, 'max' => $maxVal],
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['between'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['between'] = '" . $error . "';";
        return $this;
    }
    public function size(int|float $size, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('size', ['min', 'max', 'between', 'between_mb', 'size_mb', 'max_mb', 'min_mb', 'file_size', 'file_min', 'file_max', 'file_between'])) {
            return $this;
        }
        // 4. Float Guard
        if (is_float($size)) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataType === 'integer'
            ) {
                $this->configErrors[] = 'Rule `size` has a Non-Integer Value `' . $size . '` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        // 5. Negative Value Guard
        if ($size < 0) {
            if (
                $this->dataTypeCategory === 'string'
                || $this->dataTypeCategory === 'array'
                || $this->dataTypeCategory === 'object'
                || $this->dataTypeCategory === 'null'
                || $this->dataTypeCategory === 'boolean'
                || $this->dataTypeCategory === 'file'
            ) {
                $this->configErrors[] = 'Rule `size` has a Negative Value `' . $size . '` for Input Key `{{##INPUT_KEY##}}` which is incompatible with Data Type `' . $this->dataType . '`!';
                return $this;
            }
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Must be exactly {$size}.";
        $error = addcslashes($error, "'\\");
        // 6. Branch compilation based on selected Data Type Category
        switch ($this->dataTypeCategory) {
            case 'string':
                // Failure condition: length is NOT equal to size
                $condition = "strlen({{##INPUT##}}) !== {$size}";
                break;
            case 'numeric':
                $condition = "{{##INPUT##}} != {$size}";
                break;
            case 'array':
                $condition = "count({{##INPUT##}}) !== {$size}";
                break;
            case 'object':
                $condition = "count(get_object_vars({{##INPUT##}})) !== {$size}";
                break;
            case 'file':
                $condition = "(!isset({{##INPUT##}}['size']) || ({{##INPUT##}}['size'] / 1024) != {$size})";
                break;
            default:
                $this->configErrors[] = "Rule `size` is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        // 7. Store compiled rule
        $this->rules['size'] = [
            'error'    => $error,
            'values'    => $size,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['size'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['size'] = '" . $error . "';";
        return $this;
    }
    public function starts_with(array|string|int|float $startsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('starts_with', ['starts_with_mb'], [], ['string', 'numeric'])) {
            return $this;
        }
        $prefixes = $this->validateRuleMultipleValues('starts_with', $startsWithValues, ['string', 'integer', 'float']);
        if (!$prefixes) {
            return $this;
        }
        // 3. Build Compiled Conditions using (string)({{##INPUT##}} ?? '')
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addcslashes($prefix, "'\\");
            $conditions[] = "str_starts_with({$inputStr}, '{$escapedPrefix}')";
        }
        // If multiple prefixes, ANY match is valid: !(cond1 || cond2)
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must start with: " . implode(', ', $prefixes);
        $error = addcslashes($error, "'\\");
        $this->rules['starts_with'] = [
            'values' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['starts_with'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['starts_with'] = '" . $error . "';";
        return $this;
    }
    public function ends_with(array|string|int|float $endsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('ends_with', ['ends_with_mb'], [], ['string', 'numeric'])) {
            return $this;
        }
        $suffixes = $this->validateRuleMultipleValues('ends_with', $endsWithValues, ['string', 'integer', 'float']);
        if (!$suffixes) {
            return $this;
        }
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addcslashes($suffix, "'\\");
            $conditions[] = "str_ends_with({$inputStr}, '{$escapedSuffix}')";
        }
        // If multiple values given, ANY match passes validation
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must end with: " . implode(', ', $suffixes);
        $error = addcslashes($error, "'\\");
        $this->rules['ends_with'] = [
            'values' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['ends_with'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ends_with'] = '" . $error . "';";
        return $this;
    }
    public function contains(array|string|int|float $containsValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('contains', ['contains_mb'], [], ['string', 'numeric', 'array'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('contains', $containsValues, ['string', 'integer', 'float']);
        if (!$needles) {
            return $this;
        }
        // BUILD-TIME BRANCHING based on Data Type Category
        if ($this->dataTypeCategory === 'array') {
            // ARRAY CATEGORY: Input array must contain ALL specified values
            $inputArr = '{{##INPUT##}}';
            $conditions = [];
            foreach ($needles as $needle) {
                $exportedNeedle = var_export($needle, true);
                // Fails if ANY needle is NOT in the array
                $conditions[] = "!in_array({$exportedNeedle}, {$inputArr}, true)";
            }
            $condition = implode(' || ', $conditions);
        } else {
            // STRING/NUMERIC CATEGORY: Input string must contain AT LEAST ONE of the specified values
            $inputStr = '{{##INPUT##}}';
            $conditions = [];
            foreach ($needles as $needle) {
                $escapedNeedle = addcslashes(((string)$needle), "'\\");
                $conditions[] = "str_contains({$inputStr}, '{$escapedNeedle}')";
            }
            $condition = '!(' . implode(' || ', $conditions) . ')';
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain: " . implode(', ', $needles);
        $error = addcslashes($error, "'\\");
        $this->rules['contains'] = [
            'values'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['contains'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['contains'] = '" . $error . "';";
        return $this;
    }
    public function doesnt_start_with(array|string|int|float $startsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('doesnt_start_with', ['doesnt_start_with_mb', 'starts_with'], [], ['string', 'numeric'])) {
            return $this;
        }
        $prefixes = $this->validateRuleMultipleValues('doesnt_start_with', $startsWithValues, ['string', 'integer', 'float',]);
        if (!$prefixes) {
            return $this;
        }
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addcslashes($prefix, "'\\");
            $conditions[] = "str_starts_with({$inputStr}, '{$escapedPrefix}')";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not start with: " . implode(', ', $prefixes);
        $error = addcslashes($error, "'\\");
        $this->rules['doesnt_start_with'] = [
            'values' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['doesnt_start_with'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_start_with'] = '" . $error . "';";
        return $this;
    }
    public function doesnt_end_with(array|string|int|float $endsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('doesnt_end_with', ['doesnt_end_with_mb', 'ends_with'], [], ['string', 'numeric'])) {
            return $this;
        }
        $suffixes = $this->validateRuleMultipleValues('doesnt_end_with', $endsWithValues, ['string', 'integer', 'float',]);
        if (!$suffixes) {
            return $this;
        }
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addcslashes($suffix, "'\\");
            $conditions[] = "str_ends_with({$inputStr}, '{$escapedSuffix}')";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not end with: " . implode(', ', $suffixes);
        $error = addcslashes($error, "'\\");
        $this->rules['doesnt_end_with'] = [
            'values' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['doesnt_end_with'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_end_with'] = '" . $error . "';";
        return $this;
    }
    public function doesnt_contain(array|string|int|float $containsValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('doesnt_contain', ['doesnt_contain_mb', 'contains'], [], ['string', 'numeric', 'array'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('doesnt_contain', $containsValues, ['string', 'integer', 'float',]);
        if (!$needles) {
            return $this;
        }
        // BUILD-TIME BRANCHING based on Data Type Category
        if ($this->dataTypeCategory === 'array') {
            // ARRAY CATEGORY: Input array must NOT contain ANY of the specified values
            $inputArr = '{{##INPUT##}}';
            $conditions = [];
            foreach ($needles as $needle) {
                $exportedNeedle = var_export($needle, true);
                // Fails if ANY needle IS found in the array
                $conditions[] = "in_array({$exportedNeedle}, {$inputArr}, true)";
            }
            $condition = implode(' || ', $conditions);
        } else {
            // STRING/NUMERIC CATEGORY: Input string must NOT contain ANY of the specified values
            $inputStr = '{{##INPUT##}}';
            $conditions = [];
            foreach ($needles as $needle) {
                $escapedNeedle = addcslashes($needle, "'\\");
                $conditions[] = "str_contains({$inputStr}, '{$escapedNeedle}')";
            }
            $condition = '(' . implode(' || ', $conditions) . ')';
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not contain: " . implode(', ', $needles);
        $error = addcslashes($error, "'\\");
        $this->rules['doesnt_contain'] = [
            'values'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['doesnt_contain'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_contain'] = '" . $error . "';";
        return $this;
    }
    public function in_allowed(array|string $inAllowed, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('in_allowed', [], [], ['string', 'numeric'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('in_allowed', $inAllowed, ['string']);
        if (!$needles) {
            return $this;
        }
        $ALLOWED_LIST_ROOT = "FUNKPHP_LISTS['LISTS']['ALLOWED']";
        $listChecks = [];
        foreach ($needles as $list) {
            $escapedList = addcslashes($list, "'\\");
            $listChecks[] = "(!empty({$ALLOWED_LIST_ROOT}['{$escapedList}']) && (isset({$ALLOWED_LIST_ROOT}['{$escapedList}'][{{##INPUT_VAL##}}]) || in_array({{##INPUT_VAL##}}, {$ALLOWED_LIST_ROOT}['{$escapedList}'], true)))";
        }
        // Error if value is NOT found in any specified allowed list
        $condition = "!(" . implode(" || ", $listChecks) . ")";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is not in the allowed list.";
        $error = addcslashes($error, "'\\");
        $this->rules['in_allowed'] = [
            'values'    => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['in_allowed'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        return $this;
    }
    public function in_disallowed(array|string $inDisallowed, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('in_disallowed', [], [], ['string', 'numeric'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('in_disallowed', $inDisallowed, ['string']);
        if (!$needles) {
            return $this;
        }
        $DISALLOWED_LIST_ROOT = "FUNKPHP_LISTS['LISTS']['DISALLOWED']";
        $listChecks = [];
        foreach ($needles as $list) {
            $escapedList = addcslashes($list, "'\\");
            $listChecks[] = "(!empty({$DISALLOWED_LIST_ROOT}['{$escapedList}']) && (isset({$DISALLOWED_LIST_ROOT}['{$escapedList}'][{{##INPUT_VAL##}}]) || in_array({{##INPUT_VAL##}}, {$DISALLOWED_LIST_ROOT}['{$escapedList}'], true)))";
        }
        // Error if value is NOT found in any specified disallowed list
        $condition = "!(" . implode(" || ", $listChecks) . ")";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is not present in the disallowed list.";
        $error = addcslashes($error, "'\\");
        $this->rules['in_disallowed'] = [
            'values'    => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['in_disallowed'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        return $this;
    }
    public function not_in_allowed(array|string $notInAllowed, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('not_in_allowed', [], [], ['string', 'numeric'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('not_in_allowed', $notInAllowed, ['string']);
        if (!$needles) {
            return $this;
        }
        $ALLOWED_LIST_ROOT = "FUNKPHP_LISTS['LISTS']['ALLOWED']";
        $listChecks = [];
        foreach ($needles as $list) {
            $escapedList = addcslashes($list, "'\\");
            $listChecks[] = "(!empty({$ALLOWED_LIST_ROOT}['{$escapedList}']) && (isset({$ALLOWED_LIST_ROOT}['{$escapedList}'][{{##INPUT_VAL##}}]) || in_array({{##INPUT_VAL##}}, {$ALLOWED_LIST_ROOT}['{$escapedList}'], true)))";
        }
        // Error if value IS found in any specified allowed list
        $condition = "(" . implode(" || ", $listChecks) . ")";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` cannot be in the specified allowed list.";
        $error = addcslashes($error, "'\\");
        $this->rules['not_in_allowed'] = [
            'values'    => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['not_in_allowed'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        return $this;
    }
    public function not_in_disallowed(array|string $notInDisallowed, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('not_in_disallowed', [], [], ['string', 'numeric'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('not_in_disallowed', $notInDisallowed, ['string']);
        if (!$needles) {
            return $this;
        }
        $DISALLOWED_LIST_ROOT = "FUNKPHP_LISTS['LISTS']['DISALLOWED']";
        $listChecks = [];
        foreach ($needles as $list) {
            $escapedList = addcslashes($list, "'\\");
            $listChecks[] = "(!empty({$DISALLOWED_LIST_ROOT}['{$escapedList}']) && (isset({$DISALLOWED_LIST_ROOT}['{$escapedList}'][{{##INPUT_VAL##}}]) || in_array({{##INPUT_VAL##}}, {$DISALLOWED_LIST_ROOT}['{$escapedList}'], true)))";
        }
        // Error if value IS found in any specified disallowed list
        $condition = "(" . implode(" || ", $listChecks) . ")";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` contains a disallowed value.";
        $error = addcslashes($error, "'\\");
        $this->rules['not_in_disallowed'] = [
            'values'    => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['not_in_disallowed'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        return $this;
    }
    public function in(array|string $inValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('in', ['not_in'], [], ['string', 'numeric', 'boolean'])) {
            return $this;
        }
        $values = $this->validateRuleMultipleValues('in', $inValues, ['string', 'integer', 'boolean', 'float', null]);
        if (!$values) {
            return $this;
        }
        // 6. Build Failure Condition
        // Failure condition: input value is NOT in the allowed list
        if (count($values) <= 3) {
            $checks = [];
            foreach ($values as $val) {
                $checks[] = '{{##INPUT##}} !== ' . var_export($val, true);
            }
            $condition = '(' . implode(' && ', $checks) . ')';
        } else {
            $exportedArray = var_export($values, true);
            $condition = '!in_array({{##INPUT##}}, ' . $exportedArray . ', true)';
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Selected value for `{{##INPUT_KEY##}}` is invalid. Must be one of the following valid ones:`" . join(', ', $values) . '`!';
        $error = addcslashes($error, "'\\");
        // 7. Store Compiled Rule
        $this->rules['in'] = [
            'error'    => $error,
            'values' => $values,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['in'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['in'] = '" . $error . "';";
        return $this;
    }
    public function not_in(array|string $notInValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('not_in', ['in'], [], ['string', 'numeric', 'boolean'])) {
            return $this;
        }
        $values = $this->validateRuleMultipleValues('not_in', $notInValues, ['string', 'integer', 'boolean', 'float', null]);
        // Failure condition: input value IS in the forbidden list
        if (count($values) <= 3) {
            $checks = [];
            foreach ($values as $val) {
                $checks[] = '{{##INPUT##}} === ' . var_export($val, true);
            }
            $condition = '(' . implode(' || ', $checks) . ')';
        } else {
            $exportedArray = var_export($values, true);
            $condition = 'in_array({{##INPUT##}}, ' . $exportedArray . ', true)';
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Selected value for `{{##INPUT_KEY##}}` is forbidden. It CANNOT be any of following ones:`" . join(', ', $values) . '`!';
        $error = addcslashes($error, "'\\");
        // 7. Store Compiled Rule
        $this->rules['not_in'] = [
            'error'    => $error,
            'values' => $values,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['not_in'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['not_in'] = '" . $error . "';";
        return $this;
    }

    /* STRING-ONLY RULES */
    public function min_mb(int|float $minChars, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('min_mb', ['between', 'between_mb', 'size', 'min'], [], ['string'])) {
            return $this;
        }
        // 3. Reject Floats (Multibyte character counts cannot be fractional)
        if (is_float($minChars)) {
            $this->configErrors[] = 'Rule `min_mb` has a Non-Integer Value `' . $minChars . '` for Input Key `{{##INPUT_KEY##}}`. Multibyte String Lengths must be Whole Numbers!';
            return $this;
        }
        // 4. Reject Negative Values
        if ($minChars < 0) {
            $this->configErrors[] = 'Rule `min_mb` has a Negative Value `' . $minChars . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 6. Build Rule Error & Compiled Code
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Must be at least {$minChars} characters.";
        $error = addcslashes($error, "'\\");
        $this->rules['min_mb'] = [
            'error'    => $error,
            'values' => $minChars,
            'compiled' => "if(mb_strlen({{##INPUT##}}) < {$minChars}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['min_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['min_mb'] = '" . $error . "';";
        return $this;
    }
    public function max_mb(int|float $maxChars, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('max_mb', ['between', 'between_mb', 'size', 'max'], [], ['string'])) {
            return $this;
        }
        // 3. Reject Floats (Multibyte character counts cannot be fractional)
        if (is_float($maxChars)) {
            $this->configErrors[] = 'Rule `max_mb` has a Non-Integer Value `' . $maxChars . '` for Input Key `{{##INPUT_KEY##}}`. Multibyte String Lengths must be Whole Numbers!';
            return $this;
        }
        // 4. Reject Negative Values
        if ($maxChars < 0) {
            $this->configErrors[] = 'Rule `max_mb` has a Negative Value `' . $maxChars . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 6. Build Rule Error & Compiled Code
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Must be at most {$maxChars} characters.";
        $error = addcslashes($error, "'\\");
        $this->rules['max_mb'] = [
            'error'    => $error,
            'values' => $maxChars,
            'compiled' => "if(mb_strlen({{##INPUT##}}) > {$maxChars}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['max_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['max_mb'] = '" . $error . "';";
        return $this;
    }
    public function between_mb(int|float $minChars, int|float $maxChars, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('between_mb', ['size', 'between', 'min', 'max', 'min_mb', 'max_mb'], [], ['string'])) {
            return $this;
        }
        // 3. Reject Floats on BOTH parameters
        if (is_float($minChars) || is_float($maxChars)) {
            $this->configErrors[] = 'Rule `between_mb` has a Non-Integer Value for Input Key `{{##INPUT_KEY##}}`. Multibyte String Lengths must be Whole Numbers!';
            return $this;
        }
        // 4. Reject Negative Values & incompatible ranges
        if ($minChars < 0) {
            $this->configErrors[] = 'Rule `between_mb` has a Negative Value `' . $minChars . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if ($maxChars < 0) {
            $this->configErrors[] = 'Rule `between_mb` has a Negative Value `' . $maxChars . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if ($minChars >= $maxChars) {
            $this->configErrors[] = 'Rule `between_mb` has invalid range `[' . $minChars . ', ' . $maxChars . ']` for Input Key `{{##INPUT_KEY##}}`. MinChars must be strictly smaller than MaxChars!';
            return $this;
        }
        // 6. Build Rule Error & Compiled Code
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Must be between {$minChars} and {$maxChars} characters.";
        $error = addcslashes($error, "'\\");
        $this->rules['between_mb'] = [
            'error'    => $error,
            'values' => ['min' => $minChars, 'max' => $maxChars],
            'compiled' => "if((mb_strlen({{##INPUT##}}) < {$minChars} || mb_strlen({{##INPUT##}}) > {$maxChars})) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['between_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['between_mb'] = '" . $error . "';";
        return $this;
    }
    public function size_mb(int|float $size, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('size_mb', ['min', 'max', 'between', 'between_mb', 'size', 'max_mb', 'min_mb'], [], ['string'])) {
            return $this;
        }
        // 4. Reject Floats (Multibyte character counts cannot be fractional)
        if (is_float($size)) {
            $this->configErrors[] = 'Rule `size_mb` has a Non-Integer Value `' . $size . '` for Input Key `{{##INPUT_KEY##}}`. Multibyte String Lengths must be Whole Numbers!';
            return $this;
        }
        // 5. Reject Negative Values
        if ($size < 0) {
            $this->configErrors[] = 'Rule `size_mb` has a Negative Value `' . $size . '` for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 7. Build Rule Error & Compiled Code
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Must be exactly {$size} characters.";
        $error = addcslashes($error, "'\\");
        $this->rules['size_mb'] = [
            'error'    => $error,
            'values' => $size,
            'compiled' => "if(mb_strlen({{##INPUT##}}) !== {$size}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['size_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['size_mb'] = '" . $error . "';";
        return $this;
    }
    public function regex(string|array $regexOrRegexes, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('regex', [], [], ['string'])) {
            return $this;
        }
        // 4. Normalize to array & Validate Patterns
        $patterns = $this->validateRuleMultipleValues('regex', $regexOrRegexes, ['string']);
        if (!$patterns) {
            return $this;
        }
        $compiledConditions = [];
        foreach ($patterns as $pattern) {
            // Test regex syntax at compile-time
            try {
                if (@preg_match($pattern, '') === false) {
                    $this->configErrors[] = "Rule `regex` has Invalid Pattern Syntax `{$pattern}` (tested against Empty String!) for Input Key `{{##INPUT_KEY##}}`!";
                    return $this;
                }
            } catch (\Throwable $e) {
                $this->configErrors[] = "Rule `regex` has Invalid Pattern Syntax `{$pattern}` (tested against Empty String!) for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
            // Safely convert pattern into valid PHP code string
            $exportedPattern = var_export($pattern, true);
            // Failure condition: input DOES NOT match pattern
            $compiledConditions[] = "!preg_match({$exportedPattern}, {{##INPUT##}})";
        }
        $condition = "(" . implode(' || ', $compiledConditions) . ")";
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Field `{{##INPUT_KEY##}}` format is invalid.";
        $error = addcslashes($error, "'\\");
        // 5. Store compiled rule
        $this->rules['regex'] = [
            'error'    => $error,
            'values' => $patterns,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['regex'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['regex'] = '" . $error . "';";
        return $this;
    }
    public function not_regex(string|array $regexOrRegexes, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('not_regex', [], [], ['string'])) {
            return $this;
        }
        // 4. Normalize to array & Validate Patterns
        $patterns = $this->validateRuleMultipleValues('not_regex', $regexOrRegexes, ['string']);
        if (!$patterns) {
            return $this;
        }
        $compiledConditions = [];
        foreach ($patterns as $pattern) {
            // Test regex syntax at compile-time
            try {
                if (@preg_match($pattern, '') === false) {
                    $this->configErrors[] = "Rule `not_regex` has Invalid Pattern Syntax `{$pattern}` (tested against Empty String!) for Input Key `{{##INPUT_KEY##}}`!";
                    return $this;
                }
            } catch (\Throwable $e) {
                $this->configErrors[] = "Rule `not_regex` has Invalid Pattern Syntax `{$pattern}` (tested against Empty String!) for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
            $exportedPattern = var_export($pattern, true);
            // Failure condition: input DOES match pattern
            $compiledConditions[] = "preg_match({$exportedPattern}, {{##INPUT##}})";
        }
        $condition = "(" . implode(' || ', $compiledConditions) . ")";
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Field `{{##INPUT_KEY##}}` format is invalid.";
        $error = addcslashes($error, "'\\");
        // 5. Store compiled rule
        $this->rules['not_regex'] = [
            'error'    => $error,
            'values' => $patterns,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['not_regex'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['not_regex'] = '" . $error . "';";
        return $this;
    }
    public function mac_address(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('mac_address', [], [], ['string'])) {
            return $this;
        }
        // 4. Build Failure Condition
        $condition = 'filter_var({{##INPUT##}}, FILTER_VALIDATE_MAC) === false';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid MAC address.";
        $error = addcslashes($error, "'\\");
        // 5. Store Compiled Rule
        $this->rules['mac_address'] = [
            'error'    => $error,
            'values' => null,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['mac_address'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['mac_address'] = '" . $error . "';";
        return $this;
    }
    public function lowercase(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('lowercase', ['uppercase', 'uppercase_mb', 'lowercase_mb'], [], ['string'])) {
            return $this;
        }
        // 5. Build Failure Condition (Fails if converting to lowercase changes the value)
        $condition = 'strtolower({{##INPUT##}}) !== {{##INPUT##}}';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be lowercase.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['lowercase'] = [
            'error'    => $error,
            'values' => null,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['lowercase'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lowercase'] = '" . $error . "';";
        return $this;
    }
    public function uppercase(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('uppercase', ['lowercase', 'uppercase_mb', 'lowercase_mb'], [], ['string'])) {
            return $this;
        }
        // 5. Build Failure Condition (Fails if converting to uppercase changes the value)
        $condition = 'strtoupper({{##INPUT##}}) !== {{##INPUT##}}';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be uppercase.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['uppercase'] = [
            'error'    => $error,
            'values' => null,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['uppercase'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];

        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['uppercase'] = '" . $error . "';";
        return $this;
    }
    public function lowercase_mb(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('lowercase_mb', ['lowercase', 'uppercase_mb', 'uppercase'], [], ['string'])) {
            return $this;
        }
        // 5. Build Failure Condition (Fails if converting to lowercase_mb changes the value)
        $condition = 'mb_strtolower({{##INPUT##}}) !== {{##INPUT##}}';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be lowercase_mb.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['lowercase_mb'] = [
            'error'    => $error,
            'values' => null,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['lowercase_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lowercase_mb'] = '" . $error . "';";
        return $this;
    }
    public function uppercase_mb(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('uppercase_mb', ['lowercase', 'lowercase_mb', 'uppercase'], [], ['string'])) {
            return $this;
        }
        // 5. Build Failure Condition (Fails if converting to uppercase_mb changes the value)
        $condition = 'mb_strtoupper({{##INPUT##}}) !== {{##INPUT##}}';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be uppercase_mb.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['uppercase_mb'] = [
            'error'    => $error,
            'values' => null,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['uppercase_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];

        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['uppercase_mb'] = '" . $error . "';";
        return $this;
    }
    public function uid(array|string $formats = 'any', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('uid', [], [], ['string'])) {
            return $this;
        }
        // @see https://github.com/symfony/routing/blob/8.1/Requirement/Requirement.php Upstream Regex Source
        // @see https://github.com/symfony/uid For Symfony's robust standalone UID component
        // @link https://symfony.com/sponsor Support the Symfony project if these patterns are useful to your project!
        $patterns = [
            'v1'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-1[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v2'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-2[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v3'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v4'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v5'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v6'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-6[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v7'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'v8'      => '/^[0-9a-f]{8}-[0-9a-f]{4}-8[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'any'     => '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            'rfc4122' => '/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/i',
            'rfc9562' => '/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/i',
            'ulid'    => '/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/i',
            'base32'  => '/^[0-9A-HJKMNP-TV-Z]{26}$/i',
            'base58'  => '/^[1-9A-HJ-NP-Za-km-z]{22}$/'
        ];
        // Normalize format input
        $requested = is_string($formats)
            ? array_map('trim', explode(',', $formats))
            : array_map('trim', array_values($formats));
        if (empty($requested) || (count($requested) === 1 && $requested[0] === '')) {
            $requested = ['any'];
        }
        $selectedPatterns = [];
        $invalidFormats = [];
        foreach ($requested as $fmt) {
            $key = strtolower($fmt);
            if (isset($patterns[$key])) {
                $selectedPatterns[$key] = $patterns[$key];
            } else {
                $invalidFormats[] = $fmt;
            }
        }
        if (!empty($invalidFormats)) {
            $this->configErrors[] = 'Rule `uid` contains Invalid Format(s) (`' . implode(', ', $invalidFormats) . '`) for Input Key `{{##INPUT_KEY##}}`! Use any of the following formats: `' . implode(', ', array_keys($patterns)) . '`!';
            return $this;
        }
        // Build Failure Condition: Fails if input doesn't match ANY allowed pattern
        $matchChecks = [];
        foreach ($selectedPatterns as $pattern) {
            $matchChecks[] = '!preg_match(' . var_export($pattern, true) . ', {{##INPUT##}})';
        }
        $condition = implode(' && ', $matchChecks);
        if (count($matchChecks) > 1) {
            $condition = '(' . $condition . ')';
        }
        $fmtListStr = implode(', ', array_keys($selectedPatterns));
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is not a valid Unique Identifier ({$fmtListStr}).";
        $error = addcslashes($error, "'\\");
        // 4. Store Compiled Rule
        $this->rules['uid'] = [
            'error'    => $error,
            'values' => $formats,
            'selected_patterns' => $selectedPatterns,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['uid'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['uid'] = '" . $error . "';";
        return $this;
    }
    public function slug(string $variant = 'universal', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('slug', [], [], ['string'])) {
            return $this;
        }
        // 4. Validate Variant Option
        $variantNormalized = strtolower(trim($variant));
        if ($variantNormalized === 'ascii') {
            $pattern = '/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/';
        } elseif (in_array($variantNormalized, ['universal', 'default', 'unicode', ''], true)) {
            $pattern = '/^[^-]+(?:-[^-]+)*$/u';
        } else {
            $this->configErrors[] = 'Rule `slug` received Invalid Variant `' . $variant . '` for Input Key `{{##INPUT_KEY##}}`! (Allowed: `universal`, `ascii`)';
            return $this;
        }
        // 5. Build Failure Condition
        $condition = '!preg_match(' . var_export($pattern, true) . ', {{##INPUT##}})';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` is not a valid slug pattern.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['slug'] = [
            'error'    => $error,
            'values' => $variant,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['slug'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['slug'] = '" . $error . "';";
        return $this;
    }
    public function base64(string $variant = 'standard', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('base64', ['not_base64'], [], ['string'])) {
            return $this;
        }
        // 5. Select Pattern by Variant
        $variantNormalized = strtolower(trim($variant));
        if ($variantNormalized === 'standard' || $variantNormalized === 'default' || $variantNormalized === '') {
            $pattern = '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/';
        } elseif ($variantNormalized === 'urlsafe') {
            $pattern = '/^(?:[A-Za-z0-9_\-]{4})*(?:[A-Za-z0-9_\-]{2}==|[A-Za-z0-9_\-]{3}=)?$/';
        } elseif ($variantNormalized === 'any') {
            $pattern = '/^(?:[A-Za-z0-9+\/_\-]{4})*(?:[A-Za-z0-9+\/_\-]{2}==|[A-Za-z0-9+\/_\-]{3}=)?$/';
        } else {
            $this->configErrors[] = 'Rule `base64` received invalid variant `' . $variant . '` for Input Key `{{##INPUT_KEY##}}`! (Allowed: `standard`, `urlsafe`, `any`)';
            return $this;
        }
        // 6. Build Failure Condition (Fails if input is NOT a valid base64 string)
        $condition = '!preg_match(' . var_export($pattern, true) . ', {{##INPUT##}})';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid base64 encoded string.";
        $error = addcslashes($error, "'\\");
        // 7. Store Compiled Rule
        $this->rules['base64'] = [
            'error'    => $error,
            'values' => $variant,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['base64'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['base64'] = '" . $error . "';";
        return $this;
    }
    public function not_base64(string $variant = 'standard', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('not_base64', ['base64'], [], ['string'])) {
            return $this;
        }
        // 5. Select Pattern by Variant
        $variantNormalized = strtolower(trim($variant));
        if ($variantNormalized === 'standard' || $variantNormalized === 'default' || $variantNormalized === '') {
            $pattern = '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?$/';
        } elseif ($variantNormalized === 'urlsafe') {
            $pattern = '/^(?:[A-Za-z0-9_\-]{4})*(?:[A-Za-z0-9_\-]{2}==|[A-Za-z0-9_\-]{3}=)?$/';
        } elseif ($variantNormalized === 'any') {
            $pattern = '/^(?:[A-Za-z0-9+\/_\-]{4})*(?:[A-Za-z0-9+\/_\-]{2}==|[A-Za-z0-9+\/_\-]{3}=)?$/';
        } else {
            $this->configErrors[] = 'Rule `not_base64` received invalid variant `' . $variant . '` for Input Key `{{##INPUT_KEY##}}`! (Allowed: `standard`, `urlsafe`, `any`)';
            return $this;
        }
        // 6. Build Failure Condition (Fails if input IS a valid base64 string)
        $condition = 'preg_match(' . var_export($pattern, true) . ', {{##INPUT##}})';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must NOT be a base64 encoded string.";
        $error = addcslashes($error, "'\\");
        // 7. Store Compiled Rule
        $this->rules['not_base64'] = [
            'error'    => $error,
            'values' => $variant,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['not_base64'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['not_base64'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid Base32 encoded string (RFC 4648).
     * Common for 2FA / TOTP secret keys (e.g., JBSWY3DPEHPK3PXP).
     */
    public function base32(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('base32', ['hexadecimal', 'octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid Base32 string.";
        $error = addcslashes($error, "'\\");
        // Matches A-Z and 2-7, optional '=' padding at end (length must be multiple of 8 if padded)
        $compiledCode = "if(preg_match('/^(?:[A-Z2-7]{8})*(?:[A-Z2-7]{2}={6}|[A-Z2-7]{4}={4}|[A-Z2-7]{5}={3}|[A-Z2-7]{7}=)?$/iD', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['base32'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['base32'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['base32'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid Base58 string.
     * Common in crypto addresses (Bitcoin), IPFS hashes, and short identifiers.
     */
    public function base58(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('base58', ['hexadecimal', 'octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid Base58 string.";
        $error = addcslashes($error, "'\\");
        // Matches 1-9, A-Z (no I, O), a-z (no l)
        $compiledCode = "if(preg_match('/^[1-9A-HJ-NP-Za-km-z]+$/D', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['base58'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['base58'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['base58'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid URL-Safe Base64 string (RFC 4648 §5).
     * Uses '-' and '_' instead of '+' and '/', without '=' padding. Common in JWTs and WebAuthn.
     */
    public function base64url(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('base64url', ['base64', 'hexadecimal', 'octal', 'binary', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid URL-safe Base64 string.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[A-Za-z0-9_-]+$/D', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['base64url'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['base64url'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['base64url'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string contains only valid hexadecimal characters (0-9, a-f, A-F).
     */
    public function hexadecimal(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('hexadecimal', ['octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a Valid Hexadecimal String.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!ctype_xdigit({{##INPUT##}})) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['hexadecimal'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['hexadecimal'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['hexadecimal'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid 32-character MD5 hash.
     *
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function md5(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('md5', ['sha1', 'sha256', 'sha512', 'octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid MD5 hash.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[a-f0-9]{32}$/iD', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['md5'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['md5'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['md5'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid 40-character SHA-1 hash.
     *
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function sha1(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('sha1', ['md5', 'sha256', 'sha512', 'octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid SHA-1 hash.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[a-f0-9]{40}$/iD', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['sha1'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['sha1'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['sha1'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid 64-character SHA-256 hash.
     */
    public function sha256(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('sha256', ['octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid SHA-256 hash.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[a-f0-9]{64}$/iD', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['sha256'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['sha256'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['sha256'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid 96-character SHA-384 hash.
     *
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function sha384(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('sha384', ['md5', 'sha1', 'sha256', 'sha512', 'octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid SHA-384 hash.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[a-f0-9]{96}$/iD', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['sha384'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['sha384'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['sha384'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid 128-character SHA-512 hash.
     *
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function sha512(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('sha512', ['md5', 'sha1', 'sha256', 'octal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid SHA-512 hash.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[a-f0-9]{128}$/iD', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['sha512'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['sha512'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['sha512'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string contains only valid octal digits (0-7).
     */
    public function octal(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('octal', ['hexadecimal', 'binary', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a Valid Octal String.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[0-7]+$/D', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['octal'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['octal'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['octal'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string contains only binary digits (0 and 1).
     */
    public function binary(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('binary', ['hexadecimal', 'octal', 'base64', 'uid', 'slug', 'regex'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a Valid Binary String (0s and 1s only).";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(preg_match('/^[01]+$/D', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['binary'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['binary'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['binary'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid PEM-formatted block
     * (e.g., SSL/TLS certificates, public/private keys).
     */
    public function pem(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('pem', ['json', 'base64url', 'slug'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a Valid PEM Formatted String.";
        $error = addcslashes($error, "'\\");
        // Validates header, base64 payload, and footer pattern
        $compiledCode = "if(preg_match('/^-----BEGIN [A-Z0-9 ]+-----[\\r\\n]+[A-Za-z0-9+\\/\\r\\n=]+[\\r\\n]+-----END [A-Z0-9 ]+-----[\\r\\n]*$/D', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['pem'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['pem'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['pem'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid IP address (IPv4 or IPv6).
     */
    public function ip(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('ip', ['ipv4', 'ipv6'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid IP address.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(filter_var({{##INPUT##}}, FILTER_VALIDATE_IP) === false) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['ip'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['ip'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ip'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid IPv4 address.
     */
    public function ipv4(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('ipv4', ['ipv6', 'ip'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid IPv4 address.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(filter_var({{##INPUT##}}, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['ipv4'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['ipv4'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ipv4'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid IPv6 address.
     */
    public function ipv6(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('ipv6', ['ipv4', 'ip'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid IPv6 address.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(filter_var({{##INPUT##}}, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['ipv6'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['ipv6'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ipv6'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid JSON payload.
     * Uses native PHP json_validate() for ultra-fast validation without memory allocation.
     * OR this text above is complete BS from LLM that made it. Anyhow, it exists at least!
     */
    public function json(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('json', [], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid JSON string.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!json_validate({{##INPUT##}})) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['json'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['json'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['json'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string consists entirely of 7-bit ASCII characters (0-127).
     */
    public function ascii(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('ascii', ['ascii_printable'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain only 7-bit ASCII characters.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!mb_check_encoding({{##INPUT##}}, 'ASCII')) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['ascii'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['ascii'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ascii'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string consists entirely of printable 7-bit ASCII characters
     * (excludes non-printable control characters like Null, Bell, or line breaks).
     */
    public function ascii_printable(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('ascii_printable', ['ascii'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain only printable ASCII characters.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!ctype_print({{##INPUT##}})) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['ascii_printable'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['ascii_printable'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ascii_printable'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a well-formed UTF-8 byte sequence.
     */
    public function utf8(string $customErrorMsg = ''): self
    {
        // 'ascii' and 'printable_ascii' are subsets of UTF-8, so listing them as conflicts
        if (!$this->validateRuleUsage('utf8', ['ascii', 'printable_ascii'], [], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid UTF-8 string.";
        $error = addcslashes($error, "'\\");
        // preg_match('//u', $var) returns 1 if valid UTF-8, 0 if malformed
        $compiledCode = "if(preg_match('//u', {{##INPUT##}}) !== 1) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['utf8'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['utf8'] = ['error' => $error, 'values' => null, 'compiled' => $compiledCode];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['utf8'] = '" . $error . "';";
        return $this;
    }
    public function color(array|string $formats = 'hex6', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('color', [], [], ['string'])) {
            return $this;
        }
        // Master list of PCRE optimized color patterns (Non-capturing groups for speed)
        $allPatterns = [
            'hex6'  => '/^#[0-9a-fA-F]{6}$/',
            'hex3'  => '/^#[0-9a-fA-F]{3}$/',
            'hex8'  => '/^#[0-9a-fA-F]{8}$/',
            'hex4'  => '/^#[0-9a-fA-F]{4}$/',
            'rgb'   => '/^rgb\(\s*(?:\d{1,3}%?\s*,\s*){2}\d{1,3}%?\s*\)$/i',
            'rgba'  => '/^rgba\(\s*(?:\d{1,3}%?\s*,\s*){3}(?:0(?:\.\d+)?|1(?:\.0+)?|\d{1,2}%|100%)\s*\)$/i',
            'hsl'   => '/^hsl\(\s*(?:\d{1,3}|360)\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*\)$/i',
            'hsla'  => '/^hsla\(\s*(?:\d{1,3}|360)\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*,\s*(?:0(?:\.\d+)?|1(?:\.0+)?|\d{1,2}%|100%)\s*\)$/i',
            'names' => '/^(?:rebeccapurple|aliceblue|antiquewhite|aqua|aquamarine|azure|beige|bisque|black|blanchedalmond|blue|blueviolet|brown|burlywood|cadetblue|chartreuse|chocolate|coral|cornflowerblue|cornsilk|crimson|cyan|darkblue|darkcyan|darkgoldenrod|darkgray|darkgreen|darkgrey|darkkhaki|darkmagenta|darkolivegreen|darkorange|darkorchid|darkred|darksalmon|darkseagreen|darkslateblue|darkslategray|darkslategrey|darkturquoise|darkviolet|deeppink|deepskyblue|dimgray|dimgrey|dodgerblue|firebrick|floralwhite|forestgreen|fuchsia|gainsboro|ghostwhite|gold|goldenrod|gray|green|greenyellow|grey|honeydew|hotpink|indianred|indigo|ivory|khaki|lavender|lavenderblush|lawngreen|lemonchiffon|lightblue|lightcoral|lightcyan|lightgoldenrodyellow|lightgray|lightgreen|lightgrey|lightpink|lightsalmon|lightseagreen|lightskyblue|lightslategray|lightslategrey|lightsteelblue|lightyellow|lime|limegreen|linen|magenta|maroon|mediumaquamarine|mediumblue|mediumorchid|mediumpurple|mediumseagreen|mediumslateblue|mediumspringgreen|mediumturquoise|mediumvioletred|midnightblue|mintcream|mistyrose|moccasin|navajowhite|navy|oldlace|olive|olivedrab|orange|orangered|orchid|palegoldenrod|palegreen|paleturquoise|palevioletred|papayawhip|peachpuff|peru|pink|plum|powderblue|purple|red|rosybrown|royalblue|saddlebrown|salmon|sandybrown|seagreen|seashell|sienna|silver|skyblue|slateblue|slategray|slategrey|snow|springgreen|steelblue|tan|teal|thistle|tomato|turquoise|violet|wheat|white|whitesmoke|yellow|yellowgreen|transparent)$/i',
        ];
        // 4. Normalize & Expand Formats
        $requested = is_string($formats)
            ? array_map('trim', explode(',', $formats))
            : array_map('trim', array_values($formats));

        if (empty($requested) || (count($requested) === 1 && $requested[0] === '')) {
            $requested = ['hex6'];
        }
        $selectedPatterns = [];
        $invalidFormats   = [];
        foreach ($requested as $fmt) {
            $key = strtolower($fmt);
            if ($key === 'any' || $key === 'all') {
                $selectedPatterns = $allPatterns;
                break;
            } elseif ($key === 'hex') {
                $selectedPatterns['hex6'] = $allPatterns['hex6'];
                $selectedPatterns['hex3'] = $allPatterns['hex3'];
            } elseif (isset($allPatterns[$key])) {
                $selectedPatterns[$key] = $allPatterns[$key];
            } else {
                $invalidFormats[] = $fmt;
            }
        }
        if (!empty($invalidFormats)) {
            $this->configErrors[] = 'Rule `color` received invalid format(s) (`' . implode(', ', $invalidFormats) . '`) for Input Key `{{##INPUT_KEY##}}`! Choose one or more from these:`' . implode(', ', array_keys($allPatterns)) . '`';
            return $this;
        }
        // 5. Build Failure Condition
        $matchChecks = [];
        foreach ($selectedPatterns as $pattern) {
            $matchChecks[] = '!preg_match(' . var_export($pattern, true) . ', {{##INPUT##}})';
        }
        $condition = implode(' && ', $matchChecks);
        if (count($matchChecks) > 1) {
            $condition = '(' . $condition . ')';
        }
        $fmtListStr = implode(', ', array_keys($selectedPatterns));
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid color code ({$fmtListStr}).";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['color'] = [
            'allowed_formats' => array_keys($selectedPatterns),
            'values' => $formats,
            'selected_patterns' => $selectedPatterns,
            'error'           => $error,
            'compiled'        => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['color'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['color'] = '" . $error . "';";
        return $this;
    }
    public function single_char(array|string $allowedChars = [], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('single_char', ['single_char_mb'], [], ['string'])) {
            return $this;
        }
        $chars = $this->validateRuleMultipleValues('single_char', $allowedChars, ['string'], true);
        if ($chars === false) {
            return $this;
        }
        // 4. Validate Input Parameters
        $invalidChars = [];
        foreach ($chars as $c) {
            if (strlen($c) !== 1) {
                $invalidChars[] = $c;
            }
        }
        if (!empty($invalidChars)) {
            $this->configErrors[] = 'Rule `single_char` received invalid character(s) (`' . implode(', ', $invalidChars) . '`) for Input Key `{{##INPUT_KEY##}}`! Each allowed entry must be exactly 1 character.';
            return $this;
        }
        // 5. Build Failure Condition
        if (empty($chars)) {
            $condition = 'strlen({{##INPUT##}}) !== 1';
            $defaultError = "Field `{{##INPUT_KEY##}}` must be a single character.";
        } else {
            $exportedChars = var_export(array_values($chars), true);
            $condition = 'strlen({{##INPUT##}}) !== 1 || !in_array({{##INPUT##}}, ' . $exportedChars . ', true)';
            $defaultError = "Field `{{##INPUT_KEY##}}` must be one of the following characters: " . implode(', ', $chars) . ".";
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : $defaultError;
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['single_char'] = [
            'allowed_chars' => $chars,
            'values' => $chars,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['single_char'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['single_char'] = '" . $error . "';";
        return $this;
    }
    public function single_char_mb(array|string $allowedChars = [], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('single_char_mb', ['single_char'], [], ['string'])) {
            return $this;
        }
        $chars = $this->validateRuleMultipleValues('single_char_mb', $allowedChars, ['string'], true);
        if ($chars === false) {
            return $this;
        }
        //  Validate Input Parameters
        $invalidChars = [];
        foreach ($chars as $c) {
            if (mb_strlen($c) !== 1) {
                $invalidChars[] = $c;
            }
        }
        if (!empty($invalidChars)) {
            $this->configErrors[] = 'Rule `single_char_mb` received invalid character(s) (`' . implode(', ', $invalidChars) . '`) for Input Key `{{##INPUT_KEY##}}`! Each allowed entry must be exactly 1 character.';
            return $this;
        }
        // 5. Build Failure Condition
        if (empty($chars)) {
            $condition = 'mb_strlen({{##INPUT##}}) !== 1';
            $defaultError = "Field `{{##INPUT_KEY##}}` must be a single character.";
        } else {
            $exportedChars = var_export(array_values($chars), true);
            $condition = 'mb_strlen({{##INPUT##}}) !== 1 || !in_array({{##INPUT##}}, ' . $exportedChars . ', true)';
            $defaultError = "Field `{{##INPUT_KEY##}}` must be one of the following characters: " . implode(', ', $chars) . ".";
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : $defaultError;
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['single_char_mb'] = [
            'allowed_chars' => $chars,
            'values' => $chars,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['single_char_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['single_char_mb'] = '" . $error . "';";
        return $this;
    }
    public function starts_with_mb(array|string|int|float $startsWithValues, string $customErrorMsg = ''): self
    {
        // 1. Guard check: conflicts with 'starts_with' and strictly allows 'string' category
        if (!$this->validateRuleUsage('starts_with_mb', ['starts_with'], [], ['string'])) {
            return $this;
        }
        $prefixes = $this->validateRuleMultipleValues('starts_with_mb', $startsWithValues, ['string', 'integer', 'float',]);
        if (!$prefixes) {
            return $this;
        }
        // 3. Multibyte string start check using mb_strpos === 0
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addcslashes($prefix, "'\\");
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedPrefix}') === 0";
        }
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must start with: " . implode(', ', $prefixes);
        $error = addcslashes($error, "'\\");
        $this->rules['starts_with_mb'] = [
            'values' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['starts_with_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['starts_with_mb'] = '" . $error . "';";
        return $this;
    }
    public function ends_with_mb(array|string|int|float $endsWithValues, string $customErrorMsg = ''): self
    {
        // 1. Guard check: conflicts with 'ends_with' and strictly allows 'string' category
        if (!$this->validateRuleUsage('ends_with_mb', ['ends_with'], [], ['string'])) {
            return $this;
        }
        $suffixes = $this->validateRuleMultipleValues('ends_with_mb', $endsWithValues, ['string', 'integer', 'float',]);
        if (!$suffixes) {
            return $this;
        }
        // 3. Multibyte string end check using mb_substr
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addcslashes($suffix, "'\\");
            $length = mb_strlen($suffix);
            if ($length === 0) {
                $conditions[] = 'true';
            } else {
                $conditions[] = "mb_substr({$inputStr}, -{$length}) === '{$escapedSuffix}'";
            }
        }
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must end with: " . implode(', ', $suffixes);
        $error = addcslashes($error, "'\\");
        $this->rules['ends_with_mb'] = [
            'values' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['ends_with_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ends_with_mb'] = '" . $error . "';";
        return $this;
    }
    public function contains_mb(array|string|int|float $containsValues, string $customErrorMsg = ''): self
    {
        // 1. Guard check: conflicts with 'contains' and strictly allows 'string' category
        if (!$this->validateRuleUsage('contains_mb', ['contains'], [], ['string'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('contains_mb', $containsValues, ['string', 'integer', 'float',]);
        if (!$needles) {
            return $this;
        }
        // 3. Multibyte string contains check using mb_strpos !== false
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($needles as $needle) {
            $escapedNeedle = addcslashes($needle, "'\\");
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedNeedle}') !== false";
        }
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain: " . implode(', ', $needles);
        $error = addcslashes($error, "'\\");
        $this->rules['contains_mb'] = [
            'values'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['contains_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['contains_mb'] = '" . $error . "';";
        return $this;
    }
    public function doesnt_start_with_mb(array|string|int|float $startsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('doesnt_start_with_mb', ['doesnt_start_with', 'starts_with_mb'], [], ['string'])) {
            return $this;
        }
        $prefixes = $this->validateRuleMultipleValues('doesnt_start_with_mb', $startsWithValues, ['string', 'integer', 'float',]);
        if (!$prefixes) {
            return $this;
        }
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addcslashes($prefix, "'\\");
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedPrefix}') === 0";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not start with: " . implode(', ', $prefixes);
        $error = addcslashes($error, "'\\");
        $this->rules['doesnt_start_with_mb'] = [
            'values' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['doesnt_start_with_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_start_with_mb'] = '" . $error . "';";
        return $this;
    }
    public function doesnt_end_with_mb(array|string|int|float $endsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('doesnt_end_with_mb', ['doesnt_end_with', 'ends_with_mb'], [], ['string'])) {
            return $this;
        }
        $suffixes = $this->validateRuleMultipleValues('doesnt_end_with_mb', $endsWithValues, ['string', 'integer', 'float',]);
        if (!$suffixes) {
            return $this;
        }
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addcslashes($suffix, "'\\");
            $length = mb_strlen($suffix);
            if ($length === 0) {
                $conditions[] = 'true';
            } else {
                $conditions[] = "mb_substr({$inputStr}, -{$length}) === '{$escapedSuffix}'";
            }
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not end with: " . implode(', ', $suffixes);
        $error = addcslashes($error, "'\\");
        $this->rules['doesnt_end_with_mb'] = [
            'values' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['doesnt_end_with_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_end_with_mb'] = '" . $error . "';";
        return $this;
    }
    public function doesnt_contain_mb(array|string|int|float $containsValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('doesnt_contain_mb', ['doesnt_contain', 'contains_mb'], [], ['string'])) {
            return $this;
        }
        $needles = $this->validateRuleMultipleValues('doesnt_contain_mb', $containsValues, ['string', 'integer', 'float',]);
        if (!$needles) {
            return $this;
        }
        $inputStr = '{{##INPUT##}}';
        $conditions = [];
        foreach ($needles as $needle) {
            $escapedNeedle = addcslashes($needle, "'\\");
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedNeedle}') !== false";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not contain: " . implode(', ', $needles);
        $error = addcslashes($error, "'\\");
        $this->rules['doesnt_contain_mb'] = [
            'values'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['doesnt_contain_mb'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_contain_mb'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input is a valid date string
     *
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain a valid date format!";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(strtotime({{##INPUT##}}) === false) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date'] = [
            'error'    => $error,
            'values' => null,
            'compiled' => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input is a valid date occurring strictly AFTER the target date/time.
     *
     * @param string $targetDate Parseable date string (e.g. '2026-01-01', 'today', '+2 days').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_after(string $targetDate, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_after', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('date_after', $targetDate, ['string']);
        if ($validated === false) {
            return $this;
        }
        $targetDate = $validated[0];
        // Developer config check: Ensure the target parameter itself is a valid date string
        if (strtotime($targetDate) === false) {
            $this->configErrors[] = "Rule `date_after` Parameter `'{$targetDate}'` is NOT a Valid Parseable Date String (checked with `strtotime()`) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date after `{$targetDate}`!";
        $error = addcslashes($error, "'\\");
        // Fails if input is not a string, not parseable, or falls on/before the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) <= strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_after'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_after'] = [
            'targetDate' => $targetDate,
            'values' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_after'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input is a valid date occurring on or AFTER the target date/time.
     *
     * @param string $targetDate Parseable date string (e.g. '2026-01-01', 'today', '+2 days').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_after_or_equal(string $targetDate, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_after_or_equal', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('date_after_or_equal', $targetDate, ['string']);
        if ($validated === false) {
            return $this;
        }
        $targetDate = $validated[0];
        // Developer config check: Ensure the target parameter itself is a valid date string
        if (strtotime($targetDate) === false) {
            $this->configErrors[] = "Rule `date_after_or_equal` Parameter `'{$targetDate}'` is NOT a Valid Parseable Date String (checked with `strtotime()`) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date on or after `{$targetDate}`!";
        $error = addcslashes($error, "'\\");
        // Fails if input is unparseable or falls strictly before the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) < strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_after_or_equal'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_after_or_equal'] = [
            'targetDate' => $targetDate,
            'values' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_after_or_equal'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input is a valid date occurring strictly BEFORE the target date/time.
     *
     * @param string $targetDate Parseable date string (e.g. '2026-12-31', 'tomorrow', '-1 month').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_before(string $targetDate, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_before', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('date_before', $targetDate, ['string']);
        if ($validated === false) {
            return $this;
        }
        $targetDate = $validated[0];
        // Developer config check: Ensure the target parameter itself is a valid date string
        if (strtotime($targetDate) === false) {
            $this->configErrors[] = "Rule `date_before` parameter `'{$targetDate}'` is NOT a Valid Parseable Date String (checked with `strtotime()`) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date before `{$targetDate}`!";
        $error = addcslashes($error, "'\\");
        // Fails if input is not a string, not parseable, or falls on/after the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) >= strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_before'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_before'] = [
            'targetDate' => $targetDate,
            'values' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_before'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input is a valid date occurring on or BEFORE the target date/time.
     *
     * @param string $targetDate Parseable date string (e.g. '2026-12-31', 'tomorrow', '-1 month').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_before_or_equal(string $targetDate, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_before_or_equal', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('date_before_or_equal', $targetDate, ['string']);
        if ($validated === false) {
            return $this;
        }
        $targetDate = $validated[0];
        // Developer config check: Ensure the target parameter itself is a valid date string
        if (strtotime($targetDate) === false) {
            $this->configErrors[] = "Rule `date_before_or_equal` Parameter `'{$targetDate}'` is NOT a Valid Parseable Date String (checked with `strtotime()`) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date on or before `{$targetDate}`!";
        $error = addcslashes($error, "'\\");
        // Fails if input is unparseable or falls strictly after the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) > strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_before_or_equal'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_before_or_equal'] = [
            'targetDate' => $targetDate,
            'values' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_before_or_equal'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input date is equal to the target date/time when evaluated via strtotime().
     *
     * @param string $targetDate Parseable date string (e.g. '2026-01-01', 'today').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_equals(string $targetDate, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_equals', ['date_before', 'date_after'], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validated = $this->validateRuleMultipleValues('date_equals', $targetDate, ['string']);
        if ($validated === false) {
            return $this;
        }
        $targetDate = $validated[0];
        // Developer config check: Ensure target date is parseable
        if (strtotime($targetDate) === false) {
            $this->configErrors[] = "Rule `date_equals` Parameter `'{$targetDate}'` is NOT a Valid Parseable Date String (checked with `strtotime()`) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date equal to `{$targetDate}`!";
        $error = addcslashes($error, "'\\");
        // Fails if input is not parseable or timestamps do not match
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) !== strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_equals'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_equals'] = [
            'targetDate' => $targetDate,
            'values' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_equals'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input matches at least one of the given PHP date formats.
     *
     * @param array<int, 'Y-m-d'|'Y-m-d H:i:s'|'Y/m/d'|'m/d/Y'|'d/m/Y'|'H:i:s'|'H:i'|'Y-m-d\TH:i:sP'|'l, d-M-Y H:i:s T'|'Y-m-d\TH:i:sO'|'X-m-d\TH:i:sP'|'D, d M y H:i:s O'|'l, d-M-y H:i:s T'|'D, d M Y H:i:s O'|'D, d M Y H:i:s \G\M\T'|'Y-m-d\TH:i:s.vP'|string>|'Y-m-d'|'Y-m-d H:i:s'|'Y/m/d'|'m/d/Y'|'d/m/Y'|'H:i:s'|'H:i'|'Y-m-d\TH:i:sP'|'l, d-M-Y H:i:s T'|'Y-m-d\TH:i:sO'|'X-m-d\TH:i:sP'|'D, d M y H:i:s O'|'l, d-M-y H:i:s T'|'D, d M Y H:i:s O'|'D, d M Y H:i:s \G\M\T'|'Y-m-d\TH:i:s.vP'|string $formats Choose One or More Date Formats separated with a comma in a Single String OR as String Elements in an Array
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_format(array|string $formats, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_format', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validFormats = $this->validateRuleMultipleValues('date_format', $formats, ['string']);
        if ($validFormats === false) {
            return $this;
        }
        $dummies = [];
        $dummyDate = new \DateTimeImmutable();
        foreach ($validFormats as $index => $fmt) {
            // Ensure format string contains at least one valid PHP date specifier token
            $hasToken = (bool) preg_match('/[dDjlNSwzWFmMntLoYyaABgGhHisuveIOPpTZcrU]/', $fmt);
            try {
                $formattedDummy = $dummyDate->format($fmt);
                $dummies[] = $formattedDummy;
                if (!$hasToken) {
                    $this->configErrors[] = "Rule `date_format` Parameter [{$index}] `'{$fmt}'` does NOT contain any valid PHP Date Format Specifiers (e.g. `Y`, `m`, `d`, `H`, `i`, `s`; COMPLETE LIST BASED ON REGEX IS:`dDjlNSwzWFmMntLoYyaABgGhHisuveIOPpTZcrU`) for Input Key `{{##INPUT_KEY##}}`!";
                    return $this;
                }
            } catch (\Throwable $e) {
                $this->configErrors[] = "Rule `date_format` Parameter [{$index}] `'{$fmt}'` threw an exception when evaluated by DateTimeImmutable for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
        }
        // Build matching checks for each supplied format
        $matchingConditions = [];
        foreach ($validFormats as $fmt) {
            $escapedFmt = addcslashes($fmt, "'\\");
            $matchingConditions[] = "(\\DateTimeImmutable::createFromFormat('{$escapedFmt}', {{##INPUT##}}) !== false && " .
                "\\DateTimeImmutable::createFromFormat('{$escapedFmt}', {{##INPUT##}})->format('{$escapedFmt}') === {{##INPUT##}})";
        }
        $joinedFormats = implode('`, `', $validFormats);
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must match one of the following date formats: `{$joinedFormats}`!";
        $error = addcslashes($error, "'\\");
        // Fails if NONE of the format conditions evaluate to true
        $condition = "!(" . implode(" || ", $matchingConditions) . ")";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_format'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_format'] = [
            'values' => $formats,
            'formats'  => $validFormats,
            'dummies'  => $dummies,
            'error'    => $error,
            'compiled' => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_format'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input date is a valid date matching at least one of the given target dates.
     *
     * @param array<int, string>|string $targetDates Single parseable date string or array of date strings (e.g. 'today', ['2026-01-01', '2026-12-25']).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_in(array|string $targetDates, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_in', ['date_equals'], ['string', 'date'], ['string'])) {
            return $this;
        }
        $validatedDates = $this->validateRuleMultipleValues('date_in', $targetDates, ['string']);
        if ($validatedDates === false) {
            return $this;
        }
        // Developer config check: Ensure every target parameter is a valid parseable date string
        foreach ($validatedDates as $index => $target) {
            if (strtotime($target) === false) {
                $this->configErrors[] = "Rule `date_in` Parameter [{$index}] `'{$target}'` is NOT a Valid Parseable Date String (checked with `strtotime()`) for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
        }
        // Build individual equality checks for each target date
        $equalityChecks = [];
        foreach ($validatedDates as $target) {
            $escapedTarget = addcslashes($target, "'\\");
            $equalityChecks[] = "strtotime({{##INPUT##}}) === strtotime('{$escapedTarget}')";
        }
        $joinedTargets = implode('`, `', $validatedDates);
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date matching one of the following: `{$joinedTargets}`!";
        $error = addcslashes($error, "'\\");
        // Fails if input is unparseable OR if none of the target checks match
        $condition = "strtotime({{##INPUT##}}) === false || !(" . implode(' || ', $equalityChecks) . ")";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_in'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_in'] = [
            'targetDates' => $validatedDates,
            'values' => $targetDates,
            'error'       => $error,
            'compiled'    => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_in'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input string is a valid timezone identifier.
     *
     * @param 'all'|'africa'|'america'|'antarctica'|'arctic'|'asia'|'atlantic'|'australia'|'europe'|'indian'|'pacific'|'utc'|'per_country'|string $region Region filter or 'per_country'.
     * @param string|null $countryCode 2-letter ISO country code if $region is 'per_country' (e.g. 'US', 'SE', 'GB').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function date_timezone(string $region = 'all', ?string $countryCode = null, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('date_timezone', [], ['string', 'date'], ['string'])) {
            return $this;
        }
        // Map region strings to DateTimeZone constants
        $regionMap = [
            'all'         => \DateTimeZone::ALL,
            'africa'      => \DateTimeZone::AFRICA,
            'america'     => \DateTimeZone::AMERICA,
            'antarctica'  => \DateTimeZone::ANTARCTICA,
            'arctic'      => \DateTimeZone::ARCTIC,
            'asia'        => \DateTimeZone::ASIA,
            'atlantic'    => \DateTimeZone::ATLANTIC,
            'australia'   => \DateTimeZone::AUSTRALIA,
            'europe'      => \DateTimeZone::EUROPE,
            'indian'      => \DateTimeZone::INDIAN,
            'pacific'     => \DateTimeZone::PACIFIC,
            'utc'         => \DateTimeZone::UTC,
            'all_with_bc' => \DateTimeZone::ALL_WITH_BC,
            'per_country' => \DateTimeZone::PER_COUNTRY,
        ];
        $normalizedRegion = strtolower(trim($region));
        if (!array_key_exists($normalizedRegion, $regionMap)) {
            $this->configErrors[] = "Rule `date_timezone` Parameter `'{$region}'` is NOT a Valid Timezone Region for Input Key `{{##INPUT_KEY##}}`! The available ones are:`" . join(', ', array_keys($regionMap)) .  '`!';
            return $this;
        }
        $groupConstant = $regionMap[$normalizedRegion];
        // Developer config check: Dry-run DateTimeZone::listIdentifiers to verify parameters
        try {
            if ($groupConstant === \DateTimeZone::PER_COUNTRY) {
                if (empty($countryCode) || strlen($countryCode) !== 2) {
                    $this->configErrors[] = "Rule `date_timezone` requires a Valid 2-Letter ISO Country Code when using 'per_country' for Input Key `{{##INPUT_KEY##}}`!";
                    return $this;
                }
                $tzList = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, strtoupper($countryCode));
            } else {
                $tzList = \DateTimeZone::listIdentifiers($groupConstant);
            }
            if (empty($tzList)) {
                $this->configErrors[] = "Rule `date_timezone` produced zero valid timezones for region `'{$region}'` and country `'{$countryCode}'` for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
        } catch (\Throwable $e) {
            $this->configErrors[] = "Rule `date_timezone` failed parameter validation for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // Determine error message
        if (!empty($customErrorMsg)) {
            $error = $customErrorMsg;
            $error = addcslashes($error, "'\\");
        } elseif ($groupConstant === \DateTimeZone::PER_COUNTRY) {
            $error = "Field `{{##INPUT_KEY##}}` must be a valid timezone identifier for country `" . strtoupper($countryCode) . "`!";
        } elseif ($normalizedRegion !== 'all') {
            $error = "Field `{{##INPUT_KEY##}}` must be a valid timezone identifier in the `" . ucfirst($normalizedRegion) . "` region!";
        } else {
            $error = "Field `{{##INPUT_KEY##}}` must be a valid timezone identifier!";
        }
        // Build compiled code condition
        if ($groupConstant === \DateTimeZone::PER_COUNTRY) {
            $upperCountry = strtoupper($countryCode);
            $condition = "!\\in_array({{##INPUT##}}, \\DateTimeZone::listIdentifiers(\\DateTimeZone::PER_COUNTRY, '{$upperCountry}'), true)";
        } else {
            $condition = "!\\in_array({{##INPUT##}}, \\DateTimeZone::listIdentifiers({$groupConstant}), true)";
        }
        $compiledCode = "if({$condition}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['date_timezone'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_timezone'] = [
            'values' => ['region' => $region, 'countryCode' => $countryCode],
            'region'      => $normalizedRegion,
            'countryCode' => $countryCode,
            'error'       => $error,
            'compiled'    => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['date_timezone'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that a String input matches at least one of the specified character encodings.
     * Checks against PHP's supported mb_list_encodings() at compile time. `IMPORTANT:` This rule
     * means that ONLY one of the encoding(s) must validate in order to pass. Fail ONLY happens
     * when all passed encoding(s) did not pass the check against the string-based Input!
     *
     * @param string|array $encoding Single encoding string or array/comma-separated list of allowed encodings (e.g., 'UTF-8', 'ASCII, ISO-8859-1').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function encoding(string|array $encoding, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('encoding', ['file_encoding'], [], ['string'])) {
            return $this;
        }
        $encodingValid = $this->validateRuleMultipleValues('encoding', $encoding, ['string']);
        if ($encodingValid === false) {
            return $this;
        }
        // 1. Verify all requested encodings exist in PHP's supported list at compile time
        $supportedEncodings = array_map('strtoupper', mb_list_encodings());
        $cleanEncodings = [];
        $unrecognizedEncodings = [];
        foreach ($encodingValid as $enc) {
            $cleanEnc = strtoupper(trim($enc));
            if (in_array($cleanEnc, $supportedEncodings, true)) {
                $cleanEncodings[] = $cleanEnc;
            } else {
                $unrecognizedEncodings[] = $cleanEnc;
            }
        }
        if (!empty($unrecognizedEncodings)) {
            $unrecognizedList = implode(', ', $unrecognizedEncodings);
            $this->configErrors[] = "Rule `encoding` contains unrecognized encoding(s): [{$unrecognizedList}] for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $cleanEncodings = array_values(array_unique($cleanEncodings));
        if (empty($cleanEncodings)) {
            $this->configErrors[] = "Rule `encoding` requires at least one valid encoding for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be encoded in any of the following formats: `" . implode(', ', $cleanEncodings) . "`.";
        $error = addcslashes($error, "'\\");
        $compiledEncodings = var_export($cleanEncodings, true);
        // 2. Compiled runtime check
        $compiledCode = "{\n" .
            "    \$isValidEncoding = false;\n" .
            "    foreach ({$compiledEncodings} as \$enc) {\n" .
            "        if (mb_check_encoding({{##INPUT##}}, \$enc)) {\n" .
            "            \$isValidEncoding = true;\n" .
            "            break;\n" .
            "        }\n" .
            "    }\n" .
            "    if (!\$isValidEncoding) {\n" .
            "        {{##ERRORS##}}['encoding'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "}";
        $this->rules['encoding'] = [
            'values' => $encoding,
            'encodings' => $cleanEncodings,
            'error'     => $error,
            'compiled'  => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['encoding'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that a password string satisfies complexity requirements (length, letters, numbers, symbols, mixed case).
     *
     * @param string $length Range formatted strictly as "min-max" or "min,max" (e.g. "10-50").
     * @param int $letters Minimum count of alphabetic characters required (0 = disabled).
     * @param int $numbers Minimum count of numeric digits required (0 = disabled).
     * @param int $symbols Minimum count of special/symbol characters required (0 = disabled).
     * @param bool $mixedCase Require at least 1 uppercase and 1 lowercase character (default = true).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function password(
        string $length = "10-50",
        int $letters = 0,
        int $numbers = 0,
        int $symbols = 0,
        bool $mixedCase = true,
        string $customErrorMsg = ''
    ): self {
        // 1. Guard check: strictly allowed on 'string' data type and 'password' category
        if (!$this->validateRuleUsage('password', ['email', 'phone'], ['string', 'password'], ['string'])) {
            return $this;
        }
        if (!preg_match('/^([\d]+)[,|-]([\d]+)$/', $length, $lengthParts)) {
            $this->configErrors[] = "Rule `password` length Parameter (`{$length}`) must be formated as EITHER `min-max` OR `min,max` for Input Key `{{##INPUT_KEY##}}` !";
            return $this;
        }
        $min = (int)($lengthParts[1] ?? 10);
        $max = (int)($lengthParts[2] ?? $min);
        if (($min <= 0)) {
            $this->configErrors[] = "Rule `password` min length ({$min}) cannot be 0 or negative for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if (($min < ($letters + $numbers + $symbols))) {
            $this->configErrors[] = "Rule `password` min length ({$min}) cannot be smaller than the combination of letters, numbers and/or symbols needed for the password to be considered valid for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if (($min > $max) || ($min === $max)) {
            $this->configErrors[] = "Rule `password` min length ({$min}) cannot be greater or equal than max length ({$max}) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if (($min < 12)) {
            $this->configWarnings[] = "Rule `password` min length ({$min}) is recommended to be at least 12 or preferably even higher for Input Key `{{##INPUT_KEY##}}`!";
        }
        if (($max < 16)) {
            $this->configWarnings[] = "Rule `password` max length ({$max}) is recommended to be at least 16 or preferably even higher for Input Key `{{##INPUT_KEY##}}`!";
        }
        if (!$mixedCase) {
            $this->configWarnings[] = "Rule `password` is recommended to use mixed character casings for more secure password for Input Key `{{##INPUT_KEY##}}`!";
        }
        // 2. Build array of compiled runtime conditions
        $conditions = [];
        $inputStr = "{{##INPUT##}}";
        // Length check using O(1) strlen
        $conditions[] = "strlen({$inputStr}) < {$min}";
        if ($max > 0) {
            $conditions[] = "strlen({$inputStr}) > {$max}";
        }
        // Letter count check
        if ($letters > 0) {
            $conditions[] = "preg_match_all('/[a-zA-Z]/', {$inputStr}) < {$letters}";
        }
        // Number count check
        if ($numbers > 0) {
            $conditions[] = "preg_match_all('/[0-9]/', {$inputStr}) < {$numbers}";
        }
        // Symbol/Special character check (\W includes non-alphanumeric, _ included explicitly)
        if ($symbols > 0) {
            $conditions[] = "preg_match_all('/[\W_]/', {$inputStr}) < {$symbols}";
        }
        // Mixed case check (requires at least 1 lowercase and 1 uppercase)
        if ($mixedCase) {
            $conditions[] = "(!preg_match('/[a-z]/', {$inputStr}) || !preg_match('/[A-Z]/', {$inputStr}))";
        }
        // Join all conditions into a single logical OR evaluation
        $compiledCondition = implode(" ||\n        ", $conditions);
        // Error message construction
        $error = '';
        if (!empty($customErrorMsg)) {
            $error = $customErrorMsg;
        } else {
            $reqs = [];
            // Length requirement
            $reqs[] = "be between {$min} and {$max} characters long";
            // Mixed case requirement
            if ($mixedCase) {
                $reqs[] = "contain both uppercase and lowercase letters";
            }
            // Minimum letters count
            if ($letters > 0) {
                $reqs[] = "contain at least {$letters} " . ($letters === 1 ? 'letter' : 'letters');
            }
            // Minimum numbers count
            if ($numbers > 0) {
                $reqs[] = "contain at least {$numbers} " . ($numbers === 1 ? 'number' : 'numbers');
            }
            // Minimum symbols count
            if ($symbols > 0) {
                $reqs[] = "contain at least {$symbols} " . ($symbols === 1 ? 'symbol' : 'symbols');
            }
            // Format requirements into a clean list
            $totalReqs = count($reqs);
            if ($totalReqs === 1) {
                $reqList = $reqs[0];
            } elseif ($totalReqs === 2) {
                $reqList = $reqs[0] . " and " . $reqs[1];
            } else {
                $lastReq = array_pop($reqs);
                $reqList = implode(', ', $reqs) . ', and ' . $lastReq;
            }
            $error = "Field `{{##INPUT_KEY##}}` must {$reqList}.";
        }
        // Escape error message for single-quoted compiled PHP strings
        $error = addcslashes($error, "'\\");
        // Store compiled rule
        $this->rules['password'] = [
            'values' => [
                'min'       => $min,
                'max'       => $max,
                'letters'   => $letters,
                'numbers'   => $numbers,
                'symbols'   => $symbols,
                'mixedCase' => $mixedCase,
            ],
            'min'       => $min,
            'max'       => $max,
            'letters'   => $letters,
            'numbers'   => $numbers,
            'symbols'   => $symbols,
            'mixedCase' => $mixedCase,
            'error'     => $error,
            'compiled'  => "if({$compiledCondition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['password'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['password'] = '" . $error . "';";
        return $this;
    }
    /**
     * Checks if the password has appeared in public data breaches using the HaveIBeenPwned API (k-Anonymity model) - URL used: https://api.pwnedpasswords.com/range/
     *
     * @param int $threshold Minimum number of times leaked to consider compromised (default = 1).
     * @param float $timeout HTTP socket timeout in seconds for the API request (default = 1.5).
     * @param bool $failOnApiError If true, fails validation on API timeouts/errors; if false, fails open (default = false).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function password_uncompromised(
        int $threshold = 1,
        float $timeout = 1.5,
        bool $failOnApiError = false,
        string $customErrorMsg = ''
    ): self {
        // 1. Guard check: must be used on string / password category
        if (!$this->validateRuleUsage('password_uncompromised', ['email', 'phone'], ['string', 'password'], ['string'])) {
            return $this;
        }
        if ($threshold < 1) {
            $this->configErrors[] = "Rule `password_uncompromised` threshold must be at least 1 for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 2. Error message
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` has appeared in data breaches and is compromised.";
        $error = addcslashes($error, "'\\");
        $failOnErrCode = $failOnApiError ? 'true' : 'false';
        // 3. Runtime Pre-Compiled Logic (Concatenation style)
        $compiledCode = "if({{##INPUT##}} !== '') {\n" .
            "    \$__hash = strtoupper(sha1({{##INPUT##}}));\n" .
            "    \$__prefix = substr(\$__hash, 0, 5);\n" .
            "    \$__suffix = substr(\$__hash, 5);\n" .
            "    \$__isCompromised = false;\n" .
            "    \$__ctx = stream_context_create([\n" .
            "        'http' => [\n" .
            "            'method'  => 'GET',\n" .
            "            'timeout' => {$timeout},\n" .
            "            'header'  => \"User-Agent: FunkPHP-Validator\\r\\n\"\n" .
            "        ]\n" .
            "    ]);\n" .
            "    \$__response = @file_get_contents(\"https://api.pwnedpasswords.com/range/\" . \$__prefix, false, \$__ctx);\n" .
            "    if (\$__response === false) {\n" .
            "        if ({$failOnErrCode}) {\n" .
            "            \$__isCompromised = true;\n" .
            "        }\n" .
            "    } else {\n" .
            "        if (preg_match_all('/^' . preg_quote(\$__suffix, '/') . ':(\\\\d+)/m', \$__response, \$__matches)) {\n" .
            "            \$__count = (int)(\$__matches[1][0] ?? 0);\n" .
            "            if (\$__count >= {$threshold}) {\n" .
            "                \$__isCompromised = true;\n" .
            "            }\n" .
            "        }\n" .
            "    }\n" .
            "    if (\$__isCompromised) {\n" .
            "        {{##ERRORS##}}['password_uncompromised'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "}";
        // 4. Store compiled rule
        $this->rules['password_uncompromised'] = [
            'values' => ['threshold' => $threshold, 'timeout' => $timeout, 'failOnApiError' => $failOnApiError],
            'threshold' => $threshold,
            'error'     => $error,
            'compiled'  => $compiledCode
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['password_uncompromised'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that an input is a valid real-world email address using regex, with optional TLD and DNS record checks.
     *
     * @param bool $checkDns Performs DNS lookup for MX, A, and AAAA records on the domain (default = false).
     * @param bool $checkTld Verifies domain suffix against global TLD list constants (default = false).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function email_web(
        bool $checkDns = false,
        bool $checkTld = false,
        string $customErrorMsg = ''
    ): self {
        // 1. Guard check: strictly allowed on 'string' data type and 'email' category
        if (!$this->validateRuleUsage('email_web', [], ['string', 'email'], ['string'])) {
            return $this;
        }
        // 2. Error message setup
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid email address.";
        $error = addcslashes($error, "'\\");
        // 3. Custom Regex Validation (Handles 99% of web email formats, single-letter domains like x.se, disallows '..')
        $compiledCode = "if(!preg_match('/^(?!.*\\.\\.)[a-zA-Z0-9](?:[a-zA-Z0-9._+-]*[a-zA-Z0-9])?@(?:[a-zA-Z0-9](?!.*--)[a-zA-Z0-9-]*\\.)+[a-zA-Z]{2,}\$/D', {{##INPUT##}})) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['email'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        // 4. Optional Fast TLD Check (Uses fast in_array lookups on extracted suffix)
        if ($checkTld) {
            $compiledCode .= "\n" .
                "    \$__domain = substr(strrchr({{##INPUT##}}, '@') ?: '', 1);\n" .
                "    \$__tld = strtolower(strrchr(\$__domain, '.') ?: '');\n" .
                "    if(\$__tld === '' || (!in_array(\$__tld, FUNKPHP_LISTS['LISTS']['valid_tlds_top100'] ?? [], true) && !in_array(\$__tld, FUNKPHP_LISTS['LISTS']['valid_tlds_all'] ?? [], true))) {\n" .
                "        {{##ERRORS##}}['email'] = '{$error}';\n" .
                "        {{##GOTO_STOP_ALL##}}\n" .
                "        {{##GOTO_BAIL##}}\n" .
                "        {{##GOTO_NEXT_RULE##}}\n" .
                "        {{##GOTO_END_FIELD##}}\n" .
                "    }\n" .
                "";
        }
        // 5. Optional DNS Check (Appended only if requested)
        if ($checkDns) {
            $compiledCode .= "\n" .
                "    \$__domain = substr(strrchr({{##INPUT##}}, '@') ?: '', 1);\n" .
                "    if(!checkdnsrr(\$__domain, 'MX') && !checkdnsrr(\$__domain, 'A') && !checkdnsrr(\$__domain, 'AAAA')) {\n" .
                "        {{##ERRORS##}}['email'] = '{$error}';\n" .
                "        {{##GOTO_STOP_ALL##}}\n" .
                "        {{##GOTO_BAIL##}}\n" .
                "        {{##GOTO_NEXT_RULE##}}\n" .
                "        {{##GOTO_END_FIELD##}}\n" .
                "    }\n" .
                "";
        }
        // 6. Store compiled rule
        $this->rules['email_web'] = [
            'values' => [
                'checkDns' => $checkDns,
                'checkTld' => $checkTld,
            ],
            'checkDns' => $checkDns,
            'checkTld' => $checkTld,
            'error'    => $error,
            'compiled' => $compiledCode
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['email_web'] = '" . $error . "';";
        return $this;
    }
    /**
     *
     * Validates that an input is a formatted phone number (7-15 digits with optional +, -, (), spaces, and dots).
     *
     * @param array<SupportedCountry|string> $countryCodes Country ISO codes or dial prefixes (e.g. ['UK', 'SE'] or ['+44', '+46']).
     * @param int $minDigits Minimum number of raw numeric digits required (default = 7).
     * @param int $maxDigits Maximum number of raw numeric digits allowed (default = 15).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function phone(
        array $countryCodes = [],
        int $minDigits = 7,
        int $maxDigits = 15,
        string $customErrorMsg = ''
    ): self {
        // 1. Guard check: strictly allowed on 'string' data type and 'phone' category
        if (!$this->validateRuleUsage('phone', ['email_web', 'password', 'password_uncompromised'], ['string', 'phone'], ['string'])) {
            return $this;
        }
        // 2. Error message setup
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid phone number.";
        $error = addcslashes($error, "'\\");
        // 3. Base Regex: allows optional leading +, numbers, spaces, parens, hyphens, and dots
        $compiledCode = "if(!preg_match('/^\\+?[0-9\\s().-]{{$minDigits},30}\$/D', {{##INPUT##}})) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['phone'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "} \n" .
            "    \$__rawDigits = preg_replace('/\\D/', '', {{##INPUT##}});\n" .
            "    \$__digitCount = strlen(\$__rawDigits);\n" .
            "    if (\$__digitCount < {$minDigits} || \$__digitCount > {$maxDigits}) {\n" .
            "        {{##ERRORS##}}['phone'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    \n" .
            "}";
        // 4. Optional Country Code Prefix Check
        if (!empty($countryCodes)) {
            $exportedCodes = var_export(array_values($countryCodes), true);
            $compiledCode .= "\n" .
                "    \$__allowedPrefixes = {$exportedCodes};\n" .
                "    \$__matchesPrefix = false;\n" .
                "    foreach (\$__allowedPrefixes as \$__prefix) {\n" .
                "        if (str_starts_with({{##INPUT##}}, \$__prefix)) {\n" .
                "            \$__matchesPrefix = true;\n" .
                "            break;\n" .
                "        }\n" .
                "    }\n" .
                "    if(!\$__matchesPrefix) {\n" .
                "        {{##ERRORS##}}['phone'] = '{$error}';\n" .
                "        {{##GOTO_STOP_ALL##}}\n" .
                "        {{##GOTO_BAIL##}}\n" .
                "        {{##GOTO_NEXT_RULE##}}\n" .
                "        {{##GOTO_END_FIELD##}}\n" .
                "    }\n" .
                "";
        }
        // 5. Store compiled rule
        $this->rules['phone'] = [
            'values' => [
                'countryCodes' => $countryCodes,
                'minDigits'    => $minDigits,
                'maxDigits'    => $maxDigits,
            ],
            'countryCodes' => $countryCodes,
            'minDigits'    => $minDigits,
            'maxDigits'    => $maxDigits,
            'error'        => $error,
            'compiled'     => $compiledCode
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['phone'] = '" . $error . "';";
        return $this;
    }

    /* OTHER INPUT KEYS-ONLY RULES - value is ANOTHER data field! */
    // GLOBAL COMPILER MUST CHECK that "gte", "gt","lte","lt","same", "different" try NOT
    // to "target" themselves but this needs access to outer keys which they do not have
    // access to so they use {{##INPUT##}} and {{##TARGET_INPUT:<other_key_name>##}} thus!
    public function gte(string $targetFieldinValidation, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('gte', ['gt'])) {
            return $this;
        }
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be greater than or equal to field `{$targetFieldinValidation}`.";
        $error = addcslashes($error, "'\\");
        // 3. Branch condition based on current Data Type Category
        // Failure condition: current field is LESS THAN target field
        switch ($this->dataTypeCategory) {
            case 'string':
                $condition = "strlen({{##INPUT##}}) < strlen({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'numeric':
                $condition = "{{##INPUT##}} < {{##TARGET_INPUT:{$targetFieldinValidation}##}}";
                break;
            case 'array':
                $condition = "count({{##INPUT##}}) < count({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'object':
                $condition = "count(get_object_vars({{##INPUT##}})) < count(get_object_vars({{##TARGET_INPUT:{$targetFieldinValidation}##}}))";
                break;
            case 'file':
                $condition = "(!isset({{##INPUT##}}['size'], {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size']) || {{##INPUT##}}['size'] < {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size'])";
                break;
            default:
                $this->configErrors[] = "Rule `gte` (greater than or equal) is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        // 4. Store compiled rule (with Target Existence Guard)
        $this->rules['gte'] = [
            'error'    => $error,
            'values' => $targetFieldinValidation,
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['gte'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['gte'] = '" . $error . "';";
        return $this;
    }
    public function gt(string $targetFieldinValidation, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('gt', ['gte'])) {
            return $this;
        }
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be greater than field `{$targetFieldinValidation}`.";
        $error = addcslashes($error, "'\\");
        // Failure condition: input <= target
        switch ($this->dataTypeCategory) {
            case 'string':
                $condition = "strlen({{##INPUT##}}) <= strlen({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'numeric':
                $condition = "{{##INPUT##}} <= {{##TARGET_INPUT:{$targetFieldinValidation}##}}";
                break;
            case 'array':
                $condition = "count({{##INPUT##}}) <= count({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'object':
                $condition = "count(get_object_vars({{##INPUT##}})) <= count(get_object_vars({{##TARGET_INPUT:{$targetFieldinValidation}##}}))";
                break;
            case 'file':
                $condition = "(!isset({{##INPUT##}}['size'], {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size']) || {{##INPUT##}}['size'] <= {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size'])";
                break;
            default:
                $this->configErrors[] = "Rule `gt` (greater than) is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        $this->rules['gt'] = [
            'error'    => $error,
            'values' => $targetFieldinValidation,
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['gt'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['gt'] = '" . $error . "';";
        return $this;
    }
    public function lte(string $targetFieldinValidation, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('lte', ['lt'])) {
            return $this;
        }
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be less than or equal to field `{$targetFieldinValidation}`.";
        $error = addcslashes($error, "'\\");
        // Failure condition: input > target
        switch ($this->dataTypeCategory) {
            case 'string':
                $condition = "strlen({{##INPUT##}}) > strlen({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'numeric':
                $condition = "{{##INPUT##}} > {{##TARGET_INPUT:{$targetFieldinValidation}##}}";
                break;
            case 'array':
                $condition = "count({{##INPUT##}}) > count({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'object':
                $condition = "count(get_object_vars({{##INPUT##}})) > count(get_object_vars({{##TARGET_INPUT:{$targetFieldinValidation}##}}))";
                break;
            case 'file':
                $condition = "(!isset({{##INPUT##}}['size'], {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size']) || {{##INPUT##}}['size'] > {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size'])";
                break;
            default:
                $this->configErrors[] = "Rule `lte` (less than or equal) is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        $this->rules['lte'] = [
            'error'    => $error,
            'values' => $targetFieldinValidation,
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['lte'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lte'] = '" . $error . "';";
        return $this;
    }
    public function lt(string $targetFieldinValidation, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('lt', ['lte'])) {
            return $this;
        }
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be less than field `{$targetFieldinValidation}`.";
        $error = addcslashes($error, "'\\");
        // Failure condition: input >= target
        switch ($this->dataTypeCategory) {
            case 'string':
                $condition = "strlen({{##INPUT##}}) >= strlen({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'numeric':
                $condition = "{{##INPUT##}} >= {{##TARGET_INPUT:{$targetFieldinValidation}##}}";
                break;
            case 'array':
                $condition = "count({{##INPUT##}}) >= count({{##TARGET_INPUT:{$targetFieldinValidation}##}})";
                break;
            case 'object':
                $condition = "count(get_object_vars({{##INPUT##}})) >= count(get_object_vars({{##TARGET_INPUT:{$targetFieldinValidation}##}}))";
                break;
            case 'file':
                $condition = "(!isset({{##INPUT##}}['size'], {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size']) || {{##INPUT##}}['size'] >= {{##TARGET_INPUT:{$targetFieldinValidation}##}}['size'])";
                break;
            default:
                $this->configErrors[] = "Rule `lt` is not supported for Data Type `{$this->dataType}`!";
                return $this;
        }
        $this->rules['lt'] = [
            'error'    => $error,
            'values' => $targetFieldinValidation,
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['lt'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lt'] = '" . $error . "';";
        return $this;
    }
    public function same(string $targetField, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('same', ['different'])) {
            return $this;
        }
        // 4. Validate Target Field Parameter
        $targetField = trim($targetField);
        if ($targetField === '') {
            $this->configErrors[] = 'Rule `same` requires a Non-Empty Target Field Name for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Build Failure Condition (Fails if values are NOT strictly identical)
        // Access target field safely using var_export for the key name
        $condition = "{{##INPUT##}} !== ({{##TARGET_INPUT:{$targetField}##}} ?? null)";
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must match field `{$targetField}`.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['same'] = [
            'error'    => $error,
            'values' => $targetField,
            'target' => $targetField,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['same'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['same'] = '" . $error . "';";
        return $this;
    }
    public function different(string $targetField, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('different', ['same'])) {
            return $this;
        }
        // 4. Validate Target Field Parameter
        $targetField = trim($targetField);
        if ($targetField === '') {
            $this->configErrors[] = 'Rule `different` requires a Non-Empty Target Field Name for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Build Failure Condition (Fails if values ARE strictly identical)
        // Enclose target variable in parentheses for strict operator precedence with ?? null
        $condition = "{{##INPUT##}} === ({{##TARGET_INPUT:{$targetField}##}} ?? null)";
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be different from field `{$targetField}`.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['different'] = [
            'error'  => $error,
            'values' => $targetField,
            'target' => $targetField,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['different'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['different'] = '" . $error . "';";
        return $this;
    }

    /* INTEGER+FLOAT-ONLY RULES (meaning they support both Float+Integer data types but no other!) */
    public function multiple_of(int|float|string $value, string $customErrorMsg = ''): self
    {
        // 1. Guard check: allowed on both 'integer' and 'float' data types
        if (!$this->validateRuleUsage('multiple_of', [], ['integer', 'float'], [])) {
            return $this;
        }
        // 2. Validate parameter (accepts integer, float, or numeric string)
        $values = $this->validateRuleMultipleValues('multiple_of', $value, ['integer', 'float']);
        if ($values === false) {
            return $this;
        }
        $target = $values[0];
        // Reject zero or negative values to prevent division-by-zero errors
        if ($target <= 0) {
            $this->configErrors[] = "Rule `multiple_of` Parameter must be a Positive Number greater than 0, `{$target}` given for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 3. Pick the optimal runtime condition based on context
        $isPureInteger = ($this->dataType === 'integer' && is_int($target));
        if ($isPureInteger) {
            // Fast integer modulo operator %
            $condition = "({{##INPUT##}} % {$target}) !== 0";
        } else {
            // Floating-point fmod() with epsilon tolerance to eliminate binary float precision quirks
            $targetFloat = (float)$target;
            $condition = "abs(fmod((float)({{##INPUT##}} ?? 0), {$targetFloat})) > 0.00001";
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a multiple of {$target}.";
        $error = addcslashes($error, "'\\");
        // 4. Store compiled rule
        $this->rules['multiple_of'] = [
            'values' => $values,
            'targetValue'    => $target,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['multiple_of'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['multiple_of'] = '" . $error . "';";
        return $this;
    }

    /* INTEGER-ONLY RULES */
    public function single_digit(array|string $allowedDigitsLeaveEmptyForAll = [], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('single_digit', ['min_digits', 'max_digits', 'digits', 'decimal', 'min_decimal', 'max_decimal', 'between_decimal'], [], ['string', 'integer'])) {
            return $this;
        }
        $digits = $this->validateRuleMultipleValues('single_digit', $allowedDigitsLeaveEmptyForAll, ['integer'], true);
        if ($digits === false) {
            return $this;
        }
        // 4. Normalize & Validate Input Parameters
        $invalidDigits = [];
        foreach ($digits as $d) {
            if (!preg_match('/^\d$/', $d)) {
                $invalidDigits[] = $d;
            }
        }
        if (!empty($invalidDigits)) {
            $this->configErrors[] = 'Rule `single_digit` received invalid digit(s) (`' . implode(', ', $invalidDigits) . '`) for Input Key `{{##INPUT_KEY##}}`! Each allowed entry must be a single digit (0-9).';
            return $this;
        }
        // 5. Build Failure Condition
        if (empty($digits)) {
            // Opcode-level fast path for general single-digit check
            $condition = 'strlen({{##INPUT##}}) !== 1 || !ctype_digit({{##INPUT##}})';
            $defaultError = "Field `{{##INPUT_KEY##}}` must be a single digit (0-9).";
        } else {
            $exportedDigits = var_export(array_values($digits), true);
            $condition = 'strlen({{##INPUT##}}) !== 1 || !in_array({{##INPUT##}}, ' . $exportedDigits . ', true)';
            $defaultError = "Field `{{##INPUT_KEY##}}` must be one of the following digits: " . implode(', ', $digits) . ".";
        }
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : $defaultError;
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['single_digit'] = [
            'values' => $digits,
            'allowed_digits' => $digits,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['single_digit'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['single_digit'] = '" . $error . "';";
        return $this;
    }
    public function digits(int $exactNumberOfDigits, string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'integer' data type only
        if (!$this->validateRuleUsage('digits', ['min_digits', 'max_digits', 'digits_between'], ['integer'], [])) {
            return $this;
        }
        // 2. Validate parameter using rule values helper
        $values = $this->validateRuleMultipleValues('digits', $exactNumberOfDigits, ['integer']);
        if ($values === false) {
            return $this;
        }
        $count = $values[0];
        if ($count < 1) {
            $this->configErrors[] = "Rule `digits` parameter must be a positive integer greater than 0 for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 3. Compile failure condition (uses abs() so negative signs are not counted as digits)
        $condition = "strlen((string)abs({{##INPUT##}})) !== {$count}";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be exactly {$count} digits.";
        $error = addcslashes($error, "'\\");
        // 4. Store compiled rule
        $this->rules['digits'] = [
            'values' =>  $values,
            'digits'   => $count,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['digits'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['digits'] = '" . $error . "';";
        return $this;
    }
    public function min_digits(int $minNumberOfDigits, string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'integer' data type only
        if (!$this->validateRuleUsage('min_digits', ['digits', 'single_digit', 'digits_between'], ['integer'], [])) {
            return $this;
        }
        // 2. Validate parameter
        $values = $this->validateRuleMultipleValues('min_digits', $minNumberOfDigits, ['integer']);
        if ($values === false) {
            return $this;
        }
        $min = $values[0];
        if ($min < 1) {
            $this->configErrors[] = "Rule `min_digits` parameter must be a positive integer greater than 0 for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 3. Compile failure condition
        $condition = "strlen((string)abs({{##INPUT##}})) < {$min}";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must have at least {$min} digits.";
        $error = addcslashes($error, "'\\");
        // 4. Store compiled rule
        $this->rules['min_digits'] = [
            'values' => $min,
            'min'      => $min,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['min_digits'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['min_digits'] = '" . $error . "';";
        return $this;
    }
    public function max_digits(int $maxNumberOfDigits, string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'integer' data type only
        if (!$this->validateRuleUsage('max_digits', ['digits', 'single_digit', 'digits_between'], ['integer'], [])) {
            return $this;
        }
        // 2. Validate parameter
        $values = $this->validateRuleMultipleValues('max_digits', $maxNumberOfDigits, ['integer']);
        if ($values === false) {
            return $this;
        }
        $max = $values[0];
        if ($max < 1) {
            $this->configErrors[] = "Rule `max_digits` parameter must be a positive integer greater than 0 for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // Build-time check: prevent max_digits from being smaller than an existing min_digits rule
        if (isset($this->rules['min_digits']) && $this->rules['min_digits']['min'] > $max) {
            $this->configErrors[] = "Rule `max_digits` ({$max}) cannot be smaller than `min_digits` ({$this->rules['min_digits']['min']}) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 3. Compile failure condition
        $condition = "strlen((string)abs({{##INPUT##}})) > {$max}";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not exceed {$max} digits.";
        $error = addcslashes($error, "'\\");
        // 4. Store compiled rule
        $this->rules['max_digits'] = [
            'values' => $max,
            'max'      => $max,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['max_digits'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['max_digits'] = '" . $error . "';";
        return $this;
    }
    public function digits_between(int $min_digits, int $max_digits, string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'integer' data type only
        if (!$this->validateRuleUsage('digits_between', ['digits', 'min_digits', 'max_digits', 'single_digit'], ['integer'], [])) {
            return $this;
        }
        // 2. Validate parameters through rule values helper
        $values = $this->validateRuleMultipleValues('digits_between', [$min_digits, $max_digits], ['integer']);
        if ($values === false) {
            return $this;
        }
        $minVal = $values[0];
        $maxVal = $values[1];
        if ($minVal < 1 || $maxVal < 1) {
            $this->configErrors[] = "Rule `digits_between` Parameter values must be Positive Integers greater than 0 for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if ($minVal > $maxVal) {
            $this->configErrors[] = "Rule `digits_between` min_digits ({$minVal}) cannot be greater than max_digits ({$maxVal}) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 3. Compile failure condition (ignoring sign via abs())
        $len = "strlen((string)abs({{##INPUT##}}))";
        $condition = "({$len} < {$minVal} || {$len} > {$maxVal})";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be between {$minVal} and {$maxVal} digits.";
        $error = addcslashes($error, "'\\");
        // 4. Store compiled rule
        $this->rules['digits_between'] = [
            'values' => [
                'min'      => $minVal,
                'max'      => $maxVal,
            ],
            'min'      => $minVal,
            'max'      => $maxVal,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['digits_between'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['digits_between'] = '" . $error . "';";
        return $this;
    }

    /* FLOAT-ONLY RULES */
    public function decimal(array|string|int $decimalValues, string $customErrorMsg = ''): self
    {
        // 1. Guard check: strictly allowed on 'float' data type only
        if (!$this->validateRuleUsage('decimal', [], ['float'], [])) {
            return $this;
        }
        // 2. Validate and normalize parameters (accepts integers or numeric strings)
        $values = $this->validateRuleMultipleValues('decimal', $decimalValues, ['integer', 'string']);
        if ($values === false) {
            return $this;
        }
        // Must be either 1 value (exact) or 2 values (min, max)
        if (count($values) > 2) {
            $this->configErrors[] = "Rule `decimal` Accepts At Most 2 Parameter Values (min, max) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // Ensure all parameter values are non-negative integers
        $parsedValues = [];
        foreach ($values as $v) {
            $strV = (string)$v;
            if (!ctype_digit($strV)) {
                $this->configErrors[] = "Rule `decimal` Parameter Values must be Non-Negative Integers, `{$strV}` given for Input Key `{{##INPUT_KEY##}}`!";
                return $this;
            }
            $parsedValues[] = (int)$strV;
        }
        $min = $parsedValues[0];
        $max = $parsedValues[1] ?? $min;
        if ($min > $max) {
            $this->configErrors[] = "Rule `decimal` min decimal places ({$min}) cannot be greater than max decimal places ({$max}) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        // 3. Compile failure condition using fast runtime string splitting
        $error = '';
        $decLen = "strlen(explode('.', (string)({{##INPUT##}} ?? ''))[1] ?? '')";
        if ($min === $max) {
            // Exact decimal count check
            $condition = "{$decLen} !== {$min}";
            $error = !empty($customErrorMsg)
                ? $customErrorMsg
                : "Field `{{##INPUT_KEY##}}` must have exactly {$min} decimal places.";
        } else {
            // Range decimal count check
            $condition = "({$decLen} < {$min} || {$decLen} > {$max})";
            $error = !empty($customErrorMsg)
                ? $customErrorMsg
                : "Field `{{##INPUT_KEY##}}` must have between {$min} and {$max} decimal places.";
        }
        $error = addcslashes($error, "'\\");
        // 4. Store compiled rule
        $this->rules['decimal'] = [
            'values' => [
                'min'      => $min,
                'max'      => $max,
            ],
            'min'      => $min,
            'max'      => $max,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['decimal'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['decimal'] = '" . $error . "';";
        return $this;
    }

    /* input type='checkbox'-ONLY RULES */
    public function checked(array|string $allowedValues = [true, 1, '1', 'on', 'yes', 'true', 'checked', 'enabled', 'selected', 'ja'], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('checked', ['unchecked'], ['checkbox'], ['checkbox'])) {
            return $this;
        }
        // 4. Defaults & Parameter Normalization
        $defaults = [true, 1, '1', 'on', 'yes', 'ja', 'true', 'checked', 'enabled', 'selected'];
        $values = $this->validateRuleMultipleValues('checked', $allowedValues, ['integer', 'string', 'boolean'], true);
        if ($values === false) {
            return $this;
        }
        if (empty($allowedValues)) {
            $values = $defaults;
        }
        // 5. Build Failure Condition (Strict in_array check)
        $exportedValues = var_export($values, true);
        $condition = '!in_array({{##INPUT##}}, ' . $exportedValues . ', true)';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be checked.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['checked'] = [
            'values' => $values,
            'exported_values' => $exportedValues,
            'allowed_values' => $values,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['checked'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['checked'] = '" . $error . "';";
        return $this;
    }
    public function unchecked(array|string $allowedValues = [false, 0, '0', 'off', 'no', 'false', 'unchecked', 'disabled', 'unselected', 'nej'], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('unchecked', ['checked'], ['checkbox'], ['checkbox'])) {
            return $this;
        }
        // 4. Defaults & Parameter Normalization
        $defaults = [false, 0, '0', 'off', 'no', 'nej', 'false', 'unchecked', 'disabled', 'unselected'];
        $values = $this->validateRuleMultipleValues('unchecked', $allowedValues, ['integer', 'string', 'boolean'], true);
        if ($values === false) {
            return $this;
        }
        if (empty($allowedValues)) {
            $values = $defaults;
        }
        // 5. Build Failure Condition (Strict in_array check)
        $exportedValues = var_export($values, true);
        $condition = '!in_array({{##INPUT##}}, ' . $exportedValues . ', true)';
        $error = (!empty($customErrorMsg))
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be unchecked.";
        $error = addcslashes($error, "'\\");
        // 6. Store Compiled Rule
        $this->rules['unchecked'] = [
            'values' => $values,
            'exported_values' => $exportedValues,
            'allowed_values' => $values,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##DEBUG##}}{{##ERRORS##}}['unchecked'] = '{$error}';\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_LOOP##}}\n" .
                "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['unchecked'] = '" . $error . "';";
        return $this;
    }

    /* DATABASE-ONLY RULES (calls to database so compiler will check that database connection, table and column exists, NOT rules themselves!) */
    /**
     * Validates that the input value exists in the specified database table and column.
     * IMPORTANT: Validated at global compiler-level to insert driver-specific query logic.
     *
     * @param string $databaseConnection Connection name as defined in `/src/funkphp/config/conns.php`.
     * @param string $table Database table name.
     * @param string $column Database column name.
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function exists(string $databaseConnection, string $table, string $column, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('exists', [], [], ['string', 'boolean', 'numeric', 'null'])) {
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('exists', [$databaseConnection, $table, $column], ['string'], false, true);
        if ($ruleVals === false) {
            return $this;
        }
        [$dbConn, $dbTbl, $dbCol] = $ruleVals;
        // Developer config check: Sanitize table and column identifiers
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbTbl) || !preg_match('/^[a-zA-Z0-9_]+$/', $dbCol)) {
            $this->configErrors[] = "Rule `exists` contains invalid table or column characters (allowed: a-z, 0-9, _) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Selected `{{##INPUT_KEY##}}` does not exist.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "try {\n" .
            "    {{##DB_EXISTS_NOT_FOUND_BLOCK_END_WITH_FINAL_IF_NEGATED_STATEMENT##}} {\n" .
            "        {{##ERRORS##}}['exists'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "} catch (\\Throwable \$e) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['exists'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['exists'] = [
            'values' => [
                'databaseConnection' => $dbConn,
                'table'              => $dbTbl,
                'column'             => $dbCol,
            ],
            'databaseConnection' => $dbConn,
            'table'              => $dbTbl,
            'column'             => $dbCol,
            'error'              => $error,
            'compiled'           => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['exists'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the input value does not already exist in the specified database table and column.
     * IMPORTANT: Validated at global compiler-level to insert driver-specific query logic.
     *
     * @param string $databaseConnection Connection name as defined in `/src/funkphp/config/conns.php`.
     * @param string $table Database table name.
     * @param string $column Database column name.
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function unique(string $databaseConnection, string $table, string $column, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('unique', [], [], ['string', 'boolean', 'numeric', 'null'])) {
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('unique', [$databaseConnection, $table, $column], ['string'], false, true);
        if ($ruleVals === false) {
            return $this;
        }
        [$dbConn, $dbTbl, $dbCol] = $ruleVals;
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbTbl) || !preg_match('/^[a-zA-Z0-9_]+$/', $dbCol)) {
            $this->configErrors[] = "Rule `unique` contains invalid table or column characters (allowed: a-z, 0-9, _) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be unique. Value already exists in database.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "try {\n" .
            "    {{##DB_UNIQUE_ALREADY_EXISTS_BLOCK_END_WITH_FINAL_IF_NEGATED_STATEMENT##}} {\n" .
            "        {{##ERRORS##}}['unique'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "} catch (\\Throwable \$e) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['unique'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['unique'] = [
            'values' => [
                'databaseConnection' => $dbConn,
                'table'              => $dbTbl,
                'column'             => $dbCol,
            ],
            'databaseConnection' => $dbConn,
            'table'              => $dbTbl,
            'column'             => $dbCol,
            'error'              => $error,
            'compiled'           => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['unique'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates uniqueness while ignoring a specific record ID (useful for UPDATE operations).
     * IMPORTANT: Validated at global compiler-level to insert driver-specific query logic.
     *
     * @param string $databaseConnection Connection name as defined in `/src/funkphp/config/conns.php`.
     * @param string $table Database table name.
     * @param string $column Database column name to check uniqueness against.
     * @param string $ignoreColumn Primary key or identifier column to ignore (e.g. 'id').
     * @param string|int|float $ignoreValueFromCPath Dot-notation Path to Fetch Value from `Global Configuration Array Variable $c` (e.g., 'req.params.id' or 'shared.user.id' becomes `$c['req']['params']['id']` and `$c['shared']['user']['id]`). This allows for fetching dynamic value from the same place without knowing the value beforehand!
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function unique_except(string $databaseConnection, string $table, string $column, string $ignoreColumn, string|int|float $ignoreValueFromCPath, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('unique_except', [], [], [])) {
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('unique_except', [$databaseConnection, $table, $column, $ignoreColumn], ['string'], false, true);
        if ($ruleVals === false) {
            return $this;
        }
        [$dbConn, $dbTbl, $dbCol, $ignoreCol] = $ruleVals;
        $ruleVals2 = $this->validateRuleMultipleValues('unique_except', [$ignoreValueFromCPath], ['string', 'integer', 'float'], false, true);
        if ($ruleVals2 === false) {
            return $this;
        }
        [$ignoreValueCPath] = $ruleVals2;
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbTbl) || !preg_match('/^[a-zA-Z0-9_]+$/', $dbCol) || !preg_match('/^[a-zA-Z0-9_]+$/', $ignoreCol)) {
            $this->configErrors[] = "Rule `unique_except` contains invalid identifier characters for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if (empty($ignoreValueCPath) || !preg_match('/^[a-zA-Z0-9_\']+(\.[a-zA-Z0-9_\']+)*$/', $ignoreValueCPath)) {
            $this->configErrors[] = "Rule `unique_except` parameter `$ignoreValueFromCPath` must be a valid dot-notation path (e.g. `req.params.id` or `shared.user_id`) for Input Key `{{##INPUT_KEY##}}`! It ALWAYS uses the Globally Available Configuration Array Variable `\$c` as root starting point!";
            return $this;
        }
        // Convert dot notation "req.matched_params.id" into "$c['req']['matched_params']['id'] ?? null"
        // using "cli_bracketfy" which also makes '5' become ['5'] where as just 5 would become [5]!
        $pathParts = explode('.', $ignoreValueCPath);
        $compiledCPath = "\$c" . cli_bracketfy($pathParts) . " ?? null";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be unique and cannot be the current one.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "try {\n" .
            "        \$ignoreValue = {$compiledCPath};\n" .
            "    {{##DB_UNIQUE_EXCEPT_ALREADY_EXISTS_BLOCK_END_WITH_FINAL_IF_NEGATED_STATEMENT##}} {\n" .
            "        {{##ERRORS##}}['unique_except'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "} catch (\\Throwable \$e) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['unique_except'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['unique_except'] = [
            'values' => [
                'databaseConnection' => $dbConn,
                'table'              => $dbTbl,
                'column'             => $dbCol,
                'ignoreColumn'       => $ignoreCol,
                'ignoreValueFromCPath' => $compiledCPath,
            ],
            'databaseConnection' => $dbConn,
            'table'              => $dbTbl,
            'column'             => $dbCol,
            'ignoreColumn'       => $ignoreCol,
            'ignoreValueFromCPath' => $compiledCPath,
            'error'              => $error,
            'compiled'           => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['unique_except'] = '" . $error . "';";
        return $this;
    }

    /* FILES-ONLY RULES (accesses $_FILES array to validate things like filetype, filesize, dimensions if applicable, and so on) */
    /**
     * Validates that the uploaded file meets a minimum file size threshold.
     *
     * @param int|float $minSize Minimum file size threshold. B = Bytes
     * @param 'B'|'Bytes'|'KB'|'MB'|'GB'|'PB' $unit Unit of measurement. Defaults to 'KB' (1024 bytes).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_min(int|float $minSize, string $unit = 'KB', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_min', ['file_size', 'file_between', 'min', 'size', 'between'], ['file'], ['file'])) {
            return $this;
        }
        if (trim($unit) === '') {
            $this->configErrors[] = "Rule `file_min` Parameter `\$unit` must be A Non-Empty String for Input Key `{{##INPUT_KEY##}}`. Choose between the following Values: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        if (!in_array($unit, ['B', 'bytes', 'KB', 'MB', 'GB', 'PB'])) {
            $this->configErrors[] = "Rule `file_min` receives an invalid unit `[{$unit}]` for Input Key `{{##INPUT_KEY##}}`. Choose between: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('file_min', [$minSize], ['integer', 'float']);
        if ($ruleVals === false) {
            return $this;
        }
        $minSizeBytes = cli_parseFileSizeToBytes($minSize, $unit);
        if ($minSizeBytes === false) {
            $this->configErrors[] = "Rule `file_min` receives an invalid size or unit `[{$minSize} {$unit}]` for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must be at least {$minSize} {$unit}.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!isset({{##INPUT##}}['size']) || {{##INPUT##}}['size'] < {$minSizeBytes}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_min'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['file_min'] = [
            'values' => [
                'minSize'       => $minSize,
                'unit'          => $unit,
                'bytes'         => $minSizeBytes,
            ],
            'minSize'       => $minSize,
            'unit'          => $unit,
            'bytes'         => $minSizeBytes,
            'error'         => $error,
            'compiled'      => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_min'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the uploaded file does not exceed a maximum file size limit.
     *
     * @param int|float $maxSize Maximum allowed file size.
     * @param 'B'|'bytes'|'KB'|'MB'|'GB'|'PB' $unit Unit of measurement. Defaults to 'KB' (1024 bytes).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_max(int|float $maxSize, string $unit = 'KB', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_max', ['file_size', 'file_between', 'max', 'size', 'between'], ['file'], ['file'])) {
            return $this;
        }
        if (trim($unit) === '') {
            $this->configErrors[] = "Rule `file_max` Parameter `\$unit` must be A Non-Empty String for Input Key `{{##INPUT_KEY##}}`. Choose between the following Values: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        if (!in_array($unit, ['B', 'bytes', 'KB', 'MB', 'GB', 'PB'])) {
            $this->configErrors[] = "Rule `file_max` receives an invalid unit `[{$unit}]` for Input Key `{{##INPUT_KEY##}}`. Choose between: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('file_max', [$maxSize], ['integer', 'float']);
        if ($ruleVals === false) {
            return $this;
        }
        $maxSizeBytes = cli_parseFileSizeToBytes($maxSize, $unit);
        if ($maxSizeBytes === false) {
            $this->configErrors[] = "Rule `file_max` receives an invalid size or unit `[{$maxSize} {$unit}]` for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must not exceed {$maxSize} {$unit}.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!isset({{##INPUT##}}['size']) || {{##INPUT##}}['size'] > {$maxSizeBytes}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_max'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['file_max'] = [
            'values' => [
                'maxSize'       => $maxSize,
                'unit'          => $unit,
                'bytes'         => $maxSizeBytes,
            ],
            'maxSize'       => $maxSize,
            'unit'          => $unit,
            'bytes'         => $maxSizeBytes,
            'error'         => $error,
            'compiled'      => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_max'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the uploaded file size falls inclusively within a specified range.
     *
     * @param int|float $minSize Minimum allowed file size.
     * @param int|float $maxSize Maximum allowed file size.
     * @param 'B'|'bytes'|'KB'|'MB'|'GB'|'PB' $unit Unit of measurement. Defaults to 'KB' (1024 bytes).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_between(int|float $minSize, int|float $maxSize, string $unit = 'KB', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_between', ['file_min', 'file_max', 'file_size', 'min', 'size', 'between', 'max'], [], ['file'])) {
            return $this;
        }
        if (trim($unit) === '') {
            $this->configErrors[] = "Rule `file_between` Parameter `\$unit` must be A Non-Empty String for Input Key `{{##INPUT_KEY##}}`. Choose between the following Values: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        if (!in_array($unit, ['B', 'bytes', 'KB', 'MB', 'GB', 'PB'])) {
            $this->configErrors[] = "Rule `file_between` receives an invalid unit `[{$unit}]` for Input Key `{{##INPUT_KEY##}}`. Choose between: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('file_between', [$minSize, $maxSize], ['integer', 'float'], false, true);
        if ($ruleVals === false) {
            return $this;
        }
        [$minVal, $maxVal] = $ruleVals;
        if ($minVal >= $maxVal) {
            $this->configErrors[] = "Rule `file_between` requires minSize ({$minVal}) to be strictly less than maxSize ({$maxVal}) for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $minSizeBytes = cli_parseFileSizeToBytes($minVal, $unit);
        $maxSizeBytes = cli_parseFileSizeToBytes($maxVal, $unit);
        if ($minSizeBytes === false || $maxSizeBytes === false) {
            $this->configErrors[] = "Rule `file_between` receives invalid size or unit `[{$minVal}-{$maxVal} {$unit}]` for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must be between {$minVal} and {$maxVal} {$unit}.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!isset({{##INPUT##}}['size']) || {{##INPUT##}}['size'] < {$minSizeBytes} || {{##INPUT##}}['size'] > {$maxSizeBytes}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_between'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['file_between'] = [
            'values' => [
                'minSize'       => $minVal,
                'maxSize'       => $maxVal,
                'unit'          => $unit,
                'minBytes'      => $minSizeBytes,
                'maxBytes'      => $maxSizeBytes,
            ],
            'minSize'       => $minVal,
            'maxSize'       => $maxVal,
            'unit'          => $unit,
            'minBytes'      => $minSizeBytes,
            'maxBytes'      => $maxSizeBytes,
            'error'         => $error,
            'compiled'      => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_between'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the uploaded file matches an exact size.
     *
     * @param int|float $exactSize Exact file size.
     * @param 'B'|'bytes'|'KB'|'MB'|'GB'|'PB' $unit Unit of measurement. Defaults to 'KB' (1024 bytes).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_size(int|float $exactSize, string $unit = 'KB', string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_size', ['file_min', 'file_max', 'file_between', 'min', 'between', 'max', 'size'], [], ['file'])) {
            return $this;
        }
        if (trim($unit) === '') {
            $this->configErrors[] = "Rule `file_size` Parameter `\$unit` must be A Non-Empty String for Input Key `{{##INPUT_KEY##}}`. Choose between the following Values: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        if (!in_array($unit, ['B', 'bytes', 'KB', 'MB', 'GB', 'PB'])) {
            $this->configErrors[] = "Rule `file_size` receives an invalid unit `[{$unit}]` for Input Key `{{##INPUT_KEY##}}`. Choose between: `" . join(", ", ['B', 'bytes', 'KB', 'MB', 'GB', 'PB']) . "`!";
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('file_size', [$exactSize], ['integer', 'float'], false, true);
        if ($ruleVals === false) {
            return $this;
        }
        $exactSizeBytes = cli_parseFileSizeToBytes($exactSize, $unit);
        if ($exactSizeBytes === false) {
            $this->configErrors[] = "Rule `file_size` receives an invalid size or unit `[{$exactSize} {$unit}]` for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must be exactly {$exactSize} {$unit}.";
        $error = addcslashes($error, "'\\");
        $compiledCode = "if(!isset({{##INPUT##}}['size']) || {{##INPUT##}}['size'] !== {$exactSizeBytes}) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_size'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['file_size'] = [
            'values' => [
                'exactSize'     => $exactSize,
                'unit'          => $unit,
                'bytes'         => $exactSizeBytes,
            ],
            'exactSize'     => $exactSize,
            'unit'          => $unit,
            'bytes'         => $exactSizeBytes,
            'error'         => $error,
            'compiled'      => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_size'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the uploaded file's extension matches one of the allowed extensions (case-insensitive).
     * Note: This performs a fast check against the filename extension string. For deep magic-bytes inspection, use file_mimetype().
     *
     * @param string|array $extensions Allowed extensions as a comma-separated string (e.g. 'jpg, png, webp' or '.jpg, .png') or array (e.g. ['jpg', 'png']).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_extensions(string|array $extensions, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_extensions', [], ['file'], ['file'])) {
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('file_extensions', $extensions, ['string']);
        if ($ruleVals === false) {
            return $this;
        }
        // 1. Normalize input string or array into a flat array
        $rawExts = $ruleVals;
        $normalizedExts = [];
        // 2. Clean, trim, strip leading dots, and lowercase all extensions
        foreach ($rawExts as $ext) {
            if (!is_string($ext) && !is_numeric($ext)) {
                continue;
            }
            $cleanExt = strtolower(trim((string)$ext));
            $cleanExt = ltrim($cleanExt, '.'); // Handles '.jpg' -> 'jpg'
            if ($cleanExt !== '') {
                $normalizedExts[] = $cleanExt;
            }
        }
        // Format nice user-facing list: ".jpg, .png, .pdf"
        $allowedListStr = implode(', ', array_map(fn($e) => '.' . $e, $normalizedExts));
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must have one of the following extensions: {$allowedListStr}.";
        $error = addcslashes($error, "'\\");
        // Export array into valid PHP code array syntax
        $compiledAllowedArray = var_export($normalizedExts, true);
        $compiledCode = "if(!isset({{##INPUT##}}['name']) || !in_array(strtolower(pathinfo({{##INPUT##}}['name'], PATHINFO_EXTENSION)), {$compiledAllowedArray}, true)) {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_extensions'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['file_extensions'] = [
            'values' => $normalizedExts,
            'allowedExtensions' => $normalizedExts,
            'error'             => $error,
            'compiled'          => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_extensions'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the uploaded file's actual MIME type matches allowed extensions or MIME types.
     * Inspects magic bytes of the uploaded temporary file using PHP's native finfo_file().
     *
     * @param string|array $mimes Allowed extensions (e.g., 'jpg, png, pdf') or full MIME types (e.g., 'image/jpeg, application/pdf').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_mimes(string|array $mimes, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_mimes', [], ['file'], ['file'])) {
            return $this;
        }
        $validMimes = $this->validateRuleMultipleValues('file_mimes', $mimes, ['string']);
        if ($validMimes === false) {
            return $this;
        }
        $allowedMimeTypes = [];
        $unrecognizedItems = [];
        foreach ($validMimes as $item) {
            $itemClean = strtolower(trim($item));
            if (str_contains($itemClean, '/')) {
                // Explicit MIME type (e.g., "image/jpeg" or "application/pdf")
                $allowedMimeTypes[] = $itemClean;
            } else {
                // Extension shortcode (e.g., "jpg", ".png", "pdf")
                $ext = ltrim($itemClean, '.');
                if (isset(self::$extensionToMimeMap[$ext])) {
                    foreach (self::$extensionToMimeMap[$ext] as $mime) {
                        $allowedMimeTypes[] = $mime;
                    }
                } else {
                    $unrecognizedItems[] = $ext;
                }
            }
        }
        if (!empty($unrecognizedItems)) {
            $unrecognizedList = implode(', ', $unrecognizedItems);
            $this->configErrors[] = "Rule `file_mimes` contains unrecognized file extension(s): [{$unrecognizedList}] for Input Key `{{##INPUT_KEY##}}`! OR the `private static array \$extensionToMimeMap = []` Array in class RulesSetAll{} and/or RulesSetFile{} is incomplete. You can add to the list in `/src/cli/core/cli_classes_with_functions.php` if you would like to! Just search for `private static array \$extensionToMimeMap` in that File! OR CONSIDER to use the `callback:FN_NAME` Rule if you need to make a highly specific File Validation that is not covered by the included File-related Rules here!";
            return $this;
        }
        // Deduplicate allowed MIME types array
        $allowedMimeTypes = array_values(array_unique($allowedMimeTypes));
        if (empty($allowedMimeTypes)) {
            $this->configErrors[] = "Rule `file_mimes` requires at least one valid extension or MIME type for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must be of type: " . implode(', ', $validMimes) . '.';
        $error = addcslashes($error, "'\\");
        $compiledAllowedArray = var_export($allowedMimeTypes, true);
        // Compiled PHP: Validates temp file existence, reads magic bytes via finfo, and checks array match
        $compiledCode = "if(" .
            "!isset({{##INPUT##}}['tmp_name']) || " .
            "!is_string({{##INPUT##}}['tmp_name']) || " .
            "!is_uploaded_file({{##INPUT##}}['tmp_name'])" .
            ") {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_mimes'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "} else {\n" .
            "    static \$finfoHandle = null;\n" .
            "    \$finfoHandle ??= finfo_open(FILEINFO_MIME_TYPE);\n" .
            "    \$detectedMime = \$finfoHandle ? finfo_file(\$finfoHandle, {{##INPUT##}}['tmp_name']) : false;\n" .
            "    if (\$detectedMime && (strlen(\$detectedMime) % 2 === 0)) {\n" .
            "        \$mimeHalf = substr(\$detectedMime, 0, strlen(\$detectedMime) >> 1);\n" .
            "        if (\$mimeHalf . \$mimeHalf === \$detectedMime) { \$detectedMime = \$mimeHalf; }\n" .
            "    }\n" .
            "    if (!\$detectedMime || !in_array(strtolower(\$detectedMime), {$compiledAllowedArray}, true)) {\n" .
            "        {{##ERRORS##}}['file_mimes'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "}";
        $this->rules['file_mimes'] = [
            'values' => $mimes,
            'allowedMimeTypes' => $allowedMimeTypes,
            'error'            => $error,
            'compiled'         => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_mimes'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that the uploaded file is a valid image based on magic bytes (finfo_file).
     * By default, checks for jpg, jpeg, png, gif, bmp, webp, avif, heic, heif, tiff, tif, ico.
     * SVG is disabled by default to prevent XSS vulnerabilities, but can be enabled via $allowSVG = true.
     *
     * @param string|array|null $excludeCertainImageTypes Optional extensions or MIME types to exclude (e.g., 'gif, webp' or ['gif', 'webp']).
     * @param string $customErrorMsg Custom error message on validation failure.
     * @param bool $allowSVG Set to true to allow SVG files.
     * @return self
     */
    public function file_image(string|array|null $excludeCertainImageTypes = null, string $customErrorMsg = '', bool $allowSVG = false): self
    {
        if (!$this->validateRuleUsage('file_image', ['file_mimes'], ['file'], ['file'])) {
            return $this;
        }
        // 1. Define base image extensions list & 2. Conditionally include SVG if allowed
        $baseImageExts = [
            'jpg',
            'jpeg',
            'jpe',
            'png',
            'gif',
            'bmp',
            'webp',
            'avif',
            'heic',
            'heif',
            'tiff',
            'tif',
            'ico',
            'jp2',
            'j2k',
            'jpf',
            'jpx',
            'jxl',
            'psd',
            'tga',
            'pnm',
            'pbm',
            'pgm',
            'ppm'
        ];
        if ($allowSVG) {
            $baseImageExts[] = 'svg';
        }
        // 3. Process exclusions if specified
        $excludedItems = [];
        if ($excludeCertainImageTypes !== null) {
            $validExclusions = $this->validateRuleMultipleValues('file_image', $excludeCertainImageTypes, ['string']);
            if ($validExclusions === false) {
                return $this;
            }
            foreach ($validExclusions as $ex) {
                $cleanEx = strtolower(trim((string)$ex));
                $cleanEx = ltrim($cleanEx, '.');
                if ($cleanEx !== '') {
                    if (!in_array($cleanEx, $baseImageExts)) {
                        $this->configErrors[] = "Rule `file_image` for Input Key `{{##INPUT_KEY##}}` has an excluded Image File Type (`$cleanEx`) that does NOT exist in the list of available Image File Types of which you can exclude up until you have only one left:`" . join(", ", $baseImageExts) .  "`! OR CONSIDER to use the `callback:FN_NAME` Rule if you need to make a highly specific File Validation that is not covered by the included File-related Rules here!";
                        return $this;
                    }
                    $excludedItems[] = $cleanEx;
                }
            }
        }
        // 4. Build final allowed MIME types list from base extensions
        $allowedMimeTypes = [];
        foreach ($baseImageExts as $ext) {
            // Skip if this extension was explicitly excluded (e.g., 'gif')
            if (in_array($ext, $excludedItems, true)) {
                continue;
            }
            if (isset(self::$extensionToMimeMap[$ext])) {
                foreach (self::$extensionToMimeMap[$ext] as $mime) {
                    // Skip if specific MIME type was explicitly excluded (e.g., 'image/gif')
                    if (in_array($mime, $excludedItems, true)) {
                        continue;
                    }
                    $allowedMimeTypes[] = $mime;
                }
            }
        }
        $allowedMimeTypes = array_values(array_unique($allowedMimeTypes));
        if (empty($allowedMimeTypes)) {
            $this->configErrors[] = "Rule `file_image` for Input Key `{{##INPUT_KEY##}}` has excluded all known Base Image File Types so no typical uploaded image would be able to be validated. CONSIDER to use the `callback:FN_NAME` Rule if you need to make a highly specific File Validation that is not covered by the included File-related Rules here!";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must be a valid image.";
        $error = addcslashes($error, "'\\");
        $compiledAllowedArray = var_export($allowedMimeTypes, true);
        // 5. Compile runtime PHP check using finfo_file
        $compiledCode = "if(" .
            "!isset({{##INPUT##}}['tmp_name']) || " .
            "!is_string({{##INPUT##}}['tmp_name']) || " .
            "!is_file({{##INPUT##}}['tmp_name']) || " .
            "!is_readable({{##INPUT##}}['tmp_name'])" .
            ") {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_image'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "} else {\n" .
            "    \$finfoHandle = finfo_open(FILEINFO_MIME_TYPE);\n" .
            "    \$detectedMime = \$finfoHandle ? finfo_file(\$finfoHandle, {{##INPUT##}}['tmp_name']) : false;\n" .
            "    if (\$finfoHandle) { finfo_close(\$finfoHandle); }\n" .
            "    if (!\$detectedMime || !in_array(strtolower(\$detectedMime), {$compiledAllowedArray}, true)) {\n" .
            "        {{##ERRORS##}}['file_image'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "}";
        $this->rules['file_image'] = [
            'values' => [
                'allowSVG'          => $allowSVG,
                'excludedTypes'     => $excludedItems,
                'allowedMimeTypes'  => $allowedMimeTypes,
            ],
            'allowSVG'          => $allowSVG,
            'excludedTypes'     => $excludedItems,
            'allowedMimeTypes'  => $allowedMimeTypes,
            'error'             => $error,
            'compiled'          => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_image'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that an uploaded image meets specified dimension constraints (min/max width and height).
     * Supports units in pixels ('px'), inches ('in'), centimeters ('cm'), or millimeters ('mm').
     *
     * @param int|float|null $minWidth Minimum allowed width.
     * @param int|float|null $minHeight Minimum allowed height.
     * @param int|float|null $maxWidth Maximum allowed width.
     * @param int|float|null $maxHeight Maximum allowed height.
     * @param 'px'|'in'|'cm'|'mm' $unitType Unit type: 'px' (default), 'in', 'cm', or 'mm'.
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_dimensions(
        int|float|null $minWidth = null,
        int|float|null $minHeight = null,
        int|float|null $maxWidth = null,
        int|float|null $maxHeight = null,
        string $unitType = 'px',
        string $customErrorMsg = ''
    ): self {
        if (!$this->validateRuleUsage('file_dimensions', [], ['file'], ['file'])) {
            return $this;
        }
        $minWminHmaxWmaxH = $this->validateRuleMultipleValues(
            'file_dimensions',
            [$minWidth, $minHeight, $maxWidth, $maxHeight],
            ['integer', 'float', 'null'],
            false,
            true
        );
        if ($minWminHmaxWmaxH === false) {
            return $this;
        }
        [$minWidth, $minHeight, $maxWidth, $maxHeight] = $minWminHmaxWmaxH;
        // 1. Ensure at least one dimension constraint is provided
        if ($minWidth === null && $minHeight === null && $maxWidth === null && $maxHeight === null) {
            $this->configErrors[] = "Rule `file_dimensions` for Input Key `{{##INPUT_KEY##}}` requires at least one constraint (minWidth, minHeight, maxWidth, or maxHeight)!";
            return $this;
        }
        // Ensure min dimensions do not exceed max dimensions
        if ($minWidth !== null && $maxWidth !== null && $minWidth > $maxWidth) {
            $this->configErrors[] = "Rule `file_dimensions` for Input Key `{{##INPUT_KEY##}}` has minWidth ({$minWidth}) greater than maxWidth ({$maxWidth})!";
            return $this;
        }
        if ($minHeight !== null && $maxHeight !== null && $minHeight > $maxHeight) {
            $this->configErrors[] = "Rule `file_dimensions` for Input Key `{{##INPUT_KEY##}}` has minHeight ({$minHeight}) greater than maxHeight ({$maxHeight})!";
            return $this;
        }
        // 2. Validate unit type
        $unit = strtolower(trim($unitType));
        if ($unit === '') {
            $unit = 'px';
        }
        $validUnits = ['px', 'in', 'cm', 'mm'];
        if (!in_array($unit, $validUnits, true)) {
            $this->configErrors[] = "Rule `file_dimensions` for Input Key `{{##INPUT_KEY##}}` has invalid unit type `{$unitType}`! Allowed units are: " . implode(', ', $validUnits) . '.';
            return $this;
        }
        // 3. Build human-readable error message using original unit
        $constraints = [];
        if ($minWidth !== null)  $constraints[] = "min width: {$minWidth}{$unit}";
        if ($minHeight !== null) $constraints[] = "min height: {$minHeight}{$unit}";
        if ($maxWidth !== null)  $constraints[] = "max width: {$maxWidth}{$unit}";
        if ($maxHeight !== null) $constraints[] = "max height: {$maxHeight}{$unit}";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The Image File `{{##INPUT_KEY##}}` dimensions are invalid (" . implode(', ', $constraints) . ").";
        $error = addcslashes($error, "'\\");
        // 4. Convert unit inputs to standard pixels at compile time (using standard 96 DPI CSS scale)
        $toPixels = static function (?float $val, string $unit): ?float {
            if ($val === null) {
                return null;
            }
            return match ($unit) {
                'in'    => $val * 96.0,
                'cm'    => ($val / 2.54) * 96.0,
                'mm'    => ($val / 25.4) * 96.0,
                default => $val, // 'px'
            };
        };
        $minWidthPx  = $toPixels($minWidth !== null ? (float)$minWidth : null, $unit);
        $minHeightPx = $toPixels($minHeight !== null ? (float)$minHeight : null, $unit);
        $maxWidthPx  = $toPixels($maxWidth !== null ? (float)$maxWidth : null, $unit);
        $maxHeightPx = $toPixels($maxHeight !== null ? (float)$maxHeight : null, $unit);
        $pMinWidth  = $minWidthPx !== null ? (string)$minWidthPx : 'null';
        $pMinHeight = $minHeightPx !== null ? (string)$minHeightPx : 'null';
        $pMaxWidth  = $maxWidthPx !== null ? (string)$maxWidthPx : 'null';
        $pMaxHeight = $maxHeightPx !== null ? (string)$maxHeightPx : 'null';
        // 5. Lightweight compiled runtime check
        $compiledCode = "if(" .
            "!isset({{##INPUT##}}['tmp_name']) || " .
            "!is_string({{##INPUT##}}['tmp_name']) || " .
            "!is_file({{##INPUT##}}['tmp_name']) || " .
            "!is_readable({{##INPUT##}}['tmp_name'])" .
            ") {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_dimensions'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "} else {\n" .
            "    \$imgSizeInfo = @getimagesize({{##INPUT##}}['tmp_name']);\n" .
            "    if (\$imgSizeInfo === false) {\n" .
            "        // File is corrupt or not a readable image format\n" .
            "        {{##ERRORS##}}['file_dimensions'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    } else {\n" .
            "        \$w = (float)\$imgSizeInfo[0];\n" .
            "        \$h = (float)\$imgSizeInfo[1];\n" .
            "        \$minW = {$pMinWidth};\n" .
            "        \$minH = {$pMinHeight};\n" .
            "        \$maxW = {$pMaxWidth};\n" .
            "        \$maxH = {$pMaxHeight};\n" .
            "\n" .
            "        if ((\$minW !== null && \$w < \$minW) || " .
            "(\$minH !== null && \$h < \$minH) || " .
            "(\$maxW !== null && \$w > \$maxW) || " .
            "(\$maxH !== null && \$h > \$maxH)) {\n" .
            "            {{##ERRORS##}}['file_dimensions'] = '{$error}';\n" .
            "            {{##GOTO_STOP_ALL##}}\n" .
            "            {{##GOTO_BAIL##}}\n" .
            "            {{##GOTO_NEXT_RULE##}}\n" .
            "            {{##GOTO_END_FIELD##}}\n" .
            "        }\n" .
            "    }\n" .
            "}";
        $this->rules['file_dimensions'] = [
            'values' => [
                'minWidth'  => $minWidth,
                'minHeight' => $minHeight,
                'maxWidth'  => $maxWidth,
                'maxHeight' => $maxHeight,
                'unitType'  => $unit,
            ],
            'minWidth'  => $minWidth,
            'minHeight' => $minHeight,
            'maxWidth'  => $maxWidth,
            'maxHeight' => $maxHeight,
            'unitType'  => $unit,
            'error'     => $error,
            'compiled'  => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_dimensions'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that an uploaded image's resolution (DPI - Dots Per Inch) falls within specified min/max bounds.
     * Useful for print-on-demand services, photo products, document scanning, and prepress validation.
     * `SUPER-IMPORTANT:` This Rule has NO FALLBACK if it fails to detect DPI so be prepared that some image files might fail (for better or worse) to validate as a result of it. ONLY use this Rule when a detectable and exact range of DPI are crucial for your use-case!
     *
     * @param int|float|null $minDpi Minimum allowed DPI (e.g., 300 for print quality).
     * @param int|float|null $maxDpi Maximum allowed DPI.
     * @param string $customErrorMsg Custom error message on validation failure.

     * @return self
     */
    public function file_dpi(
        int|float|null $minDpi = null,
        int|float|null $maxDpi = null,
        string $customErrorMsg = ''
    ): self {
        if (!$this->validateRuleUsage('file_dpi', [], ['file'], ['file'])) {
            return $this;
        }
        $minMaxDpi = $this->validateRuleMultipleValues(
            'file_dpi',
            [$minDpi, $maxDpi],
            ['integer', 'float', 'null'],
            false,
            true
        );
        if ($minMaxDpi === false) {
            return $this;
        }
        [$minDpi, $maxDpi] = $minMaxDpi;
        // 1. Ensure at least one DPI constraint is provided & 2. Ensure minDpi does not exceed maxDpi
        if ($minDpi === null && $maxDpi === null) {
            $this->configErrors[] = "Rule `file_dpi` for Input Key `{{##INPUT_KEY##}}` requires at least one constraint (minDpi or maxDpi)!";
            return $this;
        }
        if ($minDpi !== null && $maxDpi !== null && $minDpi > $maxDpi) {
            $this->configErrors[] = "Rule `file_dpi` for Input Key `{{##INPUT_KEY##}}` has minDpi ({$minDpi}) greater than maxDpi ({$maxDpi})!";
            return $this;
        }
        // 3. Build human-readable default error message
        $constraints = [];
        if ($minDpi !== null) $constraints[] = "min DPI: {$minDpi}";
        if ($maxDpi !== null) $constraints[] = "max DPI: {$maxDpi}";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The Image File `{{##INPUT_KEY##}}` resolution (DPI) is invalid (" . implode(', ', $constraints) . ").";
        $error = addcslashes($error, "'\\");
        $pMinDpi = $minDpi !== null ? (string)(float)$minDpi : 'null';
        $pMaxDpi = $maxDpi !== null ? (string)(float)$maxDpi : 'null';
        // 4. Compiled runtime check
        // Attempt 1: Read EXIF metadata (JPEG / TIFF) and then Attempt 2: Read PNG pHYs chunk if EXIF produced no DPI
        // If DPI metadata is completely missing or failed to extract (=if (\$detectedDpi === null)), treat as invalid
        $compiledCode = "if(" .
            "!isset({{##INPUT##}}['tmp_name']) || " .
            "!is_string({{##INPUT##}}['tmp_name']) || " .
            "!is_file({{##INPUT##}}['tmp_name']) || " .
            "!is_readable({{##INPUT##}}['tmp_name'])" .
            ") {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_dpi'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "} else {\n" .
            "    \$tmpFile = {{##INPUT##}}['tmp_name'];\n" .
            "    \$detectedDpi = null;\n" .
            "\n" .
            "    \n" .
            "    if (function_exists('exif_read_data')) {\n" .
            "        \$exif = @exif_read_data(\$tmpFile);\n" .
            "        if (!empty(\$exif['XResolution'])) {\n" .
            "            \$resParts = explode('/', (string)\$exif['XResolution']);\n" .
            "            \$rawRes = (count(\$resParts) === 2 && (float)\$resParts[1] > 0)\n" .
            "                ? (float)\$resParts[0] / (float)\$resParts[1]\n" .
            "                : (float)\$resParts[0];\n" .
            "\n" .
            "            if (\$rawRes > 0) {\n" .
            "                \$unit = \$exif['ResolutionUnit'] ?? 2; // 2 = inches, 3 = cm\n" .
            "                \$detectedDpi = (\$unit == 3) ? round(\$rawRes * 2.54) : round(\$rawRes);\n" .
            "            }\n" .
            "        }\n" .
            "    }\n" .
            "\n" .
            "    \n" .
            "    if (\$detectedDpi === null) {\n" .
            "        \$handle = @fopen(\$tmpFile, 'rb');\n" .
            "        if (\$handle !== false) {\n" .
            "            \$header = fread(\$handle, 8);\n" .
            "            if (\$header === \"\\x89PNG\\x0d\\x0a\\x1a\\x0a\") {\n" .
            "                while (!feof(\$handle)) {\n" .
            "                    \$chunkHeader = fread(\$handle, 8);\n" .
            "                    if (strlen(\$chunkHeader) < 8) { break; }\n" .
            "                    \$chunkLen = unpack('Nlen', substr(\$chunkHeader, 0, 4))['len'];\n" .
            "                    \$chunkType = substr(\$chunkHeader, 4, 4);\n" .
            "\n" .
            "                    if (\$chunkType === 'pHYs') {\n" .
            "                        \$data = fread(\$handle, 9);\n" .
            "                        if (strlen(\$data) === 9) {\n" .
            "                            \$phys = unpack('Nx/Ny/Cunit', \$data);\n" .
            "                            if (\$phys['unit'] === 1 && \$phys['x'] > 0) { // unit 1 = meters\n" .
            "                                \$detectedDpi = round(\$phys['x'] * 0.0254);\n" .
            "                            }\n" .
            "                        }\n" .
            "                        break;\n" .
            "                    }\n" .
            "                    fseek(\$handle, \$chunkLen + 4, SEEK_CUR); // Skip data + CRC\n" .
            "                }\n" .
            "            }\n" .
            "            fclose(\$handle);\n" .
            "        }\n" .
            "    }\n" .
            "\n" .
            "    \n" .
            "    if (\$detectedDpi === null) {\n" .
            "        {{##ERRORS##}}['file_dpi'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    } else {\n" .
            "        \$minD = {$pMinDpi};\n" .
            "        \$maxD = {$pMaxDpi};\n" .
            "\n" .
            "        if ((\$minD !== null && \$detectedDpi < \$minD) || " .
            "(\$maxD !== null && \$detectedDpi > \$maxD)) {\n" .
            "            {{##ERRORS##}}['file_dpi'] = '{$error}';\n" .
            "            {{##GOTO_STOP_ALL##}}\n" .
            "            {{##GOTO_BAIL##}}\n" .
            "            {{##GOTO_NEXT_RULE##}}\n" .
            "            {{##GOTO_END_FIELD##}}\n" .
            "        }\n" .
            "    }\n" .
            "}";
        $this->rules['file_dpi'] = [
            'values' => [
                'minDpi'   => $minDpi,
                'maxDpi'   => $maxDpi,
            ],
            'minDpi'   => $minDpi,
            'maxDpi'   => $maxDpi,
            'error'    => $error,
            'compiled' => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_dpi'] = '" . $error . "';";
        return $this;
    }
    /**
     * Validates that an uploaded file matches a specific character encoding (e.g., 'UTF-8', 'ASCII', 'ISO-8859-1').
     * Streams through the entire file in 64KB chunks to guarantee 100% byte integrity without memory exhaustion.
     * Includes smart boundary back-off to handle multibyte characters split across chunk boundaries.
     *
     * @param string $encoding Target encoding to check (e.g., 'UTF-8', 'ASCII', 'ISO-8859-1').
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function file_encoding(string $encoding, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('file_encoding', [], ['file'], ['file'])) {
            return $this;
        }
        $cleanEncoding = strtoupper(trim($encoding));
        if ($cleanEncoding === '') {
            $this->configErrors[] = "Rule `file_encoding` for Input Key `{{##INPUT_KEY##}}` requires a valid non-empty encoding string!";
            return $this;
        }
        // Validate that the requested encoding is supported by PHP's multibyte extension
        $supportedEncodings = array_map('strtoupper', mb_list_encodings());
        if (!in_array($cleanEncoding, $supportedEncodings, true)) {
            $this->configErrors[] = "Rule `file_encoding` for Input Key `{{##INPUT_KEY##}}` specified an unsupported encoding `{$encoding}`! Supported encodings include: UTF-8, ASCII, ISO-8859-1, UTF-16, UTF-32, etc.";
            return $this;
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "The file `{{##INPUT_KEY##}}` must be encoded in {$cleanEncoding}.";
        $error = addcslashes($error, "'\\");
        // Compiled runtime check streaming file in 64KB chunks with boundary back-off
        $compiledCode = "if(" .
            "!isset({{##INPUT##}}['tmp_name']) || " .
            "!is_string({{##INPUT##}}['tmp_name']) || " .
            "!is_file({{##INPUT##}}['tmp_name']) || " .
            "!is_readable({{##INPUT##}}['tmp_name'])" .
            ") {\n" .
            "    {{##DEBUG##}}{{##ERRORS##}}['file_encoding'] = '{$error}';\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_LOOP##}}\n" .
            "    {{##GOTO_EXIT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_INNER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_EXIT_OUTER_LOOP##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "} else {\n" .
            "    \$filePath = {{##INPUT##}}['tmp_name'];\n" .
            "    \$targetEncoding = '{$cleanEncoding}';\n" .
            "    \$handle = @fopen(\$filePath, 'rb');\n" .
            "    \$isValidEncoding = true;\n" .
            "\n" .
            "    if (\$handle === false) {\n" .
            "        \$isValidEncoding = false;\n" .
            "    } else {\n" .
            "        \$buffer = '';\n" .
            "        \$chunkSize = 65536; // 64 KB streaming buffer\n" .
            "\n" .
            "        while (!feof(\$handle)) {\n" .
            "            \$readChunk = fread(\$handle, \$chunkSize);\n" .
            "            if (\$readChunk === false) { \$isValidEncoding = false; break; }\n" .
            "            if (\$readChunk === '') { continue; }\n" .
            "\n" .
            "            \$data = \$buffer . \$readChunk;\n" .
            "            \$buffer = '';\n" .
            "\n" .
            "            // Check if entire chunk is valid encoding\n" .
            "            if (!mb_check_encoding(\$data, \$targetEncoding)) {\n" .
            "                // Handle possible multibyte character split at chunk boundary\n" .
            "                \$dataLen = strlen(\$data);\n" .
            "                \$foundValidBoundary = false;\n" .
            "\n" .
            "                // Back off up to 4 bytes from chunk end to detect split multibyte sequences\n" .
            "                for (\$backoff = 1; \$backoff <= 4 && \$backoff < \$dataLen; \$backoff++) {\n" .
            "                    \$subData = substr(\$data, 0, \$dataLen - \$backoff);\n" .
            "                    if (mb_check_encoding(\$subData, \$targetEncoding)) {\n" .
            "                        \$buffer = substr(\$data, \$dataLen - \$backoff);\n" .
            "                        \$foundValidBoundary = true;\n" .
            "                        break;\n" .
            "                    }\n" .
            "                }\n" .
            "\n" .
            "                if (!\$foundValidBoundary) {\n" .
            "                    \$isValidEncoding = false;\n" .
            "                    break;\n" .
            "                }\n" .
            "            }\n" .
            "        }\n" .
            "        fclose(\$handle);\n" .
            "\n" .
            "        // Validate any remaining carryover buffer\n" .
            "        if (\$isValidEncoding && \$buffer !== '' && !mb_check_encoding(\$buffer, \$targetEncoding)) {\n" .
            "            \$isValidEncoding = false;\n" .
            "        }\n" .
            "    }\n" .
            "\n" .
            "    if (!\$isValidEncoding) {\n" .
            "        {{##ERRORS##}}['file_encoding'] = '{$error}';\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "}";
        $this->rules['file_encoding'] = [
            'values' => [$encoding, 'encoding' => $cleanEncoding,],
            'encoding' => $cleanEncoding,
            'error'    => $error,
            'compiled' => $compiledCode,
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['file_encoding'] = '" . $error . "';";
        return $this;
    }
}
/* !!! GLOBAL ENTRY POIUNTS FOR THE "RULESET-DATATYPES ABOVE !!! */
/**
 * Global entry point for initializing a fluent validation rule set.
 *
 * @param 'file'|'string'|'date'|'checkbox'|'integer'|'float'|'boolean'|'number'|'array'|'arr'|'object'|'json'|'password'|'password_match'|'email'|'url'|'ip' $dataType
 * @param string $customErrorMsg
 * @param 'list'|'associative'|'' $setArrayTypeToListOrAssociative
 * @return RuleSetAll
 */
function data(string $dataType, string $customErrorMsgDataTypeOnly = '', string $customErrorMsgOnlyForParameters = ''): RuleSet
{
    return (new RuleSet())->setDatatype($dataType, $customErrorMsgDataTypeOnly, $customErrorMsgOnlyForParameters);
}

/*
 * FunkPHP Classes used in the `/src/funkphp/config/app.php`
*/
/*
 * Class C is the "source of truth" regarding app state, app configuration (globally, on method leve, on route level)
 * such as `request, post-response, routes, middlewares, individual routse and their piped functions`
*/
class C
{
    // Default booleans for compile(), run()
    private bool $FunkPHPcompiled = false;
    private bool $FunkPHPbooted = false;
    // Default values for $c that is returned via
    // Use the `config()->setUse<cVariableKey>Global` to change each value!
    // It is set to the $c variable before globally starting executing!
    public array $cVariable =  [
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
    ];

    // $batches (= can be validated for compilation)
    // $invalidBatches (= cannot be validated for compilation based on initial value check)
    // $errors (= stored errors where )
    private array $batches = [
        'all' => [],
        'config' => [],
        'pipes' => [
            'request' => [],
            'post_response' => [],
            'middlewares' => [],
            'routes' => [
                'HEAD' => [],
                'GET' => [],
                'PUT' => [],
                'PATCH' => [],
                'POST' => [],
                'DELETE' => [],
            ],
        ],
        'data' => [
            'tables' => [],
            'query' => [],
            'validation' => [],
            'sql' => []
        ],
        'pages' => [
            'compiled' => [],
            'components' => [],
            'layouts' => [],
            'partials' => []
        ],
    ];
    private array $invalidBatches = [
        'all' => [],
        'config' => [],
        'pipes' => [
            'request' => [],
            'post_response' => [],
            'middlewares' => [],
            'routes' => [
                'HEAD' => [],
                'GET' => [],
                'PUT' => [],
                'PATCH' => [],
                'POST' => [],
                'DELETE' => [],
            ],
        ],
        'data' => [
            'tables' => [],
            'query' => [],
            'validation' => [],
            'sql' => []
        ],
        'pages' => [
            'compiled' => [],
            'components' => [],
            'layouts' => [],
            'partials' => []
        ],
    ];
    private array $errors = [
        'all' => [],
        'config' => [],
        'pipes' => [
            'request' => [],
            'post_response' => [],
            'middlewares' => [],
            'routes' => [
                'HEAD' => [],
                'GET' => [],
                'PUT' => [],
                'PATCH' => [],
                'POST' => [],
                'DELETE' => [],
            ],
        ],
        'data' => [
            'tables' => [],
            'query' => [],
            'validation' => [],
            'sql' => []
        ],
        'pages' => [
            'compiled' => [],
            'components' => [],
            'layouts' => [],
            'partials' => []
        ],
    ];
    // $cached = (Attempted) Access to any file/function and/or file=>function in a DRY fashion!
    private array $cached = [
        'fileNotFound' => [],
        'fileNotRadable' => [],
        'expectedNamespaceNotFound' => [],
        'expectedFileFunctionNotFound' => [],
        'all' => [],
        'config' => [],
        'pipes' => [
            'request' => [],
            'post_response' => [],
            'middlewares' => [],
            'routes' => [
                'allRouteAliases' => [],
                'HEAD' => [],
                'GET' => [],
                'PUT' => [],
                'PATCH' => [],
                'POST' => [],
                'DELETE' => [],
            ],
        ],
        'data' => [
            'tables' => [],
            'query' => [],
            'validation' => [],
            'sql' => []
        ],
        'pages' => [
            'compiled' => [],
            'components' => [],
            'layouts' => [],
            'partials' => []
        ],
    ];

    // $compiled = The entire compiled code that can either be executed as is OR
    // be exported to the `/src/funkphp/FunkPHPDeployment.php` File!
    private array $compiled = [
        'all' => [],
        'config' => [],
        'pipes' => [
            'request' => [],
            'post_response' => [],
            'middlewares' => [],
            'routes' => [
                'HEAD' => [],
                'GET' => [],
                'PUT' => [],
                'PATCH' => [],
                'POST' => [],
                'DELETE' => [],
            ],
        ],
        'data' => [
            'tables' => [],
            'query' => [],
            'validation' => [],
            'sql' => []
        ],
        'pages' => [
            'compiled' => [],
            'components' => [],
            'layouts' => [],
            'partials' => []
        ],
    ];

    // NAVIGATION VARIABLES+METHODS IN IDE config()->
    private ?FunkConfig $configScope = null;
    private ?FunkPipesRequest $pipesRequestScope = null;
    private ?FunkPipesPostResponse $pipesPostResponseScope = null;
    private ?FunkRoutes $routesScope = null;

    // ->config()
    // and can jump to->pipesRequest(),->pipesPostResponse() or ->routes()
    public function config(): FunkConfig
    {
        return $this->configScope ??= new FunkConfig($this);
    }
    // ->pipesRequest()
    // and can jump back to ->config(), or jump to ->routes()
    public function pipesRequest(): FunkPipesRequest
    {
        return $this->pipesRequestScope ??= new FunkPipesRequest($this);
    }
    // ->pipesPostResponse()
    // and can jump back to ->config(), or jump to ->routes()
    public function pipesPostResponse(): FunkPipesPostResponse
    {
        return $this->pipesPostResponseScope ??= new FunkPipesPostResponse($this);
    }
    // ->routes() | gives access to:->GET(),->POST(),->PATCH(),->PUT(),->DELETE()
    // and can jump back to ->config()
    public function routes(): FunkRoutes
    {
        return $this->routesScope ??= new FunkRoutes($this);
    }
    // batchFunctions that attempt batching something in $batches that would be validated later unless
    // placed in $invalidBatches based upon initial valid string value like empty string or invalid
    // formatting for a regex or route, and so on! It is structured on "batch<New|Set><LEVEL><WHAT>"
    // Where "New" means you can add several as long as they are not duplicates OR conflict in certain
    // order like "pipeResponse" means you have completed the request cycle for that route and now
    // any piped ->requestPostResponse() should run as a result!
    private function batchSetMethodRateLimiting(string $method, array $rateLimitingOptions) {}

    // Set & New Batches for routes! (so ->route()->route()->set|pipe<What>)
    private function batchSetRouteAlias(string $method, string $route, string $alias) {}
    private function batchSetRouteRateLimiting(string $method, string $route, array $rateLimitingOptions) {}
    private function batchSetRouteCache(string $method, string $route, array $cacheOptions) {}

    private function batchNewRoute(string $method, string $route) {}
    private function batchNewRouteParamRule(string $method, string $route, string $paramRule) {}
    private function batchNewRoutePipeFunction(string $method, string $route, string $fileFunctionName) {}
    private function batchNewRoutePipeResponse(string $method, string $route, string $typeOfResponse) {}
    private function batchNewRoutePipeSQL(string $method, string $route, string $sqlFileFunction) {}
    private function batchNewRoutePipeQuery(string $method, string $route, string $queryFileFunction) {}
    private function batchNewRoutePipeValidation(string $method, string $route, string $validationFileFunction) {}
    private function batchNewRouteMiddleware(string $method, string $route, string $middleware) {}
    private function batchNewRouteExcludeMiddleware(string $method, string $route, string $middlewareToExclude) {}
    private function batchNewRouteCSP(string $method, string $route, $srcType, string $CSP) {}
    private function batchNewRouteHeader(string $method, string $route, string $header, string $addOrRemove = "add") {}



    // Two private functions that are ONLY used via Reflection classes so you do not see
    // them while configuring `/src/funkphp/FunkPHP.php` and runs it unless `FunkPHPDeployment.php`
    // is set in `/src/public_html/index.php` to run instead!
    private function compile()
    {
        // Attempt compiling FunkPHP and create the code
    }
    private function run()
    {
        // Run the valid compiled FunkPHP
    }
}

/*
 * Class FunkPHP is the top level navigation in the IDE that "jumps" via method-chaining
 * between `->config()`,`->pipesRequest()`,`->pipesPostResponse()`,`->routes()`
*/
class FunkPHP
{
    public function __construct(private C $c) {}
    // TOP LEVEL METHOD-CHAINED-BASED NAVIGATION
    public function config(): FunkConfig
    {
        return $this->c->config();
    }
    public function pipesRequest(): FunkPipesRequest
    {
        return $this->c->pipesRequest();
    }
    public function pipesPostResponse(): FunkPipesPostResponse
    {
        return $this->c->pipesPostResponse();
    }
    public function routes(): FunkRoutes
    {
        return $this->c->routes();
    }
}
class FunkConfig
{
    public function __construct(private C $c) {}

    public function setDefaultRouteHandler(string $handlerFunctionName): self
    {
        //$this->c->run();
        return $this;
    }

    public function setMiddlewaresCascade(bool $cascadeOrNot): self
    {
        return $this;
    }

    // Allow jumping directly to another scope without breaking the chain!
    public function pipesRequest(): FunkPipesRequest
    {
        return $this->c->pipesRequest();
    }
    public function routes(): FunkRoutes
    {
        return $this->c->routes();
    }
}

/*
 * Class FunkRoutes() - accessed via ->routes() - contains references to all
 * typical method-based routes such as GET,POST,PUT,DELETE, and PATCH+HEAD!
 * Can also jump back to ->config()
*/
class FunkRoutes
{
    private array $methodInstances = [];
    public function __construct(private C $c) {}
    public function HEAD(): FunkMethod
    {
        return $this->methodInstances['HEAD'] ??= new FunkMethod($this->c, $this, 'HEAD');
    }
    public function GET(): FunkMethod
    {
        return $this->methodInstances['GET'] ??= new FunkMethod($this->c, $this, 'GET');
    }
    public function POST(): FunkMethod
    {
        return $this->methodInstances['POST'] ??= new FunkMethod($this->c, $this, 'POST');
    }
    public function PUT(): FunkMethod
    {
        return $this->methodInstances['PUT'] ??= new FunkMethod($this->c, $this, 'PUT');
    }
    public function PATCH(): FunkMethod
    {
        return $this->methodInstances['PATCH'] ??= new FunkMethod($this->c, $this, 'PATCH');
    }
    public function DELETE(): FunkMethod
    {
        return $this->methodInstances['DELETE'] ??= new FunkMethod($this->c, $this, 'DELETE');
    }
    public function config(): FunkConfig
    {
        return $this->c->config();
    }
}

class FunkMethod
{
    public function __construct(
        private C $c,
        private FunkRoutes $parent,
        private string $method
    ) {}

    public function setParamRuleMethod(string $param, string $regex, string $defaultOnRegexMismatch = null): self
    {

        return $this;
    }

    //
    public function route(string $path, array $inlineRules = []): FunkRoute
    {
        // Validate route syntax format immediately


        return new FunkRoute($this->c, $this, $this->method, $path);
    }

    // Jump back/initialize to HEAD,GET,POST,PUT,PATCH,DELETE
    // that is under ->routes() | This allows for group and such!
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





/* Global entry point for initializing FunkPHP in `/src/funkphp/config/app.php` */
function FunkPHP()
{
    return new FunkPHP(new C);
}
