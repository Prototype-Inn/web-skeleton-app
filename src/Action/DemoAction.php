<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use PrototypeIn\App\Domain\Repository\UserRepository;
use PrototypeIn\App\Domain\Service\PasswordService;
use PrototypeIn\App\Form\DemoForm;
use PrototypeIn\App\ViewModel\DemoViewModel;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class DemoAction
{
    private UserRepository $userRepository;
    private PasswordService $passwordService;
    private DemoForm $form;

    public function __construct(
        UserRepository $userRepository,
        PasswordService $passwordService,
        DemoForm $form
    ) {
        $this->userRepository = $userRepository;
        $this->passwordService = $passwordService;
        $this->form = $form;
    }

    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        return new JsonResponse([
            'form' => [
                'action' => '/demo',
                'method' => 'POST',
                'fields' => [
                    'email' => ['type' => 'email', 'label' => 'Email', 'required' => true],
                    'password' => ['type' => 'password', 'label' => 'Password', 'required' => true],
                    'firstName' => ['type' => 'text', 'label' => 'First Name', 'required' => false],
                    'lastName' => ['type' => 'text', 'label' => 'Last Name', 'required' => false],
                ],
            ],
        ]);
    }

    public function submit(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $body = (string) $request->getBody();
        $data = json_decode($body, true) ?? [];

        $this->form->setData($data);
        
        $formData = $this->form->getData();

        if (!$this->form->isValid()) {
            $messages = $this->form->getMessages();
            $errors = [];

            foreach ($messages as $field => $errorMessage) {
                $errors[$field] = is_array($errorMessage) ? array_values($errorMessage)[0] ?? 'Invalid value' : $errorMessage;
            }

            $viewModel = new DemoViewModel($formData, $errors);

            return new JsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'viewModel' => $viewModel->toArray(),
            ], 422);
        }

        $formData = $this->form->getData();
        $email = $formData['email'] ?? '';

        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser !== null) {
            $viewModel = new DemoViewModel($formData, ['email' => 'Email already registered']);
            return new JsonResponse([
                'success' => false,
                'message' => 'Email already registered',
                'viewModel' => $viewModel->toArray(),
            ], 409);
        }

        $user = new \PrototypeIn\App\Domain\Model\User();
        $user->setEmail($email);
        $user->setPasswordHash($this->passwordService->hash($formData['password']));
        $user->setFirstName($formData['firstName'] ?? null);
        $user->setLastName($formData['lastName'] ?? null);

        $this->userRepository->save($user);

        $viewModel = new DemoViewModel($formData, []);

        return new JsonResponse([
            'success' => true,
            'message' => 'Form submitted successfully',
            'viewModel' => $viewModel->toArray(),
        ], 201);
    }
}