<?php
// DEFAULT DB Connection local & online - Change as needed!
if (FUNKPHP_IS_LOCAL) {
    return [
        define("DB_HOST", "localhost"),
        define("DB_USER", "root"),
        define("DB_PASSWORD", ""),
        define("DB_NAME", "fphp"),
        define("DB_PORT", "3306"),
    ];
}
// This is your ONLINE Database connection settings!
else {
    // Either include a file, or just hardcode your DB connection!
    $dbFile = include __DIR__ . '/config/db_config.php';
    return [
        define("DB_HOST", $dbFile['DB_HOST'] ?? "localhost"),
        define("DB_USER", $dbFile['DB_USER'] ?? "root"),
        define("DB_PASSWORD", $dbFile['DB_PASSWORD'] ?? ""),
        define("DB_NAME", $dbFile['DB_NAME'] ?? "fphp"),
        define("DB_PORT", $dbFile['DB_PORT'] ?? "3306"),
    ];
}
