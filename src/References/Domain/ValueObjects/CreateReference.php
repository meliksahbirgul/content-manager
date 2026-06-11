<?php

declare(strict_types=1);

namespace Source\References\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class CreateReference
{
    public function __construct(
        private string $id,
        private string $name,
        private int $order,
    ) {
        $this->validate();
    }

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? Uuid::uuid7()->toString(),
            name: $data['name'],
            order: $data['order'] ?? 0,
        );
    }

    private function validate(): void
    {
        if (! Uuid::isValid($this->id)) {
            throw new InvalidArgumentException('Invalid UUID format for id.');
        }

        if (empty($this->name)) {
            throw new InvalidArgumentException('Name cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function order(): int
    {
        return $this->order;
    }
}
