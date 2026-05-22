<?php

declare(strict_types=1);

namespace Source\Languages\Domain\Entity;

class LanguageEntity
{
    public function __construct(
        private string $uuid,
        private string $name,
        private string $code,
        private string $status,
    ) {}

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function status(): string
    {
        return $this->status;
    }
}
