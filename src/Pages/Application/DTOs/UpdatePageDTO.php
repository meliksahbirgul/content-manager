<?php

declare(strict_types=1);

namespace Source\Pages\Application\DTOs;

readonly class UpdatePageDTO
{
    /**
     * @param  array<string, string>|null  $title
     * @param  array<string, string>|null  $content
     * @param  array<string, string>|null  $slug
     */
    public function __construct(
        private readonly string $id,
        private readonly ?array $title = null,
        private readonly ?array $content = null,
        private readonly ?array $slug = null,
        private readonly ?int $order = null,
        private readonly ?string $status = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
            order: $data['order'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'order' => $this->order,
            'status' => $this->status,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string,mixed>|null */
    public function title(): ?array
    {
        return $this->title;
    }

    /** @return array<string,mixed>|null */
    public function content(): ?array
    {
        return $this->content;
    }

    /** @return array<string,mixed>|null */
    public function slug(): ?array
    {
        return $this->slug;
    }
}
