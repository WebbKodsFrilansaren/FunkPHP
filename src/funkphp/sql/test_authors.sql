CREATE TABLE authors(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    email NVARCHAR(255) UNIQUE,
    age INT DEFAULT 18,
    nickname NVARCHAR(255) DEFAULT "Anonymous",
    updated_at TIME DEFAULT NOW(),
);

array(6) {
  ["joined_name"]=>
  string(12) "authors_name"
  ["type"]=>
  string(8) "NVARCHAR"
  ["value"]=>
  string(3) "255"
  ["nullable"]=>
  bool(false)
  ["unique"]=>
  bool(false)
  ["default"]=>
  NULL
}
array(6) {
  ["joined_name"]=>
  string(13) "authors_email"
  ["type"]=>
  string(8) "NVARCHAR"
  ["value"]=>
  string(3) "255"
  ["nullable"]=>
  bool(true)
  ["unique"]=>
  bool(true)
  ["default"]=>
  NULL
}
array(6) {
  ["joined_name"]=>
  string(11) "authors_age"
  ["type"]=>
  string(3) "INT"
  ["value"]=>
  NULL
  ["nullable"]=>
  bool(true)
  ["unique"]=>
  bool(false)
  ["default"]=>
  int(18)
}
array(6) {
  ["joined_name"]=>
  string(16) "authors_nickname"
  ["type"]=>
  string(8) "NVARCHAR"
  ["value"]=>
  string(3) "255"
  ["nullable"]=>
  bool(true)
  ["unique"]=>
  bool(false)
  ["default"]=>
  string(9) "Anonymous"
}
array(6) {
  ["joined_name"]=>
  string(18) "authors_updated_at"
  ["type"]=>
  string(4) "TIME"
  ["value"]=>
  NULL
  ["nullable"]=>
  bool(true)
  ["unique"]=>
  bool(false)
  ["default"]=>
  string(5) "NOW()"
}
array(1) {
  ["authors"]=>
  array(5) {
    ["name"]=>
    array(1) {
      ["string"]=>
      array(1) {
        ["error"]=>
        NULL
      }
    }
    ["email"]=>
    array(1) {
      ["string"]=>
      array(1) {
        ["error"]=>
        NULL
      }
    }
    ["age"]=>
    array(1) {
      ["integer"]=>
      array(1) {
        ["error"]=>
        NULL
      }
    }
    ["nickname"]=>
    array(1) {
      ["string"]=>
      array(1) {
        ["error"]=>
        NULL
      }
    }
    ["updated_at"]=>
    array(1) {
      ["time"]=>
      array(1) {
        ["error"]=>
        NULL
      }
    }
  }
}