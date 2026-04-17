<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\DTO;

interface RoleDTOInterface
{
    public function getId(): ?int;
    public function getName(): string;
    public function getDescription(): ?string;
    public function getRoleType(): string;
    public function getPermissions(): array;
    public function getCreatedAt(): \DateTimeImmutable;
    public function getUpdatedAt(): \DateTimeImmutable;
}