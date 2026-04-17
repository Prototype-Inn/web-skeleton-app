<?php

declare(strict_types=1);

namespace PrototypeIn\App\Form;

class DemoForm
{
    private array $data = [];
    private array $errors = [];

    private const VALIDATION_RULES = [
        'email' => [
            'required' => true,
            'filters' => ['trim', 'strtolower'],
            'validators' => [
                ['name' => 'email', 'message' => 'Invalid email format'],
            ],
        ],
        'password' => [
            'required' => true,
            'validators' => [
                ['name' => 'minlength', 'options' => ['min' => 8], 'message' => 'Password must be at least 8 characters'],
            ],
        ],
        'firstName' => [
            'required' => false,
            'filters' => ['trim'],
        ],
        'lastName' => [
            'required' => false,
            'filters' => ['trim'],
        ],
    ];

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function isValid(): bool
    {
        $this->errors = [];
        $data = $this->data;

        foreach (self::VALIDATION_RULES as $field => $rules) {
            $value = $data[$field] ?? '';

            if (is_string($value) && isset($rules['filters'])) {
                foreach ($rules['filters'] as $filter) {
                    $value = match ($filter) {
                        'trim' => trim($value),
                        'strtolower' => strtolower($value),
                        default => $value,
                    };
                }
            }

            $data[$field] = $value;

            if ($rules['required'] && mb_strlen($value) === 0) {
                $this->errors[$field] = ucfirst($field) . ' is required';
                continue;
            }

            if (mb_strlen($value) > 0 && isset($rules['validators'])) {
                foreach ($rules['validators'] as $validator) {
                    $valid = match ($validator['name']) {
                        'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                        'minlength' => mb_strlen($value) >= ($validator['options']['min'] ?? 8),
                        default => true,
                    };

                    if (!$valid) {
                        $this->errors[$field] = $validator['message'];
                        break;
                    }
                }
            }
        }

        $this->data = $data;
        return empty($this->errors);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMessages(): array
    {
        return $this->errors;
    }
}