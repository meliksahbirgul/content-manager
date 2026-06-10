<?php

declare(strict_types=1);

namespace Source\References\Application\DTOs;

readonly class UpdateReferenceDTO
{
    public function __construct(
        private string $id,
        private ?string $name = null,
        private ?int $order = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            order: isset($data['order']) ? (int) $data['order'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'order' => $this->order,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }
}
