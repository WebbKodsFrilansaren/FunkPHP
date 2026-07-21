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
    public array $typeGuardMap = [
        // File Types
        'files'      => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'file'      => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'image'     => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'video'     => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'audio'     => '(is_array({{##INPUT##}}) && isset({{##INPUT##}}[\'tmp_name\']) && is_uploaded_file({{##INPUT##}}[\'tmp_name\']))',
        'string'    => 'is_string({{##INPUT##}})',
        'date'    => 'is_string({{##INPUT##}})',
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
    public function min(int|float $minValue, string $customErrorMsg = ''): self
    {
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `min` before setting the Data Type Rule!';
            return $this;
        }
        if (isset($this->rules['min'])) {
            $this->configErrors[] = 'Rule `min` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (
            isset($this->rules['between'])
            || isset($this->rules['between_mb'])
            || isset($this->rules['size']) || isset($this->rules['min_mb'])
        ) {
            $this->configErrors[] = 'Rule `min` conflicts with existing boundary/exact rules (`between`, `between_mb` or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `max` before setting the Data Type Rule!';
            return $this;
        }
        if (isset($this->rules['max'])) {
            $this->configErrors[] = 'Rule `max` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (
            isset($this->rules['between'])
            || isset($this->rules['between_mb'])
            || isset($this->rules['size'])
            || isset($this->rules['max_mb'])
        ) {
            $this->configErrors[] = 'Rule `max` conflicts with existing boundary/exact rules (`between`, `between_mb`, `max_mb` or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `min_mb` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['min_mb'])) {
            $this->configErrors[] = 'Rule `min_mb` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (
            isset($this->rules['between'])
            || isset($this->rules['between_mb'])
            || isset($this->rules['size'])
            || isset($this->rules['min'])
        ) {
            $this->configErrors[] = 'Rule `min_mb` conflicts with existing boundary/exact rules (`between`, `between_mb`, `min`, or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 5. Enforce Category Rule (Strictly String Category)
        if ($this->dataTypeCategory !== 'string') {
            $this->configErrors[] = 'Rule `min_mb` is only valid for `string` types, but Data Type `' . $this->dataType . '` (Category `' . $this->dataTypeCategory . '`) was selected for Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `max_mb` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['max_mb'])) {
            $this->configErrors[] = 'Rule `max_mb` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (
            isset($this->rules['between'])
            || isset($this->rules['between_mb'])
            || isset($this->rules['size'])
            || isset($this->rules['max'])
        ) {
            $this->configErrors[] = 'Rule `max_mb` conflicts with existing boundary/exact rules (`between`, `between_mb`, `max`, or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 5. Enforce Category Rule (Strictly String Category)
        if ($this->dataTypeCategory !== 'string') {
            $this->configErrors[] = 'Rule `max_mb` is only valid for `string` types, but Data Type `' . $this->dataType . '` (Category `' . $this->dataTypeCategory . '`) was selected for Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `between` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['between'])) {
            $this->configErrors[] = 'Rule `between` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Range Sanity Guard ($minVal must be strictly less than $maxVal)
        if ($minVal >= $maxVal) {
            $this->configErrors[] = 'Rule `between` has invalid range `[' . $minVal . ', ' . $maxVal . ']` for Input Key `{{##INPUT_KEY##}}`. Min value must be strictly smaller than Max value!';
            return $this;
        }
        if (
            isset($this->rules['size']) || isset($this->rules['between_mb'])
            || isset($this->rules['min']) || isset($this->rules['max'])
            || isset($this->rules['min_mb']) || isset($this->rules['max_mb'])
        ) {
            $this->configErrors[] = 'Rule `between` conflicts with existing boundary/exact rules (`min`, `min_mb`, `max`, `max_mb`, `between_mb` or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `between_mb` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['between_mb'])) {
            $this->configErrors[] = 'Rule `between_mb` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (
            isset($this->rules['size']) || isset($this->rules['between'])
            || isset($this->rules['min']) || isset($this->rules['max'])
            || isset($this->rules['min_mb']) || isset($this->rules['max_mb'])
        ) {
            $this->configErrors[] = 'Rule `between_mb` conflicts with existing boundary/exact rules (`min`, `min_mb`, `max`, `max_mb`, `between` or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 5. Enforce Category Rule (Strictly String Category)
        if ($this->dataTypeCategory !== 'string') {
            $this->configErrors[] = 'Rule `between_mb` is only valid for `string` types, but Data Type `' . $this->dataType . '` (Category `' . $this->dataTypeCategory . '`) was selected for Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `size` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['size'])) {
            $this->configErrors[] = 'Rule `size` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Prevent Logical Redundancy / Conflicts
        if (
            isset($this->rules['min']) || isset($this->rules['max'])
            || isset($this->rules['between']) || isset($this->rules['between_mb'])
        ) {
            $this->configErrors[] = 'Rule `size` conflicts with existing boundary rules (`min`, `max`, `between`, or `between_mb`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `size_mb` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['size_mb'])) {
            $this->configErrors[] = 'Rule `size_mb` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Prevent Logical Redundancy / Conflicts
        if (
            isset($this->rules['min']) || isset($this->rules['max'])
            || isset($this->rules['min_mb']) || isset($this->rules['max_mb'])
            || isset($this->rules['between']) || isset($this->rules['between_mb'])
            || isset($this->rules['size'])
        ) {
            $this->configErrors[] = 'Rule `size_mb` conflicts with existing boundary rules (`min`, `max`, `min_mb`, `max_mb`, `between`, `between_mb`, or `size`) on Input Key `{{##INPUT_KEY##}}`!';
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
        // 6. Enforce Category Rule (Strictly String Category)
        if ($this->dataTypeCategory !== 'string') {
            $this->configErrors[] = 'Rule `size_mb` is only valid for `string` types, but Data Type `' . $this->dataType . '` (Category `' . $this->dataTypeCategory . '`) was selected for Input Key `{{##INPUT_KEY##}}`!';
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
    public function gte(string $targetFieldinValidation, string $customErrorMsg = ''): self
    {
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `gte` (greater than or equal) before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['gte'])) {
            $this->configErrors[] = 'Rule `gte` (greater than or equal) is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (isset($this->rules['gt'])) {
            $this->configErrors[] = 'Rule `gte` (greater than or equal) conflicts with existing rule `gt` on Input Key `{{##INPUT_KEY##}}`!';
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
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `gt` (greater than) before setting the Data Type Rule!';
            return $this;
        }
        if (isset($this->rules['gt'])) {
            $this->configErrors[] = 'Rule `gt` (greater than) is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (isset($this->rules['gte'])) {
            $this->configErrors[] = 'Rule `gt` (greater than) conflicts with existing rule `gte` on Input Key `{{##INPUT_KEY##}}`!';
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
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `lte` (less than or equal) before setting the Data Type Rule!';
            return $this;
        }
        if (isset($this->rules['lte'])) {
            $this->configErrors[] = 'Rule `lte` (less than or equal) is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (isset($this->rules['lt'])) {
            $this->configErrors[] = 'Rule `lte` (less than or equal) conflicts with existing rule `lt` on Input Key `{{##INPUT_KEY##}}`!';
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
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `lt` (less than) before setting the Data Type Rule!';
            return $this;
        }
        if (isset($this->rules['lt'])) {
            $this->configErrors[] = 'Rule `lt` (less than) is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        if (isset($this->rules['lte'])) {
            $this->configErrors[] = 'Rule `lt` (less than) conflicts with existing rule `lte` on Input Key `{{##INPUT_KEY##}}`!';
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
    public function regex(string|array $regexOrRegexes, string $customErrorMsg = ''): self
    {
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `regex` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['regex'])) {
            $this->configErrors[] = 'Rule `regex` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Enforce Category Rule (Strictly String Category)
        if ($this->dataTypeCategory !== 'string') {
            $this->configErrors[] = 'Rule `regex` is only valid for `string` types, but Data Type `' . $this->dataType . '` (Category `' . $this->dataTypeCategory . '`) was selected for Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `not_regex` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['not_regex'])) {
            $this->configErrors[] = 'Rule `not_regex` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Enforce Category Rule (Strictly String Category)
        if ($this->dataTypeCategory !== 'string') {
            $this->configErrors[] = 'Rule `not_regex` is only valid for `string` types, but Data Type `' . $this->dataType . '` (Category `' . $this->dataTypeCategory . '`) was selected for Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `in` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['in'])) {
            $this->configErrors[] = 'Rule `in` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Conflict Guard
        if (isset($this->rules['not_in'])) {
            $this->configErrors[] = 'Rule `in` conflicts with existing rule `not_in` on Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 4. Enforce Data Type Category (String or Numeric)
        if ($this->dataTypeCategory !== 'string' && $this->dataTypeCategory !== 'numeric' && $this->dataTypeCategory !== 'boolean') {
            $this->configErrors[] = 'Rule `in` is Only Valid for Scalar Types (`string`, `numeric`, `boolean`), but Data Type `' . $this->dataType . '` was selected for Input Key `{{##INPUT_KEY##}}`!';
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
        // 1. Ensure Data Type is set
        if (!isset($this->dataType)) {
            $this->configErrors[] = 'Cannot add Rule `not_in` before setting the Data Type Rule!';
            return $this;
        }
        // 2. Prevent Duplicate Rule Usage
        if (isset($this->rules['not_in'])) {
            $this->configErrors[] = 'Rule `not_in` is already used for Input Key: `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 3. Conflict Guard
        if (isset($this->rules['in'])) {
            $this->configErrors[] = 'Rule `not_in` conflicts with existing rule `in` on Input Key `{{##INPUT_KEY##}}`!';
            return $this;
        }
        // 4. Enforce Data Type Category (String, Numeric, or Boolean)
        if ($this->dataTypeCategory !== 'string' && $this->dataTypeCategory !== 'numeric' && $this->dataTypeCategory !== 'boolean') {
            $this->configErrors[] = 'Rule `not_in` is Only Valid for Scalar Types (`string`, `numeric`, `boolean`), but Data Type `' . $this->dataType . '` was selected for Input Key `{{##INPUT_KEY##}}`!';
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
}

class RuleSetString
{
    public ?string $dataType = null;
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

    public function string(string $customErrorMsg = ''): self
    {
        if (isset($this->dataType)) {
            $this->configErrors[] = 'The `string` Data Type is already set!';
            return $this;
        }
        $this->dataType = "string";
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

class RuleSetNumber
{
    public ?string $dataType = null;
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
