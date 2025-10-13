<?php // ***VERY IMPORTANT: This is GITIGNORED (.gitignore file: /src/funkphp/config/db_config.php)***/
// *** You need to Upload this File Manually for PRODUCTION USE! ***//
// ***  DB_CHARSET is default utf8mb4 - Change as needed during db_connect call!  ***//
/*
     SYNTAX:
     ['UNIQUE_CONNECTION_KEY' =>
        [
         'driver' => 'mysqli'/'pdo_mysql'/'pgsql'/'mongodb'/'etc.',
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
// Define the connection settings based on the environment
$credentials = [];
if (FUNKPHP_IS_LOCAL) {
    // --- LOCAL/DEVELOPMENT CREDENTIALS ---
    $credentials = [
        // Your MAIN local connection profile
        'mysql1' => [
            'driver'   => 'mysqli',
            'host'     => 'localhost',
            'user'     => 'root',
            'password' => '', // Local password
            'database' => 'fphp_dev',
            'port'     => 3306,
            'charset'  => 'utf8mb4',
        ],
        // You can add more local connections here if needed
        'mysql2' => [
            'driver'   => 'mysqli',
            'host'     => '127.0.0.1',
            'user'     => 'test_user',
            'password' => 'test_pass',
            'database' => 'fphp_tests',
            'port'     => 3306,
            'charset'  => 'utf8mb4',
        ],
    ];
}  // else = PRODUCTION - INCLUDE YOUR NECESSARY DB CONFIG FILE
else {
    // --- PRODUCTION/ONLINE CREDENTIALS ---
    // IMPORTANT: Make sure include pathing is correct!
    $credentials = include __DIR__ . '/db_config.php' ?? [];
}
return $credentials;
