<?php

declare(strict_types=1);

namespace Source\Roles\Domain\ValueObjects;

use InvalidArgumentException;

class AssignPermission
{
    public function __construct(
        private string $userUuid,
        private string $permissionUuid,
        private bool $granted = true,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->userUuid)) {
            throw new InvalidArgumentException('User UUID cannot be empty.');
        }

        if (empty($this->permissionUuid)) {
            throw new InvalidArgumentException('Permission UUID cannot be empty.');
        }
    }

    public function userUuid(): string
    {
        return $this->userUuid;
    }

    public function permissionUuid(): string
    {
        return $this->permissionUuid;
    }

    public function granted(): bool
    {
        return $this->granted;
    }
}
