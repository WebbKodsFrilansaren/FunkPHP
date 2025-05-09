<?php
return  [
  'authors' => 
   [
    'name' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'required' => 
       [
        'error' => NULL,
      ],
      'max' => 
       [
        'value' => 255,
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 1,
        'error' => NULL,
      ],
    ],
    'email' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'unique' => 
       [
        'authors' => 'email',
        'error' => NULL,
      ],
      'max' => 
       [
        'value' => 21845,
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 0,
        'error' => NULL,
      ],
    ],
    'description' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'required' => 
       [
        'error' => NULL,
      ],
      'default' => 'No description',
      'max' => 
       [
        'value' => 255,
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 1,
        'error' => NULL,
      ],
    ],
    'longer_description' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'required' => 
       [
        'error' => NULL,
      ],
      'default' => 'No longer description',
      'max' => 
       [
        'value' => 65535,
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 1,
        'error' => NULL,
      ],
    ],
    'age' => 
     [
      'integer' => 
       [
        'error' => NULL,
      ],
      'default' => 18,
    ],
    'enum_test' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'default' => 'c',
      'max' => 
       [
        'value' => 
         [
          0 => 'a test',
          1 => 'b or more',
          2 => 'c what i did',
        ],
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 0,
        'error' => NULL,
      ],
    ],
    'set_test' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'default' => 'a,b',
      'max' => 
       [
        'value' => 
         [
          0 => 'every',
          1 => 'unique',
          2 => 'carrot or morot',
        ],
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 0,
        'error' => NULL,
      ],
    ],
    'weight' => 
     [
      'float' => 
       [
        'error' => NULL,
      ],
      'required' => 
       [
        'error' => NULL,
      ],
      'default' => 70,
    ],
    'nickname' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'default' => 'Anonymous',
      'max' => 
       [
        'value' => 255,
        'error' => NULL,
      ],
      'min' => 
       [
        'value' => 0,
        'error' => NULL,
      ],
    ],
    'updated_at' => 
     [
      'time' => 
       [
        'error' => NULL,
      ],
      'default' => 'NOW()',
    ],
  ],
];;
