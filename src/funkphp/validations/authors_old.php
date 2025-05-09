<?php
return  [
  'authors' =>
  [
    'name' =>
    [
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
      'string' =>
      [
        'err' => NULL,
      ],
      'unique' =>
      [
        'authors' => 'email',
        'err' => NULL,
      ],
      'max' =>
      [
        'val' => 128,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
    ],
    'description' =>
    [
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
        'val' => 0,
        'err' => NULL,
      ],
    ],
    'longer_description' =>
    [
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
        'val' => 0,
        'err' => NULL,
      ],
    ],
    'age' =>
    [
      'integer' =>
      [
        'err' => NULL,
      ],
      'default' => 18,
      'max' =>
      [
        'val' => 3,
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
    ],
    'enum_test' =>
    [
      'string' =>
      [
        'err' => NULL,
      ],
      'default' => 'c',
      'max' =>
      [
        'val' =>
        [
          0 => 'a test',
          1 => 'b or more',
          2 => 'c what i did',
        ],
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
    ],
    'set_test' =>
    [
      'string' =>
      [
        'err' => NULL,
      ],
      'default' => 'a,b',
      'max' =>
      [
        'val' =>
        [
          0 => 'every',
          1 => 'unique',
          2 => 'carrot or morot',
        ],
        'err' => NULL,
      ],
      'min' =>
      [
        'val' => 0,
        'err' => NULL,
      ],
    ],
    'weight' =>
    [
      'float' =>
      [
        'err' => NULL,
      ],
      'required' =>
      [
        'err' => NULL,
      ],
      'default' => 70,
    ],
    'nickname' =>
    [
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
      'time' =>
      [
        'err' => NULL,
      ],
      'default' => 'NOW()',
    ],
  ],
];;
