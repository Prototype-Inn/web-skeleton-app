<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Form;

use PrototypeIn\App\Form\RegisterForm;
use PrototypeIn\App\Tests\Support\MockeryTestCase;

class RegisterFormTest extends MockeryTestCase
{
    private RegisterForm $form;

    protected function setUp(): void
    {
        $this->form = new RegisterForm();
    }

    public function testFormIsValidWithCorrectData(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];

        $this->form->setData($data);
        $this->assertTrue($this->form->isValid());
    }

    public function testFormIsInvalidWithEmptyEmail(): void
    {
        $data = [
            'email' => '',
            'password' => 'password123',
        ];

        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());

        $messages = $this->form->getMessages();
        $this->assertArrayHasKey('email', $messages);
    }

    public function testFormIsInvalidWithInvalidEmail(): void
    {
        $data = [
            'email' => 'not-an-email',
            'password' => 'password123',
        ];

        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());

        $messages = $this->form->getMessages();
        $this->assertArrayHasKey('email', $messages);
    }

    public function testFormIsInvalidWithEmptyPassword(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());

        $messages = $this->form->getMessages();
        $this->assertArrayHasKey('password', $messages);
    }

    public function testFormIsInvalidWithShortPassword(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'short',
        ];

        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());

        $messages = $this->form->getMessages();
        $this->assertArrayHasKey('password', $messages);
    }

    public function testFormReturnsDataAfterValidation(): void
    {
        $data = [
            'email' => 'Test@Example.com',
            'password' => 'password123',
            'firstName' => '  John  ',
            'lastName' => '  Doe  ',
        ];

        $this->form->setData($data);
        $this->form->isValid();

        $formData = $this->form->getData();
        $this->assertEquals('test@example.com', $formData['email']);
        $this->assertEquals('password123', $formData['password']);
    }
}
