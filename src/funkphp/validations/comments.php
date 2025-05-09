<?php
return  [
  'comments' =>
  [
    'test_number_that_is_unsigned' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'integer' =>
      [
        'err' => NULL,
      ],
      'required' =>
      [
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 4294967295,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 11,
        'err' => NULL,
      ],
    ],
    'test_number_that_is_signed' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'integer' =>
      [
        'err' => NULL,
      ],
      'required' =>
      [
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 2147483647,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => -2147483648,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 11,
        'err' => NULL,
      ],
    ],
    'article_id' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'integer' =>
      [
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 2147483647,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => -2147483648,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 11,
        'err' => NULL,
      ],
    ],
    'content' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'string' =>
      [
        'err' => NULL,
      ],
      'required' =>
      [
        'err' => NULL,
      ],
      'default' => '',
      'max' =>
      [
        'val' => 65535,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
    ],
    'author_id' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'integer' =>
      [
        'err' => NULL,
      ],
      'required' =>
      [
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 2147483647,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => -2147483648,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 11,
        'err' => NULL,
      ],
    ],
    'comment_status' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'string' =>
      [
        'err' => NULL,
      ],
      'default' => 'pending',
      'in_array' =>
      [
        'val' =>
        [
          0 => 'approved',
          1 => 'pending',
          2 => 'spam',
        ],
        'err' => NULL,
      ],
    ],
    'comment_type' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'string' =>
      [
        'err' => NULL,
      ],
      'default' => 'text',
      'in_array' =>
      [
        'val' =>
        [
          0 => 'text',
          1 => 'image',
          2 => 'video',
        ],
        'err' => NULL,
      ],
    ],
    'created_at' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'timestamp' =>
      [
        'err' => NULL,
      ],
      'default' => 'CURRENT_TIMESTAMP',
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 2147483647,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 10,
        'err' => NULL,
      ],
    ],
  ],
];
