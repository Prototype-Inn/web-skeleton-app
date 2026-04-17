<?php

declare(strict_types=1);

use Nette\Schema\Expect;

return Expect::structure([
    'driver' => Expect::string('pdo_sqlite')->required(),
    'path' => Expect::string()->nullable(),
    'host' => Expect::string('localhost')->nullable(),
    'port' => Expect::int(3306)->nullable(),
    'name' => Expect::string()->nullable(),
    'user' => Expect::string()->nullable(),
    'pass' => Expect::string()->nullable(),
]);
