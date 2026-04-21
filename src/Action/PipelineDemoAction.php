<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use PrototypeIn\App\Pipeline\RequestProcessingPipeline;
use PrototypeIn\App\Pipeline\PipelinePayload;
use PrototypeIn\App\Domain\Repository\UserRepository;
use PrototypeIn\App\Domain\Repository\RoleRepository;
use PrototypeIn\App\Domain\Service\PasswordService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Log\LoggerInterface;

class PipelineDemoAction
{
    private RequestProcessingPipeline $pipeline;
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private PasswordService $passwordService;
    private LoggerInterface $logger;

    public function __construct(
        RequestProcessingPipeline $pipeline,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        PasswordService $passwordService,
        LoggerInterface $logger
    ) {
        $this->pipeline = $pipeline;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->passwordService = $passwordService;
        $this->logger = $logger;
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $requestId = (string) $request->getAttribute('request_id', 'n/a');

        $this->logger->info('PipelineDemoAction started', [
            'event' => 'pipeline.action.start',
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
        ]);

        $finalHandler = function (PipelinePayload $payload): ResponseInterface {
            if (!$payload->isValid) {
                $this->logger->warning('Pipeline validation failed', [
                    'event' => 'pipeline.validation.failed',
                    'request_id' => $requestId,
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
                $this->logger->warning('Pipeline authorization failed', [
                    'event' => 'pipeline.authorization.failed',
                    'request_id' => $requestId,
                ]);

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
                $this->logger->warning('Pipeline: email already exists', [
                    'event' => 'pipeline.email.conflict',
                    'request_id' => $requestId,
                    'email' => $email,
                ]);

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

            $defaultRole = $this->roleRepository->findByType('user');
            if ($defaultRole !== null) {
                $user->addRole($defaultRole);
            }

            $this->userRepository->save($user);

            $this->logger->info('Pipeline: user created', [
                'event' => 'pipeline.user.created',
                'request_id' => $requestId,
                'user_id' => $user->getId()->toString(),
            ]);

            $roles = array_map(fn($role) => $role->getRoleType(), $user->getRoles());

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
                    'roles' => $roles,
                ],
            ], 201);
        };

        return $this->pipeline->process($request, $finalHandler);
    }
}
