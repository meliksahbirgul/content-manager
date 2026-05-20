<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Entity;

class PermissionEntity
{
    public function __construct(
        private string $uuid,
        private string $name,
        private string $displayName,
        private string|null $description,
    ) {}

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function description(): string|null
    {
        return $this->description;
    }
}
