<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use PrototypeIn\UrnRouter\Contracts\UrnRouterInterface;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

/**
 * Action to demonstrate URN Router and Fractal integration.
 */
class UrnDemoAction
{
    public function __construct(
        private UrnRouterInterface $urnRouter,
        private Manager $fractal
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Generate a URN for a resource
        $urn = $this->urnRouter->generate('resource', 'user', '123');

        // Validate the URN
        $isValid = $this->urnRouter->isValid($urn);

        // Parse the URN (if valid)
        $parsed = $isValid ? $this->urnRouter->parse($urn) : null;

        // Sample data to transform with Fractal
        $users = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];

        // Transform with Fractal
        $resource = new Collection($users, function ($user) {
            return [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ];
        });

        // Add the URN as meta information
        $resource->setMeta(['urn' => $urn]);

        $fractalData = $this->fractal->createData($resource)->toArray();

        // Prepare response data
        $responseData = [
            'urn_generated' => $urn,
            'urn_valid' => $isValid,
            'urn_parsed' => $parsed,
            'users' => $fractalData['data'] ?? [],
            'meta' => $fractalData['meta'] ?? [],
        ];

        return new JsonResponse($responseData);
    }
}