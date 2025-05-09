<?php
return  [
  'articles' =>
  [
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
    'title' =>
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
      'unique' =>
      [
        'val' =>
        [
          'articles' => 'title',
        ],
        'err' => NULL,
      ],
      'default' => 'test',
      'max' =>
      [
        'val' => 255,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 1,
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
    'published' =>
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
        'val' => 1,
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
        'val' => 1,
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
    'updated_at' =>
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
