<?php

declare(strict_types=1);

namespace Source\Users\Domain\Entity;

class UserEntity
{
    public function __construct(
        private string $uuid,
        private string $name,
        private string $email,
        private string $password,
    ) {}

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }
}
