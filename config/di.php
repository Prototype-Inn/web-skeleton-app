<?php

declare(strict_types=1);

use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Laminas\Diactoros\ResponseFactory;
use PrototypeIn\Abac\Factories\AbacServiceFactory;
use PrototypeIn\App\Responder\HtmlResponder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bramus\Monolog\Formatter\ColoredLineFormatter;

$container = new Container();

// Register the reflection container as a delegate.
// This allows the container to automatically resolve dependencies for classes
// that are not explicitly defined in the container.
$container->delegate(new ReflectionContainer());

// Define Twig services
$container->addShared(Twig\Loader\FilesystemLoader::class, function () {
    return new Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/src/View');
});

$container->addShared(Twig\Environment::class, function () use ($container) {
    $loader = $container->get(Twig\Loader\FilesystemLoader::class);
    return new Twig\Environment($loader, [
        'cache' => false, // Disable cache for development
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

// Define Application Responders
$container->addShared(PrototypeIn\App\Responder\HtmlResponder::class, function () use ($container) {
    return new PrototypeIn\App\Responder\HtmlResponder(
        $container->get(Twig\Environment::class)
    );
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
$container->addShared(Logger::class, function () {
    $logPath = dirname(__DIR__) . '/var/logs/app.log';
    if (!is_dir(dirname($logPath))) {
        mkdir(dirname($logPath), 0755, true);
    }
    $logger = new Logger('app');
    $handler = new StreamHandler($logPath, Logger::DEBUG);
    $formatter = new ColoredLineFormatter(null, '[%datetime%] %channel%.%level_name%: %message% %context% %extra%', 'Y-m-d H:i:s');
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);
    return $logger;
});

// Define configuration for the DI container.
// This will be expanded as we implement more components.

return $container;
