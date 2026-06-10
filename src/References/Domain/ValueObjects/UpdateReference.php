<?php

declare(strict_types=1);

namespace Source\References\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

readonly class UpdateReference
{
    public function __construct(
        private string $id,
        private ?string $name = null,
        private ?int $order = null,
    ) {
        if (! Uuid::isValid($this->id)) {
            throw new InvalidArgumentException('Invalid UUID format for id.');
        }
    }

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        if (! array_key_exists('id', $data)) {
            throw new InvalidArgumentException('The "id" field is required.');
        }

        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            order: $data['order'] ?? null,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function order(): ?int
    {
        return $this->order;
    }
}
