<?php

declare(strict_types=1);

namespace Source\Pages\Domain\ValueObjects;

use Source\Pages\Domain\Enums\PageStatus;

readonly class UpdatePage
{
    /**
     * @param array<string,string>|null $title
     * @param array<string,string>|null $content
     * @param array<string,string>|null $slug
     */
    public function __construct(
        private array|null $title = null,
        private array|null $content = null,
        private array|null $slug = null,
        private int|null $order = null,
        private PageStatus|null $status = null
    ) {}

    /** @param array<string, mixed> $data */
    public static function createFromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
            order: $data['order'] ?? null,
            status: isset($data['status']) ? PageStatus::from($data['status']) : null
        );
    }

    /** @return array<string, string>|null */
    public function title(): array|null
    {
        return $this->title;
    }

    /** @return array<string, string>|null */
    public function content(): array|null
    {
        return $this->content;
    }

    /** @return array<string, string>|null */
    public function slug(): array|null
    {
        return $this->slug;
    }

    public function order(): int|null
    {
        return $this->order;
    }

    public function status(): PageStatus|null
    {
        return $this->status;
    }
}
