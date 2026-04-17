<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserRole extends RoleBase
{
    public function getRoleType(): string
    {
        return 'user';
    }

    public function getPermissions(): array
    {
        return [
            'profile.read',
            'profile.write',
            'profile.delete',
        ];
    }
}