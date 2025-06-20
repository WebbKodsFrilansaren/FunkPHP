<?php
return  [
  'tables' =>
  [
    'articles' =>
    [
      'id' =>
      [
        'joined_name' => 'articles_id',
        'auto_increment' => true,
        'type' => 'BIGINT',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'author_id' =>
      [
        'joined_name' => 'articles_author_id',
        'type' => 'INT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
        'foreign_key' => true,
        'references' => 'authors',
        'references_column' => 'id',
        'referenced_joined' => 'authors_id',
      ],
      'title' =>
      [
        'joined_name' => 'articles_title',
        'type' => 'VARCHAR',
        'value' => 255,
        'nullable' => false,
        'unique' => true,
        'unsigned' => false,
        'signed' => false,
        'default' => 'test',
      ],
      'content' =>
      [
        'joined_name' => 'articles_content',
        'type' => 'TEXT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'published' =>
      [
        'joined_name' => 'articles_published',
        'type' => 'BOOLEAN',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'created_at' =>
      [
        'joined_name' => 'articles_created_at',
        'type' => 'TIMESTAMP',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
      'updated_at' =>
      [
        'joined_name' => 'articles_updated_at',
        'type' => 'TIMESTAMP',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
    ],
    'comments' =>
    [
      'id' =>
      [
        'joined_name' => 'comments_id',
        'auto_increment' => true,
        'type' => 'BIGINT',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'test_number_that_is_unsigned' =>
      [
        'joined_name' => 'comments_test_number_that_is_unsigned',
        'type' => 'INT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => true,
        'signed' => false,
        'default' => NULL,
      ],
      'test_number_that_is_signed' =>
      [
        'joined_name' => 'comments_test_number_that_is_signed',
        'type' => 'INT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => true,
        'default' => NULL,
      ],
      'article_id' =>
      [
        'joined_name' => 'comments_article_id',
        'type' => 'INT',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'foreign_key' => true,
        'references' => 'articles',
        'references_column' => 'id',
        'referenced_joined' => 'articles_id',
      ],
      'content' =>
      [
        'joined_name' => 'comments_content',
        'type' => 'TEXT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => '',
      ],
      'author_id' =>
      [
        'joined_name' => 'comments_author_id',
        'type' => 'INT',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
        'foreign_key' => true,
        'references' => 'authors',
        'references_column' => 'id',
        'referenced_joined' => 'authors_id',
      ],
      'comment_status' =>
      [
        'joined_name' => 'comments_comment_status',
        'type' => 'SET',
        'value' =>
        [
          0 => 'approved',
          1 => 'pending',
          2 => 'spam',
        ],
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'pending',
      ],
      'comment_type' =>
      [
        'joined_name' => 'comments_comment_type',
        'type' => 'ENUM',
        'value' =>
        [
          0 => 'text',
          1 => 'image',
          2 => 'video',
        ],
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'text',
      ],
      'created_at' =>
      [
        'joined_name' => 'comments_created_at',
        'type' => 'TIMESTAMP',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
    ],
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
        'default' => "YAS!",
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
    'articles' =>
    [],
    'comments' =>
    [],
    'authors' =>
    [],
  ],
  'mappings' =>
  [
    'articles' =>
    [],
    'comments' =>
    [],
    'authors' =>
    [],
  ],
];
