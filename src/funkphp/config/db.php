<?php
// DEFAULT DB Connection local & online - Change as needed!
if (FUNKPHP_IS_LOCAL) {
    return [
        define("DB_HOST1", "localhost"),
        define("DB_USER1", "root"),
        define("DB_PASSWORD1", ""),
        define("DB_NAME1", "fphp"),
        define("DB_PORT1", "3306"),
        define("DB_CHARSET1", "utf8mb4"),
    ];
}
// This is your ONLINE Database connection settings!
else {
    // Either include a file, or just hardcode your DB connection!
    $dbFile = include __DIR__ . '/config/db_config.php' ?? [];
    return [
        define("DB_HOST1", $dbFile['DB_HOST'] ?? "localhost"),
        define("DB_USER1", $dbFile['DB_USER'] ?? "root"),
        define("DB_PASSWORD1", $dbFile['DB_PASSWORD'] ?? ""),
        define("DB_NAME1", $dbFile['DB_NAME'] ?? "fphp"),
        define("DB_PORT1", $dbFile['DB_PORT'] ?? "3306"),
        define("DB_CHARSET1", $dbFile['DB_CHARSET'] ?? "utf8mb4"),
    ];
}
