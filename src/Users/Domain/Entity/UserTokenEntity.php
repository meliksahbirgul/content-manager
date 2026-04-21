<?php

declare(strict_types=1);

namespace Source\Users\Domain\Entity;

class UserTokenEntity
{
    public function __construct(
        private string $accessToken,
        private string $refreshToken,
        private int $expiresAt,
    ) {}

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }

    public function expiresAt(): int
    {
        return $this->expiresAt;
    }
}
