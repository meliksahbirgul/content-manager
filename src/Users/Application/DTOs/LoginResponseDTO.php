<?php

declare(strict_types=1);

namespace Source\Users\Application\DTOs;

use JsonSerializable;

class LoginResponseDTO implements JsonSerializable
{
    public function __construct(
        private string $email,
        private string $name,
        private string $token,
        private string $refreshToken,
        private int $expireTime,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'token' => $this->token,
            'refreshToken' => $this->refreshToken,
            'expire' => $this->expireTime,
        ];
    }
}
