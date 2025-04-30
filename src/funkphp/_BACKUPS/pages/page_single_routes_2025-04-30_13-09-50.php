<?php // PAGE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-29 17:17:14
 return [
  'ROUTES' => 
   [
    'GET' => 
     [
      '/test3' => 
       [
        'handler' => 't',
      ],
      '/test_all' => 
       [
        'handler' => 'testall',
      ],
      '/users' => 
       [
        'page' => 'users_p-1',
      ],
      '/users/:id' => 
       [
        'page' => 'page_file_name_to_template_return',
      ],
      '/users/:id/test' => 
       [
        'page' => 'page_file_name_to_template_return2',
      ],
    ],
    'POST' => 
     [
      '/users/:id' => 
       [
        'page' => 'page_file_name_to_template_return3',
      ],
      '/users' => 
       [
        'page' => 'page_file_name_to_template_return4',
      ],
      '/users2' => 
       [
        'page' => 'users_p',
      ],
    ],
    'PUT' => 
     [
      '/users/:id' => 
       [
        'page' => 'page_file_name_to_template_return4',
      ],
    ],
    'DELETE' => 
     [
      '/users/:id' => 
       [
        'page' => 'page_file_name_to_template_return4',
      ],
    ],
  ],
];