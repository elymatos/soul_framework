<?php

return [
    'db' => env('DB_CONNECTION', 'fnbr'),
    'lang' => 1,
    'language' => 'pt',
    'defaultIdLanguage' => 1,
    'defaultPassword' => 'default',
    'pageTitle' => env('APP_TITLE'),
    'mainTitle' => 'SOUL Framework 0.1',
    'headerTitle' => 'SOUL',
    'footer' => '&copy; 2014-2025 FNBr/UFJF',
    'version' => '0.1',
    'mediaURL' => env('APP_MEDIA_URL'),
    'login' => [
        'handler' => env('APP_AUTH'),
        'AUTH0_CLIENT_ID' => env('AUTH0_CLIENT_ID'),
        'AUTH0_CLIENT_SECRET' => env('AUTH0_CLIENT_SECRET'),
        'AUTH0_COOKIE_SECRET' => env('AUTH0_COOKIE_SECRET'),
        'AUTH0_DOMAIN' => env('AUTH0_DOMAIN'),
        'AUTH0_CALLBACK_URL' => env('AUTH0_CALLBACK_URL'),
        'AUTH0_BASE_URL' => env('AUTH0_BASE_URL'),
    ],
    'actions' => [
        'concept' => ['Concepts', '/concept', '', []],
        'graph_editor' => ['Graph Editor', '/graph-editor', '', []],
        //        'dashboard' => ['Dashboard', '/dashboard', '', []],
        //        'grapher' => ['Grapher', '/grapher', '', []],
        //        'annotation' => ['Annotation', '/annotation', 'MASTER', []],
        //        'structure' => ['Structure', '/structure', 'MASTER', []],
        //        'manager' => ['Manager', '/manager', 'MANAGER', []],
        //        'admin' => ['Admin', '/admin', 'ADMIN', []],
    ],
    'user' => ['userPanel', '/admin/user/main', '', [
        'language' => ['Language', '/language', '', [
            '2' => ['English', '/changeLanguage/en', '', []],
            '1' => ['Portuguese', '/changeLanguage/pt', '', []],
            '3' => ['Spanish', '/changeLanguage/es', '', []],
        ]],
        'profile' => ['Profile', '/profile', '', [
            'myprofile' => ['My Profile', '/profile', '', []],
            'logout' => ['Logout', '/logout', '', []],
        ]],
    ]],
    'relations' => [
        'rel_inheritance' => [
            'direct' => 'Is inherited by',
            'inverse' => 'Inherits from',
            'color' => '#FF0000',
        ],
        'rel_subframe' => [
            'direct' => 'Has as subframe',
            'inverse' => 'Is subframe of',
            'color' => '#0000FF',
        ],
        'rel_perspective_on' => [
            'direct' => 'Is perspectivized in',
            'inverse' => 'Perspective on',
            'color' => '#fdbeca',
        ],
        'rel_using' => [
            'direct' => 'Is used by',
            'inverse' => 'Uses',
            'color' => '#006301',
        ],
        'rel_precedes' => [
            'direct' => 'Precedes',
            'inverse' => 'Is preceded by',
            'color' => '#000000',
        ],
        'rel_causative_of' => [
            'direct' => 'Is causative of',
            'inverse' => 'Has as causative',
            'color' => '#fdd101',
        ],
        'rel_inchoative_of' => [
            'direct' => 'Is inchoative of',
            'inverse' => 'Has as inchoative',
            'color' => '#897201',
        ],
        'rel_see_also' => [
            'direct' => 'See also',
            'inverse' => 'Has as see_also',
            'color' => '#9e1fee',
        ],
        'rel_inheritance_cxn' => [
            'direct' => 'Is inherited by',
            'inverse' => 'Inherits from',
            'color' => '#FF0000',
        ],
        'rel_daughter_of' => [
            'direct' => 'Is daughter of',
            'inverse' => 'Has as daughter',
            'color' => '#0000FF',
        ],
        'rel_subtypeof' => [
            'direct' => 'Is subtype of',
            'inverse' => 'Has as subtype',
            'color' => '#9e1fee',
        ],
        'rel_standsfor' => [
            'direct' => 'Stands for',
            'inverse' => 'Has as stands_for',
            'color' => '#9e1fee',
        ],
        'rel_partwhole' => [
            'direct' => 'Part of',
            'inverse' => 'Has as part',
            'color' => '#9e1fee',
        ],
        'rel_hasconcept' => [
            'direct' => 'Has concept',
            'inverse' => 'Is concept of',
            'color' => '#9e1fee',
        ],
        'rel_coreset' => [
            'direct' => 'CoreSet',
            'inverse' => 'CoreSet',
            'color' => '##000',
        ],
        'rel_excludes' => [
            'direct' => 'Excludes',
            'inverse' => 'Excludes',
            'color' => '#000',
        ],
        'rel_requires' => [
            'direct' => 'Requires',
            'inverse' => 'Requires',
            'color' => '#000',
        ],
        'rel_structure' => [
            'direct' => 'Structure',
            'inverse' => 'Structured by',
            'color' => '#000',
        ],
    ],
    'fe' => [
        'icon' => [
            'cty_core' => 'black circle',
            'cty_core-unexpressed' => 'black dot circle',
            'cty_peripheral' => 'black stop circle outline',
            'cty_extra-thematic' => 'black circle outline',
        ],
        'coreness' => [
            'cty_core' => 'Core',
            'cty_core-unexpressed' => 'Core-Unexpressed',
            'cty_peripheral' => 'Peripheral',
            'cty_extra-thematic' => 'Extra-thematic',
        ],
    ],
];
