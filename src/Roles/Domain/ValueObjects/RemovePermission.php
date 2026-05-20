<?php

declare(strict_types=1);

namespace Source\Roles\Domain\ValueObjects;

use InvalidArgumentException;

class RemovePermission
{
    public function __construct(
        private string $userUuid,
        private string $permissionUuid,
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
}
