<?php

declare(strict_types=1);

namespace Source\Users\Domain\ValueObjects;

use InvalidArgumentException;
use Source\Users\Application\DTOs\LoginDTO;

use function strlen;

use const FILTER_VALIDATE_EMAIL;

class LoginUser
{
    public function __construct(
        private string $email,
        private string $password,
    ) {
        $this->validate();
    }

    public static function createFromDTO(LoginDTO $dto): self
    {
        return new self(
            email: $dto->email(),
            password: $dto->password(),
        );
    }

    private function validate(): void
    {
        if (empty($this->email()) || empty($this->password())) {
            throw new InvalidArgumentException('Email or password can not be empty.');
        }

        if (! filter_var($this->email(), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        if (strlen($this->password()) < 8) {
            throw new InvalidArgumentException('Invalid password');
        }
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
