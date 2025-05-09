<?php
return  [
  'tables' =>
  [
    'authors' =>
    [
      'id' =>
      [
        'joined_name' => 'authors_id',
        'auto_increment' => true,
        'type' => 'BIGINT',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'name' =>
      [
        'joined_name' => 'authors_name',
        'type' => 'NVARCHAR',
        'value' => 255,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'email' =>
      [
        'joined_name' => 'authors_email',
        'type' => 'NVARCHAR',
        'value' => 128,
        'nullable' => false,
        'unique' => true,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'description' =>
      [
        'joined_name' => 'authors_description',
        'type' => 'TINYTEXT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'No description',
      ],
      'longer_description' =>
      [
        'joined_name' => 'authors_longer_description',
        'type' => 'TEXT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'No longer description',
      ],
      'age' =>
      [
        'joined_name' => 'authors_age',
        'type' => 'INT',
        'value' => 3,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => true,
        'default' => 18,
      ],
      'enum_test' =>
      [
        'joined_name' => 'authors_enum_test',
        'type' => 'ENUM',
        'value' =>
        [
          0 => 'a test',
          1 => 'b or more',
          2 => 'c what i did',
        ],
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'c',
      ],
      'set_test' =>
      [
        'joined_name' => 'authors_set_test',
        'type' => 'SET',
        'value' =>
        [
          0 => 'every',
          1 => 'unique',
          2 => 'carrot or morot',
        ],
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'a,b',
      ],
      'weight' =>
      [
        'joined_name' => 'authors_weight',
        'type' => 'FLOAT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 70,
      ],
      'nickname' =>
      [
        'joined_name' => 'authors_nickname',
        'type' => 'NVARCHAR',
        'value' => 255,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'Anonymous',
      ],
      'updated_at' =>
      [
        'joined_name' => 'authors_updated_at',
        'type' => 'TIME',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'NOW()',
      ],
    ],
  ],
  'relationships' =>
  [],
];
