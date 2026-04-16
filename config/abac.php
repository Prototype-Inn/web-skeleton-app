<?php

declare(strict_types=1);

return [
    'role_provider' => [
        'guest' => ['permissions' => ['landingpage.view', 'homepage.view']],
        'admin' => ['permissions' => ['landingpage.view', 'homepage.view']],
        'user' => ['permissions' => ['landingpage.view', 'homepage.view']],
    ],
    'hierarchy' => [
        'admin' => ['user'],
    ],
    'matrix' => [
        'user::landingpage' => [
            'actions' => ['view'],
        ],
        'guest::landingpage' => [
            'actions' => ['view'],
        ],
        'user::homepage' => [
            'actions' => ['view'],
        ],
        'guest::homepage' => [
            'actions' => ['view'],
        ],
    ],
];
