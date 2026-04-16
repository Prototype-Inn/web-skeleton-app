<?php

declare(strict_types=1);

namespace PrototypeIn\App\ViewModel;

class LandingPageViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly string $subtitle,
        public readonly string $ctaText,
        public readonly string $ctaLink
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'ctaText' => $this->ctaText,
            'ctaLink' => $this->ctaLink,
        ];
    }
}
