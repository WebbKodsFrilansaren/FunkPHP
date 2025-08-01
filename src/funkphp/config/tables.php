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
    'articles' =>
    [
      'id' =>
      [
        'joined_name' => 'articles_id',
        'auto_increment' => true,
        'type' => 'BIGINT',
        'binding' => 'i',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'author_id' =>
      [
        'joined_name' => 'articles_author_id',
        'type' => 'BIGINT',
        'binding' => 'i',
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
        'binding' => 's',
        'value' => 255,
        'nullable' => false,
        'unique' => true,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'content' =>
      [
        'joined_name' => 'articles_content',
        'type' => 'TEXT',
        'binding' => 's',
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
        'binding' => 'i',
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
        'binding' => 's',
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
        'binding' => 's',
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
        'binding' => 'i',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'article_id' =>
      [
        'joined_name' => 'comments_article_id',
        'type' => 'BIGINT',
        'binding' => 'i',
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
        'binding' => 's',
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
        'type' => 'BIGINT',
        'binding' => 'i',
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
      'created_at' =>
      [
        'joined_name' => 'comments_created_at',
        'type' => 'TIMESTAMP',
        'binding' => 's',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
    ],
    'alones' =>
    [
      'id' =>
      [
        'joined_name' => 'alones_id',
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
        'joined_name' => 'alones_name',
        'type' => 'VARCHAR',
        'binding' => 's',
        'value' => 255,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
      'description' =>
      [
        'joined_name' => 'alones_description',
        'type' => 'TEXT',
        'binding' => 's',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
      ],
      'created_at' =>
      [
        'joined_name' => 'alones_created_at',
        'type' => 'TIMESTAMP',
        'binding' => 's',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
      'updated_at' =>
      [
        'joined_name' => 'alones_updated_at',
        'type' => 'TIMESTAMP',
        'binding' => 's',
        'value' => NULL,
        'nullable' => true,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
    ],
    'tags' =>
    [
      'id' =>
      [
        'joined_name' => 'tags_id',
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
        'joined_name' => 'tags_name',
        'type' => 'VARCHAR',
        'binding' => 's',
        'value' => 255,
        'nullable' => false,
        'unique' => true,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
      ],
    ],
    'authors_tags' =>
    [
      'id' =>
      [
        'joined_name' => 'authors_tags_id',
        'auto_increment' => true,
        'type' => 'BIGINT',
        'binding' => 'i',
        'value' => NULL,
        'primary_key' => true,
        'nullable' => false,
        'default' => NULL,
      ],
      'author_id' =>
      [
        'joined_name' => 'authors_tags_author_id',
        'type' => 'BIGINT',
        'binding' => 'i',
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
      'tag_id' =>
      [
        'joined_name' => 'authors_tags_tag_id',
        'type' => 'BIGINT',
        'binding' => 'i',
        'value' => NULL,
        'nullable' => false,
        'unique' => false,
        'unsigned' => false,
        'signed' => false,
        'default' => NULL,
        'foreign_key' => true,
        'references' => 'tags',
        'references_column' => 'id',
        'referenced_joined' => 'tags_id',
      ],
    ],
  ],
  'relationships' =>
  [
    'authors' =>
    [
      'articles' =>
      [
        'local_column' => 'id',
        'foreign_column' => 'author_id',
        'local_table' => 'authors',
        'foreign_table' => 'articles',
        'direction' => 'pk_to_fk',
      ],
      'comments' =>
      [
        'local_column' => 'id',
        'foreign_column' => 'author_id',
        'local_table' => 'authors',
        'foreign_table' => 'comments',
        'direction' => 'pk_to_fk',
      ],
      'authors_tags' =>
      [
        'local_column' => 'id',
        'foreign_column' => 'author_id',
        'local_table' => 'authors',
        'foreign_table' => 'authors_tags',
        'direction' => 'pk_to_fk',
      ],
      'tags' =>
      [
        'direction' => 'm_to_m',
        'local_table' => 'authors',
        'foreign_table' => 'tags',
        'pivot_table' => 'authors_tags',
        'pivot_local_key' => 'author_id',
        'pivot_foreign_key' => 'tag_id',
      ],
    ],
    'articles' =>
    [
      'authors' =>
      [
        'local_column' => 'author_id',
        'foreign_column' => 'id',
        'local_table' => 'articles',
        'foreign_table' => 'authors',
        'direction' => 'fk_to_pk',
      ],
      'comments' =>
      [
        'local_column' => 'id',
        'foreign_column' => 'article_id',
        'local_table' => 'articles',
        'foreign_table' => 'comments',
        'direction' => 'pk_to_fk',
      ],
    ],
    'comments' =>
    [
      'articles' =>
      [
        'local_column' => 'article_id',
        'foreign_column' => 'id',
        'local_table' => 'comments',
        'foreign_table' => 'articles',
        'direction' => 'fk_to_pk',
      ],
      'authors' =>
      [
        'local_column' => 'author_id',
        'foreign_column' => 'id',
        'local_table' => 'comments',
        'foreign_table' => 'authors',
        'direction' => 'fk_to_pk',
      ],
    ],
    'alones' =>
    [],
    'tags' =>
    [
      'authors_tags' =>
      [
        'local_column' => 'id',
        'foreign_column' => 'tag_id',
        'local_table' => 'tags',
        'foreign_table' => 'authors_tags',
        'direction' => 'pk_to_fk',
      ],
      'authors' =>
      [
        'direction' => 'm_to_m',
        'local_table' => 'tags',
        'foreign_table' => 'authors',
        'pivot_table' => 'authors_tags',
        'pivot_local_key' => 'tag_id',
        'pivot_foreign_key' => 'author_id',
      ],
    ],
    'authors_tags' =>
    [
      'authors' =>
      [
        'local_column' => 'author_id',
        'foreign_column' => 'id',
        'local_table' => 'authors_tags',
        'foreign_table' => 'authors',
        'direction' => 'fk_to_pk',
      ],
      'tags' =>
      [
        'local_column' => 'tag_id',
        'foreign_column' => 'id',
        'local_table' => 'authors_tags',
        'foreign_table' => 'tags',
        'direction' => 'fk_to_pk',
      ],
    ],
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
    'articles' =>
    [
      'id' =>
      [
        'json' => 'articles_id',
        'post' => 'articles_id',
        'get' => 'articles_id',
      ],
      'author_id' =>
      [
        'json' => 'articles_author_id',
        'post' => 'articles_author_id',
        'get' => 'articles_author_id',
      ],
      'title' =>
      [
        'json' => 'articles_title',
        'post' => 'articles_title',
        'get' => 'articles_title',
      ],
      'content' =>
      [
        'json' => 'articles_content',
        'post' => 'articles_content',
        'get' => 'articles_content',
      ],
      'published' =>
      [
        'json' => 'articles_published',
        'post' => 'articles_published',
        'get' => 'articles_published',
      ],
      'created_at' =>
      [
        'json' => 'articles_created_at',
        'post' => 'articles_created_at',
        'get' => 'articles_created_at',
      ],
      'updated_at' =>
      [
        'json' => 'articles_updated_at',
        'post' => 'articles_updated_at',
        'get' => 'articles_updated_at',
      ],
    ],
    'comments' =>
    [
      'id' =>
      [
        'json' => 'comments_id',
        'post' => 'comments_id',
        'get' => 'comments_id',
      ],
      'article_id' =>
      [
        'json' => 'comments_article_id',
        'post' => 'comments_article_id',
        'get' => 'comments_article_id',
      ],
      'content' =>
      [
        'json' => 'comments_content',
        'post' => 'comments_content',
        'get' => 'comments_content',
      ],
      'author_id' =>
      [
        'json' => 'comments_author_id',
        'post' => 'comments_author_id',
        'get' => 'comments_author_id',
      ],
      'created_at' =>
      [
        'json' => 'comments_created_at',
        'post' => 'comments_created_at',
        'get' => 'comments_created_at',
      ],
    ],
    'alones' =>
    [
      'id' =>
      [
        'json' => 'alones_id',
        'post' => 'alones_id',
        'get' => 'alones_id',
      ],
      'name' =>
      [
        'json' => 'alones_name',
        'post' => 'alones_name',
        'get' => 'alones_name',
      ],
      'description' =>
      [
        'json' => 'alones_description',
        'post' => 'alones_description',
        'get' => 'alones_description',
      ],
      'created_at' =>
      [
        'json' => 'alones_created_at',
        'post' => 'alones_created_at',
        'get' => 'alones_created_at',
      ],
      'updated_at' =>
      [
        'json' => 'alones_updated_at',
        'post' => 'alones_updated_at',
        'get' => 'alones_updated_at',
      ],
    ],
    'tags' =>
    [
      'id' =>
      [
        'json' => 'tags_id',
        'post' => 'tags_id',
        'get' => 'tags_id',
      ],
      'name' =>
      [
        'json' => 'tags_name',
        'post' => 'tags_name',
        'get' => 'tags_name',
      ],
    ],
    'authors_tags' =>
    [
      'id' =>
      [
        'json' => 'authors_tags_id',
        'post' => 'authors_tags_id',
        'get' => 'authors_tags_id',
      ],
      'author_id' =>
      [
        'json' => 'authors_tags_author_id',
        'post' => 'authors_tags_author_id',
        'get' => 'authors_tags_author_id',
      ],
      'tag_id' =>
      [
        'json' => 'authors_tags_tag_id',
        'post' => 'authors_tags_tag_id',
        'get' => 'authors_tags_tag_id',
      ],
    ],
  ],
];
