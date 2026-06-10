<?php

declare(strict_types=1);

namespace Source\References\Domain\Entity;

use Source\Media\Domain\Entity\MediaEntity;

class ReferenceEntity
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
}
