<?php

declare(strict_types=1);

namespace Source\Media\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;

class UploadMedia
{
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
        private int $order = 0,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (! Uuid::isValid($this->uuid)) {
            throw new InvalidArgumentException('Invalid UUID format.');
        }

        if ($this->size <= 0) {
            throw new InvalidArgumentException('Size must be greater than zero.');
        }

        if ($this->linkPageUuid !== null && ! Uuid::isValid($this->linkPageUuid)) {
            throw new InvalidArgumentException('Invalid UUID format for linkPageUuid.');
        }
    }

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

    public function order(): int
    {
        return $this->order;
    }
}
