<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Repository;

use PrototypeIn\App\Domain\Model\RoleBase;

class RoleRepository
{
    private \Doctrine\ORM\EntityManagerInterface $em;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findById(int $id): ?RoleBase
    {
        return $this->em->find(RoleBase::class, $id);
    }

    public function findByType(string $roleType): ?RoleBase
    {
        $class = match ($roleType) {
            'admin' => \PrototypeIn\App\Domain\Model\AdminRole::class,
            'user' => \PrototypeIn\App\Domain\Model\UserRole::class,
            default => null,
        };

        if ($class === null) {
            return null;
        }

        $results = $this->em->getRepository($class)->findAll();
        return $results[0] ?? null;
    }

    public function findAll(): array
    {
        return $this->em->getRepository(RoleBase::class)->findAll();
    }

    public function save(RoleBase $role): void
    {
        $this->em->wrapInTransaction(function () use ($role) {
            $this->em->persist($role);
        });
    }

    public function delete(RoleBase $role): void
    {
        $this->em->wrapInTransaction(function () use ($role) {
            $this->em->remove($role);
        });
    }
}