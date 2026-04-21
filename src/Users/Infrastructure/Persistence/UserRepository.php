<?php

declare(strict_types=1);

namespace Source\Users\Infrastructure\Persistence;

use Source\Users\Domain\Entity\UserEntity;
use Source\Users\Domain\Entity\UserTokenEntity;
use Source\Users\Domain\Models\User as EloquentUser;
use Source\Users\Domain\Repository\Repository;

class UserRepository implements Repository
{
    public function findByEmail(string $email): UserEntity|null
    {
        $user = EloquentUser::where('email', $email)->first();
        if (! $user) {
            return null;
        }

        return $this->mapToEntity($user);
    }

    public function createTokenForUser(string $email): UserTokenEntity|null
    {
        return new UserTokenEntity(
            accessToken: '',
            refreshToken: '',
            expiresAt: 60,
        );
    }

    private function mapToEntity(EloquentUser $user): UserEntity
    {
        return new UserEntity(
            name: $user->name,
            email: $user->email,
            password: $user->password,
        );
    }
}
