<?php

declare(strict_types=1);

namespace PrototypeIn\App\Service;

use League\Container\Container;
use League\Route\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\UrnRouter\Contracts\UrnRouterInterface;
use PrototypeIn\UrnRouter\Urn\Generator;
use PrototypeIn\UrnRouter\Urn\Validator;
use League\Uri\Urn;

/**
 * URN Router implementation that integrates with the existing League\Route router.
 */
class UrnRouter implements UrnRouterInterface
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function generate(string $type, string $resource, string $id): string
    {
        return Generator::create($type, $resource, $id);
    }

    public function parse(string $urn): ?array
    {
        if (!Validator::isValid($urn)) {
            return null;
        }

        $uri = Urn::new($urn);
        $parts = explode(':', $uri->toString());

        // Expected format: urn:type:resource:id
        if (count($parts) < 4) {
            return null;
        }

        return [
            'type' => $parts[1],
            'resource' => $parts[2],
            'id' => $parts[3],
        ];
    }

    public function isValid(string $urn): bool
    {
        return Validator::isValid($urn);
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        // If there's a URN attribute set (e.g., by UrnHeaderMiddleware or an action),
        // we can use it to set additional request attributes for downstream use.
        $urn = $request->getAttribute('urn');
        if ($urn && $this->isValid($urn)) {
            $parsed = $this->parse($urn);
            if ($parsed) {
                $request = $request->withAttribute('urn_type', $parsed['type'])
                                   ->withAttribute('urn_resource', $parsed['resource'])
                                   ->withAttribute('urn_id', $parsed['id']);
            }
        }

        // Delegate to the main router for actual routing.
        $router = $this->container->get(Router::class);
        return $router->dispatch($request);
    }
}