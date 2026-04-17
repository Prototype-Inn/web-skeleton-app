<?php

declare(strict_types=1);

namespace PrototypeIn\App\ViewModel;

class DemoViewModel
{
    public array $data = [];
    public array $errors = [];
    public bool $isValid = false;

    public function __construct(array $data = [], array $errors = [])
    {
        $this->data = $data;
        $this->errors = $errors;
        $this->isValid = empty($errors);
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        $this->isValid = empty($errors);
        return $this;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'errors' => $this->errors,
            'isValid' => $this->isValid,
        ];
    }
}