<?php

declare(strict_types=1);

namespace Source\Users\Infrastructure\Persistence;

use DateTimeImmutable;
use Source\Users\Domain\Entity\UserEntity;
use Source\Users\Domain\Entity\UserTokenEntity;
use Source\Users\Domain\Models\User as EloquentUser;
use Source\Users\Domain\Repository\Repository;

use function config;

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
        $user = EloquentUser::where('email', $email)->first();
        if (! $user) {
            return null;
        }

        $now = new DateTimeImmutable();

        $accessSeconds   = config('sanctum.expiration');
        $accessExpiresAt = $now->modify("+{$accessSeconds} seconds");
        $accessToken     = $user->createToken(
            name: 'access-token',
            abilities: ['access-panel'],
            expiresAt: $accessExpiresAt
        );

        $refreshSeconds  = config('sanctum.rt_expiration');
        $refreshExpireAt = $now->modify("+{$refreshSeconds} seconds");
        $refreshToken    = $user->createToken(
            name: 'refresh-token',
            abilities: ['issue-access-token'],
            expiresAt: $refreshExpireAt,
        );

        return new UserTokenEntity(
            accessToken: $accessToken->plainTextToken,
            refreshToken: $refreshToken->plainTextToken,
            expiresAt: (int) $accessSeconds,
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
