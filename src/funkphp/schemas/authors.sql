CREATE TABLE authors(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(128) UNIQUE,
    description tinytext NOT NULL DEFAULT "No description",
    longer_description text NOT NULL DEFAULT "No longer description",
    age INT(3) UNSIGNED DEFAULT 18,
    weight FLOAT DEFAULT 70.0 NOT NULL,
    nickname VARCHAR(255) DEFAULT "Anonymous",
    updated_at TIME DEFAULT NOW()
);