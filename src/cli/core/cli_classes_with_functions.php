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
function cli_compile_validation_schema($validation_schema, &$currentTables, &$currentConnections): string
{
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
    $compiledValidationSchema = '';
    return $compiledValidationSchema;
}

class RuleSetAll
{
    // Valid "Data Types" (set in $dataType)
    // TODO: test all cuz these might actually be wrong sometimes when evaluated with "if(!{$guardExpression})"???
    public array $typeGuardMap = [
        // File Types
        'files'      => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'file'      => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'image'     => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'video'     => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'audio'     => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'string'    => 'is_string({{##INPUT##}})',
        'date'    => 'is_string({{##INPUT##}})',
        'checkbox' => '(is_scalar({{##INPUT##}}) && {{##INPUT##}} !== null)',
        'integer'   => 'is_int({{##INPUT##}})',
        'float'     => 'is_float({{##INPUT##}})',
        'boolean'   => 'is_bool({{##INPUT##}})',
        'number'    => 'is_numeric({{##INPUT##}})',
        'array'     => 'is_array({{##INPUT##}})',
        'arr'       => 'is_array({{##INPUT##}})',
        'object'    => 'is_object({{##INPUT##}})',
        'json'      => 'is_string({{##INPUT##}})',
        'password'      => 'is_string({{##INPUT##}})',
        'password_match'      => 'is_string({{##INPUT##}})',
        'email'     => 'is_string({{##INPUT##}})',
        'url'       => 'is_string({{##INPUT##}})',
        'ip'        => 'is_string({{##INPUT##}})',
    ];
    public array $setDataTypeCategory = [
        'string' => 'string',
        'email' => 'string',
        'date' => 'string',
        'password' => 'string',
        'password_match' => 'string',
        'json' => 'string',
        'url' => 'string',
        'ip' => 'string',
        'integer' => 'numeric',
        'number' => 'numeric',
        'float' => 'numeric',
        'boolean' => 'boolean',
        'object' => 'object',
        'array' => 'array',
        'arr' => 'array',
        'checkbox' => 'checkbox',
        'file' => 'file',
        'files' => 'file',
        'image' => 'file',
        'video' => 'file',
        'audio' => 'file',
        'null' => 'null'
    ];
    public ?string $dataType = null;
    public ?string $dataTypeCategory = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useChainAll = false;
    public ?string $arrayType = null;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
    public function setDatatype(string $dataType, string $customErrorMsg = '', string $setArrayTypeToListOrAssociative = ''): self
    {
        if (isset($this->dataType)) {
            $this->configErrors[] = 'A Data Type is already set: `' . $this->dataType . '`!';
            return $this;
        }
        if (!isset($this->typeGuardMap[$dataType])) {
            $this->configErrors[] = 'Invalid Data Type chosen: `' . $dataType . '`.';
            return $this;
        }

        $this->dataType = $dataType;
        $this->dataTypeCategory = $this->setDataTypeCategory[$dataType];
        $guardExpression = $this->typeGuardMap[$dataType];
        $this->rules[$dataType] = [
            'error'    => ((!empty($customErrorMsg)) ? $customErrorMsg : "Must be of data type '{$dataType}'."),
        ];
        $this->rules[$dataType]['compiled'] =  "if(!{$guardExpression}) {\n" .
            "    {{##ERRORS##}}['{$dataType}'] = \"{$this->rules[$dataType]['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        if (!empty($setArrayTypeToListOrAssociative)) {
            if ($dataType !== 'array') {
                $this->configErrors[] = 'Invalid Data Type chosen: `' . $dataType . '` to set Array Type! Set `array` as Data Type to set Array Type at the same time!';
                return $this;
            }
            if (strtolower($setArrayTypeToListOrAssociative) === 'list') {
                return $this->keys_in_array_list();
            } elseif (strtolower($setArrayTypeToListOrAssociative) === 'associative') {
                return $this->keys_in_array_associative();
            } else {
                $this->configErrors[] = 'Invalid Array Type chosen: `' . $setArrayTypeToListOrAssociative . '`. Choose between `list` (a Numbered Array) OR `associative` (an Associative Array)!';
                return $this;
            }
        }

