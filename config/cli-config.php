<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use PrototypeIn\App\Service\ConfigService;
use Nette\Schema\Schema as NetteSchema;

$appEnv = getenv('APP_MODE') ?: 'production';

$configData = require dirname(__DIR__) . '/config/config.php';
$dbSchema = require dirname(__DIR__) . '/config/schema/database.php';
$configService = new ConfigService($configData, $dbSchema);

$dbDriver = $configService->get('db.driver');

if (!Type::hasType('uuid')) {
    Type::addType('uuid', \Ramsey\Uuid\Doctrine\UuidType::class);
}

$paths = [dirname(__DIR__) . '/src/Domain/Model'];
$isDevMode = ($appEnv === 'development');

if ($dbDriver === 'pdo_sqlite') {
    $dbPath = $configService->get('db.path') ?: dirname(__DIR__) . '/var/data/database.sqlite';
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
        'host' => $configService->get('db.host'),
        'port' => $configService->get('db.port'),
        'dbname' => $configService->get('db.name'),
        'user' => $configService->get('db.user'),
        'password' => $configService->get('db.pass'),
        'charset' => 'utf8mb4',
    ];
}

$config = ORMSetup::createAttributeMetadataConfig($paths, $isDevMode);

$connection = DriverManager::getConnection($connectionParams, $config);

$entityManager = new EntityManager($connection, $config);

$provider = new SingleManagerProvider($entityManager);

ConsoleRunner::run($provider);