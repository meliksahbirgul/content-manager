<?php

declare(strict_types=1);

namespace Source\Media\Application\DTOs;

use JsonSerializable;
use Source\Media\Domain\Entity\MediaEntity;

readonly class MediaResponseDTO implements JsonSerializable
{
    /**
     * @param  array<string, string>|null  $linkSlugs
     */
    public function __construct(
        private string $uuid,
        private string $url,
        private string $originalName,
        private string $mimeType,
        private int $size,
        private string $collection,
        private int $order,
        private ?string $altText = null,
        private ?string $linkPageUuid = null,
        private ?array $linkSlugs = null,
    ) {}

    public static function fromEntity(MediaEntity $entity): self
    {
        return new self(
            uuid: $entity->uuid(),
            url: $entity->url(),
            originalName: $entity->originalName(),
            mimeType: $entity->mimeType(),
            size: $entity->size(),
            collection: $entity->collection()->value,
            order: $entity->order(),
            altText: $entity->altText(),
            linkPageUuid: $entity->linkPageUuid(),
            linkSlugs: $entity->linkSlugs(),
        );
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function originalName(): string
    {
        return $this->originalName;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function collection(): string
    {
        return $this->collection;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function altText(): ?string
    {
        return $this->altText;
    }

    public function linkPageUuid(): ?string
    {
        return $this->linkPageUuid;
    }

    /** @return array<string, string>|null */
    public function linkSlugs(): ?array
    {
        return $this->linkSlugs;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->uuid(),
            'url' => $this->url(),
            'original_name' => $this->originalName(),
            'mime_type' => $this->mimeType(),
            'size' => $this->size(),
            'collection' => $this->collection(),
            'order' => $this->order(),
            'alt_text' => $this->altText(),
            'link_page_uuid' => $this->linkPageUuid(),
            'link_slugs' => $this->linkSlugs(),
        ];
    }
}
