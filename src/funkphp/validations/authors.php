<?php
return  [
  'authors' =>
  [
    'name' =>
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
        'val' => 255,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
    ],
    'email' =>
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
      'unique' =>
      [
        'val' =>
        [
          'authors' => 'email',
        ],
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 21845,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
      'email' =>
      [
        'val' => NULL,
        'err' => NULL,
      ],
    ],
    'blob_test' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'blob' =>
      [
        'err' => NULL,
      ],
      'default' => 'No blob',
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 65535,
        'err' => NULL,
      ],
    ],
    'description' =>
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
      'default' => 'No description',
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
    'longer_description' =>
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
      'default' => 'No longer description',
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
    'age' =>
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
      'default' => 18,
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
    'enum_test' =>
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
      'default' => 'c what i did',
      'in_array' =>
      [
        'val' =>
        [
          0 => 'a test',
          1 => 'b or more',
          2 => 'c what i did',
        ],
        'err' => NULL,
      ],
    ],
    'weight' =>
    [
      'MAP_TO' =>
      [
        'post' => '',
        'get' => '',
        'json' => '',
      ],
      'float' =>
      [
        'err' => NULL,
      ],
      'required' =>
      [
        'err' => NULL,
      ],
      'default' => 70,
      'max' =>
      [
        'val' => -1.175494351E-38,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => -3.402823466E+38,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 1,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 7,
        'err' => NULL,
      ],
    ],
    'nickname' =>
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
      'default' => 'Anonymous',
      'max' =>
      [
        'val' => 255,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
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
      'time' =>
      [
        'err' => NULL,
      ],
      'default' => 'NOW()',
      'min' =>
      [
        'val' => 8,
        'err' => NULL,
      ],
      'min_digits' =>
      [
        'val' => 6,
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 8,
        'err' => NULL,
      ],
      'max_digits' =>
      [
        'val' => 6,
        'err' => NULL,
      ],
    ],
  ],
];
