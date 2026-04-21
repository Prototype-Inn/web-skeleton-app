<?php

declare(strict_types=1);

use Nette\Schema\Expect;
use Monolog\Logger;

return Expect::structure([
    'name' => Expect::string('app')->required(),
    'path' => Expect::string()->required(),
    'level' => Expect::anyOf(
        Logger::DEBUG,
        Logger::INFO,
        Logger::NOTICE,
        Logger::WARNING,
        Logger::ERROR,
        Logger::CRITICAL,
        Logger::ALERT,
        Logger::EMERGENCY
    )->default(Logger::DEBUG),
    'channels' => Expect::arrayOf('string')->default(['app', 'http', 'pipeline', 'security', 'orm']),
    'redact_keys' => Expect::listOf('string')->default([
        'password',
        'token',
        'authorization',
        'cookie',
        'set-cookie',
    ]),
    'json_in_production' => Expect::bool(true),
    'rotate_keep_days' => Expect::int(14),
]);
