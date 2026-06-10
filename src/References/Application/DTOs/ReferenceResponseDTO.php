<?php

declare(strict_types=1);

namespace Source\References\Application\DTOs;

use JsonSerializable;
use Source\Media\Domain\Entity\MediaEntity;

readonly class ReferenceResponseDTO implements JsonSerializable
{
    /**
     * @param  list<MediaEntity>  $images
     */
    public function __construct(
        private string $id,
        private string $name,
        private int $order,
        private array $images = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            order: $data['order'],
        );
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

    /** @return list<MediaEntity> */
    public function images(): array
    {
        return $this->images;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'     => $this->id(),
            'name'   => $this->name(),
            'order'  => $this->order(),
            'images' => $this->images(),
        ];
    }
}
