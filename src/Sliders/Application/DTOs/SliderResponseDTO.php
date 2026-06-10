<?php

declare(strict_types=1);

namespace Source\Sliders\Application\DTOs;

use JsonSerializable;
use Source\Media\Domain\Entity\MediaEntity;

readonly class SliderResponseDTO implements JsonSerializable
{
    /**
     * @param  array<string, string>  $title
     * @param  list<MediaEntity>  $images
     */
    public function __construct(
        private string $id,
        private array $title,
        private string $href,
        private int $order,
        private string $isActive,
        private array $images = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            href: $data['href'],
            order: $data['order'],
            isActive: $data['isActive'],
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string, string> */
    public function title(): array
    {
        return $this->title;
    }

    public function href(): string
    {
        return $this->href;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function isActive(): string
    {
        return $this->isActive;
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
            'id' => $this->id(),
            'title' => $this->title(),
            'href' => $this->href(),
            'order' => $this->order(),
            'isActive' => $this->isActive(),
            'images' => $this->images(),
        ];
    }
}
