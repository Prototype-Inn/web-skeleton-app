<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class GuestRole extends RoleBase
{
    public function getRoleType(): string
    {
        return 'guest';
    }

    public function getPermissions(): array
    {
        return [
            'public.read',
        ];
    }
}