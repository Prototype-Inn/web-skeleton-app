<?php

declare(strict_types=1);

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequestFactory;
use League\Container\Container;
use League\Route\Router;
use PrototypeIn\App\Responder\HtmlResponder;
use League\Route\Http\Exception\NotFoundException;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Monolog\Logger;
use Prototype\Stool\Middleware\AdrLoggerMiddleware;
use PrototypeIn\Comet\Http\Middleware\FormSubmissionLogger;

// Include Composer's autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Create a per-request correlation id as early as possible
$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? bin2hex(random_bytes(8));
putenv('APP_REQUEST_ID=' . $requestId);

// Set up error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set up Whoops error handler (development only)
if (ini_get('display_errors')) {
    $whoops = new \Whoops\Run();
    $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

// Instantiate the DI container
/** @var Container $container */
$container = require dirname(__DIR__) . '/config/di.php';

/** @var Logger $logger */
$logger = $container->get(Logger::class);

// Load routes
$routes = require dirname(__DIR__) . '/config/routes.php';

// Create a request
$request = ServerRequestFactory::fromGlobals()->withAttribute('request_id', $requestId);

$logger->debug('Request received', [
    'event' => 'http.request.received',
    'request_id' => $requestId,
    'method' => $request->getMethod(),
    'uri' => (string)$request->getUri(),
]);

// Initialize the router
$router = $container->get(Router::class);

// Add global middleware (in order: first to last)
$router->middleware($container->get(AdrLoggerMiddleware::class));
$router->middleware($container->get(FormSubmissionLogger::class));

// Add routes to the router
foreach ($routes as $route) {
    [$method, $path, $handler] = $route;
    $router->map($method, $path, $handler);
}

// Dispatch the request
try {
    $response = $router->dispatch($request);
    $logger->debug('Response sent', [
        'event' => 'http.response.sent',
        'request_id' => $requestId,
        'status' => $response->getStatusCode(),
    ]);
} catch (NotFoundException $e) {
    $logger->warning('Route not found', [
        'event' => 'http.route.not_found',
        'request_id' => $requestId,
        'uri' => (string)$request->getUri(),
        'method' => $request->getMethod(),
    ]);
    $responder = $container->get(HtmlResponder::class);
    $response = $responder->setPayload(['title' => 'Not Found', 'message' => 'Page Not Found'])->setStatusCode(404)();
} catch (Throwable $e) {
    $logger->error('Request failed', [
        'event' => 'http.request.failed',
        'request_id' => $requestId,
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    $content = '<h1>An Error Occurred!</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    $response = new HtmlResponse($content, 500);
}

$response = $response->withHeader('X-Request-Id', $requestId);

// Emit the response
$emitter = new SapiEmitter();
$emitter->emit($response);
