<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\DTO;

class RoleDTO implements RoleDTOInterface
{
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $description,
        private string $roleType,
        private array $permissions,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getRoleType(): string
    {
        return $this->roleType;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'],
            $data['description'] ?? null,
            $data['roleType'],
            $data['permissions'] ?? [],
            $data['createdAt'] ?? new \DateTimeImmutable(),
            $data['updatedAt'] ?? new \DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'roleType' => $this->roleType,
            'permissions' => $this->permissions,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
        ];
    }
}