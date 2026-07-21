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
    private array $dataTypeRules = [
        'string',
        'integer',
        'float',
        'boolean',
        'number',
        'phone',
        'date',
        'array',
        'arr',
        'email',
        'password',
        'password_match',
        'url',
        'ip',
        'uuid',
        'json',
        'object'
    ];
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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

    public function setDatatype(string $dataType, string $customErrorMsg = ''): self
    {
        if (isset($this->dataType)) {
            $this->configErrors[] = 'A Data Type already chosen: `' . ((is_string($this->dataType)) ? $this->dataType : 'NOT_A_STRING') . '`!';
        } else if (!in_array($dataType, $this->dataTypeRules)) {
            $this->configErrors[] = 'No/Invalid Data Type chosen: `' . ((is_string($dataType)) ? $dataType : 'NOT_A_STRING') . '`. Choose from: `' . implode(', ', $this->dataTypeRules) . '`!';
        } else {
            $this->dataType = $dataType;
        }
        return $this;
    }

    public function nullable(): self
    {
        $this->useNullable = true;
        return $this;
    }
    public function required(string $customErrorMsg = ''): self
    {
        $this->useRequired = true;
        return $this;
    }
}

class RuleSetString
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
            $this->configErrors[] = 'A Data Type already chosen: `' . ((is_string($this->dataType)) ? $this->dataType : 'NOT_A_STRING') . '`!';
        } else {
            $this->dataType = "string";
        }
        return $this;
    }

    public function nullable(): self
    {
        $this->useNullable = true;
        return $this;
    }
    public function required(): self
    {
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
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetEmail
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetBoolean
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetNumber
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetInteger
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetFloat
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetPhone
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetArray
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
}

class RuleSetObject
{
    public ?string $dataType = null;
    public ?bool $useNullable = false;
    public ?bool $useRequired = false;
    public ?bool $useBail = false;
    public ?bool $useStopAllOnFirstError = false;
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
function object(string $customErrorMsg = ''): RuleSetObject
{
    return (new RuleSetObject())->object($customErrorMsg);
}
function arr(string $customErrorMsg = ''): RuleSetArray
{
    return (new RuleSetArray())->arr($customErrorMsg);
}
