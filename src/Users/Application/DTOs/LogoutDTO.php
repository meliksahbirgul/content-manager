<?php

declare(strict_types=1);

namespace Source\Users\Application\DTOs;

readonly class LogoutDTO
{
    public function __construct(
        private string $accessToken,
        private ?string $refreshToken = null,
    ) {}

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function refreshToken(): ?string
    {
        return $this->refreshToken;
    }
}