        return $this;
    }

    /* ALL DATA TYPES RULES */
    public function chain_rules_experimental(string $customErrorMsg): self // MIGHT NOT BE USED AT ALL!
    {
        if (!$this->validateRuleUsage('chain_rules_experimental')) {
            return $this;
        }
        if (empty(trim($customErrorMsg))) {
            $this->configErrors[] = 'The Compiler-applied Global Rule `chain_rules_experimental` cannot have an empty Custom Error Message but must contain the error message that includes all other rules errors as this Rule chains all rules into a single long `if(condition1 && condition2 ... && ...)` code statement!';
        }
        $this->rules['chain_rules_experimental'] = ['error' => $customErrorMsg, 'compiled' => null];
        $this->useChainAll = true;
        return $this;
    }
    public function bail(): self
    {
        if (!$this->validateRuleUsage('bail')) {
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }
    public function nullable(): self
    {
        if (!$this->validateRuleUsage('nullable')) {
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }
    public function required(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('required')) {
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
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
        $compiledCode = "if(\\{$func}(\$c,{{##INPUT##}}) === false) {\n" .
            "    {{##ERRORS##}}['callback'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['callback'] = [
            'functionName' => "\\$func",
            'error'        => $error,
            'compiled'     => $compiledCode,
        ];
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
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_null_allowed'] = [
            'required_keys' => $keys,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['keys_in_array_null_allowed'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_null_allowed'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_null_allowed_exact_count'] = [
            'required_keys'  => $keys,
            'expected_count' => $expectedCount,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['keys_in_array_null_allowed_exact_count'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_null_allowed_exact_count'] = \"" . $error . "\";";
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

        // 6. Store Compiled Rule
        $this->rules['keys_in_array_not_null'] = [
            'required_keys' => $keys,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['keys_in_array_not_null'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_not_null'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['keys_in_array_not_null_exact_count'] = [
            'required_keys'  => $keys,
            'expected_count' => $expectedCount,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['keys_in_array_not_null_exact_count'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_not_null_exact_count'] = \"" . $error . "\";";
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
        // 3. Store compiled rule
        $this->rules['keys_in_array_depths'] = [
            'paths'    => $pathsWhereEachDotIsNextDepthLevel,
            'error'    => $error,
            'compiled' => "if(!({$fullCondition})) {\n" .
                "    {{##ERRORS##}}['keys_in_array_depths'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['keys_in_array_depths'] = \"" . $error . "\";";
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
            $compiledCode = "foreach({{##INPUT##}} as \$elem) {\n" .
                "    if({$condition}) {\n" .
                "        {{##ERRORS##}}['elements_in_array_are_all'] = \"{$error}\";\n" .
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
            if ($targetType === 'distinct_ignore_case') {
                $compiledCode = "\$seen = [];\n" .
                    "foreach({{##INPUT##}} as \$elem) {\n" .
                    "    \$checkVal = is_string(\$elem) ? strtolower(\$elem) : \$elem;\n" .
                    "    if(in_array(\$checkVal, \$seen, false)) {\n" .
                    "        {{##ERRORS##}}['elements_in_array_are_all'] = \"{$error}\";\n" .
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
                    "        {{##ERRORS##}}['elements_in_array_are_all'] = \"{$error}\";\n" .
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
        return $this;
    }

    /* MIXED DATA TYPES RULES when using RuleSetAll! */
    public function min(int|float $minValue, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('min', ['between', 'between_mb', 'size', 'min_mb'])) {
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
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['min'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['min'] = \"{$error}\";";
        return $this;
    }
    public function max(int|float $maxValue, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('max', ['between', 'between_mb', 'size', 'max_mb'])) {
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
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['max'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['max'] = \"{$error}\";";
        return $this;
    }
    public function between(int|float $minVal, int|float $maxVal, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('between', ['size', 'between_mb', 'min', 'max', 'min_mb', 'max_mb'])) {
            return $this;
        }
        // 3. Range Sanity Guard ($minVal must be strictly less than $maxVal)
        if ($minVal >= $maxVal) {
            $this->configErrors[] = 'Rule `between` has invalid range `[' . $minVal . ', ' . $maxVal . ']` for Input Key `{{##INPUT_KEY##}}`. Min value must be strictly smaller than Max value!';
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
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['between'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['between'] = \"" . $error . "\";";
        return $this;
    }
    public function size(int|float $size, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('size', ['min', 'max', 'between', 'between_mb', 'size_mb', 'max_mb', 'min_mb'])) {
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
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['size'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['size'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addslashes($prefix);
            $conditions[] = "str_starts_with({$inputStr}, '{$escapedPrefix}')";
        }
        // If multiple prefixes, ANY match is valid: !(cond1 || cond2)
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must start with: " . implode(', ', $prefixes);
        $this->rules['starts_with'] = [
            'prefixes' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['starts_with'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['starts_with'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addslashes($suffix);
            $conditions[] = "str_ends_with({$inputStr}, '{$escapedSuffix}')";
        }
        // If multiple values given, ANY match passes validation
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must end with: " . implode(', ', $suffixes);
        $this->rules['ends_with'] = [
            'suffixes' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['ends_with'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ends_with'] = \"" . $error . "\";";
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
            $inputArr = '(array)({{##INPUT##}} ?? [])';
            $conditions = [];
            foreach ($needles as $needle) {
                $exportedNeedle = var_export($needle, true);
                // Fails if ANY needle is NOT in the array
                $conditions[] = "!in_array({$exportedNeedle}, {$inputArr}, true)";
            }
            $condition = implode(' || ', $conditions);
        } else {
            // STRING/NUMERIC CATEGORY: Input string must contain AT LEAST ONE of the specified values
            $inputStr = '(string)({{##INPUT##}} ?? \'\')';
            $conditions = [];
            foreach ($needles as $needle) {
                $escapedNeedle = addslashes((string)$needle);
                $conditions[] = "str_contains({$inputStr}, '{$escapedNeedle}')";
            }
            $condition = '!(' . implode(' || ', $conditions) . ')';
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain: " . implode(', ', $needles);
        $this->rules['contains'] = [
            'needles'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['contains'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['contains'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addslashes($prefix);
            $conditions[] = "str_starts_with({$inputStr}, '{$escapedPrefix}')";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not start with: " . implode(', ', $prefixes);
        $this->rules['doesnt_start_with'] = [
            'prefixes' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['doesnt_start_with'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_start_with'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addslashes($suffix);
            $conditions[] = "str_ends_with({$inputStr}, '{$escapedSuffix}')";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not end with: " . implode(', ', $suffixes);
        $this->rules['doesnt_end_with'] = [
            'suffixes' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['doesnt_end_with'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_end_with'] = \"" . $error . "\";";
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
            $inputArr = '(array)({{##INPUT##}} ?? [])';
            $conditions = [];
            foreach ($needles as $needle) {
                $exportedNeedle = var_export($needle, true);
                // Fails if ANY needle IS found in the array
                $conditions[] = "in_array({$exportedNeedle}, {$inputArr}, true)";
            }
            $condition = implode(' || ', $conditions);
        } else {
            // STRING/NUMERIC CATEGORY: Input string must NOT contain ANY of the specified values
            $inputStr = '(string)({{##INPUT##}} ?? \'\')';
            $conditions = [];
            foreach ($needles as $needle) {
                $escapedNeedle = addslashes((string)$needle);
                $conditions[] = "str_contains({$inputStr}, '{$escapedNeedle}')";
            }
            $condition = '(' . implode(' || ', $conditions) . ')';
        }
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not contain: " . implode(', ', $needles);
        $this->rules['doesnt_contain'] = [
            'needles'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['doesnt_contain'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_contain'] = \"" . $error . "\";";
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
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Selected value for `{{##INPUT_KEY##}}` is invalid.";
        // 7. Store Compiled Rule
        $this->rules['in'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['in'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['in'] = \"" . $error . "\";";
        return $this;
    }
    public function not_in(array|string $inValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('not_in', ['in'], [], ['string', 'numeric', 'boolean'])) {
            return $this;
        }
        $values = $this->validateRuleMultipleValues('not_in', $inValues, ['string', 'integer', 'boolean', 'float', null]);
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
        $error = (!empty($customErrorMsg)) ? $customErrorMsg : "Selected value for `{{##INPUT_KEY##}}` is forbidden.";
        // 7. Store Compiled Rule
        $this->rules['not_in'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['not_in'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['not_in'] = \"" . $error . "\";";
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
        $this->rules['min_mb'] = [
            'error'    => $error,
            'compiled' => "if(mb_strlen({{##INPUT##}}) < {$minChars}) {\n" .
                "    {{##ERRORS##}}['min_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['min_mb'] = \"" . $error . "\";";
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
        $this->rules['max_mb'] = [
            'error'    => $error,
            'compiled' => "if(mb_strlen({{##INPUT##}}) > {$maxChars}) {\n" .
                "    {{##ERRORS##}}['max_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['max_mb'] = \"" . $error . "\";";
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
        $this->rules['between_mb'] = [
            'error'    => $error,
            'compiled' => "if((mb_strlen({{##INPUT##}}) < {$minChars} || mb_strlen({{##INPUT##}}) > {$maxChars})) {\n" .
                "    {{##ERRORS##}}['between_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['between_mb'] = \"" . $error . "\";";
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
        $this->rules['size_mb'] = [
            'error'    => $error,
            'compiled' => "if(mb_strlen({{##INPUT##}}) !== {$size}) {\n" .
                "    {{##ERRORS##}}['size_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['size_mb'] = \"" . $error . "\";";
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
        // 5. Store compiled rule
        $this->rules['regex'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['regex'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['regex'] = \"" . $error . "\";";
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
        // 5. Store compiled rule
        $this->rules['not_regex'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['not_regex'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['not_regex'] = \"" . $error . "\";";
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

        // 5. Store Compiled Rule
        $this->rules['mac_address'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['mac_address'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['mac_address'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['lowercase'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['lowercase'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lowercase'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['uppercase'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['uppercase'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];

        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['uppercase'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['lowercase_mb'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['lowercase_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lowercase_mb'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['uppercase_mb'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['uppercase_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];

        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['uppercase_mb'] = \"" . $error . "\";";
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
        // 4. Store Compiled Rule
        $this->rules['uid'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['uid'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['uid'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['slug'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['slug'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['slug'] = \"" . $error . "\";";
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
        // 7. Store Compiled Rule
        $this->rules['base64'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['base64'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['base64'] = \"" . $error . "\";";
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
        // 7. Store Compiled Rule
        $this->rules['not_base64'] = [
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['not_base64'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['not_base64'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['color'] = [
            'allowed_formats' => array_keys($selectedPatterns),
            'error'           => $error,
            'compiled'        => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['color'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['color'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['single_char'] = [
            'allowed_chars' => $chars,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['single_char'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['single_char'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['single_char_mb'] = [
            'allowed_chars' => $chars,
            'error'         => $error,
            'compiled'      => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['single_char_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['single_char_mb'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addslashes($prefix);
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedPrefix}') === 0";
        }
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must start with: " . implode(', ', $prefixes);
        $this->rules['starts_with_mb'] = [
            'prefixes' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['starts_with_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['starts_with_mb'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addslashes($suffix);
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
        $this->rules['ends_with_mb'] = [
            'suffixes' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['ends_with_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['ends_with_mb'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($needles as $needle) {
            $escapedNeedle = addslashes($needle);
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedNeedle}') !== false";
        }
        $condition = '!(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must contain: " . implode(', ', $needles);
        $this->rules['contains_mb'] = [
            'needles'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['contains_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['contains_mb'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($prefixes as $prefix) {
            $escapedPrefix = addslashes($prefix);
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedPrefix}') === 0";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not start with: " . implode(', ', $prefixes);
        $this->rules['doesnt_start_with_mb'] = [
            'prefixes' => $prefixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['doesnt_start_with_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_start_with_mb'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($suffixes as $suffix) {
            $escapedSuffix = addslashes($suffix);
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
        $this->rules['doesnt_end_with_mb'] = [
            'suffixes' => $suffixes,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['doesnt_end_with_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_end_with_mb'] = \"" . $error . "\";";
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
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($needles as $needle) {
            $escapedNeedle = addslashes($needle);
            $conditions[] = "mb_strpos({$inputStr}, '{$escapedNeedle}') !== false";
        }
        // Fails if ANY condition matches
        $condition = '(' . implode(' || ', $conditions) . ')';
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must not contain: " . implode(', ', $needles);
        $this->rules['doesnt_contain_mb'] = [
            'needles'  => $needles,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['doesnt_contain_mb'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['doesnt_contain_mb'] = \"" . $error . "\";";
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
        $compiledCode = "if(strtotime({{##INPUT##}}) === false) {\n" .
            "    {{##ERRORS##}}['date'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date'] = [
            'error'    => $error,
            'compiled' => $compiledCode,
        ];
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
        // Fails if input is not a string, not parseable, or falls on/before the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) <= strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_after'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_after'] = [
            'targetDate' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
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
        // Fails if input is unparseable or falls strictly before the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) < strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_after_or_equal'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_after_or_equal'] = [
            'targetDate' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
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
        // Fails if input is not a string, not parseable, or falls on/after the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) >= strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_before'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_before'] = [
            'targetDate' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
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
        // Fails if input is unparseable or falls strictly after the target date
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) > strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_before_or_equal'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_before_or_equal'] = [
            'targetDate' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
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
        // Fails if input is not parseable or timestamps do not match
        $condition = "strtotime({{##INPUT##}}) === false || " .
            "strtotime({{##INPUT##}}) !== strtotime('{$targetDate}')";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_equals'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_equals'] = [
            'targetDate' => $targetDate,
            'error'      => $error,
            'compiled'   => $compiledCode,
        ];
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
            $escapedFmt = addslashes($fmt);
            $matchingConditions[] = "(\\DateTimeImmutable::createFromFormat('{$escapedFmt}', {{##INPUT##}}) !== false && " .
                "\\DateTimeImmutable::createFromFormat('{$escapedFmt}', {{##INPUT##}})->format('{$escapedFmt}') === {{##INPUT##}})";
        }
        $joinedFormats = implode('`, `', $validFormats);
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must match one of the following date formats: `{$joinedFormats}`!";
        // Fails if NONE of the format conditions evaluate to true
        $condition = "!(" . implode(" || ", $matchingConditions) . ")";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_format'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_format'] = [
            'dummies'  => $dummies,
            'formats'  => $validFormats,
            'error'    => $error,
            'compiled' => $compiledCode,
        ];

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
            $escapedTarget = addslashes($target);
            $equalityChecks[] = "strtotime({{##INPUT##}}) === strtotime('{$escapedTarget}')";
        }
        $joinedTargets = implode('`, `', $validatedDates);
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be a valid date matching one of the following: `{$joinedTargets}`!";
        // Fails if input is unparseable OR if none of the target checks match
        $condition = "strtotime({{##INPUT##}}) === false || !(" . implode(' || ', $equalityChecks) . ")";
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['date_in'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_in'] = [
            'targetDates' => $validatedDates,
            'error'       => $error,
            'compiled'    => $compiledCode,
        ];
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
            "    {{##ERRORS##}}['date_timezone'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['date_timezone'] = [
            'region'      => $normalizedRegion,
            'countryCode' => $countryCode,
            'error'       => $error,
            'compiled'    => $compiledCode,
        ];
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
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##ERRORS##}}['gte'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['gte'] = \"" . $error . "\";";
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
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##ERRORS##}}['gt'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['gt'] = \"" . $error . "\";";
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
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##ERRORS##}}['lte'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lte'] = \"" . $error . "\";";
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
            'target' => $targetFieldinValidation,
            'compiled' => "if(!isset({{##TARGET_INPUT:{$targetFieldinValidation}##}}) || {$condition}) {\n" .
                "    {{##ERRORS##}}['lt'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['lt'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['same'] = [
            'error'    => $error,
            'target' => $targetField,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['same'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['same'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['different'] = [
            'error'  => $error,
            'target' => $targetField,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['different'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['different'] = \"" . $error . "\";";
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
        // 4. Store compiled rule
        $this->rules['multiple_of'] = [
            'value'    => $target,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['multiple_of'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['multiple_of'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['single_digit'] = [
            'allowed_digits' => $digits,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['single_digit'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['single_digit'] = \"" . $error . "\";";
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
        // 4. Store compiled rule
        $this->rules['digits'] = [
            'digits'   => $count,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['digits'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['digits'] = \"" . $error . "\";";
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
        // 4. Store compiled rule
        $this->rules['min_digits'] = [
            'min'      => $min,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['min_digits'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['min_digits'] = \"" . $error . "\";";
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

        // 4. Store compiled rule
        $this->rules['max_digits'] = [
            'max'      => $max,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['max_digits'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['max_digits'] = \"" . $error . "\";";
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
        // 4. Store compiled rule
        $this->rules['digits_between'] = [
            'min'      => $minVal,
            'max'      => $maxVal,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['digits_between'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['digits_between'] = \"" . $error . "\";";
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
        // 4. Store compiled rule
        $this->rules['decimal'] = [
            'min'      => $min,
            'max'      => $max,
            'error'    => $error,
            'compiled' => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['decimal'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['decimal'] = \"" . $error . "\";";
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

        // 6. Store Compiled Rule
        $this->rules['checked'] = [
            'allowed_values' => $values,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['checked'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['checked'] = \"" . $error . "\";";
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
        // 6. Store Compiled Rule
        $this->rules['unchecked'] = [
            'allowed_values' => $values,
            'error'          => $error,
            'compiled'       => "if({$condition}) {\n" .
                "    {{##ERRORS##}}['unchecked'] = \"{$error}\";\n" .
                "    {{##GOTO_STOP_ALL##}}\n" .
                "    {{##GOTO_BAIL##}}\n" .
                "    {{##GOTO_NEXT_RULE##}}\n" .
                "    {{##GOTO_END_FIELD##}}\n" .
                "}"
        ];
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['unchecked'] = \"" . $error . "\";";
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
        $compiledCode = "try {\n" .
            "    {{##DB_EXISTS_NOT_FOUND_BLOCK_END_WITH_FINAL_IF_NEGATED_STATEMENT##}} {\n" .
            "        {{##ERRORS##}}['exists'] = \"{$error}\";\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "} catch (\\Throwable \$e) {\n" .
            "    {{##ERRORS##}}['exists'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['exists'] = [
            'databaseConnection' => $dbConn,
            'table'              => $dbTbl,
            'column'             => $dbCol,
            'error'              => $error,
            'compiled'           => $compiledCode,
        ];
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
        $compiledCode = "try {\n" .
            "    {{##DB_UNIQUE_ALREADY_EXISTS_BLOCK_END_WITH_FINAL_IF_NEGATED_STATEMENT##}} {\n" .
            "        {{##ERRORS##}}['unique'] = \"{$error}\";\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "} catch (\\Throwable \$e) {\n" .
            "    {{##ERRORS##}}['unique'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['unique'] = [
            'databaseConnection' => $dbConn,
            'table'              => $dbTbl,
            'column'             => $dbCol,
            'error'              => $error,
            'compiled'           => $compiledCode,
        ];
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
     * @param string $ignoreValueFromCPath Dot-notation Path to Fetch Value from `Global Configuration Array Variable $c` (e.g., 'req.params.id' or 'shared.user.id' becomes `$c['req']['params']['id']` and `$c['shared']['user']['id]`). This allows for fetching dynamic value from the same place without knowing the value beforehand!
     * @param string $customErrorMsg Custom error message on validation failure.
     * @return self
     */
    public function unique_except(string $databaseConnection, string $table, string $column, string $ignoreColumn, string $ignoreValueFromCPath, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('unique_except', [], [], [])) {
            return $this;
        }
        $ruleVals = $this->validateRuleMultipleValues('unique_except', [$databaseConnection, $table, $column, $ignoreColumn, $ignoreValueFromCPath], ['string'], false, true);
        if ($ruleVals === false) {
            return $this;
        }
        [$dbConn, $dbTbl, $dbCol, $ignoreCol, $ignoreValueCPath] = $ruleVals;
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbTbl) || !preg_match('/^[a-zA-Z0-9_]+$/', $dbCol) || !preg_match('/^[a-zA-Z0-9_]+$/', $ignoreCol)) {
            $this->configErrors[] = "Rule `unique_except` contains invalid identifier characters for Input Key `{{##INPUT_KEY##}}`!";
            return $this;
        }
        if (empty($ignoreValueCPath) || !preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/', $ignoreValueCPath)) {
            $this->configErrors[] = "Rule `unique_except` parameter `$ignoreValueFromCPath` must be a valid dot-notation path (e.g. `req.params.id` or `shared.user_id`) for Input Key `{{##INPUT_KEY##}}`! It ALWAYS uses the Globally Available Configuration Array Variable `\$c` as root starting point!";
            return $this;
        }
        // Convert dot notation "req.matched_params.id" into "$c['req']['matched_params']['id'] ?? null"
        $pathParts = explode('.', $ignoreValueCPath);
        $compiledCPath = "\$c['" . implode("']['", $pathParts) . "'] ?? null";
        $error = !empty($customErrorMsg)
            ? $customErrorMsg
            : "Field `{{##INPUT_KEY##}}` must be unique and cannot be the current one.";
        $compiledCode = "try {\n" .
            "        \$ignoreValue = {$compiledCPath};\n" .
            "    {{##DB_UNIQUE_EXCEPT_ALREADY_EXISTS_BLOCK_END_WITH_FINAL_IF_NEGATED_STATEMENT##}} {\n" .
            "        {{##ERRORS##}}['unique_except'] = \"{$error}\";\n" .
            "        {{##GOTO_STOP_ALL##}}\n" .
            "        {{##GOTO_BAIL##}}\n" .
            "        {{##GOTO_NEXT_RULE##}}\n" .
            "        {{##GOTO_END_FIELD##}}\n" .
            "    }\n" .
            "} catch (\\Throwable \$e) {\n" .
            "    {{##ERRORS##}}['unique_except'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['unique_except'] = [
            'databaseConnection' => $dbConn,
            'table'              => $dbTbl,
            'column'             => $dbCol,
            'ignoreColumn'       => $ignoreCol,
            'ignoreValueFromCPath' => $compiledCPath,
            'error'              => $error,
            'compiled'           => $compiledCode,
        ];
        return $this;
    }
    /* FILES-ONLY RULES (accesses $_FILES array to validate things like filetype, filesize, dimensions if applicable, and so on) */
}

class RuleSetString
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'string';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function string(string $customErrorMsg = ''): self
    {
        if (isset($this->dataType)) {
            $this->configErrors[] = 'The `string` Data Type is already set!';
            return $this;
        }
        $this->dataType = 'string';
        $this->rules['string'] = [
            'error'    => ((!empty($customErrorMsg)) ? $customErrorMsg : "Must be of data type '{'string'}'."),
        ];
        $this->rules['string']['compiled'] =  "if(!is_string({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['{'string'}'] = \"{$this->rules['string']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetPassword
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'string';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function password(string $customErrorMsg = ''): self
    {
        $this->dataType = "password";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetEmail
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'string';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function email(string $customErrorMsg = ''): self
    {
        $this->dataType = "email";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetBoolean
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'boolean';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function boolean(string $customErrorMsg = ''): self
    {
        $this->dataType = "boolean";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetCheckbox
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'checkbox';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function checkbox(string $customErrorMsg = ''): self
    {
        $this->dataType = "checkbox";
        $this->rules['checkbox'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['checkbox']['compiled'] =  "if(!is_scalar({{##INPUT##}}) || {{##INPUT##}} === null) {\n" .
            "    {{##ERRORS##}}['checkbox'] = \"{$this->rules['checkbox']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        return $this;
    }
    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }
    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }
    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetNumber
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'numeric';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function number(string $customErrorMsg = ''): self
    {
        $this->dataType = "number";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetInteger
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'numeric';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function integer(string $customErrorMsg = ''): self
    {
        $this->dataType = "integer";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetFloat
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'numeric';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function float(string $customErrorMsg = ''): self
    {
        $this->dataType = "float";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetPhone
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'string';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function phone(string $customErrorMsg = ''): self
    {
        $this->dataType = "phone";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetArray
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'array';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?string $arrayType = null;
    public bool $maxArraySizeAlreadySet = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    /**
     * Restricts validation to array structures.
     *
     * @param 'list'|'associative'|'' $listOrAssociative 'list' for indexed array, 'associative' for key-value array, or '' for any array.
     * @param int|null $maxArraySize Optional shortcut to limit max array count.
     * @param string $customErrorMsg
     * @return self
     */
    public function arr(string $listOrAssociative = '', mixed $maxArraySize = null, string $customErrorMsg = ''): self
    {
        if (isset($this->dataType)) {
            $this->configErrors[] = 'Data Type `array` has already been set, with its Array Type: ' . ($this->arrayType ? "`{$this->arrayType}`!" : '`Both Array Types`!');
            return $this;
        }
        $maxArraySize = $this->validateRuleMultipleValues('arr', $maxArraySize, ['integer', 'null']);
        if ($maxArraySize === false) {
            return $this;
        }
        $maxArraySize = $maxArraySize[0];
        $this->dataType = 'array';
        $normalizedType = strtolower(trim($listOrAssociative));
        // 1. Determine base condition and default message based on array sub-type
        if ($normalizedType === 'list') {
            $this->arrayType = 'list';
            $condition = "!is_array({{##INPUT##}}) || !array_is_list({{##INPUT##}})";
            $defaultError = "This field must be a numbered array (list)!";
        } elseif ($normalizedType === 'associative') {
            $this->arrayType = 'associative';
            $condition = "!is_array({{##INPUT##}}) || (!empty({{##INPUT##}}) && array_is_list({{##INPUT##}}))";
            $defaultError = "This field must be an associative array!";
        } elseif ($normalizedType === '') {
            $condition = "!is_array({{##INPUT##}})";
            $defaultError = "This field must be an array!";
        } else {
            $this->configErrors[] = 'Invalid Array Type chosen: `' . $listOrAssociative . '`. Choose between `list` (a Numbered Array) OR `associative` (an Associative Array)!';
            return $this;
        }
        // 2. Append optional maxArraySize check directly to the condition
        if ($maxArraySize !== null) {
            if ($maxArraySize < 0) {
                $this->configErrors[] = 'Invalid max array size provided to `arr()`: `' . $maxArraySize . '`. Maximum item count must be 0 or greater!';
                return $this;
            }
            $this->maxArraySizeAlreadySet = true;
            $condition .= " || count({{##INPUT##}}) > {$maxArraySize}";
            // Auto-build an intuitive default error if no custom message was supplied
            if (trim($customErrorMsg) === '') {
                $typeLabel = match ($normalizedType) {
                    'list'        => 'a numbered array',
                    'associative' => 'an associative array',
                    default       => 'an array',
                };
                $defaultError = "This field must be {$typeLabel} containing at most {$maxArraySize} elements!";
            }
        }
        $error = (trim($customErrorMsg) !== '') ? $customErrorMsg : $defaultError;
        // 3. Compile into a single, clean IF block without duplicate placeholders
        $compiledCode = "if({$condition}) {\n" .
            "    {{##ERRORS##}}['arr'] = \"{$error}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->rules['arr'] = [
            'arrayType' => $this->arrayType ?? 'any',
            'maxSize'   => $maxArraySize,
            'error'     => $error,
            'compiled'  => $compiledCode,
        ];
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('required')) {
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetObject
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'object';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function object(string $customErrorMsg = ''): self
    {
        $this->dataType = "object";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This field is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetFile
{
    public ?string $dataType = null;
    public ?string $dataTypeCategory = 'file';
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    // Flat associative list of configured rules
    public array $rules = [];
    // Tracks syntax/dev errors during chaining for validating the correct use of the rules
    public array $configErrors = [];
    // Collected compiled errors from the rules based on their received static scheme data
    public array $compiledErrors = [];
    // Collection of all errors besides the one for data type (this is for the else part
    // when the $dataType rule is not fulfilled in validation; e.g. it is not a string so
    // if it is not nullable then we can show all other errors immediately unless bail and/or
    // stop_all_on_first_error is set to true!)
    public array $mergedErrorsBesdiesDataType = [];
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
        bool $valuesCanBeEmpty = false
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
            if (in_array($val, $uniqueValues, true)) {
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
    public function files(string $customErrorMsg = ''): self
    {
        $this->dataType = "file";
        return $this;
    }

    public function bail(): self
    {
        if (isset($this->rules['bail'])) {
            $this->configErrors[] = 'Rule `bail` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['bail'] = ['error' => null, 'compiled' => null];
        $this->useBail = true;
        return $this;
    }

    public function nullable(): self
    {
        if (isset($this->rules['nullable'])) {
            $this->configErrors[] = 'Rule `nullable` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['nullable'] = ['error' => null, 'compiled' => null];
        $this->useNullable = true;
        return $this;
    }

    public function required(string $customErrorMsg = ''): self
    {
        if (isset($this->rules['required'])) {
            $this->configErrors[] = 'Rule `required` already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $this->rules['required'] = [
            'error' => ((!empty($customErrorMsg)) ? $customErrorMsg : 'This File is REQUIRED!')
        ];
        $this->rules['required']['compiled'] =  "if(!isset({{##INPUT##}})) {\n" .
            "    {{##ERRORS##}}['required'] = \"{$this->rules['required']['error']}\";\n" .
            "    {{##GOTO_STOP_ALL##}}\n" .
            "    {{##GOTO_BAIL##}}\n" .
            "    {{##GOTO_NEXT_RULE##}}\n" .
            "    {{##GOTO_END_FIELD##}}\n" .
            "}";
        $this->mergedErrorsBesdiesDataType[] = "    {{##ERRORS##}}['required'] = \"" . $this->rules['required']['error'] . "\";";
        $this->useRequired = true;
        return $this;
    }
}
/**
 * Global entry point for initializing a fluent validation rule set.
 *
 * @param 'files'|'file'|'image'|'video'|'audio'|'string'|'date'|'checkbox'|'integer'|'float'|'boolean'|'number'|'array'|'arr'|'object'|'json'|'password'|'password_match'|'email'|'url'|'ip' $dataType
 * @param string $customErrorMsg
 * @param 'list'|'associative'|'' $setArrayTypeToListOrAssociative
 * @return RuleSetAll
 */
function all(string $dataType, string $customErrorMsg = '', string $setArrayTypeToListOrAssociative = ''): RuleSetAll
{
    return (new RuleSetAll())->setDatatype($dataType, $customErrorMsg, $setArrayTypeToListOrAssociative);
}
function string(string $customErrorMsg = ''): RuleSetString
{
    return (new RuleSetString())->string($customErrorMsg);
}
function password(string $customErrorMsg = ''): RuleSetPassword
{
    return (new RuleSetPassword())->password($customErrorMsg);
}
function email(string $customErrorMsg = ''): RuleSetEmail
{
    return (new RuleSetEmail())->email($customErrorMsg);
}
function boolean(string $customErrorMsg = ''): RuleSetBoolean
{
    return (new RuleSetBoolean())->boolean($customErrorMsg);
}
function checkbox(string $customErrorMsg = ''): RuleSetCheckbox
{
    return (new RuleSetCheckbox())->checkbox($customErrorMsg);
}
function number(string $customErrorMsg = ''): RuleSetNumber
{
    return (new RuleSetNumber())->number($customErrorMsg);
}
function integer(string $customErrorMsg = ''): RuleSetInteger
{
    return (new RuleSetInteger())->integer($customErrorMsg);
}
function float(string $customErrorMsg = ''): RuleSetFloat
{
    return (new RuleSetFloat())->float($customErrorMsg);
}
function phone(string $customErrorMsg = ''): RuleSetPhone
{
    return (new RuleSetPhone())->phone($customErrorMsg);
}
/**
 * Restricts validation to array structures.
 *
 * @param 'list'|'associative'|'' $setArrayTypeToListOrAssociative 'list' for indexed array, 'associative' for key-value array, or '' for any array.
 * @param string $customErrorMsg
 * @return RuleSetArray
 */
function arr(string $setArrayTypeToListOrAssociative = '', mixed $maxArraySize = null, string $customErrorMsg = ''): RuleSetArray
{
    return (new RuleSetArray())->arr($setArrayTypeToListOrAssociative, $maxArraySize, $customErrorMsg);
}
function object(string $customErrorMsg = ''): RuleSetObject
{
    return (new RuleSetObject())->object($customErrorMsg);
}
function files(string $customErrorMsg = ''): RuleSetFile
{
    return (new RuleSetFile())->files($customErrorMsg);
}
