<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Service;

class PasswordService
{
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}