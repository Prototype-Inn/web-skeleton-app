<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use PrototypeIn\App\Pipeline\RequestProcessingPipeline;
use PrototypeIn\App\Pipeline\PipelinePayload;
use PrototypeIn\App\Domain\Repository\UserRepository;
use PrototypeIn\App\Domain\Service\PasswordService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Log\LoggerInterface;

class PipelineDemoAction
{
    private RequestProcessingPipeline $pipeline;
    private UserRepository $userRepository;
    private PasswordService $passwordService;
    private LoggerInterface $logger;

    public function __construct(
        RequestProcessingPipeline $pipeline,
        UserRepository $userRepository,
        PasswordService $passwordService,
        LoggerInterface $logger
    ) {
        $this->pipeline = $pipeline;
        $this->userRepository = $userRepository;
        $this->passwordService = $passwordService;
        $this->logger = $logger;
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->logger->info('PipelineDemoAction started', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
        ]);

        $finalHandler = function (PipelinePayload $payload): ResponseInterface {
            if (!$payload->isValid) {
                $this->logger->warning('Pipeline validation failed', [
                    'errors' => $payload->errors,
                ]);

                return new JsonResponse([
                    'success' => false,
                    'stage' => 'validation',
                    'message' => 'Validation failed',
                    'pipeline' => [
                        'data' => $payload->data,
                        'errors' => $payload->errors,
                        'isValid' => $payload->isValid,
                        'authorized' => $payload->authorized,
                    ],
                ], 422);
            }

            if (!$payload->authorized) {
                $this->logger->warning('Pipeline authorization failed', []);

                return new JsonResponse([
                    'success' => false,
                    'stage' => 'authorization',
                    'message' => 'Not authorized',
                ], 403);
            }

            $email = $payload->data['email'] ?? '';
            $password = $payload->data['password'] ?? '';
            $firstName = $payload->data['firstName'] ?? null;
            $lastName = $payload->data['lastName'] ?? null;

            $existingUser = $this->userRepository->findByEmail($email);
            if ($existingUser !== null) {
                $this->logger->warning('Pipeline: email already exists', ['email' => $email]);

                return new JsonResponse([
                    'success' => false,
                    'stage' => 'processing',
                    'message' => 'Email already registered',
                    'pipeline' => [
                        'data' => $payload->data,
                        'errors' => ['email' => 'Email already registered'],
                    ],
                ], 409);
            }

            $user = new \PrototypeIn\App\Domain\Model\User();
            $user->setEmail($email);
            $user->setPasswordHash($this->passwordService->hash($password));
            $user->setFirstName($firstName);
            $user->setLastName($lastName);

            $this->userRepository->save($user);

            $this->logger->info('Pipeline: user created', ['userId' => $user->getId()->toString()]);

            return new JsonResponse([
                'success' => true,
                'stage' => 'processing',
                'message' => 'User created successfully',
                'pipeline' => [
                    'data' => $payload->data,
                    'errors' => [],
                    'isValid' => $payload->isValid,
                    'authorized' => $payload->authorized,
                ],
                'user' => [
                    'id' => $user->getId()->toString(),
                    'email' => $user->getEmail(),
                ],
            ], 201);
        };

        return $this->pipeline->process($request, $finalHandler);
    }
}