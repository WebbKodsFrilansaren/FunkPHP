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
        'binding' => 'i',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'name' =>
      [
        'joined_name' => 'authors_name',
        'type' => 'VARCHAR',
        'binding' => 's',
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
        'type' => 'VARCHAR',
        'binding' => 's',
        'value' => 128,
        'nullable' => true,
        'unique' => true,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'description' =>
      [
        'joined_name' => 'authors_description',
        'type' => 'TINYTEXT',
        'binding' => 's',
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
        'binding' => 's',
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
        'binding' => 'i',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => true,
        'signed' => false,
        'default' => 18,
      ],
      'weight' =>
      [
        'joined_name' => 'authors_weight',
        'type' => 'FLOAT',
        'binding' => 'd',
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
        'type' => 'VARCHAR',
        'binding' => 's',
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
        'binding' => 's',
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
  [
    'authors' =>
    [],
  ],
  'mappings' =>
  [
    'authors' =>
    [
      'id' =>
      [
        'json' => 'authors_id',
        'post' => 'authors_id',
        'get' => 'authors_id',
      ],
      'name' =>
      [
        'json' => 'authors_name',
        'post' => 'authors_name',
        'get' => 'authors_name',
      ],
      'email' =>
      [
        'json' => 'authors_email',
        'post' => 'authors_email',
        'get' => 'authors_email',
      ],
      'description' =>
      [
        'json' => 'authors_description',
        'post' => 'authors_description',
        'get' => 'authors_description',
      ],
      'longer_description' =>
      [
        'json' => 'authors_longer_description',
        'post' => 'authors_longer_description',
        'get' => 'authors_longer_description',
      ],
      'age' =>
      [
        'json' => 'authors_age',
        'post' => 'authors_age',
        'get' => 'authors_age',
      ],
      'weight' =>
      [
        'json' => 'authors_weight',
        'post' => 'authors_weight',
        'get' => 'authors_weight',
      ],
      'nickname' =>
      [
        'json' => 'authors_nickname',
        'post' => 'authors_nickname',
        'get' => 'authors_nickname',
      ],
      'updated_at' =>
      [
        'json' => 'authors_updated_at',
        'post' => 'authors_updated_at',
        'get' => 'authors_updated_at',
      ],
    ],
  ],
];
