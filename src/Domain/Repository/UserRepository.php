<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Repository;

use PrototypeIn\App\Domain\Model\User;
use Ramsey\Uuid\Uuid;

class UserRepository
{
    private \Doctrine\ORM\EntityManagerInterface $em;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findById(Uuid $id): ?User
    {
        return $this->em->find(User::class, $id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function save(User $user): void
    {
        $this->em->wrapInTransaction(function () use ($user) {
            $this->em->persist($user);
        });
    }

    public function delete(User $user): void
    {
        $this->em->wrapInTransaction(function () use ($user) {
            $this->em->remove($user);
        });
    }
}