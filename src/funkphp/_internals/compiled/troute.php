<?php
return  [
  'GET' => 
   [
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
    '|' => 
     [
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