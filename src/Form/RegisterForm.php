<?php

declare(strict_types=1);

namespace PrototypeIn\App\Form;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterForm
{
    private ?FormInterface $form = null;
    private FormFactoryInterface $formFactory;
    private ValidatorInterface $validator;
    private array $errors = [];

    public function __construct(?FormFactoryInterface $formFactory = null, ?ValidatorInterface $validator = null)
    {
        $this->formFactory = $formFactory ?? Forms::createFormFactory();
        $this->validator = $validator ?? Validation::createValidator();
    }

    public function setData(array $data): void
    {
        $form = $this->formFactory->createBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '********',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'John',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Doe',
                ],
            ])
            ->getForm();

        $form->submit($data);
        $this->form = $form;
        $this->errors = $this->validate($data);
    }

    private function validate(array $data): array
    {
        $errors = [];

        $emailConstraint = new NotBlank(message: 'Email is required');
        $emailFormatConstraint = new Email(message: 'Invalid email format', mode: Email::VALIDATION_MODE_HTML5);
        
        $emailViolations = $this->validator->validate($data['email'] ?? '', [$emailConstraint, $emailFormatConstraint]);
        if (count($emailViolations) > 0) {
            $errors['email'] = [];
            foreach ($emailViolations as $violation) {
                $errors['email'][] = $violation->getMessage();
            }
        }

        $passwordConstraint = new NotBlank(message: 'Password is required');
        $passwordLengthConstraint = new Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters');
        
        $passwordViolations = $this->validator->validate($data['password'] ?? '', [$passwordConstraint, $passwordLengthConstraint]);
        if (count($passwordViolations) > 0) {
            $errors['password'] = [];
            foreach ($passwordViolations as $violation) {
                $errors['password'][] = $violation->getMessage();
            }
        }

        return $errors;
    }

    public function isValid(): bool
    {
        if ($this->form === null) {
            return false;
        }
        return count($this->errors) === 0;
    }

    public function getData(): array
    {
        if ($this->form === null) {
            return [];
        }
        
        $data = [];
        foreach (['email', 'password', 'firstName', 'lastName'] as $field) {
            $data[$field] = $this->form->get($field)->getNormData();
        }
        
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email'] ?? ''));
        }
        if (isset($data['firstName'])) {
            $data['firstName'] = trim($data['firstName'] ?? '');
        }
        if (isset($data['lastName'])) {
            $data['lastName'] = trim($data['lastName'] ?? '');
        }
        
        return $data;
    }

    public function getMessages(): array
    {
        return $this->errors;
    }
}