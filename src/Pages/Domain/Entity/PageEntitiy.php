<?php

declare(strict_types=1);

namespace Source\Pages\Domain\Entity;

use Source\Pages\Domain\Enums\PageStatus;

readonly class PageEntity
{
    /**
     * @param array<string, string> $title
     * @param array<string, string> $content
     * @param array<string, string> $slug
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        private string $id,
        private array $title,
        private array $content,
        private array $slug,
        private int $order,
        private PageStatus $status,
        private string|null $parentId = null,
        private array|null $metadata = null,
    ) {}

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

    public function parentId(): string|null
    {
        return $this->parentId;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function status(): PageStatus
    {
        return $this->status;
    }

    /** @return array<string, mixed>|null */
    public function metadata(): array|null
    {
        return $this->metadata;
    }
}
