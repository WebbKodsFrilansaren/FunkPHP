<?php
return  [
  'GET' => 
   [
    'BEFORE_MATCH' => 
     [
    ],
    '/' => 
     [
    ],
    'users' => 
     [
      ':' => 
       [
        'id' => 
         [
          'test' => 
           [
            '|' => 
             [
            ],
          ],
          '|' => 
           [
          ],
        ],
      ],
      '|' => 
       [
      ],
    ],
    'about' => 
     [
      'test' => 
       [
      ],
      '|' => 
       [
      ],
    ],
  ],
  'POST' => 
   [
    '/' => 
     [
    ],
    'users' => 
     [
      ':' => 
       [
        'id' => 
         [
          '|' => 
           [
          ],
        ],
      ],
      '|' => 
       [
      ],
    ],
    '|' => 
     [
    ],
  ],
  'PUT' => 
   [
    'users' => 
     [
      ':' => 
       [
        'id' => 
         [
          '|' => 
           [
          ],
        ],
      ],
    ],
  ],
  'DELETE' => 
   [
    'users' => 
     [
      ':' => 
       [
        'id' => 
         [
          '|' => 
           [
          ],
        ],
      ],
      '|' => 
       [
      ],
    ],
  ],
];