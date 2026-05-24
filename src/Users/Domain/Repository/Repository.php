<?php

declare(strict_types=1);

namespace Source\Users\Domain\Repository;

use Source\Users\Domain\Entity\UserEntity;
use Source\Users\Domain\Entity\UserTokenEntity;
use Source\Users\Domain\Models\User;

interface Repository
{
    public function findByEmail(string $email): ?UserEntity;

    public function createTokenForUser(string $email): ?UserTokenEntity;

    public function findUserByRefreshToken(string $token): ?UserEntity;

    public function deleteToken(string $token): void;

    public function getUserModelWithEmail(string $email): ?User;
}
