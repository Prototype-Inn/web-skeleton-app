<?php

declare(strict_types=1);

use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Laminas\Diactoros\ResponseFactory;
use Prototype\Stool\Drivers\RequestLogger\RequestLogger;
use Prototype\Stool\Middleware\AdrLoggerMiddleware;
use PrototypeIn\Comet\Http\Middleware\FormSubmissionLogger;
use Prototype\Stool\Interface\RequestLoggerInterface;
use PrototypeIn\Abac\Factories\AbacServiceFactory;
use PrototypeIn\App\Responder\HtmlResponder;
use PrototypeIn\App\Form\RegisterForm;
use PrototypeIn\App\Form\DemoForm;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Psr\Log\LoggerInterface;
use PrototypeIn\UrnRouter\Contracts\UrnRouterInterface;
use PrototypeIn\App\Service\UrnRouter;
use League\Fractal\Manager;
use PrototypeIn\App\Service\ConfigService;
use League\Config\ConfigurationBuilder; // Added
use League\Config\Configuration; // Added
use Nette\Schema\Schema; // Added

$appEnv = getenv('APP_MODE') ?: 'development';

$configData = require dirname(__DIR__) . '/config/config.php';
$dbSchema = require dirname(__DIR__) . '/config/schema/database.php';
$monologSchema = require dirname(__DIR__) . '/config/schema/monolog.php';

$configBuilder = new ConfigurationBuilder($configData);
$configBuilder->addSchema($dbSchema);
$configBuilder->addSchema($monologSchema);
$appConfig = $configBuilder->build(); // Build the Configuration object

$configService = new ConfigService($appConfig); // Pass the built Configuration object

$dbDriver = $configService->get('db.driver');

$container = new Container();

// Register the reflection container as a delegate.
// This allows the container to automatically resolve dependencies for classes
// that are not explicitly defined in the container.
$container->delegate(new ReflectionContainer());

// Define Twig services
$container->addShared(Twig\Loader\FilesystemLoader::class, function () {
    return new Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/src/View');
});

$container->addShared(Twig\Environment::class, function () use ($container, $appEnv) {
    $loader = $container->get(Twig\Loader\FilesystemLoader::class);
    return new Twig\Environment($loader, [
        'cache' => ($appEnv === 'development') ? false : dirname(__DIR__) . '/var/cache/twig',
        'auto_reload' => ($appEnv === 'development'),
        'debug' => ($appEnv === 'development'),
    ]);
});

// Define League\Route services
$container->addShared(ResponseFactoryInterface::class, ResponseFactory::class);
$container->addShared(ApplicationStrategy::class, function () use ($container) {
    $strategy = new ApplicationStrategy();
    $strategy->setContainer($container);
    return $strategy;
});
$container->addShared(Router::class, function () use ($container) {
    $router = new Router();
    $router->setStrategy($container->get(ApplicationStrategy::class));
    return $router;
});

// Define URN Router service
$container->addShared(UrnRouterInterface::class, function () use ($container) {
    return new PrototypeIn\App\Service\UrnRouter($container);
});

// Define Application Responders
$container->addShared(PrototypeIn\App\Responder\HtmlResponder::class, function () use ($container) {
    return new PrototypeIn\App\Responder\HtmlResponder(
        $container->get(Twig\Environment::class)
    );
});

// Define League\Fractal Manager
$container->addShared(Manager::class, function () {
    return new Manager();
});

// Define ABAC Configuration
$container->addShared('abac.config', function () {
    // Load application-specific ABAC configuration
    $appConfigPath = dirname(__DIR__) . '/config/abac.php';
    if (file_exists($appConfigPath)) {
        return require $appConfigPath;
    }
    // Fallback to vendor default if application config doesn't exist (e.g., during initial setup)
    return require dirname(__DIR__) . '/vendor/prototype-in/abac/config/abac.php';
});

