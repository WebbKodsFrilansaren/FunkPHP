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
    // ACTUAL RULES (some are data type-restricted like some for only strings, some for only arrays, etc.)
    public function setDatatype(string $dataType, string $customErrorMsg = ''): self
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
    public function keys_in_array_null_allowed(array|string $keysThatMustExistCanBeNull, string $customErrorMsg = ''): self
    {

        if (!$this->validateRuleUsage('keys_in_array_null_allowed', [], [], ['array'])) {
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
    public function regex(string|array $regexOrRegexes, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('regex', [], [], ['string'])) {
            return $this;
        }
        // 4. Normalize to array & Validate Patterns
        $patterns = is_array($regexOrRegexes) ? $regexOrRegexes : [$regexOrRegexes];
        if (empty($patterns)) {
            $this->configErrors[] = 'Rule `regex` requires at least one pattern for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $compiledConditions = [];
        $duplicatePatterns = [];
        foreach ($patterns as $pattern) {
            if (!is_string($pattern)) {
                $this->configErrors[] = 'Rule `regex` Patterns must be Strings for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            if (in_array($pattern, $duplicatePatterns, true)) {
                $this->configErrors[] = 'Rule `regex` Pattern Duplicate:`' . $pattern .  '` for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            $duplicatePatterns[] = $pattern;
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
        $patterns = is_array($regexOrRegexes) ? $regexOrRegexes : [$regexOrRegexes];
        if (empty($patterns)) {
            $this->configErrors[] = 'Rule `not_regex` requires at least one pattern for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $compiledConditions = [];
        $duplicatePatterns = [];
        foreach ($patterns as $pattern) {
            if (!is_string($pattern)) {
                $this->configErrors[] = 'Rule `not_regex` Patterns must be Strings for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            if (in_array($pattern, $duplicatePatterns, true)) {
                $this->configErrors[] = 'Rule `regex` Pattern Duplicate:`' . $pattern .  '` for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            $duplicatePatterns[] = $pattern;
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
    public function in(array|string $inValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('in', ['not_in'], [], ['string', 'numeric', 'boolean'])) {
            return $this;
        }
        if ((is_string($inValues) && (empty($inValues)))
            || (is_array($inValues) && (empty($inValues) || !array_is_list($inValues)))
        ) {
            $this->configErrors[] = 'Rule `in` requires at least one Allowed Value as a String or as a Non-Empty Numbered Array for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Normalize Input Values
        $validValues = [];
        if (is_string($inValues)) {
            $values = array_map('trim', explode(',', $inValues));
        } else {
            $values = array_values($inValues);
        }
        // Check for duplicate items in configured list
        foreach ($values as $val) {
            if (in_array($val, $validValues, true)) {
                $displayVal = is_bool($val) ? ($val ? 'true' : 'false') : (string)$val;
                $this->configErrors[] = 'Rule `in` contains duplicate (`' . $displayVal . '`) Allowed Values for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            if ((!is_scalar($val)) && ($val !== null)) {
                $this->configErrors[] = 'Rule `in` must have SCALARS or NULLS as Values for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            $validValues[] = $val;
        }
        // 6. Build Failure Condition
        $values = $validValues;
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
        if ((is_string($inValues) && (empty($inValues)))
            || (is_array($inValues) && (empty($inValues) || !array_is_list($inValues)))
        ) {
            $this->configErrors[] = 'Rule `not_in` requires at least one Forbidden Value as a String or as a Non-Empty Numbered Array for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 5. Normalize Input Values
        $validValues = [];
        if (is_string($inValues)) {
            $values = array_map('trim', explode(',', $inValues));
        } else {
            $values = array_values($inValues);
        }
        // Check for duplicate items in configured list
        foreach ($values as $val) {
            if (in_array($val, $validValues, true)) {
                $displayVal = is_bool($val) ? ($val ? 'true' : 'false') : (string)$val;
                $this->configErrors[] = 'Rule `not_in` contains duplicate (`' . $displayVal . '`) Forbidden Values for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            if ((!is_scalar($val)) && ($val !== null)) {
                $this->configErrors[] = 'Rule `not_in` must have SCALARS or NULLS as Values for Input Key `{{##INPUT_KEY##}}`!';
                return $this;
            }
            $validValues[] = $val;
        }
        // 6. Build Failure Condition
        $values = $validValues;
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
        // 4. Normalize & Validate Input Parameters
        if (is_string($allowedChars)) {
            $chars = array_filter(array_map('trim', explode(',', $allowedChars)), fn($c) => $c !== '');
        } else {
            $chars = array_values(array_map('strval', $allowedChars));
        }
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
        // 4. Normalize & Validate Input Parameters
        if (is_string($allowedChars)) {
            $chars = array_filter(array_map('trim', explode(',', $allowedChars)), fn($c) => $c !== '');
        } else {
            $chars = array_values(array_map('strval', $allowedChars));
        }
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
    public function single_digit(array|string $allowedDigits = [], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('single_digit', ['min_digits', 'max_digits', 'digits', 'decimal', 'min_decimal', 'max_decimal', 'between_decimal'], [], ['string', 'integer'])) {
            return $this;
        }
        // 4. Normalize & Validate Input Parameters
        if (is_string($allowedDigits)) {
            $digits = array_filter(array_map('trim', explode(',', $allowedDigits)), fn($d) => $d !== '');
        } else {
            $digits = array_values(array_map('strval', $allowedDigits));
        }
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
    public function checked(array|string $allowedValues = [true, 1, '1', 'on', 'yes', 'ja', 'true', 'checked', 'enabled', 'selected'], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('checked', ['unchecked'], ['checkbox'], ['checkbox'])) {
            return $this;
        }
        // 4. Defaults & Parameter Normalization
        $defaults = [true, 1, '1', 'on', 'yes', 'ja', 'true', 'checked', 'enabled', 'selected'];
        if (empty($allowedValues)) {
            $values = $defaults;
        } elseif (is_string($allowedValues)) {
            $values = array_filter(array_map('trim', explode(',', $allowedValues)), fn($v) => $v !== '');
        } else {
            $values = array_values($allowedValues);
        }
        if (empty($values)) {
            $this->configErrors[] = 'Rule `checked` requires at least one allowed value for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
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
    public function unchecked(array|string $allowedValues = [false, 0, '0', 'off', 'no', 'nej', 'false', 'unchecked', 'disabled', 'unselected'], string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('unchecked', ['checked'], ['checkbox'], ['checkbox'])) {
            return $this;
        }
        // 4. Defaults & Parameter Normalization
        $defaults = [false, 0, '0', 'off', 'no', 'nej', 'false', 'unchecked', 'disabled', 'unselected'];
        if (empty($allowedValues)) {
            $values = $defaults;
        } elseif (is_string($allowedValues)) {
            $values = array_filter(array_map('trim', explode(',', $allowedValues)), fn($v) => $v !== '');
        } else {
            $values = array_values($allowedValues);
        }
        if (empty($values)) {
            $this->configErrors[] = 'Rule `unchecked` requires at least one allowed value for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
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
    public function starts_with(array|string|int|float $startsWithValues, string $customErrorMsg = ''): self
    {
        if (!$this->validateRuleUsage('starts_with', ['starts_with_mb'], [], ['string', 'numeric'])) {
            return $this;
        }
        // 2. Normalize inputs into array of strings
        if (is_array($startsWithValues)) {
            $prefixes = array_map('strval', $startsWithValues);
        } else {
            $prefixes = array_map('trim', explode(',', (string)$startsWithValues));
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
        // Normalize inputs into array of strings
        if (is_array($endsWithValues)) {
            $suffixes = array_map('strval', $endsWithValues);
        } else {
            $suffixes = array_map('trim', explode(',', (string)$endsWithValues));
        }
        if (empty($suffixes)) {
            $this->configErrors[] = 'Rule `ends_with` requires at least one suffix value for Input Key `{{##INPUT_KEY##}}`!';
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
        if (!$this->validateRuleUsage('contains', ['contains_mb'], [], ['string', 'numeric'])) {
            return $this;
        }
        // Normalize inputs into array of strings
        if (is_array($containsValues)) {
            $needles = array_map('strval', $containsValues);
        } else {
            $needles = array_map('trim', explode(',', (string)$containsValues));
        }
        if (empty($needles)) {
            $this->configErrors[] = 'Rule `contains` requires at least one search value for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        $inputStr = '(string)({{##INPUT##}} ?? \'\')';
        $conditions = [];
        foreach ($needles as $needle) {
            $escapedNeedle = addslashes($needle);
            $conditions[] = "str_contains({$inputStr}, '{$escapedNeedle}')";
        }
        // If multiple values given, ANY match passes validation
        $condition = '!(' . implode(' || ', $conditions) . ')';
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
    public function checked(array|string $allowedValues = [true, 1, '1', 'on', 'yes', 'ja', 'true', 'checked', 'enabled', 'selected'], string $customErrorMsg = ''): self
    {
        // 1. Ensure Data Type is set
        if (!isset($this->dataType) || ($this->dataType !== 'checkbox')) {
            $this->configErrors[] = 'Cannot add Rule `checked` before setting the Data Type Rule to `checkbox`!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['checked'])) {
            $this->configErrors[] = 'Rule `checked` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Conflict Guard
        if (isset($this->rules['unchecked'])) {
            $this->configErrors[] = 'Rule `checked` conflicts with existing rule `unchecked` on Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 4. Defaults & Parameter Normalization
        $defaults = [true, 1, '1', 'on', 'yes', 'ja', 'true', 'checked', 'enabled', 'selected'];
        if (empty($allowedValues)) {
            $values = $defaults;
        } elseif (is_string($allowedValues)) {
            $values = array_filter(array_map('trim', explode(',', $allowedValues)), fn($v) => $v !== '');
        } else {
            $values = array_values($allowedValues);
        }
        if (empty($values)) {
            $this->configErrors[] = 'Rule `checked` requires at least one allowed value for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
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
    public function unchecked(array|string $allowedValues = [false, 0, '0', 'off', 'no', 'nej', 'false', 'unchecked', 'disabled', 'unselected'], string $customErrorMsg = ''): self
    {
        // 1. Ensure Data Type is set
        if (!isset($this->dataType) || ($this->dataType !== 'checkbox')) {
            $this->configErrors[] = 'Cannot add Rule `unchecked` before setting the Data Type Rule to `checkbox`!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['unchecked'])) {
            $this->configErrors[] = 'Rule `unchecked` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Conflict Guard
        if (isset($this->rules['checked'])) {
            $this->configErrors[] = 'Rule `unchecked` conflicts with existing rule `checked` on Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 4. Defaults & Parameter Normalization
        $defaults = [false, 0, '0', 'off', 'no', 'nej', 'false', 'unchecked', 'disabled', 'unselected'];
        if (empty($allowedValues)) {
            $values = $defaults;
        } elseif (is_string($allowedValues)) {
            $values = array_filter(array_map('trim', explode(',', $allowedValues)), fn($v) => $v !== '');
        } else {
            $values = array_values($allowedValues);
        }
        if (empty($values)) {
            $this->configErrors[] = 'Rule `unchecked` requires at least one allowed value for Input Key `{{##INPUT_KEY##}}`!';
            return $this;
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
    public function arr(string $customErrorMsg = ''): self
    {
        $this->dataType = "array";
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
// "PUBLICALLY AVAILABLE" Functions when "use" keyword includes them in a file to "use" them!
function all(string $dataType, string $customErrorMsg = ''): RuleSetAll
{
    return (new RuleSetAll())->setDatatype($dataType, $customErrorMsg);
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
function arr(string $customErrorMsg = ''): RuleSetArray
{
    return (new RuleSetArray())->arr($customErrorMsg);
}
function object(string $customErrorMsg = ''): RuleSetObject
{
    return (new RuleSetObject())->object($customErrorMsg);
}
function files(string $customErrorMsg = ''): RuleSetFile
{
    return (new RuleSetFile())->files($customErrorMsg);
}
