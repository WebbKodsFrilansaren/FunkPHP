<?php // PAGE_SINGLE_ROUTES.PHP - FunkPHP Framework | This File Was Modified In FunkCLI 2025-04-27 05:56:17
return [
  'ROUTES' =>
  [
    'GET' =>
    [
      '/users/:id' =>
      [
        'page' => 'page_file_name_to_template_return',
      ],
      '/users/:id/test' =>
      [
        'page' => 'page_file_name_to_template_return2',
      ],
      '/users' =>
      [
        'page' => 'users_p-1',
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
