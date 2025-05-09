CREATE TABLE authors(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    email NVARCHAR UNIQUE,
    description tinytext NOT NULL DEFAULT "No description",
    longer_description text NOT NULL DEFAULT "No longer description",
    age INT(3) DEFAULT 18 SIGNED,
    enum_test ENUM('a test', 'b or more', 'c what i did') DEFAULT 'c',
    set_test SET('every', 'unique', 'carrot or morot') DEFAULT 'a,b',
    weight FLOAT DEFAULT 70.0 NOT NULL,
    nickname NVARCHAR(255) DEFAULT "Anonymous",
    updated_at TIME DEFAULT NOW()
);