<?php

declare(strict_types=1);

namespace PrototypeIn\App\ViewModel;

class HomePageViewModel
{
    private string $title;
    private string $message;
    private string $name;

    public function __construct(string $title, string $message, string $name)
    {
        $this->title = $title;
        $this->message = $message;
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'name' => $this->name,
        ];
    }
}
