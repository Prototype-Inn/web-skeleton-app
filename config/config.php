<?php

declare(strict_types=1);

use Monolog\Logger;

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
    'logger' => [
        'name' => 'app',
        'path' => dirname(__DIR__, 2) . '/logs/app.log', // Default to development path
        'level' => Logger::DEBUG, // Default to DEBUG for development
    ],
];

