<?php

declare(strict_types=1);

namespace Source\Users\Application\DTOs;

readonly class RefreshDTO
{
    public function __construct(private string $refreshToken) {}

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }
}
