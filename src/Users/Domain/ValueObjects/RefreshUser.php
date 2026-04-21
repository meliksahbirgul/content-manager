<?php

declare(strict_types=1);

namespace Source\Users\Domain\ValueObjects;

use InvalidArgumentException;

class RefreshUser
{
    public function __construct(
        private string $token,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->token())) {
            throw new InvalidArgumentException('Token can not be empty.');
        }
    }

    public function token(): string
    {
        return $this->token;
    }
}
