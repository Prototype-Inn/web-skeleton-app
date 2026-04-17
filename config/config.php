<?php

declare(strict_types=1);

return [
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'pdo_sqlite',
        'path' => getenv('DB_PATH') ?: dirname(__DIR__, 2) . '/var/data/database.sqlite',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'app',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
    ],
];
