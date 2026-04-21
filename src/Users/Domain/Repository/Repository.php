<?php

declare(strict_types=1);

namespace Source\Users\Domain\Repository;

use Source\Users\Domain\Entity\UserEntity;
use Source\Users\Domain\Entity\UserTokenEntity;

interface Repository
{
    public function findByEmail(string $email): UserEntity|null;
    public function createTokenForUser(string $email): UserTokenEntity|null;
    public function findUserByRefreshToken(string $token): UserEntity|null;
    public function deleteToken(string $token): void;
}
