<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use PrototypeIn\App\Domain\Repository\UserRepository;
use PrototypeIn\App\Domain\Service\PasswordService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class RegisterAction
{
    private UserRepository $userRepository;
    private PasswordService $passwordService;

    public function __construct(UserRepository $userRepository, PasswordService $passwordService)
    {
        $this->userRepository = $userRepository;
        $this->passwordService = $passwordService;
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();

        $email = strtolower($data['email'] ?? '');
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;

        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (!empty($errors)) {
            return new JsonResponse(['success' => false, 'errors' => $errors], 400);
        }

        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser !== null) {
            return new JsonResponse(['success' => false, 'errors' => ['email' => 'Email already registered']], 409);
        }

        $user = new \PrototypeIn\App\Domain\Model\User();
        $user->setEmail($email);
        $user->setPasswordHash($this->passwordService->hash($password));
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $this->userRepository->save($user);

        return new JsonResponse([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId()->toString(),
                'email' => $user->getEmail(),
            ],
        ], 201);
    }
}