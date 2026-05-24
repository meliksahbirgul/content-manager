<?php

declare(strict_types=1);

namespace Source\Pages\Application\DTOs;

use JsonSerializable;
use Source\Media\Application\DTOs\MediaResponseDTO;
use Source\Pages\Domain\Entity\PageEntity;

readonly class PageEditResponseDTO implements JsonSerializable
{
    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $content
     * @param  array<string, string>  $slug
     * @param  list<MediaResponseDTO>  $images
     */
    public function __construct(
        private string $id,
        private array $title,
        private array $content,
        private array $slug,
        private string $status,
        private int $order,
        private ?string $parentId = null,
        private array $images = [],
    ) {}

    public static function fromEntity(PageEntity $entity): self
    {
        return new self(
            id: $entity->id(),
            title: $entity->title(),
            content: $entity->content(),
            slug: $entity->slug(),
            status: $entity->status()->value,
            parentId: $entity->parentId(),
            order: $entity->order(),
            images: array_map(
                fn ($image) => MediaResponseDTO::fromEntity($image),
                $entity->images()
            ),
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

    /** @return array<string, string> */
    public function content(): array
    {
        return $this->content;
    }

    /** @return array<string, string> */
    public function slug(): array
    {
        return $this->slug;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function parentId(): ?string
    {
        return $this->parentId;
    }

    /** @return list<MediaResponseDTO> */
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
            'slug' => $this->slug(),
            'content' => $this->content(),
            'status' => $this->status(),
            'order' => $this->order(),
            'parentId' => $this->parentId(),
            'images' => $this->images(),
        ];
    }
}
