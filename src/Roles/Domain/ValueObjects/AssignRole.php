<?php

declare(strict_types=1);

namespace Source\Roles\Domain\ValueObjects;

use InvalidArgumentException;

class AssignRole
{
    public function __construct(
        private string $userUuid,
        private string $roleUuid,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->userUuid)) {
            throw new InvalidArgumentException('User UUID cannot be empty.');
        }

        if (empty($this->roleUuid)) {
            throw new InvalidArgumentException('Role UUID cannot be empty.');
        }
    }

    public function userUuid(): string
    {
        return $this->userUuid;
    }

    public function roleUuid(): string
    {
        return $this->roleUuid;
    }
}
