<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AdminRole extends RoleBase
{
    public function getRoleType(): string
    {
        return 'admin';
    }

    public function getPermissions(): array
    {
        return [
            'user.read',
            'user.write',
            'user.delete',
            'role.read',
            'role.write',
            'role.delete',
            'settings.read',
            'settings.write',
            'admin.access',
        ];
    }
}