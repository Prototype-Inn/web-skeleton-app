<?php

declare(strict_types=1);

use League\Config\Schema\Schema;
use Nette\Schema\Expect;
use Monolog\Logger;

return new Schema([
    'logger' => Expect::structure([
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
    ]),
]);
