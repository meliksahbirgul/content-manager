<?php

declare(strict_types=1);

namespace Source\Media\Domain\Entity;

use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;

class MediaEntity
{
    /**
     * @param  array<string, string>|null  $linkSlugs
     */
    public function __construct(
        private string $uuid,
        private string $mediableType,
        private int $mediableId,
        private MediaCollection $collection,
        private MediaDisk $disk,
        private string $path,
        private string $url,
        private string $originalName,
        private string $mimeType,
        private int $size,
        private ?string $altText = null,
        private ?string $linkPageUuid = null,
        private ?array $linkSlugs = null,
        private int $order = 0,
    ) {}

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function mediableType(): string
    {
        return $this->mediableType;
    }

    public function mediableId(): int
    {
        return $this->mediableId;
    }

    public function collection(): MediaCollection
    {
        return $this->collection;
    }

    public function disk(): MediaDisk
    {
        return $this->disk;
    }

    public function path(): string
    {
        return $this->path;
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

    public function order(): int
    {
        return $this->order;
    }

    /** @param array<string, string>|null $slugs */
    public function withLinkSlugs(?array $slugs): self
    {
        return new self(
            uuid: $this->uuid,
            mediableType: $this->mediableType,
            mediableId: $this->mediableId,
            collection: $this->collection,
            disk: $this->disk,
            path: $this->path,
            url: $this->url,
            originalName: $this->originalName,
            mimeType: $this->mimeType,
            size: $this->size,
            altText: $this->altText,
            linkPageUuid: $this->linkPageUuid,
            linkSlugs: $slugs,
            order: $this->order,
        );
    }
}
