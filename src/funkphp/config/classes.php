<?php // "classes.php stores classes from 'composer' and also your own custom classes if needed.
/* SYNTAX For How EACH Class Would Get Its Associative Array:
    ['composer' =>
        ['className' =>
            ['config' =>
                ['args' => [<array>],
                'fqcn' => <string>],
                'instances' => [<array of instances>]]
            ],
    'custom' => [
        ['className' =>
            ['config' =>
                ['args' => [<array>],
                'fqcn' => <string>],
                'instances' => [<array of instances>]]
            ],
    ],
*/
return [
    'composer' => [],
    'custom' => [],
];
