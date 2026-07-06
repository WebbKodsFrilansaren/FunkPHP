<?php
// README_IN_IDE.php - FunkPHP | FunkCLI recreated it 2026-07-06 21:43:08

/**
 * -----------------------------------------------------
 * FUNKPHP AUTOMATICALLY GENERATED/CREATED COMPILED FILE
 * -----------------------------------------------------
 */
// ***VERY IMPORTANT: This is GITIGNORED as '/src/funkphp/config/conns.php)' ***/
// *** You need to Upload this File Manually for PRODUCTION USE! ***//
// ***  Below is a Connection Example for the Drivers whose Data Schema Validation is supported!  ***//
// *** MAKE A COPY OF THIS FILE AND RENAME IT TO "conns.php" and put it in "/src/funkphp/config" so it
// *** becomes "/src/funkphp/config/conns.php". Then it will be picked up when compiling to single file!
// *** SUPER IMPORTANT: If you see an error about non-supported "driver" key, use the "--ignore-unknown-conns-drivers"
// *** compilation flag when you run `php funk build` command and it will ignore those unknown while validating the rest!
// *** `src/funkphp/config/README_IN_IDE.php` is recreated automatically when `conns.php` does not exist anymore!
/*
     SYNTAX:
     [
     // Use this connection key with "funk_credentials_connect(,'UNIQUE_CONNECTION_KEY')" function!
     'UNIQUE_CONNECTION_KEY' =>
        [
         'driver' => 'mysqli'/'pdo_mysql'/'pgsql'/'mongodb'/'etc. It is Validated ',
         'host' => DB_HOST,
         'user' => DB_USER,
         'password' => DB_PASSWORD,
         'database' => DB_NAME,
         'port' => DB_PORT,
         'charset' => 'DB_CHARSET',
         'add_other_keys' => 'and_values_here',
        ],
     ], // and so on...
    */
return array(
  'mysql_native' =>
  array(
    'driver' => 'mysqli',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'secret',
    'database' => 'funk_db',
    'port' => 3306,
    'charset' => 'utf8mb4',
  ),
  'mysql_pdo' =>
  array(
    'driver' => 'pdo_mysql',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'secret',
    'database' => 'funk_db',
    'port' => 3306,
    'charset' => 'utf8mb4',
  ),
  'postgres_pdo' =>
  array(
    'driver' => 'pdo_pgsql',
    'host' => 'localhost',
    'user' => 'postgres',
    'password' => 'secret_pg_pass',
    'database' => 'funk_postgres',
    'port' => 5432,
    'sslmode' => 'prefer',
  ),
  'redis_main' =>
  array(
    'driver' => 'redis',
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => 'redis_auth_token',
    'database' => 0,
    'timeout' => 0,
  ),
  'memcached_cluster' =>
  array(
    'driver' => 'memcached',
    'servers' =>
    array(
      0 =>
      array(
        0 => '127.0.0.1',
        1 => 11211,
        2 => 100,
      ),
    ),
  ),
  'mongo_docs' =>
  array(
    'driver' => 'mongodb',
    'dsn' => 'mongodb://root:secret@127.0.0.1:27017',
    'database' => 'funk_nosql',
    'options' =>
    array(),
  ),
  'aws_dynamo' =>
  array(
    'driver' => 'dynamodb',
    'region' => 'us-east-1',
    'version' => 'latest',
    'key' => 'AKIAIOSFODNN7EXAMPLE',
    'secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
    'endpoint' => 'http://localhost:8000',
  ),
);
