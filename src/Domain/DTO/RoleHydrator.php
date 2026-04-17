<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\DTO;

use PrototypeIn\App\Domain\Model\RoleBase;

class RoleHydrator
{
    public function fromEntity(RoleBase $role): RoleDTOInterface
    {
        return new RoleDTO(
            $role->getId(),
            $role->getName(),
            $role->getDescription(),
            $role->getRoleType(),
            $role->getPermissions(),
            $role->getCreatedAt(),
            $role->getUpdatedAt(),
        );
    }

    /**
     * @param iterable<RoleBase> $roles
     * @return array<RoleDTOInterface>
     */
    public function fromEntities(iterable $roles): array
    {
        $dtos = [];
        foreach ($roles as $role) {
            $dtos[] = $this->fromEntity($role);
        }
        return $dtos;
    }
}