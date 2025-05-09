<?php return [
    // This file contains the supported MySQL data types and their properties.
    // It is used to validate the data types and their properties in the database.
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
        "NUMERIC" => 9
    ],
    "INTS" => [
        "TINYINT" => 1,
        "SMALLINT" => 2,
        "MEDIUMINT" => 3,
        "INT" => 4,
        "BIGINT" => 5
    ],
    "FLOATS" => [
        "FLOAT" => 1,
        "DOUBLE" => 2,
        "DECIMAL" => 3,
        "NUMERIC" => 4
    ],
    // Date and time types
    "DATETIMES" => [
        "DATE",
        "TIME",
        "DATETIME",
        "TIMESTAMP",
        "YEAR",
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
    // Each data type with its min and max values (including in unsigned
    // where applicable) and optionally the number of digits
    "BIGINT" => [
        "MIN" => -9223372036854775808,
        "MAX" => 9223372036854775807,
        "MIN_USIGNED" => 0,
        "MAX_UNSIGNED" => 18446744073709551615,
        "DIGITS" => 20,
        "TYPE" => "integer"
    ],
    "BINARY" => [
        "MIN" => null,
        "MAX" => null,
        "DIGITS" => null,
        "TYPE" => "blob"
    ],
    "BIT" => [
        "MIN" => 1,
        "MAX" => 64,
        "DIGITS" => 1,
        "TYPE" => "integer"
    ],
    "BLOB" => [
        "MIN" => 0,
        "MAX" => 65535,
        "DIGITS" => 5,
        "TYPE" => "blob"
    ],
    "BOOL" => [
        "MIN" => 0,
        "MAX" => 1,
        "DIGITS" => 1,
        "TYPE" => "integer"
    ],
    "BOOLEAN" => [
        "MIN" => 0,
        "MAX" => 1,
        "DIGITS" => 1,
        "TYPE" => "integer"
    ],
    "CHAR" => [
        "MIN" => 0,
        "MAX" => 255,
        "DIGITS" => 3,
        "TYPE" => "string"
    ],
    "DATE" => [
        "MIN" => 10,
        "MAX" => 10,
        "DIGITS" => null,
        "TYPE" => "date"
    ],
    "DATETIME" => [
        "MIN" => 19,
        "MAX" => 19,
        "DIGITS" => null,
        "TYPE" => "datetime"
    ],
    "DEC" => [
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65,
        "MIN_D" => 0,
        "MAX_D" => 30,
        "DIGITS" => null,
        "TYPE" => "float"
    ],
    "DECIMAL" => [
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65,
        "MIN_D" => 0,
        "MAX_D" => 30,
        "DIGITS" => null,
        "TYPE" => "float"
    ],
    "DOUBLE PRECISION" => [
        "MIN" => -1.7976931348623157E+308,
        "MAX" => 1.7976931348623157E+308,
        "DIGITS" => 15,
        "TYPE" => "float"
    ],
    "DOUBLE" => [
        "MIN" => -1.7976931348623157E+308,
        "MAX" => 1.7976931348623157E+308,
        "DIGITS" => 15,
        "TYPE" => "float"
    ],
    "ENUM" => [
        "MIN" => null,
        "MAX" => null,
        "DIGITS" => null,
        "TYPE" => "string"
    ],
    "FLOAT" => [
        "MIN" => -3.402823466E+38,
        "MAX" => 3.402823466E+38,
        "DIGITS" => 7,
        "TYPE" => "float"
    ],
    "INT" => [
        "MIN" => -2147483648,
        "MAX" => 2147483647,
        "DIGITS" => 11,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 4294967295,
        "TYPE" => "integer"
    ],
    "INTEGER" => [
        "MIN" => -2147483648,
        "MAX" => 2147483647,
        "DIGITS" => 11,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 4294967295,
        "TYPE" => "integer"
    ],
    "JSON" => [
        "MIN" => null,
        "MAX" => null,
        "DIGITS" => null,
        "TYPE" => "json"
    ],
    "LONGBLOB" => [
        "MIN" => 0,
        "MAX" => 4294967295,
        "DIGITS" => 10,
        "TYPE" => "blob"
    ],
    "LONGTEXT" => [
        "MIN" => 0,
        "MAX" => 4294967295,
        "DIGITS" => 10,
        "TYPE" => "string"
    ],
    "MEDIUMBLOB" => [
        "MIN" => 0,
        "MAX" => 16777215,
        "DIGITS" => 8,
        "TYPE" => "blob"
    ],
    "MEDIUMINT" => [
        "MIN" => -8388608,
        "MAX" => 8388607,
        "DIGITS" => 8,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 16777215,
        "TYPE" => "integer"
    ],
    "MEDIUMTEXT" => [
        "MIN" => 0,
        "MAX" => 16777215,
        "DIGITS" => 8,
        "TYPE" => "string"
    ],
    "NVARCHAR" => [
        "MIN" => 0,
        "MAX" => 21845,
        "DIGITS" => 5,
        "TYPE" => "string"
    ],
    "NUMERIC" => [
        "MIN_SIZE" => 0,
        "MAX_SIZE" => 65,
        "MIN_D" => 0,
        "MAX_D" => 30,
        "DIGITS" => null,
        "TYPE" => "float"
    ],
    "SET" => [
        "MIN" => null,
        "MAX" => null,
        "DIGITS" => null,
        "TYPE" => "string"
    ],
    "SMALLINT" => [
        "MIN" => -32768,
        "MAX" => 32767,
        "DIGITS" => 5,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 65535,
        "TYPE" => "integer"
    ],
    "TEXT" => [
        "MIN" => 0,
        "MAX" => 65535,
        "DIGITS" => 5,
        "TYPE" => "string"
    ],
    "TIME" => [
        "MIN" => 8,
        "MAX" => 8,
        "DIGITS" => 6,
        "TYPE" => "time"
    ],
    "TIMESTAMP" => [
        "MIN" => 0,
        "MAX" => 2147483647,
        "DIGITS" => 10,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 4294967295,
        "TYPE" => "timestamp"
    ],
    "TINYBLOB" => [
        "MIN" => 0,
        "MAX" => 255,
        "DIGITS" => 3,
        "TYPE" => "blob"
    ],
    "TINYINT" => [
        "MIN" => -128,
        "MAX" => 127,
        "DIGITS" => 3,
        "MIN_UNSIGNED" => 0,
        "MAX_UNSIGNED" => 255,
        "TYPE" => "integer"
    ],
    "TINYTEXT" => [
        "MIN" => 0,
        "MAX" => 255,
        "DIGITS" => 3,
        "TYPE" => "string"
    ],
    "VARBINARY" => [
        "MIN" => 0,
        "MAX" => 255,
        "DIGITS" => 3,
        "TYPE" => "blob"
    ],
    "VARCHAR" => [
        "MIN" => 0,
        "MAX" => 65535,
        "DIGITS" => 5,
        "TYPE" => "string"
    ],
    "YEAR" => [
        "MIN" => 4,
        "MAX" => 4,
        "DIGITS" => 4,
        "TYPE" => "year"
    ],
];
