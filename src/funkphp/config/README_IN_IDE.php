<?php // ***VERY IMPORTANT: This is GITIGNORED (.gitignore file: /src/funkphp/config/conns.php)***/
// *** You need to Upload this File Manually for PRODUCTION USE! ***//
// ***  Below is a Connection Example for the Drivers whose Data Schema Validation is supported!  ***//
// *** MAKE A COPY OF THIS FILE AND RENAME IT TO "conns.php" and put it in "/src/funkphp/config" so it
// *** becomes "/src/funkphp/config/conns.php". Then it will be picked up when compiling to single file!
// *** SUPER IMPORTANT: If you see an error about non-supported "driver" key, use the "--ignore-unknown-conns-drivers"
// *** compilation flag when you run `php funk build` command and it will ignore those unknown while validating the rest!
/*
     SYNTAX:
     [
     // Use this connection key with "funk_credentials_connect($c,'UNIQUE_CONNECTION_KEY')" function!
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
return [
    // --- PRODUCTION/ONLINE CREDENTIALS ---
    // --- NATIVE EXTENSION MYSQL PROFILE ---
    'funkphp_dev' => [ // Recommended MySQLI for Local Development!
        'driver'   => 'mysqli',
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => '', // Local password
        'database' => 'fphp',
        'port'     => 3306,
        'charset'  => 'utf8mb4',
    ],
    'mysql_native' => [
        'driver'   => 'mysqli',
        'host'     => '127.0.0.1',
        'user'     => 'root',
        'password' => 'secret',
        'database' => 'funk_db',
        'port'     => 3306,
        'charset'  => 'utf8mb4', // Optional key
    ],
    // --- PDO MYSQL PROFILE ---
    'mysql_pdo' => [
        'driver'   => 'pdo_mysql',
        'host'     => '127.0.0.1',
        'user'     => 'root',
        'password' => 'secret',
        'database' => 'funk_db',
        'port'     => 3306,      // Optional key
        'charset'  => 'utf8mb4', // Optional key
    ],
    // --- PDO POSTGRESQL PROFILE ---
    'postgres_pdo' => [
        'driver'   => 'pdo_pgsql',
        'host'     => 'localhost',
        'user'     => 'postgres',
        'password' => 'secret_pg_pass',
        'database' => 'funk_postgres',
        'port'     => 5432,      // Optional key
        'sslmode'  => 'prefer',  // Optional key (e.g., 'disable', 'require', 'prefer')
    ],
    // --- REDIS CACHE/STORE PROFILE ---
    'redis_main' => [
        'driver'   => 'redis',
        'host'     => '127.0.0.1',
        'port'     => 6379,              // Optional key
        'password' => 'redis_auth_token', // Optional key
        'database' => 0,                 // Optional key (Must be an integer index)
        'timeout'  => 0,                 // Optional key (Must be an integer)
    ],
    // --- MEMCACHED MEMORY CLUSTER PROFILE ---
    'memcached_cluster' => [
        'driver'  => 'memcached',
        'servers' => [ // Required array pattern for handling node routing/weight pooling
            ['127.0.0.1', 11211, 100],
            // ['127.0.0.2', 11211, 50], // Add additional cluster nodes as needed
        ],
    ],
    // --- MONGODB DOCUMENT TRACK PROFILE ---
    'mongo_docs' => [
        'driver'   => 'mongodb',
        'dsn'      => 'mongodb://root:secret@127.0.0.1:27017', // Native connection string URI
        'database' => 'funk_nosql',
        'options'  => [], // Optional internal driver arrays tuning parameters
    ],
    // --- AWS DYNAMODB KEY-VALUE PROFILE ---
    'aws_dynamo' => [
        'driver'   => 'dynamodb',
        'region'   => 'us-east-1',
        'version'  => 'latest',                  // String version required by AWS SDK
        'key'      => 'AKIAIOSFODNN7EXAMPLE',    // Optional key (leave empty if using IAM Roles/ENV) - THIS IS RANDOM-GENERATED HERE!
        'secret'   => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY', // Optional key - THIS IS RANDOM-GENERATED HERE!
        'endpoint' => 'http://localhost:8000',   // Optional key (Crucial pathing for LocalStack or local testing containers)
    ],
];
