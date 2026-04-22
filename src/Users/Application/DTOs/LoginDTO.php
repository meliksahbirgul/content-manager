<?php

declare(strict_types=1);

namespace Source\Users\Application\DTOs;

use InvalidArgumentException;

use function array_key_exists;

readonly class LoginDTO
{
    public function __construct(private string $email, private string $password) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        if (! array_key_exists('email', $data) || ! array_key_exists('password', $data)) {
            throw new InvalidArgumentException('Parameters are missing.');
        }

        return new self(
            email: $data['email'],
            password: $data['password'],
        );
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
