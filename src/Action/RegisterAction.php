<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use PrototypeIn\App\Domain\Repository\UserRepository;
use PrototypeIn\App\Domain\Repository\RoleRepository;
use PrototypeIn\App\Domain\Service\PasswordService;
use PrototypeIn\App\Form\RegisterForm;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class RegisterAction
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private PasswordService $passwordService;
    private RegisterForm $form;

    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        PasswordService $passwordService,
        RegisterForm $form
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->passwordService = $passwordService;
        $this->form = $form;
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();

        $this->form->setData($data);

        if (!$this->form->isValid()) {
            $messages = $this->form->getMessages();
            $errors = [];

            foreach ($messages as $field => $fieldErrors) {
                $errors[$field] = array_values($fieldErrors)[0] ?? 'Invalid value';
            }

            return new JsonResponse(['success' => false, 'errors' => $errors], 400);
        }

        $formData = $this->form->getData();
        $email = $formData['email'] ?? '';
        $password = $formData['password'] ?? null;
        $firstName = $formData['firstName'] ?? null;
        $lastName = $formData['lastName'] ?? null;

        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser !== null) {
            return new JsonResponse(['success' => false, 'errors' => ['email' => 'Email already registered']], 409);
        }

        $user = new \PrototypeIn\App\Domain\Model\User();
        $user->setEmail($email);
        $user->setPasswordHash($this->passwordService->hash($password));
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $userRole = $this->roleRepository->findByType('user');
        if ($userRole === null) {
            throw new \RuntimeException('Role "user" not found in database. Run migrations.');
        }
        $user->addRole($userRole);

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