// Define ABAC service
$container->addShared(PrototypeIn\Abac\Services\AbacService::class, function () use ($container) {
    $config = $container->get('abac.config');
    $logger = $container->get(Logger::class);
    return AbacServiceFactory::createFromConfigArray($config, $logger);
});

// Define Monolog service
$container->addShared(Logger::class, function () use ($configService, $appEnv) {
    $logPath = $configService->get('logger.path');
    $logLevel = $configService->get('logger.level');
    $logName = $configService->get('logger.name');

    if (!is_dir(dirname($logPath))) {
        mkdir(dirname($logPath), 0755, true);
    }
    
    $logger = new Logger($logName);
    $handler = new StreamHandler($logPath, $logLevel);
    
    if ($appEnv === 'development') {
        $formatter = new ColoredLineFormatter(null, '[%datetime%] %channel%.%level_name%: %message% %context% %extra%', 'Y-m-d H:i:s');
    } else {
        $formatter = new \Monolog\Formatter\LineFormatter('[%datetime%] %channel%.%level_name%: %message% %context% %extra%');
    }
    
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);
    return $logger;
});

// Define Stool Request Logger (shared instance)
$container->addShared(RequestLoggerInterface::class, function () use ($container) {
    return new RequestLogger($container->get(Logger::class));
});

// Define Stool ADR Logger Middleware
$container->addShared(AdrLoggerMiddleware::class, function () use ($container) {
    return new AdrLoggerMiddleware(
        $container->get(RequestLoggerInterface::class),
        fn($request) => $request->getAttribute('action_name', 'unknown')
    );
});

// Define Comet Form Submission Logger Middleware
$container->addShared(FormSubmissionLogger::class, function () use ($container) {
    return new FormSubmissionLogger($container->get(Logger::class));
});

// Unified Logger Bridge - makes app's Monolog available as PSR LoggerInterface
// This ensures oryx/orm MvcServiceProvider uses the same logger instance
class UnifiedLoggerServiceProvider extends \League\Container\ServiceProvider\AbstractServiceProvider
{
    protected array $provides = [\Psr\Log\LoggerInterface::class, \PrototypeIn\App\Event\ORMEventListener::class];

    public function register(): void
    {
        $container = $this->getContainer();
        
        // Bridge Monolog to PSR LoggerInterface for oryx/orm
        $container->addShared(\Psr\Log\LoggerInterface::class, function () use ($container) {
            return $container->get(Logger::class);
        });
        
        // Register ORMEventListener with app's logger
        $container->addShared(\PrototypeIn\App\Event\ORMEventListener::class, function () use ($container) {
            return new \PrototypeIn\App\Event\ORMEventListener($container->get(Logger::class));
        });
    }

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }
}

$container->addServiceProvider(new UnifiedLoggerServiceProvider());

// Doctrine ORM Configuration
$container->addShared(\Doctrine\ORM\EntityManagerInterface::class, function () use ($dbDriver, $appEnv) {
    // Register UUID type for ramsey/uuid-doctrine
    if (!\Doctrine\DBAL\Types\Type::hasType('uuid')) {
        \Doctrine\DBAL\Types\Type::addType('uuid', \Ramsey\Uuid\Doctrine\UuidType::class);
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

    $config = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfig(
        paths: $paths,
        isDevMode: $isDevMode
    );

    $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

    return new \Doctrine\ORM\EntityManager($connection, $config);
});

// Register Domain services
$container->addShared(PrototypeIn\App\Domain\Service\PasswordService::class);
$container->addShared(PrototypeIn\App\Domain\Repository\UserRepository::class, function () use ($container) {
    return new PrototypeIn\App\Domain\Repository\UserRepository(
        $container->get(\Doctrine\ORM\EntityManagerInterface::class)
    );
});

// Register Forms
$container->addShared(RegisterForm::class);
$container->addShared(DemoForm::class);

// Register Pipeline
$container->addShared(PrototypeIn\App\Pipeline\RequestProcessingPipeline::class);

// Define configuration for the DI container.

return $container;
