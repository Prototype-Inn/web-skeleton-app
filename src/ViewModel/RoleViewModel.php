<?php

declare(strict_types=1);

namespace PrototypeIn\App\ViewModel;

use PrototypeIn\App\Domain\DTO\RoleDTOInterface;

class RoleViewModel
{
    private ?int $id;
    private string $name;
    private ?string $description;
    private string $roleType;
    private array $permissions;
    private bool $canEdit;
    private bool $canDelete;

    public function __construct(RoleDTOInterface $roleDto)
    {
        $this->id = $roleDto->getId();
        $this->name = $roleDto->getName();
        $this->description = $roleDto->getDescription();
        $this->roleType = $roleDto->getRoleType();
        $this->permissions = $roleDto->getPermissions();
        $this->canEdit = false;
        $this->canDelete = false;
    }

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

    public function canEdit(): bool
    {
        return $this->canEdit;
    }

    public function canDelete(): bool
    {
        return $this->canDelete;
    }

    public function setCanEdit(bool $canEdit): self
    {
        $this->canEdit = $canEdit;
        return $this;
    }

    public function setCanDelete(bool $canDelete): self
    {
        $this->canDelete = $canDelete;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'roleType' => $this->roleType,
            'permissions' => $this->permissions,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];
    }
}