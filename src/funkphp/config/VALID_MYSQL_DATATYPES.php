<?php return [
    // This file contains the supported MySQL data types and their properties.
    // It is used to validate the data types and their properties in the database.
    /////////////////////////////////////////////////////////////////////////////'
    // Valid Keys that Table Columns can have in the `funkphp/config/tables.php` File
    "VALID_TABLE_COL_KEYS" => [
        'auto_increment',
        'default',
        'foreign_key',
        'joined_name',
        'nullable',
        'primary_key',
        'references',
        'references_column',
        'references_joined',
        'signed',
        'type',
        'unique',
        'unsigned',
        'value',
    ],
    // CATEGORIES OF DATA TYPES
    // String types
    "STRINGS" => [
        "CHAR" => 1,
        "VARCHAR" => 2,
        "BINARY" => 3,
        "VARBINARY" => 4,
        "TINYBLOB" => 5,
        "BLOB" => 6,
        "MEDIUMBLOB" => 7,
        "LONGBLOB" => 8,
        "TINYTEXT" => 9,
        "TEXT" => 10,
        "MEDIUMTEXT" => 11,
        "LONGTEXT" => 12,
        "NVARCHAR" => 13
    ],
    // Numeric types
    "NUMBERS" => [
        "TINYINT" => 1,
        "SMALLINT" => 2,
        "MEDIUMINT" => 3,
        "INT" => 4,
        "BIGINT" => 5,
        "FLOAT" => 6,
        "DOUBLE" => 7,
        "DECIMAL" => 8,
        "NUMERIC" => 9,
        "DOUBLE PRECISION" => 10,
        "BIT" => 11,
        "DEC" => 12,
        "BOOLEAN" => 13,
    ],
    "INTS" => [
        "TINYINT" => 1,
        "SMALLINT" => 2,
        "MEDIUMINT" => 3,
        "INT" => 4,
        "BIGINT" => 5,
        "SMALLSERIAL" => 6,
        "SERIAL" => 7,
        "BIGSERIAL" => 8,
    ],
    "FLOATS" => [
        "FLOAT" => 1,
        "DOUBLE" => 2,
        "DECIMAL" => 3,
        "DEC" => 4,
        "DOUBLE PRECISION" => 5,
        "NUMERIC" => 6,
        "REAL" => 7,
    ],
    // Date and time types
    "DATETIMES" => [
        "DATE" => 1,
        "TIME"  => 2,
        "DATETIME" => 3,
        "TIMESTAMP" => 4,
        "YEAR"  => 5,
    ],
    // Binary types
    "BLOBS" => [
        "TINYBLOB" => 1,
        "BLOB" => 2,
        "MEDIUMBLOB" => 3,
        "LONGBLOB" => 4
    ],
    // Text types
    "TEXTS" => [
        "TINYTEXT" => 1,
        "TEXT" => 2,
        "MEDIUMTEXT" => 3,
        "LONGTEXT" => 4
    ],
    // These data types cannot have any values assigned to them when
    // creating the table. For example "NVARCHAR" can have that to its
    // "value" key and also VARCHAR, but not "BLOB" or "TEXT" since they
    // already have fixed sizes. And also "ENUM" and "SET" types and all
    // integer and float types since they have max and min values, either
    // signed or unsigned.
    "INVALID_VALUES_FOR_NUMBER_TYPES" => [
        "TINYINT",
        "SMALLINT",
        "MEDIUMINT",
        "INT",
        "BIGINT",
        "FLOAT",
        "DOUBLE",
        "DECIMAL",
        "NUMERIC",
        "DOUBLE PRECISION",
        "BIT",
        "DEC",
        "BOOLEAN",
        "TINYBLOB",
        "BLOB",
        "MEDIUMBLOB",
        "LONGBLOB",
        "TINYTEXT",
        "TEXT",
        "MEDIUMTEXT",
        "LONGTEXT",
        "ENUM",
        "SET",
        "SERIAL",
        "BIGSERIAL",
        "SMALLSERIAL",
        "REAL",
        "DATE",
        "TIME",
        "DATETIME",
        "TIMESTAMP",
        "YEAR",
    ],
    ////////////////////////////////////////////////////////////////////
    // Each data type with its min and max values (including in unsigned
    // where applicable) and optionally the number of digits
    "BIGINT" => [
        "BINDING" => "i",
        "MIN_SIGNED" => -9223372036854775808,
        "MAX_SIGNED" => 9223372036854775807,
        "MIN_USIGNED" => 0,
        "MAX_UNSIGNED" => 18446744073709551615,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 20,
        "TYPE" => "integer",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "BIGSERIAL" => [
        "BINDING" => "i",
        "MIN" => 1,
        "MAX" => 9223372036854775807,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 19,
        "TYPE" => "integer",
    ],
    "BINARY" => [
        "BINDING" => "b",
        "MIN" => null,
        "MAX" => null,
        "TYPE" => "blob"
    ],
    "BIT" => [
        "BINDING" => "i",
        "MIN" => 1,
        "MAX" => 64,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 1,
        "TYPE" => "integer"
    ],
    "BLOB" => [
        "BINDING" => "b",
        "MIN" => 0,
        "MAX" => 65535,
        "TYPE" => "blob"
    ],
    "BOOL" => [
        "BINDING" => "i",
        "MIN" => 0,
        "MAX" => 1,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 1,
        "TYPE" => "integer"
    ],
    "BOOLEAN" => [
        "BINDING" => "i",
        "MIN" => 0,
        "MAX" => 1,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 1,
        "TYPE" => "integer"
    ],
    "CHAR" => [
        "BINDING" => "s",
        "MIN" => 1,
        "MAX" => 255,
        "TYPE" => "string"
    ],
    "DATE" => [
        "BINDING" => "s",
        "MIN" => 10,
        "MAX" => 10,
        "MIN_DIGITS" => 8,
        "MAX_DIGITS" => 8,
        "TYPE" => "date"
    ],
    "DATETIME" => [
        "BINDING" => "s",
        "MIN" => 19,
        "MAX" => 19,
        "MIN_DIGITS" => 16,
        "MAX_DIGITS" => 16,
        "TYPE" => "datetime"
    ],
    "DEC" => [
        "BINDING" => "d",
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65,
        "MIN_D" => 0,
        "MAX_D" => 30,
        "MIN_DIGITS" => 3, // 1 plus dot plus 1
        "MAX_DIGITS" => 96, // 65 plus dot plus 30
        "TYPE" => "float"
    ],
    "DECIMAL" => [
        "BINDING" => "d",
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65,
        "MIN_D" => 0,
        "MAX_D" => 30,
        "MIN_DIGITS" => 3, // 1 plus dot plus 1
        "MAX_DIGITS" => 96, // 65 plus dot plus 30
        "TYPE" => "float"
    ],
    "DOUBLE PRECISION" => [
        "BINDING" => "d",
        "MIN_SIGNED" => -1.7976931348623157E+308,
        "MAX_SIGNED" => -2.2250738585072014E-308,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 15,
        "MIN_UNSIGNED" =>  2.2250738585072014E-308,
        "MAX_UNSIGNED" => 1.7976931348623157E+308,
        "TYPE" => "float",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "DOUBLE" => [
        "BINDING" => "d",
        "MIN_SIGNED" => -1.7976931348623157E+308,
        "MAX_SIGNED" => -2.2250738585072014E-308,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 15,
        "MIN_UNSIGNED" =>  2.2250738585072014E-308,
        "MAX_UNSIGNED" => 1.7976931348623157E+308,
        "TYPE" => "float",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "ENUM" => [
        "BINDING" => "s",
        "MIN" => null,
        "MAX" => null,
        "TYPE" => "string",
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65535,
    ],
    "FLOAT" => [
        "BINDING" => "d",
        "MIN_SIGNED" => -3.402823466E+38,
        "MAX_SIGNED" => -1.175494351E-38,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 7,
        "MIN_UNSIGNED" =>  1.175494351E-38,
        "MAX_UNSIGNED" => 3.402823466E+38,
        "TYPE" => "float",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "INT" => [
        "BINDING" => "i",
        "MIN_SIGNED" => -2147483648,
        "MAX_SIGNED" => 2147483647,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 11,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 4294967295,
        "TYPE" => "integer",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "INTEGER" => [
        "BINDING" => "i",
        "MIN_SIGNED" => -2147483648,
        "MAX_SIGNED" => 2147483647,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 11,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 4294967295,
        "TYPE" => "integer",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "JSON" => [
        "BINDING" => "s",
        "MIN" => null,
        "MAX" => null,
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 18446744073709551616,
        "TYPE" => "json"
    ],
    "LONGBLOB" => [
        "BINDING" => "b",
        "MIN" => 0,
        "MAX" => 4294967295,
        "TYPE" => "blob"
    ],
    "LONGTEXT" => [
        "BINDING" => "s",
        "MIN" => 1,
        "MAX" => 4294967295,
        "TYPE" => "string"
    ],
    "MEDIUMBLOB" => [
        "BINDING" => "b",
        "MIN" => 0,
        "MAX" => 16777215,
        "TYPE" => "blob"
    ],
    "MEDIUMINT" => [
        "BINDING" => "i",
        "MIN_SIGNED" => -8388608,
        "MAX_SIGNED" => 8388607,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 16777215,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 8,
        "TYPE" => "integer",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "MEDIUMTEXT" => [
        "BINDING" => "s",
        "MIN" => 1,
        "MAX" => 16777215,
        "TYPE" => "string"
    ],
    "NVARCHAR" => [
        "BINDING" => "s",
        "MIN" => 1,
        "MAX" => 21845,
        "TYPE" => "string"
    ],
    "NUMERIC" => [
        "BINDING" => "d",
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65,
        "MIN_D" => 0,
        "MAX_D" => 30,
        "MIN_DIGITS" => 3, // 1 plus dot plus 1
        "MAX_DIGITS" => 96, // 65 plus dot plus 30
        "TYPE" => "float"
    ],
    "REAL" => [
        "BINDING" => "d",
        "MIN_SIGNED" => -3.402823466E+38,
        "MAX_SIGNED" => -1.175494351E-38,
        "MIN_DIGITS" => 3, // 1 plus dot plus 1
        "MAX_DIGITS" => 8, // 6 plus dot plus 1
        "MIN_UNSIGNED" =>  1.175494351E-38,
        "MAX_UNSIGNED" => 3.402823466E+38,
        "TYPE" => "float",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "SERIAL" => [
        "BINDING" => "i",
        "MIN" => 1,
        "MAX" => 2147483647,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 10,
        "TYPE" => "integer",
    ],
    "SET" => [
        "BINDING" => "s",
        "MIN" => null,
        "MAX" => null,
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 64,
        "TYPE" => "string"
    ],
    "SMALLINT" => [
        "BINDING" => "i",
        "MIN_SIGNED" => -32768,
        "MAX_SIGNED" => 32767,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 5,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 65535,
        "TYPE" => "integer",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "SMALLSERIAL" => [
        "BINDING" => "i",
        "MIN" => 1,
        "MAX" => 32767,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 5,
        "TYPE" => "integer",
    ],
    "TEXT" => [
        "BINDING" => "s",
        "MIN" => 1,
        "MAX" => 65535,
        "TYPE" => "string"
    ],
    "TIME" => [
        "BINDING" => "s",
        "MIN" => 8,
        "MAX" => 8,
        "MIN_DIGITS" => 6,
        "MAX_DIGITS" => 6,
        "TYPE" => "time"
    ],
    "TIMESTAMP" => [
        "BINDING" => "s",
        "MIN" => 0,
        "MAX" => 2147483647,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 10,
        "TYPE" => "timestamp",
    ],
    "TINYBLOB" => [
        "BINDING" => "b",
        "MIN" => 0,
        "MAX" => 255,
        "TYPE" => "blob"
    ],
    "TINYINT" => [
        "BINDING" => "i",
        "MIN_SIGNED" => -128,
        "MAX_SIGNED" => 127,
        "MIN_DIGITS" => 1,
        "MAX_DIGITS" => 3,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 255,
        "TYPE" => "integer",
        "CAN_BE_(UN)SIGNED" => true
    ],
    "TINYTEXT" => [
        "BINDING" => "s",
        "MIN" => 1,
        "MAX" => 255,
        "TYPE" => "string"
    ],
    "VARBINARY" => [
        "BINDING" => "b",
        "MIN" => 0,
        "MAX" => 255,
        "TYPE" => "blob"
    ],
    "VARCHAR" => [
        "BINDING" => "s",
        "MIN" => 0,
        "MAX" => 65535,
        "TYPE" => "string"
    ],
    "YEAR" => [
        "BINDING" => "i",
        "MIN" => 4,
        "MAX" => 4,
        "MIN_DIGITS" => 4,
        "MAX_DIGITS" => 4,
        "TYPE" => "year"
    ],
];
