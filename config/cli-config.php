<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

$appEnv = getenv('APP_ENV') ?: 'production';
$dbDriver = getenv('DB_DRIVER') ?: 'pdo_sqlite';

if (!Type::hasType('uuid')) {
    Type::addType('uuid', \Ramsey\Uuid\Doctrine\UuidType::class);
}

$paths = [dirname(__DIR__) . '/src/Domain/Model'];
$isDevMode = ($appEnv === 'development');

if ($dbDriver === 'pdo_sqlite') {
    $dbPath = getenv('DB_PATH') ?: dirname(__DIR__) . '/var/data/database.sqlite';
    if (!is_dir(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
    }
    $connectionParams = [
        'driver' => 'pdo_sqlite',
        'path' => $dbPath,
    ];
} else {
    $connectionParams = [
        'driver' => 'pdo_mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'dbname' => getenv('DB_NAME') ?: 'app',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ];
}

$config = ORMSetup::createAttributeMetadataConfig($paths, $isDevMode);

$connection = DriverManager::getConnection($connectionParams, $config);

$entityManager = new EntityManager($connection, $config);

$provider = new SingleManagerProvider($entityManager);

ConsoleRunner::run($provider);