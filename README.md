# FunkPHP😎🕺🎶🌈 - The 99 % Funktion-based PHP Framework by WebbKodsFrilansaren at GitHub

- This is a ~99 % function-based PHP framework meaning there are almost no classes used besides using objects for accessing some data because that is more convenient at some times.

## QUICK START GUIDE

- TODO: Add YT QUICK-START-GUIDE!

### Start Tailwind CSS Locally in IDE

- `npm run tww` to start Tailwind CSS watching in IDE

### Configure Database - /src/funkphp/config/db_config.php (NOT included in Repo Clone!)

- OPTION 1# Go into "/src/funkphp/config/db.php" and hardcode DB Connection Values from lines 12-21

- OPTION 2# Create a file called "db_config.php" and put it in "/src/funkphp/config/" using this startin code:

```
<?php // ***VERY IMPORTANT: This is GITIGNORED (.gitignore file: /src/funkphp/config/db_config.php)***/
// *** You need to Upload this File Manually for PRODUCTION USE! ***//
// ***  DB_CHARSET is default utf8mb4 - Change as needed during db_connect call!  ***//

return [
    "DB_HOST" => "",
    "DB_USER" => "",
    "DB_PASSWORD" => "",
    "DB_NAME" => "",
    "DB_PORT" => "3306",
];
```

### TO BE UPDATED
