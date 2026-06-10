<?php

declare(strict_types=1);

namespace Source\References\Application\DTOs;

readonly class CreateReferenceDTO
{
    public function __construct(
        private string $name,
        private int $order,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            order: (int) ($data['order'] ?? 0),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'order' => $this->order,
        ];
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
