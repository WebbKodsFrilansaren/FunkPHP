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
    ],
    'email' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
      'unique' => 
       [
        'error' => NULL,
        'table' => 
         [
          'authors' => 'email',
        ],
      ],
    ],
    'age' => 
     [
      'integer' => 
       [
        'error' => NULL,
      ],
    ],
    'nickname' => 
     [
      'string' => 
       [
        'error' => NULL,
      ],
    ],
    'updated_at' => 
     [
      'time' => 
       [
        'error' => NULL,
      ],
    ],
  ],
];;
