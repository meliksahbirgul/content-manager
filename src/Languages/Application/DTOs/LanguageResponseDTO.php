<?php

declare(strict_types=1);

namespace Source\Languages\Application\DTOs;

use JsonSerializable;
use Source\Languages\Domain\Entity\LanguageEntity;

readonly class LanguageResponseDTO implements JsonSerializable
{
    public function __construct(
        private string $uuid,
        private string $name,
        private string $code,
        private string $status,
    ) {}

    public static function fromEntity(LanguageEntity $entity): self
    {
        return new self(
            uuid: $entity->uuid(),
            name: $entity->name(),
            code: $entity->code(),
            status: $entity->status(),
        );
    }

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

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid(),
            'name' => $this->name(),
            'code' => $this->code(),
            'status' => $this->status(),
        ];
    }
}
