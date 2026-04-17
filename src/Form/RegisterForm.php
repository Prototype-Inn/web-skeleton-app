<?php

declare(strict_types=1);

namespace PrototypeIn\App\Form;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StringToLower;
use Laminas\Form\Form;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Password;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;

class RegisterForm extends Form implements InputFilterProviderInterface
{
    public function __construct($name = 'register', $options = [])
    {
        parent::__construct($name, $options);
        $this->init();
    }

    public function init(): void
    {
        $this->add([
            'name' => 'email',
            'type' => Email::class,
            'options' => [
                'label' => 'Email',
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => Password::class,
            'options' => [
                'label' => 'Password',
            ],
        ]);

        $this->add([
            'name' => 'firstName',
            'type' => Text::class,
            'options' => [
                'label' => 'First Name',
            ],
        ]);

        $this->add([
            'name' => 'lastName',
            'type' => Text::class,
            'options' => [
                'label' => 'Last Name',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name' => 'email',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => \Laminas\Filter\StripNewlines::class],
                    ['name' => StringToLower::class],
                ],
                'validators' => [
                    [
                        'name' => EmailAddress::class,
                        'break_chain_on_failure' => true,
                        'messages' => [
                            EmailAddress::INVALID => 'Invalid email format',
                            EmailAddress::INVALID_FORMAT => 'Invalid email format',
                            EmailAddress::INVALID_HOSTNAME => 'Invalid email hostname',
                            EmailAddress::INVALID_LOCAL_PART => 'Invalid email local part',
                            EmailAddress::INVALID_MX_RECORD => 'Invalid email hostname',
                            EmailAddress::INVALID_SEGMENT => 'Invalid email hostname',
                            EmailAddress::LENGTH_EXCEEDED => 'Email is too long',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'password',
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 8,
                            'messages' => [
                                StringLength::TOO_SHORT => 'Password must be at least 8 characters',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            [
                'name' => 'firstName',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            [
                'name' => 'lastName',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
        ];
    }
}