# DevOps Guidelines for Web Skeleton App

## 1. Dependency Injection Configuration (`config/di.php`)

### Current State
The current `config/di.php` configures the application for development:
- Twig cache is disabled (`'cache' => false`)
- Logs are written to `logs/app.log` with a StreamHandler at DEBUG level
- Dependencies are wired using League\Container

### Production Recommendations
#### Twig Configuration
For production, enable Twig caching and configure a proper cache directory:
```php
$container->addShared(Twig\Environment::class, function () use ($container) {
    $loader = $container->get(Twig\Loader\FilesystemLoader::class);
    return new Twig\Environment($loader, [
        'cache' => '/var/www/app/cache/twig', // Ensure this directory is writable by the web server
        'auto_reload' => false,
        'debug' => false,
    ]);
});
```

#### Environment-Based Configuration
Consider using environment variables to switch between environments:
```php
$env = getenv('APP_ENV') ?: 'production';
$twigCache = ($env === 'development') ? false : '/var/www/app/cache/twig';
```

#### Logging Adjustments
For production:
- Adjust log level to `Logger::INFO` or `Logger::WARNING`
- Consider using a rotating file handler or syslog
- Ensure log rotation is configured to prevent disk space issues

#### Security Considerations
- Disable debug modes in all components
- Ensure error messages do not leak sensitive information
- Consider adding a security middleware for headers (HSTS, CSP, etc.)

## 2. ETag Implementation

### Current State
The application does not currently implement ETag headers for HTTP caching.

### Why ETags Matter
ETags enable efficient cache validation, reducing bandwidth by allowing clients to reuse cached resources when unchanged.

### Implementation Recommendation
Add a middleware that generates and validates ETag headers:

```php
// src/Middleware/EtagMiddleware.php
namespace PrototypeIn\App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EtagMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        // Skip ETag for non-cacheable responses (e.g., cookies, auth)
        if ($this->shouldSkipEtag($response)) {
            return $response;
        }
        
        $body = (string) $response->getBody();
        $etag = '"' . hash('sha256', $body) . '"';
        
        // Set ETag header
        $response = $response->withHeader('ETag', $etag);
        
        // Handle If-None-Match request header
        if ($request->getHeaderLine('If-None-Match') === $etag) {
            return $response->withStatus(304)->withBody('');
        }
        
        return $response;
    }
    
    private function shouldSkipEtag(ResponseInterface $response): bool
    {
        // Skip if response has Set-Cookie or specific Cache-Control directives
        $cacheControl = $response->getHeaderLine('Cache-Control');
        return (bool) $response->getHeaderLine('Set-Cookie') 
            || str_contains(strtolower($cacheControl), 'private')
            || str_contains(strtolower($cacheControl), 'no-store');
    }
}
```

Register the middleware in `config/di.php`:
```php
// Add after other middleware definitions
$container->addShared(\PrototypeIn\App\Middleware\EtagMiddleware::class);
```

Then ensure it's applied in your application kernel (typically in `public/index.php`):
```php
$middleware = [
    // ... other middleware
    \PrototypeIn\App\Middleware\EtagMiddleware::class,
];
```

## 3. Health Endpoint (`/health`)

### Current State
No health endpoint is defined in the application.

### Why a Health Endpoint Matters
Health checks are essential for:
- Orchestration platforms (Kubernetes, Docker Swarm)
- Load balancers to determine service availability
- Monitoring systems to detect outages
- Zero-downtime deployments

### Implementation Recommendation

#### 1. Create a Health Check Action
```php
// src/Action/HealthCheckAction.php
namespace PrototypeIn\App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;

class HealthCheckAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Perform health checks (database, dependencies, etc.)
        $status = $this->checkHealth();
        
        $response = new Response();
        $response->getBody()->write(json_encode([
            'status' => $status['overall'] ? 'pass' : 'fail',
            'timestamp' => time(),
            'checks' => $status['checks'],
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status['overall'] ? 200 : 503);
    }
    
    private function checkHealth(): array
    {
        $checks = [];
        $overall = true;
        
        // Example: Database connection check
        try {
            // $db = $this->dbConnection; // Inject via constructor
            // $db->executeQuery('SELECT 1');
            $checks['database'] = ['status' => 'pass'];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'fail', 'message' => $e->getMessage()];
            $overall = false;
        }
        
        // Add more checks as needed (cache, external services, disk space, etc.)
        
        return ['overall' => $overall, 'checks' => $checks];
    }
}
```

#### 2. Add Route Configuration
Update `config/routes.php`:
```php
return [
    ['GET', '/', [LandingPageAction::class, 'handle']],
    ['GET', '/home', [HomePageAction::class, 'handle']],
    ['GET', '/health', [HealthCheckAction::class, 'handle']], // Health endpoint
];
```

#### 3. Production Considerations
- Ensure health checks are lightweight to avoid overloading dependencies
- Consider caching expensive checks for a short period
- Document which dependencies are checked in your deployment documentation
- For Kubernetes, configure liveness and readiness probes to use this endpoint

## Additional Production Considerations

### Error Handling
- Ensure custom error pages are configured (404, 500)
- Monitor error logs for unexpected exceptions
- Consider integrating with error tracking services (Sentry, Bugsnag)

### Performance Monitoring
- Add response time headers or integrate with APM tools
- Monitor slow database queries
- Consider implementing rate limiting for public endpoints

### Security Headers
Add a middleware to set security headers:
```php
// Example: SecurityHeadersMiddleware
$response = $response
    ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
    ->withHeader('X-Content-Type-Options', 'nosniff')
    ->withHeader('X-Frame-Options', 'DENY')
    ->withHeader('X-XSS-Protection', '1; mode=block')
    ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
```

### Deployment Checklist
1. Set `APP_ENV=production`
2. Verify Twig cache directory permissions
3. Check log rotation is configured
4. Test health endpoint returns 200
5. Validate security headers are present
6. Confirm error pages don't leak stack traces
7. Run `composer install --no-dev --optimize-autoloader`
8. Warm up caches if applicable
